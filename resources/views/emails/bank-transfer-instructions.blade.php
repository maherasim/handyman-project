<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank Transfer Payment Instructions</title>
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
            <h1>🏦 Bank Transfer Payment Instructions</h1>
        </div>
        
        <div class="content">
            <div class="bank-icon">
                <div class="icon">🏦</div>
            </div>
            
            <h2>Dear {{ $user->first_name }} {{ $user->last_name }},</h2>
            
            <p>Thank you for choosing bank transfer as your payment method. Please follow the instructions below to complete your subscription upgrade.</p>
            
            <div class="subscription-details">
                <h3>📋 Subscription Details</h3>
                <div class="detail-row">
                    <span class="detail-label">Plan Name:</span>
                    <span class="detail-value"><strong>{{ $subscription->title }}</strong></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Plan Type:</span>
                    <span class="detail-value">{{ ucfirst($subscription->plan_type) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Amount to Transfer:</span>
                    <span class="detail-value"><strong>€{{ number_format($subscription->amount, 2) }}</strong></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Duration:</span>
                    <span class="detail-value">{{ ucfirst($subscription->type) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Transaction ID:</span>
                    <span class="detail-value">{{ $transaction->txn_id }}</span>
                </div>
            </div>
            
            <div class="bank-info">
                <h3>🏦 Bank Transfer Information</h3>
                <div class="mb-2"><strong>For local and international transfers</strong></div>
                <div class="bank-details">
                    <div class="bank-row">
                        <span class="bank-label">Recipient:</span>
                        <span class="bank-value">Ben Ghezaiel</span>
                    </div>
                    <div class="bank-row">
                        <span class="bank-label">IBAN:</span>
                        <span class="bank-value">DE02 1001 0178 1361 6331 79</span>
                    </div>
                    <div class="bank-row">
                        <span class="bank-label">BIC:</span>
                        <span class="bank-value">REVODEB2</span>
                    </div>
                    <div class="bank-row">
                        <span class="bank-label">Bank Name and Address:</span>
                        <span class="bank-value">Revolut Bank UAB,<br>
                            Zweigniederlassung Deutschland<br>
                            FORA Linden Palais, Unter den<br>
                            Linden 40<br>
                            10117, Berlin, Germany</span>
                    </div>
                    <div class="bank-row">
                        <span class="bank-label">BIC of Sender Bank:</span>
                        <span class="bank-value">CHASDEFX</span>
                    </div>
                </div>
            </div>
            
            <div class="instructions">
                <h3>📝 Important Instructions</h3>
                <div class="instruction-steps">
                    <div class="step">
                        <span class="step-number">1</span>
                        <span class="step-text">Transfer the exact amount <strong>€{{ number_format($subscription->amount, 2) }}</strong> to the bank account above</span>
                    </div>
                    <div class="step">
                        <span class="step-number">2</span>
                        <span class="step-text">Include your name and "Subscription {{ $subscription->title }}" in the transfer reference</span>
                    </div>
                    <div class="step">
                        <span class="step-number">3</span>
                        <span class="step-text">Send proof of payment (screenshot or PDF document) to: <a href="mailto:billing@frobster.com" class="email-link">billing@frobster.com</a></span>
                    </div>
                    <div class="step">
                        <span class="step-number">4</span>
                        <span class="step-text">Your subscription will be activated within 24 hours after payment verification</span>
                    </div>
                </div>
            </div>
            
            <div class="highlight">
                <h4>⏰ Processing Time</h4>
                <p>Bank transfers may take varying business days to process. Your subscription will be activated once payment is verified by our team.</p>
            </div>
            
            <p>If you have any questions about this bank transfer or need assistance, please don't hesitate to contact our support team.</p>
            
            <div style="text-align: center;">
                <a href="{{ config('app.url') }}/provider_info/{{ $user->id }}" class="cta-button">View My Account</a>
            </div>
        </div>
        
        <div class="footer">
            <p>Thank you for choosing FROBSTER!</p>
            <p>This is an automated message. Please do not reply to this email.</p>
            <p>If you have any questions, contact us at <a href="mailto:support@frobster.com">support@frobster.com</a></p>
            <p>&copy; {{ date('Y') }} FROBSTER. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
