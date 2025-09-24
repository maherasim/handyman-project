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
            ->with('sender:id,display_name,profile_image')
            ->orderBy('id', 'asc')
            ->limit(50)
            ->get()
            ->map(function (ChatMessage $m) {
                return [
                    'id' => $m->id,
                    'sender_id' => $m->sender_id,
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

        $messages = ChatMessage::where('conversation_id', $conversation->id)
            ->with('sender:id,display_name,profile_image')
            ->orderBy('id', 'asc')
            ->limit(100)
            ->get()
            ->map(function (ChatMessage $m) {
                return [
                    'id' => $m->id,
                    'sender_id' => $m->sender_id,
                    'message' => $m->message,
                    'created_at' => $m->created_at?->toDateTimeString(),
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
}

