<div>
    <div class="text-center mb-3">
        @if(isset($data['resolveType']) && $data['resolveType'] === 'refund')
            <span class="badge bg-success fs-14 px-3 py-2">
                {{ translate('refund_approved') }}
            </span>
        @else
            <span class="badge bg-info fs-14 px-3 py-2">
                {{ translate('dispute_resolved') }}
            </span>
        @endif
    </div>

    <h3 class="mb-4 view-mail-title text-capitalize">
        {{ $title }}
    </h3>

    <div class="view-mail-body">
        {!! $body !!}
    </div>

    @if(isset($data['dispute']))
    @php
        $dispute = $data['dispute'];
        $resolveType = $data['resolveType'] ?? 'release';
    @endphp
    <hr>
    <div class="email-table p-3 bg-color-white-smoke rounded mb-3">
        <h5 class="mb-3">{{ translate('resolution_details') }}</h5>
        <table class="table table-borderless mb-0">
            <tbody>
                <tr>
                    <th class="text-nowrap" style="width:40%">{{ translate('dispute_id') }}</th>
                    <td><strong>#{{ $dispute->id }}</strong></td>
                </tr>
                <tr>
                    <th>{{ translate('order_id') }}</th>
                    <td>#{{ $dispute->order_id }}</td>
                </tr>
                <tr>
                    <th>{{ translate('decision') }}</th>
                    <td class="text-capitalize">
                        @if($resolveType === 'refund')
                            <span class="text-success fw-bold">{{ translate('refund_to_buyer') }}</span>
                        @else
                            <span class="text-info fw-bold">{{ translate('payment_released_to_vendor') }}</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>{{ translate('resolved_at') }}</th>
                    <td>{{ $dispute->resolved_at ? \Carbon\Carbon::parse($dispute->resolved_at)->format('d M Y, h:i A') : now()->format('d M Y, h:i A') }}</td>
                </tr>
                @if($dispute->admin_decision)
                <tr>
                    <th>{{ translate('admin_note') }}</th>
                    <td class="fst-italic">"{{ $dispute->admin_decision }}"</td>
                </tr>
                @endif
            </tbody>
        </table>
        @if($resolveType === 'refund')
        <div class="alert alert-success mt-3 mb-0 fs-12">
            {{ translate('your_refund_is_being_processed_it_will_be_credited_to_your_wallet_shortly') }}
        </div>
        @endif
    </div>
    @endif

    <hr>
    @include('admin-views.business-settings.email-template.partials-design.footer')
</div>
