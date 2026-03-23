@php($recaptcha = getWebConfig(name: 'recaptcha'))

@if ($web_config['firebase_otp_verification'] && $web_config['firebase_otp_verification']['status'])
    <div class="generate-firebase-auth-recaptcha" id="firebase-auth-recaptcha-{{ rand(111, 999) }}"></div>
@elseif(isset($recaptcha) && $recaptcha['status'] == 1)
    @php($randomNumber = rand(1111, 9999))
    <div class="dynamic-default-and-recaptcha-section">
        <input type="hidden" name="g-recaptcha-response" class="render-grecaptcha-response" data-action="customer_auth"
            data-input="#login-default-captcha-section-{{ $randomNumber }}"
            data-default-captcha="#login-default-captcha-section-{{ $randomNumber }}">

        <div class="default-captcha-container d-none" id="login-default-captcha-section-{{ $randomNumber }}"
            data-placeholder="{{ translate('enter_captcha_value') }}"
            data-base-url="{{ route('g-recaptcha-session-store') }}"
            data-session="{{ 'default_recaptcha_id_customer_auth' }}">
        </div>
    </div>
@else
    @php
        $captchaSessionKey = $captchaSessionKey ?? 'default_recaptcha_id_customer_auth';
        $mathNum1 = rand(1, 9);
        $mathNum2 = rand(1, 9);
        session([$captchaSessionKey => $mathNum1 + $mathNum2]);
    @endphp
    <div class="d-flex align-items-center gap-3 mt-2" data-math-captcha-key="{{ $captchaSessionKey }}">
        <span class="fs-5 fw-bold user-select-none px-3 py-2 rounded"
            style="background: rgba(var(--bs-primary-rgb, 13,110,253), 0.1); letter-spacing: 3px; white-space: nowrap; border: 1px solid rgba(var(--bs-primary-rgb, 13,110,253), 0.2);"
            data-math-question>
            {{ $mathNum1 }} + {{ $mathNum2 }} = ?
        </span>
        <button type="button" class="btn btn-outline-secondary btn-sm" data-refresh-captcha
            title="{{ translate('Refresh_captcha') }}">&#8635;</button>
        <input type="number" class="form-control" name="default_captcha_value" placeholder="{{ translate('Answer') }}"
            min="0" max="18" autocomplete="off" required>
    </div>
    <script>
        (function() {
            var container = document.querySelector('[data-math-captcha-key]');
            if (!container) return;

            function refreshCaptcha() {
                var key = container.getAttribute('data-math-captcha-key');
                fetch('/captcha/math-refresh?session_key=' + encodeURIComponent(key), {
                        credentials: 'same-origin'
                    })
                    .then(function(r) {
                        return r.json();
                    })
                    .then(function(d) {
                        var q = container.querySelector('[data-math-question]');
                        var inp = container.querySelector('input[name="default_captcha_value"]');
                        if (q) q.textContent = d.num1 + ' + ' + d.num2 + ' = ?';
                        if (inp) inp.value = '';
                    });
            }
            window.addEventListener('pageshow', function(e) {
                if (e.persisted) {
                    refreshCaptcha();
                }
            });
            container.querySelector('[data-refresh-captcha]').addEventListener('click', refreshCaptcha);
        })();
    </script>

@endif
