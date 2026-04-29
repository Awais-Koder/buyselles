<?php

namespace App\Http\Controllers\Web;

use App\Enums\DisputeStatus;
use App\Enums\DisputeUserType;
use App\Http\Controllers\Controller;
use App\Models\Dispute;
use App\Models\DisputeMessage;
use App\Models\Order;
use App\Services\DisputeService;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CustomerDisputeController extends Controller
{
    public function __construct(
        private readonly DisputeService $disputeService,
    ) {}

    /**
     * Show the form to open a dispute for a delivered order.
     */
    public function openDisputeForm(int $orderId): View|RedirectResponse
    {
        if (! auth('customer')->check()) {
            return redirect()->route('customer.auth.login');
        }

        $customerId = auth('customer')->id();

        $order = Order::with(['details.product'])
            ->where('id', $orderId)
            ->where('customer_id', $customerId)
            ->where('is_guest', '0')
            ->first();

        if (! $order) {
            Toastr::error(translate('order_not_found'));

            return redirect()->route('account-oder');
        }

        $check = $this->disputeService->canOpenDispute($order, DisputeUserType::BUYER);

        if (! $check['allowed']) {
            Toastr::warning($check['reason']);

            return redirect()->route('account-order-details', ['id' => $orderId]);
        }

        $reasons = $this->disputeService->getBuyerDisputeReasons();

        return view(VIEW_FILE_NAMES['account_dispute_open_form'], compact('order', 'reasons'));
    }

    /**
     * Save a new dispute.
     */
    public function storeDispute(Request $request): RedirectResponse
    {
        if (! auth('customer')->check()) {
            return redirect()->route('customer.auth.login');
        }

        $customerId = auth('customer')->id();

        $request->validate([
            'order_id' => 'required|integer',
            'reason_id' => 'nullable|integer|exists:dispute_reasons,id',
            'description' => 'required|string|min:20|max:2000',
            'files' => 'nullable|array|max:5',
            'files.*' => 'file|mimes:jpg,jpeg,png,mp4',
        ], [
            'description.min' => translate('Please_describe_the_issue_in_at_least_20_characters'),
            'files.*.mimes' => translate('Only_jpg,_jpeg,_png,_and_mp4_files_are_allowed'),
        ]);

        $files = $request->file('files', []);

        try {
            $this->disputeService->validateEvidenceFiles($files);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput();
        }

        $order = Order::where('id', $request->order_id)
            ->where('customer_id', $customerId)
            ->where('is_guest', '0')
            ->first();

        if (! $order) {
            Toastr::error(translate('order_not_found'));

            return redirect()->route('account-oder');
        }

        $check = $this->disputeService->canOpenDispute($order, DisputeUserType::BUYER);

        if (! $check['allowed']) {
            Toastr::warning($check['reason']);

            return redirect()->route('account-order-details', ['id' => $order->id]);
        }

        $dispute = $this->disputeService->createDispute(
            array_merge($request->only(['order_id', 'reason_id', 'description']), ['user_id' => $customerId]),
            DisputeUserType::BUYER
        );

        // Upload optional evidence files
        if (! empty($files)) {
            $this->disputeService->uploadEvidenceFiles(
                dispute: $dispute,
                files: $files,
                uploadedBy: $customerId,
                userType: DisputeUserType::BUYER,
            );
        }

        Toastr::success(translate('dispute_opened_successfully'));

        return redirect()->route('account-dispute.details', $dispute->id);
    }

    /**
     * Send a message on an existing dispute.
     */
    public function sendMessage(Request $request, int $id): RedirectResponse
    {
        if (! auth('customer')->check()) {
            return redirect()->route('customer.auth.login');
        }

        $customerId = auth('customer')->id();

        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $dispute = Dispute::where('id', $id)->where('buyer_id', $customerId)->first();

        if (! $dispute) {
            Toastr::error(translate('dispute_not_found'));

            return redirect()->route('account-disputes');
        }

        if (in_array($dispute->status, [
            DisputeStatus::RESOLVED_REFUND,
            DisputeStatus::RESOLVED_RELEASE,
            DisputeStatus::CLOSED,
            DisputeStatus::AUTO_CLOSED,
        ])) {
            Toastr::warning(translate('dispute_is_already_closed'));

            return redirect()->route('account-dispute.details', $id);
        }

        DisputeMessage::create([
            'dispute_id' => $dispute->id,
            'sender_id' => $customerId,
            'sender_type' => DisputeUserType::BUYER,
            'message' => $request->message,
            'created_at' => now(),
        ]);

        Toastr::success(translate('message_sent_successfully'));

        return redirect()->route('account-dispute.details', $id);
    }

    /**
     * Upload evidence files to an existing dispute.
     */
    public function uploadEvidence(Request $request, int $id): RedirectResponse
    {
        if (! auth('customer')->check()) {
            return redirect()->route('customer.auth.login');
        }

        $customerId = auth('customer')->id();

        $request->validate([
            'files' => 'required|array|max:5',
            'files.*' => 'file|mimes:jpg,jpeg,png,mp4',
        ]);

        $dispute = Dispute::where('id', $id)->where('buyer_id', $customerId)->first();

        if (! $dispute) {
            Toastr::error(translate('dispute_not_found'));

            return redirect()->route('account-disputes');
        }

        if (in_array($dispute->status, [
            DisputeStatus::RESOLVED_REFUND,
            DisputeStatus::RESOLVED_RELEASE,
            DisputeStatus::CLOSED,
            DisputeStatus::AUTO_CLOSED,
        ])) {
            Toastr::warning(translate('dispute_is_already_closed'));

            return redirect()->route('account-dispute.details', $id);
        }

        try {
            $this->disputeService->uploadEvidenceFiles(
                dispute: $dispute,
                files: $request->file('files', []),
                uploadedBy: $customerId,
                userType: DisputeUserType::BUYER,
            );
        } catch (ValidationException $exception) {
            Toastr::error(collect($exception->errors())->flatten()->first() ?? translate('something_went_wrong'));

            return redirect()->route('account-dispute.details', $id);
        }

        Toastr::success(translate('evidence_uploaded_successfully'));

        return redirect()->route('account-dispute.details', $id);
    }

    /**
     * Escalate a dispute to admin review.
     */
    public function escalate(int $id): RedirectResponse
    {
        if (! auth('customer')->check()) {
            return redirect()->route('customer.auth.login');
        }

        $customerId = auth('customer')->id();

        $dispute = Dispute::where('id', $id)->where('buyer_id', $customerId)->first();

        if (! $dispute) {
            Toastr::error(translate('dispute_not_found'));

            return redirect()->route('account-disputes');
        }

        if (! in_array($dispute->status, [DisputeStatus::OPEN, DisputeStatus::VENDOR_RESPONSE])) {
            Toastr::warning(translate('dispute_cannot_be_escalated_at_this_stage'));

            return redirect()->route('account-dispute.details', $id);
        }

        $this->disputeService->escalateToAdmin($dispute, $customerId, DisputeUserType::BUYER);

        Toastr::success(translate('dispute_escalated_to_admin_for_review'));

        return redirect()->route('account-dispute.details', $id);
    }

    /**
     * Buyer confirms the admin-requested closure, finalising the dispute as closed.
     */
    public function confirmClosure(int $id): RedirectResponse
    {
        if (! auth('customer')->check()) {
            return redirect()->route('customer.auth.login');
        }

        $customerId = auth('customer')->id();

        $dispute = Dispute::where('id', $id)
            ->where('buyer_id', $customerId)
            ->where('status', DisputeStatus::PENDING_CLOSURE)
            ->first();

        if (! $dispute) {
            Toastr::error(translate('dispute_not_found_or_closure_not_pending'));

            return redirect()->route('account-disputes');
        }

        $this->disputeService->closeDispute($dispute, $customerId, translate('Buyer_confirmed_closure'), DisputeUserType::BUYER);

        Toastr::success(translate('dispute_closed_successfully'));

        return redirect()->route('account-disputes');
    }
}
