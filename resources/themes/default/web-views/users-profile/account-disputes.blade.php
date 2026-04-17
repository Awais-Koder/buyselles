@extends('layouts.front-end.app')

@section('title', translate('My_Disputes'))

@section('content')
    <div class="container py-4 rtl text-align-direction">
        <div class="row">
            @include('web-views.partials._profile-aside')

            <section class="col-lg-9 __customer-profile">

                <div class="card __card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                            <h5 class="mb-0 fs-16 font-bold">{{ translate('my_disputes') }}</h5>
                            <span class="fs-12 text-muted">{{ translate('disputes_are_opened_from_delivered_orders') }}</span>
                        </div>

                        @if ($disputes->count() > 0)
                            <div class="table-responsive">
                                <table class="table mb-0 __table-2 fs-13">
                                    <thead class="thead-light fs-13 font-semibold">
                                        <tr>
                                            <th class="border-0">
                                                <span class="d-block fs-13 font-semibold">{{ translate('dispute_id') }}</span>
                                            </th>
                                            <th class="border-0">
                                                <span class="d-block fs-13 font-semibold">{{ translate('order') }}</span>
                                            </th>
                                            <th class="border-0">
                                                <span class="d-block fs-13 font-semibold">{{ translate('reason') }}</span>
                                            </th>
                                            <th class="border-0">
                                                <span class="d-block fs-13 font-semibold">{{ translate('date') }}</span>
                                            </th>
                                            <th class="border-0">
                                                <span class="d-block fs-13 font-semibold">{{ translate('status') }}</span>
                                            </th>
                                            <th class="border-0 text-center">
                                                <span class="d-block fs-13 font-semibold">{{ translate('action') }}</span>
                                            </th>
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
                                                <td>
                                                    <span class="fs-13 font-semibold">#{{ $dispute->id }}</span>
                                                </td>
                                                <td>
                                                    <span class="fs-13 font-semibold text-muted">
                                                        {{ translate('order') }} #{{ $dispute->order_id }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="fs-13">
                                                        {{ $dispute->reason ? translate($dispute->reason->title) : translate('general') }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="text-secondary-50 fs-12 font-semibold">
                                                        {{ \Carbon\Carbon::parse($dispute->created_at)->format('d M Y') }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge __badge rounded-full {{ $badgeClass }}">
                                                        {{ translate($dispute->status) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn--container flex-nowrap justify-content-center">
                                                        <a class="__action-btn btn-shadow rounded-full text-primary"
                                                            href="{{ route('account-dispute.details', $dispute->id) }}"
                                                            title="{{ translate('view_dispute') }}">
                                                            <i class="fa fa-eye"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3 d-flex justify-content-center">
                                {{ $disputes->links() }}
                            </div>
                        @else
                            <div class="d-flex flex-column align-items-center justify-content-center gap-3 py-5">
                                <img
                                    src="{{ theme_asset(path: 'public/assets/front-end/img/empty-search-1.png') }}"
                                    class="mw-100" width="180" alt="">
                                <p class="text-muted fs-14">{{ translate('no_disputes_found') }}</p>
                                <p class="text-muted fs-12 text-center">
                                    {{ translate('you_can_open_a_dispute_from_a_delivered_order_page') }}
                                </p>
                                <a href="{{ route('account-oder') }}" class="btn btn--primary btn-sm font-semibold">
                                    {{ translate('view_my_orders') }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

            </section>
        </div>
    </div>
@endsection
