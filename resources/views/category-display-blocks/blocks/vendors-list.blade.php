@php
    $vendors = $data['vendors'] ?? collect();
@endphp

<div class="category-display-ajax-block"
     data-ajax-url="{{ route('category-display.vendors', ['categoryId' => $category->id]) }}"
     data-block-id="{{ $block->id }}">
    <div class="row g-2 mb-3">
        <div class="col-md-8">
            <input type="search" class="form-control form-control-sm cdb-search-input"
                   placeholder="{{ translate('search_by_shop_name') }}"
                   value="{{ request('search') }}">
        </div>
        <div class="col-md-4">
            <button type="button" class="btn btn-primary btn-sm w-100 cdb-apply-filter">
                {{ translate('search') }}
            </button>
        </div>
    </div>

    @include('category-display-blocks._location-filters', [
        'blockId' => $block->id,
        'selectedCountry' => request('country_id'),
        'selectedCity' => request('city_id'),
        'selectedArea' => request('area_id'),
    ])

    <div class="cdb-ajax-content mt-3">
        @include('category-display-blocks._vendors-grid', [
            'vendors' => $vendors,
            'themeKey' => $themeKey,
        ])
    </div>
</div>
