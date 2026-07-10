@php $locale = $mailLocale ?? app()->getLocale(); @endphp
<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.email_advance_title') }}</title>
        @include('emails._email_styles')
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
                    <span class="detail-value">{{ \Carbon\Carbon::parse($payment->datetime ?? now())->locale($locale)->translatedFormat('F d, Y H:i') }}</span>
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

