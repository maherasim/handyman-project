<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.email_advance_title') }}</title>
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
        .highlight-box {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }
        .highlight-box .amount {
            font-size: 32px;
            font-weight: bold;
            margin: 10px 0;
        }
        .info-box {
            background-color: #f8f9fa;
            border-left: 4px solid #667eea;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .info-box h3 {
            margin-top: 0;
            color: #667eea;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 600;
            color: #6c757d;
        }
        .detail-value {
            color: #212529;
            text-align: right;
        }
        .reassurance-box {
            background-color: #e7f3ff;
            border-left: 4px solid #2196f3;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .reassurance-box h3 {
            margin-top: 0;
            color: #1976d2;
        }
        .reassurance-box ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        .reassurance-box li {
            margin: 8px 0;
            color: #1976d2;
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
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>{{ __('messages.email_advance_title') }}</h1>
        </div>
        
        <div class="content">
            <h2>{{ __('messages.email_dear_name', ['name' => $provider->display_name ?? $provider->first_name ?? __('messages.email_valued_provider')]) }}</h2>
            
            <p>{{ __('messages.email_advance_intro') }}</p>
            
            <div class="highlight-box">
                <div style="font-size: 18px; margin-bottom: 10px;">{{ __('messages.email_advance_amount') }}</div>
                <div class="amount">{{ getPriceFormat((float)$advanceAmount) }}</div>
            </div>
            
            <div class="info-box">
                <h3>{{ __('messages.email_booking_details') }}</h3>
                <div class="detail-row">
                    <span class="detail-label">{{ __('messages.booking_id') }}:</span>
                    <span class="detail-value">#{{ $booking->id }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ __('messages.service') }}:</span>
                    <span class="detail-value">{{ optional($booking->service)->name ?? __('messages.not_available') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ __('messages.customer') }}:</span>
                    <span class="detail-value">{{ optional($booking->customer)->display_name ?? __('messages.not_available') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ __('messages.email_payment_method') }}:</span>
                    <span class="detail-value">{{ ucfirst(str_replace('_', ' ', $payment->payment_type ?? __('messages.not_available'))) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ __('messages.email_payment_date') }}:</span>
                    <span class="detail-value">{{ \Carbon\Carbon::parse($payment->datetime ?? now())->locale(app()->getLocale())->translatedFormat('F d, Y H:i') }}</span>
                </div>
            </div>
            
            <div class="reassurance-box">
                <h3>{{ __('messages.email_payment_status_info') }}</h3>
                <p><strong>{{ __('messages.email_payment_secure_held') }}</strong></p>
                <ul>
                    <li>{{ __('messages.email_advance_li_received') }}</li>
                    <li>{{ __('messages.email_advance_li_secure') }}</li>
                    <li>{{ __('messages.email_advance_li_release') }}</li>
                    <li>{{ __('messages.email_advance_li_notify') }}</li>
                </ul>
                <p style="margin-top: 15px; font-weight: 600; color: #1976d2;">
                    {{ __('messages.email_advance_reassurance') }}
                </p>
            </div>
            
            <p style="margin-top: 30px; color: #6c757d; font-size: 14px;">
                <strong>{{ __('messages.email_note') }}:</strong> {{ __('messages.email_auto_support_note') }}
            </p>
        </div>
        
        <div class="footer">
            <p>{{ __('messages.email_footer_provider_thanks') }}</p>
            <p>{{ __('messages.email_automated_no_reply') }}</p>
        </div>
    </div>
</body>
</html>

