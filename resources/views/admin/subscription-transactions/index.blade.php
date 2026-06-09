@php
    $transactions = \App\Models\SubscriptionTransaction::with(['user', 'subscription'])
        ->orderBy('created_at', 'desc')
        ->get();
@endphp

<x-master-layout>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Subscription Transactions</h4>
                    <p class="text-muted">All subscription payments with status</p>
                    
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
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Email</th>
                                    <th>Plan</th>
                                    <th>Amount</th>
                                    <th>Payment Type</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Action</th>
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
                                                Unknown User
                                            @endif
                                        </td>
                                        <td>
                                            @if($transaction->user)
                                                {{ $transaction->user->email }}
                                            @else
                                                No Email
                                            @endif
                                        </td>
                                        <td>
                                            @if($transaction->subscription)
                                                {{ $transaction->subscription->title }}
                                            @else
                                                Unknown Plan
                                            @endif
                                        </td>
                                        <td>€{{ number_format($transaction->amount, 2) }}</td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $transaction->payment_type)) }}</td>
                                        <td>
                                            @if($transaction->payment_status === 'pending')
                                                <span class="badge bg-warning">Pending</span>
                                            @elseif($transaction->payment_status === 'paid')
                                                <span class="badge bg-success">Paid</span>
                                            @elseif($transaction->payment_status === 'rejected')
                                                <span class="badge bg-danger">Rejected</span>
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
                                                            onclick="return confirmVerify('{{ $transaction->user->first_name ?? 'User' }}', '{{ $transaction->subscription->title ?? 'Plan' }}', '€{{ number_format($transaction->amount, 2) }}')">
                                                        Verify
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">No subscription transactions found</td>
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
function confirmVerify(userName, planName, amount) {
    Swal.fire({
        title: 'Verify Payment?',
        html: `
            <div class="text-start">
                <p><strong>User:</strong> ${userName}</p>
                <p><strong>Plan:</strong> ${planName}</p>
                <p><strong>Amount:</strong> ${amount}</p>
                <p class="mt-3">Are you sure you want to verify this bank transfer payment?</p>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Verify Payment',
        cancelButtonText: '{{ __("messages.cancel") }}'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Verifying...',
                text: 'Please wait while we verify the payment.',
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
