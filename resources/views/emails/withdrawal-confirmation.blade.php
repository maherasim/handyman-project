<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Withdrawal Request Confirmed</title>
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
            <h1>✅ Withdrawal Request Confirmed</h1>
        </div>
        
        <div class="content">
            <h2>Dear {{ $user->display_name ?? $user->first_name ?? 'Valued User' }},</h2>
            
            <div class="success-box">
                <h3>🎉 Great News!</h3>
                <p>Your withdrawal request has been confirmed and processed successfully. The funds will be transferred to your bank account shortly.</p>
            </div>
            
            <div class="withdrawal-details">
                <h3>💰 Withdrawal Information</h3>
                <div class="detail-row">
                    <span class="detail-label">Withdrawal ID:</span>
                    <span class="detail-value">#{{ $withdrawal->id }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Amount:</span>
                    <span class="detail-value amount-highlight">{{ getPriceFormat($withdrawal->amount) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Status:</span>
                    <span class="detail-value" style="color: #28a745; font-weight: bold;">Paid</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment Type:</span>
                    <span class="detail-value">{{ ucfirst($withdrawal->payment_type ?? 'Bank Transfer') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Transaction Date:</span>
                    <span class="detail-value">{{ \Carbon\Carbon::parse($withdrawal->datetime ?? $withdrawal->created_at)->format('F d, Y h:i A') }}</span>
                </div>
                @if($withdrawal->transaction)
                <div class="detail-row">
                    <span class="detail-label">Transaction ID:</span>
                    <span class="detail-value">{{ $withdrawal->transaction }}</span>
                </div>
                @endif
            </div>
            
            @if($bank)
            <div class="bank-details">
                <h3>🏦 Bank Account Details</h3>
                <div class="detail-row">
                    <span class="detail-label">Bank Name:</span>
                    <span class="detail-value">{{ $bank->bank_name ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Branch Name:</span>
                    <span class="detail-value">{{ $bank->branch_name ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Account Number:</span>
                    <span class="detail-value">{{ $bank->account_no ?? 'N/A' }}</span>
                </div>
                @if($bank->account_holder)
                <div class="detail-row">
                    <span class="detail-label">Account Holder:</span>
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
                    <span class="detail-label">BIC Number:</span>
                    <span class="detail-value">{{ $bank->bic_number }}</span>
                </div>
                @endif
            </div>
            @endif
            
            <div style="margin-top: 30px; padding: 20px; background-color: #fff3cd; border-left: 4px solid #ffc107; border-radius: 8px;">
                <p style="margin: 0; color: #856404;">
                    <strong>Note:</strong> Please allow 2-5 business days for the funds to appear in your bank account. If you have any questions or concerns, please contact our support team.
                </p>
            </div>
        </div>
        
        <div class="footer">
            <p>Thank you for using our services!</p>
            <p>This is an automated email, please do not reply.</p>
        </div>
    </div>
</body>
</html>

