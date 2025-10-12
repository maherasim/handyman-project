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
                                   @hasanyrole('user')
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
                                                        target="_blank" data-id="{{ $bookingdata->id }}">
                                                    <a class="float-end btn btn-primary"
                                                        {{ __('messages.advance_pay') }}
                                                        target="_blank" data-id="{{ $bookingdata->id }}">
                                @endif
                                                        {{ __('messages.advance_pay') }}
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
                                                data-status="pending_approval"
                                                data-confirm-message="You want to end this booking?">
                                                <i class="las la-check-circle"></i>
                                                {{ __('messages.done') }}
                                            </button>
                                        </div>
                                    @endhasanyrole
                                @endif

                                @if ($bookingdata->status === 'pending_approval')
                                    @hasanyrole(['user'])
                                        <div class="w3-third">
                                             <button class="float-end btn btn-success confirm-booking"
                                                data-id="{{ $bookingdata->id }}"
                                                data-handyman-id="{{ $bookingdata->provider_id }}"
                                                data-status="confirm"
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

                                        <button class="float-end btn btn-success" id="complete-booking"
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
                                        <div class="w3-third d-flex align-items-end">
                                            <button class="float-end btn btn-warning" id="rate-now-btn"
                                                data-id="{{ $bookingdata->id }}">
                                                <i class="las la-star"></i>
                                                <!-- Changed to a star icon (Line Awesome) -->
                                                {{ __('messages.rate_now') }}
                                            </button>
                                        </div>
                                        @if (isset($payment) && $payment->payment_status != 'paid')
                                            @php
                                                // Calculate remaining amount
                                                $grandTotal = $bookingdata->total_amount ?? 0;
                                                $advancePaid = $bookingdata->advance_paid_amount ?? 0;
                                                $remainingAmount = $grandTotal - $advancePaid;
                                            @endphp
                                            <div class="w3-third d-flex align-items-end">
                                                <a class="float-end btn btn-warning d-flex align-items-center gap-2"
                                                    href="{{ route('book.service', ['id' => $bookingdata->service_id, 'booking_id' => $bookingdata->id, 'payment_type' => 'full_payment']) }}"
                                                    target="_blank" data-id="{{ $bookingdata->id }}"
                                                    style="background: linear-gradient(135deg, #fd7e14, #ffc107); border: none; box-shadow: 0 4px 12px rgba(253, 126, 20, 0.3);">
                                                    <i class="las la-credit-card"></i>
                                                    <span>{{ __('Pay Remaining') }}</span>
                                                    <span class="badge bg-light text-dark px-2 py-1 rounded-pill">
                                                        <i class="fas fa-euro-sign me-1"></i>{{ getPriceFormat($remainingAmount) }}
                                                    </span>
                                                </a>
                                            </div>
                                        @endif
                                    @endhasanyrole
                                @endif

                                @if ($bookingdata->status === 'completed' && isset($payment) && $payment->payment_status == 'paid')
                                    @hasanyrole('handyman')
                                        <div class="w3-third d-flex align-items-end">
                                            <button class="float-end btn btn-primary" id="service-proof-btn"
                                        </div>
                                                <a class="float-end btn btn-warning"
                                @endif
                                                    target="_blank" data-id="{{ $bookingdata->id }}">
                                        <a href="{{ route('invoice_pdf', $bookingdata->id) }}" class="btn btn-primary"
                                                    <!-- Changed to a star icon (Line Awesome) -->
                                                    {{ __('Pay now') }}
                                    @endif
                                @endhasanyrole

                                @php
                                    $authUser = auth()->user();
                                    $isBookParticipant = $authUser && (
                                        ($authUser->hasRole('provider') && ($authUser->id ?? 0) === ($bookingdata->provider_id ?? 0)) ||
                                        ($authUser->hasRole('user') && ($authUser->id ?? 0) === ($bookingdata->customer_id ?? 0))
                                    );
                                    $advancePaidForBooking = false;
                                    if (isset($payment)) {
                                        if (($payment->payment_type ?? null) === 'bank_transfer') {
                                            $advancePaidForBooking = (int) ($payment->status ?? 0) === 1;
                                        } else {
                                            // Any non-bank-transfer payment implies funds captured
                                            $advancePaidForBooking = true;
                                        }
                                    }
                                @endphp
                                @if ($isBookParticipant && $advancePaidForBooking)
                                    <a href="{{ route('chat.view.booking', $bookingdata->id) }}" class="btn btn-outline-primary">
                                        <i class="fas fa-comments"></i> {{ __('Chat with Provider') }}
                                    </a>
                                    @if($bookingdata->handymanAdded && $bookingdata->handymanAdded->count())
                                        @php $firstHandyman = optional($bookingdata->handymanAdded->first())->handyman_id; @endphp
                                        @if($firstHandyman)
                                            <a href="{{ route('chat.view.booking.handyman', ['bookingId' => $bookingdata->id, 'handymanId' => $firstHandyman]) }}" class="btn btn-outline-secondary">
                                                <i class="fas fa-user-cog"></i> {{ __('Chat with Handyman') }}
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
                                    if ($is_enable_advance_payment == 1 && (!isset($payment) || $payment->payment_status != 'advanced_paid')) {
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
                default => 'text-danger',
            };
        @endphp

        <div class="card-body" style="{{ $cardStyle }}">
            <p class="fz-12 {{ $isPaid ? 'text-white' : 'opacity-75' }}">
                {{ __('messages.payment_status') }}
            </p>

                            <div class="col-md-4 ">
                <p class="mb-0 {{ $statusClass }}">
                    {{ str_replace('_', ' ', ucfirst($paymentStatus)) }}
                </p>
            @else
                <p class="mb-0 text-danger">
                    {{ __('messages.pending') }}
                </p>
            @endif
        </div>
    </div>
</div>
                            <div class="col-md-4">
                            

                        <div class="col-md-4 mb-3">
                                <div class="card h-100 soft-shadow hover-lift">
                                    <div class="card-body">
                                        <p class="opacity-75 fz-12">{{ __('Working Address') }}</p>
                                        <p class="mb-0 text-primary" id="booking_status__span">
                                            {{ str_replace('_', ' ', ucfirst($bookingdata->address)) }}</p>
                            <div class="col-md-4">
                                </div>
                        </div>






                            <!-- Add Cancellation Reason Card -->
                            @if ($bookingdata->status === 'cancelled')
                                <div class="col-md-4 mb-3">
                                <div class="col-md-4">
                                        <div class="card-body">
                                            <p class="opacity-75 fz-12">{{ __('landingpage.cancel_reason') }}</p>
                                            <p class="mb-0 text-danger">
                                                {{ $bookingdata->reason ?? __('messages.no_reason_provided') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="col-md-4">
                            <div class="col-md-4 mb-3">
                                <div class="card h-100 soft-shadow hover-lift">
                                    <div class="card-body">
                                        <p class="opacity-75 fz-12 mb-3">{{ __('Booking Schedule') }}</p>
                                        @if($bookingdata->slots && count($bookingdata->slots) > 0)
                                            <div class="booking-slots-container">
                                                @foreach ($bookingdata->slots as $index => $slot)
                                                    <div class="slot-item d-flex align-items-center mb-2 p-2 rounded" 
                                                         style="background: linear-gradient(135deg, #f8f9fa, #e9ecef); border-left: 3px solid #007bff;">
                                                        <div class="flex-shrink-0 me-2">
                                                            <i class="ri-calendar-check-line text-primary" style="font-size: 16px;"></i>
                                                        </div>
                                    <div class="col-md-4">
                                                            <div class="fw-semibold text-dark small">
                                                                {{ date("M d, Y", strtotime($slot->date)) ?? '-' }}
                                                            </div>
                                                            <div class="text-muted small">
                                                                <i class="ri-time-line me-1"></i>
                                                                {{ date('g:i A', strtotime($slot->start_time)) }} - {{ date('g:i A', strtotime($slot->end_time)) }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="text-muted text-center py-3">
                                                <i class="ri-calendar-line" style="font-size: 24px; opacity: 0.5;"></i>
                                                <p class="mb-0 small mt-2">No slots scheduled</p>
                            <div class="col-md-4">
                                <div class="card h-100 soft-shadow hover-lift">
                                    <div class="card-body">
                                        <p class="opacity-75 fz-12 mb-3">{{ __('Booking Schedule') }}</p>
                                        @if($bookingdata->slots && count($bookingdata->slots) > 0)
                                            <div class="booking-slots-container">
                                                @foreach ($bookingdata->slots as $index => $slot)
                                                    <div class="slot-item d-flex align-items-center mb-2 p-2 rounded" 
                                                         style="background: linear-gradient(135deg, #f8f9fa, #e9ecef); border-left: 3px solid #007bff;">
                                                        <div class="flex-shrink-0 me-2">
                                                            <i class="ri-calendar-check-line text-primary" style="font-size: 16px;"></i>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <div class="fw-semibold text-dark small">
                                                                {{ date("M d, Y", strtotime($slot->date)) ?? '-' }}
                                                            </div>
                                                            <div class="text-muted small">
                                                                <i class="ri-time-line me-1"></i>
                                                                {{ date('g:i A', strtotime($slot->start_time)) }} - {{ date('g:i A', strtotime($slot->end_time)) }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="text-muted text-center py-3">
                                                <i class="ri-calendar-line" style="font-size: 24px; opacity: 0.5;"></i>
                                                <p class="mb-0 small mt-2">No slots scheduled</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                           <div class="col-md-4">  
                                <div class="card h-100 soft-shadow hover-lift">
                                    <div class="card-body">
                                        <p class="opacity-75 fz-12 mb-3">{{ __('Booking Schedule') }}</p>
                                        @if($bookingdata->slots && count($bookingdata->slots) > 0)
                                            <div class="booking-slots-container">
                                                @foreach ($bookingdata->slots as $index => $slot)
                                                    <div class="slot-item d-flex align-items-center mb-2 p-2 rounded" 
                                                         style="background: linear-gradient(135deg, #f8f9fa, #e9ecef); border-left: 3px solid #007bff;">
                                                        <div class="flex-shrink-0 me-2">
                                                            <i class="ri-calendar-check-line text-primary" style="font-size: 16px;"></i>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <div class="fw-semibold text-dark small">
                                                                {{ date("M d, Y", strtotime($slot->date)) ?? '-' }}
                                                            </div>
                                                            <div class="text-muted small">
                                                                <i class="ri-time-line me-1"></i>
                                                                {{ date('g:i A', strtotime($slot->start_time)) }} - {{ date('g:i A', strtotime($slot->end_time)) }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="text-muted text-center py-3">
                                                <i class="ri-calendar-line" style="font-size: 24px; opacity: 0.5;"></i>
                                                <p class="mb-0 small mt-2">No slots scheduled</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                           <div class="col-md-4">  
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
                        <div class="col-md-4">ole-card role-customer">
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
                                <div class="col-md-4">
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
                        <div class="col-md-4">
                                <li class="d-flex align-items-center">
                                    <i class="ri-map-pin-line me-2"></i>
                                    <span
                                        class="text-wrap">{{ strip_tags(optional($bookingdata->customer)->address) ?? '-' }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- Provider Information -->
                <div class="col-md-4">
                    <div class="card soft-shadow hover-lift role-card role-provider">
                        <div class="card-body">
                            <div class="d-flex align-items-start gap-3">
                                <div class="flex-shrink-0">

                                <div class="col-md-4">edia($bookingdata->provider, 'profile_image', null) }}"
                                        alt="Provider Profile" class="rounded-circle"
                                        style="width: 60px; height: 60px; object-fit: cover;">
                                    @if (optional($bookingdata->provider)->profile_image)
                                        <img src="{{ asset('images/default-user.png') }}" alt="Default Profile"
                                            class="rounded-circle"
                                            style="width: 60px; height: 60px; object-fit: cover;">
                                    @endif
                                </div>
                                <div class="flex-grow-1">
                                    <p class="mb-1 text-primary d-flex align-items-center"><i class="ri-briefcase-line role-icon me-1"></i> {{ __('Employer') }}</p>
                                            <img src="{{ getSingleMedia($booking->handyman, 'profile_image', null) }}"
                                                alt="Handyman Profile" class="rounded-circle"
                                                style="width: 60px; height: 60px; object-fit: cover;">
                                            @if (optional($booking->handyman)->profile_image)
                                                <img src="{{ asset('images/default-user.png') }}"
                                                    alt="Default Profile" class="rounded-circle"
                                                    style="width: 60px; height: 60px; object-fit: cover;">
                                  messages.provider  @endif
                                        </div>
                                        <div class="flex-grow-1">
                                            <p class="mb-1 text-primary d-flex align-items-center"><i class="ri-tools-line role-icon me-1"></i> {{ __('Worker') }}</p>
                                            <h5 class="mb-2 ">
                                                {{ optional($booking->handyman)->display_name ?? '-' }}
                                            </h5>
                                        </div>
                                    </div>
                                    <ul class="list-unstyled mt-3">
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
                                                class="text-wrap">{{ strip_tags(optional($booking->handyman)->address) ?? '-' }}</span>
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
                <div class="card-body">
                    <div class="table-responsive mb-4">
                        <h4 class="mb-3">{{ __('messages.extra_charge') }}</h4>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>{{ __('messages.title') }}</th>
                                    <th>{{ __('messages.price') }}</th>
                                    <th>{{ __('messages.quantity') }}</th>
                                    <th class="text-end">{{ __('messages.total_amount') }}</th>
                                </tr>
                                @php
                                    $extraChargeTotal = $bookingdata->bookingExtraCharge->sum(function ($charge) {
                                        return $charge->price * $charge->qty;
                                    });
                                @endphp
                                <tr class="table-info">
                                    <th colspan="3" class="text-end fw-bold">{{ __('Total Extra Charges') }}:</th>
                                    <th class="text-end fw-bold">{{ getPriceFormat($extraChargeTotal) }}</th>
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
                                                <a href=""
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
<script>
    var baseUrl = "{{ url('/') }}";
    var csrfToken = "{{ csrf_token() }}";

    $(document).ready(function () {
            fetch("{{ route('bookingStatus.update') }}", {
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
        // Complete booking - charges modal
        $('#complete-booking').on('click', function () {
            $('#extraChargesWrapper').html('');
            addChargeRow();
            const row = `
            const row = `
            $('#extraChargesModal').find('#bookingId').val(bookingId);
        // Complete booking - charges modal
        // Complete booking - charges modal
        $('#complete-booking').on('click', function () {
            $('#extraChargesWrapper').html('');
            addChargeRow();
            const row = `
            const row = `ine-secondary btn-sm decrease-qty" type="button">-</button>
            $('#extraChargesModal').find('#bookingId').val(bookingId);
        // Add extra charge row
            $('#extraChargesModal').find('#bookingId').val(bookingId);
            $('#extraChargesModal').modal('show');             <label class="form-label">Quantity</label>
                            <div class="input-group">
                                <button class="btn btn-outline-secondary btn-sm decrease-qty" type="button">-</button>
        // Add extra charge row
        $('#addChargeRow').on('click', function () {ider { border-left-color: #16a34a; }
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
