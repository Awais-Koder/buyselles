<?php

namespace App\Http\Controllers\Admin\Supplier;

use App\Http\Controllers\BaseController;
use App\Models\SupplierApi;
use App\Models\SupplierApiLog;
use App\Models\SupplierOrder;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class SupplierLogController extends BaseController
{
    public function index(?Request $request, ?string $type = null): View|Collection|LengthAwarePaginator|null|callable|RedirectResponse|JsonResponse
    {
        return $this->apiLogs($request);
    }

    /**
     * Display API logs with filters.
     */
    public function apiLogs(Request $request): View
    {
        $supplierId = $request->get('supplier_id');
        $action = $request->get('action');
        $status = $request->get('status');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $logs = SupplierApiLog::query()
            ->with('supplierApi:id,name')
            ->when($supplierId, fn ($q) => $q->where('supplier_api_id', $supplierId))
            ->when($action, fn ($q) => $q->where('action', $action))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('created_at', '<=', $dateTo))
            ->orderByDesc('created_at')
            ->paginate(getWebConfig(name: 'pagination_limit'));

        $suppliers = SupplierApi::orderBy('name')->get(['id', 'name']);

        return view('admin-views.supplier.api-logs', compact(
            'logs',
            'suppliers',
            'supplierId',
            'action',
            'status',
            'dateFrom',
            'dateTo',
        ));
    }

    /**
     * Show a single log entry detail (AJAX modal).
     */
    public function logDetail(int $id): View
    {
        $log = SupplierApiLog::with('supplierApi:id,name')->findOrFail($id);

        return view('admin-views.supplier.partials._log-detail-modal', compact('log'));
    }

    /**
     * Display supplier orders (purchases from suppliers).
     */
    public function supplierOrders(Request $request): View
    {
        $supplierId = $request->get('supplier_id');
        $status = $request->get('status');

        $orders = SupplierOrder::query()
            ->with(['supplierApi:id,name', 'productMapping.product:id,name', 'order:id'])
            ->when($supplierId, fn ($q) => $q->where('supplier_api_id', $supplierId))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(getWebConfig(name: 'pagination_limit'));

        $suppliers = SupplierApi::orderBy('name')->get(['id', 'name']);

        return view('admin-views.supplier.supplier-orders', compact('orders', 'suppliers', 'supplierId', 'status'));
    }
}
