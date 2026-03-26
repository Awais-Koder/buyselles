<?php

namespace App\Utils;

use App\Models\BusinessSetting;
use App\Models\Chatting;
use App\Models\Order;

class VendorHeaderManager
{
    /**
     * Get the count of pending orders for a seller.
     */
    public static function getPendingOrderCount(int $sellerId): int
    {
        try {
            return Order::where([
                'seller_is' => 'seller',
                'seller_id' => $sellerId,
                'order_status' => 'pending',
            ])->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    /**
     * Get the count of unread messages (seen_by_seller = 0) for a seller.
     */
    public static function getUnreadMessageCount(int $sellerId): int
    {
        try {
            return Chatting::where([
                'seen_by_seller' => 0,
                'seller_id' => $sellerId,
            ])->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    /**
     * Get the count of seen messages (seen_by_seller = 1) for a seller.
     * Used in the website_info mobile nav section.
     */
    public static function getSeenMessageCount(int $sellerId): int
    {
        try {
            return Chatting::where([
                'seen_by_seller' => 1,
                'seller_id' => $sellerId,
            ])->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    /**
     * Get the count of unread customer messages (seen_by_seller = 0) for a seller.
     */
    public static function getUnreadMessageCustomerCount(int $sellerId): int
    {
        try {
            return Chatting::where([
                'seen_by_seller' => 0,
                'seller_id' => $sellerId,
            ])->whereNotNull('user_id')->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    /**
     * Get the count of unread delivery-man messages (seen_by_seller = 0) for a seller.
     */
    public static function getUnreadMessageDeliveryCount(int $sellerId): int
    {
        try {
            return Chatting::where([
                'seen_by_seller' => 0,
                'seller_id' => $sellerId,
            ])->whereNotNull('delivery_man_id')->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    /**
     * The active language setting.
     */
    public static function getLanguageSetting(): ?BusinessSetting
    {
        try {
            return BusinessSetting::where('type', 'language')->first();
        } catch (\Throwable) {
            return null;
        }
    }
}
