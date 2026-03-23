<?php

namespace App\Http\Controllers;

use App\Enums\SessionKey;
use App\Http\Requests\Request;
use App\Traits\ActivationClass;
use App\Traits\RecaptchaTrait;
use App\Utils\Helpers;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Session;

class SharedController extends Controller
{
    use ActivationClass;
    use RecaptchaTrait;

    public function changeLanguage(Request $request): JsonResponse
    {
        $direction = 'ltr';
        $language = getWebConfig('language');
        foreach ($language as $data) {
            if ($data['code'] == $request['language_code']) {
                $direction = $data['direction'] ?? 'ltr';
            }
        }
        session()->forget('language_settings');
        Helpers::language_load();
        session()->put('local', $request['language_code']);
        Session::put('direction', $direction);
        Artisan::call('cache:clear');

        return response()->json(['message' => translate('language_change_successfully').'.']);
    }

    public function getSessionRecaptchaCode(Request $request): JsonResponse
    {
        if (env('APP_MODE') == 'dev' && session()->has($request['sessionKey'])) {
            $code = session($request['sessionKey']);
        }

        return response()->json(['code' => $code ?? '']);
    }

    public function storeRecaptchaResponse(Request $request): JsonResponse
    {
        $response = $request->get('g_recaptcha_response', null);
        if ($response) {
            session()->put('g-recaptcha-response', $response);
        }

        return response()->json(['recaptcha' => $response]);
    }

    public function storeRecaptchaSession(Request $request): void
    {
        $recaptchaBuilder = $this->generateDefaultReCaptcha(4);
        if (session()->has($request['sessionKey'])) {
            Session::forget($request['sessionKey']);
        }
        Session::put($request['sessionKey'], $recaptchaBuilder->getPhrase());
        header('Cache-Control: no-cache, must-revalidate');
        header('Content-Type:image/jpeg');
        header('Pragma:no-cache');
        header('Expires:Sat, 26 Jul 1997 05:00:00 GMT');
        $recaptchaBuilder->output();
    }

    public function refreshMathCaptcha(Request $request): JsonResponse
    {
        $allowedKeys = [
            'default_recaptcha_id_customer_auth',
            'default_recaptcha_id_vendor_forgot_password',
            'default_captcha_value_contact',
            SessionKey::VENDOR_RECAPTCHA_KEY,
        ];
        $sessionKey = $request->query('session_key', 'default_recaptcha_id_customer_auth');
        if (! in_array($sessionKey, $allowedKeys)) {
            $sessionKey = 'default_recaptcha_id_customer_auth';
        }
        $num1 = rand(1, 9);
        $num2 = rand(1, 9);
        session([$sessionKey => $num1 + $num2]);

        return response()->json(['num1' => $num1, 'num2' => $num2]);
    }

    public function getActivationCheckView(Request $request): View|RedirectResponse
    {
        $config = $this->getAddonsConfig();
        $adminPanel = $config['admin_panel'] ?? [];
        $status = ($this->is_local() || env('DEVELOPMENT_ENVIRONMENT', false)) ? 1 : ($adminPanel['active'] ?? 0);

        return $status == 1 ? redirect(url('/')) : view('installation.activation-check');
    }

    public function activationCheck(Request $request): RedirectResponse
    {
        $response = $this->getRequestConfig(
            username: $request['username'],
            purchaseKey: $request['purchase_key'],
            softwareType: $request->get('software_type', base64_decode('cHJvZHVjdA=='))
        );
        $this->updateActivationConfig(app: 'admin_panel', response: $response);

        if (! empty($response['errors'])) {
            foreach ($response['errors'] as $error) {
                $message = is_array($error) ? ($error[0] ?? 'Unknown error') : $error;
                ToastMagic::error($message);
            }
        }

        return redirect(url('/'));
    }
}
