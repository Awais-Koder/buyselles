<?php

namespace App\Http\Controllers\Customer;

use App\Enums\DisputeStatus;
use App\Enums\DisputeUserType;
use App\Enums\EscrowStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\OpenDisputeRequest;
use App\Http\Requests\DisputeMessageRequest;
use App\Models\Dispute;
use App\Models\DisputeEvidence;
use App\Models\DisputeMessage;
use App\Models\Escrow;
use App\Models\Order;
use App\Services\DisputeService;
use App\Services\EscrowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DisputeController extends Controller
{
    public function __construct(
        private readonly DisputeService $disputeService,
        private readonly EscrowService $escrowService,
    ) {}

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

        $dispute = $this->disputeService->createDispute(
            array_merge($request->validated(), ['user_id' => $buyerId]),
            DisputeUserType::BUYER
        );

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

        $uploaded = [];

        foreach ($request->file('files', []) as $file) {
            $mime = $file->getMimeType();
            $isVideo = str_contains($mime, 'video');
            $maxBytes = $isVideo ? 50 * 1024 * 1024 : 5 * 1024 * 1024;

            if ($file->getSize() > $maxBytes) {
                $limit = $isVideo ? '50MB' : '5MB';

                return response()->json([
                    'status' => 422,
                    'message' => translate('file_exceeds_maximum_allowed_size_of').' '.$limit,
                ], 422);
            }

            $path = $file->store("dispute-evidence/{$id}", 'public');

            $evidence = DisputeEvidence::create([
                'dispute_id' => $dispute->id,
                'uploaded_by' => $buyerId,
                'user_type' => DisputeUserType::BUYER,
                'file_path' => $path,
                'file_type' => $isVideo ? 'video' : 'image',
            ]);

            $uploaded[] = $evidence;
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
