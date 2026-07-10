@php $locale = $mailLocale ?? app()->getLocale(); @endphp
<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.email_chat_title') }}</title>
        @include('emails._email_styles')
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>{{ __('messages.email_chat_title') }}</h1>
        </div>
        
        <div class="content">
            <h2>{{ __('messages.email_dear_name', ['name' => $recipient->display_name ?? $recipient->first_name ?? __('messages.email_valued_user')]) }}</h2>
            
            <p>{!! __('messages.email_chat_received_from', ['name' => '<strong>' . e($sender->display_name ?? ($sender->first_name . ' ' . $sender->last_name) ?? __('messages.someone')) . '</strong>']) !!}</p>
            
            <div class="message-box">
                <div class="sender-info">
                    <div class="sender-avatar">
                        {{ strtoupper(substr($sender->display_name ?? $sender->first_name ?? 'U', 0, 1)) }}
                    </div>
                    <div class="sender-details">
                        <div class="sender-name">{{ $sender->display_name ?? ($sender->first_name . ' ' . $sender->last_name) ?? 'User' }}</div>
                        <div class="message-time">{{ \Carbon\Carbon::parse($chatMessage->created_at)->locale($locale)->translatedFormat('F d, Y H:i') }}</div>
                    </div>
                </div>
                
                <div style="margin-top: 15px; padding: 15px; background-color: #fff3cd; border-left: 4px solid #ffc107; border-radius: 8px;">
                    <p style="margin: 0; color: #856404; font-size: 15px;">
                        <strong>{{ __('messages.email_chat_title') }}</strong>
                    </p>
                    <p style="margin: 10px 0 0 0; color: #856404; font-size: 14px;">
                        {{ __('messages.email_chat_open_hint') }}
                    </p>
                </div>
            </div>
            
            <div class="button-container">
                <a href="{{ url('/messages') }}" class="button">{{ __('messages.email_chat_view_button') }}</a>
            </div>
            
            <p style="margin-top: 30px; color: #6c757d; font-size: 14px; text-align: center;">
                <strong>{{ __('messages.email_note') }}:</strong> {{ __('messages.email_chat_privacy_note') }}
            </p>
        </div>
        
        <div class="footer">
            <p>{{ __('messages.email_footer_user_thanks') }}</p>
            <p>{{ __('messages.email_automated_no_reply') }}</p>
        </div>
    </div>
</body>
</html>

