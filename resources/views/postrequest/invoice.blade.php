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
    // Inputs: $bid (App\Models\PostJobBid), optional $payment (PaymentPostJOb)

    $unitPrice = (float) ($bid->price ?? 0);
    $extraChargeUnit = (float) ($bid->extra_charges ?? 0);
    $extraChargeQty = (int) ($bid->quantity ?? 1);

    // Determine quantity based on price type
    if (($bid->postrequest->price_type ?? $bid->postrequest->job_price ?? 'fixed') === 'hourly') {
        $quantity = (float) ($bid->postrequest->total_hours ?? 1);
    } elseif (($bid->postrequest->price_type ?? $bid->postrequest->job_price ?? 'fixed') === 'daily') {
        $quantity = (float) ($bid->postrequest->total_days ?? 1);
    } else {
        $quantity = 1;
    }

    $baseTotal = $unitPrice * $quantity;

    $extraChargeTotal = $extraChargeUnit * $extraChargeQty;

    $subTotal = $baseTotal + $extraChargeTotal;

    $taxRate = 0;
    $taxTitle = '';
    $countryId = $bid->postrequest->country_id ?? null;
    if ($countryId) {
        $taxModel = \App\Models\Tax::find($countryId);
        $taxRate = (float) ($taxModel->value ?? 0);
        $taxTitle = (string) ($taxModel->title ?? '');
    }
    $taxAmount = ($subTotal * $taxRate) / 100;
    $grandTotal = $subTotal + $taxAmount;

    $advancePercent = (float) ($bid->advance_percent ?? 0);
    $advancePaid = ($advancePercent > 0) ? ($baseTotal * $advancePercent / 100.0) : 0;
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
                        <div class="text-muted">{{ __('Invoice No:') }} #{{ $bid->id }}</div>
                        <div class="text-muted">{{ __('Currency:') }} {{ $bid->currency ?? 'EUR' }}</div>
                        <div class="text-muted">{{ __('Date Issued:') }} {{ optional($bid->created_at)->format('d M Y') }}</div>
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
                        <div class="mb-6">{{ optional($bid->provider)->display_name ?? '-' }}</div>
                        <div class="text-muted">{{ optional($bid->provider)->address ?? '-' }}</div>
                        <div class="text-muted">{{ __('VAT Number:') }} {{ optional($bid->provider)->vat_number ?? '-' }}</div>
                    </td>
                    <td style="width: 50%; border: 0;">
                        <div class="fw-bold mb-6">{{ __('Bill To') }}</div>
                        <div class="mb-6">{{ optional($bid->customer)->display_name ?? '-' }}</div>
                        <div class="text-muted">{{ optional($bid->customer)->address ?? '-' }}</div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="border: 0;">
                        <div class="fw-bold mb-6">{{ __('Job Request') }}</div>
                        <div class="text-muted">{{ optional($bid->postrequest)->title ?? '-' }}</div>
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
                        <td>{{ optional($bid->postrequest)->title ?? '-' }} ({{ strtoupper(optional($bid->postrequest)->price_type ?? optional($bid->postrequest)->job_price ?? 'fixed') }})</td>
                        <td class="text-right">{{ $quantity }}</td>
                        <td class="text-right">{{ getPriceFormat($unitPrice) }}</td>
                        <td class="text-right">{{ getPriceFormat($baseTotal) }}</td>
                    </tr>
                    @if ($extraChargeTotal > 0)
                    <tr>
                        <td>{{ __('Extra Charges') }}</td>
                        <td class="text-right">{{ $extraChargeQty }}</td>
                        <td class="text-right">{{ getPriceFormat($extraChargeUnit) }}</td>
                        <td class="text-right">{{ getPriceFormat($extraChargeTotal) }}</td>
                    </tr>
                    @endif
                </tbody>
            </table>

            <table class="totals" cellspacing="0" cellpadding="0" style="margin-top: 16px;">
                <tbody>
                    <tr>
                        <td>{{ __('Sub Total') }}</td>
                        <td class="text-right">{{ getPriceFormat($subTotal) }}</td>
                    </tr>
                    <tr>
                        <td>{{ __('Tax') }} ({{ $taxRate }}%) {{ $taxTitle }}</td>
                        <td class="text-right">{{ getPriceFormat($taxAmount) }}</td>
                    </tr>
                    <tr>
                        <td>{{ __('Grand Total') }}</td>
                        <td class="text-right">{{ getPriceFormat($grandTotal) }}</td>
                    </tr>
                    @if ($advancePercent > 0)
                    <tr>
                        <td>{{ __('Advance Payment') }} ({{ number_format($advancePercent, 0) }}%)</td>
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