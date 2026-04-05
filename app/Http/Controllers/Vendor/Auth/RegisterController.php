<?php

namespace App\Http\Controllers\Vendor\Auth;

use App\Contracts\Repositories\AdminRepositoryInterface;
use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Contracts\Repositories\EmailTemplatesRepositoryInterface;
use App\Contracts\Repositories\HelpTopicRepositoryInterface;
use App\Contracts\Repositories\ShopRepositoryInterface;
use App\Contracts\Repositories\VendorRepositoryInterface;
use App\Contracts\Repositories\VendorWalletRepositoryInterface;
use App\Enums\SessionKey;
use App\Events\VendorRegistrationEvent;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Vendor\VendorAddRequest;
use App\Models\AreaRequest;
use App\Models\CityRequest;
use App\Models\LocationArea;
use App\Models\LocationCity;
use App\Models\LocationCountry;
use App\Models\Notification;
use App\Repositories\VendorRegistrationReasonRepository;
use App\Services\RecaptchaService;
use App\Services\ShopService;
use App\Services\VendorService;
use App\Traits\EmailTemplateTrait;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class RegisterController extends BaseController
{
    use EmailTemplateTrait;

    public function __construct(
        private readonly VendorRepositoryInterface $vendorRepo,
        private readonly AdminRepositoryInterface $adminRepo,
        private readonly VendorWalletRepositoryInterface $vendorWalletRepo,
        private readonly ShopRepositoryInterface $shopRepo,
        private readonly VendorService $vendorService,
        private readonly ShopService $shopService,
        private readonly EmailTemplatesRepositoryInterface $emailTemplatesRepo,
        private readonly BusinessSettingRepositoryInterface $businessSettingRepo,
        private readonly HelpTopicRepositoryInterface $helpTopicRepo,
        private readonly VendorRegistrationReasonRepository $vendorRegistrationReasonRepo,
    ) {}

    public function index(?Request $request, ?string $type = null): View|Collection|LengthAwarePaginator|null|callable|RedirectResponse
    {
        $businessMode = getWebConfig(name: 'business_mode');
        $vendorRegistration = getWebConfig(name: 'seller_registration');
        if ((isset($businessMode) && $businessMode == 'single') || (isset($vendorRegistration) && $vendorRegistration == 0)) {
            ToastMagic::warning(translate('access_denied') . '!!');

            return redirect('/');
        }
        $vendorRegistrationHeader = json_decode($this->businessSettingRepo->getFirstWhere(params: ['type' => 'vendor_registration_header'])['value']);
        $vendorRegistrationReasons = $this->vendorRegistrationReasonRepo->getListWhere(orderBy: ['priority' => 'desc'], filters: ['status' => 1], dataLimit: 'all');
        $sellWithUs = json_decode($this->businessSettingRepo->getFirstWhere(params: ['type' => 'vendor_registration_sell_with_us'])['value']);
        $downloadVendorApp = json_decode($this->businessSettingRepo->getFirstWhere(params: ['type' => 'download_vendor_app'])['value']);
        $businessProcess = json_decode($this->businessSettingRepo->getFirstWhere(params: ['type' => 'business_process_main_section'])['value']);
        $businessProcessStep = json_decode($this->businessSettingRepo->getFirstWhere(params: ['type' => 'business_process_step'])['value']);
        $helpTopics = $this->helpTopicRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            filters: ['type' => 'vendor_registration', 'status' => '1'],
            dataLimit: 'all'
        );

        $recaptcha = getWebConfig(name: 'recaptcha');
        $mathNum1 = rand(1, 9);
        $mathNum2 = rand(1, 9);
        session([SessionKey::VENDOR_RECAPTCHA_KEY => $mathNum1 + $mathNum2]);

        return view(VIEW_FILE_NAMES['seller_registration'], compact('vendorRegistrationHeader', 'vendorRegistrationReasons', 'sellWithUs', 'downloadVendorApp', 'helpTopics', 'businessProcess', 'businessProcessStep', 'recaptcha', 'mathNum1', 'mathNum2'));
    }

    public function add(VendorAddRequest $request): JsonResponse
    {
        $result = RecaptchaService::verificationStatus(request: $request, session: SessionKey::VENDOR_RECAPTCHA_KEY, action: 'register');
        if ($result && ! $result['status']) {
            if ($request->ajax()) {
                return response()->json([
                    'error' => $result['message'],
                ]);
            }
        }
        $adminEmail = $this->adminRepo->getFirstWhere(['admin_role_id' => 1]);
        if ($adminEmail && isset($adminEmail['email']) && $request['email'] === $adminEmail['email']) {
            return response()->json([
                'error' => translate('Email_already_exist_please_try_another_email'),
            ]);
        }
        $vendor = $this->vendorRepo->add(data: $this->vendorService->getAddData($request));
        $this->shopRepo->add($this->shopService->getAddShopDataForRegistration(request: $request, vendorId: $vendor['id']));
        $this->vendorWalletRepo->add($this->vendorService->getInitialWalletData(vendorId: $vendor['id']));

        $data = [
            'vendorName' => $request['f_name'],
            'status' => 'pending',
            'subject' => translate('Vendor_Registration_Successfully_Completed'),
            'title' => translate('Vendor_Registration_Successfully_Completed'),
            'userType' => 'vendor',
            'templateName' => 'registration',
        ];
        try {
            event(new VendorRegistrationEvent(email: $request['email'], data: $data));
        } catch (Exception $e) {
            return response()->json(
                ['status' => 1, 'redirectRoute' => route('vendor.auth.login')]
            );
        }

        return response()->json(
            ['status' => 1, 'redirectRoute' => route('vendor.auth.login')]
        );
    }

    public function locationCountries(): JsonResponse
    {
        $countries = LocationCountry::where('is_active', 1)->orderBy('name')->get(['id', 'name']);

        return response()->json($countries);
    }

    public function locationCities(int $countryId): JsonResponse
    {
        $cities = LocationCity::where('country_id', $countryId)->where('is_active', 1)->orderBy('name')->get(['id', 'name']);

        return response()->json($cities);
    }

    public function locationAreas(int $cityId): JsonResponse
    {
        $areas = LocationArea::where('city_id', $cityId)->where('is_active', 1)->orderBy('name')->get(['id', 'name']);

        return response()->json($areas);
    }

    public function requestCity(Request $request): JsonResponse
    {
        $request->validate([
            'city_name' => ['required', 'string', 'max:191', function ($attribute, $value, $fail) use ($request) {
                $exists = LocationCity::where('country_id', $request->integer('country_id'))
                    ->whereRaw('LOWER(name) = ?', [mb_strtolower($value)])
                    ->exists();
                if ($exists) {
                    $fail(translate('this_city_already_exists'));
                }

                $pendingExists = CityRequest::where('country_id', $request->integer('country_id'))
                    ->whereRaw('LOWER(city_name) = ?', [mb_strtolower($value)])
                    ->where('status', 'pending')
                    ->exists();
                if ($pendingExists) {
                    $fail(translate('a_request_for_this_city_is_already_pending'));
                }
            }],
            'country_id' => ['required', 'integer', 'exists:location_countries,id'],
        ]);

        $cityRequest = CityRequest::create([
            'seller_id' => null,
            'country_id' => $request->integer('country_id'),
            'city_name' => $request->input('city_name'),
            'status' => 'pending',
        ]);

        Notification::create([
            'sent_by' => 'system',
            'sent_to' => 'admin',
            'title' => translate('new_city_request'),
            'description' => translate('a_new_vendor_registration_requested_a_new_city') . ': ' . $cityRequest->city_name,
            'notification_count' => 1,
            'status' => 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => translate('city_request_submitted_for_admin_approval'),
            'city_request' => $cityRequest->only(['id', 'city_name', 'status']),
        ]);
    }

    public function requestArea(Request $request): JsonResponse
    {
        $request->validate([
            'area_name' => ['required', 'string', 'max:191', function ($attribute, $value, $fail) use ($request) {
                $exists = LocationArea::where('city_id', $request->integer('city_id'))
                    ->whereRaw('LOWER(name) = ?', [mb_strtolower($value)])
                    ->exists();
                if ($exists) {
                    $fail(translate('this_area_already_exists'));
                }

                $pendingExists = AreaRequest::where('city_id', $request->integer('city_id'))
                    ->whereRaw('LOWER(area_name) = ?', [mb_strtolower($value)])
                    ->where('status', 'pending')
                    ->exists();
                if ($pendingExists) {
                    $fail(translate('a_request_for_this_area_is_already_pending'));
                }
            }],
            'city_id' => ['required', 'integer', 'exists:location_cities,id'],
        ]);

        $areaRequest = AreaRequest::create([
            'seller_id' => null,
            'city_id' => $request->integer('city_id'),
            'area_name' => $request->input('area_name'),
            'status' => 'pending',
        ]);

        Notification::create([
            'sent_by' => 'system',
            'sent_to' => 'admin',
            'title' => translate('new_area_request'),
            'description' => translate('a_new_vendor_registration_requested_a_new_area') . ': ' . $areaRequest->area_name,
            'notification_count' => 1,
            'status' => 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => translate('area_request_submitted_for_admin_approval'),
            'area_request' => $areaRequest->only(['id', 'area_name', 'status']),
        ]);
    }
}
