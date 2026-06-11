<?php

use App\Models\Central\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('central.tenants', function (User $user): bool {
    return $user->is_active && $user->can('tenants.view');
});
