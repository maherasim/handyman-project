@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-6">
                            <h4 class="card-title mb-0">Subscription Transactions</h4>
                            <p class="text-muted">Manage and verify subscription payments</p>
                        </div>
                        <div class="col-md-6 text-end">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-success" id="verifySelectedBtn" disabled>
                                    <i class="fas fa-check"></i> Verify Selected
                                </button>
                                <button type="button" class="btn btn-danger" id="rejectSelectedBtn" disabled>
                                    <i class="fas fa-times"></i> Reject Selected
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <select class="form-select" id="paymentStatusFilter">
                                <option value="">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="paid">Paid</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="paymentTypeFilter">
                                <option value="">All Payment Types</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="stripe">Stripe</option>
                                <option value="paypal">PayPal</option>
                                <option value="wallet">Wallet</option>
                                <option value="free">Free</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="date" class="form-control" id="dateFromFilter" placeholder="From Date">
                        </div>
                        <div class="col-md-2">
                            <input type="date" class="form-control" id="dateToFilter" placeholder="To Date">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-primary" id="applyFiltersBtn">
                                <i class="fas fa-filter"></i> Apply Filters
                            </button>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4 class="mb-0" id="pendingCount">0</h4>
                                            <p class="mb-0">Pending</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-clock fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4 class="mb-0" id="paidCount">0</h4>
                                            <p class="mb-0">Paid</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-check-circle fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4 class="mb-0" id="rejectedCount">0</h4>
                                            <p class="mb-0">Rejected</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-times-circle fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4 class="mb-0" id="totalAmount">€0</h4>
                                            <p class="mb-0">Total Amount</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-euro-sign fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- DataTable -->
                    <div class="table-responsive">
                        <table class="table table-striped" id="subscriptionTransactionsTable">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" id="selectAll" class="form-check-input">
                                    </th>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Email</th>
                                    <th>Plan</th>
                                    <th>Amount</th>
                                    <th>Payment Type</th>
                                    <th>Status</th>
                                    <th>Transaction ID</th>
                                    <th>Date</th>
                                    <th>Actions</th>
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

<!-- Payment Verification Modal -->
<div class="modal fade" id="paymentVerificationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Payment Verification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <i class="fas fa-question-circle fa-3x text-warning mb-3"></i>
                    <h5>Verify Payment?</h5>
                    <p>Are you sure you want to verify this payment? This will activate the user's subscription.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmVerifyBtn">
                    <i class="fas fa-check"></i> Verify Payment
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Payment Rejection Modal -->
<div class="modal fade" id="paymentRejectionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <i class="fas fa-times-circle fa-3x text-danger mb-3"></i>
                    <h5>Reject Payment?</h5>
                    <p>Are you sure you want to reject this payment? This will deactivate the user's subscription.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmRejectBtn">
                    <i class="fas fa-times"></i> Reject Payment
                </button>
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
                data: function(d) {
                    d.payment_status = $('#paymentStatusFilter').val();
                    d.payment_type = $('#paymentTypeFilter').val();
                    d.date_from = $('#dateFromFilter').val();
                    d.date_to = $('#dateToFilter').val();
                }
            },
            columns: [
                {
                    data: 'id',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        return '<input type="checkbox" class="form-check-input transaction-checkbox" value="' + data + '">';
                    }
                },
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
                        let icon = '';
                        switch(data) {
                            case 'pending':
                                badgeClass = 'bg-warning';
                                icon = 'fas fa-clock';
                                break;
                            case 'paid':
                                badgeClass = 'bg-success';
                                icon = 'fas fa-check-circle';
                                break;
                            case 'rejected':
                                badgeClass = 'bg-danger';
                                icon = 'fas fa-times-circle';
                                break;
                        }
                        return '<span class="badge ' + badgeClass + '"><i class="' + icon + '"></i> ' + data.charAt(0).toUpperCase() + data.slice(1) + '</span>';
                    }
                },
                { data: 'txn_id' },
                { data: 'created_at' },
                {
                    data: 'actions',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        let buttons = '';
                        if (row.payment_status === 'pending') {
                            buttons += '<button class="btn btn-sm btn-success me-1 verify-btn" data-id="' + data + '" title="Verify Payment"><i class="fas fa-check"></i></button>';
                            buttons += '<button class="btn btn-sm btn-danger reject-btn" data-id="' + data + '" title="Reject Payment"><i class="fas fa-times"></i></button>';
                        } else {
                            buttons += '<span class="text-muted">No actions</span>';
                        }
                        return buttons;
                    }
                }
            ],
            order: [[1, 'desc']],
            pageLength: 25,
            responsive: true,
            language: {
                processing: "Loading transactions..."
            }
        });
    }

    // Apply filters
    $('#applyFiltersBtn').click(function() {
        table.ajax.reload();
        updateStatistics();
    });

    // Select all checkbox
    $('#selectAll').change(function() {
        $('.transaction-checkbox').prop('checked', this.checked);
        updateBulkActionButtons();
    });

    // Individual checkbox change
    $(document).on('change', '.transaction-checkbox', function() {
        updateBulkActionButtons();
        updateSelectAllCheckbox();
    });

    // Update bulk action buttons
    function updateBulkActionButtons() {
        const selectedCount = $('.transaction-checkbox:checked').length;
        $('#verifySelectedBtn, #rejectSelectedBtn').prop('disabled', selectedCount === 0);
    }

    // Update select all checkbox
    function updateSelectAllCheckbox() {
        const totalCheckboxes = $('.transaction-checkbox').length;
        const checkedCheckboxes = $('.transaction-checkbox:checked').length;
        $('#selectAll').prop('checked', totalCheckboxes === checkedCheckboxes);
    }

    // Verify single payment
    $(document).on('click', '.verify-btn', function() {
        currentTransactionId = $(this).data('id');
        $('#paymentVerificationModal').modal('show');
    });

    // Reject single payment
    $(document).on('click', '.reject-btn', function() {
        currentTransactionId = $(this).data('id');
        $('#paymentRejectionModal').modal('show');
    });

    // Confirm verify
    $('#confirmVerifyBtn').click(function() {
        if (currentTransactionId) {
            verifyPayment(currentTransactionId);
        }
    });

    // Confirm reject
    $('#confirmRejectBtn').click(function() {
        if (currentTransactionId) {
            rejectPayment(currentTransactionId);
        }
    });

    // Verify payment function
    function verifyPayment(transactionId) {
        $.ajax({
            url: '{{ route("admin.subscription-transactions.verify", ":id") }}'.replace(':id', transactionId),
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
                    updateStatistics();
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: response.message,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
                $('#paymentVerificationModal').modal('hide');
            },
            error: function() {
                Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred while verifying the payment.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                $('#paymentVerificationModal').modal('hide');
            }
        });
    }

    // Reject payment function
    function rejectPayment(transactionId) {
        $.ajax({
            url: '{{ route("admin.subscription-transactions.reject", ":id") }}'.replace(':id', transactionId),
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
                    updateStatistics();
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: response.message,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
                $('#paymentRejectionModal').modal('hide');
            },
            error: function() {
                Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred while rejecting the payment.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                $('#paymentRejectionModal').modal('hide');
            }
        });
    }

    // Bulk verify
    $('#verifySelectedBtn').click(function() {
        const selectedIds = $('.transaction-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) {
            Swal.fire({
                title: 'No Selection',
                text: 'Please select transactions to verify.',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            return;
        }

        Swal.fire({
            title: 'Verify Payments?',
            text: `Are you sure you want to verify ${selectedIds.length} payment(s)?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Verify',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                bulkAction('verify', selectedIds);
            }
        });
    });

    // Bulk reject
    $('#rejectSelectedBtn').click(function() {
        const selectedIds = $('.transaction-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) {
            Swal.fire({
                title: 'No Selection',
                text: 'Please select transactions to reject.',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            return;
        }

        Swal.fire({
            title: 'Reject Payments?',
            text: `Are you sure you want to reject ${selectedIds.length} payment(s)?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Reject',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                bulkAction('reject', selectedIds);
            }
        });
    });

    // Bulk action function
    function bulkAction(action, ids) {
        $.ajax({
            url: '{{ route("admin.subscription-transactions.bulk-action") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                action: action,
                ids: ids
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
                    updateStatistics();
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: response.message,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred while processing the bulk action.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        });
    }

    // Update statistics
    function updateStatistics() {
        $.ajax({
            url: '{{ route("admin.subscription-transactions.statistics") }}',
            type: 'GET',
            success: function(response) {
                $('#pendingCount').text(response.pending);
                $('#paidCount').text(response.paid);
                $('#rejectedCount').text(response.rejected);
                $('#totalAmount').text('€' + response.total_amount);
            }
        });
    }

    // Initialize
    initializeTable();
    updateStatistics();
});
</script>
@endpush
