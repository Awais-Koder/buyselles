<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Enums\EscrowStatus;
use App\Enums\ViewPaths\Admin\Dispute as DisputeViewPath;
use App\Http\Controllers\BaseController;
use App\Models\Escrow;
use App\Services\EscrowService;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class EscrowController extends BaseController
{
    public function __construct(
        private readonly EscrowService $escrowService,
        private readonly BusinessSettingRepositoryInterface $businessSettingRepo,
    ) {}

    public function index(?Request $request, ?string $type = null): View|Collection|LengthAwarePaginator|null|callable|RedirectResponse|JsonResponse
    {
        $status = $request->get('status', 'all');
        $search = $request->get('search');

        $query = Escrow::with(['order', 'seller', 'buyer', 'dispute'])->latest();

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('id', $search)
                    ->orWhere('order_id', $search);
            });
        }

        $escrows = $query->paginate(15)->appends($request->query());

        $stats = [
            'held' => Escrow::where('status', EscrowStatus::HELD)->count(),
            'released' => Escrow::where('status', EscrowStatus::RELEASED)->count(),
            'disputed' => Escrow::where('status', EscrowStatus::DISPUTED)->count(),
            'refunded' => Escrow::where('status', EscrowStatus::REFUNDED)->count(),
            'total_held_amount' => Escrow::where('status', EscrowStatus::HELD)->sum('amount'),
        ];

        return view(DisputeViewPath::ESCROW_INDEX[VIEW], compact('escrows', 'stats', 'status', 'search'));
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $request->validate([
            'escrow_auto_release_hours' => 'required|integer|min:1|max:720',
        ]);

        $this->businessSettingRepo->updateOrInsert(type: 'escrow_protection_status', value: $request->get('escrow_protection_status', 0));
        $this->businessSettingRepo->updateOrInsert(type: 'escrow_auto_release_hours', value: $request->get('escrow_auto_release_hours', 48));

        clearWebConfigCacheKeys();

        ToastMagic::success(translate('escrow_settings_updated_successfully'));

        return back();
    }

    public function show(int $id): View
    {
        $escrow = Escrow::with(['order.orderDetails.product', 'seller', 'buyer', 'dispute'])
            ->findOrFail($id);

        return view(DisputeViewPath::ESCROW_DETAIL[VIEW], compact('escrow'));
    }

    public function manualRelease(int $id): RedirectResponse
    {
        $escrow = Escrow::findOrFail($id);

        if ($escrow->status !== EscrowStatus::HELD) {
            ToastMagic::error(translate('escrow_is_not_in_held_status'));

            return back();
        }

        if ($escrow->dispute_id) {
            ToastMagic::error(translate('cannot_release_escrow_with_active_dispute'));

            return back();
        }

        $this->escrowService->releaseEscrow($escrow, 'admin_manual');

        ToastMagic::success(translate('escrow_released_successfully'));

        return redirect()->route('admin.escrow.index');
    }
}
