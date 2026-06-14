@php $locale = $mailLocale ?? app()->getLocale(); @endphp
<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.email_bank_transfer_title') }}</title>
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
        .bank-icon {
            text-align: center;
            margin-bottom: 20px;
        }
        .bank-icon .icon {
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
        .subscription-details {
            background-color: #e3f2fd;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #2196f3;
        }
        .subscription-details h3 {
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
        }
        .bank-info {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #28a745;
        }
        .bank-info h3 {
            margin-top: 0;
            color: #28a745;
            font-size: 20px;
        }
        .bank-details {
            background-color: white;
            padding: 15px;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .bank-row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .bank-row:last-child {
            border-bottom: none;
        }
        .bank-label {
            font-weight: 600;
            color: #495057;
        }
        .bank-value {
            color: #6c757d;
            font-family: monospace;
            font-weight: 600;
        }
        .instructions {
            background-color: #fff3cd;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #ffc107;
        }
        .instructions h3 {
            margin-top: 0;
            color: #856404;
            font-size: 20px;
        }
        .instruction-steps {
            margin-top: 15px;
        }
        .step {
            display: flex;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        .step-number {
            background-color: #ffc107;
            color: #212529;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
            margin-right: 15px;
            flex-shrink: 0;
        }
        .step-text {
            flex: 1;
            line-height: 1.5;
        }
        .email-link {
            color: #007bff;
            text-decoration: none;
            font-weight: 600;
        }
        .email-link:hover {
            text-decoration: underline;
        }
        .highlight {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
        }
        .highlight h4 {
            margin-top: 0;
            color: #0c5460;
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
    </style>
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
            
            <div class="bank-info">
                <h3>{{ __('messages.email_bank_transfer_information') }}</h3>
                <div class="mb-2"><strong>{{ __('messages.email_for_local_international') }}</strong></div>
                <div class="bank-details">
                    <div class="bank-row">
                        <span class="bank-label">{{ __('messages.email_recipient') }}:</span>
                        <span class="bank-value">{{ config('bank_transfer.recipient') }}</span>
                    </div>
                    <div class="bank-row">
                        <span class="bank-label">IBAN:</span>
                        <span class="bank-value">{{ config('bank_transfer.iban') }}</span>
                    </div>
                    <div class="bank-row">
                        <span class="bank-label">BIC:</span>
                        <span class="bank-value">{{ config('bank_transfer.bic') }}</span>
                    </div>
                    <div class="bank-row">
                        <span class="bank-label">{{ __('messages.email_bank_name_address') }}:</span>
                        <span class="bank-value">{{ config('bank_transfer.bank_name') }},<br>
                            {!! nl2br(e(config('bank_transfer.bank_address'))) !!}</span>
                    </div>
                    <div class="bank-row">
                        <span class="bank-label">{{ __('messages.email_bic_sender_bank') }}:</span>
                        <span class="bank-value">{{ config('bank_transfer.sender_bic') }}</span>
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
