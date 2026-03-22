@extends('layouts.front-end.app')

@section('title', translate('forgot_Password'))
@section('content')
    <div class="container py-4 py-lg-5 my-4 rtl">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10 text-start">
                <h2 class="h3 mb-4">{{ translate('forgot_your_password') }}?</h2>
                <p class="font-size-md">
                    {{ translate('change_your_password_in_three_easy_steps.') }}
                    {{ translate('this_helps_to_keep_your_new_password_secure.') }}
                </p>
                <ol class="list-unstyled font-size-md p-0">
                    <li>
                        <span class="text-primary mr-2">{{ translate('1') }}.</span>
                        @if ($verification_by === 'email')
                            {{ translate('use_your_registered_email.') }}
                        @else
                            {{ translate('use_your_registered_phone.') }}
                        @endif
                    </li>
                    <li>
                        <span class="text-primary mr-2">{{ translate('2') }}.</span>
                        @if ($verification_by === 'email')
                            {{ translate('we_will_send_you_a_password_reset_link_to_your_email') }}.
                        @else
                            {{ translate('we_will_send_you_a_temporary_OTP_in_your_phone') }}.
                        @endif
                    </li>
                    <li>
                        <span class="text-primary mr-2">{{ translate('3') }}.</span>
                        @if ($verification_by === 'email')
                            {{ translate('use_the_link_to_reset_your_password_on_our_secure_website.') }}
                        @else
                            {{ translate('use_the_OTP_code_to_change_your_password_on_our_secure_website.') }}
                        @endif
                    </li>
                </ol>

                <div class="card py-2 mt-4">
                    <form class="card-body needs-validation" action="{{ route('customer.auth.forgot-password') }}"
                        method="post" id="customer-forgot-password-form">
                        @csrf
                        <div class="form-group">
                            @if ($verification_by === 'email')
                                <label for="recover-email">{{ translate('Email') }}</label>
                                <input class="form-control" type="email" name="identity" id="recover-email" required
                                    placeholder="{{ translate('enter_your_email_address') }}">
                                <div class="invalid-feedback">
                                    {{ translate('please_provide_valid_email') }}
                                </div>
                            @else
                                <label for="recover-phone">{{ translate('Phone') }}</label>
                                <input class="form-control clean-phone-input-value" type="text" name="identity"
                                    id="recover-phone" required placeholder="{{ translate('enter_your_phone_number') }}">
                                <span class="fs-12 text-muted">*
                                    {{ translate('must_use_country_code_before_phone_number') }}</span>
                                <div class="invalid-feedback">
                                    {{ translate('please_provide_valid_phone_number') }}
                                </div>
                            @endif
                        </div>

                        @if ($web_config['firebase_otp_verification'] && $web_config['firebase_otp_verification']['status'])
                            <div class="generate-firebase-auth-recaptcha"
                                id="firebase-auth-recaptcha-{{ rand(111, 999) }}"></div>
                        @elseif(isset($recaptcha) && $recaptcha['status'] == 1)
                            <div class="dynamic-default-and-recaptcha-section">
                                <input type="hidden" name="g-recaptcha-response" class="render-grecaptcha-response"
                                    data-input="#login-default-captcha-section"
                                    data-default-captcha="#login-default-captcha-section" data-action="customer_auth">

                                <div class="default-captcha-container d-none" id="login-default-captcha-section"
                                    data-placeholder="{{ translate('enter_captcha_value') }}"
                                    data-base-url="{{ route('g-recaptcha-session-store') }}"
                                    data-session="{{ 'default_recaptcha_id_customer_auth' }}">
                                </div>
                            </div>
                        @else
                            <div class="d-flex align-items-center gap-3 mt-2 mb-2">
                                <span class="fs-5 fw-bold user-select-none px-3 py-2 rounded"
                                    style="background: rgba(var(--bs-primary-rgb, 13,110,253), 0.1); letter-spacing: 3px; white-space: nowrap; border: 1px solid rgba(var(--bs-primary-rgb, 13,110,253), 0.2);">
                                    {{ $mathNum1 }} + {{ $mathNum2 }} = ?
                                </span>
                                <input type="number" class="form-control" name="default_captcha_value"
                                    placeholder="{{ translate('Answer') }}" min="0" max="18"
                                    autocomplete="off" required>
                            </div>
                        @endif

                        <button class="btn btn--primary" type="submit">
                            @if ($verification_by === 'email')
                                {{ translate('send_reset_link') }}
                            @else
                                {{ translate('send_OTP') }}
                            @endif
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
