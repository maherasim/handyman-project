<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <title>{{ env('APP_NAME') }} — Invoice</title>
    <meta charset="utf-8">
    <style type="text/css">
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: #ffffff;
            color: #1a202c;
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            font-size: 12.5px;
            line-height: 1.6;
        }

        /* ── Page wrapper ── */
        .page { width: 100%; max-width: 860px; margin: 0 auto; }

        /* ── Header band ── */
        .header {
            background: #1a2b4a;
            color: #ffffff;
            padding: 28px 32px;
        }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-table td { border: none; vertical-align: middle; }
        .header-left { width: 55%; }
        .header-right { width: 45%; text-align: right; }
        .invoice-word {
            font-size: 30px;
            font-weight: 700;
            letter-spacing: 2px;
            color: #ffffff;
            text-transform: uppercase;
        }
        .invoice-meta { margin-top: 6px; color: #93a8c9; font-size: 12px; line-height: 1.8; }
        .invoice-meta span { color: #ffffff; font-weight: 600; }
        .logo-img { height: 52px; width: auto; }
        .contact-info { color: #93a8c9; font-size: 11.5px; margin-top: 8px; line-height: 1.7; }

        /* ── Accent stripe ── */
        .accent-bar { background: #c0392b; height: 4px; }

        /* ── Status badge row ── */
        .status-row {
            background: #f7f8fc;
            padding: 10px 32px;
            border-bottom: 1px solid #e2e8f0;
        }
        .badge {
            display: inline-block;
            padding: 3px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
        }
        .badge-paid    { background: #d4edda; color: #155724; }
        .badge-pending { background: #fff3cd; color: #856404; }
        .badge-partial { background: #cce5ff; color: #004085; }
        .badge-default { background: #e2e3e5; color: #383d41; }

        /* ── Body section ── */
        .body { padding: 28px 32px; }

        /* ── Bill From / To ── */
        .billing-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        .billing-table td { border: none; vertical-align: top; width: 50%; }
        .billing-box {
            background: #f7f8fc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 14px 16px;
            margin-right: 8px;
        }
        .billing-box-right { margin-right: 0; margin-left: 8px; }
        .billing-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .8px;
            color: #c0392b;
            font-weight: 700;
            margin-bottom: 6px;
            padding-bottom: 5px;
            border-bottom: 1px solid #e2e8f0;
        }
        .billing-name { font-weight: 700; font-size: 13px; color: #1a202c; margin-bottom: 3px; }
        .billing-detail { color: #718096; font-size: 11.5px; line-height: 1.7; }

        /* ── Service label ── */
        .section-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .8px;
            color: #c0392b;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .service-name {
            font-weight: 600;
            color: #1a202c;
            margin-bottom: 18px;
            padding: 8px 12px;
            background: #f7f8fc;
            border-left: 3px solid #c0392b;
            border-radius: 0 4px 4px 0;
        }

        /* ── Line items table ── */
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .items-table thead tr { background: #1a2b4a; color: #ffffff; }
        .items-table thead th {
            padding: 10px 12px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .5px;
            font-weight: 600;
            text-align: left;
        }
        .items-table thead th.text-right { text-align: right; }
        .items-table tbody tr { border-bottom: 1px solid #e2e8f0; }
        .items-table tbody tr:nth-child(even) { background: #f7f8fc; }
        .items-table tbody td { padding: 10px 12px; vertical-align: top; }
        .items-table tfoot td {
            padding: 8px 12px;
            font-size: 11.5px;
            border-bottom: 1px solid #e2e8f0;
        }
        .text-right { text-align: right; }
        .text-muted  { color: #718096; }

        /* ── Totals box ── */
        .totals-wrap { width: 300px; margin-left: auto; margin-top: 4px; }
        .totals-table { width: 100%; border-collapse: collapse; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden; }
        .totals-table td { padding: 9px 14px; border-bottom: 1px solid #e2e8f0; font-size: 12px; }
        .totals-table td.text-right { text-align: right; }
        .totals-table .row-discount td { color: #c0392b; }
        .totals-table .row-grand { background: #1a2b4a; color: #ffffff; font-weight: 700; font-size: 13.5px; }
        .totals-table .row-grand td { border-bottom: none; }
        .totals-table .row-advance td { color: #27ae60; font-weight: 600; }
        .totals-table .row-remaining td { font-weight: 700; }

        /* ── Payment method line ── */
        .payment-line { margin-top: 20px; padding: 10px 14px; background: #f7f8fc; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 12px; }
        .payment-line span { font-weight: 600; }

        /* ── Footer ── */
        .footer {
            margin-top: 32px;
            padding-top: 14px;
            border-top: 2px solid #e2e8f0;
            text-align: center;
            color: #a0aec0;
            font-size: 11px;
            line-height: 1.7;
        }
        .footer strong { color: #718096; }

        /* ── Divider ── */
        .divider { height: 1px; background: #e2e8f0; margin: 18px 0; }
    </style>
</head>
<?php
use App\Models\Setting;
$settings = Setting::whereIn('type', ['site-setup', 'general-setting'])
    ->whereIn('key', ['site-setup', 'general-setting'])
    ->get()
    ->keyBy('key');

$app         = isset($settings['site-setup'])     ? json_decode($settings['site-setup']->value)     : null;
$generaldata = isset($settings['general-setting']) ? json_decode($settings['general-setting']->value) : null;
$logoPath    = public_path('assets/frobster logo.png');
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

    $unitPrice    = (float) ($bookingdata->amount ?? 0);
    $quantity     = (int)   ($bookingdata->quantity ?? 1);
    $baseTotal    = $unitPrice * $quantity;

    $discountAmount = ($bookingdata->discount ?? 0) > 0 ? (float) ($bookingdata->final_discount_amount ?? 0) : 0;
    $couponAmount   = $bookingdata->couponAdded ? (float) ($bookingdata->final_coupon_discount_amount ?? 0) : 0;
    $subTotal       = $baseTotal - $discountAmount - $couponAmount;

    $addonTotal      = (float) ($bookingdata->bookingAddonService->sum('price') ?? 0);
    $extraChargeTotal = 0;
    foreach ($bookingdata->bookingExtraCharge as $item) {
        $extraChargeTotal += (float) ($item->price ?? 0) * (int) ($item->qty ?? 0);
    }

    $totalBeforeTax = $subTotal + $addonTotal + $extraChargeTotal;

    $taxRate = 0;
    if (!empty(optional($bookingdata->service)->tax_country_id)) {
        $taxModel = \App\Models\Tax::find(optional($bookingdata->service)->tax_country_id);
        $taxRate  = $taxModel->value ?? 0;
    }

    $taxAmount    = ($totalBeforeTax * $taxRate) / 100;
    $grandTotal   = $totalBeforeTax + $taxAmount;
    $advancePaid  = (float) ($bookingdata->advance_paid_amount ?? 0);
    $remaining    = $grandTotal - $advancePaid;

    $invoiceDate = $bookingdata->created_at
        ? \Carbon\Carbon::parse($bookingdata->created_at)->locale(app()->getLocale())->translatedFormat('d F Y')
        : '';

    // Status badge
    $payStatus   = optional($payment)->payment_status ?? $bookingdata->payment_status ?? '';
    $badgeClass  = match(strtolower($payStatus)) {
        'paid'           => 'badge-paid',
        'advanced_paid'  => 'badge-partial',
        'pending'        => 'badge-pending',
        default          => 'badge-default',
    };
    $badgeLabel  = match(strtolower($payStatus)) {
        'paid'           => __('messages.paid'),
        'advanced_paid'  => __('messages.advance_payment'),
        'pending'        => __('messages.pending'),
        default          => ucwords(str_replace('_', ' ', $payStatus)) ?: __('messages.pending'),
    };

    // Payment method human label
    $rawMethod   = optional($payment)->payment_type ?? '';
    $methodLabel = match(strtolower($rawMethod)) {
        'stripe'        => 'Stripe',
        'paypal'        => 'PayPal',
        'wallet'        => __('messages.wallet'),
        'bank_transfer' => __('messages.bank_transfer'),
        'cash'          => __('messages.cash'),
        default         => ucwords(str_replace('_', ' ', $rawMethod)),
    };
@endphp
<body>
<div class="page">

    {{-- ── Header ── --}}
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="header-left">
                    <div class="invoice-word">{{ __('messages.invoice') }}</div>
                    <div class="invoice-meta">
                        {{ __('messages.invoice_pdf_invoice_no') }}&nbsp;<span>#{{ $bookingdata->id }}</span><br>
                        {{ __('messages.invoice_pdf_date_issued') }}&nbsp;<span>{{ $invoiceDate }}</span><br>
                        {{ __('messages.invoice_pdf_currency') }}&nbsp;<span>{{ $bookingdata->currency ?? 'USD' }}</span>
                    </div>
                </td>
                <td class="header-right">
                    @if (file_exists($logoPath))
                        <img src="{{ $logoPath }}" alt="logo" class="logo-img">
                    @else
                        <div style="font-size:20px; font-weight:700; color:#fff;">{{ env('APP_NAME') }}</div>
                    @endif
                    <div class="contact-info">
                        @if(!empty($generaldata->inquriy_email)){{ $generaldata->inquriy_email }}<br>@endif
                        @if(!empty($generaldata->helpline_number)){{ $generaldata->helpline_number }}<br>@endif
                        @if(!empty($app->site_name)){{ $app->site_name }}@endif
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ── Red accent stripe ── --}}
    <div class="accent-bar"></div>

    {{-- ── Payment status badge row ── --}}
    @if($payStatus)
    <div class="status-row">
        <span class="badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
        @if($rawMethod)
            &nbsp;&nbsp;<span style="color:#718096; font-size:11.5px;">{{ __('messages.payment_method') }}: <strong>{{ $methodLabel }}</strong></span>
        @endif
    </div>
    @endif

    {{-- ── Body ── --}}
    <div class="body">

        {{-- ── Bill From / To ── --}}
        <table class="billing-table">
            <tr>
                <td>
                    <div class="billing-box">
                        <div class="billing-label">{{ __('messages.invoice_pdf_bill_from') }}</div>
                        <div class="billing-name">{{ optional($bookingdata->provider)->display_name ?? '-' }}</div>
                        <div class="billing-detail">
                            @if(!empty(optional($bookingdata->provider)->company_name))
                                {{ optional($bookingdata->provider)->company_name }}<br>
                            @endif
                            @if(!empty(optional($bookingdata->provider)->address))
                                {{ optional($bookingdata->provider)->address }}<br>
                            @endif
                            @if(!empty(optional($bookingdata->provider)->vat_number))
                                {{ __('messages.invoice_pdf_vat_number') }} {{ optional($bookingdata->provider)->vat_number }}
                            @endif
                        </div>
                    </div>
                </td>
                <td>
                    <div class="billing-box billing-box-right">
                        <div class="billing-label">{{ __('messages.invoice_pdf_bill_to') }}</div>
                        <div class="billing-name">{{ optional($bookingdata->customer)->display_name ?? '-' }}</div>
                        <div class="billing-detail">
                            @if(!empty(optional($bookingdata->customer)->company_name))
                                {{ optional($bookingdata->customer)->company_name }}<br>
                            @endif
                            @if(!empty(optional($bookingdata->customer)->address))
                                {{ optional($bookingdata->customer)->address }}<br>
                            @endif
                            @if(!empty(optional($bookingdata->customer)->vat_number))
                                {{ __('messages.invoice_pdf_vat_number') }} {{ optional($bookingdata->customer)->vat_number }}
                            @endif
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        {{-- ── Service ── --}}
        <div class="section-label">{{ __('messages.service') }}</div>
        <div class="service-name">{{ optional($bookingdata->service)->name ?? '-' }}</div>

        {{-- ── Line items ── --}}
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width:55%; border-radius:4px 0 0 0;">{{ __('messages.description') }}</th>
                    <th class="text-right" style="width:10%;">{{ __('messages.Qty') }}</th>
                    <th class="text-right" style="width:17.5%;">{{ __('messages.invoice_pdf_unit_price') }}</th>
                    <th class="text-right" style="width:17.5%; border-radius:0 4px 0 0;">{{ __('messages.amount') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong>{{ optional($bookingdata->service)->name ?? '-' }}</strong>
                        @if(optional($bookingdata->service)->description)
                            <div class="text-muted" style="font-size:11px; margin-top:2px;">{{ Str::limit(optional($bookingdata->service)->description, 80) }}</div>
                        @endif
                    </td>
                    <td class="text-right">{{ $quantity }}</td>
                    <td class="text-right">{{ getPriceFormat($unitPrice) }}</td>
                    <td class="text-right">{{ getPriceFormat($baseTotal) }}</td>
                </tr>

                @foreach($bookingdata->bookingAddonService as $addon)
                <tr>
                    <td class="text-muted" style="padding-left:24px;">↳ {{ $addon->name ?? __('messages.service_addons') }}</td>
                    <td class="text-right">1</td>
                    <td class="text-right">{{ getPriceFormat((float)($addon->price ?? 0)) }}</td>
                    <td class="text-right">{{ getPriceFormat((float)($addon->price ?? 0)) }}</td>
                </tr>
                @endforeach

                @foreach($bookingdata->bookingExtraCharge as $charge)
                <tr>
                    <td class="text-muted" style="padding-left:24px;">↳ {{ $charge->title ?? __('messages.extra_charge') }}</td>
                    <td class="text-right">{{ (int)($charge->qty ?? 1) }}</td>
                    <td class="text-right">{{ getPriceFormat((float)($charge->price ?? 0)) }}</td>
                    <td class="text-right">{{ getPriceFormat((float)($charge->price ?? 0) * (int)($charge->qty ?? 1)) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- ── Totals ── --}}
        <div class="totals-wrap">
            <table class="totals-table">
                <tbody>
                    <tr>
                        <td>{{ __('messages.Sub_Total') }}</td>
                        <td class="text-right">{{ getPriceFormat($baseTotal) }}</td>
                    </tr>
                    @if ($discountAmount > 0)
                    <tr class="row-discount">
                        <td>{{ __('messages.discount') }} ({{ $bookingdata->discount }}%)</td>
                        <td class="text-right">− {{ getPriceFormat($discountAmount) }}</td>
                    </tr>
                    @endif
                    @if ($couponAmount > 0)
                    <tr class="row-discount">
                        <td>{{ __('messages.coupon') }}{{ optional($bookingdata->couponAdded)->code ? ' (' . optional($bookingdata->couponAdded)->code . ')' : '' }}</td>
                        <td class="text-right">− {{ getPriceFormat($couponAmount) }}</td>
                    </tr>
                    @endif
                    @if ($taxRate > 0)
                    <tr>
                        <td>{{ __('messages.tax') }} ({{ $taxRate }}%)</td>
                        <td class="text-right">{{ getPriceFormat($taxAmount) }}</td>
                    </tr>
                    @endif
                    <tr class="row-grand">
                        <td>{{ __('messages.grand_total') }}</td>
                        <td class="text-right">{{ getPriceFormat($grandTotal) }}</td>
                    </tr>
                    @if ($showAdvance && $advancePaid > 0)
                    <tr class="row-advance">
                        <td>
                            @if(optional($bookingdata->service)->is_enable_advance_payment == 1 && (float)(optional($bookingdata->service)->advance_payment_amount ?? 0) > 0)
                                {{ __('messages.pjr_advance_payment_line', ['pct' => $bookingdata->service->advance_payment_amount]) }}
                            @else
                                {{ __('messages.advance_payment') }}
                            @endif
                        </td>
                        <td class="text-right">{{ getPriceFormat($advancePaid) }}</td>
                    </tr>
                    <tr class="row-remaining">
                        <td>{{ __('messages.remaining_amount') }}</td>
                        <td class="text-right">{{ getPriceFormat($remaining) }}</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>

        {{-- ── Footer ── --}}
        <div class="footer">
            <strong>{{ $app->site_name ?? env('APP_NAME') }}</strong><br>
            {{ __('messages.invoice_pdf_footer_copyright', ['year' => date('Y'), 'site' => preg_replace('/^www\./i', '', request()->getHost())]) }}
        </div>

    </div>{{-- /body --}}
</div>{{-- /page --}}
</body>
</html>
