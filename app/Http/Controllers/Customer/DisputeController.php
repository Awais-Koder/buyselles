<?php

namespace App\Http\Controllers\Customer;

use App\Enums\DisputeStatus;
use App\Enums\DisputeUserType;
use App\Enums\EscrowStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\OpenDisputeRequest;
use App\Http\Requests\DisputeMessageRequest;
use App\Models\Dispute;
use App\Models\DisputeMessage;
use App\Models\Escrow;
use App\Models\Order;
use App\Services\DisputeService;
use App\Services\EscrowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DisputeController extends Controller
{
    public function __construct(
        private readonly DisputeService $disputeService,
        private readonly EscrowService $escrowService,
    ) {}

    public function reasons(): JsonResponse
    {
        $reasons = $this->disputeService->getBuyerDisputeReasons();

        return response()->json([
            'status' => 200,
            'message' => 'success',
            'data' => $reasons,
        ]);
    }

    public function confirmClosure(int $id): JsonResponse
    {
        $buyerId = auth('api')->id();

        $dispute = Dispute::where('id', $id)
            ->where('buyer_id', $buyerId)
            ->where('status', DisputeStatus::PENDING_CLOSURE)
            ->first();

        if (! $dispute) {
            return response()->json(['status' => 404, 'message' => translate('dispute_not_found_or_closure_not_pending')], 404);
        }

        $this->disputeService->closeDispute(
            dispute: $dispute,
            closedById: $buyerId,
            note: translate('Buyer_confirmed_closure'),
            closedByType: DisputeUserType::BUYER
        );

        return response()->json([
            'status' => 200,
            'message' => translate('dispute_closed_successfully'),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $buyerId = auth('api')->id();

        $disputes = Dispute::where('buyer_id', $buyerId)
            ->with(['reason', 'order'])
            ->latest()
            ->paginate(15);

        return response()->json([
            'status' => 200,
            'message' => 'success',
            'data' => $disputes,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $buyerId = auth('api')->id();

        $dispute = Dispute::where('id', $id)
            ->where('buyer_id', $buyerId)
            ->with(['reason', 'order', 'messages.sender', 'evidence', 'statusLogs'])
            ->first();

        if (! $dispute) {
            return response()->json(['status' => 404, 'message' => translate('dispute_not_found')], 404);
        }

        return response()->json([
            'status' => 200,
            'message' => 'success',
            'data' => $dispute,
        ]);
    }

    public function store(OpenDisputeRequest $request): JsonResponse
    {
        $buyerId = auth('api')->id();
        $files = $request->file('files', []);

        $order = Order::where('id', $request->order_id)
            ->where('customer_id', $buyerId)
            ->first();

        if (! $order) {
            return response()->json(['status' => 403, 'message' => translate('you_are_not_authorized_to_dispute_this_order')], 403);
        }

        $check = $this->disputeService->canOpenDispute($order, DisputeUserType::BUYER);

        if (! $check['allowed']) {
            return response()->json(['status' => 422, 'message' => $check['reason']], 422);
        }

        try {
            $this->disputeService->validateEvidenceFiles($files);
        } catch (ValidationException $exception) {
            return response()->json([
                'status' => 422,
                'message' => collect($exception->errors())->flatten()->first() ?? translate('something_went_wrong'),
                'errors' => $exception->errors(),
            ], 422);
        }

        $dispute = $this->disputeService->createDispute(
            array_merge($request->validated(), ['user_id' => $buyerId]),
            DisputeUserType::BUYER
        );

        if (! empty($files)) {
            $this->disputeService->uploadEvidenceFiles(
                dispute: $dispute,
                files: $files,
                uploadedBy: $buyerId,
                userType: DisputeUserType::BUYER,
            );

            $dispute->load('evidence');
        }

        return response()->json([
            'status' => 201,
            'message' => translate('dispute_opened_successfully'),
            'data' => $dispute,
        ], 201);
    }

    public function addMessage(DisputeMessageRequest $request, int $id): JsonResponse
    {
        $buyerId = auth('api')->id();

        $dispute = Dispute::where('id', $id)->where('buyer_id', $buyerId)->first();

        if (! $dispute) {
            return response()->json(['status' => 404, 'message' => translate('dispute_not_found')], 404);
        }

        if (in_array($dispute->status, [DisputeStatus::RESOLVED_REFUND, DisputeStatus::RESOLVED_RELEASE, DisputeStatus::CLOSED, DisputeStatus::AUTO_CLOSED])) {
            return response()->json(['status' => 422, 'message' => translate('dispute_is_already_closed')], 422);
        }

        $message = DisputeMessage::create([
            'dispute_id' => $dispute->id,
            'sender_id' => $buyerId,
            'sender_type' => DisputeUserType::BUYER,
            'message' => $request->message,
        ]);

        return response()->json([
            'status' => 201,
            'message' => translate('message_sent_successfully'),
            'data' => $message,
        ], 201);
    }

    public function uploadEvidence(Request $request, int $id): JsonResponse
    {
        $buyerId = auth('api')->id();

        $dispute = Dispute::where('id', $id)->where('buyer_id', $buyerId)->first();

        if (! $dispute) {
            return response()->json(['status' => 404, 'message' => translate('dispute_not_found')], 404);
        }

        $request->validate([
            'files' => 'required|array|max:5',
            'files.*' => 'file|mimes:jpg,jpeg,png,mp4',
        ]);

        try {
            $uploaded = $this->disputeService->uploadEvidenceFiles(
                dispute: $dispute,
                files: $request->file('files', []),
                uploadedBy: $buyerId,
                userType: DisputeUserType::BUYER,
            );
        } catch (ValidationException $exception) {
            return response()->json([
                'status' => 422,
                'message' => collect($exception->errors())->flatten()->first() ?? translate('something_went_wrong'),
                'errors' => $exception->errors(),
            ], 422);
        }

        return response()->json([
            'status' => 201,
            'message' => translate('evidence_uploaded_successfully'),
            'data' => $uploaded,
        ], 201);
    }

    public function confirmReceipt(int $orderId): JsonResponse
    {
        $buyerId = auth('api')->id();

        $order = Order::where('id', $orderId)->where('customer_id', $buyerId)->first();

        if (! $order) {
            return response()->json(['status' => 403, 'message' => translate('order_not_found')], 403);
        }

        $escrow = Escrow::where('order_id', $orderId)
            ->where('status', EscrowStatus::HELD)
            ->whereNull('dispute_id')
            ->first();

        if (! $escrow) {
            return response()->json(['status' => 404, 'message' => translate('no_held_escrow_found_for_this_order')], 404);
        }

        $this->escrowService->releaseEscrow($escrow, 'buyer_confirm');

        return response()->json([
            'status' => 200,
            'message' => translate('receipt_confirmed_funds_released_to_vendor'),
        ]);
    }

    public function escalate(int $id): JsonResponse
    {
        $buyerId = auth('api')->id();

        $dispute = Dispute::where('id', $id)->where('buyer_id', $buyerId)->first();

        if (! $dispute) {
            return response()->json(['status' => 404, 'message' => translate('dispute_not_found')], 404);
        }

        if (! in_array($dispute->status, [DisputeStatus::OPEN, DisputeStatus::VENDOR_RESPONSE])) {
            return response()->json(['status' => 422, 'message' => translate('dispute_cannot_be_escalated_in_current_status')], 422);
        }

        $dispute = $this->disputeService->escalateToAdmin($dispute, $buyerId, DisputeUserType::BUYER);

        return response()->json([
            'status' => 200,
            'message' => translate('dispute_escalated_to_admin'),
            'data' => $dispute,
        ]);
    }
}
