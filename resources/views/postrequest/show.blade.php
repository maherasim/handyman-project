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
            $normalizedStatus = strtolower(str_replace('_', ' ', trim($status)));
            $nextActor = null; // 'provider' | 'user' | null
            $nextText = null;

            switch ($normalizedStatus) {
                case 'requested':
                    $nextActor = 'user';
                    $nextText = __('messages.pjr_waiting_customer_accept');
                    break;
                case 'accepted':
                    $nextActor = 'provider';
                    $nextText = __('messages.pjr_waiting_employer_split');
                    break;
                case 'advance payment pending':
                    $nextActor = 'user';
                    $nextText = __('messages.pjr_waiting_customer_pay_advance');
                    break;
                case 'advance_paid':
                    $nextActor = 'provider';
                    $nextText = __('messages.pjr_waiting_employer_start');
                    break;
                case 'in_process':
                    $nextActor = 'user';
                    $nextText = __('messages.pjr_waiting_confirm_start');
                    break;
                case 'in_progress':
                    $nextActor = 'provider';
                    $nextText = __('messages.pjr_work_in_progress');
                    break;
                case 'hold':
                    $nextActor = 'provider';
                    $nextText = __('messages.pjr_waiting_employer_resume');
                    break;
                case 'done':
                    $nextActor = 'user';
                    $nextText = __('messages.pjr_waiting_customer_confirm_done');
                    break;
                case 'confirm_done':
                    $nextActor = 'provider';
                    $nextText = __('messages.pjr_waiting_employer_complete');
                    break;
                case 'remaining_paid':
                    $nextActor = null;
                    $nextText = __('messages.pjr_payment_completed');
                    break;
                case 'completed':
                    $nextActor = 'user';
                    $nextText = __('messages.pjr_waiting_pay_remaining');
                    break;
                case 'cancelled':
                    $nextActor = null;
                    $nextText = __('messages.pjr_bid_cancelled');
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
                    <i class="fas fa-sliders-h"></i> {{ __('messages.pjr_split_payment') }}
                </button>
                <button class="btn btn-success updateStatusBtn" data-id="{{ $bid->id }}" data-status="cancelled">
                    {{ __('messages.pjr_cancel') }}
                </button>
            @elseif($bid->status === 'advance_paid')
                <button class="btn btn-primary updateStatusBtn" data-id="{{ $bid->id }}" data-status="in_process">
                    {{ __('messages.pjr_start_work') }}
                </button>
            @elseif($bid->status === 'in_progress')
                <button class="btn btn-warning holdBidBtn" data-id="{{ $bid->id }}">
                    {{ __('messages.pjr_hold') }}
                </button>
                <button class="btn btn-success updateStatusBtn" data-id="{{ $bid->id }}" data-status="done">
                    {{ __('messages.pjr_done') }}
                </button>
            @elseif($bid->status === 'hold')
                <button class="btn btn-primary updateStatusBtn" data-id="{{ $bid->id }}" data-status="in_progress">
                    {{ __('messages.pjr_resume_work') }}
                </button>
            @elseif($bid->status === 'confirm_done')
                <button class="btn btn-primary updateStatusBtn" data-id="{{ $bid->id }}" data-status="completed">
                    {{ __('messages.pjr_completed') }}
                </button>
                <button class="btn btn-outline-secondary extraChargesBtn" data-id="{{ $bid->id }}">
                    <i class="fas fa-plus"></i> {{ __('messages.pjr_extra_charges') }}
                </button>
            @elseif($bid->status === 'remaining_paid')
                <a href="{{ route('postrequest.invoice', $bid->id) }}" class="btn btn-outline-success ms-2">
                    <i class="fas fa-file-download"></i> {{ __('messages.pjr_download_invoice') }}
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
                <span>{{ __('messages.pjr_chat') }}</span>
            </a>
        @endif
        @php
        $canRatePostBid = auth()->user()->user_type === 'user'
            && (int)auth()->id() === (int)($bid->customer_id ?? 0)
            && ($showRateNowButton ?? false); // showRateNowButton = customer has not yet rated employer (from controller)
    @endphp
    @if($canRatePostBid)
        <button type="button" class="btn btn-warning rounded-pill px-4 py-2 shadow-sm me-2 d-inline-flex align-items-center gap-2 mt-2"
                id="postbid-rate-now-btn" data-id="{{ $bid->id }}" data-bs-toggle="modal" data-bs-target="#postBidRatingModal">
            <i class="las la-star"></i>
            <span>{{ __('messages.pjr_rate_employer') }}</span>
        </button>
    @endif
    @if(auth()->user()->user_type === 'user' && (int)auth()->id() === (int)($bid->customer_id ?? 0) && ($customerHasRatedProvider ?? false) && in_array(strtolower((string)($bid->status ?? '')), ['remaining_paid', 'confirm_done', 'completed']))
        <span class="badge bg-secondary rounded-pill px-3 py-2 mt-2 me-2">{{ __('messages.pjr_rated_employer') }}</span>
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
            <span>{{ __('messages.pjr_rate_customer') }}</span>
        </button>
    @endif






        {{-- Customer Actions --}}
        @if ($auth_user->user_type === 'user' && $auth_user->id == $bid->customer_id)

            @if ($bid->status === 'requested')
                <button class="btn btn-success updateStatusBtn" data-id="{{ $bid->id }}"
                    data-status="accepted">{{ __('messages.pjr_accept') }}</button>
            @elseif($bid->status === 'in_process')
                <button class="btn btn-info updateStatusBtn" data-id="{{ $bid->id }}" data-status="in_progress">
                    {{ __('messages.pjr_lets_start_work') }}
                </button>
            @elseif($bid->status === 'done')
                <button class="btn btn-info updateStatusBtn" data-id="{{ $bid->id }}" data-status="confirm_done">
                    {{ __('messages.pjr_confirm_work_done') }}
                </button>
            @elseif($bid->status === 'accepted')
                <button class="btn btn-info updateStatusBtn" data-id="{{ $bid->id }}" data-status="cancelled">
                    {{ __('messages.pjr_cancel') }}
                </button>
            @elseif($bid->status === 'remaining_paid')
                <a href="{{ route('postrequest.invoice', $bid->id) }}" class="btn btn-outline-success ms-2">
                    <i class="fas fa-file-download"></i> {{ __('messages.pjr_download_invoice') }}
                </a>
            @elseif($normalizedStatus === 'advance payment pending')
                <button class="btn btn-success rounded-pill px-4 py-2 shadow-sm payAdvanceBtn d-inline-flex align-items-center gap-2" data-post-id="{{ $bid->id }}"
                    data-amount="{{ $advAmount }}">
                    <i class="fas fa-wallet"></i>
                    <span>{{ __('messages.pjr_pay_advance_btn') }} {{ getPriceFormat($advAmount) }}</span>
                    ({{ $advPct }}%)
                </button>
            @elseif($bid->status === 'completed' && !$bid->has_advance_paid)
                <button class="btn btn-primary rounded-pill px-4 py-2 shadow-sm payRemainingBtn d-inline-flex align-items-center gap-2" data-post-id="{{ $bid->id }}"
                    data-amount="{{ number_format($remaining, 2, '.', '') }}">
                    <i class="fas fa-credit-card"></i>
                    <span>{{ __('messages.pjr_pay_remaining_btn') }} {{ getPriceFormat($remaining) }}</span>
                </button>
            @elseif($bid->status === 'hold')
                <div class="alert alert-warning d-flex align-items-start shadow-sm border rounded p-3 mt-2">
                    <i class="fas fa-exclamation-triangle fa-lg me-2 text-danger"></i>
                    <div>
                        <h6 class="fw-bold mb-1">{{ __('messages.pjr_bid_on_hold') }}</h6>
                        <p class="mb-0 text-muted">
                            {{ __('messages.pjr_reason') }} <span class="fw-bold">{{ $bid->hold_reason ?? __('messages.pjr_no_reason_provided') }}</span>
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
                                <h6 class="fw-bold mb-1">{{ __('messages.pjr_title') }}</h6>
                                <p class="mb-0">{{ $bid->postrequest->title ?? $bid->title }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card border-success shadow-sm h-100 hover-shadow">
                            <div class="card-body text-center">
                                <i class="fas fa-map-marker-alt fa-2x text-success mb-2"></i>
                                <h6 class="fw-bold mb-1">{{ __('messages.pjr_location') }}</h6>
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
                                <h6 class="fw-bold mb-1">{{ __('messages.pjr_job_type') }}</h6>
                                <p class="mb-0">{{ $bid->postrequest->type ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card border-warning shadow-sm h-100 hover-shadow">
                            <div class="card-body text-center">
                                <i class="fas fa-dollar-sign fa-2x text-warning mb-2"></i>
                                <h6 class="fw-bold mb-1">{{ __('messages.pjr_rate_type') }}</h6>
                                <p class="mb-0">{{ $bid->postrequest->job_price ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card border-info shadow-sm h-100 hover-shadow">
                            <div class="card-body text-center">
                                <i class="far fa-calendar-check fa-2x text-info mb-2"></i>
                                <h6 class="fw-bold mb-1">{{ __('messages.pjr_start_date') }}</h6>
                                <p class="mb-0">{{ optional($bid->postrequest->start_date ? \Carbon\Carbon::parse($bid->postrequest->start_date) : null)?->format('Y-m-d') ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card border-danger shadow-sm h-100 hover-shadow">
                            <div class="card-body text-center">
                                <i class="far fa-calendar-times fa-2x text-danger mb-2"></i>
                                <h6 class="fw-bold mb-1">{{ __('messages.pjr_end_date') }}</h6>
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
                                    <h6 class="fw-bold mb-1">{{ __('messages.pjr_working_address') }}</h6>
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
                                <h6 class="fw-bold mb-1">{{ __('messages.pjr_total_budget') }}</h6>
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
                                <h6 class="fw-bold mb-1">{{ __('messages.pjr_purposal') }}</h6>
                                <p class="mb-0">{{ $bid->postrequest->postBidList->count() ?? 0 }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card border-primary shadow-sm h-100 hover-shadow">
                            <div class="card-body text-center">
                                <i class="fas fa-user fa-2x text-primary mb-2"></i>
                                <h6 class="fw-bold mb-1">{{ __('messages.pjr_employer') }}</h6>
                                <p class="mb-0">{{ $bid->provider->display_name ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card border-success shadow-sm h-100 hover-shadow">
                            <div class="card-body text-center">
                                <i class="fas fa-user-tie fa-2x text-success mb-2"></i>
                                <h6 class="fw-bold mb-1">{{ __('messages.customer') }}</h6>
                                <p class="mb-0">{{ $bid->customer->display_name ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card border-info shadow-sm h-100 hover-shadow">
                            <div class="card-body text-center">
                                <i class="fas fa-flag fa-2x text-info mb-2"></i>
                                <h6 class="fw-bold mb-1">{{ __('messages.status') }}</h6>
                                @php
                                    $statusKey = strtolower(str_replace('_', ' ', trim((string)($bid->status ?? ''))));
                                    $statusMap = [
                                        'requested' => [__('messages.pjr_st_requested'), 'bg-secondary text-white'],
                                        'pending' => [__('messages.pjr_st_pending'), 'bg-warning text-dark'],
                                        'accepted' => [__('messages.pjr_st_accepted'), 'bg-info text-white'],
                                        'advance paid' => [__('messages.pjr_st_advance_paid'), 'bg-primary text-white'],
                                        'advance payment pending' => [__('messages.pjr_st_advance_payment_pending'), 'bg-warning text-dark'],
                                        'in progress' => [__('messages.pjr_st_in_progress'), 'bg-primary text-white'],
                                        'in process' => [__('messages.pjr_st_in_process'), 'bg-primary text-white'],
                                        'done' => [__('messages.pjr_st_done'), 'bg-success text-white'],
                                        'confirm done' => [__('messages.pjr_st_confirm_done'), 'bg-success text-white'],
                                        'completed' => [__('messages.pjr_st_completed'), 'bg-success text-white'],
                                        'remaining paid' => [__('messages.pjr_st_remaining_paid'), 'bg-primary text-white'],
                                        'hold' => [__('messages.pjr_st_hold'), 'bg-warning text-dark'],
                                        'cancelled' => [__('messages.pjr_st_cancelled'), 'bg-danger text-white'],
                                        'rejected' => [__('messages.pjr_st_rejected'), 'bg-danger text-white'],
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
                        {{ __('messages.pjr_price_breakdown') }}
                    </div>
                    <div class="card-body">


                        <table class="table table-sm table-hover price-table">
                            <tbody>
                                <tr>
                                    <td>{{ __('messages.pjr_rate_unit_price') }}</td>
                                    <td class="text-end">{{ getPriceFormat($unitPrice) }}</td>
                                </tr>
                                <tr>
                                    <td>{{ __('messages.pjr_quantity_packages_hours_days') }}</td>
                                    <td class="text-end">{{ $quantity }}</td>
                                </tr>
                                <tr>
                                    <td>{{ __('messages.pjr_total_amount') }}</td>
                                    <td class="text-end">{{ getPriceFormat($totalAmount) }}</td>
                                </tr>
                                <tr>
                                    <td>
                                        @if($hasExtraLines)
                                            {{ __('messages.pjr_extra_charges_n_items', ['count' => $extraChargesCount]) }}
                                        @else
                                            {{ __('messages.pjr_extra_charges_n_times', ['qty' => $extraChargeQty, 'unit' => getPriceFormat($extraChargeUnit)]) }}
                                        @endif
                                    </td>
                                    <td class="text-end">{{ getPriceFormat($extraChargesTotal) }}</td>
                                </tr>
                                <tr class="fw-bold">
                                    <td>{{ __('messages.sub_total') }}</td>
                                    <td class="text-end">{{ getPriceFormat($subTotal) }}</td>
                                </tr>

                                <tr class="fw-bold">
                                    <td>{{ __('messages.pjr_net_amount') }}</td>
                                    <td class="text-end">{{ getPriceFormat($netAmount) }}</td>
                                </tr>
                                <tr>
                                    <td>{{ __('messages.pjr_tax_line', ['rate' => number_format($taxRate, 0), 'title' => $taxTitle]) }}</td>
                                    <td class="text-end">{{ getPriceFormat($taxAmount) }}</td>
                                </tr>

                                <tr class="fw-bold">
                                    <td>{{ __('messages.grand_total') }}</td>
                                    <td class="text-end">{{ getPriceFormat($subTotal) }}</td>
                                </tr>
                                <tr>
                                    <td>{{ __('messages.pjr_advance_payment_line', ['pct' => $advPct]) }}</td>
                                    <td class="text-end">{{ getPriceFormat($advAmount) }}</td>
                                </tr>
                                <tr class="fw-bold">
                                    <td>{{ __('messages.pjr_remaining_amount') }}</td>

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
                                            <span class="fw-semibold">{{ __('messages.pjr_extra_charges') }}</span>
                                            <span class="badge bg-light text-dark border">
                                                {{ $extraChargesCount }} {{ $extraChargesCount === 1 ? __('messages.pjr_item_singular') : __('messages.pjr_items_plural') }}
                                            </span>
                                        </div>
                                        <div class="text-end">
                                            <span class="small text-white-50 me-2">{{ __('messages.pjr_ec_total_badge') }}</span>
                                            <span class="badge rounded-pill bg-light text-dark border">{{ getPriceFormat($extraChargesTotal) }}</span>
                                        </div>
                                    </div>
                                </th>
                            </tr>
                            <tr>
                                <th>{{ __('messages.pjr_ec_column_title') }}</th>
                                <th class="text-end">{{ __('messages.pjr_ec_column_qty') }}</th>
                                <th class="text-end">{{ __('messages.pjr_ec_column_unit') }}</th>
                                <th class="text-end">{{ __('messages.pjr_ec_column_total') }}</th>
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

        </div>
    </div>
    @endif

{{-- Customer rates provider modal – outside extraCharges block so it's in DOM when Rate Now is shown (even when bid has no extra charges) --}}
@if($canRatePostBid ?? false)
<div class="modal fade" id="postBidRatingModal" tabindex="-1" aria-labelledby="postBidRatingModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="postBidRatingModalLabel">{{ __('messages.pjr_modal_rate_title', ['name' => $bid->provider->display_name ?? __('messages.pjr_fallback_employer')]) }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('messages.close') }}"></button>
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
                <label class="form-label">{{ __('messages.pjr_comments_optional') }}</label>
                <textarea id="postBidReviewText" class="form-control" rows="3" placeholder="{{ __('messages.pjr_placeholder_share_experience') }}"></textarea>
            </div>
            <div class="text-end">
                <button type="submit" class="btn btn-primary">{{ __('messages.submit') }}</button>
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
@endif

{{-- Provider rates customer modal (outside extraCharges block so it's always in DOM when provider can rate) --}}
@if(($showRateCustomerButton ?? false) && auth()->user()->user_type === 'provider' && (int)auth()->id() === (int)($bid->provider_id ?? 0))
<div class="modal fade" id="postBidRateCustomerModal" tabindex="-1" aria-labelledby="postBidRateCustomerModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="postBidRateCustomerModalLabel">{{ __('messages.pjr_modal_rate_title', ['name' => $bid->customer->display_name ?? $bid->customer->username ?? __('messages.pjr_fallback_customer')]) }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('messages.close') }}"></button>
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
                <label class="form-label">{{ __('messages.pjr_comments_optional') }}</label>
                <textarea id="postBidRateCustomerReview" class="form-control" rows="3" placeholder="{{ __('messages.pjr_placeholder_share_experience') }}"></textarea>
            </div>
            <div class="text-end">
                <button type="submit" class="btn btn-primary">{{ __('messages.submit') }}</button>
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
                        {{ __('messages.pjr_review_section_customer_to_employer') }}
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>{{ __('messages.pjr_th_customer_rater') }}</th>
                                        <th class="text-center">{{ __('messages.pjr_th_rating') }}</th>
                                        <th>{{ __('messages.pjr_th_review') }}</th>
                                        <th class="text-end">{{ __('messages.pjr_th_date') }}</th>
                                        @auth
                                            <th class="text-end">{{ __('messages.action') }}</th>
                                        @endauth
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bid->ratings as $r)
                                        <tr>
                                            <td>{{ optional($r->customer)->display_name ?? ('#'.$r->customer_id) }}</td>
                                            <td class="text-center">
                                                @php $stars = max(1, min(5, (int)($r->rating ?? 0))); @endphp
                                                <span class="text-warning">{!! str_repeat('★', $stars) !!}</span>
                                                <span class="text-muted">{!! str_repeat('☆', 5 - $stars) !!}</span>
                                            </td>
                                            <td>{{ $r->review ?: '-' }}</td>
                                            <td class="text-end">{{ optional($r->created_at)->format('Y-m-d') ?? '-' }}</td>
                                            @auth
                                                <td class="text-end">
                                                    @if((int) auth()->id() !== (int) ($r->customer_id ?? 0))
                                                        <button type="button" class="btn btn-outline-danger btn-sm"
                                                            onclick="if(window.triggerUgcReportReview) window.triggerUgcReportReview({{ (int) $r->id }}, this, 'post_job_bid_rating');">
                                                            <i class="fas fa-flag me-1"></i>{{ __('messages.ugc_report') }}
                                                        </button>
                                                    @endif
                                                </td>
                                            @endauth
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

    @if($bid->customerRatings && $bid->customerRatings->count() > 0)
    <div class="container py-3">
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white fw-bold">
                        {{ __('messages.pjr_review_section_employer_to_customer') }}
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>{{ __('messages.pjr_th_employer_rater') }}</th>
                                        <th class="text-center">{{ __('messages.pjr_th_rating') }}</th>
                                        <th>{{ __('messages.pjr_th_review') }}</th>
                                        <th class="text-end">{{ __('messages.pjr_th_date') }}</th>
                                        @auth
                                            <th class="text-end">{{ __('messages.action') }}</th>
                                        @endauth
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bid->customerRatings as $r)
                                        <tr>
                                            <td>{{ optional($r->provider)->display_name ?? ('#'.$r->provider_id) }}</td>
                                            <td class="text-center">
                                                @php $stars = max(1, min(5, (int)($r->rating ?? 0))); @endphp
                                                <span class="text-warning">{!! str_repeat('★', $stars) !!}</span>
                                                <span class="text-muted">{!! str_repeat('☆', 5 - $stars) !!}</span>
                                            </td>
                                            <td>{{ $r->review ?: '-' }}</td>
                                            <td class="text-end">{{ optional($r->created_at)->format('Y-m-d') ?? '-' }}</td>
                                            @auth
                                                <td class="text-end">
                                                    @if((int) auth()->id() !== (int) ($r->provider_id ?? 0))
                                                        <button type="button" class="btn btn-outline-danger btn-sm"
                                                            onclick="if(window.triggerUgcReportReview) window.triggerUgcReportReview({{ (int) $r->id }}, this, 'post_job_bid_customer_rating');">
                                                            <i class="fas fa-flag me-1"></i>{{ __('messages.ugc_report') }}
                                                        </button>
                                                    @endif
                                                </td>
                                            @endauth
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
        /* Info cards (Title, etc.) have white background — keep text dark for readability */
        .card.border-primary .card-body,
        .card.border-primary .card-body * {
            color: #000 !important;
        }
        .card.border-primary .card-body h6,
        .card.border-primary .card-body p {
            color: #000 !important;
        }
        .card.border-primary .card-body i.text-primary {
            color: var(--bs-primary) !important;
            -webkit-text-fill-color: var(--bs-primary) !important;
            background: transparent !important;
            -webkit-background-clip: unset !important;
            background-clip: unset !important;
        }
        
        /* Red-Blue Gradient for Card Headers */
        .card-header.bg-primary {
            background: #3333ff !important;
            color: #fff !important;
            border: none !important;
        }
        
        /* Red-Blue Gradient for Table Headers */
        .table thead th,
        .table thead tr th {
            background: #3333ff !important;
            color: #fff !important;
            border-color: transparent !important;
        }
        
        .extra-charges-card .card-header {
            background: #3333ff !important;
            border-bottom: 1px solid rgba(255,255,255,0.25);
        }
        .extra-charges-table thead .extra-charges-heading th {
            background: #3333ff !important;
            color: #fff !important;
            border-bottom: none !important;
        }
        .extra-charges-table thead tr {
            background: #3333ff !important;
            color: #ffffff !important;
        }
        .extra-charges-table thead th {
            background: #3333ff !important;
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
        @php
            $pjrJsLang = [
                'update_payment_split' => __('messages.pjr_update_payment_split'),
                'advance_percentage' => __('messages.pjr_advance_percentage'),
                'remaining_percentage' => __('messages.pjr_remaining_percentage'),
                'update' => __('messages.pjr_update'),
                'updated' => __('messages.pjr_updated'),
                'payment_split_updated' => __('messages.pjr_payment_split_updated'),
                'error' => __('messages.pjr_error'),
                'unable_to_update' => __('messages.pjr_unable_to_update'),
                'unable_to_save' => __('messages.pjr_unable_to_save'),
                'something_went_wrong' => __('messages.pjr_something_went_wrong_short'),
                'advance_20_99' => __('messages.pjr_advance_20_99'),
                'confirm' => __('messages.pjr_js_confirm'),
                'update_status' => __('messages.pjr_js_update_status'),
                'yes_update' => __('messages.pjr_js_yes_update'),
                'updated_title' => __('messages.pjr_js_updated'),
                'status_updated' => __('messages.pjr_js_status_updated'),
                'are_you_sure' => __('messages.pjr_js_are_you_sure'),
                'accept_bid_text' => __('messages.pjr_js_accept_bid_text'),
                'yes_accept' => __('messages.pjr_js_yes_accept'),
                'accepted' => __('messages.pjr_js_accepted'),
                'pay_remaining_title' => __('messages.pjr_js_pay_remaining_title'),
                'pay_advance_title' => __('messages.pjr_js_pay_advance_title'),
                'amount_label' => __('messages.pjr_js_amount_label'),
                'choose_payment' => __('messages.pjr_js_choose_payment'),
                'wallet' => __('messages.pjr_js_wallet'),
                'success' => __('messages.pjr_js_success'),
                'js_error' => __('messages.pjr_js_error'),
                'something_wrong_exclaim' => __('messages.pjr_js_something_wrong_exclaim'),
                'stripe_init_failed' => __('messages.pjr_js_stripe_init_failed'),
                'bank_transfer' => __('messages.pjr_js_bank_transfer'),
                'proceed' => __('messages.pjr_js_proceed'),
                'recorded' => __('messages.pjr_js_recorded'),
                'transfer_recorded' => __('messages.pjr_js_transfer_recorded'),
                'unable_record_transfer' => __('messages.pjr_js_unable_record_transfer'),
                'fetch_bank_failed' => __('messages.pjr_js_fetch_bank_failed'),
                'paypal_init_failed' => __('messages.pjr_js_paypal_init_failed'),
                'put_on_hold' => __('messages.pjr_js_put_on_hold'),
                'hold_reason_label' => __('messages.pjr_js_hold_reason_label'),
                'hold_reason_placeholder' => __('messages.pjr_js_hold_reason_placeholder'),
                'hold_reason_required' => __('messages.pjr_js_hold_reason_required'),
                'hold_reason_too_long' => __('messages.pjr_js_hold_reason_too_long'),
                'on_hold' => __('messages.pjr_js_on_hold'),
                'submit' => __('messages.submit'),
                'add_extra_charges' => __('messages.pjr_js_add_extra_charges'),
                'stripe' => __('messages.pjr_js_stripe'),
                'paypal' => __('messages.pjr_js_paypal'),
                'bank_transfer_btn' => __('messages.pjr_js_bank_transfer_btn'),
                'ec_title' => __('messages.pjr_ec_column_title'),
                'ec_amount' => __('messages.amount'),
                'ec_qty' => __('messages.pjr_ec_wizard_qty_short'),
                'ec_placeholder_material' => __('messages.pjr_ec_wizard_placeholder_material'),
                'ec_placeholder_extra_work' => __('messages.pjr_ec_wizard_placeholder_extra_work'),
                'ec_add_more' => __('messages.pjr_ec_wizard_add_more'),
                'ec_footer_note' => __('messages.pjr_ec_wizard_footer_note'),
                'ec_remove_title' => __('messages.pjr_ec_wizard_remove_title'),
                'save' => __('messages.pjr_js_save'),
                'saved_title' => __('messages.pjr_js_saved'),
                'extra_charges_saved_msg' => __('messages.pjr_js_extra_charges_saved_msg'),
                'ec_val_row_title' => __('messages.pjr_ec_val_row_title'),
                'ec_val_row_amount' => __('messages.pjr_ec_val_row_amount'),
                'ec_val_qty_min' => __('messages.pjr_ec_val_qty_min'),
                'ec_val_one_row' => __('messages.pjr_ec_val_one_row'),
                'ec_total_label' => __('messages.pjr_ec_total_label'),
                'select_star_rating' => __('messages.pjr_js_select_star_rating'),
                'thank_you_rating' => __('messages.pjr_js_thank_you_rating'),
                'rating_submitted' => __('messages.pjr_js_rating_submitted'),
                'rating_failed' => __('messages.pjr_js_rating_failed'),
                'session_expired_rating' => __('messages.pjr_js_session_expired_rating'),
                'sign_in_again_rating' => __('messages.pjr_js_sign_in_again_rating'),
                'bank_info_heading' => __('messages.pjr_bank_info_heading'),
                'bank_for_transfers' => __('messages.pjr_bank_for_transfers'),
                'bank_recipient' => __('messages.pjr_bank_recipient'),
                'bank_iban' => __('messages.pjr_bank_iban'),
                'bank_bic' => __('messages.pjr_bank_bic'),
                'bank_name_address' => __('messages.pjr_bank_name_address'),
                'bank_bic_sender' => __('messages.pjr_bank_bic_sender'),
                'bank_instructions' => __('messages.pjr_bank_instructions'),
                'bank_send_proof' => __('messages.pjr_bank_send_proof'),
            ];
        @endphp
        var pjrJsLang = @json($pjrJsLang);
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
                        title: pjrJsLang.are_you_sure,
                        text: pjrJsLang.accept_bid_text,
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#28a745",
                        cancelButtonColor: "#d33",
                        confirmButtonText: pjrJsLang.yes_accept
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
                                Swal.fire(response.status ? pjrJsLang.accepted : pjrJsLang.js_error,
                                        response.message, response.status ? "success" :
                                        "error")
                                    .then(() => location.reload());
                            }).catch(() => Swal.fire(pjrJsLang.js_error, pjrJsLang.something_wrong_exclaim,
                                "error"));
                    });
                });
            });

            document.querySelectorAll('.updateStatusBtn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const bidId = this.dataset.id;
                    const nextStatus = this.dataset.status;
                    Swal.fire({
                        title: pjrJsLang.confirm,
                        text: (pjrJsLang.update_status || '').replace(':status', nextStatus.replace(/_/g, ' ')),
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: pjrJsLang.yes_update,
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
                                Swal.fire(response.status ? pjrJsLang.updated_title : pjrJsLang.js_error,
                                        response.message || pjrJsLang.status_updated, response
                                        .status ? 'success' : 'error')
                                    .then(() => location.reload());
                            }).catch(() => Swal.fire(pjrJsLang.error, pjrJsLang.something_went_wrong,
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
                        title: isRemaining ? pjrJsLang.pay_remaining_title : pjrJsLang.pay_advance_title,
                        html: `
                            <div class="text-start">
                                <p class="mb-2">${pjrJsLang.amount_label} <strong>${formatCurrencyJS(formattedAmount)}</strong></p>
                                <label class="form-label fw-bold">${pjrJsLang.choose_payment}</label>
                                <div class="d-grid gap-2">
                                    <button class="btn btn-outline-primary" id="walletPayBtn"><i class="fas fa-wallet me-1"></i> ${pjrJsLang.wallet}</button>
                                    <button class="btn btn-outline-dark" id="stripePayBtn"><i class="fab fa-cc-stripe me-1"></i> ${pjrJsLang.stripe}</button>
                                    <button class="btn btn-outline-primary" id="paypalPayBtn"><i class="fab fa-paypal me-1"></i> ${pjrJsLang.paypal}</button>
                                     <button class="btn btn-outline-secondary" id="bankPayBtn"><i class="la la-university me-1"></i> ${pjrJsLang.bank_transfer_btn}</button>
                                </div>
                            </div>
                        `,
                        showConfirmButton: false,
                        showCancelButton: true,
                    }).then(() => {});

                    let isProcessing = false;

                    setTimeout(() => {
                        const walletBtn = document.getElementById('walletPayBtn');
                        const stripeBtn = document.getElementById('stripePayBtn');
                        const paypalBtn = document.getElementById('paypalPayBtn');
                        const bankBtn   = document.getElementById('bankPayBtn');

                        function setProcessing(flag) {
                            isProcessing = flag;
                            [walletBtn, stripeBtn, paypalBtn, bankBtn].forEach(b => {
                                if (b) b.disabled = flag;
                            });
                        }

                        if (walletBtn) {
                            walletBtn.addEventListener('click', () => {
                                if (isProcessing) return;
                                setProcessing(true);
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
                                                type: isRemaining ? 'remaining' : 'advance'
                                            })
                                        }).then(res => res.json())
                                    .then(response => {
                                        setProcessing(false);
                                        Swal.fire(response.status ? pjrJsLang.success : pjrJsLang.js_error,
                                                response.message,
                                                response.status ? 'success' : 'error')
                                            .then(() => location.reload());
                                    }).catch(() => {
                                        setProcessing(false);
                                        Swal.fire(pjrJsLang.js_error, pjrJsLang.something_wrong_exclaim, 'error');
                                    });
                            });
                        }

                        if (stripeBtn) {
                            stripeBtn.addEventListener('click', () => {
                                if (isProcessing) return;
                                setProcessing(true);
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
                                                type: isRemaining ? 'remaining' : 'advance',
                                                use_checkout: true
                                            })
                                        }).then(res => res.json())
                                    .then(session => {
                                        if (session && session.status && session.url) {
                                            window.location.href = session.url;
                                        } else {
                                            setProcessing(false);
                                            Swal.fire(pjrJsLang.error, session.message || pjrJsLang.stripe_init_failed, 'error');
                                        }
                                    }).catch(() => {
                                        setProcessing(false);
                                        Swal.fire(pjrJsLang.js_error, pjrJsLang.something_wrong_exclaim, 'error');
                                    });
                            });
                        }

                        if (bankBtn) {
                            bankBtn.addEventListener('click', () => {
                                if (isProcessing) return;
                                setProcessing(true);
                                // Fetch bank details from the central API
                                const lang = (document.documentElement.lang || 'en').split('-')[0];
                                const bankApiUrl = (document.querySelector('meta[name="baseUrl"]')?.getAttribute('content') || '') + '/api/bank-transfer-settings?language=' + lang;
                                fetch(bankApiUrl)
                                    .then(res => res.json())
                                    .then(json => {
                                        const bank = Array.isArray(json.data) ? (json.data[0] || {}) : (json.data || {});
                                        const bankName    = bank.bank_name    || '';
                                        const bankAddress = bank.bank_address || '';
                                        const infoHtml = `
<div class="text-start">
  <h6 class="mb-2">${pjrJsLang.bank_info_heading}</h6>
  <div class="mb-2"><strong>${pjrJsLang.amount_label}</strong> ${formatCurrencyJS(formattedAmount)}</div>
  <div class="mb-2"><strong>${pjrJsLang.bank_for_transfers}</strong></div>
  <div><strong>${pjrJsLang.bank_recipient}</strong> ${bank.recipient || ''}</div>
  <div><strong>${pjrJsLang.bank_iban}</strong> ${bank.iban || ''}</div>
  <div><strong>${pjrJsLang.bank_bic}</strong> ${bank.bic || ''}</div>
  <div class="mt-2"><strong>{{ __('messages.bank_transfer_bank_name') }}:</strong> ${bankName}</div>
  <div><strong>{{ __('messages.bank_transfer_bank_address') }}:</strong> <span style="white-space:pre-line">${bankAddress}</span></div>
  <h6 class="mt-3">${pjrJsLang.bank_instructions}</h6>
  <div class="small mt-1">
    ${pjrJsLang.bank_send_proof}
    <a href="mailto:${bank.email || ''}">${bank.email || ''}</a>
  </div>
</div>`;
                                        setProcessing(false);
                                        Swal.fire({
                                            title: pjrJsLang.bank_transfer,
                                            html: infoHtml,
                                            showCancelButton: true,
                                            confirmButtonText: pjrJsLang.proceed,
                                        }).then(result => {
                                            if (!result.isConfirmed) return;
                                            setProcessing(true);
                                            fetch(`{{ route('postjob.bank.transfer', ':id') }}`
                                                    .replace(':id', postId), {
                                                        method: 'POST',
                                                        headers: {
                                                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                            'Content-Type': 'application/json'
                                                        },
                                                        body: JSON.stringify({
                                                            amount: amount,
                                                            type: isRemaining ? 'remaining' : 'advance'
                                                        })
                                                    }).then(res => res.json())
                                                .then(response => {
                                                    setProcessing(false);
                                                    Swal.fire(
                                                            response.status ? pjrJsLang.recorded : pjrJsLang.js_error,
                                                            response.message || (response.status ? pjrJsLang.transfer_recorded : pjrJsLang.unable_record_transfer),
                                                            response.status ? 'success' : 'error')
                                                        .then(() => location.reload());
                                                }).catch(() => {
                                                    setProcessing(false);
                                                    Swal.fire(pjrJsLang.js_error, pjrJsLang.something_wrong_exclaim, 'error');
                                                });
                                        });
                                    }).catch(() => {
                                        setProcessing(false);
                                        Swal.fire(pjrJsLang.js_error, pjrJsLang.fetch_bank_failed, 'error');
                                    });
                            });
                        }

                        if (paypalBtn) {
                            paypalBtn.addEventListener('click', () => {
                                if (isProcessing) return;
                                setProcessing(true);
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
                                                type: isRemaining ? 'remaining' : 'advance'
                                            })
                                        }).then(res => res.json())
                                    .then(data => {
                                        if (data && data.url) {
                                            window.location.href = data.url;
                                        } else {
                                            setProcessing(false);
                                            Swal.fire(pjrJsLang.js_error, data.error || pjrJsLang.paypal_init_failed, 'error');
                                        }
                                    }).catch(() => {
                                        setProcessing(false);
                                        Swal.fire(pjrJsLang.js_error, pjrJsLang.something_wrong_exclaim, 'error');
                                    });
                            });
                        }
                    }, 50);
                });
            });

            document.querySelectorAll('.holdBidBtn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const bidId = this.dataset.id;
                    Swal.fire({
                        title: pjrJsLang.put_on_hold,
                        input: 'textarea',
                        inputLabel: pjrJsLang.hold_reason_label,
                        inputPlaceholder: pjrJsLang.hold_reason_placeholder,
                        inputAttributes: {
                            'aria-label': pjrJsLang.hold_reason_label
                        },
                        showCancelButton: true,
                        confirmButtonText: pjrJsLang.submit,
                        preConfirm: value => {
                            if (!value || value.trim().length === 0) Swal
                                .showValidationMessage(pjrJsLang.hold_reason_required);
                            else if (value.length > 500) Swal.showValidationMessage(
                                pjrJsLang.hold_reason_too_long);
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
                            .then(response => Swal.fire(response.status ? pjrJsLang.on_hold :
                                    pjrJsLang.js_error, response.message, response.status ? 'success' :
                                    'error')
                                .then(() => location.reload()))
                            .catch(() => Swal.fire(pjrJsLang.error, pjrJsLang.something_went_wrong,
                                'error'));
                    });
                });
            });

            $(document).on('click', '.extraChargesBtn', function() {
                const bidId = $(this).data('id');
                Swal.fire({
                    title: pjrJsLang.add_extra_charges,
                    width: 700,
                    html: `
            <div class="text-start">
                <div id="ec_rows">
                    <div class="row g-2 ec_row align-items-end mb-2">
                        <div class="col-6">
                            <label class="form-label fw-bold">${pjrJsLang.ec_title}</label>
                            <input type="text" class="form-control ec_title" placeholder="${pjrJsLang.ec_placeholder_material}" />
                        </div>
                        <div class="col-3">
                            <label class="form-label fw-bold">${pjrJsLang.ec_amount}</label>
                            <input type="number" class="form-control ec_amount" step="0.01" min="0.01" placeholder="20" />
                        </div>
                        <div class="col-2">
                            <label class="form-label fw-bold">${pjrJsLang.ec_qty}</label>
                            <input type="number" class="form-control ec_qty" step="0.5" min="0.5" value="1" />
                        </div>
                        <div class="col-1 text-end">
                            <button type="button" class="btn btn-outline-danger btn-sm ec_remove" title="${pjrJsLang.ec_remove_title}"><i class="la la-times"></i></button>
                        </div>
                    </div>
                </div>
                <button type="button" id="ec_add_row" class="btn btn-outline-primary btn-sm"><i class="la la-plus"></i> ${pjrJsLang.ec_add_more}</button>
                <div class="d-flex justify-content-end align-items-center mt-3 pt-2 border-top">
                    <span class="fw-bold me-2">${pjrJsLang.ec_total_label}:</span>
                    <span id="ec_total_display" class="fw-bold">0.00</span>
                </div>
                <div class="mt-2 small text-muted">${pjrJsLang.ec_footer_note}</div>
            </div>
        `,
                    didOpen: () => {
                        const container = document.getElementById('ec_rows');
                        const addBtn = document.getElementById('ec_add_row');
                        const totalDisplay = document.getElementById('ec_total_display');

                        const recalcTotal = () => {
                            let total = 0;
                            container.querySelectorAll('.ec_row').forEach((row) => {
                                const amount = parseFloat(row.querySelector('.ec_amount').value) || 0;
                                const qty = parseFloat(row.querySelector('.ec_qty').value) || 0;
                                total += amount * qty;
                            });
                            totalDisplay.textContent = total.toFixed(2);
                        };

                        addBtn.addEventListener('click', () => {
                            const row = document.createElement('div');
                            row.className = 'row g-2 ec_row align-items-end mb-2';
                            row.innerHTML = `
                                <div class="col-6">
                                    <input type="text" class="form-control ec_title" placeholder="${pjrJsLang.ec_placeholder_extra_work}" />
                                </div>
                                <div class="col-3">
                                    <input type="number" class="form-control ec_amount" step="0.01" min="0.01" placeholder="10" />
                                </div>
                                <div class="col-2">
                                    <input type="number" class="form-control ec_qty" step="0.5" min="0.5" value="1" />
                                </div>
                                <div class="col-1 text-end">
                                    <button type="button" class="btn btn-outline-danger btn-sm ec_remove" title="${pjrJsLang.ec_remove_title}"><i class="la la-times"></i></button>
                                </div>`;
                            container.appendChild(row);
                            recalcTotal();
                        });
                        container.addEventListener('click', (e) => {
                            if (e.target.closest('.ec_remove')) {
                                const rows = container.querySelectorAll('.ec_row');
                                if (rows.length > 1) e.target.closest('.ec_row').remove();
                                recalcTotal();
                            }
                        });
                        container.addEventListener('input', (e) => {
                            if (e.target.classList.contains('ec_amount') || e.target.classList.contains('ec_qty')) {
                                recalcTotal();
                            }
                        });
                        recalcTotal();
                    },
                    focusConfirm: false,
                    showCancelButton: true,
                    confirmButtonText: pjrJsLang.save,
                    preConfirm: () => {
                        const rows = Array.from(document.querySelectorAll('#ec_rows .ec_row'));
                        const items = [];
                        for (const r of rows) {
                            const title = r.querySelector('.ec_title').value.trim();
                            const amount = parseFloat(r.querySelector('.ec_amount').value);
                            const qty = parseFloat(r.querySelector('.ec_qty').value || '1');
                            if (!title) {
                                Swal.showValidationMessage(pjrJsLang.ec_val_row_title);
                                return false;
                            }
                            if (!amount || amount <= 0) {
                                Swal.showValidationMessage(pjrJsLang.ec_val_row_amount);
                                return false;
                            }
                            if (!qty || qty <= 0) {
                                Swal.showValidationMessage(pjrJsLang.ec_val_qty_min);
                                return false;
                            }
                            items.push({ title, amount, quantity: qty });
                        }
                        if (items.length === 0) {
                            Swal.showValidationMessage(pjrJsLang.ec_val_one_row);
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
                                Swal.fire(pjrJsLang.saved_title, response.message || pjrJsLang.extra_charges_saved_msg, 'success')
                                    .then(() => { window.location.reload(); });
                            } else {
                                Swal.fire(pjrJsLang.error, (response && response.message) ? response.message : pjrJsLang.unable_to_save, 'error');
                            }
                        },
                        error: function(xhr) {
                            Swal.fire(pjrJsLang.error, (xhr && xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : pjrJsLang.something_went_wrong, 'error');
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
                return Swal.fire(pjrJsLang.js_error, pjrJsLang.select_star_rating, 'warning');
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
                xhrFields: { withCredentials: true },
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                success: function(){
                    Swal.fire(pjrJsLang.thank_you_rating, pjrJsLang.rating_submitted, 'success');
                    $('#postBidRatingModal').modal('hide');
                    window.location.reload();
                },
                error: function(xhr){
                    var msg = pjrJsLang.rating_failed;
                    if (xhr.status === 419) msg = pjrJsLang.session_expired_rating;
                    else if (xhr.status === 401) msg = pjrJsLang.sign_in_again_rating;
                    else if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    else if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) msg = Object.values(xhr.responseJSON.errors).flat().join(' ');
                    Swal.fire(pjrJsLang.error, msg, 'error');
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
                return Swal.fire(pjrJsLang.js_error, pjrJsLang.select_star_rating, 'warning');
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
                xhrFields: { withCredentials: true },
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                success: function(){
                    Swal.fire(pjrJsLang.thank_you_rating, pjrJsLang.rating_submitted, 'success');
                    $('#postBidRateCustomerModal').modal('hide');
                    window.location.reload();
                },
                error: function(xhr){
                    var msg = pjrJsLang.rating_failed;
                    if (xhr.status === 419) msg = pjrJsLang.session_expired_rating;
                    else if (xhr.status === 401) msg = pjrJsLang.sign_in_again_rating;
                    else if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    else if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) msg = Object.values(xhr.responseJSON.errors).flat().join(' ');
                    Swal.fire(pjrJsLang.error, msg, 'error');
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
                        title: pjrJsLang.update_payment_split,
                        html: `
                    <div class="mb-3 text-start">
                        <label class="form-label fw-bold">${pjrJsLang.advance_percentage}</label>
                        <input type="number" id="advanceInput" class="form-control" value="${currentAdvance}" min="20" max="99" step="1" placeholder="20-99" />
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
                                Swal.showValidationMessage(pjrJsLang.advance_20_99);
                                return false;
                            }
                            const advance = parseInt(raw, 10);
                            if (isNaN(advance) || advance < 20 || advance > 99) {
                                Swal.showValidationMessage(pjrJsLang.advance_20_99);
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
                                        Swal.fire(pjrJsLang.updated, response.message ||
                                                pjrJsLang.payment_split_updated, "success")
                                            .then(() => location.reload());
                                    } else {
                                        Swal.fire(pjrJsLang.error, response.message ||
                                            pjrJsLang.unable_to_update, "error");
                                    }
                                }).catch(() => Swal.fire(pjrJsLang.error, pjrJsLang.something_went_wrong,
                                    "error"));
                        }
                    });
                });
            });
        });
    </script>
    @auth
        @include('partials.ugc-service-cards-script')
    @endauth
</x-master-layout>
