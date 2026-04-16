<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DisputeStatus;
use App\Enums\DisputeUserType;
use App\Enums\ViewPaths\Admin\Dispute as DisputeViewPath;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\ResolveDisputeRequest;
use App\Models\Dispute;
use App\Models\DisputeStatusLog;
use App\Services\DisputeService;
use App\Services\EscrowService;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class DisputeArbitrationController extends BaseController
{
    public function __construct(
        private readonly DisputeService $disputeService,
        private readonly EscrowService $escrowService,
    ) {}

    public function index(?Request $request, ?string $type = null): View|Collection|LengthAwarePaginator|null|callable|RedirectResponse|JsonResponse
    {
        $status = $request->get('status', 'all');
        $priority = $request->get('priority');
        $search = $request->get('search');
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');

        $query = Dispute::with(['buyer', 'vendor', 'reason', 'order'])->latest();

        if ($status && $status !== 'all') {
            if ($status === 'resolved') {
                $query->whereIn('status', [DisputeStatus::RESOLVED_REFUND, DisputeStatus::RESOLVED_RELEASE]);
            } elseif ($status === 'closed') {
                $query->whereIn('status', [DisputeStatus::CLOSED, DisputeStatus::AUTO_CLOSED]);
            } else {
                $query->where('status', $status);
            }
        }

        if ($priority) {
            $query->where('priority', $priority);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('id', $search)
                    ->orWhereHas('order', fn($o) => $o->where('id', $search));
            });
        }

        if ($fromDate) {
            $query->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        $disputes = $query->paginate(15)->appends($request->query());

        $stats = [
            'total' => Dispute::count(),
            'open' => Dispute::where('status', DisputeStatus::OPEN)->count(),
            'under_review' => Dispute::where('status', DisputeStatus::UNDER_REVIEW)->count(),
            'resolved' => Dispute::whereIn('status', [DisputeStatus::RESOLVED_REFUND, DisputeStatus::RESOLVED_RELEASE])->count(),
            'vendor_response' => Dispute::where('status', DisputeStatus::VENDOR_RESPONSE)->count(),
        ];

        return view(DisputeViewPath::INDEX[VIEW], compact('disputes', 'stats', 'status', 'priority', 'search', 'fromDate', 'toDate'));
    }

    public function show(int $id): View
    {
        $dispute = Dispute::with([
            'buyer',
            'vendor',
            'reason',
            'order.orderDetails.product',
            'messages',
            'evidence',
            'statusLogs',
            'escrow',
            'resolvedBy',
        ])->findOrFail($id);

        return view(DisputeViewPath::DETAIL[VIEW], [
            'dispute' => $dispute,
            'escrow' => $dispute->escrow,
        ]);
    }

    public function underReview(int $id): RedirectResponse
    {
        $admin = auth('admin')->user();
        $dispute = Dispute::findOrFail($id);

        if (! in_array($dispute->status, [DisputeStatus::OPEN, DisputeStatus::VENDOR_RESPONSE])) {
            ToastMagic::error(translate('dispute_is_not_in_a_reviewable_state'));

            return back();
        }

        $this->disputeService->escalateToAdmin($dispute, (int) $admin->id, DisputeUserType::ADMIN);

        ToastMagic::success(translate('dispute_marked_as_under_review'));

        return back();
    }

    public function resolveRefund(ResolveDisputeRequest $request, int $id): RedirectResponse
    {
        $admin = auth('admin')->user();
        $dispute = Dispute::findOrFail($id);

        if (! in_array($dispute->status, [DisputeStatus::OPEN, DisputeStatus::VENDOR_RESPONSE, DisputeStatus::UNDER_REVIEW])) {
            ToastMagic::error(translate('dispute_cannot_be_resolved_in_current_status'));

            return back();
        }

        $this->disputeService->resolveRefund($dispute, (int) $admin->id, $request->decision, $request->admin_note);

        ToastMagic::success(translate('dispute_resolved_refund_issued_to_buyer'));

        return redirect()->route('admin.dispute.index');
    }

    public function resolveRelease(ResolveDisputeRequest $request, int $id): RedirectResponse
    {
        $admin = auth('admin')->user();
        $dispute = Dispute::findOrFail($id);

        if (! in_array($dispute->status, [DisputeStatus::OPEN, DisputeStatus::VENDOR_RESPONSE, DisputeStatus::UNDER_REVIEW])) {
            ToastMagic::error(translate('dispute_cannot_be_resolved_in_current_status'));

            return back();
        }

        $this->disputeService->resolveRelease($dispute, (int) $admin->id, $request->decision, $request->admin_note);

        ToastMagic::success(translate('dispute_resolved_funds_released_to_vendor'));

        return redirect()->route('admin.dispute.index');
    }

    public function close(Request $request, int $id): RedirectResponse
    {
        $admin = auth('admin')->user();
        $dispute = Dispute::findOrFail($id);

        if (in_array($dispute->status, [DisputeStatus::RESOLVED_REFUND, DisputeStatus::RESOLVED_RELEASE, DisputeStatus::CLOSED, DisputeStatus::AUTO_CLOSED])) {
            ToastMagic::error(translate('dispute_is_already_closed'));

            return back();
        }

        $oldStatus = $dispute->status;

        $dispute->update(['status' => DisputeStatus::CLOSED]);

        DisputeStatusLog::create([
            'dispute_id' => $dispute->id,
            'changed_by' => $admin->id,
            'changed_by_type' => DisputeUserType::ADMIN,
            'from_status' => $oldStatus,
            'to_status' => DisputeStatus::CLOSED,
            'note' => $request->get('note', 'Closed by admin'),
            'created_at' => now(),
        ]);

        $dispute->order()->update(['dispute_status' => 'resolved']);

        ToastMagic::success(translate('dispute_closed_successfully'));

        return redirect()->route('admin.dispute.index');
    }
}
