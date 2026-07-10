@php
    $locale = $mailLocale ?? app()->getLocale();
    $t = fn ($key, $replace = []) => __($key, $replace, $locale);
    $statusLabel = function ($status) use ($locale) {
        $key = 'messages.booking_status_option_' . $status;
        return \Illuminate\Support\Facades\Lang::has($key, $locale)
            ? __($key, [], $locale)
            : ucwords(str_replace('_', ' ', $status));
    };
    $actorTypeLabel = function ($type) use ($locale) {
        return [
            'provider' => __('messages.provider', [], $locale),
            'handyman' => __('messages.worker', [], $locale),
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
    <title>{{ $t('messages.booking_status_email_title') }}</title>
        @include('emails._email_styles')
</head>
<body>
    <div class="header">
        <h1>{{ $t('messages.booking_status_email_title') }}</h1>
    </div>

    <div class="content">
        <p>{{ $t('messages.email_hello_name', ['name' => $recipient->display_name ?? $recipient->first_name ?? $t('messages.email_there')]) }}</p>
        <p>{{ $t('messages.booking_status_email_intro') }}</p>

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
            <h3 style="margin-top: 0;">{{ $t('messages.booking_status_email_details') }}</h3>
            <div class="info-row">
                <span class="info-label">{{ $t('messages.booking_id') }}:</span>
                <span>#{{ $booking->id }}</span>
            </div>
            @if($booking->service)
            <div class="info-row">
                <span class="info-label">{{ $t('messages.service') }}:</span>
                <span>{{ $booking->service->name ?? $t('messages.not_available') }}</span>
            </div>
            @endif
            @if($booking->customer)
            <div class="info-row">
                <span class="info-label">{{ $t('messages.customer') }}:</span>
                <span>{{ $booking->customer->display_name ?? $t('messages.not_available') }}</span>
            </div>
            @endif
            @if($booking->provider)
            <div class="info-row">
                <span class="info-label">{{ $t('messages.provider') }}:</span>
                <span>{{ $booking->provider->display_name ?? $t('messages.not_available') }}</span>
            </div>
            @endif
            @if($booking->handymanAdded && $booking->handymanAdded->count() > 0)
            <div class="info-row">
                <span class="info-label">{{ $t('messages.worker') }}:</span>
                <span>
                    @foreach($booking->handymanAdded as $handymanMapping)
                        {{ $handymanMapping->handyman->display_name ?? $t('messages.not_available') }}@if(!$loop->last), @endif
                    @endforeach
                </span>
            </div>
            @endif
            <div class="info-row">
                <span class="info-label">{{ $t('messages.total_amount') }}:</span>
                <span>{{ getPriceFormat($booking->total_amount ?? 0) }}</span>
            </div>
        </div>

        <p>{{ $t('messages.booking_status_email_login_hint') }}</p>

        <div style="text-align: center;">
            <a href="{{ url('/booking/' . $booking->id) }}" class="button">{{ $t('messages.booking_status_email_view_booking') }}</a>
        </div>
    </div>

    <div class="footer">
        <p>{{ $t('messages.email_automated_no_reply') }}</p>
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. {{ $t('messages.email_all_rights_reserved') }}</p>
    </div>
</body>
</html>
