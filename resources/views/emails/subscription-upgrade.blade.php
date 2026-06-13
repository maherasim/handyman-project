@php $locale = $mailLocale ?? app()->getLocale(); @endphp
<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.email_subscription_upgrade_title') }}</title>
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
        .success-icon {
            text-align: center;
            margin-bottom: 20px;
        }
        .success-icon .icon {
            width: 60px;
            height: 60px;
            background-color: #28a745;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }
        .plan-details {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #28a745;
        }
        .plan-details h3 {
            margin-top: 0;
            color: #28a745;
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
        }
        .payment-method {
            background-color: #e3f2fd;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
            border-left: 4px solid #2196f3;
        }
        .payment-method h4 {
            margin-top: 0;
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
        .highlight {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
        }
        .highlight h4 {
            margin-top: 0;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>{{ __('messages.email_subscription_upgrade_title') }}</h1>
        </div>
        
        <div class="content">
            <div class="success-icon">
                <div class="icon">✓</div>
            </div>
            
            <h2>{{ __('messages.email_dear_name', ['name' => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: __('messages.email_valued_user')]) }}</h2>
            
            <p>{{ __('messages.email_subscription_upgrade_intro') }}</p>
            
            <div class="plan-details">
                <h3>{{ __('messages.email_subscription_details') }}</h3>
                <div class="detail-row">
                    <span class="detail-label">{{ __('messages.email_plan_name') }}:</span>
                    <span class="detail-value"><strong>{{ $subscription->title }}</strong></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ __('messages.email_plan_type') }}:</span>
                    <span class="detail-value">{{ ucfirst($subscription->plan_type) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ __('messages.amount') }}:</span>
                    <span class="detail-value">€{{ number_format($subscription->amount, 2) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ __('messages.email_duration') }}:</span>
                    <span class="detail-value">{{ ucfirst($subscription->type) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ __('messages.email_start_date') }}:</span>
                    <span class="detail-value">{{ \Carbon\Carbon::parse($subscription->start_at)->locale($locale)->translatedFormat('F j, Y') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ __('messages.email_end_date') }}:</span>
                    <span class="detail-value">{{ \Carbon\Carbon::parse($subscription->end_at)->locale($locale)->translatedFormat('F j, Y') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ __('messages.status') }}:</span>
                    <span class="detail-value"><span style="color: #28a745; font-weight: 600;">{{ __('messages.active') }}</span></span>
                </div>
            </div>
            
            <div class="payment-method">
                <h4>{{ __('messages.email_payment_information') }}</h4>
                <div class="detail-row">
                    <span class="detail-label">{{ __('messages.email_payment_method') }}:</span>
                    <span class="detail-value">{{ ucfirst($paymentMethod) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ __('messages.email_transaction_id') }}:</span>
                    <span class="detail-value">{{ $transactionId }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ __('messages.email_payment_date') }}:</span>
                    <span class="detail-value">{{ \Carbon\Carbon::now()->locale($locale)->translatedFormat('F j, Y H:i') }}</span>
                </div>
            </div>
            
            <div class="highlight">
                <h4>{{ __('messages.email_whats_next') }}</h4>
                <p>{{ __('messages.email_subscription_active_hint') }}</p>
            </div>
            
            <p>{{ __('messages.email_subscription_support_hint') }}</p>
            
            <div style="text-align: center;">
                <a href="{{ config('app.url') }}/provider_info/{{ $user->id }}" class="cta-button">{{ __('messages.email_view_my_account') }}</a>
            </div>
        </div>
        
        <div class="footer">
            <p>{{ __('messages.email_footer_frobster_thanks') }}</p>
            <p>{{ __('messages.email_automated_no_reply_long') }}</p>
            <p>{!! __('messages.email_contact_support_at', ['email' => '<a href="mailto:support@frobster.com">support@frobster.com</a>']) !!}</p>
            <p>&copy; {{ date('Y') }} FROBSTER. {{ __('messages.email_all_rights_reserved') }}</p>
        </div>
    </div>
</body>
</html>
