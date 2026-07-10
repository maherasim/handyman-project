@php $locale = $mailLocale ?? app()->getLocale(); @endphp
<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.email_cash_verify_title') }}</title>
        @include('emails._email_styles')
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>{{ __('messages.email_cash_verify_title') }}</h1>
        </div>
        
        <div class="content">
            <h2>{{ __('messages.email_dear_admin') }}</h2>
            
            <div class="alert-box">
                <h3>{{ __('messages.email_cash_submitted_title') }}</h3>
                <p>{{ __('messages.email_cash_booking_intro') }}</p>
                <p><strong>{{ __('messages.email_cash_verify_hint') }}</strong></p>
            </div>
            
            <div class="payment-details">
                <h3>{{ __('messages.email_payment_information_heading') }}</h3>
                <div class="detail-row">
                    <span class="detail-label">{{ __('messages.email_payment_type') }}:</span>
                    <span class="detail-value">{{ $paymentType === 'advance_payment' ? __('messages.email_advance_payment') : __('messages.email_remaining_payment') }}</span>
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
            
            <div class="booking-details">
                <h3>{{ __('messages.email_booking_information') }}</h3>
                <div class="detail-row">
                    <span class="detail-label">{{ __('messages.booking_id') }}:</span>
                    <span class="detail-value">#{{ $booking->id }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ __('messages.customer') }}:</span>
                    <span class="detail-value">{{ $booking->customer ? ($booking->customer->first_name ?? '') . ' ' . ($booking->customer->last_name ?? '') : __('messages.not_available') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ __('messages.provider') }}:</span>
                    <span class="detail-value">{{ $booking->provider ? ($booking->provider->first_name ?? '') . ' ' . ($booking->provider->last_name ?? '') : __('messages.not_available') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ __('messages.email_total_booking_amount') }}:</span>
                    <span class="detail-value">${{ number_format($booking->total_amount, 2) }}</span>
                </div>
                @if($paymentType === 'advance_payment')
                <div class="detail-row">
                    <span class="detail-label">{{ __('messages.email_advance_paid') }}:</span>
                    <span class="detail-value">${{ number_format($booking->advance_paid_amount ?? 0, 2) }}</span>
                </div>
                @else
                <div class="detail-row">
                    <span class="detail-label">{{ __('messages.email_advance_paid') }}:</span>
                    <span class="detail-value">${{ number_format($booking->advance_paid_amount ?? 0, 2) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ __('messages.remaining_amount') }}:</span>
                    <span class="detail-value">${{ number_format(($booking->total_amount ?? 0) - ($booking->advance_paid_amount ?? 0), 2) }}</span>
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

