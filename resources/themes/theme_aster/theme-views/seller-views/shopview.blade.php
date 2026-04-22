@php use App\Utils\Helpers; @endphp
@extends('theme-views.layouts.app')

@section('title',translate('shop_Page').' | '.$web_config['company_name'].' '.translate('ecommerce'))

@push('css_or_js')
    @if($shopInfoArray['author_type'] != "admin")
        <meta property="og:image" content="{{ $shopInfoArray['image_full_url']['path'] }}"/>
        <meta property="og:title" content="{{ $shopInfoArray['name']}} "/>
        <meta property="og:url" content="{{route('vendor-shop',['slug' => $shopInfoArray['slug']])}}">
    @else
        <meta property="og:image" content="{{ getStorageImages(path: getInHouseShopConfig(key: 'image_full_url'), type: 'shop') }}"/>
        <meta property="og:title" content="{{ $shopInfoArray['name']}} "/>
        <meta property="og:url" content="{{route('vendor-shop',['slug' => $shopInfoArray['slug']])}}">
    @endif

  @if($shopInfoArray['author_type'] != "admin")
        <meta property="twitter:card" content="{{$shopInfoArray['image_full_url']['path']}}"/>
        <meta property="twitter:title" content="{{route('vendor-shop',['slug' => $shopInfoArray['slug']])}}"/>
        <meta property="twitter:url" content="{{route('vendor-shop',['slug' => $shopInfoArray['slug']])}}">
    @else
        <meta property="twitter:card" content="{{ getStorageImages(path: getInHouseShopConfig(key: 'image_full_url'), type: 'shop') }}"/>
        <meta property="twitter:title" content="{{route('vendor-shop',['slug' => $shopInfoArray['slug']])}}"/>
        <meta property="twitter:url" content="{{route('vendor-shop',['slug' => $shopInfoArray['slug']])}}">
    @endif

    <meta property="og:description" content="{{ $web_config['meta_description'] }}">
    <meta property="twitter:description" content="{{ $web_config['meta_description'] }}">
@endpush
@section('content')
    <main class="main-content d-flex flex-column gap-3 py-3">

        @include("theme-views.seller-views.partials._shop-main-banner-section", ['shopInfoArray' => $shopInfoArray])

        @include("theme-views.seller-views.partials._shop-featured-products", ['featuredProductsList' => $featuredProductsList])

        <section>
            <div class="container">

                <form method="POST" action="{{ route('vendor-shop', ['slug'=> $slug]) }}" class="product-list-filter">
                    @csrf
                    <input hidden name="offer_type" value="{{ $data['offer_type'] }}">

                    @include('theme-views.product.partials._product-list-header', [
                        'pageTitleContent' => translate('search_product'),
                        'pageProductsCount' => $products->total(),
                        'pageProductsShow' => true,
                        'searchBarSection' => true,
                        'sortBySection' => true,
                        'showProductsFilter' => true,
                    ])

                    <div class="flexible-grid lg-down-1 gap-3 width--16rem">
                        <div class="card filter-toggle-aside mb-5">
                            <div class="d-flex d-lg-none pb-0 p-3 justify-content-end">
                                <button class="filter-aside-close border-0 bg-transparent">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                            <div class="card-body d-flex flex-column gap-4">
                                @include('theme-views.product.partials._filter-product-type')
                                @include('theme-views.product.partials._filter-product-price')
                                @include('theme-views.product.partials._filter-product-categories', [
                                    'productCategories' => $categories,
                                    'dataFrom' => 'flash-deals',
                                ])
                                @include('theme-views.product.partials._filter-product-brands', [
                                    'productBrands' => $activeBrands,
                                    'dataFrom' => 'flash-deals',
                                ])
                                @include('theme-views.product.partials._filter-publishing-houses', [
                                    'productPublishingHouses' => $shopPublishingHouses,
                                    'dataFrom' => 'flash-deals',
                                ])
                                @include('theme-views.product.partials._filter-product-authors', [
                                    'productAuthors' => $digitalProductAuthors,
                                    'dataFrom' => 'flash-deals',
                                ])

                                @include('theme-views.product.partials._filter-product-reviews', [
                                    'productRatings' => $ratings,
                                    'selectedRatings' => $selectedRatings
                                ])
                            </div>
                        </div>

                        <div class="">
                            @php
                                $hasSubCats = isset($subCategories) && $subCategories->isNotEmpty();
                                $catIsSelected = !empty($data['category_id']) || !empty($data['sub_category_id']);
                                $showProducts = !$hasSubCats || $catIsSelected;
                            @endphp
                            @if ($hasSubCats)
                                @php
                                    $breadcrumbItems = [];
                                    $selCatId = $data['category_id'] ?? ($data['sub_category_id'] ?? null);
                                    if ($selCatId) {
                                        $currentCat = App\Models\Category::find($selCatId);
                                        if ($currentCat) {
                                            array_unshift($breadcrumbItems, $currentCat->name);
                                            if ($currentCat->parent_id) {
                                                $parentCat = App\Models\Category::find($currentCat->parent_id);
                                                if ($parentCat) {
                                                    array_unshift($breadcrumbItems, $parentCat->name);
                                                    if ($parentCat->parent_id) {
                                                        $grandParent = App\Models\Category::find($parentCat->parent_id);
                                                        if ($grandParent) {
                                                            array_unshift($breadcrumbItems, $grandParent->name);
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                @endphp
                                <div class="mt-3 mb-4">
                                    @if (!empty($breadcrumbItems))
                                        <p class="text-muted fs-12 mb-1">
                                            {{ translate('category') }}@foreach ($breadcrumbItems as $crumb) <span class="mx-1">&rarr;</span>{{ $crumb }}@endforeach
                                        </p>
                                    @endif
                                    <h5 class="fw-bold mb-3">{{ empty($breadcrumbItems) ? translate('Categories') : end($breadcrumbItems) }}</h5>
                                    <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 g-3">
                                        @foreach ($subCategories as $sub)
                                            <div class="col">
                                                <a href="{{ route('vendor-shop', ['slug' => $slug, $subCatParam => $sub->id, 'data_from' => 'category', 'offer_type' => $data['offer_type'] ?? '', 'page' => 1]) }}"
                                                    class="card text-center text-decoration-none border rounded-3 p-3 h-100 sub-cat-card-aster"
                                                    style="transition: box-shadow .2s, transform .2s;">
                                                    <div class="d-flex justify-content-center mb-2">
                                                        <img src="{{ getStorageImages(path: $sub->icon_full_url, type: 'category') }}"
                                                            alt="{{ $sub->name }}" width="64" height="64"
                                                            class="rounded-circle border p-1" style="object-fit: contain;">
                                                    </div>
                                                    <span class="fs-13 text-dark fw-semibold">{{ $sub->name }}</span>
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <style>
                                    .sub-cat-card-aster:hover { box-shadow: 0 4px 16px rgba(0,0,0,.12); transform: translateY(-2px); }
                                    .sub-cat-card-aster:hover span { color: var(--bs-primary) !important; }
                                </style>
                            @endif
                            @if ($showProducts)
                            <div class="d-flex flex-wrap flex-lg-nowrap align-items-start justify-content-between gap-3 mb-2">

                                <div class="d-flex flex-wrap flex-md-nowrap align-items-center justify-content-between gap-2 gap-md-3 flex-grow-1">
                                    <div class="nav nav-nowrap gap-3 gap-xl-4 {{ $stockClearanceProducts > 0 ? 'nav--tabs' : ''}}">
                                        <a href="{{ route('vendor-shop',['slug' => $slug]) }}" class="text-capitalize {{ request('offer_type') != 'clearance_sale' ? 'active' : '' }}">{{ translate('all_products') }}</a>
                                        @if($stockClearanceSetup && $stockClearanceProducts > 0)
                                            <a href="{{ route('vendor-shop',['slug' => $slug, 'offer_type' => 'clearance_sale']) }}" class="text-capitalize {{ request('offer_type') == 'clearance_sale' ? 'active' : '' }}">{{ translate('clearance_sale') }}</a>
                                        @endif
                                    </div>

                                    <ul class="product-view-option option-select-btn align-items-center gap-3">
                                        <li>
                                            <label>
                                                <input type="radio" name="product_view" value="grid-view" hidden=""
                                                    {{(!session('product_view_style') || session('product_view_style') == 'grid-view' ? 'checked' : '' )}}>
                                                <span class="py-2 d-flex align-items-center gap-2 text-capitalize"><i
                                                        class="bi bi-grid-fill"></i> {{translate('grid_view')}}</span>
                                            </label>
                                        </li>
                                        <li>
                                            <label>
                                                <input type="radio" name="product_view" value="list-view" hidden=""
                                                    {{(session('product_view_style') == 'list-view'?'checked':'')}}>
                                                <span class="py-2 d-flex align-items-center gap-2 text-capitalize"><i
                                                        class="bi bi-list-ul"></i> {{translate('list_view')}}</span>
                                            </label>
                                        </li>
                                    </ul>
                                    <button class="toggle-filter square-btn btn btn-outline-primary rounded d-lg-none">
                                        <i class="bi bi-funnel"></i>
                                    </button>
                                </div>
                            </div>
                            @php $decimal_point_settings = getWebConfig(name: 'decimal_point_settings'); @endphp
                            <div id="ajax-products-view">
                                @include('theme-views.product._ajax-products',['products'=>$products,'decimal_point_settings'=>$decimal_point_settings])
                            </div>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </main>

    <span id="filter-url" data-url="{{ route('vendor-shop', ['slug' => $slug]) }}"></span>
    <span id="product-view-style-url" data-url="{{route('product_view_style')}}"></span>
    <span id="shop-follow-url" data-url="{{route('shop-follow')}}"></span>
@endsection

@push('script')
    <script src="{{ theme_asset(path: 'assets/js/product-view.js') }}"></script>
@endpush
