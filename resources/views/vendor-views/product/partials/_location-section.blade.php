@php
    $existingProduct = $product ?? null;
    $selectedCountryId = old('location_country_id', $existingProduct?->location_country_id);
    $selectedCityId = old('location_city_id', $existingProduct?->location_city_id);
    $selectedAreaId = old('location_area_id', $existingProduct?->location_area_id);
    $selectedPendingCityRequestId = old('pending_city_request_id', $existingProduct?->pending_city_request_id);
    $pendingCityRequestName = $existingProduct?->pendingCityRequest?->city_name;

    // Digital product with no country = Global
    if ($existingProduct && $existingProduct->product_type === 'digital' && empty($selectedCountryId)) {
        $selectedCountryId = 0;
    }
@endphp

{{-- ============================================================
     Location Section — country (always) + city/area (physical only)
     ============================================================ --}}
<div class="col-12">
    <hr class="my-2">
    <h6 class="title-color mb-3">
        <i class="fi fi-rr-marker me-1"></i>
        {{ translate('Product_Location') }}
    </h6>
</div>

{{-- Country (global, admin-managed — no "Add new") --}}
<div class="col-md-6 col-lg-4">
    <div class="form-group">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <label class="title-color mb-0">{{ translate('Country') }} <span class="input-required-icon">*</span></label>
        </div>
        <select name="location_country_id" id="product_location_country" class="form-control" required
            data-selected="{{ $selectedCountryId }}"
            data-cities-route="{{ route('vendor.shop.location.cities', ':id') }}"
            data-areas-route="{{ route('vendor.shop.location.areas', ':id') }}">
            <option value="">{{ translate('Select_Country') }}</option>
            <option value="0" class="global-option {{ ($existingProduct?->product_type ?? old('product_type')) !== 'digital' ? 'd-none' : '' }}"
                {{ $selectedCountryId !== null && (string)$selectedCountryId === '0' ? 'selected' : '' }}>
                {{ translate('Global') }} ({{ translate('All_Countries') }})
            </option>
            @foreach($activeCountries ?? [] as $country)
                <option value="{{ $country->id }}" {{ $selectedCountryId !== null && (int)$selectedCountryId === $country->id ? 'selected' : '' }}>
                    {{ $country->name }}
                </option>
            @endforeach
        </select>
    </div>
</div>

{{-- City — physical only (request city instead of add) --}}
<div class="col-md-6 col-lg-4 physical_product_show">
    <div class="form-group">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <label class="title-color mb-0">{{ translate('City') }}</label>
            <button type="button" class="btn btn-link p-0 fs-12 text-primary" data-toggle="modal"
                data-target="#requestCityModal" title="{{ translate('Request_New_City') }}">
                <i class="fi fi-rr-paper-plane"></i> {{ translate('Request_city') }}
            </button>
        </div>
        <select name="location_city_id" id="product_location_city" class="form-control"
            data-selected="{{ $selectedCityId }}" {{ ($activeCities ?? collect())->isEmpty() ? 'disabled' : '' }}>
            <option value="">{{ translate('Select_City') }}</option>
            @foreach($activeCities ?? [] as $city)
                <option value="{{ $city->id }}" {{ $selectedCityId !== null && (int)$selectedCityId === $city->id ? 'selected' : '' }}>
                    {{ $city->name }}
                </option>
            @endforeach
        </select>

        {{-- Hidden field to link this product to a pending city request --}}
        <input type="hidden" name="pending_city_request_id" id="pending_city_request_id"
            value="{{ $selectedPendingCityRequestId }}">

        {{-- Notice shown when no city is selected but a city request is linked --}}
        <div id="pending-city-notice" class="mt-2 {{ $selectedPendingCityRequestId ? '' : 'd-none' }}">
            <div class="alert alert-warning d-flex align-items-start gap-2 py-2 px-3 mb-0" role="alert">
                <i class="fi fi-sr-triangle-warning mt-1 flex-shrink-0"></i>
                <div>
                    <strong>{{ translate('City_Request_Pending') }}</strong><br>
                    <small id="pending-city-name">{{ $pendingCityRequestName ? '"' . $pendingCityRequestName . '" — ' : '' }}{{ translate('product_will_go_to_admin_review_until_city_is_approved') }}</small>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Area — physical only, optional --}}
<div class="col-md-6 col-lg-4 physical_product_show">
    <div class="form-group">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <label class="title-color mb-0">{{ translate('Area') }}</label>
            <button type="button" class="btn btn-link p-0 fs-12 text-primary quick-add-area-btn" data-toggle="modal"
                data-target="#quickAddAreaModal" title="{{ translate('Add_New_Area') }}">
                <i class="fi fi-rr-plus"></i> {{ translate('Add_new') }}
            </button>
        </div>
        <select name="location_area_id" id="product_location_area" class="form-control"
            data-selected="{{ $selectedAreaId }}" {{ ($activeAreas ?? collect())->isEmpty() ? 'disabled' : '' }}>
            <option value="">{{ translate('Select_Area') }}</option>
            @foreach($activeAreas ?? [] as $area)
                <option value="{{ $area->id }}" {{ $selectedAreaId !== null && (int)$selectedAreaId === $area->id ? 'selected' : '' }}>
                    {{ $area->name }}
                </option>
            @endforeach
        </select>
    </div>
</div>

{{-- ============================================================
     Request City Modal — sends request to admin
     ============================================================ --}}
<div class="modal fade" id="requestCityModal" tabindex="-1" role="dialog" aria-labelledby="requestCityModalLabel">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="requestCityModalLabel">{{ translate('Request_New_City') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">{{ translate('if_the_city_you_need_is_not_listed_request_admin_to_add_it') }}</p>
                <div class="form-group">
                    <label class="title-color">{{ translate('Country') }} <span class="input-required-icon">*</span></label>
                    <select id="rc_country_id" class="form-control">
                        <option value="">{{ translate('Select_Country') }}</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="title-color">{{ translate('City_Name') }} <span class="input-required-icon">*</span></label>
                    <input type="text" id="rc_city_name" class="form-control"
                        placeholder="{{ translate('e.g._New_York') }}">
                </div>
                <div id="request-city-feedback" class="mt-2 d-none">
                    <span class="text-success" id="request-city-success-msg"></span>
                    <span class="text-danger" id="request-city-error-msg"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ translate('Cancel') }}</button>
                <button type="button" class="btn btn--primary" id="requestCitySaveBtn">
                    <span>{{ translate('Submit_Request') }}</span>
                    <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ============================================================
     Quick-Add Area Modal
     ============================================================ --}}
<div class="modal fade" id="quickAddAreaModal" tabindex="-1" role="dialog" aria-labelledby="quickAddAreaModalLabel">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="quickAddAreaModalLabel">{{ translate('Add_New_Area') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="title-color">{{ translate('Country') }}</label>
                    <select id="qa_area_country_id" class="form-control">
                        <option value="">{{ translate('Select_Country') }}</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="title-color">{{ translate('City') }}</label>
                    <select id="qa_area_city_id" class="form-control" disabled>
                        <option value="">{{ translate('Select_City') }}</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="title-color">{{ translate('Area_Name') }} <span class="input-required-icon">*</span></label>
                    <input type="text" id="qa_area_name" class="form-control"
                        placeholder="{{ translate('e.g._Manhattan') }}">
                </div>
                <div id="quick-add-area-feedback" class="mt-2 d-none">
                    <span class="text-success" id="quick-add-area-success-msg"></span>
                    <span class="text-danger" id="quick-add-area-error-msg"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ translate('Cancel') }}</button>
                <button type="button" class="btn btn-primary" id="quickAddAreaSaveBtn">
                    <span>{{ translate('Save') }}</span>
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

            var citiesRouteTemplate = $('#product_location_country').data('cities-route');
            var areasRouteTemplate = $('#product_location_country').data('areas-route');

            var requestCityRoute = '{{ route('vendor.shop.location.quick-add-city-request') }}';
            var quickAddAreaRoute = '{{ route('vendor.shop.location.quick-add-area') }}';

            var allCountriesCache = [];

            // ── Global option visibility based on product type ───────────────────

            function toggleGlobalOption() {
                var productType = $('#product_type').val();
                var $globalOpt = $('#product_location_country .global-option');
                if (productType === 'digital') {
                    $globalOpt.removeClass('d-none');
                } else {
                    $globalOpt.addClass('d-none');
                    // If Global was selected and user switches to physical, reset
                    if ($('#product_location_country').val() === '0') {
                        $('#product_location_country').val('');
                        $('#product_location_city').val('').prop('disabled', true).html(
                            '<option value="">{{ translate('Select_City') }}</option>');
                        $('#product_location_area').val('').prop('disabled', true).html(
                            '<option value="">{{ translate('Select_Area') }}</option>');
                    }
                }
            }

            // Listen for product type changes
            $(document).on('change', '#product_type', function() {
                toggleGlobalOption();
            });

            // ── helpers ──────────────────────────────────────────────────────────────

            function buildCitiesUrl(countryId) {
                return citiesRouteTemplate.replace(':id', countryId);
            }

            function buildAreasUrl(cityId) {
                return areasRouteTemplate.replace(':id', cityId);
            }

            function populateSelect($select, items, selectedId, placeholder) {
                $select.empty().append($('<option>', {
                    value: '',
                    text: placeholder
                }));
                $.each(items, function(i, item) {
                    $select.append($('<option>', {
                        value: item.id,
                        text: item.name,
                        selected: (String(item.id) === String(selectedId)),
                    }));
                });
            }

            // ── load countries cache from server-rendered options ───────────────────

            function buildCountriesCache() {
                allCountriesCache = [];
                $('#product_location_country option').each(function() {
                    var val = $(this).val();
                    if (val && val !== '0') {
                        allCountriesCache.push({ id: parseInt(val), name: $(this).text().trim() });
                    }
                });
            }

            function loadCities(countryId, callback) {
                var $citySelect = $('#product_location_city');
                $citySelect.html('<option value="">{{ translate('Loading...') }}</option>').prop('disabled', true);
                $.getJSON(buildCitiesUrl(countryId), function(data) {
                    populateSelect($citySelect, data, $citySelect.data('selected'),
                        "{{ translate('Select_City') }}");
                    $citySelect.prop('disabled', false);
                    if (typeof callback === 'function') {
                        callback(data);
                    }
                }).fail(function() {
                    $citySelect.html('<option value="">{{ translate('Select_City') }}</option>');
                });
            }

            function loadAreas(cityId, callback) {
                var $areaSelect = $('#product_location_area');
                $areaSelect.html('<option value="">{{ translate('Loading...') }}</option>').prop('disabled', true);
                $.getJSON(buildAreasUrl(cityId), function(data) {
                    populateSelect($areaSelect, data, $areaSelect.data('selected'),
                        "{{ translate('Select_Area') }}");
                    $areaSelect.prop('disabled', false);
                    if (typeof callback === 'function') {
                        callback(data);
                    }
                }).fail(function() {
                    $areaSelect.html('<option value="">{{ translate('Select_Area') }}</option>');
                });
            }

            // ── cascade change handlers ──────────────────────────────────────────────

            $(document).on('change', '#product_location_country', function() {
                var countryId = $(this).val();
                $('#product_location_city').val('').prop('disabled', true).html(
                    '<option value="">{{ translate('Select_City') }}</option>');
                $('#product_location_area').val('').prop('disabled', true).html(
                    '<option value="">{{ translate('Select_Area') }}</option>');
                // Country changed — clear any linked city request
                $('#pending_city_request_id').val('');
                $('#pending-city-notice').addClass('d-none');
                if (countryId && countryId !== '0') {
                    loadCities(countryId);
                }
            });

            $(document).on('change', '#product_location_city', function() {
                var cityId = $(this).val();
                $('#product_location_area').val('').prop('disabled', true).html(
                    '<option value="">{{ translate('Select_Area') }}</option>');
                if (cityId) {
                    loadAreas(cityId);
                    // City was selected — clear any pending city request link
                    $('#pending_city_request_id').val('');
                    $('#pending-city-notice').addClass('d-none');
                }
            });

            // ── Request City Modal ───────────────────────────────────────────────────

            $('#requestCityModal').on('show.bs.modal', function() {
                populateSelect($('#rc_country_id'), allCountriesCache, $('#product_location_country').val(),
                    '{{ translate('Select_Country') }}');
                $('#rc_city_name').val('');
                $('#request-city-feedback').addClass('d-none');
                $('#request-city-success-msg, #request-city-error-msg').text('');
            });

            $('#requestCitySaveBtn').on('click', function() {
                var $btn = $(this);
                var countryId = $('#rc_country_id').val();
                var cityName = $.trim($('#rc_city_name').val());

                $('#request-city-feedback').addClass('d-none');
                $('#request-city-success-msg, #request-city-error-msg').text('');

                if (!countryId) {
                    $('#request-city-error-msg').text('{{ translate('Please_select_a_country') }}');
                    $('#request-city-feedback').removeClass('d-none');
                    return;
                }
                if (!cityName) {
                    $('#request-city-error-msg').text('{{ translate('City_name_is_required') }}');
                    $('#request-city-feedback').removeClass('d-none');
                    return;
                }

                $btn.find('.spinner-border').removeClass('d-none');
                $btn.find('span:first').text('{{ translate('Submitting...') }}');

                $.post(requestCityRoute, {
                    country_id: countryId,
                    city_name: cityName,
                    _token: '{{ csrf_token() }}'
                })
                .done(function(response) {
                    if (response.success) {
                        $('#request-city-success-msg').text(response.message);
                        $('#request-city-feedback').removeClass('d-none');

                        // Link the city request to this product
                        if (response.city_request && response.city_request.id) {
                            $('#pending_city_request_id').val(response.city_request.id);
                            var cityLabel = '"' + response.city_request.city_name + '" — ';
                            $('#pending-city-name').text(cityLabel + '{{ translate('product_will_go_to_admin_review_until_city_is_approved') }}');
                            $('#pending-city-notice').removeClass('d-none');
                        }

                        setTimeout(function() {
                            $('#requestCityModal').modal('hide');
                        }, 1500);
                    } else {
                        $('#request-city-error-msg').text(response.message || '{{ translate('Something_went_wrong') }}');
                        $('#request-city-feedback').removeClass('d-none');
                    }
                })
                .fail(function(xhr) {
                    var errors = xhr.responseJSON && xhr.responseJSON.errors ?
                        Object.values(xhr.responseJSON.errors).flat().join(' ') :
                        '{{ translate('Something_went_wrong') }}';
                    $('#request-city-error-msg').text(errors);
                    $('#request-city-feedback').removeClass('d-none');
                })
                .always(function() {
                    $btn.find('.spinner-border').addClass('d-none');
                    $btn.find('span:first').text('{{ translate('Submit_Request') }}');
                });
            });

            // ── Quick-Add Area Modal ─────────────────────────────────────────────────

            $('#quickAddAreaModal').on('show.bs.modal', function() {
                populateSelect($('#qa_area_country_id'), allCountriesCache, $('#product_location_country').val(),
                    '{{ translate('Select_Country') }}');
                var countryId = $('#product_location_country').val();
                if (countryId) {
                    var $citySelect = $('#qa_area_city_id');
                    $citySelect.html('<option value="">{{ translate('Loading...') }}</option>').prop('disabled', true);
                    $.getJSON(buildCitiesUrl(countryId), function(data) {
                        populateSelect($citySelect, data, $('#product_location_city').val(), '{{ translate('Select_City') }}');
                        $citySelect.prop('disabled', false);
                    });
                }
                $('#qa_area_name').val('');
                $('#quick-add-area-feedback').addClass('d-none');
                $('#quick-add-area-success-msg, #quick-add-area-error-msg').text('');
            });

            $(document).on('change', '#qa_area_country_id', function() {
                var countryId = $(this).val();
                var $citySelect = $('#qa_area_city_id');
                $citySelect.html('<option value="">{{ translate('Loading...') }}</option>').prop('disabled', true);
                if (!countryId) {
                    $citySelect.html('<option value="">{{ translate('Select_City') }}</option>').prop('disabled', false);
                    return;
                }
                $.getJSON(buildCitiesUrl(countryId), function(data) {
                    populateSelect($citySelect, data, null, '{{ translate('Select_City') }}');
                    $citySelect.prop('disabled', false);
                }).fail(function() {
                    $citySelect.html('<option value="">{{ translate('Select_City') }}</option>').prop('disabled', false);
                });
            });

            $('#quickAddAreaSaveBtn').on('click', function() {
                var $btn = $(this);
                var cityId = $('#qa_area_city_id').val();
                var areaName = $.trim($('#qa_area_name').val());

                $('#quick-add-area-feedback').addClass('d-none');
                $('#quick-add-area-success-msg, #quick-add-area-error-msg').text('');

                if (!cityId) {
                    $('#quick-add-area-error-msg').text('{{ translate('Please_select_a_city') }}');
                    $('#quick-add-area-feedback').removeClass('d-none');
                    return;
                }
                if (!areaName) {
                    $('#quick-add-area-error-msg').text('{{ translate('Area_name_is_required') }}');
                    $('#quick-add-area-feedback').removeClass('d-none');
                    return;
                }

                $btn.find('.spinner-border').removeClass('d-none');
                $btn.find('span:first').text('{{ translate('Saving...') }}');

                $.post(quickAddAreaRoute, {
                    name: areaName,
                    city_id: cityId,
                    _token: '{{ csrf_token() }}'
                })
                .done(function(response) {
                    if (response.success) {
                        $('#quick-add-area-success-msg').text(response.message);
                        $('#quick-add-area-feedback').removeClass('d-none');
                        // Reload areas for currently-selected city in main form
                        if (String($('#product_location_city').val()) === String(cityId)) {
                            loadAreas(cityId, function() {
                                if (response.area) {
                                    $('#product_location_area').val(response.area.id);
                                }
                            });
                        }
                        setTimeout(function() {
                            $('#quickAddAreaModal').modal('hide');
                        }, 1200);
                    } else {
                        $('#quick-add-area-error-msg').text(response.message || '{{ translate('Something_went_wrong') }}');
                        $('#quick-add-area-feedback').removeClass('d-none');
                    }
                })
                .fail(function(xhr) {
                    var errors = xhr.responseJSON && xhr.responseJSON.errors ?
                        Object.values(xhr.responseJSON.errors).flat().join(' ') :
                        '{{ translate('Something_went_wrong') }}';
                    $('#quick-add-area-error-msg').text(errors);
                    $('#quick-add-area-feedback').removeClass('d-none');
                })
                .always(function() {
                    $btn.find('.spinner-border').addClass('d-none');
                    $btn.find('span:first').text('{{ translate('Save') }}');
                });
            });

            // ── init ─────────────────────────────────────────────────────────────────
            buildCountriesCache();
            toggleGlobalOption();

        }());
    </script>
@endpush
