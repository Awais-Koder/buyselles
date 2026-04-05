@php
    $locationCountries = \App\Models\LocationCountry::where('is_active', true)
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get(['id', 'name']);
    $locCountryId = request('country_id');
    $locCityId    = request('city_id');
    $locAreaId    = request('area_id');
@endphp

@if ($locationCountries->count() > 0)
    <div>
        <h6 class="mb-3">{{ translate('location') }}</h6>

        <div class="d-flex flex-column gap-2">
            <select class="form-select form-select-sm real-time-action-update" name="country_id" id="location-country-filter">
                <option value="">--- {{ translate('all_locations') }} ---</option>
                <option value="global" {{ $locCountryId == 'global' ? 'selected' : '' }}>{{ translate('global') }} ({{ translate('all_countries') }})</option>
                @foreach ($locationCountries as $country)
                    <option value="{{ $country->id }}"
                        {{ $locCountryId == $country->id ? 'selected' : '' }}>
                        {{ $country->name }}
                    </option>
                @endforeach
            </select>

            <select class="form-select form-select-sm real-time-action-update" name="city_id" id="location-city-filter"
                {{ $locCountryId && $locCountryId != 'global' ? '' : 'disabled' }}>
                <option value="">--- {{ translate('all_cities') }} ---</option>
            </select>

            <select class="form-select form-select-sm real-time-action-update" name="area_id" id="location-area-filter"
                {{ $locCityId ? '' : 'disabled' }}>
                <option value="">--- {{ translate('all_areas') }} ---</option>
            </select>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const countrySelect = document.getElementById('location-country-filter');
            const citySelect = document.getElementById('location-city-filter');
            const areaSelect = document.getElementById('location-area-filter');
            const initCountry = '{{ $locCountryId }}';
            const initCity    = '{{ $locCityId }}';
            const initArea    = '{{ $locAreaId }}';

            if (initCountry && initCountry !== 'global') {
                loadCities(initCountry, initCity, function() {
                    if (initCity) {
                        loadAreas(initCity, initArea);
                    }
                });
            }

            countrySelect.addEventListener('change', function() {
                citySelect.innerHTML = '<option value="">--- {{ translate('all_cities') }} ---</option>';
                areaSelect.innerHTML = '<option value="">--- {{ translate('all_areas') }} ---</option>';
                citySelect.disabled = true;
                areaSelect.disabled = true;
                citySelect.value = '';
                areaSelect.value = '';

                if (this.value && this.value !== 'global') {
                    loadCities(this.value);
                }
            });

            citySelect.addEventListener('change', function() {
                areaSelect.innerHTML = '<option value="">--- {{ translate('all_areas') }} ---</option>';
                areaSelect.disabled = true;
                areaSelect.value = '';

                if (this.value) {
                    loadAreas(this.value);
                }
            });

            function loadCities(countryId, selectedCityId, callback) {
                fetch('{{ route('get-location-cities', ':id') }}'.replace(':id', countryId))
                    .then(response => response.json())
                    .then(cities => {
                        citySelect.innerHTML = '<option value="">--- {{ translate('all_cities') }} ---</option>';
                        cities.forEach(city => {
                            const selected = selectedCityId && city.id == selectedCityId ? 'selected' : '';
                            citySelect.innerHTML += '<option value="' + city.id + '" ' + selected + '>' + city.name + '</option>';
                        });
                        citySelect.disabled = false;
                        if (callback) { callback(); }
                    });
            }

            function loadAreas(cityId, selectedAreaId) {
                fetch('{{ route('get-location-areas', ':id') }}'.replace(':id', cityId))
                    .then(response => response.json())
                    .then(areas => {
                        areaSelect.innerHTML = '<option value="">--- {{ translate('all_areas') }} ---</option>';
                        areas.forEach(area => {
                            const selected = selectedAreaId && area.id == selectedAreaId ? 'selected' : '';
                            areaSelect.innerHTML += '<option value="' + area.id + '" ' + selected + '>' + area.name + '</option>';
                        });
                        areaSelect.disabled = false;
                    });
            }
        });
    </script>
@endif
