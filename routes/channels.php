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

Broadcast::channel('App.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Admin inbox channel — authenticated via admin guard
Broadcast::channel('admin.chat', function () {
    return auth('admin')->check();
}, ['guards' => ['admin']]);

// Vendor inbox channel — authenticated via seller guard
Broadcast::channel('seller.{sellerId}.chat', function ($seller, int $sellerId) {
    return (int) $seller->id === $sellerId;
}, ['guards' => ['seller']]);

// Admin order status channel — authenticated via admin guard
Broadcast::channel('admin.orders', function () {
    return auth('admin')->check();
}, ['guards' => ['admin']]);

// Vendor order status channel — authenticated via seller guard
Broadcast::channel('seller.{sellerId}.orders', function ($seller, int $sellerId) {
    return (int) $seller->id === $sellerId;
}, ['guards' => ['seller']]);

// Customer order status channel — authenticated via customer (web) guard
Broadcast::channel('customer.{customerId}.orders', function ($user, int $customerId) {
    return (int) $user->id === $customerId;
});
