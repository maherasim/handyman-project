<?php

namespace App\Http\Controllers;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\PostJobBid;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

class ChatController extends Controller
{
    /**
     * Determine if 1:1 chat is enabled for the given bid based on its status.
     * Now allows chat for all statuses - no advance payment restriction.
     */
    protected function isChatEnabledForBid(\App\Models\PostJobBid $bid): bool
    {
        // Allow chat for all bid statuses - no restrictions
        return true;
    }

    /** Determine if 1:1 chat is enabled for the given booking based on payment. */
    protected function isChatEnabledForBooking(Booking $booking): bool
    {
        // Allow chat for all bookings - no payment restrictions
        return true;
    }

    protected function authorizeForBid(PostJobBid $bid): void
    {
        $user = Auth::user();
        abort_unless($user && ($user->id === ($bid->provider_id ?? 0) || $user->id === ($bid->customer_id ?? 0)), 403);
    }

    protected function authorizeForConversation(ChatConversation $conversation): void
    {
        $userId = Auth::id();
        abort_unless($userId && $conversation->includesUser($userId), 403);
    }

    public function open(Request $request, int $bidId)
    {
        $bid = PostJobBid::with(['postrequest'])->findOrFail($bidId);
        $this->authorizeForBid($bid);

        $conversation = ChatConversation::firstOrCreate(
            ['post_job_bid_id' => $bid->id],
            ['user_one_id' => $bid->provider_id, 'user_two_id' => $bid->customer_id]
        );

        $messages = ChatMessage::where('conversation_id', $conversation->id)
            ->with('sender:id,display_name')
            ->orderBy('id', 'asc')
            ->limit(50)
            ->get()
            ->map(function (ChatMessage $m) {
                $hidden = (bool) $m->contains_pii; // hide for both sides
                return [
                    'id' => $m->id,
                    'sender_id' => $m->sender_id,
                    'sender_name' => optional($m->sender)->display_name,
                    'sender_avatar_url' => getSingleMedia(optional($m->sender), 'profile_image', null),
                    'message' => $hidden ? null : $m->message,
                    'created_at' => $m->created_at?->toDateTimeString(),
                    'attachment' => $hidden ? null : ($m->attachment_path ? [
                        'type' => $m->attachment_type,
                        'name' => basename($m->attachment_path),
                        'download_url' => route('chat.download', $m->id)
                    ] : null),
                    'policy_violation' => (bool) $m->contains_pii,
                    'hidden' => $hidden,
                    'pii_types' => $m->pii_types ? explode(',', $m->pii_types) : [],
                ];
            });

        return response()->json([
            'status' => true,
            'conversation' => [
                'id' => $conversation->id,
                'bid_id' => $bid->id,
            ],
            'messages' => $messages,
            'current_user_id' => Auth::id(),
        ]);
    }

    public function messages(Request $request, int $conversationId)
    {
        $conversation = ChatConversation::findOrFail($conversationId);
        $this->authorizeForConversation($conversation);

        // Viewing messages should always be allowed once you are a participant

        $beforeId = (int) $request->query('before_id', 0);
        $afterId = (int) $request->query('after_id', 0);
        $limit = (int) $request->query('limit', 50);
        if ($limit < 1 || $limit > 200) { $limit = 50; }

        $query = ChatMessage::where('conversation_id', $conversation->id);
        if ($beforeId > 0) {
            $query->where('id', '<', $beforeId)->orderBy('id', 'desc');
        } elseif ($afterId > 0) {
            $query->where('id', '>', $afterId)->orderBy('id', 'asc');
        } else {
            $query->orderBy('id', 'asc');
        }

        $messagesCollection = $query->with('sender:id,display_name')->limit($limit)->get();
        // If we fetched older with desc order, reverse to chronological
        if ($beforeId > 0) {
            $messagesCollection = $messagesCollection->reverse()->values();
        }

        // Mark as read any messages from the other user
        $currentUserId = (int) auth()->id();
        ChatMessage::where('conversation_id', $conversation->id)
            ->whereNull('read_at')
            ->where('sender_id', '!=', $currentUserId)
            ->update(['read_at' => now()]);

        $messages = $messagesCollection->map(function (ChatMessage $m) {
            $hidden = (bool) $m->contains_pii; // hide for both sides
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
                    'download_url' => route('chat.download', $m->id)
                ] : null),
                'policy_violation' => (bool) $m->contains_pii,
                'hidden' => $hidden,
                'pii_types' => $m->pii_types ? explode(',', $m->pii_types) : [],
            ];
        });

        return response()->json(['status' => true, 'messages' => $messages]);
    }

    public function send(Request $request, int $conversationId)
    {
        $conversation = ChatConversation::findOrFail($conversationId);
        $this->authorizeForConversation($conversation);

        // Sending messages is now allowed for all participants - no advance payment restriction

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

        // Send notification to the recipient
        $this->sendMessageNotification($conversation, $msg);

        return response()->json([
            'status' => true,
            'message' => 'Sent',
            'id' => $msg->id,
            'flagged' => (bool) $containsPii,
            'pii_types' => $containsPii ? $piiTypes : [],
        ]);
    }

    /**
     * Send notification to the recipient of a chat message.
     */
    protected function sendMessageNotification(ChatConversation $conversation, ChatMessage $message): void
    {
        $sender = $message->sender;
        $recipientId = ($conversation->user_one_id === $sender->id) 
            ? $conversation->user_two_id 
            : $conversation->user_one_id;
            
        $recipient = \App\Models\User::find($recipientId);
        if (!$recipient) return;
        
        // Prepare notification data - using a simple approach
        $notificationData = [
            'id' => $message->id,
            'type' => 'chat_message',
            'subject' => 'New Message from ' . ($sender->display_name ?? $sender->name),
            'message' => $message->message ? mb_substr($message->message, 0, 100) : 'New attachment',
            'sender_name' => $sender->display_name ?? $sender->name,
            'sender_id' => $sender->id,
            'conversation_id' => $conversation->id,
            'user_type' => $recipient->user_type,
            "ios_badgeType" => "Increase",
            "ios_badgeCount" => 1,
            "notification-type" => 'chat'
        ];
        
        // Send notification using the existing helper function
        try {
            sendNotification($recipient->user_type, $recipient, $notificationData);
        } catch (\Exception $e) {
            // Log error but don't break the message sending
            \Log::error('Failed to send chat notification: ' . $e->getMessage());
        }
    }

    /**
     * Detect personal contact information in a text message.
     * Returns array [bool containsPii, array types]
     */
    protected function detectPii(?string $text): array
    {
        $text = (string) ($text ?? '');
        if ($text === '') { return [false, []]; }
        $hay = mb_strtolower($text);
        $types = [];

        // Normalize obfuscated emails like "name at the rate g mail dot com"
        $norm = $hay;
        $norm = preg_replace('/\b(at the rate|at)\b/i', '@', $norm);
        $norm = str_ireplace(['[at]', '(at)', '{at}', ' (at) ', ' [at] '], '@', $norm);
        $norm = preg_replace('/\b(dot)\b/i', '.', $norm);
        $norm = str_ireplace(['[dot]', '(dot)', '{dot}', ' (dot) ', ' [dot] '], '.', $norm);
        // Collapse spaces around @ and .
        $norm = preg_replace('/\s*(@|\.)\s*/', '$1', $norm);
        // Merge common providers split by spaces
        $providerPatterns = [
            '/g\s*mail/i' => 'gmail',
            '/y\s*ahoo/i' => 'yahoo',
            '/hot\s*mail/i' => 'hotmail',
            '/out\s*look/i' => 'outlook',
            '/proton\s*mail/i' => 'protonmail',
            '/i\s*cloud/i' => 'icloud',
            '/y\s*andex/i' => 'yandex',
            '/z\s*o\s*h\s*o/i' => 'zoho',
        ];
        foreach ($providerPatterns as $pat => $rep) {
            $norm = preg_replace($pat, $rep, $norm);
        }
        // Email addresses (direct or normalized)
        if (preg_match('/[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}/i', $hay) || preg_match('/[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}/i', $norm)) {
            $types[] = 'email';
        }

        // Phone numbers (international/local patterns: 7+ digits possibly separated)
        if (preg_match('/(?:(?:\+|00)?\d{1,3}[\s.-]?)?(?:\(?\d{2,4}\)?[\s.-]?)?\d{3,4}[\s.-]?\d{3,4}/', $hay)) {
            if (preg_match_all('/\d/', $hay, $m) && count($m[0]) >= 7) { $types[] = 'phone'; }
        }
        // Spelled-out phone numbers (e.g., "zero three one two ..." with double/triple)
        $tokens = preg_split('/[^a-z0-9+]+/i', $hay, -1, PREG_SPLIT_NO_EMPTY);
        $wordToDigit = [ 'zero'=>'0','oh'=>'0','o'=>'0','one'=>'1','two'=>'2','three'=>'3','four'=>'4','five'=>'5','six'=>'6','seven'=>'7','eight'=>'8','nine'=>'9' ];
        $digitCount = 0; $repeat = 1;
        foreach ($tokens as $tok) {
            if ($tok === 'double') { $repeat = 2; continue; }
            if ($tok === 'triple') { $repeat = 3; continue; }
            $digitsToAdd = '';
            if (isset($wordToDigit[$tok])) { $digitsToAdd = str_repeat($wordToDigit[$tok], $repeat); }
            elseif (preg_match('/^\+?\d+$/', $tok)) { $digitsToAdd = str_repeat(preg_replace('/\D/', '', $tok), $repeat); }
            if ($digitsToAdd !== '') {
                $digitCount += strlen($digitsToAdd);
                if ($digitCount >= 7) { $types[] = 'phone'; break; }
            }
            $repeat = 1;
        }

        // WhatsApp keywords or wa.me links
        if (strpos($hay, 'whatsapp') !== false || strpos($hay, 'wa.me/') !== false || strpos($hay, 'api.whatsapp.com') !== false) { $types[] = 'whatsapp'; }
        // Social handles hint: telegram
        if (strpos($hay, 'telegram') !== false || strpos($hay, 't.me/') !== false) { $types[] = 'telegram'; }

        // Email provider keywords (covers cases like "name at gmail dot com")
        $emailProviders = ['gmail','yahoo','hotmail','outlook','icloud','protonmail','ymail','gmx','aol','mail.com','yandex','zoho'];
        foreach ($emailProviders as $prov) { if (strpos($hay, $prov) !== false) { $types[] = 'email'; break; } }

        // Instagram handles/links
        if (strpos($hay, 'instagram.com') !== false || preg_match('/\binsta(?:gram)?\b/i', $hay)) { $types[] = 'instagram'; }
        // Facebook handles/links
        if (strpos($hay, 'facebook.com') !== false || strpos($hay, 'fb.com') !== false || strpos($hay, 'm.me/') !== false || strpos($hay, 'messenger.com') !== false || preg_match('/\bfacebook\b/i', $hay)) { $types[] = 'facebook'; }

        $types = array_values(array_unique($types));
        return [!empty($types), $types];
    }

    public function download(int $messageId)
    {
        $message = ChatMessage::with('conversation')->findOrFail($messageId);
        $this->authorizeForConversation($message->conversation);

        // Allow downloading attachments for existing conversations you are in
        abort_unless($message->attachment_path && Storage::disk('public')->exists($message->attachment_path), 404);
        $mime = $message->attachment_type ?: 'application/octet-stream';
        return Storage::disk('public')->download($message->attachment_path, basename($message->attachment_path), [
            'Content-Type' => $mime,
        ]);
    }

    // REMOVED: viewByBid method - using standalone chat only

    // REMOVED: All booking/bid dependent methods - using standalone chat only
    // The old viewByBooking and viewByBookingHandyman methods have been removed
    // All chat functionality now uses the standalone viewWithUser method

    /**
     * Completely standalone user-to-user chat - NO dependencies on booking/bid.
     */
    public function viewWithUser(Request $request, int $userId)
    {
        $currentUser = Auth::user();
        $targetUser = \App\Models\User::findOrFail($userId);
        
        // Prevent users from chatting with themselves
        abort_if($currentUser->id === $userId, 403, 'Cannot chat with yourself');
        
        // Find or create conversation between these two users - completely standalone
        $conversation = ChatConversation::where(function($query) use ($currentUser, $userId) {
            $query->where('user_one_id', $currentUser->id)
                  ->where('user_two_id', $userId);
        })->orWhere(function($query) use ($currentUser, $userId) {
            $query->where('user_one_id', $userId)
                  ->where('user_two_id', $currentUser->id);
        })->whereNull('booking_id')
          ->whereNull('post_job_bid_id')
          ->first();
        
        if (!$conversation) {
            $conversation = ChatConversation::create([
                'user_one_id' => $currentUser->id,
                'user_two_id' => $userId,
                'conversation_type' => 'standalone',
                'booking_id' => null,
                'post_job_bid_id' => null
            ]);
        }
        
        return view('chat.simple', [
            'conversation' => $conversation,
            'targetUser' => $targetUser
        ]);
    }

    public function unreadPing(Request $request)
    {
        $uid = (int) auth()->id();
        abort_unless($uid > 0, 401);
        
        // Get ONLY standalone conversations for this user
        $conversationIds = ChatConversation::where(function($query) use ($uid) {
            $query->where('user_one_id', $uid)->orWhere('user_two_id', $uid);
        })
        ->whereNull('booking_id')
        ->whereNull('post_job_bid_id')
        ->where('conversation_type', 'standalone')
        ->pluck('id');

        if ($conversationIds->isEmpty()) {
            return response()->json(['status' => true, 'count' => 0]);
        }

        $baseQuery = ChatMessage::whereIn('conversation_id', $conversationIds)
            ->where('sender_id', '!=', $uid)
            ->whereNull('read_at');

        $count = (clone $baseQuery)->count();
        $latest = (clone $baseQuery)->with(['sender:id,display_name', 'conversation.userOne:id,display_name', 'conversation.userTwo:id,display_name'])
            ->latest('id')->first();

        $latestMeta = null;
        if ($latest) {
            // Get the other user in the conversation
            $otherUser = ($latest->conversation->user_one_id === $uid) 
                ? $latest->conversation->userTwo 
                : $latest->conversation->userOne;
                
            $latestMeta = [
                'id' => $latest->id,
                'conversation_id' => $latest->conversation_id,
                'sender_id' => $latest->sender_id,
                'sender_name' => optional($latest->sender)->display_name,
                'other_user_id' => optional($otherUser)->id,
                'other_user_name' => optional($otherUser)->display_name,
                'snippet' => $latest->message ? mb_substr($latest->message, 0, 80) : ($latest->attachment_path ? 'Attachment' : ''),
                'created_at' => $latest->created_at?->toDateTimeString(),
            ];
        }

        return response()->json(['status' => true, 'count' => $count, 'latest' => $latestMeta]);
    }

    /**
     * Show all standalone conversations for the current user - NO booking/bid dependencies.
     */
    public function index(Request $request)
    {
        $uid = (int) auth()->id();
        abort_unless($uid > 0, 401);

        // Get ONLY standalone conversations for this user
        $conversations = ChatConversation::where(function ($q) use ($uid) {
                $q->where('user_one_id', $uid)->orWhere('user_two_id', $uid);
            })
            ->whereNull('booking_id')
            ->whereNull('post_job_bid_id')
            ->where('conversation_type', 'standalone')
            ->with(['userOne:id,display_name', 'userTwo:id,display_name'])
            ->orderBy('updated_at', 'desc')
            ->get();

        $list = [];
        foreach ($conversations as $c) {
            $last = ChatMessage::where('conversation_id', $c->id)->orderByDesc('id')->first();
            $unread = ChatMessage::where('conversation_id', $c->id)
                ->whereNull('read_at')
                ->where('sender_id', '!=', $uid)
                ->count();
            $otherId = ($c->user_one_id === $uid) ? $c->user_two_id : $c->user_one_id;
            $other = $otherId === optional($c->userOne)->id ? $c->userOne : $c->userTwo;

            // Simple standalone URL - no booking/bid logic
            $url = route('chat.view.user', $otherId);
            $title = 'Direct Message with ' . (optional($other)->display_name ?? 'Unknown');

            $maskedSnippet = '';
            if ($last) {
                $maskedSnippet = $last->contains_pii ? 'Message hidden due to policy violation' : ($last->message ? mb_substr($last->message, 0, 80) : 'Attachment');
            }
            $list[] = [
                'conversation_id' => $c->id,
                'url' => $url,
                'title' => $title,
                'bid_id' => null, // No bid dependency
                'bid_title' => null, // No bid dependency
                'other_name' => optional($other)->display_name,
                'other_avatar' => getSingleMedia(optional($other), 'profile_image', null),
                'unread' => $unread,
                'last_snippet' => $maskedSnippet,
                'last_at' => $last?->created_at?->toDateTimeString(),
            ];
        }

        return view('chat.index', [ 'items' => $list ]);
    }

    /**
     * Admin: view flagged messages list.
     */
    public function flaggedIndex(Request $request)
    {
        $user = auth()->user();
        abort_unless($user && ($user->hasRole('admin') || $user->hasRole('demo_admin')), 403);
        $perPage = 50;
        $messages = ChatMessage::where('contains_pii', true)
            ->with(['sender:id,display_name', 'conversation'])
            ->orderByDesc('id')
            ->paginate($perPage);
        return view('chat.flagged', ['messages' => $messages]);
    }

    public function sendWarningEmail(Request $request, int $id)
    {
        $user = auth()->user();
        abort_unless($user && ($user->hasRole('admin') || $user->hasRole('demo_admin')), 403);
        $message = ChatMessage::with('sender')->findOrFail($id);
        $recipient = optional($message->sender);
        $email = $recipient->email ?? null;
        abort_unless($email, 422);

        $piiTypes = $message->pii_types ? explode(',', $message->pii_types) : [];
        $snippet = $message->message ? (mb_strlen($message->message) > 160 ? (mb_substr($message->message, 0, 160) . '…') : $message->message) : '';

        $data = [
            'name' => $recipient->display_name ?? ($recipient->first_name ?? 'User'),
            'types' => $piiTypes,
            'snippet' => $snippet,
            'date' => $message->created_at?->toDateTimeString(),
        ];

        Mail::send('emails.chat_pii_warning', $data, function($m) use ($email) {
            $m->to($email)->subject('Policy Warning: Sharing personal contact information');
        });

        return back()->with('status', 'Warning email sent to sender.');
    }

    /**
     * Admin ping for flagged (PII) messages.
     */
    public function flaggedPing(Request $request)
    {
        $user = auth()->user();
        abort_unless($user && ($user->hasRole('admin') || $user->hasRole('demo_admin')), 403);
        $count = ChatMessage::where('contains_pii', true)->count();
        $latest = ChatMessage::where('contains_pii', true)->with('sender:id,display_name')->latest('id')->first();
        return response()->json([
            'status' => true,
            'count' => (int) $count,
            'latest' => $latest ? [
                'id' => $latest->id,
                'sender_id' => $latest->sender_id,
                'sender_name' => optional($latest->sender)->display_name,
                'created_at' => $latest->created_at?->toDateTimeString(),
                'types' => $latest->pii_types ? explode(',', $latest->pii_types) : [],
            ] : null,
        ]);
    }
}

