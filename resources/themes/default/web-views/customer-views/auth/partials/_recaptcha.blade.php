@if ($web_config['firebase_otp_verification'] && $web_config['firebase_otp_verification']['status'])
    <div class="generate-firebase-auth-recaptcha" id="firebase-auth-recaptcha-{{ rand(111, 999) }}"></div>
@elseif(isset($recaptcha) && $recaptcha['status'] == 1)
    <div class="dynamic-default-and-recaptcha-section">
        <input type="hidden" name="g-recaptcha-response" class="render-grecaptcha-response" data-action="customer_auth"
            data-input="#login-default-captcha-section" data-default-captcha="#login-default-captcha-section">

        <div class="default-captcha-container d-none" id="login-default-captcha-section"
            data-placeholder="{{ translate('enter_captcha_value') }}"
            data-base-url="{{ route('g-recaptcha-session-store') }}"
            data-session="{{ 'default_recaptcha_id_customer_auth' }}">
        </div>
    </div>
@else
    <div class="form-group mb-2">
        <label class="form-label">{{ translate('Verification') }}</label>
        <div class="d-flex align-items-center gap-3">
            <span class="fs-5 fw-bold user-select-none px-3 py-2 rounded"
                style="background: rgba(var(--bs-primary-rgb, 13,110,253), 0.1); letter-spacing: 3px; white-space: nowrap; border: 1px solid rgba(var(--bs-primary-rgb, 13,110,253), 0.2);">
                {{ $mathNum1 ?? 0 }} + {{ $mathNum2 ?? 0 }} = ?
            </span>
            <input type="number" class="form-control" name="default_captcha_value"
                placeholder="{{ translate('Answer') }}" min="0" max="18" autocomplete="off" required>
        </div>
    </div>
@endif
