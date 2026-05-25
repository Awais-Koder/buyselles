@if (($subCategories ?? collect())->isNotEmpty())
    <div class="row">
        @foreach ($subCategories as $sub)
            <div class="col-lg-3 col-md-4 col-sm-6 col-6 p-2">
                <a href="{{ route('category-products', ['slug' => $sub->slug]) }}"
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
