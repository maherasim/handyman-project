@php
    $transactions = \App\Models\SubscriptionTransaction::with(['user', 'subscription'])
        ->where('payment_type', 'bank_transfer')
        ->where('payment_status', 'pending')
        ->orderBy('created_at', 'desc')
        ->get();
@endphp

@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Bank Transfer Payments - Pending Verification</h4>
                    <p class="text-muted">Verify pending bank transfer payments</p>
                    
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
                                            @if($transaction->payment_status === 'pending')
                                                <form method="POST" action="{{ route('admin.subscription-transactions.verify', $transaction->id) }}" style="display: inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success verify-btn" 
                                                            onclick="return confirmVerify('{{ $transaction->user->first_name ?? 'User' }}', '{{ $transaction->subscription->title ?? 'Plan' }}', '€{{ number_format($transaction->amount, 2) }}')">
                                                        Verify
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-muted">Verified</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">No pending bank transfer payments found</td>
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

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        cancelButtonText: 'Cancel'
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
