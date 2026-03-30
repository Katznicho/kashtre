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

    $sanitize = static function ($value): ?string {
        if ($value === null) {
            return null;
        }

        $value = (string) $value;
        $sanitized = @iconv('UTF-8', 'UTF-8//IGNORE', $value);

        return $sanitized === false ? $value : $sanitized;
    };

    return [
        'id'    => (int) $user->id,
        'uuid'  => $sanitize($user->uuid),
        'name'  => $sanitize($user->name),
        'photo' => $sanitize($user->profile_photo_url),
    ];
});
