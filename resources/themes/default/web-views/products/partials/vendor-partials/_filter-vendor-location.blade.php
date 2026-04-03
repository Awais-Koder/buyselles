{{-- Location Filter Sidebar Partial for Vendors Listing --}}
<div class="widget widget-categories mb-4">
    <h5 class="fs-16 font-weight-bold mb-3">{{ translate('Filter_By_Location') }}</h5>

    <div class="mb-3">
        <label class="form-label small fw-semibold">{{ translate('country') }}</label>
        <select id="loc-country-filter" class="form-control form-control-sm"
            data-cities-url="{{ route('location.cities', ':countryId') }}">
            <option value="">--- {{ translate('all_countries') }} ---</option>
            @foreach ($activeCountries as $country)
                <option value="{{ $country->id }}" {{ ($selectedCountryId ?? '') == $country->id ? 'selected' : '' }}>
                    {{ $country->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="mb-3" id="loc-city-wrapper">
        <label class="form-label small fw-semibold">{{ translate('city') }}</label>
        <select id="loc-city-filter" class="form-control form-control-sm"
            data-areas-url="{{ route('location.areas', ':cityId') }}">
            <option value="">--- {{ translate('all_cities') }} ---</option>
            @foreach ($preloadedCities as $city)
                <option value="{{ $city->id }}" {{ ($selectedCityId ?? '') == $city->id ? 'selected' : '' }}>
                    {{ $city->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="mb-3" id="loc-area-wrapper">
        <label class="form-label small fw-semibold">{{ translate('area') }}</label>
        <select id="loc-area-filter" class="form-control form-control-sm">
            <option value="">--- {{ translate('all_areas') }} ---</option>
            {{-- Populated via JS when city changes --}}
        </select>
    </div>

    <div class="d-flex gap-2">
        <button id="loc-apply-filter" class="btn btn-primary btn-sm flex-grow-1">
            {{ translate('apply') }}
        </button>
        @if (request('country_id') || request('city_id') || request('area_id'))
            <a href="{{ url()->current() . '?' . http_build_query(collect(request()->except(['country_id', 'city_id', 'area_id']))->toArray()) }}"
                class="btn btn-outline-secondary btn-sm">
                {{ translate('clear') }}
            </a>
        @endif
    </div>
</div>

@push('script')
    <script>
        (function() {
            var citiesUrlTpl = $('#loc-country-filter').data('cities-url');
            var areasUrlTpl = $('#loc-city-filter').data('areas-url');

            function loadCities(countryId, selectedCityId) {
                if (!countryId) {
                    $('#loc-city-filter').html('<option value="">--- {{ translate('all_cities') }} ---</option>');
                    $('#loc-area-filter').html('<option value="">--- {{ translate('all_areas') }} ---</option>');
                    return;
                }
                var url = citiesUrlTpl.replace(':countryId', countryId);
                $.get(url, function(cities) {
                    var html = '<option value="">--- {{ translate('all_cities') }} ---</option>';
                    cities.forEach(function(c) {
                        html += '<option value="' + c.id + '"' + (c.id == selectedCityId ? ' selected' :
                            '') + '>' + c.name + '</option>';
                    });
                    $('#loc-city-filter').html(html);
                    $('#loc-area-filter').html(
                        '<option value="">--- {{ translate('all_areas') }} ---</option>');
                });
            }

            function loadAreas(cityId, selectedAreaId) {
                if (!cityId) {
                    $('#loc-area-filter').html('<option value="">--- {{ translate('all_areas') }} ---</option>');
                    return;
                }
                var url = areasUrlTpl.replace(':cityId', cityId);
                $.get(url, function(areas) {
                    var html = '<option value="">--- {{ translate('all_areas') }} ---</option>';
                    areas.forEach(function(a) {
                        html += '<option value="' + a.id + '"' + (a.id == selectedAreaId ? ' selected' :
                            '') + '>' + a.name + '</option>';
                    });
                    $('#loc-area-filter').html(html);
                });
            }

            // Pre-load areas if city already selected on page load
            var initCityId = '{{ $selectedCityId ?? '' }}';
            var initAreaId = '{{ $selectedAreaId ?? '' }}';
            if (initCityId) {
                loadAreas(initCityId, initAreaId);
            }

            $(document).on('change', '#loc-country-filter', function() {
                loadCities($(this).val(), null);
            });

            $(document).on('change', '#loc-city-filter', function() {
                loadAreas($(this).val(), null);
            });

            $(document).on('click', '#loc-apply-filter', function() {
                var params = new URLSearchParams(window.location.search);
                var countryId = $('#loc-country-filter').val();
                var cityId = $('#loc-city-filter').val();
                var areaId = $('#loc-area-filter').val();

                if (countryId) {
                    params.set('country_id', countryId);
                } else {
                    params.delete('country_id');
                }
                if (cityId) {
                    params.set('city_id', cityId);
                } else {
                    params.delete('city_id');
                }
                if (areaId) {
                    params.set('area_id', areaId);
                } else {
                    params.delete('area_id');
                }

                window.location.href = window.location.pathname + '?' + params.toString();
            });
        })();
    </script>
@endpush
