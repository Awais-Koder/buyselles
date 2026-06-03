@php
    $astLocaleLang = session('local') ?? getDefaultLanguage();
    $astLocaleCurr = session('currency_code') ?? 'USD';
    $astLanguages = $web_config['language'] ?? [];
    $astCurrencies = $web_config['currencies'] ?? \App\Models\Currency::where('status', 1)->get();
@endphp

<div class="modal fade" id="localeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header border-0 pb-2">
                <h6 class="modal-title">{{ translate('Language_&_Currency') }}</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">{{ translate('Language') }}</label>
                    <select class="form-select" id="localeLanguageSelect">
                        @foreach ($astLanguages as $lang)
                            @if (!empty($lang['status']) && $lang['status'] == 1)
                                <option value="{{ $lang['code'] }}" {{ $astLocaleLang === $lang['code'] ? 'selected' : '' }}>
                                    {{ $lang['name'] }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">{{ translate('Currency') }}</label>
                    <select class="form-select" id="localeCurrencySelect">
                        @foreach ($astCurrencies as $cur)
                            <option value="{{ $cur['code'] }}" {{ $astLocaleCurr === $cur['code'] ? 'selected' : '' }}>
                                {{ $cur['name'] ?? $cur['code'] }} ({{ $cur['symbol'] ?? $cur['code'] }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ translate('Cancel') }}</button>
                <button type="button" class="btn btn-primary" id="localeSaveBtn">{{ translate('Save') }}</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var saveBtn = document.getElementById('localeSaveBtn');
        if (!saveBtn) return;
        saveBtn.addEventListener('click', function () {
            var lang = document.getElementById('localeLanguageSelect').value;
            var curr = document.getElementById('localeCurrencySelect').value;
            var x = new XMLHttpRequest();
            x.open('POST', '{{ route('locale.switch') }}');
            x.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="_token"]').getAttribute('content'));
            x.setRequestHeader('Content-Type', 'application/json');
            x.onload = function () { if (x.status === 200) location.reload(); };
            x.send(JSON.stringify({ language_code: lang, currency_code: curr }));
        });
    });
</script>
