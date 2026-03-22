<?php

namespace App\Imports;

use App\Models\Product;
use App\Services\DigitalProductCodeService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DigitalProductCodesImport implements ToCollection, WithHeadingRow
{
    /** @var array{processed: int, skipped: int, failed: int, duplicates: int, errors: array<int, string>} */
    private array $summary = [
        'processed' => 0,
        'skipped' => 0,
        'failed' => 0,
        'duplicates' => 0,
        'errors' => [],
    ];

    /**
     * @param  int  $sellerId  0 = admin-uploaded, >0 = vendor-uploaded
     */
    public function __construct(
        private readonly DigitalProductCodeService $codeService,
        private readonly int $sellerId = 0,
    ) {}

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // row 1 is the heading

            $productId = (int) ($row['product_id'] ?? 0);
            $productName = trim((string) ($row['product_name'] ?? ''));
            // 'pin' is the user-facing column name for the digital code
            $pin = trim((string) ($row['pin'] ?? $row['digital_code'] ?? $row['digital_code_fill_this'] ?? ''));
            $serialNumber = trim((string) ($row['serial_number'] ?? '')) ?: null;
            $expiryRaw = trim((string) ($row['expiry_date'] ?? ''));

            // Skip completely blank rows
            if ($pin === '' && $productId === 0 && $productName === '') {
                continue;
            }

            // Skip the template example row (product_name contains "⚠ EXAMPLE")
            if (str_contains($productName, 'EXAMPLE')) {
                continue;
            }

            // PIN is required
            if ($pin === '') {
                $this->summary['skipped']++;

                continue;
            }

            // ── Resolve or create the product ───────────────────────────────
            $product = null;

            if ($productId > 0) {
                $product = $this->findProduct(id: $productId);
                if (! $product) {
                    $this->summary['failed']++;
                    $this->summary['errors'][] = "Row {$rowNumber}: Product ID {$productId} not found or does not belong to your account.";

                    continue;
                }
            } elseif ($productName !== '') {
                $product = $this->findProductByName($productName);

                if (! $product) {
                    // Attempt to create the product from the row data
                    $price = (float) ($row['price'] ?? 0);
                    $categoryId = (int) ($row['category_id'] ?? 0);

                    if ($price <= 0 || $categoryId <= 0) {
                        $this->summary['failed']++;
                        $this->summary['errors'][] = "Row {$rowNumber}: Product '{$productName}' not found. Provide a valid 'price' and 'category_id' to create it automatically.";

                        continue;
                    }

                    $product = $this->createProduct($productName, $price, $categoryId);

                    if (! $product) {
                        $this->summary['failed']++;
                        $this->summary['errors'][] = "Row {$rowNumber}: Could not create product '{$productName}'. Check price and category_id.";

                        continue;
                    }
                }
            } else {
                $this->summary['failed']++;
                $this->summary['errors'][] = "Row {$rowNumber}: Either product_id or product_name is required.";

                continue;
            }

            // ── Parse expiry date ────────────────────────────────────────────
            $expiryDate = $this->parseDate($expiryRaw);

            // ── Add code to pool ─────────────────────────────────────────────
            $result = $this->codeService->addToPool(
                productId: $product->id,
                plainCode: $pin,
                serialNumber: $serialNumber,
                expiryDate: $expiryDate,
            );

            if ($result === null) {
                $this->summary['duplicates']++;
                $this->summary['errors'][] = "Row {$rowNumber}: Skipped — duplicate PIN or serial number already exists in the system.";

                continue;
            }

            $this->summary['processed']++;
        }
    }

    /** @return array{processed: int, skipped: int, failed: int, errors: array<int, string>} */
    public function getSummary(): array
    {
        return $this->summary;
    }

    // ── Internals ────────────────────────────────────────────────────────────

    private function findProduct(int $id): ?Product
    {
        $query = Product::query()
            ->where('id', $id)
            ->where('product_type', 'digital');

        if ($this->sellerId > 0) {
            $query->where('user_id', $this->sellerId)->where('added_by', 'seller');
        }

        return $query->first();
    }

    private function findProductByName(string $name): ?Product
    {
        $query = Product::query()
            ->where('product_type', 'digital')
            ->whereRaw('LOWER(name) = ?', [strtolower($name)]);

        if ($this->sellerId > 0) {
            $query->where('user_id', $this->sellerId)->where('added_by', 'seller');
        }

        return $query->first();
    }

    private function createProduct(string $name, float $price, int $categoryId): ?Product
    {
        try {
            $slug = \Illuminate\Support\Str::slug($name, '-').'-'.\Illuminate\Support\Str::random(6);

            return Product::create([
                'name' => $name,
                'slug' => $slug,
                'unit_price' => $price,
                'purchase_price' => 0,
                'category_id' => $categoryId,
                'category_ids' => json_encode([$categoryId]),
                'user_id' => $this->sellerId > 0 ? $this->sellerId : null,
                'added_by' => $this->sellerId > 0 ? 'seller' : 'admin',
                'product_type' => 'digital',
                'digital_product_type' => 'ready_product',
                'status' => 0, // pending admin review for seller-created products
                'current_stock' => 0,
            ]);
        } catch (\Throwable) {
            return null;
        }
    }

    private function parseDate(string $raw): ?string
    {
        if ($raw === '') {
            return null;
        }

        try {
            // Handle Excel serial date numbers
            if (is_numeric($raw)) {
                return Carbon::createFromTimestamp(($raw - 25569) * 86400)->toDateString();
            }

            return Carbon::parse($raw)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }
}
