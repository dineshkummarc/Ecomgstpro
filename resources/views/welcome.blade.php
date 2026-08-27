@extends('layouts.app')

{{-- ============================================================
     EcomgstPro — home.blade.php
     Theme : Dark Navy #0a1628 + Teal #00c98d + Sky #00aaff
     Fonts : Syne (headings) + DM Sans (body)
     Layout: @include header → sections → @include footer
     ============================================================ --}}

@section('content')

{{-- ─── ASSETS ─── --}}
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
/* ═══════════════════════════════════════════
   GLOBAL TOKENS
═══════════════════════════════════════════ */

.html,body{overflow-x:hidden;
}
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

/* shared helpers */
.gp-section{padding:96px 0}
.gp-container{max-width:1200px;margin:0 auto;padding:0 32px}
.gp-eyebrow{
    display:inline-flex;align-items:center;gap:8px;
    font-size:11px;font-weight:700;letter-spacing:2.5px;
    text-transform:uppercase;color:var(--teal);
    margin-bottom:16px;
}
.gp-eyebrow::before{
    content:'';width:18px;height:2px;background:var(--teal);border-radius:2px;
}
.gp-h2{
    font-family:'Syne',sans-serif;font-weight:800;font-size:clamp(32px,4vw,48px);
    line-height:1.15;letter-spacing:-.5px;color:var(--navy);margin-bottom:16px;
}
.gp-h2 em{color:var(--teal);font-style:normal}
.gp-h2-white{color:#fff}
.gp-lead{font-size:17px;line-height:1.75;color:#4a5568;max-width:560px}
.gp-lead-white{color:var(--muted)}

/* dot-grid bg (reusable on dark sections) */
.dot-grid{
    background-image:radial-gradient(rgba(255,255,255,.03) 1px,transparent 1px);
    background-size:26px 26px;
}

/* ═══════════════════════════════════════════
   1. HERO BANNER
═══════════════════════════════════════════ */
.hero{
    background:linear-gradient(135deg,var(--navy) 0%,var(--deep) 55%,#0a2240 100%);
    min-height:92vh;
    display:flex;align-items:center;
    position:relative;overflow:hidden;
}

/* radial glows */
.hero::before{
    content:'';position:absolute;
    width:700px;height:700px;border-radius:50%;
    background:radial-gradient(circle,rgba(0,170,255,.07) 0%,transparent 70%);
    top:-120px;right:-160px;pointer-events:none;
}
.hero::after{
    content:'';position:absolute;
    width:500px;height:500px;border-radius:50%;
    background:radial-gradient(circle,rgba(0,201,141,.08) 0%,transparent 70%);
    bottom:-80px;left:-100px;pointer-events:none;
}

.hero-inner{
    position:relative;z-index:2;
    max-width:1200px;margin:0 auto;padding:0 32px;
    display:grid;grid-template-columns:1fr 1fr;gap:64px;align-items:center;
}

/* left: text */
.hero-badge{
    display:inline-flex;align-items:center;gap:8px;
    background:rgba(0,201,141,.12);border:1px solid rgba(0,201,141,.25);
    border-radius:30px;padding:6px 16px;
    font-size:12px;font-weight:700;letter-spacing:1.5px;
    text-transform:uppercase;color:var(--teal);margin-bottom:24px;
}
.hero-badge span{
    width:6px;height:6px;border-radius:50%;background:var(--teal);
    animation:blink 1.8s infinite;
}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.3}}

.hero-title{
    font-family:'Syne',sans-serif;font-weight:800;
    font-size:clamp(38px,5vw,64px);line-height:1.08;
    letter-spacing:-1.5px;color:#fff;margin-bottom:24px;
}
.hero-title .accent{
    color:transparent;
    -webkit-text-stroke:1.5px var(--teal);
    display:block;
}
.hero-title .teal{color:var(--teal)}

.hero-sub{
    font-size:17px;line-height:1.75;color:var(--muted);
    max-width:500px;margin-bottom:36px;
}

.hero-actions{display:flex;align-items:center;gap:14px;flex-wrap:wrap;margin-bottom:48px}

.btn-primary-gp{
    font-family:'DM Sans',sans-serif;font-weight:700;font-size:15px;
    color:var(--navy);
    background:linear-gradient(135deg,var(--teal) 0%,#00e0a8 100%);
    border:none;border-radius:10px;padding:14px 28px;
    text-decoration:none;display:inline-flex;align-items:center;gap:8px;
    box-shadow:0 4px 22px rgba(0,201,141,.35);
    transition:all .2s;
}
.btn-primary-gp:hover{
    transform:translateY(-2px);
    box-shadow:0 8px 30px rgba(0,201,141,.5);color:var(--navy);
}

.btn-ghost-gp{
    font-family:'DM Sans',sans-serif;font-weight:600;font-size:15px;
    color:var(--txt);
    background:transparent;
    border:1px solid rgba(255,255,255,.15);
    border-radius:10px;padding:13px 26px;
    text-decoration:none;display:inline-flex;align-items:center;gap:8px;
    transition:all .2s;
}
.btn-ghost-gp:hover{
    color:#fff;border-color:rgba(255,255,255,.35);
    background:rgba(255,255,255,.06);
}

/* trust chips */
.hero-trust{display:flex;align-items:center;gap:24px;flex-wrap:wrap}
.trust-chip{
    display:flex;align-items:center;gap:8px;
    font-size:13px;color:var(--muted);
}
.trust-chip i{color:var(--teal);font-size:15px}
.trust-chip strong{color:#fff;font-weight:600}

/* ═══════════════════════════════════════════
   2. MARQUEE TICKER
═══════════════════════════════════════════ */
.ticker{
    background:var(--teal);padding:12px 0;overflow:hidden;
}
.ticker-track{
    display:flex;align-items:center;gap:48px;
    animation:ticker-scroll 22s linear infinite;
    width:max-content;
}
.ticker-item{
    display:flex;align-items:center;gap:10px;
    font-family:'Syne',sans-serif;font-weight:700;font-size:13px;
    letter-spacing:1px;text-transform:uppercase;color:var(--navy);
    white-space:nowrap;
}
.ticker-item::after{
    content:'✦';font-size:10px;opacity:.5;margin-left:24px;
}
@keyframes ticker-scroll{from{transform:translateX(0)}to{transform:translateX(-50%)}}

/* ═══════════════════════════════════════════
   3. STATS BAR
═══════════════════════════════════════════ */
.stats-bar{
    background:var(--deep);border-bottom:1px solid var(--border);
    padding:48px 0;
}
.stats-grid{
    display:grid;grid-template-columns:repeat(4,1fr);
    gap:0;
}
.stat-item{
    text-align:center;padding:16px 24px;
    border-right:1px solid var(--border);
}
.stat-item:last-child{border-right:none}
.stat-num{
    font-family:'Syne',sans-serif;font-weight:800;font-size:42px;
    color:#fff;line-height:1;margin-bottom:6px;
}
.stat-num em{color:var(--teal);font-style:normal}
.stat-lbl{font-size:14px;color:var(--muted)}

/* ═══════════════════════════════════════════
   4. FEATURES
═══════════════════════════════════════════ */
.features{background:#fff}

.features-grid{
    display:grid;grid-template-columns:repeat(3,1fr);gap:24px;
    margin-top:56px;
}
.feat-card{
    border:1px solid #e8edf5;border-radius:16px;padding:32px 28px;
    transition:all .25s;position:relative;overflow:hidden;
}
.feat-card::before{
    content:'';position:absolute;top:0;left:0;right:0;height:3px;
    background:linear-gradient(90deg,var(--teal),var(--sky));
    transform:scaleX(0);transform-origin:left;
    transition:transform .3s;
}
.feat-card:hover{
    border-color:rgba(0,201,141,.3);
    box-shadow:0 8px 40px rgba(0,201,141,.10);
    transform:translateY(-4px);
}
.feat-card:hover::before{transform:scaleX(1)}

.feat-icon{
    width:52px;height:52px;border-radius:12px;
    background:var(--teal-low);border:1px solid rgba(0,201,141,.2);
    display:flex;align-items:center;justify-content:center;
    color:var(--teal);font-size:24px;margin-bottom:20px;
}
.feat-title{
    font-family:'Syne',sans-serif;font-weight:700;font-size:18px;
    color:var(--navy);margin-bottom:10px;
}
.feat-desc{font-size:14.5px;line-height:1.75;color:#4a5568}

/* ═══════════════════════════════════════════
   5. HOW IT WORKS
═══════════════════════════════════════════ */
.how{
    background:linear-gradient(135deg,var(--navy) 0%,var(--deep) 100%);
}
.how-grid{
    display:grid;grid-template-columns:repeat(4,1fr);gap:0;
    margin-top:56px;position:relative;
}
/* connector line */
.how-grid::before{
    content:'';position:absolute;
    top:40px;left:calc(12.5% + 24px);right:calc(12.5% + 24px);
    height:2px;
    background:linear-gradient(90deg,var(--teal),var(--sky),var(--teal));
    opacity:.3;z-index:0;
}
.how-step{
    text-align:center;padding:0 20px;position:relative;z-index:1;
}
.how-num{
    width:72px;height:72px;border-radius:50%;
    background:rgba(0,201,141,.10);border:2px solid rgba(0,201,141,.25);
    display:flex;align-items:center;justify-content:center;
    font-family:'Syne',sans-serif;font-weight:800;font-size:22px;
    color:var(--teal);margin:0 auto 20px;
    transition:all .3s;
}
.how-step:hover .how-num{
    background:var(--teal);color:var(--navy);
    box-shadow:0 0 24px rgba(0,201,141,.4);
}
.how-step-title{
    font-family:'Syne',sans-serif;font-weight:700;font-size:16px;
    color:#fff;margin-bottom:10px;
}
.how-step-desc{font-size:13.5px;line-height:1.7;color:var(--muted)}

/* ═══════════════════════════════════════════
   6. WHY CHOOSE US (split)
═══════════════════════════════════════════ */
.why{background:#f7faff}
.why-inner{
    display:grid;grid-template-columns:1fr 1fr;gap:72px;align-items:center;
}
.why-list{display:flex;flex-direction:column;gap:20px;margin-top:32px}
.why-item{
    display:flex;gap:16px;padding:20px;border-radius:12px;
    border:1px solid #e8edf5;background:#fff;
    transition:all .2s;
}
.why-item:hover{border-color:rgba(0,201,141,.3);box-shadow:0 4px 20px rgba(0,201,141,.08)}
.why-item-icon{
    width:44px;height:44px;border-radius:10px;flex-shrink:0;
    background:var(--teal-low);border:1px solid rgba(0,201,141,.2);
    display:flex;align-items:center;justify-content:center;
    color:var(--teal);font-size:20px;
}
.why-item-title{font-family:'Syne',sans-serif;font-weight:700;font-size:15px;color:var(--navy);margin-bottom:4px}
.why-item-desc{font-size:13.5px;color:#4a5568;line-height:1.65}

/* right side: dark card */
.why-right-card{
    background:var(--deep);border-radius:24px;padding:48px 40px;
    border:1px solid var(--border);position:relative;overflow:hidden;
}
.why-right-card::before{
    content:'';position:absolute;top:-60px;right:-60px;
    width:200px;height:200px;border-radius:50%;
    background:radial-gradient(circle,rgba(0,201,141,.12) 0%,transparent 70%);
}
.why-metric{margin-bottom:28px}
.why-metric-num{
    font-family:'Syne',sans-serif;font-weight:800;font-size:48px;
    color:#fff;line-height:1;
}
.why-metric-num em{color:var(--teal);font-style:normal}
.why-metric-lbl{font-size:14px;color:var(--muted);margin-top:4px}
.why-metric-bar{
    height:4px;border-radius:2px;
    background:rgba(255,255,255,.08);margin-top:10px;
}
.why-metric-fill{height:100%;border-radius:2px;background:linear-gradient(90deg,var(--teal),var(--sky))}

/* ═══════════════════════════════════════════
   7. PRODUCTS / USE-CASES
═══════════════════════════════════════════ */
.products{background:#fff}
.products-grid{
    display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-top:56px;
}
.prod-card{
    background:var(--deep);border-radius:16px;padding:28px 24px;
    border:1px solid var(--border);cursor:pointer;
    transition:all .25s;
}
.prod-card:hover{
    transform:translateY(-6px);
    border-color:rgba(0,201,141,.35);
    box-shadow:0 12px 40px rgba(0,0,0,.3);
}
.prod-card-icon{
    width:48px;height:48px;border-radius:12px;
    background:var(--teal-low);border:1px solid rgba(0,201,141,.2);
    display:flex;align-items:center;justify-content:center;
    color:var(--teal);font-size:22px;margin-bottom:18px;
}
.prod-card-title{font-family:'Syne',sans-serif;font-weight:700;font-size:17px;color:#fff;margin-bottom:8px}
.prod-card-desc{font-size:13.5px;line-height:1.7;color:var(--muted)}
.prod-card-link{
    display:inline-flex;align-items:center;gap:6px;
    font-size:13px;font-weight:600;color:var(--teal);
    text-decoration:none;margin-top:16px;
    transition:gap .2s;
}
.prod-card-link:hover{gap:10px;color:var(--teal)}

/* ═══════════════════════════════════════════
   8. PRICING TEASER
═══════════════════════════════════════════ */
.pricing{background:#f7faff}
.pricing-grid{
    display:grid;grid-template-columns:repeat(3,1fr);gap:24px;margin-top:56px;
}
.price-card{
    background:#fff;border-radius:18px;padding:36px 28px;
    border:1.5px solid #e8edf5;
    transition:all .25s;position:relative;
}
.price-card.popular{
    border-color:var(--teal);
    box-shadow:0 8px 40px rgba(0,201,141,.15);
}
.popular-badge{
    position:absolute;top:-14px;left:50%;transform:translateX(-50%);
    background:linear-gradient(135deg,var(--teal),var(--sky));
    color:var(--navy);font-size:11px;font-weight:800;
    letter-spacing:1px;text-transform:uppercase;
    padding:5px 18px;border-radius:20px;white-space:nowrap;
}
.price-card:hover{
    transform:translateY(-4px);
    box-shadow:0 12px 40px rgba(0,0,0,.10);
}
.price-name{font-family:'Syne',sans-serif;font-weight:700;font-size:18px;color:var(--navy);margin-bottom:8px}
.price-amt{font-family:'Syne',sans-serif;font-weight:800;font-size:44px;color:var(--navy);line-height:1}
.price-amt sup{font-size:20px;vertical-align:top;margin-top:8px;display:inline-block}
.price-per{font-size:13px;color:#4a5568}
.price-divider{height:1px;background:#e8edf5;margin:24px 0}
.price-feats{list-style:none;display:flex;flex-direction:column;gap:10px}
.price-feats li{display:flex;align-items:center;gap:10px;font-size:14px;color:#4a5568}
.price-feats li i{color:var(--teal);font-size:15px;flex-shrink:0}
.btn-price{
    display:block;text-align:center;margin-top:28px;
    font-family:'DM Sans',sans-serif;font-weight:700;font-size:14.5px;
    border-radius:10px;padding:13px;text-decoration:none;
    transition:all .2s;
}
.btn-price-outline{
    border:1.5px solid var(--teal);color:var(--teal);
    background:transparent;
}
.btn-price-outline:hover{background:var(--teal-low);color:var(--teal)}
.btn-price-solid{
    background:linear-gradient(135deg,var(--teal),#00e0a8);
    color:var(--navy);border:none;
    box-shadow:0 4px 18px rgba(0,201,141,.3);
}
.btn-price-solid:hover{transform:translateY(-1px);box-shadow:0 8px 28px rgba(0,201,141,.45);color:var(--navy)}

/* ═══════════════════════════════════════════
   9. CTA BANNER
═══════════════════════════════════════════ */
.cta-section{
    background:linear-gradient(135deg,var(--navy) 0%,#0d2545 100%);
    padding:100px 0;position:relative;overflow:hidden;
}
.cta-section::before{
    content:'';position:absolute;
    width:600px;height:600px;border-radius:50%;
    background:radial-gradient(circle,rgba(0,201,141,.08) 0%,transparent 65%);
    top:-200px;right:-100px;pointer-events:none;
}
.cta-inner{
    text-align:center;position:relative;z-index:1;
}
.cta-title{
    font-family:'Syne',sans-serif;font-weight:800;
    font-size:clamp(32px,5vw,54px);letter-spacing:-1px;
    color:#fff;margin-bottom:16px;line-height:1.1;
}
.cta-title em{color:var(--teal);font-style:normal}
.cta-sub{font-size:17px;color:var(--muted);max-width:520px;margin:0 auto 40px}
.cta-actions{
    display:flex;align-items:center;justify-content:center;gap:14px;flex-wrap:wrap;
}

/* ═══════════════════════════════════════════
   ENHANCED RESPONSIVE - Mobile First Approach
═══════════════════════════════════════════ */

/* Large tablets and small desktops */
@media(max-width:1024px){
    .hero-inner{grid-template-columns:1fr;gap:48px;padding:64px 32px}
    .hero-right{max-width:480px;margin:0 auto}
    .features-grid{grid-template-columns:1fr 1fr}
    .how-grid{grid-template-columns:1fr 1fr;gap:32px}
    .how-grid::before{display:none}
    .why-inner{grid-template-columns:1fr}
    .products-grid{grid-template-columns:1fr 1fr}
    .pricing-grid{grid-template-columns:1fr}
    .stats-grid{grid-template-columns:1fr 1fr}
    .stat-item:nth-child(2){border-right:none}
    .gp-h2{font-size:clamp(28px,4vw,36px)}
    .gp-lead{font-size:15px}
}

/* Medium tablets */
@media(max-width:768px){
    .hero{min-height:80vh;padding:48px 0}
    .hero-inner{gap:36px;padding:48px 20px}
    .hero-title{font-size:clamp(28px,6vw,42px)}
    .hero-sub{font-size:15px}
    .hero-actions{flex-direction:column;gap:12px}
    .btn-primary-gp,.btn-ghost-gp{width:100%;justify-content:center}
    .hero-trust{justify-content:center}
    .stats-bar{padding:36px 0}
    .stats-grid{grid-template-columns:1fr 1fr;gap:1px}
    .stat-item{padding:12px 16px}
    .stat-num{font-size:32px}
    .features-grid{grid-template-columns:1fr}
    .how-grid{grid-template-columns:1fr;gap:24px}
    .products-grid{grid-template-columns:1fr}
    .why-inner{gap:48px}
    .why-list{margin-top:24px}
    .pricing-grid{grid-template-columns:1fr;gap:20px}
    .price-card{padding:28px 20px}
    .cta-section{padding:72px 0}
    .cta-title{font-size:clamp(28px,5vw,40px)}
    .cta-actions{flex-direction:column;gap:12px}
    .btn-primary-gp,.btn-ghost-gp{width:100%;justify-content:center}
    .gp-section{padding:56px 0}
    .gp-container{padding:0 20px}
}

/* Small phones */
@media(max-width:640px){
    .hero{min-height:70vh}
    .hero-inner{padding:40px 16px 36px;gap:32px}
    .hero-title{font-size:clamp(24px,7vw,36px);line-height:1.15}
    .hero-sub{font-size:14px;line-height:1.6}
    .hero-badge{font-size:11px;padding:5px 12px}
    .hero-actions{gap:10px}
    .btn-primary-gp,.btn-ghost-gp{padding:12px 20px;font-size:14px}
    .trust-chip{font-size:12px;gap:6px}
    .hero-right{grid-template-columns:1fr 1fr;gap:12px}
    .hero-card{padding:16px 14px}
    .hero-card-num{font-size:24px}
    .ticker{padding:10px 0}
    .ticker-item{font-size:12px}
    .stats-grid{grid-template-columns:1fr}
    .stat-item{border-right:none!important;padding:16px}
    .stat-num{font-size:36px}
    .features-grid{grid-template-columns:1fr}
    .feat-card{padding:24px 20px}
    .feat-icon{width:44px;height:44px;font-size:20px}
    .feat-title{font-size:16px}
    .feat-desc{font-size:13px}
    .how-grid{grid-template-columns:1fr}
    .how-num{width:56px;height:56px;font-size:18px}
    .how-step-title{font-size:15px}
    .how-step-desc{font-size:12px}
    .why-item{padding:16px;gap:12px}
    .why-item-icon{width:36px;height:36px;font-size:18px}
    .why-item-title{font-size:14px}
    .why-item-desc{font-size:12px}
    .why-right-card{padding:32px 24px}
    .why-metric-num{font-size:36px}
    .products-grid{grid-template-columns:1fr;gap:16px}
    .prod-card{padding:24px 20px}
    .prod-card-icon{width:40px;height:40px;font-size:18px}
    .prod-card-title{font-size:15px}
    .prod-card-desc{font-size:12px}
    .pricing-grid{grid-template-columns:1fr}
    .price-card{padding:28px 20px}
    .price-amt{font-size:36px}
    .cta-section{padding:60px 0}
    .cta-title{font-size:clamp(24px,6vw,36px)}
    .cta-sub{font-size:14px}
    .btn-primary-gp,.btn-ghost-gp{padding:12px 20px;font-size:14px}
    .gp-section{padding:48px 0}
    .gp-container{padding:0 16px}
    .gp-h2{font-size:clamp(24px,5vw,32px);margin-bottom:12px}
    .gp-lead{font-size:14px;line-height:1.6}
    .gp-eyebrow{font-size:10px;margin-bottom:12px}
}

/* Extra small phones */
@media(max-width:480px){
    .hero{min-height:65vh}
    .hero-inner{padding:32px 12px 28px}
    .hero-title{font-size:clamp(22px,8vw,30px)}
    .hero-sub{max-width:100%}
    .hero-right{grid-template-columns:1fr}
    .hero-card.span2{grid-column:span 1}
    .ticker-item::after{display:none}
    .stat-num{font-size:30px}
    .stat-lbl{font-size:12px}
    .feat-card{padding:20px 16px}
    .how-num{width:48px;height:48px;font-size:16px}
    .why-right-card{padding:24px 20px}
    .why-metric-num{font-size:30px}
    .price-amt{font-size:32px}
    .cta-content{padding:0 16px}
    .cta-heading{font-size:clamp(22px,7vw,28px)}
    .cta-subtitle{font-size:13px}
}

/* Ensure touch-friendly targets */
@media(hover:none) and (pointer:coarse){
    .btn-primary-gp,.btn-ghost-gp{min-height:44px}
    .feat-card,.prod-card,.price-card{min-height:120px}
    a,button{min-height:44px}
}

/* Landscape orientation adjustments */
@media(max-height:500px) and (orientation:landscape){
    .hero{min-height:auto;padding:32px 0}
    .hero-inner{gap:24px}
    .hero-title{font-size:clamp(24px,4vw,36px)}
    .gp-section{padding:40px 0}
}
</style>




{{-- ══════════════════════════════════════
     §1  HERO BANNER (WITH SOLUTIONS GRID)
══════════════════════════════════════ --}}
<section class="hero dot-grid">
    <div class="hero-inner">

        {{-- LEFT: copy --}}
        <div>
            <div class="hero-badge">
                <span></span> #1 GST Automation Platform in India
            </div>

            <h1 class="hero-title">
                File GST.<br>
                <span class="teal">Zero Errors.</span><br>
                <span class="accent">Maximum Speed.</span>
            </h1>

            <div class="hero-actions">
                <a href="{{ route('register') }}" class="btn-primary-gp">
                    <i class="bi bi-person-plus-fill"></i> Sign Up Free
                </a>
                <a href="{{ route('login') }}" class="btn-ghost-gp">
                    <i class="bi bi-box-arrow-in-right"></i> Sign In
                </a>
            </div>

            <div class="hero-trust">
                <div class="trust-chip">
                    <i class="bi bi-shield-check-fill"></i>
                    <span>GSP Certified</span>
                </div>
                <div class="trust-chip">
                    <i class="bi bi-people-fill"></i>
                    <span><strong>10,000+</strong> Businesses</span>
                </div>
                <div class="trust-chip">
                    <i class="bi bi-award-fill"></i>
                    <span>ISO 27001</span>
                </div>
            </div>
        </div>

        {{-- RIGHT: Our Solutions (matching color theme from image) --}}
        
        
        
        {{-- RIGHT: Our Solutions (matching color theme from image) --}}
<div class="hero-solutions">
    <div class="solutions-header">
        <h3 class="solutions-title">Our Solutions</h3>
        <p class="solutions-subtitle">Choose the automation that fits your business needs</p>
    </div>

    <div class="solutions-grid solutions-grid--single">
        <a href="{{ route('gstr.clients.index') }}" class="solution-card solution-card--featured text-decoration-none">
            <div class="solution-badge">Most Popular</div>

            <div class="solution-icon">
                <i class="bi bi-shop"></i>
            </div>

            <div class="solution-content">
                <h4 class="solution-name">E-Commerce Seller GSTR - 1</h4>
                <p class="solution-desc">
                    Powerful platform to file GSTR-1 of E-Commerce Seller, For CA & Tax Professionals
                </p>
            </div>

            <div class="solution-cta">
                <span>Explore Now</span>
                <i class="bi bi-arrow-right"></i>
            </div>
        </a>
    </div>
</div>

<style>
    /* ── Single-card "Our Solutions" override ── */
.solutions-grid--single {
    display: flex;
    justify-content: center;
}

.solution-card--featured {
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 0;
    width: 100%;
    max-width: 380px;
    padding: 2rem 1.75rem 1.75rem;
    background: linear-gradient(160deg, #ffffff 0%, #f0fdfa 100%);
    border: 1.5px solid rgba(20, 184, 166, 0.25);
    border-radius: 20px;
    position: relative;
    box-shadow: 0 10px 30px -8px rgba(15, 118, 110, 0.15);
}

.solution-card--featured:hover {
    transform: translateY(-6px);
    box-shadow: 0 20px 40px -10px rgba(15, 118, 110, 0.28);
}

.solution-card--featured .solution-badge {
    position: absolute;
    top: -13px;
    left: 50%;
    transform: translateX(-50%);
    background: linear-gradient(135deg, #14b8a6, #0f766e);
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    padding: 5px 16px;
    border-radius: 20px;
    white-space: nowrap;
    box-shadow: 0 4px 12px rgba(15, 118, 110, 0.35);
}

.solution-card--featured .solution-icon {
    width: 72px;
    height: 72px;
    margin-bottom: 1.25rem;
    border-radius: 18px;
}

.solution-card--featured .solution-icon i {
    font-size: 2.1rem;
}

.solution-card--featured .solution-content {
    margin-bottom: 1.25rem;
}

.solution-card--featured .solution-name {
    font-size: 1.15rem;
    margin-bottom: 0.6rem;
}

.solution-card--featured .solution-desc {
    font-size: 0.875rem;
    line-height: 1.55;
}

.solution-card--featured .solution-cta {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(135deg, #0f766e, #14b8a6);
    color: #fff;
    font-weight: 700;
    font-size: 0.85rem;
    padding: 10px 22px;
    border-radius: 30px;
    transition: gap 0.25s ease, box-shadow 0.25s ease;
    box-shadow: 0 4px 14px rgba(15, 118, 110, 0.3);
}

.solution-card--featured:hover .solution-cta {
    gap: 12px;
    box-shadow: 0 6px 18px rgba(15, 118, 110, 0.4);
}

@media (max-width: 640px) {
    .solution-card--featured {
        max-width: 100%;
        padding: 1.75rem 1.25rem 1.5rem;
    }
}
</style>
        
        
        
        <!--<div class="hero-solutions">-->
        <!--    <div class="solutions-header">-->
        <!--        <h3 class="solutions-title">Our Solutions</h3>-->
        <!--        <p class="solutions-subtitle">Choose the automation that fits your business needs</p>-->
        <!--    </div>-->

        <!--    <div class="solutions-grid">-->
                {{-- PDF Bank Statement Converter --}}
                <!--<div class="solution-card">-->
                <!--    <div class="solution-icon">-->
                <!--        <i class="bi bi-file-pdf-fill"></i>-->
                <!--    </div>-->
                <!--    <div class="solution-content">-->
                <!--        <h4 class="solution-name">PDF Bank Statement Converter</h4>-->
                <!--        <p class="solution-desc">Automate Bank entry by converting bank statement PDF into Tally XML</p>-->
                <!--    </div>-->
                <!--</div>-->

                {{-- E-Commerce Seller GSTR - 1 --}}
<!--                <a href="{{ route('gstr.clients.index') }}" class="solution-card text-decoration-none">-->
<!--    <div class="solution-icon">-->
<!--        <i class="bi bi-shop"></i>-->
<!--    </div>-->

<!--    <div class="solution-content">-->
<!--        <h4 class="solution-name">E-Commerce Seller GSTR - 1</h4>-->
<!--        <p class="solution-desc">-->
<!--            Powerful platform to file GSTR-1 of E-Commerce Seller, For CA & Tax Professionals-->
<!--        </p>-->
<!--    </div>-->
<!--</a>-->
            </div>
        </div>
    </div>
</section>

{{-- CSS Styles (to be placed in your Laravel CSS file) --}}
<style>
/* Hero Section Base */
.hero {
    padding: 5rem 2rem;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    position: relative;
    overflow: hidden;
}

.dot-grid {
    background-image: radial-gradient(#cbd5e1 1px, transparent 1px);
    background-size: 24px 24px;
}

.hero-inner {
    max-width: 1280px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 3rem;
    align-items: start;
}

/* Left side styles */
.hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: #e6f7f5;
    padding: 0.5rem 1rem;
    border-radius: 100px;
    color: #0f766e;
    font-size: 0.875rem;
    font-weight: 500;
    margin-bottom: 1.5rem;
}

.hero-badge span {
    width: 8px;
    height: 8px;
    background: #14b8a6;
    border-radius: 50%;
    display: inline-block;
}

.hero-title {
    font-size: 3.5rem;
    font-weight: 800;
    line-height: 1.2;
    margin-bottom: 1.5rem;
    color: #0f172a;
}

.hero-title .teal {
    color: #0f766e;
    background: linear-gradient(135deg, #0f766e, #14b8a6);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
}

.hero-title .accent {
    color: #ea580c;
    background: linear-gradient(135deg, #ea580c, #f97316);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
}

.hero-actions {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
}

.btn-primary-gp {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: linear-gradient(135deg, #0f766e, #14b8a6);
    color: white;
    padding: 0.875rem 1.75rem;
    border-radius: 12px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(15, 118, 110, 0.3);
}

.btn-primary-gp:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(15, 118, 110, 0.4);
    background: linear-gradient(135deg, #0d6b64, #12a697);
}

.btn-ghost-gp {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: white;
    color: #0f766e;
    padding: 0.875rem 1.75rem;
    border-radius: 12px;
    font-weight: 600;
    text-decoration: none;
    border: 1px solid #cbd5e1;
    transition: all 0.3s ease;
}

.btn-ghost-gp:hover {
    border-color: #14b8a6;
    background: #f0fdfa;
    transform: translateY(-2px);
}

.hero-trust {
    display: flex;
    gap: 1.5rem;
    flex-wrap: wrap;
}

.trust-chip {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: white;
    padding: 0.5rem 1rem;
    border-radius: 100px;
    font-size: 0.875rem;
    color: #334155;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}

.trust-chip i {
    color: #14b8a6;
    font-size: 1rem;
}

/* Right side - Solutions Section */
.hero-solutions {
    background: white;
    border-radius: 24px;
    padding: 1.75rem;
    box-shadow: 0 20px 35px -12px rgba(0,0,0,0.1);
    border: 1px solid rgba(20, 184, 166, 0.2);
    transition: all 0.3s ease;
}

.hero-solutions:hover {
    transform: translateY(-4px);
    box-shadow: 0 25px 40px -12px rgba(15, 118, 110, 0.2);
}

.solutions-header {
    text-align: center;
    margin-bottom: 1.75rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #e2e8f0;
}

.solutions-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 0.5rem;
    background: linear-gradient(135deg, #0f766e, #14b8a6);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
}

.solutions-subtitle {
    font-size: 0.875rem;
    color: #64748b;
    margin: 0;
}

.solutions-grid {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.solution-card {
    display: flex;
    gap: 1rem;
    padding: 1.25rem;
    background: linear-gradient(135deg, #ffffff, #f8fafc);
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    transition: all 0.3s ease;
    cursor: pointer;
}

.solution-card:hover {
    border-color: #14b8a6;
    box-shadow: 0 4px 12px rgba(20, 184, 166, 0.15);
    transform: translateX(4px);
}

.solution-icon {
    flex-shrink: 0;
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, #e6f7f5, #ccfbf1);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.solution-card:hover .solution-icon {
    background: linear-gradient(135deg, #14b8a6, #0f766e);
    transform: scale(1.05);
}

.solution-icon i {
    font-size: 1.75rem;
    color: #0f766e;
    transition: all 0.3s ease;
}

.solution-card:hover .solution-icon i {
    color: white;
}

.solution-content {
    flex: 1;
}

.solution-name {
    font-size: 1rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 0.5rem;
    line-height: 1.3;
}

.solution-desc {
    font-size: 0.8125rem;
    color: #475569;
    line-height: 1.4;
    margin: 0;
}

/* Responsive */
@media (max-width: 1024px) {
    .hero-inner {
        grid-template-columns: 1fr;
        gap: 2rem;
    }

    .hero-title {
        font-size: 2.5rem;
    }

    .hero-solutions {
        max-width: 600px;
        margin: 0 auto;
    }
}

@media (max-width: 640px) {
    .hero {
        padding: 3rem 1rem;
    }

    .hero-title {
        font-size: 1.875rem;
    }

    .hero-actions {
        flex-direction: column;
    }

    .btn-primary-gp,
    .btn-ghost-gp {
        justify-content: center;
    }

    .hero-trust {
        justify-content: center;
    }

    .solution-card {
        flex-direction: column;
        text-align: center;
    }

    .solution-icon {
        margin: 0 auto;
    }
}
</style>
{{-- ══════════════════════════════════════
     TICKER
══════════════════════════════════════ --}}
{{-- ═══════════════════════════════════════════════════════════════════
     POWERED BY FINTAXPRO — Branding Marquee Strip
     Purpose: Subtle, attractive scrolling credit bar linking to FinTaxPro
     Style: Dark navy strip, slow seamless scroll, pauses on hover
══════════════════════════════════════════════════════════════════ --}}

<style>
.fintax-strip {
    background: linear-gradient(135deg, #0a1628 0%, #0d1f3c 100%);
    padding: 14px 0;
    overflow: hidden;
    position: relative;
    border-top: 1px solid rgba(0,201,141,.15);
    border-bottom: 1px solid rgba(0,201,141,.15);
}

/* edge fade masks */
.fintax-strip::before,
.fintax-strip::after {
    content: '';
    position: absolute;
    top: 0; width: 90px; height: 100%;
    z-index: 2; pointer-events: none;
}
.fintax-strip::before { left: 0;  background: linear-gradient(to right, #0a1628, transparent); }
.fintax-strip::after  { right: 0; background: linear-gradient(to left, #0a1628, transparent); }

.fintax-track-wrap {
    display: flex;
    width: max-content;
    animation: fintaxScroll 38s linear infinite;
}
.fintax-strip:hover .fintax-track-wrap { animation-play-state: paused; }

@keyframes fintaxScroll {
    from { transform: translateX(0); }
    to   { transform: translateX(-50%); }
}

.fintax-item {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
    background: rgba(255,255,255,.04);
    border: 1px solid rgba(0,201,141,.18);
    border-radius: 40px;
    padding: 8px 22px 8px 10px;
    margin: 0 14px;
    white-space: nowrap;
    transition: all .25s ease;
}
.fintax-item:hover {
    background: rgba(0,201,141,.10);
    border-color: var(--teal, #00c98d);
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(0,201,141,.25);
}

.fintax-logo {
    width: 34px; height: 34px;
    border-radius: 50%;
    background: #fff;
    display: flex; align-items: center; justify-content: center;
    overflow: hidden;
    flex-shrink: 0;
    box-shadow: 0 0 0 2px rgba(0,201,141,.3);
}
.fintax-logo img {
    width: 100%; height: 100%; object-fit: contain;
}

.fintax-text {
    display: flex;
    flex-direction: column;
    line-height: 1.25;
}
.fintax-text small {
    font-family: 'DM Sans', sans-serif;
    font-size: 10px;
    font-weight: 600;
    letter-spacing: .5px;
    text-transform: uppercase;
    color: #8a9bbf;
}
.fintax-text strong {
    font-family: 'Syne', sans-serif;
    font-size: 14px;
    font-weight: 700;
    color: #fff;
}
.fintax-text strong em {
    color: #00c98d;
    font-style: normal;
}

.fintax-item i.bi-box-arrow-up-right {
    font-size: 11px;
    color: #00c98d;
    margin-left: 4px;
    opacity: .7;
}

@media (max-width: 640px) {
    .fintax-strip { padding: 10px 0; }
    .fintax-item { padding: 6px 16px 6px 8px; margin: 0 8px; }
    .fintax-logo { width: 28px; height: 28px; }
    .fintax-text strong { font-size: 12px; }
    .fintax-text small { font-size: 9px; }
}
</style>

<section class="fintax-strip">
    <div class="fintax-track-wrap">
        @php
            $fintaxItems = str_repeat('x', 8); // 8 repeats for a long seamless loop
        @endphp
        @for ($i = 0; $i < 8; $i++)
        <a href="https://fintaxpro.in" target="_blank" rel="noopener" class="fintax-item">
            <span class="fintax-logo">
                <img src="{{ asset('images/fintaxlogo.jpg') }}" alt="FinTaxPro">
            </span>
            <span class="fintax-text">
                <small>Powered &amp; Trusted by</small>
                <strong>Fin<em>Tax</em>Pro — Finance Taxation Accounts</strong>
            </span>
            <i class="bi bi-box-arrow-up-right"></i>
        </a>
        @endfor
    </div>
</section>


{{-- ══════════════════════════════════════
     §2  STATS BAR
══════════════════════════════════════ --}}
{{-- ═══════════════════════════════════════════════════════════════════
     POWERED BY FINTAXPRO — Attractive Textual Branding Banner
     Height: 300px | Fully clickable | Logo + Founder credit
══════════════════════════════════════════════════════════════════ --}}

<style>
.fintax-banner {
    height: 300px;
    background: linear-gradient(135deg, #0a1628 0%, #0d1f3c 55%, #0a2240 100%);
    border-radius: 28px;
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    text-decoration: none;
    border: 1px solid rgba(0,201,141,.18);
    transition: all .35s ease;
}

.fintax-banner:hover {
    transform: translateY(-4px);
    border-color: rgba(0,201,141,.4);
    box-shadow: 0 25px 50px -12px rgba(0,201,141,.25);
}

/* glow orbs */
.fintax-banner::before {
    content: '';
    position: absolute;
    width: 320px; height: 320px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(0,201,141,.12) 0%, transparent 70%);
    top: -120px; right: -80px;
    pointer-events: none;
}
.fintax-banner::after {
    content: '';
    position: absolute;
    width: 260px; height: 260px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(0,170,255,.10) 0%, transparent 70%);
    bottom: -100px; left: -60px;
    pointer-events: none;
}

.fintax-dot-grid {
    position: absolute;
    inset: 0;
    background-image: radial-gradient(rgba(255,255,255,.04) 1px, transparent 1px);
    background-size: 22px 22px;
    pointer-events: none;
}

.fintax-banner-inner {
    position: relative;
    z-index: 2;
    width: 100%;
    padding: 0 36px;
    display: flex;
    align-items: center;
    gap: 28px;
}

/* logo medallion */
.fintax-logo-medal {
    flex-shrink: 0;
    width: 92px;
    height: 92px;
    border-radius: 50%;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 10px;
    box-shadow: 0 0 0 3px rgba(0,201,141,.35), 0 10px 30px rgba(0,0,0,.3);
}

.fintax-logo-medal img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

/* text content */
.fintax-text-block { flex: 1; min-width: 0; }

.fintax-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(0,201,141,.12);
    border: 1px solid rgba(0,201,141,.25);
    border-radius: 30px;
    padding: 5px 16px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: #00c98d;
    margin-bottom: 12px;
}

.fintax-eyebrow span {
    width: 6px; height: 6px;
    border-radius: 50%;
    background: #00c98d;
    animation: fintaxBlink 1.8s infinite;
}
@keyframes fintaxBlink { 0%,100%{opacity:1} 50%{opacity:.3} }

.fintax-headline {
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    font-size: clamp(20px, 2.2vw, 28px);
    line-height: 1.25;
    color: #fff;
    margin-bottom: 10px;
}

.fintax-headline em {
    color: #00c98d;
    font-style: normal;
}

.fintax-sub {
    font-size: 13.5px;
    line-height: 1.65;
    color: #8a9bbf;
    max-width: 480px;
    margin-bottom: 16px;
}

.fintax-founder-row {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.fintax-founder-chip {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255,255,255,.05);
    border: 1px solid rgba(255,255,255,.1);
    border-radius: 30px;
    padding: 6px 14px;
    font-size: 12px;
    color: #e8f0fe;
}

.fintax-founder-chip i { color: #00c98d; font-size: 14px; }
.fintax-founder-chip strong { color: #fff; font-weight: 700; }

.fintax-arrow-cta {
    flex-shrink: 0;
    width: 52px;
    height: 52px;
    border-radius: 50%;
    background: linear-gradient(135deg, #00c98d, #00e0a8);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #0a1628;
    font-size: 20px;
    transition: all .3s ease;
}

.fintax-banner:hover .fintax-arrow-cta {
    transform: rotate(45deg) scale(1.08);
    box-shadow: 0 8px 25px rgba(0,201,141,.4);
}

/* Responsive */
@media (max-width: 768px) {
    .fintax-banner { height: auto; min-height: 260px; padding: 24px 0; border-radius: 22px; }
    .fintax-banner-inner { flex-direction: column; text-align: center; padding: 0 22px; gap: 16px; }
    .fintax-logo-medal { width: 72px; height: 72px; }
    .fintax-headline { font-size: 19px; }
    .fintax-sub { max-width: 100%; font-size: 12.5px; }
    .fintax-founder-row { justify-content: center; }
    .fintax-arrow-cta { display: none; }
}

@media (max-width: 480px) {
    .fintax-eyebrow { font-size: 10px; padding: 4px 12px; }
    .fintax-founder-chip { font-size: 11px; padding: 5px 12px; }
}
</style>

<a href="https://fintaxpro.in" target="_blank" rel="noopener" class="fintax-banner">
    <div class="fintax-dot-grid"></div>
    <div class="fintax-banner-inner">

        {{-- Logo --}}
        <div class="fintax-logo-medal">
            <img src="{{ asset('images/fintaxlogo.jpg') }}" alt="FinTaxPro Logo">
        </div>

        {{-- Text --}}
        <div class="fintax-text-block">
            <div class="fintax-eyebrow"><span></span> Powered &amp; Trusted By</div>
            <h3 class="fintax-headline">
                <em>FinTaxPro</em> — Finance, Taxation &amp; Accounts
            </h3>
            <p class="fintax-sub">
                EcomgstPro is proudly backed by FinTaxPro's deep expertise in GST, accounting and compliance — bringing you a platform built by tax professionals, for businesses that demand accuracy.
            </p>
            <div class="fintax-founder-row">
                <span class="fintax-founder-chip">
                    <i class="bi bi-patch-check-fill"></i>
                    Founded by <strong>CA Rajat Garg</strong>
                </span>
                <span class="fintax-founder-chip">
                    <i class="bi bi-mortarboard-fill"></i>
                    Chartered Accountant &amp; Tax Educator
                </span>
            </div>
        </div>

        {{-- Arrow CTA --}}
        <div class="fintax-arrow-cta">
            <i class="bi bi-arrow-up-right"></i>
        </div>

    </div>
</a>


{{-- ═══════════════════════════════════════════════════════════════════
     INFINITE SCROLLING MARQUEE — BANKS & E-COMMERCE PLATFORMS
     Purpose: Showcases the vast integration network with dual-direction,
     seamless infinite scroll animations.
     Styling: Clean WHITE background with modern card design
══════════════════════════════════════════════════════════════════ --}}

<style>
/* ─────────────────────────────────────────────────────────────────
   MARQUEE CONTAINERS (Wrapper for infinite scroll rows)
   WHITE BACKGROUND VERSION
   ───────────────────────────────────────────────────────────── */
.marquee-wrapper {
    width: 100%;
    overflow: hidden;
    position: relative;
    background: #ffffff;         /* Clean white background */
    padding: 3rem 0;
    border-top: 1px solid #eef2f8;
    border-bottom: 1px solid #eef2f8;
}

/* Gradient masks on edges for smooth fade-in/out effect */
.marquee-wrapper::before,
.marquee-wrapper::after {
    content: '';
    position: absolute;
    top: 0;
    width: 120px;
    height: 100%;
    z-index: 2;
    pointer-events: none;
}

.marquee-wrapper::before {
    left: 0;
    background: linear-gradient(to right, #ffffff, transparent);
}

.marquee-wrapper::after {
    right: 0;
    background: linear-gradient(to left, #ffffff, transparent);
}

/* Individual row containers */
.marquee-row {
    display: flex;
    align-items: center;
    white-space: nowrap;
    will-change: transform;
    margin-bottom: 2rem;
}

.marquee-row:last-child {
    margin-bottom: 0;
}

/* Track that holds the duplicate set for seamless looping */
.marquee-track {
    display: inline-flex;
    align-items: center;
    gap: 4rem;
    padding: 0 2rem;
}

/* Individual logo card style - WHITE BACKGROUND VERSION */
.marquee-item {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    background: #f8fafc;
    border: 1px solid #eef2f8;
    border-radius: 48px;
    padding: 0.7rem 1.8rem;
    transition: all 0.2s ease;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
}

.marquee-item:hover {
    border-color: var(--teal);
    background: rgba(0, 201, 141, 0.04);
    transform: scale(1.02);
    box-shadow: 0 4px 12px rgba(0, 201, 141, 0.08);
}

/* Logo icon styling */
.marquee-item i {
    font-size: 1.6rem;
    color: var(--teal);
}

.marquee-item .bank-icon {
    background: rgba(0, 201, 141, 0.08);
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.marquee-item .bank-icon i {
    font-size: 1.2rem;
    margin: 0;
}

/* Text styling - DARK for white background */
.marquee-item span {
    font-family: 'DM Sans', sans-serif;
    font-weight: 600;
    font-size: 1rem;
    color: #1a2a3a;
    letter-spacing: -0.2px;
}

/* Category badge (Bank/Ecom) */
.item-category {
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    background: #eef2f8;
    padding: 2px 8px;
    border-radius: 30px;
    color: #5a6874;
    margin-left: 6px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .marquee-wrapper {
        padding: 2rem 0;
    }
    .marquee-track {
        gap: 2rem;
    }
    .marquee-item {
        padding: 0.5rem 1.2rem;
    }
    .marquee-item span {
        font-size: 0.85rem;
    }
    .marquee-item i {
        font-size: 1.2rem;
    }
}

/* Animation keyframes - opposite directions for visual richness */
@keyframes scrollLeft {
    0% {
        transform: translateX(0);
    }
    100% {
        transform: translateX(-50%);
    }
}

@keyframes scrollRight {
    0% {
        transform: translateX(-50%);
    }
    100% {
        transform: translateX(0);
    }
}

/* Assign animations to the rows */
.marquee-row.left .marquee-track {
    animation: scrollLeft 28s linear infinite;
}

.marquee-row.right .marquee-track {
    animation: scrollRight 32s linear infinite;
}

/* Pause animation on hover for better UX */
.marquee-row:hover .marquee-track {
    animation-play-state: paused;
}
</style>

{{-- ================================================================
     SECTION: INTEGRATIONS (Banks & E-Commerce) - WHITE BACKGROUND
     ================================================================ --}}
<section class="marquee-wrapper">
    <div class="gp-container" style="margin-bottom: 2rem;">
        <div class="text-center">
            <div class="gp-eyebrow" style="justify-content: center; color: var(--teal);">Seamless Integrations</div>
            <h2 class="gp-h2" style="margin: 0 auto 12px; text-align: center; color: var(--navy);">
                Connect with <em>690+ Banks</em> & <br><em>10+ E-Commerce</em> Giants
            </h2>
            <p class="gp-lead" style="margin: 0 auto; text-align: center; max-width: 650px; color: #5a6874;">
                EcomgstPro plugs directly into your existing ecosystem. From leading banks to major marketplaces, unify all your financial data in one place.
            </p>
        </div>
    </div>

    {{-- ROW 1: BANKS (Scrolling Left) --}}
    <div class="marquee-row left">
        <div class="marquee-track">
            {{-- Banks Set --}}
            <div class="marquee-item"><i class="bi bi-bank2"></i><span>DHANLAXMI BANK</span><small class="item-category">Bank</small></div>
            <div class="marquee-item"><i class="bi bi-building"></i><span>NAINITAL BANK</span><small class="item-category">Bank</small></div>
            <div class="marquee-item"><i class="bi bi-bank"></i><span>ESFB</span><small class="item-category">Bank</small></div>
            <div class="marquee-item"><i class="bi bi-credit-card"></i><span>HSBC</span><small class="item-category">Bank</small></div>
            <div class="marquee-item"><i class="bi bi-building"></i><span>DBS</span><small class="item-category">Bank</small></div>
            <div class="marquee-item"><i class="bi bi-bank2"></i><span>DEUTSCHE BANK</span><small class="item-category">Bank</small></div>
            <div class="marquee-item"><i class="bi bi-credit-card"></i><span>BARCLAYS</span><small class="item-category">Bank</small></div>
            <div class="marquee-item"><i class="bi bi-bank"></i><span>SCB</span><small class="item-category">Bank</small></div>
            <div class="marquee-item"><i class="bi bi-building"></i><span>HDFC BANK</span><small class="item-category">Bank</small></div>
            <div class="marquee-item"><i class="bi bi-bank2"></i><span>ICICI BANK</span><small class="item-category">Bank</small></div>
            <div class="marquee-item"><i class="bi bi-building"></i><span>SBI</span><small class="item-category">Bank</small></div>
            <div class="marquee-item"><i class="bi bi-credit-card"></i><span>AXIS BANK</span><small class="item-category">Bank</small></div>
            <div class="marquee-item"><i class="bi bi-bank"></i><span>KOTAK BANK</span><small class="item-category">Bank</small></div>
            <div class="marquee-item"><i class="bi bi-building"></i><span>YES BANK</span><small class="item-category">Bank</small></div>
            <div class="marquee-item"><i class="bi bi-bank2"></i><span>RBL BANK</span><small class="item-category">Bank</small></div>
            <div class="marquee-item"><i class="bi bi-credit-card"></i><span>INDUSIND BANK</span><small class="item-category">Bank</small></div>
            <div class="marquee-item"><i class="bi bi-building"></i><span>FEDERAL BANK</span><small class="item-category">Bank</small></div>
            <div class="marquee-item"><i class="bi bi-bank"></i><span>IDFC FIRST BANK</span><small class="item-category">Bank</small></div>
            <div class="marquee-item"><i class="bi bi-credit-card"></i><span>PNB</span><small class="item-category">Bank</small></div>
            <div class="marquee-item"><i class="bi bi-bank2"></i><span>BOB</span><small class="item-category">Bank</small></div>
            {{-- Duplicate set for seamless infinite loop --}}
            <div class="marquee-item"><i class="bi bi-bank2"></i><span>DHANLAXMI BANK</span><small class="item-category">Bank</small></div>
            <div class="marquee-item"><i class="bi bi-building"></i><span>NAINITAL BANK</span><small class="item-category">Bank</small></div>
            <div class="marquee-item"><i class="bi bi-bank"></i><span>ESFB</span><small class="item-category">Bank</small></div>
            <div class="marquee-item"><i class="bi bi-credit-card"></i><span>HSBC</span><small class="item-category">Bank</small></div>
            <div class="marquee-item"><i class="bi bi-building"></i><span>DBS</span><small class="item-category">Bank</small></div>
            <div class="marquee-item"><i class="bi bi-bank2"></i><span>DEUTSCHE BANK</span><small class="item-category">Bank</small></div>
            <div class="marquee-item"><i class="bi bi-credit-card"></i><span>BARCLAYS</span><small class="item-category">Bank</small></div>
            <div class="marquee-item"><i class="bi bi-bank"></i><span>SCB</span><small class="item-category">Bank</small></div>
            <div class="marquee-item"><i class="bi bi-building"></i><span>HDFC BANK</span><small class="item-category">Bank</small></div>
            <div class="marquee-item"><i class="bi bi-bank2"></i><span>ICICI BANK</span><small class="item-category">Bank</small></div>
            <div class="marquee-item"><i class="bi bi-building"></i><span>SBI</span><small class="item-category">Bank</small></div>
            <div class="marquee-item"><i class="bi bi-credit-card"></i><span>AXIS BANK</span><small class="item-category">Bank</small></div>
            <div class="marquee-item"><i class="bi bi-bank"></i><span>KOTAK BANK</span><small class="item-category">Bank</small></div>
            <div class="marquee-item"><i class="bi bi-building"></i><span>YES BANK</span><small class="item-category">Bank</small></div>
            <div class="marquee-item"><i class="bi bi-bank2"></i><span>RBL BANK</span><small class="item-category">Bank</small></div>
            <div class="marquee-item"><i class="bi bi-credit-card"></i><span>INDUSIND BANK</span><small class="item-category">Bank</small></div>
            <div class="marquee-item"><i class="bi bi-building"></i><span>FEDERAL BANK</span><small class="item-category">Bank</small></div>
            <div class="marquee-item"><i class="bi bi-bank"></i><span>IDFC FIRST BANK</span><small class="item-category">Bank</small></div>
            <div class="marquee-item"><i class="bi bi-credit-card"></i><span>PNB</span><small class="item-category">Bank</small></div>
            <div class="marquee-item"><i class="bi bi-bank2"></i><span>BOB</span><small class="item-category">Bank</small></div>
        </div>
    </div>

    {{-- ROW 2: E-COMMERCE PLATFORMS (Scrolling Right) --}}
    <div class="marquee-row right">
        <div class="marquee-track">
            {{-- E-Commerce Set --}}
            <div class="marquee-item"><i class="bi bi-shop"></i><span>MEESHO</span><small class="item-category">E-Com</small></div>
            <div class="marquee-item"><i class="bi bi-bag"></i><span>SNAPDEAL</span><small class="item-category">E-Com</small></div>
            <div class="marquee-item"><i class="bi bi-cart4"></i><span>SHOP 101</span><small class="item-category">E-Com</small></div>
            <div class="marquee-item"><i class="bi bi-amazon"></i><span>AMAZON</span><small class="item-category">E-Com</small></div>
            <div class="marquee-item"><i class="bi bi-gem"></i><span>GLOWROAD</span><small class="item-category">E-Com</small></div>
            <div class="marquee-item"><i class="bi bi-basket3"></i><span>FLIPKART</span><small class="item-category">E-Com</small></div>
            <div class="marquee-item"><i class="bi bi-building"></i><span>JIOMART</span><small class="item-category">E-Com</small></div>
            <div class="marquee-item"><i class="bi bi-bag-check"></i><span>MYNTRA</span><small class="item-category">E-Com</small></div>
            <div class="marquee-item"><i class="bi bi-shop-window"></i><span>CITYMALL</span><small class="item-category">E-Com</small></div>
            <div class="marquee-item"><i class="bi bi-cart"></i><span>LIMEROAD</span><small class="item-category">E-Com</small></div>
            <div class="marquee-item"><i class="bi bi-amazon"></i><span>AJIO</span><small class="item-category">E-Com</small></div>
            <div class="marquee-item"><i class="bi bi-bag"></i><span>NYKAA</span><small class="item-category">E-Com</small></div>
            <div class="marquee-item"><i class="bi bi-shop"></i><span>PAYTM MALL</span><small class="item-category">E-Com</small></div>
            <div class="marquee-item"><i class="bi bi-cart"></i><span>ZEPTO</span><small class="item-category">E-Com</small></div>
            <div class="marquee-item"><i class="bi bi-bag"></i><span>BIGBASKET</span><small class="item-category">E-Com</small></div>
            {{-- Duplicate set for seamless infinite loop --}}
            <div class="marquee-item"><i class="bi bi-shop"></i><span>MEESHO</span><small class="item-category">E-Com</small></div>
            <div class="marquee-item"><i class="bi bi-bag"></i><span>SNAPDEAL</span><small class="item-category">E-Com</small></div>
            <div class="marquee-item"><i class="bi bi-cart4"></i><span>SHOP 101</span><small class="item-category">E-Com</small></div>
            <div class="marquee-item"><i class="bi bi-amazon"></i><span>AMAZON</span><small class="item-category">E-Com</small></div>
            <div class="marquee-item"><i class="bi bi-gem"></i><span>GLOWROAD</span><small class="item-category">E-Com</small></div>
            <div class="marquee-item"><i class="bi bi-basket3"></i><span>FLIPKART</span><small class="item-category">E-Com</small></div>
            <div class="marquee-item"><i class="bi bi-building"></i><span>JIOMART</span><small class="item-category">E-Com</small></div>
            <div class="marquee-item"><i class="bi bi-bag-check"></i><span>MYNTRA</span><small class="item-category">E-Com</small></div>
            <div class="marquee-item"><i class="bi bi-shop-window"></i><span>CITYMALL</span><small class="item-category">E-Com</small></div>
            <div class="marquee-item"><i class="bi bi-cart"></i><span>LIMEROAD</span><small class="item-category">E-Com</small></div>
            <div class="marquee-item"><i class="bi bi-amazon"></i><span>AJIO</span><small class="item-category">E-Com</small></div>
            <div class="marquee-item"><i class="bi bi-bag"></i><span>NYKAA</span><small class="item-category">E-Com</small></div>
            <div class="marquee-item"><i class="bi bi-shop"></i><span>PAYTM MALL</span><small class="item-category">E-Com</small></div>
            <div class="marquee-item"><i class="bi bi-cart"></i><span>ZEPTO</span><small class="item-category">E-Com</small></div>
            <div class="marquee-item"><i class="bi bi-bag"></i><span>BIGBASKET</span><small class="item-category">E-Com</small></div>
        </div>
    </div>
</section>



{{-- ═══════════════════════════════════════════════════════════════════
     WHY EcomgstPro SECTION — Fully Responsive for Mobile
     Layout: Left side (scrollable content) | Right side (sticky card)
     Style: Clean white background with subtle elevation
══════════════════════════════════════════════════════════════════ --}}

<style>
/* ─────────────────────────────────────────────────────────────────
   WHY EcomgstPro SECTION STYLES - FULLY RESPONSIVE
   Split Layout: Left (scrollable) + Right (sticky card)
   Mobile-first approach
   ───────────────────────────────────────────────────────────── */
.why-EcomgstPro {
    background: #ffffff;
    position: relative;
    overflow: hidden;
    padding: 3rem 1rem;
}

@media (min-width: 768px) {
    .why-EcomgstPro {
        padding: 5rem 0;
    }
}

/* Decorative background elements */
.why-EcomgstPro::before {
    content: '';
    position: absolute;
    top: -100px;
    right: -100px;
    width: 400px;
    height: 400px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(0,201,141,0.03) 0%, transparent 70%);
    pointer-events: none;
    z-index: 0;
}

.why-EcomgstPro::after {
    content: '';
    position: absolute;
    bottom: -80px;
    left: -80px;
    width: 350px;
    height: 350px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(0,170,255,0.03) 0%, transparent 70%);
    pointer-events: none;
    z-index: 0;
}

/* Split Container - Mobile first (stacked) */
.why-split {
    display: flex;
    flex-direction: column;
    gap: 2rem;
    position: relative;
    z-index: 1;
}

@media (min-width: 1024px) {
    .why-split {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 3rem;
        align-items: start;
        flex-direction: row;
    }
}

/* ─────────────────────────────────────────────────────────────────
   LEFT SIDE — SCROLLABLE CONTENT (Mobile Optimized)
   ───────────────────────────────────────────────────────────── */
.why-content-scroll {
    max-height: none;
    overflow-y: visible;
    padding-right: 0;
    scroll-behavior: smooth;
}

@media (min-width: 1024px) {
    .why-content-scroll {
        max-height: 550px;
        overflow-y: auto;
        padding-right: 1rem;
    }
}

/* Custom scrollbar styling - Desktop only */
@media (min-width: 1024px) {
    .why-content-scroll::-webkit-scrollbar {
        width: 4px;
    }

    .why-content-scroll::-webkit-scrollbar-track {
        background: #eef2f8;
        border-radius: 10px;
    }

    .why-content-scroll::-webkit-scrollbar-thumb {
        background: var(--teal);
        border-radius: 10px;
    }

    .why-content-scroll::-webkit-scrollbar-thumb:hover {
        background: #00b077;
    }
}

/* Header styles - Mobile optimized */
.why-header {
    margin-bottom: 1.5rem;
    text-align: left;
}

@media (min-width: 768px) {
    .why-header {
        margin-bottom: 2rem;
    }
}

.why-header .gp-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 2.5px;
    text-transform: uppercase;
    color: var(--teal);
    margin-bottom: 0.75rem;
}

.why-header .gp-eyebrow::before {
    content: '';
    width: 18px;
    height: 2px;
    background: var(--teal);
    border-radius: 2px;
}

@media (min-width: 768px) {
    .why-header .gp-eyebrow {
        margin-bottom: 1rem;
    }
}

.why-header .gp-h2 {
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    font-size: 1.75rem;
    line-height: 1.2;
    color: var(--navy);
    margin-bottom: 0.75rem;
}

@media (min-width: 768px) {
    .why-header .gp-h2 {
        font-size: clamp(28px, 3.5vw, 40px);
        margin-bottom: 1rem;
    }
}

.why-header .gp-h2 em {
    color: var(--teal);
    font-style: normal;
}

.why-header .gp-lead {
    font-size: 0.9rem;
    line-height: 1.6;
    color: #5a6874;
    max-width: 100%;
}

@media (min-width: 768px) {
    .why-header .gp-lead {
        font-size: 1rem;
        max-width: 90%;
    }
}

/* Feature items - Mobile optimized (vertical layout) */
.why-feature-item {
    display: flex;
    gap: 0.875rem;
    padding: 1rem;
    background: #f8fafc;
    border-radius: 16px;
    margin-bottom: 0.875rem;
    transition: all 0.3s ease;
    border: 1px solid #eef2f8;
}

@media (min-width: 768px) {
    .why-feature-item {
        gap: 1.2rem;
        padding: 1.2rem;
        margin-bottom: 1rem;
    }
}

.why-feature-item:hover {
    background: #ffffff;
    border-color: rgba(0,201,141,0.2);
    box-shadow: 0 8px 25px -8px rgba(0, 0, 0, 0.08);
    transform: translateX(4px);
}

.why-feature-icon {
    width: 44px;
    height: 44px;
    background: linear-gradient(135deg, rgba(0,201,141,0.1) 0%, rgba(0,170,255,0.05) 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: all 0.3s ease;
}

@media (min-width: 768px) {
    .why-feature-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
    }
}

.why-feature-item:hover .why-feature-icon {
    background: linear-gradient(135deg, rgba(0,201,141,0.2) 0%, rgba(0,170,255,0.1) 100%);
    transform: scale(1.02);
}

.why-feature-icon i {
    font-size: 1.25rem;
    color: var(--teal);
}

@media (min-width: 768px) {
    .why-feature-icon i {
        font-size: 1.5rem;
    }
}

.why-feature-text {
    flex: 1;
}

.why-feature-title {
    font-family: 'Syne', sans-serif;
    font-weight: 700;
    font-size: 0.9rem;
    color: var(--navy);
    margin-bottom: 0.25rem;
}

@media (min-width: 768px) {
    .why-feature-title {
        font-size: 1rem;
        margin-bottom: 0.3rem;
    }
}

.why-feature-desc {
    font-size: 0.75rem;
    line-height: 1.5;
    color: #5a6874;
    margin: 0;
}

@media (min-width: 768px) {
    .why-feature-desc {
        font-size: 0.85rem;
        line-height: 1.6;
    }
}

/* ─────────────────────────────────────────────────────────────────
   RIGHT SIDE — STICKY CARD (Mobile Optimized)
   ───────────────────────────────────────────────────────────── */
.why-visual {
    position: relative;
    top: 0;
    width: 100%;
}

@media (min-width: 1024px) {
    .why-visual {
        position: sticky;
        top: 2rem;
    }
}

.why-visual-card {
    background: linear-gradient(135deg, var(--navy) 0%, var(--deep) 100%);
    border-radius: 24px;
    padding: 1.5rem;
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.08);
    box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.2);
}

@media (min-width: 768px) {
    .why-visual-card {
        border-radius: 28px;
        padding: 2rem;
    }
}

/* Decorative glow */
.why-visual-card::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -30%;
    width: 300px;
    height: 300px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(0,201,141,0.1) 0%, transparent 70%);
    pointer-events: none;
}

.why-visual-card::after {
    content: '';
    position: absolute;
    bottom: -30%;
    left: -20%;
    width: 250px;
    height: 250px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(0,170,255,0.08) 0%, transparent 70%);
    pointer-events: none;
}

/* Main icon/image area - Mobile optimized */
.why-visual-icon {
    text-align: center;
    margin-bottom: 1rem;
    position: relative;
    z-index: 1;
}

@media (min-width: 768px) {
    .why-visual-icon {
        margin-bottom: 1.5rem;
    }
}

.why-visual-icon .main-icon {
    width: 70px;
    height: 70px;
    background: rgba(0,201,141,0.12);
    border-radius: 20px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid rgba(0,201,141,0.2);
    animation: floatIcon 3s ease-in-out infinite;
}

@media (min-width: 768px) {
    .why-visual-icon .main-icon {
        width: 100px;
        height: 100px;
        border-radius: 30px;
    }
}

@keyframes floatIcon {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-8px); }
}

.why-visual-icon .main-icon i {
    font-size: 2rem;
    color: var(--teal);
}

@media (min-width: 768px) {
    .why-visual-icon .main-icon i {
        font-size: 3rem;
    }
}

/* Stats inside visual card - Mobile grid */
.why-visual-stats {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
    margin: 1rem 0;
    position: relative;
    z-index: 1;
}

@media (min-width: 768px) {
    .why-visual-stats {
        gap: 1rem;
        margin: 1.5rem 0;
    }
}

.why-visual-stat {
    text-align: center;
    background: rgba(255, 255, 255, 0.03);
    border-radius: 14px;
    padding: 0.6rem;
    border: 1px solid rgba(255, 255, 255, 0.05);
}

@media (min-width: 768px) {
    .why-visual-stat {
        border-radius: 16px;
        padding: 0.8rem;
    }
}

.why-visual-stat .stat-value {
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    font-size: 1.3rem;
    color: var(--teal);
    line-height: 1;
}

@media (min-width: 768px) {
    .why-visual-stat .stat-value {
        font-size: 1.8rem;
    }
}

.why-visual-stat .stat-label {
    font-size: 0.6rem;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 0.2rem;
}

@media (min-width: 768px) {
    .why-visual-stat .stat-label {
        font-size: 0.7rem;
        margin-top: 0.3rem;
    }
}

/* Trust badge - Mobile */
.why-visual-trust {
    text-align: center;
    position: relative;
    z-index: 1;
    margin-top: 0.75rem;
}

@media (min-width: 768px) {
    .why-visual-trust {
        margin-top: 1rem;
    }
}

.why-visual-trust .trust-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    background: rgba(0,201,141,0.1);
    border-radius: 40px;
    padding: 0.4rem 1rem;
    font-size: 0.7rem;
    color: #fff;
    border: 1px solid rgba(0,201,141,0.2);
}

@media (min-width: 768px) {
    .why-visual-trust .trust-badge {
        gap: 0.5rem;
        padding: 0.5rem 1.2rem;
        font-size: 0.8rem;
    }
}

.trust-badge i {
    color: var(--teal);
    font-size: 0.8rem;
}

@media (min-width: 768px) {
    .trust-badge i {
        font-size: 1rem;
    }
}

/* CTA button inside card - Mobile */
.why-visual-cta {
    margin-top: 1rem;
    text-align: center;
    position: relative;
    z-index: 1;
}

@media (min-width: 768px) {
    .why-visual-cta {
        margin-top: 1.5rem;
    }
}

.why-visual-cta .btn-card {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    background: linear-gradient(135deg, var(--teal), #00e0a8);
    color: var(--navy);
    font-weight: 700;
    padding: 0.7rem 1.2rem;
    border-radius: 40px;
    text-decoration: none;
    font-size: 0.8rem;
    transition: all 0.3s ease;
    width: 100%;
}

@media (min-width: 768px) {
    .why-visual-cta .btn-card {
        gap: 0.5rem;
        padding: 0.8rem 1.8rem;
        font-size: 0.9rem;
        width: auto;
        min-width: 200px;
    }
}

.why-visual-cta .btn-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,201,141,0.3);
    gap: 0.8rem;
}

/* Dot pattern overlay */
.why-dot-pattern {
    position: absolute;
    inset: 0;
    background-image: radial-gradient(rgba(255,255,255,0.02) 1px, transparent 1px);
    background-size: 20px 20px;
    pointer-events: none;
    z-index: 0;
}

/* Scroll hint - Hide on mobile */
.scroll-hint {
    display: none;
}

@media (min-width: 1024px) {
    .scroll-hint {
        display: flex;
        text-align: center;
        margin-top: 1rem;
        font-size: 0.7rem;
        color: #94a3b8;
        align-items: center;
        justify-content: center;
        gap: 0.3rem;
    }
}

/* Footnote text */
.why-visual-card > p {
    text-align: center;
    font-size: 0.6rem;
    color: var(--muted);
    margin-top: 0.75rem;
    position: relative;
    z-index: 1;
}

@media (min-width: 768px) {
    .why-visual-card > p {
        font-size: 0.7rem;
        margin-top: 1rem;
    }
}

/* Animation */
@keyframes subtlePulse {
    0%, 100% { opacity: 0.6; }
    50% { opacity: 1; }
}

.scroll-hint i {
    animation: subtlePulse 1.5s ease infinite;
}

/* Container padding fix */
.gp-container {
    max-width: 1280px;
    margin: 0 auto;
    padding: 0 1rem;
}

@media (min-width: 768px) {
    .gp-container {
        padding: 0 2rem;
    }
}

@media (min-width: 1280px) {
    .gp-container {
        padding: 0;
    }
}

/* CSS Variables if not defined globally */
:root {
    --navy: #0a1628;
    --deep: #0d1f3c;
    --teal: #00c98d;
    --sky: #00aaff;
    --muted: #8a9bbf;
}
</style>

{{-- ================================================================
     WHY EcomgstPro SECTION — Split Layout (Fully Responsive)
     ================================================================ --}}
<section class="why-EcomgstPro">
    <div class="gp-container">
        <div class="why-split">

            {{-- ───────────────────────────────────────────────
                 LEFT SIDE: SCROLLABLE FEATURE LIST
                 ─────────────────────────────────────────────── --}}
            <div class="why-content-scroll">
                <div class="why-header">
                    <div class="gp-eyebrow">Why EcomgstPro</div>
                    <h2 class="gp-h2">
                        Built for <em>Indian businesses</em><br>of every size
                    </h2>
                    <p class="gp-lead">
                        From freelancers filing quarterly returns to large enterprises managing multi-GSTIN portfolios — EcomgstPro scales with you.
                    </p>
                </div>

                {{-- Feature 1 --}}
                <div class="why-feature-item">
                    <div class="why-feature-icon">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                    <div class="why-feature-text">
                        <h4 class="why-feature-title">GSP Certified Infrastructure</h4>
                        <p class="why-feature-desc">Direct API access to the GST portal — no third-party risks, no manual uploads, 99.9% uptime guaranteed.</p>
                    </div>
                </div>

                {{-- Feature 2 --}}
                <div class="why-feature-item">
                    <div class="why-feature-icon">
                        <i class="bi bi-cpu"></i>
                    </div>
                    <div class="why-feature-text">
                        <h4 class="why-feature-title">AI-Powered Matching Engine</h4>
                        <p class="why-feature-desc">Intelligent reconciliation that learns your business patterns and improves over time. 98% accuracy.</p>
                    </div>
                </div>

                {{-- Feature 3 --}}
                <div class="why-feature-item">
                    <div class="why-feature-icon">
                        <i class="bi bi-headset"></i>
                    </div>
                    <div class="why-feature-text">
                        <h4 class="why-feature-title">Dedicated Support Team</h4>
                        <p class="why-feature-desc">CA-assisted onboarding, live chat, and WhatsApp support — real humans, real fast, real help.</p>
                    </div>
                </div>

                {{-- Feature 4 --}}
                <div class="why-feature-item">
                    <div class="why-feature-icon">
                        <i class="bi bi-lock"></i>
                    </div>
                    <div class="why-feature-text">
                        <h4 class="why-feature-title">Bank-Grade Security</h4>
                        <p class="why-feature-desc">Your data is encrypted at rest and in transit. SOC 2 Type II and ISO 27001 certified infrastructure.</p>
                    </div>
                </div>

                {{-- Feature 5 --}}
                <div class="why-feature-item">
                    <div class="why-feature-icon">
                        <i class="bi bi-graph-up"></i>
                    </div>
                    <div class="why-feature-text">
                        <h4 class="why-feature-title">Real-Time Analytics</h4>
                        <p class="why-feature-desc">Live dashboard with ITC tracking, liability forecasts, and filing status — always up to date.</p>
                    </div>
                </div>

                {{-- Feature 6 --}}
                <div class="why-feature-item">
                    <div class="why-feature-icon">
                        <i class="bi bi-arrow-repeat"></i>
                    </div>
                    <div class="why-feature-text">
                        <h4 class="why-feature-title">Automated Reconciliation</h4>
                        <p class="why-feature-desc">Match 10,000+ invoices in seconds. Spot mismatches, claim maximum ITC, and generate audit reports.</p>
                    </div>
                </div>

                <!-- Scroll hint (appears only on desktop when content overflows) -->
                <div class="scroll-hint">
                    <i class="bi bi-mouse"></i>
                    <span>Scroll for more</span>
                    <i class="bi bi-chevron-down"></i>
                </div>
            </div>

            {{-- ───────────────────────────────────────────────
                 RIGHT SIDE: STICKY VISUAL CARD
                 ─────────────────────────────────────────────── --}}
            <div class="why-visual">
                <div class="why-visual-card">
                    <div class="why-dot-pattern"></div>

                    {{-- Animated Icon --}}
                    <div class="why-visual-icon">
                        <div class="main-icon">
                            <i class="bi bi-stars"></i>
                        </div>
                    </div>

                    {{-- Key Stats --}}
                    <div class="why-visual-stats">
                        <div class="why-visual-stat">
                            <div class="stat-value">10K+</div>
                            <div class="stat-label">Businesses</div>
                        </div>
                        <div class="why-visual-stat">
                            <div class="stat-value">₹50Cr+</div>
                            <div class="stat-label">ITC Recovered</div>
                        </div>
                    </div>

                    {{-- Trust Indicators --}}
                    <div class="why-visual-trust">
                        <div class="trust-badge">
                            <i class="bi bi-shield-check-fill"></i>
                            <span>GSP Certified • ISO 27001</span>
                        </div>
                    </div>

                    {{-- Trust message --}}
                    <div class="why-visual-trust" style="margin-top: 0.6rem;">
                        <div class="trust-badge" style="background: rgba(255,255,255,0.03);">
                            <i class="bi bi-people-fill"></i>
                            <span>Trusted by 500+ CAs</span>
                        </div>
                    </div>

                    {{-- CTA Button --}}
                    <div class="why-visual-cta">
                        <a href="{{ route('register') }}" class="btn-card">
                            Start Free Trial <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>

                    {{-- Small footnote --}}
                    <p>
                        No credit card required • Cancel anytime
                    </p>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════════════
     PAIN POINTS SECTION — "You're Here Because Something Isn't Working"
     Purpose: Highlights 6 common GST compliance frustrations that
     drive users to seek a solution. Creates emotional connection and
     validates their pain before presenting the solution.
     Location: Above the Products section (Section 6)
     Style: Clean white background with subtle shadows & hover effects
══════════════════════════════════════════════════════════════════ --}}

<style>
/* ─────────────────────────────────────────────────────────────────
   PAIN POINTS SECTION STYLES
   ───────────────────────────────────────────────────────────── */
.pain-points {
    background: #ffffff;
    position: relative;
    overflow: hidden;
}

/* Decorative background element */
.pain-points::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, rgba(0,201,141,0.03) 0%, transparent 70%);
    pointer-events: none;
    z-index: 0;
}

.pain-points::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 300px;
    height: 300px;
    background: radial-gradient(circle, rgba(0,170,255,0.03) 0%, transparent 70%);
    pointer-events: none;
    z-index: 0;
}

.pain-header {
    text-align: center;
    max-width: 700px;
    margin: 0 auto 3rem;
    position: relative;
    z-index: 1;
}

.pain-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(0,201,141,0.08);
    border: 1px solid rgba(0,201,141,0.15);
    border-radius: 40px;
    padding: 6px 18px;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: var(--teal);
    margin-bottom: 1.5rem;
}

.pain-eyebrow i {
    font-size: 14px;
}

.pain-title {
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    font-size: clamp(28px, 4vw, 42px);
    line-height: 1.2;
    color: var(--navy);
    margin-bottom: 1rem;
}

.pain-title em {
    color: var(--teal);
    font-style: normal;
    position: relative;
}

.pain-title em::after {
    content: '';
    position: absolute;
    bottom: -4px;
    left: 0;
    right: 0;
    height: 8px;
    background: rgba(0,201,141,0.2);
    border-radius: 4px;
    z-index: -1;
}

.pain-subtitle {
    font-size: 16px;
    line-height: 1.6;
    color: #5a6874;
    max-width: 550px;
    margin: 0 auto;
}

/* Pain Grid Layout */
.pain-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.75rem;
    position: relative;
    z-index: 1;
}

/* Pain Card Design */
.pain-card {
    background: #ffffff;
    border-radius: 20px;
    padding: 1.8rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
    border: 1px solid #eef2f8;
    transition: all 0.3s cubic-bezier(0.2, 0.9, 0.4, 1.1);
    position: relative;
    overflow: hidden;
}

/* Animated accent line at bottom */
.pain-card::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--teal), var(--sky));
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.3s ease;
}

.pain-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 20px 35px -12px rgba(0, 0, 0, 0.12);
    border-color: rgba(0,201,141,0.2);
}

.pain-card:hover::after {
    transform: scaleX(1);
}

/* Card Icon */
.pain-icon {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, rgba(0,201,141,0.1) 0%, rgba(0,170,255,0.05) 100%);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.4rem;
    transition: all 0.3s ease;
}

.pain-card:hover .pain-icon {
    background: linear-gradient(135deg, rgba(0,201,141,0.2) 0%, rgba(0,170,255,0.1) 100%);
    transform: scale(1.02);
}

.pain-icon i {
    font-size: 28px;
    color: var(--teal);
}

/* Icon variants for different cards */
.pain-icon.icon-warning i { color: #f59e0b; }
.pain-icon.icon-danger i { color: #ef4444; }
.pain-icon.icon-clock i { color: var(--teal); }
.pain-icon.icon-chart i { color: var(--sky); }

/* Card Content */
.pain-card-title {
    font-family: 'Syne', sans-serif;
    font-weight: 700;
    font-size: 1.2rem;
    color: var(--navy);
    margin-bottom: 0.75rem;
    line-height: 1.3;
}

.pain-card-desc {
    font-size: 0.9rem;
    line-height: 1.65;
    color: #5a6874;
    margin-bottom: 1.2rem;
}

/* Pain meter / severity indicator */
.pain-meter {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 0.75rem;
}

.pain-meter-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #e2e8f0;
    transition: all 0.3s ease;
}

.pain-meter-dot.active {
    background: var(--teal);
    box-shadow: 0 0 4px var(--teal);
}

.pain-meter-dot.high {
    background: #ef4444;
}

.pain-meter-text {
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #94a3b8;
    margin-left: 6px;
}

/* Responsive */
@media (max-width: 1024px) {
    .pain-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
    }
}

@media (max-width: 640px) {
    .pain-grid {
        grid-template-columns: 1fr;
    }
    .pain-card {
        padding: 1.5rem;
    }
}
</style>

{{-- ================================================================
     PAIN POINTS SECTION
     ================================================================ --}}
<section class="pain-points gp-section">
    <div class="gp-container">
        <div class="pain-header">
            <div class="pain-eyebrow">
                <i class="bi bi-emoji-frown"></i>
                <span>Feeling Stuck?</span>
            </div>
            <h2 class="pain-title">
                You're Here Because <br><em>Something Isn't Working</em>
            </h2>
            <p class="pain-subtitle">
                We understand these frustrations. Every day you lose time, money, and peace of mind dealing with GST compliance chaos.
            </p>
        </div>

        <div class="pain-grid">
            {{-- Card 1: Taking Too Long --}}
            <div class="pain-card">
                <div class="pain-icon icon-clock">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <h3 class="pain-card-title">Taking Too Long</h3>
                <p class="pain-card-desc">
                    Too much of your valuable time is lost filing GSTR-1 and managing bank entries manually. Hours become days.
                </p>
                <div class="pain-meter">
                    <span class="pain-meter-dot active"></span>
                    <span class="pain-meter-dot active"></span>
                    <span class="pain-meter-dot active"></span>
                    <span class="pain-meter-dot active"></span>
                    <span class="pain-meter-dot"></span>
                    <span class="pain-meter-text">Critical</span>
                </div>
            </div>

            {{-- Card 2: Manual Errors --}}
            <div class="pain-card">
                <div class="pain-icon icon-danger">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <h3 class="pain-card-title">Manual Errors</h3>
                <p class="pain-card-desc">
                    You're still doing bank entries or filing GSTR-1 manually — which makes you prone to costly human errors and notices.
                </p>
                <div class="pain-meter">
                    <span class="pain-meter-dot active"></span>
                    <span class="pain-meter-dot active"></span>
                    <span class="pain-meter-dot active"></span>
                    <span class="pain-meter-dot active"></span>
                    <span class="pain-meter-dot active"></span>
                    <span class="pain-meter-text">High Risk</span>
                </div>
            </div>

            {{-- Card 3: Deadline Pressure --}}
            <div class="pain-card">
                <div class="pain-icon icon-warning">
                    <i class="bi bi-alarm-fill"></i>
                </div>
                <h3 class="pain-card-title">Deadline Pressure</h3>
                <p class="pain-card-desc">
                    You're constantly worried about missing strict deadlines or facing the pressure of last-minute compliance filing.
                </p>
                <div class="pain-meter">
                    <span class="pain-meter-dot active"></span>
                    <span class="pain-meter-dot active"></span>
                    <span class="pain-meter-dot active"></span>
                    <span class="pain-meter-dot active"></span>
                    <span class="pain-meter-dot"></span>
                    <span class="pain-meter-text">Urgent</span>
                </div>
            </div>

            {{-- Card 4: Inefficient Processes --}}
            <div class="pain-card">
                <div class="pain-icon">
                    <i class="bi bi-gear-wide-connected"></i>
                </div>
                <h3 class="pain-card-title">Inefficient Processes</h3>
                <p class="pain-card-desc">
                    You are frustrated with broken workflows and outdated systems that are slowing down your team's productivity.
                </p>
                <div class="pain-meter">
                    <span class="pain-meter-dot active"></span>
                    <span class="pain-meter-dot active"></span>
                    <span class="pain-meter-dot active"></span>
                    <span class="pain-meter-dot"></span>
                    <span class="pain-meter-dot"></span>
                    <span class="pain-meter-text">Moderate</span>
                </div>
            </div>

            {{-- Card 5: Lost ITC Claims (New) --}}
            <div class="pain-card">
                <div class="pain-icon">
                    <i class="bi bi-currency-rupee"></i>
                </div>
                <h3 class="pain-card-title">Lost ITC Claims</h3>
                <p class="pain-card-desc">
                    Missing invoices and unreconciled purchases mean you're leaving lakhs of rupees in Input Tax Credit unclaimed every year.
                </p>
                <div class="pain-meter">
                    <span class="pain-meter-dot active"></span>
                    <span class="pain-meter-dot active"></span>
                    <span class="pain-meter-dot active"></span>
                    <span class="pain-meter-dot active"></span>
                    <span class="pain-meter-dot"></span>
                    <span class="pain-meter-text">Financial Loss</span>
                </div>
            </div>

            {{-- Card 6: Notice Anxiety (New) --}}
            <div class="pain-card">
                <div class="pain-icon icon-danger">
                    <i class="bi bi-envelope-exclamation-fill"></i>
                </div>
                <h3 class="pain-card-title">Notice Anxiety</h3>
                <p class="pain-card-desc">
                    Every mismatch or delay raises the fear of receiving a scrutiny notice from the department — adding unnecessary stress.
                </p>
                <div class="pain-meter">
                    <span class="pain-meter-dot active"></span>
                    <span class="pain-meter-dot active"></span>
                    <span class="pain-meter-dot active"></span>
                    <span class="pain-meter-dot active"></span>
                    <span class="pain-meter-dot active"></span>
                    <span class="pain-meter-text">Severe</span>
                </div>
            </div>
        </div>
    </div>
</section>




{{-- ═══════════════════════════════════════════════════════════════════
     SOLUTION BANNER — "Your Step by Step Solution"
     Purpose: Bridges the gap between pain points and products.
     Shows users exactly how EcomgstPro solves their problems in 4 clear steps.
     Height: 400px fixed (responsive friendly)
     Style: Full-width banner with gradient background, floating cards
══════════════════════════════════════════════════════════════════ --}}

<style>
/* ─────────────────────────────────────────────────────────────────
   SOLUTION BANNER STYLES
   Height: 400px on desktop, auto on mobile
   ───────────────────────────────────────────────────────────── */
.solution-banner {
    background: linear-gradient(135deg, #0a1628 0%, #0d1f3c 50%, #0a2240 100%);
    position: relative;
    overflow: hidden;
    height: 400px;
    display: flex;
    align-items: center;
}

/* Decorative elements */
.solution-banner::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 500px;
    height: 500px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(0,201,141,0.08) 0%, transparent 70%);
    pointer-events: none;
}

.solution-banner::after {
    content: '';
    position: absolute;
    bottom: -30%;
    left: -5%;
    width: 400px;
    height: 400px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(0,170,255,0.06) 0%, transparent 70%);
    pointer-events: none;
}

/* Dot grid overlay */
.solution-banner .dot-overlay {
    position: absolute;
    inset: 0;
    background-image: radial-gradient(rgba(255,255,255,0.02) 1px, transparent 1px);
    background-size: 30px 30px;
    pointer-events: none;
}

.solution-container {
    max-width: 1300px;
    margin: 0 auto;
    padding: 0 32px;
    width: 100%;
    position: relative;
    z-index: 2;
}

/* Header section inside banner */
.solution-header {
    text-align: center;
    margin-bottom: 2.5rem;
}

.solution-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(0,201,141,0.12);
    border: 1px solid rgba(0,201,141,0.25);
    border-radius: 40px;
    padding: 5px 18px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: var(--teal);
    margin-bottom: 1rem;
}

.solution-badge i {
    font-size: 12px;
}

.solution-title {
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    font-size: clamp(24px, 3.5vw, 36px);
    color: #ffffff;
    margin: 0;
    line-height: 1.2;
}

.solution-title em {
    color: var(--teal);
    font-style: normal;
}

/* Steps grid - 4 columns */
.solution-steps {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.5rem;
}

/* Individual step card */
.step-card {
    background: rgba(255, 255, 255, 0.04);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 20px;
    padding: 1.5rem 1.2rem;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.step-card:hover {
    background: rgba(255, 255, 255, 0.08);
    border-color: rgba(0,201,141,0.3);
    transform: translateY(-4px);
}

/* Step number circle */
.step-number {
    position: absolute;
    top: 12px;
    right: 16px;
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    font-size: 2.5rem;
    color: rgba(0,201,141,0.15);
    line-height: 1;
    pointer-events: none;
}

/* Step icon */
.step-icon {
    width: 52px;
    height: 52px;
    background: linear-gradient(135deg, rgba(0,201,141,0.15) 0%, rgba(0,170,255,0.08) 100%);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.2rem;
    transition: all 0.3s ease;
}

.step-card:hover .step-icon {
    background: linear-gradient(135deg, rgba(0,201,141,0.25) 0%, rgba(0,170,255,0.15) 100%);
    transform: scale(1.02);
}

.step-icon i {
    font-size: 26px;
    color: var(--teal);
}

/* Step text */
.step-title {
    font-family: 'Syne', sans-serif;
    font-weight: 700;
    font-size: 1.1rem;
    color: #ffffff;
    margin-bottom: 0.5rem;
    line-height: 1.3;
}

.step-desc {
    font-size: 0.8rem;
    line-height: 1.6;
    color: #8a9bbf;
    margin-bottom: 0;
}

/* Connector line between steps (desktop only) */
.step-connector {
    display: none;
}

/* Responsive adjustments */
@media (max-width: 1024px) {
    .solution-banner {
        height: auto;
        padding: 3rem 0;
    }

    .solution-steps {
        grid-template-columns: repeat(2, 1fr);
        gap: 1.2rem;
    }

    .step-card {
        padding: 1.2rem;
    }
}

@media (max-width: 640px) {
    .solution-steps {
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    .solution-container {
        padding: 0 20px;
    }

    .step-card {
        display: flex;
        align-items: center;
        gap: 1rem;
        text-align: left;
        padding: 1rem;
    }

    .step-number {
        display: none;
    }

    .step-icon {
        margin-bottom: 0;
        width: 48px;
        height: 48px;
        flex-shrink: 0;
    }

    .step-icon i {
        font-size: 22px;
    }

    .step-content {
        flex: 1;
    }

    .step-title {
        font-size: 1rem;
        margin-bottom: 0.25rem;
    }

    .step-desc {
        font-size: 0.75rem;
    }
}
</style>

{{-- ================================================================
     SOLUTION BANNER SECTION
     Height: 400px (desktop)
     ================================================================ --}}
<section class="solution-banner">
    <div class="dot-overlay"></div>
    <div class="solution-container">

        {{-- Header --}}
        <div class="solution-header">
            <div class="solution-badge">
                <i class="bi bi-check-circle-fill"></i>
                <span>Your Path to Compliance Freedom</span>
            </div>
            <h2 class="solution-title">
                From <em>Frustration</em> to <em>Automation</em><br>
                in 4 Simple Steps
            </h2>
        </div>

        {{-- Steps Grid --}}
        <div class="solution-steps">

            {{-- Step 1: Connect --}}
            <div class="step-card">
                <div class="step-number">01</div>
                <div class="step-icon">
                    <i class="bi bi-plug-fill"></i>
                </div>
                <div class="step-content">
                    <h4 class="step-title">Connect Your GSTIN</h4>
                    <p class="step-desc">Link your GST number in seconds via our GSP-certified API. Secure, read-only access to the GST portal.</p>
                </div>
            </div>

            {{-- Step 2: Upload Data --}}
            <div class="step-card">
                <div class="step-number">02</div>
                <div class="step-icon">
                    <i class="bi bi-cloud-upload-fill"></i>
                </div>
                <div class="step-content">
                    <h4 class="step-title">Upload Any Format</h4>
                    <p class="step-desc">Drag & drop bank statements, purchase registers, or marketplace reports. We accept Excel, PDF, CSV.</p>
                </div>
            </div>

            {{-- Step 3: Auto-Reconcile --}}
            <div class="step-card">
                <div class="step-number">03</div>
                <div class="step-icon">
                    <i class="bi bi-robot"></i>
                </div>
                <div class="step-content">
                    <h4 class="step-title">AI Auto-Reconciliation</h4>
                    <p class="step-desc">Our AI matches invoices with GSTR-2A/2B, flags mismatches, and maximizes your ITC claims automatically.</p>
                </div>
            </div>

            {{-- Step 4: One-Click Filing --}}
            <div class="step-card">
                <div class="step-number">04</div>
                <div class="step-icon">
                    <i class="bi bi-send-check-fill"></i>
                </div>
                <div class="step-content">
                    <h4 class="step-title">One-Click Filing</h4>
                    <p class="step-desc">Review pre-filled returns and push directly to the GST portal. Done in minutes, not days.</p>
                </div>
            </div>

        </div>
    </div>
</section>



{{-- ═══════════════════════════════════════════════════════════════════
     LIVE DEMO SECTION — Bank Statement Processor
     Purpose: Let users upload a bank statement and see how the platform
     processes transactions in real-time.
     Features: 200+ Indian banks dropdown, PDF upload, drag & drop,
     live preview of processed data.
══════════════════════════════════════════════════════════════════ --}}

<style>
/* ─────────────────────────────────────────────────────────────────
   LIVE DEMO SECTION STYLES
   ───────────────────────────────────────────────────────────── */
.live-demo {
    background: linear-gradient(135deg, #f7faff 0%, #ffffff 100%);
    position: relative;
    overflow: hidden;
    padding: 5rem 0;
}

/* Decorative elements */
.live-demo::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent, var(--teal), transparent);
}

.live-demo .section-header {
    text-align: center;
    margin-bottom: 3rem;
}

.live-demo .demo-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(0,201,141,0.08);
    border: 1px solid rgba(0,201,141,0.15);
    border-radius: 40px;
    padding: 6px 18px;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: var(--teal);
    margin-bottom: 1rem;
}

.live-demo .demo-title {
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    font-size: clamp(28px, 4vw, 42px);
    color: var(--navy);
    margin-bottom: 1rem;
}

.live-demo .demo-title em {
    color: var(--teal);
    font-style: normal;
}

.live-demo .demo-subtitle {
    font-size: 1rem;
    color: #5a6874;
    max-width: 600px;
    margin: 0 auto;
}

/* Demo Container */
.demo-container {
    max-width: 1300px;
    margin: 0 auto;
    padding: 0 2rem;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
}

/* ─────────────────────────────────────────────────────────────────
   LEFT PANEL — Input Form
   ───────────────────────────────────────────────────────────── */
.demo-input-panel {
    background: #ffffff;
    border-radius: 28px;
    padding: 2rem;
    box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.08);
    border: 1px solid #eef2f8;
}

.input-group {
    margin-bottom: 1.5rem;
}

.input-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    font-size: 0.85rem;
    color: var(--navy);
    margin-bottom: 0.5rem;
}

.input-label i {
    color: var(--teal);
    font-size: 1rem;
}

/* Bank Select Dropdown */
.bank-select {
    width: 100%;
    padding: 1rem 1.2rem;
    border: 1.5px solid #eef2f8;
    border-radius: 16px;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.9rem;
    color: var(--navy);
    background: #ffffff;
    cursor: pointer;
    transition: all 0.2s ease;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2300c98d' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 1rem center;
}

.bank-select:hover, .bank-select:focus {
    border-color: var(--teal);
    outline: none;
    box-shadow: 0 0 0 3px rgba(0,201,141,0.1);
}

/* File Upload Area */
.upload-area {
    border: 2px dashed #eef2f8;
    border-radius: 20px;
    padding: 2rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
    background: #fafcff;
}

.upload-area:hover, .upload-area.drag-over {
    border-color: var(--teal);
    background: rgba(0,201,141,0.02);
}

.upload-icon {
    width: 64px;
    height: 64px;
    background: rgba(0,201,141,0.08);
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
}

.upload-icon i {
    font-size: 28px;
    color: var(--teal);
}

.upload-title {
    font-weight: 600;
    color: var(--navy);
    margin-bottom: 0.5rem;
}

.upload-hint {
    font-size: 0.75rem;
    color: #94a3b8;
}

.file-input {
    display: none;
}

.selected-file {
    margin-top: 1rem;
    padding: 0.5rem;
    background: #f8fafc;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.8rem;
    color: var(--teal);
}

/* Process Button */
.process-btn {
    width: 100%;
    padding: 1rem;
    background: linear-gradient(135deg, var(--teal), #00e0a8);
    border: none;
    border-radius: 16px;
    font-weight: 700;
    font-size: 1rem;
    color: var(--navy);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    cursor: pointer;
    transition: all 0.2s ease;
    margin-top: 1rem;
}

.process-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,201,141,0.3);
}

.process-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* ─────────────────────────────────────────────────────────────────
   RIGHT PANEL — Results Display
   ───────────────────────────────────────────────────────────── */
.demo-result-panel {
    background: linear-gradient(135deg, var(--navy) 0%, var(--deep) 100%);
    border-radius: 28px;
    padding: 2rem;
    border: 1px solid rgba(255, 255, 255, 0.08);
    position: relative;
    overflow: hidden;
}

.demo-result-panel::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -30%;
    width: 300px;
    height: 300px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(0,201,141,0.08) 0%, transparent 70%);
    pointer-events: none;
}

.result-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.result-header i {
    font-size: 1.5rem;
    color: var(--teal);
}

.result-header h3 {
    font-family: 'Syne', sans-serif;
    font-weight: 700;
    font-size: 1.2rem;
    color: #fff;
    margin: 0;
}

/* Result Stats */
.result-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.result-stat-card {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 16px;
    padding: 1rem;
    text-align: center;
    border: 1px solid rgba(255, 255, 255, 0.05);
}

.result-stat-card .stat-value {
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    font-size: 1.5rem;
    color: var(--teal);
}

.result-stat-card .stat-label {
    font-size: 0.7rem;
    color: var(--muted);
    margin-top: 0.3rem;
}

/* Transactions List */
.transactions-list {
    max-height: 300px;
    overflow-y: auto;
    margin-top: 1rem;
}

.transactions-list::-webkit-scrollbar {
    width: 4px;
}

.transactions-list::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 10px;
}

.transactions-list::-webkit-scrollbar-thumb {
    background: var(--teal);
    border-radius: 10px;
}

.transaction-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.8rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    transition: all 0.2s ease;
}

.transaction-item:hover {
    background: rgba(255, 255, 255, 0.03);
}

.transaction-date {
    font-size: 0.7rem;
    color: var(--muted);
    min-width: 70px;
}

.transaction-desc {
    font-size: 0.8rem;
    color: #fff;
    flex: 1;
    margin-left: 1rem;
}

.transaction-amount {
    font-weight: 600;
    font-size: 0.85rem;
}

.transaction-amount.credit {
    color: var(--teal);
}

.transaction-amount.debit {
    color: #f97316;
}

.empty-result {
    text-align: center;
    padding: 3rem;
    color: var(--muted);
}

.empty-result i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

/* Loading Animation */
.loading-spinner {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 2px solid rgba(0,0,0,0.1);
    border-top-color: var(--navy);
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Responsive */
@media (max-width: 1024px) {
    .demo-container {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }

    .demo-input-panel, .demo-result-panel {
        padding: 1.5rem;
    }
}

@media (max-width: 640px) {
    .live-demo {
        padding: 3rem 0;
    }

    .demo-container {
        padding: 0 1rem;
    }

    .result-stats {
        grid-template-columns: repeat(2, 1fr);
    }

    .upload-area {
        padding: 1.2rem;
    }
}
</style>

{{-- ================================================================
     LIVE DEMO SECTION
     ================================================================ --}}
<!--<section class="live-demo">-->
<!--    <div class="section-header">-->
<!--        <div class="demo-badge">-->
<!--            <i class="bi bi-play-circle-fill"></i>-->
<!--            <span>Live Demo</span>-->
<!--        </div>-->
<!--        <h2 class="demo-title">-->
<!--            Experience the <em>Precision</em><br>in Seconds.-->
<!--        </h2>-->
<!--        <p class="demo-subtitle">-->
<!--            Upload your bank statement to see how our platform processes transactions automatically.-->
<!--        </p>-->
<!--    </div>-->

<!--    <div class="demo-container">-->
<!--        {{-- LEFT PANEL: Input Form --}}-->
<!--        <div class="demo-input-panel">-->
<!--            {{-- Bank Selection --}}-->
<!--            <div class="input-group">-->
<!--                <div class="input-label">-->
<!--                    <i class="bi bi-bank2"></i>-->
<!--                    <span>Select Bank</span>-->
<!--                </div>-->
<!--                <select class="bank-select" id="bankSelect">-->
<!--                    <option value="">-- Select your bank --</option>-->
<!--                    <optgroup label="Public Sector Banks">-->
<!--                        <option value="SBI">State Bank of India (SBI)</option>-->
<!--                        <option value="PNB">Punjab National Bank (PNB)</option>-->
<!--                        <option value="BOB">Bank of Baroda (BOB)</option>-->
<!--                        <option value="Canara">Canara Bank</option>-->
<!--                        <option value="Union">Union Bank of India</option>-->
<!--                        <option value="Indian">Indian Bank</option>-->
<!--                        <option value="UCO">UCO Bank</option>-->
<!--                        <option value="Central">Central Bank of India</option>-->
<!--                        <option value="Bank of India">Bank of India</option>-->
<!--                        <option value="Indian Overseas">Indian Overseas Bank</option>-->
<!--                        <option value="Punjab & Sind">Punjab & Sind Bank</option>-->
<!--                    </optgroup>-->
<!--                    <optgroup label="Private Sector Banks">-->
<!--                        <option value="HDFC">HDFC Bank</option>-->
<!--                        <option value="ICICI">ICICI Bank</option>-->
<!--                        <option value="AXIS">Axis Bank</option>-->
<!--                        <option value="Kotak">Kotak Mahindra Bank</option>-->
<!--                        <option value="Yes">Yes Bank</option>-->
<!--                        <option value="IDFC">IDFC First Bank</option>-->
<!--                        <option value="RBL">RBL Bank</option>-->
<!--                        <option value="IndusInd">IndusInd Bank</option>-->
<!--                        <option value="Federal">Federal Bank</option>-->
<!--                        <option value="South Indian">South Indian Bank</option>-->
<!--                        <option value="Karur Vysya">Karur Vysya Bank</option>-->
<!--                        <option value="Dhanlaxmi">Dhanlaxmi Bank</option>-->
<!--                        <option value="Nainital">Nainital Bank</option>-->
<!--                        <option value="ESFB">ESFB</option>-->
<!--                    </optgroup>-->
<!--                    <optgroup label="Foreign Banks">-->
<!--                        <option value="HSBC">HSBC Bank</option>-->
<!--                        <option value="DBS">DBS Bank</option>-->
<!--                        <option value="Deutsche">Deutsche Bank</option>-->
<!--                        <option value="Barclays">Barclays Bank</option>-->
<!--                        <option value="SCB">Standard Chartered Bank</option>-->
<!--                        <option value="Citi">Citibank</option>-->
<!--                    </optgroup>-->
<!--                    <optgroup label="Regional Rural Banks">-->
<!--                        <option value="RRB">Regional Rural Banks</option>-->
<!--                        <option value="Grameen">Grameen Banks</option>-->
<!--                    </optgroup>-->
<!--                    <optgroup label="Cooperative Banks">-->
<!--                        <option value="Cooperative">Cooperative Banks</option>-->
<!--                        <option value="Saraswat">Saraswat Bank</option>-->
<!--                        <option value="Cosmos">Cosmos Bank</option>-->
<!--                    </optgroup>-->
<!--                </select>-->
<!--            </div>-->

<!--            {{-- File Upload Area --}}-->
<!--            <div class="input-group">-->
<!--                <div class="input-label">-->
<!--                    <i class="bi bi-file-pdf-fill"></i>-->
<!--                    <span>Upload Bank Statement</span>-->
<!--                </div>-->
<!--                <div class="upload-area" id="uploadArea">-->
<!--                    <div class="upload-icon">-->
<!--                        <i class="bi bi-cloud-upload"></i>-->
<!--                    </div>-->
<!--                    <div class="upload-title">Upload PDF</div>-->
<!--                    <div class="upload-hint">or, Drag & drop your files here</div>-->
<!--                    <input type="file" id="fileInput" class="file-input" accept=".pdf, .csv, .xlsx">-->
<!--                </div>-->
<!--                <div id="selectedFileDisplay" class="selected-file" style="display: none;">-->
<!--                    <i class="bi bi-file-earmark-pdf-fill"></i>-->
<!--                    <span id="fileName"></span>-->
<!--                </div>-->
<!--            </div>-->

<!--            {{-- Process Button --}}-->
<!--            <button class="process-btn" id="processBtn" disabled>-->
<!--                <i class="bi bi-lightning-charge-fill"></i>-->
<!--                Process Statement-->
<!--            </button>-->

<!--            <p style="font-size: 0.7rem; text-align: center; color: #94a3b8; margin-top: 1rem;">-->
<!--                <i class="bi bi-shield-check"></i> Your data is secure & encrypted. Demo only — no data stored.-->
<!--            </p>-->
<!--        </div>-->

<!--        {{-- RIGHT PANEL: Results --}}-->
<!--        <div class="demo-result-panel">-->
<!--            <div class="result-header">-->
<!--                <i class="bi bi-graph-up"></i>-->
<!--                <h3>Processing Results</h3>-->
<!--            </div>-->

<!--            <div id="resultContent">-->
<!--                {{-- Empty State --}}-->
<!--                <div class="empty-result">-->
<!--                    <i class="bi bi-cloud-arrow-up"></i>-->
<!--                    <p>Upload a bank statement<br>to see instant results</p>-->
<!--                    <small style="font-size: 0.7rem;">Supports PDF, Excel, CSV formats</small>-->
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->
<!--    </div>-->
<!--</section>-->

<script>
// ─────────────────────────────────────────────────────────────────
// LIVE DEMO JAVASCRIPT
// Simulates bank statement processing with realistic data
// ─────────────────────────────────────────────────────────────────
(function() {
    // const bankSelect = document.getElementById('bankSelect');
    const bankSelect = document.getElementById('bankSelect');
    const fileInput = document.getElementById('fileInput');
    const uploadArea = document.getElementById('uploadArea');
    const selectedFileDisplay = document.getElementById('selectedFileDisplay');
    const fileNameSpan = document.getElementById('fileName');
    const processBtn = document.getElementById('processBtn');
    const resultContent = document.getElementById('resultContent');

    let selectedFile = null;
    let selectedBank = null;

    // Update process button state
    function updateProcessButton() {
        processBtn.disabled = !(selectedBank && selectedFile);
    }

    // Bank selection change
    bankSelect.addEventListener('change', function() {
        selectedBank = this.value;
        updateProcessButton();
    });

    // File upload handling
    function handleFile(file) {
        if (file && (file.type === 'application/pdf' || file.name.endsWith('.pdf') ||
                     file.name.endsWith('.csv') || file.name.endsWith('.xlsx'))) {
            selectedFile = file;
            fileNameSpan.textContent = file.name;
            selectedFileDisplay.style.display = 'inline-flex';
            updateProcessButton();
        } else {
            alert('Please upload a valid PDF, CSV, or Excel file');
        }
    }

    fileInput.addEventListener('change', function(e) {
        if (e.target.files.length) {
            handleFile(e.target.files[0]);
        }
    });

    // Drag & drop handlers
    uploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('drag-over');
    });

    uploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.classList.remove('drag-over');
    });

    uploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('drag-over');
        if (e.dataTransfer.files.length) {
            handleFile(e.dataTransfer.files[0]);
        }
    });

    uploadArea.addEventListener('click', function() {
        fileInput.click();
    });

    // Generate random transaction data
    function generateTransactions(bankName) {
        const descriptions = [
            'GST Payment - March 2025', 'ITC Claim Received', 'TDS Payment',
            'Salary Processing', 'Vendor Payment - Office Supplies', 'Customer Receipt',
            'Rent Payment', 'Electricity Bill', 'Professional Fees', 'Software Subscription',
            'Marketing Expenses', 'Travel Reimbursement', 'Interest Received', 'Loan EMI',
            'Refund Processed', 'Commission Paid', 'Dividend Received', 'Insurance Premium'
        ];

        const transactions = [];
        const now = new Date();

        for (let i = 0; i < 12; i++) {
            const date = new Date(now);
            date.setDate(now.getDate() - i);
            const isCredit = Math.random() > 0.6;
            const amount = Math.floor(Math.random() * 50000) + 500;

            transactions.push({
                date: date.toLocaleDateString('en-IN'),
                description: descriptions[Math.floor(Math.random() * descriptions.length)],
                amount: amount,
                type: isCredit ? 'credit' : 'debit',
                gst: isCredit ? (amount * 0.18).toFixed(0) : null
            });
        }

        return transactions.sort((a,b) => new Date(b.date) - new Date(a.date));
    }

    // Calculate stats from transactions
    function calculateStats(transactions) {
        let totalCredit = 0;
        let totalDebit = 0;
        let gstCollected = 0;

        transactions.forEach(t => {
            if (t.type === 'credit') {
                totalCredit += t.amount;
                if (t.gst) gstCollected += parseFloat(t.gst);
            } else {
                totalDebit += t.amount;
            }
        });

        return {
            totalCredit: totalCredit.toLocaleString('en-IN'),
            totalDebit: totalDebit.toLocaleString('en-IN'),
            transactionCount: transactions.length,
            gstCollected: gstCollected.toLocaleString('en-IN')
        };
    }

    // Display results
    function displayResults(transactions, bankName, fileName) {
        const stats = calculateStats(transactions);

        let transactionsHtml = '';
        transactions.forEach(t => {
            transactionsHtml += `
                <div class="transaction-item">
                    <span class="transaction-date">${t.date}</span>
                    <span class="transaction-desc">${t.description}</span>
                    <span class="transaction-amount ${t.type}">
                        ${t.type === 'credit' ? '+' : '-'} ₹${t.amount.toLocaleString('en-IN')}
                    </span>
                </div>
            `;
        });

        const html = `
            <div class="result-stats">
                <div class="result-stat-card">
                    <div class="stat-value">${stats.transactionCount}</div>
                    <div class="stat-label">Transactions</div>
                </div>
                <div class="result-stat-card">
                    <div class="stat-value">₹${stats.totalCredit}</div>
                    <div class="stat-label">Total Credit</div>
                </div>
                <div class="result-stat-card">
                    <div class="stat-value">₹${stats.totalDebit}</div>
                    <div class="stat-label">Total Debit</div>
                </div>
            </div>

            <div style="margin-bottom: 1rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.75rem; color: var(--muted); margin-bottom: 0.5rem;">
                    <span><i class="bi bi-bank2"></i> ${bankName}</span>
                    <span><i class="bi bi-file-earmark-pdf"></i> ${fileName}</span>
                </div>
                <div style="background: rgba(0,201,141,0.1); border-radius: 12px; padding: 0.5rem 1rem; display: flex; justify-content: space-between;">
                    <span style="font-size: 0.75rem;">Estimated GST Collected</span>
                    <span style="font-weight: 700; color: var(--teal);">₹${stats.gstCollected}</span>
                </div>
            </div>

            <div style="font-size: 0.75rem; color: var(--muted); margin-bottom: 0.5rem;">
                <i class="bi bi-list-ul"></i> Transaction Details
            </div>
            <div class="transactions-list">
                ${transactionsHtml}
            </div>
        `;

        resultContent.innerHTML = html;
    }

    // Simulate loading and processing
    function simulateProcessing() {
        if (!selectedBank || !selectedFile) return;

        // Show loading state
        resultContent.innerHTML = `
            <div class="empty-result">
                <div class="loading-spinner" style="width: 40px; height: 40px; border-width: 3px;"></div>
                <p style="margin-top: 1rem;">Processing your statement...</p>
                <small>Analyzing transactions, extracting GST data</small>
            </div>
        `;

        processBtn.disabled = true;
        processBtn.innerHTML = '<span class="loading-spinner"></span> Processing...';

        // Simulate API delay
        setTimeout(() => {
            const bankName = bankSelect.options[bankSelect.selectedIndex]?.text || selectedBank;
            const transactions = generateTransactions(selectedBank);
            displayResults(transactions, bankName, selectedFile.name);

            processBtn.disabled = false;
            processBtn.innerHTML = '<i class="bi bi-lightning-charge-fill"></i> Process Statement';
        }, 2000);
    }

    processBtn.addEventListener('click', simulateProcessing);
})();
</script>



{{-- ══════════════════════════════════════
     §6  PRODUCTS
══════════════════════════════════════ --}}
{{-- ══════════════════════════════════════
     §6  CAPABILITIES SHOWCASE (Interactive Tabs)
     Replaces old "Products" section
══════════════════════════════════════ --}}
<style>
.cap-section{background:#fff;padding:96px 0;position:relative;overflow:hidden}
.cap-section::before{
    content:'';position:absolute;top:-100px;left:-100px;width:350px;height:350px;
    border-radius:50%;background:radial-gradient(circle,rgba(0,201,141,.05) 0%,transparent 70%);
    pointer-events:none;
}
.cap-header{text-align:center;max-width:680px;margin:0 auto 48px;position:relative;z-index:1}
.cap-eyebrow{
    display:inline-flex;align-items:center;gap:8px;font-size:11px;font-weight:700;
    letter-spacing:2.5px;text-transform:uppercase;color:var(--teal);margin-bottom:16px;
}
.cap-eyebrow::before{content:'';width:18px;height:2px;background:var(--teal);border-radius:2px}
.cap-title{
    font-family:'Syne',sans-serif;font-weight:800;font-size:clamp(28px,4vw,42px);
    line-height:1.2;color:var(--navy);margin-bottom:14px;
}
.cap-title em{color:var(--teal);font-style:normal}
.cap-sub{font-size:16px;line-height:1.7;color:#5a6874}

.cap-layout{
    display:grid;grid-template-columns:300px 1fr;gap:32px;
    max-width:1200px;margin:0 auto;position:relative;z-index:1;
}

/* LEFT: tab list */
.cap-tabs{display:flex;flex-direction:column;gap:10px}
.cap-tab{
    display:flex;align-items:center;gap:14px;padding:16px 18px;border-radius:14px;
    border:1.5px solid #eef2f8;background:#fafcff;cursor:pointer;
    transition:all .25s;text-align:left;
}
.cap-tab:hover{border-color:rgba(0,201,141,.35)}
.cap-tab.active{
    background:var(--navy);border-color:var(--navy);
    box-shadow:0 10px 28px rgba(10,22,40,.18);
}
.cap-tab-icon{
    width:40px;height:40px;flex-shrink:0;border-radius:10px;
    background:var(--teal-low);border:1px solid rgba(0,201,141,.2);
    display:flex;align-items:center;justify-content:center;color:var(--teal);font-size:18px;
    transition:all .25s;
}
.cap-tab.active .cap-tab-icon{background:rgba(0,201,141,.18);color:var(--teal)}
.cap-tab-label{font-family:'Syne',sans-serif;font-weight:700;font-size:14.5px;color:var(--navy)}
.cap-tab.active .cap-tab-label{color:#fff}
.cap-tab-tag{font-size:11px;color:#94a3b8;margin-top:2px}
.cap-tab.active .cap-tab-tag{color:var(--muted)}

/* RIGHT: content panel */
.cap-panel-wrap{
    background:linear-gradient(135deg,var(--navy) 0%,var(--deep) 100%);
    border-radius:24px;padding:40px;border:1px solid var(--border);
    position:relative;overflow:hidden;min-height:360px;
}
.cap-panel-wrap::before{
    content:'';position:absolute;top:-60px;right:-60px;width:220px;height:220px;
    border-radius:50%;background:radial-gradient(circle,rgba(0,201,141,.12) 0%,transparent 70%);
    pointer-events:none;
}
.cap-panel{display:none;position:relative;z-index:1;animation:capFade .35s ease}
.cap-panel.active{display:block}
@keyframes capFade{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}

.cap-panel-badge{
    display:inline-flex;align-items:center;gap:8px;background:rgba(0,201,141,.12);
    border:1px solid rgba(0,201,141,.25);border-radius:30px;padding:6px 16px;
    font-size:11px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;
    color:var(--teal);margin-bottom:18px;
}
.cap-panel-title{
    font-family:'Syne',sans-serif;font-weight:800;font-size:26px;color:#fff;
    margin-bottom:14px;line-height:1.3;
}
.cap-panel-title em{color:var(--teal);font-style:normal}
.cap-panel-desc{font-size:15px;line-height:1.75;color:var(--muted);margin-bottom:24px;max-width:560px}

.cap-panel-points{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.cap-point{
    display:flex;align-items:flex-start;gap:10px;background:rgba(255,255,255,.04);
    border:1px solid rgba(255,255,255,.06);border-radius:12px;padding:12px 14px;
}
.cap-point i{color:var(--teal);font-size:15px;margin-top:2px}
.cap-point span{font-size:13px;color:#e8f0fe;line-height:1.5}

/* Responsive */
@media(max-width:900px){
    .cap-layout{grid-template-columns:1fr;gap:20px}
    .cap-tabs{flex-direction:row;overflow-x:auto;padding-bottom:6px;gap:10px}
    .cap-tab{flex-direction:column;align-items:flex-start;min-width:180px}
    .cap-panel-wrap{padding:28px 22px}
    .cap-panel-points{grid-template-columns:1fr}
}
</style>

<section class="cap-section">
    <div class="gp-container">

        <div class="cap-header">
            <div class="cap-eyebrow" style="justify-content:center">How It Works</div>
            <h2 class="cap-title">One Platform, <em>Every GST Problem</em> Solved</h2>
            <p class="cap-sub">Whether you're a CA or an e-commerce seller — pick your workflow and see exactly how EcomgstPro handles it.</p>
        </div>

        <div class="cap-layout">

            {{-- TABS --}}
            <div class="cap-tabs" id="capTabs">
                <button class="cap-tab active" data-tab="recon">
                    <span class="cap-tab-icon"><i class="bi bi-arrow-left-right"></i></span>
                    <span>
                        <span class="cap-tab-label" style="display:block">2A/2B Reconciliation</span>
                        <span class="cap-tab-tag">CA & Tax Firms</span>
                    </span>
                </button>
                <button class="cap-tab" data-tab="ecom">
                    <span class="cap-tab-icon"><i class="bi bi-shop-window"></i></span>
                    <span>
                        <span class="cap-tab-label" style="display:block">E-Commerce Data Fix</span>
                        <span class="cap-tab-tag">Amazon / Flipkart / Meesho Sellers</span>
                    </span>
                </button>
                <button class="cap-tab" data-tab="filing">
                    <span class="cap-tab-icon"><i class="bi bi-send-check-fill"></i></span>
                    <span>
                        <span class="cap-tab-label" style="display:block">GSTR Filing Suite</span>
                        <span class="cap-tab-tag">Everyone</span>
                    </span>
                </button>
                <button class="cap-tab" data-tab="itc">
                    <span class="cap-tab-icon"><i class="bi bi-graph-up"></i></span>
                    <span>
                        <span class="cap-tab-label" style="display:block">ITC Optimiser</span>
                        <span class="cap-tab-tag">Finance Teams</span>
                    </span>
                </button>
            </div>

            {{-- PANEL --}}
            <div class="cap-panel-wrap">

                <div class="cap-panel active" data-panel="recon">
                    <div class="cap-panel-badge"><i class="bi bi-shield-check"></i> Zero Missed Invoices</div>
                    <h3 class="cap-panel-title">Match your purchase register against <em>GSTR-2A/2B</em> in seconds</h3>
                    <p class="cap-panel-desc">Built for CAs and tax firms — upload invoices in bulk and the system automatically flags mismatches, excess claims, and reversals. No more manual line-by-line matching.</p>
                    <div class="cap-panel-points">
                        <div class="cap-point"><i class="bi bi-check-circle-fill"></i><span>Auto-match unlimited invoices</span></div>
                        <div class="cap-point"><i class="bi bi-check-circle-fill"></i><span>Mismatch & excess ITC flags</span></div>
                        <div class="cap-point"><i class="bi bi-check-circle-fill"></i><span>Vendor-wise reconciliation report</span></div>
                        <div class="cap-point"><i class="bi bi-check-circle-fill"></i><span>Multi-GSTIN client management</span></div>
                    </div>
                </div>

                <div class="cap-panel" data-panel="ecom">
                    <div class="cap-panel-badge"><i class="bi bi-cpu"></i> Marketplace Reports, Auto-Fixed</div>
                    <h3 class="cap-panel-title">Turn Amazon, Flipkart & Meesho <em>reports</em> into clean GSTR-1 data</h3>
                    <p class="cap-panel-desc">Upload B2C/B2B MTR reports directly — GSTIN validation, quantity parsing, and state-code normalization all happen automatically. TCS reconciliation and multi-state supply are handled out of the box.</p>
                    <div class="cap-panel-points">
                        <div class="cap-point"><i class="bi bi-check-circle-fill"></i><span>Multi-platform file support</span></div>
                        <div class="cap-point"><i class="bi bi-check-circle-fill"></i><span>Auto GSTIN & state validation</span></div>
                        <div class="cap-point"><i class="bi bi-check-circle-fill"></i><span>TCS credit matching</span></div>
                        <div class="cap-point"><i class="bi bi-check-circle-fill"></i><span>Portal-ready JSON export</span></div>
                    </div>
                </div>

                <div class="cap-panel" data-panel="filing">
                    <div class="cap-panel-badge"><i class="bi bi-file-earmark-check-fill"></i> Filing in Minutes, Not Days</div>
                    <h3 class="cap-panel-title">File GSTR-1, 3B, 9 & 9C <em>from one dashboard</em></h3>
                    <p class="cap-panel-desc">As soon as your data is prepared, built-in validation checks and auto error-correction take over. Review the return, then push it directly to the GST portal in one click.</p>
                    <div class="cap-panel-points">
                        <div class="cap-point"><i class="bi bi-check-circle-fill"></i><span>End-to-end return preparation</span></div>
                        <div class="cap-point"><i class="bi bi-check-circle-fill"></i><span>Built-in error validation</span></div>
                        <div class="cap-point"><i class="bi bi-check-circle-fill"></i><span>Direct portal push</span></div>
                        <div class="cap-point"><i class="bi bi-check-circle-fill"></i><span>Batch filing history tracking</span></div>
                    </div>
                </div>

                <div class="cap-panel" data-panel="itc">
                    <div class="cap-panel-badge"><i class="bi bi-currency-rupee"></i> ₹50Cr+ ITC Recovered</div>
                    <h3 class="cap-panel-title">Find unclaimed ITC <em>and claim it</em> before the deadline</h3>
                    <p class="cap-panel-desc">Our AI engine highlights every missed invoice and unreconciled purchase, helping you file revised claims within the time limit — before the credit is lost for good.</p>
                    <div class="cap-panel-points">
                        <div class="cap-point"><i class="bi bi-check-circle-fill"></i><span>AI-based ITC gap detection</span></div>
                        <div class="cap-point"><i class="bi bi-check-circle-fill"></i><span>Deadline alerts</span></div>
                        <div class="cap-point"><i class="bi bi-check-circle-fill"></i><span>Revised claim assistance</span></div>
                        <div class="cap-point"><i class="bi bi-check-circle-fill"></i><span>Live liability dashboard</span></div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<script>
(function(){
    const tabs = document.querySelectorAll('#capTabs .cap-tab');
    const panels = document.querySelectorAll('.cap-panel');
    tabs.forEach(tab => {
        tab.addEventListener('click', function(){
            const target = this.dataset.tab;
            tabs.forEach(t => t.classList.remove('active'));
            panels.forEach(p => p.classList.remove('active'));
            this.classList.add('active');
            document.querySelector('.cap-panel[data-panel="'+target+'"]').classList.add('active');
        });
    });
})();
</script>


{{-- ══════════════════════════════════════
     §7  PRICING TEASER
══════════════════════════════════════ --}}
<section class="pricing gp-section">
    <div class="gp-container">
        <div class="text-center">
            <div class="gp-eyebrow">Simple Pricing</div>
            <h2 class="gp-h2" style="margin:0 auto 12px;text-align:center">Transparent plans, <em>no surprises</em></h2>
            <p class="gp-lead" style="margin:0 auto;text-align:center">Start free. Upgrade when you're ready. Cancel anytime.</p>
        </div>
        <div class="pricing-grid">

            <div class="price-card">
                <div class="price-name">Starter</div>
                <div class="price-amt"><sup>₹</sup>0</div>
                <div class="price-per">Free forever</div>
                <div class="price-divider"></div>
                <ul class="price-feats">
                    <li><i class="bi bi-check-circle-fill"></i> 1 GSTIN</li>
                    <li><i class="bi bi-check-circle-fill"></i> 100 invoices/month</li>
                    <li><i class="bi bi-check-circle-fill"></i> GSTR-1 & 3B filing</li>
                    <li><i class="bi bi-check-circle-fill"></i> Email support</li>
                </ul>
                <a href="{{ route('register') }}" class="btn-price btn-price-outline">Get Started Free</a>
            </div>

            <div class="price-card popular">
                <span class="popular-badge">Most Popular</span>
                <div class="price-name">Professional</div>
                <div class="price-amt"><sup>₹</sup>999</div>
                <div class="price-per">per GSTIN / month</div>
                <div class="price-divider"></div>
                <ul class="price-feats">
                    <li><i class="bi bi-check-circle-fill"></i> Up to 5 GSTINs</li>
                    <li><i class="bi bi-check-circle-fill"></i> Unlimited invoices</li>
                    <li><i class="bi bi-check-circle-fill"></i> 2A/2B Reconciliation</li>
                    <li><i class="bi bi-check-circle-fill"></i> E-Commerce module</li>
                    <li><i class="bi bi-check-circle-fill"></i> Priority support</li>
                </ul>
                <a href="{{ route('register') }}" class="btn-price btn-price-solid">Start Free Trial</a>
            </div>

            <div class="price-card">
                <div class="price-name">Enterprise</div>
                <div class="price-amt" style="font-size:32px;padding-top:6px">Custom</div>
                <div class="price-per">For large businesses & CAs</div>
                <div class="price-divider"></div>
                <ul class="price-feats">
                    <li><i class="bi bi-check-circle-fill"></i> Unlimited GSTINs</li>
                    <li><i class="bi bi-check-circle-fill"></i> Dedicated account manager</li>
                    <li><i class="bi bi-check-circle-fill"></i> API access</li>
                    <li><i class="bi bi-check-circle-fill"></i> White-label option</li>
                    <li><i class="bi bi-check-circle-fill"></i> SLA-backed support</li>
                </ul>
                <a href="#" class="btn-price btn-price-outline">Contact Sales</a>
            </div>

        </div>
    </div>
</section>


{{-- ═══════════════════════════════════════════════════════════════════
     TESTIMONIAL SECTION — Single Row Infinite Scroll
     Purpose: Showcase customer feedback with single-direction infinite scroll
     Style: Clean white background with modern review cards
     Features: Auto-scrolling (single row), pause on hover, rating stars, avatars
══════════════════════════════════════════════════════════════════ --}}

<style>
/* ─────────────────────────────────────────────────────────────────
   TESTIMONIAL SECTION STYLES
   Single Row Infinite Scroll Marquee
   ───────────────────────────────────────────────────────────── */
.testimonial-section {
    background: #ffffff;
    position: relative;
    overflow: hidden;
    padding: 5rem 0;
}

/* Decorative elements */
.testimonial-section::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, rgba(0,201,141,0.03) 0%, transparent 70%);
    pointer-events: none;
}

.testimonial-section::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 300px;
    height: 300px;
    background: radial-gradient(circle, rgba(0,170,255,0.03) 0%, transparent 70%);
    pointer-events: none;
}

/* Header Styles */
.testimonial-header {
    text-align: center;
    margin-bottom: 3rem;
    position: relative;
    z-index: 1;
}

.testimonial-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(0,201,141,0.08);
    border: 1px solid rgba(0,201,141,0.15);
    border-radius: 40px;
    padding: 6px 18px;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: var(--teal);
    margin-bottom: 1rem;
}

.testimonial-badge i {
    font-size: 12px;
}

.testimonial-title {
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    font-size: clamp(28px, 4vw, 42px);
    color: var(--navy);
    margin-bottom: 1rem;
}

.testimonial-title em {
    color: var(--teal);
    font-style: normal;
}

.testimonial-subtitle {
    font-size: 1rem;
    color: #5a6874;
    max-width: 600px;
    margin: 0 auto;
}

/* Rating stars summary */
.rating-summary {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    margin-top: 1.5rem;
    flex-wrap: wrap;
}

.rating-stars {
    display: flex;
    gap: 4px;
}

.rating-stars i {
    color: #fbbf24;
    font-size: 1rem;
}

.rating-score {
    font-weight: 700;
    font-size: 1.1rem;
    color: var(--navy);
}

.rating-count {
    font-size: 0.8rem;
    color: #94a3b8;
}

/* ─────────────────────────────────────────────────────────────────
   SINGLE MARQUEE CONTAINER
   ───────────────────────────────────────────────────────────── */
.testimonial-marquee-wrapper {
    width: 100%;
    overflow: hidden;
    position: relative;
    padding: 1rem 0;
}

/* Gradient masks on edges */
.testimonial-marquee-wrapper::before,
.testimonial-marquee-wrapper::after {
    content: '';
    position: absolute;
    top: 0;
    width: 100px;
    height: 100%;
    z-index: 2;
    pointer-events: none;
}

.testimonial-marquee-wrapper::before {
    left: 0;
    background: linear-gradient(to right, #ffffff, transparent);
}

.testimonial-marquee-wrapper::after {
    right: 0;
    background: linear-gradient(to left, #ffffff, transparent);
}

/* Row container - Single Row */
.testimonial-row {
    display: flex;
    align-items: center;
    white-space: nowrap;
    will-change: transform;
}

/* Track for infinite scroll */
.testimonial-track {
    display: inline-flex;
    align-items: center;
    gap: 1.5rem;
    padding: 0 1rem;
    animation: scrollTestimonial 40s linear infinite;
}

/* Pause animation on hover */
.testimonial-row:hover .testimonial-track {
    animation-play-state: paused;
}

/* Individual Review Card */
.review-card {
    background: #f8fafc;
    border: 1px solid #eef2f8;
    border-radius: 20px;
    padding: 1.5rem;
    width: 340px;
    white-space: normal;
    transition: all 0.3s ease;
    display: inline-block;
    vertical-align: top;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
}

.review-card:hover {
    transform: translateY(-4px);
    border-color: rgba(0,201,141,0.2);
    box-shadow: 0 12px 25px -8px rgba(0, 0, 0, 0.08);
}

/* Card Header (Avatar + Name) */
.review-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
}

.review-avatar {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, var(--teal), var(--sky));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-weight: 700;
    font-size: 1.2rem;
    flex-shrink: 0;
}

.reviewer-info h4 {
    font-family: 'Syne', sans-serif;
    font-weight: 700;
    font-size: 1rem;
    color: var(--navy);
    margin: 0 0 2px 0;
}

.reviewer-info .reviewer-title {
    font-size: 0.7rem;
    color: var(--teal);
    font-weight: 600;
}

/* Review Rating Stars */
.review-rating {
    display: flex;
    gap: 3px;
    margin-bottom: 0.8rem;
}

.review-rating i {
    font-size: 0.8rem;
    color: #fbbf24;
}

/* Review Text */
.review-text {
    font-size: 0.85rem;
    line-height: 1.65;
    color: #4a5568;
    margin-bottom: 1rem;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Review Footer */
.review-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 0.7rem;
    color: #94a3b8;
    border-top: 1px solid #eef2f8;
    padding-top: 0.8rem;
    margin-top: 0.5rem;
}

.verified-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    color: var(--teal);
    font-size: 0.65rem;
}

.platform-tag {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: rgba(0,201,141,0.08);
    padding: 2px 8px;
    border-radius: 20px;
    font-size: 0.6rem;
    font-weight: 600;
    color: var(--teal);
}

/* Animation */
@keyframes scrollTestimonial {
    0% {
        transform: translateX(0);
    }
    100% {
        transform: translateX(-50%);
    }
}

/* Responsive */
@media (max-width: 768px) {
    .testimonial-section {
        padding: 3rem 0;
    }

    .review-card {
        width: 280px;
        padding: 1.2rem;
    }

    .testimonial-marquee-wrapper::before,
    .testimonial-marquee-wrapper::after {
        width: 60px;
    }
}

@media (max-width: 640px) {
    .review-card {
        width: 260px;
    }

    .rating-summary {
        gap: 0.5rem;
    }
}
</style>



<!-- why choose use section satrt from here  -->

{{-- ═══════════════════════════════════════════════════════════════════
     BENEFIT CARDS SECTION — "You're in the Right Place"
     4 cards with inline SVG human figure illustrations
     Location: After stats-bar section in home.blade.php
══════════════════════════════════════════════════════════════════ --}}

<style>
.gp-benefit-section {
    padding: 5rem 0;
    background: #ffffff;
    position: relative;
    overflow: hidden;
}
.gp-benefit-section::before {
    content: '';
    position: absolute;
    top: -80px; right: -80px;
    width: 350px; height: 350px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(0,201,141,.04) 0%, transparent 70%);
    pointer-events: none;
}
.benefit-heading-wrap {
    text-align: center;
    margin-bottom: 3rem;
}
.benefit-heading-wrap .gp-eyebrow {
    justify-content: center;
}
.benefit-heading-wrap .gp-h2 {
    text-align: center;
    margin: 0 auto 14px;
}
.benefit-heading-wrap .gp-lead {
    text-align: center;
    margin: 0 auto;
}
.benefit-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}
.bcard {
    background: #eef5ff;
    border-radius: 20px;
    padding: 1.6rem;
    display: flex;
    gap: 1rem;
    align-items: center;
    border: 1px solid #d8eaff;
    transition: transform .25s, box-shadow .25s, border-color .25s;
    position: relative;
    overflow: hidden;
}
.bcard::after {
    content: '';
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--teal), var(--sky));
    transform: scaleX(0);
    transform-origin: left;
    transition: transform .3s;
}
.bcard:hover {
    transform: translateY(-5px);
    box-shadow: 0 14px 36px rgba(0, 201, 141, .12);
    border-color: rgba(0, 201, 141, .4);
}
.bcard:hover::after {
    transform: scaleX(1);
}
.bcard-illo {
    flex-shrink: 0;
    width: 130px;
    height: 110px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.bcard-body h3 {
    font-family: 'Syne', sans-serif;
    font-weight: 700;
    font-size: 17px;
    color: var(--navy);
    margin: 0 0 8px;
}
.bcard-body p {
    font-size: 14px;
    line-height: 1.75;
    color: #4a5568;
    margin: 0;
}

/* Responsive */
@media (max-width: 1024px) {
    .benefit-grid {
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }
    .bcard-illo {
        width: 110px;
        height: 95px;
    }
}
@media (max-width: 768px) {
    .gp-benefit-section {
        padding: 3rem 0;
    }
    .benefit-grid {
        grid-template-columns: 1fr;
        gap: 14px;
    }
    .bcard {
        flex-direction: row;
        align-items: center;
        padding: 1.2rem;
    }
    .bcard-illo {
        width: 100px;
        height: 88px;
    }
    .bcard-body h3 {
        font-size: 15px;
    }
    .bcard-body p {
        font-size: 13px;
    }
}
@media (max-width: 480px) {
    .bcard {
        flex-direction: column;
        align-items: flex-start;
    }
    .bcard-illo {
        width: 100%;
        height: 110px;
    }
}
</style>

<section class="gp-benefit-section">
    <div class="gp-container">

        <div class="benefit-heading-wrap">
            <div class="gp-eyebrow">Why EcomgstPro Works</div>
            <h2 class="gp-h2">
                You get it. You're in the <em>right place.</em><br>
                Here's how we help.
            </h2>
            <p class="gp-lead">
                Every feature is built around a real GST compliance frustration Indian businesses face — and solves it completely.
            </p>
        </div>

        <div class="benefit-grid">

            {{-- ── Card 1: Time Savings ── --}}
            <div class="bcard">
                <div class="bcard-illo">
                    <svg viewBox="0 0 130 110" fill="none" xmlns="http://www.w3.org/2000/svg" width="130" height="110">
                        <!-- floor shadow -->
                        <ellipse cx="65" cy="100" rx="55" ry="6" fill="#c7dcf8"/>
                        <!-- sofa base -->
                        <rect x="18" y="68" width="72" height="22" rx="8" fill="#2563eb"/>
                        <!-- sofa back -->
                        <rect x="18" y="62" width="72" height="12" rx="6" fill="#3b82f6"/>
                        <!-- sofa arms -->
                        <rect x="13" y="65" width="12" height="25" rx="5" fill="#1d4ed8"/>
                        <rect x="77" y="65" width="12" height="25" rx="5" fill="#1d4ed8"/>
                        <!-- laptop -->
                        <rect x="42" y="55" width="34" height="22" rx="3" fill="#1e293b"/>
                        <rect x="44" y="57" width="30" height="16" rx="2" fill="#38bdf8"/>
                        <!-- screen content lines -->
                        <rect x="47" y="60" width="10" height="2" rx="1" fill="#fff" opacity=".7"/>
                        <rect x="47" y="64" width="20" height="1.5" rx=".75" fill="#fff" opacity=".4"/>
                        <rect x="47" y="67" width="16" height="1.5" rx=".75" fill="#fff" opacity=".4"/>
                        <!-- laptop base -->
                        <rect x="42" y="77" width="34" height="3" rx="1.5" fill="#1e293b"/>
                        <!-- person body -->
                        <rect x="52" y="40" width="18" height="26" rx="6" fill="#fbbf24"/>
                        <!-- head -->
                        <circle cx="61" cy="33" r="11" fill="#fde68a"/>
                        <!-- hair -->
                        <path d="M50 28 Q61 18 72 28 Q72 22 61 20 Q50 22 50 28Z" fill="#1e293b"/>
                        <!-- eyes -->
                        <circle cx="57" cy="32" r="1.5" fill="#92400e"/>
                        <circle cx="65" cy="32" r="1.5" fill="#92400e"/>
                        <!-- smile -->
                        <path d="M57 37 Q61 40 65 37" stroke="#92400e" stroke-width="1.2" fill="none" stroke-linecap="round"/>
                        <!-- arms -->
                        <path d="M52 48 Q44 52 46 58" stroke="#fbbf24" stroke-width="5" stroke-linecap="round"/>
                        <path d="M70 48 Q78 52 76 58" stroke="#fbbf24" stroke-width="5" stroke-linecap="round"/>
                        <!-- clock top right -->
                        <circle cx="108" cy="24" r="18" fill="#fff" stroke="#3b82f6" stroke-width="2"/>
                        <circle cx="108" cy="24" r="14" fill="#eff6ff"/>
                        <circle cx="108" cy="24" r="2" fill="#2563eb"/>
                        <!-- clock hands -->
                        <line x1="108" y1="24" x2="108" y2="14" stroke="#1d4ed8" stroke-width="2" stroke-linecap="round"/>
                        <line x1="108" y1="24" x2="116" y2="28" stroke="#1d4ed8" stroke-width="1.5" stroke-linecap="round"/>
                        <!-- clock ticks -->
                        <line x1="108" y1="12" x2="108" y2="14" stroke="#3b82f6" stroke-width="1.5"/>
                        <line x1="108" y1="34" x2="108" y2="36" stroke="#3b82f6" stroke-width="1.5"/>
                        <line x1="96"  y1="24" x2="98"  y2="24" stroke="#3b82f6" stroke-width="1.5"/>
                        <line x1="118" y1="24" x2="120" y2="24" stroke="#3b82f6" stroke-width="1.5"/>
                        <!-- speed dash lines -->
                        <line x1="90" y1="18" x2="84" y2="15" stroke="#93c5fd" stroke-width="1.2" stroke-dasharray="2 2"/>
                        <line x1="88" y1="24" x2="82" y2="24" stroke="#93c5fd" stroke-width="1.2" stroke-dasharray="2 2"/>
                        <!-- plant left -->
                        <rect x="8" y="80" width="8" height="14" rx="2" fill="#1d4ed8"/>
                        <ellipse cx="12" cy="76" rx="7" ry="10" fill="#16a34a"/>
                        <ellipse cx="7"  cy="72" rx="5" ry="7"  fill="#15803d"/>
                        <ellipse cx="17" cy="71" rx="5" ry="7"  fill="#15803d"/>
                    </svg>
                </div>
                <div class="bcard-body">
                    <h3>Significant Time Savings</h3>
                    <p>Manual GSTR-1 filing burns hours every cycle. EcomgstPro processes your full marketplace report in under 2 minutes — saving up to 95% of your time.</p>
                </div>
            </div>

            {{-- ── Card 2: Improved Accuracy ── --}}
            <div class="bcard">
                <div class="bcard-illo">
                    <svg viewBox="0 0 130 110" fill="none" xmlns="http://www.w3.org/2000/svg" width="130" height="110">
                        <!-- floor shadow -->
                        <ellipse cx="65" cy="100" rx="52" ry="5" fill="#c7dcf8"/>
                        <!-- desk top -->
                        <rect x="60" y="72" width="55" height="6" rx="3" fill="#1e293b"/>
                        <!-- desk legs -->
                        <rect x="70" y="78" width="5" height="20" rx="2" fill="#1e293b"/>
                        <rect x="100" y="78" width="5" height="20" rx="2" fill="#1e293b"/>
                        <!-- monitor -->
                        <rect x="65" y="44" width="45" height="30" rx="4" fill="#1e293b"/>
                        <rect x="67" y="46" width="41" height="24" rx="2" fill="#0ea5e9"/>
                        <!-- bullseye on screen -->
                        <circle cx="83" cy="58" r="10" fill="#fff" opacity=".25"/>
                        <circle cx="83" cy="58" r="7"  fill="#ef4444" opacity=".85"/>
                        <circle cx="83" cy="58" r="4"  fill="#fbbf24"/>
                        <circle cx="83" cy="58" r="1.5" fill="#fff"/>
                        <!-- checkmark on screen right -->
                        <path d="M98 52 L101 56 L108 49" stroke="#00c98d" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <!-- monitor stand -->
                        <rect x="84" y="74" width="10" height="5" rx="1" fill="#334155"/>
                        <rect x="80" y="79" width="18" height="3" rx="1.5" fill="#334155"/>
                        <!-- person body -->
                        <rect x="22" y="44" width="16" height="28" rx="6" fill="#fbbf24"/>
                        <!-- head -->
                        <circle cx="30" cy="36" r="11" fill="#fde68a"/>
                        <!-- hair -->
                        <path d="M19 31 Q30 21 41 31 Q41 24 30 22 Q19 24 19 31Z" fill="#7c2d12"/>
                        <!-- eyes -->
                        <circle cx="26" cy="35" r="1.5" fill="#92400e"/>
                        <circle cx="34" cy="35" r="1.5" fill="#92400e"/>
                        <!-- smile -->
                        <path d="M26 40 Q30 43 34 40" stroke="#92400e" stroke-width="1.2" fill="none" stroke-linecap="round"/>
                        <!-- arm pointing at screen -->
                        <path d="M38 54 Q52 56 62 58" stroke="#fbbf24" stroke-width="5" stroke-linecap="round"/>
                        <!-- left arm down -->
                        <path d="M22 52 Q14 56 15 64" stroke="#fbbf24" stroke-width="5" stroke-linecap="round"/>
                        <!-- legs -->
                        <rect x="24" y="72" width="7" height="20" rx="3" fill="#1d4ed8"/>
                        <rect x="33" y="72" width="7" height="20" rx="3" fill="#1d4ed8"/>
                        <!-- arrow hitting target -->
                        <line x1="122" y1="46" x2="95" y2="58" stroke="#ef4444" stroke-width="2" stroke-linecap="round"/>
                        <polygon points="95,58 101,53 100,61" fill="#ef4444"/>
                        <!-- sparkle star -->
                        <path d="M114 36 L116 41 L121 43 L116 45 L114 50 L112 45 L107 43 L112 41Z" fill="#fbbf24"/>
                    </svg>
                </div>
                <div class="bcard-body">
                    <h3>Improved Accuracy</h3>
                    <p>Typos and invoice mismatches trigger GST notices. Our AI validates every entry against GSTR-2A/2B and catches all errors before you hit submit.</p>
                </div>
            </div>

            {{-- ── Card 3: Easy to Use ── --}}
            <div class="bcard">
                <div class="bcard-illo">
                    <svg viewBox="0 0 130 110" fill="none" xmlns="http://www.w3.org/2000/svg" width="130" height="110">
                        <!-- floor shadow -->
                        <ellipse cx="52" cy="103" rx="40" ry="5" fill="#c7dcf8"/>
                        <!-- legs cross-legged -->
                        <path d="M26 90 Q34 98 44 94 Q54 90 60 94" stroke="#1d4ed8" stroke-width="7" stroke-linecap="round" fill="none"/>
                        <!-- body -->
                        <rect x="34" y="58" width="20" height="30" rx="7" fill="#fbbf24"/>
                        <!-- head -->
                        <circle cx="44" cy="48" r="13" fill="#fde68a"/>
                        <!-- hair -->
                        <path d="M31 43 Q44 31 57 43 Q56 36 44 33 Q32 36 31 43Z" fill="#1e293b"/>
                        <!-- eyes -->
                        <circle cx="39" cy="47" r="1.8" fill="#92400e"/>
                        <circle cx="49" cy="47" r="1.8" fill="#92400e"/>
                        <!-- smile -->
                        <path d="M39 53 Q44 57 49 53" stroke="#92400e" stroke-width="1.3" fill="none" stroke-linecap="round"/>
                        <!-- laptop on lap -->
                        <rect x="27" y="76" width="35" height="20" rx="3" fill="#1e293b"/>
                        <rect x="29" y="78" width="31" height="14" rx="2" fill="#0f172a"/>
                        <!-- bar chart on screen -->
                        <rect x="32" y="86" width="4" height="4"  rx="1" fill="#00c98d"/>
                        <rect x="38" y="83" width="4" height="7"  rx="1" fill="#38bdf8"/>
                        <rect x="44" y="81" width="4" height="9"  rx="1" fill="#a78bfa"/>
                        <rect x="50" y="84" width="4" height="6"  rx="1" fill="#fbbf24"/>
                        <!-- laptop base -->
                        <rect x="27" y="96" width="35" height="3" rx="1.5" fill="#334155"/>
                        <!-- arms -->
                        <path d="M34 68 Q26 76 30 82" stroke="#fbbf24" stroke-width="5" stroke-linecap="round"/>
                        <path d="M54 68 Q62 76 59 82" stroke="#fbbf24" stroke-width="5" stroke-linecap="round"/>
                        <!-- pie chart floating right -->
                        <circle cx="100" cy="52" r="26" fill="#e0f2fe"/>
                        <path d="M100 52 L100 26 A26 26 0 0 1 126 52Z" fill="#3b82f6"/>
                        <path d="M100 52 L126 52 A26 26 0 0 1 87 75Z" fill="#00c98d"/>
                        <path d="M100 52 L87 75  A26 26 0 0 1 74 52Z" fill="#fbbf24"/>
                        <path d="M100 52 L74 52  A26 26 0 0 1 100 26Z" fill="#f87171"/>
                        <!-- pie center hole -->
                        <circle cx="100" cy="52" r="11" fill="#fff"/>
                        <!-- connect line -->
                        <path d="M62 74 Q80 64 74 52" stroke="#93c5fd" stroke-width="1.2" stroke-dasharray="3 2" fill="none"/>
                    </svg>
                </div>
                <div class="bcard-body">
                    <h3>Effortlessly Easy to Use</h3>
                    <p>No CA degree needed. Upload your file, pick your platform, and download portal-ready JSON. Three steps, zero training — anyone can do it.</p>
                </div>
            </div>

            {{-- ── Card 4: Increased Efficiency ── --}}
            <div class="bcard">
                <div class="bcard-illo">
                    <svg viewBox="0 0 130 110" fill="none" xmlns="http://www.w3.org/2000/svg" width="130" height="110">
                        <!-- floor shadow -->
                        <ellipse cx="65" cy="104" rx="52" ry="5" fill="#c7dcf8"/>
                        <!-- bar chart columns (stairs) -->
                        <rect x="10" y="80" width="16" height="22" rx="2" fill="#a78bfa"/>
                        <rect x="30" y="68" width="16" height="34" rx="2" fill="#7c3aed"/>
                        <rect x="50" y="54" width="16" height="48" rx="2" fill="#00c98d"/>
                        <!-- flag on tallest bar -->
                        <rect x="64" y="40" width="2" height="17" rx="1" fill="#1e293b"/>
                        <path d="M66 40 L76 45 L66 50Z" fill="#ef4444"/>
                        <!-- trend line dots -->
                        <path d="M18 79 L38 67 L58 53" stroke="#1e293b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" stroke-dasharray="3 2"/>
                        <circle cx="18" cy="79" r="3" fill="#1e293b"/>
                        <circle cx="38" cy="67" r="3" fill="#1e293b"/>
                        <circle cx="58" cy="53" r="3" fill="#1e293b"/>
                        <!-- person legs -->
                        <rect x="57" y="82" width="6" height="16" rx="3" fill="#1d4ed8"/>
                        <rect x="65" y="82" width="6" height="16" rx="3" fill="#1d4ed8"/>
                        <!-- person body -->
                        <rect x="54" y="58" width="19" height="26" rx="7" fill="#fbbf24"/>
                        <!-- head -->
                        <circle cx="63" cy="48" r="12" fill="#fde68a"/>
                        <!-- hair -->
                        <path d="M51 43 Q63 33 75 43 Q74 36 63 34 Q52 36 51 43Z" fill="#92400e"/>
                        <!-- eyes -->
                        <circle cx="58" cy="47" r="1.8" fill="#78350f"/>
                        <circle cx="68" cy="47" r="1.8" fill="#78350f"/>
                        <!-- happy mouth -->
                        <path d="M58 53 Q63 57 68 53" stroke="#78350f" stroke-width="1.3" fill="none" stroke-linecap="round"/>
                        <!-- arm raised with rocket -->
                        <path d="M73 66 Q84 56 92 46" stroke="#fbbf24" stroke-width="5" stroke-linecap="round"/>
                        <!-- left arm down -->
                        <path d="M54 66 Q46 72 47 80" stroke="#fbbf24" stroke-width="5" stroke-linecap="round"/>
                        <!-- rocket body -->
                        <ellipse cx="98" cy="37" rx="7" ry="12" fill="#ef4444" transform="rotate(-30 98 37)"/>
                        <!-- rocket nose fin left -->
                        <path d="M91 46 L86 54 L97 50Z" fill="#fbbf24"/>
                        <!-- rocket nose fin right -->
                        <path d="M103 30 L111 22 L107 34Z" fill="#fbbf24"/>
                        <!-- rocket window -->
                        <circle cx="97" cy="35" r="3" fill="#fff" opacity=".8"/>
                        <!-- rocket flame -->
                        <ellipse cx="89" cy="50" rx="3" ry="5" fill="#fb923c" opacity=".85" transform="rotate(-30 89 50)"/>
                        <ellipse cx="89" cy="50" rx="1.5" ry="3" fill="#fde68a" transform="rotate(-30 89 50)"/>
                        <!-- speed lines -->
                        <line x1="107" y1="40" x2="122" y2="38" stroke="#fcd34d" stroke-width="1.5" stroke-linecap="round"/>
                        <line x1="108" y1="47" x2="123" y2="49" stroke="#fcd34d" stroke-width="1.5" stroke-linecap="round"/>
                        <line x1="105" y1="33" x2="118" y2="27" stroke="#fcd34d" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                </div>
                <div class="bcard-body">
                    <h3>Increased Efficiency</h3>
                    <p>From Amazon report to filed GSTR-1 — what took a full day now runs in one automated batch. Handle 10× more clients without any extra effort.</p>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- why choose use section end from here  -->


{{-- ================================================================
     TESTIMONIAL SECTION — Single Row Infinite Scroll
     ================================================================ --}}
<section class="testimonial-section">
    <div class="testimonial-header">
        <div class="testimonial-badge">
            <i class="bi bi-chat-quote-fill"></i>
            <span>Trusted by 10,000+ Businesses</span>
        </div>
        <h2 class="testimonial-title">
            What Our <em>Customers</em> Say
        </h2>
        <p class="testimonial-subtitle">
            Join thousands of Indian businesses that have simplified their GST compliance with EcomgstPro
        </p>

        {{-- Rating Summary --}}
        <div class="rating-summary">
            <div class="rating-stars">
                <i class="bi bi-star-fill"></i>
                <i class="bi bi-star-fill"></i>
                <i class="bi bi-star-fill"></i>
                <i class="bi bi-star-fill"></i>
                <i class="bi bi-star-fill"></i>
            </div>
            <span class="rating-score">4.9 out of 5</span>
            <span class="rating-count">(Based on 2,847+ reviews)</span>
        </div>
    </div>

    {{-- SINGLE ROW: Scrolling Left --}}
    <div class="testimonial-marquee-wrapper">
        <div class="testimonial-row">
            <div class="testimonial-track">

                {{-- Review 1 --}}
                <div class="review-card">
                    <div class="review-header">
                        <div class="review-avatar">RK</div>
                        <div class="reviewer-info">
                            <h4>Rajesh Kumar</h4>
                            <div class="reviewer-title">CA & Tax Consultant</div>
                        </div>
                    </div>
                    <div class="review-rating">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <p class="review-text">
                        "EcomgstPro has transformed how I handle my clients' GST filings. The auto-reconciliation feature saves me at least 15 hours every month. Highly recommended for every CA practice!"
                    </p>
                    <div class="review-footer">
                        <span class="verified-badge"><i class="bi bi-patch-check-fill"></i> Verified Client</span>
                        <span class="platform-tag"><i class="bi bi-briefcase-fill"></i> CA Practice</span>
                    </div>
                </div>

                {{-- Review 2 --}}
                <div class="review-card">
                    <div class="review-header">
                        <div class="review-avatar" style="background: linear-gradient(135deg, #00aaff, #00c98d);">PS</div>
                        <div class="reviewer-info">
                            <h4>Priya Sharma</h4>
                            <div class="reviewer-title">E-commerce Seller</div>
                        </div>
                    </div>
                    <div class="review-rating">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <p class="review-text">
                        "As an Amazon seller, GSTR-1 filing was a nightmare. EcomgstPro's e-commerce module automatically generates returns from marketplace reports. Best decision ever!"
                    </p>
                    <div class="review-footer">
                        <span class="verified-badge"><i class="bi bi-patch-check-fill"></i> Verified Client</span>
                        <span class="platform-tag"><i class="bi bi-amazon"></i> Amazon Seller</span>
                    </div>
                </div>

                {{-- Review 3 --}}
                <div class="review-card">
                    <div class="review-header">
                        <div class="review-avatar">AM</div>
                        <div class="reviewer-info">
                            <h4>Amit Mehta</h4>
                            <div class="reviewer-title">Finance Director</div>
                        </div>
                    </div>
                    <div class="review-rating">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <p class="review-text">
                        "We manage 15+ GSTINs across our group companies. EcomgstPro's multi-GSTIN dashboard gives us complete visibility. The ITC optimization alone paid for the subscription."
                    </p>
                    <div class="review-footer">
                        <span class="verified-badge"><i class="bi bi-patch-check-fill"></i> Enterprise Client</span>
                        <span class="platform-tag"><i class="bi bi-building"></i> Manufacturing</span>
                    </div>
                </div>

                {{-- Review 4 --}}
                <div class="review-card">
                    <div class="review-header">
                        <div class="review-avatar" style="background: linear-gradient(135deg, #00c98d, #00aaff);">SG</div>
                        <div class="reviewer-info">
                            <h4>Sunita Gupta</h4>
                            <div class="reviewer-title">Startup Founder</div>
                        </div>
                    </div>
                    <div class="review-rating">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <p class="review-text">
                        "Being a non-CA founder, GST compliance was scary. EcomgstPro's simple UI and helpful support team made it effortless. Now I file returns in under 10 minutes!"
                    </p>
                    <div class="review-footer">
                        <span class="verified-badge"><i class="bi bi-patch-check-fill"></i> Verified Client</span>
                        <span class="platform-tag"><i class="bi bi-rocket-takeoff"></i> Startup</span>
                    </div>
                </div>

                {{-- Review 5 --}}
                <div class="review-card">
                    <div class="review-header">
                        <div class="review-avatar">VK</div>
                        <div class="reviewer-info">
                            <h4>Vikram Singh</h4>
                            <div class="reviewer-title">Accountant</div>
                        </div>
                    </div>
                    <div class="review-rating">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <p class="review-text">
                        "Bank statement converter is a game changer! I used to spend hours manually entering bank entries. Now it's done in seconds. My employer is very happy."
                    </p>
                    <div class="review-footer">
                        <span class="verified-badge"><i class="bi bi-patch-check-fill"></i> Verified Client</span>
                        <span class="platform-tag"><i class="bi bi-bank2"></i> Accounting Firm</span>
                    </div>
                </div>

                {{-- Review 6 --}}
                <div class="review-card">
                    <div class="review-header">
                        <div class="review-avatar" style="background: linear-gradient(135deg, #00aaff, #00c98d);">NJ</div>
                        <div class="reviewer-info">
                            <h4>Neha Jain</h4>
                            <div class="reviewer-title">Flipkart Seller</div>
                        </div>
                    </div>
                    <div class="review-rating">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <p class="review-text">
                        "The TCS reconciliation feature is exactly what Flipkart sellers need. EcomgstPro automatically matches TCS credits with my returns. Saved me from many notices!"
                    </p>
                    <div class="review-footer">
                        <span class="verified-badge"><i class="bi bi-patch-check-fill"></i> Verified Client</span>
                        <span class="platform-tag"><i class="bi bi-basket3"></i> Flipkart Seller</span>
                    </div>
                </div>

                {{-- Review 7 --}}
                <div class="review-card">
                    <div class="review-header">
                        <div class="review-avatar">MS</div>
                        <div class="reviewer-info">
                            <h4>Manoj Sharma</h4>
                            <div class="reviewer-title">Tax Consultant</div>
                        </div>
                    </div>
                    <div class="review-rating">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <p class="review-text">
                        "I've tried multiple GST software. EcomgstPro is by far the most intuitive. The 2A/2B reconciliation is spot on, and their support team is exceptional."
                    </p>
                    <div class="review-footer">
                        <span class="verified-badge"><i class="bi bi-patch-check-fill"></i> Verified Client</span>
                        <span class="platform-tag"><i class="bi bi-calculator-fill"></i> Tax Practice</span>
                    </div>
                </div>

                {{-- Review 8 --}}
                <div class="review-card">
                    <div class="review-header">
                        <div class="review-avatar" style="background: linear-gradient(135deg, #00c98d, #00aaff);">RD</div>
                        <div class="reviewer-info">
                            <h4>Rahul Doshi</h4>
                            <div class="reviewer-title">CFO</div>
                        </div>
                    </div>
                    <div class="review-rating">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <p class="review-text">
                        "API integration with our ERP was seamless. Now our purchase data flows directly into EcomgstPro for reconciliation. We've recovered ₹12L+ in unclaimed ITC."
                    </p>
                    <div class="review-footer">
                        <span class="verified-badge"><i class="bi bi-patch-check-fill"></i> Enterprise Client</span>
                        <span class="platform-tag"><i class="bi bi-diagram-3-fill"></i> ERP Integrated</span>
                    </div>
                </div>

                {{-- Review 9 --}}
                <div class="review-card">
                    <div class="review-header">
                        <div class="review-avatar">PC</div>
                        <div class="reviewer-info">
                            <h4>Priyanka Choudhary</h4>
                            <div class="reviewer-title">Business Owner</div>
                        </div>
                    </div>
                    <div class="review-rating">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <p class="review-text">
                        "I love how simple EcomgstPro makes everything. From uploading purchase registers to filing GSTR-3B, everything is streamlined. Worth every rupee!"
                    </p>
                    <div class="review-footer">
                        <span class="verified-badge"><i class="bi bi-patch-check-fill"></i> Verified Client</span>
                        <span class="platform-tag"><i class="bi bi-store"></i> Retail Business</span>
                    </div>
                </div>

                {{-- Review 10 --}}
                <div class="review-card">
                    <div class="review-header">
                        <div class="review-avatar" style="background: linear-gradient(135deg, #00aaff, #00c98d);">AK</div>
                        <div class="reviewer-info">
                            <h4>Ankit Kapoor</h4>
                            <div class="reviewer-title">CA Firm Partner</div>
                        </div>
                    </div>
                    <div class="review-rating">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <p class="review-text">
                        "We've onboarded 50+ clients to EcomgstPro. The white-label option is perfect for CAs. Our clients love the seamless experience and we love the backend control."
                    </p>
                    <div class="review-footer">
                        <span class="verified-badge"><i class="bi bi-patch-check-fill"></i> Verified Client</span>
                        <span class="platform-tag"><i class="bi bi-people-fill"></i> CA Firm</span>
                    </div>
                </div>

                {{-- DUPLICATE SET for seamless infinite loop --}}
                <div class="review-card">
                    <div class="review-header">
                        <div class="review-avatar">RK</div>
                        <div class="reviewer-info">
                            <h4>Rajesh Kumar</h4>
                            <div class="reviewer-title">CA & Tax Consultant</div>
                        </div>
                    </div>
                    <div class="review-rating">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <p class="review-text">
                        "EcomgstPro has transformed how I handle my clients' GST filings. The auto-reconciliation feature saves me at least 15 hours every month."
                    </p>
                    <div class="review-footer">
                        <span class="verified-badge"><i class="bi bi-patch-check-fill"></i> Verified Client</span>
                        <span class="platform-tag"><i class="bi bi-briefcase-fill"></i> CA Practice</span>
                    </div>
                </div>

                <div class="review-card">
                    <div class="review-header">
                        <div class="review-avatar" style="background: linear-gradient(135deg, #00aaff, #00c98d);">PS</div>
                        <div class="reviewer-info">
                            <h4>Priya Sharma</h4>
                            <div class="reviewer-title">E-commerce Seller</div>
                        </div>
                    </div>
                    <div class="review-rating">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <p class="review-text">
                        "As an Amazon seller, GSTR-1 filing was a nightmare. EcomgstPro's e-commerce module automatically generates returns."
                    </p>
                    <div class="review-footer">
                        <span class="verified-badge"><i class="bi bi-patch-check-fill"></i> Verified Client</span>
                        <span class="platform-tag"><i class="bi bi-amazon"></i> Amazon Seller</span>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Trust Indicators Footer --}}
    <div style="text-align: center; margin-top: 2rem; position: relative; z-index: 1;">
        <div style="display: flex; align-items: center; justify-content: center; gap: 2rem; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <i class="bi bi-trophy-fill" style="color: #fbbf24;"></i>
                <span style="font-size: 0.75rem; color: #5a6874;">#1 GST Software India 2024</span>
            </div>
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <i class="bi bi-star-fill" style="color: var(--teal);"></i>
                <span style="font-size: 0.75rem; color: #5a6874;">4.9 Rating on Google</span>
            </div>
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <i class="bi bi-people-fill" style="color: var(--teal);"></i>
                <span style="font-size: 0.75rem; color: #5a6874;">10,000+ Happy Clients</span>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════════════
     CTA SECTION — No credit card required
     Height: 400px fixed (desktop), auto on mobile
     Theme: Dark Navy #0a1628 + Teal #00c98d + Sky #00aaff
     ═══════════════════════════════════════════════════════════════════ --}}

<style>
/* ─────────────────────────────────────────────────────────────────
   CTA SECTION STYLES
   Fixed height: 400px on desktop
   ───────────────────────────────────────────────────────────── */
.cta-fixed-height {
    background: linear-gradient(135deg, #0a1628 0%, #0d1f3c 50%, #0a2240 100%);
    position: relative;
    overflow: hidden;
    height: 300px;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
}

/* Animated dot grid background */
.cta-fixed-height .dot-grid-bg {
    position: absolute;
    inset: 0;
    background-image: radial-gradient(rgba(0,201,141,0.08) 1px, transparent 1px);
    background-size: 28px 28px;
    pointer-events: none;
    animation: subtleFloat 20s linear infinite;
}

@keyframes subtleFloat {
    0% { background-position: 0 0; }
    100% { background-position: 56px 56px; }
}

/* Glow effects */
.cta-fixed-height::before {
    content: '';
    position: absolute;
    top: -40%;
    right: -20%;
    width: 500px;
    height: 500px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(0,201,141,0.08) 0%, transparent 70%);
    pointer-events: none;
    animation: pulseGlow 6s ease-in-out infinite;
}

.cta-fixed-height::after {
    content: '';
    position: absolute;
    bottom: -30%;
    left: -15%;
    width: 450px;
    height: 450px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(0,170,255,0.06) 0%, transparent 70%);
    pointer-events: none;
    animation: pulseGlow2 8s ease-in-out infinite;
}

@keyframes pulseGlow {
    0%, 100% { opacity: 0.6; transform: scale(1); }
    50% { opacity: 1; transform: scale(1.05); }
}

@keyframes pulseGlow2 {
    0%, 100% { opacity: 0.4; transform: scale(1); }
    50% { opacity: 0.8; transform: scale(1.08); }
}

/* Floating particles */
.particle {
    position: absolute;
    background: rgba(0,201,141,0.15);
    border-radius: 50%;
    pointer-events: none;
    animation: floatParticle 12s ease-in-out infinite;
}

@keyframes floatParticle {
    0%, 100% { transform: translateY(0) translateX(0); opacity: 0; }
    20% { opacity: 0.5; }
    80% { opacity: 0.3; }
    100% { transform: translateY(-100px) translateX(50px); opacity: 0; }
}

/* Content container */
.cta-content {
    position: relative;
    z-index: 10;
    max-width: 800px;
    margin: 0 auto;
    padding: 0 2rem;
}

/* Badge styling */
.cta-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(0,201,141,0.12);
    border: 1px solid rgba(0,201,141,0.3);
    border-radius: 60px;
    padding: 8px 20px;
    margin-bottom: 28px;
    backdrop-filter: blur(4px);
}

.cta-badge span {
    width: 8px;
    height: 8px;
    background: #00c98d;
    border-radius: 50%;
    display: inline-block;
    animation: blinkGreen 1.8s ease-in-out infinite;
}

@keyframes blinkGreen {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.4; transform: scale(0.8); }
}

.cta-badge-text {
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: #00c98d;
}

/* Main heading */
.cta-heading {
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    font-size: clamp(32px, 4.5vw, 52px);
    line-height: 1.15;
    letter-spacing: -1px;
    color: #ffffff;
    margin-bottom: 20px;
}

.cta-heading em {
    color: #00c98d;
    font-style: normal;
    position: relative;
    display: inline-block;
}

.cta-heading em::after {
    content: '';
    position: absolute;
    bottom: 6px;
    left: 0;
    right: 0;
    height: 8px;
    background: rgba(0,201,141,0.25);
    border-radius: 4px;
    z-index: -1;
}

/* Subtitle */
.cta-subtitle {
    font-size: 16px;
    line-height: 1.7;
    color: #8a9bbf;
    max-width: 550px;
    margin: 0 auto 32px;
}

/* Button group */
.cta-buttons {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 16px;
    flex-wrap: wrap;
    margin-bottom: 28px;
}

.btn-cta-primary {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: linear-gradient(135deg, #00c98d 0%, #00e0a8 100%);
    color: #0a1628;
    font-family: 'DM Sans', sans-serif;
    font-weight: 700;
    font-size: 15px;
    padding: 14px 32px;
    border-radius: 14px;
    text-decoration: none;
    transition: all 0.25s ease;
    border: none;
    cursor: pointer;
    box-shadow: 0 6px 20px rgba(0,201,141,0.3);
}

.btn-cta-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 30px rgba(0,201,141,0.45);
    color: #0a1628;
}

.btn-cta-secondary {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: transparent;
    color: #e8f0fe;
    font-family: 'DM Sans', sans-serif;
    font-weight: 600;
    font-size: 15px;
    padding: 13px 28px;
    border-radius: 14px;
    text-decoration: none;
    transition: all 0.25s ease;
    border: 1.5px solid rgba(255, 255, 255, 0.2);
    cursor: pointer;
}

.btn-cta-secondary:hover {
    border-color: #00c98d;
    background: rgba(0,201,141,0.08);
    color: #ffffff;
    transform: translateY(-2px);
}

/* Trust text */
.cta-trust {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 24px;
    flex-wrap: wrap;
}

.trust-item {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #8a9bbf;
}

.trust-item i {
    color: #00c98d;
    font-size: 14px;
}

.trust-item strong {
    color: #ffffff;
    font-weight: 600;
}

/* Responsive */
@media (max-width: 768px) {
    .cta-fixed-height {
        height: auto;
        min-height: 420px;
        padding: 60px 0;
    }

    .cta-heading {
        font-size: 28px;
    }

    .cta-heading em::after {
        bottom: 2px;
        height: 6px;
    }

    .cta-buttons {
        gap: 12px;
    }

    .btn-cta-primary, .btn-cta-secondary {
        padding: 11px 24px;
        font-size: 14px;
    }

    .cta-trust {
        gap: 16px;
    }

    .trust-item {
        font-size: 11px;
    }
}

@media (max-width: 480px) {
    .cta-buttons {
        flex-direction: column;
        width: 100%;
    }

    .btn-cta-primary, .btn-cta-secondary {
        width: 100%;
        justify-content: center;
    }

    .cta-trust {
        flex-direction: column;
        gap: 8px;
    }
}
</style>

{{-- ================================================================
     CTA SECTION — Fixed Height 400px
     ================================================================ --}}
<section class="cta-fixed-height">
    {{-- Animated dot grid --}}
    <div class="dot-grid-bg"></div>

    {{-- Floating particles --}}
    <div class="particle" style="width: 3px; height: 3px; top: 15%; left: 10%; animation-duration: 14s;"></div>
    <div class="particle" style="width: 5px; height: 5px; top: 70%; left: 85%; animation-duration: 18s; animation-delay: 2s;"></div>
    <div class="particle" style="width: 4px; height: 4px; top: 40%; left: 92%; animation-duration: 12s; animation-delay: 4s;"></div>
    <div class="particle" style="width: 6px; height: 6px; top: 80%; left: 15%; animation-duration: 20s; animation-delay: 1s;"></div>
    <div class="particle" style="width: 3px; height: 3px; top: 25%; left: 88%; animation-duration: 16s; animation-delay: 3s;"></div>

    {{-- Main Content --}}
    <div class="cta-content">
        {{-- Badge --}}
        <div class="cta-badge">
            <span></span>
            <span class="cta-badge-text">No credit card required</span>
        </div>

        {{-- Heading --}}
        <h2 class="cta-heading">
            Ready to <em>fly past</em><br>GST complexity?
        </h2>

        {{-- Subtitle --}}
        <p class="cta-subtitle">
            Join 10,000+ Indian businesses already saving hours every month with EcomgstPro. Set up in under 5 minutes.
        </p>

        {{-- Buttons --}}
        <div class="cta-buttons">
            <a href="{{ route('register') }}" class="btn-cta-primary">
                <i class="bi bi-person-plus-fill"></i> Create Free Account
            </a>
            <a href="{{ route('login') }}" class="btn-cta-secondary">
                <i class="bi bi-box-arrow-in-right"></i> Sign In
            </a>
        </div>

        {{-- Trust indicators --}}
        <div class="cta-trust">
            <span class="trust-item">
                <i class="bi bi-shield-check-fill"></i>
                <span><strong>GSP Certified</strong> | ISO 27001</span>
            </span>
            <span class="trust-item">
                <i class="bi bi-people-fill"></i>
                <span><strong>10,000+</strong> Active Businesses</span>
            </span>
            <span class="trust-item">
                <i class="bi bi-graph-up-arrow"></i>
                <span><strong>₹50Cr+</strong> ITC Recovered</span>
            </span>
        </div>
    </div>
</section>




<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

@endsection
