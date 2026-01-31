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
                    $nextText = 'Waiting for Employer to split the payment';
                    break;
                case 'Advance Payment Pending':
                    $nextActor = 'user';
                    $nextText = 'Waiting for customer to pay the advance';
                    break;
                case 'advance_paid':
                    $nextActor = 'provider';
                    $nextText = 'Waiting for Employer to start work';
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
                    $nextText = 'Waiting for Employer to resume work';
                    break;
                case 'done':
                    $nextActor = 'user';
                    $nextText = 'Waiting for customer to confirm work done';
                    break;
                case 'confirm_done':
                    $nextActor = 'provider';
                    $nextText = 'Waiting for Employer to mark the bid as completed';
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

        {{-- Chat button visible only after advance payment --}}
        @php
            $isParticipant = ($auth_user->user_type === 'provider' && $auth_user->id == ($bid->provider_id ?? 0)) || ($auth_user->user_type === 'user' && $auth_user->id == ($bid->customer_id ?? 0));
            $statusKey = strtolower((string) ($bid->status ?? ''));
            $advancePaid = in_array($statusKey, ['advance_paid','in_progress','done','confirm_done','completed','remaining_paid']);
        @endphp
        @if($isParticipant && $advancePaid)
            @php
                $chatUserId = ($auth_user->user_type === 'provider') ? $bid->customer_id : $bid->provider_id;
            @endphp
            <a href="{{ route('chat.view.user', $chatUserId) }}" class="btn btn-outline-secondary rounded-pill px-4 py-2 shadow-sm me-2 d-inline-flex align-items-center gap-2">
                <i class="fas fa-comments"></i>
                <span>Chat</span>
            </a>
        @endif
        @php
        $canRatePostBid = auth()->user()->user_type === 'user'
            && (int)auth()->id() === (int)($bid->customer_id ?? 0)
            && in_array(strtolower((string)$bid->status), ['remaining_paid']);
    @endphp
    @if($canRatePostBid)
        <button type="button" class="btn btn-warning rounded-pill px-4 py-2 shadow-sm me-2 d-inline-flex align-items-center gap-2 mt-2"
                id="postbid-rate-now-btn" data-id="{{ $bid->id }}" data-bs-toggle="modal" data-bs-target="#postBidRatingModal">
            <i class="las la-star"></i>
            <span>Rate Now</span>
        </button>
    @endif

    @php
        $canProviderRateCustomer = (auth()->user()->user_type === 'provider'
            && (int)auth()->id() === (int)($bid->provider_id ?? 0)
            && ($showRateCustomerButton ?? false)); // showRateCustomerButton = provider has not yet rated (from controller)
    @endphp
    @if($canProviderRateCustomer)
        <button type="button" class="btn btn-info rounded-pill px-4 py-2 shadow-sm me-2 d-inline-flex align-items-center gap-2 mt-2"
                id="postbid-rate-customer-btn" data-id="{{ $bid->id }}" data-bs-toggle="modal" data-bs-target="#postBidRateCustomerModal">
            <i class="las la-star"></i>
            <span>Rate Customer</span>
        </button>
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
                <button class="btn btn-success rounded-pill px-4 py-2 shadow-sm payAdvanceBtn d-inline-flex align-items-center gap-2" data-post-id="{{ $bid->id }}"
                    data-amount="{{ $advAmount }}">
                    <i class="fas fa-wallet"></i>
                    <span>Pay Advance {{ getPriceFormat($advAmount) }}</span>
                    ({{ $advPct }}%)
                </button>
            @elseif($bid->status === 'completed' && !$bid->has_advance_paid)
                <button class="btn btn-primary rounded-pill px-4 py-2 shadow-sm payRemainingBtn d-inline-flex align-items-center gap-2" data-post-id="{{ $bid->id }}"
                    data-amount="{{ number_format($remaining, 2, '.', '') }}">
                    <i class="fas fa-credit-card"></i>
                    <span>Pay Remaining {{ getPriceFormat($remaining) }}</span>
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
                                <p class="mb-0">{{ optional($bid->postrequest->start_date ? \Carbon\Carbon::parse($bid->postrequest->start_date) : null)?->format('Y-m-d') ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card border-danger shadow-sm h-100 hover-shadow">
                            <div class="card-body text-center">
                                <i class="far fa-calendar-times fa-2x text-danger mb-2"></i>
                                <h6 class="fw-bold mb-1">End Date</h6>
                                <p class="mb-0">{{ optional($bid->postrequest->end_date ? \Carbon\Carbon::parse($bid->postrequest->end_date) : null)?->format('Y-m-d') ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    @php
                        $advancePaidStatuses = ['advance_paid', 'in_progress', 'hold', 'done', 'confirm_done', 'remaining_paid', 'completed'];
                        $showWorkingAddress = in_array($bid->status ?? '', $advancePaidStatuses);
                    @endphp
                    @if ($showWorkingAddress)
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
                                    {{ isset($bid->postrequest->total_budget) ? getPriceFormat($bid->postrequest->total_budget) : '-' }}
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
                                <h6 class="fw-bold mb-1">Employer</h6>
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
                                @php
                                    $statusKey = strtolower((string)($bid->status ?? ''));
                                    $statusMap = [
                                        'requested' => ['Requested', 'bg-secondary text-white'],
                                        'pending' => ['Pending', 'bg-warning text-dark'],
                                        'accepted' => ['Accepted', 'bg-info text-white'],
                                        'advance_paid' => ['Advance Paid', 'bg-primary text-white'],
                                        'in_progress' => ['IN Progress', 'bg-primary text-white'],
                                        'in_process' => ['IN Process', 'bg-primary text-white'],
                                        'done' => ['Done', 'bg-success text-white'],
                                        'confirm_done' => ['Confirm Done', 'bg-success text-white'],
                                        'completed' => ['Completed', 'bg-success text-white'],
                                        'remaining_paid' => ['Remaining Paid', 'bg-primary text-white'],
                                        'hold' => ['On Hold', 'bg-warning text-dark'],
                                        'cancelled' => ['Cancelled', 'bg-danger text-white'],
                                        'rejected' => ['Rejected', 'bg-danger text-white'],
                                    ];
                                    [$label, $cls] = $statusMap[$statusKey] ?? [ucwords(str_replace('_',' ', (string)($bid->status ?? '-'))), 'bg-secondary text-white'];
                                @endphp
                                <span class="badge px-3 py-2 {{ $cls }}">{{ $label }}</span>
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
                                    <td class="text-end">{{ getPriceFormat($unitPrice) }}</td>
                                </tr>
                                <tr>
                                    <td>Quantity (Packages / Hours / Days)</td>
                                    <td class="text-end">{{ $quantity }}</td>
                                </tr>
                                <tr>
                                    <td>Total Amount</td>
                                    <td class="text-end">{{ getPriceFormat($totalAmount) }}</td>
                                </tr>
                                <tr>
                                    <td>
                                        @if($hasExtraLines)
                                            Extra Charges ({{ $extraChargesCount }} items)
                                        @else
                                            Extra Charges ({{ $extraChargeQty }} × {{ getPriceFormat($extraChargeUnit) }})
                                        @endif
                                    </td>
                                    <td class="text-end">{{ getPriceFormat($extraChargesTotal) }}</td>
                                </tr>
                                <tr class="fw-bold">
                                    <td>Subtotal</td>
                                    <td class="text-end">{{ getPriceFormat($subTotal) }}</td>
                                </tr>

                                <tr class="fw-bold">
                                    <td>Net Amount </td>
                                    <td class="text-end">{{ getPriceFormat($netAmount) }}</td>
                                </tr>
                                <tr>
                                    <td>Tax ({{ number_format($taxRate, 0) }}%) {{ $taxTitle }}</td>
                                    <td class="text-end">{{ getPriceFormat($taxAmount) }}</td>
                                </tr>

                                <tr class="fw-bold">
                                    <td>Grand Total</td>
                                    <td class="text-end">{{ getPriceFormat($subTotal) }}</td>
                                </tr>
                                <tr>
                                    <td>Advance Payment ({{ $advPct }}%)</td>
                                    <td class="text-end">{{ getPriceFormat($advAmount) }}</td>
                                </tr>
                                <tr class="fw-bold">
                                    <td>Remaining Amount</td>

                                    <td class="text-end">{{ getPriceFormat($remaining) }}</td>
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
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="las la-receipt"></i>
                                            <span class="fw-semibold">Extra Charges</span>
                                            <span class="badge bg-light text-dark border">
                                                {{ $extraChargesCount }} {{ \Illuminate\Support\Str::plural('item', $extraChargesCount) }}
                                            </span>
                                        </div>
                                        <div class="text-end">
                                            <span class="small text-white-50 me-2">Total</span>
                                            <span class="badge rounded-pill bg-light text-dark border">{{ getPriceFormat($extraChargesTotal) }}</span>
                                        </div>
                                    </div>
                                </th>
                            </tr>
                            <tr>
                                <th>Title</th>
                                <th class="text-end">Quantity</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bid->extraCharges as $line)
                                <tr>
                                    <td>{{ $line->title }}</td>
                                    <td class="text-end">{{ (int) ($line->quantity ?? 0) }}</td>
                                    <td class="text-end">{{ getPriceFormat((float) ($line->amount ?? 0)) }}</td>
                                    <td class="text-end">{{ getPriceFormat(((float) ($line->amount ?? 0)) * ((int) ($line->quantity ?? 0))) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
        </div>
                </div>
            </div>

<div class="modal fade" id="postBidRatingModal" tabindex="-1" aria-labelledby="postBidRatingModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="postBidRatingModalLabel">Rate {{ $bid->provider->display_name ?? 'Employer' }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="postBidRatingForm">
            <input type="hidden" id="postBidIdForRating" value="{{ $bid->id }}">
            <div class="mb-3 text-center">
                <span class="postbid-star" data-value="1">★</span>
                <span class="postbid-star" data-value="2">★</span>
                <span class="postbid-star" data-value="3">★</span>
                <span class="postbid-star" data-value="4">★</span>
                <span class="postbid-star" data-value="5">★</span>
            </div>
            <div class="mb-3">
                <label class="form-label">Comments (optional)</label>
                <textarea id="postBidReviewText" class="form-control" rows="3" placeholder="Share your experience"></textarea>
            </div>
            <div class="text-end">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </form>
      </div>
    </div>
  </div>
  <style>
    .postbid-star{cursor:pointer;font-size:28px;color:#ccc;margin:0 2px}
    .postbid-star.selected{color:#f1c40f}
  </style>
</div>

        </div>
    </div>
    @endif

{{-- Provider rates customer modal (outside extraCharges block so it's always in DOM when provider can rate) --}}
@if(($showRateCustomerButton ?? false) && auth()->user()->user_type === 'provider' && (int)auth()->id() === (int)($bid->provider_id ?? 0))
<div class="modal fade" id="postBidRateCustomerModal" tabindex="-1" aria-labelledby="postBidRateCustomerModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="postBidRateCustomerModalLabel">Rate {{ $bid->customer->display_name ?? $bid->customer->username ?? 'Customer' }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="postBidRateCustomerForm">
            <input type="hidden" id="postBidIdForRateCustomer" value="{{ $bid->id }}">
            <div class="mb-3 text-center">
                <span class="provider-rate-star" data-value="1">★</span>
                <span class="provider-rate-star" data-value="2">★</span>
                <span class="provider-rate-star" data-value="3">★</span>
                <span class="provider-rate-star" data-value="4">★</span>
                <span class="provider-rate-star" data-value="5">★</span>
            </div>
            <div class="mb-3">
                <label class="form-label">Comments (optional)</label>
                <textarea id="postBidRateCustomerReview" class="form-control" rows="3" placeholder="Share your experience"></textarea>
            </div>
            <div class="text-end">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </form>
      </div>
    </div>
  </div>
  <style>
    .provider-rate-star{cursor:pointer;font-size:28px;color:#ccc;margin:0 2px}
    .provider-rate-star.selected{color:#f1c40f}
  </style>
</div>
@endif

    @if($bid->ratings && $bid->ratings->count() > 0)
    <div class="container py-3">
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white fw-bold">
                        Ratings
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Customer</th>
                                        <th class="text-center">Rating</th>
                                        <th>Review</th>
                                        <th class="text-end">Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bid->ratings as $r)
                                        <tr>
                                            <td>{{ $bid->customer->display_name ?? ('#'.$r->customer_id) }}</td>
                                            <td class="text-center">
                                                @php $stars = max(1, min(5, (int)($r->rating ?? 0))); @endphp
                                                <span class="text-warning">{!! str_repeat('★', $stars) !!}</span>
                                                <span class="text-muted">{!! str_repeat('☆', 5 - $stars) !!}</span>
                                            </td>
                                            <td>{{ $r->review ?: '-' }}</td>
                                            <td class="text-end">{{ optional($r->created_at)->format('Y-m-d') ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    <style>
        /* Fix text color for cards with red-blue gradient background */
        .card.border-primary .card-body,
        .card.border-primary .card-body * {
            color: #fff !important;
        }
        
        .card.border-primary .card-body h6,
        .card.border-primary .card-body p,
        .card.border-primary .card-body i {
            color: #fff !important;
        }
        
        .card.border-primary .card-body i.text-primary {
            color: rgba(255, 255, 255, 0.9) !important;
            -webkit-text-fill-color: rgba(255, 255, 255, 0.9) !important;
            background: transparent !important;
            -webkit-background-clip: unset !important;
            background-clip: unset !important;
        }
        
        /* Red-Blue Gradient for Card Headers */
        .card-header.bg-primary {
            background: linear-gradient(135deg, #FF0000 0%, #5F60B9 100%) !important;
            color: #fff !important;
            border: none !important;
        }
        
        /* Red-Blue Gradient for Table Headers */
        .table thead th,
        .table thead tr th {
            background: linear-gradient(135deg, #FF0000 0%, #5F60B9 100%) !important;
            color: #fff !important;
            border-color: transparent !important;
        }
        
        .extra-charges-card .card-header {
            background: linear-gradient(135deg, #FF0000 0%, #5F60B9 100%) !important;
            border-bottom: 1px solid rgba(255,255,255,0.25);
        }
        .extra-charges-table thead .extra-charges-heading th {
            background: linear-gradient(135deg, #FF0000 0%, #5F60B9 100%) !important;
            color: #fff !important;
            border-bottom: none !important;
        }
        .extra-charges-table thead tr {
            background: linear-gradient(135deg, #FF0000 0%, #5F60B9 100%) !important;
            color: #ffffff !important;
        }
        .extra-charges-table thead th {
            background: linear-gradient(135deg, #FF0000 0%, #5F60B9 100%) !important;
            color: #fff !important;
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
    <!-- Ensure jQuery is present before any plugin relying on it -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script>
        // Dynamic currency for this page
        const DEFAULT_CURRENCY = @json(Currency::getDefaultCurrency(true));
        const DECIMALS = DEFAULT_CURRENCY?.afterdecimalpoint ?? 2;
        const POSITION = DEFAULT_CURRENCY?.defaultPosition ?? 'left';
        const CURRENCY_SYMBOL = DEFAULT_CURRENCY?.defaultCurrency?.symbol ?? '€';
        function formatCurrencyJS(amount) {
            const n = Number(amount || 0).toFixed(DECIMALS);
            switch (String(POSITION)) {
                case 'left_with_space': return `${CURRENCY_SYMBOL} ${n}`;
                case 'right': return `${n}${CURRENCY_SYMBOL}`;
                case 'right_with_space': return `${n} ${CURRENCY_SYMBOL}`;
                case 'left':
                default: return `${CURRENCY_SYMBOL}${n}`;
            }
        }
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
                                <p class="mb-2">Amount: <strong>${formatCurrencyJS(formattedAmount)}</strong></p>
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
                                                    'remaining' : 'advance',
                                                use_checkout: true
                                            })
                                        }).then(res => res.json())
                                    .then(session => {
                                        if (session && session.status && session.url) {
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
  <div class="mb-2"><strong>Amount:</strong> ${formatCurrencyJS(formattedAmount)}</div>
  <div class="mb-2"><strong>For local and international transfers</strong></div>
  <div><strong>Recipient:</strong> Ben Ghezaiel</div>
  <div><strong>IBAN:</strong> DE02 1001 0178 1361 6331 79</div>
  <div><strong>BIC:</strong> REVODEB2</div>
  <div class="mt-2"><strong>Bank Name and Address:</strong></div>
  <div class="ms-3">Revolut Bank UAB,<br>
    Zweigniederlassung Deutschland<br>
    FORA Linden Palais, Unter den<br>
    Linden 40<br>
    10117, Berlin, Germany</div>
  <div class="mt-2"><strong>BIC of Sender Bank:</strong> CHASDEFX</div>
  
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
        // Post Bid Rating (mirrors booking rating behavior)
        let postBidSelectedRating = 0;

        $(document).on('click', '#postbid-rate-now-btn', function(){
            postBidSelectedRating = 0;
            $('.postbid-star').removeClass('selected');
            $('#postBidReviewText').val('');
            const modalEl = document.getElementById('postBidRatingModal');
            try {
                if (window.bootstrap && modalEl) {
                    let modal = window.bootstrap.Modal.getInstance(modalEl);
                    if (!modal) modal = new window.bootstrap.Modal(modalEl);
                    modal.show();
                } else {
                    $('#postBidRatingModal').modal('show');
                }
            } catch(e) {
                $('#postBidRatingModal').modal('show');
            }
        });

        $(document).on('click', '.postbid-star', function(){
            postBidSelectedRating = $(this).data('value');
            $('.postbid-star').removeClass('selected');
            $(this).prevAll().addBack().addClass('selected');
        });

        $('#postBidRatingForm').on('submit', function(e){
            e.preventDefault();
            if(postBidSelectedRating === 0){
                return Swal.fire('Error','Please select a star rating.','warning');
            }
            const payload = {
                post_job_bid_id: $('#postBidIdForRating').val(),
                provider_id: '{{ $bid->provider_id }}',
                customer_id: '{{ $bid->customer_id }}',
                rating: postBidSelectedRating,
                review: ($('#postBidReviewText').val() || '').trim()
            };
            $.ajax({
                url: '{{ url('/api/postbid/rating/save') }}',
                type: 'POST',
                data: payload,
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                success: function(){
                    Swal.fire('Thank you!','Your rating has been submitted.','success');
                    $('#postBidRatingModal').modal('hide');
                    window.location.reload();
                },
                error: function(){
                    Swal.fire('Error','Failed to submit rating.','error');
                }
            });
        });

        // Provider rates customer – open modal explicitly (same as Rate Now)
        let providerRateCustomerStars = 0;
        $(document).on('click', '#postbid-rate-customer-btn', function(e){
            e.preventDefault();
            providerRateCustomerStars = 0;
            $('.provider-rate-star').removeClass('selected');
            $('#postBidRateCustomerReview').val('');
            var modalEl = document.getElementById('postBidRateCustomerModal');
            if (modalEl) {
                try {
                    if (window.bootstrap && window.bootstrap.Modal) {
                        var modal = window.bootstrap.Modal.getInstance(modalEl);
                        if (!modal) modal = new window.bootstrap.Modal(modalEl);
                        modal.show();
                    } else {
                        $('#postBidRateCustomerModal').modal('show');
                    }
                } catch (err) {
                    $('#postBidRateCustomerModal').modal('show');
                }
            }
        });
        $(document).on('click', '.provider-rate-star', function(){
            providerRateCustomerStars = $(this).data('value');
            $('.provider-rate-star').removeClass('selected');
            $(this).prevAll().addBack().addClass('selected');
        });
        $('#postBidRateCustomerForm').on('submit', function(e){
            e.preventDefault();
            if(providerRateCustomerStars === 0){
                return Swal.fire('Error','Please select a star rating.','warning');
            }
            const payload = {
                post_job_bid_id: $('#postBidIdForRateCustomer').val(),
                provider_id: '{{ $bid->provider_id }}',
                customer_id: '{{ $bid->customer_id }}',
                rating: providerRateCustomerStars,
                review: ($('#postBidRateCustomerReview').val() || '').trim()
            };
            $.ajax({
                url: '{{ url('/api/postbid/rating-by-provider/save') }}',
                type: 'POST',
                data: payload,
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                success: function(){
                    Swal.fire('Thank you!','Your rating has been submitted.','success');
                    $('#postBidRateCustomerModal').modal('hide');
                    window.location.reload();
                },
                error: function(){
                    Swal.fire('Error','Failed to submit rating.','error');
                }
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
