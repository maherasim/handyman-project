<x-master-layout>
    <div class="d-flex justify-content-center flex-wrap gap-2">


        @php
            $auth_user = auth()->user();
            $unitPrice = (float) ($bid->price ?? 0);
            $advPct = (float) ($bid->advance_percent ?? 0);

            // Determine quantity based on price type
            if ($bid->postrequest->price_type == 'hourly') {
                $quantity = (float) ($bid->postrequest->total_hours ?? 1);
            } 
            elseif ($bid->postrequest->price_type == 'daily') {
                $quantity = (float) ($bid->postrequest->total_days ?? 1);
            } elseif ($bid->postrequest->price_type == 'fixed') {
                $quantity = 1;
            } else {
                $quantity = (float) ($bid->quantity ?? 1);
            }

            // Calculations
            $totalAmount = $unitPrice * $quantity;

            // Extra charges: prefer line items, fallback to single unit*qty
            $hasExtraLines = ($bid->relationLoaded('extraCharges') && $bid->extraCharges && $bid->extraCharges->count() > 0);
            $extraChargesCount = 0;
            $extraChargesTotal = 0.0;
            $extraChargeUnit = (float) ($bid->extra_charges ?? 0);
            $extraChargeQty = (int) ($bid->quantity ?? 1);
            if ($hasExtraLines) {
                foreach ($bid->extraCharges as $ec) {
                    $lineAmount = (float) ($ec->amount ?? 0);
                    $lineQty = (int) ($ec->quantity ?? 0);
                    $extraChargesTotal += ($lineAmount * $lineQty);
                }
                $extraChargesCount = $bid->extraCharges->count();
                $extraChargeQty = (int) $bid->extraCharges->sum('quantity');
            } else {
                $extraChargesTotal = $extraChargeUnit * $extraChargeQty;
                $extraChargesCount = $extraChargeQty > 0 ? 1 : 0;
            }

            $subTotal = $totalAmount + $extraChargesTotal;

            // Tax
            $countryId = $bid->postrequest->country_id ?? null;
            $taxRate = 0;
            $taxTitle = '';
            if ($countryId) {
                $taxModel = \App\Models\Tax::find($countryId);
                $taxRate = $taxModel->value ?? 0;
                $taxTitle = $taxModel->title ?? '';
            }
            $taxAmount = ($subTotal * $taxRate) / 100;

            // Net Amount = Subtotal - Tax
            $netAmount = $subTotal - $taxAmount;

            // Grand Total = Subtotal + Tax
            $grandTotal = $subTotal + $taxAmount;

            // Advance Payment calculated on Grand Total
            $advAmount = ($totalAmount * $advPct) / 100;

            // Remaining Amount = Grand Total - Advance Payment
            $remaining = $subTotal - $advAmount;
            //   @dd($remaining);
        @endphp

        {{-- Dynamic Next-Step Marquee --}}
        @php
            $status = (string) ($bid->status ?? '');
            $nextActor = null; // 'provider' | 'user' | null
            $nextText = null;

            switch ($status) {
                case 'requested':
                    $nextActor = 'user';
                    $nextText = 'Waiting for customer to accept the bid';
                    break;
                case 'accepted':
                    $nextActor = 'provider';
                    $nextText = 'Waiting for provider to split the payment';
                    break;
                case 'Advance Payment Pending':
                    $nextActor = 'user';
                    $nextText = 'Waiting for customer to pay the advance';
                    break;
                case 'advance_paid':
                    $nextActor = 'provider';
                    $nextText = 'Waiting for provider to start work';
                    break;
                case 'in_process':
                    $nextActor = 'user';
                    $nextText = "Waiting for customer to confirm 'Let's Start Work'";
                    break;
                case 'in_progress':
                    $nextActor = 'provider';
                    $nextText = 'Work in progress — waiting for provider to update or mark done';
                    break;
                case 'hold':
                    $nextActor = 'provider';
                    $nextText = 'Waiting for provider to resume work';
                    break;
                case 'done':
                    $nextActor = 'user';
                    $nextText = 'Waiting for customer to confirm work done';
                    break;
                case 'confirm_done':
                    $nextActor = 'provider';
                    $nextText = 'Waiting for provider to mark the bid as completed';
                    break;
                case 'remaining_paid':
                    $nextActor = null;
                    $nextText = 'Payment completed. You can download the invoice.';
                    break;
                case 'completed':
                    $nextActor = 'user';
                    $nextText = 'Job is completed — waiting for customer to pay remaining amount';
                    break;
                case 'cancelled':
                    $nextActor = null;
                    $nextText = 'This bid has been cancelled.';
                    break;
                default:
                    $nextActor = null;
                    $nextText = null;
            }
        @endphp

        @if ($nextText)
            <div class="w-100 px-3">
                <div class="marquee-banner {{ $nextActor === 'provider' ? 'marquee-provider' : ($nextActor === 'user' ? 'marquee-user' : 'marquee-neutral') }}">
                    <marquee behavior="scroll" direction="left" scrollamount="6">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ $nextText }}
                    </marquee>
                </div>
            </div>
        @endif

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

        {{-- Show Chat button for all participants --}}
        @php
            $isParticipant = ($auth_user->user_type === 'provider' && $auth_user->id == ($bid->provider_id ?? 0)) || ($auth_user->user_type === 'user' && $auth_user->id == ($bid->customer_id ?? 0));
        @endphp
        @if($isParticipant)
            @php
                $chatUserId = ($auth_user->user_type === 'provider') ? $bid->customer_id : $bid->provider_id;
            @endphp
            <a href="{{ route('chat.view.user', $chatUserId) }}" class="btn btn-outline-primary">
                <i class="fas fa-comments"></i> Chat
            </a>
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
                                    <p class="mb-0">{{ $bid->postrequest->street_address ?? '-' }}</p>
                                    <p class="mb-0">{{ $bid->postrequest->house_number ?? '-' }}</p>
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
                                    <td>
                                        @if($hasExtraLines)
                                            Extra Charges ({{ $extraChargesCount }} items)
                                        @else
                                            Extra Charges ({{ $extraChargeQty }} × {{ number_format($extraChargeUnit, 2) }})
                                        @endif
                                    </td>
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
                                    <td class="text-end">€{{ number_format($subTotal, 2) }}</td>
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

    @if($bid->extraCharges && $bid->extraCharges->count() > 0)
    <div class="row mt-3">
        <div class="col-12">
            <div class="card shadow-sm border-0 extra-charges-card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                    <table id="extra-charges-table" class="table table-hover table-sm align-middle mb-0 extra-charges-table">
                        <thead>
                            <tr class="extra-charges-heading">
                                <th colspan="4">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="fw-bold">Extra Charges</span>
                                        <span class="badge rounded-pill bg-light text-dark border">€{{ number_format($extraChargesTotal, 2) }}</span>
                                    </div>
                                </th>
                            </tr>
                            <tr>
                                <th>Title</th>
                                <th class="text-end">Quantity</th>
                                <th class="text-end">Unit Amount</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bid->extraCharges as $line)
                                <tr>
                                    <td>{{ $line->title }}</td>
                                    <td class="text-end">{{ (int) ($line->quantity ?? 0) }}</td>
                                    <td class="text-end">€{{ number_format((float) ($line->amount ?? 0), 2) }}</td>
                                    <td class="text-end">€{{ number_format(((float) ($line->amount ?? 0)) * ((int) ($line->quantity ?? 0)), 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <style>
        .extra-charges-card .card-header {
            background: linear-gradient(180deg, #7d8fb3 0%, #8fa1c4 100%);
            border-bottom: 1px solid rgba(255,255,255,0.25);
        }
        .extra-charges-table thead .extra-charges-heading th {
            background: linear-gradient(180deg, #7d8fb3 0%, #8fa1c4 100%);
            color: #fff;
            border-bottom: none;
        }
        .extra-charges-table thead tr {
            background: #5755d9;
            color: #ffffff;
        }
        .extra-charges-table thead th {
            font-weight: 600;
            letter-spacing: .2px;
            border-bottom: none;
        }
        .extra-charges-table tbody tr td {
            vertical-align: middle;
        }
        .extra-charges-table tbody tr:not(:last-child) td {
            border-bottom: 1px solid #eef1f5;
        }
        .extra-charges-table tbody tr:hover {
            background: #f9fbff;
        }
        .marquee-banner {
            border-radius: 6px;
            padding: 8px 12px;
            margin: 8px 0 4px 0;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
            border-left: 4px solid transparent;
        }
        .marquee-provider {
            background: #fff8e1;
            color: #7a5d00;
            border-left-color: #ffc107;
        }
        .marquee-user {
            background: #e7f1ff;
            color: #084298;
            border-left-color: #0d6efd;
        }
        .marquee-neutral {
            background: #f1f1f1;
            color: #333;
            border-left-color: #6c757d;
        }
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
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
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
                <div id="ec_rows">
                    <div class="row g-2 ec_row align-items-end mb-2">
                        <div class="col-6">
                            <label class="form-label fw-bold">Title</label>
                            <input type="text" class="form-control ec_title" placeholder="e.g., Material" />
                        </div>
                        <div class="col-3">
                            <label class="form-label fw-bold">Amount</label>
                            <input type="number" class="form-control ec_amount" step="0.01" min="0.01" placeholder="20" />
                        </div>
                        <div class="col-2">
                            <label class="form-label fw-bold">Qty</label>
                            <input type="number" class="form-control ec_qty" step="1" min="1" value="1" />
                        </div>
                        <div class="col-1 text-end">
                            <button type="button" class="btn btn-outline-danger btn-sm ec_remove" title="Remove"><i class="la la-times"></i></button>
                        </div>
                    </div>
                </div>
                <button type="button" id="ec_add_row" class="btn btn-outline-primary btn-sm"><i class="la la-plus"></i> Add More</button>
                <div class="mt-3 small text-muted">Extra charges will be included in the final invoice.</div>
            </div>
        `,
                    didOpen: () => {
                        const container = document.getElementById('ec_rows');
                        const addBtn = document.getElementById('ec_add_row');
                        addBtn.addEventListener('click', () => {
                            const row = document.createElement('div');
                            row.className = 'row g-2 ec_row align-items-end mb-2';
                            row.innerHTML = `
                                <div class="col-6">
                                    <input type="text" class="form-control ec_title" placeholder="e.g., Extra work" />
                                </div>
                                <div class="col-3">
                                    <input type="number" class="form-control ec_amount" step="0.01" min="0.01" placeholder="10" />
                                </div>
                                <div class="col-2">
                                    <input type="number" class="form-control ec_qty" step="1" min="1" value="1" />
                                </div>
                                <div class="col-1 text-end">
                                    <button type="button" class="btn btn-outline-danger btn-sm ec_remove" title="Remove"><i class="la la-times"></i></button>
                                </div>`;
                            container.appendChild(row);
                        });
                        container.addEventListener('click', (e) => {
                            if (e.target.closest('.ec_remove')) {
                                const rows = container.querySelectorAll('.ec_row');
                                if (rows.length > 1) e.target.closest('.ec_row').remove();
                            }
                        });
                    },
                    focusConfirm: false,
                    showCancelButton: true,
                    confirmButtonText: 'Save',
                    preConfirm: () => {
                        const rows = Array.from(document.querySelectorAll('#ec_rows .ec_row'));
                        const items = [];
                        for (const r of rows) {
                            const title = r.querySelector('.ec_title').value.trim();
                            const amount = parseFloat(r.querySelector('.ec_amount').value);
                            const qty = parseInt(r.querySelector('.ec_qty').value || '1', 10);
                            if (!title) {
                                Swal.showValidationMessage('Each row must have a title');
                                return false;
                            }
                            if (!amount || amount <= 0) {
                                Swal.showValidationMessage('Each row must have amount > 0');
                                return false;
                            }
                            if (!qty || qty < 1) {
                                Swal.showValidationMessage('Quantity must be at least 1');
                                return false;
                            }
                            items.push({ title, amount, quantity: qty });
                        }
                        if (items.length === 0) {
                            Swal.showValidationMessage('Add at least one row');
                            return false;
                        }
                        return { items };
                    }
                }).then((result) => {
                    if (!result.isConfirmed) return;
                    const { items } = result.value;

                    $.ajax({
                        url: '{{ route('postjob.addExtraCharges', ':id') }}'.replace(':id', bidId),
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            items: items
                        },
                        success: function(response) {
                            if (response && response.status) {
                                Swal.fire('Saved', response.message || 'Extra charges saved', 'success')
                                    .then(() => { window.location.reload(); });
                            } else {
                                Swal.fire('Error', (response && response.message) ? response.message : 'Unable to save', 'error');
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Error', (xhr && xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Something went wrong', 'error');
                        }
                    });
                });
            });

        });
    </script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tbl = $('#extra-charges-table');
            if (tbl && tbl.length && $.fn.dataTable) {
                tbl.DataTable({
                    paging: false,
                    lengthChange: false,
                    searching: false,
                    info: false,
                    order: [],
                    columnDefs: [
                        { targets: [1,2,3], className: 'dt-body-right' }
                    ]
                });
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
                    <div class="alert alert-info d-flex align-items-start mb-3">
                        <i class="fas fa-info-circle me-2 mt-1"></i>
                        <div>
                            <div class="fw-bold">Advance payment guidelines</div>
                            <div class="small mb-0">
                                You can request an advance between 1% and 99% of the total. Values outside this range are not allowed. The remainder will be due later.
                            </div>
                        </div>
                    </div>
                    <div class="mb-3 text-start">
                        <label class="form-label fw-bold">Advance Percentage</label>
                        <input type="number" id="advanceInput" class="form-control" value="${currentAdvance}" min="1" max="99" step="1" placeholder="1-99" />
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
                            const onInput = () => {
                                let val = parseInt(advanceInput.value, 10);
                                if (isNaN(val)) {
                                    remainingInput.value = 100;
                                    return;
                                }
                                if (val > 99) {
                                    val = 99;
                                    advanceInput.value = val;
                                }
                                if (val < 0) {
                                    val = 0;
                                    advanceInput.value = val;
                                }
                                remainingInput.value = 100 - val;
                            };
                            onInput();
                            advanceInput.addEventListener('input', onInput);
                            advanceInput.addEventListener('blur', () => {
                                let val = parseInt(advanceInput.value, 10);
                                if (isNaN(val)) return; // leave empty if user cleared; preConfirm will validate
                                if (val < 1) { val = 1; advanceInput.value = val; }
                                if (val > 99) { val = 99; advanceInput.value = val; }
                                remainingInput.value = 100 - val;
                            });
                        },
                        preConfirm: () => {
                            const raw = document.getElementById('advanceInput').value;
                            if (raw === '' || raw === null) {
                                Swal.showValidationMessage("Advance must be between 1% and 99%.");
                                return false;
                            }
                            const advance = parseInt(raw, 10);
                            if (isNaN(advance) || advance < 1 || advance > 99) {
                                Swal.showValidationMessage("Advance must be between 1% and 99%.");
                                return false;
                            }
                            return { advance, remaining: 100 - advance };
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
