<?php

namespace App\Imports;

use App\Models\Product;
use App\Services\DigitalProductCodeService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class DigitalProductCodesImport implements ToCollection, WithHeadingRow, WithValidation
{
    /** @var array{processed: int, skipped: int, failed: int, errors: array<int, string>} */
    private array $summary = [
        'processed' => 0,
        'skipped' => 0,
        'failed' => 0,
        'errors' => [],
    ];

    public function __construct(private readonly DigitalProductCodeService $codeService) {}

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 because row 1 is heading
            $productId = (int) ($row['product_id'] ?? 0);
            $digitalCode = trim((string) ($row['digital_code_fill_this'] ?? $row['digital_code'] ?? ''));

            if (empty($digitalCode)) {
                $this->summary['skipped']++;

                continue;
            }

            if ($productId <= 0) {
                $this->summary['failed']++;
                $this->summary['errors'][] = "Row {$rowNumber}: Invalid product ID.";

                continue;
            }

            $product = Product::query()
                ->where('id', $productId)
                ->where('product_type', 'digital')
                ->first();

            if (! $product) {
                $this->summary['failed']++;
                $this->summary['errors'][] = "Row {$rowNumber}: Product ID {$productId} not found or is not a digital product.";

                continue;
            }

            $this->codeService->addToPool($productId, $digitalCode);

            $this->summary['processed']++;
        }
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'numeric'],
        ];
    }

    /** @return array{processed: int, skipped: int, failed: int, errors: array<int, string>} */
    public function getSummary(): array
    {
        return $this->summary;
    }
}
