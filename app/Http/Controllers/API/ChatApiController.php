<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\PostJobBid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ChatApiController extends Controller
{
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

        $conversation = ChatConversation::firstOrCreate(
            ['post_job_bid_id' => $bid->id],
            ['user_one_id' => $bid->provider_id, 'user_two_id' => $bid->customer_id]
        );

        return response()->json(['status' => true, 'conversation_id' => $conversation->id]);
    }

    public function listMessages(int $conversationId)
    {
        $conversation = ChatConversation::findOrFail($conversationId);
        $this->ensureParticipant($conversation);
        $messages = ChatMessage::where('conversation_id', $conversation->id)
            ->orderBy('id', 'asc')
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
                        'download_url' => route('api.chat.download', $m->id),
                    ] : null,
                ];
            });
        return response()->json(['status' => true, 'messages' => $messages]);
    }

    public function sendMessage(Request $request, int $conversationId)
    {
        $conversation = ChatConversation::findOrFail($conversationId);
        $this->ensureParticipant($conversation);

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

        return response()->json(['status' => true, 'id' => $msg->id]);
    }

    public function download(int $messageId)
    {
        $message = ChatMessage::with('conversation')->findOrFail($messageId);
        $this->ensureParticipant($message->conversation);
        abort_unless($message->attachment_path && Storage::disk('public')->exists($message->attachment_path), 404);
        $mime = $message->attachment_type ?: 'application/octet-stream';
        return Storage::disk('public')->download($message->attachment_path, basename($message->attachment_path), [
            'Content-Type' => $mime,
        ]);
    }
}

