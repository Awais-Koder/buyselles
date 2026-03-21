<?php

namespace App\Http\Controllers\Vendor\Product;

use App\Exports\DigitalProductCodeTemplateExport;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessDigitalCodeImportJob;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DigitalCodeImportController extends Controller
{
    public function index(): \Illuminate\Contracts\View\View
    {
        return view('vendor-views.digital-product-code.import');
    }

    public function downloadTemplate(): BinaryFileResponse
    {
        $sellerId = (int) auth('seller')->id();
        $export = new DigitalProductCodeTemplateExport(sellerId: $sellerId);

        return Excel::download($export, 'digital-code-template-'.now()->format('Y-m-d').'.xlsx');
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'excel_file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ]);

        $storedPath = $request->file('excel_file')->store('digital-code-imports', 'local');
        $absolutePath = storage_path('app/'.$storedPath);

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
}
