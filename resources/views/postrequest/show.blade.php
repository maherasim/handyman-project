<x-master-layout>
    <div class="d-flex justify-content-center flex-wrap gap-2">
        @php
        $auth_user = auth()->user();
    
        $unitPrice = (float) ($bid->price ?? 0);
        $advPct = (float) ($bid->advance_percent ?? 0);
    
        // Determine quantity based on price type
        $priceType = strtolower((string) ($bid->postrequest->price_type ?? $bid->postrequest->job_price ?? 'fixed'));
        if ($priceType === 'hourly') {
            $quantity = (float) ($bid->postrequest->total_hours ?? 1);
        } elseif ($priceType === 'daily') {
            $quantity = (float) ($bid->postrequest->total_days ?? 1);
        } else {
            $quantity = 1.0;
        }
    
        // Base amount and aggregated extra charges
        $totalAmount = $unitPrice * $quantity;
    
        // New: aggregated extra charges from backend (sum of amount × qty across rows)
        $extraChargesTotal = (float) ($bid->extra_charges ?? 0);
        $extraChargeQty    = (int) ($bid->quantity ?? 0); // optional display
    
        // Subtotal = base + aggregated extras
        $subTotal = $totalAmount + $extraChargesTotal;
    
        // Tax
        $countryId = $bid->postrequest->country_id ?? null;
        $taxRate = 0;
        $taxTitle = '';
        if ($countryId) {
            $taxModel = \App\Models\Tax::find($countryId);
            $taxRate = (float) ($taxModel->value ?? 0);
            $taxTitle = (string) ($taxModel->title ?? '');
        }
        $taxAmount = ($subTotal * $taxRate) / 100;
    
        // Net Amount and Grand Total
        $netAmount   = $subTotal - $taxAmount;
        $grandTotal  = $subTotal + $taxAmount;
    
        // Advance Payment calculated on base total (matches your sample)
        $advAmount = ($totalAmount * $advPct) / 100;
    
        // Remaining = Grand Total - Advance (matches your sample math)
        $remaining = $grandTotal - $advAmount;
    @endphp
    

        {{-- Provider Actions --}}
        @if ($auth_user->user_type === 'provider' && $auth_user->id == $bid->provider_id)

            @if ($bid->status === 'accepted')
                <button class="btn btn-primary startWorkBtn" data-post-id="{{ $bid->id }}"
                    data-advance="{{ $bid->advance_percent ?? 0 }}" data-remaining="{{ $bid->remaining_percent ?? 0 }}">
                    <i class="fas fa-sliders-h"></i> Split Payment
                </button>
                <button class="btn btn-success updateStatusBtn" data-id="{{ $bid->id }}" data-status="cancelled">
                    Cancel
                </button>
            @elseif($bid->status === 'advance_paid')
                <button class="btn btn-primary updateStatusBtn" data-id="{{ $bid->id }}" data-status="in_process">
                    Start Work
                </button>
            @elseif($bid->status === 'in_progress')
                <button class="btn btn-warning holdBidBtn" data-id="{{ $bid->id }}">
                    Hold
                </button>
                <button class="btn btn-success updateStatusBtn" data-id="{{ $bid->id }}" data-status="done">
                    Done
                </button>
            @elseif($bid->status === 'hold')
                <button class="btn btn-primary updateStatusBtn" data-id="{{ $bid->id }}" data-status="in_progress">
                    Resume Work
                </button>
            @elseif($bid->status === 'confirm_done')
                <button class="btn btn-primary updateStatusBtn" data-id="{{ $bid->id }}" data-status="completed">
                    Completed
                </button>
                <button class="btn btn-outline-secondary extraChargesBtn" data-id="{{ $bid->id }}">
                    <i class="fas fa-plus"></i> Extra Charges
                </button>
            @elseif($bid->status === 'remaining_paid')
                <a href="{{ route('postrequest.invoice', $bid->id) }}" class="btn btn-outline-success ms-2">
                    <i class="fas fa-file-download"></i> Download Invoice
                </a>
            @endif

        @endif

        {{-- Customer Actions --}}
        @if ($auth_user->user_type === 'user' && $auth_user->id == $bid->customer_id)

            @if ($bid->status === 'requested')
                <button class="btn btn-success updateStatusBtn" data-id="{{ $bid->id }}"
                    data-status="accepted">Accept</button>
            @elseif($bid->status === 'in_process')
                <button class="btn btn-info updateStatusBtn" data-id="{{ $bid->id }}" data-status="in_progress">
                    Let's Start Work
                </button>
            @elseif($bid->status === 'done')
                <button class="btn btn-info updateStatusBtn" data-id="{{ $bid->id }}" data-status="confirm_done">
                    Confirm Work Done
                </button>
            @elseif($bid->status === 'accepted')
                <button class="btn btn-info updateStatusBtn" data-id="{{ $bid->id }}" data-status="cancelled">
                    Cancel
                </button>
            @elseif($bid->status === 'remaining_paid')
                <a href="{{ route('postrequest.invoice', $bid->id) }}" class="btn btn-outline-success ms-2">
                    <i class="fas fa-file-download"></i> Download Invoice
                </a>
            @elseif($bid->status === 'Advance Payment Pending')
                <button class="btn btn-success payAdvanceBtn" data-post-id="{{ $bid->id }}"
                    data-amount="{{ $advAmount }}">
                    <i class="fas fa-wallet"></i> Pay Advance €{{ number_format($advAmount, 2) }}
                    ({{ $advPct }}%)
                </button>
            @elseif($bid->status === 'completed' && !$bid->has_advance_paid)
                <button class="btn btn-primary payRemainingBtn" data-post-id="{{ $bid->id }}"
                    data-amount="{{ number_format($remaining, 2, '.', '') }}">

                    <i class="fas fa-credit-card"></i> Pay Remaining €{{ number_format($remaining, 2) }}
                </button>
            @elseif($bid->status === 'hold')
                <div class="alert alert-warning d-flex align-items-start shadow-sm border rounded p-3 mt-2">
                    <i class="fas fa-exclamation-triangle fa-lg me-2 text-danger"></i>
                    <div>
                        <h6 class="fw-bold mb-1">This bid is currently on hold</h6>
                        <p class="mb-0 text-muted">
                            Reason: <span class="fw-bold">{{ $bid->hold_reason ?? 'No reason provided' }}</span>
                        </p>
                    </div>
                </div>
            @endif

        @endif



    </div>

    <div class="container py-4">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="row g-3">

                    <div class="col-md-4">
                        <div class="card border-primary shadow-sm h-100 hover-shadow">
                            <div class="card-body text-center">
                                <i class="fas fa-heading fa-2x text-primary mb-2"></i>
                                <h6 class="fw-bold mb-1">Title</h6>
                                <p class="mb-0">{{ $bid->postrequest->title ?? $bid->title }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card border-success shadow-sm h-100 hover-shadow">
                            <div class="card-body text-center">
                                <i class="fas fa-map-marker-alt fa-2x text-success mb-2"></i>
                                <h6 class="fw-bold mb-1">Location</h6>
                                <p class="mb-0">
                                    {{ $bid->postrequest->city->name ?? '-' }},
                                    {{ $bid->postrequest->country->name ?? '-' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card border-warning shadow-sm h-100 hover-shadow">
                            <div class="card-body text-center">
                                <i class="fas fa-briefcase fa-2x text-warning mb-2"></i>
                                <h6 class="fw-bold mb-1">Job Type</h6>
                                <p class="mb-0">{{ $bid->postrequest->type ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card border-warning shadow-sm h-100 hover-shadow">
                            <div class="card-body text-center">
                                <i class="fas fa-dollar-sign fa-2x text-warning mb-2"></i>
                                <h6 class="fw-bold mb-1">Rate Type</h6>
                                <p class="mb-0">{{ $bid->postrequest->job_price ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card border-info shadow-sm h-100 hover-shadow">
                            <div class="card-body text-center">
                                <i class="far fa-calendar-check fa-2x text-info mb-2"></i>
                                <h6 class="fw-bold mb-1">Start Date</h6>
                                <p class="mb-0">{{ $bid->postrequest->start_date ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card border-danger shadow-sm h-100 hover-shadow">
                            <div class="card-body text-center">
                                <i class="far fa-calendar-times fa-2x text-danger mb-2"></i>
                                <h6 class="fw-bold mb-1">End Date</h6>
                                <p class="mb-0">{{ $bid->postrequest->end_date ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    @php
                        $excludedStatuses = ['cancelled', 'split_payment', 'accepted', 'requested'];
                    @endphp
                    @if (!in_array($bid->status, $excludedStatuses))
                        <div class="col-md-4">
                            <div class="card border-secondary shadow-sm h-100 hover-shadow">
                                <div class="card-body text-center">
                                    <i class="fas fa-map-marker-alt fa-2x text-secondary mb-2"></i>
                                    <h6 class="fw-bold mb-1">Working Address</h6>
                                    <p class="mb-0">{{ strip_tags($bid->postrequest->working_address ?? '-') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="col-md-4">
                        <div class="card border-secondary shadow-sm h-100 hover-shadow">
                            <div class="card-body text-center">
                                <i class="fas fa-wallet fa-2x text-secondary mb-2"></i>
                                <h6 class="fw-bold mb-1">Total Budget</h6>
                                <p class="mb-0">
                                    {{ isset($bid->postrequest->total_budget)
                                        ? number_format($bid->postrequest->total_budget, 2, '.', '') . ' €'
                                        : '-' }}
                                </p>
                            </div>
                        </div>
                    </div>


                    <div class="col-md-4">
                        <div class="card border-dark shadow-sm h-100 hover-shadow">
                            <div class="card-body text-center">
                                <i class="fas fa-users fa-2x text-dark mb-2"></i>
                                <h6 class="fw-bold mb-1">Purposal</h6>
                                <p class="mb-0">{{ $bid->postrequest->postBidList->count() ?? 0 }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card border-primary shadow-sm h-100 hover-shadow">
                            <div class="card-body text-center">
                                <i class="fas fa-user fa-2x text-primary mb-2"></i>
                                <h6 class="fw-bold mb-1">Provider</h6>
                                <p class="mb-0">{{ $bid->provider->display_name ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card border-success shadow-sm h-100 hover-shadow">
                            <div class="card-body text-center">
                                <i class="fas fa-user-tie fa-2x text-success mb-2"></i>
                                <h6 class="fw-bold mb-1">Customer</h6>
                                <p class="mb-0">{{ $bid->customer->display_name ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card border-info shadow-sm h-100 hover-shadow">
                            <div class="card-body text-center">
                                <i class="fas fa-flag fa-2x text-info mb-2"></i>
                                <h6 class="fw-bold mb-1">Status</h6>
                                @switch($bid->status)
                                    @case('pending')
                                        <span class="badge px-3 py-2 bg-warning text-dark">{{ $bid->status }}</span>
                                    @break

                                    @case('accepted')
                                        <span class="badge px-3 py-2 bg-success">{{ $bid->status }}</span>
                                    @break

                                    @case('in_progress')
                                        <span class="badge px-3 py-2 bg-primary">In Progress</span>
                                    @break

                                    @default
                                        <span class="badge px-3 py-2 bg-secondary">{{ $bid->status }}</span>
                                @endswitch
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white fw-bold">
                        Price Breakdown
                    </div>
                    <div class="card-body">


                        <table class="table table-sm table-hover price-table">
                            <tbody>
                                <tr>
                                    <td>Rate (Unit Price)</td>
                                    <td class="text-end">€{{ number_format($unitPrice, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>Quantity (Packages / Hours / Days)</td>
                                    <td class="text-end">{{ $quantity }}</td>
                                </tr>
                                <tr>
                                    <td>Total Amount</td>
                                    <td class="text-end">€{{ number_format($totalAmount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>Extra Charges</td>
                                    <td class="text-end">€{{ number_format($extraChargesTotal, 2) }}</td>
                                </tr>
                                <tr class="fw-bold">
                                    <td>Subtotal</td>
                                    <td class="text-end">€{{ number_format($subTotal, 2) }}</td>
                                </tr>

                                <tr class="fw-bold">
                                    <td>Net Amount (Subtotal - Tax)</td>
                                    <td class="text-end">€{{ number_format($netAmount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>Tax ({{ number_format($taxRate, 0) }}%) {{ $taxTitle }}</td>
                                    <td class="text-end">€{{ number_format($taxAmount, 2) }}</td>
                                </tr>

                                <tr class="fw-bold">
                                    <td>Grand Total</td>
                                    <td class="text-end">€{{ number_format($grandTotal, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>Advance Payment ({{ $advPct }}%)</td>
                                    <td class="text-end">€{{ number_format($advAmount, 2) }}</td>
                                </tr>
                                <tr class="fw-bold">
                                    <td>Remaining Amount</td>
                                    <td class="text-end">€{{ number_format($remaining, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>


        </div>
    </div>

    <style>
        .hover-shadow:hover {
            transform: translateY(-3px);
            transition: 0.3s;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }

        .price-table tbody tr {
            transition: background-color 0.2s ease, border-left-color 0.2s ease;
            border-left: 3px solid transparent;
        }

        .price-table tbody tr:hover {
            background-color: #f8f9fa;
            border-left-color: #0d6efd;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.acceptBid').forEach(btn => {
                btn.addEventListener('click', function() {
                    let bidId = this.dataset.id;
                    Swal.fire({
                        title: "Are you sure?",
                        text: "Do you want to accept this bid?",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#28a745",
                        cancelButtonColor: "#d33",
                        confirmButtonText: "Yes, accept it!"
                    }).then(result => {
                        if (!result.isConfirmed) return;
                        fetch(`/bids/accept/${bidId}`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Content-Type': 'application/json'
                                }
                            }).then(res => res.json())
                            .then(response => {
                                Swal.fire(response.status ? "Accepted!" : "Error!",
                                        response.message, response.status ? "success" :
                                        "error")
                                    .then(() => location.reload());
                            }).catch(() => Swal.fire("Error!", "Something went wrong!",
                                "error"));
                    });
                });
            });

            document.querySelectorAll('.updateStatusBtn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const bidId = this.dataset.id;
                    const nextStatus = this.dataset.status;
                    Swal.fire({
                        title: 'Confirm',
                        text: 'Do you want to update status to ' + nextStatus.replace('_',
                            ' ') + '?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, update',
                    }).then(result => {
                        if (!result.isConfirmed) return;
                        fetch(`{{ route('postjob.updateStatus', ':id') }}`.replace(':id',
                                bidId), {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    status: nextStatus
                                })
                            })
                            .then(res => res.json())
                            .then(response => {
                                Swal.fire(response.status ? 'Updated' : 'Error',
                                        response.message || 'Status updated', response
                                        .status ? 'success' : 'error')
                                    .then(() => location.reload());
                            }).catch(() => Swal.fire('Error', 'Something went wrong',
                                'error'));
                    });
                });
            });

            document.querySelectorAll('.payAdvanceBtn, .payRemainingBtn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const postId = this.dataset.postId;
                    const amount = this.dataset.amount;
                    const amountNum = parseFloat(amount);
                    const formattedAmount = amountNum.toFixed(2); // always 2 decimals

                    const isRemaining = btn.classList.contains('payRemainingBtn');

                    Swal.fire({
                        title: isRemaining ? 'Pay Remaining' : 'Pay Advance',
                        html: `
                            <div class="text-start">
                                <p class="mb-2">Amount: <strong>€${formattedAmount}</strong></p>
                                <label class="form-label fw-bold">Choose Payment Method</label>
                                <div class="d-grid gap-2">
                                    <button class="btn btn-outline-primary" id="walletPayBtn"><i class="fas fa-wallet me-1"></i> Wallet</button>
                                    <button class="btn btn-outline-dark" id="stripePayBtn"><i class="fab fa-cc-stripe me-1"></i> Stripe</button>
                                    <button class="btn btn-outline-primary" id="paypalPayBtn"><i class="fab fa-paypal me-1"></i> PayPal</button>
                                     <button class="btn btn-outline-secondary" id="bankPayBtn"><i class="la la-university me-1"></i> Bank Transfer</button>
                                </div>
                            </div>
                        `,
                        showConfirmButton: false,
                        showCancelButton: true,
                    }).then(() => {});

                    setTimeout(() => {
                        const walletBtn = document.getElementById('walletPayBtn');
                        const stripeBtn = document.getElementById('stripePayBtn');
                        const paypalBtn = document.getElementById('paypalPayBtn');
                        const bankBtn = document.getElementById('bankPayBtn');
                        if (walletBtn) {
                            walletBtn.addEventListener('click', () => {
                                Swal.close();
                                fetch(`{{ route('post-job-request.pay-advance', ':id') }}`
                                        .replace(':id', postId), {
                                            method: 'POST',
                                            headers: {
                                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                'Content-Type': 'application/json'
                                            },
                                            body: JSON.stringify({
                                                amount: amount,
                                                type: isRemaining ?
                                                    'remaining' : 'advance'
                                            })
                                        }).then(res => res.json())
                                    .then(response => {
                                        Swal.fire(response.status ? 'Success' :
                                                'Error', response.message,
                                                response.status ? 'success' :
                                                'error')
                                            .then(() => location.reload());
                                    }).catch(() => Swal.fire('Error',
                                        'Something went wrong!', 'error'));
                            });
                        }

                        if (stripeBtn) {
                            stripeBtn.addEventListener('click', () => {
                                Swal.close();
                                fetch(`{{ route('postjob.stripe.create', ':id') }}`
                                        .replace(':id', postId), {
                                            method: 'POST',
                                            headers: {
                                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                'Content-Type': 'application/json'
                                            },
                                            body: JSON.stringify({
                                                amount: amount,
                                                type: isRemaining ?
                                                    'remaining' : 'advance'
                                            })
                                        }).then(res => res.json())
                                    .then(session => {
                                        if (session && session.status && session
                                            .url) {
                                            window.location.href = session.url;
                                        } else {
                                            Swal.fire('Error', session
                                                .message ||
                                                'Unable to initiate Stripe payment',
                                                'error');
                                        }
                                    }).catch(() => Swal.fire('Error',
                                        'Something went wrong!', 'error'));
                            });
                        }
                        if (bankBtn) {
                            bankBtn.addEventListener('click', () => {
                                // 1) Fetch provider bank details
                                fetch(`{{ route('postjob.bank.details', ':id') }}`
                                        .replace(':id', postId), {
                                            method: 'GET',
                                            headers: {
                                                'X-Requested-With': 'XMLHttpRequest'
                                            }
                                        }).then(res => res.json())
                                    .then(data => {
                                        const d = data || {};
                                        const bank = d.bank || {};
                                        const infoHtml = `
                 <div class="text-start">
  <h6 class="mb-2">Bank Information</h6>
  <div><strong>Bank Name:</strong> Norisbank</div>
  <div><strong>Country:</strong> Germany</div>
  <div><strong>Account Number:</strong> 4776167</div>
  <div><strong>IBAN:</strong> DE57760260000477616700</div>
  <div><strong>BIC/Swift:</strong> NORDSDE71XXX</div>
  
  <h6 class="mt-3">Instructions</h6>
 
  <div class="small mt-1">
    Send Proof of Payment (screenshot or PDF Document) to: 
    <a href="mailto:billing@frobster.com">billing@frobster.com</a>
  </div>
</div>

              `;
                                        // 2) Show popup with details and confirm
                                        Swal.fire({
                                            title: 'Bank Transfer',
                                            html: infoHtml,
                                            showCancelButton: true,
                                            confirmButtonText: 'Proceed',
                                        }).then(result => {
                                            if (!result.isConfirmed)
                                                return;
                                            // 3) Create pending bank transfer record
                                            fetch(`{{ route('postjob.bank.transfer', ':id') }}`
                                                    .replace(':id',
                                                        postId), {
                                                        method: 'POST',
                                                        headers: {
                                                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                            'Content-Type': 'application/json'
                                                        },
                                                        body: JSON
                                                            .stringify({
                                                                amount: amount,
                                                                type: isRemaining ?
                                                                    'remaining' :
                                                                    'advance'
                                                            })
                                                    }).then(res => res
                                                    .json())
                                                .then(response => {
                                                    Swal.fire(
                                                            response
                                                            .status ?
                                                            'Recorded' :
                                                            'Error',
                                                            response
                                                            .message ||
                                                            (response
                                                                .status ?
                                                                'Transfer recorded' :
                                                                'Unable to record transfer'
                                                            ),
                                                            response
                                                            .status ?
                                                            'success' :
                                                            'error')
                                                        .then(() =>
                                                            location
                                                            .reload()
                                                        );
                                                }).catch(() => Swal
                                                    .fire('Error',
                                                        'Something went wrong!',
                                                        'error'));
                                        });
                                    }).catch(() => Swal.fire('Error',
                                        'Unable to fetch bank details', 'error'
                                    ));
                            });
                        }
                        if (paypalBtn) {
                            paypalBtn.addEventListener('click', () => {
                                Swal.close();
                                fetch(`{{ route('postjob.paypal.create', ':id') }}`
                                        .replace(':id', postId), {
                                            method: 'POST',
                                            headers: {
                                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                'Content-Type': 'application/json'
                                            },
                                            body: JSON.stringify({
                                                amount: amount,
                                                type: isRemaining ?
                                                    'remaining' : 'advance'
                                            })
                                        }).then(res => res.json())
                                    .then(data => {
                                        if (data && data.url) {
                                            window.location.href = data
                                                .url; // PayPal approval page
                                        } else {
                                            Swal.fire('Error', data.error ||
                                                'Unable to initiate PayPal payment',
                                                'error');
                                        }
                                    }).catch(() => Swal.fire('Error',
                                        'Something went wrong!', 'error'));
                            });
                        }
                    }, 50);
                });
            });

            document.querySelectorAll('.holdBidBtn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const bidId = this.dataset.id;
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
                        preConfirm: value => {
                            if (!value || value.trim().length === 0) Swal
                                .showValidationMessage('Hold reason is required');
                            else if (value.length > 500) Swal.showValidationMessage(
                                'Reason too long (max 500 chars)');
                            else return value;
                        }
                    }).then(result => {
                        if (!result.isConfirmed) return;
                        fetch(`{{ route('postjob.updateStatus', ':id') }}`.replace(':id',
                                bidId), {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    status: 'hold',
                                    hold_reason: result.value
                                })
                            })
                            .then(res => res.json())
                            .then(response => Swal.fire(response.status ? 'On Hold' :
                                    'Error', response.message, response.status ? 'success' :
                                    'error')
                                .then(() => location.reload()))
                            .catch(() => Swal.fire('Error', 'Something went wrong!',
                                'error'));
                    });
                });
            });

            $(document).on('click', '.extraChargesBtn', function() {
  const bidId = $(this).data('id');
  Swal.fire({
    title: 'Add Extra Charges',
    width: 700,
    html: `
      <div class="text-start">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <div class="fw-bold">Charges</div>
          <button type="button" class="btn btn-sm btn-outline-primary" id="ec_addRow">
            <i class="las la-plus"></i> Add Row
          </button>
        </div>
        <div class="table-responsive">
          <table class="table table-sm align-middle mb-2" style="border-collapse:separate;border-spacing:0 6px;">
            <thead>
              <tr>
                <th style="width:50%">Title</th>
                <th style="width:16%" class="text-end">Amount</th>
                <th style="width:16%" class="text-end">Qty</th>
                <th style="width:16%" class="text-end">Total</th>
                <th style="width:2%"></th>
              </tr>
            </thead>
            <tbody id="ec_wrapper"></tbody>
          </table>
        </div>
        <div class="d-flex justify-content-end">
          <div class="fw-bold me-2">Overall Total:</div>
          <div id="ec_overall_total" class="fw-bold">0.00</div>
        </div>
      </div>
    `,
    focusConfirm: false,
    showCancelButton: true,
    confirmButtonText: 'Add',
    didOpen: () => {
      const $wrap = $('#ec_wrapper');
      function fmt(n){ const v = parseFloat(n||0); return v.toFixed(2); }
      function recalcRow($row){
        const amt = parseFloat($row.find('.ec_amount').val())||0;
        const qty = parseInt($row.find('.ec_qty').val(),10)||0;
        const total = amt*qty;
        $row.find('.ec_total').text(fmt(total));
        recalcOverall();
      }
      function recalcOverall(){
        let sum = 0;
        $wrap.find('.ec-row').each(function(){
          const t = parseFloat($(this).find('.ec_total').text())||0;
          sum += t;
        });
        $('#ec_overall_total').text(fmt(sum));
      }
      function addRow(){
        const $row = $(`
          <tr class="ec-row bg-light">
            <td><input type="text" class="form-control form-control-sm ec_title" placeholder="e.g., Travel cost" /></td>
            <td><input type="number" step="0.01" min="0.01" class="form-control form-control-sm text-end ec_amount" placeholder="0.00" /></td>
            <td><input type="number" step="1" min="1" class="form-control form-control-sm text-end ec_qty" value="1" /></td>
            <td class="text-end ec_total">0.00</td>
            <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger ec_remove">&times;</button></td>
          </tr>
        `);
        $wrap.append($row);
        $row.on('input', '.ec_amount, .ec_qty', () => recalcRow($row));
        $row.find('.ec_remove').on('click', function(){ $row.remove(); recalcOverall(); });
      }
      $('#ec_addRow').on('click', addRow);
      addRow();
    },
    preConfirm: () => {
      const items = [];
      let hasError = false;
      $('#ec_wrapper .ec-row').each(function(){
        const title = $(this).find('.ec_title').val().trim();
        const amount = parseFloat($(this).find('.ec_amount').val());
        const qty = parseInt($(this).find('.ec_qty').val(),10);
        if (!title || !amount || amount<=0 || !qty || qty<=0){ hasError = true; }
        else { items.push({ title, amount, quantity: qty }); }
      });
      if (hasError || items.length === 0){
        Swal.showValidationMessage('Each row must have a title, amount > 0 and qty > 0');
        return false;
      }
      return items;
    }
  }).then(async (result) => {
    if (!result.isConfirmed) return;
    const items = result.value;
    try {
      await $.ajax({
        url: '{{ route('postjob.addExtraCharges', ':id') }}'.replace(':id', bidId),
        type: 'POST',
        data: { _token: '{{ csrf_token() }}', items: items }
      });
      Swal.fire('Added', 'Extra charges added successfully', 'success').then(() => window.location.reload());
    } catch (e){
      Swal.fire('Error', 'Unable to add extra charges', 'error');
    }
 
 








        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.startWorkBtn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const postId = this.dataset.postId;
                    const currentAdvance = this.dataset.advance || 0;
                    const currentRemaining = this.dataset.remaining || 100 - currentAdvance;

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
                            const advanceInput = document.getElementById(
                                'advanceInput');
                            const remainingInput = document.getElementById(
                                'remainingInput');
                            advanceInput.addEventListener('input', function() {
                                let val = parseInt(this.value) || 0;
                                if (val > 100) val = 100;
                                remainingInput.value = 100 - val;
                            });
                        },
                        preConfirm: () => {
                            const advance = parseInt(document.getElementById(
                                'advanceInput').value);
                            const remaining = parseInt(document.getElementById(
                                'remainingInput').value);
                            if (advance < 0 || advance > 100) {
                                Swal.showValidationMessage(
                                    "Please enter a valid advance percentage (0-100)"
                                );
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
                            fetch('{{ route('adjustpayment.start-work', ':id') }}'.replace(
                                    ':id', postId), {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Content-Type': 'application/json'
                                    },
                                    body: JSON.stringify({
                                        advance_percent: advance,
                                        remaining_percent: remaining
                                    })
                                }).then(res => res.json())
                                .then(response => {
                                    if (response.status) {
                                        Swal.fire("Updated!", response.message ||
                                                "Payment split updated.", "success")
                                            .then(() => location.reload());
                                    } else {
                                        Swal.fire("Error!", response.message ||
                                            "Unable to update.", "error");
                                    }
                                }).catch(() => Swal.fire("Error!", "Something went wrong!",
                                    "error"));
                        }
                    });
                });
            });
        });
    </script>
</x-master-layout>
