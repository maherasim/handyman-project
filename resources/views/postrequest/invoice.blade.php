<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<title>{{ __('Invoice') }} — {{ env('APP_NAME') }}</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<style type="text/css">
		@page { size: A4; margin: 10mm; }
		* { box-sizing: border-box; }
		body {
			margin: 0;
			padding: 0;
			background: #f8f9fa;
			color: #1a1a1a;
			font-family: 'DejaVu Sans', 'Segoe UI', Helvetica, Arial, sans-serif;
			font-size: 12px;
			line-height: 1.35;
			-webkit-print-color-adjust: exact;
			print-color-adjust: exact;
		}
		@media print {
			body { background: #fff; }
			.invoice-paper { box-shadow: none !important; page-break-inside: avoid; }
			.no-print { display: none !important; }
			.invoice-wrap { padding: 0 8px 8px !important; }
		}
		.invoice-wrap {
			max-width: 800px;
			margin: 0 auto;
			padding: 16px 12px;
		}
		.invoice-paper {
			background: #fff;
			border-radius: 8px;
			box-shadow: 0 2px 12px rgba(0,0,0,.06);
			overflow: hidden;
		}
		/* Header */
		.invoice-header {
			display: table;
			width: 100%;
			padding: 14px 20px 10px;
			background: #fff;
			border-bottom: 3px solid #1e3a5f;
		}
		.invoice-header-left { display: table-cell; width: 55%; vertical-align: top; }
		.invoice-header-right { display: table-cell; width: 45%; text-align: right; vertical-align: top; }
		.invoice-label {
			font-size: 10px;
			font-weight: 700;
			text-transform: uppercase;
			letter-spacing: 1px;
			color: #1e3a5f;
			margin-bottom: 2px;
		}
		.invoice-title {
			font-size: 22px;
			font-weight: 700;
			color: #1a1a1a;
			letter-spacing: -0.5px;
			margin: 0 0 6px 0;
		}
		.invoice-meta {
			font-size: 11px;
			color: #5c6370;
			line-height: 1.4;
		}
		.invoice-meta span { display: block; }
		.logo { height: 36px; width: auto; max-width: 160px; object-fit: contain; margin-bottom: 4px; }
		.invoice-header-right .invoice-meta { text-align: right; }
		/* Parties */
		.invoice-parties {
			display: table;
			width: 100%;
			padding: 12px 20px;
			border-bottom: 1px solid #e8eaed;
		}
		.party-block { display: table-cell; width: 50%; vertical-align: top; padding-right: 16px; }
		.party-block:last-child { padding-right: 0; padding-left: 16px; border-left: 1px solid #e8eaed; }
		.party-label {
			font-size: 9px;
			font-weight: 700;
			text-transform: uppercase;
			letter-spacing: 0.6px;
			color: #5c6370;
			margin-bottom: 4px;
		}
		.party-name { font-size: 13px; font-weight: 600; color: #1a1a1a; margin-bottom: 4px; }
		.party-detail { font-size: 11px; color: #5c6370; line-height: 1.4; }
		.job-ref-block { padding: 8px 20px; background: #f8f9fb; border-bottom: 1px solid #e8eaed; }
		.job-ref-label { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; color: #5c6370; margin-bottom: 2px; }
		.job-ref-title { font-size: 12px; font-weight: 600; color: #1a1a1a; }
		/* Line items */
		.invoice-items-wrap { padding: 12px 20px 14px; }
		.items-table {
			width: 100%;
			border-collapse: collapse;
			font-size: 11px;
		}
		.items-table thead th {
			text-align: left;
			padding: 8px 10px;
			background: #1e3a5f;
			color: #fff;
			font-size: 9px;
			font-weight: 700;
			text-transform: uppercase;
			letter-spacing: 0.5px;
		}
		.items-table thead th.text-right { text-align: right; }
		.items-table tbody td {
			padding: 8px 10px;
			border-bottom: 1px solid #e8eaed;
			vertical-align: middle;
		}
		.items-table tbody tr:last-child td { border-bottom: none; }
		.items-table tbody tr:nth-child(even) { background: #fafbfc; }
		.items-table .text-right { text-align: right; }
		.items-table .desc { color: #1a1a1a; font-weight: 500; }
		.items-table .amount { font-weight: 600; color: #1a1a1a; }
		/* Totals */
		.totals-wrap { margin-top: 12px; display: table; width: 100%; }
		.totals-spacer { display: table-cell; width: 50%; }
		.totals-box {
			display: table-cell;
			width: 300px;
			border: 1px solid #e8eaed;
			border-radius: 6px;
			overflow: hidden;
			background: #fff;
		}
		.totals-box table { width: 100%; border-collapse: collapse; }
		.totals-box td {
			padding: 6px 12px;
			border-bottom: 1px solid #e8eaed;
			font-size: 11px;
		}
		.totals-box tr:last-child td { border-bottom: none; }
		.totals-box .total-label { color: #5c6370; }
		.totals-box .total-value { text-align: right; font-weight: 600; color: #1a1a1a; }
		.totals-box tr.grand-total td {
			background: #1e3a5f;
			color: #fff;
			font-size: 12px;
			font-weight: 700;
			padding: 8px 12px;
		}
		.totals-box tr.grand-total .total-value { color: #fff; }
		.totals-box tr.emphasis td { font-weight: 600; }
		/* Footer */
		.invoice-footer {
			padding: 8px 20px 10px;
			border-top: 1px solid #e8eaed;
			text-align: center;
			font-size: 10px;
			color: #8b9199;
		}
		.text-right { text-align: right; }
		.print-actions {
			position: fixed;
			top: 12px;
			left: 50%;
			transform: translateX(-50%);
			z-index: 9999;
			display: flex;
			gap: 8px;
			align-items: center;
		}
		.print-actions button {
			padding: 10px 20px;
			background: #1e3a5f;
			color: #fff;
			border: none;
			border-radius: 6px;
			font-size: 14px;
			font-weight: 600;
			cursor: pointer;
			box-shadow: 0 2px 8px rgba(0,0,0,.15);
		}
		.print-actions button:hover { background: #16304d; }
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
	$unitPrice = (float) ($bid->price ?? 0);
	$hasExtraLines = ($bid->relationLoaded('extraCharges') && $bid->extraCharges && $bid->extraCharges->count() > 0);
	$extraChargeUnit = (float) ($bid->extra_charges ?? 0);
	$extraChargeQty = (int) ($bid->quantity ?? 0);

	$priceType = strtolower((string)($bid->postrequest->price_type ?? $bid->postrequest->job_price ?? 'fixed'));
	if ($priceType === 'hourly') {
		$quantity = (float) ($bid->postrequest->total_hours ?? 0);
	} elseif ($priceType === 'daily') {
		$quantity = (float) ($bid->postrequest->total_days ?? 0);
	} else {
		$quantity = 1.0;
	}

	$baseTotal = $unitPrice * $quantity;
	$extraChargeTotal = 0.0;
	if ($hasExtraLines) {
		foreach ($bid->extraCharges as $ec) {
			$lineAmount = (float) ($ec->amount ?? 0);
			$lineQty = (int) ($ec->quantity ?? 0);
			$extraChargeTotal += ($lineAmount * $lineQty);
		}
		$extraChargeQty = (int) $bid->extraCharges->sum('quantity');
	} else {
		$extraChargeTotal = $extraChargeUnit * $extraChargeQty;
	}
	$subTotal = $baseTotal + $extraChargeTotal;

	$countryName = optional($bid->postrequest->country)->name ?? '';
	$taxRate = 0;
	$taxTitle = '';
	$countryId = $bid->postrequest->country_id ?? null;
	if ($countryId) {
		$taxModel = \App\Models\Tax::find($countryId);
		$taxRate  = (float) ($taxModel->value ?? 0);
		$taxTitle = (string) ($taxModel->title ?? $countryName);
	}
	$taxAmount = ($subTotal * $taxRate) / 100;
	$grandTotal = $subTotal + $taxAmount;
	$netAmount = $subTotal - $taxAmount;

	$advancePercent = (float) ($bid->advance_percent ?? 0);
	$advancePaid = $advancePercent > 0 ? ($baseTotal * $advancePercent / 100.0) : 0;
	$remainingAmount = $subTotal - $advancePaid;

	$fmt = function ($n) { return getPriceFormat((float)$n); };
@endphp
<body>
@if(!empty($preview))
<div class="print-actions no-print">
	<button type="button" onclick="window.print();">{{ __('Print / Save as PDF') }}</button>
</div>
@endif
<div class="invoice-wrap">
	<div class="invoice-paper">
		<header class="invoice-header">
			<div class="invoice-header-left">
				<div class="invoice-label">{{ __('Invoice') }}</div>
				<h1 class="invoice-title">#{{ $bid->id }}</h1>
				<div class="invoice-meta">
					<span>{{ __('Date Issued:') }} {{ optional($bid->created_at)->format('d M Y') }}</span>
					<span>{{ __('Currency:') }} {{ $bid->currency ?? 'EUR' }}</span>
				</div>
			</div>
			<div class="invoice-header-right">
				@if (file_exists($logoPath))
					<img src="{{ $logoPath }}" alt="{{ env('APP_NAME') }}" class="logo">
				@endif
				<div class="invoice-meta">
					@if(!empty($generaldata->inquriy_email))
						<span>{{ $generaldata->inquriy_email }}</span>
					@endif
					@if(!empty($generaldata->helpline_number))
						<span>{{ $generaldata->helpline_number }}</span>
					@endif
				</div>
			</div>
		</header>

		<div class="invoice-parties">
			<div class="party-block">
				<div class="party-label">{{ __('Bill From') }}</div>
				<div class="party-name">{{ optional($bid->provider)->display_name ?? '—' }}</div>
				<div class="party-detail">Address: {{ optional($bid->provider)->address ?? '—' }}</div>
				<div class="party-detail">{{ __('VAT Number:') }} {{ optional($bid->provider)->vat_number ?? '—' }}</div>
			</div>
			<div class="party-block">
				<div class="party-label">{{ __('Bill To') }}</div>
				<div class="party-name">{{ optional($bid->customer)->display_name ?? '—' }}</div>
				<div class="party-detail">Address: {{ optional($bid->customer)->address ?? '—' }}</div>
				<div class="party-detail">{{ __('VAT Number:') }} {{ optional($bid->customer)->vat_number ?? '—' }}</div>
			</div>
		</div>

		<div class="job-ref-block">
			<div class="job-ref-label">{{ __('Job Request') }}</div>
			<div class="job-ref-title">{{ optional($bid->postrequest)->title ?? '—' }}</div>
		</div>

		<div class="invoice-items-wrap">
			<table class="items-table">
				<thead>
					<tr>
						<th style="width: 52%">{{ __('Description') }}</th>
						<th style="width: 12%" class="text-right">{{ __('Qty') }}</th>
						<th style="width: 18%" class="text-right">{{ __('Unit Price') }}</th>
						<th style="width: 18%" class="text-right">{{ __('Amount') }}</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="desc">{{ optional($bid->postrequest)->title ?? '—' }} ({{ strtoupper($priceType) }})</td>
						<td class="text-right">{{ $quantity }}</td>
						<td class="text-right">{{ $fmt($unitPrice) }}</td>
						<td class="text-right amount">{{ $fmt($baseTotal) }}</td>
					</tr>
					@if($hasExtraLines)
						@foreach($bid->extraCharges as $line)
							<tr>
								<td class="desc">{{ $line->title }}</td>
								<td class="text-right">{{ (int)($line->quantity ?? 0) }}</td>
								<td class="text-right">{{ $fmt((float)($line->amount ?? 0)) }}</td>
								<td class="text-right amount">{{ $fmt(((float)($line->amount ?? 0)) * ((int)($line->quantity ?? 0))) }}</td>
							</tr>
						@endforeach
					@else
						<tr>
							<td class="desc">{{ __('Extra Charges') }}</td>
							<td class="text-right">{{ $extraChargeQty }}</td>
							<td class="text-right">{{ $fmt($extraChargeUnit) }}</td>
							<td class="text-right amount">{{ $fmt($extraChargeTotal) }}</td>
						</tr>
					@endif
				</tbody>
			</table>

			<div class="totals-wrap">
				<div class="totals-spacer"></div>
				<div class="totals-box">
					<table>
						<tr>
							<td class="total-label">{{ __('Rate (Unit Price)') }}</td>
							<td class="total-value">{{ $fmt($unitPrice) }}</td>
						</tr>
						<tr>
							<td class="total-label">{{ __('Quantity (Packages / Hours / Days)') }}</td>
							<td class="total-value">{{ $quantity }}</td>
						</tr>
						<tr>
							<td class="total-label">{{ __('Total Amount') }}</td>
							<td class="total-value">{{ $fmt($baseTotal) }}</td>
						</tr>
						<tr>
							<td class="total-label">
								@if($hasExtraLines)
									{{ __('Extra Charges (items)') }}
								@else
									{{ __('Extra Charges') }} ({{ $extraChargeQty }} × {{ number_format($extraChargeUnit, 2) }})
								@endif
							</td>
							<td class="total-value">{{ $fmt($extraChargeTotal) }}</td>
						</tr>
						<tr class="emphasis">
							<td class="total-label">{{ __('Subtotal') }}</td>
							<td class="total-value">{{ $fmt($subTotal) }}</td>
						</tr>
						<tr>
							<td class="total-label">{{ __('Net Amount (Subtotal - Tax)') }}</td>
							<td class="total-value">{{ $fmt($netAmount) }}</td>
						</tr>
						<tr>
							<td class="total-label">{{ __('Tax') }} ({{ number_format($taxRate, 0) }}%) {{ $taxTitle ?: $countryName }}</td>
							<td class="total-value">{{ $fmt($taxAmount) }}</td>
						</tr>
						<tr class="grand-total">
							<td class="total-label">{{ __('Grand Total') }}</td>
							<td class="total-value">{{ $fmt($grandTotal) }}</td>
						</tr>
						<tr>
							<td class="total-label">{{ __('Advance Payment') }} ({{ number_format($advancePercent, 0) }}%)</td>
							<td class="total-value">{{ $fmt($advancePaid) }}</td>
						</tr>
						<tr class="emphasis">
							<td class="total-label">{{ __('Remaining Amount') }}</td>
							<td class="total-value">{{ $fmt($remainingAmount) }}</td>
						</tr>
					</table>
				</div>
			</div>
		</div>

		<footer class="invoice-footer">
			{{ __('Thank you for your business.') }}
		</footer>
	</div>
</div>
</body>
</html>
