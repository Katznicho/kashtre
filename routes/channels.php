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

// Default user notification channel
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// P2P Calling — private user channel for call events
Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// PA (Public Announcement) — public channel, no auth needed (display stations subscribe without a user session)
// Channel name: pa-business.{businessId}
// Events: PaAnnouncementStarted, PaAnnouncementStopped, PaAudioChunk

// Presence channel — tracks online users per business
Broadcast::channel('presence-business.{businessId}', function ($user, $businessId) {
    if ((int) $user->business_id !== (int) $businessId) return false;
    return [
        'id'    => $user->id,
        'uuid'  => $user->uuid,
        'name'  => $user->name,
        'photo' => $user->profile_photo_url,
    ];
});
