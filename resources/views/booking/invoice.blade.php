<!DOCTYPE html>
<html>
<head>
    <title>{{ env('APP_NAME') }}</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style type="text/css">
        body { margin: 0; padding: 0; background: #ffffff; color: #222; font-family: DejaVu Sans, Helvetica, Arial, sans-serif; font-size: 13px; line-height: 1.5; }
        .container { width: 100%; max-width: 900px; margin: 0 auto; padding: 24px; }
        .card { border: 1px solid #e5e7eb; border-radius: 8px; }
        .section { padding: 18px 18px; }
        .border-b { border-bottom: 1px solid #e5e7eb; }
        .mb-12 { margin-bottom: 12px; }
        .mb-6 { margin-bottom: 6px; }
        .text-right { text-align: right; }
        .text-muted { color: #6b7280; }
        .fw-bold { font-weight: 700; }
        .title { font-size: 20px; font-weight: 700; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
        thead th { background: #fafafa; font-size: 12px; text-transform: uppercase; letter-spacing: .3px; }
        .no-border td { border: 0; }
        .totals { width: 360px; margin-left: auto; border: 1px solid #e5e7eb; border-radius: 6px; }
        .totals td { border-bottom: 1px solid #e5e7eb; }
        .totals tr:last-child td { border-bottom: 0; font-weight: 700; font-size: 15px; }
        .footer { margin-top: 16px; padding-top: 10px; border-top: 1px solid #e5e7eb; text-align: center; color: #6b7280; font-size: 12px; }
    </style>
</head>
<?php
use App\Models\Setting;
$settings = Setting::whereIn('type', ['site-setup', 'general-setting'])
    ->whereIn('key', ['site-setup', 'general-setting'])
    ->get()
    ->keyBy('key');

$app = isset($settings['site-setup']) ? json_decode($settings['site-setup']->value) : null;
$generaldata = isset($settings['general-setting']) ? json_decode($settings['general-setting']->value) : null;
$logoPath = public_path('assets/frobster logo.png');
?>
@php
    $showAdvance = false;
    if (isset($payment)) {
        if ($payment->payment_type === 'bank_transfer' && $payment->status == 1) {
            $showAdvance = true;
        } elseif ($payment->payment_type !== 'bank_transfer') {
            $showAdvance = true;
        }
    }

    $unitPrice = (float) ($bookingdata->amount ?? 0);
    $quantity = (int) ($bookingdata->quantity ?? 1);
    $baseTotal = $unitPrice * $quantity;

    $discountAmount = ($bookingdata->discount ?? 0) > 0 ? (float) ($bookingdata->final_discount_amount ?? 0) : 0;
    $couponAmount = $bookingdata->couponAdded ? (float) ($bookingdata->final_coupon_discount_amount ?? 0) : 0;

    $subTotal = $baseTotal - $discountAmount - $couponAmount;

    $addonTotal = (float) ($bookingdata->bookingAddonService->sum('price') ?? 0);
    $extraChargeTotal = 0;
    foreach ($bookingdata->bookingExtraCharge as $item) {
        $extraChargeTotal += (float) ($item->price ?? 0) * (int) ($item->qty ?? 0);
    }

    $totalBeforeTax = $subTotal + $addonTotal + $extraChargeTotal;

    $taxRate = 0;
    if (!empty(optional($bookingdata->service)->tax_country_id)) {
        $taxModel = \App\Models\Tax::find(optional($bookingdata->service)->tax_country_id);
        $taxRate = $taxModel->value ?? 0;
    }

    $taxAmount = ($totalBeforeTax * $taxRate) / 100;
    $grandTotal = $totalBeforeTax + $taxAmount;

    $advancePaid = (float) ($bookingdata->advance_paid_amount ?? 0);
    $remainingAmount = $grandTotal - $advancePaid;
@endphp
<body>
<div class="container">
    <div class="card">
        <div class="section border-b">
            <table class="no-border">
                <tr>
                    <td style="width: 60%; border: 0;">
                        <div class="title">{{ __('Invoice') }}</div>
                        <div class="text-muted">{{ __('Invoice No:') }} #{{ $bookingdata->id }}</div>
                        <div class="text-muted">{{ __('Currency:') }} {{ $bookingdata->currency ?? 'EUR' }}</div>
                        <div class="text-muted">{{ __('Date Issued:') }} {{ optional($bookingdata->created_at)->format('d M Y') }}</div>
                    </td>
                    <td class="text-right" style="width: 40%; border: 0;">
                        @if (file_exists($logoPath))
                            <img src="{{ $logoPath }}" alt="logo" style="height: 56px; width: auto;">
                        @endif
                        <div class="text-muted">{{ $generaldata->inquriy_email ?? '' }}</div>
                        @if(!empty($generaldata->helpline_number))
                            <div class="text-muted">{{ $generaldata->helpline_number }}</div>
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <div class="section border-b">
            <table class="no-border">
                <tr>
                    <td style="width: 50%; border: 0;">
                        <div class="fw-bold mb-6">{{ __('Bill From') }}</div>
                        <div class="mb-6">{{ optional($bookingdata->provider)->display_name ?? '-' }}</div>
                        <div class="text-muted">{{ optional($bookingdata->provider)->company_name ?? '-' }}</div>

                        <div class="text-muted">{{ optional($bookingdata->provider)->address ?? '-' }}</div>
                        <div class="text-muted">{{ __('VAT Number:') }} {{ optional($bookingdata->provider)->vat_number ?? '-' }}</div>
                    </td>
                    <td style="width: 50%; border: 0;">
                        <div class="fw-bold mb-6">{{ __('Bill To') }}</div>
                        <div class="mb-6">{{ optional($bookingdata->customer)->display_name ?? '-' }}</div>
                        <div class="mb-6">{{ optional($bookingdata->customer)->company_name ?? '-' }}</div>
                        <div class="text-muted">{{ __('Addres:') }}{{ optional($bookingdata->customer)->address ?? '-' }}</div>
                        <div class="text-muted">{{ __('VAT Number:') }}{{ optional($bookingdata->customer)->vat_number ?? '-' }}</div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="border: 0;">
                        <div class="fw-bold mb-6">{{ __('Service') }}</div>
                        <div class="text-muted">{{ optional($bookingdata->service)->name ?? '-' }}</div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="section">
            <table>
                <thead>
                    <tr>
                        <th style="width: 60%">{{ __('Description') }}</th>
                        <th style="width: 10%" class="text-right">{{ __('Qty') }}</th>
                        <th style="width: 15%" class="text-right">{{ __('Unit Price') }}</th>
                        <th style="width: 15%" class="text-right">{{ __('Amount') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ optional($bookingdata->service)->name ?? '-' }}</td>
                        <td class="text-right">{{ $quantity }}</td>
                        <td class="text-right">{{ getPriceFormat($unitPrice) }}</td>
                        <td class="text-right">{{ getPriceFormat($baseTotal) }}</td>
                    </tr>
                </tbody>
            </table>

            <table class="totals" cellspacing="0" cellpadding="0" style="margin-top: 16px;">
                <tbody>
                    <tr>
                        <td>{{ __('Sub Total') }}</td>
                        <td class="text-right">{{ getPriceFormat($baseTotal) }}</td>
                    </tr>
                    @if ($discountAmount > 0)
                    <tr>
                        <td>{{ __('Discount') }} ({{ $bookingdata->discount }}%)</td>
                        <td class="text-right">-{{ getPriceFormat($discountAmount) }}</td>
                    </tr>
                    @endif
                    @if ($couponAmount > 0)
                    <tr>
                        <td>{{ __('Coupon') }} {{ optional($bookingdata->couponAdded)->code ? '(' . optional($bookingdata->couponAdded)->code . ')' : '' }}</td>
                        <td class="text-right">-{{ getPriceFormat($couponAmount) }}</td>
                    </tr>
                    @endif
                    @if ($addonTotal > 0)
                    <tr>
                        <td>{{ __('Service Addons') }}</td>
                        <td class="text-right">{{ getPriceFormat($addonTotal) }}</td>
                    </tr>
                    @endif
                    @if ($extraChargeTotal > 0)
                    <tr>
                        <td>{{ __('Extra Charges') }}</td>
                        <td class="text-right">{{ getPriceFormat($extraChargeTotal) }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td>{{ __('Total') }}</td>
                        <td class="text-right">{{ getPriceFormat($totalBeforeTax) }}</td>
                    </tr>
                    <tr>
                        <td>{{ __('Tax') }} ({{ $taxRate }}%)</td>
                        <td class="text-right">{{ getPriceFormat($taxAmount) }}</td>
                    </tr>
                    <tr>
                        <td>{{ __('Grand Total') }}</td>
                        <td class="text-right">{{ getPriceFormat($grandTotal) }}</td>
                    </tr>
                    @if ($showAdvance)
                    <tr>
                        <td>{{ __('Advance Payment') }}</td>
                        <td class="text-right">{{ getPriceFormat($advancePaid) }}</td>
                    </tr>
                    <tr>
                        <td>{{ __('Remaining Amount') }}</td>
                        <td class="text-right">{{ getPriceFormat($remainingAmount) }}</td>
                    </tr>
                    @endif
                </tbody>
            </table>

            <div class="footer">{{ $app->site_copyright ?? '' }}</div>
        </div>
    </div>
</div>
</body>
</html>