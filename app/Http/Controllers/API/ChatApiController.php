<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\PostJobBid;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ChatApiController extends Controller
{
    /**
     * Determine if chat is enabled for a given bid by its status.
     */
    protected function isChatEnabledForBid(\App\Models\PostJobBid $bid): bool
    {
        $allowedStatuses = [
            'advance_paid',
            'in_process',
            'in_progress',
            'hold',
            'done',
            'confirm_done',
            'remaining_paid',
            'completed',
        ];
        return in_array((string) ($bid->status ?? ''), $allowedStatuses, true);
    }

    protected function isChatEnabledForBooking(Booking $booking): bool
    {
        $payment = optional($booking)->payment;
        if (!$payment) { return false; }
        if (($payment->payment_type ?? null) === 'bank_transfer') {
            return (int) ($payment->status ?? 0) === 1;
        }
        return true;
    }
    protected function ensureParticipant(ChatConversation $conversation): void
    {
        $uid = Auth::id();
        abort_unless($uid && $conversation->includesUser($uid), 403);
    }

    public function openByBid(Request $request)
    {
        $request->validate([
            'bid_id' => 'required|integer|exists:post_job_bids,id',
        ]);
        $bid = PostJobBid::findOrFail($request->input('bid_id'));
        $uid = Auth::id();
        abort_unless($uid && ($uid === ($bid->provider_id ?? 0) || $uid === ($bid->customer_id ?? 0)), 403);

        // Gate chat until advance is paid (or later)
        abort_unless($this->isChatEnabledForBid($bid), 403);

        $conversation = ChatConversation::firstOrCreate(
            ['post_job_bid_id' => $bid->id],
            ['user_one_id' => $bid->provider_id, 'user_two_id' => $bid->customer_id]
        );

        return response()->json(['status' => true, 'conversation_id' => $conversation->id]);
    }

    public function openByBooking(Request $request)
    {
        $request->validate(['booking_id' => 'required|integer|exists:bookings,id']);
        $booking = Booking::with(['payment'])->findOrFail($request->input('booking_id'));
        $uid = Auth::id();
        abort_unless($uid && in_array($uid, [ (int) $booking->customer_id, (int) $booking->provider_id ], true), 403);
        abort_unless($this->isChatEnabledForBooking($booking), 403);

        $conversation = ChatConversation::firstOrCreate(
            ['booking_id' => $booking->id, 'post_job_bid_id' => null],
            ['user_one_id' => $booking->customer_id, 'user_two_id' => $booking->provider_id]
        );
        return response()->json(['status' => true, 'conversation_id' => $conversation->id]);
    }

    public function listMessages(int $conversationId)
    {
        $conversation = ChatConversation::findOrFail($conversationId);
        $this->ensureParticipant($conversation);

        // Viewing messages is allowed for all participants regardless of payment

        $beforeId = (int) request()->query('before_id', 0);
        $afterId = (int) request()->query('after_id', 0);
        $limit = (int) request()->query('limit', 50);
        if ($limit < 1 || $limit > 200) { $limit = 50; }

        $query = ChatMessage::where('conversation_id', $conversation->id);
        if ($beforeId > 0) {
            $query->where('id', '<', $beforeId)->orderBy('id', 'desc');
        } elseif ($afterId > 0) {
            $query->where('id', '>', $afterId)->orderBy('id', 'asc');
        } else {
            $query->orderBy('id', 'asc');
        }

        $collection = $query->with('sender:id,display_name')->limit($limit)->get();
        if ($beforeId > 0) { $collection = $collection->reverse()->values(); }

        $currentUserId = (int) auth()->id();
        ChatMessage::where('conversation_id', $conversation->id)
            ->whereNull('read_at')
            ->where('sender_id', '!=', $currentUserId)
            ->update(['read_at' => now()]);

        $messages = $collection->map(function (ChatMessage $m) use ($currentUserId) {
            $hidden = (bool) $m->contains_pii && (int) $m->sender_id !== (int) $currentUserId;
            return [
                'id' => $m->id,
                'sender_id' => $m->sender_id,
                'sender_name' => optional($m->sender)->display_name,
                'sender_avatar_url' => getSingleMedia(optional($m->sender), 'profile_image', null),
                'message' => $hidden ? null : $m->message,
                'created_at' => $m->created_at?->toDateTimeString(),
                'read' => $m->read_at !== null,
                'attachment' => $hidden ? null : ($m->attachment_path ? [
                    'type' => $m->attachment_type,
                    'name' => basename($m->attachment_path),
                    'download_url' => route('api.chat.download', $m->id),
                ] : null),
                'policy_violation' => (bool) $m->contains_pii,
                'hidden' => $hidden,
                'pii_types' => $m->pii_types ? explode(',', $m->pii_types) : [],
            ];
        });
        return response()->json(['status' => true, 'messages' => $messages]);
    }

    public function sendMessage(Request $request, int $conversationId)
    {
        $conversation = ChatConversation::findOrFail($conversationId);
        $this->ensureParticipant($conversation);

        // Keep gating only for sending new content
        $conversation->loadMissing(['bid', 'booking.payment']);
        $bid = $conversation->bid;
        if ($bid) {
            abort_unless($this->isChatEnabledForBid($bid), 403);
        } else if ($conversation->booking) {
            abort_unless($this->isChatEnabledForBooking($conversation->booking), 403);
        }

        $request->validate([
            'message' => 'nullable|string|max:4000',
            'attachment' => 'nullable|file|max:5120',
        ]);

        $attachmentPath = null;
        $attachmentType = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $ext = $file->getClientOriginalExtension();
            $name = Str::uuid()->toString() . ($ext ? ('.' . $ext) : '');
            $attachmentPath = $file->storeAs('chat_attachments', $name, 'public');
            $attachmentType = $file->getMimeType();
        }

        if (!$attachmentPath && !$request->filled('message')) {
            return response()->json(['status' => false, 'message' => 'Message or attachment required'], 422);
        }

        // PII detection
        $text = $request->input('message');
        [$containsPii, $piiTypes] = $this->detectPii($text);

        $msg = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => Auth::id(),
            'message' => $text,
            'attachment_path' => $attachmentPath,
            'attachment_type' => $attachmentType,
            'contains_pii' => $containsPii,
            'pii_types' => $containsPii ? implode(',', $piiTypes) : null,
            'flagged_at' => $containsPii ? now() : null,
        ]);
        $conversation->touch();
        return response()->json(['status' => true, 'id' => $msg->id]);
    }

    /**
     * Detect personal contact information in message text.
     * Returns [bool contains, array types]
     */
    protected function detectPii(?string $text): array
    {
        $text = (string) ($text ?? '');
        if ($text === '') { return [false, []]; }
        $hay = mb_strtolower($text);
        $types = [];

        if (preg_match('/[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}/i', $hay)) {
            $types[] = 'email';
        }
        if (preg_match('/(?:(?:\+|00)?\d{1,3}[\s.-]?)?(?:\(?\d{2,4}\)?[\s.-]?)?\d{3,4}[\s.-]?\d{3,4}/', $hay)) {
            if (preg_match_all('/\d/', $hay, $m) && count($m[0]) >= 7) {
                $types[] = 'phone';
            }
        }
        if (strpos($hay, 'whatsapp') !== false || strpos($hay, 'wa.me/') !== false || strpos($hay, 'api.whatsapp.com') !== false) {
            $types[] = 'whatsapp';
        }
        if (strpos($hay, 'telegram') !== false || strpos($hay, 't.me/') !== false) {
            $types[] = 'telegram';
        }

        $types = array_values(array_unique($types));
        return [!empty($types), $types];
    }

    public function conversations(Request $request)
    {
        $uid = (int) Auth::id();
        abort_unless($uid > 0, 401);
        $page = max(1, (int) $request->query('page', 1));
        $perPage = min(100, max(1, (int) $request->query('per_page', 20)));
        $skip = ($page - 1) * $perPage;

        $base = ChatConversation::where(function($q) use ($uid){
                $q->where('user_one_id', $uid)->orWhere('user_two_id', $uid);
            })
            ->with(['bid.postrequest', 'userOne:id,display_name', 'userTwo:id,display_name'])
            ->orderBy('updated_at', 'desc');

        $total = (clone $base)->count();
        $convs = (clone $base)->skip($skip)->take($perPage)->get();

        $items = [];
        foreach ($convs as $c) {
            $last = ChatMessage::where('conversation_id', $c->id)->orderByDesc('id')->first();
            $unread = ChatMessage::where('conversation_id', $c->id)
                ->whereNull('read_at')->where('sender_id', '!=', $uid)->count();
            $otherId = ($c->user_one_id === $uid) ? $c->user_two_id : $c->user_one_id;
            $other = $otherId === optional($c->userOne)->id ? $c->userOne : $c->userTwo;
            $items[] = [
                'conversation_id' => $c->id,
                'bid_id' => $c->post_job_bid_id,
                'bid_title' => optional(optional($c->bid)->postrequest)->title,
                'other_name' => optional($other)->display_name,
                'other_avatar_url' => getSingleMedia(optional($other), 'profile_image', null),
                'unread' => $unread,
                'last_snippet' => $last ? ($last->message ? mb_substr($last->message, 0, 120) : 'Attachment') : '',
                'last_at' => $last?->created_at?->toDateTimeString(),
            ];
        }

        return response()->json([
            'status' => true,
            'page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'items' => $items,
        ]);
    }

    public function unread(Request $request)
    {
        $uid = (int) Auth::id();
        abort_unless($uid > 0, 401);
        $conversationIds = ChatConversation::where(function($q) use ($uid){
                $q->where('user_one_id', $uid)->orWhere('user_two_id', $uid);
            })
            ->pluck('id');
        if ($conversationIds->isEmpty()) {
            return response()->json(['status' => true, 'count' => 0]);
        }
        $base = ChatMessage::whereIn('conversation_id', $conversationIds)
            ->where('sender_id', '!=', $uid)->whereNull('read_at');
        $count = (clone $base)->count();
        $latest = (clone $base)->with('sender:id,display_name')->latest('id')->first();
        return response()->json([
            'status' => true,
            'count' => $count,
            'latest' => $latest ? [
                'id' => $latest->id,
                'sender_id' => $latest->sender_id,
                'sender_name' => optional($latest->sender)->display_name,
                'created_at' => $latest->created_at?->toDateTimeString(),
            ] : null,
        ]);
    }

    public function markRead(Request $request, int $conversationId)
    {
        $conversation = ChatConversation::findOrFail($conversationId);
        $this->ensureParticipant($conversation);
        $uid = (int) Auth::id();
        $upToId = (int) $request->input('up_to_id', 0);
        $q = ChatMessage::where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', $uid)
            ->whereNull('read_at');
        if ($upToId > 0) { $q->where('id', '<=', $upToId); }
        $updated = $q->update(['read_at' => now()]);
        return response()->json(['status' => true, 'updated' => $updated]);
    }

    public function download(int $messageId)
    {
        $message = ChatMessage::with('conversation')->findOrFail($messageId);
        $this->ensureParticipant($message->conversation);
        // Enforce chat gating by bid status
        $message->conversation->loadMissing('bid');
        $bid = $message->conversation->bid;
        abort_unless($bid && $this->isChatEnabledForBid($bid), 403);
        abort_unless($message->attachment_path && Storage::disk('public')->exists($message->attachment_path), 404);
        $mime = $message->attachment_type ?: 'application/octet-stream';
        return Storage::disk('public')->download($message->attachment_path, basename($message->attachment_path), [
            'Content-Type' => $mime,
        ]);
    }
}

