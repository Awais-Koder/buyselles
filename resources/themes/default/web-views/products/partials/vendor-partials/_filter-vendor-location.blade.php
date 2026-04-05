{{-- Location Filter Sidebar Partial for Vendors Listing (Country → City → Area) --}}
<div class="widget widget-categories mb-4">
    <h5 class="fs-16 font-weight-bold mb-3">{{ translate('Filter_By_Location') }}</h5>

    <div class="mb-3">
        <label class="form-label small fw-semibold">{{ translate('country') }}</label>
        <select id="vendor-location-country" class="form-control form-control-sm">
            <option value="">--- {{ translate('all_countries') }} ---</option>
            @foreach ($countries as $country)
                <option value="{{ $country->id }}" {{ ($selectedCountryId ?? '') == $country->id ? 'selected' : '' }}>
                    {{ $country->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label small fw-semibold">{{ translate('city') }}</label>
        <select id="vendor-location-city" class="form-control form-control-sm" {{ empty($selectedCountryId) ? 'disabled' : '' }}>
            <option value="">--- {{ translate('all_cities') }} ---</option>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label small fw-semibold">{{ translate('area') }}</label>
        <select id="vendor-location-area" class="form-control form-control-sm" {{ empty($selectedCityId) ? 'disabled' : '' }}>
            <option value="">--- {{ translate('all_areas') }} ---</option>
        </select>
    </div>

    <div class="d-flex gap-2">
        <button id="vendor-location-apply" class="btn btn--primary btn-sm flex-grow-1">
            {{ translate('apply') }}
        </button>
        @if (($selectedCountryId ?? '') || ($selectedCityId ?? '') || ($selectedAreaId ?? ''))
            <a href="{{ url()->current() . '?' . http_build_query(collect(request()->except(['store_country_id', 'store_city_id', 'store_area_id']))->toArray()) }}"
                class="btn btn-outline-secondary btn-sm">
                {{ translate('clear') }}
            </a>
        @endif
    </div>
</div>

@push('script')
    <script>
        (function() {
            var selectedCityId = '{{ $selectedCityId ?? '' }}';
            var selectedAreaId = '{{ $selectedAreaId ?? '' }}';

            // Load cities if country is pre-selected
            var initialCountryId = $('#vendor-location-country').val();
            if (initialCountryId) {
                loadCities(initialCountryId, selectedCityId, function() {
                    if (selectedCityId) {
                        loadAreas(selectedCityId, selectedAreaId);
                    }
                });
            }

            // Country change → load cities
            $(document).on('change', '#vendor-location-country', function() {
                var countryId = $(this).val();
                $('#vendor-location-city').html('<option value="">--- {{ translate("all_cities") }} ---</option>').prop('disabled', true);
                $('#vendor-location-area').html('<option value="">--- {{ translate("all_areas") }} ---</option>').prop('disabled', true);
                if (countryId) {
                    loadCities(countryId);
                }
            });

            // City change → load areas
            $(document).on('change', '#vendor-location-city', function() {
                var cityId = $(this).val();
                $('#vendor-location-area').html('<option value="">--- {{ translate("all_areas") }} ---</option>').prop('disabled', true);
                if (cityId) {
                    loadAreas(cityId);
                }
            });

            // Apply filter
            $(document).on('click', '#vendor-location-apply', function() {
                var params = new URLSearchParams(window.location.search);
                params.delete('store_country_id');
                params.delete('store_city_id');
                params.delete('store_area_id');
                params.delete('store_country');
                params.delete('page');

                var countryId = $('#vendor-location-country').val();
                var cityId = $('#vendor-location-city').val();
                var areaId = $('#vendor-location-area').val();

                if (countryId) params.set('store_country_id', countryId);
                if (cityId) params.set('store_city_id', cityId);
                if (areaId) params.set('store_area_id', areaId);

                window.location.href = window.location.pathname + '?' + params.toString();
            });

            function loadCities(countryId, preselect, callback) {
                $.get('{{ url("location/cities") }}/' + countryId, function(data) {
                    var $select = $('#vendor-location-city');
                    $select.html('<option value="">--- {{ translate("all_cities") }} ---</option>');
                    $.each(data, function(i, city) {
                        var selected = (preselect && preselect == city.id) ? ' selected' : '';
                        $select.append('<option value="' + city.id + '"' + selected + '>' + city.name + '</option>');
                    });
                    $select.prop('disabled', false);
                    if (typeof callback === 'function') callback();
                });
            }

            function loadAreas(cityId, preselect, callback) {
                $.get('{{ url("location/areas") }}/' + cityId, function(data) {
                    var $select = $('#vendor-location-area');
                    $select.html('<option value="">--- {{ translate("all_areas") }} ---</option>');
                    $.each(data, function(i, area) {
                        var selected = (preselect && preselect == area.id) ? ' selected' : '';
                        $select.append('<option value="' + area.id + '"' + selected + '>' + area.name + '</option>');
                    });
                    $select.prop('disabled', false);
                    if (typeof callback === 'function') callback();
                });
            }
        })();
    </script>
@endpush
