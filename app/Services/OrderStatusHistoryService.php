<?php

namespace App\Services;

class OrderStatusHistoryService
{
    public function __construct() {}

    /**
     * @param  null  $cause
     */
    public function getOrderHistoryData(string|int $orderId, string|int $userId, string $userType, string $status, $cause = null): array
    {
        return [
            'order_id' => $orderId,
            'user_id' => $userId,
            'user_type' => $userType,
            'status' => $status,
            'cause' => $cause,
        ];
    }
}
