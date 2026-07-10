<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('emails._email_styles')
</head>
<body>
<div class="wrapper">
  <div class="header">
    <h1>💼 Commission Change Requested</h1>
    <p>Your provider has submitted a commission change request for your profile</p>
  </div>
  <div class="body">

    <div class="hi-box">
      Hello <strong>{{ $handyman->display_name }}</strong>,
    </div>

    <div class="info-box">
      ℹ️ <strong>{{ $provider->display_name }}</strong> (your provider) has submitted a request to change your commission rate. The admin will review this request and may reach out to you via the Helpdesk for discussion before any change is approved.
    </div>

    <div class="commission-box">
      <div class="c-card current">
        <div class="c-label">Your Current Commission</div>
        <div class="c-value">{{ $currentCommission }}%</div>
      </div>
      <div class="c-card requested">
        <div class="c-label">Requested Commission</div>
        <div class="c-value">{{ $requestedCommission }}%</div>
      </div>
    </div>

    <div class="reason-box">
      <div class="r-label">Provider's Reason</div>
      {{ $reason }}
    </div>

    <p style="font-size:14px;color:#475569;font-weight:600;margin-bottom:8px;">What happens next:</p>
    <ul class="steps">
      <li>
        <span class="step-num">1</span>
        <span>The admin reviews the request and may open a discussion in the Helpdesk</span>
      </li>
      <li>
        <span class="step-num">2</span>
        <span>You will receive a Helpdesk message — you can reply with your thoughts</span>
      </li>
      <li>
        <span class="step-num">3</span>
        <span>Once both parties agree, the admin approves and the commission is updated</span>
      </li>
    </ul>

    <div style="text-align:center;">
      <a href="{{ $helpdeskUrl }}" class="btn">View Discussion in Helpdesk</a>
    </div>

  </div>
  <div class="footer">
    This is an automated notification from your handyman platform.<br>
    If you have questions, please respond via the Helpdesk.
  </div>
</div>
</body>
</html>
