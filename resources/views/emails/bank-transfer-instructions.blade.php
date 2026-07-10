@php $locale = $mailLocale ?? app()->getLocale(); @endphp
<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.email_bank_transfer_title') }}</title>
        @include('emails._email_styles')
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>{{ __('messages.email_bank_transfer_title') }}</h1>
        </div>
        
        <div class="content">
            <div class="bank-icon">
                <div class="icon">🏦</div>
            </div>
            
            <h2>{{ __('messages.email_dear_name', ['name' => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: __('messages.email_valued_user')]) }}</h2>
            
            <p>{{ __('messages.email_bank_transfer_intro') }}</p>
            
            <div class="subscription-details">
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
                    <span class="detail-label">{{ __('messages.email_amount_to_transfer') }}:</span>
                    <span class="detail-value"><strong>{{ getPriceFormat($subscription->amount) }}</strong></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ __('messages.email_duration') }}:</span>
                    <span class="detail-value">{{ ucfirst($subscription->type) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ __('messages.email_transaction_id') }}:</span>
                    <span class="detail-value">{{ $transaction->txn_id }}</span>
                </div>
            </div>
            
            @php $bankConfig = getBankTransferDisplayConfig($locale); @endphp
            <div class="bank-info">
                <h3>{{ __('messages.email_bank_transfer_information') }}</h3>
                <div class="mb-2"><strong>{{ __('messages.email_for_local_international') }}</strong></div>
                <div class="bank-details">
                    <div class="bank-row">
                        <span class="bank-label">{{ __('messages.email_recipient') }}:</span>
                        <span class="bank-value">{{ $bankConfig['recipient'] }}</span>
                    </div>
                    <div class="bank-row">
                        <span class="bank-label">IBAN:</span>
                        <span class="bank-value">{{ $bankConfig['iban'] }}</span>
                    </div>
                    <div class="bank-row">
                        <span class="bank-label">BIC:</span>
                        <span class="bank-value">{{ $bankConfig['bic'] }}</span>
                    </div>
                    <div class="bank-row">
                        <span class="bank-label">{{ __('messages.email_bank_name_address') }}:</span>
                        <span class="bank-value">{{ $bankConfig['bank_name'] }},<br>
                            {!! nl2br(e($bankConfig['bank_address'])) !!}</span>
                    </div>
                </div>
            </div>
            
            <div class="instructions">
                <h3>{{ __('messages.email_important_instructions') }}</h3>
                <div class="instruction-steps">
                    <div class="step">
                        <span class="step-number">1</span>
                        <span class="step-text">{!! __('messages.email_bt_step_transfer', ['amount' => '<strong>' . getPriceFormat($subscription->amount) . '</strong>']) !!}</span>
                    </div>
                    <div class="step">
                        <span class="step-number">2</span>
                        <span class="step-text">{{ __('messages.email_bt_step_reference', ['plan' => $subscription->title]) }}</span>
                    </div>
                    <div class="step">
                        <span class="step-number">3</span>
                        <span class="step-text">{{ __('messages.email_bt_step_proof') }} <a href="mailto:billing@frobster.com" class="email-link">billing@frobster.com</a></span>
                    </div>
                    <div class="step">
                        <span class="step-number">4</span>
                        <span class="step-text">{{ __('messages.email_bt_step_activation') }}</span>
                    </div>
                </div>
            </div>
            
            <div class="highlight">
                <h4>{{ __('messages.email_processing_time') }}</h4>
                <p>{{ __('messages.email_processing_hint') }}</p>
            </div>
            
            <p>{{ __('messages.email_bank_support_hint') }}</p>
            
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
