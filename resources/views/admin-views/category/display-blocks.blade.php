@extends('layouts.admin.app')

@section('title', translate('Category_Display_Blocks'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h2 class="h1 mb-2 d-flex gap-10 align-items-center">
                    <img src="{{ dynamicAsset(path: 'public/assets/new/back-end/img/brand-setup.png') }}" alt="">
                    {{ translate('Category_Display_Blocks') }}
                </h2>
                <p class="mb-0 text-muted">
                    {{ translate('configure_the_order_and_visibility_of_sections_shown_on_app_and_web_for') }}
                    <strong>{{ $category->name }}</strong>
                    <span class="opacity-70">({{ translate('ID') }} #{{ $category->id }})</span>
                </p>
            </div>
            <a href="{{ route('admin.category.view') }}" class="btn btn-outline-secondary">
                <i class="fi fi-rr-arrow-left"></i>
                {{ translate('back_to_categories') }}
            </a>
        </div>

        <div class="bg-info bg-opacity-10 fs-12 px-12 py-10 text-dark rounded d-flex gap-2 align-items-center mb-3">
            <i class="fi fi-sr-lightbulb-on text-info"></i>
            <span>{{ translate('drag_blocks_to_reorder_how_the_customer_app_and_web_render_this_main_category') }}</span>
        </div>

        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body d-flex flex-column gap-20">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <h3 class="mb-0">
                                {{ translate('block_layout') }}
                                <span class="badge text-dark bg-body-secondary fw-semibold rounded-50">{{ $blocks->count() }}</span>
                            </h3>
                            @if($blocks->count() > 1)
                                <span class="fs-12 text-muted">{{ translate('drag_to_reorder') }}</span>
                            @endif
                        </div>

                        @if($blocks->isEmpty())
                            @include('layouts.admin.partials._empty-state', ['text' => 'no_display_blocks_configured', 'image' => 'default'])
                        @else
                            <ul class="list-group list-group-flush category-display-blocks-sortable" id="category-display-blocks-list">
                                @foreach($blocks as $block)
                                    @php
                                        $blockType = \App\Enums\CategoryDisplayBlockType::tryFrom($block->block_type);
                                        $label = $blockType?->label() ?? $block->block_type;
                                        $description = $blockType?->description() ?? '';
                                        $customTitle = $block->settings['title'] ?? null;
                                    @endphp
                                    <li class="list-group-item px-0 border-0" data-block-id="{{ $block->id }}">
                                        <div class="card border shadow-none mb-0 category-display-block-item">
                                            <div class="card-body d-flex flex-wrap align-items-center gap-3">
                                                <button type="button"
                                                        class="btn btn-outline-secondary icon-btn category-display-block-drag-handle"
                                                        title="{{ translate('drag_to_reorder') }}"
                                                        @if($blocks->count() <= 1) disabled @endif>
                                                    <i class="fi fi-rr-grip-dots-vertical"></i>
                                                </button>

                                                <div class="flex-grow-1 min-w-200">
                                                    <div class="fw-semibold">{{ $label }}</div>
                                                    @if($description)
                                                        <div class="fs-12 text-muted mt-1">{{ $description }}</div>
                                                    @endif
                                                    @if($customTitle)
                                                        <div class="fs-12 mt-1">
                                                            <span class="text-muted">{{ translate('section_title') }}:</span>
                                                            {{ $customTitle }}
                                                        </div>
                                                    @endif
                                                    <code class="fs-10 mt-1 d-inline-block">{{ $block->block_type }}</code>
                                                </div>

                                                <div class="d-flex align-items-center gap-3 flex-wrap">
                                                    <label class="switcher" for="block-status-{{ $block->id }}">
                                                        <input class="switcher_input category-display-block-status"
                                                               type="checkbox"
                                                               id="block-status-{{ $block->id }}"
                                                               data-id="{{ $block->id }}"
                                                               {{ $block->is_active ? 'checked' : '' }}>
                                                        <span class="switcher_control"></span>
                                                    </label>

                                                    @if(in_array($block->block_type, ['mixed_products', 'vendors_list', 'location_pipeline'], true))
                                                        <button type="button"
                                                                class="btn btn-outline-info icon-btn"
                                                                data-bs-toggle="collapse"
                                                                data-bs-target="#block-settings-{{ $block->id }}"
                                                                title="{{ translate('settings') }}">
                                                            <i class="fi fi-sr-settings"></i>
                                                        </button>
                                                    @endif

                                                    <form action="{{ route('admin.category.display-blocks.delete') }}" method="post"
                                                          class="d-inline"
                                                          onsubmit="return confirm('{{ translate('want_to_delete_this_block') }}?');">
                                                        @csrf
                                                        <input type="hidden" name="id" value="{{ $block->id }}">
                                                        <input type="hidden" name="category_id" value="{{ $category->id }}">
                                                        <button type="submit" class="btn btn-outline-danger icon-btn" title="{{ translate('delete') }}">
                                                            <i class="fi fi-rr-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>

                                            @if(in_array($block->block_type, ['mixed_products', 'vendors_list', 'location_pipeline'], true))
                                                <div class="collapse border-top" id="block-settings-{{ $block->id }}">
                                                    <div class="card-body pt-3">
                                                        <form action="{{ route('admin.category.display-blocks.settings') }}" method="post">
                                                            @csrf
                                                            <input type="hidden" name="id" value="{{ $block->id }}">
                                                            <input type="hidden" name="category_id" value="{{ $category->id }}">
                                                            <div class="row g-3 align-items-end">
                                                                <div class="col-md-8">
                                                                    <label class="form-label">{{ translate('optional_section_title') }}</label>
                                                                    <input type="text" name="title" class="form-control"
                                                                           value="{{ $customTitle }}"
                                                                           placeholder="{{ translate('e.g._Discover_Local') }}"
                                                                           maxlength="120">
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <button type="submit" class="btn btn-primary w-100">
                                                                        {{ translate('save') }}
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body d-flex flex-column gap-20">
                        <h3 class="mb-0">{{ translate('add_block') }}</h3>
                        <p class="fs-12 text-muted mb-0">
                            {{ translate('each_block_type_can_only_be_added_once_per_main_category') }}
                        </p>

                        @if(count($availableBlockTypes) === 0)
                            <div class="alert alert-soft-success mb-0">
                                {{ translate('all_block_types_have_been_added') }}
                            </div>
                        @else
                            <form action="{{ route('admin.category.display-blocks.store') }}" method="post">
                                @csrf
                                <input type="hidden" name="category_id" value="{{ $category->id }}">

                                <div class="form-group mb-3">
                                    <label class="form-label">{{ translate('block_type') }} <span class="text-danger">*</span></label>
                                    <select name="block_type" class="form-control" required id="block-type-select">
                                        <option value="">{{ translate('select_block_type') }}</option>
                                        @foreach($availableBlockTypes as $type)
                                            <option value="{{ $type->value }}" data-description="{{ $type->description() }}">
                                                {{ $type->label() }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted d-block mt-2" id="block-type-description"></small>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="form-label">{{ translate('optional_section_title') }}</label>
                                    <input type="text" name="title" class="form-control"
                                           placeholder="{{ translate('used_for_mixed_products_vendors_or_location_pipeline') }}"
                                           maxlength="120">
                                </div>

                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fi fi-sr-plus"></i>
                                    {{ translate('add_block') }}
                                </button>
                            </form>
                        @endif

                        <hr>

                        <h4 class="fs-14 fw-semibold mb-2">{{ translate('available_block_types') }}</h4>
                        <ul class="list-unstyled fs-12 mb-0 d-flex flex-column gap-2">
                            @foreach(\App\Enums\CategoryDisplayBlockType::cases() as $type)
                                <li>
                                    <span class="fw-medium">{{ $type->label() }}</span>
                                    <span class="d-block text-muted">{{ $type->description() }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <span id="category-display-blocks-config"
          data-category-id="{{ $category->id }}"
          data-reorder-url="{{ route('admin.category.display-blocks.reorder') }}"
          data-status-url="{{ route('admin.category.display-blocks.status') }}"
          data-csrf="{{ csrf_token() }}"></span>
@endsection

@push('script')
    <script src="{{ dynamicAsset(path: 'public/assets/backend/admin/js/products/category-display-blocks.js') }}"></script>
@endpush
