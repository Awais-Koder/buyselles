<div>
    <h3 class="mb-4 view-mail-title">
        {{ $title }}
    </h3>
    <div class="view-mail-body">
        {!! $body !!}
    </div>

    <div class="mt-4 {{ $template['button_content_status'] == 1 ? '' : 'd-none' }}" id="button-content">
        <div class="d-flex justify-content-center mb-4">
            <a href="{{ $template['button_url'] ?? route('track-order.index') }}" target="_blank"
                class="btn btn-primary view-button-content view-button-link m-auto">{{ $buttonName ?? translate('track_Order') }}</a>
        </div>
    </div>
    <div class="main-table-inner mb-4">
        <div class="d-flex justify-content-center pt-3">
            <img class="mb-4 w-100px h-auto" id="view-mail-logo"
                src="{{ $template->image_full_url['path'] ?? getStorageImages(path: $companyLogo, type: 'backend-logo') }}"
                alt="">
        </div>
    </div>
    <hr>
    @include('admin-views.business-settings.email-template.partials-design.footer')
</div>
