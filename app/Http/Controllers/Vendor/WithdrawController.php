<?php

namespace App\Http\Controllers\Vendor;

use App\Contracts\Repositories\VendorRepositoryInterface;
use App\Contracts\Repositories\VendorWalletRepositoryInterface;
use App\Contracts\Repositories\VendorWithdrawMethodInfoRepositoryInterface;
use App\Contracts\Repositories\WithdrawalMethodRepositoryInterface;
use App\Contracts\Repositories\WithdrawRequestRepositoryInterface;
use App\Exports\VendorWithdrawRequest;
use App\Http\Controllers\BaseController;
use App\Services\VendorWalletService;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class WithdrawController extends BaseController
{
    public function __construct(
        private readonly WithdrawRequestRepositoryInterface $withdrawRequestRepo,
        private readonly VendorWalletRepositoryInterface $vendorWalletRepo,
        private readonly VendorWalletService $vendorWalletService,
        private readonly VendorRepositoryInterface $vendorRepo,
        private readonly VendorWithdrawMethodInfoRepositoryInterface $vendorWithdrawMethodInfoRepo,
        private readonly WithdrawalMethodRepositoryInterface $withdrawalMethodRepo,
    ) {}

    public function index(?Request $request, ?string $type = null): View|Collection|LengthAwarePaginator|null|callable
    {
        if ($request->has('status')) {
            $filters['status'] = $request['status'];
        }
        $vendorId = auth('seller')->id();
        $vendorWallet = $this->vendorWalletRepo->getFirstWhere(params: ['seller_id' => $vendorId]);

        $withdrawRequests = $this->withdrawRequestRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            searchValue: $request['search'] ?? null,
            filters: array_merge(['vendorId' => $vendorId], $filters ?? []),
            relations: ['seller'],
            dataLimit: getWebConfig('pagination_limit')
        );

        $withdrawalMethods = $this->withdrawalMethodRepo->getListWhere(filters: ['is_active' => 1], dataLimit: 'all');

        updateSetupGuideCacheKey(key: 'withdraw_setup', panel: 'vendor');

        return view('vendor-views.withdraw.index', [
            'vendorWallet' => $vendorWallet,
            'withdrawRequests' => $withdrawRequests,
            'withdrawalMethods' => $withdrawalMethods,
        ]);
    }

    public function renderInfosView(Request $request): JsonResponse
    {
        $vendorId = auth('seller')->id();
        $vendorWallet = $this->vendorWalletRepo->getFirstWhere(params: ['seller_id' => $vendorId]);

        $withdrawalMethod = $this->withdrawalMethodRepo->getFirstWhere(params: ['id' => $request['method_id']]);

        return response()->json([
            'htmlView' => view('vendor-views.withdraw._withdraw-request-method-filed', [
                'vendorWallet' => $vendorWallet,
                'withdrawalMethod' => $withdrawalMethod,
            ])->render(),
        ]);
    }

    /**
     * @throws Throwable
     */
    public function getListByStatus(Request $request): JsonResponse
    {
        $vendorId = auth('seller')->id();
        $withdrawRequests = $this->withdrawRequestRepo->getListWhere(
            filters: [
                'vendorId' => $vendorId,
                'status' => $request['status'],
            ],
            relations: ['seller'],
            dataLimit: getWebConfig('pagination_limit')
        );

        return response()->json([
            'view' => view('vendor-views.withdraw._table', compact('withdrawRequests'))->render(),
            'count' => $withdrawRequests->count(),
        ], 200);
    }

    /**
     * @throws Exception
     */
    public function closeWithdrawRequest(string|int $id): RedirectResponse
    {
        $withdrawRequest = $this->withdrawRequestRepo->getFirstWhere(params: ['id' => $id]);
        $wallet = $this->vendorWalletRepo->getFirstWhere(params: ['seller_id' => auth('seller')->id()]);
        if ($withdrawRequest['approved'] == 0) {
            $totalEarning = $wallet['total_earning'] + currencyConverter(amount: $withdrawRequest['amount']);
            $pendingWithdraw = $wallet['pending_withdraw'] - currencyConverter(amount: $withdrawRequest['amount']);
            $this->vendorWalletRepo->update(
                id: $wallet['id'],
                data: $this->vendorWalletService->getVendorWalletData(
                    totalEarning: $totalEarning,
                    pendingWithdraw: $pendingWithdraw
                )
            );
            $this->withdrawRequestRepo->delete(['id' => $withdrawRequest['id']]);
            ToastMagic::success(message: translate('withdraw_request_closed') . '!');
        } else {
            ToastMagic::error(message: translate('invalid_withdraw_request'));
        }

        return redirect()->back();
    }

    public function exportList(Request $request): BinaryFileResponse
    {
        $vendorId = auth('seller')->id();
        $vendor = $this->vendorRepo->getFirstWhere(params: ['id' => $vendorId]);
        $withdrawRequests = $this->withdrawRequestRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            searchValue: $request['searchValue'],
            filters: [
                'vendorId' => $vendorId,
                'status' => $request['status'],
            ],
            relations: ['seller'],
            dataLimit: 'all'
        );
        $pendingRequest = $withdrawRequests->where('approved', 0)->count();
        $approvedRequest = $withdrawRequests->where('approved', 1)->count();
        $deniedRequest = $withdrawRequests->where('approved', 2)->count();
        $data = [
            'data-from' => 'vendor',
            'vendor' => $vendor,
            'withdraw_request' => $withdrawRequests,
            'filter' => $request['status'],
            'searchValue' => $request['searchValue'],
            'pending' => $pendingRequest,
            'approved' => $approvedRequest,
            'denied' => $deniedRequest,
        ];

        return Excel::download(export: new VendorWithdrawRequest($data), fileName: 'Vendor-Withdraw-Request.xlsx');
    }
}
