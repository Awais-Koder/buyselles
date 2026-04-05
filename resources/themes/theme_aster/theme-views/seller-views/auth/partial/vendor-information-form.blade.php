<div class="second-el d--none">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <h3 class="mb-4 text-capitalize">{{ translate('create_an_account') }}</h3>
                        <div class="border p-3 p-xl-4 rounded">
                            <h4 class="mb-3 text-capitalize">{{ translate('vendor_information') }}</h4>

                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group mb-4">
                                        <label class="mb-2 text-capitalize" for="f_name">{{ translate('first_name') }}
                                            <span class="text-danger">*</span></label>
                                        <input class="form-control" type="text" name="f_name"
                                            placeholder="{{ translate('ex') . ': John' }}" required>
                                    </div>
                                    <div class="form-group mb-4">
                                        <label class="mb-2 text-capitalize" for="l_name">
                                            {{ translate('last_name') }}
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input class="form-control" type="text" name="l_name"
                                            placeholder="{{ translate('ex') . ': Doe' }}" required>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="">
                                        <div class="d-flex flex-column gap-3 align-items-center">
                                            <div class="upload-file">
                                                <input type="file" class="upload-file__input" name="image"
                                                    data-max-size="2"
                                                    accept="{{ getFileUploadFormats(skip: '.svg,.webp,.gif') }}">
                                                <div class="upload-file__img">
                                                    <div class="temp-img-box">
                                                        <div class="d-flex align-items-center flex-column gap-2">
                                                            <i class="bi bi-upload fs-30"></i>
                                                            <div class="fs-12 text-muted text-capitalize">
                                                                {{ translate('upload_file') }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <img src="#" class="dark-support img-fit-contain border"
                                                        alt="" hidden>
                                                </div>
                                            </div>

                                            <div class="d-flex flex-column gap-1 upload-img-content text-center">
                                                <h6 class="text-uppercase mb-1">
                                                    {{ translate('vendor_image') }}
                                                    <span class="text-danger">*</span>
                                                </h6>
                                                <div class="text-muted text-capitalize">
                                                    {{ translate('image_ratio') . ' ' . '1:1' }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="border p-3 p-xl-4 rounded mt-4">
                            <h4 class="mb-3 text-capitalize">{{ translate('shop_information') }}</h4>
                            <div class="form-group mb-4">
                                <label class="mb-2 text-capitalize" for="store_name">
                                    {{ translate('store_name') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <input class="form-control" type="text" name="shop_name"
                                    placeholder="{{ translate('ex') . ': XYZ store' }}" required>
                            </div>
                            <div class="form-group mb-4">
                                <label class="mb-2 text-capitalize" for="store_address">
                                    {{ translate('store_address') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control" name="shop_address" rows="4" placeholder="{{ translate('store_address') }}"
                                    required></textarea>
                            </div>

                            {{-- Location: Country & City --}}
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group mb-4">
                                        <label class="mb-2 text-capitalize" for="store_country_id">
                                            {{ translate('store_country') }}
                                            <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-control" name="store_country_id" id="reg_store_country_id" required>
                                            <option value="">{{ translate('select_country') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group mb-4">
                                        <label class="mb-2 text-capitalize" for="store_city_id">
                                            {{ translate('store_city') }}
                                        </label>
                                        <select class="form-control" name="store_city_id" id="reg_store_city_id" disabled>
                                            <option value="">{{ translate('select_city') }}</option>
                                        </select>
                                        <small class="text-muted">{{ translate('if_your_city_is_not_listed_you_can_request_it_after_registration_from_your_dashboard') }}</small>
                                    </div>
                                </div>
                            </div>

                            <div class="border p-3 p-xl-4 rounded mb-4">
                                <div class="d-flex flex-column gap-3 align-items-center">
                                    <div class="upload-file">
                                        <input type="file" class="upload-file__input" name="logo"
                                            data-max-size="{{ getFileUploadMaxSize() }}"
                                            accept="{{ getFileUploadFormats(skip: '.svg,.gif,.webp') }}">
                                        <div class="upload-file__img">
                                            <div class="temp-img-box">
                                                <div class="d-flex align-items-center flex-column gap-2">
                                                    <i class="bi bi-upload fs-30"></i>
                                                    <div class="fs-12 text-muted text-capitalize">
                                                        {{ translate('upload_file') }}
                                                    </div>
                                                </div>
                                            </div>
                                            <img src="#" class="dark-support img-fit-contain border"
                                                alt="" hidden>
                                        </div>
                                    </div>

                                    <div class="d-flex flex-column gap-1 upload-img-content text-center">
                                        <h6 class="text-uppercase mb-1">
                                            {{ translate('store_image') }}
                                            <span class="text-danger">*</span>
                                        </h6>
                                        <div class="text-muted text-capitalize">
                                            {{ translate('image_ratio') . ' ' . '1:1' }}
                                        </div>
                                        <div class="text-muted text-capitalize">
                                            {{ translate('Image Size : Max 2 MB') }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="border p-3 p-xl-4 rounded mb-4">
                                <div class="d-flex flex-column gap-3 align-items-center">
                                    <div class="upload-file">
                                        <input type="file" class="upload-file__input" name="banner"
                                            data-max-size="{{ getFileUploadMaxSize() }}"
                                            accept="{{ getFileUploadFormats(skip: '.svg,.gif') }}">
                                        <div class="upload-file__img style--two">
                                            <div class="temp-img-box">
                                                <div class="d-flex align-items-center flex-column gap-2">
                                                    <i class="bi bi-upload fs-30"></i>
                                                    <div class="fs-12 text-muted text-capitalize">
                                                        {{ translate('upload_file') }}
                                                    </div>
                                                </div>
                                            </div>
                                            <img src="#" class="dark-support img-fit-contain border"
                                                alt="" hidden>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column gap-1 upload-img-content text-center">
                                        <h6 class="text-uppercase mb-1">
                                            {{ translate('store_banner') }}
                                            <span class="text-danger">*</span>
                                        </h6>
                                        <div class="text-muted text-capitalize">
                                            {{ translate('image_ratio') . ' ' . '1:1' }}
                                        </div>
                                        <div class="text-muted text-capitalize">
                                            {{ translate('Image Size : Max 2 MB') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="border p-3 p-xl-4 rounded mb-4">
                                <div class="d-flex flex-column gap-3 align-items-center">
                                    <div class="upload-file">
                                        <input type="file" class="upload-file__input" name="bottom_banner"
                                            data-max-size="{{ getFileUploadMaxSize() }}"
                                            accept="{{ getFileUploadFormats(skip: '.svg,.gif') }}">
                                        <div class="upload-file__img style--two">
                                            <div class="temp-img-box">
                                                <div class="d-flex align-items-center flex-column gap-2">
                                                    <i class="bi bi-upload fs-30"></i>
                                                    <div class="fs-12 text-muted text-capitalize">
                                                        {{ translate('upload_file') }}
                                                    </div>
                                                </div>
                                            </div>
                                            <img src="#" class="dark-support img-fit-contain border"
                                                alt="" hidden>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column gap-1 upload-img-content text-center">
                                        <h6 class="text-uppercase mb-1">
                                            {{ translate('store_secondary_banner') }}
                                            <span class="text-danger">*</span>
                                        </h6>
                                        <div class="text-muted text-capitalize">
                                            {{ translate('image_ratio') . ' ' . '1:1' }}
                                        </div>
                                        <div class="text-muted text-capitalize">
                                            {{ translate('Image Size : Max 2 MB') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="border p-3 p-xl-4 rounded">
                                <div class="row gy-4">
                                    <div class="col-lg-6">
                                        <div>
                                            <h4 class="mb-3 text-capitalize">{{ translate('Business_TIN') }}</h4>
                                            <div class="form-group mb-4">
                                                <label class="mb-2 text-capitalize"
                                                    for="">{{ translate('taxpayer_identification_number(TIN)') }}
                                                </label>
                                                <input class="form-control" type="text"
                                                    name="tax_identification_number"
                                                    placeholder="{{ translate('type_your_user_name') }}">
                                            </div>
                                            <div class="form-group mb-4">
                                                <label class="mb-2 text-capitalize" for="">
                                                    {{ translate('Expire_Date') }}
                                                </label>
                                                <div class="position-relative">
                                                    <span class="bi bi-calendar icon-absolute-on-right"></span>
                                                    <input type="text"
                                                        class="js-daterangepicker_single-date-with-placeholder form-control"
                                                        placeholder="{{ translate('click_to_add_date') }}"
                                                        name="tin_expire_date" value="" readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group mb-0">
                                            <div class="d-flex justify-content-center document-upload-container">
                                                <div class="document-file-assets"
                                                    data-picture-icon="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/picture.svg') }}"
                                                    data-document-icon="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/document.svg') }}"
                                                    data-blank-thumbnail="{{ dynamicAsset(path: 'public/assets/back-end/img/file-placeholder.png') }}">
                                                </div>
                                                <div class="document-upload-wrapper doc-upload-wrapper">
                                                    <input type="file" name="tin_certificate"
                                                        data-max-size="{{ getFileUploadMaxSize(type: 'file') }}"
                                                        data-validation-error-msg="{{ translate('File_size_is_too_large_Maximum_') . ' ' . getFileUploadMaxSize(type: 'file') . ' ' . 'MB' }}"
                                                        class="document_input" accept=".pdf,.doc,.jpg">
                                                    <div class="textbox">
                                                        <img class="svg" alt=""
                                                            src="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/doc-upload-icon.svg') }}">
                                                        <p class="fs-12 mb-0">
                                                            {{ translate('Select_a_file_or') }}
                                                            <span class="font-weight-semibold">
                                                                {{ translate('Drag_and_Drop_here') }}
                                                            </span>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="d-flex flex-column gap-1 upload-img-content text-center mt-3">
                                                <h6 class="text-uppercase mb-1">{{ translate('TIN_Certificate') }}
                                                </h6>
                                                <div class="text-muted text-capitalize">
                                                    {{ translate('pdf,_doc,_jpg._file_size_:_max_5_MB') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if (isset($recaptcha) && $recaptcha['status'] == 1)
                            <div class="dynamic-default-and-recaptcha-section">
                                <input type="hidden" name="g-recaptcha-response" class="render-grecaptcha-response"
                                    data-action="register" data-input="#login-default-captcha-section"
                                    data-default-captcha="#login-default-captcha-section">

                                <div class="default-captcha-container d-none" id="login-default-captcha-section"
                                    data-placeholder="{{ translate('enter_captcha_value') }}"
                                    data-base-url="{{ route('g-recaptcha-session-store') }}"
                                    data-session="{{ 'vendorRecaptchaSessionKey' }}">
                                </div>
                            </div>
                        @else
                            <div class="d-flex align-items-center gap-3 mt-2"
                                data-math-captcha-key="vendorRecaptchaSessionKey">
                                <span class="fs-5 fw-bold user-select-none px-3 py-2 rounded"
                                    style="background: rgba(var(--bs-primary-rgb, 13,110,253), 0.1); letter-spacing: 3px; white-space: nowrap; border: 1px solid rgba(var(--bs-primary-rgb, 13,110,253), 0.2);"
                                    data-math-question>
                                    {{ $mathNum1 }} + {{ $mathNum2 }} = ?
                                </span>
                                <input type="number" class="form-control" name="default_captcha_value"
                                    placeholder="{{ translate('Answer') }}" min="0" max="18"
                                    autocomplete="off" required>
                            </div>
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    var c = document.querySelector('[data-math-captcha-key="vendorRecaptchaSessionKey"]');
                                    if (!c) return;
                                    fetch('/captcha/math-refresh?session_key=vendorRecaptchaSessionKey', {
                                            credentials: 'same-origin'
                                        })
                                        .then(function(r) {
                                            return r.json();
                                        })
                                        .then(function(d) {
                                            var q = c.querySelector('[data-math-question]');
                                            var inp = c.querySelector('input[name="default_captcha_value"]');
                                            if (q) q.textContent = d.num1 + ' + ' + d.num2 + ' = ?';
                                            if (inp) inp.value = '';
                                        });
                                });
                            </script>
                        @endif

                        <div class="col-12">
                            <label class="custom-checkbox align-items-center">
                                <input type="checkbox" class="form-check-input" id="terms-checkbox">
                                <span class="form-check-label">
                                    {{ translate('i_agree_with_the') }}
                                    <a href="{{ route('business-page.view', ['slug' => 'terms-and-conditions']) }}"
                                        target="_blank" class="text-decoration-underline color-bs-primary-force">
                                        {{ translate('terms_&_conditions') }}
                                    </a>
                                </span>
                            </label>
                        </div>
                        <div class="d-flex justify-content-end mt-4 mb-2 gap-2">
                            <button type="button" class="btn btn-secondary back-to-main-page">
                                {{ translate('back') }}
                            </button>
                            <button type="submit" class="btn btn-primary disabled" id="vendor-apply-submit">
                                {{ translate('submit') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('script')
<script>
    (function() {
        'use strict';

        var countriesUrl = '{{ route('vendor.auth.registration.location-countries') }}';
        var citiesUrlTemplate = '{{ route('vendor.auth.registration.location-cities', ':id') }}';

        function loadRegistrationCountries() {
            var $select = $('#reg_store_country_id');
            $.getJSON(countriesUrl, function(data) {
                $select.empty().append($('<option>', { value: '', text: '{{ translate('select_country') }}' }));
                $.each(data, function(i, item) {
                    $select.append($('<option>', { value: item.id, text: item.name }));
                });
            });
        }

        $(document).on('change', '#reg_store_country_id', function() {
            var countryId = $(this).val();
            var $citySelect = $('#reg_store_city_id');
            $citySelect.html('<option value="">{{ translate('select_city') }}</option>').prop('disabled', true);
            if (!countryId) return;

            $citySelect.html('<option value="">{{ translate('Loading...') }}</option>');
            $.getJSON(citiesUrlTemplate.replace(':id', countryId), function(data) {
                $citySelect.empty().append($('<option>', { value: '', text: '{{ translate('select_city') }}' }));
                $.each(data, function(i, item) {
                    $citySelect.append($('<option>', { value: item.id, text: item.name }));
                });
                $citySelect.prop('disabled', false);
            });
        });

        loadRegistrationCountries();
    }());
</script>
@endpush
