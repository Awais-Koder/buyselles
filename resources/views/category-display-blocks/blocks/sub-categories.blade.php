@php
    $nextStep = ($currentStepIndex ?? 0) + 1;
    $stepContext = $context ?? [];
@endphp
@if (($subCategories ?? collect())->isNotEmpty())
    <div class="row">
        @foreach ($subCategories as $sub)
            @php
                $subParams = array_filter([
                    'step' => $nextStep,
                    'parent_id' => $sub->id,
                    'parent_name' => $sub->name,
                    'vendor_id' => $stepContext['vendor_id'] ?? null,
                    'vendor_name' => $stepContext['vendor_name'] ?? null,
                ], fn ($value) => $value !== null && $value !== '');
            @endphp
            <div class="col-lg-3 col-md-4 col-sm-6 col-6 p-2">
                <a href="{{ url()->current() }}?{{ http_build_query($subParams) }}"
                   class="card text-center text-decoration-none border rounded-3 overflow-hidden h-100"
                   style="transition: box-shadow .2s, transform .2s;">
                    <div style="aspect-ratio: 1/1; overflow: hidden;">
                        <img src="{{ getStorageImages(path: $sub->icon_full_url, type: 'category') }}"
                             alt="{{ $sub->name }}" class="w-100 h-100" style="object-fit: cover;">
                    </div>
                    <div class="p-2">
                        <span class="fs-13 text-dark fw-semibold">{{ $sub->name }}</span>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
@else
    @include('category-display-blocks._empty-placeholder', [
        'message' => translate('No_sub_categories_found'),
        'icon' => 'category',
    ])
@endif
