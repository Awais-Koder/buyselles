<?php

namespace App\Http\Controllers;

use App\Enums\SessionKey;
use App\Models\Admin;
use App\Models\BusinessSetting;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest:admin', ['except' => 'logout']);
    }

    public function login($login_url)
    {
        $data = array_column(BusinessSetting::whereIn('type', ['employee_login_url', 'admin_login_url'])->get(['type', 'value'])->toArray(), 'value', 'type');

        $loginTypes = [
            'admin' => 'admin_login_url',
            'employee' => 'employee_login_url',
        ];

        $role = null;

        $user_type = array_search($login_url, $data);
        abort_if(! $user_type, 404);
        $role = array_search($user_type, $loginTypes, true);
        abort_if($role == null, 404);

        $recaptcha = getWebConfig(name: 'recaptcha');
        $mathNum1 = rand(1, 9);
        $mathNum2 = rand(1, 9);

        if (! (isset($recaptcha) && $recaptcha['status'] == 1)) {
            $sessionKey = $role === 'admin' ? SessionKey::ADMIN_RECAPTCHA_KEY : SessionKey::EMPLOYEE_RECAPTCHA_KEY;
            Session::put($sessionKey, (string) ($mathNum1 + $mathNum2));
        }

        if ($role === 'admin' || $role === 'employee') {
            return view('admin-views.auth.login', compact('mathNum1', 'mathNum2', 'role'));
        }
    }

    public function submit(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
            'role' => 'required',
        ]);

        $sessionKey = $request['role'] == 'admin' ? SessionKey::ADMIN_RECAPTCHA_KEY : SessionKey::EMPLOYEE_RECAPTCHA_KEY;
        $recaptcha = getWebConfig(name: 'recaptcha');

        if (isset($recaptcha) && $recaptcha['status'] == 1 && ! ($request['set_default_captcha'] == 1)) {
            $request->validate([
                'g-recaptcha-response' => [
                    function ($attribute, $value, $fail) {
                        $secret_key = getWebConfig(name: 'recaptcha')['secret_key'];
                        $response = $value;
                        $url = 'https://www.google.com/recaptcha/api/siteverify?secret='.$secret_key.'&response='.$response;
                        $response = Http::get($url);
                        $response = $response->json();
                        if (! isset($response['success']) || ! $response['success']) {
                            $fail(translate('ReCAPTCHA_Failed'));
                        }
                    },
                ],
            ]);
        } elseif (session($sessionKey) === null || (string) $request['default_captcha_value'] !== session($sessionKey)) {
            Toastr::error(translate('Incorrect answer, please try again'));

            return back();
        }

        if ($request->role == 'admin') {
            $data = Admin::where('email', $request['email'])->where('admin_role_id', 1)->first();

            if (! isset($data)) {
                return redirect()->back()->withInput($request->only('email', 'remember'))
                    ->withErrors(['Credentials does not match.']);
            } elseif (isset($data) && $data->status != 1) {
                return redirect()->back()->withInput($request->only('email', 'remember'))
                    ->withErrors(['You are blocked!!, contact with admin.']);
            }
        } elseif ($request->role == 'employee') {

            $data = Admin::where('email', $request->email)->where('admin_role_id', '!=', 1)->first();

            if (! isset($data)) {
                return redirect()->back()->withInput($request->only('email', 'remember'))
                    ->withErrors(['Credentials does not match.']);
            } elseif (isset($data) && $data->status != 1) {
                return redirect()->back()->withInput($request->only('email', 'remember'))
                    ->withErrors(['You are blocked!!, contact with admin.']);
            }
        } else {
            Toastr::error(translate('role_missing'));

            return back();
        }

        $data = $this->login_attemp($request->role, $request->email, $request->password, $request->remember);

        if ($data == 'admin' || $data == 'employee') {
            return redirect()->route('admin.dashboard.index');
        }

        return redirect()->back()->withInput($request->only('email', 'remember'))
            ->withErrors(['Credentials does not match.']);
    }

    public function login_attemp($role, $email, $password, $remember = false)
    {
        if ($role == 'admin' || $role == 'employee') {
            if (auth('admin')->attempt(['email' => $email, 'password' => $password], $remember)) {
                return $role;
            }
        }

        return false;
    }

    public function captcha(Request $request, $tmp): void
    {
        // Legacy route kept for backwards compatibility — math captcha is now used on login.
    }
}
