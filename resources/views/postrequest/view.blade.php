<x-master-layout>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">

                <!-- Page Header -->
                <div class="card card-block card-stretch mb-3">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">
                            {{ $pageTitle }}
                            @if(auth()->user()->user_type === 'provider')
                                <span class="ms-3 text-muted" style="font-size: 0.9rem;">Average Bid: <strong>{{ number_format($averageBid, 2) }}</strong></span>
                            @endif
                        </h5>

                        @if(auth()->user()->user_type !== 'provider')
                        <div class="d-flex gap-2"> {{-- buttons grouped together --}}
                            {{-- Customer/Other roles UI remains intact --}}
                            @if (isset($advance_payment))
                                @php
                                    $advanceAmount =
                                        ($advance_payment->price * $advance_payment->advance_percent) / 100;
                                    $remainingAmount =
                                        ($advance_payment->price * $advance_payment->remaining_percent) / 100;
                                @endphp
                                <button class="btn btn-success payAdvanceBtn" data-post-id="{{ $advance_payment->id }}"
                                    data-amount="{{ $advanceAmount }}">
                                    <i class="fas fa-wallet"></i>
                                    Pay Advance {{ $advanceAmount }} ({{ $advance_payment->advance_percent }}%)
                                </button>
                                <button class="btn btn-info payRemainingBtn" data-post-id="{{ $advance_payment->id }}"
                                    data-advance="{{ $advance_payment->advance_percent }}"
                                    data-remaining="{{ $advance_payment->remaining_percent }}">
                                    <i class="fas fa-credit-card"></i>
                                    Pay Remaining {{ $remainingAmount }} ({{ $advance_payment->remaining_percent }}%)
                                </button>
                            @endif
                        </div>
                        @endif

                    </div>
                </div>


                @if(auth()->user()->user_type !== 'provider')
                <!-- Search Bar (non-provider only) -->
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
                @endif

                <div class="table-responsive">
                    <table id="postBidsTable" class="table table-striped table-bordered w-100">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Posted at</th>
                                <th>Max. Budget</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Provider</th>
                                <th>Why Choose Me</th>
                                <th>Bid Amount</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const table = $('#postBidsTable').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                paging: true,
                pageLength: 10,
                ajax: {
                    url: @json(route('postrequest.index_data', $id)),
                    type: 'GET'
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'title', name: 'title' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'total_budget', name: 'total_budget' },
                    { data: 'start_date', name: 'start_date' },
                    { data: 'end_date', name: 'end_date' },
                    { data: 'provider', name: 'provider' },
                    { data: 'why_choose_me', name: 'why_choose_me', orderable:false, searchable:false,
                        render: function(data, type, row){
                            const safeId = row.id;
                            const payload = (typeof data === 'string') ? data : '';
                            return `<button type=\"button\" class=\"btn btn-sm btn-outline-primary viewWhyBtn\" data-bid-id=\"${safeId}\" data-why=\"${encodeURIComponent(payload)}\" title=\"View\"><i class=\"far fa-eye\"></i></button>`;
                        }
                    },
                    { data: 'bid_amount', name: 'bid_amount' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ]
            });

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
                                    $('#datatable').DataTable().ajax.reload();
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
    
    <!-- Why Choose Me Modal -->
    <div class="modal fade" id="whyChooseMeModal" tabindex="-1" aria-labelledby="whyChooseMeLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="whyChooseMeLabel">Why Choose Me</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="p-2" id="whyChooseMeContent"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function(){
            $(document).on('click', '.viewWhyBtn', function(){
                const raw = $(this).data('why');
                const html = raw ? decodeURIComponent(String(raw)) : '';
                $('#whyChooseMeContent').html(html || '<em>No content provided.</em>');
                $('#whyChooseMeModal').modal('show');
            });
            // Clear modal on hide
            $('#whyChooseMeModal').on('hidden.bs.modal', function(){
                $('#whyChooseMeContent').html('');
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
                const btn = evt.target && evt.target.closest && (evt.target.closest('.payAdvanceBtn') || evt
                    .target.closest('.payRemainingBtn'));
                if (!btn) return;

                const postId = btn.getAttribute('data-post-id');
                const providedAmount = btn.getAttribute('data-amount');
                const isRemaining = btn.classList.contains('payRemainingBtn');
                if (!postId) return;

                Swal.fire({
                    title: isRemaining ? "Confirm Remaining Payment" : "Confirm Advance Payment",
                    text: providedAmount ?
                        `${isRemaining ? 'Pay remaining amount' : 'Pay advance amount'}: ${providedAmount}. Proceed?` :
                        (isRemaining ? "Proceed to pay remaining amount?" :
                            "Are you sure you want to proceed with the advance payment?"),
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#28a745",
                    cancelButtonColor: "#d33",
                    confirmButtonText: isRemaining ? "Yes, pay remaining" : "Yes, pay advance",
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
                                amount: providedAmount,
                                type: isRemaining ? 'remaining' : 'advance'
                            })
                        })
                        .then(function(res) {
                            return res.json();
                        })
                        .then(function(response) {
                            if (response && response.status) {
                                Swal.fire("Success", response.message || (isRemaining ?
                                    "Remaining paid successfully." :
                                    "Advance paid successfully."), "success");
                                if (window.jQuery && window.jQuery.fn && window.jQuery.fn
                                    .DataTable && window.jQuery('#datatable').length) {
                                    window.jQuery('#datatable').DataTable().ajax.reload();
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
                                    $('#datatable').DataTable().ajax.reload();
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

            // Unified status update (provider/user)
            $(document).on('click', '.updateStatusBtn', function() {
                const bidId = $(this).data('id');
                const nextStatus = $(this).data('status');

                Swal.fire({
                    title: 'Confirm',
                    text: 'Do you want to update status to ' + nextStatus.replace('_', ' ') + '?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, update',
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    $.ajax({
                        url: '{{ route('postjob.updateStatus', ':id') }}'.replace(':id',
                            bidId),
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            status: nextStatus
                        },
                        success: function(response) {
                            if (response && response.status) {
                                Swal.fire('Updated', response.message ||
                                    'Status updated', 'success');
                                $('#datatable').DataTable().ajax.reload();
                            } else {
                                Swal.fire('Error', (response && response.message) ?
                                    response.message : 'Unable to update', 'error');
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Error', (xhr && xhr.responseJSON && xhr
                                    .responseJSON.message) ? xhr.responseJSON
                                .message : 'Something went wrong', 'error');
                        }
                    });
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
                        url: '{{ route('postjob.updateStatus', ':id') }}'.replace(':id',
                            bidId),
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            status: 'hold',
                            hold_reason: result.value
                        },
                        success: function(response) {
                            if (response && response.status) {
                                Swal.fire('On Hold', response.message ||
                                    'Status updated to hold', 'success');
                                $('#datatable').DataTable().ajax.reload();
                            } else {
                                Swal.fire('Error', (response && response.message) ?
                                    response.message : 'Unable to update', 'error');
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Error', (xhr && xhr.responseJSON && xhr
                                    .responseJSON.message) ? xhr.responseJSON
                                .message : 'Something went wrong', 'error');
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
                                    $('#datatable').DataTable().ajax.reload();
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
