@php
    $blockId = $blockId ?? '0';
    $prefix = 'cdb-'.$blockId;
    $showApplyButton = $showApplyButton ?? true;
    $locationCountries = \App\Models\LocationCountry::where('is_active', true)
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get(['id', 'name']);
    $selectedCountry = $selectedCountry ?? request('country_id');
    $selectedCity = $selectedCity ?? request('city_id');
    $selectedArea = $selectedArea ?? request('area_id');
@endphp

@if ($locationCountries->count() > 0)
    <div class="row g-2 align-items-center category-display-location-filters flex-nowrap overflow-auto pb-1"
         data-block-prefix="{{ $prefix }}">
        <div class="col min-w-0">
            <select class="form-control form-control-sm cdb-country" id="{{ $prefix }}-country" data-prefix="{{ $prefix }}">
                <option value="">--- {{ translate('all_countries') }} ---</option>
                @foreach ($locationCountries as $country)
                    <option value="{{ $country->id }}" {{ (string) $selectedCountry === (string) $country->id ? 'selected' : '' }}>
                        {{ $country->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col min-w-0">
            <select class="form-control form-control-sm cdb-city" id="{{ $prefix }}-city" data-prefix="{{ $prefix }}" {{ $selectedCountry ? '' : 'disabled' }}>
                <option value="">--- {{ translate('all_cities') }} ---</option>
            </select>
        </div>
        <div class="col min-w-0">
            <select class="form-control form-control-sm cdb-area" id="{{ $prefix }}-area" data-prefix="{{ $prefix }}" {{ $selectedCity ? '' : 'disabled' }}>
                <option value="">--- {{ translate('all_areas') }} ---</option>
            </select>
        </div>
        @if ($showApplyButton)
            <div class="col-auto flex-shrink-0" style="width: 5.5rem;">
                <button type="button" class="btn btn--primary btn-sm w-100 text-nowrap cdb-apply-filter">
                    {{ translate('apply') }}
                </button>
            </div>
        @endif
    </div>
    <span class="d-none cdb-location-init"
          data-prefix="{{ $prefix }}"
          data-country="{{ $selectedCountry }}"
          data-city="{{ $selectedCity }}"
          data-area="{{ $selectedArea }}"
          data-cities-url="{{ route('get-location-cities', ':id') }}"
          data-areas-url="{{ route('get-location-areas', ':id') }}"></span>
@endif
