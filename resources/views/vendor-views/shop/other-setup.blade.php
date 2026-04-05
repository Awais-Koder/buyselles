<?php
$minimumOrderAmountStatus = getWebConfig(name: 'minimum_order_amount_status');
$minimumOrderAmountByVendor = getWebConfig(name: 'minimum_order_amount_by_seller');
$freeDeliveryStatus = getWebConfig(name: 'free_delivery_status');
$freeDeliveryResponsibility = getWebConfig(name: 'free_delivery_responsibility');
?>

@extends('layouts.vendor.app')

@section('title', translate('shop_view'))

@section('content')
    <div class="content container-fluid">
        <h2 class="h1 mb-0 text-capitalize d-flex mb-3">
            {{ translate('shop_info') }}
        </h2>

        @include('vendor-views.shop.inline-menu')

        <form action="{{ route('vendor.shop.update-other-settings') }}" method="post" enctype="multipart/form-data"
            class="form-advance-validation  form-advance-inputs-validation form-advance-file-validation non-ajax-form-validate"
            novalidate="novalidate">
            @csrf
            <div class="card card-body mb-3">
                <div class="mb-4">
                    <h3 class="mb-1">{{ translate('Order_Setup') }}</h3>
                    <p class="fs-12 mb-0">
                        {{ translate('configure_how_the_minimum_order_amount,_free_delivery,_and_reorder_settings_will_work_for_customers_.') }}
                    </p>
                </div>

                <div class="bg-light p-3 rounded mb-3">
                    <div class="row gy-3">
                        @if ($minimumOrderAmountStatus && $minimumOrderAmountByVendor)
                            <div class="col-lg-4 col-sm-6">
                                <div class="form-group mb-0">
                                    <label class="form-label text-dark">
                                        {{ translate('minimum_order_amount') }}
                                        ({{ getCurrencySymbol() }})
                                        <i class="fi fi-sr-info cursor-pointer text-muted" data-toggle="tooltip"
                                            title="{{ translate('define_the_minimum_order_amount_required_for_customers_to_place_an_order_for_your_products') }}"></i>
                                    </label>
                                    <input type="number" step="any" class="form-control w-100"
                                        id="minimum_order_amount" name="minimum_order_amount" min="0"
                                        value="{{ usdToDefaultCurrency(amount: $vendor->minimum_order_amount) ?? 0 }}"
                                        placeholder="{{ translate('Ex') }}: {{ '300' }}">
                                </div>
                            </div>
                        @endif

                        @if ($freeDeliveryStatus && $freeDeliveryResponsibility == 'seller')
                            <div class="col-lg-4 col-sm-6">
                                <div class="form-group mb-0">
                                    <div class="d-flex justify-content-between align-items-center gap-2">
                                        <label class="form-label text-dark">
                                            {{ translate('free_delivery_over_amount') }}
                                            ({{ getCurrencySymbol() }})
                                            <i class="fi fi-sr-info cursor-pointer text-muted" data-toggle="tooltip"
                                                title="{{ translate('set_the_order_amount_for_free_delivery._customers_will_receive_free_delivery_on_orders_reaching_this_value') }}"></i>
                                        </label>
                                        <label class="switcher" for="free-delivery-status">
                                            <input type="checkbox" class="switcher_input toggle-switch-message"
                                                name="free_delivery_status" id="free-delivery-status"
                                                {{ $vendor['free_delivery_status'] == 1 ? 'checked' : '' }}
                                                data-modal-id = "toggle-modal" data-toggle-id = "free-delivery-status"
                                                data-on-image = "free-delivery-on.png"
                                                data-off-image = "free-delivery-on.png"
                                                data-on-title = "{{ translate('want_to_Turn_ON_Free_Delivery') }}"
                                                data-off-title = "{{ translate('want_to_Turn_OFF_Free_Delivery') }}"
                                                data-on-message = "<p>{{ translate('if_enabled_the_free_delivery_feature_will_be_shown_from_the_system') }}</p>"
                                                data-off-message = "<p>{{ translate('if_disabled_the_free_delivery_feature_will_be_hidden_from_the_system') }}</p>">
                                            <span class="switcher_control"></span>
                                        </label>
                                    </div>
                                    <input type="number" class="form-control" name="free_delivery_over_amount"
                                        id="free-delivery-over-amount" min="0"
                                        placeholder="{{ translate('ex') . ':' . translate('10') }}"
                                        value="{{ usdToDefaultCurrency($vendor['free_delivery_over_amount']) ?? 0 }}">
                                </div>
                            </div>
                        @endif

                        <div class="col-lg-4 col-sm-6">
                            <div class="form-group mb-0">
                                <label class="form-label text-dark">
                                    {{ translate('Re-order_Level') }}
                                    <i class="fi fi-sr-info cursor-pointer text-muted" data-toggle="tooltip"
                                        title="{{ translate('set_the_stock_alert_level_the_system_will_notify_when_your_product_stock_gets_down_to_this_number') }}"></i>
                                </label>
                                <input type="number" class="form-control"
                                    placeholder="{{ translate('Ex') }}: {{ '$100' }}"
                                    value="{{ $vendor?->stock_limit ?? 0 }}" name="stock_limit">
                                <small class="text-muted">
                                    {{ translate('set_the_stock_limit_for_the_reorder_level.') }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-body mb-3">
                <div class="mb-4">
                    <h3 class="mb-1">{{ translate('Store_Location') }}</h3>
                    <p class="fs-12 mb-0">
                        {{ translate('select_the_country_where_your_store_is_based') }}
                    </p>
                </div>
                <div class="bg-light p-3 rounded">
                    <div class="row gy-3">
                        {{-- Country --}}
                        <div class="col-lg-4">
                            <div class="form-group mb-0">
                                <label class="form-label text-dark">{{ translate('Store_Country') }}</label>
                                <select name="store_country_id" id="shop_store_country" class="form-control js-select2-custom">
                                    <option value="">--- {{ translate('Select_Country') }} ---</option>
                                    @foreach ($countries as $country)
                                        <option value="{{ $country->id }}"
                                            {{ (int) ($shop?->store_country_id) === $country->id ? 'selected' : '' }}>
                                            {{ $country->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- City --}}
                        <div class="col-lg-4">
                            <div class="form-group mb-0">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label text-dark mb-0">{{ translate('Store_City') }}</label>
                                    <button type="button" class="btn btn-link p-0 fs-12 text-primary" data-toggle="modal"
                                        data-target="#shopRequestCityModal" title="{{ translate('Request_New_City') }}">
                                        <i class="fi fi-rr-paper-plane"></i> {{ translate('Request_city') }}
                                    </button>
                                </div>
                                <select name="store_city_id" id="shop_store_city" class="form-control"
                                    {{ empty($shop?->store_country_id) ? 'disabled' : '' }}>
                                    <option value="">--- {{ translate('Select_City') }} ---</option>
                                </select>
                            </div>
                        </div>

                        {{-- Area --}}
                        <div class="col-lg-4">
                            <div class="form-group mb-0">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label text-dark mb-0">{{ translate('Store_Area') }}</label>
                                    <button type="button" class="btn btn-link p-0 fs-12 text-primary" data-toggle="modal"
                                        data-target="#shopQuickAddAreaModal" title="{{ translate('Add_New_Area') }}">
                                        <i class="fi fi-rr-plus"></i> {{ translate('Add_new') }}
                                    </button>
                                </div>
                                <select name="store_area_id" id="shop_store_area" class="form-control"
                                    {{ empty($shop?->store_city_id) ? 'disabled' : '' }}>
                                    <option value="">--- {{ translate('Select_Area') }} ---</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Request City Modal --}}
            <div class="modal fade" id="shopRequestCityModal" tabindex="-1" role="dialog" aria-labelledby="shopRequestCityModalLabel">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="shopRequestCityModalLabel">{{ translate('Request_New_City') }}</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p class="text-muted mb-3">{{ translate('if_the_city_you_need_is_not_listed_request_admin_to_add_it') }}</p>
                            <div class="form-group">
                                <label class="title-color">{{ translate('Country') }} <span class="input-required-icon">*</span></label>
                                <select id="shop_rc_country_id" class="form-control">
                                    <option value="">{{ translate('Select_Country') }}</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="title-color">{{ translate('City_Name') }} <span class="input-required-icon">*</span></label>
                                <input type="text" id="shop_rc_city_name" class="form-control"
                                    placeholder="{{ translate('e.g._New_York') }}">
                            </div>
                            <div id="shop-request-city-feedback" class="mt-2 d-none">
                                <span class="text-success" id="shop-request-city-success-msg"></span>
                                <span class="text-danger" id="shop-request-city-error-msg"></span>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ translate('Cancel') }}</button>
                            <button type="button" class="btn btn--primary" id="shopRequestCitySaveBtn">
                                <span>{{ translate('Submit_Request') }}</span>
                                <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Quick Add Area Modal --}}
            <div class="modal fade" id="shopQuickAddAreaModal" tabindex="-1" role="dialog" aria-labelledby="shopQuickAddAreaModalLabel">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="shopQuickAddAreaModalLabel">{{ translate('Add_New_Area') }}</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="title-color">{{ translate('Country') }}</label>
                                <select id="shop_qa_area_country_id" class="form-control">
                                    <option value="">{{ translate('Select_Country') }}</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="title-color">{{ translate('City') }}</label>
                                <select id="shop_qa_area_city_id" class="form-control" disabled>
                                    <option value="">{{ translate('Select_City') }}</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="title-color">{{ translate('Area_Name') }} <span class="input-required-icon">*</span></label>
                                <input type="text" id="shop_qa_area_name" class="form-control"
                                    placeholder="{{ translate('e.g._Manhattan') }}">
                            </div>
                            <div id="shop-quick-add-area-feedback" class="mt-2 d-none">
                                <span class="text-success" id="shop-quick-add-area-success-msg"></span>
                                <span class="text-danger" id="shop-quick-add-area-error-msg"></span>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ translate('Cancel') }}</button>
                            <button type="button" class="btn btn--primary" id="shopQuickAddAreaSaveBtn">
                                <span>{{ translate('Save') }}</span>
                                <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-body">
                <div class="mb-4">
                    <h3 class="mb-1">{{ translate('Business_TIN') }}</h3>
                    <p class="fs-12 mb-0">
                        {{ translate('provide_your_business_tax_id_and_related_information_for_taxpayer_verification') }}.
                    </p>
                </div>

                <div class="row">
                    <div class="col-lg-8">
                        <div class="bg-light p-3 rounded h-100">
                            <div class="form-group">
                                <label class="form-label text-dark">
                                    {{ translate('taxpayer_identification_number(TIN)') }}
                                </label>
                                <input type="text" class="form-control" name="tax_identification_number"
                                    value="{{ $shop?->tax_identification_number }}"
                                    placeholder="{{ translate('type_your_TIN_number') }}">
                            </div>
                            <div class="form-group mb-0">
                                <label class="form-label text-dark">
                                    {{ translate('Expire_Date') }}
                                </label>
                                <div class="position-relative">
                                    <span class="fi fi-sr-calendar icon-absolute-on-right"></span>
                                    <input type="text" name="tin_expire_date"
                                        value="{{ $shop?->tin_expire_date ? \Carbon\Carbon::parse($shop->tin_expire_date)->format('m/d/Y') : '' }}"
                                        class="js-daterangepicker_single-date-with-placeholder form-control"
                                        placeholder="{{ translate('click_to_add_date') }}" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    @php
                        $certificatePath = null;
                        $certificatePathExist = false;

                        if (!empty($shop?->tin_certificate)) {
                            $certificatePath = dynamicStorage(
                                path: 'storage/app/public/shop/documents/' . $shop->tin_certificate,
                            );
                            $certificatePathExist = file_exists(
                                base_path('storage/app/public/shop/documents/' . $shop->tin_certificate),
                            );
                        }
                    @endphp


                    <div class="col-lg-4">
                        <div class="bg-light p-3 rounded h-100">
                            <div class="d-flex flex-column align-items-center single_mx-100 document-upload-container">
                                <div class="d-flex gap-4 justify-content-between mb-20 w-100">
                                    <div>
                                        <label class="form-label text-dark font-weight-semibold">
                                            {{ translate('TIN_Certificate') }}
                                        </label>
                                        <p class="fs-12 mb-0">
                                            {{ 'pdf, doc, jpg. ' . translate('File_size') . ' : ' . translate('Max_5_MB') }}
                                        </p>
                                    </div>
                                    <div class="d-flex gap-3 align-items-center">
                                        <button type="button" id="tin-certificate-edit-btn"
                                            data-warning-text="{{ translate('are_you_going_to_delete_the_old_file_and_upload_a_new_one') }} ?"
                                            class="btn btn--primary btn-sm square-btn">
                                            <i class="tio-edit"></i>
                                        </button>
                                        @if ($certificatePathExist)
                                            <button type="button"
                                                class="btn btn-success btn-sm square-btn doc_download_btn">
                                                <i class="tio-download-to"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                                <div class="d-flex justify-content-center w-100">
                                    <div class="document-file-assets"
                                        data-picture-icon="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/picture.svg') }}"
                                        data-document-icon="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/document.svg') }}"
                                        data-blank-thumbnail="{{ dynamicAsset(path: 'public/assets/back-end/img/file-placeholder.png') }}">
                                    </div>

                                    <div class="document-existing-file" data-file-url="" data-file-name=""
                                        data-file-type="">
                                    </div>
                                    <div class="document-upload-wrapper mw-100 doc-upload-wrapper" {!! $certificatePathExist ? 'style="display: none"' : '' !!}>
                                        <input type="file" name="tin_certificate" class="document_input"
                                            data-max-size="{{ getFileUploadMaxSize(type: 'file') }}"
                                            data-validation-error-msg="{{ translate('File_size_is_too_large_Maximum_') . ' ' . getFileUploadMaxSize(type: 'file') . ' ' . 'MB' }}"
                                            accept=".pdf, .doc, .docx, application/msword, application/vnd.openxmlformats-officedocument.wordprocessingml.document, application/pdf">
                                        <div class="textbox">
                                            <img class="svg"
                                                src="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/doc-upload-icon.svg') }}"
                                                alt="">
                                            <p class="fs-12 mb-0">
                                                {{ translate('select_a_file_or') }}
                                                <span class="font-weight-semibold">
                                                    {{ translate('drag_and_drop_here') }}
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                    @if ($certificatePathExist)
                                        <div class="pdf-single" data-file-name="{{ $shop?->tin_certificate }}"
                                            data-file-url="{{ $certificatePath }}">
                                            <div class="pdf-frame">
                                                <canvas class="pdf-preview d--none"></canvas>
                                                <img class="pdf-thumbnail" alt="File Thumbnail"
                                                    src="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/document.svg') }}">
                                            </div>
                                            <div class="overlay">
                                                <div class="pdf-info">
                                                    <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/document.svg') }}"
                                                        width="34" alt="File Type Logo">
                                                    <div class="file-name-wrapper">
                                                        <span class="file-name">
                                                            {{ $shop?->tin_certificate }}
                                                        </span>
                                                        <span class="opacity-50">
                                                            {{ translate('Click_to_view_the_file') }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-wrap justify-content-end gap-3 mt-4">
                <a href="{{ route('vendor.shop.other-setup') }}" class="btn btn-secondary px-3 px-sm-4 min-w-120">
                    {{ translate('Reset') }}
                </a>
                <button type="submit" class="btn btn--primary px-3 px-sm-4 min-w-120">
                    <i class="fi fi-sr-disk"></i>
                    {{ translate('Save_information') }}
                </button>
            </div>
        </form>
    </div>
    @include('layouts.vendor.partials.offcanvas._shop-other-setup')
@endsection

@push('script')
    <script src="{{ dynamicAsset(path: 'public/assets/backend/vendor/js/business-settings/shop-settings.js') }}"></script>
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/file-upload/pdf.min.js') }}"></script>
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/file-upload/pdf-worker.min.js') }}"></script>
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/file-upload/multiple-document-upload.js') }}"></script>

    <script>
    (function() {
        var citiesRoute   = '{{ route("vendor.shop.location.cities", ":id") }}';
        var areasRoute    = '{{ route("vendor.shop.location.areas", ":id") }}';
        var allCitiesRoute = '{{ route("vendor.shop.location.all-cities", ":id") }}';
        var requestCityRoute = '{{ route("vendor.shop.location.quick-add-city-request") }}';
        var quickAddAreaRoute = '{{ route("vendor.shop.location.quick-add-area") }}';
        var csrfToken     = '{{ csrf_token() }}';

        var preselectedCityId = '{{ $shop?->store_city_id ?? "" }}';
        var preselectedAreaId = '{{ $shop?->store_area_id ?? "" }}';

        // On page load: if country is already selected, load cities
        var initialCountryId = $('#shop_store_country').val();
        if (initialCountryId) {
            loadCities(initialCountryId, preselectedCityId, function() {
                if (preselectedCityId) {
                    loadAreas(preselectedCityId, preselectedAreaId);
                }
            });
        }

        // Country change → load cities, reset area
        $(document).on('change', '#shop_store_country', function() {
            var countryId = $(this).val();
            $('#shop_store_city').html('<option value="">--- {{ translate("Select_City") }} ---</option>').prop('disabled', true);
            $('#shop_store_area').html('<option value="">--- {{ translate("Select_Area") }} ---</option>').prop('disabled', true);
            if (countryId) {
                loadCities(countryId);
            }
        });

        // City change → load areas
        $(document).on('change', '#shop_store_city', function() {
            var cityId = $(this).val();
            $('#shop_store_area').html('<option value="">--- {{ translate("Select_Area") }} ---</option>').prop('disabled', true);
            if (cityId) {
                loadAreas(cityId);
            }
        });

        function loadCities(countryId, preselect, callback) {
            $.get(citiesRoute.replace(':id', countryId), function(data) {
                var $select = $('#shop_store_city');
                $select.html('<option value="">--- {{ translate("Select_City") }} ---</option>');
                $.each(data, function(i, city) {
                    var selected = (preselect && preselect == city.id) ? ' selected' : '';
                    $select.append('<option value="' + city.id + '"' + selected + '>' + city.name + '</option>');
                });
                $select.prop('disabled', false);
                if (typeof callback === 'function') callback();
            });
        }

        function loadAreas(cityId, preselect, callback) {
            $.get(areasRoute.replace(':id', cityId), function(data) {
                var $select = $('#shop_store_area');
                $select.html('<option value="">--- {{ translate("Select_Area") }} ---</option>');
                $.each(data, function(i, area) {
                    var selected = (preselect && preselect == area.id) ? ' selected' : '';
                    $select.append('<option value="' + area.id + '"' + selected + '>' + area.name + '</option>');
                });
                $select.prop('disabled', false);
                if (typeof callback === 'function') callback();
            });
        }

        // --- Request City Modal ---
        $('#shopRequestCityModal').on('show.bs.modal', function() {
            var $rcCountry = $('#shop_rc_country_id');
            if ($rcCountry.find('option').length <= 1) {
                $.get('{{ route("vendor.shop.location.countries") }}', function(data) {
                    $rcCountry.html('<option value="">{{ translate("Select_Country") }}</option>');
                    $.each(data, function(i, c) {
                        $rcCountry.append('<option value="' + c.id + '">' + c.name + '</option>');
                    });
                    // Pre-select the currently chosen country
                    var currentCountry = $('#shop_store_country').val();
                    if (currentCountry) {
                        $rcCountry.val(currentCountry);
                    }
                });
            } else {
                var currentCountry = $('#shop_store_country').val();
                if (currentCountry) {
                    $rcCountry.val(currentCountry);
                }
            }
            // Reset form
            $('#shop_rc_city_name').val('');
            $('#shop-request-city-feedback').addClass('d-none');
            $('#shop-request-city-success-msg').text('');
            $('#shop-request-city-error-msg').text('');
        });

        $(document).on('click', '#shopRequestCitySaveBtn', function() {
            var $btn = $(this);
            var countryId = $('#shop_rc_country_id').val();
            var cityName  = $('#shop_rc_city_name').val().trim();

            if (!countryId || !cityName) {
                $('#shop-request-city-feedback').removeClass('d-none');
                $('#shop-request-city-error-msg').text('{{ translate("Please_select_country_and_enter_city_name") }}');
                $('#shop-request-city-success-msg').text('');
                return;
            }

            $btn.prop('disabled', true);
            $btn.find('.spinner-border').removeClass('d-none');

            $.ajax({
                url: requestCityRoute,
                type: 'POST',
                data: {
                    _token: csrfToken,
                    country_id: countryId,
                    city_name: cityName
                },
                success: function(response) {
                    $('#shop-request-city-feedback').removeClass('d-none');
                    $('#shop-request-city-success-msg').text(response.message || '{{ translate("City_request_submitted_successfully") }}');
                    $('#shop-request-city-error-msg').text('');
                    $('#shop_rc_city_name').val('');
                    setTimeout(function() {
                        $('#shopRequestCityModal').modal('hide');
                    }, 1500);
                },
                error: function(xhr) {
                    $('#shop-request-city-feedback').removeClass('d-none');
                    var errMsg = (xhr.responseJSON && xhr.responseJSON.message)
                        ? xhr.responseJSON.message
                        : '{{ translate("Something_went_wrong") }}';
                    $('#shop-request-city-error-msg').text(errMsg);
                    $('#shop-request-city-success-msg').text('');
                },
                complete: function() {
                    $btn.prop('disabled', false);
                    $btn.find('.spinner-border').addClass('d-none');
                }
            });
        });

        // --- Quick Add Area Modal ---
        $('#shopQuickAddAreaModal').on('show.bs.modal', function() {
            var $qaCountry = $('#shop_qa_area_country_id');
            // Populate countries from main dropdown
            $qaCountry.html('<option value="">{{ translate("Select_Country") }}</option>');
            $('#shop_store_country option').each(function() {
                if ($(this).val()) {
                    $qaCountry.append('<option value="' + $(this).val() + '">' + $(this).text() + '</option>');
                }
            });
            // Pre-select current country
            var currentCountry = $('#shop_store_country').val();
            if (currentCountry) {
                $qaCountry.val(currentCountry);
                // Load cities for this country
                var $qaCitySelect = $('#shop_qa_area_city_id');
                $qaCitySelect.html('<option value="">{{ translate("Loading...") }}</option>').prop('disabled', true);
                $.get(citiesRoute.replace(':id', currentCountry), function(data) {
                    $qaCitySelect.html('<option value="">{{ translate("Select_City") }}</option>');
                    var currentCity = $('#shop_store_city').val();
                    $.each(data, function(i, city) {
                        var selected = (currentCity && currentCity == city.id) ? ' selected' : '';
                        $qaCitySelect.append('<option value="' + city.id + '"' + selected + '>' + city.name + '</option>');
                    });
                    $qaCitySelect.prop('disabled', false);
                });
            }
            // Reset form
            $('#shop_qa_area_name').val('');
            $('#shop-quick-add-area-feedback').addClass('d-none');
            $('#shop-quick-add-area-success-msg').text('');
            $('#shop-quick-add-area-error-msg').text('');
        });

        // Country change inside area modal → load cities
        $(document).on('change', '#shop_qa_area_country_id', function() {
            var countryId = $(this).val();
            var $qaCitySelect = $('#shop_qa_area_city_id');
            $qaCitySelect.html('<option value="">{{ translate("Select_City") }}</option>').prop('disabled', true);
            if (countryId) {
                $.get(citiesRoute.replace(':id', countryId), function(data) {
                    $qaCitySelect.html('<option value="">{{ translate("Select_City") }}</option>');
                    $.each(data, function(i, city) {
                        $qaCitySelect.append('<option value="' + city.id + '">' + city.name + '</option>');
                    });
                    $qaCitySelect.prop('disabled', false);
                });
            }
        });

        $(document).on('click', '#shopQuickAddAreaSaveBtn', function() {
            var $btn = $(this);
            var cityId = $('#shop_qa_area_city_id').val();
            var areaName = $('#shop_qa_area_name').val().trim();

            $('#shop-quick-add-area-feedback').addClass('d-none');
            $('#shop-quick-add-area-success-msg').text('');
            $('#shop-quick-add-area-error-msg').text('');

            if (!cityId) {
                $('#shop-quick-add-area-error-msg').text('{{ translate("Please_select_a_city") }}');
                $('#shop-quick-add-area-feedback').removeClass('d-none');
                return;
            }
            if (!areaName) {
                $('#shop-quick-add-area-error-msg').text('{{ translate("Area_name_is_required") }}');
                $('#shop-quick-add-area-feedback').removeClass('d-none');
                return;
            }

            $btn.prop('disabled', true);
            $btn.find('.spinner-border').removeClass('d-none');

            $.ajax({
                url: quickAddAreaRoute,
                type: 'POST',
                data: {
                    _token: csrfToken,
                    city_id: cityId,
                    name: areaName
                },
                success: function(response) {
                    if (response.success) {
                        $('#shop-quick-add-area-feedback').removeClass('d-none');
                        $('#shop-quick-add-area-success-msg').text(response.message || '{{ translate("Area_added_successfully") }}');
                        $('#shop-quick-add-area-error-msg').text('');
                        // Reload areas in main form if same city is selected
                        if (String($('#shop_store_city').val()) === String(cityId)) {
                            loadAreas(cityId, null, function() {
                                if (response.area) {
                                    $('#shop_store_area').val(response.area.id);
                                }
                            });
                        }
                        setTimeout(function() {
                            $('#shopQuickAddAreaModal').modal('hide');
                        }, 1200);
                    } else {
                        $('#shop-quick-add-area-error-msg').text(response.message || '{{ translate("Something_went_wrong") }}');
                        $('#shop-quick-add-area-feedback').removeClass('d-none');
                    }
                },
                error: function(xhr) {
                    $('#shop-quick-add-area-feedback').removeClass('d-none');
                    var errMsg = (xhr.responseJSON && xhr.responseJSON.errors)
                        ? Object.values(xhr.responseJSON.errors).flat().join(' ')
                        : (xhr.responseJSON && xhr.responseJSON.message)
                            ? xhr.responseJSON.message
                            : '{{ translate("Something_went_wrong") }}';
                    $('#shop-quick-add-area-error-msg').text(errMsg);
                    $('#shop-quick-add-area-success-msg').text('');
                },
                complete: function() {
                    $btn.prop('disabled', false);
                    $btn.find('.spinner-border').addClass('d-none');
                }
            });
        });
    })();
    </script>
@endpush
