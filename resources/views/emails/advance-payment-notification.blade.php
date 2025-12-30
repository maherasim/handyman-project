<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advance Payment Received</title>
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
        .highlight-box {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }
        .highlight-box .amount {
            font-size: 32px;
            font-weight: bold;
            margin: 10px 0;
        }
        .info-box {
            background-color: #f8f9fa;
            border-left: 4px solid #667eea;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .info-box h3 {
            margin-top: 0;
            color: #667eea;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 600;
            color: #6c757d;
        }
        .detail-value {
            color: #212529;
            text-align: right;
        }
        .reassurance-box {
            background-color: #e7f3ff;
            border-left: 4px solid #2196f3;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .reassurance-box h3 {
            margin-top: 0;
            color: #1976d2;
        }
        .reassurance-box ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        .reassurance-box li {
            margin: 8px 0;
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
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>✅ Advance Payment Received</h1>
        </div>
        
        <div class="content">
            <h2>Dear {{ $provider->display_name ?? $provider->first_name ?? 'Valued Provider' }},</h2>
            
            <p>We are pleased to inform you that an advance payment has been successfully received for one of your bookings.</p>
            
            <div class="highlight-box">
                <div style="font-size: 18px; margin-bottom: 10px;">Advance Payment Amount</div>
                <div class="amount">{{ getPriceFormat((float)$advanceAmount) }}</div>
            </div>
            
            <div class="info-box">
                <h3>📋 Booking Details</h3>
                <div class="detail-row">
                    <span class="detail-label">Booking ID:</span>
                    <span class="detail-value">#{{ $booking->id }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Service:</span>
                    <span class="detail-value">{{ optional($booking->service)->name ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Customer:</span>
                    <span class="detail-value">{{ optional($booking->customer)->display_name ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment Method:</span>
                    <span class="detail-value">{{ ucfirst(str_replace('_', ' ', $payment->payment_type ?? 'N/A')) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment Date:</span>
                    <span class="detail-value">{{ \Carbon\Carbon::parse($payment->datetime ?? now())->format('F d, Y h:i A') }}</span>
                </div>
            </div>
            
            <div class="reassurance-box">
                <h3>💼 Payment Status & Information</h3>
                <p><strong>Your payment is secure and being held by our system.</strong></p>
                <ul>
                    <li>✅ The advance payment has been successfully received and processed</li>
                    <li>🔒 Your payment is safely held in our secure system</li>
                    <li>⏳ Payment will be released to you once the full payment is completed</li>
                    <li>📧 You will receive a notification email when the payment is released</li>
                </ul>
                <p style="margin-top: 15px; font-weight: 600; color: #1976d2;">
                    Don't worry - your payment is secure and you will receive it in full once the customer completes the final payment.
                </p>
            </div>
            
            <p style="margin-top: 30px; color: #6c757d; font-size: 14px;">
                <strong>Note:</strong> This is an automated email notification. If you have any questions or concerns, please contact our support team.
            </p>
        </div>
        
        <div class="footer">
            <p>Thank you for being a valued provider on our platform!</p>
            <p>This is an automated email, please do not reply.</p>
        </div>
    </div>
</body>
</html>

