<!DOCTYPE html>
{{-- ============================================================
     resources/views/privacy-policy-pdf.blade.php
     DomPDF-compatible view — no Bootstrap, no Google Fonts CDN,
     no flex/grid (DomPDF has limited CSS support).
     Theme: Navy + Teal. Fonts: DejaVu Sans (built-in to DomPDF).
     ============================================================ --}}
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
/* ── Reset ── */
*{margin:0;padding:0;box-sizing:border-box}
body{
    font-family: DejaVu Sans, sans-serif;
    font-size: 10pt;
    color: #1a2540;
    background: #fff;
    line-height: 1.65;
}

/* ── Cover Page ── */
.cover{
    text-align: center;
    padding: 80px 60px 60px;
    background: #0a1628;
    color: #fff;
    page-break-after: always;
}
.cover-logo{
    font-size: 28pt;
    font-weight: 700;
    color: #00c98d;
    letter-spacing: -1px;
    margin-bottom: 8px;
}
.cover-logo span{ color: #fff; }
.cover-tagline{
    font-size: 10pt;
    color: #8a9bbf;
    margin-bottom: 48px;
}
.cover-title{
    font-size: 22pt;
    font-weight: 700;
    color: #fff;
    margin-bottom: 8px;
}
.cover-subtitle{
    font-size: 12pt;
    color: #00c98d;
    margin-bottom: 40px;
}
.cover-meta{
    font-size: 9pt;
    color: #8a9bbf;
    margin-bottom: 6px;
}
.cover-line{
    border: none;
    border-top: 1px solid rgba(255,255,255,0.1);
    margin: 32px 0;
}
.cover-address{
    font-size: 9pt;
    color: #8a9bbf;
    line-height: 1.7;
}

/* ── Page header / footer ── */
@page{
    margin: 22mm 18mm 20mm 18mm;
}

/* ── Section heading ── */
.section-divider{
    border: none;
    border-top: 2px solid #00c98d;
    margin: 24px 0 20px;
}
.section-block-title{
    font-size: 14pt;
    font-weight: 700;
    color: #0a1628;
    margin-bottom: 16px;
    padding-bottom: 6px;
    border-bottom: 1px solid #e8edf5;
}

/* ── Policy section ── */
.ps{
    margin-bottom: 28px;
    padding-bottom: 28px;
    border-bottom: 1px solid #eef2f8;
}
.ps:last-child{ border-bottom: none; }

.ps-header{
    margin-bottom: 10px;
}
.ps-num-inline{
    display: inline;
    font-size: 9pt;
    font-weight: 700;
    color: #00c98d;
    background: rgba(0,201,141,0.1);
    border: 1px solid rgba(0,201,141,0.2);
    padding: 2px 7px;
    border-radius: 4px;
    margin-right: 6px;
}
.ps-title{
    font-size: 13pt;
    font-weight: 700;
    color: #0a1628;
    display: inline;
}

.ps p{
    font-size: 10pt;
    color: #374151;
    margin-bottom: 10px;
    line-height: 1.7;
}

/* ── Info box ── */
.ps-infobox{
    background: #f8faff;
    border-left: 4px solid #00c98d;
    padding: 12px 14px;
    margin: 12px 0;
    font-size: 10pt;
    color: #374151;
    line-height: 1.65;
}

/* ── Highlight box ── */
.ps-highlight{
    background: #0a1628;
    border-radius: 8px;
    padding: 16px 18px;
    margin: 14px 0;
    font-size: 10pt;
    color: #e8f0fe;
    line-height: 1.7;
}
.ps-highlight strong{ color: #00c98d; }

/* ── List ── */
.ps-list{
    margin: 10px 0 10px 0;
    padding: 0;
    list-style: none;
}
.ps-list li{
    font-size: 10pt;
    color: #374151;
    padding: 4px 0 4px 18px;
    position: relative;
    line-height: 1.65;
}
.ps-list li::before{
    content: "✓";
    position: absolute;
    left: 0;
    color: #00c98d;
    font-weight: 700;
}
.ps-list li.cross::before{ content: "✗"; color: #e53e3e; }
.ps-list li.warn::before{ content: "!"; color: #f6ad55; }
.ps-list li.cal::before{ content: "◆"; color: #00aaff; }
.ps-list li.down::before{ content: "↓"; color: #00aaff; }

/* ── Table ── */
.ps-table{
    width: 100%;
    border-collapse: collapse;
    margin: 12px 0;
    font-size: 9.5pt;
}
.ps-table thead tr{ background: #0a1628; }
.ps-table thead th{
    padding: 9px 12px;
    color: #fff;
    font-weight: 700;
    text-align: left;
    font-size: 9pt;
}
.ps-table tbody tr{ border-bottom: 1px solid #eef2f8; }
.ps-table tbody tr:nth-child(even){ background: #f8faff; }
.ps-table tbody td{
    padding: 9px 12px;
    color: #374151;
    vertical-align: top;
    line-height: 1.5;
}
.ps-table tbody td:first-child{ font-weight: 600; color: #0a1628; }

/* ── Ordered list ── */
.ps-ol{
    padding-left: 20px;
    font-size: 10pt;
    color: #374151;
    line-height: 1.7;
    margin: 10px 0;
}
.ps-ol li{ margin-bottom: 6px; }

/* ── Intro box ── */
.policy-intro{
    background: #f0faf6;
    border: 1px solid rgba(0,201,141,0.2);
    border-radius: 6px;
    padding: 16px 18px;
    margin-bottom: 24px;
    font-size: 10pt;
    color: #374151;
    line-height: 1.7;
}

/* ── Contact card ── */
.contact-card{
    background: #0a1628;
    border-radius: 8px;
    padding: 22px 20px;
    margin-top: 32px;
    text-align: center;
    color: #e8f0fe;
}
.contact-card h3{
    font-size: 13pt;
    font-weight: 700;
    color: #fff;
    margin-bottom: 8px;
}
.contact-card p{ font-size: 10pt; color: #8a9bbf; margin-bottom: 12px; }
.contact-card a{ color: #00c98d; text-decoration: none; }

/* ── Page break helpers ── */
.page-break{ page-break-before: always; }
</style>
</head>
<body>

{{-- ── COVER PAGE ── --}}
<div class="cover">
    <div class="cover-logo">GST<span>pilot</span></div>
    <div class="cover-tagline">GST Compliance Automation Platform</div>

    <hr class="cover-line">

    <div class="cover-title">Privacy &amp; Refund Policy</div>
    <div class="cover-subtitle">Legal Documentation — Version 1.0</div>

    <div class="cover-meta">Effective Date: 1 January 2026</div>
    <div class="cover-meta">Last Updated: 1 January 2026</div>
    <div class="cover-meta">Governed by Indian Law &nbsp;|&nbsp; DPDPA 2023 Compliant &nbsp;|&nbsp; ISO 27001 Certified</div>

    <hr class="cover-line">

    <div class="cover-address">
        GSTpilot Technologies Private Limited<br>
        Office No. 458, 4th Floor, Agarwal Metro Heights Tower<br>
        Netaji Subhash Place, Pitampura, Delhi – 110034, India<br>
        support@gstpilot.in &nbsp;|&nbsp; +91-8076865836
    </div>
</div>

{{-- ── INTRO ── --}}
<div class="policy-intro">
    <strong>GSTpilot</strong> ("we", "us", "our") is committed to protecting the privacy and security of your personal and financial data.
    This Privacy &amp; Refund Policy explains what information we collect, why we collect it, how we protect it, and what rights you have over it,
    as well as our refund and cancellation terms. By using GSTpilot's platform, website, or any associated service, you agree to the practices
    described in this Policy.
</div>

{{-- ════════════════════════════════════════
     PRIVACY POLICY
     ════════════════════════════════════════ --}}
<div class="section-block-title">Part A — Privacy Policy</div>

<div class="ps">
    <div class="ps-header">
        <span class="ps-num-inline">01</span><span class="ps-title">Who We Are</span>
    </div>
    <p>GSTpilot is a GST compliance automation platform developed and operated by <strong>GSTpilot Technologies Private Limited</strong>, founded in 2026 and registered under the Companies Act, 2013. GSTpilot operates as a <strong>GST Suvidha Provider (GSP)</strong> certified by the GSTN, and holds ISO 27001 certification.</p>
    <p>For the purposes of the Digital Personal Data Protection Act, 2023 (DPDPA), GSTpilot Technologies Private Limited is the <strong>Data Fiduciary</strong> responsible for your personal data.</p>
    <div class="ps-infobox">
        <strong>Registered Address:</strong> Office No. 458, 4th Floor, Agarwal Metro Heights Tower, Netaji Subhash Place, Pitampura, Delhi – 110034, India<br>
        <strong>Email:</strong> support@gstpilot.in
    </div>
</div>

<div class="ps">
    <div class="ps-header">
        <span class="ps-num-inline">02</span><span class="ps-title">Data We Collect</span>
    </div>
    <p>We collect only the data necessary to provide our GST compliance services:</p>
    <table class="ps-table">
        <thead><tr><th>Category</th><th>Examples</th><th>Why Collected</th></tr></thead>
        <tbody>
            <tr><td>Account Data</td><td>Name, email, mobile, business name, GSTIN, PAN</td><td>Account creation, identity verification</td></tr>
            <tr><td>Financial &amp; Tax Data</td><td>Bank statements, purchase registers, marketplace reports, invoice data</td><td>GSTR-1 processing, reconciliation</td></tr>
            <tr><td>Usage Data</td><td>IP address, browser type, pages visited, session duration</td><td>Security monitoring, platform improvement</td></tr>
            <tr><td>Communication Data</td><td>Support tickets, chat transcripts, email correspondence</td><td>Customer support, dispute resolution</td></tr>
            <tr><td>Payment Data</td><td>Transaction ID, plan selected, billing address</td><td>Subscription management (card details NOT stored)</td></tr>
        </tbody>
    </table>
    <p>We do not collect biometric data, religious beliefs, political opinions, or health information.</p>
</div>

<div class="ps">
    <div class="ps-header">
        <span class="ps-num-inline">03</span><span class="ps-title">How We Use Your Data</span>
    </div>
    <p>Your data is used exclusively for the following purposes:</p>
    <ul class="ps-list">
        <li><strong>GST Return Processing:</strong> Generating GSTR-1, GSTR-3B from uploaded data. Files are processed in-memory and not retained after processing.</li>
        <li><strong>2A/2B Reconciliation:</strong> Matching purchase data against GST portal records to maximise ITC claims.</li>
        <li><strong>Bank Statement Conversion:</strong> Converting uploaded bank PDFs to Tally-compatible XML. Files purged after download.</li>
        <li><strong>Account Management:</strong> Maintaining your account, processing payments, managing subscriptions.</li>
        <li><strong>Legal Compliance:</strong> Retaining data as required under Indian tax law, IT Act 2000, and GSTN regulations.</li>
        <li><strong>Platform Security:</strong> Detecting and preventing unauthorised access, fraud, and abuse.</li>
        <li><strong>Service Communications:</strong> Filing reminders, product updates, support responses. Marketing emails are opt-in only.</li>
    </ul>
    <div class="ps-highlight">
        <p><strong>We do not use your financial or tax data to train AI models, serve advertisements, or profile you for marketing purposes.</strong></p>
    </div>
</div>

<div class="ps">
    <div class="ps-header">
        <span class="ps-num-inline">04</span><span class="ps-title">Data Storage &amp; Security</span>
    </div>
    <p>GSTpilot is ISO 27001 certified. We protect your data through:</p>
    <ul class="ps-list">
        <li><strong>Encryption in Transit:</strong> TLS 1.3 enforced on every page and API endpoint.</li>
        <li><strong>Encryption at Rest:</strong> AES-256 encryption for all stored data.</li>
        <li><strong>Access Controls:</strong> Multi-factor authentication and audit logging for all production access.</li>
        <li><strong>Indian Data Centres:</strong> All primary data stored on India-based servers.</li>
        <li><strong>Regular Security Audits:</strong> Independent penetration testing and vulnerability assessments.</li>
        <li><strong>File Purging:</strong> Uploaded processing files are purged after session ends or output is downloaded.</li>
    </ul>
    <div class="ps-infobox">
        In the event of a data breach, we will notify you and the relevant authority within 72 hours as required by the DPDPA 2023.
    </div>
</div>

<div class="ps">
    <div class="ps-header">
        <span class="ps-num-inline">05</span><span class="ps-title">Sharing &amp; Disclosure of Data</span>
    </div>
    <p>We do not sell, rent, or trade your personal data. We may share limited data only in these circumstances:</p>
    <ul class="ps-list">
        <li><strong>GSTN / GST Portal:</strong> Only when you explicitly initiate a filing or reconciliation fetch.</li>
        <li><strong>Payment Processors (Razorpay/PayU):</strong> Transaction amounts and billing reference only — never your tax data.</li>
        <li><strong>Cloud Infrastructure:</strong> ISO 27001 certified, India-based hosting provider with no access to data content.</li>
        <li><strong>Legal Requirements:</strong> When required by law, court order, or competent government authority.</li>
    </ul>
    <div class="ps-highlight">
        <p><strong>We never share your financial data, tax data, or GSTIN information with advertisers, data brokers, or any commercial third party.</strong></p>
    </div>
</div>

<div class="ps">
    <div class="ps-header">
        <span class="ps-num-inline">06</span><span class="ps-title">Cookies &amp; Tracking Technologies</span>
    </div>
    <p>We use cookies to make the platform work and improve your experience. We do not use third-party advertising cookies.</p>
    <table class="ps-table">
        <thead><tr><th>Cookie Type</th><th>Purpose</th><th>Duration</th></tr></thead>
        <tbody>
            <tr><td>Essential</td><td>Login session, CSRF protection, platform functionality</td><td>Session / 30 days</td></tr>
            <tr><td>Preference</td><td>Language, theme, and display preferences</td><td>1 year</td></tr>
            <tr><td>Analytics</td><td>Aggregate usage statistics (no personal identification)</td><td>90 days</td></tr>
            <tr><td>Security</td><td>Fraud detection and bot prevention</td><td>Session</td></tr>
        </tbody>
    </table>
    <p>We do not use Facebook Pixel, Google Ads remarketing, or any advertising tracking technology.</p>
</div>

<div class="ps">
    <div class="ps-header">
        <span class="ps-num-inline">07</span><span class="ps-title">Data Retention</span>
    </div>
    <table class="ps-table">
        <thead><tr><th>Data Type</th><th>Retention Period</th><th>Reason</th></tr></thead>
        <tbody>
            <tr><td>Account Information</td><td>Duration of account + 3 years</td><td>Legal obligation, dispute resolution</td></tr>
            <tr><td>GST Return Data (filed)</td><td>8 years from date of filing</td><td>GST law audit requirement</td></tr>
            <tr><td>Uploaded Processing Files</td><td>Purged after session / download</td><td>No retention needed post-processing</td></tr>
            <tr><td>Payment Records</td><td>8 years</td><td>Income Tax Act, GST Act requirements</td></tr>
            <tr><td>Support Tickets</td><td>3 years</td><td>Service quality, dispute resolution</td></tr>
            <tr><td>Usage Logs</td><td>90 days</td><td>Security monitoring and debugging</td></tr>
        </tbody>
    </table>
</div>

<div class="ps">
    <div class="ps-header">
        <span class="ps-num-inline">08</span><span class="ps-title">Your Rights Under Indian Law (DPDPA 2023)</span>
    </div>
    <ul class="ps-list">
        <li><strong>Right to Access:</strong> Request a copy of the personal data we hold about you.</li>
        <li><strong>Right to Correction:</strong> Request correction of inaccurate or incomplete personal data.</li>
        <li><strong>Right to Erasure:</strong> Request deletion when data is no longer needed (subject to legal retention).</li>
        <li><strong>Right to Withdraw Consent:</strong> Withdraw consent for data processing at any time.</li>
        <li><strong>Right to Grievance Redressal:</strong> Lodge a complaint with our Grievance Officer or the Data Protection Board of India.</li>
        <li><strong>Right to Nominate:</strong> Nominate another individual to exercise rights on your behalf.</li>
    </ul>
    <p>Email <strong>support@gstpilot.in</strong> from your registered email address. We respond within 30 days.</p>
</div>

<div class="ps">
    <div class="ps-header">
        <span class="ps-num-inline">09</span><span class="ps-title">Children's Privacy</span>
    </div>
    <p>GSTpilot is intended exclusively for adults (18+) who are GST-registered business owners, accountants, or tax professionals. We do not knowingly collect data from minors. Contact support@gstpilot.in immediately if you believe a minor has used our platform.</p>
</div>

<div class="ps">
    <div class="ps-header">
        <span class="ps-num-inline">10</span><span class="ps-title">Third‑Party Links &amp; Integrations</span>
    </div>
    <p>GSTpilot may link to gst.gov.in, income tax portal, and Tally documentation. These external sites have their own privacy policies. For marketplace integrations (Amazon, Flipkart, Meesho), GSTpilot only receives the report file you upload — we do not maintain API connections to read your seller data.</p>
</div>

<div class="ps">
    <div class="ps-header">
        <span class="ps-num-inline">11</span><span class="ps-title">International Data Transfers</span>
    </div>
    <p>All primary user data is stored on India-based servers. Your GSTIN data, financial records, and tax return data are never transferred outside India under any circumstances.</p>
</div>

<div class="ps">
    <div class="ps-header">
        <span class="ps-num-inline">12</span><span class="ps-title">GSP Access &amp; GST Portal Data</span>
    </div>
    <ul class="ps-list">
        <li>We access the GST portal only on your explicit instruction.</li>
        <li>Your GST portal credentials are never stored — authentication goes directly to GSTN via OTP-based government APIs.</li>
        <li>GSTR-2A/2B records are stored in your account for the return period plus statutory retention.</li>
        <li>All GSP-related handling complies with GSTN data sharing and security guidelines.</li>
    </ul>
</div>

<div class="ps">
    <div class="ps-header">
        <span class="ps-num-inline">13</span><span class="ps-title">Changes to This Policy</span>
    </div>
    <p>Material changes will be communicated by email (at least 15 days prior), a dashboard notice on login, and updating the "Last Updated" date. Continued use after the effective date constitutes acceptance.</p>
</div>

<div class="ps">
    <div class="ps-header">
        <span class="ps-num-inline">14</span><span class="ps-title">Contact &amp; Grievance Redressal</span>
    </div>
    <div class="ps-infobox">
        <strong>Email:</strong> support@gstpilot.in &nbsp;|&nbsp; <strong>Phone/WhatsApp:</strong> +91-8076865836<br>
        <strong>Address:</strong> Office No. 458, 4th Floor, Agarwal Metro Heights Tower, Netaji Subhash Place, Pitampura, Delhi – 110034<br>
        <strong>Response Time:</strong> Within 30 days of receipt
    </div>
</div>

{{-- ════════════════════════════════════════
     REFUND & CANCELLATION POLICY
     ════════════════════════════════════════ --}}
<div class="page-break"></div>
<div class="section-block-title">Part B — Refund &amp; Cancellation Policy</div>

<p style="font-size:10pt;color:#374151;margin-bottom:22px;line-height:1.7;">
    At GSTpilot, we stand behind our platform. This Refund &amp; Cancellation Policy explains when and how you can request a refund, how cancellations work, and what happens to your data and subscription access.
</p>

<div class="ps">
    <div class="ps-header">
        <span class="ps-num-inline">15</span><span class="ps-title">Refund Policy Overview</span>
    </div>
    <p>GSTpilot offers a straightforward refund process for eligible subscriptions. Refunds are processed based on the plan type (monthly or annual) and the reason for the refund request.</p>
</div>

<div class="ps">
    <div class="ps-header">
        <span class="ps-num-inline">16</span><span class="ps-title">Eligible Refunds</span>
    </div>
    <ul class="ps-list">
        <li><strong>Monthly Plans:</strong> Full refund if requested within 7 days of initial purchase. After 7 days — no refund.</li>
        <li><strong>Annual Plans:</strong> Pro‑rated refund for unused months if cancelled within first 30 days. After 30 days — non‑refundable except for service failure.</li>
        <li><strong>Service Outages:</strong> Partial refund or service credit if GSTpilot has a significant outage.</li>
        <li><strong>Duplicate Payments:</strong> Full refund for any duplicate transaction.</li>
    </ul>
    <p>Submit all refund requests to <strong>support@gstpilot.in</strong> with subject "Refund Request".</p>
</div>

<div class="ps">
    <div class="ps-header">
        <span class="ps-num-inline">17</span><span class="ps-title">Non‑Refundable Cases</span>
    </div>
    <ul class="ps-list">
        <li class="cross"><strong>Change of Mind</strong> after the eligible refund window.</li>
        <li class="cross"><strong>Failure to Use</strong> the platform during the subscription period.</li>
        <li class="cross"><strong>Incorrect Data Provided</strong> by you resulting in filing errors.</li>
        <li class="cross"><strong>Third‑Party Issues</strong> (GST portal downtime, ISP failures, etc.)</li>
        <li class="cross"><strong>Plan Downgrades</strong> — difference is credited as account credit, not cash.</li>
    </ul>
</div>

<div class="ps">
    <div class="ps-header">
        <span class="ps-num-inline">18</span><span class="ps-title">Annual Plan Refunds</span>
    </div>
    <p>If you cancel your annual subscription within the first 30 days, you receive a <strong>pro‑rata refund</strong> for unused months. Calculation: Total Annual Fee ÷ 12 × Unused Months.</p>
    <div class="ps-highlight">
        <p><strong>Example:</strong> Paid ₹12,000 for an annual plan. Cancel after 2 months. Refund = ₹10,000 (10 unused months × ₹1,000/month).</p>
    </div>
    <p>After 30 days, annual plans are non‑refundable except in cases of proven service failure.</p>
</div>

<div class="ps">
    <div class="ps-header">
        <span class="ps-num-inline">19</span><span class="ps-title">How to Request a Refund</span>
    </div>
    <ol class="ps-ol">
        <li>Email <strong>support@gstpilot.in</strong> with subject "Refund Request – [Your GSTIN or Email]".</li>
        <li>Include your full name, registered email, and reason for the request.</li>
        <li>Attach supporting documents if applicable (e.g., duplicate payment receipt).</li>
        <li>Our support team will respond within 2 business days.</li>
        <li>If approved, the refund will be processed per the timeline in Section 20.</li>
    </ol>
</div>

<div class="ps">
    <div class="ps-header">
        <span class="ps-num-inline">20</span><span class="ps-title">Refund Timeline</span>
    </div>
    <table class="ps-table">
        <thead><tr><th>Refund Type</th><th>Approval Time</th><th>Credit to Source</th></tr></thead>
        <tbody>
            <tr><td>Monthly Plan Refund</td><td>Within 2 business days</td><td>5 – 7 business days</td></tr>
            <tr><td>Annual Plan (Pro‑rata)</td><td>Within 3 business days</td><td>5 – 7 business days</td></tr>
            <tr><td>Duplicate Payment</td><td>Within 1 business day</td><td>3 – 5 business days</td></tr>
        </tbody>
    </table>
    <p>GSTpilot is not responsible for additional delays caused by third‑party payment processors or banks.</p>
</div>

<div class="ps">
    <div class="ps-header">
        <span class="ps-num-inline">21</span><span class="ps-title">Cancellation Policy</span>
    </div>
    <ul class="ps-list">
        <li class="cal"><strong>Monthly Plans:</strong> Cancellation takes effect at end of current billing cycle. Access retained until period end.</li>
        <li class="cal"><strong>Annual Plans:</strong> Cancellation at end of annual cycle. Pro‑rata refund if within first 30 days; otherwise access continues to year-end.</li>
        <li class="cal"><strong>How to Cancel:</strong> Dashboard → Billing &amp; Plans → Cancel Subscription. Or email support@gstpilot.in.</li>
    </ul>
    <p>Data remains available for 30 days after subscription ends, then permanently deleted unless you re‑subscribe.</p>
</div>

<div class="ps">
    <div class="ps-header">
        <span class="ps-num-inline">22</span><span class="ps-title">Plan Downgrades</span>
    </div>
    <ul class="ps-list">
        <li class="down"><strong>Monthly:</strong> New lower rate from next billing cycle. No refund for current month remainder.</li>
        <li class="down"><strong>Annual:</strong> Price difference credited to GSTpilot wallet as account credit. No cash refund.</li>
    </ul>
    <p>Downgrades may remove access to features available only in higher plans.</p>
</div>

<div class="ps">
    <div class="ps-header">
        <span class="ps-num-inline">23</span><span class="ps-title">Failed Payments</span>
    </div>
    <ul class="ps-list">
        <li class="warn">Payment retried up to 3 times over 5 days.</li>
        <li class="warn">After 3 failed attempts, subscription is paused and you receive email notification with payment update link.</li>
        <li class="warn">Subscription reinstated once payment succeeds. No late fees charged.</li>
        <li class="warn">Access may be suspended until outstanding amount is settled.</li>
    </ul>
</div>

{{-- CONTACT CARD --}}
<div class="contact-card">
    <h3>Questions about Privacy or Refunds?</h3>
    <p>Our team responds within 48 hours.</p>
    <p>
        <a href="mailto:support@gstpilot.in">support@gstpilot.in</a> &nbsp;|&nbsp;
        <a href="tel:+918076865836">+91-8076865836</a> &nbsp;|&nbsp;
        WhatsApp: +91-8076865836
    </p>
</div>

</body>
</html>