<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('admin-notifications', function ($user) {
    // Autorizar solo a usuarios con rol Administrador (Spatie roles)
    if (method_exists($user, 'hasRole')) {
        return $user->hasRole('Administrador');
    }
    // Fallback: permitir usuario ID 1
    return (int) $user->id === 1;
});
