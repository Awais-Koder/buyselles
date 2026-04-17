@extends('theme-views.layouts.app')
@section('title', translate('My_Disputes') . ' | ' . $web_config['company_name'] . ' ' . translate('ecommerce'))
@section('content')
    <main class="main-content d-flex flex-column gap-3 py-3 mb-5">
        <div class="container">
            <div class="row g-3">
                @include('theme-views.partials._profile-aside')
                <div class="col-lg-9">
                    <div class="card h-100">
                        <div class="card-body p-lg-4">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                                <h5>{{ translate('my_disputes') }}</h5>
                                <small class="text-muted">{{ translate('disputes_are_opened_from_delivered_orders') }}</small>
                            </div>

                            @if ($disputes->count() > 0)
                                <div class="table-responsive">
                                    <table class="table mb-0 fs-13">
                                        <thead class="fs-13">
                                            <tr>
                                                <th>{{ translate('dispute_id') }}</th>
                                                <th>{{ translate('order') }}</th>
                                                <th>{{ translate('reason') }}</th>
                                                <th>{{ translate('date') }}</th>
                                                <th>{{ translate('status') }}</th>
                                                <th class="text-center">{{ translate('action') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($disputes as $dispute)
                                                @php
                                                    $statusColors = [
                                                        'open'             => 'badge-warning',
                                                        'vendor_response'  => 'badge-info',
                                                        'under_review'     => 'badge-primary',
                                                        'resolved_refund'  => 'badge-success',
                                                        'resolved_release' => 'badge-secondary',
                                                        'closed'           => 'badge-secondary',
                                                        'auto_closed'      => 'badge-secondary',
                                                    ];
                                                    $badgeClass = $statusColors[$dispute->status] ?? 'badge-secondary';
                                                @endphp
                                                <tr>
                                                    <td>#{{ $dispute->id }}</td>
                                                    <td>{{ translate('order') }} #{{ $dispute->order_id }}</td>
                                                    <td>{{ $dispute->reason ? translate($dispute->reason->title) : translate('general') }}</td>
                                                    <td>{{ \Carbon\Carbon::parse($dispute->created_at)->format('d M Y') }}</td>
                                                    <td>
                                                        <span class="badge {{ $badgeClass }}">
                                                            {{ translate($dispute->status) }}
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        <a href="{{ route('account-dispute.details', $dispute->id) }}"
                                                            class="btn btn-sm btn-outline-primary">
                                                            {{ translate('view') }}
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-4">
                                    {{ $disputes->links() }}
                                </div>
                            @else
                                <div class="d-flex flex-column align-items-center justify-content-center gap-3 py-5">
                                    <p class="text-muted fs-14">{{ translate('no_disputes_found') }}</p>
                                    <a href="{{ route('account-oder') }}" class="btn btn-primary btn-sm">
                                        {{ translate('view_my_orders') }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
