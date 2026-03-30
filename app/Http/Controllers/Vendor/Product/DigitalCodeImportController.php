<?php

namespace App\Http\Controllers\Vendor\Product;

use App\Exports\DigitalProductCodeTemplateExport;
use App\Exports\ProductCodeTemplateExport;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessDigitalCodeImportJob;
use App\Models\DigitalProductCode;
use App\Models\Product;
use App\Services\DigitalProductCodeService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DigitalCodeImportController extends Controller
{
    public function index(): View
    {
        return view('vendor-views.digital-product-code.import');
    }

    public function downloadTemplate(): BinaryFileResponse
    {
        $sellerId = (int) auth('seller')->id();
        $export = new DigitalProductCodeTemplateExport(sellerId: $sellerId);

        return Excel::download($export, 'digital-code-template-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'excel_file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ]);

        $storedPath = $request->file('excel_file')->store('digital-code-imports', 'local');
        $absolutePath = storage_path('app/' . $storedPath);

        /** @var \App\Models\Seller $seller */
        $seller = auth('seller')->user();
        $importedBy = $seller->name ?? 'Vendor';
        $sellerId = (int) $seller->id;

        ProcessDigitalCodeImportJob::dispatch($absolutePath, $importedBy, 0, $sellerId)
            ->onQueue('default');

        return redirect()
            ->route('vendor.products.digital-code-import.index')
            ->with('success', translate('Your_file_has_been_queued_for_processing._You_will_be_notified_by_email_once_it_is_complete.'));
    }

    public function productCodes(int $productId): View
    {
        $sellerId = (int) auth('seller')->id();

        $product = Product::query()
            ->where('product_type', 'digital')
            ->where('added_by', 'seller')
            ->where('user_id', $sellerId)
            ->findOrFail($productId);

        $codes = DigitalProductCode::query()
            ->where('product_id', $productId)
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        $stats = [
            'available' => DigitalProductCode::where('product_id', $productId)->where('status', 'available')->where('is_active', true)->count(),
            'inactive' => DigitalProductCode::where('product_id', $productId)->where('is_active', false)->count(),
            'reserved' => DigitalProductCode::where('product_id', $productId)->where('status', 'reserved')->count(),
            'sold' => DigitalProductCode::where('product_id', $productId)->where('status', 'sold')->count(),
            'expired' => DigitalProductCode::where('product_id', $productId)->where('status', 'expired')->count(),
            'total' => DigitalProductCode::where('product_id', $productId)->count(),
        ];

        $expiringCount = DigitalProductCode::where('product_id', $productId)
            ->where('status', 'available')
            ->where('expiry_date', '<=', now()->addDays(7))
            ->where('expiry_date', '>', now())
            ->count();

        // Vendors can always view their own codes
        $canViewPin = true;

        return view('vendor-views.digital-product-code.product-codes', compact('product', 'codes', 'stats', 'expiringCount', 'canViewPin'));
    }

    public function productImportForm(int $productId): View
    {
        $sellerId = (int) auth('seller')->id();

        $product = Product::query()
            ->where('product_type', 'digital')
            ->where('added_by', 'seller')
            ->where('user_id', $sellerId)
            ->findOrFail($productId);

        return view('vendor-views.digital-product-code.product-import', compact('product'));
    }

    public function productTemplate(int $productId): BinaryFileResponse
    {
        $sellerId = (int) auth('seller')->id();

        $product = Product::query()
            ->where('product_type', 'digital')
            ->where('added_by', 'seller')
            ->where('user_id', $sellerId)
            ->findOrFail($productId);

        $export = new ProductCodeTemplateExport(productName: $product->name);

        return Excel::download($export, 'codes-template-' . Str::slug($product->name) . '-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function productImportUpload(Request $request, int $productId, DigitalProductCodeService $service): RedirectResponse
    {
        $request->validate([
            'excel_file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ]);

        $sellerId = (int) auth('seller')->id();

        $product = Product::query()
            ->where('product_type', 'digital')
            ->where('added_by', 'seller')
            ->where('user_id', $sellerId)
            ->findOrFail($productId);

        $spreadsheet = IOFactory::load($request->file('excel_file')->getPathname());
        $rows = $spreadsheet->getActiveSheet()->toArray();

        array_shift($rows); // remove header

        $processed = 0;
        $duplicates = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $pin = trim((string) ($row[0] ?? ''));
            $serial = trim((string) ($row[1] ?? ''));
            $expiryRaw = trim((string) ($row[2] ?? ''));

            if ($pin === '' || strtolower($pin) === 'pin') {
                $skipped++;

                continue;
            }

            $expiry = null;
            if ($expiryRaw !== '') {
                try {
                    $expiry = Carbon::parse($expiryRaw)->startOfDay();
                } catch (\Throwable $e) {
                    $expiry = null;
                }
            }

            $result = $service->addToPool(
                productId: $productId,
                plainCode: $pin,
                serialNumber: $serial ?: null,
                expiryDate: $expiry?->toDateString(),
            );

            if ($result) {
                $processed++;
            } else {
                $duplicates++;
            }
        }

        return redirect()
            ->route('vendor.products.digital-code-import.product-codes', $productId)
            ->with('import_summary', [
                'processed' => $processed,
                'duplicates' => $duplicates,
                'skipped' => $skipped,
            ]);
    }

    /**
     * Toggle the active/inactive status of a digital code.
     * Only the owning vendor can toggle their own codes.
     */
    public function toggleCodeStatus(int $id, DigitalProductCodeService $service): JsonResponse
    {
        $sellerId = (int) auth('seller')->id();

        try {
            $code = $service->toggleActive($id, $sellerId);

            return response()->json([
                'success' => true,
                'is_active' => $code->is_active,
                'message' => $code->is_active
                    ? translate('Code_activated_successfully')
                    : translate('Code_deactivated_successfully'),
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Decrypt and return the plain-text PIN for a single code record.
     * Only the owning vendor can decrypt their own codes.
     */
    public function decryptCode(int $id): JsonResponse
    {
        $sellerId = (int) auth('seller')->id();

        $code = DigitalProductCode::where('seller_id', $sellerId)->findOrFail($id);

        try {
            $plain = $code->decryptCode();
        } catch (\Exception) {
            return response()->json(['error' => translate('decryption_failed_code_may_be_corrupted')], 500);
        }

        return response()->json([
            'id' => $code->id,
            'pin' => $plain,
            'serial' => $code->serial_number,
        ]);
    }

    /**
     * Delete a digital code from the pool.
     * Only the owning vendor can delete their own codes.
     */
    public function deleteCode(int $id, DigitalProductCodeService $service): JsonResponse
    {
        $sellerId = (int) auth('seller')->id();

        try {
            $service->deleteCode($id, $sellerId);

            return response()->json([
                'success' => true,
                'message' => translate('Code_deleted_successfully'),
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
