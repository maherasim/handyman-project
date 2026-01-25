<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank Transfer Payment Notification</title>
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
        .alert-box {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .alert-box h3 {
            margin-top: 0;
            color: #856404;
            font-size: 20px;
        }
        .payment-details {
            background-color: #e3f2fd;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #2196f3;
        }
        .payment-details h3 {
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
            font-weight: 600;
        }
        .booking-details {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #28a745;
        }
        .booking-details h3 {
            margin-top: 0;
            color: #28a745;
            font-size: 20px;
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
        .amount-highlight {
            font-size: 24px;
            color: #28a745;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>🏦 Bank Transfer Payment Notification</h1>
        </div>
        
        <div class="content">
            <h2>Dear Admin,</h2>
            
            <div class="alert-box">
                <h3>⚠️ Action Required</h3>
                <p>A new bank transfer payment has been received and requires your verification. Please review the payment details below and verify the transaction in your bank account.</p>
            </div>
            
            <div class="payment-details">
                <h3>💰 Payment Information</h3>
                <div class="detail-row">
                    <span class="detail-label">Payment Type:</span>
                    <span class="detail-value">{{ $paymentType === 'advance_payment' ? 'Advance Payment' : 'Remaining Payment' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Amount:</span>
                    <span class="detail-value amount-highlight">${{ number_format($payment->total_amount, 2) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Transaction ID:</span>
                    <span class="detail-value">{{ $payment->txn_id ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment Date:</span>
                    <span class="detail-value">{{ \Carbon\Carbon::parse($payment->datetime)->format('F d, Y h:i A') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment Status:</span>
                    <span class="detail-value" style="color: #ffc107; font-weight: bold;">Pending Verification</span>
                </div>
            </div>
            
            <div class="booking-details">
                <h3>📋 Booking Information</h3>
                <div class="detail-row">
                    <span class="detail-label">Booking ID:</span>
                    <span class="detail-value">#{{ $booking->id }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Customer:</span>
                    <span class="detail-value">{{ $booking->customer ? ($booking->customer->first_name ?? '') . ' ' . ($booking->customer->last_name ?? '') : 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Employer:</span>
                    <span class="detail-value">{{ $booking->provider ? ($booking->provider->first_name ?? '') . ' ' . ($booking->provider->last_name ?? '') : 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Total Booking Amount:</span>
                    <span class="detail-value">${{ number_format($booking->total_amount, 2) }}</span>
                </div>
                @if($paymentType === 'advance_payment')
                <div class="detail-row">
                    <span class="detail-label">Advance Paid:</span>
                    <span class="detail-value">${{ number_format($booking->advance_paid_amount ?? 0, 2) }}</span>
                </div>
                @else
                <div class="detail-row">
                    <span class="detail-label">Advance Paid:</span>
                    <span class="detail-value">${{ number_format($booking->advance_paid_amount ?? 0, 2) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Remaining Amount:</span>
                    <span class="detail-value">${{ number_format(($booking->total_amount ?? 0) - ($booking->advance_paid_amount ?? 0), 2) }}</span>
                </div>
                @endif
            </div>
            
            <p><strong>Next Steps:</strong></p>
            <ol>
                <li>Verify the payment has been received in your bank account</li>
                <li>Check that the amount matches: <strong>${{ number_format($payment->total_amount, 2) }}</strong></li>
                <li>Verify the transaction ID: <strong>{{ $payment->txn_id ?? 'N/A' }}</strong></li>
                <li>Approve or reject the payment in the admin panel</li>
            </ol>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="https://frobster.com/cash-payment-list" class="cta-button">Review Payment →</a>
            </div>
            
            <p style="margin-top: 30px;">Please verify this payment as soon as possible to ensure timely processing of the booking.</p>
        </div>
        
        <div class="footer">
            <p>This is an automated notification from FROBSTER Payment System.</p>
            <p>If you have any questions, please contact the support team.</p>
            <p>&copy; {{ date('Y') }} FROBSTER. All rights reserved.</p>
        </div>
    </div>
</body>
</html>

