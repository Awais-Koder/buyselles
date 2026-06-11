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
                    $backContextParams = $backContext ?? [];
                    $backContextQuery = !empty($backContextParams) ? '&'.http_build_query($backContextParams) : '';
                    $prevUrl = ($previousStepIndex ?? null) !== null
                        ? url()->current().'?step='.$previousStepIndex.$backContextQuery.'&direction=back'
                        : route('categories');

                    $nextContextParams = [];
                    if (isset($context['parent_id'])) {
                        $nextContextParams['parent_id'] = $context['parent_id'];
                    }
                    if (isset($context['parent_name'])) {
                        $nextContextParams['parent_name'] = $context['parent_name'];
                    }
                    if (isset($context['vendor_id'])) {
                        $nextContextParams['vendor_id'] = $context['vendor_id'];
                    }
                    if (isset($context['vendor_name'])) {
                        $nextContextParams['vendor_name'] = $context['vendor_name'];
                    }
                    $nextContextQuery = !empty($nextContextParams) ? '&'.http_build_query($nextContextParams) : '';
                    $nextUrl = ($nextStepIndex ?? null) !== null
                        ? url()->current().'?step='.$nextStepIndex.$nextContextQuery.'&direction=next'
                        : null;
                @endphp

                @if (! ($hasNoContent ?? false))
                    <div class="d-flex justify-content-between mb-3">
                        <div>
                            <a href="{{ $prevUrl }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-arrow-left"></i> {{ translate('back') }}
                            </a>
                        </div>
                        <div>
                            @if ($hasNext && $nextUrl)
                                <a href="{{ $nextUrl }}" class="btn btn-primary btn-sm">
                                    {{ translate('next') }} <i class="bi bi-arrow-right"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                @endif

                @if (! ($hasNoContent ?? false) && ($displayTotalSteps ?? 0) > 1)
                    <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                        @foreach ($dataBlockIndices ?? [] as $index)
                            <span class="badge {{ $index === $currentStepIndex ? 'bg-primary' : 'bg-secondary' }}"
                                  style="width: 24px; height: 6px; border-radius: 3px; display: inline-block;"></span>
                        @endforeach
                        <small class="text-muted ms-2">{{ translate('step') }} {{ $displayStepNumber ?? ($currentStepIndex + 1) }} {{ translate('of') }} {{ $displayTotalSteps ?? $totalSteps }}</small>
                    </div>
                @endif

                @if (($hasNoContent ?? false) || ! $currentBlock)
                    <div class="text-center py-5 px-3">
                        <div class="mb-3">
                            <i class="bi bi-inbox" style="font-size: 3.5rem; color: var(--bs-secondary);"></i>
                        </div>
                        <h5 class="text-muted mb-2">{{ translate('no_content_configured_for_this_category') }}</h5>
                        <p class="text-muted mb-0 fs-14">{{ translate('not_found_anything') }}</p>
                    </div>
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
                        'currentStepIndex' => $currentStepIndex,
                        'nextStepIndex' => $nextStepIndex,
                        'context' => $context,
                    ])
                @endif
            </div>
        </section>
    </main>
@endsection

@push('script')
    <script src="{{ theme_asset(path: 'public/assets/front-end/js/category-display-blocks.js') }}"></script>
@endpush
