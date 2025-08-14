<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('admin-notifications', function ($user) {
    // Autorizar solo a usuarios con rol admin (ajusta según tu sistema de roles)
    if (method_exists($user, 'hasRole')) {
        return $user->hasRole('admin');
    }
    // Fallback: permitir solo primer usuario (ID=1) si no hay roles
    return (int) $user->id === 1;
});
