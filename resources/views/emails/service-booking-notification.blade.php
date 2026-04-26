<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Service Booking</title>
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
        .success-box {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            color: white;
            border-radius: 8px;
            padding: 25px;
            margin: 20px 0;
            text-align: center;
        }
        .success-box .message {
            font-size: 20px;
            font-weight: 600;
            margin-top: 10px;
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
            font-weight: 500;
        }
        .booking-details {
            background-color: #e7f3ff;
            border-left: 4px solid #2196f3;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .booking-details h3 {
            margin-top: 0;
            color: #1976d2;
        }
        .customer-info {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .customer-info h3 {
            margin-top: 0;
            color: #856404;
        }
        .action-box {
            background-color: #d4edda;
            border-left: 4px solid #28a745;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .action-box h3 {
            margin-top: 0;
            color: #155724;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            font-size: 16px;
        }
        .button:hover {
            opacity: 0.9;
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
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>📅 New Service Booking Received</h1>
        </div>
        
        <div class="content">
            <h2>Dear {{ $provider->display_name ?? $provider->first_name ?? 'Valued Provider' }},</h2>
            
            <p>Great news! You have received a new service booking request.</p>
            
            <div class="success-box">
                <div style="font-size: 48px; margin-bottom: 10px;">🎉</div>
                <div class="message">Your Service Has Been Booked!</div>
            </div>
            
            <div class="info-box">
                <h3>📋 Booking Information</h3>
                <div class="detail-row">
                    <span class="detail-label">Booking ID:</span>
                    <span class="detail-value">#{{ $booking->id }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Service:</span>
                    <span class="detail-value">{{ optional($booking->service)->name ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Booking Status:</span>
                    <span class="detail-value">
                        <span class="status-badge status-pending">{{ ucfirst($booking->status ?? 'Pending') }}</span>
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Total Amount:</span>
                    <span class="detail-value" style="color: #28a745; font-weight: bold;">{{ getPriceFormat((float)($booking->total_amount ?? 0)) }}</span>
                </div>
                @if($booking->quantity)
                <div class="detail-row">
                    <span class="detail-label">Quantity:</span>
                    <span class="detail-value">{{ $booking->quantity }}</span>
                </div>
                @endif
            </div>
            
            <div class="customer-info">
                <h3>👤 Customer Information</h3>
                <div class="detail-row">
                    <span class="detail-label">Customer Name:</span>
                    <span class="detail-value">{{ $customer->display_name ?? ($customer->first_name . ' ' . $customer->last_name) ?? 'N/A' }}</span>
                </div>
                @php $serviceLocationLabel = $booking->emailVenueLocation(); @endphp
                @if($serviceLocationLabel !== '')
                <div class="detail-row">
                    <span class="detail-label">Service Location:</span>
                    <span class="detail-value" style="text-align: right; max-width: 60%; word-wrap: break-word;">{{ $serviceLocationLabel }}</span>
                </div>
                @endif
            </div>
            
            @if($booking->slots && $booking->slots->count() > 0)
            <div class="booking-details">
                <h3>📅 Service Schedule</h3>
                @foreach($booking->slots as $slot)
                <div style="padding: 10px 0; border-bottom: 1px solid #b3d9ff;">
                    <div style="font-weight: 600; color: #1976d2; margin-bottom: 5px;">
                        {{ \Carbon\Carbon::parse($slot->date)->format('F d, Y') }}
                    </div>
                    @if($slot->start_time && $slot->end_time)
                    <div style="color: #6c757d; font-size: 14px;">
                        <i class="ri-time-line"></i> 
                        {{ \Carbon\Carbon::parse($slot->start_time)->format('g:i A') }} - 
                        {{ \Carbon\Carbon::parse($slot->end_time)->format('g:i A') }}
                    </div>
                    @endif
                    @if($slot->total_hours)
                    <div style="color: #6c757d; font-size: 14px; margin-top: 5px;">
                        Duration: {{ $slot->total_hours }} hour(s)
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
            @endif
            
            @if($booking->description)
            <div class="booking-details">
                <h3>📝 Additional Notes</h3>
                <p style="color: #212529; margin: 0;">{{ $booking->description }}</p>
            </div>
            @endif
            
            <div class="action-box">
                <h3>✅ Next Steps</h3>
                <ul style="margin: 10px 0; padding-left: 20px; color: #155724;">
                    <li>Review the booking details above</li>
                    <li>Accept or respond to the booking request</li>
                    <li>Prepare for the scheduled service</li>
                    <li>Contact the customer if you need any clarification</li>
                </ul>
            </div>
            
            <div class="button-container">
                <a href="{{ url('/booking/' . $booking->id) }}" class="button">View Booking Details</a>
            </div>
            
            <p style="margin-top: 30px; color: #6c757d; font-size: 14px;">
                <strong>Note:</strong> This is an automated email notification. Please log in to your account to manage this booking and communicate with the customer.
            </p>
        </div>
        
        <div class="footer">
            <p>Thank you for being a valued provider on our platform!</p>
            <p>This is an automated email, please do not reply.</p>
        </div>
    </div>
</body>
</html>

