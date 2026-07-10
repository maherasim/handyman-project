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
        @include('emails._email_styles')
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
