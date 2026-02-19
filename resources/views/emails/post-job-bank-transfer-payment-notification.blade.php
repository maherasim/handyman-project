<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Job Bank Transfer Payment Notification</title>
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
        .bid-details {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #28a745;
        }
        .bid-details h3 {
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
            <h1>Cash Payment – Verification Required (Post Job)</h1>
        </div>
        
        <div class="content">
            <h2>Dear Admin,</h2>
            
            <div class="alert-box">
                <h3>Cash / Bank Transfer Payment Submitted</h3>
                <p>A customer has submitted a cash payment (bank transfer) for the following post job bid. Please check your bank account for the corresponding transfer.</p>
                <p><strong>If you have received the amount, you may proceed</strong> to verify and approve this payment in the admin panel. To review and manage all cash payments, please use the link below.</p>
            </div>
            
            <div class="payment-details">
                <h3>💰 Payment Information</h3>
                <div class="detail-row">
                    <span class="detail-label">Payment Type:</span>
                    <span class="detail-value">{{ $paymentType === 'advance' ? 'Advance Payment' : 'Remaining Payment' }}</span>
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
            
            <div class="bid-details">
                <h3>📋 Post Job Bid Information</h3>
                <div class="detail-row">
                    <span class="detail-label">Bid ID:</span>
                    <span class="detail-value">#{{ $bid->id }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Customer:</span>
                    <span class="detail-value">{{ $bid->customer ? ($bid->customer->first_name ?? '') . ' ' . ($bid->customer->last_name ?? '') : 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Employer:</span>
                    <span class="detail-value">{{ $bid->provider ? ($bid->provider->first_name ?? '') . ' ' . ($bid->provider->last_name ?? '') : 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Bid Price:</span>
                    <span class="detail-value">${{ number_format($bid->price ?? 0, 2) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Bid Title:</span>
                    <span class="detail-value">{{ $bid->title ?? 'N/A' }}</span>
                </div>
                @if($bid->request)
                <div class="detail-row">
                    <span class="detail-label">Post Job Request ID:</span>
                    <span class="detail-value">#{{ $bid->request->id ?? 'N/A' }}</span>
                </div>
                @endif
            </div>
            
            <p><strong>What to do next:</strong></p>
            <ol>
                <li>Check your bank account to confirm whether the amount has been received.</li>
                <li>If the amount matches (<strong>${{ number_format($payment->total_amount, 2) }}</strong>) and the transaction is correct, you can proceed to approve the payment.</li>
                <li>Verify and manage this payment (and all cash payments) in the admin panel using the link below.</li>
            </ol>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="https://frobster.com/cash-payment-list" class="cta-button">Verify &amp; manage cash payments</a>
            </div>
            
            <p style="margin-top: 30px;">Thank you for your attention. Timely verification helps ensure a smooth experience for customers and providers.</p>
        </div>
        
        <div class="footer">
            <p>This is an automated notification from FROBSTER Payment System.</p>
            <p>If you have any questions, please contact the support team.</p>
            <p>&copy; {{ date('Y') }} FROBSTER. All rights reserved.</p>
        </div>
    </div>
</body>
</html>

