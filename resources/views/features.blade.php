@extends('layouts.app')

{{-- ============================================================
     GST Pilot — features.blade.php
     Theme : Dark Navy #0a1628 + Teal #00c98d + Sky #00aaff
     Fonts : Syne (headings) + DM Sans (body)
     ============================================================ --}}

@section('content')

{{-- ─── ASSETS ─── --}}
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
.html,body{overflow-x:hidden}
:root{
    --navy:     #0a1628;
    --deep:     #0d1f3c;
    --mid:      #132240;
    --teal:     #00c98d;
    --sky:      #00aaff;
    --teal-low: rgba(0,201,141,.12);
    --sky-low:  rgba(0,170,255,.10);
    --txt:      #e8f0fe;
    --muted:    #8a9bbf;
    --border:   rgba(255,255,255,.08);
}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'DM Sans',sans-serif;background:#f0f4ff;color:var(--navy)}

.gp-section{padding:96px 0}
.gp-container{max-width:1200px;margin:0 auto;padding:0 32px}
.gp-eyebrow{
    display:inline-flex;align-items:center;gap:8px;
    font-size:11px;font-weight:700;letter-spacing:2.5px;
    text-transform:uppercase;color:var(--teal);margin-bottom:16px;
}
.gp-eyebrow::before{content:'';width:18px;height:2px;background:var(--teal);border-radius:2px}
.gp-h2{
    font-family:'Syne',sans-serif;font-weight:800;font-size:clamp(32px,4vw,48px);
    line-height:1.15;letter-spacing:-.5px;color:var(--navy);margin-bottom:16px;
}
.gp-h2 em{color:var(--teal);font-style:normal}
.gp-h2-white{color:#fff}
.gp-lead{font-size:17px;line-height:1.75;color:#4a5568;max-width:560px}
.gp-lead-white{color:var(--muted)}
.dot-grid{background-image:radial-gradient(rgba(255,255,255,.03) 1px,transparent 1px);background-size:26px 26px}

/* ═══ HERO ═══ */
.feat-hero{
    background:linear-gradient(135deg,var(--navy) 0%,var(--deep) 55%,#0a2240 100%);
    padding:140px 0 110px;position:relative;overflow:hidden;text-align:center;
}
.feat-hero::before{
    content:'';position:absolute;width:700px;height:700px;border-radius:50%;
    background:radial-gradient(circle,rgba(0,170,255,.08) 0%,transparent 70%);
    top:-220px;right:-160px;pointer-events:none;
}
.feat-hero::after{
    content:'';position:absolute;width:500px;height:500px;border-radius:50%;
    background:radial-gradient(circle,rgba(0,201,141,.1) 0%,transparent 70%);
    bottom:-180px;left:-120px;pointer-events:none;
}
.feat-hero-inner{position:relative;z-index:2;max-width:780px;margin:0 auto;padding:0 32px}
.hero-badge{
    display:inline-flex;align-items:center;gap:8px;
    background:rgba(0,201,141,.12);border:1px solid rgba(0,201,141,.25);
    border-radius:30px;padding:6px 16px;font-size:12px;font-weight:700;
    letter-spacing:1.5px;text-transform:uppercase;color:var(--teal);margin-bottom:24px;
}
.hero-badge span{width:6px;height:6px;border-radius:50%;background:var(--teal);animation:blink 1.8s infinite}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.3}}
.feat-hero-title{
    font-family:'Syne',sans-serif;font-weight:800;font-size:clamp(36px,5.5vw,62px);
    line-height:1.1;letter-spacing:-1.5px;color:#fff;margin-bottom:22px;
}
.feat-hero-title .teal{color:var(--teal)}
.feat-hero-sub{font-size:17px;line-height:1.75;color:var(--muted);max-width:600px;margin:0 auto 36px}
.hero-actions{display:flex;align-items:center;justify-content:center;gap:14px;flex-wrap:wrap}
.btn-primary-gp{
    font-family:'DM Sans',sans-serif;font-weight:700;font-size:15px;color:var(--navy);
    background:linear-gradient(135deg,var(--teal) 0%,#00e0a8 100%);
    border:none;border-radius:10px;padding:14px 28px;text-decoration:none;
    display:inline-flex;align-items:center;gap:8px;box-shadow:0 4px 22px rgba(0,201,141,.35);
    transition:all .2s;
}
.btn-primary-gp:hover{transform:translateY(-2px);box-shadow:0 8px 30px rgba(0,201,141,.5);color:var(--navy)}
.btn-ghost-gp{
    font-family:'DM Sans',sans-serif;font-weight:600;font-size:15px;color:var(--txt);
    background:transparent;border:1px solid rgba(255,255,255,.15);border-radius:10px;
    padding:13px 26px;text-decoration:none;display:inline-flex;align-items:center;gap:8px;transition:all .2s;
}
.btn-ghost-gp:hover{color:#fff;border-color:rgba(255,255,255,.35);background:rgba(255,255,255,.06)}

/* ═══ TICKER ═══ */
.ticker{background:var(--teal);padding:12px 0;overflow:hidden}
.ticker-track{display:flex;align-items:center;gap:48px;animation:ticker-scroll 22s linear infinite;width:max-content}
.ticker-item{
    display:flex;align-items:center;gap:10px;font-family:'Syne',sans-serif;font-weight:700;
    font-size:13px;letter-spacing:1px;text-transform:uppercase;color:var(--navy);white-space:nowrap;
}
.ticker-item::after{content:'✦';font-size:10px;opacity:.5;margin-left:24px}
@keyframes ticker-scroll{from{transform:translateX(0)}to{transform:translateX(-50%)}}

/* ═══ CORE FEATURES GRID ═══ */
.core-features{background:#fff}
.features-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px;margin-top:56px}
.feat-card{
    border:1px solid #e8edf5;border-radius:16px;padding:32px 28px;
    transition:all .25s;position:relative;overflow:hidden;background:#fff;
}
.feat-card::before{
    content:'';position:absolute;top:0;left:0;right:0;height:3px;
    background:linear-gradient(90deg,var(--teal),var(--sky));
    transform:scaleX(0);transform-origin:left;transition:transform .3s;
}
.feat-card:hover{border-color:rgba(0,201,141,.3);box-shadow:0 8px 40px rgba(0,201,141,.10);transform:translateY(-4px)}
.feat-card:hover::before{transform:scaleX(1)}
.feat-icon{
    width:52px;height:52px;border-radius:12px;background:var(--teal-low);
    border:1px solid rgba(0,201,141,.2);display:flex;align-items:center;justify-content:center;
    color:var(--teal);font-size:24px;margin-bottom:20px;
}
.feat-title{font-family:'Syne',sans-serif;font-weight:700;font-size:18px;color:var(--navy);margin-bottom:10px}
.feat-desc{font-size:14.5px;line-height:1.75;color:#4a5568}

/* ═══ DEEP-DIVE ALTERNATING SHOWCASE ═══ */
.deep-dive{background:#f7faff}
.dd-row{
    display:grid;grid-template-columns:1fr 1fr;gap:64px;align-items:center;
    padding:64px 0;border-bottom:1px solid #e3eaf6;
}
.dd-row:last-child{border-bottom:none}
.dd-row.reverse .dd-text{order:2}
.dd-row.reverse .dd-visual{order:1}
.dd-tag{
    display:inline-flex;align-items:center;gap:6px;background:var(--teal-low);
    border:1px solid rgba(0,201,141,.25);border-radius:30px;padding:5px 14px;
    font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;
    color:var(--teal);margin-bottom:18px;
}
.dd-title{font-family:'Syne',sans-serif;font-weight:800;font-size:clamp(24px,3vw,32px);color:var(--navy);margin-bottom:14px;line-height:1.25}
.dd-title em{color:var(--teal);font-style:normal}
.dd-desc{font-size:15.5px;line-height:1.8;color:#4a5568;margin-bottom:22px}
.dd-points{list-style:none;display:flex;flex-direction:column;gap:12px}
.dd-points li{display:flex;align-items:flex-start;gap:10px;font-size:14.5px;color:#374151}
.dd-points li i{color:var(--teal);font-size:17px;margin-top:1px;flex-shrink:0}
.dd-visual{display:flex;align-items:center;justify-content:center}
.dd-visual-card{
    width:100%;max-width:460px;background:linear-gradient(135deg,var(--navy) 0%,var(--deep) 100%);
    border-radius:24px;padding:36px;position:relative;overflow:hidden;
    border:1px solid var(--border);box-shadow:0 25px 60px -15px rgba(10,22,40,.35);
}
.dd-visual-card::before{
    content:'';position:absolute;top:-60px;right:-60px;width:200px;height:200px;border-radius:50%;
    background:radial-gradient(circle,rgba(0,201,141,.14) 0%,transparent 70%);
}

/* ═══ FEATURE MATRIX TABLE ═══ */
.feat-matrix{background:#fff}
.matrix-wrap{overflow-x:auto;margin-top:56px;border-radius:18px;border:1px solid #e8edf5}
table.matrix{width:100%;border-collapse:collapse;min-width:640px}
table.matrix th, table.matrix td{padding:18px 22px;text-align:left;font-size:14px}
table.matrix thead th{
    background:var(--deep);color:#fff;font-family:'Syne',sans-serif;font-weight:700;font-size:14px;
}
table.matrix thead th:not(:first-child){text-align:center}
table.matrix tbody tr:nth-child(even){background:#f7faff}
table.matrix tbody td{color:#374151;border-bottom:1px solid #eef2f8}
table.matrix tbody td:not(:first-child){text-align:center}
table.matrix tbody td:first-child{font-weight:600;color:var(--navy)}
table.matrix i.bi-check-circle-fill{color:var(--teal);font-size:17px}
table.matrix i.bi-dash-circle{color:#cbd5e1;font-size:17px}

/* ═══ AUTOMATION FLOW STRIP ═══ */
.flow-strip{background:linear-gradient(135deg,var(--navy) 0%,var(--deep) 100%)}
.flow-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:0;margin-top:56px;position:relative}
.flow-grid::before{
    content:'';position:absolute;top:40px;left:calc(12.5% + 24px);right:calc(12.5% + 24px);
    height:2px;background:linear-gradient(90deg,var(--teal),var(--sky),var(--teal));opacity:.3;z-index:0;
}
.flow-step{text-align:center;padding:0 20px;position:relative;z-index:1}
.flow-num{
    width:72px;height:72px;border-radius:50%;background:rgba(0,201,141,.10);
    border:2px solid rgba(0,201,141,.25);display:flex;align-items:center;justify-content:center;
    font-family:'Syne',sans-serif;font-weight:800;font-size:22px;color:var(--teal);
    margin:0 auto 20px;transition:all .3s;
}
.flow-step:hover .flow-num{background:var(--teal);color:var(--navy);box-shadow:0 0 24px rgba(0,201,141,.4)}
.flow-step-title{font-family:'Syne',sans-serif;font-weight:700;font-size:16px;color:#fff;margin-bottom:10px}
.flow-step-desc{font-size:13.5px;line-height:1.7;color:var(--muted)}

/* ═══ SECURITY / TRUST STRIP ═══ */
.trust-strip{background:#f7faff}
.trust-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:20px;margin-top:56px}
.trust-card{
    background:#fff;border:1px solid #e8edf5;border-radius:16px;padding:28px 22px;text-align:center;
    transition:all .25s;
}
.trust-card:hover{transform:translateY(-4px);border-color:rgba(0,201,141,.3);box-shadow:0 8px 30px rgba(0,201,141,.10)}
.trust-card-icon{
    width:56px;height:56px;border-radius:14px;background:var(--teal-low);margin:0 auto 16px;
    display:flex;align-items:center;justify-content:center;color:var(--teal);font-size:26px;
}
.trust-card-title{font-family:'Syne',sans-serif;font-weight:700;font-size:15px;color:var(--navy);margin-bottom:8px}
.trust-card-desc{font-size:13px;line-height:1.6;color:#5a6874}

/* ═══ STATS BAR ═══ */
.stats-bar{background:var(--deep);border-bottom:1px solid var(--border);padding:48px 0}
.stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:0}
.stat-item{text-align:center;padding:16px 24px;border-right:1px solid var(--border)}
.stat-item:last-child{border-right:none}
.stat-num{font-family:'Syne',sans-serif;font-weight:800;font-size:42px;color:#fff;line-height:1;margin-bottom:6px}
.stat-num em{color:var(--teal);font-style:normal}
.stat-lbl{font-size:14px;color:var(--muted)}

/* ═══ CTA ═══ */
.cta-fixed-height{
    background:linear-gradient(135deg,#0a1628 0%,#0d1f3c 50%,#0a2240 100%);
    position:relative;overflow:hidden;padding:90px 0;text-align:center;
}
.cta-fixed-height::before{
    content:'';position:absolute;top:-40%;right:-20%;width:500px;height:500px;border-radius:50%;
    background:radial-gradient(circle,rgba(0,201,141,.08) 0%,transparent 70%);pointer-events:none;
}
.cta-fixed-height::after{
    content:'';position:absolute;bottom:-30%;left:-15%;width:450px;height:450px;border-radius:50%;
    background:radial-gradient(circle,rgba(0,170,255,.06) 0%,transparent 70%);pointer-events:none;
}
.cta-content{position:relative;z-index:10;max-width:760px;margin:0 auto;padding:0 32px}
.cta-heading{
    font-family:'Syne',sans-serif;font-weight:800;font-size:clamp(30px,4.5vw,48px);
    line-height:1.15;letter-spacing:-1px;color:#fff;margin-bottom:18px;
}
.cta-heading em{color:var(--teal);font-style:normal}
.cta-subtitle{font-size:16px;line-height:1.7;color:var(--muted);max-width:540px;margin:0 auto 32px}
.cta-buttons{display:flex;align-items:center;justify-content:center;gap:16px;flex-wrap:wrap}

/* ═══ RESPONSIVE ═══ */
@media(max-width:1024px){
    .features-grid{grid-template-columns:1fr 1fr}
    .flow-grid{grid-template-columns:1fr 1fr;gap:32px}
    .flow-grid::before{display:none}
    .trust-grid{grid-template-columns:1fr 1fr}
    .stats-grid{grid-template-columns:1fr 1fr}
    .stat-item:nth-child(2){border-right:none}
    .dd-row{grid-template-columns:1fr;gap:36px}
    .dd-row.reverse .dd-text{order:1}
    .dd-row.reverse .dd-visual{order:2}
    .gp-h2{font-size:clamp(28px,4vw,36px)}
}
@media(max-width:768px){
    .feat-hero{padding:96px 0 72px}
    .hero-actions{flex-direction:column;gap:12px}
    .btn-primary-gp,.btn-ghost-gp{width:100%;justify-content:center}
    .features-grid{grid-template-columns:1fr}
    .flow-grid{grid-template-columns:1fr;gap:24px}
    .trust-grid{grid-template-columns:1fr}
    .stats-grid{grid-template-columns:1fr 1fr;gap:1px}
    .gp-section{padding:56px 0}
    .gp-container{padding:0 20px}
    .dd-row{padding:40px 0}
}
@media(max-width:640px){
    .feat-hero-title{font-size:clamp(26px,7vw,38px)}
    .stats-grid{grid-template-columns:1fr}
    .stat-item{border-right:none!important;padding:16px}
    .cta-buttons{flex-direction:column;width:100%}
    .btn-primary-gp,.btn-ghost-gp{width:100%}
}
</style>

{{-- ══════════════════════════════════════
     §1 HERO
══════════════════════════════════════ --}}
<section class="feat-hero dot-grid">
    <div class="feat-hero-inner">
        <div class="hero-badge"><span></span> Everything You Need, One Platform</div>
        <h1 class="feat-hero-title">
            Powerful <span class="teal">GST Features</span><br>
            Built to Save You Time
        </h1>
        <p class="feat-hero-sub">
            From reconciliation to filing, bank conversion to ITC recovery — explore every tool EcomgstPro
            gives you to make GST compliance fast, accurate, and stress-free.
        </p>
        <div class="hero-actions">
            <a href="{{ route('register') }}" class="btn-primary-gp">
                <i class="bi bi-person-plus-fill"></i> Try It Free
            </a>
            <a href="#deep-dive" class="btn-ghost-gp">
                <i class="bi bi-arrow-down-circle"></i> Explore Features
            </a>
        </div>
    </div>
</section>

{{-- TICKER --}}
<div class="ticker">
    <div class="ticker-track">
        @php $items = ['2A/2B Reconciliation','Bank Statement Converter','E-Commerce GSTR-1','ITC Optimiser','Purchase Register','GSTR Filing Suite','Multi-GSTIN Dashboard','API Access','Real-Time Analytics']; @endphp
        @foreach(array_merge($items,$items) as $item)
            <div class="ticker-item">{{ $item }}</div>
        @endforeach
    </div>
</div>

{{-- ══════════════════════════════════════
     §2 CORE FEATURES GRID
══════════════════════════════════════ --}}
<section class="core-features gp-section">
    <div class="gp-container">
        <div class="text-center">
            <div class="gp-eyebrow" style="justify-content:center">Core Capabilities</div>
            <h2 class="gp-h2" style="margin:0 auto 12px;text-align:center">Everything in <em>one platform</em></h2>
            <p class="gp-lead" style="margin:0 auto;text-align:center">A complete toolkit covering reconciliation, filing, banking, and analytics — built specifically for Indian GST compliance.</p>
        </div>

        <div class="features-grid">
            @php
            $coreFeats = [
                ['bi-arrow-left-right','2A / 2B Reconciliation','Auto-match your purchase register against GSTR-2A & 2B, flag mismatches and missing invoices instantly.'],
                ['bi-bank','Bank Statement Converter','Convert PDF/CSV bank statements from 690+ banks into clean, GST-categorised Tally-ready entries.'],
                ['bi-shop-window','E-Commerce GSTR-1 Suite','Generate GSTR-1 directly from Amazon, Flipkart, Meesho & 10+ marketplace reports, with TCS handling.'],
                ['bi-graph-up','ITC Optimiser','AI scans your data to surface unclaimed input tax credit before the claim window closes.'],
                ['bi-bar-chart-steps','GSTR Filing Suite','End-to-end GSTR-1, 3B, 9 & 9C preparation with built-in validation before you file.'],
                ['bi-diagram-3','Multi-GSTIN Dashboard','Manage every GSTIN across your business group from a single unified view.'],
            ];
            @endphp
            @foreach($coreFeats as [$icon,$title,$desc])
            <div class="feat-card">
                <div class="feat-icon"><i class="bi bi-{{ $icon }}"></i></div>
                <div class="feat-title">{{ $title }}</div>
                <p class="feat-desc">{{ $desc }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ══════════════════════════════════════
     §3 DEEP-DIVE ALTERNATING SHOWCASE
══════════════════════════════════════ --}}
<section class="deep-dive gp-section" id="deep-dive">
    <div class="gp-container">

        <div class="text-center" style="margin-bottom:24px">
            <div class="gp-eyebrow" style="justify-content:center">Feature Deep Dive</div>
            <h2 class="gp-h2" style="margin:0 auto 12px;text-align:center">See it <em>in action</em></h2>
        </div>

        {{-- Row 1: Reconciliation --}}
        <div class="dd-row">
            <div class="dd-text">
                <div class="dd-tag"><i class="bi bi-stars"></i> Smart Matching</div>
                <h3 class="dd-title">AI-Powered <em>2A/2B Reconciliation</em></h3>
                <p class="dd-desc">Upload your purchase register once and let EcomgstPro match every invoice against GSTR-2A and 2B automatically — no spreadsheets, no manual cross-checking.</p>
                <ul class="dd-points">
                    <li><i class="bi bi-check-circle-fill"></i> Match 10,000+ invoices in under a minute</li>
                    <li><i class="bi bi-check-circle-fill"></i> Auto-flag mismatches, excess claims & reversals</li>
                    <li><i class="bi bi-check-circle-fill"></i> Download audit-ready mismatch reports</li>
                </ul>
            </div>
            <div class="dd-visual">
                <div class="dd-visual-card">
                    <svg viewBox="0 0 400 260" xmlns="http://www.w3.org/2000/svg" width="100%" height="auto">
                        <rect x="20" y="20" width="160" height="220" rx="14" fill="rgba(255,255,255,.04)" stroke="rgba(255,255,255,.08)"/>
                        <rect x="220" y="20" width="160" height="220" rx="14" fill="rgba(255,255,255,.04)" stroke="rgba(255,255,255,.08)"/>
                        <text x="100" y="44" fill="#8a9bbf" font-size="11" font-family="DM Sans" text-anchor="middle">PURCHASE REGISTER</text>
                        <text x="300" y="44" fill="#8a9bbf" font-size="11" font-family="DM Sans" text-anchor="middle">GSTR-2B</text>
                        <g font-family="DM Sans" font-size="10" fill="#e8f0fe">
                            <rect x="32" y="58" width="136" height="22" rx="6" fill="rgba(0,201,141,.12)"/>
                            <text x="42" y="73">INV-1042 ₹18,200</text>
                            <rect x="32" y="86" width="136" height="22" rx="6" fill="rgba(0,201,141,.12)"/>
                            <text x="42" y="101">INV-1043 ₹9,450</text>
                            <rect x="32" y="114" width="136" height="22" rx="6" fill="rgba(239,68,68,.15)"/>
                            <text x="42" y="129">INV-1044 ₹4,200</text>
                            <rect x="32" y="142" width="136" height="22" rx="6" fill="rgba(0,201,141,.12)"/>
                            <text x="42" y="157">INV-1045 ₹22,800</text>
                        </g>
                        <g font-family="DM Sans" font-size="10" fill="#e8f0fe">
                            <rect x="232" y="58" width="136" height="22" rx="6" fill="rgba(0,201,141,.12)"/>
                            <text x="242" y="73">INV-1042 ₹18,200</text>
                            <rect x="232" y="86" width="136" height="22" rx="6" fill="rgba(0,201,141,.12)"/>
                            <text x="242" y="101">INV-1043 ₹9,450</text>
                            <rect x="232" y="114" width="136" height="22" rx="6" fill="rgba(255,255,255,.05)"/>
                            <text x="242" y="129" opacity=".4">— missing —</text>
                            <rect x="232" y="142" width="136" height="22" rx="6" fill="rgba(0,201,141,.12)"/>
                            <text x="242" y="157">INV-1045 ₹22,800</text>
                        </g>
                        <line x1="180" y1="69" x2="220" y2="69" stroke="#00c98d" stroke-width="1.5" stroke-dasharray="3 2"/>
                        <line x1="180" y1="97" x2="220" y2="97" stroke="#00c98d" stroke-width="1.5" stroke-dasharray="3 2"/>
                        <line x1="180" y1="153" x2="220" y2="153" stroke="#00c98d" stroke-width="1.5" stroke-dasharray="3 2"/>
                        <circle cx="200" cy="125" r="16" fill="#ef4444"/>
                        <path d="M194 125h12M200 119v12" stroke="#fff" stroke-width="2" stroke-linecap="round" transform="rotate(45 200 125)"/>
                        <rect x="32" y="178" width="336" height="44" rx="10" fill="rgba(0,201,141,.08)" stroke="rgba(0,201,141,.2)"/>
                        <text x="50" y="204" fill="#00c98d" font-size="12" font-family="Syne" font-weight="700">1 mismatch found</text>
                        <text x="50" y="216" fill="#8a9bbf" font-size="9" font-family="DM Sans">INV-1044 missing in GSTR-2B — ITC at risk</text>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Row 2: Bank Converter --}}
        <div class="dd-row reverse">
            <div class="dd-text">
                <div class="dd-tag"><i class="bi bi-bank2"></i> Banking</div>
                <h3 class="dd-title">Bank Statements → <em>Clean Ledgers</em></h3>
                <p class="dd-desc">Drop in a PDF, CSV, or Excel statement from any of 690+ supported banks and get back a structured, GST-categorised ledger — ready for Tally or your accountant.</p>
                <ul class="dd-points">
                    <li><i class="bi bi-check-circle-fill"></i> Works with scanned & digital PDF statements</li>
                    <li><i class="bi bi-check-circle-fill"></i> Auto-categorises credits, debits & GST components</li>
                    <li><i class="bi bi-check-circle-fill"></i> Export straight to Tally XML or Excel</li>
                </ul>
            </div>
            <div class="dd-visual">
                <div class="dd-visual-card">
                    <svg viewBox="0 0 400 260" xmlns="http://www.w3.org/2000/svg" width="100%" height="auto">
                        <rect x="30" y="30" width="120" height="150" rx="10" fill="rgba(255,255,255,.05)" stroke="rgba(255,255,255,.1)"/>
                        <rect x="44" y="44" width="92" height="8" rx="4" fill="rgba(255,255,255,.2)"/>
                        <rect x="44" y="60" width="70" height="6" rx="3" fill="rgba(255,255,255,.12)"/>
                        <rect x="44" y="78" width="92" height="1" fill="rgba(255,255,255,.1)"/>
                        <rect x="44" y="90" width="60" height="6" rx="3" fill="rgba(255,255,255,.15)"/>
                        <rect x="44" y="102" width="40" height="6" rx="3" fill="rgba(255,255,255,.1)"/>
                        <rect x="44" y="120" width="60" height="6" rx="3" fill="rgba(255,255,255,.15)"/>
                        <rect x="44" y="132" width="40" height="6" rx="3" fill="rgba(255,255,255,.1)"/>
                        <rect x="44" y="150" width="60" height="6" rx="3" fill="rgba(255,255,255,.15)"/>
                        <text x="90" y="20" fill="#8a9bbf" font-size="10" font-family="DM Sans" text-anchor="middle">BANK PDF</text>
                        <path d="M160 100 h60" stroke="#00c98d" stroke-width="2" stroke-dasharray="4 3" marker-end="url(#arrow)"/>
                        <defs><marker id="arrow" markerWidth="8" markerHeight="8" refX="4" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 Z" fill="#00c98d"/></marker></defs>
                        <circle cx="200" cy="100" r="22" fill="rgba(0,201,141,.15)" stroke="#00c98d" stroke-width="1.5"/>
                        <text x="200" y="105" text-anchor="middle" fill="#00c98d" font-size="16" font-family="Syne" font-weight="800">AI</text>
                        <path d="M240 100 h60" stroke="#00c98d" stroke-width="2" stroke-dasharray="4 3" marker-end="url(#arrow)"/>
                        <rect x="260" y="30" width="120" height="150" rx="10" fill="rgba(0,201,141,.08)" stroke="rgba(0,201,141,.2)"/>
                        <text x="320" y="20" fill="#00c98d" font-size="10" font-family="DM Sans" text-anchor="middle">CLEAN LEDGER</text>
                        <g font-family="DM Sans" font-size="9" fill="#e8f0fe">
                            <rect x="272" y="44" width="96" height="18" rx="4" fill="rgba(0,201,141,.12)"/>
                            <text x="280" y="56">Credit ₹22,800</text>
                            <rect x="272" y="66" width="96" height="18" rx="4" fill="rgba(239,68,68,.1)"/>
                            <text x="280" y="78">Debit ₹4,500</text>
                            <rect x="272" y="88" width="96" height="18" rx="4" fill="rgba(0,201,141,.12)"/>
                            <text x="280" y="100">Credit ₹18,200</text>
                            <rect x="272" y="110" width="96" height="18" rx="4" fill="rgba(239,68,68,.1)"/>
                            <text x="280" y="122">Debit ₹2,100</text>
                            <rect x="272" y="132" width="96" height="18" rx="4" fill="rgba(0,201,141,.12)"/>
                            <text x="280" y="144">Credit ₹9,450</text>
                        </g>
                        <rect x="30" y="200" width="350" height="38" rx="10" fill="rgba(0,170,255,.08)" stroke="rgba(0,170,255,.2)"/>
                        <text x="50" y="223" fill="#00aaff" font-size="11" font-family="Syne" font-weight="700">690+ banks supported</text>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Row 3: ITC Optimiser --}}
        <div class="dd-row">
            <div class="dd-text">
                <div class="dd-tag"><i class="bi bi-currency-rupee"></i> Recovery</div>
                <h3 class="dd-title">Recover Every Rupee with <em>ITC Optimiser</em></h3>
                <p class="dd-desc">EcomgstPro scans your filings and purchase data to find input tax credit you may have missed, then shows you exactly how to claim it within the allowed window.</p>
                <ul class="dd-points">
                    <li><i class="bi bi-check-circle-fill"></i> Detects unclaimed ITC across past periods</li>
                    <li><i class="bi bi-check-circle-fill"></i> Tracks the claim deadline for every invoice</li>
                    <li><i class="bi bi-check-circle-fill"></i> Average recovery of ₹50,000+ per GSTIN/year</li>
                </ul>
            </div>
            <div class="dd-visual">
                <div class="dd-visual-card">
                    <svg viewBox="0 0 400 260" xmlns="http://www.w3.org/2000/svg" width="100%" height="auto">
                        <text x="200" y="30" text-anchor="middle" fill="#8a9bbf" font-size="11" font-family="DM Sans">ITC RECOVERY TREND</text>
                        <line x1="40" y1="210" x2="370" y2="210" stroke="rgba(255,255,255,.12)"/>
                        <line x1="40" y1="50" x2="40" y2="210" stroke="rgba(255,255,255,.12)"/>
                        <rect x="60" y="170" width="36" height="40" rx="4" fill="rgba(0,201,141,.25)"/>
                        <rect x="120" y="150" width="36" height="60" rx="4" fill="rgba(0,201,141,.35)"/>
                        <rect x="180" y="120" width="36" height="90" rx="4" fill="rgba(0,201,141,.5)"/>
                        <rect x="240" y="90" width="36" height="120" rx="4" fill="rgba(0,201,141,.7)"/>
                        <rect x="300" y="60" width="36" height="150" rx="4" fill="#00c98d"/>
                        <path d="M78 168 L138 148 L198 118 L258 88 L318 58" stroke="#00aaff" stroke-width="2" fill="none" stroke-linecap="round"/>
                        <circle cx="318" cy="58" r="5" fill="#00aaff"/>
                        <text x="318" y="44" text-anchor="middle" fill="#00aaff" font-size="11" font-family="Syne" font-weight="700">₹50K+</text>
                        <rect x="40" y="226" width="330" height="24" rx="8" fill="rgba(0,201,141,.1)"/>
                        <text x="55" y="242" fill="#00c98d" font-size="10" font-family="DM Sans" font-weight="600">Unclaimed ITC found: ₹54,280 this quarter</text>
                    </svg>
                </div>
            </div>
        </div>

    </div>
</section>

{{-- ══════════════════════════════════════
     §4 FEATURE MATRIX TABLE
══════════════════════════════════════ --}}
<section class="feat-matrix gp-section">
    <div class="gp-container">
        <div class="text-center">
            <div class="gp-eyebrow" style="justify-content:center">Compare Plans</div>
            <h2 class="gp-h2" style="margin:0 auto 12px;text-align:center">Find the <em>right fit</em> for your business</h2>
            <p class="gp-lead" style="margin:0 auto;text-align:center">Every plan includes our core engine. Higher tiers unlock automation depth, scale, and dedicated support.</p>
        </div>

        <div class="matrix-wrap">
            <table class="matrix">
                <thead>
                    <tr>
                        <th>Feature</th>
                        <th>Starter</th>
                        <th>Professional</th>
                        <th>Enterprise</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>GSTR-1 & 3B Filing</td><td><i class="bi bi-check-circle-fill"></i></td><td><i class="bi bi-check-circle-fill"></i></td><td><i class="bi bi-check-circle-fill"></i></td></tr>
                    <tr><td>Bank Statement Converter</td><td><i class="bi bi-dash-circle"></i></td><td><i class="bi bi-check-circle-fill"></i></td><td><i class="bi bi-check-circle-fill"></i></td></tr>
                    <tr><td>2A / 2B Reconciliation</td><td><i class="bi bi-dash-circle"></i></td><td><i class="bi bi-check-circle-fill"></i></td><td><i class="bi bi-check-circle-fill"></i></td></tr>
                    <tr><td>E-Commerce GSTR-1 Suite</td><td><i class="bi bi-dash-circle"></i></td><td><i class="bi bi-check-circle-fill"></i></td><td><i class="bi bi-check-circle-fill"></i></td></tr>
                    <tr><td>ITC Optimiser</td><td><i class="bi bi-dash-circle"></i></td><td><i class="bi bi-check-circle-fill"></i></td><td><i class="bi bi-check-circle-fill"></i></td></tr>
                    <tr><td>Multi-GSTIN Dashboard</td><td><i class="bi bi-dash-circle"></i></td><td>Up to 5</td><td>Unlimited</td></tr>
                    <tr><td>API Access</td><td><i class="bi bi-dash-circle"></i></td><td><i class="bi bi-dash-circle"></i></td><td><i class="bi bi-check-circle-fill"></i></td></tr>
                    <tr><td>White-Label Option</td><td><i class="bi bi-dash-circle"></i></td><td><i class="bi bi-dash-circle"></i></td><td><i class="bi bi-check-circle-fill"></i></td></tr>
                    <tr><td>Support</td><td>Email</td><td>Priority</td><td>Dedicated Manager</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

{{-- ══════════════════════════════════════
     §5 AUTOMATION FLOW STRIP
══════════════════════════════════════ --}}
<section class="flow-strip gp-section">
    <div class="gp-container">
        <div class="text-center">
            <div class="gp-eyebrow" style="justify-content:center;color:var(--teal)">How Features Connect</div>
            <h2 class="gp-h2 gp-h2-white" style="margin:0 auto 12px;text-align:center">From data to filing, <em>automatically</em></h2>
            <p class="gp-lead gp-lead-white" style="margin:0 auto;text-align:center">Every feature feeds into the next — no manual hand-offs, no re-keying data.</p>
        </div>

        <div class="flow-grid">
            <div class="flow-step">
                <div class="flow-num">01</div>
                <div class="flow-step-title">Connect & Upload</div>
                <div class="flow-step-desc">Link your GSTIN and upload bank statements, purchase registers, or marketplace reports.</div>
            </div>
            <div class="flow-step">
                <div class="flow-num">02</div>
                <div class="flow-step-title">Auto-Reconcile</div>
                <div class="flow-step-desc">2A/2B matching and ITC scanning run instantly across every invoice.</div>
            </div>
            <div class="flow-step">
                <div class="flow-num">03</div>
                <div class="flow-step-title">Review & Validate</div>
                <div class="flow-step-desc">Built-in error checks flag issues before they ever reach the GST portal.</div>
            </div>
            <div class="flow-step">
                <div class="flow-num">04</div>
                <div class="flow-step-title">File in One Click</div>
                <div class="flow-step-desc">Push validated, pre-filled returns straight to the GST portal.</div>
            </div>
        </div>
    </div>
</section>

{{-- ══════════════════════════════════════
     §6 SECURITY & TRUST STRIP
══════════════════════════════════════ --}}
<section class="trust-strip gp-section">
    <div class="gp-container">
        <div class="text-center">
            <div class="gp-eyebrow" style="justify-content:center">Built On Trust</div>
            <h2 class="gp-h2" style="margin:0 auto 12px;text-align:center">Security & reliability <em>by default</em></h2>
        </div>

        <div class="trust-grid">
            @php
            $trustFeats = [
                ['bi-shield-check-fill','GSP Certified','Direct, secure API access to the GST portal — no third-party scraping or manual uploads.'],
                ['bi-lock-fill','Bank-Grade Encryption','Data encrypted at rest and in transit, with SOC 2 Type II controls.'],
                ['bi-award-fill','ISO 27001 Certified','Independently audited information security management practices.'],
                ['bi-clock-history','99.9% Uptime','Monitored infrastructure built to be available exactly when filing deadlines hit.'],
            ];
            @endphp
            @foreach($trustFeats as [$icon,$title,$desc])
            <div class="trust-card">
                <div class="trust-card-icon"><i class="bi bi-{{ $icon }}"></i></div>
                <div class="trust-card-title">{{ $title }}</div>
                <p class="trust-card-desc">{{ $desc }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ══════════════════════════════════════
     §7 STATS BAR
══════════════════════════════════════ --}}
<section class="stats-bar dot-grid">
    <div class="gp-container">
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-num">10<em>K+</em></div>
                <div class="stat-lbl">Businesses Trust EcomgstPro</div>
            </div>
            <div class="stat-item">
                <div class="stat-num">₹50<em>Cr+</em></div>
                <div class="stat-lbl">ITC Recovered for Clients</div>
            </div>
            <div class="stat-item">
                <div class="stat-num">98<em>%</em></div>
                <div class="stat-lbl">Filing Accuracy Rate</div>
            </div>
            <div class="stat-item">
                <div class="stat-num">690<em>+</em></div>
                <div class="stat-lbl">Banks Supported</div>
            </div>
        </div>
    </div>
</section>

{{-- ══════════════════════════════════════
     §8 CTA
══════════════════════════════════════ --}}
<section class="cta-fixed-height">
    <div class="cta-content">
        <h2 class="cta-heading">Ready to put these <em>features</em> to work?</h2>
        <p class="cta-subtitle">Start free, connect your GSTIN, and see EcomgstPro's automation handle your next filing cycle in minutes.</p>
        <div class="cta-buttons">
            <a href="{{ route('register') }}" class="btn-primary-gp">
                <i class="bi bi-person-plus-fill"></i> Create Free Account
            </a>
            <a href="{{ route('login') }}" class="btn-ghost-gp">
                <i class="bi bi-box-arrow-in-right"></i> Sign In
            </a>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

@endsection
