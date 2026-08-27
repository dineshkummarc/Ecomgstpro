<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="margin:0;padding:0;background:#0a1628;font-family:'DM Sans',Arial,sans-serif;">
<div style="max-width:520px;margin:0 auto;padding:40px 20px;">
    <div style="background:#0d1f3c;border:1px solid rgba(255,255,255,.08);border-radius:20px;padding:36px;">
        <h2 style="color:#fff;font-size:20px;margin:0 0 16px;">Account Deactivated</h2>
        <p style="color:#c0cfe8;font-size:14px;line-height:1.6;margin:0 0 16px;">
            Hi {{ $userName }},
        </p>
        <p style="color:#c0cfe8;font-size:14px;line-height:1.6;margin:0 0 16px;">
            Your EcomgstPro account has been deactivated by our team. You will not be able to log in until it is reactivated.
        </p>
        <div style="background:rgba(255,92,122,.1);border:1px solid rgba(255,92,122,.25);border-radius:12px;padding:14px 16px;margin-bottom:20px;">
            <div style="color:#8a9bbf;font-size:12px;font-weight:600;text-transform:uppercase;margin-bottom:4px;">Reason</div>
            <div style="color:#ffb3c1;font-size:14px;">{{ $reason }}</div>
        </div>
        <p style="color:#8a9bbf;font-size:13px;line-height:1.6;">
            If you believe this was a mistake, please contact our support team.
        </p>
    </div>
</div>
</body>
</html>
