@extends('layouts.front-end.app')

@section('title', $pageTitleContent)

@push('css_or_js')
    <meta property="og:image" content="{{ $web_config['web_logo']['path'] }}" />
    <meta property="og:title" content="{{ $pageTitleContent }} — {{ $web_config['company_name'] }}" />
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:description" content="{{ $web_config['meta_description'] }}">
    <meta property="twitter:card" content="{{ $web_config['web_logo']['path'] }}" />
    <meta property="twitter:title" content="{{ $pageTitleContent }} — {{ $web_config['company_name'] }}" />
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:description" content="{{ $web_config['meta_description'] }}">
@endpush

@section('content')
    <div class="container pb-3 mb-2 mb-md-4 rtl __inline-52 text-align-direction">

        {{-- Page header + breadcrumb --}}
        <div class="bg-primary-light rounded-10 my-4 p-3 p-sm-4"
            data-bg-img="{{ theme_asset(path: 'public/assets/front-end/img/media/bg.png') }}">
            <div class="row align-items-center g-3">
                <div class="col-xl-8 col-lg-7 col-md-6">
                    <div class="d-flex flex-column gap-1 text-primary">
                        <h4 class="mb-0 text-start fw-bold text-primary text-uppercase">
                            {{ $category->name }}
                        </h4>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0 fs-13">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('home') }}" class="text-primary">{{ translate('Home') }}</a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="{{ route('categories') }}"
                                        class="text-primary">{{ translate('categories') }}</a>
                                </li>
                                <li class="breadcrumb-item active text-primary fw-semibold">
                                    {{ $category->name }}
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>
                @if ($category->banner_full_url ?? false)
                    <div class="col-xl-4 col-lg-5 col-md-6 text-end">
                        <img src="{{ getStorageImages(path: $category->banner_full_url, type: 'category') }}"
                            alt="{{ $category->name }}" class="img-fluid rounded-10" style="max-height:80px;">
                    </div>
                @endif
            </div>
        </div>

        {{-- Sub-categories grid --}}
        @if ($subCategories->isNotEmpty())
            <div class="brand_div-wrap">
                @foreach ($subCategories as $sub)
                    <a href="{{ route('category-products', ['slug' => $sub->slug]) }}" class="brand_div">
                        <img src="{{ getStorageImages(path: $sub->icon_full_url, type: 'category') }}"
                            alt="{{ $sub->name }}">
                        <div>{{ $sub->name }}</div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="d-flex justify-content-center align-items-center pt-5">
                <div class="d-flex flex-column justify-content-center align-items-center gap-3">
                    <img src="{{ dynamicAsset(path: 'public/assets/front-end/img/empty-icons/empty-category.svg') }}"
                        alt="{{ translate('category') }}" class="img-fluid" width="100">
                    <h5 class="text-muted fs-14 font-semi-bold text-center">
                        {{ translate('No_sub_categories_found') }}
                    </h5>
                </div>
            </div>
        @endif

    </div>
@endsection
