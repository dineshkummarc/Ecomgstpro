<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Your trial is ending soon</title>
</head>
<body style="margin:0;padding:0;background:#f0f2f8;font-family:Arial,Helvetica,sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f0f2f8;padding:32px 12px;">
<tr>
<td align="center">

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#ffffff;border-radius:20px;overflow:hidden;box-shadow:0 8px 30px rgba(10,22,40,0.08);">

    <!-- HEADER -->
    <tr>
        <td style="background:linear-gradient(135deg,#0a1628 0%,#0d1f3c 60%,#0a2240 100%);padding:36px 32px;text-align:center;">
            <div style="display:inline-block;background:rgba(0,201,141,0.12);border:1px solid rgba(0,201,141,0.3);border-radius:30px;padding:6px 16px;font-size:11px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:#00c98d;margin-bottom:16px;">
                ⏳ Trial Ending Soon
            </div>
            <div style="font-family:Arial,sans-serif;font-weight:800;font-size:22px;color:#ffffff;">
                Ecomgst<span style="color:#00c98d;">Pro</span>
            </div>
        </td>
    </tr>

    <!-- BODY -->
    <tr>
        <td style="padding:36px 32px 8px;">
            <h1 style="font-size:21px;color:#0a1628;margin:0 0 14px;font-weight:800;">
                Hi {{ $name }}, your free trial ends on {{ $trialEndDate }}
            </h1>
            <p style="font-size:14.5px;line-height:1.7;color:#4a5568;margin:0 0 20px;">
                You've got just <strong style="color:#0a1628;">2 days left</strong> on your free Ecomgstpro trial.
                To keep filing GSTR-1 without any interruption — reconciliation, e-commerce parsing, and everything
                else you've been using — activate a plan before access pauses.
            </p>

            <!-- Pricing mini-table -->
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:20px 0;border-collapse:separate;border-spacing:0 8px;">
                <tr>
                    <td style="background:#f7faff;border:1px solid #e8edf5;border-radius:12px;padding:14px 18px;">
                        <table width="100%"><tr>
                            <td style="font-size:13.5px;font-weight:700;color:#0a1628;">Monthly</td>
                            <td align="right" style="font-size:13.5px;color:#4a5568;">₹99 / month</td>
                        </tr></table>
                    </td>
                </tr>
                <tr>
                    <td style="background:#f0fdfa;border:1px solid rgba(0,201,141,0.3);border-radius:12px;padding:14px 18px;">
                        <table width="100%"><tr>
                            <td style="font-size:13.5px;font-weight:700;color:#0a1628;">Quarterly <span style="color:#00c98d;font-size:11px;">· Save 4%</span></td>
                            <td align="right" style="font-size:13.5px;color:#4a5568;">₹95 / month</td>
                        </tr></table>
                    </td>
                </tr>
                <tr>
                    <td style="background:#f0fdfa;border:1.5px solid #00c98d;border-radius:12px;padding:14px 18px;">
                        <table width="100%"><tr>
                            <td style="font-size:13.5px;font-weight:700;color:#0a1628;">Yearly <span style="color:#00c98d;font-size:11px;">· Best Value, Save 12%</span></td>
                            <td align="right" style="font-size:13.5px;color:#4a5568;">₹87 / month</td>
                        </tr></table>
                    </td>
                </tr>
            </table>

            <!-- CTA -->
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:28px 0;">
                <tr>
                    <td align="center">
                        <a href="{{ $pricingUrl }}"
                           style="display:inline-block;background:linear-gradient(135deg,#00c98d,#00e0a8);color:#0a1628;font-weight:700;font-size:15px;text-decoration:none;padding:14px 36px;border-radius:12px;">
                            Choose a Plan &amp; Continue →
                        </a>
                    </td>
                </tr>
            </table>

            <p style="font-size:12.5px;line-height:1.6;color:#94a3b8;margin:0 0 4px;">
                If you don't activate a plan by <strong>{{ $trialEndDate }}</strong>, your account will be
                paused until you subscribe — your data stays safe and picks up right where you left off.
            </p>
        </td>
    </tr>

    <!-- FOOTER -->
    <tr>
        <td style="padding:24px 32px;border-top:1px solid #eef2f8;text-align:center;">
            <p style="font-size:11.5px;color:#94a3b8;margin:0;">
                EcomgstPro — GST Automation for Indian Businesses<br>
                Need help? Just reply to this email.
            </p>
        </td>
    </tr>

</table>

</td>
</tr>
</table>
</body>
</html>