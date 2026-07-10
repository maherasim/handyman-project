@php
    $locale = $mailLocale ?? app()->getLocale();
    $t = fn ($key, $replace = []) => __($key, $replace, $locale);
    $postJob = $bid->postrequest;
    $statusLabel = function ($status) use ($locale) {
        $key = 'messages.pjr_st_' . $status;
        return \Illuminate\Support\Facades\Lang::has($key, $locale)
            ? __($key, [], $locale)
            : ucwords(str_replace('_', ' ', $status));
    };
    $actorTypeLabel = function ($type) use ($locale) {
        return [
            'provider' => __('messages.provider', [], $locale),
            'user' => __('messages.customer', [], $locale),
            'system' => __('messages.system', [], $locale),
        ][$type] ?? ucfirst((string) $type);
    };
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $locale) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $t('messages.email_post_job_bid_status_title') }}</title>
        @include('emails._email_styles')
</head>
<body>
    <div class="header">
        <h1>{{ $t('messages.email_post_job_bid_status_title') }}</h1>
    </div>

    <div class="content">
        <p>{{ $t('messages.email_hello_name', ['name' => $recipient->display_name ?? $recipient->first_name ?? $t('messages.email_there')]) }}</p>
        <p>{{ $t('messages.email_post_job_bid_status_intro', ['title' => optional($postJob)->title ?? '']) }}</p>

        <div class="status-box">
            <div class="status-label">{{ $t('messages.booking_status_email_status_changed') }}</div>
            <p style="margin: 10px 0;">
                <strong>{{ $statusLabel($oldStatus) }}</strong>
                &rarr;
                <strong>{{ $statusLabel($newStatus) }}</strong>
            </p>
            <p style="margin-top: 10px; color: #666;">
                {{ $t('messages.booking_status_email_updated_by') }}
                <strong>{{ $actorName }}</strong>
                ({{ $actorTypeLabel($actorType) }})
            </p>
        </div>

        <div class="booking-info">
            <h3 style="margin-top: 0;">{{ $t('messages.email_job_information') }}</h3>
            <div class="info-row">
                <span class="info-label">{{ $t('messages.email_job_title') }}:</span>
                <span>{{ optional($postJob)->title ?? $t('messages.not_available') }}</span>
            </div>
            @if($bid->provider)
            <div class="info-row">
                <span class="info-label">{{ $t('messages.provider') }}:</span>
                <span>{{ $bid->provider->display_name ?? $t('messages.not_available') }}</span>
            </div>
            @endif
            @if($bid->customer)
            <div class="info-row">
                <span class="info-label">{{ $t('messages.customer') }}:</span>
                <span>{{ $bid->customer->display_name ?? $t('messages.not_available') }}</span>
            </div>
            @endif
            <div class="info-row">
                <span class="info-label">{{ $t('messages.email_bid_amount') }}:</span>
                <span>{{ getPriceFormat((float)($bid->price ?? 0)) }}</span>
            </div>
        </div>

        @if($postJob)
        <div style="text-align: center;">
            <a href="{{ route('post-job-bid.show', ['id' => $postJob->id]) }}" class="button">{{ $t('messages.email_bid_view_button') }}</a>
        </div>
        @endif
    </div>

    <div class="footer">
        <p>{{ $t('messages.email_automated_no_reply') }}</p>
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. {{ $t('messages.email_all_rights_reserved') }}</p>
    </div>
</body>
</html>
