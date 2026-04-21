<div>
    <div class="text-center mb-3">
        <span class="badge badge-info fs-14 px-3 py-2">
            {{ translate('dispute_under_admin_review') }}
        </span>
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
        $escalatedByType = $data['escalatedByType'] ?? 'buyer';
        $orderUrl = url('account/order-details/' . $dispute->order_id);
    @endphp
    <hr>
    <div class="email-table p-3 bg-color-white-smoke rounded mb-3">
        <h5 class="mb-3">{{ translate('dispute_summary') }}</h5>
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
                    <th>{{ translate('escalated_by') }}</th>
                    <td class="text-capitalize">{{ translate($escalatedByType) }}</td>
                </tr>
                <tr>
                    <th>{{ translate('status') }}</th>
                    <td>{{ translate('under_admin_review') }}</td>
                </tr>
                <tr>
                    <th>{{ translate('reason') }}</th>
                    <td>{{ $dispute->reason?->title ?? translate('N/A') }}</td>
                </tr>
            </tbody>
        </table>
        <p class="mt-3 mb-0 text-muted fs-12">
            {{ translate('our_team_will_review_the_dispute_and_notify_you_of_the_final_decision') }}
        </p>
    </div>
    @endif

    <hr>
    @include('admin-views.business-settings.email-template.partials-design.footer')
</div>
