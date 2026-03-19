<?php

namespace App\Http\Controllers\Vendor\Shipping;

use App\Contracts\Repositories\ShippingTypeRepositoryInterface;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Vendor\ShippingTypeRequest;
use App\Services\ShippingTypeService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ShippingTypeController extends BaseController
{
    public function __construct(
        private readonly ShippingTypeRepositoryInterface $shippingTypeRepo,
        private readonly ShippingTypeService $shippingTypeService
    ) {}

    public function index(?Request $request, ?string $type = null): View|Collection|LengthAwarePaginator|null|callable|RedirectResponse
    {
        return null;
    }

    public function addOrUpdate(ShippingTypeRequest $request): JsonResponse
    {
        $vendorId = auth('seller')->id();
        $shippingType = $this->shippingTypeRepo->getFirstWhere(['seller_id' => $vendorId]);
        if (! empty($shippingType)) {
            $this->shippingTypeRepo->update(
                id: $shippingType['id'],
                data: $this->shippingTypeService->getShippingTypeDataForUpdate(request: $request)
            );
        } else {

            $this->shippingTypeRepo->add(
                data: $this->shippingTypeService->getShippingTypeDataForAdd(
                    request: $request,
                    id: $vendorId)
            );
        }

        return response()->json();
    }
}
