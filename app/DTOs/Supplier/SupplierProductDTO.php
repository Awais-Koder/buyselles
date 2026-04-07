<?php

namespace App\DTOs\Supplier;

/**
 * Normalized product representation from any supplier.
 */
readonly class SupplierProductDTO
{
    public function __construct(
        public string $supplierProductId,
        public string $name,
        public ?string $description = null,
        public ?string $category = null,
        public ?string $imageUrl = null,
        public float $price = 0,
        public string $currency = 'USD',
        public int $stockAvailable = 0,
        public ?string $region = null,
        public array $rawData = [],
    ) {}

    /**
     * Create from an associative array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            supplierProductId: (string) ($data['supplier_product_id'] ?? $data['id'] ?? ''),
            name: (string) ($data['name'] ?? ''),
            description: $data['description'] ?? null,
            category: $data['category'] ?? null,
            imageUrl: $data['image_url'] ?? $data['image'] ?? null,
            price: (float) ($data['price'] ?? 0),
            currency: (string) ($data['currency'] ?? 'USD'),
            stockAvailable: (int) ($data['stock'] ?? $data['stock_available'] ?? 0),
            region: $data['region'] ?? null,
            rawData: $data,
        );
    }
}
