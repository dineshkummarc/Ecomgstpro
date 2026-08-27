@extends('layouts.app')

{{-- ============================================================
    ecomgstpro — refund.blade.php (FULL WIDTH VERSION)
     Theme: Dark Navy #0a1628 + Teal #00c98d + Sky #00aaff
     Changes:
       1. Removed PDF download option completely
       2. Full-width content with collapsible TOC drawer
     ============================================================ --}}

@section('content')

<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
:root{
    --navy:#0a1628;--deep:#0d1f3c;--teal:#00c98d;--sky:#00aaff;
    --teal-low:rgba(0,201,141,.12);--border:rgba(255,255,255,.08);
    --muted:#8a9bbf;--txt:#e8f0fe;
}
html,body{overflow-x:hidden}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'DM Sans',sans-serif;background:#f0f4ff;color:var(--navy)}
.gp-container{max-width:1200px;margin:0 auto;padding:0 32px}
.dot-grid{background-image:radial-gradient(rgba(255,255,255,.03) 1px,transparent 1px);background-size:26px 26px}

/* ── Hero ── */
.policy-hero{background:linear-gradient(135deg,var(--navy) 0%,var(--deep) 55%,#0a2240 100%);padding:80px 0 60px;position:relative;}
.policy-hero::before{content:'';position:absolute;width:600px;height:600px;border-radius:50%;background:radial-gradient(circle,rgba(0,170,255,.07) 0%,transparent 70%);top:-150px;right:-100px;pointer-events:none}
.policy-hero::after{content:'';position:absolute;width:400px;height:400px;border-radius:50%;background:radial-gradient(circle,rgba(0,201,141,.06) 0%,transparent 70%);bottom:-80px;left:-80px;pointer-events:none}
.policy-hero-inner{position:relative;z-index:2}
.ph-breadcrumb{display:flex;align-items:center;gap:8px;font-size:13px;color:var(--muted);margin-bottom:20px}
.ph-breadcrumb a{color:var(--muted);text-decoration:none;transition:color .2s}
.ph-breadcrumb a:hover{color:var(--teal)}
.ph-breadcrumb i{font-size:10px;color:var(--teal)}
.ph-badge{display:inline-flex;align-items:center;gap:8px;background:var(--teal-low);border:1px solid rgba(0,201,141,.25);border-radius:30px;padding:6px 16px;font-size:12px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--teal);margin-bottom:20px}
.ph-title{font-family:'Syne',sans-serif;font-weight:800;font-size:clamp(32px,4.5vw,54px);color:#fff;letter-spacing:-1px;line-height:1.1;margin-bottom:16px}
.ph-title em{color:var(--teal);font-style:normal}
.ph-meta{display:flex;align-items:center;gap:24px;flex-wrap:wrap;font-size:13px;color:var(--muted);margin-bottom:28px}
.ph-meta i{color:var(--teal)}
.ph-actions{display:flex;gap:12px;flex-wrap:wrap}
.btn-outline{display:inline-flex;align-items:center;gap:8px;background:transparent;border:1px solid rgba(255,255,255,.15);color:var(--txt);font-weight:600;font-size:14px;padding:11px 22px;border-radius:10px;text-decoration:none;transition:all .2s}
.btn-outline:hover{border-color:rgba(255,255,255,.35);color:#fff;background:rgba(255,255,255,.06)}

/* ── Ticker ── */
.ticker{background:var(--teal);padding:12px 0;overflow:hidden}
.ticker-track{display:flex;align-items:center;gap:48px;animation:ticker-scroll 24s linear infinite;width:max-content}
.ticker-item{display:flex;align-items:center;gap:10px;font-family:'Syne',sans-serif;font-weight:700;font-size:13px;letter-spacing:1px;text-transform:uppercase;color:var(--navy);white-space:nowrap}
.ticker-item::after{content:'✦';font-size:10px;opacity:.5;margin-left:24px}
@keyframes ticker-scroll{from{transform:translateX(0)}to{transform:translateX(-50%)}}

/* ── COLLAPSIBLE TOC DRAWER ── */
.policy-layout{display:block;position:relative}

.toc-col{
    position:fixed;
    left:0;
    top:0;
    width:280px;
    height:100vh;
    background:#f0f4ff;
    z-index:1040;
    transform:translateX(-100%);
    transition:transform .3s ease-out;
    overflow-y:auto;
    padding-top:80px;
    padding-left:0;
    padding-right:0;
}

.toc-col.active{
    transform:translateX(0);
    box-shadow:2px 0 20px rgba(0,0,0,.15);
}

.policy-toc{
    position:relative;
    top:0;
    background:#fff;
    border-radius:18px;
    border:1px solid #e8edf5;
    padding:24px 20px;
    box-shadow:0 4px 20px rgba(0,0,0,.04);
    margin:0 16px;
}

.toc-toggle{
    position:fixed;
    left:24px;
    top:120px;
    width:48px;
    height:48px;
    background:linear-gradient(135deg,var(--teal),#00e0a8);
    border:none;
    border-radius:14px;
    color:#fff;
    font-size:22px;
    cursor:pointer;
    display:none;
    align-items:center;
    justify-content:center;
    z-index:1030;
    box-shadow:0 4px 18px rgba(0,201,141,.35);
    transition:all .3s;
    padding:0;
}

.toc-toggle:hover{
    transform:scale(1.08);
    box-shadow:0 8px 28px rgba(0,201,141,.45);
}

.toc-toggle.active{
    background:linear-gradient(135deg,#00a8e8,#00c9ff);
}

.toc-overlay{
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,.5);
    z-index:1035;
    display:none;
    opacity:0;
    transition:opacity .3s;
}

.toc-overlay.active{
    display:block;
    opacity:1;
}

.toc-title{font-family:'Syne',sans-serif;font-weight:700;font-size:13px;text-transform:uppercase;letter-spacing:1.5px;color:var(--teal);margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid #eef2f8}
.toc-list{list-style:none;display:flex;flex-direction:column;gap:4px}
.toc-list a{display:flex;align-items:center;gap:8px;font-size:13px;color:#4a5568;text-decoration:none;padding:7px 10px;border-radius:8px;transition:all .2s;line-height:1.35}
.toc-list a:hover,.toc-list a.active{background:var(--teal-low);color:var(--teal);font-weight:600}
.toc-list a .toc-num{font-family:'Syne',sans-serif;font-weight:800;font-size:11px;color:var(--teal);min-width:18px}

/* ── Quick Summary Cards ── */
.quick-cards{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:40px}
.qc{background:linear-gradient(135deg,#f8faff,#eef5ff);border:1px solid #e0eaff;border-radius:14px;padding:20px;text-align:center}
.qc-icon{width:44px;height:44px;border-radius:12px;background:var(--teal-low);border:1px solid rgba(0,201,141,.2);display:flex;align-items:center;justify-content:center;font-size:20px;color:var(--teal);margin:0 auto 12px}
.qc-value{font-family:'Syne',sans-serif;font-weight:800;font-size:22px;color:var(--navy);line-height:1;margin-bottom:4px}
.qc-label{font-size:12px;color:#6b7a99;font-weight:500}

/* ── Content ── */
.policy-content{background:#fff;border-radius:18px;border:1px solid #e8edf5;padding:48px 44px;box-shadow:0 4px 20px rgba(0,0,0,.04);max-width:100%;margin:0}
.policy-intro{background:linear-gradient(135deg,#f0faf6,#e8f5ff);border-radius:14px;border:1px solid rgba(0,201,141,.2);padding:24px 28px;margin-bottom:40px}
.policy-intro p{font-size:15px;line-height:1.75;color:#374151;margin:0}
.policy-intro strong{color:var(--navy)}

.ps{margin-bottom:48px;padding-bottom:48px;border-bottom:1px solid #eef2f8}
.ps:last-child{border-bottom:none;margin-bottom:0}
.ps-anchor{display:block;margin-top:-80px;padding-top:80px}
.ps-header{display:flex;align-items:flex-start;gap:14px;margin-bottom:20px}
.ps-num{width:38px;height:38px;border-radius:10px;flex-shrink:0;background:var(--teal-low);border:1px solid rgba(0,201,141,.2);display:flex;align-items:center;justify-content:center;font-family:'Syne',sans-serif;font-weight:800;font-size:14px;color:var(--teal)}
.ps-title{font-family:'Syne',sans-serif;font-weight:800;font-size:22px;color:var(--navy);line-height:1.2;margin:0;padding-top:6px}
.ps p{font-size:15.5px;line-height:1.8;color:#374151;margin-bottom:16px}
.ps p:last-child{margin-bottom:0}
.ps-infobox{background:#f8faff;border-left:4px solid var(--teal);border-radius:0 12px 12px 0;padding:18px 20px;margin:20px 0}
.ps-infobox p{font-size:14.5px;color:#374151;margin:0;line-height:1.7}
.ps-list{list-style:none;display:flex;flex-direction:column;gap:10px;margin:16px 0}
.ps-list li{display:flex;align-items:flex-start;gap:10px;font-size:15px;line-height:1.65;color:#374151}
.ps-list li i{color:var(--teal);font-size:15px;flex-shrink:0;margin-top:3px}
.ps-list li strong{color:var(--navy)}
.ps-list li.no-icon i{color:#ef4444}
.ps-table{width:100%;border-collapse:collapse;margin:20px 0;font-size:14px}
.ps-table thead tr{background:var(--navy)}
.ps-table thead th{padding:12px 16px;color:#fff;font-family:'Syne',sans-serif;font-weight:700;font-size:13px;text-align:left}
.ps-table tbody tr{border-bottom:1px solid #eef2f8;transition:background .15s}
.ps-table tbody tr:hover{background:#f8faff}
.ps-table tbody td{padding:12px 16px;color:#374151;vertical-align:top;line-height:1.55}
.ps-table tbody td:first-child{font-weight:600;color:var(--navy)}
.ps-highlight{background:var(--navy);border-radius:14px;padding:24px 28px;margin:24px 0}
.ps-highlight p{color:var(--txt);font-size:14.5px;line-height:1.75;margin:0}
.ps-highlight strong{color:var(--teal)}
.ps-warning{background:#fff5f5;border-left:4px solid #ef4444;border-radius:0 12px 12px 0;padding:18px 20px;margin:20px 0}
.ps-warning p{font-size:14.5px;color:#374151;margin:0;line-height:1.7}
.ps-warning strong{color:#dc2626}
.ps-success{background:#f0faf6;border-left:4px solid var(--teal);border-radius:0 12px 12px 0;padding:18px 20px;margin:20px 0}
.ps-success p{font-size:14.5px;color:#374151;margin:0;line-height:1.7}
.ps-success strong{color:#047857}

/* ── Refund Timeline Steps ── */
.refund-steps{display:flex;flex-direction:column;gap:0;margin:24px 0;position:relative}
.refund-steps::before{content:'';position:absolute;left:19px;top:24px;bottom:24px;width:2px;background:linear-gradient(to bottom,var(--teal),rgba(0,201,141,.1));z-index:0}
.rs{display:flex;align-items:flex-start;gap:16px;padding:16px 0;position:relative;z-index:1}
.rs-dot{width:40px;height:40px;border-radius:50%;background:var(--teal);border:3px solid #fff;box-shadow:0 0 0 2px var(--teal);display:flex;align-items:center;justify-content:center;font-family:'Syne',sans-serif;font-weight:800;font-size:13px;color:var(--navy);flex-shrink:0}
.rs-body{padding-top:6px}
.rs-step{font-family:'Syne',sans-serif;font-weight:700;font-size:14px;color:var(--navy);margin-bottom:4px}
.rs-desc{font-size:14px;color:#6b7a99;line-height:1.55}
.rs-time{display:inline-block;margin-top:6px;background:var(--teal-low);border:1px solid rgba(0,201,141,.25);border-radius:20px;padding:2px 10px;font-size:12px;font-weight:600;color:var(--teal)}

/* ── Contact Card ── */
.contact-card{background:linear-gradient(135deg,var(--navy),var(--deep));border:1px solid var(--border);border-radius:18px;padding:32px 28px;margin-top:48px;text-align:center}
.contact-card h3{font-family:'Syne',sans-serif;font-weight:800;font-size:22px;color:#fff;margin-bottom:10px}
.contact-card p{font-size:15px;color:var(--muted);max-width:480px;margin:0 auto 24px}
.contact-links{display:flex;gap:12px;justify-content:center;flex-wrap:wrap}
.contact-link{display:inline-flex;align-items:center;gap:8px;background:rgba(0,201,141,.12);border:1px solid rgba(0,201,141,.25);border-radius:10px;padding:10px 20px;font-size:14px;font-weight:600;color:var(--teal);text-decoration:none;transition:all .2s}
.contact-link:hover{background:rgba(0,201,141,.2);color:var(--teal)}

/* ── Responsive ── */
@media(max-width:1024px){
    .toc-toggle{display:flex}
    .policy-content{padding:32px 28px}
}

@media(max-width:768px){
    .policy-content{padding:24px 18px;border-radius:12px}
    .ps-title{font-size:18px}
    .quick-cards{grid-template-columns:1fr 1fr}
}

@media(max-width:640px){
    .gp-container{padding:0 16px}
    .policy-content{padding:18px 14px}
    .toc-col{width:240px;padding-top:70px}
    .toc-toggle{width:44px;height:44px;font-size:18px;left:16px;top:100px}
    .quick-cards{grid-template-columns:1fr}
    .ph-actions{flex-direction:column}
    .btn-outline{justify-content:center}
}
</style>

{{-- ── TOC OVERLAY ── --}}
<div class="toc-overlay" id="tocOverlay"></div>

{{-- ── TOC TOGGLE BUTTON ── --}}
<button class="toc-toggle" id="tocToggle" title="Show Contents">
    <i class="bi bi-list-ul"></i>
</button>

{{-- ── Hero ── --}}
<section class="policy-hero dot-grid">
    <div class="gp-container">
        <div class="policy-hero-inner">
            <div class="ph-breadcrumb">
                <a href="{{ route('home') }}">Home</a>
                <i class="bi bi-chevron-right"></i>
                <span>Refund Policy</span>
            </div>
            <div class="ph-badge"><i class="bi bi-arrow-counterclockwise"></i> Refund Policy</div>
            <h1 class="ph-title">Refund &amp; <em>Cancellation</em></h1>
            <div class="ph-meta">
                <span><i class="bi bi-calendar3"></i> Effective: 1 January 2026</span>
                <span><i class="bi bi-arrow-clockwise"></i> Last Updated: 1 January 2026</span>
                <span><i class="bi bi-geo-alt-fill"></i> Jurisdiction: India</span>
            </div>
            <div class="ph-actions">
                <a href="{{ route('terms') }}" class="btn-outline">
                    <i class="bi bi-file-earmark-text"></i> Terms &amp; Conditions
                </a>
            </div>
        </div>
    </div>
</section>

{{-- ── Ticker ── --}}
<div class="ticker">
    <div class="ticker-track">
        @php $ti=['7-Day Refund Window','No Questions Asked','Prorated Cancellations','Instant Processing','Transparent Policy','Fair Billing','GST Invoice Provided','Annual Plan Refunds','No Hidden Fees','Secure Payments']; @endphp
        @foreach(array_merge($ti,$ti) as $t)<div class="ticker-item">{{ $t }}</div>@endforeach
    </div>
</div>

{{-- ── Body ── --}}
<section style="background:#f0f4ff;padding:64px 0">
    <div class="gp-container">
        <div class="policy-layout">

            {{-- ── COLLAPSIBLE TOC DRAWER ── --}}
            <div class="toc-col" id="tocCol">
                <nav class="policy-toc" aria-label="Table of Contents">
                    <div class="toc-title"><i class="bi bi-list-ul"></i> Contents</div>
                    <ul class="toc-list">
                        <li><a href="#r1"><span class="toc-num">01</span> Overview</a></li>
                        <li><a href="#r2"><span class="toc-num">02</span> Eligible Refunds</a></li>
                        <li><a href="#r3"><span class="toc-num">03</span> Non-Refundable Cases</a></li>
                        <li><a href="#r4"><span class="toc-num">04</span> Annual Plan Refunds</a></li>
                        <li><a href="#r5"><span class="toc-num">05</span> How to Request</a></li>
                        <li><a href="#r6"><span class="toc-num">06</span> Refund Timeline</a></li>
                        <li><a href="#r7"><span class="toc-num">07</span> Cancellation Policy</a></li>
                        <li><a href="#r8"><span class="toc-num">08</span> Plan Downgrades</a></li>
                        <li><a href="#r9"><span class="toc-num">09</span> Failed Payments</a></li>
                        <li><a href="#r10"><span class="toc-num">10</span> Disputes & Chargebacks</a></li>
                        <li><a href="#r11"><span class="toc-num">11</span> Contact Us</a></li>
                    </ul>
                </nav>
            </div>

            {{-- ── FULL WIDTH CONTENT ── --}}
            <div class="policy-content">

                {{-- Quick stats --}}
                <div class="quick-cards">
                    <div class="qc">
                        <div class="qc-icon"><i class="bi bi-clock-history"></i></div>
                        <div class="qc-value">7 Days</div>
                        <div class="qc-label">Refund window (monthly plans)</div>
                    </div>
                    <div class="qc">
                        <div class="qc-icon"><i class="bi bi-lightning-charge-fill"></i></div>
                        <div class="qc-value">5–7 Days</div>
                        <div class="qc-label">Refund credited to source</div>
                    </div>
                    <div class="qc">
                        <div class="qc-icon"><i class="bi bi-shield-check-fill"></i></div>
                        <div class="qc-value">Pro-rata</div>
                        <div class="qc-label">Refund for annual plans</div>
                    </div>
                </div>

                <div class="policy-intro">
                    <p>At EcomgstPro, we stand behind our platform. This Refund &amp; Cancellation Policy explains when and how you can request a refund, how cancellations work, and what happens to your data and subscription access. This policy forms part of your agreement with <strong>ecomgstpro Technologies Private Limited</strong> and should be read alongside our <a href="{{ route('terms') }}" style="color:var(--teal)">Terms &amp; Conditions</a> and <a href="{{ route('privacy') }}" style="color:var(--teal)">Privacy Policy</a>.</p>
                </div>

                {{-- Section 1 --}}
                <div id="r1" class="ps">
                    <span class="ps-anchor"></span>
                    <div class="ps-header"><div class="ps-num">01</div><h2 class="ps-title">Overview</h2></div>
                    <p>EcomgstPro offers a free Starter plan and paid Professional and Enterprise plans billed monthly or annually. We want you to be fully satisfied with our service. If you are not satisfied, you may be eligible for a refund under the conditions described in this policy.</p>
                    <div class="ps-infobox">
                        <p><strong>Key principle:</strong> Monthly subscribers may request a full refund within 7 days of their initial payment or renewal. Annual subscribers may request a prorated refund at any time during the subscription year.</p>
                    </div>
                    <p>All refund requests are reviewed by our billing team and processed within 5–7 business days to the original payment method. EcomgstPro does not offer refunds in the form of credits or vouchers — all refunds are returned to your original payment source.</p>
                </div>

                {{-- Section 2 --}}
                <div id="r2" class="ps">
                    <span class="ps-anchor"></span>
                    <div class="ps-header"><div class="ps-num">02</div><h2 class="ps-title">Eligible Refunds</h2></div>
                    <p>You are entitled to a refund in the following situations:</p>
                    <ul class="ps-list">
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>7-Day New Subscriber Refund:</strong> If you are a first-time subscriber to a paid plan and are not satisfied for any reason, you may request a full refund within 7 days of your first payment.</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>7-Day Renewal Refund:</strong> If your subscription auto-renewed and you did not intend to renew (e.g., you forgot to cancel), you may request a refund within 7 days of the renewal charge.</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>Annual Plan Prorated Refund:</strong> Annual subscribers who wish to cancel after 7 days will receive a prorated refund for the unused full months remaining in their subscription year.</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>Technical Failure:</strong> If a proven technical failure in EcomgstPro prevented you from accessing a core service feature for more than 72 consecutive hours and we were unable to resolve it, you may request a refund for the affected period.</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>Duplicate Charge:</strong> If you were charged more than once for the same billing period due to a payment gateway error, the duplicate charge will be fully refunded.</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>Upgrade Credit:</strong> If you upgrade from a monthly to an annual plan mid-cycle, the unused portion of your monthly plan will be credited as a deduction on your annual plan invoice.</span></li>
                    </ul>
                </div>

                {{-- Section 3 --}}
                <div id="r3" class="ps">
                    <span class="ps-anchor"></span>
                    <div class="ps-header"><div class="ps-num">03</div><h2 class="ps-title">Non-Refundable Cases</h2></div>
                    <p>The following situations are not eligible for a refund:</p>
                    <ul class="ps-list">
                        <li class="no-icon"><i class="bi bi-x-circle-fill"></i><span>Requests made more than 7 days after a monthly subscription payment.</span></li>
                        <li class="no-icon"><i class="bi bi-x-circle-fill"></i><span>Accounts suspended or terminated due to violation of our <a href="{{ route('terms') }}" style="color:var(--teal)">Terms &amp; Conditions</a>, including fraudulent filing activity.</span></li>
                        <li class="no-icon"><i class="bi bi-x-circle-fill"></i><span>Dissatisfaction due to features described accurately on our pricing page that were available during your subscription period.</span></li>
                        <li class="no-icon"><i class="bi bi-x-circle-fill"></i><span>Downtime or unavailability of the GSTN portal (gst.gov.in), which is operated by the Government of India and outside our control.</span></li>
                        <li class="no-icon"><i class="bi bi-x-circle-fill"></i><span>GST notices, penalties, or interest arising from returns you filed using the platform, where those returns were based on data you provided.</span></li>
                        <li class="no-icon"><i class="bi bi-x-circle-fill"></i><span>Subscription fees for add-ons, one-time services, or Enterprise onboarding fees after the service has been delivered.</span></li>
                        <li class="no-icon"><i class="bi bi-x-circle-fill"></i><span>Refund requests submitted after account deletion, as we cannot verify usage history post-deletion.</span></li>
                    </ul>
                    <div class="ps-warning">
                        <p><strong>Note:</strong> GSTN portal downtime during filing deadlines is a known and recurring occurrence. We strongly recommend filing returns at least 3 days before the due date to avoid deadline-related issues. EcomgstPro is not responsible for penalties arising from last-minute filing attempts during portal outages.</p>
                    </div>
                </div>

                {{-- Section 4 --}}
                <div id="r4" class="ps">
                    <span class="ps-anchor"></span>
                    <div class="ps-header"><div class="ps-num">04</div><h2 class="ps-title">Annual Plan Refunds</h2></div>
                    <p>Annual plans offer the best value, and we want to ensure you feel comfortable committing to one. If you subscribed to an annual plan and wish to cancel after the initial 7-day window, here is how your prorated refund is calculated:</p>
                    <table class="ps-table">
                        <thead><tr><th>Month of Cancellation</th><th>Refund Calculation</th><th>Example (₹9,999/yr)</th></tr></thead>
                        <tbody>
                            <tr><td>Within 7 days</td><td>Full refund</td><td>₹9,999</td></tr>
                            <tr><td>Month 2</td><td>10 unused months × monthly rate</td><td>~₹8,332</td></tr>
                            <tr><td>Month 3</td><td>9 unused months × monthly rate</td><td>~₹7,499</td></tr>
                            <tr><td>Month 6</td><td>6 unused months × monthly rate</td><td>~₹4,999</td></tr>
                            <tr><td>Month 11</td><td>1 unused month × monthly rate</td><td>~₹833</td></tr>
                            <tr><td>Month 12 (last month)</td><td>No refund</td><td>₹0</td></tr>
                        </tbody>
                    </table>
                    <div class="ps-infobox">
                        <p><strong>Monthly rate for proration:</strong> Annual plan price ÷ 12 months. The refund covers full unused months only — partial months are not refunded. GST component (18%) is also refunded proportionally.</p>
                    </div>
                </div>

                {{-- Section 5 --}}
                <div id="r5" class="ps">
                    <span class="ps-anchor"></span>
                    <div class="ps-header"><div class="ps-num">05</div><h2 class="ps-title">How to Request a Refund</h2></div>
                    <p>Requesting a refund is straightforward. Follow these steps:</p>
                    <ul class="ps-list">
                        <li><i class="bi bi-1-circle-fill"></i><span><strong>Email us</strong> at <a href="mailto:billing@ecomgstpro.com" style="color:var(--teal)">billing@ecomgstpro.com</a> with the subject line: <em>"Refund Request – [Your Registered Email]"</em></span></li>
                        <li><i class="bi bi-2-circle-fill"></i><span><strong>Include</strong> your registered email address, the transaction/invoice number, and a brief reason for the refund request.</span></li>
                        <li><i class="bi bi-3-circle-fill"></i><span><strong>Our billing team</strong> will acknowledge your request within 24 business hours and confirm eligibility.</span></li>
                        <li><i class="bi bi-4-circle-fill"></i><span><strong>Once approved</strong>, the refund will be initiated to your original payment method within 2 business days of approval.</span></li>
                        <li><i class="bi bi-5-circle-fill"></i><span><strong>Credit timeline</strong>: The refunded amount appears in your bank/card account within 5–7 business days depending on your payment provider.</span></li>
                    </ul>
                    <div class="ps-success">
                        <p><strong>Tip:</strong> You can also initiate a cancellation request directly from your EcomgstPro account under <em>Settings → Billing → Cancel Subscription</em>. If you are within the 7-day refund window, you will be prompted to request a refund automatically.</p>
                    </div>
                </div>

                {{-- Section 6 --}}
                <div id="r6" class="ps">
                    <span class="ps-anchor"></span>
                    <div class="ps-header"><div class="ps-num">06</div><h2 class="ps-title">Refund Processing Timeline</h2></div>
                    <p>Here is what happens after you submit a refund request:</p>
                    <div class="refund-steps">
                        <div class="rs">
                            <div class="rs-dot">1</div>
                            <div class="rs-body">
                                <div class="rs-step">Request Received</div>
                                <div class="rs-desc">You email billing@ecomgstpro.com with your request details.</div>
                                <span class="rs-time">Day 0</span>
                            </div>
                        </div>
                        <div class="rs">
                            <div class="rs-dot">2</div>
                            <div class="rs-body">
                                <div class="rs-step">Acknowledgement</div>
                                <div class="rs-desc">Our billing team acknowledges your request and checks eligibility.</div>
                                <span class="rs-time">Within 24 business hours</span>
                            </div>
                        </div>
                        <div class="rs">
                            <div class="rs-dot">3</div>
                            <div class="rs-body">
                                <div class="rs-step">Review & Approval</div>
                                <div class="rs-desc">Eligibility is confirmed and refund amount is calculated. You receive a confirmation email.</div>
                                <span class="rs-time">Day 1–2</span>
                            </div>
                        </div>
                        <div class="rs">
                            <div class="rs-dot">4</div>
                            <div class="rs-body">
                                <div class="rs-step">Refund Initiated</div>
                                <div class="rs-desc">Refund is initiated to your original payment source (card, UPI, net banking, or wallet).</div>
                                <span class="rs-time">Day 2–3</span>
                            </div>
                        </div>
                        <div class="rs">
                            <div class="rs-dot">5</div>
                            <div class="rs-body">
                                <div class="rs-step">Amount Credited</div>
                                <div class="rs-desc">Funds appear in your bank or card account. Timeline depends on your payment provider.</div>
                                <span class="rs-time">Day 5–10</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Section 7 --}}
                <div id="r7" class="ps">
                    <span class="ps-anchor"></span>
                    <div class="ps-header"><div class="ps-num">07</div><h2 class="ps-title">Cancellation Policy</h2></div>
                    <p>You may cancel your EcomgstPro subscription at any time. Here is what you need to know:</p>
                    <ul class="ps-list">
                        <li><i class="bi bi-toggle-off"></i><span><strong>How to Cancel:</strong> Go to <em>Settings → Billing → Cancel Subscription</em> in your account, or email support@ecomgstpro.com. Cancellation takes effect immediately; no phone call is required.</span></li>
                        <li><i class="bi bi-toggle-off"></i><span><strong>Access After Cancellation:</strong> Your paid plan features remain active until the end of your current billing period. You will not be charged again after cancellation.</span></li>
                        <li><i class="bi bi-toggle-off"></i><span><strong>Downgrade, Not Delete:</strong> After cancellation, your account reverts to the free Starter plan. Your data, previously filed returns, and processed files are retained and accessible.</span></li>
                        <li><i class="bi bi-toggle-off"></i><span><strong>Data Retention:</strong> EcomgstPro retains your account data for a minimum of 6 years from the date of the last filed return, in compliance with GST Act record-keeping requirements, unless you explicitly request deletion.</span></li>
                        <li><i class="bi bi-toggle-off"></i><span><strong>Reactivation:</strong> You can reactivate a paid plan at any time by subscribing again. All your historical data will be immediately accessible.</span></li>
                    </ul>
                </div>

                {{-- Section 8 --}}
                <div id="r8" class="ps">
                    <span class="ps-anchor"></span>
                    <div class="ps-header"><div class="ps-num">08</div><h2 class="ps-title">Plan Downgrades</h2></div>
                    <p>If you wish to move from a higher-tier plan to a lower-tier plan (e.g., Professional to Starter), the following applies:</p>
                    <ul class="ps-list">
                        <li><i class="bi bi-arrow-down-circle-fill"></i><span><strong>Monthly Plans:</strong> Downgrade takes effect from the next billing cycle. You retain current plan features until the end of the billing period.</span></li>
                        <li><i class="bi bi-arrow-down-circle-fill"></i><span><strong>Annual Plans:</strong> You may request a downgrade and receive a prorated refund for the difference between plans for unused months. Email billing@ecomgstpro.com to process a plan downgrade refund.</span></li>
                        <li><i class="bi bi-arrow-down-circle-fill"></i><span><strong>Feature Access:</strong> Upon downgrade, features exclusive to your previous higher-tier plan (e.g., multi-GSTIN management, 2A/2B reconciliation) will no longer be accessible. Your data is preserved and accessible if you upgrade again.</span></li>
                    </ul>
                </div>

                {{-- Section 9 --}}
                <div id="r9" class="ps">
                    <span class="ps-anchor"></span>
                    <div class="ps-header"><div class="ps-num">09</div><h2 class="ps-title">Failed Payments</h2></div>
                    <p>If your subscription renewal payment fails, here is how EcomgstPro handles it:</p>
                    <table class="ps-table">
                        <thead><tr><th>Day</th><th>Action Taken</th><th>Your Account Status</th></tr></thead>
                        <tbody>
                            <tr><td>Day 0</td><td>Initial payment attempt fails</td><td>Paid plan active; email notification sent</td></tr>
                            <tr><td>Day 1</td><td>Automatic retry attempt 1</td><td>Paid plan active</td></tr>
                            <tr><td>Day 2</td><td>Automatic retry attempt 2</td><td>Paid plan active</td></tr>
                            <tr><td>Day 3</td><td>Automatic retry attempt 3</td><td>Paid plan active</td></tr>
                            <tr><td>Day 4</td><td>All retries exhausted; account downgraded</td><td>Reverts to Starter plan; data retained</td></tr>
                            <tr><td>Day 4–90</td><td>No action; data retained</td><td>Starter plan; can reactivate anytime</td></tr>
                            <tr><td>Day 90+</td><td>Inactive account review</td><td>Data retained per Privacy Policy retention schedule</td></tr>
                        </tbody>
                    </table>
                    <div class="ps-infobox">
                        <p>To update your payment method, go to <em>Settings → Billing → Payment Methods</em>. If you believe a payment failure was caused by a bank error and the amount was debited from your account but not received by us, contact billing@ecomgstpro.com immediately with your bank transaction reference.</p>
                    </div>
                </div>

                {{-- Section 10 --}}
                <div id="r10" class="ps">
                    <span class="ps-anchor"></span>
                    <div class="ps-header"><div class="ps-num">10</div><h2 class="ps-title">Disputes & Chargebacks</h2></div>
                    <p>We encourage you to contact us before raising a dispute with your bank or payment provider. Chargebacks initiated without contacting us first are taken seriously and may result in account suspension.</p>
                    <div class="ps-highlight">
                        <p>If you initiate a chargeback or payment dispute, EcomgstPro will provide your bank with all transaction records, usage logs, and communications to contest the chargeback. <strong>Contact billing@ecomgstpro.com first</strong> — we resolve legitimate refund cases promptly and fairly, usually faster than a bank dispute process.</p>
                    </div>
                    <p>In cases of confirmed fraud, identity theft, or unauthorised card use on your account, we will fully cooperate with your bank and expedite the refund process. Please report such cases to billing@ecomgstpro.com and security@ecomgstpro.com simultaneously.</p>
                </div>

                {{-- Section 11 --}}
                <div id="r11" class="ps">
                    <span class="ps-anchor"></span>
                    <div class="ps-header"><div class="ps-num">11</div><h2 class="ps-title">Contact Us</h2></div>
                    <p>For all refund and billing related queries, please reach out to our dedicated billing team:</p>
                    <div class="ps-infobox">
                        <p><strong>ecomgstpro Technologies Private Limited</strong><br>
                        <strong>Billing & Refunds:</strong> billing@ecomgstpro.com<br>
                        <strong>General Support:</strong> support@ecomgstpro.com<br>
                        <strong>Security / Fraud:</strong> security@ecomgstpro.com<br>
                        <strong>Response time:</strong> Billing queries within 24 business hours; Refund decisions within 48 hours</p>
                    </div>
                </div>

                <div class="contact-card">
                    <h3>Need help with a refund?</h3>
                    <p>Our billing team responds within 24 business hours. Most refunds are processed within 5–7 business days.</p>
                    <div class="contact-links">
                        <a href="mailto:billing@ecomgstpro.com" class="contact-link"><i class="bi bi-envelope-fill"></i> Email</a>
                        <a href="{{ route('terms') }}" class="contact-link"><i class="bi bi-file-earmark-text"></i> Terms &amp; Conditions</a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// TOC Drawer Toggle
const tocToggle = document.getElementById('tocToggle');
const tocCol = document.getElementById('tocCol');
const tocOverlay = document.getElementById('tocOverlay');

function toggleTOC() {
    tocCol.classList.toggle('active');
    tocToggle.classList.toggle('active');
    tocOverlay.classList.toggle('active');
}

function closeTOC() {
    tocCol.classList.remove('active');
    tocToggle.classList.remove('active');
    tocOverlay.classList.remove('active');
}

tocToggle.addEventListener('click', toggleTOC);
tocOverlay.addEventListener('click', closeTOC);

// Close TOC when clicking on a link
document.querySelectorAll('.toc-list a').forEach(link => {
    link.addEventListener('click', closeTOC);
});

// Highlight active TOC item on scroll
(function(){
    const sections = document.querySelectorAll('.ps');
    const tocLinks = document.querySelectorAll('.toc-list a');

    const obs = new IntersectionObserver(entries => {
        entries.forEach(e => {
            if(e.isIntersecting){
                const id = e.target.id;
                if(id) tocLinks.forEach(l => {
                    l.classList.toggle('active', l.getAttribute('href') === '#' + id);
                });
            }
        });
    }, { threshold: 0.3 });

    sections.forEach(s => obs.observe(s));
})();
</script>
@endsection
