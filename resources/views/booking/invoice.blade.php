<!DOCTYPE html>
<html>
<head>
    <title>{{ env('APP_NAME') }}</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root {
            --text: #1f2937;
            --muted: #6b7280;
            --border: #e5e7eb;
            --bg: #ffffff;
            --primary: #0d6efd;
        }
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; background: #f7f8fa; color: var(--text); font: 400 14px/1.6 -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, "Apple Color Emoji", "Segoe UI Emoji"; }
        .invoice-container { max-width: 960px; margin: 32px auto; background: var(--bg); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; box-shadow: 0 2px 16px rgba(0,0,0,.04); }
        .section { padding: 24px; }
        .header { display: flex; align-items: center; justify-content: space-between; gap: 16px; border-bottom: 1px solid var(--border); }
        .brand { display: flex; align-items: center; gap: 12px; }
        .brand img { height: 48px; width: auto; display: block; }
        .brand .meta { display: grid; gap: 2px; }
        .brand .app-name { font-weight: 700; font-size: 18px; }
        .brand .contact { color: var(--muted); font-size: 12px; }
        .invoice-meta { text-align: right; display: grid; gap: 2px; }
        .invoice-title { font-weight: 700; font-size: 22px; letter-spacing: .3px; }
        .meta-line { color: var(--muted); font-size: 12px; }

        .grid { display: grid; gap: 24px; }
        .grid-2 { grid-template-columns: 1fr 1fr; }
        .panel { border: 1px solid var(--border); border-radius: 10px; padding: 16px; }
        .panel h4 { margin: 0 0 8px; font-size: 14px; letter-spacing: .4px; color: var(--muted); text-transform: uppercase; }
        .panel .line { margin: 2px 0; }

        table { width: 100%; border-collapse: collapse; }
        thead th { background: #fafafc; color: #111827; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: .3px; border-bottom: 1px solid var(--border); padding: 12px; text-align: left; }
        tbody td { border-bottom: 1px solid var(--border); padding: 12px; font-size: 14px; }
        tfoot td { padding: 8px 12px; font-size: 14px; }
        .text-right { text-align: right; }
        .text-muted { color: var(--muted); }
        .fw-600 { font-weight: 600; }
        .totals { width: 360px; margin-left: auto; border: 1px solid var(--border); border-radius: 10px; overflow: hidden; }
        .totals tr td:first-child { color: var(--muted); }
        .totals tr:last-child td { border-top: 1px solid var(--border); font-weight: 700; font-size: 16px; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 999px; background: #e8f0ff; color: var(--primary); font-size: 12px; font-weight: 600; }

        .note { margin-top: 12px; color: var(--muted); font-size: 12px; }
        .footer { margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--border); color: var(--muted); text-align: center; font-size: 12px; }

        @media print {
            html, body { background: #fff; }
            .invoice-container { margin: 0; border: 0; border-radius: 0; box-shadow: none; }
            .section { padding: 16px 24px; }
            .no-print { display: none !important; }
            tr, td, th { page-break-inside: avoid; }
        }
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

    $unitPrice = $bookingdata->amount;
    $quantity = $bookingdata->quantity;
    $baseTotal = $unitPrice * $quantity;

    $discountAmount = $bookingdata->discount > 0 ? $bookingdata->final_discount_amount : 0;
    $couponAmount = $bookingdata->couponAdded ? $bookingdata->final_coupon_discount_amount : 0;

    $subTotal = $baseTotal - $discountAmount - $couponAmount;

    $addonTotal = $bookingdata->bookingAddonService->sum('price');
    $extraChargeTotal = $bookingdata->bookingExtraCharge->sum(fn($item) => $item->price * $item->qty);

    $totalBeforeTax = $subTotal + $addonTotal + $extraChargeTotal;

    $taxRate = 0;
    if (!empty($bookingdata->service->tax_country_id)) {
        $taxModel = \App\Models\Tax::find($bookingdata->service->tax_country_id);
        $taxRate = $taxModel->value ?? 0;
    }

    $taxAmount = ($totalBeforeTax * $taxRate) / 100;
    $grandTotal = $totalBeforeTax + $taxAmount;

    $advancePaid = $bookingdata->advance_paid_amount;
    $remainingAmount = $grandTotal - $advancePaid;
@endphp
<body>
<div class="invoice-container">
    <div class="section header">
        <div class="brand">
            <img src="{{ asset('assets/frobster logo.png') }}" alt="logo">
            <div class="meta">
                <div class="app-name">{{ env('APP_NAME') }}</div>
                <div class="contact">
                    {{ $generaldata->inquriy_email ?? '' }}
                    @if(!empty($generaldata->helpline_number)) • {{ $generaldata->helpline_number }} @endif
                </div>
            </div>
        </div>
        <div class="invoice-meta">
            <div class="invoice-title">{{ __('Invoice') }}</div>
            <div class="meta-line">{{ __('Invoice No:') }} #{{ $bookingdata->id }}</div>
            <div class="meta-line">{{ __('Currency:') }} {{ $bookingdata->currency ?? 'EUR' }}</div>
            <div class="meta-line">{{ __('Date Issued:') }} {{ $bookingdata->created_at->format('d M Y') }}</div>
        </div>
    </div>

    <div class="section">
        <div class="grid grid-2">
            <div class="panel">
                <h4>{{ __('Bill From') }}</h4>
                <div class="line fw-600">{{ optional($bookingdata->provider)->display_name ?? '-' }}</div>
                <div class="line">{{ optional($bookingdata->provider)->address ?? '-' }}</div>
                <div class="line">{{ __('VAT Number:') }} {{ optional($bookingdata->provider)->vat_number ?? '-' }}</div>
            </div>
            <div class="panel">
                <h4>{{ __('Bill To') }}</h4>
                <div class="line fw-600">{{ optional($bookingdata->customer)->display_name ?? '-' }}</div>
                <div class="line">{{ optional($bookingdata->customer)->address ?? '-' }}</div>
            </div>
        </div>
        <div class="panel" style="margin-top: 16px;">
            <h4>{{ __('Service') }}</h4>
            <div class="line">{{ optional($bookingdata->service)->name ?? '-' }}</div>
        </div>
    </div>

    <div class="section" style="padding-top: 0;">
        <table>
            <thead>
            <tr>
                <th style="width: 60%">{{ __('Description') }}</th>
                <th class="text-right" style="width: 10%">{{ __('Qty') }}</th>
                <th class="text-right" style="width: 15%">{{ __('Unit Price') }}</th>
                <th class="text-right" style="width: 15%">{{ __('Amount') }}</th>
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
    </div>

    <div class="section" style="padding-top: 8px;">
        <table class="totals">
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
                    <td>{{ __('Coupon') }} {{ $bookingdata->couponAdded->code ? '(' . $bookingdata->couponAdded->code . ')' : '' }}</td>
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
        <div class="note">{{ __('Thank you for your business.') }}</div>
        <div class="footer">{{ $app->site_copyright ?? '' }}</div>
    </div>
</div>
</body>
</html>
