@php
    $productCount = isset($products)
        ? ($products instanceof \Illuminate\Support\Collection || $products instanceof \Illuminate\Pagination\AbstractPaginator
            ? $products->count()
            : count($products))
        : 0;
@endphp

@if ($productCount > 0)
    @include('category-display-blocks._products-grid', [
        'products' => $products instanceof \Illuminate\Pagination\AbstractPaginator ? $products : collect($products),
        'themeKey' => $themeKey ?? theme_root_path(),
    ])
    @if (!empty($category?->slug))
        <div class="text-end mt-2">
            <a href="{{ route('products') }}?data_from=category&category_id={{ $category->id }}"
               class="btn btn-outline-primary btn-sm">
                {{ translate('view_all') }}
            </a>
        </div>
    @endif
@else
    @include('category-display-blocks._empty-placeholder', [
        'message' => translate('no_product_found'),
        'icon' => 'product',
    ])
@endif
