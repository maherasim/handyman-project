<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Status Update</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background: #f9f9f9;
            padding: 30px;
            border-radius: 0 0 10px 10px;
        }
        .status-box {
            background: white;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .status-label {
            font-weight: bold;
            color: #667eea;
            font-size: 18px;
        }
        .booking-info {
            background: white;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: bold;
            color: #666;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #999;
            font-size: 12px;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📋 Booking Status Update</h1>
    </div>
    
    <div class="content">
        <p>Hello {{ $recipient->display_name ?? $recipient->first_name ?? 'there' }},</p>
        
        <p>We wanted to inform you that the status of your booking has been updated.</p>
        
        <div class="status-box">
            <div class="status-label">Status Changed:</div>
            <p style="margin: 10px 0;">
                <strong>{{ ucwords(str_replace('_', ' ', $oldStatus)) }}</strong> 
                → 
                <strong style="color: #667eea;">{{ ucwords(str_replace('_', ' ', $newStatus)) }}</strong>
            </p>
            <p style="margin-top: 10px; color: #666;">
                Updated by: <strong>{{ $actorName }}</strong> 
                @php
                    // Convert text: provider -> Employer, handyman -> Worker, user -> Customer
                    $displayActorType = $actorType;
                    if ($actorType === 'provider') {
                        $displayActorType = 'Employer';
                    } elseif ($actorType === 'handyman') {
                        $displayActorType = 'Worker';
                    } elseif ($actorType === 'user') {
                        $displayActorType = 'Customer';
                    }
                @endphp
                ({{ ucfirst($displayActorType) }})
            </p>
        </div>
        
        <div class="booking-info">
            <h3 style="margin-top: 0; color: #667eea;">Booking Details</h3>
            <div class="info-row">
                <span class="info-label">Booking ID:</span>
                <span>#{{ $booking->id }}</span>
            </div>
            @if($booking->service)
            <div class="info-row">
                <span class="info-label">Service:</span>
                <span>{{ $booking->service->name ?? 'N/A' }}</span>
            </div>
            @endif
            @if($booking->customer)
            <div class="info-row">
                <span class="info-label">Customer:</span>
                <span>{{ $booking->customer->display_name ?? 'N/A' }}</span>
            </div>
            @endif
            @if($booking->provider)
            <div class="info-row">
                <span class="info-label">Employer:</span>
                <span>{{ $booking->provider->display_name ?? 'N/A' }}</span>
            </div>
            @endif
            @if($booking->handymanAdded && $booking->handymanAdded->count() > 0)
            <div class="info-row">
                <span class="info-label">Worker:</span>
                <span>
                    @foreach($booking->handymanAdded as $handymanMapping)
                        {{ $handymanMapping->handyman->display_name ?? 'N/A' }}@if(!$loop->last), @endif
                    @endforeach
                </span>
            </div>
            @endif
            <div class="info-row">
                <span class="info-label">Total Amount:</span>
                <span>{{ getPriceFormat($booking->total_amount ?? 0) }}</span>
            </div>
        </div>
        
        <p>Please log in to your account to view more details about this booking.</p>
        
        <div style="text-align: center;">
            <a href="{{ url('/booking/' . $booking->id) }}" class="button">View Booking Details</a>
        </div>
    </div>
    
    <div class="footer">
        <p>This is an automated notification. Please do not reply to this email.</p>
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
    </div>
</body>
</html>

