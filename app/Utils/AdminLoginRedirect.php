<?php

namespace App\Utils;

use App\Enums\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class AdminLoginRedirect
{
    public const LOGIN_URL_COOKIE = 'admin_panel_login_url';

    public static function loginUrlForRole(string $role): string
    {
        $configKey = $role === UserRole::ADMIN ? 'admin_login_url' : 'employee_login_url';

        return getWebConfig(name: $configKey);
    }

    public static function rememberLoginUrlForRole(string $role): void
    {
        Cookie::queue(
            self::LOGIN_URL_COOKIE,
            self::loginUrlForRole($role),
            60 * 24 * 30
        );
    }

    public static function forgetLoginUrl(): void
    {
        Cookie::queue(Cookie::forget(self::LOGIN_URL_COOKIE));
    }

    public static function resolveLoginUrl(?Request $request = null): string
    {
        return $request?->cookie(self::LOGIN_URL_COOKIE)
            ?? getWebConfig(name: 'employee_login_url');
    }

    public static function guestRedirect(?Request $request = null): RedirectResponse
    {
        return redirect()->guest(url('login/'.self::resolveLoginUrl($request)));
    }
}
