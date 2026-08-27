@extends('layouts.app')

{{-- ============================================================
     GST Pilot — terms.blade.php (FULL WIDTH VERSION)
     Theme: Dark Navy #0a1628 + Teal #00c98d + Sky #00aaff
     Layout: Matches refund.blade.php — full-width content with
     collapsible TOC drawer (no sticky sidebar)
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
.btn-dl{display:inline-flex;align-items:center;gap:8px;background:linear-gradient(135deg,var(--teal),#00e0a8);color:var(--navy);font-weight:700;font-size:14px;padding:12px 24px;border-radius:10px;text-decoration:none;box-shadow:0 4px 18px rgba(0,201,141,.35);transition:all .2s}
.btn-dl:hover{transform:translateY(-2px);box-shadow:0 8px 28px rgba(0,201,141,.45);color:var(--navy)}
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
}

@media(max-width:640px){
    .gp-container{padding:0 16px}
    .policy-content{padding:18px 14px}
    .toc-col{width:240px;padding-top:70px}
    .toc-toggle{width:44px;height:44px;font-size:18px;left:16px;top:100px}
    .ph-actions{flex-direction:column}
    .btn-dl,.btn-outline{justify-content:center}
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
                <span>Terms &amp; Conditions</span>
            </div>
            <div class="ph-badge"><i class="bi bi-file-earmark-text-fill"></i> Legal Document</div>
            <h1 class="ph-title">Terms &amp; <em>Conditions</em></h1>
            <div class="ph-meta">
                <span><i class="bi bi-calendar3"></i> Effective: 1 January 2026</span>
                <span><i class="bi bi-arrow-clockwise"></i> Last Updated: 1 January 2026</span>
                <span><i class="bi bi-geo-alt-fill"></i> Jurisdiction: India</span>
            </div>
            <div class="ph-actions">
                <a href="{{ asset('downloads/EcomgstPro-terms-conditions.pdf') }}" download class="btn-dl">
                    <i class="bi bi-file-earmark-pdf-fill"></i> Download PDF
                </a>
                <a href="{{ route('privacy') }}" class="btn-outline">
                    <i class="bi bi-shield-lock"></i> Privacy Policy
                </a>
            </div>
        </div>
    </div>
</section>

{{-- ── Ticker ── --}}
<div class="ticker">
    <div class="ticker-track">
        @php $ti=['Terms & Conditions','User Agreement','GSP Certified Platform','Indian Law Governed','Fair Use Policy','Subscription Terms','IP Rights Protected','GSTN Compliant','Honest Pricing','No Hidden Fees']; @endphp
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
                        <li><a href="#t1"><span class="toc-num">01</span> Agreement to Terms</a></li>
                        <li><a href="#t2"><span class="toc-num">02</span> Eligibility</a></li>
                        <li><a href="#t3"><span class="toc-num">03</span> Account Registration</a></li>
                        <li><a href="#t4"><span class="toc-num">04</span> Services Description</a></li>
                        <li><a href="#t5"><span class="toc-num">05</span> Subscriptions & Billing</a></li>
                        <li><a href="#t6"><span class="toc-num">06</span> Acceptable Use</a></li>
                        <li><a href="#t7"><span class="toc-num">07</span> Intellectual Property</a></li>
                        <li><a href="#t8"><span class="toc-num">08</span> Your Data & Content</a></li>
                        <li><a href="#t9"><span class="toc-num">09</span> GSP & Portal Liability</a></li>
                        <li><a href="#t10"><span class="toc-num">10</span> Warranties & Disclaimer</a></li>
                        <li><a href="#t11"><span class="toc-num">11</span> Limitation of Liability</a></li>
                        <li><a href="#t12"><span class="toc-num">12</span> Indemnification</a></li>
                        <li><a href="#t13"><span class="toc-num">13</span> Termination</a></li>
                        <li><a href="#t14"><span class="toc-num">14</span> Governing Law</a></li>
                        <li><a href="#t15"><span class="toc-num">15</span> Modifications</a></li>
                        <li><a href="#t16"><span class="toc-num">16</span> Contact</a></li>
                    </ul>
                </nav>
            </div>

            {{-- ── FULL WIDTH CONTENT ── --}}
            <div class="policy-content">

                <div class="policy-intro">
                    <p>These Terms and Conditions ("Terms") govern your access to and use of EcomgstPro's platform, website, and services operated by <strong>EcomgstPro Technologies Private Limited</strong> ("EcomgstPro", "we", "us"). By creating an account, accessing our platform, or using any EcomgstPro service, you confirm that you have read, understood, and agree to be legally bound by these Terms. If you do not agree, do not use our services.</p>
                </div>

                <div id="t1" class="ps">
                    <span class="ps-anchor"></span>
                    <div class="ps-header"><div class="ps-num">01</div><h2 class="ps-title">Agreement to Terms</h2></div>
                    <p>By accessing or using EcomgstPro's services, you enter into a legally binding agreement with EcomgstPro Technologies Private Limited. These Terms apply to all users of the platform, including registered account holders, CA firms, individual taxpayers, e-commerce sellers, and any individual browsing the website.</p>
                    <p>These Terms should be read alongside our <a href="{{ route('privacy') }}" style="color:var(--teal)">Privacy Policy</a> and <a href="{{ route('refund') }}" style="color:var(--teal)">Refund Policy</a>, which are incorporated herein by reference and form part of the complete agreement between you and EcomgstPro.</p>
                    <div class="ps-infobox">
                        <p><strong>Last effective date:</strong> 1 January 2026. We will notify you at least 15 days before any material change to these Terms via your registered email.</p>
                    </div>
                </div>

                <div id="t2" class="ps">
                    <span class="ps-anchor"></span>
                    <div class="ps-header"><div class="ps-num">02</div><h2 class="ps-title">Eligibility</h2></div>
                    <p>To use EcomgstPro, you must meet all of the following requirements:</p>
                    <ul class="ps-list">
                        <li><i class="bi bi-check-circle-fill"></i><span>You are at least 18 years of age.</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span>You are a person, business entity, or professional (CA/tax consultant) with a valid GSTIN or with authority to act on behalf of a GST-registered entity.</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span>You have the legal capacity to enter into binding contracts under Indian law.</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span>You are not prohibited from using our services under any applicable Indian or international law.</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span>Your account has not been previously suspended or terminated by EcomgstPro.</span></li>
                    </ul>
                    <p>EcomgstPro is designed for use within India and is optimised for the Indian GST framework. Use from outside India is permitted but support for non-Indian tax jurisdictions is not provided.</p>
                </div>

                <div id="t3" class="ps">
                    <span class="ps-anchor"></span>
                    <div class="ps-header"><div class="ps-num">03</div><h2 class="ps-title">Account Registration & Security</h2></div>
                    <p>To access EcomgstPro's services, you must register for an account by providing accurate, current, and complete information. You are responsible for:</p>
                    <ul class="ps-list">
                        <li><i class="bi bi-person-fill-lock"></i><span><strong>Account Accuracy:</strong> Providing and maintaining accurate registration information, including your name, email, business name, and GSTIN.</span></li>
                        <li><i class="bi bi-person-fill-lock"></i><span><strong>Password Security:</strong> Keeping your login credentials confidential. You are responsible for all activity that occurs under your account.</span></li>
                        <li><i class="bi bi-person-fill-lock"></i><span><strong>Unauthorised Access:</strong> Notifying us immediately at security@EcomgstPro.in if you suspect any unauthorised use of your account.</span></li>
                        <li><i class="bi bi-person-fill-lock"></i><span><strong>One Account Per Entity:</strong> Each GSTIN entity should have one account unless you have subscribed to our multi-GSTIN or Enterprise plan.</span></li>
                    </ul>
                    <p>EcomgstPro will never ask for your GST portal password. Authentication with the GST portal is handled via official GSTN OTP-based APIs. Do not share your portal credentials with EcomgstPro or anyone claiming to represent EcomgstPro.</p>
                </div>

                <div id="t4" class="ps">
                    <span class="ps-anchor"></span>
                    <div class="ps-header"><div class="ps-num">04</div><h2 class="ps-title">Services Description</h2></div>
                    <p>EcomgstPro provides the following GST compliance automation services, subject to your subscription plan:</p>
                    <table class="ps-table">
                        <thead><tr><th>Service</th><th>Description</th><th>Available On</th></tr></thead>
                        <tbody>
                            <tr><td>E-Commerce GSTR-1</td><td>Automated GSTR-1 generation from Amazon, Flipkart, Meesho, and other marketplace reports</td><td>All Plans</td></tr>
                            <tr><td>Bank Statement Converter</td><td>PDF-to-Tally XML conversion for 690+ Indian banks</td><td>All Plans</td></tr>
                            <tr><td>2A/2B Reconciliation</td><td>Match purchase registers against GSTN portal data for ITC optimisation</td><td>Professional &amp; Enterprise</td></tr>
                            <tr><td>GSTR-3B Filing</td><td>Automated GSTR-3B preparation and direct portal filing</td><td>Professional &amp; Enterprise</td></tr>
                            <tr><td>Multi-GSTIN Dashboard</td><td>Manage multiple GSTINs from a single account</td><td>Professional (up to 5) &amp; Enterprise</td></tr>
                            <tr><td>API Access</td><td>Programmatic access to EcomgstPro features</td><td>Enterprise Only</td></tr>
                        </tbody>
                    </table>
                    <p>EcomgstPro reserves the right to modify, add, or discontinue any service feature with appropriate notice. We will not discontinue a core feature without providing at least 30 days' notice and a reasonable transition period.</p>
                </div>

                <div id="t5" class="ps">
                    <span class="ps-anchor"></span>
                    <div class="ps-header"><div class="ps-num">05</div><h2 class="ps-title">Subscriptions & Billing</h2></div>
                    <p>EcomgstPro offers a free Starter plan and paid Professional and Enterprise plans. The following terms govern paid subscriptions:</p>
                    <ul class="ps-list">
                        <li><i class="bi bi-credit-card-fill"></i><span><strong>Billing Cycle:</strong> Paid plans are billed monthly or annually, as selected at the time of subscription. Annual plans are billed in advance for the full year.</span></li>
                        <li><i class="bi bi-credit-card-fill"></i><span><strong>Auto-Renewal:</strong> Subscriptions auto-renew at the end of each billing period. You will receive a reminder email 7 days before renewal. You may cancel auto-renewal at any time from your account settings.</span></li>
                        <li><i class="bi bi-credit-card-fill"></i><span><strong>Price Changes:</strong> We will notify you at least 30 days before any price change takes effect. Continued use after the effective date constitutes acceptance of the new price.</span></li>
                        <li><i class="bi bi-credit-card-fill"></i><span><strong>GST on Subscriptions:</strong> All subscription prices are exclusive of applicable GST (18%). GST will be added at checkout. A proper GST invoice will be issued for every payment.</span></li>
                        <li><i class="bi bi-credit-card-fill"></i><span><strong>Failed Payments:</strong> If your payment fails, we will attempt to retry over 3 days. If unsuccessful, your account will be downgraded to the Starter plan and your data retained for 90 days.</span></li>
                    </ul>
                    <p>For refund eligibility and process, please refer to our <a href="{{ route('refund') }}" style="color:var(--teal)">Refund Policy</a>.</p>
                </div>

                <div id="t6" class="ps">
                    <span class="ps-anchor"></span>
                    <div class="ps-header"><div class="ps-num">06</div><h2 class="ps-title">Acceptable Use Policy</h2></div>
                    <p>You agree to use EcomgstPro only for lawful purposes and in accordance with these Terms. The following uses are strictly prohibited:</p>
                    <ul class="ps-list">
                        <li class="no-icon"><i class="bi bi-x-circle-fill"></i><span>Filing false or fabricated GST returns using our platform. EcomgstPro must not be used to commit tax fraud or evasion.</span></li>
                        <li class="no-icon"><i class="bi bi-x-circle-fill"></i><span>Uploading data belonging to a business you do not have authorisation to represent.</span></li>
                        <li class="no-icon"><i class="bi bi-x-circle-fill"></i><span>Attempting to reverse-engineer, decompile, disassemble, or derive the source code of EcomgstPro.</span></li>
                        <li class="no-icon"><i class="bi bi-x-circle-fill"></i><span>Using automated scripts, bots, or scraping tools to access EcomgstPro without our written permission.</span></li>
                        <li class="no-icon"><i class="bi bi-x-circle-fill"></i><span>Sharing account credentials with individuals outside your organisation.</span></li>
                        <li class="no-icon"><i class="bi bi-x-circle-fill"></i><span>Reselling access to EcomgstPro as your own service without an Enterprise white-label agreement.</span></li>
                        <li class="no-icon"><i class="bi bi-x-circle-fill"></i><span>Uploading malware, viruses, or malicious code through our upload interfaces.</span></li>
                        <li class="no-icon"><i class="bi bi-x-circle-fill"></i><span>Using EcomgstPro to process data of entities on GSTN blacklists or under enforcement action where you are not the authorised representative.</span></li>
                    </ul>
                    <div class="ps-warning">
                        <p><strong>Important:</strong> Using EcomgstPro to file fraudulent GST returns is a serious criminal offence under the GST Act, 2017 and may result in prosecution. EcomgstPro will cooperate fully with law enforcement authorities investigating such cases and will suspend accounts immediately upon reasonable suspicion of fraudulent activity.</p>
                    </div>
                </div>

                <div id="t7" class="ps">
                    <span class="ps-anchor"></span>
                    <div class="ps-header"><div class="ps-num">07</div><h2 class="ps-title">Intellectual Property Rights</h2></div>
                    <p>EcomgstPro and all associated technology, software, design, algorithms, trademarks, logos, brand names, and content are the exclusive intellectual property of EcomgstPro Technologies Private Limited, protected under the Copyright Act, 1957, the Trade Marks Act, 1999, and other applicable Indian and international IP laws.</p>
                    <ul class="ps-list">
                        <li><i class="bi bi-shield-fill"></i><span><strong>Platform Licence:</strong> We grant you a limited, non-exclusive, non-transferable, revocable licence to use EcomgstPro solely for your own GST compliance purposes in accordance with these Terms.</span></li>
                        <li><i class="bi bi-shield-fill"></i><span><strong>No Ownership Transfer:</strong> Using EcomgstPro does not give you any ownership rights in the platform, software, or any of our intellectual property.</span></li>
                        <li><i class="bi bi-shield-fill"></i><span><strong>Feedback:</strong> Any feedback, suggestions, or ideas you provide regarding EcomgstPro become the property of EcomgstPro Technologies Private Limited. We may use them without obligation to compensate you.</span></li>
                    </ul>
                    <p>EcomgstPro's name, logo, and tagline are registered trademarks. Any unauthorised use, reproduction, or imitation is prohibited and may result in legal action.</p>
                </div>

                <div id="t8" class="ps">
                    <span class="ps-anchor"></span>
                    <div class="ps-header"><div class="ps-num">08</div><h2 class="ps-title">Your Data & Content</h2></div>
                    <p>You retain full ownership of all data, documents, and content you upload to EcomgstPro. By uploading data, you grant EcomgstPro a limited, temporary, non-exclusive licence to process that data solely for the purpose of delivering the service you have requested.</p>
                    <p>This licence terminates when the processing is complete or your account is closed. EcomgstPro will not use your financial data, tax data, or GST return information for any purpose other than providing the service you paid for. See our Privacy Policy for full details on data handling.</p>
                    <p>You are responsible for ensuring that the data you upload to EcomgstPro is accurate, lawfully obtained, and that you have the right to process it. EcomgstPro is not liable for errors in returns caused by inaccurate source data uploaded by you.</p>
                </div>

                <div id="t9" class="ps">
                    <span class="ps-anchor"></span>
                    <div class="ps-header"><div class="ps-num">09</div><h2 class="ps-title">GSP & GST Portal Liability</h2></div>
                    <p>EcomgstPro is a GSP (GST Suvidha Provider) certified platform. When you use EcomgstPro to interact with the GST portal, the following applies:</p>
                    <ul class="ps-list">
                        <li><i class="bi bi-info-circle-fill"></i><span><strong>Portal Availability:</strong> The GSTN portal is operated by the Government of India. EcomgstPro is not responsible for portal downtime, maintenance windows, or service interruptions caused by GSTN.</span></li>
                        <li><i class="bi bi-info-circle-fill"></i><span><strong>Filing Confirmation:</strong> EcomgstPro provides a filing acknowledgement based on the response received from the GST portal. You are responsible for verifying the acknowledgement number on the official portal (gst.gov.in).</span></li>
                        <li><i class="bi bi-info-circle-fill"></i><span><strong>Late Filing:</strong> EcomgstPro is not responsible for late filing penalties caused by portal unavailability, provided you attempted filing on time using our platform and we have a logged record of the attempt.</span></li>
                        <li><i class="bi bi-info-circle-fill"></i><span><strong>Regulatory Changes:</strong> GST law and portal requirements change frequently. EcomgstPro endeavours to keep the platform updated promptly, but is not liable for any period between a regulatory change and our platform update.</span></li>
                    </ul>
                </div>

                <div id="t10" class="ps">
                    <span class="ps-anchor"></span>
                    <div class="ps-header"><div class="ps-num">10</div><h2 class="ps-title">Warranties & Disclaimer</h2></div>
                    <p>EcomgstPro provides its services on an "as is" and "as available" basis, with all due efforts to ensure accuracy and uptime. However:</p>
                    <div class="ps-highlight">
                        <p><strong>EcomgstPro does not warrant</strong> that the platform will be uninterrupted, error-free, or completely secure at all times. We do not warrant that the GSTR outputs generated by EcomgstPro will always reflect the latest regulatory changes without any transition period. EcomgstPro is a tool to assist your compliance — you remain responsible as the taxpayer or authorised representative for reviewing and confirming all returns before filing.</p>
                    </div>
                    <p>EcomgstPro's AI reconciliation and processing features are designed to assist, not replace, professional CA judgement. Where a return involves complex GST issues, professional advice from a qualified CA should be sought before filing.</p>
                </div>

                <div id="t11" class="ps">
                    <span class="ps-anchor"></span>
                    <div class="ps-header"><div class="ps-num">11</div><h2 class="ps-title">Limitation of Liability</h2></div>
                    <p>To the fullest extent permitted under applicable Indian law:</p>
                    <ul class="ps-list">
                        <li><i class="bi bi-exclamation-triangle-fill" style="color:#f59e0b"></i><span>EcomgstPro's total liability for any claim arising out of your use of our services shall not exceed the subscription fees you paid to EcomgstPro in the <strong>12 months preceding</strong> the event giving rise to the claim.</span></li>
                        <li><i class="bi bi-exclamation-triangle-fill" style="color:#f59e0b"></i><span>EcomgstPro shall not be liable for indirect, incidental, special, consequential, or punitive damages — including loss of profits, revenue, data, or GST notices — even if we have been advised of the possibility of such damages.</span></li>
                        <li><i class="bi bi-exclamation-triangle-fill" style="color:#f59e0b"></i><span>EcomgstPro shall not be liable for any GST notices, penalties, or interest charges arising from returns filed by you using our platform, except where directly caused by a proven technical error in our platform that we failed to correct after being notified.</span></li>
                    </ul>
                </div>

                <div id="t12" class="ps">
                    <span class="ps-anchor"></span>
                    <div class="ps-header"><div class="ps-num">12</div><h2 class="ps-title">Indemnification</h2></div>
                    <p>You agree to indemnify, defend, and hold harmless EcomgstPro Technologies Private Limited, its founder, directors, employees, and agents from any claims, liabilities, damages, costs, and expenses (including reasonable legal fees) arising out of or related to:</p>
                    <ul class="ps-list">
                        <li><i class="bi bi-arrow-right-circle-fill"></i><span>Your violation of these Terms or any applicable law.</span></li>
                        <li><i class="bi bi-arrow-right-circle-fill"></i><span>Inaccurate, fraudulent, or unauthorised data you uploaded to the platform.</span></li>
                        <li><i class="bi bi-arrow-right-circle-fill"></i><span>Any third-party claims related to your use of EcomgstPro's services.</span></li>
                        <li><i class="bi bi-arrow-right-circle-fill"></i><span>Your infringement of any intellectual property or other rights of any third party.</span></li>
                    </ul>
                </div>

                <div id="t13" class="ps">
                    <span class="ps-anchor"></span>
                    <div class="ps-header"><div class="ps-num">13</div><h2 class="ps-title">Termination</h2></div>
                    <p><strong>By You:</strong> You may close your account at any time from your account settings or by emailing support@EcomgstPro.in. Closing your account cancels your subscription from the next billing cycle. Previously filed returns and processed data will be retained for the statutory period per our Privacy Policy.</p>
                    <p><strong>By EcomgstPro:</strong> We may suspend or terminate your account immediately, without prior notice, if we determine that you have:</p>
                    <ul class="ps-list">
                        <li class="no-icon"><i class="bi bi-x-circle-fill"></i><span>Violated these Terms, particularly the Acceptable Use Policy in Section 6.</span></li>
                        <li class="no-icon"><i class="bi bi-x-circle-fill"></i><span>Used the platform to file fraudulent or false GST returns.</span></li>
                        <li class="no-icon"><i class="bi bi-x-circle-fill"></i><span>Failed to pay subscription fees after due notice.</span></li>
                        <li class="no-icon"><i class="bi bi-x-circle-fill"></i><span>Engaged in conduct that endangers the security of the platform or other users.</span></li>
                    </ul>
                    <p>Upon termination, your access to the platform will cease. Your data will be handled per the retention schedule in our Privacy Policy. For terminations due to policy violations, no refund will be issued.</p>
                </div>

                <div id="t14" class="ps">
                    <span class="ps-anchor"></span>
                    <div class="ps-header"><div class="ps-num">14</div><h2 class="ps-title">Governing Law & Dispute Resolution</h2></div>
                    <p>These Terms are governed by and construed in accordance with the laws of India, without regard to conflict of law principles. Specifically:</p>
                    <ul class="ps-list">
                        <li><i class="bi bi-balance-scale-fill"></i><span><strong>Applicable Law:</strong> The Information Technology Act 2000, GST Act 2017, Consumer Protection Act 2019, and the Indian Contract Act 1872, as applicable.</span></li>
                        <li><i class="bi bi-balance-scale-fill"></i><span><strong>Jurisdiction:</strong> Any disputes shall be subject to the exclusive jurisdiction of the courts located in India at the registered office of EcomgstPro Technologies Private Limited.</span></li>
                        <li><i class="bi bi-balance-scale-fill"></i><span><strong>Arbitration:</strong> Before initiating litigation, parties agree to attempt good-faith negotiation for 30 days. If unresolved, disputes shall be referred to arbitration under the Arbitration and Conciliation Act 1996, with a sole arbitrator agreed upon by both parties.</span></li>
                    </ul>
                </div>

                <div id="t15" class="ps">
                    <span class="ps-anchor"></span>
                    <div class="ps-header"><div class="ps-num">15</div><h2 class="ps-title">Modifications to Terms</h2></div>
                    <p>EcomgstPro may update these Terms from time to time to reflect changes in services, law, or business practices. When we make material changes:</p>
                    <ul class="ps-list">
                        <li><i class="bi bi-bell-fill"></i><span>We will send an email notification to your registered email at least <strong>15 days before</strong> the changes take effect.</span></li>
                        <li><i class="bi bi-bell-fill"></i><span>A notice will appear on the EcomgstPro dashboard for all active users.</span></li>
                        <li><i class="bi bi-bell-fill"></i><span>The updated "Effective Date" will be displayed at the top of this page.</span></li>
                    </ul>
                    <p>Continued use of EcomgstPro after the effective date constitutes acceptance of the updated Terms. If you disagree with any changes, you may close your account before the effective date and request a prorated refund for unused subscription time.</p>
                </div>

                <div id="t16" class="ps">
                    <span class="ps-anchor"></span>
                    <div class="ps-header"><div class="ps-num">16</div><h2 class="ps-title">Contact Us</h2></div>
                    <p>If you have any questions or concerns about these Terms and Conditions, please reach out to us:</p>
                    <div class="ps-infobox">
                        <p><strong>EcomgstPro Technologies Private Limited</strong><br>
                        <strong>Legal queries:</strong> legal@ecomgstpro.com<br>
                        <strong>General support:</strong> support@ecomgstpro.com<br>
                        <strong>Grievance Officer:</strong> CA Rajat — grievance@ecomgstpro.com<br>
                        <strong>Response time:</strong> Within 30 days for legal matters; 48 hours for support</p>
                    </div>
                </div>

                <div class="contact-card">
                    <h3>Questions about our Terms?</h3>
                    <p>Our team reviews every query. For legal matters, our Grievance Officer responds within 30 days.</p>
                    <div class="contact-links">
                        <a href="mailto:legal@ecomgstpro.com" class="contact-link"><i class="bi bi-envelope-fill"></i> legal@ecomgstpro.com</a>
                        <a href="{{ route('privacy') }}" class="contact-link"><i class="bi bi-shield-lock"></i> Privacy Policy</a>
                        <a href="{{ asset('downloads/EcomgstPro-terms-conditions.pdf') }}" download class="contact-link"><i class="bi bi-file-earmark-pdf-fill"></i> Download PDF</a>
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
