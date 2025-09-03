<x-master-layout>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">

                <!-- Page Header -->
                <div class="card card-block card-stretch mb-3">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">{{ $pageTitle }} </h5>

                        <div class="d-flex gap-2"> {{-- buttons grouped together --}}
                            {{-- Provider sees Set Payment --}}
                            @if (isset($assignedPost) && auth()->id() === $assignedPost->provider_id)
                                <button class="btn btn-primary startWorkBtn" data-post-id="{{ $assignedPost->id }}">
                                    <i class="fas fa-play"></i> Set Payment
                                </button>
                            @endif
                            {{-- Customer sees Pay Advance --}}
                            @if (isset($advance_payment))
                                @php
                                    $advanceAmount =
                                        ($advance_payment->price * $advance_payment->advance_percent) / 100;
                                    $remainingAmount =
                                        ($advance_payment->price * $advance_payment->remaining_percent) / 100;
                                @endphp

                                <!-- Pay Advance -->
                                <button class="btn btn-success payAdvanceBtn" data-post-id="{{ $advance_payment->id }}"
                                    data-amount="{{ $advanceAmount }}">
                                    <i class="fas fa-wallet"></i>
                                    Pay Advance {{ $advanceAmount }} ({{ $advance_payment->advance_percent }}%)
                                </button>


                                <!-- Pay Remaining -->
                                <button class="btn btn-info payRemainingBtn" data-post-id="{{ $advance_payment->id }}"
                                    data-advance="{{ $advance_payment->advance_percent }}"
                                    data-remaining="{{ $advance_payment->remaining_percent }}">
                                    <i class="fas fa-credit-card"></i>
                                    Pay Remaining {{ $remainingAmount }} ({{ $advance_payment->remaining_percent }}%)
                                </button>

                                <button class="btn btn-warning updateAdvanceBtn"
                                    data-post-id="{{ $advance_payment->id }}"
                                    data-advance="{{ $advance_payment->advance_percent }}"
                                    data-remaining="{{ $advance_payment->remaining_percent }}">
                                    <i class="fas fa-edit"></i> Alter Payment
                                </button>
                            @endif

                        </div>

                    </div>
                </div>


                <!-- Search Bar -->
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-end">
                            <div class="input-group w-25">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control dt-search" placeholder="Search bids...">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="myBidsTable" class="table table-striped table-bordered w-100">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Titel</th>
                                <th>Posted at</th>
                                <th>Max. Budget</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Customer</th>
                                <th>My Bids</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            let table = $('#myBidsTable').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                paging: true,
                pageLength: 10,
                ajax: {
                    url: '{{ route('bidsshowjson') }}',
                    type: "GET",
                    data: function(d) {
                        d.search = {
                            value: $('.dt-search').val()
                        }
                    }
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'post_title', name: 'post_title' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'total_budget', name: 'total_budget' },
                    { data: 'start_date', name: 'start_date' },
                    { data: 'end_date', name: 'end_date' },
                    { data: 'customer_name', name: 'customer_name' },
                    { data: 'price', name: 'price' },
                    { data: 'status', name: 'status' }
                ]
            });

            // Live search
            $('.dt-search').on('keyup', function() {
                table.ajax.reload();
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // Event delegation for dynamically loaded buttons
            $(document).on('click', '.updateAdvanceBtn', function() {
                const postId = $(this).data('post-id');
                const currentAdvance = $(this).data('advance');
                const currentRemaining = $(this).data('remaining');

                Swal.fire({
                    title: "Update Payment Split",
                    html: `
                <div class="mb-3 text-start">
                    <label class="form-label fw-bold">Advance Percentage</label>
                    <input type="number" id="advanceInput" class="form-control" value="${currentAdvance}" min="0" max="100" />
                </div>
                <div class="text-start">
                    <label class="form-label fw-bold">Remaining Percentage</label>
                    <input type="number" id="remainingInput" class="form-control" value="${currentRemaining}" readonly />
                </div>
            `,
                    focusConfirm: false,
                    showCancelButton: true,
                    confirmButtonText: "Update",
                    didOpen: () => {
                        const advanceInput = document.getElementById('advanceInput');
                        const remainingInput = document.getElementById('remainingInput');

                        advanceInput.addEventListener('input', function() {
                            let val = parseInt(this.value) || 0;
                            if (val > 100) val = 100;
                            remainingInput.value = 100 - val;
                        });
                    },
                    preConfirm: () => {
                        const advance = document.getElementById('advanceInput').value;
                        const remaining = document.getElementById('remainingInput').value;
                        if (!advance || advance < 0 || advance > 100) {
                            Swal.showValidationMessage(
                                "Please enter a valid advance percentage (0-100)");
                            return false;
                        }
                        return {
                            advance,
                            remaining
                        };
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const {
                            advance,
                            remaining
                        } = result.value;
                        $.ajax({
                            url: '{{ route('adjustpayment.start-work', ':id') }}'.replace(
                                ':id', postId),
                            type: "POST",
                            data: {
                                _token: '{{ csrf_token() }}',
                                advance_percent: advance,
                                remaining_percent: remaining
                            },
                            success: function(response) {
                                if (response.status) {
                                    Swal.fire("Updated!", response.message ||
                                        "Payment split updated.", "success");
                                    $('#myBidsTable').DataTable().ajax.reload();
                                } else {
                                    Swal.fire("Error!", response.message ||
                                        "Unable to update.", "error");
                                }
                            },
                            error: function() {
                                Swal.fire("Error!", "Something went wrong!", "error");
                            }
                        });
                    }
                });
            });

        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Isolate Pay Advance binding without jQuery dependency
        (function bindPayAdvanceOnce() {
            if (window.__payAdvanceBound) return;
            window.__payAdvanceBound = true;

            document.addEventListener('click', function(evt) {
                const btn = evt.target && evt.target.closest && evt.target.closest('.payAdvanceBtn');
                if (!btn) return;

                const postId = btn.getAttribute('data-post-id');
                const providedAmount = btn.getAttribute('data-amount');
                if (!postId) return;

                Swal.fire({
                    title: "Confirm Advance Payment",
                    text: providedAmount ? `Pay advance amount: ${providedAmount}. Proceed?` :
                        "Are you sure you want to proceed with the advance payment?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#28a745",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Yes, pay advance",
                    cancelButtonText: "Cancel"
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    const url = '{{ route('post-job-request.pay-advance', ':id') }}'.replace(':id',
                        postId);
                    fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                amount: providedAmount
                            })
                        })
                        .then(function(res) {
                            return res.json();
                        })
                        .then(function(response) {
                            if (response && response.status) {
                                Swal.fire("Success", response.message ||
                                    "Advance paid successfully.", "success");
                                if (window.jQuery && window.jQuery.fn && window.jQuery.fn
                                    .DataTable && window.jQuery('#myBidsTable').length) {
                                    window.jQuery('#myBidsTable').DataTable().ajax.reload();
                                }
                            } else {
                                Swal.fire("Error", (response && response.message) ? response
                                    .message : "Unable to process.", "error");
                            }
                        })
                        .catch(function() {
                            Swal.fire("Error", "Something went wrong!", "error");
                        });
                });
            }, {
                passive: true
            });
        })();
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // Accept Bid
            $(document).on('click', '.acceptBid', function() {
                let bidId = $(this).data('id');
                Swal.fire({
                    title: "Are you sure?",
                    text: "Do you want to accept this bid?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#28a745",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Yes, accept it!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ url('/bids/accept') }}/' + bidId,
                            type: "POST",
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.status) {
                                    Swal.fire("Accepted!", response.message, "success");
                                    $('#myBidsTable').DataTable().ajax.reload();
                                } else {
                                    Swal.fire("Error!", response.message, "error");
                                }
                            },
                            error: function() {
                                Swal.fire("Error!", "Something went wrong!", "error");
                            }
                        });
                    }
                });
            });

            // Provider: Hold with reason
            $(document).on('click', '.holdBidBtn', function() {
                const bidId = $(this).data('id');
                Swal.fire({
                    title: 'Put on Hold',
                    input: 'textarea',
                    inputLabel: 'Provide hold reason',
                    inputPlaceholder: 'Type your reason here... (max 500 chars)',
                    inputAttributes: {
                        'aria-label': 'Hold reason'
                    },
                    showCancelButton: true,
                    confirmButtonText: 'Submit',
                    preConfirm: (value) => {
                        if (!value || value.trim().length === 0) {
                            Swal.showValidationMessage('Hold reason is required');
                            return false;
                        }
                        if (value.length > 500) {
                            Swal.showValidationMessage('Reason too long (max 500 chars)');
                            return false;
                        }
                        return value;
                    }
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    $.ajax({
                        url: '{{ route('postjob.updateStatus', ':id') }}'.replace(':id', bidId),
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            status: 'hold',
                            hold_reason: result.value
                        },
                        success: function(response) {
                            if (response && response.status) {
                                Swal.fire('On Hold', response.message || 'Status updated to hold', 'success');
                                $('#myBidsTable').DataTable().ajax.reload();
                            } else {
                                Swal.fire('Error', (response && response.message) ? response.message : 'Unable to update', 'error');
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Error', (xhr && xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Something went wrong', 'error');
                        }
                    });
                });
            });

            // Update status (both provider and user as per data-status)
            $(document).on('click', '.updateStatusBtn', function() {
                const bidId = $(this).data('id');
                let nextStatus = $(this).data('status');

                // Fallback: infer status from button text when not provided
                if (!nextStatus) {
                    const btnText = ($(this).text() || '').trim().toLowerCase();
                    if (btnText.includes('cancel')) nextStatus = 'cancelled';
                    else if (btnText.includes('resume')) nextStatus = 'in_process';
                    else if (btnText.includes('start')) nextStatus = 'in_progress';
                    else if (btnText.includes('done')) nextStatus = 'done';
                    else if (btnText.includes('complete')) nextStatus = 'completed';
                }

                const label = (typeof nextStatus === 'string' && nextStatus)
                    ? String(nextStatus).replace('_', ' ')
                    : (($(this).data('label')) || ($(this).text() || '').trim() || 'this action');

                if (!nextStatus) {
                    Swal.fire('Error', 'Unable to determine next status.', 'error');
                    return;
                }

                Swal.fire({
                    title: 'Confirm',
                    text: 'Do you want to update status to ' + label + '?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, update',
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    $.ajax({
                        url: '{{ route('postjob.updateStatus', ':id') }}'.replace(':id', bidId),
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            status: nextStatus
                        },
                        success: function(response) {
                            if (response && response.status) {
                                Swal.fire('Updated', response.message || 'Status updated', 'success');
                                $('#datatable').DataTable().ajax.reload();
                            } else {
                                Swal.fire('Error', (response && response.message) ? response.message : 'Unable to update', 'error');
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Error', (xhr && xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Something went wrong', 'error');
                        }
                    });
                });
            });

            // Extra Charges: prompt for title, amount, quantity and update bid price
            $(document).on('click', '.extraChargesBtn', function() {
                const bidId = $(this).data('id');
                Swal.fire({
                    title: 'Add Extra Charges',
                    html: `
                        <div class="text-start">
                            <label class="form-label fw-bold">Title</label>
                            <input type="text" id="ec_title" class="form-control" placeholder="e.g., Title" />
                        </div>
                        <div class="mt-2 text-start">
                            <label class="form-label fw-bold">Amount</label>
                            <input type="number" id="ec_amount" class="form-control" step="0.01" min="0.01" placeholder="e.g., 20" />
                        </div>
                        <div class="mt-2 text-start">
                            <label class="form-label fw-bold">Quantity (optional)</label>
                            <input type="number" id="ec_qty" class="form-control" step="1" min="1" placeholder="1" />
                        </div>
                    `,
                    focusConfirm: false,
                    showCancelButton: true,
                    confirmButtonText: 'Add',
                    preConfirm: () => {
                        const title = document.getElementById('ec_title').value.trim();
                        const amount = parseFloat(document.getElementById('ec_amount').value);
                        const qtyRaw = document.getElementById('ec_qty').value;
                        const quantity = qtyRaw ? parseInt(qtyRaw, 10) : 1;
                        if (!title) {
                            Swal.showValidationMessage('Title is required');
                            return false;
                        }
                        if (!amount || amount <= 0) {
                            Swal.showValidationMessage('Enter a valid amount > 0');
                            return false;
                        }
                        if (quantity && quantity < 1) {
                            Swal.showValidationMessage('Quantity must be at least 1');
                            return false;
                        }
                        return { title, amount, quantity };
                    }
                }).then((result) => {
                    if (!result.isConfirmed) return;
                    const { title, amount, quantity } = result.value;
                    $.ajax({
                        url: '{{ route('postjob.addExtraCharges', ':id') }}'.replace(':id', bidId),
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            title: title,
                            amount: amount,
                            quantity: quantity
                        },
                        success: function(response) {
                            if (response && response.status) {
                                Swal.fire('Added', response.message || 'Extra charges added', 'success');
                                $('#datatable').DataTable().ajax.reload();
                            } else {
                                Swal.fire('Error', (response && response.message) ? response.message : 'Unable to add charges', 'error');
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Error', (xhr && xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Something went wrong', 'error');
                        }
                    });
                });
            });

            // Start Work with Payment Split
            $(document).on('click', '.startWorkBtn', function() {
                const postId = $(this).data('post-id');

                Swal.fire({
                    title: "Set Payment Split",
                    html: `
                        <div class="mb-3 text-start">
                            <label class="form-label fw-bold">Advance Percentage</label>
                            <input type="number" id="advanceInput" class="form-control" placeholder="Enter advance %" min="0" max="100" />
                        </div>
                        <div class="text-start">
                            <label class="form-label fw-bold">Remaining Percentage</label>
                            <input type="number" id="remainingInput" class="form-control" readonly />
                        </div>
                    `,
                    focusConfirm: false,
                    showCancelButton: true,
                    confirmButtonText: "Submit",
                    preConfirm: () => {
                        const advance = document.getElementById('advanceInput').value;
                        const remaining = document.getElementById('remainingInput').value;

                        if (!advance || advance < 0 || advance > 100) {
                            Swal.showValidationMessage(
                                "Please enter a valid advance percentage (0-100)");
                            return false;
                        }
                        return {
                            advance,
                            remaining
                        };
                    },
                    didOpen: () => {
                        const advanceInput = document.getElementById('advanceInput');
                        const remainingInput = document.getElementById('remainingInput');

                        advanceInput.addEventListener('input', function() {
                            let val = parseInt(this.value) || 0;
                            if (val > 100) val = 100;
                            remainingInput.value = 100 - val;
                        });
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const {
                            advance,
                            remaining
                        } = result.value;

                        $.ajax({

                            url: '{{ route('adjustpayment.start-work', ':id') }}'.replace(
                                ':id', postId),



                            type: "POST",
                            data: {
                                _token: '{{ csrf_token() }}',
                                advance_percent: advance,
                                remaining_percent: remaining
                            },
                            success: function(response) {
                                if (response.status) {
                                    Swal.fire("Saved!", response.message ||
                                        "Payment split set & work started.",
                                        "success");
                                    $('#myBidsTable').DataTable().ajax.reload();
                                } else {
                                    Swal.fire("Error!", response.message ||
                                        "Unable to save.", "error");
                                }
                            },
                            error: function() {
                                Swal.fire("Error!", "Something went wrong!", "error");
                            }
                        });

                    }
                });
            });
        });
    </script>
</x-master-layout>
