{{-- Store Country Filter Sidebar Partial for Vendors Listing --}}
@php
    $countriesMap = collect(COUNTRIES)->keyBy('code');
@endphp
<div class="widget widget-categories mb-4">
    <h5 class="fs-16 font-weight-bold mb-3">{{ translate('Filter_By_Location') }}</h5>

    <div class="mb-3">
        <label class="form-label small fw-semibold">{{ translate('country') }}</label>
        <select id="store-country-filter" class="form-control form-control-sm">
            <option value="">--- {{ translate('all_countries') }} ---</option>
            @foreach ($storeCountries as $code)
                <option value="{{ $code }}" {{ ($selectedStoreCountry ?? '') == $code ? 'selected' : '' }}>
                    {{ $countriesMap->get($code)['name'] ?? $code }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="d-flex gap-2">
        <button id="store-country-apply" class="btn btn-primary btn-sm flex-grow-1">
            {{ translate('apply') }}
        </button>
        @if ($selectedStoreCountry ?? '')
            <a href="{{ url()->current() . '?' . http_build_query(collect(request()->except(['store_country']))->toArray()) }}"
                class="btn btn-outline-secondary btn-sm">
                {{ translate('clear') }}
            </a>
        @endif
    </div>
</div>

@push('script')
    <script>
        (function() {
            $(document).on('click', '#store-country-apply', function() {
                var params = new URLSearchParams(window.location.search);
                var storeCountry = $('#store-country-filter').val();

                params.delete('store_country');
                params.delete('page');

                if (storeCountry) {
                    params.set('store_country', storeCountry);
                }

                window.location.href = window.location.pathname + '?' + params.toString();
            });
        })();
    </script>
@endpush
