<div class="second-el d--none">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <h3 class="mb-4">{{ translate('create_an_account') }}</h3>
                        <div class="border p-3 p-xl-4 rounded">
                            <h4 class="mb-3">{{ translate('vendor_information') }}</h4>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group mb-4">
                                        <label for="f_name">{{ translate('first_name') }} <span
                                                class="text-danger">*</span></label>
                                        <input class="form-control" type="text" name="f_name"
                                            placeholder="{{ translate('ex') . ': John' }}" required>
                                    </div>
                                    <div class="form-group mb-4">
                                        <label for="l_name">{{ translate('last_name') }} <span
                                                class="text-danger">*</span></label>
                                        <input class="form-control" type="text" name="l_name"
                                            placeholder="{{ translate('ex') . ': Doe' }}" required>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="d-flex flex-column gap-3 align-items-center">
                                        <div class="upload-file">
                                            <input type="file" class="upload-file__input" name="image"
                                                data-max-size="{{ getFileUploadMaxSize() }}"
                                                accept="{{ getFileUploadFormats(skip: '.svg') }}">
                                            <div class="upload-file__img">
                                                <div class="temp-img-box">
                                                    <div class="d-flex align-items-center flex-column gap-2">
                                                        <i class="tio-upload fs-30"></i>
                                                        <div class="fs-12 text-muted text-capitalize">
                                                            {{ translate('upload_file') }}</div>
                                                    </div>
                                                </div>
                                                <img src="#" class="dark-support img-fit-contain border"
                                                    alt="" hidden>
                                            </div>
                                        </div>

                                        <div class="d-flex flex-column gap-1 upload-img-content text-center">
                                            <h6 class="text-uppercase mb-1 fs-14">
                                                {{ translate('vendor_image') }}
                                                <span class="text-danger">*</span>
                                            </h6>
                                            <div class="text-muted text-capitalize fs-12">
                                                {{ translate('image_ratio') . ' ' . '1:1' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="border p-3 p-xl-4 rounded mt-4">
                            <h4 class="mb-3 text-capitalize">{{ translate('shop_information') }}</h4>

                            <div class="form-group mb-4">
                                <label for="store_name" class="text-capitalize">{{ translate('shop_Name') }} <span
                                        class="text-danger">*</span></label>
                                <input class="form-control" type="text" id="shop_name" name="shop_name"
                                    placeholder="{{ translate('Ex: XYZ store') }}" required>
                            </div>
                            <div class="form-group mb-4">
                                <label for="store_address" class="text-capitalize">{{ translate('shop_address') }}
                                    <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="shop_address" id="shop_address" rows="4"
                                    placeholder="{{ translate('shop_address') }}" required></textarea>
                            </div>

                            {{-- Location: Country, City & Area --}}
                            <div class="row">
                                <div class="col-sm-6 col-lg-4">
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
                                <div class="col-sm-6 col-lg-4">
                                    <div class="form-group mb-4">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="mb-0 text-capitalize" for="store_city_id">
                                                {{ translate('store_city') }}
                                            </label>
                                            <button type="button" class="btn btn-link p-0 fs-12 text-primary" data-toggle="modal"
                                                data-target="#regRequestCityModal" title="{{ translate('Request_New_City') }}">
                                                <i class="tio-send"></i> {{ translate('Request_city') }}
                                            </button>
                                        </div>
                                        <select class="form-control" name="store_city_id" id="reg_store_city_id" disabled>
                                            <option value="">{{ translate('select_city') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-lg-4">
                                    <div class="form-group mb-4">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="mb-0 text-capitalize" for="store_area_id">
                                                {{ translate('store_area') }}
                                            </label>
                                            <button type="button" class="btn btn-link p-0 fs-12 text-primary" data-toggle="modal"
                                                data-target="#regRequestAreaModal" title="{{ translate('Request_New_Area') }}">
                                                <i class="tio-send"></i> {{ translate('Request_area') }}
                                            </button>
                                        </div>
                                        <select class="form-control" name="store_area_id" id="reg_store_area_id" disabled>
                                            <option value="">{{ translate('select_area') }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="border p-3 p-xl-4 rounded mb-4">
                                <div class="d-flex flex-column gap-3 align-items-center">
                                    <div class="upload-file">
                                        <input type="file" class="upload-file__input" name="logo"
                                            data-max-size="{{ getFileUploadMaxSize() }}"
                                            accept="{{ getFileUploadFormats(skip: '.svg') }}">
                                        <div class="upload-file__img">
                                            <div class="temp-img-box">
                                                <div class="d-flex align-items-center flex-column gap-2">
                                                    <i class="tio-upload fs-30"></i>
                                                    <div class="fs-12 text-muted text-capitalize">
                                                        {{ translate('upload_file') }}</div>
                                                </div>
                                            </div>
                                            <img src="#" class="dark-support img-fit-contain border"
                                                alt="" hidden>
                                        </div>
                                    </div>

                                    <div class="d-flex flex-column gap-1 upload-img-content text-center">
                                        <h6 class="text-uppercase mb-1 fs-14">
                                            {{ translate('upload_logo') }}
                                            <span class="text-danger">*</span>
                                        </h6>
                                        <div class="text-muted text-capitalize fs-12">
                                            {{ translate('image_ratio') . ' ' . '1:1' }}</div>
                                        <div class="text-muted text-capitalize fs-12">
                                            {{ translate('Image Size : Max 2 MB') }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="border p-3 p-xl-4 rounded">
                                <div class="d-flex flex-column gap-3 align-items-center">
                                    <div class="upload-file">
                                        <input type="file" class="upload-file__input" name="banner"
                                            data-max-size="{{ getFileUploadMaxSize() }}"
                                            accept="{{ getFileUploadFormats(skip: '.svg') }}" required>
                                        <div class="upload-file__img style--two">
                                            <div class="temp-img-box">
                                                <div class="d-flex align-items-center flex-column gap-2">
                                                    <i class="tio-upload fs-30"></i>
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
                                        <h6 class="text-uppercase mb-1 fs-14">
                                            {{ translate('upload_banner') }}
                                            <span class="text-danger">*</span>
                                        </h6>
                                        <div class="text-muted text-capitalize fs-12">
                                            {{ translate('image_ratio') . ' ' . '2:1' }}</div>
                                        <div class="text-muted text-capitalize fs-12">
                                            {{ translate('Image Size : Max 2 MB') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="border p-3 p-xl-4 rounded mt-2">
                            <div class="row gy-4">
                                <div class="col-lg-6">
                                    <div>
                                        <h4 class="mb-3 text-capitalize">
                                            {{ translate('Business_TIN') }}
                                        </h4>
                                        <div class="form-group mb-4">
                                            <label class="mb-2 text-capitalize" for="">
                                                {{ translate('taxpayer_identification_number(TIN)') }}
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
                                                <span class="fi fi-sr-calendar icon-absolute-on-right"></span>
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
                                                    <img class="svg"
                                                        src="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/doc-upload-icon.svg') }}"
                                                        alt="">
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
                                            <h6 class="text-uppercase mb-1">
                                                {{ translate('TIN_Certificate') }}
                                            </h6>
                                            <div class="text-muted text-capitalize">
                                                {{ translate('pdf,_doc,_jpg._file_size_:_max_5_MB') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="my-2">

                            @if (isset($recaptcha) && $recaptcha['status'] == 1)
                                <div class="dynamic-default-and-recaptcha-section">
                                    <input type="hidden" name="g-recaptcha-response"
                                        class="render-grecaptcha-response" data-input="#login-default-captcha-section"
                                        data-default-captcha="#login-default-captcha-section" data-action="register">

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
                        </div>

                        <div class="d-flex justify-content-start mt-2">
                            <label class="custom-checkbox align-items-center">
                                <input type="checkbox" class="" id="terms-checkbox">
                                <span class="form-check-label">
                                    {{ translate('i_agree_with_the') }}
                                    <a href="{{ route('business-page.view', ['slug' => 'terms-and-conditions']) }}"
                                        target="_blank" class="text-underline color-bs-primary-force">
                                        {{ translate('terms_&_conditions') }}
                                    </a>
                                </span>
                            </label>
                        </div>
                        <div class="d-flex justify-content-end mb-2 gap-2">
                            <button type="button" class="btn btn-secondary back-to-main-page">
                                {{ translate('back') }} </button>
                            <button type="submit" class="btn btn--primary" id="vendor-apply-submit"
                                disabled="disabled"> {{ translate('submit') }} </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Request City Modal --}}
<div class="modal fade" id="regRequestCityModal" tabindex="-1" aria-labelledby="regRequestCityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="regRequestCityModalLabel">{{ translate('Request_New_City') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">{{ translate('if_the_city_you_need_is_not_listed_request_admin_to_add_it') }}</p>
                <div class="form-group mb-3">
                    <label class="mb-2">{{ translate('Country') }} <span class="text-danger">*</span></label>
                    <select id="rc_reg_country_id" class="form-control">
                        <option value="">{{ translate('select_country') }}</option>
                    </select>
                </div>
                <div class="form-group mb-3">
                    <label class="mb-2">{{ translate('City_Name') }} <span class="text-danger">*</span></label>
                    <input type="text" id="rc_reg_city_name" class="form-control"
                        placeholder="{{ translate('e.g._New_York') }}">
                </div>
                <div id="reg-request-city-feedback" class="mt-2 d-none">
                    <span class="text-success" id="reg-request-city-success-msg"></span>
                    <span class="text-danger" id="reg-request-city-error-msg"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ translate('Cancel') }}</button>
                <button type="button" class="btn btn-primary" id="regRequestCitySaveBtn">
                    <span>{{ translate('Submit_Request') }}</span>
                    <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Request Area Modal --}}
<div class="modal fade" id="regRequestAreaModal" tabindex="-1" aria-labelledby="regRequestAreaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="regRequestAreaModalLabel">{{ translate('Request_New_Area') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">{{ translate('if_the_area_you_need_is_not_listed_request_admin_to_add_it') }}</p>
                <div class="form-group mb-3">
                    <label class="mb-2">{{ translate('Country') }} <span class="text-danger">*</span></label>
                    <select id="ra_reg_country_id" class="form-control">
                        <option value="">{{ translate('select_country') }}</option>
                    </select>
                </div>
                <div class="form-group mb-3">
                    <label class="mb-2">{{ translate('City') }} <span class="text-danger">*</span></label>
                    <select id="ra_reg_city_id" class="form-control" disabled>
                        <option value="">{{ translate('select_city') }}</option>
                    </select>
                </div>
                <div class="form-group mb-3">
                    <label class="mb-2">{{ translate('Area_Name') }} <span class="text-danger">*</span></label>
                    <input type="text" id="ra_reg_area_name" class="form-control"
                        placeholder="{{ translate('e.g._Downtown') }}">
                </div>
                <div id="reg-request-area-feedback" class="mt-2 d-none">
                    <span class="text-success" id="reg-request-area-success-msg"></span>
                    <span class="text-danger" id="reg-request-area-error-msg"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ translate('Cancel') }}</button>
                <button type="button" class="btn btn-primary" id="regRequestAreaSaveBtn">
                    <span>{{ translate('Submit_Request') }}</span>
                    <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                </button>
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
        var areasUrlTemplate = '{{ route('vendor.auth.registration.location-areas', ':id') }}';
        var requestCityUrl = '{{ route('vendor.auth.registration.request-city') }}';
        var requestAreaUrl = '{{ route('vendor.auth.registration.request-area') }}';

        var allCountriesCache = [];

        function initLocationSelect2($select) {
            if ($select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy');
            }
            var opts = { width: '100%' };
            var $modal = $select.closest('.modal');
            if ($modal.length) {
                opts.dropdownParent = $modal;
            }
            $select.select2(opts);
        }

        function loadRegistrationCountries() {
            var $select = $('#reg_store_country_id');
            $.getJSON(countriesUrl, function(data) {
                allCountriesCache = data;
                $select.empty().append($('<option>', { value: '', text: '{{ translate('select_country') }}' }));
                $.each(data, function(i, item) {
                    $select.append($('<option>', { value: item.id, text: item.name }));
                });
                initLocationSelect2($select);
            });
        }

        $(document).on('change', '#reg_store_country_id', function() {
            var countryId = $(this).val();
            var $citySelect = $('#reg_store_city_id');
            var $areaSelect = $('#reg_store_area_id');
            if ($citySelect.hasClass('select2-hidden-accessible')) $citySelect.select2('destroy');
            if ($areaSelect.hasClass('select2-hidden-accessible')) $areaSelect.select2('destroy');
            $citySelect.html('<option value="">{{ translate('select_city') }}</option>').prop('disabled', true);
            $areaSelect.html('<option value="">{{ translate('select_area') }}</option>').prop('disabled', true);
            initLocationSelect2($citySelect);
            initLocationSelect2($areaSelect);
            if (!countryId) return;

            $citySelect.html('<option value="">{{ translate('Loading...') }}</option>');
            $.getJSON(citiesUrlTemplate.replace(':id', countryId), function(data) {
                $citySelect.empty().append($('<option>', { value: '', text: '{{ translate('select_city') }}' }));
                $.each(data, function(i, item) {
                    $citySelect.append($('<option>', { value: item.id, text: item.name }));
                });
                $citySelect.prop('disabled', false);
                initLocationSelect2($citySelect);
            });
        });

        $(document).on('change', '#reg_store_city_id', function() {
            var cityId = $(this).val();
            var $areaSelect = $('#reg_store_area_id');
            if ($areaSelect.hasClass('select2-hidden-accessible')) $areaSelect.select2('destroy');
            $areaSelect.html('<option value="">{{ translate('select_area') }}</option>').prop('disabled', true);
            initLocationSelect2($areaSelect);
            if (!cityId) return;

            $areaSelect.html('<option value="">{{ translate('Loading...') }}</option>');
            $.getJSON(areasUrlTemplate.replace(':id', cityId), function(data) {
                $areaSelect.empty().append($('<option>', { value: '', text: '{{ translate('select_area') }}' }));
                $.each(data, function(i, item) {
                    $areaSelect.append($('<option>', { value: item.id, text: item.name }));
                });
                $areaSelect.prop('disabled', false);
                initLocationSelect2($areaSelect);
            });
        });

        // ── Request City Modal ───────────────────────────────────────────────
        $('#regRequestCityModal').on('show.bs.modal', function() {
            var $modalCountry = $('#rc_reg_country_id');
            $modalCountry.empty().append($('<option>', { value: '', text: '{{ translate('select_country') }}' }));
            $.each(allCountriesCache, function(i, item) {
                $modalCountry.append($('<option>', {
                    value: item.id,
                    text: item.name,
                    selected: (String(item.id) === String($('#reg_store_country_id').val()))
                }));
            });            initLocationSelect2($modalCountry);            $('#rc_reg_city_name').val('');
            $('#reg-request-city-feedback').addClass('d-none');
            $('#reg-request-city-success-msg, #reg-request-city-error-msg').text('');
        });

        $('#regRequestCitySaveBtn').on('click', function() {
            var $btn = $(this);
            var countryId = $('#rc_reg_country_id').val();
            var cityName = $.trim($('#rc_reg_city_name').val());

            $('#reg-request-city-feedback').addClass('d-none');
            $('#reg-request-city-success-msg, #reg-request-city-error-msg').text('');

            if (!countryId) {
                $('#reg-request-city-error-msg').text('{{ translate('Please_select_a_country') }}');
                $('#reg-request-city-feedback').removeClass('d-none');
                return;
            }
            if (!cityName) {
                $('#reg-request-city-error-msg').text('{{ translate('City_name_is_required') }}');
                $('#reg-request-city-feedback').removeClass('d-none');
                return;
            }

            $btn.find('.spinner-border').removeClass('d-none');
            $btn.find('span:first').text('{{ translate('Submitting...') }}');

            $.post(requestCityUrl, {
                country_id: countryId,
                city_name: cityName,
                _token: '{{ csrf_token() }}'
            })
            .done(function(response) {
                if (response.success) {
                    $('#reg-request-city-success-msg').text(response.message);
                    $('#reg-request-city-feedback').removeClass('d-none');
                    setTimeout(function() {
                        $('#regRequestCityModal').modal('hide');
                    }, 1500);
                } else {
                    $('#reg-request-city-error-msg').text(response.message || '{{ translate('Something_went_wrong') }}');
                    $('#reg-request-city-feedback').removeClass('d-none');
                }
            })
            .fail(function(xhr) {
                var errors = xhr.responseJSON && xhr.responseJSON.errors ?
                    Object.values(xhr.responseJSON.errors).flat().join(' ') :
                    '{{ translate('Something_went_wrong') }}';
                $('#reg-request-city-error-msg').text(errors);
                $('#reg-request-city-feedback').removeClass('d-none');
            })
            .always(function() {
                $btn.find('.spinner-border').addClass('d-none');
                $btn.find('span:first').text('{{ translate('Submit_Request') }}');
            });
        });

        // ── Request Area Modal ───────────────────────────────────────────────
        $('#regRequestAreaModal').on('show.bs.modal', function() {
            var $modalCountry = $('#ra_reg_country_id');
            var $modalCity = $('#ra_reg_city_id');
            $modalCountry.empty().append($('<option>', { value: '', text: '{{ translate('select_country') }}' }));
            $.each(allCountriesCache, function(i, item) {
                $modalCountry.append($('<option>', {
                    value: item.id,
                    text: item.name,
                    selected: (String(item.id) === String($('#reg_store_country_id').val()))
                }));
            });
            initLocationSelect2($modalCountry);

            // If a country is already selected, pre-load cities
            var preSelectedCountry = $('#reg_store_country_id').val();
            if (preSelectedCountry) {
                $modalCity.html('<option value="">{{ translate('Loading...') }}</option>');
                $.getJSON(citiesUrlTemplate.replace(':id', preSelectedCountry), function(data) {
                    $modalCity.empty().append($('<option>', { value: '', text: '{{ translate('select_city') }}' }));
                    $.each(data, function(i, item) {
                        $modalCity.append($('<option>', {
                            value: item.id,
                            text: item.name,
                            selected: (String(item.id) === String($('#reg_store_city_id').val()))
                        }));
                    });
                    $modalCity.prop('disabled', false);
                    initLocationSelect2($modalCity);
                });
            } else {
                $modalCity.html('<option value="">{{ translate('select_city') }}</option>').prop('disabled', true);
                initLocationSelect2($modalCity);
            }

            $('#ra_reg_area_name').val('');
            $('#reg-request-area-feedback').addClass('d-none');
            $('#reg-request-area-success-msg, #reg-request-area-error-msg').text('');
        });

        $(document).on('change', '#ra_reg_country_id', function() {
            var countryId = $(this).val();
            var $modalCity = $('#ra_reg_city_id');
            if ($modalCity.hasClass('select2-hidden-accessible')) $modalCity.select2('destroy');
            $modalCity.html('<option value="">{{ translate('select_city') }}</option>').prop('disabled', true);
            initLocationSelect2($modalCity);
            if (!countryId) return;

            $modalCity.html('<option value="">{{ translate('Loading...') }}</option>');
            $.getJSON(citiesUrlTemplate.replace(':id', countryId), function(data) {
                $modalCity.empty().append($('<option>', { value: '', text: '{{ translate('select_city') }}' }));
                $.each(data, function(i, item) {
                    $modalCity.append($('<option>', { value: item.id, text: item.name }));
                });
                $modalCity.prop('disabled', false);
                initLocationSelect2($modalCity);
            });
        });

        $('#regRequestAreaSaveBtn').on('click', function() {
            var $btn = $(this);
            var cityId = $('#ra_reg_city_id').val();
            var areaName = $.trim($('#ra_reg_area_name').val());

            $('#reg-request-area-feedback').addClass('d-none');
            $('#reg-request-area-success-msg, #reg-request-area-error-msg').text('');

            if (!cityId) {
                $('#reg-request-area-error-msg').text('{{ translate('Please_select_a_city') }}');
                $('#reg-request-area-feedback').removeClass('d-none');
                return;
            }
            if (!areaName) {
                $('#reg-request-area-error-msg').text('{{ translate('Area_name_is_required') }}');
                $('#reg-request-area-feedback').removeClass('d-none');
                return;
            }

            $btn.find('.spinner-border').removeClass('d-none');
            $btn.find('span:first').text('{{ translate('Submitting...') }}');

            $.post(requestAreaUrl, {
                city_id: cityId,
                area_name: areaName,
                _token: '{{ csrf_token() }}'
            })
            .done(function(response) {
                if (response.success) {
                    $('#reg-request-area-success-msg').text(response.message);
                    $('#reg-request-area-feedback').removeClass('d-none');
                    setTimeout(function() {
                        $('#regRequestAreaModal').modal('hide');
                    }, 1500);
                } else {
                    $('#reg-request-area-error-msg').text(response.message || '{{ translate('Something_went_wrong') }}');
                    $('#reg-request-area-feedback').removeClass('d-none');
                }
            })
            .fail(function(xhr) {
                var errors = xhr.responseJSON && xhr.responseJSON.errors ?
                    Object.values(xhr.responseJSON.errors).flat().join(' ') :
                    '{{ translate('Something_went_wrong') }}';
                $('#reg-request-area-error-msg').text(errors);
                $('#reg-request-area-feedback').removeClass('d-none');
            })
            .always(function() {
                $btn.find('.spinner-border').addClass('d-none');
                $btn.find('span:first').text('{{ translate('Submit_Request') }}');
            });
        });

        loadRegistrationCountries();
    }());
</script>
@endpush
