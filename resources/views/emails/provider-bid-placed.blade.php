@php $locale = $mailLocale ?? app()->getLocale(); @endphp
<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.email_provider_bid_placed_title') }}</title>
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
        .provider-info {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .provider-info h3 {
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
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>{{ __('messages.email_provider_bid_placed_title') }}</h1>
        </div>

        <div class="content">
            <h2>{{ __('messages.email_dear_name', ['name' => $customer->display_name ?? $customer->first_name ?? __('messages.email_valued_user')]) }}</h2>

            <p>{{ __('messages.email_provider_bid_placed_intro', ['title' => $postJob->title]) }}</p>

            <div class="success-box">
                <div style="font-size: 48px; margin-bottom: 10px;">💼</div>
                <div class="message">{{ getPriceFormat((float)($bid->price ?? 0)) }}</div>
            </div>

            <div class="info-box">
                <h3>{{ __('messages.email_job_information') }}</h3>
                <div class="detail-row">
                    <span class="detail-label">{{ __('messages.email_job_title') }}:</span>
                    <span class="detail-value">{{ $postJob->title }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ __('messages.email_bid_amount') }}:</span>
                    <span class="detail-value" style="color: #28a745; font-weight: bold;">{{ getPriceFormat((float)($bid->price ?? 0)) }}</span>
                </div>
                @if($bid->duration)
                <div class="detail-row">
                    <span class="detail-label">{{ __('messages.email_bid_duration') }}:</span>
                    <span class="detail-value">{{ $bid->duration }}</span>
                </div>
                @endif
                @if($bid->quantity)
                <div class="detail-row">
                    <span class="detail-label">{{ __('messages.quantity') }}:</span>
                    <span class="detail-value">{{ $bid->quantity }}</span>
                </div>
                @endif
            </div>

            <div class="provider-info">
                <h3>{{ __('messages.email_provider_information') }}</h3>
                <div class="detail-row">
                    <span class="detail-label">{{ __('messages.email_provider_name') }}:</span>
                    <span class="detail-value">{{ $provider->display_name ?? __('messages.not_available') }}</span>
                </div>
            </div>

            @if($bid->why_choose_me)
            <div class="booking-details">
                <h3>{{ __('messages.email_bid_why_choose_me') }}</h3>
                <p style="color: #212529; margin: 0;">{{ strip_tags($bid->why_choose_me) }}</p>
            </div>
            @endif

            <div class="action-box">
                <h3>{{ __('messages.email_next_steps') }}</h3>
                <ul style="margin: 10px 0; padding-left: 20px; color: #155724;">
                    <li>{{ __('messages.email_bid_step_review') }}</li>
                    <li>{{ __('messages.email_bid_step_compare') }}</li>
                    <li>{{ __('messages.email_bid_step_accept') }}</li>
                </ul>
            </div>

            <div class="button-container">
                <a href="{{ route('post-job-bid.show', ['id' => $postJob->id]) }}" class="button">{{ __('messages.email_bid_view_button') }}</a>
            </div>

            <p style="margin-top: 30px; color: #6c757d; font-size: 14px;">
                <strong>{{ __('messages.email_note') }}:</strong> {{ __('messages.email_bid_manage_note') }}
            </p>
        </div>

        <div class="footer">
            <p>{{ __('messages.email_footer_user_thanks') }}</p>
            <p>{{ __('messages.email_automated_no_reply') }}</p>
        </div>
    </div>
</body>
</html>
