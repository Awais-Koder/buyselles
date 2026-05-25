@extends('theme-views.layouts.app')

@section('title', $pageTitleContent)

@push('css_or_js')
    <meta property="og:image" content="{{ $web_config['web_logo']['path'] }}" />
    <meta property="og:title" content="{{ $pageTitleContent }} — {{ $web_config['company_name'] }}" />
    <meta property="og:url" content="{{ url()->current() }}">
@endpush

@section('content')
    <main class="main-content d-flex flex-column gap-3 pt-3">
        <section>
            <div class="container">
                <div class="card mb-3">
                    <div class="card-body">
                        <h3 class="mb-2">{{ $category->name }}</h3>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ translate('home') }}</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('categories') }}">{{ translate('categories') }}</a></li>
                                <li class="breadcrumb-item active">{{ $category->name }}</li>
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
        </section>
    </main>
@endsection

@push('script')
    <script src="{{ theme_asset(path: 'public/assets/front-end/js/category-display-blocks.js') }}"></script>
@endpush
