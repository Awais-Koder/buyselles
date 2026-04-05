@php
    $existingProduct = $product ?? null;
    $selectedCountryId = old('location_country_id', $existingProduct?->location_country_id);
    $selectedCityId = old('location_city_id', $existingProduct?->location_city_id);
    $selectedAreaId = old('location_area_id', $existingProduct?->location_area_id);

    // Digital product with no country = Global
    if ($existingProduct && $existingProduct->product_type === 'digital' && empty($selectedCountryId)) {
        $selectedCountryId = 0;
    }

    // Query location data server-side for reliable pre-selection
    $activeCountries = $activeCountries ?? \App\Models\LocationCountry::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(['id', 'name']);
    $activeCities = $activeCities ?? ($selectedCountryId ? \App\Models\LocationCity::where('country_id', $selectedCountryId)->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(['id', 'name']) : collect());
    $activeAreas = $activeAreas ?? ($selectedCityId ? \App\Models\LocationArea::where('city_id', $selectedCityId)->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(['id', 'name']) : collect());
@endphp

{{-- ============================================================
     Location Section — country (always) + city/area (physical only)
     ============================================================ --}}
<div class="col-12">
    <hr class="my-2">
    <h6 class="form-label mb-3">
        <i class="fi fi-rr-marker me-1"></i>
        {{ translate('Product_Location') }}
    </h6>
</div>

{{-- Country --}}
<div class="col-md-6 col-lg-4">
    <div class="form-group">
        <label class="form-label">{{ translate('Country') }} <span class="input-required-icon">*</span></label>
        <select name="location_country_id" id="product_location_country" class="form-control" required
            data-selected="{{ $selectedCountryId }}"
            data-cities-route="{{ route('admin.business-settings.location.get-cities', ':id') }}"
            data-areas-route="{{ route('admin.business-settings.location.get-areas', ':id') }}">
            <option value="">{{ translate('Select_Country') }}</option>
            <option value="0" class="global-option {{ ($existingProduct?->product_type ?? old('product_type')) !== 'digital' ? 'd-none' : '' }}"
                {{ $selectedCountryId !== null && (string)$selectedCountryId === '0' ? 'selected' : '' }}>
                {{ translate('Global') }} ({{ translate('All_Countries') }})
            </option>
            @foreach($activeCountries as $country)
                <option value="{{ $country->id }}" {{ $selectedCountryId !== null && (int)$selectedCountryId === $country->id ? 'selected' : '' }}>
                    {{ $country->name }}
                </option>
            @endforeach
        </select>
    </div>
</div>

{{-- City — physical only --}}
<div class="col-md-6 col-lg-4 show-for-physical-product">
    <div class="form-group">
        <label class="form-label">{{ translate('City') }}</label>
        <select name="location_city_id" id="product_location_city" class="form-control"
            data-selected="{{ $selectedCityId }}" {{ $activeCities->isEmpty() ? 'disabled' : '' }}>
            <option value="">{{ translate('Select_City') }}</option>
            @foreach($activeCities as $city)
                <option value="{{ $city->id }}" {{ $selectedCityId !== null && (int)$selectedCityId === $city->id ? 'selected' : '' }}>
                    {{ $city->name }}
                </option>
            @endforeach
        </select>
    </div>
</div>

{{-- Area — physical only --}}
<div class="col-md-6 col-lg-4 show-for-physical-product">
    <div class="form-group">
        <label class="form-label">{{ translate('Area') }}</label>
        <select name="location_area_id" id="product_location_area" class="form-control"
            data-selected="{{ $selectedAreaId }}" {{ $activeAreas->isEmpty() ? 'disabled' : '' }}>
            <option value="">{{ translate('Select_Area') }}</option>
            @foreach($activeAreas as $area)
                <option value="{{ $area->id }}" {{ $selectedAreaId !== null && (int)$selectedAreaId === $area->id ? 'selected' : '' }}>
                    {{ $area->name }}
                </option>
            @endforeach
        </select>
    </div>
</div>

@push('script')
    <script>
        (function() {
            'use strict';

            var citiesRouteTemplate = $('#product_location_country').data('cities-route');
            var areasRouteTemplate = $('#product_location_country').data('areas-route');

            // ── Global option visibility based on product type ──

            function toggleGlobalOption() {
                var productType = $('#product_type').val();
                var $globalOpt = $('#product_location_country .global-option');
                if (productType === 'digital') {
                    $globalOpt.removeClass('d-none');
                } else {
                    $globalOpt.addClass('d-none');
                    if ($('#product_location_country').val() === '0') {
                        $('#product_location_country').val('');
                        $('#product_location_city').val('').prop('disabled', true).html(
                            '<option value="">{{ translate('Select_City') }}</option>');
                        $('#product_location_area').val('').prop('disabled', true).html(
                            '<option value="">{{ translate('Select_Area') }}</option>');
                    }
                }
            }

            $(document).on('change', '#product_type', function() {
                toggleGlobalOption();
            });

            // ── helpers ──

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

            // ── cascade change handlers ──

            $(document).on('change', '#product_location_country', function() {
                var countryId = $(this).val();
                $('#product_location_city').val('').prop('disabled', true).html(
                    '<option value="">{{ translate('Select_City') }}</option>');
                $('#product_location_area').val('').prop('disabled', true).html(
                    '<option value="">{{ translate('Select_Area') }}</option>');
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
                }
            });

            // ── init ──
            toggleGlobalOption();

        }());
    </script>
@endpush
