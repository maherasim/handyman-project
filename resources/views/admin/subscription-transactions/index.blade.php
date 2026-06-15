@php
    $transactions = \App\Models\SubscriptionTransaction::with(['user', 'subscription'])
        ->orderBy('created_at', 'desc')
        ->get();
    $subTxLang = [
        'verify_payment'            => __('messages.verify_payment'),
        'user_label'                => __('messages.name'),
        'plan_label'                => __('messages.plan'),
        'amount_label'              => __('messages.amount'),
        'verify_bank_transfer_confirm' => __('messages.verify_bank_transfer_confirm'),
        'yes_verify_payment'        => __('messages.yes_verify_payment'),
        'verifying'                 => __('messages.verifying'),
        'please_wait_verifying'     => __('messages.please_wait_verifying'),
        'cancel'                    => __('messages.cancel'),
    ];
@endphp

<x-master-layout>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">{{ __('messages.sidebar_subscription_transactions') }}</h4>
                    <p class="text-muted">{{ __('messages.subscription_transactions_subtitle') }}</p>

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{ __('messages.id') }}</th>
                                    <th>{{ __('messages.name') }}</th>
                                    <th>{{ __('messages.email') }}</th>
                                    <th>{{ __('messages.plan') }}</th>
                                    <th>{{ __('messages.amount') }}</th>
                                    <th>{{ __('messages.payment_type') }}</th>
                                    <th>{{ __('messages.status') }}</th>
                                    <th>{{ __('messages.date') }}</th>
                                    <th>{{ __('messages.action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transactions as $transaction)
                                    <tr>
                                        <td>{{ $transaction->id }}</td>
                                        <td>
                                            @if($transaction->user)
                                                {{ $transaction->user->first_name }} {{ $transaction->user->last_name }}
                                            @else
                                                {{ __('messages.unknown_user') }}
                                            @endif
                                        </td>
                                        <td>
                                            @if($transaction->user)
                                                {{ $transaction->user->email }}
                                            @else
                                                {{ __('messages.no_email') }}
                                            @endif
                                        </td>
                                        <td>
                                            @if($transaction->subscription)
                                                {{ $transaction->subscription->title }}
                                            @else
                                                {{ __('messages.unknown_plan') }}
                                            @endif
                                        </td>
                                        <td>{{ getPriceFormat($transaction->amount) }}</td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $transaction->payment_type)) }}</td>
                                        <td>
                                            @if($transaction->payment_status === 'pending')
                                                <span class="badge bg-warning">{{ __('messages.pending') }}</span>
                                            @elseif($transaction->payment_status === 'paid')
                                                <span class="badge bg-success">{{ __('messages.paid') }}</span>
                                            @elseif($transaction->payment_status === 'rejected')
                                                <span class="badge bg-danger">{{ __('messages.rejected') }}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ ucfirst($transaction->payment_status) }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $transaction->created_at->format('Y-m-d H:i:s') }}</td>
                                        <td>
                                            @if($transaction->payment_status === 'pending' && $transaction->payment_type === 'bank_transfer')
                                                <form method="POST" action="{{ route('admin.subscription-transactions.verify', $transaction->id) }}" style="display: inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success verify-btn"
                                                            onclick="return confirmVerify('{{ $transaction->user->first_name ?? __('messages.unknown') }}', '{{ $transaction->subscription->title ?? __('messages.unknown_plan') }}', '{{ getPriceFormat($transaction->amount) }}')">
                                                        {{ __('messages.verify') }}
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">{{ __('messages.no_subscription_transactions') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</x-master-layout>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<head>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
        <style>
            /* Red-Blue Gradient for Primary Colors */
            .btn-primary,
            button.btn-primary,
            a.btn-primary {
                background: #3333ff !important;
                border: none !important;
                color: #fff !important;
            }
            .btn-primary:hover,
            button.btn-primary:hover,
            a.btn-primary:hover {
                background: linear-gradient(135deg, #cc0000 0%, #4a4d94 100%) !important;
            }
            .text-primary,
            a.text-primary {
                background: #3333ff;
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }
            .bg-primary,
            .badge.bg-primary {
                background: #3333ff !important;
                color: #fff !important;
            }
            .table thead th,
            #datatable thead th,
            table thead th {
                background: #3333ff !important;
                color: #fff !important;
                border-color: transparent !important;
            }
            /* DataTables pagination */
            .dataTables_wrapper .dataTables_paginate .paginate_button.current,
            .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
                background: #3333ff !important;
                border: none !important;
                color: #fff !important;
            }
            .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
                background: linear-gradient(135deg, rgba(255, 0, 0, 0.1) 0%, rgba(95, 96, 185, 0.1) 100%) !important;
                border: none !important;
            }
            /* Select2 primary colors */
            .select2-container--default .select2-results__option--highlighted[aria-selected] {
                background: #3333ff !important;
                color: #fff !important;
            }
        </style>
    </head>script>
<script>
var subTxLang = @json($subTxLang);
function confirmVerify(userName, planName, amount) {
    Swal.fire({
        title: subTxLang.verify_payment,
        html: `
            <div class="text-start">
                <p><strong>${subTxLang.user_label}:</strong> ${userName}</p>
                <p><strong>${subTxLang.plan_label}:</strong> ${planName}</p>
                <p><strong>${subTxLang.amount_label}:</strong> ${amount}</p>
                <p class="mt-3">${subTxLang.verify_bank_transfer_confirm}</p>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: subTxLang.yes_verify_payment,
        cancelButtonText: subTxLang.cancel
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: subTxLang.verifying,
                text: subTxLang.please_wait_verifying,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // The form will submit automatically after confirmation
            return true;
        } else {
            return false;
        }
    });

    // Return false to prevent form submission until confirmed
    return false;
}
</script>
@endpush
