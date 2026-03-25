@php
    $locationCountries = \App\Models\LocationCountry::where('is_active', true)
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get(['id', 'name']);
@endphp

@if ($locationCountries->count() > 0)
    <div>
        <h6 class="mb-3">{{ translate('location') }}</h6>

        @if (session('location_area_id'))
            <div class="d-flex align-items-center justify-content-between mb-2 px-2 py-1 rounded bg-light">
                <small class="text-muted">
                    <i class="bi bi-geo-alt"></i> {{ session('location_label') }}
                </small>
                <button type="button" class="btn btn-sm btn-link text-danger p-0 clear-location-filter"
                    title="{{ translate('clear') }}">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        @endif

        <div class="d-flex flex-column gap-2">
            <select class="form-select form-select-sm" id="location-country-filter">
                <option value="">{{ translate('select_country') }}</option>
                @foreach ($locationCountries as $country)
                    <option value="{{ $country->id }}"
                        {{ session('location_country_id') == $country->id ? 'selected' : '' }}>
                        {{ $country->name }}
                    </option>
                @endforeach
            </select>

            <select class="form-select form-select-sm" id="location-city-filter"
                {{ session('location_country_id') ? '' : 'disabled' }}>
                <option value="">{{ translate('select_city') }}</option>
            </select>

            <select class="form-select form-select-sm" id="location-area-filter"
                {{ session('location_city_id') ? '' : 'disabled' }}>
                <option value="">{{ translate('select_area') }}</option>
            </select>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const countrySelect = document.getElementById('location-country-filter');
            const citySelect = document.getElementById('location-city-filter');
            const areaSelect = document.getElementById('location-area-filter');

            if (countrySelect.value) {
                loadCities(countrySelect.value, '{{ session('location_city_id') }}');
            }

            countrySelect.addEventListener('change', function() {
                citySelect.innerHTML = '<option value="">{{ translate('select_city') }}</option>';
                areaSelect.innerHTML = '<option value="">{{ translate('select_area') }}</option>';
                citySelect.disabled = true;
                areaSelect.disabled = true;

                if (this.value) {
                    loadCities(this.value);
                }
            });

            citySelect.addEventListener('change', function() {
                areaSelect.innerHTML = '<option value="">{{ translate('select_area') }}</option>';
                areaSelect.disabled = true;

                if (this.value) {
                    loadAreas(this.value);
                }
            });

            areaSelect.addEventListener('change', function() {
                if (this.value) {
                    applyLocationFilter(this.value);
                }
            });

            function loadCities(countryId, selectedCityId) {
                fetch('{{ route('get-location-cities', ':id') }}'.replace(':id', countryId))
                    .then(response => response.json())
                    .then(cities => {
                        citySelect.innerHTML = '<option value="">{{ translate('select_city') }}</option>';
                        cities.forEach(city => {
                            const selected = selectedCityId && city.id == selectedCityId ? 'selected' :
                                '';
                            citySelect.innerHTML +=
                                `<option value="${city.id}" ${selected}>${city.name}</option>`;
                        });
                        citySelect.disabled = false;

                        if (selectedCityId && citySelect.value) {
                            loadAreas(selectedCityId, '{{ session('location_area_id') }}');
                        }
                    });
            }

            function loadAreas(cityId, selectedAreaId) {
                fetch('{{ route('get-location-areas', ':id') }}'.replace(':id', cityId))
                    .then(response => response.json())
                    .then(areas => {
                        areaSelect.innerHTML = '<option value="">{{ translate('select_area') }}</option>';
                        areas.forEach(area => {
                            const selected = selectedAreaId && area.id == selectedAreaId ? 'selected' :
                                '';
                            areaSelect.innerHTML +=
                                `<option value="${area.id}" ${selected}>${area.name}</option>`;
                        });
                        areaSelect.disabled = false;
                    });
            }

            function applyLocationFilter(areaId) {
                fetch('{{ route('set-location') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            area_id: areaId
                        })
                    }).then(response => response.json())
                    .then(() => {
                        window.location.reload();
                    });
            }

            const clearBtn = document.querySelector('.clear-location-filter');
            if (clearBtn) {
                clearBtn.addEventListener('click', function() {
                    fetch('{{ route('set-location') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                area_id: null
                            })
                        }).then(response => response.json())
                        .then(() => {
                            window.location.reload();
                        });
                });
            }
        });
    </script>
@endif
