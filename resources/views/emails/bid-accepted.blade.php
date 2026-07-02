@php $locale = $mailLocale ?? app()->getLocale(); $postJob = $bid->postrequest; @endphp
<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.email_bid_accepted_title') }}</title>
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
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>{{ __('messages.email_bid_accepted_title') }}</h1>
        </div>

        <div class="content">
            <h2>{{ __('messages.email_dear_name', ['name' => $provider->display_name ?? $provider->first_name ?? __('messages.email_valued_provider')]) }}</h2>

            <p>{{ __('messages.email_bid_accepted_intro', ['title' => optional($postJob)->title ?? '']) }}</p>

            <div class="success-box">
                <div style="font-size: 48px; margin-bottom: 10px;">🎉</div>
                <div class="message">{{ __('messages.email_bid_accepted_confirmed') }}</div>
            </div>

            <div class="info-box">
                <h3>{{ __('messages.email_job_information') }}</h3>
                <div class="detail-row">
                    <span class="detail-label">{{ __('messages.email_job_title') }}:</span>
                    <span class="detail-value">{{ optional($postJob)->title ?? __('messages.not_available') }}</span>
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
            </div>

            <div class="customer-info">
                <h3>{{ __('messages.email_customer_information') }}</h3>
                <div class="detail-row">
                    <span class="detail-label">{{ __('messages.email_customer_name') }}:</span>
                    <span class="detail-value">{{ $customer->display_name ?? __('messages.not_available') }}</span>
                </div>
            </div>

            <div class="action-box">
                <h3>{{ __('messages.email_next_steps') }}</h3>
                <ul style="margin: 10px 0; padding-left: 20px; color: #155724;">
                    <li>{{ __('messages.email_bid_accepted_step_contact') }}</li>
                    <li>{{ __('messages.email_bid_accepted_step_prepare') }}</li>
                    <li>{{ __('messages.email_bid_accepted_step_start') }}</li>
                </ul>
            </div>

            @if($postJob)
            <div class="button-container">
                <a href="{{ route('post-job-bid.show', ['id' => $postJob->id]) }}" class="button">{{ __('messages.email_bid_view_button') }}</a>
            </div>
            @endif

            <p style="margin-top: 30px; color: #6c757d; font-size: 14px;">
                <strong>{{ __('messages.email_note') }}:</strong> {{ __('messages.email_bid_manage_note') }}
            </p>
        </div>

        <div class="footer">
            <p>{{ __('messages.email_footer_provider_thanks') }}</p>
            <p>{{ __('messages.email_automated_no_reply') }}</p>
        </div>
    </div>
</body>
</html>
