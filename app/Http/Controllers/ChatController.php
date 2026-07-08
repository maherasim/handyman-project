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
use App\Events\ChatMessageSent;
use App\Mail\ChatMessageNotificationMail;

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
                'violation_message' => $hidden ? __('messages.chat_pii_warning_bubble') : null,
            ];
        });

        return response()->json(['status' => true, 'messages' => $messages]);
    }

    public function send(Request $request, int $conversationId)
    {
        $conversation = ChatConversation::findOrFail($conversationId);
        $currentUser = Auth::user();
        
        // Debug logging
        \Log::info('Chat send - User: ' . $currentUser->id . ' (' . $currentUser->user_type . '), Conversation: ' . $conversationId . ', Users: ' . $conversation->user_one_id . ' <-> ' . $conversation->user_two_id);
        
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
            return response()->json(['status' => false, 'message' => __('messages.chat_message_or_attachment_required')], 422);
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

        // Ensure this conversation floats to the top in lists
        $conversation->touch();

        // Broadcast realtime event
        try {
            $payload = [
                'id' => (int) $msg->id,
                'conversation_id' => (int) $conversation->id,
                'sender_id' => (int) $msg->sender_id,
                'sender_name' => optional($msg->sender)->display_name,
                'sender_avatar_url' => getSingleMedia(optional($msg->sender), 'profile_image', null),
                'message' => $msg->contains_pii ? null : $msg->message,
                'created_at' => $msg->created_at?->toDateTimeString(),
                'attachment' => $msg->contains_pii ? null : ($msg->attachment_path ? [
                    'type' => $msg->attachment_type,
                    'name' => basename($msg->attachment_path),
                    'download_url' => route('chat.download', $msg->id)
                ] : null),
                'policy_violation' => (bool) $msg->contains_pii,
                'hidden' => (bool) $msg->contains_pii,
                'pii_types' => $msg->pii_types ? explode(',', $msg->pii_types) : [],
                'violation_message' => $msg->contains_pii ? __('messages.chat_pii_warning_bubble') : null,
            ];
            broadcast(new ChatMessageSent((int) $conversation->id, $payload))->toOthers();
        } catch (\Throwable $e) {
            \Log::warning('Broadcast failed for message '.$msg->id.': '.$e->getMessage());
        }

        // Load sender relationship for notification
        $msg->load('sender');
        
        // Send notification to the recipient
        \Log::info('Sending notification for message ' . $msg->id . ' in conversation ' . $conversation->id);
        $this->sendMessageNotification($conversation, $msg);

        return response()->json([
            'status' => true,
            'message' => __('messages.chat_sent'),
            'id' => $msg->id,
            'flagged' => (bool) $containsPii,
            'pii_types' => $containsPii ? $piiTypes : [],
            'warning_message' => $containsPii ? __('messages.chat_pii_warning') : null,
        ]);
    }

    /**
     * Send notification to the recipient of a chat message.
     */
    protected function sendMessageNotification(ChatConversation $conversation, ChatMessage $message): void
    {
        // Ensure sender is loaded
        if (!$message->relationLoaded('sender')) {
            $message->load('sender');
        }
        
        $sender = $message->sender;
        if (!$sender) {
            \Log::error('Chat notification failed - Sender not found for message ID: ' . $message->id);
            return;
        }
        
        $recipientId = ($conversation->user_one_id === $sender->id) 
            ? $conversation->user_two_id 
            : $conversation->user_one_id;
            
        $recipient = \App\Models\User::find($recipientId);
        if (!$recipient) {
            \Log::error('Chat notification failed - Recipient not found: ' . $recipientId);
            return;
        }
        
        // Debug logging
        \Log::info('Chat notification - Sender: ' . $sender->id . ' (' . $sender->user_type . '), Recipient: ' . $recipient->id . ' (' . $recipient->user_type . ')');
        \Log::info('Recipient email: ' . ($recipient->email ?? 'NULL'));
        
        // Use CommonNotification with template system for consistency
        try {
            $messagePreview = $message->message ? mb_substr($message->message, 0, 100) : __('messages.chat_new_attachment');
            
            $notificationData = [
                'id' => $message->id,
                'message_id' => $message->id,
                'conversation_id' => $conversation->id,
                'sender_id' => $sender->id,
                'sender_name' => $sender->display_name ?? $sender->name,
                'message_preview' => $messagePreview,
                'user_type' => $recipient->user_type ?? 'user',
            ];
            
            // Use CommonNotification which will use templates
            $recipient->notify(new \App\Notifications\CommonNotification('chat_message', $notificationData));
            
            // Also try direct FCM as fallback (but don't fail if it doesn't work)
            $this->sendDirectFCMNotification($recipient, $sender, $message);
            
        } catch (\Exception $e) {
            // Log error but don't break the message sending
            \Log::error('Failed to send chat notification: ' . $e->getMessage());
            // Fallback: create direct database notification if template system fails
            try {
                $notificationData = [
                    'id' => $message->id,
                    'type' => 'chat_message',
                    'message_id' => $message->id,
                    'conversation_id' => $conversation->id,
                    'sender_id' => $sender->id,
                    'sender_name' => $sender->display_name ?? $sender->name,
                    'message_preview' => $message->message ? mb_substr($message->message, 0, 100) : __('messages.chat_new_attachment'),
                ];
                \DB::table('notifications')->insert([
                    'id' => \Str::uuid()->toString(),
                    'type' => 'App\Notifications\CommonNotification',
                    'notifiable_type' => 'App\Models\User',
                    'notifiable_id' => $recipient->id,
                    'data' => json_encode($notificationData),
                    'read_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Exception $fallbackError) {
                \Log::error('Failed to create fallback chat notification: ' . $fallbackError->getMessage());
            }
        }
        // Send only the chat-message-notification.blade.php email (no template email for chat_message)
        try {
            if ($recipient && $recipient->email) {
                Mail::to($recipient->email)->locale(getRecipientLocale($recipient))->send(new ChatMessageNotificationMail($recipient, $sender, $message, $conversation, getRecipientLocale($recipient))); // *** new: locale-aware email ***
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send chat message email: ' . $e->getMessage());
        }
    }
    
    /**
     * Send direct FCM notification for chat messages.
     */
    protected function sendDirectFCMNotification($recipient, $sender, $message): void
    {
        try {
            $othersetting = \App\Models\Setting::where('type','OTHER_SETTING')->first();
            $decodedata = $othersetting ? json_decode($othersetting['value']) : null;
            $firebase_notification = $decodedata->firebase_notification ?? 0;

            if($firebase_notification == 1) {
                $projectID = isset($decodedata->project_id) ? $decodedata->project_id : null;
                if (!$projectID) {
                    \Log::info('FCM notification skipped - No project ID configured');
                    return;
                }

                $apiUrl = 'https://fcm.googleapis.com/v1/projects/' . $projectID . '/messages:send';
                $access_token = getAccessToken();
                
                if (!$access_token) {
                    \Log::info('FCM notification skipped - No access token available');
                    return;
                }
                
                $headers = [
                    'Authorization: Bearer ' . $access_token,
                    'Content-Type: application/json',
                ];

                $heading = __('messages.chat_new_message_from') . ' ' . ($sender->display_name ?? $sender->name);
                $content = $message->message ? mb_substr($message->message, 0, 100) : __('messages.chat_new_attachment');

                // FCM v1 API requires payload wrapped in 'message' object
                $firebase_data = [
                    'message' => [
                        'topic' => 'user_' . $recipient->id,
                        'notification' => [
                            'title' => $heading,
                            'body' => $content,
                        ],
                        'data' => [
                            'type' => 'chat_message',
                            'id' => (string) $message->id,
                            'conversation_id' => (string) $message->conversation_id,
                            'sender_id' => (string) $sender->id,
                            'sender_name' => $sender->display_name ?? $sender->name,
                        ],
                        'android' => [
                            'priority' => 'high',
                            'notification' => [
                                'sound' => 'default',
                                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                            ],
                        ],
                        'apns' => [
                            'headers' => [
                                'apns-priority' => '10',
                            ],
                            'payload' => [
                                'aps' => [
                                    'sound' => 'default',
                                ],
                            ],
                        ],
                    ],
                ];

                $ch = curl_init($apiUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($firebase_data));

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($httpCode === 200) {
                    \Log::info('FCM notification sent successfully to user ' . $recipient->id);
                } else {
                    \Log::warning('FCM notification failed for user ' . $recipient->id . ' - HTTP ' . $httpCode . ': ' . $response);
                }
            } else {
                \Log::info('FCM notification skipped - Firebase notifications disabled');
            }
        } catch (\Exception $e) {
            \Log::warning('FCM notification failed: ' . $e->getMessage());
        }
    }

    /**
     * Detect personal contact information in a text message.
     * Returns array [bool containsPii, array types]
     */
    protected function detectPii(?string $text): array
    {
        return \App\Services\PiiDetector::detect($text);
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
        
        // Debug logging
        \Log::info('Chat viewWithUser - Current user: ' . $currentUser->id . ' (' . $currentUser->user_type . '), Target user: ' . $userId . ' (' . $targetUser->user_type . '), Status: ' . $targetUser->status . ', Verified: ' . ($targetUser->email_verified_at ? 'Yes' : 'No'));
        
        // Prevent users from chatting with themselves
        abort_if($currentUser->id === $userId, 403, __('messages.chat_cannot_chat_self'));
        
        // Find or create conversation between these two users
        // Prefer the most recently active conversation between these two users (any type)
        $conversation = ChatConversation::where(function($query) use ($currentUser, $userId) {
            $query->where('user_one_id', $currentUser->id)
                  ->where('user_two_id', $userId);
        })->orWhere(function($query) use ($currentUser, $userId) {
            $query->where('user_one_id', $userId)
                  ->where('user_two_id', $currentUser->id);
        })
          ->orderByDesc('updated_at')
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
        
        // Include all conversations (standalone or legacy) for this user
        $conversationIds = ChatConversation::where(function($query) use ($uid) {
            $query->where('user_one_id', $uid)->orWhere('user_two_id', $uid);
        })->pluck('id');

        if ($conversationIds->isEmpty()) {
            return response()->json(['status' => true, 'count' => 0]);
        }

        $baseQuery = ChatMessage::whereIn('conversation_id', $conversationIds)
            ->where('sender_id', '!=', $uid)
            ->whereNull('read_at');

        $count = (clone $baseQuery)->count();

        // First load can request to ignore the persisted last seen, so initial toast appears
        $ignoreLastSeen = (bool) $request->boolean('first');
        $lastSeenId = (int) optional(auth()->user())->last_notification_seen;
        $latestQuery = (clone $baseQuery)
            ->with(['sender:id,display_name', 'conversation.userOne:id,display_name', 'conversation.userTwo:id,display_name'])
            ->latest('id');
        if (!$ignoreLastSeen && $lastSeenId > 0) {
            $latestQuery->where('id', '>', $lastSeenId);
        }
        $latest = $latestQuery->first();

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
                'snippet' => $latest->message ? mb_substr($latest->message, 0, 80) : ($latest->attachment_path ? __('messages.attachment') : ''),
                'created_at' => $latest->created_at?->toDateTimeString(),
            ];
        }

        return response()->json(['status' => true, 'count' => $count, 'latest' => $latestMeta]);
    }

    /**
     * Persist the last notification id that the user has acknowledged to avoid repeat toasts.
     */
    public function unreadAck(Request $request)
    {
        $uid = (int) auth()->id();
        abort_unless($uid > 0, 401);
        $lastId = (int) $request->input('last_id', 0);
        if ($lastId <= 0) {
            return response()->json(['status' => false], 422);
        }
        $user = auth()->user();
        $current = (int) ($user->last_notification_seen ?? 0);
        if ($lastId > $current) {
            $user->last_notification_seen = $lastId;
            $user->save();
        }
        return response()->json(['status' => true, 'last_id' => (int) $user->last_notification_seen]);
    }

    /**
     * Show all standalone conversations for the current user - NO booking/bid dependencies.
     */
    public function index(Request $request)
    {
        $uid = (int) auth()->id();
        abort_unless($uid > 0, 401);

        $user = auth()->user();
        $isAdmin = $user && ($user->hasRole('admin') || $user->hasRole('demo_admin'));

        // Admin: list all users (excluding self), allow starting chat with anyone
        if ($isAdmin) {
            $q = \App\Models\User::where('id', '!=', $uid)->where('status', 1);
            $search = trim((string) $request->query('search', ''));
            if ($search !== '') {
                $q->where(function($qq) use ($search){
                    $qq->where('display_name', 'like', "%{$search}%")
                       ->orWhere('first_name', 'like', "%{$search}%")
                       ->orWhere('last_name', 'like', "%{$search}%")
                       ->orWhere('user_type', 'like', "%{$search}%");
                });
            }

            // Admin filters: user_type, country, state, city
            $filters = [
                'user_type' => $request->query('user_type'),
                'country_id' => $request->query('country_id'),
                'state_id' => $request->query('state_id'),
                'city_id' => $request->query('city_id'),
            ];

            $allowedTypes = ['user','provider','handyman'];
            if (!empty($filters['user_type']) && in_array($filters['user_type'], $allowedTypes, true)) {
                $q->where('user_type', $filters['user_type']);
            }
            if (!empty($filters['country_id'])) { $q->where('country_id', (int) $filters['country_id']); }
            if (!empty($filters['state_id'])) { $q->where('state_id', (int) $filters['state_id']); }
            if (!empty($filters['city_id'])) { $q->where('city_id', (int) $filters['city_id']); }

            $users = $q->select('id','display_name','first_name','last_name','user_type','country_id','state_id','city_id')
                ->orderBy('display_name')->limit(500)->get();

            $countries = \App\Models\Country::orderBy('name')->get(['id','name']);

            $items = [];
            foreach ($users as $u) {
                // All conversations between admin and this user (any type)
                $convIds = ChatConversation::where(function($cq) use ($uid, $u){
                        $cq->where('user_one_id', $uid)->where('user_two_id', $u->id);
                    })->orWhere(function($cq) use ($uid, $u){
                        $cq->where('user_one_id', $u->id)->where('user_two_id', $uid);
                    })
                    ->pluck('id');

                $last = null; $unread = 0; $lastAt = null; $snippet = '';
                if ($convIds->isNotEmpty()) {
                    $last = ChatMessage::whereIn('conversation_id', $convIds)->orderByDesc('id')->first();
                    $unread = ChatMessage::whereIn('conversation_id', $convIds)
                        ->whereNull('read_at')
                        ->where('sender_id', '!=', $uid)
                        ->count();
                }
                if ($last) {
                    $lastAt = $last->created_at?->toDateTimeString();
                    $snippet = $last->contains_pii ? __('messages.chat_policy_violation_hidden') : ($last->message ? mb_substr($last->message, 0, 80) : __('messages.attachment'));
                }

                $items[] = [
                    'conversation_id' => null,
                    'url' => route('chat.view.user', $u->id),
                    'title' =>  $u->user_type ?? __('messages.unknown'),
                    'other_name' => $u->display_name ?: trim(($u->first_name.' '.$u->last_name)),
                    'other_avatar' => getSingleMedia($u, 'profile_image', null),
                    'unread' => $unread,
                    'last_snippet' => $snippet,
                    'last_at' => $lastAt,
                ];
            }

            return view('chat.index', [
                'items' => $items,
                'countries' => $countries,
                'filters' => $filters,
            ]);
        }

        // Non-admin: show all conversations the user participates in
        $conversations = ChatConversation::where(function ($q) use ($uid) {
                $q->where('user_one_id', $uid)->orWhere('user_two_id', $uid);
            })
            ->with([
                'userOne:id,display_name,user_type',
                'userTwo:id,display_name,user_type',
            ])
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

            // Route to per-conversation view for booking/bid threads; user-to-user for standalone
            if ($c->booking_id || $c->post_job_bid_id) {
                $url = route('chat.messages', $c->id);
                $contextLabel = $c->booking_id
                    ? __('messages.booking') . ' #' . $c->booking_id
                    : __('messages.post_job') . ' #' . $c->post_job_bid_id;
                $title = ($other->user_type ?? __('messages.unknown')) . ' — ' . $contextLabel;
            } else {
                $url = route('chat.view.user', $otherId);
                $title = $other->user_type ?? __('messages.unknown');
            }

            $maskedSnippet = '';
            if ($last) {
                $maskedSnippet = $last->contains_pii ? __('messages.chat_policy_violation_hidden') : ($last->message ? mb_substr($last->message, 0, 80) : __('messages.attachment'));
            }
            $list[] = [
                'conversation_id' => $c->id,
                'url' => $url,
                'title' => $title,
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

        $recipientLocale = getRecipientLocale($recipient); // *** new: recipient locale ***
        $data = [
            'name' => $recipient->display_name ?? ($recipient->first_name ?? __('messages.user')),
            'types' => $piiTypes,
            'snippet' => $snippet,
            'date' => $message->created_at?->toDateTimeString(),
        ];

        Mail::to($email)->locale($recipientLocale)->send(new \App\Mail\ChatPiiWarningMail($data, $recipientLocale)); // *** new: locale-aware email ***

        return back()->with('status', __('messages.chat_warning_email_sent'));
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

