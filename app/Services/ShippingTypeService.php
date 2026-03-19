<?php

namespace App\Services;

class ShippingTypeService
{
    public function getShippingTypeDataForAdd(object $request, string|int $id): array
    {
        return [
            'seller_id' => $id,
            'shipping_type' => $request['shippingType'],
        ];
    }

    public function getShippingTypeDataForUpdate(object $request): array
    {
        return [
            'shipping_type' => $request['shippingType'],
        ];
    }
}
