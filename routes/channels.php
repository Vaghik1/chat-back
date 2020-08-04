<?php

use Illuminate\Support\Facades\Broadcast;

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

Broadcast::channel('App.User.{id}', function ($user, $id) {
    return $user->id == $id;
});

Broadcast::channel('Chat', function ($user) {
    return Auth::check();
});

Broadcast::channel('App.UserTyping.{recipient_id}', function ($user, $recipient_id) {
    return true;
});