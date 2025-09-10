<?php

use Illuminate\Support\Facades\Broadcast;

// Private channel for user notifications
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Public channel for poll updates - anyone can listen to poll results
Broadcast::channel('poll.{pollId}', function ($user, $pollId) {
    return true;
});
