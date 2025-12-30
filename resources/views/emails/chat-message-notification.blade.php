<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Chat Message</title>
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
        .message-box {
            background-color: #f8f9fa;
            border-left: 4px solid #667eea;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .sender-info {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        .sender-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: #667eea;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 20px;
            margin-right: 15px;
        }
        .sender-details {
            flex: 1;
        }
        .sender-name {
            font-weight: 600;
            font-size: 18px;
            color: #333;
            margin-bottom: 5px;
        }
        .message-time {
            font-size: 14px;
            color: #6c757d;
        }
        .message-content {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
            border: 1px solid #e9ecef;
        }
        .message-text {
            color: #212529;
            font-size: 16px;
            line-height: 1.6;
            margin: 0;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .attachment-notice {
            background-color: #e3f2fd;
            border-left: 4px solid #2196f3;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
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
            <h1>💬 New Message Received</h1>
        </div>
        
        <div class="content">
            <h2>Dear {{ $recipient->display_name ?? $recipient->first_name ?? 'Valued User' }},</h2>
            
            <p>You have received a new message in your conversation.</p>
            
            <div class="message-box">
                <div class="sender-info">
                    <div class="sender-avatar">
                        {{ strtoupper(substr($sender->display_name ?? $sender->first_name ?? 'U', 0, 1)) }}
                    </div>
                    <div class="sender-details">
                        <div class="sender-name">{{ $sender->display_name ?? ($sender->first_name . ' ' . $sender->last_name) ?? 'User' }}</div>
                        <div class="message-time">{{ \Carbon\Carbon::parse($chatMessage->created_at)->format('F d, Y h:i A') }}</div>
                    </div>
                </div>
                
                @if($chatMessage->message)
                <div class="message-content">
                    <p class="message-text">{{ $chatMessage->message }}</p>
                </div>
                @endif
                
                @if($chatMessage->attachment_path)
                <div class="attachment-notice">
                    📎 <strong>Attachment:</strong> A file has been shared with you
                </div>
                @endif
            </div>
            
            <div class="button-container">
                <a href="{{ url('/messages') }}" class="button">View Message</a>
            </div>
            
            <p style="margin-top: 30px; color: #6c757d; font-size: 14px;">
                <strong>Note:</strong> This is an automated email notification. You can reply to this message by logging into your account.
            </p>
        </div>
        
        <div class="footer">
            <p>Thank you for using our services!</p>
            <p>This is an automated email, please do not reply.</p>
        </div>
    </div>
</body>
</html>

