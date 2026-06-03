<?php

namespace App\Http\Controllers\Web;

use App\Contracts\Repositories\CurrencyRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Utils\Helpers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Session;

class LocaleController extends Controller
{
    public function __construct(
        private readonly CurrencyRepositoryInterface $currencyRepo
    ) {}

    public function switchLocale(Request $request): JsonResponse
    {
        $languageCode = $request->input('language_code');
        $currencyCode = $request->input('currency_code');

        if ($languageCode) {
            $direction = 'ltr';
            $language = getWebConfig('language');
            foreach ($language as $data) {
                if ($data['code'] == $languageCode) {
                    $direction = $data['direction'] ?? 'ltr';
                }
            }
            session()->forget('language_settings');
            Helpers::language_load();
            session()->put('local', $languageCode);
            Session::put('direction', $direction);
            Artisan::call('cache:clear');
        }

        if ($currencyCode) {
            session()->put('currency_code', $currencyCode);
            $currency = $this->currencyRepo->getFirstWhere(params: ['code' => $currencyCode]);
            session()->put('currency_symbol', $currency['symbol']);
            session()->put('currency_exchange_rate', $currency['exchange_rate']);
            session()->forget('default');
            session()->forget('usd');
        }

        return response()->json(['success' => true]);
    }
}
