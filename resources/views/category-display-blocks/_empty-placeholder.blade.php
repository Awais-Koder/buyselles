@php
    $message = $message ?? translate('no_product_found');
    $icon = $icon ?? 'product';
@endphp

<div class="category-display-empty text-center py-4 px-3 my-2 rounded bg-section">
    @if ($icon === 'product')
        <img src="{{ theme_asset(path: 'public/assets/front-end/img/media/product.svg') }}" class="img-fluid mb-2" width="64" alt="">
    @elseif ($icon === 'vendor')
        <img src="{{ dynamicAsset(path: 'public/assets/front-end/img/empty-icons/empty-vendor.svg') }}" class="img-fluid mb-2" width="64" alt="" onerror="this.style.display='none'">
    @elseif ($icon === 'category')
        <img src="{{ dynamicAsset(path: 'public/assets/front-end/img/empty-icons/empty-category.svg') }}" class="img-fluid mb-2" width="64" alt="">
    @endif
    <p class="mb-0 text-muted fs-14">{{ $message }}</p>
</div>
