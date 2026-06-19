<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Handyman Account Credentials</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; background: #f4f4f4; }
        .wrapper { background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,.08); }
        .header { background: linear-gradient(135deg, #3333ff 0%, #6366f1 100%); color: white; padding: 35px 30px; text-align: center; }
        .header h1 { margin: 0 0 6px; font-size: 24px; }
        .header p { margin: 0; opacity: .85; font-size: 14px; }
        .content { padding: 30px; }
        .greeting { font-size: 16px; margin-bottom: 16px; }
        .intro { color: #555; margin-bottom: 24px; font-size: 15px; }
        .credentials-box { background: #f0f4ff; border: 2px solid #3333ff; border-radius: 10px; padding: 24px; margin: 24px 0; }
        .credentials-box h3 { margin: 0 0 16px; color: #3333ff; font-size: 16px; text-transform: uppercase; letter-spacing: .05em; }
        .cred-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #d0d9ff; }
        .cred-row:last-child { border-bottom: none; }
        .cred-label { font-weight: bold; color: #555; font-size: 14px; }
        .cred-value { font-family: monospace; font-size: 15px; color: #1a1a2e; font-weight: bold; }
        .btn { display: inline-block; background: #3333ff; color: #fff !important; padding: 14px 32px; border-radius: 8px; text-decoration: none; font-weight: bold; font-size: 15px; margin: 20px 0; }
        .warning-box { background: #fff8e1; border-left: 4px solid #f59e0b; border-radius: 5px; padding: 14px 18px; margin: 20px 0; font-size: 14px; color: #7c5a00; }
        .info-box { background: #f0fdf4; border-left: 4px solid #22c55e; border-radius: 5px; padding: 14px 18px; margin: 20px 0; font-size: 14px; color: #166534; }
        .footer { background: #f8fafc; padding: 20px 30px; text-align: center; font-size: 12px; color: #888; border-top: 1px solid #e8ecf0; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <h1>🔑 Welcome to {{ config('app.display_name', 'Frobster') }}</h1>
        <p>Your handyman account has been created</p>
    </div>

    <div class="content">
        <p class="greeting">Hello <strong>{{ $handyman->display_name }}</strong>,</p>

        <p class="intro">
            Your handyman account on <strong>{{ config('app.display_name', 'Frobster') }}</strong> has been created by
            <strong>{{ $provider->display_name }}</strong>. Below are your login credentials to access the platform.
        </p>

        <div class="credentials-box">
            <h3>🔐 Your Login Credentials</h3>
            <div class="cred-row">
                <span class="cred-label">Email</span>
                <span class="cred-value">{{ $handyman->email }}</span>
            </div>
            <div class="cred-row">
                <span class="cred-label">Password</span>
                <span class="cred-value">{{ $plainPassword }}</span>
            </div>
            <div class="cred-row">
                <span class="cred-label">Login URL</span>
                <span class="cred-value">{{ $loginUrl }}</span>
            </div>
        </div>

        <div style="text-align:center;">
            <a href="{{ $loginUrl }}" class="btn">Login to Your Account →</a>
        </div>

        <div class="warning-box">
            ⚠️ <strong>Important:</strong> Please change your password after your first login to keep your account secure.
        </div>

        <div class="info-box">
            ℹ️ Your commission rate has been set by your provider. If you have any questions about your account,
            please contact your provider through the Helpdesk system inside the platform.
        </div>

        <p style="color:#888; font-size:13px; margin-top:24px;">
            If you did not expect this email, please contact <strong>{{ config('app.display_name', 'Frobster') }}</strong> support immediately.
        </p>
    </div>

    <div class="footer">
        <p>© {{ date('Y') }} {{ config('app.display_name', 'Frobster') }}. All rights reserved.</p>
        <p>This email was sent to {{ $handyman->email }}</p>
    </div>
</div>
</body>
</html>
