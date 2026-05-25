@extends('layouts.front-end.app')

@section('title', $pageTitleContent)

@push('css_or_js')
    <meta property="og:image" content="{{ $web_config['web_logo']['path'] }}" />
    <meta property="og:title" content="{{ $pageTitleContent }} — {{ $web_config['company_name'] }}" />
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:description" content="{{ $web_config['meta_description'] }}">
@endpush

@section('content')
    <div class="container py-3 mb-4 rtl text-align-direction">
        <div class="bg-primary-light rounded-10 my-3 p-3 p-sm-4"
             data-bg-img="{{ theme_asset(path: 'public/assets/front-end/img/media/bg.png') }}">
            <div class="d-flex flex-column gap-1 text-primary">
                <h4 class="mb-0 text-start fw-bold text-primary text-uppercase">{{ $category->name }}</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 fs-13">
                        <li class="breadcrumb-item">
                            <a href="{{ route('home') }}" class="text-primary">{{ translate('Home') }}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('categories') }}" class="text-primary">{{ translate('categories') }}</a>
                        </li>
                        <li class="breadcrumb-item active text-primary fw-semibold">{{ $category->name }}</li>
                    </ol>
                </nav>
            </div>
        </div>

        @if ($blocks->isEmpty())
            <div class="text-center py-5 text-muted">{{ translate('no_content_configured_for_this_category') }}</div>
        @else
            @foreach ($blocks as $block)
                @include('category-display-blocks._block', [
                    'block' => $block,
                    'category' => $category,
                    'blockPayloads' => $blockPayloads,
                    'themeKey' => $themeKey,
                ])
            @endforeach
        @endif
    </div>
@endsection

@push('script')
    <script src="{{ theme_asset(path: 'public/assets/front-end/js/category-display-blocks.js') }}"></script>
@endpush
