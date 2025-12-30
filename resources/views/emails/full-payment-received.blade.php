<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Full Payment Received</title>
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
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
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
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            color: white;
            border-radius: 8px;
            padding: 25px;
            margin: 20px 0;
            text-align: center;
        }
        .success-box .amount {
            font-size: 36px;
            font-weight: bold;
            margin: 15px 0;
        }
        .success-box .message {
            font-size: 18px;
            margin-top: 10px;
        }
        .info-box {
            background-color: #f8f9fa;
            border-left: 4px solid #43e97b;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .info-box h3 {
            margin-top: 0;
            color: #28a745;
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
            font-weight: 500;
        }
        .payment-breakdown {
            background-color: #e7f3ff;
            border-left: 4px solid #2196f3;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .payment-breakdown h3 {
            margin-top: 0;
            color: #1976d2;
        }
        .breakdown-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 16px;
        }
        .breakdown-item.total {
            border-top: 2px solid #1976d2;
            margin-top: 10px;
            padding-top: 15px;
            font-weight: bold;
            font-size: 18px;
            color: #1976d2;
        }
        .released-box {
            background-color: #d4edda;
            border-left: 4px solid #28a745;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .released-box h3 {
            margin-top: 0;
            color: #155724;
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
            <h1>🎉 Payment Received Successfully!</h1>
        </div>
        
        <div class="content">
            <h2>Dear {{ $provider->display_name ?? $provider->first_name ?? 'Valued Provider' }},</h2>
            
            <p>Great news! The full payment for your booking has been successfully completed and processed.</p>
            
            <div class="success-box">
                <div style="font-size: 20px; margin-bottom: 10px;">✅ Payment Completed</div>
                <div class="amount">{{ getPriceFormat((float)$totalAmount) }}</div>
                <div class="message">Your payment has been released to your account!</div>
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
                <div class="detail-row">
                    <span class="detail-label">Payment Status:</span>
                    <span class="detail-value" style="color: #28a745; font-weight: bold;">Paid</span>
                </div>
            </div>
            
            @if($advanceAmount > 0)
            <div class="payment-breakdown">
                <h3>💰 Payment Breakdown</h3>
                <div class="breakdown-item">
                    <span>Advance Payment:</span>
                    <span>{{ getPriceFormat((float)$advanceAmount) }}</span>
                </div>
                <div class="breakdown-item">
                    <span>Remaining Payment:</span>
                    <span>{{ getPriceFormat((float)$remainingAmount) }}</span>
                </div>
                <div class="breakdown-item total">
                    <span>Total Amount:</span>
                    <span>{{ getPriceFormat((float)$totalAmount) }}</span>
                </div>
            </div>
            @endif
            
            <div class="released-box">
                <h3>✅ Payment Released</h3>
                <p><strong>Your payment has been successfully processed and released to your account.</strong></p>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>✅ Full payment amount: <strong>{{ getPriceFormat((float)$totalAmount) }}</strong></li>
                    <li>✅ Payment method: <strong>{{ ucfirst(str_replace('_', ' ', $payment->payment_type ?? 'N/A')) }}</strong></li>
                    <li>✅ Payment status: <strong>Completed</strong></li>
                    <li>✅ Funds are now available in your account</li>
                </ul>
                <p style="margin-top: 15px; font-weight: 600; color: #155724;">
                    You can now withdraw or use these funds as needed. Thank you for your excellent service!
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

