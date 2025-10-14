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
                        <table class="table table-striped" id="subscriptionTransactionsTable">
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
                                <!-- Data will be loaded via AJAX -->
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
    let table;
    let currentTransactionId = null;

    // Initialize DataTable
    function initializeTable() {
        table = $('#subscriptionTransactionsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("admin.subscription-transactions.data") }}',
                error: function(xhr, error, thrown) {
                    console.error('DataTable AJAX Error:', error);
                    console.error('Response:', xhr.responseText);
                    alert('Error loading data: ' + error);
                }
            },
            columns: [
                { data: 'id' },
                { data: 'user_name' },
                { data: 'user_email' },
                { data: 'plan_name' },
                { data: 'amount' },
                { data: 'payment_type' },
                {
                    data: 'payment_status',
                    render: function(data, type, row) {
                        let badgeClass = '';
                        switch(data) {
                            case 'pending':
                                badgeClass = 'bg-warning';
                                break;
                            case 'paid':
                                badgeClass = 'bg-success';
                                break;
                            case 'rejected':
                                badgeClass = 'bg-danger';
                                break;
                        }
                        return '<span class="badge ' + badgeClass + '">' + data.charAt(0).toUpperCase() + data.slice(1) + '</span>';
                    }
                },
                { data: 'created_at' },
                {
                    data: 'actions',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        if (row.payment_status === 'pending') {
                            return '<button class="btn btn-sm btn-success verify-btn" data-id="' + data + '">Verify</button>';
                        } else {
                            return '<span class="text-muted">Verified</span>';
                        }
                    }
                }
            ],
            order: [[0, 'desc']],
            pageLength: 25
        });
    }

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
                        });
                        table.ajax.reload();
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

    // Initialize
    initializeTable();
});
</script>
@endpush
