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
        $adminUrl = url('admin/dispute/dispute-detail/' . $dispute->id);
    @endphp
    <hr>
    <div class="email-table p-3 bg-color-white-smoke rounded mb-3">
        <h5 class="mb-3">{{ translate('dispute_details') }}</h5>
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
                    <th>{{ translate('escalated_at') }}</th>
                    <td>{{ $dispute->escalated_at ? \Carbon\Carbon::parse($dispute->escalated_at)->format('d M Y, h:i A') : now()->format('d M Y, h:i A') }}</td>
                </tr>
                <tr>
                    <th>{{ translate('priority') }}</th>
                    <td class="text-capitalize">{{ $dispute->priority ?? 'medium' }}</td>
                </tr>
                <tr>
                    <th>{{ translate('buyer') }}</th>
                    <td>{{ $dispute->buyer?->name ?? translate('N/A') }}</td>
                </tr>
                <tr>
                    <th>{{ translate('vendor') }}</th>
                    <td>{{ $dispute->vendor?->shop?->name ?? $dispute->vendor?->name ?? translate('N/A') }}</td>
                </tr>
                <tr>
                    <th>{{ translate('reason') }}</th>
                    <td>{{ $dispute->reason?->title ?? translate('N/A') }}</td>
                </tr>
            </tbody>
        </table>
        <div class="mt-3 text-center">
            <a href="{{ $adminUrl }}" class="btn btn-primary btn-sm px-4">
                {{ translate('review_dispute') }}
            </a>
        </div>
    </div>
    @endif

    <hr>
    @include('admin-views.business-settings.email-template.partials-design.footer')
</div>
