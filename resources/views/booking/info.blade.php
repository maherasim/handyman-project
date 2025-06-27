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
<div class="container-fluid">
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
                                            <button class="float-end btn btn-primary" id="assign-provider"
                                                data-id="{{ $bookingdata->id }}"
                                                data-handyman-id="{{ $bookingdata->provider_id }}">
                                                <i class="lab la-telegram-plane"></i>
                                                {{ __('messages.assign_provider') }}
                                            </button>
                                        </div>

                                        <div class="w3-third">
                                            <a href="{{ route('booking.assign_form', ['id' => $bookingdata->id]) }}"
                                                class="float-end btn btn-primary loadRemoteModel">
                                                <i class="lab la-telegram-plane"></i>
                                                {{ __('messages.assign_handyman') }}
                                            </a>
                                        </div>
                                    @endhasanyrole
                                @endif

                                @if ($bookingdata->payment_id !== null)
                                    <a href="{{ route('invoice_pdf', $bookingdata->id) }}" class="btn btn-primary"
                                        target="_blank">
                                        <i class="ri-file-text-line"></i>
                                        {{ __('messages.invoice') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                        <!-- Main Content Row -->
                        <div class="row ">
                            <div class="col-md-4 ">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <p class="opacity-75 fz-12">{{ __('messages.book_placed') }}</p>
                                        <p class="mb-0">
                                            {{ date("$datetime->date_format $datetime->time_format", strtotime($bookingdata->created_at)) ?? '-' }}
                                        </p>
                                    </div>
                                </div>
                            </div>


                            <div class="col-md-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <p class="opacity-75 fz-12">{{ __('messages.booking_status') }}</p>
                                        <p class="mb-0 text-primary" id="booking_status__span">
                                            {{ $bookingdata->status }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <p class="opacity-75 fz-12">{{ __('Location') }}</p>
                                        <p class="mb-0 text-primary">
                                            {{ optional($bookingdata->service->city)->name ?? '-' }},
                                            {{ optional($bookingdata->service->country)->name ?? '-' }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <p class="opacity-75 fz-12">{{ __('messages.total_amount') }}</p>
                                        <p class="mb-0 text-primary">
                                                   {{ getPriceFormat($bookingdata->total_amount ?? 0) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <p class="opacity-75 fz-12">{{ __('messages.payment_method') }}</p>
                                        <p class="mb-0 text-primary">
                                            {{ isset($payment) ? ucfirst($payment->payment_type) : '-' }}</p>
                                    </div>
                                </div>
                            </div>
                            @if (
                                (isset($payment) && $payment->payment_type === 'bank_transfer' && $payment->status == 1) ||
                                    (isset($payment) && $payment->payment_type !== 'bank_transfer'))
                                <div class="col-md-4">
                                    <div class="card h-65 border-0 shadow"
                                        style="background: linear-gradient(135deg, #f7c59f, #ff9a9e); color: #fff;">
                                        <div class="card-body">
                                            <p class="mb-1 fw-bold text-uppercase" style="opacity: 0.9;">
                                                {{ __('Advance Payment') }}
                                            </p>
                                            <p class="mb-0 fs-5 fw-bold" id="service_schedule__span">
                                                {{ getPriceFormat($bookingdata->advance_paid_amount) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endif


                            <div class="col-md-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <p class="opacity-75 fz-12">{{ __('Booking Slots') }}</p>
                                        @foreach ($bookingdata->slots as $slot)
                                            <p class="mb-0 text-primary">
                                                {{ date("$datetime->date_format", strtotime($slot->date)) ?? '-' }}
                                                / {{ $slot->start_time }} -> {{ $slot->end_time }}</p>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <p class="opacity-75 fz-12">{{ __('messages.payment_status') }}</p>

                                        @if (isset($payment) && $payment->payment_type === 'bank_transfer' && $payment->status == 0)
                                            <p class="mb-0 text-warning">
                                                {{ __('messages.pending') }}
                                            </p>
                                        @elseif (isset($payment) && $payment->payment_status)
                                            @php
                                                $statusClass = match ($payment->payment_status) {
                                                    'paid', 'advanced_paid' => 'text-success',
                                                    'Advanced Refund' => 'text-warning',
                                                    default => 'text-danger',
                                                };
                                            @endphp
                                            <p class="mb-0 {{ $statusClass }}">
                                                {{ str_replace('_', ' ', ucfirst($payment->payment_status)) }}
                                            </p>
                                        @else
                                            <p class="mb-0 text-danger">
                                                {{ __('messages.pending') }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>


                            <!-- Add Cancellation Reason Card -->
                            @if ($bookingdata->status === 'cancelled')
                                <div class="col-md-4">
                                    <div class="card h-100">
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
                    </div>
                </div>
            </div>

            <!-- Order information section  -->
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
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
                                    <p class="mb-1 text-primary">{{ __('messages.customer') }}</p>
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
                                        class="text-wrap">{{ optional($bookingdata->customer)->address ?? '-' }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- Provider Information -->
                <div class="col-md-4">
                    <div class="card">
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
                                    <p class="mb-1 text-primary">{{ __('messages.provider') }}</p>
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
                                        class="text-wrap">{{ optional($bookingdata->provider)->address ?? '-' }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- Handyman Information -->
                <div class="col-md-4">
                    <div class="card">
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
                                            <p class="mb-1 text-primary">{{ __('messages.handyman') }}</p>
                                            <h5 class="mb-2 ">{{ optional($booking->handyman)->display_name ?? '-' }}
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
                                                class="text-wrap">{{ optional($booking->handyman)->address ?? '-' }}</span>
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
      <!-- billing section -->
<div class="col-md-4">
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

                        <!-- Advance and Remaining -->
                        @if ($showAdvance)
                            <tr>
                                <td>{{ __('Advance Payment') }}</td>
                                <td class="bk-value">
                                    {{ getPriceFormat($bookingdata->advance_paid_amount) }}
                                </td>
                            </tr>
                            <tr class="grand-total">
                                <td>{{ __('Remaining Amount') }}</td>
                                <td class="bk-value">
                                    {{ getPriceFormat($grandTotal - $bookingdata->advance_paid_amount) }}
                                </td>
                            </tr>
                        @endif

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

    </div>

    <!-- Extra Charges table -->
    @if (count($bookingdata->bookingExtraCharge) > 0)
        <div class="col-md-12">
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

    <!-- Addon  Charges table -->
    @if ($bookingdata->bookingAddonService->count() > 0)
        <div class="col-md-12">
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
<script>
    $(document).on('change', '.bookingstatus', function() {
        var status = $(this).val();
        var id = $(this).attr('data-id');

        $.ajax({
            type: "POST",
            dataType: "json",
            url: "{{ route('bookingStatus.update') }}",
            data: {
                'status': status,
                'bookingId': id
            },
            success: function(data) {
                // Handle success response
            }
        });
    });

    $(document).on('change', '.paymentStatus', function() {
        var status = $(this).val();
        var id = $(this).attr('data-id');

        $.ajax({
            type: "POST",
            dataType: "json",
            url: "{{ route('bookingStatus.update') }}",
            data: {
                'status': status,
                'bookingId': id
            },
            success: function(data) {
                // Handle success response
            }
        });
    });

    $(document).ready(function() {
        $('#assign-provider').on('click', function() {
            var bookingId = $(this).data('id');
            var handymanIds = [];
            handymanIds.push($(this).data('handyman-id'));

            // SweetAlert confirmation
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
                if (willAssign.isConfirmed) {
                    $.ajax({
                        url: '{{ route('booking.assigned') }}',
                        type: 'POST',
                        data: {
                            id: bookingId,
                            'handyman_id[]': handymanIds,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire("Success!", response.message, "success");
                            window.location.reload();
                        },
                        error: function(xhr) {
                            Swal.fire("Error!", xhr.responseText, "error");
                        }
                    });
                } else {
                    Swal.fire("Assignment canceled!", "The provider was not assigned.", "info");
                }
            });
        });
    });
</script>
