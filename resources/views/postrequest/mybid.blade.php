<x-master-layout>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">

                <!-- Page Header -->
                <div class="card card-block card-stretch mb-3">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">{{ $pageTitle }} </h5>

                        <div class="d-flex gap-2"> {{-- buttons grouped together --}}
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
                                    {{ __('messages.pjr_pay_advance') }} {{ $advanceAmount }} ({{ $advance_payment->advance_percent }}%)
                                </button>


                                <!-- Pay Remaining -->
                                <button class="btn btn-info payRemainingBtn" data-post-id="{{ $advance_payment->id }}"
                                    data-advance="{{ $advance_payment->advance_percent }}"
                                    data-remaining="{{ $advance_payment->remaining_percent }}">
                                    <i class="fas fa-credit-card"></i>
                                    {{ __('messages.pjr_pay_remaining') }} {{ $remainingAmount }} ({{ $advance_payment->remaining_percent }}%)
                                </button>

                                <button class="btn btn-warning updateAdvanceBtn"
                                    data-post-id="{{ $advance_payment->id }}"
                                    data-advance="{{ $advance_payment->advance_percent }}"
                                    data-remaining="{{ $advance_payment->remaining_percent }}">
                                    <i class="fas fa-edit"></i> {{ __('messages.pjr_alter_payment') }}
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
                                <input type="text" class="form-control dt-search" placeholder="{{ __('messages.pjr_search_bids') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="myBidsTable" class="table table-striped table-bordered w-100">
                        <thead>
                            <tr>
                                <th>{{ __('messages.pjr_id') }}</th>
                                <th>{{ __('messages.pjr_title') }}</th>
                                <th>{{ __('messages.pjr_posted_at_th') }}</th>
                                <th>{{ __('messages.pjr_max_budget') }}</th>
                                <th>{{ __('messages.pjr_start_date') }}</th>
                                <th>{{ __('messages.pjr_end_date') }}</th>
                                <th>{{ __('messages.customer') }}</th>
                                <th>{{ __('messages.pjr_my_bids') }}</th>
                                <th>{{ __('messages.status') }}</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    <script>
        @php
            $pjrJsLang = [
                'update_payment_split'  => __('messages.pjr_update_payment_split'),
                'advance_percentage'    => __('messages.pjr_advance_percentage'),
                'remaining_percentage'  => __('messages.pjr_remaining_percentage'),
                'update'                => __('messages.pjr_update'),
                'advance_validation'    => __('messages.pjr_advance_percentage_validation'),
                'updated'               => __('messages.pjr_js_updated'),
                'payment_split_updated' => __('messages.pjr_payment_split_updated'),
                'error'                 => __('messages.pjr_js_error'),
                'unable_to_update'      => __('messages.pjr_unable_to_update'),
                'something_went_wrong'  => __('messages.pjr_js_something_wrong_exclaim'),
                'success'               => __('messages.pjr_js_success'),
                'advance_paid_ok'       => __('messages.pjr_js_advance_paid_ok'),
                'are_you_sure'          => __('messages.pjr_js_are_you_sure'),
                'accept_bid_text'       => __('messages.pjr_js_accept_bid_text'),
                'yes_accept'            => __('messages.pjr_js_yes_accept'),
                'accepted'              => __('messages.pjr_js_accepted'),
                'on_hold'               => __('messages.pjr_js_on_hold'),
                'on_hold_msg'           => __('messages.pjr_js_on_hold_msg'),
                'put_on_hold'           => __('messages.pjr_js_put_on_hold'),
                'hold_reason_label'     => __('messages.pjr_js_hold_reason_label'),
                'hold_reason_ph'        => __('messages.pjr_js_hold_reason_placeholder'),
                'hold_reason_required'  => __('messages.pjr_js_hold_reason_required'),
                'reason_too_long'       => __('messages.pjr_js_hold_reason_too_long'),
                'confirm'               => __('messages.pjr_js_confirm'),
                'update_status'         => __('messages.pjr_js_update_status'),
                'yes_update'            => __('messages.pjr_js_yes_update'),
                'status_updated'        => __('messages.pjr_js_status_updated'),
                'unable_to_determine'   => __('messages.pjr_js_unable_to_determine'),
                'add_extra_charges'     => __('messages.pjr_js_add_extra_charges'),
                'add'                   => __('messages.pjr_js_add'),
                'title_required'        => __('messages.pjr_js_title_required'),
                'enter_valid_amount'    => __('messages.pjr_js_enter_valid_amount'),
                'qty_at_least_1'        => __('messages.pjr_js_qty_at_least_1'),
                'extra_charges_added'   => __('messages.pjr_js_extra_charges_added'),
                'unable_add_charges'    => __('messages.pjr_js_unable_add_charges'),
                'saved'                 => __('messages.pjr_js_saved'),
                'payment_split_started' => __('messages.pjr_js_payment_split_started'),
                'unable_to_save'        => __('messages.pjr_js_unable_to_save'),
            ];
        @endphp
        var pjrJsLang = @json($pjrJsLang);
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
                    { data: 'status', name: 'status', render: function(data){ return data; } }
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
                    title: pjrJsLang.update_payment_split,
                    html: `
                <div class="mb-3 text-start">
                    <label class="form-label fw-bold">${pjrJsLang.advance_percentage}</label>
                    <input type="number" id="advanceInput" class="form-control" value="${currentAdvance}" min="0" max="100" />
                </div>
                <div class="text-start">
                    <label class="form-label fw-bold">${pjrJsLang.remaining_percentage}</label>
                    <input type="number" id="remainingInput" class="form-control" value="${currentRemaining}" readonly />
                </div>
            `,
                    focusConfirm: false,
                    showCancelButton: true,
                    confirmButtonText: pjrJsLang.update,
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
                            Swal.showValidationMessage(pjrJsLang.advance_validation);
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
                                    Swal.fire(pjrJsLang.updated, response.message ||
                                        pjrJsLang.payment_split_updated, "success");
                                    $('#myBidsTable').DataTable().ajax.reload();
                                } else {
                                    Swal.fire(pjrJsLang.error, response.message ||
                                        pjrJsLang.unable_to_update, "error");
                                }
                            },
                            error: function() {
                                Swal.fire(pjrJsLang.error, pjrJsLang.something_went_wrong, "error");
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
                                Swal.fire(pjrJsLang.success, response.message ||
                                    pjrJsLang.advance_paid_ok, "success");
                                if (window.jQuery && window.jQuery.fn && window.jQuery.fn
                                    .DataTable && window.jQuery('#myBidsTable').length) {
                                    window.jQuery('#myBidsTable').DataTable().ajax.reload();
                                }
                            } else {
                                Swal.fire(pjrJsLang.error, (response && response.message) ? response
                                    .message : pjrJsLang.unable_to_update, "error");
                            }
                        })
                        .catch(function() {
                            Swal.fire(pjrJsLang.error, pjrJsLang.something_went_wrong, "error");
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
                    title: pjrJsLang.are_you_sure,
                    text: pjrJsLang.accept_bid_text,
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#28a745",
                    cancelButtonColor: "#d33",
                    confirmButtonText: pjrJsLang.yes_accept
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
                                    Swal.fire(pjrJsLang.accepted, response.message, "success");
                                    $('#myBidsTable').DataTable().ajax.reload();
                                } else {
                                    Swal.fire(pjrJsLang.error, response.message, "error");
                                }
                            },
                            error: function() {
                                Swal.fire(pjrJsLang.error, pjrJsLang.something_went_wrong, "error");
                            }
                        });
                    }
                });
            });

            // Provider: Hold with reason
            $(document).on('click', '.holdBidBtn', function() {
                const bidId = $(this).data('id');
                Swal.fire({
                    title: pjrJsLang.put_on_hold,
                    input: 'textarea',
                    inputLabel: pjrJsLang.hold_reason_label,
                    inputPlaceholder: pjrJsLang.hold_reason_ph,
                    inputAttributes: {
                        'aria-label': 'Hold reason'
                    },
                    showCancelButton: true,
                    confirmButtonText: pjrJsLang.confirm,
                    preConfirm: (value) => {
                        if (!value || value.trim().length === 0) {
                            Swal.showValidationMessage(pjrJsLang.hold_reason_required);
                            return false;
                        }
                        if (value.length > 500) {
                            Swal.showValidationMessage(pjrJsLang.reason_too_long);
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
                                Swal.fire(pjrJsLang.on_hold, response.message || pjrJsLang.on_hold_msg, 'success');
                                $('#myBidsTable').DataTable().ajax.reload();
                            } else {
                                Swal.fire(pjrJsLang.error, (response && response.message) ? response.message : pjrJsLang.unable_to_update, 'error');
                            }
                        },
                        error: function(xhr) {
                            Swal.fire(pjrJsLang.error, (xhr && xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : pjrJsLang.something_went_wrong, 'error');
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
                    Swal.fire(pjrJsLang.error, pjrJsLang.unable_to_determine, 'error');
                    return;
                }

                Swal.fire({
                    title: pjrJsLang.confirm,
                    text: pjrJsLang.update_status.replace(':status', label),
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: pjrJsLang.yes_update,
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
                                Swal.fire(pjrJsLang.updated, response.message || pjrJsLang.status_updated, 'success');
                                $('#datatable').DataTable().ajax.reload();
                            } else {
                                Swal.fire(pjrJsLang.error, (response && response.message) ? response.message : pjrJsLang.unable_to_update, 'error');
                            }
                        },
                        error: function(xhr) {
                            Swal.fire(pjrJsLang.error, (xhr && xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : pjrJsLang.something_went_wrong, 'error');
                        }
                    });
                });
            });

            // Extra Charges: prompt for title, amount, quantity and update bid price
            $(document).on('click', '.extraChargesBtn', function() {
                const bidId = $(this).data('id');
                Swal.fire({
                    title: pjrJsLang.add_extra_charges,
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
                    confirmButtonText: pjrJsLang.add,
                    preConfirm: () => {
                        const title = document.getElementById('ec_title').value.trim();
                        const amount = parseFloat(document.getElementById('ec_amount').value);
                        const qtyRaw = document.getElementById('ec_qty').value;
                        const quantity = qtyRaw ? parseInt(qtyRaw, 10) : 1;
                        if (!title) {
                            Swal.showValidationMessage(pjrJsLang.title_required);
                            return false;
                        }
                        if (!amount || amount <= 0) {
                            Swal.showValidationMessage(pjrJsLang.enter_valid_amount);
                            return false;
                        }
                        if (quantity && quantity < 1) {
                            Swal.showValidationMessage(pjrJsLang.qty_at_least_1);
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
                                Swal.fire(pjrJsLang.success, response.message || pjrJsLang.extra_charges_added, 'success');
                                $('#datatable').DataTable().ajax.reload();
                            } else {
                                Swal.fire(pjrJsLang.error, (response && response.message) ? response.message : pjrJsLang.unable_add_charges, 'error');
                            }
                        },
                        error: function(xhr) {
                            Swal.fire(pjrJsLang.error, (xhr && xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : pjrJsLang.something_went_wrong, 'error');
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
                                    Swal.fire(pjrJsLang.saved, response.message ||
                                        pjrJsLang.payment_split_started, "success");
                                    $('#myBidsTable').DataTable().ajax.reload();
                                } else {
                                    Swal.fire(pjrJsLang.error, response.message ||
                                        pjrJsLang.unable_to_save, "error");
                                }
                            },
                            error: function() {
                                Swal.fire(pjrJsLang.error, pjrJsLang.something_went_wrong, "error");
                            }
                        });

                    }
                });
            });
        });
    </script>
</x-master-layout>
