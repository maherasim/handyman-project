<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('messages.chat_policy_warning_email_title') }}</title>
    <style>
        body { font-family: Arial, sans-serif; color:#222; }
        .wrap { max-width:600px; margin:0 auto; padding:20px; }
        .btn { display:inline-block; padding:10px 16px; background:#dc3545; color:#fff; text-decoration:none; border-radius:4px; }
        .small { color:#666; font-size:12px; }
        .badge { display:inline-block; padding:2px 8px; background:#ffe08a; color:#6b5000; border-radius:12px; font-size:12px; margin-right:6px; }
        .card { border:1px solid #eee; border-radius:6px; padding:16px; }
    </style>
    </head>
<body>
    <div class="wrap">
        <h2>{{ __('messages.chat_policy_warning_email_subject') }}</h2>
        <p>{{ __('messages.chat_policy_warning_email_greeting', ['name' => $name]) }}</p>
        <p>{{ __('messages.chat_policy_warning_email_intro') }}</p>
        @if(!empty($types))
        <p>{{ __('messages.chat_detected_types') }}:
            @foreach($types as $t)
                @php $typeKey = 'messages.chat_pii_type_' . $t; @endphp
                <span class="badge">{{ \Illuminate\Support\Facades\Lang::has($typeKey) ? __($typeKey) : ucfirst(str_replace('_', ' ', $t)) }}</span>
            @endforeach
        </p>
        @endif
        @if(!empty($snippet))
        <div class="card">
            <div class="small">{{ __('messages.chat_message_excerpt_with_date', ['date' => $date]) }}:</div>
            <div>{{ $snippet }}</div>
        </div>
        @endif
        <p>{{ __('messages.chat_policy_warning_email_keep_platform') }}</p>
        <p>{{ __('messages.chat_policy_warning_email_thanks') }}</p>
        <p class="small">{{ __('messages.chat_policy_warning_email_footer') }}</p>
    </div>
</body>
</html>

