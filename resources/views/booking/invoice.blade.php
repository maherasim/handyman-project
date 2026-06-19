<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <title>{{ env('APP_NAME') }}</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style type="text/css">
        body { margin: 0; padding: 0; background: #ffffff; color: #222; font-family: DejaVu Sans, Helvetica, Arial, sans-serif; font-size: 13px; line-height: 1.6; }
        .container { width: 100%; max-width: 860px; margin: 0 auto; padding: 28px; }
        .card { border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; }
        .section { padding: 18px 20px; }
        .border-b { border-bottom: 1px solid #e5e7eb; }
        .text-right { text-align: right; }
        .text-muted { color: #6b7280; }
        .fw-bold { font-weight: 700; }
        .title { font-size: 22px; font-weight: 700; color: #111; }
        table { width: 100%; border-collapse: collapse; }
        td, th { padding: 9px 10px; vertical-align: top; }
        .no-border td { border: 0 !important; }
        /* Price breakdown table */
        .breakdown { border: 1px solid #e5e7eb; border-radius: 6px; margin-top: 20px; }
        .breakdown td { border-bottom: 1px solid #f3f4f6; }
        .breakdown tr:last-child td { border-bottom: 0; }
        .breakdown .row-label { color: #374151; width: 60%; }
        .breakdown .row-value { text-align: right; font-weight: 600; width: 40%; }
        .breakdown .row-section { background: #f9fafb; }
        .breakdown .row-section td { color: #6b7280; font-size: 11px; text-transform: uppercase; letter-spacing: .4px; padding: 6px 10px; border-bottom: 1px solid #e5e7eb; font-weight: 400; }
        .breakdown .row-grand td { background: #f0fdf4; font-weight: 700; font-size: 14px; color: #15803d; }
        .breakdown .row-advance td { background: #fffbeb; color: #92400e; }
        .breakdown .row-remaining td { background: #fef2f2; font-weight: 700; color: #991b1b; }
        .breakdown .row-discount .row-value { color: #16a34a; }
        .badge { display: inline-block; padding: 3px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; }
        .badge-paid { background: #dcfce7; color: #15803d; }
        .badge-advance { background: #fef9c3; color: #92400e; }
        .badge-pending { background: #f3f4f6; color: #6b7280; }
        .badge-cancelled { background: #fee2e2; color: #991b1b; }
        .status-row td { padding: 14px 10px; }
        .footer { margin-top: 18px; padding-top: 12px; border-top: 1px solid #e5e7eb; text-align: center; color: #9ca3af; font-size: 11px; }
        .mb-4 { margin-bottom: 4px; }
        .mb-8 { margin-bottom: 8px; }
    </style>
</head>
<?php
use App\Models\Setting;
$settings = Setting::whereIn('type', ['site-setup', 'general-setting'])
    ->whereIn('key', ['site-setup', 'general-setting'])
    ->get()
    ->keyBy('key');

$app         = isset($settings['site-setup'])      ? json_decode($settings['site-setup']->value)      : null;
$generaldata = isset($settings['general-setting']) ? json_decode($settings['general-setting']->value) : null;
$logoPath    = public_path('assets/frobster logo.png');
?>
@php
    $unitPrice = (float) ($bookingdata->amount   ?? 0);
    $quantity  = (float) ($bookingdata->quantity ?? 1);
    $baseTotal = $unitPrice * $quantity;

    $discountAmount = ($bookingdata->discount ?? 0) > 0 ? (float) ($bookingdata->final_discount_amount        ?? 0) : 0;
    $couponAmount   = $bookingdata->couponAdded         ? (float) ($bookingdata->final_coupon_discount_amount ?? 0) : 0;

    $subTotal = $baseTotal - $discountAmount - $couponAmount;

    $addonTotal       = (float) ($bookingdata->bookingAddonService->sum('price') ?? 0);
    $extraChargeTotal = 0;
    foreach ($bookingdata->bookingExtraCharge as $item) {
        $extraChargeTotal += (float) ($item->price ?? 0) * (float) ($item->qty ?? 0);
    }

    $totalBeforeTax = $subTotal + $addonTotal + $extraChargeTotal;

    $taxRate = 0;
    if (!empty(optional($bookingdata->service)->tax_country_id)) {
        $taxModel = \App\Models\Tax::find(optional($bookingdata->service)->tax_country_id);
        $taxRate  = $taxModel->value ?? 0;
    }

    $taxAmount  = ($totalBeforeTax * $taxRate) / 100;
    $grandTotal = $totalBeforeTax + $taxAmount;

    // Advance payment — show calculated due amount when not yet paid
    $advancePercent   = (float) (optional($bookingdata->service)->advance_payment_amount ?? 0);
    $isAdvanceEnabled = optional($bookingdata->service)->is_enable_advance_payment == 1 && $advancePercent > 0;

    $advancePaid = (float) ($bookingdata->advance_paid_amount ?? 0);
    if ($advancePaid <= 0 && $isAdvanceEnabled) {
        $advancePaid = ($grandTotal * $advancePercent) / 100;
    }
    $remainingAmount = $grandTotal - $advancePaid;

    // Payment status
    $paymentStatusRaw = strtolower($payment->payment_status ?? 'pending');
    $paymentStatusLabel = __('messages.payment_status_display_' . $paymentStatusRaw)
        ?: ucwords(str_replace('_', ' ', $paymentStatusRaw));

    if (in_array($paymentStatusRaw, ['paid'])) {
        $statusBadgeClass = 'badge-paid';
    } elseif ($paymentStatusRaw === 'advanced_paid') {
        $statusBadgeClass = 'badge-advance';
    } elseif (in_array($paymentStatusRaw, ['cancelled', 'failed'])) {
        $statusBadgeClass = 'badge-cancelled';
    } else {
        $statusBadgeClass = 'badge-pending';
    }

    $invoiceDateIssued = $bookingdata->created_at
        ? \Carbon\Carbon::parse($bookingdata->created_at)->locale(app()->getLocale())->translatedFormat('d F Y')
        : '';

    $firstHandymanRow = $bookingdata->handymanAdded->first();
    $handymanName = $firstHandymanRow ? (optional($firstHandymanRow->handyman)->display_name ?? '-') : '-';
@endphp
<body>
<div class="container">
    <div class="card">

        {{-- ── Header ── --}}
        <div class="section border-b">
            <table class="no-border">
                <tr>
                    <td style="width:60%; border:0;">
                        <div class="title">{{ __('messages.invoice') }}</div>
                        <div class="text-muted mb-4">{{ __('messages.invoice_pdf_invoice_no') }} #{{ $bookingdata->id }}</div>
                        <div class="text-muted mb-4">{{ __('messages.invoice_pdf_date_issued') }} {{ $invoiceDateIssued }}</div>
                        <div class="text-muted">{{ __('messages.invoice_pdf_currency') }} {{ $bookingdata->currency ?? 'EUR' }}</div>
                    </td>
                    <td class="text-right" style="width:40%; border:0;">
                        @if (file_exists($logoPath))
                            <img src="{{ $logoPath }}" alt="logo" style="height:56px; width:auto; margin-bottom:6px;"><br>
                        @endif
                        <div class="text-muted">{{ $generaldata->inquriy_email ?? '' }}</div>
                        @if(!empty($generaldata->helpline_number))
                            <div class="text-muted">{{ $generaldata->helpline_number }}</div>
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        {{-- ── Bill From / To ── --}}
        <div class="section border-b">
            <table class="no-border">
                <tr>
                    <td style="width:50%; border:0;">
                        <div class="fw-bold mb-8">{{ __('messages.invoice_pdf_bill_from') }}</div>
                        <div class="mb-4">{{ optional($bookingdata->provider)->display_name ?? '-' }}</div>
                        @if(!empty(optional($bookingdata->provider)->company_name))
                            <div class="text-muted mb-4">{{ __('messages.company_name') }}: {{ $bookingdata->provider->company_name }}</div>
                        @endif
                        @if(!empty(optional($bookingdata->provider)->address))
                            <div class="text-muted mb-4">{{ __('messages.address') }}: {{ $bookingdata->provider->address }}</div>
                        @endif
                        @if(!empty(optional($bookingdata->provider)->vat_number))
                            <div class="text-muted">{{ __('messages.invoice_pdf_vat_number') }} {{ $bookingdata->provider->vat_number }}</div>
                        @endif
                    </td>
                    <td style="width:50%; border:0;">
                        <div class="fw-bold mb-8">{{ __('messages.invoice_pdf_bill_to') }}</div>
                        <div class="mb-4">{{ optional($bookingdata->customer)->display_name ?? '-' }}</div>
                        @if(!empty(optional($bookingdata->customer)->company_name))
                            <div class="text-muted mb-4">{{ __('messages.company_name') }}: {{ $bookingdata->customer->company_name }}</div>
                        @endif
                        @if(!empty(optional($bookingdata->customer)->address))
                            <div class="text-muted mb-4">{{ __('messages.address') }}: {{ $bookingdata->customer->address }}</div>
                        @endif
                        @if(!empty(optional($bookingdata->customer)->vat_number))
                            <div class="text-muted">{{ __('messages.invoice_pdf_vat_number') }} {{ $bookingdata->customer->vat_number }}</div>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td style="border:0; padding-top:12px;">
                        <span class="text-muted">{{ __('messages.service') }}:</span>
                        <strong>{{ optional($bookingdata->service)->name ?? '-' }}</strong>
                    </td>
                    <td style="border:0; padding-top:12px;">
                        <span class="text-muted">{{ __('messages.handyman') }}:</span>
                        <strong>{{ $handymanName }}</strong>
                    </td>
                </tr>
            </table>
        </div>

        {{-- ── Price Breakdown ── --}}
        <div class="section">
            <table class="breakdown" cellspacing="0" cellpadding="0">
                <tbody>
                    {{-- Service base --}}
                    <tr class="row-section"><td colspan="2">{{ __('messages.service') }}</td></tr>

                    <tr>
                        <td class="row-label">{{ __('messages.price_unit_price') }}</td>
                        <td class="row-value">{{ getPriceFormat($unitPrice) }}</td>
                    </tr>
                    <tr>
                        <td class="row-label">{{ __('messages.quantity_nbr_packages') }}</td>
                        <td class="row-value">{{ $quantity }}</td>
                    </tr>
                    <tr>
                        <td class="row-label">{{ __('messages.total_amount') }}</td>
                        <td class="row-value">{{ getPriceFormat($baseTotal) }}</td>
                    </tr>

                    {{-- Discounts --}}
                    @if ($discountAmount > 0)
                    <tr class="row-discount">
                        <td class="row-label">{{ __('messages.discount_percent_off', ['pct' => $bookingdata->discount]) }}</td>
                        <td class="row-value" style="color:#16a34a;">-{{ getPriceFormat($discountAmount) }}</td>
                    </tr>
                    @endif

                    @if ($couponAmount > 0)
                    <tr class="row-discount">
                        <td class="row-label">{{ __('messages.coupon') }}{{ optional($bookingdata->couponAdded)->code ? ' (' . $bookingdata->couponAdded->code . ')' : '' }}</td>
                        <td class="row-value" style="color:#16a34a;">-{{ getPriceFormat($couponAmount) }}</td>
                    </tr>
                    @endif

                    @if ($discountAmount > 0 || $couponAmount > 0)
                    <tr>
                        <td class="row-label" style="font-weight:600;">{{ __('messages.sub_total_after_discount') }}</td>
                        <td class="row-value">{{ getPriceFormat($subTotal) }}</td>
                    </tr>
                    @endif

                    {{-- Add-ons --}}
                    @if ($addonTotal > 0)
                    <tr>
                        <td class="row-label">{{ __('messages.service_addons') }}</td>
                        <td class="row-value">{{ getPriceFormat($addonTotal) }}</td>
                    </tr>
                    @endif

                    {{-- Extra charges --}}
                    @if ($extraChargeTotal > 0)
                    <tr>
                        <td class="row-label">{{ __('messages.extra_charges') }}</td>
                        <td class="row-value">{{ getPriceFormat($extraChargeTotal) }}</td>
                    </tr>
                    @endif

                    {{-- Totals section header --}}
                    <tr class="row-section"><td colspan="2">{{ __('messages.total') }}</td></tr>

                    <tr>
                        <td class="row-label">{{ __('messages.total') }}</td>
                        <td class="row-value">{{ getPriceFormat($totalBeforeTax) }}</td>
                    </tr>
                    <tr>
                        <td class="row-label">{{ __('messages.tax') }} ({{ $taxRate }}%)</td>
                        <td class="row-value" style="color:#dc2626;">{{ getPriceFormat($taxAmount) }}</td>
                    </tr>

                    {{-- Grand Total --}}
                    <tr class="row-grand">
                        <td>{{ __('messages.grand_total') }}</td>
                        <td class="text-right">{{ getPriceFormat($grandTotal) }}</td>
                    </tr>

                    {{-- Advance / Remaining --}}
                    @if ($isAdvanceEnabled)
                    <tr class="row-advance">
                        <td>{{ __('messages.pjr_advance_payment_line', ['pct' => $advancePercent]) }}</td>
                        <td class="text-right" style="font-weight:600;">{{ getPriceFormat($advancePaid) }}</td>
                    </tr>
                    <tr class="row-remaining">
                        <td>{{ __('messages.remaining_amount') }}</td>
                        <td class="text-right">{{ getPriceFormat($remainingAmount) }}</td>
                    </tr>
                    @endif

                    {{-- Payment Status --}}
                    <tr class="status-row" style="background:#f9fafb;">
                        <td style="color:#374151; font-weight:600;">{{ __('messages.payment_status') }}</td>
                        <td class="text-right">
                            <span class="badge {{ $statusBadgeClass }}">{{ $paymentStatusLabel }}</span>
                        </td>
                    </tr>

                </tbody>
            </table>

            <div class="footer">{{ __('messages.invoice_pdf_footer_copyright', ['year' => date('Y'), 'site' => preg_replace('/^www\./i', '', request()->getHost())]) }}</div>
        </div>

    </div>
</div>
</body>
</html>
