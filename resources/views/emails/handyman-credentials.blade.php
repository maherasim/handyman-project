@php $locale = $mailLocale ?? app()->getLocale(); $appName = config('app.display_name', 'Frobster'); @endphp
<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.handyman_credentials_email_subject', [], $locale) }}</title>
        @include('emails._email_styles')
</head>
<body>
<div class="wrapper">
    <div class="header">
        <h1>🔑 {{ __('messages.email_handyman_credentials_welcome', ['app' => $appName], $locale) }}</h1>
        <p>{{ __('messages.email_handyman_credentials_account_created', [], $locale) }}</p>
    </div>

    <div class="content">
        <p class="greeting">{{ __('messages.email_hello_name', ['name' => $handyman->display_name], $locale) }}</p>

        <p class="intro">
            {!! __('messages.email_handyman_credentials_intro', ['app' => '<strong>'.e($appName).'</strong>', 'provider' => '<strong>'.e($provider->display_name).'</strong>'], $locale) !!}
        </p>

        <div class="credentials-box">
            <h3>🔐 {{ __('messages.email_handyman_credentials_heading', [], $locale) }}</h3>
            <div class="cred-row">
                <span class="cred-label">{{ __('messages.email', [], $locale) }} :</span>
                <span class="cred-value">{{ $handyman->email }}</span>
            </div>
            <div class="cred-row">
                <span class="cred-label">{{ __('messages.password', [], $locale) }} :  </span>
                <span class="cred-value">{{ $plainPassword }}</span>
            </div>
            <div class="cred-row">
                <span class="cred-label">{{ __('messages.email_login_url_label', [], $locale) }} :</span>
                <span class="cred-value">{{ $loginUrl }}</span>
            </div>
        </div>

        <div style="text-align:center;">
            <a href="{{ $loginUrl }}" class="btn">{{ __('messages.email_handyman_credentials_login_button', [], $locale) }} →</a>
        </div>

        <div class="warning-box">
            ⚠️ <strong>{{ __('messages.email_important_label', [], $locale) }}:</strong> {{ __('messages.email_handyman_credentials_password_warning', [], $locale) }}
        </div>

        <div class="info-box">
            ℹ️ {{ __('messages.email_handyman_credentials_commission_info', [], $locale) }}
        </div>

        <p style="color:#888; font-size:13px; margin-top:24px;">
            {!! __('messages.email_handyman_credentials_unexpected', ['app' => '<strong>'.e($appName).'</strong>'], $locale) !!}
        </p>
    </div>

    <div class="footer">
        <p>© {{ date('Y') }} {{ $appName }}. {{ __('messages.email_all_rights_reserved', [], $locale) }}</p>
        <p>{{ __('messages.email_handyman_credentials_sent_to', ['email' => $handyman->email], $locale) }}</p>
    </div>
</div>
</body>
</html>
