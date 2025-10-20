<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Policy Warning</title>
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
        <h2>Policy Warning: Sharing personal contact information</h2>
        <p>Hi {{ $name }},</p>
        <p>We detected that one of your recent chat messages appears to include personal contact information, which is not permitted on our platform.</p>
        @if(!empty($types))
        <p>Detected types:
            @foreach($types as $t)
                <span class="badge">{{ $t }}</span>
            @endforeach
        </p>
        @endif
        @if(!empty($snippet))
        <div class="card">
            <div class="small">Message excerpt ({{ $date }}):</div>
            <div>{{ $snippet }}</div>
        </div>
        @endif
        <p>Please keep all communications within the platform and avoid sharing emails, phone numbers, or messenger handles. Repeated violations may lead to account actions.</p>
        <p>Thank you for understanding and helping us keep transactions safe for everyone.</p>
        <p class="small">This is an automated message. If you believe this was a mistake, please contact support.</p>
    </div>
</body>
</html>

