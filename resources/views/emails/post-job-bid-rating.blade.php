@php
    $locale = $mailLocale ?? app()->getLocale();
    $postJob = $bid->postrequest;
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.email_post_job_bid_rated_title') }}</title>
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
            background: linear-gradient(135deg, #f6d365 0%, #fda085 100%);
            color: white;
            border-radius: 8px;
            padding: 25px;
            margin: 20px 0;
            text-align: center;
        }
        .success-box .stars {
            font-size: 32px;
            letter-spacing: 4px;
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
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>{{ __('messages.email_post_job_bid_rated_title') }}</h1>
        </div>

        <div class="content">
            <h2>{{ __('messages.email_dear_name', ['name' => $recipient->display_name ?? $recipient->first_name ?? __('messages.email_there')]) }}</h2>

            <p>{{ __('messages.email_post_job_bid_rated_intro', ['name' => $rater->display_name ?? __('messages.not_available'), 'title' => optional($postJob)->title ?? '']) }}</p>

            <div class="success-box">
                <div class="stars">{{ str_repeat('⭐', max(0, min(5, $rating))) }}</div>
            </div>

            <div class="info-box">
                <h3>{{ __('messages.email_job_information') }}</h3>
                <div class="detail-row">
                    <span class="detail-label">{{ __('messages.email_job_title') }}:</span>
                    <span class="detail-value">{{ optional($postJob)->title ?? __('messages.not_available') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ __('messages.rating') }}:</span>
                    <span class="detail-value">{{ $rating }}/5</span>
                </div>
            </div>

            @if($review !== '')
            <div class="booking-details">
                <h3>{{ __('messages.email_post_job_bid_review') }}</h3>
                <p style="color: #212529; margin: 0;">{{ $review }}</p>
            </div>
            @endif

            @if($postJob)
            <div class="button-container">
                <a href="{{ route('post-job-bid.show', ['id' => $postJob->id]) }}" class="button">{{ __('messages.email_bid_view_button') }}</a>
            </div>
            @endif
        </div>

        <div class="footer">
            <p>{{ __('messages.email_automated_no_reply') }}</p>
        </div>
    </div>
</body>
</html>
