@php
    $sitesetup = App\Models\Setting::where('type', 'site-setup')->where('key', 'site-setup')->first();
    $datetime = $sitesetup ? json_decode($sitesetup->value) : null;
@endphp
{{ html()->hidden('id', $bookingdata->id ?? null) }}
<table class="table-sm title-color align-right w-100" style="display: none;">

    <tbody>
        <!-- Unit Price --> 
        <tr>
            <td>{{ __('Price (Unit Price)') }}</td>
            <td class="bk-value">
                {{ getPriceFormat($bookingdata->amount) }}
            </td>
        </tr>

        <!-- Quantity -->
        <tr>
            <td>{{ __('Quantity (Nbr of Packages, Hours, Days)') }}</td>
            <td class="bk-value">
                {{ $bookingdata->quantity }}
            </td>
        </tr>

        <!-- Total Amount (Price x Quantity) -->
        <tr>
            <td>{{ __('Total Amount') }}</td>
            <td class="bk-value">
                {{ getPriceFormat($bookingdata->amount * $bookingdata->quantity) }}
            </td>
        </tr>

        <!-- Discount -->
        @if ($bookingdata->discount > 0)
            <tr>
                <td>{{ __('Discount') }} ({{ $bookingdata->discount }}% off)</td>
                <td class="bk-value text-success">
                    -{{ getPriceFormat($bookingdata->final_discount_amount) }}
                </td>
            </tr>
        @endif

        <!-- Coupon -->
        @if ($bookingdata->couponAdded)
            <tr>
                <td>{{ __('Coupon') }} ({{ $bookingdata->couponAdded->code }})</td>
                <td class="bk-value text-success">
                    -{{ getPriceFormat($bookingdata->final_coupon_discount_amount) }}
                </td>
            </tr>
        @endif

        <!-- Sub Total -->
        @php
            $subTotal = $bookingdata->amount * $bookingdata->quantity;
            if ($bookingdata->discount > 0) {
                $subTotal -= $bookingdata->final_discount_amount;
            }
            if ($bookingdata->couponAdded) {
                $subTotal -= $bookingdata->final_coupon_discount_amount;
            }
        @endphp
        <tr class="grand-sub-total">
            <td>{{ __('Sub Total') }}</td>
            <td class="bk-value">{{ getPriceFormat($subTotal) }}</td>
        </tr>

        <!-- Extra Charges -->
        <tr>
            <td>{{ __('Extra Charges') }}</td>
            <td class="bk-value">
                {{ getPriceFormat($bookingdata->extra_charges) }}
            </td>
        </tr>

        <!-- Total (Sub Total + Extra Charges) -->
        @php
            $totalWithExtras = $subTotal + $bookingdata->extra_charges;
        @endphp
        <tr>
            <td>{{ __('Total') }}</td>
            <td class="bk-value">{{ getPriceFormat($totalWithExtras) }}</td>
        </tr>

        <!-- Taxes -->
        @php
            // Get the tax_country_id from the service
            $serviceTaxId = $bookingdata->service->tax_country_id ?? null;

            // Initialize tax rate
            $taxRate = 0;

            // Look up tax rate from the taxes table using the service's tax_country_id
            if ($serviceTaxId) {
                $tax = \App\Models\Tax::find($serviceTaxId);
                $taxRate = $tax->value ?? 0;
            }
            //dd($taxRate);
            // Calculate tax amount
            $taxAmount = ($totalWithExtras * $taxRate) / 100;
        @endphp
        <tr>
            <td>{{ __('Tax') }} ({{ $taxRate }}%)</td>
            <td class="bk-value text-danger">{{ getPriceFormat($taxAmount) }}</td>
        </tr>

        <!-- Grand Total (Total + Taxes) -->
        @php
            $grandTotal = $totalWithExtras + $taxAmount;
        @endphp
        <tr>
            <td>{{ __('Grand Total') }}</td>
            <td class="bk-value">{{ getPriceFormat($grandTotal) }}</td>
        </tr>

        <!-- Advance Payment -->
        <tr>
            <td>{{ __('Advance Payment') }}</td>
            <td class="bk-value">
                {{ getPriceFormat($bookingdata->advance_paid_amount) }}
            </td>
        </tr>

        <!-- Remaining Amount (Grand Total - Advance Payment) -->
        <tr class="grand-total">
            <td>{{ __('Remaining Amount') }}</td>
            <td class="bk-value">
                {{ getPriceFormat($grandTotal - $bookingdata->advance_paid_amount) }}
            </td>
        </tr>
    </tbody>
</table>
<style>
    /* Red-Blue Gradient for Booking Info Tables */
    /* Extra Charges table header */
    .booking-info-container .card-header.bg-primary {
        background: linear-gradient(135deg, #FF0000 0%, #5F60B9 100%) !important;
        border: none !important;
    }
    
    /* Review, Service Proof, and Extra Charges table headers */
    .booking-info-container .table.table-bordered thead th {
        background: linear-gradient(135deg, #FF0000 0%, #5F60B9 100%) !important;
        color: #fff !important;
        border-color: transparent !important;
    }
    
    /* Star Rating Styles */
    .star-rating {
        font-size: 2rem;
        color: #ccc;
        cursor: pointer;
    }
    .star-rating .star.selected,
    .star-rating .star:hover,
    .star-rating .star:hover ~ .star {
        color: #fbc02d;
    }
    
    /* Customer Rating Info Modal Styles */
    .star-rating-large {
        font-size: 2.5rem;
        color: #ccc;
        display: inline-block;
    }
    .star-rating-large .star-filled {
        color: #fbc02d;
    }
    .star-rating-large .star-empty {
        color: #ccc;
    }
    .star-rating-small {
        font-size: 0.9rem;
        color: #ccc;
    }
    .star-rating-small .star-filled-small {
        color: #fbc02d;
    }
    .star-rating-small .star-empty-small {
        color: #ccc;
    }
    
    /* Customer Rating Display Styles */
    .customer-rating-section {
        min-height: 60px;
    }
    .star-rating-display {
        font-size: 1.2rem;
        line-height: 1;
        display: inline-flex;
        gap: 2px;
    }
    .star-filled-display {
        color: #fbc02d;
    }
    .star-half-display {
        color: #fbc02d;
        opacity: 0.5;
    }
    .star-empty-display {
        color: #ddd;
    }
    .rating-value {
        font-size: 1.1rem;
        color: #333;
    }
</style>
<div class="container-fluid booking-info-container">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <!-- Header Section -->
                        <div
                            class="border-bottom pb-1 d-flex justify-content-between align-items-center gap-3 flex-wrap">
                            <div>
                                <h3 class="mb-2 text-primary">{{ __('messages.book_id') }}
                                    {{ '#' . $bookingdata->id ?? '-' }}</h3>
                            </div>
                            <div class="d-flex flex-wrap flex-xxl-nowrap gap-3">

                                @if ($bookingdata->status === 'pending')
                                    @hasanyrole('admin|demo_admin|provider')
                                        <div class="w3-third">
                                            <button class="float-end btn btn-primary update-booking"
                                                data-id="{{ $bookingdata->id }}"
                                                data-handyman-id="{{ $bookingdata->provider_id }}" data-status="accept"
                                                data-confirm-message="You want to accept this booking?">
                                                <i class="las la-play-circle"></i>
                                                {{ __('messages.accept_booking') }}
                                            </button>
                                        </div>
                                        @endhasanyrole
                                    @endif
                                  @if ($bookingdata->status === 'pending')
                                   @hasanyrole('user|provider')
                                        <div class="w3-third">
                                            <button class="float-end btn btn-primary update-booking" id="cancel-booking"
                                                data-id="{{ $bookingdata->id }}"
                                                data-handyman-id="{{ $bookingdata->provider_id }}" data-status="cancelled"
                                                data-confirm-message="You want to cancelled this booking?">
                                                <i class="las la-ban"></i>
                                                {{ __('messages.cancel') }}
                                            </button>
                                        </div>
                                    @endhasanyrole
                                @endif

                                @if ($bookingdata->status === 'accept')
                                    @if (
                                        $bookingdata->handymanAdded->isNotEmpty() &&
                                            $bookingdata->handymanAdded->contains(function ($item) {
                                                return $item->handyman->id === auth()->id();
                                            }))
                                        @hasanyrole(['provider', 'handyman'])
                                            <div class="w3-third">
                                                <button class="float-end btn btn-primary update-booking" id="start-booking"
                                                    data-id="{{ $bookingdata->id }}"
                                                    data-handyman-id="{{ $bookingdata->provider_id }}"
                                                    data-status="on_going"
                                                    data-confirm-message="You want to start this booking?">
                                                    <i class="las la-play-circle"></i>
                                                    {{ __('Start Work') }}
                                                </button>
                                            </div>

                                            {{-- <div class="w3-third">
                                            <button class="float-end btn btn-danger update-booking" id="reject-booking"
                                                    data-id="{{ $bookingdata->id }}"
                                                    data-handyman-id="{{ $bookingdata->provider_id }}"
                                                    data-status="rejected"
                                                    data-confirm-message="You want to reject this booking?">
                                                <i class="las la-times-circle"></i>
                                                {{ __('messages.decline') }}
                                            </button>
                                        </div> --}}
                                        @endhasanyrole

                                        @hasanyrole('user')
                                            <div class="w3-third">
                                                <button class="float-end btn btn-primary update-booking" id="cancel-booking"
                                                    data-id="{{ $bookingdata->id }}"
                                                    data-handyman-id="{{ $bookingdata->provider_id }}"
                                                    data-status="cancelled"
                                                    data-confirm-message="You want to cancelled this booking?">
                                                    <i class="las la-ban"></i>
                                                    {{ __('messages.cancel') }}
                                                </button>
                                            </div>
                                        @endhasanyrole
                                    @elseif($bookingdata->handymanAdded->isEmpty())
                                        @hasanyrole('admin|demo_admin|provider')
                                            @if (
                                                $is_enable_advance_payment == 0 ||
                                                    (isset($bookingdata->payment) && strtolower($bookingdata->payment->payment_status) == 'advanced_paid'))
                                                {{-- <div class="w3-third">
                                                <button class="float-end btn btn-primary" id="assign-provider"
                                                        data-id="{{ $bookingdata->id }}"
                                                        data-handyman-id="{{ $bookingdata->provider_id }}">
                                                    <i class="lab la-telegram-plane"></i>
                                                    {{ __('messages.assign_provider') }}
                                                </button>
                                            </div> --}}

                                                <div class="w3-third">
                                                    <a href="{{ route('booking.assign_form', ['id' => $bookingdata->id]) }}"
                                                        class="float-end btn btn-primary loadRemoteModel">
                                                        <i class="lab la-telegram-plane"></i>
                                                        {{ __('messages.assign_handyman') }}
                                                    </a>
                                                </div>
                                            @else
                                                <div class="w3-third d-flex align-items-end">
                                                    <p><span class="text-info font-size-14" style="font-weight: 700">Waiting
                                                            for
                                                            client advance pay</span>
                                                    </p>
                                                </div>
                                            @endif
                                        @endhasanyrole

                                        @hasanyrole('user')
                                            @if (!isset($bookingdata->payment) && $is_enable_advance_payment == 1)
                                                @php
                                                    // Calculate advance amount using same logic as billing table
                                                    $baseTotal = $bookingdata->amount * $bookingdata->quantity;
                                                    $subTotal = $baseTotal;
                                                    if ($bookingdata->discount > 0) {
                                                        $subTotal -= $bookingdata->final_discount_amount;
                                                    }
                                                    if ($bookingdata->couponAdded) {
                                                        $subTotal -= $bookingdata->final_coupon_discount_amount;
                                                    }
                                                    $addonTotal = $bookingdata->bookingAddonService->sum('price');
                                                    $extraChargeTotal = $bookingdata->bookingExtraCharge->sum(function ($item) {
                                                        return $item->price * $item->qty;
                                                    });
                                                    $totalBeforeTax = $subTotal + $addonTotal + $extraChargeTotal;
                                                    $serviceTaxId = $bookingdata->service->tax_country_id ?? null;
                                                    $taxRate = 0;
                                                    if ($serviceTaxId) {
                                                        $tax = \App\Models\Tax::find($serviceTaxId);
                                                        $taxRate = $tax->value ?? 0;
                                                    }
                                                    $taxAmount = ($totalBeforeTax * $taxRate) / 100;
                                                    $grandTotal = $totalBeforeTax + $taxAmount;
                                                    $advancePaidAmount = $bookingdata->advance_paid_amount;
                                                    if ($advancePaidAmount <= 0 && isset($advanceservice) && $advanceservice > 0) {
                                                        $advancePaidAmount = ($grandTotal * $advanceservice) / 100;
                                                    }
                                                    $advanceAmount = $advancePaidAmount;
                                                @endphp
                                                <div class="w3-third">
                                                    <a class="float-end btn btn-primary d-flex align-items-center gap-2"
                                                        href="{{ route('book.service', ['id' => $bookingdata->service_id, 'booking_id' => $bookingdata->id, 'payment_type' => 'advance_paid']) }}"
                                                        target="_blank" data-id="{{ $bookingdata->id }}"
                                                        style="background: linear-gradient(135deg, #28a745, #20c997); border: none; box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);">
                                                        <i class="las la-credit-card"></i>
                                                        <span>{{ __('messages.advance_pay') }}</span>
                                                        <span class="badge bg-light text-dark px-2 py-1 rounded-pill">
                                                            {{ getPriceFormat($advanceAmount) }}
                                                        </span>
                                                    </a>
                                                </div>
                                            @endif
                                        @endhasanyrole
                                    @endif
                                @endif

                                @if ($bookingdata->status === 'on_going')
                                    @hasanyrole(['provider', 'handyman'])
                                        <div class="w3-third d-flex align-items-end">
                                            <p><span class="text-info font-size-14" style="font-weight: 700">Waiting for
                                                    response by customer</span>
                                            </p>
                                        </div>
                                    @endhasanyrole

                                    @hasanyrole('user')
                                        <div class="w3-third">
                                            <button class="float-end btn btn-primary update-booking"
                                                data-id="{{ $bookingdata->id }}"
                                                data-handyman-id="{{ $bookingdata->provider_id }}"
                                                data-status="in_progress"
                                                data-confirm-message="You want to start this booking?">
                                                <i class="las la-play-circle"></i>
                                                {{ __('Lets Start Working') }}
                                            </button>
                                        </div>
                                    @endhasanyrole
                                @endif

                                @if ($bookingdata->status === 'in_progress')
                                    @hasanyrole(['user', 'handyman'])
                                        <div class="w3-third">
                                            <button class="float-end btn btn-warning hold-booking"
                                                data-id="{{ $bookingdata->id }}"
                                                data-handyman-id="{{ $bookingdata->provider_id }}" data-status="hold"
                                                data-confirm-message="You want to start this booking?">
                                                <i class="las la-pause-circle"></i>
                                                {{ __('messages.hold') }}
                                            </button>
                                        </div>
                                    @endhasanyrole
                                      @hasanyrole(['handyman'])
                                        <div class="w3-third">
                                            <button class="float-end btn btn-primary update-booking"
                                                data-id="{{ $bookingdata->id }}"
                                                data-handyman-id="{{ $bookingdata->provider_id }}"
                                                data-status="done_by_provider"
                                                data-confirm-message="You want to end this booking?">
                                                <i class="las la-check-circle"></i>
                                                {{ __('messages.done') }}
                                            </button>
                                        </div>
                                    @endhasanyrole
                                @endif

                                @if ($bookingdata->status === 'pending_approval')
                                    @hasanyrole(['user'])
                                        <div class="w3-third d-flex align-items-end">
                                            <p><span class="text-info font-size-14" style="font-weight: 700">Waiting for
                                                    handyman to mark booking as completed</span>
                                            </p>
                                        </div>
                                    @endhasanyrole

                                    @hasanyrole(['handyman'])
                                        <div class="w3-third">
                                            <button class="float-end btn btn-success update-booking"
                                                data-id="{{ $bookingdata->id }}"
                                                data-handyman-id="{{ $bookingdata->provider_id }}"
                                                data-status="completed"
                                                data-confirm-message="Are you sure you want to complete this booking?">
                                                <i class="las la--check-circle"></i>
                                                {{ __('messages.completed') }}
                                            </button>
                                        </div>

                                        <button class="float-end btn" id="complete-booking" style="background-color: #ffb366; border-color: #ffb366; color: #000;"
                                            data-id="{{ $bookingdata->id }}"
                                            data-handyman-id="{{ $bookingdata->provider_id }}" data-status="cancelled"
                                            data-confirm-message="Are you sure you want to cancel this booking?">
                                            <i class="las la-file-invoice-dollar"></i>
                                            {{ __('messages.add_extra_charges') }}
                                        </button>
                                    @endhasanyrole
                                @endif

                                @if ($bookingdata->status === 'done_by_provider')
                                    @hasanyrole(['user'])
                                        <div class="w3-third">
                                             <button class="float-end btn btn-success confirm-booking"
                                                data-id="{{ $bookingdata->id }}"
                                                data-handyman-id="{{ $bookingdata->provider_id }}"
                                                data-status="pending_approval"
                                                data-advance="{{ $bookingdata->is_advance_paid ? 1 : 0 }}"
                                                data-confirm-message="You want to Confirm this booking?">
                                                <i class="las la-check-circle"></i>
                                                {{ __('Confirm Job Done') }}
                                            </button>

                                        </div>
                                    @endhasanyrole
                                @endif

















                                @if ($bookingdata->status === 'hold')
                                    @hasanyrole(['user', 'handyman'])
                                        <div class="w3-third d-flex align-items-end">
                                            <p><span class="text-danger font-size-14" style="font-weight: 700">Hold Reason
                                                    :</span> {{ $bookingdata->reason }}
                                            </p>
                                        </div>
                                    @endhasanyrole

                                    @hasanyrole(['user', 'handyman'])
                                        <div class="w3-third">
                                            <button class="float-end btn btn-primary update-booking"
                                                data-id="{{ $bookingdata->id }}"
                                                data-handyman-id="{{ $bookingdata->provider_id }}"
                                                data-status="in_progress"
                                                data-confirm-message="Are you sure you want to resume this booking?">
                                                <i class="las la-play"></i>
                                                {{ __('messages.resume') }}
                                            </button>
                                        </div>
                                    @endhasanyrole
                                @endif

                                @if ($bookingdata->status === 'confirm')
                                    @hasanyrole(['handyman'])
                                        <div class="w3-third">
                                            <button class="float-end btn btn-success update-booking"
                                                data-id="{{ $bookingdata->id }}"
                                                data-handyman-id="{{ $bookingdata->provider_id }}"
                                                data-status="completed"
                                                data-confirm-message="Are you sure you want to complete this booking?">
                                                <i class="las la--check-circle"></i>
                                                {{ __('messages.completed') }}
                                            </button>
                                        </div>

                                        <button class="float-end btn" id="complete-booking" style="background-color: #ffb366; border-color: #ffb366; color: #000;"
                                            data-id="{{ $bookingdata->id }}"
                                            data-handyman-id="{{ $bookingdata->provider_id }}" data-status="cancelled"
                                            data-confirm-message="Are you sure you want to cancel this booking?">
                                            <i class="las la-file-invoice-dollar"></i>
                                            {{ __('messages.add_extra_charges') }}
                                        </button>
                                    @endhasanyrole

                                    @hasanyrole('user')
                                        <div class="w3-third d-flex align-items-end">
                                            <p><span class="text-info font-size-14" style="font-weight: 700">Waiting for
                                                    response </span>
                                            </p>
                                        </div>
                                    @endhasanyrole
                                @endif

                                @if ($bookingdata->status === 'completed' && empty($customer_review))
                                    @hasanyrole('user')
                                        @php
                                            // Calculate remaining amount using same logic as billing table
                                            $baseTotal = $bookingdata->amount * $bookingdata->quantity;
                                            $subTotal = $baseTotal;
                                            if ($bookingdata->discount > 0) {
                                                $subTotal -= $bookingdata->final_discount_amount;
                                            }
                                            if ($bookingdata->couponAdded) {
                                                $subTotal -= $bookingdata->final_coupon_discount_amount;
                                            }
                                            $addonTotal = $bookingdata->bookingAddonService->sum('price');
                                            $extraChargeTotal = $bookingdata->bookingExtraCharge->sum(function ($item) {
                                                return $item->price * $item->qty;
                                            });
                                            $totalBeforeTax = $subTotal + $addonTotal + $extraChargeTotal;
                                            $serviceTaxId = $bookingdata->service->tax_country_id ?? null;
                                            $taxRate = 0;
                                            if ($serviceTaxId) {
                                                $tax = \App\Models\Tax::find($serviceTaxId);
                                                $taxRate = $tax->value ?? 0;
                                            }
                                            $taxAmount = ($totalBeforeTax * $taxRate) / 100;
                                            $grandTotal = $totalBeforeTax + $taxAmount;
                                            $advancePaidAmount = $bookingdata->advance_paid_amount ?? 0;
                                            if ($advancePaidAmount <= 0 && isset($advanceservice) && $advanceservice > 0) {
                                                $advancePaidAmount = ($grandTotal * $advanceservice) / 100;
                                            }
                                            $remainingAmount = $grandTotal - $advancePaidAmount;
                                            
                                            // Check if payment is fully paid
                                            $isPaymentPaid = isset($payment) && $payment->payment_status == 'paid';
                                            $hasRemainingAmount = $remainingAmount > 0;
                                        @endphp
                                        
                                        {{-- Show "Pay Remaining" button when payment is not fully paid and there's remaining amount --}}
                                        @if (isset($payment) && !$isPaymentPaid && $hasRemainingAmount)
                                            <div class="w3-third d-flex align-items-end">
                                                <a class="float-end btn btn-warning d-flex align-items-center gap-2"
                                                    href="{{ route('book.service', ['id' => $bookingdata->service_id, 'booking_id' => $bookingdata->id, 'payment_type' => 'full_payment']) }}"
                                                    target="_blank" data-id="{{ $bookingdata->id }}"
                                                    style="background: linear-gradient(135deg, #fd7e14, #ffc107); border: none; box-shadow: 0 4px 12px rgba(253, 126, 20, 0.3);">
                                                    <i class="las la-credit-card"></i>
                                                    <span>{{ __('Pay Remaining') }}</span>
                                                    <span class="badge bg-light text-dark px-2 py-1 rounded-pill">
                                                        {{ getPriceFormat($remainingAmount) }}
                                                    </span>
                                                </a>
                                            </div>
                                        @endif
                                        
                                        {{-- Show "Rate Now" button only after payment is fully paid --}}
                                        @if ($isPaymentPaid || (!isset($payment) && !$hasRemainingAmount))
                                            <div class="w3-third d-flex align-items-end">
                                                <button class="float-end btn btn-warning" id="rate-now-btn"
                                                    data-id="{{ $bookingdata->id }}">
                                                    <i class="las la-star"></i>
                                                    <!-- Changed to a star icon (Line Awesome) -->
                                                    {{ __('messages.rate_now') }}
                                                </button>
                                            </div>
                                        @endif
                                    @endhasanyrole
                                @endif

                                @if (($bookingdata->status === 'completed' || $bookingdata->status === 'paid') && isset($payment) && $payment->payment_status == 'paid')
                                    @hasanyrole(['handyman', 'provider'])
                                        <div class="w3-third d-flex align-items-end gap-2">
                                            <button class="float-end btn btn-primary" id="service-proof-btn"
                                                data-id="{{ $bookingdata->id }}"
                                                data-service-id="{{ $bookingdata->service_id }}"
                                                data-user-id="{{ $bookingdata->customer_id }}">
                                                <i class="las la-clipboard-list"></i>
                                                {{ __('messages.service_proof') }}
                                            </button>
                                            @if ($auth_user->user_type == 'provider' || $bookingdata->provider_id == $auth_user->id)
                                            <button class="float-end btn btn-warning" id="rate-customer-btn"
                                                data-booking-id="{{ $bookingdata->id }}"
                                                data-customer-id="{{ $bookingdata->customer_id }}"
                                                data-provider-id="{{ $bookingdata->provider_id }}">
                                                <i class="las la-star"></i>
                                                {{ __('landingpage.rate_customer') }}
                                            </button>
                                            @endif
                                        </div>
                                    @endhasanyrole
                                @endif
                                @hasanyrole(['user', 'provider', 'admin'])
                                    @if ($bookingdata->status === 'completed' && isset($payment) && $payment->payment_status == 'paid')
                                        <a href="{{ route('invoice_pdf', $bookingdata->id) }}" class="btn btn-primary"
                                            target="_blank">
                                            <i class="ri-file-text-line"></i>
                                            {{ __('messages.invoice') }}
                                        </a>
                                    @endif
                                @endhasanyrole

                                @php
                                    $currentUser = auth()->user();
                                    $isProvider = $currentUser && $currentUser->id == ($bookingdata->provider_id ?? 0);
                                    $isCustomer = $currentUser && $currentUser->id == ($bookingdata->customer_id ?? 0);
                                    $isHandyman = $currentUser && ($bookingdata->handymanAdded && $bookingdata->handymanAdded->contains('handyman_id', $currentUser->id));
                                @endphp
                                @if ($isProvider || $isCustomer || $isHandyman)
                                    
                                    @php $__chatAllowed = ($bookingdata->status !== 'cancelled') && isset($payment) && in_array(strtolower($payment->payment_status ?? ''), ['advanced_paid','paid'], true); @endphp
                                    @if($__chatAllowed)
                                        @if($isCustomer)
                                            {{-- Customer sees: Chat with Provider and Chat with Handyman --}}
                                            <a href="{{ route('chat.view.user', $bookingdata->provider_id) }}" class="btn btn-outline-primary">
                                                <i class="fas fa-comments"></i> {{ __('Chat with Provider') }}
                                            </a>
                                            @if($bookingdata->handymanAdded && $bookingdata->handymanAdded->count())
                                                @php $firstHandyman = optional($bookingdata->handymanAdded->first())->handyman_id; @endphp
                                                @if($firstHandyman)
                                                    <a href="{{ route('chat.view.user', $firstHandyman) }}" class="btn btn-outline-secondary">
                                                        <i class="fas fa-user-cog"></i> {{ __('Chat with Handyman') }}
                                                    </a>
                                                @endif
                                            @endif
                                        @elseif($isProvider)
                                            {{-- Provider sees: Chat with Customer and Chat with Handyman --}}
                                            <a href="{{ route('chat.view.user', $bookingdata->customer_id) }}" class="btn btn-outline-primary">
                                                <i class="fas fa-comments"></i> {{ __('Chat with Customer') }}
                                            </a>
                                            @if($bookingdata->handymanAdded && $bookingdata->handymanAdded->count())
                                                @php $firstHandyman = optional($bookingdata->handymanAdded->first())->handyman_id; @endphp
                                                @if($firstHandyman)
                                                    <a href="{{ route('chat.view.user', $firstHandyman) }}" class="btn btn-outline-secondary">
                                                        <i class="fas fa-user-cog"></i> {{ __('Chat with Handyman') }}
                                                    </a>
                                                @endif
                                            @endif
                                        @elseif($isHandyman)
                                            {{-- Handyman sees: Chat with Provider and Chat with Customer --}}
                                            <a href="{{ route('chat.view.user', $bookingdata->provider_id) }}" class="btn btn-outline-primary">
                                                <i class="fas fa-comments"></i> {{ __('Chat with Provider') }}
                                            </a>
                                            <a href="{{ route('chat.view.user', $bookingdata->customer_id) }}" class="btn btn-outline-secondary">
                                                <i class="fas fa-user"></i> {{ __('Chat with Customer') }}
                                            </a>
                                        @endif
                                    @endif
                                @endif

                            </div>
                        </div>

                        {{-- Dynamic Next-Step Marquee --}}
                        @php
                            $status = (string) ($bookingdata->status ?? '');
                            $nextActor = null; // 'provider' | 'user' | 'handyman' | null
                            $nextText = null;

                            switch ($status) {
                                case 'pending':
                                    $nextActor = 'provider';
                                    $nextText = 'Waiting for provider to accept the booking';
                                    break;
                                case 'accept':
                                    // Check if payment status is pending_by_admin
                                    if (isset($payment) && strtolower($payment->payment_status) === 'pending_by_admin') {
                                        $nextActor = null;
                                        $nextText = 'Waiting for payment approval by admin';
                                    } elseif ($is_enable_advance_payment == 1 && (!isset($payment) || $payment->payment_status != 'advanced_paid')) {
                                        $nextActor = 'user';
                                        $nextText = 'Waiting for customer to pay advance payment';
                                    } else {
                                        $nextActor = 'provider';
                                        $nextText = 'Waiting for provider to assign handyman';
                                    }
                                    break;
                                case 'on_going':
                                    $nextActor = 'user';
                                    $nextText = 'Waiting for customer to confirm "Let\'s Start Work"';
                                    break;
                                case 'in_progress':
                                    $nextActor = 'handyman';
                                    $nextText = 'Work in progress — waiting for handyman to complete or put on hold';
                                    break;
                                case 'hold':
                                    $nextActor = 'handyman';
                                    $nextText = 'Work is on hold — waiting for handyman to resume';
                                    break;
                                case 'pending_approval':
                                    $nextActor = 'user';
                                    $nextText = 'Waiting for customer to confirm work is done';
                                    break;
                                case 'done_by_provider':
                                    $nextActor = 'user';
                                    $nextText = 'Waiting for customer to confirm work is done';
                                    break;
                                case 'confirm':
                                    $nextActor = 'handyman';
                                    $nextText = 'Waiting for handyman to mark booking as completed';
                                    break;
                                case 'completed':
                                    // Check if payment status is pending_by_admin
                                    if (isset($payment) && strtolower($payment->payment_status) === 'pending_by_admin') {
                                        $nextActor = null;
                                        $nextText = 'Waiting for payment approval by admin';
                                    } elseif (isset($payment) && $payment->payment_status != 'paid') {
                                        $nextActor = 'user';
                                        $nextText = 'Waiting for customer to pay remaining amount';
                                    } else {
                                        $nextActor = null;
                                        $nextText = 'Booking completed successfully! You can download the invoice.';
                                    }
                                    break;
                                case 'cancelled':
                                    $nextActor = null;
                                    $nextText = 'This booking has been cancelled.';
                                    break;
                                default:
                                    $nextActor = null;
                                    $nextText = null;
                            }
                        @endphp

                        @if ($nextText)
                            <div class="w-100 px-3 mb-4">
                                <div class="marquee-banner {{ $nextActor === 'provider' ? 'marquee-provider' : ($nextActor === 'user' ? 'marquee-user' : ($nextActor === 'handyman' ? 'marquee-handyman' : 'marquee-neutral')) }}">
                                    <marquee behavior="scroll" direction="left" scrollamount="6">
                                        <i class="fas fa-info-circle me-2"></i>
                                        {{ $nextText }}
                                    </marquee>
                                </div>
                            </div>
                        @endif

                        <!-- Main Content Row -->
                        <div class="row ">
                            <div class="col-md-4 mb-3">
                                <div class="card h-100 soft-shadow hover-lift">
                                    <div class="card-body">
                                        <p class="opacity-75 fz-12">{{ __('messages.book_placed') }}</p>
                                        <p class="mb-0">
                                            {{ date("$datetime->date_format $datetime->time_format", strtotime($bookingdata->created_at)) ?? '-' }}
                                        </p>
                                    </div>
                                </div>
                            </div>


                            <div class="col-md-4 mb-3">
                                <div class="card h-100 soft-shadow hover-lift">
                                    <div class="card-body">
                                        <p class="opacity-75 fz-12">{{ __('messages.booking_status') }}</p>
                                        <p class="mb-0 text-primary" id="booking_status__span">
                                            {{ str_replace('_', ' ', ucfirst($bookingdata->status)) }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card h-100 soft-shadow hover-lift">
                                    <div class="card-body">
                                        <p class="opacity-75 fz-12">{{ __('messages.location') }}</p>
                                        <p class="mb-0 text-primary">
                                            {{ optional($bookingdata->service->city)->name ?? '-' }},
                                            {{ optional($bookingdata->service->country)->name ?? '-' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            @hasanyrole(['user', 'provider', 'admin'])
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 soft-shadow hover-lift">
                                        <div class="card-body">
                                            <p class="opacity-75 fz-12">{{ __('messages.total_amount') }}</p>
                                            <p class="mb-0 text-primary">
                                                {{ getPriceFormat($bookingdata->total_amount ?? 0) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endhasanyrole
                            <div class="col-md-4 mb-3">
                                <div class="card h-100 soft-shadow hover-lift">
                                    <div class="card-body">
                                        <p class="opacity-75 fz-12">{{ __('messages.payment_method') }}</p>
                                        <p class="mb-0 text-primary">
                                            {{ isset($payment) ? ucfirst($payment->payment_type) : '-' }}</p>
                                    </div>
                                </div>
                            </div>
                            @hasanyrole(['user', 'provider', 'admin'])
                                @if (
                                    (isset($payment) && $payment->payment_type === 'bank_transfer' && $payment->status == 1) ||
                                        (isset($payment) && $payment->payment_type !== 'bank_transfer'))
                                    <div class="col-md-4 mb-3">
                                        <div class="card h-65 border-0 soft-shadow hover-lift"
                                            style="background: linear-gradient(135deg, #f7c59f, #ff9a9e); color: #fff;">
                                            <div class="card-body">
                                                <p class="mb-1 fw-bold text-uppercase" style="opacity: 0.9;">
                                                    {{ __('landingpage.advance_payment') }}
                                                </p>
                                                <p class="mb-0 fs-5 fw-bold" id="service_schedule__span">
                                                    {{ getPriceFormat($bookingdata->advance_paid_amount) }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endhasanyrole

                           <div class="col-md-4 mb-3">  
    <div class="card h-65 soft-shadow hover-lift">
        @php
            // If booking is cancelled, override payment status
            if (isset($bookingdata) && $bookingdata->status === 'cancelled') {
                $paymentStatus = 'cancelled';
            } elseif (isset($payment) && $payment->payment_status) {
                $paymentStatus = $payment->payment_status;
            } else {
                $paymentStatus = null;
            }

            $isPaid = $paymentStatus === 'paid';
            $cardStyle = $isPaid
                ? 'background: linear-gradient(135deg, #43e97b, #38f9d7); color: #fff; border-radius: 10px; padding: 12px;'
                : 'background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; border-radius: 10px; padding: 12px;';

            $statusClass = match ($paymentStatus) {
                'paid' => 'text-white fw-bold',
                'advanced_paid' => 'text-white fw-bold',
                'Advanced Refund' => 'text-white fw-bold',
                'cancelled' => 'text-white fw-bold',
                default => 'text-white fw-bold',
            };
        @endphp

        <div class="card-body" style="{{ $cardStyle }}">
            <p class="fz-12 text-white">
                {{ __('messages.payment_status') }}
            </p>

            @if ($paymentStatus)
                <p class="mb-0 {{ $statusClass }}">
                    {{ str_replace('_', ' ', ucfirst($paymentStatus)) }}
                </p>
            @else
                <p class="mb-0 text-white fw-bold">
                    {{ __('messages.pending') }}
                </p>
            @endif
        </div>
    </div>
</div>

                            
                            
                        @php
                            // Show working location ONLY when payment is approved
                            // Hide if payment status is pending/pending_by_admin (for ANY payment type)
                            $showWorkingLocation = false;
                            
                            if (isset($payment)) {
                                $paymentStatus = strtolower($payment->payment_status ?? '');
                                $paymentType = strtolower($payment->payment_type ?? '');
                                
                                // For bank_transfer: only show if status is paid or advanced_paid
                                if ($paymentType === 'bank_transfer') {
                                    $showWorkingLocation = in_array($paymentStatus, ['paid', 'advanced_paid'], true);
                                } else {
                                    // For other payment types: show if status is paid or advanced_paid (not pending)
                                    $showWorkingLocation = in_array($paymentStatus, ['paid', 'advanced_paid'], true);
                                }
                            }
                            // If no payment exists, don't show location (default is false)
                        @endphp
                        @if($showWorkingLocation)
                        <div class="col-md-4 mb-3">
                                <div class="card h-65 soft-shadow hover-lift">
                                    <div class="card-body">
                                        <p class="opacity-75 fz-12">{{ __('Working Location') }}</p>
                                        <p class="mb-0 text-primary" id="booking_status__span" style="word-wrap: break-word; overflow-wrap: break-word;">
                                            @if(!empty($bookingdata->address))
                                                {{ $bookingdata->address }}
                                            @elseif(isset($bookingdata->city->name) || isset($bookingdata->country->name))
                                                {{ str_replace('_', ' ', ucfirst($bookingdata->city->name ?? '')) }}@if(isset($bookingdata->city->name) && isset($bookingdata->country->name)), @endif{{ str_replace('_', ' ', ucfirst($bookingdata->country->name ?? '')) }}
                                            @else
                                                {{ __('N/A') }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                        </div>
                        @endif






                            <!-- Add Cancellation Reason Card -->
                            @if ($bookingdata->status === 'cancelled')
                                <div class="col-md-4 mb-3">
                                    <div class="card h-65 soft-shadow hover-lift">
                                        <div class="card-body">
                                            <p class="opacity-75 fz-12">{{ __('landingpage.cancel_reason') }}</p>
                                            <p class="mb-0 text-danger">
                                                {{ $bookingdata->reason ?? __('messages.no_reason_provided') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endif

                        </div>
                        
                        <!-- Service Schedule Card - Horizontal Row Layout -->
                        <div class="row mt-3">
                            <div class="col-12 mb-3">
                                <div class="card soft-shadow">
                                    <div class="card-body">
                                        <p class="opacity-75 fz-12 mb-3 fw-semibold">{{ __('messages.service_schedule') }}</p>
                                        @if($bookingdata->slots && count($bookingdata->slots) > 0)
                                            <div class="booking-slots-horizontal">
                                                @foreach ($bookingdata->slots as $index => $slot)
                                                    <div class="slot-item-horizontal">
                                                        <div class="slot-date-horizontal">
                                                            <i class="ri-calendar-2-line me-2"></i>
                                                            <span class="fw-semibold">{{ date("M d, Y", strtotime($slot->date)) ?? '-' }}</span>
                                                        </div>
                                                        <div class="slot-time-horizontal">
                                                            <i class="ri-time-line me-2"></i>
                                                            <span>{{ date('g:i A', strtotime($slot->start_time)) }} - {{ date('g:i A', strtotime($slot->end_time)) }}</span>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="no-slots-simple text-muted">
                                                <i class="ri-calendar-line me-2"></i>
                                                <span>No slots scheduled</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order information section  -->
            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="card soft-shadow hover-lift role-card role-customer">
                        <div class="card-body">
                            <div class="d-flex align-items-start gap-3">
                                <div class="flex-shrink-0">

                                    <img src="{{ getSingleMedia($bookingdata->customer, 'profile_image', null) }}"
                                        alt="Customer Profile" class="rounded-circle"
                                        style="width: 60px; height: 60px; object-fit: cover;">
                                    @if (optional($bookingdata->customer)->profile_image)
                                        <img src="{{ asset('public/images/default.png') }}" alt="Default Profile"
                                            class="rounded-circle"
                                            style="width: 60px; height: 60px; object-fit: cover;">
                                    @endif
                                </div>
                                <div class="flex-grow-1">
                                    <p class="mb-1 text-primary d-flex align-items-center"><i class="ri-user-line role-icon me-1"></i> {{ __('messages.customer') }}</p>
                                    <h5 class="mb-2">{{ optional($bookingdata->customer)->display_name ?? '-' }}
                                    </h5>
                                </div>
                            </div>
                            <ul class="list-unstyled mt-3">
                                <li class="d-flex align-items-center mb-2">
                                    <i class="ri-calendar-line me-2"></i>
                                    <span class="text-body">
                                        {{ optional($bookingdata->customer)->created_at ? optional($bookingdata->customer)->created_at->format('Y-m-d') : '-' }}
                                    </span>
                                </li>

                                <!-- <li class="d-flex align-items-center mb-2">
                                        <i class="ri-mail-line me-2"></i>
                                        <a href="mailto:{{ optional($bookingdata->customer)->email }}" class="text-body">
                                            {{ optional($bookingdata->customer)->email ?? '-' }}
                                </a>
                            </li> -->
                                <li class="d-flex align-items-center">
                                    <i class="ri-map-pin-line me-2"></i>
                                    <span
                                        class="text-wrap">{{ strip_tags(optional($bookingdata->customer)->city->name ?? '') ?? '-' }}-</span>
                                        {{ strip_tags(optional($bookingdata->customer)->country->name ?? '') ?? '-' }}
                                        </span>
                                </li>
                            </ul>
                            
                            <!-- Customer Rating Section (Like Freelancer/Upwork) -->
                            <div class="customer-rating-section mt-3 pt-3 border-top" id="customer-rating-section-{{ $bookingdata->customer_id }}" data-customer-id="{{ $bookingdata->customer_id }}">
                                <div class="text-center">
                                    <div class="spinner-border spinner-border-sm" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Provider Information -->
                <div class="col-md-4">
                    <div class="card soft-shadow hover-lift role-card role-provider">
                        <div class="card-body">
                            <div class="d-flex align-items-start gap-3">
                                <div class="flex-shrink-0">

                                    <img src="{{ getSingleMedia($bookingdata->provider, 'profile_image', null) }}"
                                        alt="Provider Profile" class="rounded-circle"
                                        style="width: 60px; height: 60px; object-fit: cover;">
                                    @if (optional($bookingdata->provider)->profile_image)
                                        <img src="{{ asset('images/default-user.png') }}" alt="Default Profile"
                                            class="rounded-circle"
                                            style="width: 60px; height: 60px; object-fit: cover;">
                                    @endif
                                </div>
                                <div class="flex-grow-1">
                                    <p class="mb-1 text-primary d-flex align-items-center"><i class="ri-briefcase-line role-icon me-1"></i> {{ __('messages.provider') }}</p>
                                    <h5 class="mb-2">{{ optional($bookingdata->provider)->display_name ?? '-' }}
                                    </h5>
                                </div>
                            </div>
                            <ul class="list-unstyled mt-3">
                                <li class="d-flex align-items-center mb-2">
                                    <i class="ri-calendar-line me-2"></i>
                                    <span class="text-body">
                                        {{ optional($bookingdata->provider)->created_at ? optional($bookingdata->provider)->created_at->format('Y-m-d') : '-' }}
                                    </span>
                                </li>
                                <!-- <li class="d-flex align-items-center mb-2">
                                            <i class="ri-mail-line me-2"></i>
                                            <a href="mailto:{{ optional($bookingdata->provider)->email }}" class="text-body">
                                                {{ optional($bookingdata->provider)->email ?? '-' }}
                                </a>
                            </li> -->
                                <li class="d-flex align-items-center">
                                    <i class="ri-map-pin-line me-2"></i>
                                    <span
                                        class="text-wrap">{{ optional($bookingdata->provider)->city->name ?? '-' }} - </span>
                                        {{ optional($bookingdata->provider)->country->name ?? '-' }}
                                        </span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- Handyman Information -->
                <div class="col-md-4">
                    <div class="card soft-shadow hover-lift role-card role-handyman">
                        @if (count($bookingdata->handymanAdded) > 0)
                            @foreach ($bookingdata->handymanAdded as $booking)
                                <div class="card-body">
                                    <div class="d-flex align-items-start gap-4">
                                        <div class="flex-shrink-0">

                                            <img src="{{ getSingleMedia($booking->handyman, 'profile_image', null) }}"
                                                alt="Handyman Profile" class="rounded-circle"
                                                style="width: 60px; height: 60px; object-fit: cover;">
                                            @if (optional($booking->handyman)->profile_image)
                                                <img src="{{ asset('images/default-user.png') }}"
                                                    alt="Default Profile" class="rounded-circle"
                                                    style="width: 60px; height: 60px; object-fit: cover;">
                                            @endif
                                        </div>
                                        <div class="flex-grow-1">
                                            <p class="mb-1 text-primary d-flex align-items-center"><i class="ri-tools-line role-icon me-1"></i> {{ __('messages.handyman') }}</p>
                                            <h5 class="mb-2 ">
                                                {{ optional($booking->handyman)->display_name ?? '-' }}
                                            </h5>
                                        </div>
                                    </div>
                                    <ul class="list-unstyled mt-3">
                                        <li class="d-flex align-items-center mb-2">
                                            <i class="ri-calendar-line me-2"></i>
                                            <span class="text-body">
                                                {{ optional($booking->handyman)->created_at ? optional($booking->handyman)->created_at->format('Y-m-d') : '-' }}
                                            </span>
                                        </li>
                                        {{-- <li class="d-flex align-items-center mb-2">
                                            <i class="ri-phone-line me-2"></i>
                                            <a href="tel:{{ optional($booking->handyman)->contact_number }}"
                                                class="text-body">
                                                {{ optional($booking->handyman)->contact_number ?? '-' }}
                                            </a>
                                        </li> --}}
                                        <li class="d-flex align-items-center">
                                            <i class="ri-map-pin-line me-2"></i>
                                            <span
                                                class="text-wrap">{{ strip_tags(optional($booking->handyman)->city->name ?? '') ?? '-' }}-
                                                {{ strip_tags(optional($booking->handyman)->country->name ?? '') ?? '-' }}
                                                </span>
                                        </li>
                                    </ul>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>

            </div>
        </div>
        @php
            $showAdvance = false;

            if (isset($payment)) {
                if ($payment->payment_type === 'bank_transfer' && $payment->status == 1) {
                    $showAdvance = true;
                } elseif ($payment->payment_type !== 'bank_transfer') {
                    $showAdvance = true;
                }
            }
        @endphp

        <!-- billing section -->
        @hasanyrole(['user', 'provider', 'admin'])
            <div class="col-md-4 mt-4">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table text-nowrap align-middle mb-0">
                                <tbody>
                                    <!-- Unit Price -->
                                    <tr>
                                        <td>{{ __('Price (Unit Price)') }}</td>
                                        <td class="bk-value">
                                            {{ getPriceFormat($bookingdata->amount) }}
                                        </td>
                                    </tr>

                                    <!-- Quantity -->
                                    <tr>
                                        <td>{{ __('Quantity (Nbr of Packages, Hours, Days)') }}</td>
                                        <td class="bk-value">
                                            {{ $bookingdata->quantity }}
                                        </td>
                                    </tr>

                                    <!-- Total Amount (Price x Quantity) -->
                                    <tr>
                                        <td>{{ __('Total Amount') }}</td>
                                        <td class="bk-value">
                                            {{ getPriceFormat($bookingdata->amount * $bookingdata->quantity) }}
                                        </td>
                                    </tr>

                                    <!-- Discount -->
                                    @if ($bookingdata->discount > 0)
                                        <tr>
                                            <td>{{ __('Discount') }} ({{ $bookingdata->discount }}% off)</td>
                                            <td class="bk-value text-success">
                                                -{{ getPriceFormat($bookingdata->final_discount_amount) }}
                                            </td>
                                        </tr>
                                    @endif

                                    <!-- Coupon -->
                                    @if ($bookingdata->couponAdded)
                                        <tr>
                                            <td>{{ __('Coupon') }} ({{ $bookingdata->couponAdded->code }})</td>
                                            <td class="bk-value text-success">
                                                -{{ getPriceFormat($bookingdata->final_coupon_discount_amount) }}
                                            </td>
                                        </tr>
                                    @endif

                                    <!-- Subtotal after discounts -->
                                    @php
                                        $baseTotal = $bookingdata->amount * $bookingdata->quantity;
                                        $subTotal = $baseTotal;

                                        if ($bookingdata->discount > 0) {
                                            $subTotal -= $bookingdata->final_discount_amount;
                                        }

                                        if ($bookingdata->couponAdded) {
                                            $subTotal -= $bookingdata->final_coupon_discount_amount;
                                        }
                                    @endphp
                                    <tr class="grand-sub-total">
                                        <td>{{ __('Sub Total (After Discount)') }}</td>
                                        <td class="bk-value">{{ getPriceFormat($subTotal) }}</td>
                                    </tr>

                                    <!-- Addon Services -->
                                    @php
                                        $addonTotal = $bookingdata->bookingAddonService->sum('price');
                                    @endphp
                                    @if ($addonTotal > 0)
                                        <tr>
                                            <td>{{ __('Service Addons') }}</td>
                                            <td class="bk-value">{{ getPriceFormat($addonTotal) }}</td>
                                        </tr>
                                    @endif

                                    <!-- Extra Charges -->
                                    @php
                                        $extraChargeTotal = $bookingdata->bookingExtraCharge->sum(function ($item) {
                                            return $item->price * $item->qty;
                                        });
                                    @endphp
                                    @if ($extraChargeTotal > 0)
                                        <tr>
                                            <td>{{ __('Extra Charges') }}</td>
                                            <td class="bk-value">{{ getPriceFormat($extraChargeTotal) }}</td>
                                        </tr>
                                    @endif

                                    <!-- Total after Addons and Extra Charges -->
                                    @php
                                        $totalBeforeTax = $subTotal + $addonTotal + $extraChargeTotal;
                                    @endphp
                                    <tr>
                                        <td>{{ __('Total') }}</td>
                                        <td class="bk-value">{{ getPriceFormat($totalBeforeTax) }}</td>
                                    </tr>

                                    <!-- Taxes -->
                                    @php
                                        $serviceTaxId = $bookingdata->service->tax_country_id ?? null;
                                        $taxRate = 0;
                                        if ($serviceTaxId) {
                                            $tax = \App\Models\Tax::find($serviceTaxId);
                                            $taxRate = $tax->value ?? 0;
                                        }
                                        $taxAmount = ($totalBeforeTax * $taxRate) / 100;
                                    @endphp
                                    <tr>
                                        <td>{{ __('Tax') }} ({{ $taxRate }}%)</td>
                                        <td class="bk-value text-danger">{{ getPriceFormat($taxAmount) }}</td>
                                    </tr>

                                    <!-- Grand Total -->
                                    @php
                                        $grandTotal = $totalBeforeTax + $taxAmount;
                                    @endphp
                                    <tr>
                                        <td>{{ __('Grand Total') }}</td>
                                        <td class="bk-value">{{ getPriceFormat($grandTotal) }}</td>
                                    </tr>

                                    @php
                                        $advancePaidAmount = $bookingdata->advance_paid_amount;

                                        // Only calculate advance if not already paid
                                        if ($advancePaidAmount <= 0 && isset($advanceservice) && $advanceservice > 0) {
                                            // $advanceservice is already the percentage (e.g., 50)
                                            $advancePaidAmount = ($grandTotal * $advanceservice) / 100;
                                        }

                                        $remainingAmount = $grandTotal - $advancePaidAmount;
                                    @endphp

                                    <tr>
                                        <td>{{ __('Advance Payment') }}</td>
                                        <td class="bk-value">
                                            {{ getPriceFormat($advancePaidAmount) }}
                                        </td>
                                    </tr>
                                    <tr class="grand-total">
                                        <td>{{ __('Remaining Amount') }}</td>
                                        <td class="bk-value">
                                            {{ getPriceFormat($remainingAmount) }}
                                        </td>
                                    </tr>

                                    {{-- @endif --}}

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endhasanyrole

    </div>

    <!-- Extra Charges table -->
    @if (count($bookingdata->bookingExtraCharge) > 0)
        <div class="col-md-12 mt-4">
            <div class="card">
                @php
                $extraChargeTotal = $bookingdata->bookingExtraCharge->sum(function ($charge) {
                    return $charge->price * $charge->qty;
                });
                @endphp
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-plus-circle me-2"></i>
                            {{ __('messages.extra_charge') }}
                        </h5>
                        <div class="text-end">
                            <span class="badge bg-light text-dark fs-6">
                                {{ __('Total Extra Charges') }}: <strong>{{ getPriceFormat($extraChargeTotal) }}</strong>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>{{ __('messages.title') }}</th>
                                    <th>{{ __('messages.price') }}</th>
                                    <th>{{ __('messages.quantity') }}</th>
                                    <th class="text-end">{{ __('messages.total_amount') }}</th>
                                </tr>
                               
                               
                            </thead>
                            <tbody>
                                @foreach ($bookingdata->bookingExtraCharge as $charge)
                                    <tr>
                                        <td>{{ $charge->title }}</td>
                                        <td>{{ getPriceFormat($charge->price) }}</td>
                                        <td>{{ $charge->qty }}</td>
                                        <td class="text-end">{{ getPriceFormat($charge->price * $charge->qty) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    @endif

    @if (count($bookingdata->bookingRating) > 0)
        <div class="col-md-12 mt-4">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive mb-4">
                        <h4 class="mb-3">{{ __('messages.Reviews') }}</h4>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>{{ __('messages.name') }}</th>
                                    <th>{{ __('messages.rating') }}</th>
                                    <th>{{ __('messages.review') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($bookingdata->bookingRating as $review)
                                    <tr>
                                        <td>{{ $review->customer->first_name ?? '' }}
                                            {{ $review->customer->last_name ?? '' }}</td>
                                        <td>{{ $review->rating }}</td>
                                        <td>{{ $review->review }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    @endif

    @if (!empty($customer_review))
        <div class="col-md-12 mt-4">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive mb-4">
                        <h4 class="mb-3">{{ __('messages.my_review') }}</h4>
                        <table class="table table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th>{{ __('messages.name') }}</th>
                                    <th>{{ __('messages.rating') }}</th>
                                    <th>{{ __('messages.review') }}</th>
                                    <th class="text-center">{{ __('messages.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>{{ $customer_review->customer->first_name ?? '' }}
                                        {{ $customer_review->customer->last_name ?? '' }}</td>
                                    <td>{{ $customer_review->rating }}</td>
                                    <td>{{ $customer_review->review }}</td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-warning edit-review"
                                            data-id="{{ $customer_review->id }}"
                                            data-rating="{{ $customer_review->rating }}"
                                            data-review="{{ $customer_review->review }}">
                                            <i class="las la-pen"></i> {{ __('messages.edit') }}
                                        </button>
                                        <button class="btn btn-sm btn-danger delete-review"
                                            data-id="{{ $customer_review->id }}">
                                            <i class="las la-trash"></i> {{ __('messages.delete') }}
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    @endif

    @if (!empty($serviceProof) && count($serviceProof) > 0)
        <div class="col-md-12 mt-4">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive mb-4">
                        <h4 class="mb-3">{{ __('messages.service_proof') }}</h4>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>{{ __('messages.title') }}</th>
                                    <th>{{ __('messages.description') }}</th>
                                    <th>{{ __('messages.attachments') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($serviceProof as $proof)
                                    <tr>
                                        <td>{{ $proof->title }}</td>
                                        <td>{{ $proof->description }}</td>
                                        <td>
                                            @if (!empty($proof->proof_attachments))
                                                @foreach ($proof->proof_attachments as $url)
                                                    <a href="{{ $url }}" target="_blank">
                                                        <img src="{{ $url }}" alt="Proof Image"
                                                            style="height: 50px; width: 50px; object-fit: cover; margin-right: 5px;">
                                                    </a>
                                                @endforeach
                                            @else
                                                <span class="text-muted">No attachments</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Debug: Show if no service proof found -->
        <div class="col-md-12 mt-4">
            {{-- <div class="alert alert-info">
                <strong>Debug:</strong> No service proof found for this booking. 
                ServiceProof count: {{ isset($serviceProof) ? count($serviceProof) : 'Not set' }}
            </div> --}}
        </div>
    @endif


    <!-- Addon  Charges table -->
    @if ($bookingdata->bookingAddonService->count() > 0)
        <div class="col-md-12 mt-4">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive mb-4">
                        <h4 class="mb-3">{{ __('messages.service_addon') }}</h4>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th class="ps-lg-3">{{ __('messages.title') }}</th>
                                    <th>{{ __('messages.price') }}</th>
                                    <th class="text-end">{{ __('messages.total_amount') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $addonTotalPrice = 0;
                                @endphp
                                @foreach ($bookingdata->bookingAddonService as $addonservice)
                                    @php
                                        $addonTotalPrice += $addonservice->price;
                                    @endphp
                                    <tr>
                                        <td class="text-wrap ps-lg-3">
                                            <div class="d-flex flex-column">
                                                <a href="{{ route('service.detail', $bookingdata->service_id) }}#{{ \Illuminate\Support\Str::slug($addonservice->name) }}"
                                                    class="booking-service-link fw-bold">{{ $addonservice->name }}</a>
                                            </div>
                                        </td>
                                        <td>{{ getPriceFormat($addonservice->price) }}</td>
                                        <td class="text-end">{{ getPriceFormat($addonservice->price) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    @endif
</div>


<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
<script>
    var baseUrl = "{{ url('/') }}";
    var csrfToken = "{{ csrf_token() }}";

    $(document).ready(function () {
        // Handle booking status dropdown
        $(document).on('change', '.bookingstatus', function () {
            var status = $(this).val();
            var id = $(this).attr('data-id');
            fetch("{{ route('bookingStatus.update') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ status: status, bookingId: id })
            }).then(r=>r.json()).then(() => {})
            .catch(()=>{});
        });

        // Handle payment status dropdown
        $(document).on('change', '.paymentStatus', function () {
            var status = $(this).val();
            var id = $(this).attr('data-id');
            fetch("{{ route('bookingStatus.update') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ status: status, bookingId: id })
            }).then(r=>r.json()).then(() => {})
            .catch(()=>{});
        });

        // Assign provider button
        $('#assign-provider').on('click', function () {
            var bookingId = $(this).data('id');
            var handymanIds = [$(this).data('handyman-id')];

            Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to assign this provider?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, assign it!',
                cancelButtonText: 'No, cancel'
            }).then((willAssign) => {
                if (!willAssign.isConfirmed) return;
                fetch('{{ route('booking.assigned') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id: bookingId, 'handyman_id[]': handymanIds })
                }).then(r=>r.json()).then(response => {
                    Swal.fire('Success!', response.message || 'Assigned', 'success');
                    $('.assign-provider-btn, .assign-provider').prop('disabled', true).addClass('disabled');
                }).catch(() => Swal.fire('Error!', 'Unable to assign provider', 'error'));
            });
        });

        // Handle confirm or update button
        $(document).on('click', '.update-booking, .confirm-booking', function (e) {
            e.preventDefault();

            const bookingId = $(this).data('id');
            const status = $(this).data('status');
            const confirmMessage = $(this).data('confirm-message');
            const isAdvancePaid = $(this).data('advance') === 1;

            if (status === 'cancelled') {
                Swal.fire({
                    title: 'Cancellation Reason',
                    input: 'textarea',
                    inputLabel: 'Please provide a reason for cancellation',
                    inputPlaceholder: 'Type your reason here...',
                    inputAttributes: {
                        'aria-label': 'Cancellation reason'
                    },
                    showCancelButton: true,
                    confirmButtonText: 'Submit',
                    cancelButtonText: 'Close',
                    inputValidator: (value) => {
                        if (!value || value.trim() === '') {
                            return 'Reason is required';
                        }
                    }
                }).then((inputResult) => {
                    if (inputResult.isConfirmed) {
                        const reason = inputResult.value;
                        updateBookingStatus(bookingId, status, isAdvancePaid, reason);
                    }
                });
            } else {
                Swal.fire({
                    title: 'Are you sure?',
                    text: confirmMessage,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes!',
                    cancelButtonText: 'No, cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        updateBookingStatus(bookingId, status, isAdvancePaid);
                    }
                });
            }
        });

        // Function to update booking status (no full page reload)
        function updateBookingStatus(bookingId, newStatus, isAdvancePaid, reason = '', charges = null) {
            const requestPayload = {
                id: bookingId,
                start_at: '',
                end_at: '',
                duration_diff: 0,
                reason: reason,
                status: newStatus,
                payment_status: isAdvancePaid ? 'ADVANCE_PAID' : '',
                extra_charges: charges
            };

            let api_url = baseUrl + '/api/booking-update';
            setButtonsPending(true);
            fetch(api_url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(requestPayload)
            }).then(r => r.json()).then(response => {
                const label = humanizeStatus(newStatus);
                $('#booking_status__span').text(label);
                setButtonsPending(false);
                disableStatusActions();
                Swal.fire('Updated', 'Booking updated successfully', 'success')
                    .then(() => window.location.reload());
            }).catch(err => {
                Swal.fire('Error', 'Something went wrong', 'error');
                setButtonsPending(false);
            });
        }
        function humanizeStatus(val){
            if(!val) return '';
            return String(val).replace(/_/g,' ').replace(/\b\w/g, c => c.toUpperCase());
        }
        function setButtonsPending(isPending){
            const $btns = $('.status-content').find('button, a.btn');
            if(isPending){
                $btns.addClass('disabled').attr('aria-busy','true');
            }else{
                $btns.removeClass('disabled').removeAttr('aria-busy');
            }
        }
        function disableStatusActions(){
            // After a status change, disable action buttons to prevent immediate repeat
            $('.update-booking, .confirm-booking, .hold-booking, #complete-booking').prop('disabled', true).addClass('disabled');
        }

        // Hold booking via SweetAlert textarea (fast, like show.blade.php)
        $(document).on('click', '.hold-booking', function () {
            const bookingId = $(this).data('id');
            Swal.fire({
                title: 'Put on Hold',
                input: 'textarea',
                inputLabel: 'Provide hold reason',
                inputPlaceholder: 'Type your reason here... (max 500 chars)',
                showCancelButton: true,
                confirmButtonText: 'Submit',
                preConfirm: (value) => {
                    if (!value || value.trim().length === 0) return Swal.showValidationMessage('Hold reason is required');
                    if (value.length > 500) return Swal.showValidationMessage('Reason too long (max 500 chars)');
                    return value;
                }
            }).then((result) => {
                if (result.isConfirmed) updateBookingStatus(bookingId, 'hold', 1, result.value);
            });
        });

        // Complete booking - charges modal
        $('#complete-booking').on('click', function () {
            $('#extraChargesWrapper').html('');
            addChargeRow();

            const bookingId = $(this).data('id');
            $('#extraChargesModal').find('#bookingId').val(bookingId);
            $('#extraChargesModal').modal('show');
        });

        // Add extra charge row
        $('#addChargeRow').on('click', function () {
            addChargeRow();
        });

        // Remove charge row
        $(document).on('click', '.remove-charge-row', function () {
            $(this).closest('.charge-row').remove();
        });

        // Increase quantity
        $(document).on('click', '.increase-qty', function () {
            const input = $(this).closest('.input-group').find('.charge-quantity');
            input.val(parseInt(input.val()) + 1);
        });

        // Decrease quantity
        $(document).on('click', '.decrease-qty', function () {
            const input = $(this).closest('.input-group').find('.charge-quantity');
            let qty = parseInt(input.val());
            if (qty > 1) input.val(qty - 1);
        });

        // Submit charges
        $('#extraChargesForm').on('submit', function (e) {
            e.preventDefault();

            const charges = [];
            $('.charge-row').each(function () {
                const title = $(this).find('.charge-detail').val();
                const price = parseFloat($(this).find('.charge-amount').val()) || 0;
                const qty = parseInt($(this).find('.charge-quantity').val()) || 0;
                const total_amount = price * qty;

                if (title && price > 0 && qty > 0) {
                    charges.push({ title, price, qty, total_amount });
                }
            });

            const bookingId = $('#extraChargesModal').find('#bookingId').val();

            Swal.fire({
                title: 'Are you sure?',
                text: 'Are you sure you want to perform this action?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes!',
                cancelButtonText: 'No, cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    updateBookingStatus(bookingId, 'completed', 1, '', charges);
                    $('#extraChargesModal').modal('hide');
                }
            });
        });

        // Add charge row function
        function addChargeRow() {
            const row = `
                <div class="charge-row border p-3 mb-3 rounded bg-light">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label">Extra Charge Detail</label>
                            <input type="text" class="form-control charge-detail" placeholder="e.g. Travel cost">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Amount</label>
                            <input type="number" class="form-control charge-amount" placeholder="e.g. 100">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Quantity</label>
                            <div class="input-group">
                                <button class="btn btn-outline-secondary btn-sm decrease-qty" type="button">-</button>
                                <input type="number" class="form-control text-center charge-quantity" value="1" min="1">
                                <button class="btn btn-outline-secondary btn-sm increase-qty" type="button">+</button>
                            </div>
                        </div>
                        <div class="col-md-1 text-end">
                            <button class="btn btn-danger btn-sm remove-charge-row" type="button">&times;</button>
                        </div>
                    </div>
                </div>`;
            $('#extraChargesWrapper').append(row);
        }

        // Rating and review handlers
        let selectedRating = 0;
        let editingReviewId = null;

        $(document).on('click', '#rate-now-btn', function () {
            const bookingId = $(this).data('id');
            $('#ratingBookingId').val(bookingId);
            $('#reviewText').val('');
            selectedRating = 0;
            $('.star').removeClass('selected');
            $('#ratingModal').modal('show');
        });

        $(document).on('click', '.star', function () {
            selectedRating = $(this).data('value');
            $('.star').removeClass('selected');
            $(this).prevAll().addBack().addClass('selected');
        });

        $('#ratingForm').on('submit', function (e) {
            e.preventDefault();

            const bookingId = $('#ratingBookingId').val();
            const review = $('#reviewText').val().trim();

            if (selectedRating === 0) {
                return Swal.fire('Error', 'Please select a star rating.', 'warning');
            }

            const payload = {
                booking_id: "{{ $bookingdata->id }}",
                service_id: "{{ $bookingdata->service_id }}",
                customer_id: "{{ $bookingdata->customer_id }}",
                rating: selectedRating,
                review: review
            };

            if (editingReviewId) {
                payload.id = editingReviewId;
            }

            $.ajax({
                url: baseUrl + '/api/save-booking-rating',
                type: 'POST',
                data: payload,
                success: function (response) {
                    Swal.fire('Thank you!', 'Your rating has been submitted.', 'success');
                    $('#ratingModal').modal('hide');
                    window.location.reload();
                },
                error: function (xhr) {
                    console.error(xhr);
                    Swal.fire('Error', 'Failed to submit rating.', 'error');
                }
            });
        });

        $(document).on('click', '.edit-review', function () {
            const reviewId = $(this).data('id');
            const rating = $(this).data('rating');
            const reviewText = $(this).data('review');

            selectedRating = rating;
            editingReviewId = reviewId;

            $('#reviewText').val(reviewText);
            $('.star').removeClass('selected').each(function () {
                if ($(this).data('value') <= rating) {
                    $(this).addClass('selected');
                }
            });

            $('#ratingModal').modal('show');
        });

        $(document).on('click', '.delete-review', function () {
            const reviewId = $(this).data('id');

            Swal.fire({
                title: 'Are you sure?',
                text: 'You will not be able to recover this review!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: baseUrl + '/api/delete-booking-rating',
                        type: 'POST',
                        data: { id: reviewId },
                        success: function (res) {
                            Swal.fire('Deleted!', 'Your review has been removed.', 'success');
                            window.location.reload();
                        },
                        error: function (xhr) {
                            console.error(xhr);
                            Swal.fire('Error!', 'Failed to delete the review.', 'error');
                        }
                    });
                }
            });
        });

    });
</script>
<style>
	.soft-shadow {
		box-shadow: 0 6px 18px rgba(0,0,0,0.06);
		transition: transform 0.25s ease, box-shadow 0.25s ease;
		border-radius: 12px;
		border: 1px solid rgba(0,0,0,0.04);
	}
	.hover-lift:hover {
		transform: translateY(-4px);
		box-shadow: 0 12px 24px rgba(0,0,0,0.12);
	}
	.role-card {
		border-left: 4px solid transparent;
	}
	.role-card .role-icon { font-size: 18px; }
	.role-customer { border-left-color: #3b82f6; }
	.role-provider { border-left-color: #16a34a; }
	.role-handyman { border-left-color: #f59e0b; }
	.role-customer .role-icon { color: #3b82f6; }
	.role-provider .role-icon { color: #16a34a; }
	.role-handyman .role-icon { color: #f59e0b; }
	.booking-info-container .card-body p.fz-12,
	.booking-info-container .card-body .opacity-75 {
		opacity: 0.75 !important;
		font-size: 12px !important;
		margin-bottom: 6px;
	}
	.booking-info-container .card-body .text-primary {
		font-weight: 600;
	}
	
	/* Equal height for all cards */
	.h-65 {
		height: 110px !important;
		display: flex !important;
		flex-direction: column !important;
	}
	
	.h-65 .card-body {
		display: flex !important;
		flex-direction: column !important;
		justify-content: center !important;
		flex: 1 !important;
		overflow: hidden !important;
		padding: 12px !important;
	}
	
	.h-65 .booking-slots-container {
		max-height: 50px !important;
		overflow-y: auto !important;
		overflow-x: hidden !important;
	}
	
	.h-65 .booking-slots-container::-webkit-scrollbar {
		width: 3px;
	}
	
	.h-65 .booking-slots-container::-webkit-scrollbar-thumb {
		background: #ccc;
		border-radius: 2px;
	}
	
	.h-65 .slot-item {
		margin-bottom: 4px !important;
		padding: 4px 8px !important;
		font-size: 11px !important;
	}
	
	/* Simple Service Schedule Card Styles - Row-wise Grid Layout */
	.booking-slots-grid {
		display: grid !important;
		grid-template-columns: 1fr !important;
		gap: 12px !important;
		width: 100%;
	}
	
	/* Horizontal Service Schedule Layout */
	.booking-slots-horizontal {
		display: flex !important;
		flex-direction: row !important;
		flex-wrap: wrap !important;
		gap: 12px !important;
		width: 100%;
		overflow-x: auto;
	}
	
	.slot-item-horizontal {
		display: flex !important;
		flex-direction: row !important;
		align-items: center !important;
		gap: 16px !important;
		padding: 12px 16px !important;
		background: #f8f9fa !important;
		border-radius: 8px !important;
		border: 1px solid #e9ecef !important;
		border-left: 3px solid #5F60B9 !important;
		transition: all 0.2s ease;
		min-width: fit-content !important;
		flex: 0 0 auto !important;
		white-space: nowrap;
	}
	
	.slot-item-horizontal:hover {
		background: #f1f3f5 !important;
		border-color: #dee2e6 !important;
		border-left-color: #dc3545 !important;
		box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
		transform: translateY(-2px);
	}
	
	.slot-date-horizontal {
		display: flex !important;
		align-items: center;
		font-size: 13px;
		color: #212529;
		line-height: 1.4;
		white-space: nowrap;
	}
	
	.slot-date-horizontal i {
		color: #dc3545;
		font-size: 15px;
		flex-shrink: 0;
	}
	
	.slot-time-horizontal {
		display: flex !important;
		align-items: center;
		font-size: 13px;
		color: #6c757d;
		line-height: 1.4;
		white-space: nowrap;
	}
	
	.slot-time-horizontal i {
		color: #5F60B9;
		font-size: 15px;
		flex-shrink: 0;
	}
	
	.slot-item-simple {
		display: flex !important;
		flex-direction: column !important;
		gap: 8px;
		padding: 12px 14px !important;
		background: #f8f9fa !important;
		border-radius: 8px !important;
		border: 1px solid #e9ecef !important;
		border-left: 3px solid #5F60B9 !important;
		transition: all 0.2s ease;
		min-width: 0;
		width: 100% !important;
	}
	
	.slot-item-simple:hover {
		background: #f1f3f5 !important;
		border-color: #dee2e6 !important;
		border-left-color: #dc3545 !important;
		box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
		transform: translateY(-2px);
	}
	
	.slot-date-simple {
		display: flex !important;
		align-items: center;
		font-size: 13px;
		color: #212529;
		margin-bottom: 6px !important;
		line-height: 1.4;
	}
	
	.slot-date-simple i {
		color: #dc3545;
		font-size: 15px;
		flex-shrink: 0;
	}
	
	.slot-date-simple span {
		font-weight: 600;
	}
	
	.slot-time-simple {
		display: flex !important;
		align-items: center;
		font-size: 12px;
		color: #6c757d;
		line-height: 1.4;
	}
	
	.slot-time-simple i {
		color: #6c757d;
		font-size: 13px;
		flex-shrink: 0;
	}
	
	.slot-time-simple span {
		white-space: normal;
		word-break: break-word;
	}
	
	.no-slots-simple {
		display: flex;
		align-items: center;
		padding: 12px;
		font-size: 13px;
	}
	
	.no-slots-simple i {
		font-size: 16px;
	}
	
	/* Responsive adjustments */
	@media (max-width: 768px) {
		.booking-slots-grid {
			grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)) !important;
			gap: 10px !important;
		}
		
		.slot-item-simple {
			padding: 10px 12px !important;
		}
		
		.slot-date-simple {
			font-size: 12px;
		}
		
		.slot-time-simple {
			font-size: 11px;
		}
	}
	
	@media (max-width: 576px) {
		.booking-slots-grid {
			grid-template-columns: 1fr !important;
		}
	}
	
	/* Legacy styles kept for backward compatibility */
	.booking-slots-container-modern {
		max-height: none !important;
		overflow-y: visible !important;
		overflow-x: visible !important;
		padding-right: 0;
	}
	
	.slot-item-modern {
		display: flex;
		align-items: center;
		gap: 10px;
		padding: 8px 10px;
		margin-bottom: 6px;
		background: linear-gradient(135deg, rgba(255, 0, 0, 0.05) 0%, rgba(95, 96, 185, 0.05) 100%);
		border-radius: 8px;
		border: 1px solid rgba(255, 0, 0, 0.1);
		transition: all 0.3s ease;
		position: relative;
		overflow: hidden;
	}
	
	.slot-item-modern::before {
		content: '';
		position: absolute;
		left: 0;
		top: 0;
		bottom: 0;
		width: 3px;
		background: linear-gradient(135deg, #FF0000 0%, #5F60B9 100%);
		border-radius: 0 8px 8px 0;
	}
	
	.slot-item-modern:hover {
		background: linear-gradient(135deg, rgba(255, 0, 0, 0.1) 0%, rgba(95, 96, 185, 0.1) 100%);
		transform: translateX(2px);
		box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
	}
	
	.slot-icon-wrapper {
		width: 32px;
		height: 32px;
		display: flex;
		align-items: center;
		justify-content: center;
		background: linear-gradient(135deg, #FF0000 0%, #5F60B9 100%);
		border-radius: 8px;
		flex-shrink: 0;
		box-shadow: 0 2px 6px rgba(255, 0, 0, 0.2);
	}
	
	.slot-icon-wrapper i {
		color: #fff;
		font-size: 16px;
	}
	
	.slot-content {
		flex: 1;
		min-width: 0;
	}
	
	.slot-date {
		font-size: 12px;
		font-weight: 600;
		color: #212529;
		margin-bottom: 2px;
		line-height: 1.3;
	}
	
	.slot-time {
		display: flex;
		align-items: center;
		gap: 4px;
		font-size: 11px;
		color: #6c757d;
		line-height: 1.3;
	}
	
	.slot-time i {
		font-size: 12px;
		color: #5F60B9;
	}
	
	.no-slots-modern {
		text-align: center;
		padding: 20px 10px;
		color: #6c757d;
	}
	
	.no-slots-modern i {
		font-size: 32px;
		color: #dee2e6;
		margin-bottom: 8px;
		display: block;
	}
	
	.no-slots-modern p {
		font-size: 12px;
		margin: 0;
		color: #adb5bd;
	}
	
	/* Marquee Banner Styles */
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
	.marquee-handyman {
		background: #f0f9ff;
		color: #0c4a6e;
		border-left-color: #0ea5e9;
	}
	.marquee-neutral {
		background: #f1f1f1;
		color: #333;
		border-left-color: #6c757d;
	}
</style>

<!-- View Customer Rating Info Modal (Before Accepting) -->
<div class="modal fade" id="viewCustomerRatingModal" tabindex="-1" aria-labelledby="viewCustomerRatingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewCustomerRatingModalLabel">{{ __('Customer Rating Information') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="customer-rating-info-content">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">{{ __('messages.close') }}</button>
                <button type="button" class="btn btn-success" id="confirm-accept-booking">{{ __('messages.accept_booking') }}</button>
            </div>
        </div>
    </div>
</div>

<!-- Customer Rating Modal -->
<div class="modal fade" id="customerRatingModal" tabindex="-1" aria-labelledby="customerRatingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="customerRatingModalLabel">{{ __('landingpage.rate_customer') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="customerRatingForm">
                    <input type="hidden" id="customer_rating_booking_id">
                    <input type="hidden" id="customer_rating_customer_id">
                    <input type="hidden" id="customer_rating_provider_id">
                    
                    <div class="mb-3">
                        <label class="form-label">{{ __('messages.rating') }}</label>
                        <div class="star-rating">
                            <span class="star" data-value="1">&#9733;</span>
                            <span class="star" data-value="2">&#9733;</span>
                            <span class="star" data-value="3">&#9733;</span>
                            <span class="star" data-value="4">&#9733;</span>
                            <span class="star" data-value="5">&#9733;</span>
                        </div>
                        <input type="hidden" id="customer_rating_value" value="0">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">{{ __('messages.review') }}</label>
                        <textarea class="form-control" id="customer_review_text" rows="4" placeholder="Write your review..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">{{ __('messages.cancel') }}</button>
                <button type="button" class="btn btn-primary" id="submitCustomerRating">{{ __('messages.submit') }}</button>
            </div>
        </div>
    </div>
</div>

<!-- Service Proof Modal -->
<div class="modal fade" id="serviceProofModal" tabindex="-1" aria-labelledby="serviceProofModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="serviceProofModalLabel">{{ __('messages.service_proof') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="serviceProofForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="proof_booking_id" name="booking_id">
                    <input type="hidden" id="proof_service_id" name="service_id">
                    <input type="hidden" id="proof_user_id" name="user_id">
                    
                    <div class="mb-3">
                        <label for="proof_title" class="form-label">{{ __('messages.title') }}</label>
                        <input type="text" class="form-control" id="proof_title" name="title" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="proof_description" class="form-label">{{ __('messages.description') }}</label>
                        <textarea class="form-control" id="proof_description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="proof_images" class="form-label">{{ __('messages.upload_images') }}</label>
                        <input type="file" class="form-control" id="proof_images" name="images[]" multiple accept="image/*" required>
                        <div class="form-text">{{ __('messages.upload_multiple_images') }}</div>
                    </div>
                    
                    <div id="imagePreview" class="mb-3"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                <button type="button" class="btn btn-primary" id="submitServiceProof">{{ __('messages.submit') }}</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Load Customer Rating Info for all customer sections
    $('.customer-rating-section').each(function() {
        var $section = $(this);
        var customerId = $section.data('customer-id');
        var sectionId = $section.attr('id');
        
        if (!customerId) return;
        
        $.ajax({
            url: baseUrl + '/api/get-customer-rating-info',
            type: 'GET',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            data: {
                customer_id: customerId
            },
            success: function(response) {
                console.log('Customer Rating Response:', response); // Debug
                var html = '';
                
                // Handle response - API returns data directly (not wrapped in 'data' key)
                // Check if response has 'data' key, otherwise use response directly
                var data = (response && response.data) ? response.data : response;
                
                if (data && (data.average_rating !== undefined || data.total_reviews !== undefined)) {
                    var avgRating = parseFloat(data.average_rating) || 0;
                    var totalReviews = parseInt(data.total_reviews) || 0;
                    
                    console.log('Average Rating:', avgRating, 'Total Reviews:', totalReviews); // Debug
                    
                    // Display stars based on rating
                    html += '<div class="customer-rating-display">';
                    html += '<div class="d-flex align-items-center justify-content-center gap-2 mb-2">';
                    html += '<div class="star-rating-display">';
                    
                    // Show filled and empty stars
                    for (var i = 1; i <= 5; i++) {
                        if (i <= Math.floor(avgRating)) {
                            // Full star
                            html += '<span class="star-filled-display">&#9733;</span>';
                        } else if (i === Math.ceil(avgRating) && avgRating % 1 !== 0 && i === Math.ceil(avgRating)) {
                            // Half star (only show if it's the ceiling and has decimal)
                            html += '<span class="star-half-display">&#9733;</span>';
                        } else {
                            // Empty star
                            html += '<span class="star-empty-display">&#9734;</span>';
                        }
                    }
                    
                    html += '</div>';
                    html += '<strong class="rating-value">' + avgRating.toFixed(1) + '</strong>';
                    html += '</div>';
                    
                    html += '<div class="text-center">';
                    html += '<small class="text-muted">' + totalReviews + ' ' + (totalReviews === 1 ? 'review' : 'reviews') + '</small>';
                    html += '</div>';
                    
                    html += '</div>';
                } else {
                    html += '<div class="text-center">';
                    html += '<div class="star-rating-display mb-2">';
                    for (var j = 1; j <= 5; j++) {
                        html += '<span class="star-empty-display">&#9734;</span>';
                    }
                    html += '</div>';
                    html += '<small class="text-muted">No ratings yet</small>';
                    html += '</div>';
                }
                
                $section.html(html);
            },
            error: function(xhr) {
                console.error('Error loading customer rating:', xhr);
                console.error('Response:', xhr.responseText); // Debug
                $section.html('<div class="text-center"><small class="text-muted">Rating unavailable</small></div>');
            }
        });
    });
    
    // View Customer Rating Info (Before Accepting) - Keep for modal if needed
    var pendingBookingId = null;
    var pendingCustomerId = null;
    
    $(document).on('click', '#view-customer-rating-btn', function() {
        var bookingId = $(this).data('booking-id');
        var customerId = $(this).data('customer-id');
        
        pendingBookingId = bookingId;
        pendingCustomerId = customerId;
        
        // Show modal
        $('#viewCustomerRatingModal').modal('show');
        
        // Load customer rating info
        $.ajax({
            url: baseUrl + '/api/get-customer-rating-info',
            type: 'GET',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            data: {
                customer_id: customerId
            },
            success: function(response) {
                var html = '<div class="customer-rating-summary">';
                
                if (response.data) {
                    var data = response.data;
                    var avgRating = data.average_rating || 0;
                    var totalReviews = data.total_reviews || 0;
                    
                    html += '<div class="text-center mb-4">';
                    html += '<h4>' + (data.customer_name || 'Customer') + '</h4>';
                    html += '<div class="mb-3">';
                    html += '<div class="star-rating-large">';
                    for (var i = 1; i <= 5; i++) {
                        if (i <= Math.round(avgRating)) {
                            html += '<span class="star-filled">&#9733;</span>';
                        } else {
                            html += '<span class="star-empty">&#9734;</span>';
                        }
                    }
                    html += '</div>';
                    html += '<div class="mt-2"><strong>' + parseFloat(avgRating).toFixed(1) + '</strong> <span class="text-muted">(' + totalReviews + ' ' + (totalReviews === 1 ? 'review' : 'reviews') + ')</span></div>';
                    html += '</div>';
                    html += '</div>';
                    
                    if (data.recent_reviews && data.recent_reviews.length > 0) {
                        html += '<div class="recent-reviews">';
                        html += '<h5 class="mb-3">Recent Reviews</h5>';
                        html += '<div class="list-group">';
                        data.recent_reviews.forEach(function(review) {
                            html += '<div class="list-group-item mb-2">';
                            html += '<div class="d-flex justify-content-between align-items-start mb-2">';
                            html += '<div>';
                            html += '<strong>' + (review.provider_name || 'Provider') + '</strong>';
                            html += '<div class="star-rating-small mt-1">';
                            for (var j = 1; j <= 5; j++) {
                                if (j <= review.rating) {
                                    html += '<span class="star-filled-small">&#9733;</span>';
                                } else {
                                    html += '<span class="star-empty-small">&#9734;</span>';
                                }
                            }
                            html += '</div>';
                            html += '</div>';
                            html += '<small class="text-muted">' + (review.created_at || '') + '</small>';
                            html += '</div>';
                            if (review.review) {
                                html += '<p class="mb-0">' + review.review + '</p>';
                            }
                            html += '</div>';
                        });
                        html += '</div>';
                        html += '</div>';
                    } else {
                        html += '<div class="alert alert-info">No reviews available for this customer yet.</div>';
                    }
                } else {
                    html += '<div class="alert alert-info">No rating information available for this customer.</div>';
                }
                
                html += '</div>';
                $('#customer-rating-info-content').html(html);
            },
            error: function(xhr) {
                console.error('Error:', xhr);
                $('#customer-rating-info-content').html('<div class="alert alert-danger">Failed to load customer rating information.</div>');
            }
        });
    });
    
    // Confirm Accept Booking after viewing rating
    $(document).on('click', '#confirm-accept-booking', function() {
        $('#viewCustomerRatingModal').modal('hide');
        
        // Show confirmation dialog
        Swal.fire({
            title: 'Are you sure?',
            text: 'You want to accept this booking?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, accept it!',
            cancelButtonText: 'No, cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Update booking status to accept
                updateBookingStatus(pendingBookingId, 'accept', 0);
            }
        });
    });
    
    // Customer Rating Modal Handler
    var customerSelectedRating = 0;
    
    // Open Customer Rating Modal
    $(document).on('click', '#rate-customer-btn', function() {
        var bookingId = $(this).data('booking-id');
        var customerId = $(this).data('customer-id');
        var providerId = $(this).data('provider-id');
        
        $('#customer_rating_booking_id').val(bookingId);
        $('#customer_rating_customer_id').val(customerId);
        $('#customer_rating_provider_id').val(providerId);
        $('#customer_review_text').val('');
        customerSelectedRating = 0;
        $('#customer_rating_value').val(0);
        $('#customerRatingModal .star').removeClass('selected');
        
        // Show modal - support both Bootstrap 3/4 and 5
        $('#customerRatingModal').modal('show');
    });
    
    // Close modal handlers - support both Bootstrap versions
    $(document).on('click', '#customerRatingModal .btn-close, #customerRatingModal [data-bs-dismiss="modal"], #customerRatingModal [data-dismiss="modal"]', function() {
        $('#customerRatingModal').modal('hide');
    });
    
    // Star Rating Click Handler
    $(document).on('click', '#customerRatingModal .star', function() {
        customerSelectedRating = $(this).data('value');
        $('#customer_rating_value').val(customerSelectedRating);
        $('#customerRatingModal .star').removeClass('selected');
        $(this).prevAll().addBack().addClass('selected');
    });
    
    // Submit Customer Rating
    $('#submitCustomerRating').click(function() {
        var bookingId = $('#customer_rating_booking_id').val();
        var customerId = $('#customer_rating_customer_id').val();
        var providerId = $('#customer_rating_provider_id').val();
        var rating = $('#customer_rating_value').val();
        var review = $('#customer_review_text').val().trim();
        
        if (!rating || rating == 0) {
            if (typeof Swal !== 'undefined' && Swal.fire) {
                Swal.fire('Error', 'Please select a star rating.', 'warning');
            } else {
                alert('Please select a star rating.');
            }
            return;
        }
        
        var payload = {
            booking_id: bookingId,
            customer_id: customerId,
            provider_id: providerId,
            rating: rating,
            review: review
        };
        
        $.ajax({
            url: baseUrl + '/api/save-customer-rating',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json'
            },
            data: JSON.stringify(payload),
            success: function(response) {
                // Close modal
                $('#customerRatingModal').modal('hide');
                
                // Reset form
                $('#customerRatingForm')[0].reset();
                customerSelectedRating = 0;
                $('#customerRatingModal .star').removeClass('selected');
                
                // Show success message
                if (typeof Swal !== 'undefined' && Swal.fire) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message || 'Rating submitted successfully.',
                        showConfirmButton: true
                    }).then(function(){
                        location.reload();
                    });
                } else {
                    alert('Rating submitted successfully.');
                    location.reload();
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr);
                var errorMsg = 'Failed to submit rating.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                if (typeof Swal !== 'undefined' && Swal.fire) {
                    Swal.fire('Error', errorMsg, 'error');
                } else {
                    alert(errorMsg);
                }
            }
        });
    });
    
    // Service Proof Button Click
    $('#service-proof-btn').click(function() {
        var bookingId = $(this).data('id');
        var serviceId = $(this).data('service-id');
        var userId = $(this).data('user-id');
        
        $('#proof_booking_id').val(bookingId);
        $('#proof_service_id').val(serviceId);
        $('#proof_user_id').val(userId);
        
        $('#serviceProofModal').modal('show');
    });
    
    // Image Preview
    $('#proof_images').change(function() {
        var files = this.files;
        var preview = $('#imagePreview');
        preview.empty();
        
        for (var i = 0; i < files.length; i++) {
            var file = files[i];
            if (file.type.startsWith('image/')) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    preview.append('<img src="' + e.target.result + '" class="img-thumbnail me-2 mb-2" style="width: 100px; height: 100px; object-fit: cover;">');
                };
                reader.readAsDataURL(file);
            }
        }
    });
    
    // Submit Service Proof
    $('#submitServiceProof').click(function() {
        var formData = new FormData();
        
        // Add form fields
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
        formData.append('booking_id', $('#proof_booking_id').val());
        formData.append('service_id', $('#proof_service_id').val());
        formData.append('user_id', $('#proof_user_id').val());
        formData.append('title', $('#proof_title').val());
        formData.append('description', $('#proof_description').val());
        
        // Add images with correct naming convention
        var files = $('#proof_images')[0].files;
        formData.append('attachment_count', files.length);
        
        for (var i = 0; i < files.length; i++) {
            formData.append('booking_attachment_' + i, files[i]);
        }
        
        $.ajax({
            url: '{{ route("service.proof.store") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('Service Proof Response:', response);
                // Close modal (Bootstrap 5 API with jQuery fallback)
                try {
                    var modalEl = document.getElementById('serviceProofModal');
                    var modal = window.bootstrap ? (window.bootstrap.Modal.getInstance(modalEl) || new window.bootstrap.Modal(modalEl)) : null;
                    if(modal){ modal.hide(); } else { $('#serviceProofModal').modal('hide'); }
                } catch(e) { $('#serviceProofModal').modal('hide'); }
                $('#serviceProofForm')[0].reset();
                $('#imagePreview').empty();
                
                // Show non-blocking toast then reload
                if (typeof Swal !== 'undefined' && Swal.fire) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: '{{ __("messages.service_proof_submitted_successfully") }}',
                        showConfirmButton: false,
                        timer: 1600,
                        timerProgressBar: true
                    }).then(function(){
                        location.reload();
                    });
                } else {
                    setTimeout(function(){ location.reload(); }, 1200);
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr);
                alert('{{ __("messages.error_occurred") }}');
            }
        });
    });
});
</script>

<!-- reverted: removed inline modal/iframe logic to restore redirect behavior -->
