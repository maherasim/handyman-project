<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
  body { margin:0; padding:0; background:#f4f6f9; font-family: 'Segoe UI', Arial, sans-serif; }
  .wrapper { max-width:600px; margin:30px auto; background:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,0.08); }
  .header { background:linear-gradient(135deg,#3333ff 0%,#6366f1 100%); padding:36px 32px; text-align:center; }
  .header h1 { color:#fff; font-size:22px; margin:0; font-weight:700; }
  .header p { color:rgba(255,255,255,0.85); font-size:14px; margin:8px 0 0; }
  .body { padding:32px; }
  .alert-box { background:#fff8e1; border-left:4px solid #f59e0b; border-radius:6px; padding:16px 20px; margin-bottom:24px; }
  .alert-box p { margin:0; font-size:14px; color:#92400e; }
  .info-row { display:flex; justify-content:space-between; padding:12px 0; border-bottom:1px solid #f1f5f9; font-size:14px; }
  .info-row:last-child { border-bottom:none; }
  .info-label { color:#64748b; font-weight:600; }
  .info-value { color:#1e293b; text-align:right; }
  .commission-box { display:flex; gap:16px; margin:24px 0; }
  .c-card { flex:1; padding:16px; border-radius:8px; text-align:center; }
  .c-card.current { background:#fef2f2; border:1px solid #fecaca; }
  .c-card.requested { background:#f0fdf4; border:1px solid #bbf7d0; }
  .c-card .c-label { font-size:12px; font-weight:600; color:#64748b; margin-bottom:6px; }
  .c-card .c-value { font-size:28px; font-weight:800; }
  .c-card.current .c-value { color:#ef4444; }
  .c-card.requested .c-value { color:#22c55e; }
  .reason-box { background:#f8fafc; border-radius:8px; padding:16px 20px; margin:20px 0; font-size:14px; color:#334155; }
  .reason-box .r-label { font-size:12px; font-weight:700; color:#94a3b8; margin-bottom:8px; text-transform:uppercase; letter-spacing:.05em; }
  .btn { display:inline-block; background:linear-gradient(135deg,#3333ff,#6366f1); color:#fff !important; text-decoration:none; padding:14px 32px; border-radius:8px; font-weight:700; font-size:15px; margin-top:24px; }
  .footer { background:#f8fafc; padding:20px 32px; text-align:center; font-size:12px; color:#94a3b8; }
</style>
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
