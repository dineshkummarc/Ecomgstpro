<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    body{ font-family: Helvetica, Arial, sans-serif; color:#1a2a3a; font-size:12px; margin:0; padding:0; }
    .wrap{ padding:36px 40px; }
    table{ width:100%; border-collapse:collapse; }

    .head-table td{ vertical-align:top; }
    .brand{ font-size:22px; font-weight:bold; color:#0a1628; }
    .brand span{ color:#00c98d; }
    .brand-sub{ font-size:10px; color:#8a9bbf; margin-top:2px; }

    .inv-title{ font-size:20px; font-weight:bold; color:#0a1628; text-align:right; }
    .inv-meta{ font-size:11px; color:#4a5568; text-align:right; margin-top:4px; }
    .inv-meta strong{ color:#0a1628; }

    .divider{ border-bottom:2px solid #eef2f8; margin:20px 0; }

    .bill-to-label{ font-size:10px; font-weight:bold; text-transform:uppercase; letter-spacing:1px; color:#8a9bbf; margin-bottom:4px; }
    .bill-to-name{ font-size:13px; font-weight:bold; color:#0a1628; }
    .bill-to-detail{ font-size:11px; color:#4a5568; margin-top:2px; }

    .items-table{ margin-top:26px; }
    .items-table thead th{
        background:#0a1628; color:#fff; font-size:10px; text-transform:uppercase;
        letter-spacing:.5px; padding:10px 12px; text-align:left;
    }
    .items-table thead th.right{ text-align:right; }
    .items-table tbody td{ padding:12px; border-bottom:1px solid #eef2f8; font-size:12px; }
    .items-table tbody td.right{ text-align:right; }

    .totals-table{ margin-top:6px; width:260px; float:right; }
    .totals-table td{ padding:6px 10px; font-size:12px; }
    .totals-table .label{ color:#4a5568; }
    .totals-table .value{ text-align:right; color:#0a1628; }
    .totals-table .grand td{ border-top:2px solid #0a1628; font-weight:bold; font-size:14px; padding-top:10px; }
    .grand .value{ color:#00c98d; }

    .status-paid{
        display:inline-block; background:#d1fae5; color:#065f46;
        font-size:11px; font-weight:bold; padding:4px 12px; border-radius:20px;
    }

    .footer{ margin-top:60px; padding-top:16px; border-top:1px solid #eef2f8; font-size:10px; color:#94a3b8; text-align:center; }
    .clearfix{ clear:both; }
</style>
</head>
<body>
<div class="wrap">

    <table class="head-table">
        <tr>
            <td width="50%">
                <div class="brand">Ecomgst<span>Pro</span></div>
                <div class="brand-sub">GST Automation for Indian Businesses</div>
                <div class="brand-sub">Powered by FinTaxPro — Finance, Taxation &amp; Accounts</div>
            </td>
            <td width="50%">
                <div class="inv-title">INVOICE</div>
                <div class="inv-meta"><strong>Invoice #:</strong> {{ $invoice->invoice_number }}</div>
                <div class="inv-meta"><strong>Date:</strong> {{ $invoice->issued_at->format('d M Y') }}</div>
                <div class="inv-meta"><strong>Payment ID:</strong> {{ $payment->razorpay_payment_id }}</div>
            </td>
        </tr>
    </table>

    <div class="divider"></div>

    <table>
        <tr>
            <td width="50%">
                <div class="bill-to-label">Billed To</div>
                <div class="bill-to-name">{{ $user->name }}</div>
                <div class="bill-to-detail">{{ $user->email }}</div>
                @if($user->phone)<div class="bill-to-detail">{{ $user->phone }}</div>@endif
                @if($user->company_name)<div class="bill-to-detail">{{ $user->company_name }}</div>@endif
            </td>
            <td width="50%" style="text-align:right;">
                <span class="status-paid">PAID</span>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th>Description</th>
                <th>Billing Period</th>
                <th class="right">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong>{{ $plan->name }} Plan</strong><br>
                    <span style="color:#8a9bbf;font-size:10.5px;">Ecomgstpro subscription — {{ $plan->duration_months }} month(s)</span>
                </td>
                <td>
                    {{ $payment->plan_started_at->format('d M Y') }} – {{ $payment->plan_ends_at->format('d M Y') }}
                </td>
                <td class="right">₹{{ number_format($invoice->amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <table class="totals-table">
        <tr><td class="label">Subtotal</td><td class="value">₹{{ number_format($invoice->amount, 2) }}</td></tr>
        <tr><td class="label">Tax</td><td class="value">Inclusive</td></tr>
        <tr class="grand"><td class="label">Total Paid</td><td class="value">₹{{ number_format($invoice->amount, 2) }}</td></tr>
    </table>
    <div class="clearfix"></div>

    <div class="footer">
        This is a computer-generated invoice and does not require a signature.<br>
        EcomgstPro · support@ecomgstpro.com &nbsp;|&nbsp; Thank you for your business.
    </div>

</div>
</body>
</html>