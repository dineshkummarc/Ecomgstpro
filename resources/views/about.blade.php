@extends('layouts.app')

{{-- ============================================================
     GST Pilot — about.blade.php
     Theme : Dark Navy #0a1628 + Teal #00c98d + Sky #00aaff
     Fonts : Syne (headings) + DM Sans (body)
     Sections: Hero → Ticker → Our Story (zigzag) →
               Mission/Vision/Values → Roadmap →
               Location SEO → FAQ → CTA
     ============================================================ --}}

@section('content')

<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
/* ═══════════════════════════════════════════
   GLOBAL TOKENS
═══════════════════════════════════════════ */
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
html,body{overflow-x:hidden}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'DM Sans',sans-serif;background:#f0f4ff;color:var(--navy)}

.gp-section{padding:96px 0}
.gp-container{max-width:1200px;margin:0 auto;padding:0 32px}

.gp-eyebrow{
    display:inline-flex;align-items:center;gap:8px;
    font-size:11px;font-weight:700;letter-spacing:2.5px;
    text-transform:uppercase;color:var(--teal);margin-bottom:16px;
}
.gp-eyebrow::before{content:'';width:18px;height:2px;background:var(--teal);border-radius:2px;}

.gp-h2{
    font-family:'Syne',sans-serif;font-weight:800;font-size:clamp(30px,4vw,46px);
    line-height:1.15;letter-spacing:-.5px;color:var(--navy);margin-bottom:16px;
}
.gp-h2 em{color:var(--teal);font-style:normal}
.gp-h2-white{color:#fff}

.gp-lead{font-size:16px;line-height:1.8;color:#4a5568;max-width:560px}
.gp-lead-muted{color:var(--muted)}

.dot-grid{
    background-image:radial-gradient(rgba(255,255,255,.03) 1px,transparent 1px);
    background-size:26px 26px;
}

/* ═══════════════════════════════════════════
   ABOUT HERO
═══════════════════════════════════════════ */
.about-hero{
    background:linear-gradient(135deg,var(--navy) 0%,var(--deep) 55%,#0a2240 100%);
    min-height:88vh;display:flex;align-items:center;
    position:relative;overflow:hidden;padding:80px 0;
}
.about-hero::before{
    content:'';position:absolute;width:700px;height:700px;border-radius:50%;
    background:radial-gradient(circle,rgba(0,170,255,.07) 0%,transparent 70%);
    top:-120px;right:-160px;pointer-events:none;
}
.about-hero::after{
    content:'';position:absolute;width:500px;height:500px;border-radius:50%;
    background:radial-gradient(circle,rgba(0,201,141,.08) 0%,transparent 70%);
    bottom:-80px;left:-100px;pointer-events:none;
}
.about-hero-inner{
    position:relative;z-index:2;
    max-width:1200px;margin:0 auto;padding:0 32px;
    display:grid;grid-template-columns:1fr 1fr;gap:64px;align-items:center;
}

.ab-breadcrumb{
    display:flex;align-items:center;gap:8px;
    font-size:13px;color:var(--muted);margin-bottom:20px;
}
.ab-breadcrumb a{color:var(--muted);text-decoration:none;transition:color .2s}
.ab-breadcrumb a:hover{color:var(--teal)}
.ab-breadcrumb i{font-size:10px;color:var(--teal)}

.about-badge{
    display:inline-flex;align-items:center;gap:8px;
    background:rgba(0,201,141,.12);border:1px solid rgba(0,201,141,.25);
    border-radius:30px;padding:6px 16px;
    font-size:12px;font-weight:700;letter-spacing:1.5px;
    text-transform:uppercase;color:var(--teal);margin-bottom:24px;
}
.about-badge span{
    width:6px;height:6px;border-radius:50%;background:var(--teal);
    animation:blink 1.8s infinite;
}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.3}}

.about-hero-title{
    font-family:'Syne',sans-serif;font-weight:800;
    font-size:clamp(36px,5vw,60px);line-height:1.1;
    letter-spacing:-1.5px;color:#fff;margin-bottom:20px;
}
.about-hero-title .teal{color:var(--teal)}
.about-hero-title .outline{
    color:transparent;-webkit-text-stroke:1.5px var(--teal);display:block;
}
.about-hero-sub{
    font-size:16px;line-height:1.8;color:var(--muted);
    max-width:490px;margin-bottom:36px;
}

.hero-btns{display:flex;gap:12px;flex-wrap:wrap}

.btn-primary-gp{
    font-family:'DM Sans',sans-serif;font-weight:700;font-size:15px;color:var(--navy);
    background:linear-gradient(135deg,var(--teal) 0%,#00e0a8 100%);
    border:none;border-radius:10px;padding:14px 28px;
    text-decoration:none;display:inline-flex;align-items:center;gap:8px;
    box-shadow:0 4px 22px rgba(0,201,141,.35);transition:all .2s;
}
.btn-primary-gp:hover{transform:translateY(-2px);box-shadow:0 8px 30px rgba(0,201,141,.5);color:var(--navy)}

.btn-ghost-gp{
    font-family:'DM Sans',sans-serif;font-weight:600;font-size:15px;color:var(--txt);
    background:transparent;border:1px solid rgba(255,255,255,.15);
    border-radius:10px;padding:13px 26px;
    text-decoration:none;display:inline-flex;align-items:center;gap:8px;transition:all .2s;
}
.btn-ghost-gp:hover{color:#fff;border-color:rgba(255,255,255,.35);background:rgba(255,255,255,.06)}

/* Hero right: info cards */
.hero-right{display:flex;flex-direction:column;gap:16px}

.info-card{
    background:rgba(255,255,255,.05);border:1px solid var(--border);
    border-radius:20px;padding:26px 24px;backdrop-filter:blur(8px);
    transition:border-color .3s;
}
.info-card:hover{border-color:rgba(0,201,141,.3)}

.info-card-icon{
    width:44px;height:44px;border-radius:12px;
    background:var(--teal-low);border:1px solid rgba(0,201,141,.2);
    display:flex;align-items:center;justify-content:center;
    color:var(--teal);font-size:20px;margin-bottom:14px;
}
.info-card-title{
    font-family:'Syne',sans-serif;font-weight:700;font-size:16px;
    color:#fff;margin-bottom:8px;
}
.info-card-text{font-size:13.5px;line-height:1.7;color:var(--muted)}

.about-trust{display:flex;align-items:center;gap:20px;flex-wrap:wrap;margin-top:28px}
.trust-chip{display:flex;align-items:center;gap:7px;font-size:13px;color:var(--muted)}
.trust-chip i{color:var(--teal);font-size:15px}
.trust-chip strong{color:#fff;font-weight:600}

/* ═══════════════════════════════════════════
   TICKER
═══════════════════════════════════════════ */
.ticker{background:var(--teal);padding:12px 0;overflow:hidden}
.ticker-track{
    display:flex;align-items:center;gap:48px;
    animation:ticker-scroll 22s linear infinite;width:max-content;
}
.ticker-item{
    display:flex;align-items:center;gap:10px;
    font-family:'Syne',sans-serif;font-weight:700;font-size:12px;
    letter-spacing:1px;text-transform:uppercase;color:var(--navy);white-space:nowrap;
}
.ticker-item::after{content:'✦';font-size:9px;opacity:.5;margin-left:24px}
@keyframes ticker-scroll{from{transform:translateX(0)}to{transform:translateX(-50%)}}

/* ═══════════════════════════════════════════
   OUR STORY — ZIG-ZAG
═══════════════════════════════════════════ */
.story-section{background:#fff}

.zigzag-row{
    display:grid;grid-template-columns:1fr 1fr;gap:72px;align-items:center;
    padding:80px 0;border-bottom:1px solid #eef2f8;
}
.zigzag-row:last-child{border-bottom:none}
.zigzag-row.reverse{direction:rtl}
.zigzag-row.reverse > *{direction:ltr}

.zz-image-wrap{
    border-radius:24px;overflow:hidden;position:relative;
    box-shadow:0 24px 48px rgba(0,0,0,.10);
}
.zz-image-wrap img{
    width:100%;height:360px;object-fit:cover;display:block;
    transition:transform .4s ease;
}
.zz-image-wrap:hover img{transform:scale(1.04)}

.zz-img-badge{
    position:absolute;bottom:20px;left:20px;
    background:var(--navy);border:1px solid rgba(0,201,141,.35);
    border-radius:14px;padding:12px 16px;
    display:flex;align-items:center;gap:10px;
}
.zz-img-badge-icon{
    width:36px;height:36px;border-radius:10px;
    background:var(--teal-low);display:flex;align-items:center;justify-content:center;
    color:var(--teal);font-size:18px;
}
.zz-img-badge-txt{font-size:13px;color:#fff;font-weight:600}
.zz-img-badge-sub{font-size:11px;color:var(--muted)}

.zz-title{
    font-family:'Syne',sans-serif;font-weight:800;
    font-size:clamp(24px,3vw,36px);line-height:1.2;
    color:var(--navy);margin-bottom:14px;letter-spacing:-.3px;
}
.zz-title em{color:var(--teal);font-style:normal}
.zz-desc{font-size:15px;line-height:1.8;color:#4a5568;margin-bottom:22px}

.zz-list{list-style:none;display:flex;flex-direction:column;gap:12px;margin-bottom:22px}
.zz-list li{
    display:flex;align-items:flex-start;gap:12px;
    font-size:14.5px;line-height:1.6;color:#374151;
}
.zz-list li i{font-size:16px;flex-shrink:0;margin-top:2px}
.zz-list li i.green{color:var(--teal)}
.zz-list li i.red{color:#ef4444}

.zz-chips{display:flex;gap:10px;flex-wrap:wrap}
.zz-chip{
    background:#f0f9f5;border:1px solid rgba(0,201,141,.25);
    border-radius:10px;padding:9px 15px;
    font-family:'Syne',sans-serif;font-weight:700;font-size:13px;color:var(--navy);
}
.zz-chip em{color:var(--teal);font-style:normal}

/* ═══════════════════════════════════════════
   MISSION / VISION / VALUES
═══════════════════════════════════════════ */
.mvv-section{background:#f7faff;padding:96px 0}

.mvv-grid{
    display:grid;grid-template-columns:repeat(3,1fr);gap:24px;margin-top:56px;
}
.mvv-card{
    background:#fff;border-radius:20px;padding:36px 28px;
    border:1px solid #e8edf5;transition:all .25s;position:relative;overflow:hidden;
}
.mvv-card::before{
    content:'';position:absolute;top:0;left:0;right:0;height:4px;
    background:linear-gradient(90deg,var(--teal),var(--sky));
    transform:scaleX(0);transform-origin:left;transition:transform .3s;
}
.mvv-card:hover{transform:translateY(-5px);box-shadow:0 16px 40px rgba(0,201,141,.10);border-color:rgba(0,201,141,.2)}
.mvv-card:hover::before{transform:scaleX(1)}

.mvv-icon{
    width:60px;height:60px;border-radius:16px;
    background:var(--teal-low);border:1px solid rgba(0,201,141,.2);
    display:flex;align-items:center;justify-content:center;
    font-size:28px;color:var(--teal);margin-bottom:22px;
}
.mvv-label{font-size:10px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--teal);margin-bottom:8px}
.mvv-title{font-family:'Syne',sans-serif;font-weight:800;font-size:20px;color:var(--navy);margin-bottom:12px;line-height:1.3}
.mvv-desc{font-size:14px;line-height:1.75;color:#4a5568}

/* ═══════════════════════════════════════════
   VALUES GRID
═══════════════════════════════════════════ */
.values-section{
    background:linear-gradient(135deg,var(--navy) 0%,var(--deep) 100%);
    padding:96px 0;position:relative;overflow:hidden;
}
.values-section::after{
    content:'';position:absolute;bottom:-80px;right:-80px;
    width:350px;height:350px;border-radius:50%;
    background:radial-gradient(circle,rgba(0,170,255,.07) 0%,transparent 70%);
}
.values-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:20px;margin-top:56px}

.val-card{
    background:rgba(255,255,255,.04);border:1px solid var(--border);
    border-radius:18px;padding:28px 22px;transition:all .25s;text-align:center;
}
.val-card:hover{background:rgba(255,255,255,.07);border-color:rgba(0,201,141,.3);transform:translateY(-4px)}

.val-icon{
    width:56px;height:56px;border-radius:14px;
    background:rgba(0,201,141,.10);border:1px solid rgba(0,201,141,.2);
    display:flex;align-items:center;justify-content:center;
    font-size:26px;color:var(--teal);margin:0 auto 18px;
}
.val-title{font-family:'Syne',sans-serif;font-weight:700;font-size:15px;color:#fff;margin-bottom:10px}
.val-desc{font-size:13px;line-height:1.7;color:var(--muted)}

/* ═══════════════════════════════════════════
   ROADMAP
═══════════════════════════════════════════ */
.roadmap-section{background:#fff;padding:96px 0}

.roadmap-intro{
    display:grid;grid-template-columns:1fr 1fr;gap:64px;align-items:start;margin-bottom:72px;
}
.roadmap-intro-right{
    background:linear-gradient(135deg,var(--navy),var(--deep));
    border-radius:24px;padding:36px 32px;border:1px solid var(--border);position:relative;overflow:hidden;
}
.roadmap-intro-right::before{
    content:'';position:absolute;top:-40px;right:-40px;width:180px;height:180px;border-radius:50%;
    background:radial-gradient(circle,rgba(0,201,141,.12) 0%,transparent 70%);
}
.roadmap-pill{
    display:inline-flex;align-items:center;gap:6px;
    background:rgba(0,201,141,.12);border:1px solid rgba(0,201,141,.25);
    border-radius:20px;padding:5px 14px;font-size:11px;font-weight:700;
    color:var(--teal);letter-spacing:1px;text-transform:uppercase;margin-bottom:16px;
}
.roadmap-quote{
    font-family:'Syne',sans-serif;font-weight:700;font-size:18px;
    color:#fff;line-height:1.55;margin-bottom:16px;
}
.roadmap-quote-sub{font-size:13.5px;line-height:1.7;color:var(--muted)}

/* Phase timeline */
.roadmap-phases{display:flex;flex-direction:column;gap:0}

.phase-item{
    display:grid;grid-template-columns:160px 1fr;gap:0;
    position:relative;
}
.phase-item:last-child .phase-connector{display:none}

.phase-left{
    padding:0 28px 48px 0;display:flex;flex-direction:column;align-items:flex-end;
    border-right:2px solid #eef2f8;position:relative;
}
.phase-item:last-child .phase-left{padding-bottom:0}

.phase-dot{
    position:absolute;right:-10px;top:0;
    width:20px;height:20px;border-radius:50%;
    border:2px solid var(--teal);background:#fff;
    display:flex;align-items:center;justify-content:center;
    transition:all .3s;
}
.phase-dot-inner{width:8px;height:8px;border-radius:50%;background:var(--teal)}

.phase-item:hover .phase-dot{background:var(--teal)}
.phase-item:hover .phase-dot-inner{background:#fff}

.phase-label{
    font-family:'Syne',sans-serif;font-weight:800;font-size:13px;
    color:var(--teal);text-align:right;margin-bottom:4px;
}
.phase-quarter{font-size:12px;color:#94a3b8;text-align:right}

.phase-right{padding:0 0 48px 32px}
.phase-item:last-child .phase-right{padding-bottom:0}

.phase-card{
    background:#f8faff;border:1px solid #e8edf5;border-radius:16px;padding:22px 24px;
    transition:all .25s;
}
.phase-card:hover{border-color:rgba(0,201,141,.3);box-shadow:0 6px 24px rgba(0,201,141,.08);background:#fff}

.phase-status{
    display:inline-flex;align-items:center;gap:6px;
    font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;
    border-radius:20px;padding:3px 10px;margin-bottom:12px;
}
.phase-status.live{background:rgba(0,201,141,.12);color:var(--teal)}
.phase-status.building{background:rgba(0,170,255,.10);color:var(--sky)}
.phase-status.planned{background:rgba(138,155,191,.10);color:#8a9bbf}
.phase-status .dot{width:6px;height:6px;border-radius:50%;background:currentColor}

.phase-title{font-family:'Syne',sans-serif;font-weight:800;font-size:18px;color:var(--navy);margin-bottom:8px}
.phase-desc{font-size:13.5px;line-height:1.75;color:#4a5568;margin-bottom:14px}

.phase-features{display:flex;flex-wrap:wrap;gap:8px}
.phase-feat{
    background:#fff;border:1px solid #e8edf5;border-radius:8px;
    padding:5px 12px;font-size:12px;color:#374151;font-weight:500;
    display:inline-flex;align-items:center;gap:5px;
}
.phase-feat i{color:var(--teal);font-size:12px}
.phase-feat.sky i{color:var(--sky)}
.phase-feat.gray i{color:#94a3b8}

/* ═══════════════════════════════════════════
   LOCATION SEO
═══════════════════════════════════════════ */
.loc-section{background:#f7faff;padding:96px 0}

.loc-grid{
    display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-top:56px;
}
.loc-card{
    background:#fff;border-radius:16px;padding:28px 22px;
    border:1px solid #e8edf5;transition:all .25s;position:relative;overflow:hidden;
}
.loc-card::before{
    content:'';position:absolute;top:0;left:0;right:0;height:3px;
    background:linear-gradient(90deg,var(--teal),var(--sky));
    transform:scaleX(0);transform-origin:left;transition:transform .3s;
}
.loc-card:hover{transform:translateY(-4px);box-shadow:0 12px 32px rgba(0,201,141,.10);border-color:rgba(0,201,141,.2)}
.loc-card:hover::before{transform:scaleX(1)}

.loc-icon{
    width:48px;height:48px;border-radius:12px;
    background:var(--teal-low);border:1px solid rgba(0,201,141,.2);
    display:flex;align-items:center;justify-content:center;
    color:var(--teal);font-size:22px;margin-bottom:14px;
}
.loc-title{font-family:'Syne',sans-serif;font-weight:700;font-size:16px;color:var(--navy);margin-bottom:6px}
.loc-desc{font-size:13px;line-height:1.7;color:#4a5568}

.city-tags{display:flex;flex-wrap:wrap;gap:6px;margin-top:12px}
.city-tag{
    background:rgba(0,201,141,.08);border:1px solid rgba(0,201,141,.2);
    border-radius:20px;padding:3px 10px;font-size:11px;font-weight:600;color:#007a56;
}

.seo-text-box{
    background:#fff;border-radius:16px;padding:28px 32px;
    margin-top:24px;border:1px solid #e8edf5;
}
.seo-text-box p{font-size:14px;line-height:1.85;color:#4a5568;text-align:center;margin:0}
.seo-text-box p strong{color:var(--navy)}

/* ═══════════════════════════════════════════
   FAQ
═══════════════════════════════════════════ */
.faq-section{background:#fff;padding:96px 0}

.faq-grid{
    display:grid;grid-template-columns:1fr 1fr;gap:48px;
    margin-top:56px;align-items:start;
}

.faq-sidebar-title{
    font-family:'Syne',sans-serif;font-weight:800;font-size:28px;
    color:var(--navy);margin-bottom:16px;line-height:1.3;
}
.faq-sidebar-title em{color:var(--teal);font-style:normal}
.faq-sidebar-desc{font-size:15px;line-height:1.75;color:#4a5568;margin-bottom:28px}

.faq-contact-card{
    background:var(--navy);border-radius:18px;padding:24px;border:1px solid var(--border);
}
.faq-contact-card h4{font-family:'Syne',sans-serif;font-weight:700;font-size:16px;color:#fff;margin-bottom:8px}
.faq-contact-card p{font-size:13px;color:var(--muted);margin-bottom:16px}
.faq-contact-btn{
    display:inline-flex;align-items:center;gap:8px;
    background:linear-gradient(135deg,var(--teal),#00e0a8);
    color:var(--navy);font-weight:700;font-size:13.5px;
    padding:10px 20px;border-radius:10px;text-decoration:none;transition:all .2s;
}
.faq-contact-btn:hover{transform:translateY(-1px);box-shadow:0 6px 20px rgba(0,201,141,.3);color:var(--navy)}

.faq-list{display:flex;flex-direction:column;gap:10px}
.faq-item{border:1.5px solid #e8edf5;border-radius:14px;overflow:hidden;transition:border-color .2s}
.faq-item.open{border-color:rgba(0,201,141,.35)}

.faq-question{
    width:100%;background:none;border:none;
    display:flex;justify-content:space-between;align-items:center;
    padding:18px 20px;cursor:pointer;text-align:left;gap:16px;
}
.faq-q-text{font-family:'Syne',sans-serif;font-weight:700;font-size:14.5px;color:var(--navy);line-height:1.35}
.faq-item.open .faq-q-text{color:var(--teal)}

.faq-toggle{
    width:30px;height:30px;border-radius:8px;flex-shrink:0;
    background:var(--teal-low);border:1px solid rgba(0,201,141,.2);
    display:flex;align-items:center;justify-content:center;
    color:var(--teal);font-size:18px;transition:transform .3s,background .2s;
}
.faq-item.open .faq-toggle{background:var(--teal);color:var(--navy);transform:rotate(45deg)}

.faq-answer{max-height:0;overflow:hidden;transition:max-height .4s ease,padding .3s;padding:0 20px}
.faq-item.open .faq-answer{max-height:400px;padding:0 20px 18px}
.faq-answer p{font-size:14px;line-height:1.8;color:#4a5568}
.faq-answer strong{color:var(--navy)}

/* ═══════════════════════════════════════════
   CTA SECTION
═══════════════════════════════════════════ */
.about-cta{
    background:linear-gradient(135deg,var(--navy) 0%,#0d2545 100%);
    padding:100px 0;position:relative;overflow:hidden;text-align:center;
}
.about-cta::before{
    content:'';position:absolute;width:600px;height:600px;border-radius:50%;
    background:radial-gradient(circle,rgba(0,201,141,.07) 0%,transparent 65%);
    top:-200px;right:-100px;
}
.about-cta::after{
    content:'';position:absolute;inset:0;
    background-image:radial-gradient(rgba(0,201,141,.04) 1px,transparent 1px);
    background-size:26px 26px;
}
.about-cta-inner{position:relative;z-index:2}

.cta-badge{
    display:inline-flex;align-items:center;gap:8px;
    background:rgba(0,201,141,.12);border:1px solid rgba(0,201,141,.3);
    border-radius:60px;padding:7px 20px;margin-bottom:26px;
}
.cta-badge-dot{width:8px;height:8px;background:var(--teal);border-radius:50%;animation:blink 1.8s infinite}
.cta-badge-text{font-size:11px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--teal)}

.about-cta-title{
    font-family:'Syne',sans-serif;font-weight:800;
    font-size:clamp(30px,4.5vw,52px);color:#fff;
    letter-spacing:-1px;line-height:1.15;margin-bottom:16px;
}
.about-cta-title em{color:var(--teal);font-style:normal}

.about-cta-sub{font-size:16px;color:var(--muted);max-width:520px;margin:0 auto 36px}

.about-cta-btns{display:flex;align-items:center;justify-content:center;gap:14px;flex-wrap:wrap;margin-bottom:28px}

.cta-trust-row{display:flex;align-items:center;justify-content:center;gap:24px;flex-wrap:wrap}
.cta-trust-item{display:flex;align-items:center;gap:7px;font-size:13px;color:var(--muted)}
.cta-trust-item i{color:var(--teal);font-size:14px}
.cta-trust-item strong{color:#fff;font-weight:600}

/* ═══════════════════════════════════════════
   RESPONSIVE
═══════════════════════════════════════════ */
@media(max-width:1024px){
    .about-hero-inner{grid-template-columns:1fr;gap:40px}
    .hero-right{max-width:500px}
    .zigzag-row{grid-template-columns:1fr;gap:40px;direction:ltr!important}
    .zigzag-row.reverse > *{direction:ltr}
    .mvv-grid{grid-template-columns:1fr 1fr}
    .values-grid{grid-template-columns:1fr 1fr}
    .roadmap-intro{grid-template-columns:1fr}
    .faq-grid{grid-template-columns:1fr}
    .loc-grid{grid-template-columns:1fr 1fr}
    .phase-item{grid-template-columns:120px 1fr}
}
@media(max-width:768px){
    .gp-section{padding:60px 0}
    .gp-container{padding:0 20px}
    .about-hero{min-height:auto;padding:56px 0}
    .about-hero-inner{padding:0 20px}
    .about-hero-title{font-size:clamp(28px,6vw,40px)}
    .hero-btns{flex-direction:column}
    .btn-primary-gp,.btn-ghost-gp{width:100%;justify-content:center}
    .mvv-grid{grid-template-columns:1fr}
    .values-grid{grid-template-columns:1fr 1fr}
    .loc-grid{grid-template-columns:1fr}
    .zigzag-row{padding:48px 0}
    .phase-item{grid-template-columns:100px 1fr}
    .roadmap-section{padding:60px 0}
    .about-cta{padding:64px 0}
    .cta-trust-row{gap:14px}
}
@media(max-width:640px){
    .about-hero-title{font-size:clamp(24px,7vw,34px)}
    .mvv-grid{grid-template-columns:1fr}
    .values-grid{grid-template-columns:1fr}
    .phase-item{grid-template-columns:1fr;gap:0}
    .phase-left{border-right:none;border-bottom:2px solid #eef2f8;flex-direction:row;
        align-items:center;gap:12px;padding:0 0 16px;margin-bottom:16px}
    .phase-dot{position:static}
    .phase-label{text-align:left;margin-bottom:0}
    .phase-quarter{text-align:left}
    .phase-right{padding:0 0 40px}
    .about-cta-btns{flex-direction:column}
    .btn-primary-gp,.btn-ghost-gp{width:100%;justify-content:center}
    .cta-trust-row{flex-direction:column;gap:10px}
    .zz-chips{flex-direction:column}
}
</style>


{{-- ══════════════════════════════════════
     §1  HERO BANNER
══════════════════════════════════════ --}}
<section class="about-hero dot-grid">
    <div class="about-hero-inner">

        {{-- LEFT: copy --}}
        <div>
            <div class="ab-breadcrumb">
                <a href="{{ route('home') }}">Home</a>
                <i class="bi bi-chevron-right"></i>
                <span>About Us</span>
            </div>

            <div class="about-badge">
                <span></span> Our Story
            </div>

            <h1 class="about-hero-title">
                Simplifying GST<br>
                for Every <span class="teal">Indian Business.</span><br>
                <span class="outline">Since 2026.</span>
            </h1>

            <p class="about-hero-sub">
                EcomgstPro was built because GST compliance in India was broken — too slow, too error-prone, and too expensive for the businesses that needed it most. We built the platform we wished existed.
            </p>

            <div class="hero-btns">
                <a href="{{ route('register') }}" class="btn-primary-gp">
                    <i class="bi bi-person-plus-fill"></i> Start Free
                </a>
                <a href="#roadmap" class="btn-ghost-gp">
                    <i class="bi bi-map"></i> See Our Roadmap
                </a>
            </div>

            <div class="about-trust">
                <div class="trust-chip">
                    <i class="bi bi-shield-check-fill"></i>
                    <span>GSP Certified</span>
                </div>
                <div class="trust-chip">
                    <i class="bi bi-award-fill"></i>
                    <span>ISO 27001</span>
                </div>
                <div class="trust-chip">
                    <i class="bi bi-geo-alt-fill"></i>
                    <span>Pan-India Platform</span>
                </div>
            </div>
        </div>

        {{-- RIGHT: info cards --}}
        <div class="hero-right">
            <div class="info-card">
                <div class="info-card-icon"><i class="bi bi-lightbulb-fill"></i></div>
                <div class="info-card-title">Why We Built EcomgstPro</div>
                <div class="info-card-text">
                    Every filing cycle, Indian businesses wasted hours on tasks that software could do in minutes. Marketplace reports in 5 different formats. Bank PDFs with no automation. Manual GSTR-1 entry. ITC slipping through the cracks. EcomgstPro fixes all of this.
                </div>
            </div>

            <div class="info-card">
                <div class="info-card-icon"><i class="bi bi-bullseye"></i></div>
                <div class="info-card-title">Our Mission</div>
                <div class="info-card-text">
                    To make GST compliance so effortless that every business owner in India — from a Meesho seller in Jaipur to a CA firm in Mumbai — can file returns without fear, without errors, and without wasting hours.
                </div>
            </div>

            <div class="info-card">
                <div class="info-card-icon"><i class="bi bi-geo-alt-fill"></i></div>
                <div class="info-card-title">Built for India, Pan-India</div>
                <div class="info-card-text">
                    EcomgstPro is 100% online, available across all 28 states and 8 Union Territories. No installation, no travel, no dependency on a local CA. Instant GST compliance wherever your business is.
                </div>
            </div>
        </div>

    </div>
</section>


{{-- ══════════════════════════════════════
     TICKER
══════════════════════════════════════ --}}
<div class="ticker">
    <div class="ticker-track">
        @php
        $items = ['Our Story','Founded 2026','GSP Certified','Built for India','E-Commerce GST','Bank Statement Converter','GSTR-1 Automation','2A/2B Reconciliation','ITC Recovery','ISO 27001','Pan-India Platform','Zero Manual Entry'];
        @endphp
        @foreach(array_merge($items,$items) as $item)
            <div class="ticker-item">{{ $item }}</div>
        @endforeach
    </div>
</div>


{{-- ══════════════════════════════════════
     §2  OUR STORY — ZIG-ZAG
══════════════════════════════════════ --}}
<section class="story-section gp-section">
    <div class="gp-container">

        {{-- Row 1: The Problem --}}
        <div class="zigzag-row">
            <div class="zz-image-wrap">
                <img
                    src="https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?w=800&q=80"
                    alt="GST compliance paperwork chaos — the problem EcomgstPro was built to solve"
                    loading="lazy"
                >
                <div class="zz-img-badge">
                    <div class="zz-img-badge-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
                    <div>
                        <div class="zz-img-badge-txt">The Problem</div>
                        <div class="zz-img-badge-sub">Before EcomgstPro</div>
                    </div>
                </div>
            </div>

            <div>
                <div class="gp-eyebrow">The Problem We Saw</div>
                <h2 class="zz-title">
                    Indian businesses were <em>drowning</em> in GST paperwork
                </h2>
                <p class="zz-desc">
                    Every month, thousands of businesses spent days on what should have been a two-hour task. Marketplace reports from Amazon, Flipkart, and Meesho arrived in incompatible formats. Bank entries were manually typed. Errors triggered GST notices. And lakhs of rupees in ITC went unclaimed because nobody caught the mismatches in time.
                </p>
                <ul class="zz-list">
                    <li><i class="bi bi-x-circle-fill red"></i> Marketplace reports in 5+ incompatible Excel formats</li>
                    <li><i class="bi bi-x-circle-fill red"></i> Manual GSTR-1 taking 6–8 hours per client per cycle</li>
                    <li><i class="bi bi-x-circle-fill red"></i> Bank PDFs with zero automation — everything typed by hand</li>
                    <li><i class="bi bi-x-circle-fill red"></i> ITC lost to mismatches no one had time to find</li>
                    <li><i class="bi bi-x-circle-fill red"></i> GST notices from human errors in manual filings</li>
                </ul>
            </div>
        </div>

        {{-- Row 2: The Solution (reversed) --}}
        <div class="zigzag-row reverse">
            <div class="zz-image-wrap">
                <img
                    src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=800&q=80"
                    alt="EcomgstPro automated GST filing dashboard"
                    loading="lazy"
                >
                <div class="zz-img-badge">
                    <div class="zz-img-badge-icon"><i class="bi bi-lightning-charge-fill"></i></div>
                    <div>
                        <div class="zz-img-badge-txt">GSTR-1 in 2 min</div>
                        <div class="zz-img-badge-sub">With EcomgstPro</div>
                    </div>
                </div>
            </div>

            <div>
                <div class="gp-eyebrow">The Solution</div>
                <h2 class="zz-title">
                    We built <em>EcomgstPro</em> so no one had to suffer through it again
                </h2>
                <p class="zz-desc">
                    EcomgstPro was designed from the ground up around real compliance pain — not a feature checklist. Every module maps to a specific problem Indian businesses face every filing cycle. Amazon, Flipkart, Meesho, and 10+ other marketplaces are supported natively. Bank PDFs convert to Tally XML automatically. And GSTR-1 goes from zero to filed in under 2 minutes.
                </p>
                <div class="zz-chips">
                    <div class="zz-chip">GSP <em>Certified</em></div>
                    <div class="zz-chip">ISO <em>27001</em></div>
                    <div class="zz-chip">GSTR-1 in <em>&lt;2 min</em></div>
                    <div class="zz-chip">690+ <em>Banks</em></div>
                    <div class="zz-chip">10+ <em>Marketplaces</em></div>
                </div>
            </div>
        </div>

        {{-- Row 3: Where We Are --}}
        <div class="zigzag-row">
            <div class="zz-image-wrap">
                <img
                    src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=800&q=80"
                    alt="EcomgstPro team — CA-built GST compliance platform for India"
                    loading="lazy"
                >
                <div class="zz-img-badge">
                    <div class="zz-img-badge-icon"><i class="bi bi-patch-check-fill"></i></div>
                    <div>
                        <div class="zz-img-badge-txt">CA-Grade Accuracy</div>
                        <div class="zz-img-badge-sub">Every Output, Every Time</div>
                    </div>
                </div>
            </div>

            <div>
                <div class="gp-eyebrow">Where We Stand Today</div>
                <h2 class="zz-title">
                    A platform built on <em>real CA expertise</em> — not assumptions
                </h2>
                <p class="zz-desc">
                    EcomgstPro is used by CAs, tax consultants, e-commerce sellers, and businesses across India. Every update, every new marketplace supported, every bank added — comes from actual user feedback. We are a GST-first product built by people who understand Indian tax law from the inside.
                </p>
                <ul class="zz-list">
                    <li><i class="bi bi-check-circle-fill green"></i> CA-reviewed logic behind every JSON output we generate</li>
                    <li><i class="bi bi-check-circle-fill green"></i> Used by e-commerce sellers on every major Indian marketplace</li>
                    <li><i class="bi bi-check-circle-fill green"></i> Regular updates aligned with GST Council notifications</li>
                    <li><i class="bi bi-check-circle-fill green"></i> Support from GST specialists — not generic helpdesk</li>
                </ul>
            </div>
        </div>

    </div>
</section>


{{-- ══════════════════════════════════════
     §3  MISSION / VISION / VALUES
══════════════════════════════════════ --}}
<section class="mvv-section">
    <div class="gp-container">
        <div class="text-center">
            <div class="gp-eyebrow" style="justify-content:center">What We Stand For</div>
            <h2 class="gp-h2" style="text-align:center;margin:0 auto 12px">Our <em>Mission,</em> Vision &amp; Promise</h2>
            <p class="gp-lead" style="text-align:center;margin:0 auto">Everything we build traces back to three core commitments to India's businesses and their CAs.</p>
        </div>

        <div class="mvv-grid">
            <div class="mvv-card">
                <div class="mvv-icon"><i class="bi bi-bullseye"></i></div>
                <div class="mvv-label">Mission</div>
                <h3 class="mvv-title">Make GST compliance a 10-minute task — not a 10-hour ordeal</h3>
                <p class="mvv-desc">Every Indian business, regardless of size or technical ability, deserves access to tools that make tax compliance accurate, fast, and stress-free. We build those tools — and we keep building them until compliance is invisible.</p>
            </div>

            <div class="mvv-card">
                <div class="mvv-icon"><i class="bi bi-eye"></i></div>
                <div class="mvv-label">Vision</div>
                <h3 class="mvv-title">India's most trusted GST automation platform for CAs and businesses</h3>
                <p class="mvv-desc">We are building the compliance infrastructure layer for Indian GST — the platform that CAs, businesses, and developers rely on for everything from GSTR filing to reconciliation to full audit preparation. One platform. Every need.</p>
            </div>

            <div class="mvv-card">
                <div class="mvv-icon"><i class="bi bi-shield-check"></i></div>
                <div class="mvv-label">Promise</div>
                <h3 class="mvv-title">CA-grade accuracy in every output — every single time</h3>
                <p class="mvv-desc">Our platform logic is built and reviewed by practicing CAs. Every JSON we generate, every reconciliation we run, every tax calculation we produce — is held to the standard a CA would sign off on before submitting to the portal.</p>
            </div>
        </div>
    </div>
</section>


{{-- ══════════════════════════════════════
     §4  VALUES
══════════════════════════════════════ --}}
<section class="values-section dot-grid">
    <div class="gp-container">
        <div style="text-align:center">
            <div class="gp-eyebrow" style="justify-content:center;color:var(--teal)">How We Work</div>
            <h2 class="gp-h2 gp-h2-white" style="color:#fff;text-align:center;margin:0 auto 12px">
                The principles that <em>guide every decision</em>
            </h2>
        </div>

        <div class="values-grid">
            <div class="val-card">
                <div class="val-icon"><i class="bi bi-patch-check-fill"></i></div>
                <div class="val-title">CA-Grade Accuracy</div>
                <p class="val-desc">Every output — JSON, report, or reconciliation — is held to the standard a practicing CA would sign off on before portal submission.</p>
            </div>
            <div class="val-card">
                <div class="val-icon"><i class="bi bi-speedometer2"></i></div>
                <div class="val-title">Speed Without Shortcuts</div>
                <p class="val-desc">Fast does not mean careless. We automate the tedious parts so the important parts get proper human attention and review.</p>
            </div>
            <div class="val-card">
                <div class="val-icon"><i class="bi bi-lock-fill"></i></div>
                <div class="val-title">Security First</div>
                <p class="val-desc">Your financial data never leaves our encrypted infrastructure without your explicit action. ISO 27001 certified. No third-party sharing, ever.</p>
            </div>
            <div class="val-card">
                <div class="val-icon"><i class="bi bi-person-check-fill"></i></div>
                <div class="val-title">Built From Real Feedback</div>
                <p class="val-desc">Every feature starts with a real complaint from a real user — not a competitor's checklist. We build what India's businesses and CAs actually need.</p>
            </div>
        </div>
    </div>
</section>


{{-- ══════════════════════════════════════
     §5  ROADMAP
══════════════════════════════════════ --}}
<section class="roadmap-section" id="roadmap">
    <div class="gp-container">

        <div class="roadmap-intro">
            <div>
                <div class="gp-eyebrow">Product Roadmap</div>
                <h2 class="gp-h2">Where EcomgstPro <em>is headed</em> — and what's already live</h2>
                <p class="gp-lead">
                    We move fast, we ship often, and every milestone is driven by one question: what pain does this solve for Indian businesses right now?
                </p>
                <p class="gp-lead" style="margin-top:14px">
                    Below is our honest, open roadmap — what's live, what we're building, and what's next on the horizon.
                </p>
            </div>

            <div class="roadmap-intro-right">
                <div class="roadmap-pill">
                    <i class="bi bi-compass-fill"></i> Our Direction
                </div>
                <div class="roadmap-quote">
                    "Every feature we ship removes one more reason for an Indian business to dread GST filing day."
                </div>
                <div class="roadmap-quote-sub">
                    We track pain points obsessively. If a CA firm reports spending more than 30 minutes on something EcomgstPro should automate — that becomes a roadmap item within the next sprint. Fast, user-driven, honest.
                </div>
            </div>
        </div>

        {{-- Phase Timeline --}}
        <div class="roadmap-phases">

            {{-- Phase 1: Live --}}
            <div class="phase-item">
                <div class="phase-left">
                    <div class="phase-dot"><div class="phase-dot-inner"></div></div>
                    <div class="phase-label">Phase 1</div>
                    <div class="phase-quarter">2026 — Q1</div>
                </div>
                <div class="phase-right">
                    <div class="phase-card">
                        <div class="phase-status live">
                            <span class="dot"></span> Live Now
                        </div>
                        <div class="phase-title">Core GST Automation — Launched</div>
                        <p class="phase-desc">
                            The foundation of EcomgstPro is live and running. E-commerce GSTR-1 filing, bank statement conversion, and GSP-certified direct portal filing are all available on the platform today.
                        </p>
                        <div class="phase-features">
                            <span class="phase-feat"><i class="bi bi-check-circle-fill"></i> E-Commerce GSTR-1 (Amazon, Flipkart, Meesho)</span>
                            <span class="phase-feat"><i class="bi bi-check-circle-fill"></i> Bank Statement → Tally XML (690+ banks)</span>
                            <span class="phase-feat"><i class="bi bi-check-circle-fill"></i> GSP-Certified Direct Filing API</span>
                            <span class="phase-feat"><i class="bi bi-check-circle-fill"></i> GSTR-3B Preparation</span>
                            <span class="phase-feat"><i class="bi bi-check-circle-fill"></i> Multi-GSTIN Dashboard for CAs</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Phase 2: Building --}}
            <div class="phase-item">
                <div class="phase-left">
                    <div class="phase-dot" style="border-color:var(--sky)"><div class="phase-dot-inner" style="background:var(--sky)"></div></div>
                    <div class="phase-label" style="color:var(--sky)">Phase 2</div>
                    <div class="phase-quarter">2026 — Q2</div>
                </div>
                <div class="phase-right">
                    <div class="phase-card">
                        <div class="phase-status building">
                            <span class="dot"></span> Building Now
                        </div>
                        <div class="phase-title">ITC Intelligence &amp; 2A/2B Reconciliation</div>
                        <p class="phase-desc">
                            We are actively building our AI-powered reconciliation engine — designed to match thousands of purchase invoices against GSTR-2A and 2B, flag every mismatch, and generate a CA-ready audit report in seconds.
                        </p>
                        <div class="phase-features">
                            <span class="phase-feat sky"><i class="bi bi-gear-wide-connected"></i> AI Invoice Matching Engine</span>
                            <span class="phase-feat sky"><i class="bi bi-gear-wide-connected"></i> GSTR-2A / 2B Auto-Reconciliation</span>
                            <span class="phase-feat sky"><i class="bi bi-gear-wide-connected"></i> ITC Gap Report & Recovery Tool</span>
                            <span class="phase-feat sky"><i class="bi bi-gear-wide-connected"></i> Mismatch Alert System</span>
                            <span class="phase-feat sky"><i class="bi bi-gear-wide-connected"></i> Vendor-wise ITC Tracker</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Phase 3: Planned --}}
            <div class="phase-item">
                <div class="phase-left">
                    <div class="phase-dot" style="border-color:#8a9bbf"><div class="phase-dot-inner" style="background:#8a9bbf"></div></div>
                    <div class="phase-label" style="color:#8a9bbf">Phase 3</div>
                    <div class="phase-quarter">2026 — Q3</div>
                </div>
                <div class="phase-right">
                    <div class="phase-card">
                        <div class="phase-status planned">
                            <span class="dot"></span> Planned
                        </div>
                        <div class="phase-title">Annual Returns &amp; CA White-Label Suite</div>
                        <p class="phase-desc">
                            Full GSTR-9 and GSTR-9C support for annual filing season, plus a white-label portal that lets CA firms brand EcomgstPro as their own client-facing compliance tool.
                        </p>
                        <div class="phase-features">
                            <span class="phase-feat gray"><i class="bi bi-clock-history"></i> GSTR-9 Annual Return</span>
                            <span class="phase-feat gray"><i class="bi bi-clock-history"></i> GSTR-9C Reconciliation Statement</span>
                            <span class="phase-feat gray"><i class="bi bi-clock-history"></i> CA White-Label Portal</span>
                            <span class="phase-feat gray"><i class="bi bi-clock-history"></i> Client Portal with Self-Filing Mode</span>
                            <span class="phase-feat gray"><i class="bi bi-clock-history"></i> Bulk Return Filing for CA Firms</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Phase 4: Planned --}}
            <div class="phase-item">
                <div class="phase-left">
                    <div class="phase-dot" style="border-color:#8a9bbf"><div class="phase-dot-inner" style="background:#8a9bbf"></div></div>
                    <div class="phase-label" style="color:#8a9bbf">Phase 4</div>
                    <div class="phase-quarter">2026 — Q4</div>
                </div>
                <div class="phase-right">
                    <div class="phase-card">
                        <div class="phase-status planned">
                            <span class="dot"></span> On The Horizon
                        </div>
                        <div class="phase-title">API Platform, ERP Integration &amp; Composition Scheme</div>
                        <p class="phase-desc">
                            Open API access for developers and ERP integrations, plus support for composition taxpayers (GSTR-4) and Input Service Distributors (GSTR-6) — making EcomgstPro the single GST layer for every Indian business type.
                        </p>
                        <div class="phase-features">
                            <span class="phase-feat gray"><i class="bi bi-clock-history"></i> Public REST API for Developers</span>
                            <span class="phase-feat gray"><i class="bi bi-clock-history"></i> Tally / SAP / Zoho ERP Connectors</span>
                            <span class="phase-feat gray"><i class="bi bi-clock-history"></i> GSTR-4 (Composition Taxpayers)</span>
                            <span class="phase-feat gray"><i class="bi bi-clock-history"></i> GSTR-6 (Input Service Distributor)</span>
                            <span class="phase-feat gray"><i class="bi bi-clock-history"></i> E-Invoice Integration (IRN / QR)</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>


{{-- ══════════════════════════════════════
     §6  LOCATION SEO
══════════════════════════════════════ --}}
<section class="loc-section">
    <div class="gp-container">
        <div class="text-center">
            <div class="gp-eyebrow" style="justify-content:center">Pan-India Coverage</div>
            <h2 class="gp-h2" style="text-align:center;margin:0 auto 12px">
                Best <em>GST Software Near You</em> — Anywhere in India
            </h2>
            <p class="gp-lead" style="text-align:center;margin:0 auto">
                EcomgstPro is 100% online — no installation, no travel, available instantly in every state and city across India.
            </p>
        </div>

        <div class="loc-grid">
            <div class="loc-card">
                <div class="loc-icon"><i class="bi bi-buildings-fill"></i></div>
                <div class="loc-title">GST Filing in India's Metro Cities</div>
                <div class="loc-desc">Businesses and CA firms in India's top metros trust EcomgstPro for accurate, on-time GSTR-1, GSTR-3B, and reconciliation — every month, every cycle.</div>
                <div class="city-tags">
                    <span class="city-tag">Mumbai</span>
                    <span class="city-tag">Delhi NCR</span>
                    <span class="city-tag">Bangalore</span>
                    <span class="city-tag">Chennai</span>
                    <span class="city-tag">Kolkata</span>
                    <span class="city-tag">Hyderabad</span>
                </div>
            </div>

            <div class="loc-card">
                <div class="loc-icon"><i class="bi bi-shop-window"></i></div>
                <div class="loc-title">GST Software for Tier-2 &amp; Tier-3 Cities</div>
                <div class="loc-desc">Growing businesses in emerging Indian cities use EcomgstPro for fast, error-free GST compliance — without expensive local CA dependency or manual data entry.</div>
                <div class="city-tags">
                    <span class="city-tag">Pune</span>
                    <span class="city-tag">Ahmedabad</span>
                    <span class="city-tag">Jaipur</span>
                    <span class="city-tag">Surat</span>
                    <span class="city-tag">Lucknow</span>
                    <span class="city-tag">Indore</span>
                    <span class="city-tag">Nagpur</span>
                    <span class="city-tag">Bhopal</span>
                </div>
            </div>

            <div class="loc-card">
                <div class="loc-icon"><i class="bi bi-cart4"></i></div>
                <div class="loc-title">E-Commerce GST Filing Across All States</div>
                <div class="loc-desc">Amazon, Flipkart, and Meesho sellers from every Indian state use EcomgstPro to auto-generate GSTR-1 — handling inter-state IGST and intra-state CGST/SGST automatically.</div>
                <div class="city-tags">
                    <span class="city-tag">All 28 States</span>
                    <span class="city-tag">All 8 UTs</span>
                    <span class="city-tag">IGST / CGST</span>
                    <span class="city-tag">TCS Adjusted</span>
                    <span class="city-tag">Pan-India</span>
                </div>
            </div>
        </div>

        <div class="seo-text-box">
            <p>
                <strong>EcomgstPro</strong> is the <strong>best online GST filing software in India</strong> for e-commerce sellers, CA professionals, and SMEs.
                Whether you need <strong>GST filing software in Mumbai</strong>, <strong>GSTR-1 automation in Delhi</strong>,
                <strong>GST software in Bangalore</strong>, <strong>online GST return filing near me</strong>,
                or <strong>CA GST tools in Pune and Ahmedabad</strong> — EcomgstPro works 100% online,
                available instantly, across every Indian state and Union Territory.
                No installation required. No local server. Just fast, accurate, GSP-certified GST compliance from anywhere in India.
            </p>
        </div>

    </div>
</section>


{{-- ══════════════════════════════════════
     §7  FAQ
══════════════════════════════════════ --}}
<section class="faq-section" id="faq">
    <div class="gp-container">
        <div class="text-center">
            <div class="gp-eyebrow" style="justify-content:center">Frequently Asked Questions</div>
            <h2 class="gp-h2" style="text-align:center;margin:0 auto 12px">
                Everything You Want to Know About <em>GST Filing & EcomgstPro</em>
            </h2>
            <p class="gp-lead" style="text-align:center;margin:0 auto">
                High-ranking GST questions answered honestly — by people who understand Indian tax compliance.
            </p>
        </div>

        <div class="faq-grid">

            {{-- LEFT SIDEBAR --}}
            <div>
                <h3 class="faq-sidebar-title">
                    Still have a <em>question?</em><br>Let's talk.
                </h3>
                <p class="faq-sidebar-desc">
                    Our support team understands GST from both sides of the table. No bots, no canned replies — real answers from GST specialists. Available Mon–Sat, 9 AM – 7 PM IST.
                </p>
                <div class="faq-contact-card">
                    <h4>Talk to our team</h4>
                    <p>Quick queries? We're also on WhatsApp. Complex problems? Email us and a GST specialist replies.</p>
                    <a href="mailto:support@ecomgstpro.com" class="faq-contact-btn">
                        <i class="bi bi-envelope-fill"></i> support@ecomgstpro.com
                    </a>
                </div>
            </div>

            {{-- RIGHT FAQ LIST --}}
            <div class="faq-list">

                <div class="faq-item open">
                    <button class="faq-question" aria-expanded="true">
                        <span class="faq-q-text">What is the best GST filing software in India for small businesses and e-commerce sellers?</span>
                        <span class="faq-toggle"><i class="bi bi-plus"></i></span>
                    </button>
                    <div class="faq-answer">
                        <p>EcomgstPro is purpose-built for Indian small businesses, e-commerce sellers, and CA firms. It automates GSTR-1 from Amazon, Flipkart, and Meesho reports, converts bank PDFs to Tally XML, and handles 2A/2B reconciliation — all in a GSP-certified, ISO 27001 platform. The Starter plan is completely free with no credit card required.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">
                        <span class="faq-q-text">How do I file GSTR-1 online for my e-commerce business in India?</span>
                        <span class="faq-toggle"><i class="bi bi-plus"></i></span>
                    </button>
                    <div class="faq-answer">
                        <p>With EcomgstPro: (1) Download your settlement or sales report from Amazon Seller Central, Flipkart Seller Hub, or Meesho Dashboard. (2) Upload it to EcomgstPro — the platform auto-detects your marketplace. (3) AI generates a <strong>GST portal-ready GSTR-1 JSON in under 2 minutes</strong> — with correct B2CS/B2B classification, state codes, TCS adjustment, and HSN summaries. (4) File directly via our GSP API or download the JSON for manual upload. No manual data entry.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">
                        <span class="faq-q-text">What is GSP certification and why does it matter for GST software?</span>
                        <span class="faq-toggle"><i class="bi bi-plus"></i></span>
                    </button>
                    <div class="faq-answer">
                        <p>GSP (GST Suvidha Provider) is a certification granted by the GST Council to platforms that meet strict technical and security requirements for <strong>direct API access to the government's GST portal</strong>. GSP-certified software like EcomgstPro can push your returns directly without you manually logging into the portal. It is the gold standard for GST software in India — and EcomgstPro holds it alongside ISO 27001 information security certification.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">
                        <span class="faq-q-text">How to maximise ITC claims in GST? What is 2A/2B reconciliation?</span>
                        <span class="faq-toggle"><i class="bi bi-plus"></i></span>
                    </button>
                    <div class="faq-answer">
                        <p><strong>GSTR-2A</strong> shows inward supplies auto-populated from suppliers' GSTR-1. <strong>GSTR-2B</strong> is a static monthly ITC statement. Reconciling your purchase register against both reveals: missing invoices, duplicate entries, and unclaimed credits. EcomgstPro's AI reconciliation matches thousands of invoices in seconds, flags every mismatch, and generates an audit-ready ITC report — helping businesses recover maximum input tax credit that would otherwise be lost.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">
                        <span class="faq-q-text">Is there free GST return filing software available online in India?</span>
                        <span class="faq-toggle"><i class="bi bi-plus"></i></span>
                    </button>
                    <div class="faq-answer">
                        <p>Yes — EcomgstPro's <strong>Starter plan is 100% free forever</strong>. It includes 1 GSTIN, up to 100 invoices per month, GSTR-1 and GSTR-3B filing, and email support. No credit card, no hidden fees, no time limit. Upgrade to Professional (₹999/GSTIN/month) for unlimited invoices, multi-GSTIN support, 2A/2B reconciliation, and the full e-commerce module.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">
                        <span class="faq-q-text">Can CAs manage multiple clients' GST filings from one account?</span>
                        <span class="faq-toggle"><i class="bi bi-plus"></i></span>
                    </button>
                    <div class="faq-answer">
                        <p>Yes — EcomgstPro is specifically built for CA practices managing multiple clients. The Professional and Enterprise plans give you a <strong>multi-GSTIN dashboard</strong> where you can switch between client profiles, batch-generate returns, download client-wise reports, and manage all filings from a single login. Ideal for CA firms handling 10, 20, or 100+ clients from cities like Mumbai, Delhi, Bangalore, and Pune.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">
                        <span class="faq-q-text">Does EcomgstPro work for businesses in all Indian states and cities?</span>
                        <span class="faq-toggle"><i class="bi bi-plus"></i></span>
                    </button>
                    <div class="faq-answer">
                        <p>Absolutely — EcomgstPro is a <strong>100% online, pan-India platform</strong> available to every business across all 28 states and 8 Union Territories. It handles inter-state (IGST) and intra-state (CGST/SGST) classification automatically, supports all state codes, and is compliant with the latest GST Council notifications. Whether you're in Jammu, Kochi, Bhopal, or Kolkata — EcomgstPro works instantly with no setup or travel.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">
                        <span class="faq-q-text">How does the Bank Statement to Tally XML converter work?</span>
                        <span class="faq-toggle"><i class="bi bi-plus"></i></span>
                    </button>
                    <div class="faq-answer">
                        <p>Upload your bank PDF or CSV from any of <strong>690+ supported Indian banks</strong> — including SBI, HDFC, ICICI, Axis, Kotak, PNB, Canara, and all regional and cooperative banks. EcomgstPro reads the statement, categorises every transaction (credit, debit, TDS, GST-related), and generates a <strong>Tally-ready XML import file</strong> — eliminating all manual voucher entry. It takes under 60 seconds and requires zero configuration or template mapping.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">
                        <span class="faq-q-text">What GST returns can I file with EcomgstPro?</span>
                        <span class="faq-toggle"><i class="bi bi-plus"></i></span>
                    </button>
                    <div class="faq-answer">
                        <p>Currently live: <strong>GSTR-1, GSTR-3B</strong>, and e-commerce specific B2B/B2CS/CDNR/HSN filing. GSTR-9 (Annual Return) and GSTR-9C (Reconciliation Statement) are on our active roadmap for Q3 2026. GSTR-4 (Composition Taxpayers) and GSTR-6 (ISD) are planned for Q4 2026. See our full roadmap above for the complete release schedule.</p>
                    </div>
                </div>

            </div>

        </div>
    </div>
</section>


{{-- ══════════════════════════════════════
     §8  CTA BANNER
══════════════════════════════════════ --}}
<section class="about-cta dot-grid">
    <div class="gp-container">
        <div class="about-cta-inner">

            <div class="cta-badge">
                <span class="cta-badge-dot"></span>
                <span class="cta-badge-text">No credit card required</span>
            </div>

            <h2 class="about-cta-title">
                India's Smartest<br><em>GST Platform</em> — Try Free Today
            </h2>

            <p class="about-cta-sub">
                File GST returns faster, smarter, and with zero errors. Set up in under 5 minutes. Works for every business type, every state, every marketplace.
            </p>

            <div class="about-cta-btns">
                <a href="{{ route('register') }}" class="btn-primary-gp">
                    <i class="bi bi-person-plus-fill"></i> Create Free Account
                </a>
                <a href="{{ route('login') }}" class="btn-ghost-gp">
                    <i class="bi bi-box-arrow-in-right"></i> Sign In
                </a>
            </div>

            <div class="cta-trust-row">
                <span class="cta-trust-item">
                    <i class="bi bi-shield-check-fill"></i>
                    <span>GSP Certified · ISO 27001</span>
                </span>
                <span class="cta-trust-item">
                    <i class="bi bi-geo-alt-fill"></i>
                    <span>Available <strong>Pan-India</strong></span>
                </span>
                <span class="cta-trust-item">
                    <i class="bi bi-arrow-repeat"></i>
                    <span>Cancel anytime</span>
                </span>
            </div>

        </div>
    </div>
</section>


{{-- FAQ ACCORDION JS --}}
<script>
(function(){
    document.querySelectorAll('.faq-question').forEach(function(btn){
        btn.addEventListener('click', function(){
            var item = this.closest('.faq-item');
            var isOpen = item.classList.contains('open');
            document.querySelectorAll('.faq-item.open').forEach(function(o){
                o.classList.remove('open');
                o.querySelector('.faq-question').setAttribute('aria-expanded','false');
            });
            if(!isOpen){
                item.classList.add('open');
                this.setAttribute('aria-expanded','true');
            }
        });
    });
})();
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@endsection
