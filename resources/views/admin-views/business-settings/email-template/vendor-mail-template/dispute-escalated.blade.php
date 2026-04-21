<div>
    <div class="text-center mb-3">
        <span class="badge badge-warning fs-14 px-3 py-2">
            {{ translate('dispute_escalated_to_admin') }}
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
        $vendorDisputeUrl = url('vendor/dispute/dispute-detail/' . $dispute->id);
    @endphp
    <hr>
    <div class="email-table p-3 bg-color-white-smoke rounded mb-3">
        <h5 class="mb-3">{{ translate('dispute_information') }}</h5>
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
        <p class="mt-3 mb-1 text-muted fs-12">
            {{ translate('admin_will_review_the_dispute_and_contact_both_parties_with_the_decision') }}
        </p>
        <p class="mb-0 text-muted fs-12">
            {{ translate('no_further_action_is_required_from_you_at_this_time') }}
        </p>
    </div>
    @endif

    <hr>
    @include('admin-views.business-settings.email-template.partials-design.footer')
</div>
