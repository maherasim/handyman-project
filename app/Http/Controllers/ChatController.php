<?php

namespace App\Http\Controllers;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\PostJobBid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ChatController extends Controller
{
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
                return [
                    'id' => $m->id,
                    'sender_id' => $m->sender_id,
                    'sender_name' => optional($m->sender)->display_name,
                    'sender_avatar_url' => getSingleMedia(optional($m->sender), 'profile_image', null),
                    'message' => $m->message,
                    'created_at' => $m->created_at?->toDateTimeString(),
                    'attachment' => $m->attachment_path ? [
                        'type' => $m->attachment_type,
                        'name' => basename($m->attachment_path),
                        'download_url' => route('chat.download', $m->id)
                    ] : null,
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
            return [
                'id' => $m->id,
                'sender_id' => $m->sender_id,
                'sender_name' => optional($m->sender)->display_name,
                'sender_avatar_url' => getSingleMedia(optional($m->sender), 'profile_image', null),
                'message' => $m->message,
                'created_at' => $m->created_at?->toDateTimeString(),
                'read' => $m->read_at !== null,
                'attachment' => $m->attachment_path ? [
                    'type' => $m->attachment_type,
                    'name' => basename($m->attachment_path),
                    'download_url' => route('chat.download', $m->id)
                ] : null,
            ];
        });

        return response()->json(['status' => true, 'messages' => $messages]);
    }

    public function send(Request $request, int $conversationId)
    {
        $conversation = ChatConversation::findOrFail($conversationId);
        $this->authorizeForConversation($conversation);

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

        $msg = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => Auth::id(),
            'message' => $request->input('message'),
            'attachment_path' => $attachmentPath,
            'attachment_type' => $attachmentType,
        ]);

        return response()->json(['status' => true, 'message' => 'Sent', 'id' => $msg->id]);
    }

    public function download(int $messageId)
    {
        $message = ChatMessage::with('conversation')->findOrFail($messageId);
        $this->authorizeForConversation($message->conversation);
        abort_unless($message->attachment_path && Storage::disk('public')->exists($message->attachment_path), 404);
        $mime = $message->attachment_type ?: 'application/octet-stream';
        return Storage::disk('public')->download($message->attachment_path, basename($message->attachment_path), [
            'Content-Type' => $mime,
        ]);
    }

    public function viewByBid(Request $request, int $bidId)
    {
        $bid = PostJobBid::with(['postrequest'])->findOrFail($bidId);
        $this->authorizeForBid($bid);

        $conversation = ChatConversation::firstOrCreate(
            ['post_job_bid_id' => $bid->id],
            ['user_one_id' => $bid->provider_id, 'user_two_id' => $bid->customer_id]
        );

        return view('chat.show', [
            'conversation' => $conversation,
            'bid' => $bid,
        ]);
    }

    public function unreadPing(Request $request)
    {
        $uid = (int) auth()->id();
        abort_unless($uid > 0, 401);
        $conversationIds = ChatConversation::where('user_one_id', $uid)
            ->orWhere('user_two_id', $uid)
            ->pluck('id');

        if ($conversationIds->isEmpty()) {
            return response()->json(['status' => true, 'count' => 0]);
        }

        $baseQuery = ChatMessage::whereIn('conversation_id', $conversationIds)
            ->where('sender_id', '!=', $uid)
            ->whereNull('read_at');

        $count = (clone $baseQuery)->count();
        $latest = (clone $baseQuery)->with(['sender:id,display_name', 'conversation.bid.postrequest'])
            ->latest('id')->first();

        $latestMeta = null;
        if ($latest) {
            $latestMeta = [
                'id' => $latest->id,
                'conversation_id' => $latest->conversation_id,
                'sender_id' => $latest->sender_id,
                'sender_name' => optional($latest->sender)->display_name,
                'bid_id' => optional($latest->conversation)->post_job_bid_id,
                'bid_title' => optional(optional(optional($latest->conversation)->bid)->postrequest)->title,
                'snippet' => $latest->message ? mb_substr($latest->message, 0, 80) : ($latest->attachment_path ? 'Attachment' : ''),
                'created_at' => $latest->created_at?->toDateTimeString(),
            ];
        }

        return response()->json(['status' => true, 'count' => $count, 'latest' => $latestMeta]);
    }

    public function index(Request $request)
    {
        $uid = (int) auth()->id();
        abort_unless($uid > 0, 401);
        $conversations = ChatConversation::where(function ($q) use ($uid) {
                $q->where('user_one_id', $uid)->orWhere('user_two_id', $uid);
            })
            ->with(['bid.postrequest', 'userOne:id,display_name', 'userTwo:id,display_name'])
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
            $list[] = [
                'conversation_id' => $c->id,
                'bid_id' => $c->post_job_bid_id,
                'bid_title' => optional(optional($c->bid)->postrequest)->title,
                'other_name' => optional($other)->display_name,
                'other_avatar' => getSingleMedia(optional($other), 'profile_image', null),
                'unread' => $unread,
                'last_snippet' => $last ? ($last->message ? mb_substr($last->message, 0, 80) : 'Attachment') : '',
                'last_at' => $last?->created_at?->toDateTimeString(),
            ];
        }

        return view('chat.index', [ 'items' => $list ]);
    }
}

