@php $locale = $mailLocale ?? app()->getLocale(); @endphp
<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.email_subscription_upgrade_title') }}</title>
        @include('emails._email_styles')
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
