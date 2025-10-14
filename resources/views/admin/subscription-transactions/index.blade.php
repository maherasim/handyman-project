@php
    $transactions = \App\Models\SubscriptionTransaction::with(['user', 'subscription'])->orderBy('created_at', 'desc')->get();
@endphp

@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Subscription Transactions</h4>
                    <p class="text-muted">Verify bank transfer payments</p>
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
                                                <button class="btn btn-sm btn-success verify-btn" data-id="{{ $transaction->id }}">
                                                    Verify
                                                </button>
                                            @else
                                                <span class="text-muted">Verified</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">No transactions found</td>
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

<!-- Simple Verification Modal -->
<div class="modal fade" id="verifyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Verify Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to verify this payment?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmVerifyBtn">Verify</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    let currentTransactionId = null;

    // Verify payment
    $(document).on('click', '.verify-btn', function() {
        currentTransactionId = $(this).data('id');
        $('#verifyModal').modal('show');
    });

    // Confirm verify
    $('#confirmVerifyBtn').click(function() {
        if (currentTransactionId) {
            $.ajax({
                url: '{{ route("admin.subscription-transactions.verify", ":id") }}'.replace(':id', currentTransactionId),
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.status) {
                        Swal.fire({
                            title: 'Success!',
                            text: response.message,
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: response.message,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                    $('#verifyModal').modal('hide');
                },
                error: function() {
                    Swal.fire({
                        title: 'Error!',
                        text: 'An error occurred while verifying the payment.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                    $('#verifyModal').modal('hide');
                }
            });
        }
    });
});
</script>
@endpush
