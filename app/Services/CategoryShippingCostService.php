<?php

namespace App\Services;

class CategoryShippingCostService
{
    public function getAddCategoryWiseShippingCostData(string $addedBy, int|string $id): array
    {
        return [
            'seller_id' => $addedBy === 'seller' ? auth('seller')->id() : 0,
            'category_id' => $id,
            'cost' => 0,
        ];
    }

    public function getUpdateCategoryWiseShippingData(int $key, int|string $id, object $request): array
    {
        return [
            'cost' => currencyConverter($request['cost'][$key], 'usd'),
            'multiply_qty' => isset($request['multiplyQTY']) ? (in_array($id, $request['multiplyQTY']) ? 1 : 0) : 0,
        ];
    }
}
