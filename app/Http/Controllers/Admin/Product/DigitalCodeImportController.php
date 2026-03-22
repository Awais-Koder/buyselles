<?php

namespace App\Http\Controllers\Admin\Product;

use App\Exports\DigitalProductCodeTemplateExport;
use App\Exports\ProductCodeTemplateExport;
use App\Http\Controllers\BaseController;
use App\Jobs\ProcessDigitalCodeImportJob;
use App\Models\DigitalProductCode;
use App\Models\Product;
use App\Services\DigitalProductCodeService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DigitalCodeImportController extends BaseController
{
    // ── General (multi-product) import ───────────────────────────────────────

    public function index(?Request $request = null, ?string $type = null): View|\Illuminate\Database\Eloquent\Collection|LengthAwarePaginator|null|callable|RedirectResponse|JsonResponse
    {
        return view('admin-views.digital-product-code.import');
    }

    public function downloadTemplate(Request $request): BinaryFileResponse
    {
        $export = new DigitalProductCodeTemplateExport(sellerId: null);

        return Excel::download($export, 'digital-code-template-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'excel_file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ]);

        $storedPath = $request->file('excel_file')->store('digital-code-imports', 'local');
        $absolutePath = storage_path('app/' . $storedPath);

        $importedBy = auth('admin')->user()->name ?? 'Admin';
        $adminId = (int) auth('admin')->id();

        ProcessDigitalCodeImportJob::dispatch($absolutePath, $importedBy, $adminId)
            ->onQueue('default');

        return redirect()
            ->route('admin.products.digital-code-import.index')
            ->with('success', translate('Your_file_has_been_queued_for_processing._You_will_be_notified_by_email_and_dashboard_notification_once_it_is_complete.'));
    }

    // ── Per-product code management ──────────────────────────────────────────

    /**
     * List all codes for a specific digital product.
     */
    public function productCodes(int $productId): View|RedirectResponse
    {
        $product = Product::where('id', $productId)
            ->where('product_type', 'digital')
            ->firstOrFail();

        $codesQuery = DigitalProductCode::where('product_id', $productId)
            ->orderByRaw("FIELD(status,'available','reserved','expired','sold','failed')")
            ->orderBy('expiry_date');

        $stats = [
            'available' => DigitalProductCode::where('product_id', $productId)->available()->count(),
            'reserved' => DigitalProductCode::where('product_id', $productId)->where('status', 'reserved')->count(),
            'sold' => DigitalProductCode::where('product_id', $productId)->where('status', 'sold')->count(),
            'expired' => DigitalProductCode::where('product_id', $productId)->where('status', 'expired')->count(),
            'total' => DigitalProductCode::where('product_id', $productId)->count(),
        ];

        // Codes expiring within 7 days (still available)
        $expiringCount = DigitalProductCode::where('product_id', $productId)
            ->where('status', 'available')
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '>=', now()->toDateString())
            ->whereDate('expiry_date', '<=', now()->addDays(7)->toDateString())
            ->count();

        $codes = $codesQuery->paginate(50)->withQueryString();

        return view('admin-views.digital-product-code.product-codes', compact(
            'product',
            'codes',
            'stats',
            'expiringCount'
        ));
    }

    /**
     * Show the per-product code upload form.
     */
    public function productImportForm(int $productId): View|RedirectResponse
    {
        $product = Product::where('id', $productId)
            ->where('product_type', 'digital')
            ->firstOrFail();

        return view('admin-views.digital-product-code.product-import', compact('product'));
    }

    /**
     * Download the simple 3-column template for a specific product.
     */
    public function productTemplate(int $productId): BinaryFileResponse
    {
        $product = Product::where('id', $productId)
            ->where('product_type', 'digital')
            ->firstOrFail();

        $export = new ProductCodeTemplateExport(productName: $product->name);
        $filename = 'codes-' . str()->slug($product->name) . '-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download($export, $filename);
    }

    /**
     * Handle synchronous per-product code upload and return an immediate summary.
     */
    public function productImportUpload(Request $request, int $productId, DigitalProductCodeService $service): RedirectResponse
    {
        $request->validate([
            'excel_file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ]);

        $product = Product::where('id', $productId)
            ->where('product_type', 'digital')
            ->firstOrFail();

        $rows = Excel::toCollection(null, $request->file('excel_file'))->first();

        if ($rows === null || $rows->isEmpty()) {
            return redirect()
                ->route('admin.products.digital-code-import.product-codes', $productId)
                ->with('import_error', translate('The_uploaded_file_was_empty_or_could_not_be_read.'));
        }

        $processed = 0;
        $duplicates = 0;
        $skipped = 0;
        $errors = [];

        // Detect header row — first row with 'pin' or 'digital_code' in it
        $headerRow = $rows->first();
        $hasHeader = $headerRow->filter(fn($v) => in_array(
            strtolower((string) $v),
            ['pin', 'digital_code', 'serial_number', 'expiry_date'],
            true
        ))->isNotEmpty();

        $dataRows = $hasHeader ? $rows->skip(1) : $rows;

        // Build column index map from header
        $colPin = 0;
        $colSerial = 1;
        $colExpiry = 2;

        if ($hasHeader) {
            foreach ($headerRow as $idx => $hVal) {
                $h = strtolower(trim((string) $hVal));
                if (in_array($h, ['pin', 'digital_code'])) {
                    $colPin = $idx;
                } elseif ($h === 'serial_number') {
                    $colSerial = $idx;
                } elseif ($h === 'expiry_date') {
                    $colExpiry = $idx;
                }
            }
        }

        foreach ($dataRows as $rowIndex => $row) {
            $rowNum = $rowIndex + ($hasHeader ? 3 : 2);
            $pin = trim((string) ($row[$colPin] ?? ''));
            $serial = trim((string) ($row[$colSerial] ?? '')) ?: null;
            $expiryRaw = trim((string) ($row[$colExpiry] ?? ''));

            // Skip blank and example rows
            if ($pin === '' || str_contains(strtoupper($pin), 'EXAMPLE')) {
                $skipped++;
                continue;
            }

            $expiry = $this->parseDate($expiryRaw);

            $result = $service->addToPool(
                productId: $product->id,
                plainCode: $pin,
                serialNumber: $serial,
                expiryDate: $expiry,
            );

            if ($result === null) {
                $duplicates++;
            } else {
                $processed++;
            }
        }

        $summary = compact('processed', 'duplicates', 'skipped', 'errors');

        return redirect()
            ->route('admin.products.digital-code-import.product-codes', $productId)
            ->with('import_summary', $summary);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function parseDate(string $raw): ?string
    {
        if ($raw === '') {
            return null;
        }

        try {
            if (is_numeric($raw)) {
                return Carbon::createFromTimestamp(((float) $raw - 25569) * 86400)->toDateString();
            }

            return Carbon::parse($raw)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }
}
