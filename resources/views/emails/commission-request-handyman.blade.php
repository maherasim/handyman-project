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
  .hi-box { font-size:16px; color:#1e293b; margin-bottom:20px; }
  .info-box { background:#f0f9ff; border:1px solid #bae6fd; border-radius:8px; padding:20px; margin-bottom:20px; font-size:14px; color:#0369a1; }
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
  .steps { list-style:none; padding:0; margin:20px 0; }
  .steps li { display:flex; align-items:flex-start; gap:12px; padding:10px 0; font-size:14px; color:#475569; border-bottom:1px solid #f1f5f9; }
  .steps li:last-child { border-bottom:none; }
  .step-num { background:#3333ff; color:#fff; border-radius:50%; width:22px; height:22px; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:700; flex-shrink:0; margin-top:1px; }
  .btn { display:inline-block; background:linear-gradient(135deg,#3333ff,#6366f1); color:#fff !important; text-decoration:none; padding:14px 32px; border-radius:8px; font-weight:700; font-size:15px; margin-top:24px; }
  .footer { background:#f8fafc; padding:20px 32px; text-align:center; font-size:12px; color:#94a3b8; }
</style>
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
