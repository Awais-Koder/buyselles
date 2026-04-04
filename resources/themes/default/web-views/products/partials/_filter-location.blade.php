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
        <h6 class="font-semibold fs-13 mb-2">{{ translate('location') }}</h6>

        <div class="mb-2">
            <select class="form-control form-control-sm" id="prod-location-country">
                <option value="">--- {{ translate('all_countries') }} ---</option>
                @foreach ($locationCountries as $country)
                    <option value="{{ $country->id }}" {{ $locCountryId == $country->id ? 'selected' : '' }}>
                        {{ $country->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-2">
            <select class="form-control form-control-sm" id="prod-location-city" {{ $locCountryId ? '' : 'disabled' }}>
                <option value="">--- {{ translate('all_cities') }} ---</option>
            </select>
        </div>

        <div class="mb-2">
            <select class="form-control form-control-sm" id="prod-location-area" {{ $locCityId ? '' : 'disabled' }}>
                <option value="">--- {{ translate('all_areas') }} ---</option>
            </select>
        </div>

        <div class="d-flex gap-2">
            <button type="button" id="prod-location-apply" class="btn btn-primary btn-sm flex-grow-1">
                {{ translate('apply') }}
            </button>
            @if ($locCountryId || $locCityId || $locAreaId)
                <button type="button" id="prod-location-clear" class="btn btn-outline-secondary btn-sm">
                    {{ translate('clear') }}
                </button>
            @endif
        </div>
    </div>

    <script>
        (function () {
            var citiesUrl = '{{ route('get-location-cities', ':id') }}';
            var areasUrl  = '{{ route('get-location-areas', ':id') }}';
            var initCountry = '{{ $locCountryId }}';
            var initCity    = '{{ $locCityId }}';
            var initArea    = '{{ $locAreaId }}';

            var countryEl = document.getElementById('prod-location-country');
            var cityEl    = document.getElementById('prod-location-city');
            var areaEl    = document.getElementById('prod-location-area');

            function loadCities(countryId, selectedCityId, callback) {
                fetch(citiesUrl.replace(':id', countryId))
                    .then(function (r) { return r.json(); })
                    .then(function (cities) {
                        var html = '<option value="">--- {{ translate('all_cities') }} ---</option>';
                        cities.forEach(function (c) {
                            html += '<option value="' + c.id + '"' + (c.id == selectedCityId ? ' selected' : '') + '>' + c.name + '</option>';
                        });
                        cityEl.innerHTML = html;
                        cityEl.disabled  = false;
                        if (callback) { callback(); }
                    });
            }

            function loadAreas(cityId, selectedAreaId) {
                fetch(areasUrl.replace(':id', cityId))
                    .then(function (r) { return r.json(); })
                    .then(function (areas) {
                        var html = '<option value="">--- {{ translate('all_areas') }} ---</option>';
                        areas.forEach(function (a) {
                            html += '<option value="' + a.id + '"' + (a.id == selectedAreaId ? ' selected' : '') + '>' + a.name + '</option>';
                        });
                        areaEl.innerHTML = html;
                        areaEl.disabled  = false;
                    });
            }

            // Pre-load saved selections on page load
            if (initCountry) {
                loadCities(initCountry, initCity, function () {
                    if (initCity) {
                        loadAreas(initCity, initArea);
                    }
                });
            }

            countryEl.addEventListener('change', function () {
                cityEl.innerHTML = '<option value="">--- {{ translate('all_cities') }} ---</option>';
                areaEl.innerHTML = '<option value="">--- {{ translate('all_areas') }} ---</option>';
                cityEl.disabled  = true;
                areaEl.disabled  = true;
                if (this.value) { loadCities(this.value, null, null); }
            });

            cityEl.addEventListener('change', function () {
                areaEl.innerHTML = '<option value="">--- {{ translate('all_areas') }} ---</option>';
                areaEl.disabled  = true;
                if (this.value) { loadAreas(this.value, null); }
            });

            // Apply: update productListPageData and trigger AJAX filter
            document.getElementById('prod-location-apply').addEventListener('click', function () {
                var country = countryEl.value || '';
                var city    = cityEl.value || '';
                var area    = areaEl.value || '';

                // Update the AJAX filter state if it exists (product list pages)
                if (typeof productListPageData !== 'undefined') {
                    productListPageData.country_id = country;
                    productListPageData.city_id    = city;
                    productListPageData.area_id    = area;
                    productListPageData.page       = 1;
                    getProductListFilterRender();
                } else {
                    // Fallback: URL navigation for pages without AJAX filter
                    var params = new URLSearchParams(window.location.search);
                    params.delete('country_id');
                    params.delete('city_id');
                    params.delete('area_id');
                    params.delete('page');
                    if (country) { params.set('country_id', country); }
                    if (city)    { params.set('city_id', city); }
                    if (area)    { params.set('area_id', area); }
                    window.location.href = window.location.pathname + '?' + params.toString();
                }
            });

            // Clear: reset location and refresh
            var clearBtn = document.getElementById('prod-location-clear');
            if (clearBtn) {
                clearBtn.addEventListener('click', function () {
                    countryEl.value = '';
                    cityEl.innerHTML = '<option value="">--- {{ translate('all_cities') }} ---</option>';
                    cityEl.disabled  = true;
                    areaEl.innerHTML = '<option value="">--- {{ translate('all_areas') }} ---</option>';
                    areaEl.disabled  = true;

                    if (typeof productListPageData !== 'undefined') {
                        productListPageData.country_id = '';
                        productListPageData.city_id    = '';
                        productListPageData.area_id    = '';
                        productListPageData.page       = 1;
                        getProductListFilterRender();
                    } else {
                        var params = new URLSearchParams(window.location.search);
                        params.delete('country_id');
                        params.delete('city_id');
                        params.delete('area_id');
                        params.delete('page');
                        window.location.href = window.location.pathname + '?' + params.toString();
                    }
                });
            }
        })();
    </script>
@endif
