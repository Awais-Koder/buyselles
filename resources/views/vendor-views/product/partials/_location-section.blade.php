@php
    $existingProduct = $product ?? null;
    $selectedCountryId = old('location_country_id', $existingProduct?->location_country_id);
    $selectedCityId = old('location_city_id', $existingProduct?->location_city_id);
    $selectedAreaId = old('location_area_id', $existingProduct?->location_area_id);
@endphp

{{-- ============================================================
     Location Section — country (always) + city/area (physical only)
     ============================================================ --}}
<div class="col-12">
    <hr class="my-2">
    <h6 class="title-color mb-3">
        <i class="fi fi-rr-marker me-1"></i>
        {{ translate('Product_Location') }}
        <small class="text-muted fs-12 fw-normal ms-1">{{ translate('(optional)') }}</small>
    </h6>
</div>

{{-- Country --}}
<div class="col-md-6 col-lg-4">
    <div class="form-group">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <label class="title-color mb-0">{{ translate('Country') }}</label>
            <button type="button" class="btn btn-link p-0 fs-12 text-primary quick-add-location-btn" data-toggle="modal"
                data-target="#quickAddLocationModal" data-type="country" title="{{ translate('Add_New_Country') }}">
                <i class="fi fi-rr-plus"></i> {{ translate('Add_new') }}
            </button>
        </div>
        <select name="location_country_id" id="product_location_country" class="form-control"
            data-selected="{{ $selectedCountryId }}"
            data-countries-route="{{ route('vendor.shop.location.countries') }}"
            data-cities-route="{{ route('vendor.shop.location.cities', ':id') }}"
            data-areas-route="{{ route('vendor.shop.location.areas', ':id') }}">
            <option value="">{{ translate('Select_Country') }}</option>
        </select>
    </div>
</div>

{{-- City — physical only --}}
<div class="col-md-6 col-lg-4 physical_product_show">
    <div class="form-group">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <label class="title-color mb-0">{{ translate('City') }}</label>
            <button type="button" class="btn btn-link p-0 fs-12 text-primary quick-add-location-btn"
                data-toggle="modal" data-target="#quickAddLocationModal" data-type="city"
                title="{{ translate('Add_New_City') }}">
                <i class="fi fi-rr-plus"></i> {{ translate('Add_new') }}
            </button>
        </div>
        <select name="location_city_id" id="product_location_city" class="form-control"
            data-selected="{{ $selectedCityId }}" disabled>
            <option value="">{{ translate('Select_City') }}</option>
        </select>
    </div>
</div>

{{-- Area — physical only --}}
<div class="col-md-6 col-lg-4 physical_product_show">
    <div class="form-group">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <label class="title-color mb-0">{{ translate('Area') }}</label>
            <button type="button" class="btn btn-link p-0 fs-12 text-primary quick-add-location-btn"
                data-toggle="modal" data-target="#quickAddLocationModal" data-type="area"
                title="{{ translate('Add_New_Area') }}">
                <i class="fi fi-rr-plus"></i> {{ translate('Add_new') }}
            </button>
        </div>
        <select name="location_area_id" id="product_location_area" class="form-control"
            data-selected="{{ $selectedAreaId }}" disabled>
            <option value="">{{ translate('Select_Area') }}</option>
        </select>
    </div>
</div>

{{-- ============================================================
     Quick-Add Location Modal (shared for country / city / area)
     ============================================================ --}}
<div class="modal fade" id="quickAddLocationModal" tabindex="-1" role="dialog"
    aria-labelledby="quickAddLocationModalLabel">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="quickAddLocationModalLabel">{{ translate('Add_New_Location') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                {{-- Country form --}}
                <div id="quick-add-country-form">
                    <div class="form-group">
                        <label class="title-color">{{ translate('Country_Name') }} <span
                                class="input-required-icon">*</span></label>
                        <input type="text" id="qa_country_name" class="form-control"
                            placeholder="{{ translate('e.g._United_States') }}">
                    </div>
                    <div class="form-group">
                        <label class="title-color">{{ translate('Country_Code') }}</label>
                        <input type="text" id="qa_country_code" class="form-control"
                            placeholder="{{ translate('e.g._US') }}" maxlength="10">
                    </div>
                </div>

                {{-- City form --}}
                <div id="quick-add-city-form" style="display:none">
                    <div class="form-group">
                        <label class="title-color">{{ translate('Country') }}</label>
                        <select id="qa_city_country_id" class="form-control">
                            <option value="">{{ translate('Select_Country') }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="title-color">{{ translate('City_Name') }} <span
                                class="input-required-icon">*</span></label>
                        <input type="text" id="qa_city_name" class="form-control"
                            placeholder="{{ translate('e.g._New_York') }}">
                    </div>
                </div>

                {{-- Area form --}}
                <div id="quick-add-area-form" style="display:none">
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
                        <label class="title-color">{{ translate('Area_Name') }} <span
                                class="input-required-icon">*</span></label>
                        <input type="text" id="qa_area_name" class="form-control"
                            placeholder="{{ translate('e.g._Manhattan') }}">
                    </div>
                </div>

                <div id="quick-add-location-feedback" class="mt-2 d-none">
                    <span class="text-success" id="quick-add-success-msg"></span>
                    <span class="text-danger" id="quick-add-error-msg"></span>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                    data-dismiss="modal">{{ translate('Cancel') }}</button>
                <button type="button" class="btn btn-primary" id="quickAddLocationSaveBtn">
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

            var countriesRoute = $('#product_location_country').data('countries-route');
            var citiesRouteTemplate = $('#product_location_country').data('cities-route');
            var areasRouteTemplate = $('#product_location_country').data('areas-route');

            var quickAddRoutes = {
                country: '{{ route('vendor.shop.location.quick-add-country') }}',
                city: '{{ route('vendor.shop.location.quick-add-city') }}',
                area: '{{ route('vendor.shop.location.quick-add-area') }}',
            };

            var allCountriesCache = [];
            var currentModalType = 'country';

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

            // ── load countries on page load ──────────────────────────────────────────

            function loadCountries(callback) {
                $.getJSON(countriesRoute, function(data) {
                    allCountriesCache = data;
                    var selectedCountryId = $('#product_location_country').data('selected');
                    populateSelect($('#product_location_country'), data, selectedCountryId,
                        "{{ translate('Select_Country') }}");

                    if (selectedCountryId) {
                        loadCities(selectedCountryId, function() {
                            var selectedCityId = $('#product_location_city').data('selected');
                            if (selectedCityId) {
                                loadAreas(selectedCityId, function() {
                                    var selectedAreaId = $('#product_location_area').data(
                                        'selected');
                                    if (selectedAreaId) {
                                        $('#product_location_area').val(selectedAreaId);
                                    }
                                });
                            }
                        });
                    }

                    if (typeof callback === 'function') {
                        callback(data);
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
                if (countryId) {
                    loadCities(countryId);
                }
            });

            $(document).on('change', '#product_location_city', function() {
                var cityId = $(this).val();
                $('#product_location_area').val('').prop('disabled', true).html(
                    '<option value="">{{ translate('Select_Area') }}</option>');
                if (cityId) {
                    loadAreas(cityId);
                }
            });

            // ── quick-add modal ──────────────────────────────────────────────────────

            $(document).on('click', '.quick-add-location-btn', function() {
                currentModalType = $(this).data('type');
                resetQuickAddModal();
                showQuickAddForm(currentModalType);
                $('#quickAddLocationModal').modal('show');
            });

            function resetQuickAddModal() {
                $('#qa_country_name, #qa_country_code, #qa_city_name, #qa_area_name').val('');
                $('#qa_city_country_id, #qa_area_city_id').html('<option value="">{{ translate('Select') }}</option>')
                    .prop('disabled', false);
                $('#qa_area_country_id').html('<option value="">{{ translate('Select_Country') }}</option>');
                $('#quick-add-location-feedback').addClass('d-none');
                $('#quick-add-success-msg, #quick-add-error-msg').text('');
                $('#quickAddLocationSaveBtn .spinner-border').addClass('d-none');
                $('#quickAddLocationSaveBtn span:first').text('{{ translate('Save') }}');
            }

            function showQuickAddForm(type) {
                $('#quick-add-country-form, #quick-add-city-form, #quick-add-area-form').hide();
                if (type === 'country') {
                    $('#quick-add-country-form').show();
                    $('#quickAddLocationModalLabel').text('{{ translate('Add_New_Country') }}');
                } else if (type === 'city') {
                    populateSelect($('#qa_city_country_id'), allCountriesCache, null,
                        '{{ translate('Select_Country') }}');
                    $('#quick-add-city-form').show();
                    $('#quickAddLocationModalLabel').text('{{ translate('Add_New_City') }}');
                } else if (type === 'area') {
                    populateSelect($('#qa_area_country_id'), allCountriesCache, null,
                        '{{ translate('Select_Country') }}');
                    $('#quick-add-area-form').show();
                    $('#quickAddLocationModalLabel').text('{{ translate('Add_New_Area') }}');
                }
            }

            // city dropdown in quick-add area form: load cities when country selected
            $(document).on('change', '#qa_area_country_id', function() {
                var countryId = $(this).val();
                var $citySelect = $('#qa_area_city_id');
                $citySelect.html('<option value="">{{ translate('Loading...') }}</option>').prop('disabled',
                    true);
                if (!countryId) {
                    $citySelect.html('<option value="">{{ translate('Select_City') }}</option>').prop(
                        'disabled', false);
                    return;
                }
                $.getJSON(buildCitiesUrl(countryId), function(data) {
                    populateSelect($citySelect, data, null, '{{ translate('Select_City') }}');
                    $citySelect.prop('disabled', false);
                }).fail(function() {
                    $citySelect.html('<option value="">{{ translate('Select_City') }}</option>').prop(
                        'disabled', false);
                });
            });

            // ── save button logic ────────────────────────────────────────────────────

            $('#quickAddLocationSaveBtn').on('click', function() {
                var $btn = $(this);
                $btn.find('.spinner-border').removeClass('d-none');
                $btn.find('span:first').text('{{ translate('Saving...') }}');
                $('#quick-add-location-feedback').addClass('d-none');
                $('#quick-add-success-msg, #quick-add-error-msg').text('');

                var payload = {};
                var route = quickAddRoutes[currentModalType];

                if (currentModalType === 'country') {
                    var name = $.trim($('#qa_country_name').val());
                    if (!name) {
                        return showError('{{ translate('Country_name_is_required') }}');
                    }
                    payload = {
                        name: name,
                        code: $('#qa_country_code').val(),
                        _token: '{{ csrf_token() }}'
                    };

                } else if (currentModalType === 'city') {
                    var name = $.trim($('#qa_city_name').val());
                    var countryId = $('#qa_city_country_id').val();
                    if (!name) {
                        return showError('{{ translate('City_name_is_required') }}');
                    }
                    if (!countryId) {
                        return showError('{{ translate('Please_select_a_country') }}');
                    }
                    payload = {
                        name: name,
                        country_id: countryId,
                        _token: '{{ csrf_token() }}'
                    };

                } else if (currentModalType === 'area') {
                    var name = $.trim($('#qa_area_name').val());
                    var cityId = $('#qa_area_city_id').val();
                    if (!name) {
                        return showError('{{ translate('Area_name_is_required') }}');
                    }
                    if (!cityId) {
                        return showError('{{ translate('Please_select_a_city') }}');
                    }
                    payload = {
                        name: name,
                        city_id: cityId,
                        _token: '{{ csrf_token() }}'
                    };
                }

                $.post(route, payload)
                    .done(function(response) {
                        if (response.success) {
                            showSuccess(response.message);
                            onQuickAddSuccess(currentModalType, response);
                        } else {
                            showError(response.message || '{{ translate('Something_went_wrong') }}');
                        }
                    })
                    .fail(function(xhr) {
                        var errors = xhr.responseJSON && xhr.responseJSON.errors ?
                            Object.values(xhr.responseJSON.errors).flat().join(' ') :
                            '{{ translate('Something_went_wrong') }}';
                        showError(errors);
                    })
                    .always(function() {
                        $btn.find('.spinner-border').addClass('d-none');
                        $btn.find('span:first').text('{{ translate('Save') }}');
                    });
            });

            function showError(msg) {
                $('#quick-add-error-msg').text(msg);
                $('#quick-add-location-feedback').removeClass('d-none');
                $('#quickAddLocationSaveBtn .spinner-border').addClass('d-none');
                $('#quickAddLocationSaveBtn span:first').text('{{ translate('Save') }}');
            }

            function showSuccess(msg) {
                $('#quick-add-success-msg').text(msg);
                $('#quick-add-location-feedback').removeClass('d-none');
            }

            function onQuickAddSuccess(type, response) {
                if (type === 'country') {
                    var newCountry = response.country;
                    allCountriesCache.push(newCountry);
                    // Add to main select and auto-select it
                    $('#product_location_country').append($('<option>', {
                        value: newCountry.id,
                        text: newCountry.name
                    }));
                    $('#product_location_country').val(newCountry.id).trigger('change');

                } else if (type === 'city') {
                    var newCity = response.city;
                    var countryId = $('#qa_city_country_id').val();
                    // Reload cities for the currently-selected country in the main form
                    if (String($('#product_location_country').val()) === String(countryId)) {
                        loadCities(countryId, function() {
                            $('#product_location_city').val(newCity.id);
                        });
                    }
                } else if (type === 'area') {
                    var newArea = response.area;
                    var cityId = $('#qa_area_city_id').val();
                    // Reload areas for the currently-selected city in the main form
                    if (String($('#product_location_city').val()) === String(cityId)) {
                        loadAreas(cityId, function() {
                            $('#product_location_area').val(newArea.id);
                        });
                    }
                }

                setTimeout(function() {
                    $('#quickAddLocationModal').modal('hide');
                }, 1200);
            }

            // ── init ─────────────────────────────────────────────────────────────────
            loadCountries();

        }());
    </script>
@endpush
