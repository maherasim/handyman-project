<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\ChatConversation;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{id}', function ($user, $id) {
    \Log::info('[BroadcastAuth] chat.' . $id . ' — user_id=' . $user->id);
    $conversation = ChatConversation::find((int) $id);
    if (!$conversation) {
        \Log::warning('[BroadcastAuth] chat.' . $id . ' — conversation NOT FOUND');
        return false;
    }
    $allowed = $conversation->includesUser((int) $user->id);
    \Log::info('[BroadcastAuth] chat.' . $id . ' — allowed=' . ($allowed ? 'true' : 'false'));
    return $allowed;
});
