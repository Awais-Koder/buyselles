@php
    $payload = $blockPayloads[$block->id] ?? null;
    $title = $payload['title'] ?? '';
    $data = $payload['data'] ?? [];
@endphp

@if ($payload)
    <section class="category-display-block mb-4 pb-3 border-bottom" id="category-block-{{ $block->id }}">
        <h4 class="fw-bold mb-2">{{ $title }}</h4>

        @switch($block->block_type)
            @case('sub_categories')
                @include('category-display-blocks.blocks.sub-categories', $data)
                @break
            @case('sub_category_products')
            @case('sub_sub_category_products')
                @include('category-display-blocks.blocks.category-products', $data + ['themeKey' => $themeKey, 'category' => $category])
                @break
            @case('sub_sub_categories')
                @include('category-display-blocks.blocks.sub-sub-categories', $data)
                @break
            @case('mixed_products')
                @include('category-display-blocks.blocks.mixed-products', [
                    'block' => $block,
                    'category' => $category,
                    'data' => $data,
                    'themeKey' => $themeKey,
                ])
                @break
            @case('vendors_list')
                @include('category-display-blocks.blocks.vendors-list', [
                    'block' => $block,
                    'category' => $category,
                    'data' => $data,
                    'themeKey' => $themeKey,
                ])
                @break
            @case('location_pipeline')
                @include('category-display-blocks.blocks.location-pipeline', [
                    'block' => $block,
                    'category' => $category,
                    'data' => $data,
                    'themeKey' => $themeKey,
                ])
                @break
        @endswitch
    </section>
@endif
