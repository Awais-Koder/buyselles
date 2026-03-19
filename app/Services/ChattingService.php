<?php

namespace App\Services;

use App\Enums\GlobalConstant;
use App\Traits\FileManagerTrait;

class ChattingService
{
    use FileManagerTrait;

    public function getAttachment(object $request): array
    {
        $attachment = [];
        if ($request->file('media')) {
            foreach ($request['media'] as $file) {
                if (in_array('.'.$file->getClientOriginalExtension(), GlobalConstant::VIDEO_EXTENSION)) {
                    $attachment[] = [
                        'file_name' => $this->fileUpload(dir: 'chatting/', format: $file->getClientOriginalExtension(), file: $file),
                        'storage' => getWebConfig(name: 'storage_connection_type') ?? 'public',
                    ];
                } else {
                    $attachment[] = [
                        'file_name' => $this->upload('chatting/', 'webp', $file),
                        'storage' => getWebConfig(name: 'storage_connection_type') ?? 'public',
                    ];
                }
            }
        }
        if ($request->file('file')) {
            foreach ($request['file'] as $value) {
                $attachment[] = [
                    'file_name' => $this->fileUpload(dir: 'chatting/', format: $value->getClientOriginalExtension(), file: $value),
                    'storage' => getWebConfig(name: 'storage_connection_type') ?? 'public',
                ];

            }
        }

        return $attachment;
    }

    public function getDeliveryManChattingData(object $request, string|int $shopId, string|int $vendorId): array
    {
        return [
            'delivery_man_id' => $request['delivery_man_id'],
            'seller_id' => $vendorId,
            'shop_id' => $shopId,
            'message' => $request['message'],
            'attachment' => json_encode($this->getAttachment($request)),
            'sent_by_seller' => 1,
            'seen_by_seller' => 1,
            'seen_by_delivery_man' => 0,
            'notification_receiver' => 'deliveryman',
            'created_at' => now(),
        ];
    }

    public function getCustomerChattingData(object $request, string|int $shopId, string|int $vendorId): array
    {
        return [
            'user_id' => $request['user_id'],
            'seller_id' => $vendorId,
            'shop_id' => $shopId,
            'message' => $request->message,
            'attachment' => json_encode($this->getAttachment($request)),
            'sent_by_seller' => 1,
            'seen_by_seller' => 1,
            'seen_by_customer' => 0,
            'notification_receiver' => 'customer',
            'created_at' => now(),
        ];
    }

    public function addChattingData(object $request, string $type): array
    {
        $attachment = $this->getAttachment(request: $request);

        return [
            'delivery_man_id' => $type == 'delivery-man' ? $request['delivery_man_id'] : null,
            'user_id' => $type == 'customer' ? $request['user_id'] : null,
            'admin_id' => 0,
            'message' => $request['message'],
            'attachment' => json_encode($attachment),
            'sent_by_admin' => 1,
            'seen_by_admin' => 1,
            'seen_by_customer' => 0,
            'seen_by_delivery_man' => $type == 'delivery-man' ? 0 : null,
            'notification_receiver' => $type == 'delivery-man' ? 'deliveryman' : 'customer',
            'created_at' => now(),
        ];
    }

    public function addChattingDataForWeb(object $request, string|int $userId, string $type, string|int|null $shopId = null, string|int|null $vendorId = null, ?int $adminId = null, ?int $deliveryManId = null): array
    {
        return [
            'user_id' => $userId,
            'seller_id' => $vendorId,
            'shop_id' => $shopId,
            'admin_id' => $adminId,
            'delivery_man_id' => $deliveryManId,
            'message' => $request->message,
            'attachment' => json_encode($this->getAttachment($request)),
            'sent_by_customer' => 1,
            'seen_by_customer' => 1,
            'seen_by_seller' => 0,
            'seen_by_admin' => $type == 'admin' ? 0 : null,
            'seen_by_delivery_man' => $type == 'deliveryman' ? 0 : null,
            'notification_receiver' => $type,
            'created_at' => now(),
        ];
    }
}
