@php $locale = $mailLocale ?? app()->getLocale(); @endphp
<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.email_cash_verify_post_job_title') }}</title>
        @include('emails._email_styles')
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

