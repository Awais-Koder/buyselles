<div class="mb-4">
    <h5 class="fw-semibold fs-16 mb-3">
        {{ translate('Best_Selling_Products_in') }} {{ $locationLabel }}
    </h5>
    @include('category-display-blocks._products-grid', [
        'products' => $products,
        'themeKey' => $themeKey ?? theme_root_path(),
    ])
</div>

<div>
    <h5 class="fw-semibold fs-16 mb-3">
        {{ translate('Verified_Merchants_Operating_in') }} {{ $locationLabel }}
    </h5>
    @include('category-display-blocks._vendors-grid', [
        'vendors' => $vendors,
        'themeKey' => $themeKey ?? theme_root_path(),
    ])
</div>
