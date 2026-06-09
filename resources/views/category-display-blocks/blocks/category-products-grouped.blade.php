@php
    $groupedProducts = $groupedProducts ?? [];
@endphp

@if (count($groupedProducts) > 0)
    @foreach ($groupedProducts as $group)
        <div class="mb-4">
            <h5 class="fw-bold mb-3">{{ ($group['category'] ?? $group['sub_category'])->name }}</h5>
            @include('category-display-blocks._products-grid', [
                'products' => $group['products'],
                'themeKey' => $themeKey ?? theme_root_path(),
            ])
        </div>
    @endforeach
@else
    @include('category-display-blocks._empty-placeholder', [
        'message' => translate('no_product_found'),
        'icon' => 'product',
    ])
@endif
