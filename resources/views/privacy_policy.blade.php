@extends('layouts.app')

{{-- ============================================================
     GST Pilot — privacy-policy.blade.php (FULL WIDTH VERSION)
     Theme: Dark Navy #0a1628 + Teal #00c98d + Sky #00aaff
     Fonts: Syne (headings) + DM Sans (body)
     FIXES:
       1. Full-width content (no wasted space)
       2. TOC as collapsible drawer with toggle button
       3. Responsive horizontal TOC on mobile
       4. No PDF download option
     ============================================================ --}}

@section('content')

<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
/* ── ROOT ── */
:root{
    --navy:#0a1628;--deep:#0d1f3c;--teal:#00c98d;--sky:#00aaff;
    --teal-low:rgba(0,201,141,.12);--border:rgba(255,255,255,.08);
    --muted:#8a9bbf;--txt:#e8f0fe;
}
html,body{overflow-x:hidden}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'DM Sans',sans-serif;background:#f0f4ff;color:var(--navy)}
.gp-container{max-width:1200px;margin:0 auto;padding:0 32px}
.dot-grid{
    background-image:radial-gradient(rgba(255,255,255,.03) 1px,transparent 1px);
    background-size:26px 26px;
}

/* ── HERO ── */
.policy-hero{
    background:linear-gradient(135deg,var(--navy) 0%,var(--deep) 55%,#0a2240 100%);
    padding:40px 0 30px;
    border-bottom:1px solid rgba(255,255,255,.05);
}
.policy-hero-inner{position:relative;z-index:2}
.ph-breadcrumb{display:flex;align-items:center;gap:8px;font-size:13px;color:var(--muted);margin-bottom:16px}
.ph-breadcrumb a{color:var(--muted);text-decoration:none;transition:color .2s}
.ph-breadcrumb a:hover{color:var(--teal)}
.ph-breadcrumb i{font-size:10px;color:var(--teal)}
.ph-badge{
    display:inline-flex;align-items:center;gap:8px;
    background:var(--teal-low);border:1px solid rgba(0,201,141,.25);
    border-radius:30px;padding:4px 14px;
    font-size:11px;font-weight:700;letter-spacing:1.5px;
    text-transform:uppercase;color:var(--teal);margin-bottom:12px;
}
.ph-title{
    font-family:'Syne',sans-serif;font-weight:800;
    font-size:clamp(28px,3.5vw,42px);color:#fff;
    letter-spacing:-1px;line-height:1.1;margin-bottom:10px;
}
.ph-title em{color:var(--teal);font-style:normal}
.ph-meta{
    display:flex;align-items:center;gap:20px;flex-wrap:wrap;
    font-size:12px;color:var(--muted);margin-bottom:16px;
}
.ph-meta i{color:var(--teal)}
.ph-actions{display:flex;gap:10px;flex-wrap:wrap}
.btn-dl{
    display:inline-flex;align-items:center;gap:8px;
    background:linear-gradient(135deg,var(--teal),#00e0a8);
    color:var(--navy);font-weight:700;font-size:13px;
    padding:10px 20px;border-radius:8px;text-decoration:none;
    box-shadow:0 4px 18px rgba(0,201,141,.35);transition:all .2s;
    cursor:pointer;border:none;
}
.btn-dl:hover{transform:translateY(-2px);box-shadow:0 8px 28px rgba(0,201,141,.45);color:var(--navy)}
.btn-outline{
    display:inline-flex;align-items:center;gap:8px;
    background:transparent;border:1px solid rgba(255,255,255,.15);
    color:var(--txt);font-weight:600;font-size:13px;
    padding:9px 18px;border-radius:8px;text-decoration:none;transition:all .2s;
}
.btn-outline:hover{border-color:rgba(255,255,255,.35);color:#fff;background:rgba(255,255,255,.06)}

/* ── TICKER ── */
.ticker{background:var(--teal);padding:10px 0;overflow:hidden}
.ticker-track{
    display:flex;align-items:center;gap:48px;
    animation:ticker-scroll 24s linear infinite;width:max-content;
}
.ticker-item{
    display:flex;align-items:center;gap:10px;
    font-family:'Syne',sans-serif;font-weight:700;font-size:12px;
    letter-spacing:1px;text-transform:uppercase;color:var(--navy);white-space:nowrap;
}
.ticker-item::after{content:'✦';font-size:10px;opacity:.5;margin-left:24px}
@keyframes ticker-scroll{from{transform:translateX(0)}to{transform:translateX(-50%)}}

/* ────────────────────────────────────────────────
   MAIN LAYOUT - FULL WIDTH + COLLAPSIBLE TOC DRAWER
   ──────────────────────────────────────────────── */
.policy-section{
    background:#f0f4ff;
    padding:40px 0 64px;
}

.policy-layout{
    display:block;
    position:relative;
}

/* Left column: HIDDEN - appears as side drawer */
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

/* Sticky TOC card in drawer */
.policy-toc{
    position:relative;
    top:0;
    background:#fff;
    border-radius:18px;
    border:1px solid #e8edf5;
    box-shadow:0 4px 20px rgba(0,0,0,.04);
    padding:24px 20px;
    max-height:none;
    overflow-y:visible;
    margin:0 16px;
}

/* TOC Toggle Button */
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

/* Overlay when TOC is open */
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

/* Right column - FULL WIDTH */
.policy-content{
    background:#fff;
    border-radius:18px;
    border:1px solid #e8edf5;
    padding:48px 44px;
    box-shadow:0 4px 20px rgba(0,0,0,.04);
    max-width:100%;
    margin:0;
}

.toc-title{
    font-family:'Syne',sans-serif;font-weight:700;font-size:13px;
    text-transform:uppercase;letter-spacing:1.5px;color:var(--teal);
    margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid #eef2f8;
}
.toc-list{list-style:none;display:flex;flex-direction:column;gap:4px}
.toc-list a{
    display:flex;align-items:center;gap:8px;
    font-size:13px;color:#4a5568;text-decoration:none;
    padding:7px 10px;border-radius:8px;transition:all .2s;
    line-height:1.35;
}
.toc-list a:hover,.toc-list a.active{background:var(--teal-low);color:var(--teal);font-weight:600}
.toc-list a .toc-num{
    font-family:'Syne',sans-serif;font-weight:800;font-size:11px;
    color:var(--teal);min-width:18px;
}
.toc-divider{height:1px;background:#eef2f8;margin:12px 0}

/* ── CONTENT ELEMENTS ── */
.policy-intro{
    background:linear-gradient(135deg,#f0faf6,#e8f5ff);
    border-radius:14px;border:1px solid rgba(0,201,141,.2);
    padding:24px 28px;margin-bottom:40px;
}
.policy-intro p{font-size:15px;line-height:1.75;color:#374151;margin:0}
.policy-intro strong{color:var(--navy)}

.ps{margin-bottom:48px;padding-bottom:48px;border-bottom:1px solid #eef2f8}
.ps:last-child{border-bottom:none;margin-bottom:0}
.ps-anchor{display:block;visibility:hidden;margin-top:-100px;padding-top:100px}
.ps-header{display:flex;align-items:flex-start;gap:14px;margin-bottom:20px}
.ps-num{
    width:38px;height:38px;border-radius:10px;flex-shrink:0;
    background:var(--teal-low);border:1px solid rgba(0,201,141,.2);
    display:flex;align-items:center;justify-content:center;
    font-family:'Syne',sans-serif;font-weight:800;font-size:14px;color:var(--teal);
}
.ps-title{
    font-family:'Syne',sans-serif;font-weight:800;font-size:22px;
    color:var(--navy);line-height:1.2;margin:0;padding-top:6px;
}
.ps p{font-size:15.5px;line-height:1.8;color:#374151;margin-bottom:16px}
.ps p:last-child{margin-bottom:0}

.ps-infobox{
    background:#f8faff;border-left:4px solid var(--teal);
    border-radius:0 12px 12px 0;padding:18px 20px;margin:20px 0;
}
.ps-infobox p{font-size:14.5px;color:#374151;margin:0;line-height:1.7}

.ps-list{list-style:none;display:flex;flex-direction:column;gap:10px;margin:16px 0}
.ps-list li{display:flex;align-items:flex-start;gap:10px;font-size:15px;line-height:1.65;color:#374151}
.ps-list li i{color:var(--teal);font-size:15px;flex-shrink:0;margin-top:3px}
.ps-list li strong{color:var(--navy)}

.ps-table{width:100%;border-collapse:collapse;margin:20px 0;font-size:14px}
.ps-table thead tr{background:var(--navy)}
.ps-table thead th{padding:12px 16px;color:#fff;font-family:'Syne',sans-serif;font-weight:700;font-size:13px;text-align:left}
.ps-table tbody tr{border-bottom:1px solid #eef2f8;transition:background .15s}
.ps-table tbody tr:hover{background:#f8faff}
.ps-table tbody td{padding:12px 16px;color:#374151;vertical-align:top;line-height:1.55}
.ps-table tbody td:first-child{font-weight:600;color:var(--navy)}

.ps-highlight{
    background:var(--navy);border-radius:14px;padding:24px 28px;margin:24px 0;
}
.ps-highlight p{color:var(--txt);font-size:14.5px;line-height:1.75;margin:0}
.ps-highlight strong{color:var(--teal)}

.contact-card{
    background:linear-gradient(135deg,var(--navy),var(--deep));
    border:1px solid var(--border);border-radius:18px;
    padding:32px 28px;margin-top:48px;text-align:center;
}
.contact-card h3{font-family:'Syne',sans-serif;font-weight:800;font-size:22px;color:#fff;margin-bottom:10px}
.contact-card p{font-size:15px;color:var(--muted);max-width:480px;margin:0 auto 24px}
.contact-links{display:flex;gap:12px;justify-content:center;flex-wrap:wrap}
.contact-link{
    display:inline-flex;align-items:center;gap:8px;
    background:rgba(0,201,141,.12);border:1px solid rgba(0,201,141,.25);
    border-radius:10px;padding:10px 20px;
    font-size:14px;font-weight:600;color:var(--teal);text-decoration:none;
    transition:all .2s;cursor:pointer;
}
.contact-link:hover{background:rgba(0,201,141,.2);color:var(--teal)}

/* ── RESPONSIVE ── */
@media(max-width:1024px){
    .policy-content{padding:32px 28px}
    .toc-toggle{display:flex}
}

@media(max-width:768px){
    .gp-container{padding:0 20px}
    .policy-content{padding:24px 18px;border-radius:12px}
    .policy-section{padding:30px 0 40px}
    .ps-title{font-size:18px}
    .policy-hero{padding:30px 0 20px}
    .ph-actions{flex-direction:column}
    .btn-dl,.btn-outline{justify-content:center}
}

@media(max-width:640px){
    .gp-container{padding:0 16px}
    .policy-content{padding:18px 14px}
    .toc-col{width:240px;padding-top:70px}
    .toc-toggle{width:44px;height:44px;font-size:18px;left:16px;top:100px}
    .ps{margin-bottom:32px;padding-bottom:32px}
    .ps-title{font-size:16px}
    .ps-num{width:32px;height:32px;font-size:12px}
}
</style>

{{-- ── TOC OVERLAY (Click to close) ── --}}
<div class="toc-overlay" id="tocOverlay"></div>

{{-- ── TOC TOGGLE BUTTON ── --}}
<button class="toc-toggle" id="tocToggle" title="Show Contents">
    <i class="bi bi-list-ul"></i>
</button>

{{-- ── HERO ── --}}
<section class="policy-hero dot-grid">
    <div class="gp-container">
        <div class="policy-hero-inner">
            <div class="ph-breadcrumb">
                <a href="{{ route('home') }}">Home</a>
                <i class="bi bi-chevron-right"></i>
                <span>Privacy &amp; Refund Policy</span>
            </div>
            <div class="ph-badge"><i class="bi bi-shield-lock-fill"></i> Legal Documents</div>
            <h1 class="ph-title">Privacy &amp; <em>Refund</em> Policy</h1>
            <div class="ph-meta">
                <span><i class="bi bi-calendar3"></i> Effective: 1 January 2026</span>
                <span><i class="bi bi-arrow-clockwise"></i> Last Updated: 1 January 2026</span>
                <span><i class="bi bi-geo-alt-fill"></i> Governed by Indian Law</span>
            </div>
            <div class="ph-actions">
                <a href="{{ route('terms') }}" class="btn-outline">
                    <i class="bi bi-file-text"></i> Terms &amp; Conditions
                </a>
            </div>
        </div>
    </div>
</section>

{{-- ── TICKER ── --}}
<div class="ticker">
    <div class="ticker-track">
        @php $ti=['Privacy Policy','Your Data is Safe','ISO 27001 Certified','GSP Certified','No Data Selling','Bank-Grade Encryption','Indian Law Compliant','PDPB 2023 Aligned','Transparent Practices','Zero Third-Party Ads','Refund Policy','7‑Day Refund','Pro‑rata Refunds']; @endphp
        @foreach(array_merge($ti,$ti) as $t)<div class="ticker-item">{{ $t }}</div>@endforeach
    </div>
</div>

{{-- ── MAIN LAYOUT ── --}}
<section class="policy-section">
    <div class="gp-container">
        <div class="policy-layout">

            {{-- ── LEFT: COLLAPSIBLE TOC DRAWER ── --}}
            <div class="toc-col" id="tocCol">
                <nav class="policy-toc" aria-label="Table of Contents">
                    <div class="toc-title"><i class="bi bi-list-ul"></i> Contents</div>
                    <ul class="toc-list">
                        <li><a href="#s1"><span class="toc-num">01</span> Who We Are</a></li>
                        <li><a href="#s2"><span class="toc-num">02</span> Data We Collect</a></li>
                        <li><a href="#s3"><span class="toc-num">03</span> How We Use Data</a></li>
                        <li><a href="#s4"><span class="toc-num">04</span> Data Storage &amp; Security</a></li>
                        <li><a href="#s5"><span class="toc-num">05</span> Sharing &amp; Disclosure</a></li>
                        <li><a href="#s6"><span class="toc-num">06</span> Cookies &amp; Tracking</a></li>
                        <li><a href="#s7"><span class="toc-num">07</span> Data Retention</a></li>
                        <li><a href="#s8"><span class="toc-num">08</span> Your Rights</a></li>
                        <li><a href="#s9"><span class="toc-num">09</span> Children's Privacy</a></li>
                        <li><a href="#s10"><span class="toc-num">10</span> Third‑Party Links</a></li>
                        <li><a href="#s11"><span class="toc-num">11</span> International Transfers</a></li>
                        <li><a href="#s12"><span class="toc-num">12</span> GSP &amp; Portal Access</a></li>
                        <li><a href="#s13"><span class="toc-num">13</span> Policy Changes</a></li>
                        <li><a href="#s14"><span class="toc-num">14</span> Contact Us</a></li>
                        <li class="toc-divider"></li>
                        <li><a href="#r1"><span class="toc-num">15</span> Refund Overview</a></li>
                        <li><a href="#r2"><span class="toc-num">16</span> Eligible Refunds</a></li>
                        <li><a href="#r3"><span class="toc-num">17</span> Non‑Refundable Cases</a></li>
                        <li><a href="#r4"><span class="toc-num">18</span> Annual Plan Refunds</a></li>
                        <li><a href="#r5"><span class="toc-num">19</span> How to Request</a></li>
                        <li><a href="#r6"><span class="toc-num">20</span> Refund Timeline</a></li>
                        <li><a href="#r7"><span class="toc-num">21</span> Cancellation Policy</a></li>
                        <li><a href="#r8"><span class="toc-num">22</span> Plan Downgrades</a></li>
                        <li><a href="#r9"><span class="toc-num">23</span> Failed Payments</a></li>
                    </ul>
                </nav>
            </div>
            {{-- END LEFT --}}

            {{-- ── RIGHT: FULL WIDTH CONTENT ── --}}
            <div class="policy-content">

                <div class="policy-intro">
                    <p><strong>EcomgstPro</strong> ("we", "us", "our") is committed to protecting the privacy and security of your personal and financial data. This Privacy &amp; Refund Policy explains what information we collect, why we collect it, how we protect it, and what rights you have over it, as well as our refund and cancellation terms. By using EcomgstPro's platform, website, or any associated service, you agree to the practices described in this Policy. Please read it carefully before using our services.</p>
                </div>

                {{-- ============ PRIVACY SECTIONS ============ --}}
                <div class="ps">
                    <span class="ps-anchor" id="s1"></span>
                    <div class="ps-header">
                        <div class="ps-num">01</div>
                        <h2 class="ps-title">Who We Are</h2>
                    </div>
                    <p>EcomgstPro is a GST compliance automation platform developed and operated by <strong>EcomgstPro Technologies Private Limited</strong>, founded in 2026 and registered under the Companies Act, 2013, with its registered office in India. EcomgstPro operates as a <strong>GST Suvidha Provider (GSP)</strong> certified by the Goods and Services Tax Network (GSTN), and holds ISO 27001 certification for information security management.</p>
                    <p>For the purposes of applicable data protection law, including the Digital Personal Data Protection Act, 2023 (DPDPA), EcomgstPro Technologies Private Limited is the <strong>Data Fiduciary</strong> responsible for your personal data.</p>
                    <div class="ps-infobox">
                        <p><strong>Registered Address:</strong> Office No. 458, 4th Floor, Agarwal Metro Heights Tower, Netaji Subhash Place, Pitampura, Delhi – 110034, India<br>
                        <strong>Email:</strong> support@ecomgstpro.com</p>
                    </div>
                </div>

                <div class="ps">
                    <span class="ps-anchor" id="s2"></span>
                    <div class="ps-header">
                        <div class="ps-num">02</div>
                        <h2 class="ps-title">Data We Collect</h2>
                    </div>
                    <p>We collect only the data necessary to provide our GST compliance services. This falls into three categories:</p>
                    <table class="ps-table">
                        <thead><tr><th>Category</th><th>Examples</th><th>Why Collected</th></tr></thead>
                        <tbody>
                            <tr><td>Account Data</td><td>Name, email address, mobile number, business name, GSTIN, PAN</td><td>Account creation, identity verification, GST portal authentication</td></tr>
                            <tr><td>Financial &amp; Tax Data</td><td>Bank statements, purchase registers, marketplace settlement reports, invoice data</td><td>Processing GSTR-1, reconciliation, bank statement conversion</td></tr>
                            <tr><td>Usage Data</td><td>IP address, browser type, pages visited, session duration, feature usage logs</td><td>Security monitoring, platform improvement, fraud prevention</td></tr>
                            <tr><td>Communication Data</td><td>Support tickets, chat transcripts, email correspondence</td><td>Customer support, dispute resolution</td></tr>
                            <tr><td>Payment Data</td><td>Transaction ID, plan selected, billing address</td><td>Subscription management (card details handled by payment gateway, not stored by us)</td></tr>
                        </tbody>
                    </table>
                    <p>We do not collect sensitive personal data such as biometric data, religious beliefs, political opinions, or health information. We do not collect any data beyond what is necessary to provide the services you have requested.</p>
                </div>

                <div class="ps">
                    <span class="ps-anchor" id="s3"></span>
                    <div class="ps-header">
                        <div class="ps-num">03</div>
                        <h2 class="ps-title">How We Use Your Data</h2>
                    </div>
                    <p>Your data is used exclusively for the following purposes:</p>
                    <ul class="ps-list">
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>GST Return Processing:</strong> Generating GSTR-1, GSTR-3B, and other returns from the data you upload. Your financial files are processed in-memory and not permanently stored after processing is complete.</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>2A/2B Reconciliation:</strong> Matching your purchase data against GST portal records to identify mismatches and maximise ITC claims.</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>Bank Statement Conversion:</strong> Converting your uploaded bank PDFs into Tally-compatible XML. Files are purged after download.</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>Account Management:</strong> Maintaining your EcomgstPro account, processing payments, and managing subscriptions.</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>Legal Compliance:</strong> Retaining data as required under Indian tax law, the IT Act 2000, and GSTN regulations.</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>Platform Security:</strong> Detecting and preventing unauthorised access, fraud, and abuse.</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>Service Communications:</strong> Sending product updates, filing reminders, and support responses. Marketing emails are opt-in only.</span></li>
                    </ul>
                    <div class="ps-highlight">
                        <p><strong>We do not use your financial or tax data to train AI models, serve advertisements, or profile you for marketing purposes.</strong> Your GST data is yours — we only process it on your instruction.</p>
                    </div>
                </div>

                <div class="ps">
                    <span class="ps-anchor" id="s4"></span>
                    <div class="ps-header">
                        <div class="ps-num">04</div>
                        <h2 class="ps-title">Data Storage &amp; Security</h2>
                    </div>
                    <p>EcomgstPro takes data security extremely seriously. We are ISO 27001 certified, which means our information security management system meets the highest international standards. Here is exactly how we protect your data:</p>
                    <ul class="ps-list">
                        <li><i class="bi bi-shield-fill-check"></i><span><strong>Encryption in Transit:</strong> All data transmitted between your browser and our servers is encrypted using TLS 1.3 (Transport Layer Security). HTTPS is enforced on every page and API endpoint.</span></li>
                        <li><i class="bi bi-shield-fill-check"></i><span><strong>Encryption at Rest:</strong> All stored data — including your account information and uploaded documents — is encrypted using AES-256 encryption.</span></li>
                        <li><i class="bi bi-shield-fill-check"></i><span><strong>Access Controls:</strong> Only authorised EcomgstPro engineers with a demonstrated business need can access production systems, subject to multi-factor authentication and audit logging.</span></li>
                        <li><i class="bi bi-shield-fill-check"></i><span><strong>Indian Data Centres:</strong> All primary data is stored on servers located in India, ensuring compliance with Indian data localisation requirements.</span></li>
                        <li><i class="bi bi-shield-fill-check"></i><span><strong>Regular Security Audits:</strong> We conduct regular penetration testing and vulnerability assessments by independent security firms.</span></li>
                        <li><i class="bi bi-shield-fill-check"></i><span><strong>File Purging:</strong> Uploaded bank statements and marketplace reports used for processing are automatically purged from our systems after your session ends or the output file is downloaded — whichever comes first.</span></li>
                    </ul>
                    <div class="ps-infobox">
                        <p>Despite best efforts, no internet transmission is 100% secure. In the event of a data breach that affects your personal data, we will notify you and the relevant authority within 72 hours as required by the DPDPA 2023.</p>
                    </div>
                </div>

                <div class="ps">
                    <span class="ps-anchor" id="s5"></span>
                    <div class="ps-header">
                        <div class="ps-num">05</div>
                        <h2 class="ps-title">Sharing &amp; Disclosure of Data</h2>
                    </div>
                    <p>We do not sell, rent, or trade your personal data to any third party. Period. We may share limited data only in the following circumstances:</p>
                    <ul class="ps-list">
                        <li><i class="bi bi-arrow-right-circle-fill"></i><span><strong>GSTN / GST Portal (on your instruction):</strong> When you use EcomgstPro to file returns, your return data is transmitted to the GST portal via our GSP API. This is the entire purpose of the filing feature and is done only on your explicit instruction.</span></li>
                        <li><i class="bi bi-arrow-right-circle-fill"></i><span><strong>Payment Processors:</strong> Our payment gateway (Razorpay / PayU / equivalent) processes your subscription payments. They receive only transaction amounts and billing reference — never your tax or financial data.</span></li>
                        <li><i class="bi bi-arrow-right-circle-fill"></i><span><strong>Cloud Infrastructure Providers:</strong> Our hosting provider (ISO 27001 certified, India-based) has limited, contractually bound access to infrastructure. They cannot access your data content.</span></li>
                        <li><i class="bi bi-arrow-right-circle-fill"></i><span><strong>Legal Requirements:</strong> We may disclose data when required by law, court order, or a competent government authority under the IT Act 2000 or other applicable Indian legislation.</span></li>
                    </ul>
                    <div class="ps-highlight">
                        <p><strong>We never share your financial data, tax data, or GSTIN information with advertisers, data brokers, or any commercial third party.</strong></p>
                    </div>
                </div>

                <div class="ps">
                    <span class="ps-anchor" id="s6"></span>
                    <div class="ps-header">
                        <div class="ps-num">06</div>
                        <h2 class="ps-title">Cookies &amp; Tracking Technologies</h2>
                    </div>
                    <p>EcomgstPro uses cookies and similar technologies to make the platform work correctly and to improve your experience. We do not use third-party advertising cookies.</p>
                    <table class="ps-table">
                        <thead><tr><th>Cookie Type</th><th>Purpose</th><th>Duration</th></tr></thead>
                        <tbody>
                            <tr><td>Essential Cookies</td><td>Login session, CSRF protection, platform functionality</td><td>Session / 30 days</td></tr>
                            <tr><td>Preference Cookies</td><td>Your language, theme, and display preferences</td><td>1 year</td></tr>
                            <tr><td>Analytics Cookies</td><td>Aggregate usage statistics (no personal identification)</td><td>90 days</td></tr>
                            <tr><td>Security Cookies</td><td>Fraud detection and bot prevention</td><td>Session</td></tr>
                        </tbody>
                    </table>
                    <p>You can control cookies through your browser settings. Disabling essential cookies will prevent you from using the logged-in features of EcomgstPro. We do not use Facebook Pixel, Google Ads remarketing, or any advertising tracking technology.</p>
                </div>

                <div class="ps">
                    <span class="ps-anchor" id="s7"></span>
                    <div class="ps-header">
                        <div class="ps-num">07</div>
                        <h2 class="ps-title">Data Retention</h2>
                    </div>
                    <p>We retain your data only for as long as necessary for the purposes described in this Policy, or as required by law.</p>
                    <table class="ps-table">
                        <thead><tr><th>Data Type</th><th>Retention Period</th><th>Reason</th></tr></thead>
                        <tbody>
                            <tr><td>Account Information</td><td>Duration of account + 3 years</td><td>Legal obligation, dispute resolution</td></tr>
                            <tr><td>GST Return Data (filed)</td><td>8 years from date of filing</td><td>GST law requires 6-year audit availability; we retain 8 for safety</td></tr>
                            <tr><td>Uploaded Processing Files</td><td>Purged after session / download</td><td>No retention needed once output is generated</td></tr>
                            <tr><td>Payment Records</td><td>8 years</td><td>Income Tax Act, GST Act requirements</td></tr>
                            <tr><td>Support Tickets</td><td>3 years</td><td>Service quality, dispute resolution</td></tr>
                            <tr><td>Usage Logs</td><td>90 days</td><td>Security monitoring and debugging</td></tr>
                        </tbody>
                    </table>
                    <p>After the retention period, your data is permanently and securely deleted using methods that make recovery impossible. You can request early deletion — see Section 8 for your rights.</p>
                </div>

                <div class="ps">
                    <span class="ps-anchor" id="s8"></span>
                    <div class="ps-header">
                        <div class="ps-num">08</div>
                        <h2 class="ps-title">Your Rights Under Indian Law</h2>
                    </div>
                    <p>Under the Digital Personal Data Protection Act, 2023 (DPDPA) and other applicable Indian law, you have the following rights regarding your personal data:</p>
                    <ul class="ps-list">
                        <li><i class="bi bi-person-check-fill"></i><span><strong>Right to Access:</strong> Request a copy of the personal data we hold about you at any time.</span></li>
                        <li><i class="bi bi-pencil-fill"></i><span><strong>Right to Correction:</strong> Request correction of inaccurate or incomplete personal data.</span></li>
                        <li><i class="bi bi-trash-fill"></i><span><strong>Right to Erasure:</strong> Request deletion of your personal data when it is no longer needed for the stated purpose (subject to legal retention requirements).</span></li>
                        <li><i class="bi bi-x-circle-fill"></i><span><strong>Right to Withdraw Consent:</strong> Withdraw consent for data processing where consent is the legal basis. Withdrawal does not affect the lawfulness of prior processing.</span></li>
                        <li><i class="bi bi-info-circle-fill"></i><span><strong>Right to Grievance Redressal:</strong> Lodge a complaint with our Grievance Officer. If unsatisfied, you may approach the Data Protection Board of India.</span></li>
                        <li><i class="bi bi-person-x-fill"></i><span><strong>Right to Nominate:</strong> Nominate another individual to exercise data rights on your behalf in the event of death or incapacity.</span></li>
                    </ul>
                    <p>To exercise any of these rights, email <strong>support@ecomgstpro.com</strong> from your registered email address. We will respond within 30 days. Identity verification will be required before processing requests.</p>
                </div>

                <div class="ps">
                    <span class="ps-anchor" id="s9"></span>
                    <div class="ps-header">
                        <div class="ps-num">09</div>
                        <h2 class="ps-title">Children's Privacy</h2>
                    </div>
                    <p>EcomgstPro is a professional B2B GST compliance platform intended exclusively for use by adults who are GST-registered business owners, accountants, or tax professionals. Our services are not directed at or intended for use by individuals under the age of 18.</p>
                    <p>We do not knowingly collect personal data from anyone under 18 years of age. If we become aware that a minor has created an account or provided personal data, we will take immediate steps to delete that data and close the account. If you believe a minor has used our platform, please contact us at support@ecomgstpro.com immediately.</p>
                </div>

                <div class="ps">
                    <span class="ps-anchor" id="s10"></span>
                    <div class="ps-header">
                        <div class="ps-num">10</div>
                        <h2 class="ps-title">Third‑Party Links &amp; Integrations</h2>
                    </div>
                    <p>EcomgstPro may contain links to third-party websites, including the official GST portal (gst.gov.in), income tax portal, and Tally documentation. These external sites have their own privacy policies, which we do not control and are not responsible for.</p>
                    <p>When you use our marketplace integrations (Amazon Seller Central, Flipkart Seller Hub, Meesho dashboard), you access those platforms directly. EcomgstPro only receives the report file you upload to us — we do not maintain API connections to marketplace platforms that read your seller data without your explicit action.</p>
                </div>

                <div class="ps">
                    <span class="ps-anchor" id="s11"></span>
                    <div class="ps-header">
                        <div class="ps-num">11</div>
                        <h2 class="ps-title">International Data Transfers</h2>
                    </div>
                    <p>EcomgstPro stores all primary user data on servers located within India. We do not routinely transfer personal data outside India. In the limited circumstances where we use internationally-hosted tools (such as email delivery services or error monitoring), we ensure such transfers comply with applicable Indian data protection regulations and are governed by appropriate contractual safeguards.</p>
                    <p>Your GSTIN data, financial records, and tax return data are never transferred outside India under any circumstances.</p>
                </div>

                <div class="ps">
                    <span class="ps-anchor" id="s12"></span>
                    <div class="ps-header">
                        <div class="ps-num">12</div>
                        <h2 class="ps-title">GSP Access &amp; GST Portal Data</h2>
                    </div>
                    <p>EcomgstPro is a <strong>GST Suvidha Provider (GSP)</strong> certified by the GSTN. This certification authorises us to access the GST portal on behalf of taxpayers via API. The following governs how we handle portal-related data:</p>
                    <ul class="ps-list">
                        <li><i class="bi bi-check2-circle"></i><span>We access the GST portal only with your explicit instruction — when you initiate a filing, reconciliation fetch, or return download from within EcomgstPro.</span></li>
                        <li><i class="bi bi-check2-circle"></i><span>Your GST portal credentials are never stored by EcomgstPro. Authentication is handled via OTP-based government APIs where your credentials go directly to GSTN.</span></li>
                        <li><i class="bi bi-check2-circle"></i><span>Data retrieved from the GST portal (such as GSTR-2A/2B records) is stored in your EcomgstPro account for the duration of the return period plus the statutory retention period.</span></li>
                        <li><i class="bi bi-check2-circle"></i><span>All GSP-related data handling complies with GSTN's data sharing and security guidelines.</span></li>
                    </ul>
                </div>

                <div class="ps">
                    <span class="ps-anchor" id="s13"></span>
                    <div class="ps-header">
                        <div class="ps-num">13</div>
                        <h2 class="ps-title">Changes to This Policy</h2>
                    </div>
                    <p>We may update this Privacy Policy from time to time to reflect changes in our services, applicable law, or regulatory guidance. When we make material changes, we will:</p>
                    <ul class="ps-list">
                        <li><i class="bi bi-bell-fill"></i><span>Send an email notification to your registered email address at least 15 days before the changes take effect.</span></li>
                        <li><i class="bi bi-bell-fill"></i><span>Display a prominent notice on the EcomgstPro dashboard when you log in.</span></li>
                        <li><i class="bi bi-bell-fill"></i><span>Update the "Last Updated" date at the top of this page.</span></li>
                    </ul>
                    <p>Your continued use of EcomgstPro after the effective date of the updated Policy constitutes your acceptance of the changes. If you do not agree with the updated Policy, you may close your account before the effective date.</p>
                </div>

                <div class="ps">
                    <span class="ps-anchor" id="s14"></span>
                    <div class="ps-header">
                        <div class="ps-num">14</div>
                        <h2 class="ps-title">Contact &amp; Grievance Redressal</h2>
                    </div>
                    <p>If you have any questions, concerns, or requests related to this Privacy Policy or the handling of your personal data, please contact us through the following channels:</p>
                    <div class="ps-infobox">
                        <p><strong>Email:</strong> support@ecomgstpro.com<br>
                        <strong>Phone:</strong> +91-8076865836<br>
                        <strong>WhatsApp:</strong> +91-8076865836<br>
                        <strong>Address:</strong> Office No. 458, 4th Floor, Agarwal Metro Heights Tower, Netaji Subhash Place, Pitampura, Delhi – 110034, India<br>
                        <strong>Response Time:</strong> Within 30 days of receipt of complaint</p>
                    </div>
                    <p>If your grievance is not satisfactorily resolved within 30 days, you have the right to escalate to the <strong>Data Protection Board of India</strong> once it is constituted under the Digital Personal Data Protection Act, 2023.</p>
                </div>

                {{-- ============ REFUND & CANCELLATION POLICY ============ --}}
                <div style="margin-top:60px;padding-top:40px;border-top:2px solid #eef2f8;">
                    <h2 style="font-family:'Syne',sans-serif;font-weight:800;font-size:28px;color:var(--navy);margin-bottom:12px;">
                        <i class="bi bi-arrow-repeat" style="color:var(--teal);margin-right:10px;"></i> Refund &amp; Cancellation Policy
                    </h2>
                    <p style="font-size:15.5px;line-height:1.8;color:#374151;margin-bottom:32px;">
                        At EcomgstPro, we stand behind our platform. This Refund &amp; Cancellation Policy explains when and how you can request a refund, how cancellations work, and what happens to your data and subscription access. This policy forms part of your agreement with <strong>EcomgstPro Technologies Private Limited</strong> and should be read alongside our <a href="{{ route('terms') }}" style="color:var(--teal);">Terms &amp; Conditions</a> and <a href="{{ route('privacy') }}" style="color:var(--teal);">Privacy Policy</a>.
                    </p>

                    <div class="ps">
                        <span class="ps-anchor" id="r1"></span>
                        <div class="ps-header">
                            <div class="ps-num">15</div>
                            <h2 class="ps-title">Refund Policy Overview</h2>
                        </div>
                        <p>EcomgstPro offers a straightforward refund process for eligible subscriptions. Our goal is to ensure you are fully satisfied with our services. If you are not, we will work with you to resolve the issue or process a refund in accordance with this policy.</p>
                        <p>Refunds are processed based on the plan type (monthly or annual) and the reason for the refund request. All refunds are subject to the terms outlined in the following sections.</p>
                    </div>

                    <div class="ps">
                        <span class="ps-anchor" id="r2"></span>
                        <div class="ps-header">
                            <div class="ps-num">16</div>
                            <h2 class="ps-title">Eligible Refunds</h2>
                        </div>
                        <p>You may be eligible for a full or partial refund in the following cases:</p>
                        <ul class="ps-list">
                            <li><i class="bi bi-check-lg"></i><span><strong>Monthly Plans:</strong> You are eligible for a full refund if you request it within <strong>7 days</strong> of your initial purchase. After 7 days, no refund will be issued for monthly plans.</span></li>
                            <li><i class="bi bi-check-lg"></i><span><strong>Annual Plans:</strong> You may receive a pro‑rated refund for the unused portion of your subscription if you cancel within the first 30 days. After 30 days, annual plans are non‑refundable except in cases of service failure.</span></li>
                            <li><i class="bi bi-check-lg"></i><span><strong>Service Outages:</strong> If EcomgstPro experiences a significant service outage that materially affects your ability to use the platform, you may be eligible for a partial refund or service credit.</span></li>
                            <li><i class="bi bi-check-lg"></i><span><strong>Duplicate Payments:</strong> If you are charged twice for the same subscription period, we will refund the duplicate transaction in full.</span></li>
                        </ul>
                        <p>All refund requests must be submitted via email to <strong>support@ecomgstpro.com</strong> with the subject "Refund Request".</p>
                    </div>

                    <div class="ps">
                        <span class="ps-anchor" id="r3"></span>
                        <div class="ps-header">
                            <div class="ps-num">17</div>
                            <h2 class="ps-title">Non‑Refundable Cases</h2>
                        </div>
                        <p>Refunds will not be provided in the following situations:</p>
                        <ul class="ps-list">
                            <li><i class="bi bi-x-lg"></i><span><strong>Change of Mind:</strong> If you decide you no longer need the service after the eligible refund window has passed.</span></li>
                            <li><i class="bi bi-x-lg"></i><span><strong>Failure to Use:</strong> If you do not use the platform during your subscription period, no refund will be given.</span></li>
                            <li><i class="bi bi-x-lg"></i><span><strong>Incorrect Data Provided:</strong> If you provide incorrect or incomplete data that results in filing errors, we are not liable and no refund will be processed.</span></li>
                            <li><i class="bi bi-x-lg"></i><span><strong>Third‑Party Issues:</strong> Delays or failures caused by the GST portal, your internet provider, or other external factors beyond our control.</span></li>
                            <li><i class="bi bi-x-lg"></i><span><strong>Plan Downgrades:</strong> Downgrading from a higher plan to a lower one does not trigger a refund for the difference; the remaining value is credited as account credit.</span></li>
                        </ul>
                    </div>

                    <div class="ps">
                        <span class="ps-anchor" id="r4"></span>
                        <div class="ps-header">
                            <div class="ps-num">18</div>
                            <h2 class="ps-title">Annual Plan Refunds</h2>
                        </div>
                        <p>Annual plans are billed on a yearly basis. If you cancel your annual subscription within the first 30 days, you will receive a <strong>pro‑rata refund</strong> for the unused months, minus any applicable fees. The pro‑rata calculation is based on the total annual fee divided by 12, multiplied by the number of unused months.</p>
                        <p>After 30 days, annual plans are generally non‑refundable. However, we may consider exceptions on a case‑by‑case basis if there is a proven service failure on our part.</p>
                        <div class="ps-highlight">
                            <p><strong>Pro‑rata refund example:</strong> If you paid ₹12,000 for an annual plan and cancel after 2 months, you would receive a refund of ₹10,000 (10 unused months × ₹1,000/month).</p>
                        </div>
                    </div>

                    <div class="ps">
                        <span class="ps-anchor" id="r5"></span>
                        <div class="ps-header">
                            <div class="ps-num">19</div>
                            <h2 class="ps-title">How to Request a Refund</h2>
                        </div>
                        <p>To request a refund, follow these steps:</p>
                        <ol style="padding-left:20px;font-size:15px;line-height:1.8;color:#374151;margin:16px 0;">
                            <li>Send an email to <strong>support@ecomgstpro.com</strong> with the subject line "Refund Request – [Your GSTIN or Email]".</li>
                            <li>Include your full name, registered email address, and the reason for your refund request.</li>
                            <li>If applicable, attach any supporting documents (e.g., duplicate payment receipts).</li>
                            <li>Our support team will review your request and respond within 2 business days.</li>
                            <li>If approved, the refund will be processed according to the timeline in Section 20.</li>
                        </ol>
                        <p>We reserve the right to request additional information to verify your identity and eligibility.</p>
                    </div>

                    <div class="ps">
                        <span class="ps-anchor" id="r6"></span>
                        <div class="ps-header">
                            <div class="ps-num">20</div>
                            <h2 class="ps-title">Refund Timeline</h2>
                        </div>
                        <p>Once your refund is approved, we process it as follows:</p>
                        <table class="ps-table">
                            <thead><tr><th>Refund Type</th><th>Processing Time</th><th>Credit to Source</th></tr></thead>
                            <tbody>
                                <tr><td>Monthly Plan Refund</td><td>Approved within 2 business days</td><td>5 – 7 business days</td></tr>
                                <tr><td>Annual Plan (Pro‑rata)</td><td>Approved within 3 business days</td><td>5 – 7 business days</td></tr>
                                <tr><td>Duplicate Payment</td><td>Approved within 1 business day</td><td>3 – 5 business days</td></tr>
                            </tbody>
                        </table>
                        <p><strong>Note:</strong> The actual time for the refund to appear in your account depends on your bank or payment gateway. EcomgstPro is not responsible for delays caused by third‑party payment processors.</p>
                    </div>

                    <div class="ps">
                        <span class="ps-anchor" id="r7"></span>
                        <div class="ps-header">
                            <div class="ps-num">21</div>
                            <h2 class="ps-title">Cancellation Policy</h2>
                        </div>
                        <p>You may cancel your EcomgstPro subscription at any time. Here's how cancellation works:</p>
                        <ul class="ps-list">
                            <li><i class="bi bi-calendar-x"></i><span><strong>Monthly Plans:</strong> Cancellation takes effect at the end of the current billing cycle. You will not be charged for the next month, and you retain access until the end of the paid period.</span></li>
                            <li><i class="bi bi-calendar-x"></i><span><strong>Annual Plans:</strong> Cancellation takes effect at the end of the current annual billing cycle. If you cancel within the first 30 days, you may receive a pro‑rata refund (see Section 18). After 30 days, no refund is provided, but your access continues until the end of the year.</span></li>
                            <li><i class="bi bi-calendar-x"></i><span><strong>How to Cancel:</strong> Log in to your dashboard, go to "Billing &amp; Plans", and click "Cancel Subscription". You will be asked to confirm. Alternatively, email support@ecomgstpro.com with your cancellation request.</span></li>
                        </ul>
                        <p>Upon cancellation, your data will remain available for 30 days after the subscription ends, after which it will be deleted unless you re‑subscribe or request an extension.</p>
                    </div>

                    <div class="ps">
                        <span class="ps-anchor" id="r8"></span>
                        <div class="ps-header">
                            <div class="ps-num">22</div>
                            <h2 class="ps-title">Plan Downgrades</h2>
                        </div>
                        <p>If you downgrade from a higher‑tier plan (e.g., Pro to Basic), the following applies:</p>
                        <ul class="ps-list">
                            <li><i class="bi bi-arrow-down-circle"></i><span><strong>Monthly:</strong> The new lower rate will apply from the next billing cycle. No refund is given for the remainder of the current month.</span></li>
                            <li><i class="bi bi-arrow-down-circle"></i><span><strong>Annual:</strong> The difference in price will be credited as account credit to your EcomgstPro wallet, which you can use toward future subscriptions or add‑ons. No cash refund is provided for downgrades.</span></li>
                        </ul>
                        <p>Downgrades may result in loss of access to features available only in higher plans. Please review the plan comparison before downgrading.</p>
                    </div>

                    <div class="ps">
                        <span class="ps-anchor" id="r9"></span>
                        <div class="ps-header">
                            <div class="ps-num">23</div>
                            <h2 class="ps-title">Failed Payments</h2>
                        </div>
                        <p>If a payment fails due to insufficient funds, expired card, or technical issues:</p>
                        <ul class="ps-list">
                            <li><i class="bi bi-exclamation-triangle-fill"></i><span>We will retry the payment up to 3 times over 5 days.</span></li>
                            <li><i class="bi bi-exclamation-triangle-fill"></i><span>If all attempts fail, your subscription will be paused, and you will receive email notifications with a link to update your payment method.</span></li>
                            <li><i class="bi bi-exclamation-triangle-fill"></i><span>Once payment is successfully processed, your subscription is reinstated.</span></li>
                            <li><i class="bi bi-exclamation-triangle-fill"></i><span>No late fees are charged for failed payments, but we reserve the right to suspend access until the outstanding amount is settled.</span></li>
                        </ul>
                        <p>If you believe a payment was wrongly declined, contact support@ecomgstpro.com for assistance.</p>
                    </div>

                </div>{{-- end refund section --}}

                {{-- CONTACT CARD --}}
                <div class="contact-card">
                    <h3>Have a question about privacy or refunds?</h3>
                    <p>Our team responds within 48 hours. For urgent issues, reach out via WhatsApp or phone.</p>
                    <div class="contact-links">
                        <a href="mailto:support@ecomgstpro.com" class="contact-link"><i class="bi bi-envelope-fill"></i> Email</a>
                        <a href="tel:+918076865836" class="contact-link"><i class="bi bi-telephone-fill"></i> Call</a>
                        <a href="https://wa.me/918076865836?text=Hi%2C%20I%20have%20a%20question%20about%20EcomgstPro%20policies" target="_blank" class="contact-link"><i class="bi bi-whatsapp"></i> WhatsApp</a>
                    </div>
                </div>

            </div>{{-- end policy-content --}}
        </div>{{-- end policy-layout --}}
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
    const anchors = document.querySelectorAll('.ps-anchor[id]');
    const tocLinks = document.querySelectorAll('.toc-list a');

    const obs = new IntersectionObserver(entries => {
        entries.forEach(e => {
            if(e.isIntersecting){
                const id = e.target.id;
                tocLinks.forEach(l => {
                    l.classList.toggle('active', l.getAttribute('href') === '#' + id);
                });
            }
        });
    }, {
        rootMargin: '-10% 0px -80% 0px',
        threshold: 0
    });

    anchors.forEach(a => obs.observe(a));
})();
</script>
@endsection
