@php $locale = $mailLocale ?? app()->getLocale(); @endphp
<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.email_cash_verify_post_job_title') }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 300;
        }
        .content {
            padding: 30px 20px;
        }
        .alert-box {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .alert-box h3 {
            margin-top: 0;
            color: #856404;
            font-size: 20px;
        }
        .payment-details {
            background-color: #e3f2fd;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #2196f3;
        }
        .payment-details h3 {
            margin-top: 0;
            color: #1976d2;
            font-size: 20px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 600;
            color: #495057;
        }
        .detail-value {
            color: #6c757d;
            font-weight: 600;
        }
        .bid-details {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #28a745;
        }
        .bid-details h3 {
            margin-top: 0;
            color: #28a745;
            font-size: 20px;
        }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            margin: 20px 0;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
        }
        .footer a {
            color: #667eea;
            text-decoration: none;
        }
        .amount-highlight {
            font-size: 24px;
            color: #28a745;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>{{ __('messages.email_cash_verify_post_job_title') }}</h1>
        </div>
        
        <div class="content">
            <h2>{{ __('messages.email_dear_admin') }}</h2>
            
            <div class="alert-box">
                <h3>{{ __('messages.email_cash_submitted_title') }}</h3>
                <p>{{ __('messages.email_cash_post_job_intro') }}</p>
                <p><strong>{{ __('messages.email_cash_verify_hint') }}</strong></p>
            </div>
            
            <div class="payment-details">
                <h3>{{ __('messages.email_payment_information_heading') }}</h3>
                <div class="detail-row">
                    <span class="detail-label">{{ __('messages.email_payment_type') }}:</span>
                    <span class="detail-value">{{ $paymentType === 'advance' ? __('messages.email_advance_payment') : __('messages.email_remaining_payment') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ __('messages.amount') }}:</span>
                    <span class="detail-value amount-highlight">${{ number_format($payment->total_amount, 2) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ __('messages.email_transaction_id') }}:</span>
                    <span class="detail-value">{{ $payment->txn_id ?? __('messages.not_available') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ __('messages.email_payment_date') }}:</span>
                    <span class="detail-value">{{ \Carbon\Carbon::parse($payment->datetime)->locale($locale)->translatedFormat('F d, Y H:i') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ __('messages.email_payment_status') }}:</span>
                    <span class="detail-value" style="color: #ffc107; font-weight: bold;">{{ __('messages.pending_verification') }}</span>
                </div>
            </div>
            
            <div class="bid-details">
                <h3>{{ __('messages.email_post_job_bid_information') }}</h3>
                <div class="detail-row">
                    <span class="detail-label">{{ __('messages.email_bid_id') }}:</span>
                    <span class="detail-value">#{{ $bid->id }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ __('messages.customer') }}:</span>
                    <span class="detail-value">{{ $bid->customer ? ($bid->customer->first_name ?? '') . ' ' . ($bid->customer->last_name ?? '') : __('messages.not_available') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ __('messages.provider') }}:</span>
                    <span class="detail-value">{{ $bid->provider ? ($bid->provider->first_name ?? '') . ' ' . ($bid->provider->last_name ?? '') : __('messages.not_available') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ __('messages.email_bid_price') }}:</span>
                    <span class="detail-value">${{ number_format($bid->price ?? 0, 2) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ __('messages.email_bid_title') }}:</span>
                    <span class="detail-value">{{ $bid->title ?? __('messages.not_available') }}</span>
                </div>
                @if($bid->request)
                <div class="detail-row">
                    <span class="detail-label">{{ __('messages.email_post_job_request_id') }}:</span>
                    <span class="detail-value">#{{ $bid->request->id ?? __('messages.not_available') }}</span>
                </div>
                @endif
            </div>
            
            <p><strong>{{ __('messages.email_what_to_do_next') }}:</strong></p>
            <ol>
                <li>{{ __('messages.email_cash_step_check_bank') }}</li>
                <li>{{ __('messages.email_cash_step_match', ['amount' => '$' . number_format($payment->total_amount, 2)]) }}</li>
                <li>{{ __('messages.email_cash_step_manage') }}</li>
            </ol>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="https://frobster.com/cash-payment-list" class="cta-button">{{ __('messages.email_verify_manage_cash') }}</a>
            </div>
            
            <p style="margin-top: 30px;">{{ __('messages.email_cash_thanks') }}</p>
        </div>
        
        <div class="footer">
            <p>{{ __('messages.email_payment_system_footer') }}</p>
            <p>{{ __('messages.email_footer_support') }}</p>
            <p>&copy; {{ date('Y') }} FROBSTER. {{ __('messages.email_all_rights_reserved') }}</p>
        </div>
    </div>
</body>
</html>

