<?php

namespace App\Http\Controllers\Admin\Product;

use App\Exports\DigitalProductCodeTemplateExport;
use App\Http\Controllers\BaseController;
use App\Jobs\ProcessDigitalCodeImportJob;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DigitalCodeImportController extends BaseController
{
    public function index(?Request $request = null, ?string $type = null): View|\Illuminate\Database\Eloquent\Collection|LengthAwarePaginator|null|callable|RedirectResponse|JsonResponse
    {
        return view('admin-views.digital-product-code.import');
    }

    public function downloadTemplate(Request $request): BinaryFileResponse
    {
        $export = new DigitalProductCodeTemplateExport(sellerId: null);

        return Excel::download($export, 'digital-code-template-'.now()->format('Y-m-d').'.xlsx');
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'excel_file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ]);

        // Store the file temporarily so the background job can read it
        $storedPath = $request->file('excel_file')->store('digital-code-imports', 'local');
        $absolutePath = storage_path('app/'.$storedPath);

        $importedBy = auth('admin')->user()->name ?? 'Admin';
        $adminId = (int) auth('admin')->id();

        ProcessDigitalCodeImportJob::dispatch($absolutePath, $importedBy, $adminId)
            ->onQueue('default');

        return redirect()
            ->route('admin.products.digital-code-import.index')
            ->with('success', translate('Your_file_has_been_queued_for_processing._You_will_be_notified_by_email_and_dashboard_notification_once_it_is_complete.'));
    }
}
