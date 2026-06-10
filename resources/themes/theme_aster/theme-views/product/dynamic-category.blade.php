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
                                @foreach ($breadcrumbs as $crumb)
                                    @if ($crumb['url'])
                                        <li class="breadcrumb-item"><a href="{{ $crumb['url'] }}">{{ $crumb['label'] }}</a></li>
                                    @else
                                        <li class="breadcrumb-item active">{{ $crumb['label'] }}</li>
                                    @endif
                                @endforeach
                            </ol>
                        </nav>
                    </div>
                </div>

                @php
                    $contextParams = [];
                    if (isset($context['parent_id'])) {
                        $contextParams['parent_id'] = $context['parent_id'];
                    }
                    if (isset($context['parent_name'])) {
                        $contextParams['parent_name'] = $context['parent_name'];
                    }
                    $contextQuery = !empty($contextParams) ? '&'.http_build_query($contextParams) : '';
                    $prevUrl = url()->current().'?step='.max(0, $currentStepIndex - 1).$contextQuery;
                    $nextUrl = url()->current().'?step='.min($totalSteps - 1, $currentStepIndex + 1).$contextQuery;
                @endphp

                @if ($hasPrev || $hasNext)
                    <div class="d-flex justify-content-between mb-3">
                        <div>
                            @if ($hasPrev)
                                <a href="{{ $prevUrl }}" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-arrow-left"></i> {{ translate('back') }}
                                </a>
                            @endif
                        </div>
                        <div>
                            @if ($hasNext)
                                <a href="{{ $nextUrl }}" class="btn btn-primary btn-sm">
                                    {{ translate('next') }} <i class="bi bi-arrow-right"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                @endif

                @if ($totalSteps > 1)
                    <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                        @for ($i = 0; $i < $totalSteps; $i++)
                            <span class="badge {{ $i === $currentStepIndex ? 'bg-primary' : 'bg-secondary' }}"
                                  style="width: 24px; height: 6px; border-radius: 3px; display: inline-block;"></span>
                        @endfor
                        <small class="text-muted ms-2">{{ translate('step') }} {{ $currentStepIndex + 1 }} {{ translate('of') }} {{ $totalSteps }}</small>
                    </div>
                @endif

                @if (! $currentBlock)
                    <div class="text-center py-5 text-muted">{{ translate('no_content_configured_for_this_category') }}</div>
                @else
                    @php
                        $blockPayloads = [
                            $currentBlock->id => [
                                'block' => $currentBlock,
                                'title' => $currentStepData['title'],
                                'data' => $currentStepData['data'],
                            ],
                        ];
                    @endphp
                    @include('category-display-blocks._block', [
                        'block' => $currentBlock,
                        'category' => $category,
                        'blockPayloads' => $blockPayloads,
                        'themeKey' => $themeKey,
                    ])
                @endif
            </div>
        </section>
    </main>
@endsection

@push('script')
    <script src="{{ theme_asset(path: 'public/assets/front-end/js/category-display-blocks.js') }}"></script>
@endpush
