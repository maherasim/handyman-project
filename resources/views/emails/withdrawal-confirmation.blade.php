@php $locale = $mailLocale ?? app()->getLocale(); @endphp
<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.email_withdrawal_title') }}</title>
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
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
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
        .success-box {
            background-color: #d4edda;
            border-left: 4px solid #28a745;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .success-box h3 {
            margin-top: 0;
            color: #155724;
            font-size: 20px;
        }
        .withdrawal-details {
            background-color: #e3f2fd;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #2196f3;
        }
        .withdrawal-details h3 {
            margin-top: 0;
            color: #1976d2;
            font-size: 20px;
        }
        .bank-details {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #6c757d;
        }
        .bank-details h3 {
            margin-top: 0;
            color: #495057;
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
            color: #212529;
            text-align: right;
        }
        .amount-highlight {
            font-size: 24px;
            font-weight: bold;
            color: #28a745;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
        }
        .footer a {
            color: #007bff;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>{{ __('messages.email_withdrawal_title') }}</h1>
        </div>
        
        <div class="content">
            <h2>{{ __('messages.email_dear_name', ['name' => $user->display_name ?? $user->first_name ?? __('messages.email_valued_user')]) }}</h2>
            
            <div class="success-box">
                <h3>{{ __('messages.email_great_news') }}</h3>
                <p>{{ __('messages.email_withdrawal_intro') }}</p>
            </div>
            
            <div class="withdrawal-details">
                <h3>{{ __('messages.email_withdrawal_information') }}</h3>
                <div class="detail-row">
                    <span class="detail-label">{{ __('messages.email_withdrawal_id') }}:</span>
                    <span class="detail-value">#{{ $withdrawal->id }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ __('messages.amount') }}:</span>
                    <span class="detail-value amount-highlight">{{ getPriceFormat($withdrawal->amount) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ __('messages.status') }}:</span>
                    <span class="detail-value" style="color: #28a745; font-weight: bold;">{{ __('messages.paid') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ __('messages.email_payment_type') }}:</span>
                    <span class="detail-value">{{ __('messages.bank_transfer') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ __('messages.email_transaction_date') }}:</span>
                    <span class="detail-value">{{ \Carbon\Carbon::parse($withdrawal->datetime ?? $withdrawal->created_at)->locale($locale)->translatedFormat('F d, Y H:i') }}</span>
                </div>
                @if($withdrawal->transaction)
                <div class="detail-row">
                    <span class="detail-label">{{ __('messages.email_transaction_id') }}:</span>
                    <span class="detail-value">{{ $withdrawal->transaction }}</span>
                </div>
                @endif
            </div>
            
            @if($bank)
            <div class="bank-details">
                <h3>{{ __('messages.email_bank_account_details') }}</h3>
                <div class="detail-row">
                    <span class="detail-label">{{ __('messages.email_bank_name') }}:</span>
                    <span class="detail-value">{{ $bank->bank_name ?? __('messages.not_available') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ __('messages.email_branch_name') }}:</span>
                    <span class="detail-value">{{ $bank->branch_name ?? __('messages.not_available') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ __('messages.email_account_number') }}:</span>
                    <span class="detail-value">{{ $bank->account_no ?? __('messages.not_available') }}</span>
                </div>
                @if($bank->account_holder)
                <div class="detail-row">
                    <span class="detail-label">{{ __('messages.email_account_holder') }}:</span>
                    <span class="detail-value">{{ $bank->account_holder }}</span>
                </div>
                @endif
                @if($bank->iban_no)
                <div class="detail-row">
                    <span class="detail-label">IBAN:</span>
                    <span class="detail-value">{{ $bank->iban_no }}</span>
                </div>
                @endif
                @if($bank->bic_number)
                <div class="detail-row">
                    <span class="detail-label">{{ __('messages.email_bic_number') }}:</span>
                    <span class="detail-value">{{ $bank->bic_number }}</span>
                </div>
                @endif
            </div>
            @endif
            
            <div style="margin-top: 30px; padding: 20px; background-color: #fff3cd; border-left: 4px solid #ffc107; border-radius: 8px;">
                <p style="margin: 0; color: #856404;">
                    <strong>{{ __('messages.email_note') }}:</strong> {{ __('messages.email_withdrawal_note') }}
                </p>
            </div>
        </div>
        
        <div class="footer">
            <p>{{ __('messages.email_footer_user_thanks') }}</p>
            <p>{{ __('messages.email_automated_no_reply') }}</p>
        </div>
    </div>
</body>
</html>

