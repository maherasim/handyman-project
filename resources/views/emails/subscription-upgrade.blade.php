<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Upgrade Confirmation</title>
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
            <h1>🎉 Subscription Upgrade Successful!</h1>
        </div>
        
        <div class="content">
            <div class="success-icon">
                <div class="icon">✓</div>
            </div>
            
            <h2>Dear {{ $user->first_name }} {{ $user->last_name }},</h2>
            
            <p>Congratulations! Your subscription has been successfully upgraded. We're excited to provide you with enhanced features and benefits.</p>
            
            <div class="plan-details">
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
                    <span class="detail-label">Amount:</span>
                    <span class="detail-value">€{{ number_format($subscription->amount, 2) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Duration:</span>
                    <span class="detail-value">{{ ucfirst($subscription->type) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Start Date:</span>
                    <span class="detail-value">{{ \Carbon\Carbon::parse($subscription->start_at)->format('F j, Y') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">End Date:</span>
                    <span class="detail-value">{{ \Carbon\Carbon::parse($subscription->end_at)->format('F j, Y') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Status:</span>
                    <span class="detail-value"><span style="color: #28a745; font-weight: 600;">Active</span></span>
                </div>
            </div>
            
            <div class="payment-method">
                <h4>💳 Payment Information</h4>
                <div class="detail-row">
                    <span class="detail-label">Payment Method:</span>
                    <span class="detail-value">{{ ucfirst($paymentMethod) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Transaction ID:</span>
                    <span class="detail-value">{{ $transactionId }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment Date:</span>
                    <span class="detail-value">{{ \Carbon\Carbon::now()->format('F j, Y \a\t g:i A') }}</span>
                </div>
            </div>
            
            <div class="highlight">
                <h4>✨ What's Next?</h4>
                <p>Your upgraded subscription is now active! You can immediately start enjoying all the premium features and benefits of your new plan.</p>
            </div>
            
            <p>If you have any questions about your subscription or need assistance with your account, please don't hesitate to contact our support team.</p>
            
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
