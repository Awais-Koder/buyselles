@php
    $products = $data['products'] ?? collect();
    $vendors = $data['vendors'] ?? collect();
    $locationLabel = $data['location_label'] ?? translate('your_area');
@endphp

<div class="category-display-ajax-block"
     data-ajax-url="{{ route('category-display.location-pipeline', ['categoryId' => $category->id]) }}"
     data-block-id="{{ $block->id }}">
    <div class="mb-3">
        @include('category-display-blocks._location-filters', [
            'blockId' => $block->id,
            'selectedCountry' => request('country_id'),
            'selectedCity' => request('city_id'),
            'selectedArea' => request('area_id'),
        ])
    </div>

    <div class="cdb-ajax-content mt-3">
        @if (!request()->hasAny(['country_id', 'city_id', 'area_id']))
            @include('category-display-blocks._empty-placeholder', [
                'message' => translate('Please_select_country_city_or_area_to_view_results'),
                'icon' => 'category',
            ])
        @else
        @include('category-display-blocks._location-pipeline-results', [
            'products' => $products,
            'vendors' => $vendors,
            'locationLabel' => $locationLabel,
            'themeKey' => $themeKey,
        ])
        @endif
    </div>
</div>
