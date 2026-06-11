@php
    $nextStep = ($currentStepIndex ?? 0) + 1;
    $stepContext = $context ?? [];
    $hasSubSubCategories = false;
    if (($subCategoriesWithChildren ?? collect())->isNotEmpty()) {
        foreach ($subCategoriesWithChildren as $subCategory) {
            if ($subCategory->childes && $subCategory->childes->isNotEmpty()) {
                $hasSubSubCategories = true;
                break;
            }
        }
    }
@endphp

@if ($hasSubSubCategories)
    @foreach ($subCategoriesWithChildren as $subCategory)
        @if ($subCategory->childes && $subCategory->childes->isNotEmpty())
            <div class="mb-3">
                <h6 class="fw-semibold mb-2">{{ $subCategory->name }}</h6>
                <div class="row">
                    @foreach ($subCategory->childes as $subSub)
                        @php
                            $subSubParams = array_filter([
                                'step' => $nextStep,
                                'parent_id' => $subSub->id,
                                'parent_name' => $subSub->name,
                                'vendor_id' => $stepContext['vendor_id'] ?? null,
                                'vendor_name' => $stepContext['vendor_name'] ?? null,
                            ], fn ($value) => $value !== null && $value !== '');
                        @endphp
                        <div class="col-lg-3 col-md-4 col-sm-6 col-6 p-2">
                            <a href="{{ url()->current() }}?{{ http_build_query($subSubParams) }}"
                               class="card text-center text-decoration-none border rounded-3 overflow-hidden h-100">
                                <div style="aspect-ratio: 1/1; overflow: hidden;">
                                    <img src="{{ getStorageImages(path: $subSub->icon_full_url, type: 'category') }}"
                                         alt="{{ $subSub->name }}" class="w-100 h-100" style="object-fit: cover;">
                                </div>
                                <div class="p-2">
                                    <span class="fs-13 text-dark fw-semibold">{{ $subSub->name }}</span>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endforeach
@else
    @include('category-display-blocks._empty-placeholder', [
        'message' => translate('No_sub_categories_found'),
        'icon' => 'category',
    ])
@endif
