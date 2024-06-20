<?php

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

use App\Broadcasting\GrainChainChannel;

Broadcast::channel( 'App.Users.{id}', function( $user, $id ){
    return (int) $user->id == (int) $id;
});

Broadcast::channel( 'user.{id}', function() {
    return true;
});

// Broadcast::channel( env( 'CHANNEL'), GrainChainChannel::class );
