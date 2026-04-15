<!DOCTYPE html>
<?php
use App\Models\SocialMedia;
use Illuminate\Support\Facades\Session;

$companyPhone = getWebConfig(name: 'company_phone');
$companyEmail = getWebConfig(name: 'company_email');
$companyName = getWebConfig(name: 'company_name');
$companyLogo = getWebConfig(name: 'company_web_logo');
$lang = \App\Utils\Helpers::default_lang();
$direction = Session::get('direction');
?>
<html lang="{{ $lang }}" dir="{{ $direction === 'rtl' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ translate('Your_Digital_Codes') }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap');

        body {
            margin: 0;
            font-family: 'Roboto', sans-serif;
            font-size: 13px;
            line-height: 21px;
            color: #737883;
            background: #f7fbff;
            padding: 0;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            color: #334257;
        }

        * {
            box-sizing: border-box;
        }

        :root {
            --base: #006161;
        }

        .main-table {
            width: 500px;
            background: #FFFFFF;
            margin: 0 auto;
            padding: 40px;
        }

        a {
            text-decoration: none;
        }

        .text-center {
            text-align: center;
        }

        .mb-1 {
            margin-bottom: 5px;
        }

        .mb-2 {
            margin-bottom: 10px;
        }

        .mb-3 {
            margin-bottom: 15px;
        }

        .mb-4 {
            margin-bottom: 20px;
        }

        .mail-img {
            width: 100%;
            height: 45px;
            object-fit: contain;
        }

        .code-block {
            background: #f4f8ff;
            border: 1.5px dashed #006161;
            border-radius: 6px;
            padding: 14px 18px;
            margin: 10px 0;
        }

        .code-val {
            font-family: 'Courier New', Courier, monospace;
            font-size: 17px;
            font-weight: 700;
            letter-spacing: 2px;
            color: #063C93;
            word-break: break-all;
        }

        .product-label {
            font-weight: 600;
            color: #334257;
            font-size: 13px;
            margin-bottom: 4px;
        }

        .meta-row {
            font-size: 11px;
            color: #888;
            margin-top: 4px;
        }

        .badge-order {
            background: #e6f9f5;
            border-radius: 4px;
            color: #006161;
            font-weight: 700;
            padding: 2px 8px;
            font-size: 13px;
        }

        .divider {
            border: none;
            border-top: 1px solid #e5e5e5;
            margin: 20px 0;
        }

        .social {
            margin: 15px 0 8px;
            display: block;
            text-align: center;
        }

        .privacy {
            text-align: center;
            display: block;
        }

        .copyright {
            text-align: center;
            display: block;
            font-size: 11px;
            color: #aaa;
        }
    </style>
</head>

<body style="background:#e9ecef; padding:15px">

    <table class="main-table" align="center" cellpadding="0" cellspacing="0">
        <tbody>
            <tr>
                <td>
                    {{-- Header --}}
                    <div class="text-center mb-3">
                        @php
                            try {
                                $logoUrl = getStorageImages(path: $companyLogo, type: 'backend-logo');
                            } catch (\Throwable $e) {
                                $logoUrl = asset('assets/back-end/img/placeholder-logo.png');
                            }
                        @endphp
                        <img class="mail-img" src="{{ $logoUrl }}" alt="{{ $companyName }}">
                    </div>

                    <h2 class="text-center" style="color:#006161; margin-bottom:4px;">
                        🎉 {{ translate('Your_Digital_Codes_Are_Ready') }}
                    </h2>

                    <p class="mb-3">
                        {{ translate('Hi') }} <strong>{{ $data['customerName'] }}</strong>,<br>
                        {{ translate('Thank_you_for_your_purchase!') }}
                        {{ translate('Your_digital_product_code(s)_for_order') }}
                        <span class="badge-order">#{{ $data['orderId'] }}</span>
                        {{ translate('are_included_below._Please_save_them_immediately.') }}
                    </p>

                    <hr class="divider">

                    {{-- Code blocks --}}
                    @foreach ($data['codes'] as $item)
                        <div class="code-block">
                            <div class="product-label">{{ $item['productName'] }}</div>
                            <div class="code-val">{{ $item['code'] }}</div>
                            <div class="meta-row">
                                @if (!empty($item['pin']))
                                    {{ translate('PIN') }}: <strong>{{ $item['pin'] }}</strong> &nbsp;|&nbsp;
                                @endif
                                @if (!empty($item['serial']))
                                    {{ translate('Serial') }}: {{ $item['serial'] }} &nbsp;|&nbsp;
                                @endif
                                @if (!empty($item['expiry']))
                                    {{ translate('Expires') }}: {{ $item['expiry'] }}
                                @endif
                            </div>
                        </div>
                    @endforeach

                    <hr class="divider">

                    <p style="color:#d9534f; font-size:12px; margin:0 0 12px;">
                        ⚠️ {{ translate('Do_not_share_this_email_with_anyone._These_codes_are_for_your_use_only.') }}
                    </p>

                    <p class="mb-2">
                        {{ translate('You_can_also_view_your_codes_any_time_from_your_order_history.') }}
                    </p>

                    <div class="mb-4">
                        {{ translate('Thanks_&_Regards') }},<br>
                        <strong>{{ $companyName }}</strong>
                    </div>

                    {{-- Footer --}}
                    <hr class="divider">
                    <span class="privacy">
                        <a href="{{ route('business-page.view', ['slug' => 'privacy-policy']) }}"
                            style="color:#334257;">
                            {{ translate('Privacy_Policy') }}
                        </a>
                        &nbsp;|&nbsp;
                        <a href="{{ route('contacts') }}" style="color:#334257;">
                            {{ translate('Contact_Us') }}
                        </a>
                    </span>
                    <span class="social">
                        @php($social_media = SocialMedia::where('active_status', 1)->get())
                        @foreach ($social_media as $social)
                            <a href="{{ $social->link }}" target="_blank" style="margin: 0 5px;">
                                <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/' . $social->name . '.png') }}"
                                    width="16" alt="{{ $social->name }}">
                            </a>
                        @endforeach
                    </span>
                    <span class="copyright">
                        {{ translate('All_copy_right_reserved') }}, {{ date('Y') }} {{ $companyName }}
                    </span>
                </td>
            </tr>
        </tbody>
    </table>

</body>

</html>
