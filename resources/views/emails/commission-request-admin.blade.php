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
    <h1>⚙️ New Commission Change Request</h1>
    <p>A provider has requested a commission change — your review is needed</p>
  </div>
  <div class="body">

    <div class="alert-box">
      <p>📋 <strong>Action Required:</strong> Please review this request and communicate with both parties via the Helpdesk before approving or rejecting.</p>
    </div>

    <div class="info-row">
      <span class="info-label">Handyman</span>
      <span class="info-value">{{ $handyman->display_name }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">Handyman Email</span>
      <span class="info-value">{{ $handyman->email }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">Provider</span>
      <span class="info-value">{{ $provider->display_name }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">Provider Email</span>
      <span class="info-value">{{ $provider->email }}</span>
    </div>

    <div class="commission-box">
      <div class="c-card current">
        <div class="c-label">Current Commission</div>
        <div class="c-value">{{ $currentCommission }}%</div>
      </div>
      <div class="c-card requested">
        <div class="c-label">Requested Commission</div>
        <div class="c-value">{{ $requestedCommission }}%</div>
      </div>
    </div>

    <div class="reason-box">
      <div class="r-label">Reason Given by Provider</div>
      {{ $reason }}
    </div>

    <div style="text-align:center;">
      <a href="{{ $reviewUrl }}" class="btn">Review Request in Admin Panel</a>
    </div>

  </div>
  <div class="footer">
    This is an automated notification. Do not reply to this email.<br>
    Please use the Admin Panel or Helpdesk thread to respond.
  </div>
</div>
</body>
</html>
