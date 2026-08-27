@extends('layouts.app')

@section('content')

<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
:root {
    --navy: #0a1628;
    --deep: #0d1f3c;
    --mid: #132240;
    --teal: #00c98d;
    --sky: #00aaff;
    --teal-low: rgba(0,201,141,.12);
    --sky-low: rgba(0,170,255,.10);
    --txt: #e8f0fe;
    --muted: #8a9bbf;
    --border: rgba(255,255,255,.08);
}

* { box-sizing: border-box; margin: 0; padding: 0; }
html, body { overflow-x: hidden; }
body { font-family: 'DM Sans', sans-serif; background: #f0f4ff; color: var(--navy); }

/* ── SHARED ── */
.tp-section { padding: 80px 0; }
.tp-container { max-width: 1200px; margin: 0 auto; padding: 0 32px; }
.tp-eyebrow {
    display: inline-flex; align-items: center; gap: 8px;
    font-size: 11px; font-weight: 700; letter-spacing: 2.5px;
    text-transform: uppercase; color: var(--teal); margin-bottom: 14px;
}
.tp-eyebrow::before { content: ''; width: 18px; height: 2px; background: var(--teal); border-radius: 2px; }
.tp-h2 {
    font-family: 'Syne', sans-serif; font-weight: 800;
    font-size: clamp(28px, 4vw, 44px); line-height: 1.15;
    letter-spacing: -.5px; color: var(--navy); margin-bottom: 14px;
}
.tp-h2 em { color: var(--teal); font-style: normal; }
.tp-lead { font-size: 16px; line-height: 1.75; color: #4a5568; max-width: 580px; }

/* ── HERO ── */
.tut-hero {
    background: linear-gradient(135deg, var(--navy) 0%, var(--deep) 60%, #0a2240 100%);
    padding: 80px 0 0;
    position: relative; overflow: hidden;
}
.tut-hero::before {
    content: ''; position: absolute; width: 600px; height: 600px; border-radius: 50%;
    background: radial-gradient(circle, rgba(0,170,255,.07) 0%, transparent 70%);
    top: -100px; right: -120px; pointer-events: none;
}
.tut-hero::after {
    content: ''; position: absolute; width: 400px; height: 400px; border-radius: 50%;
    background: radial-gradient(circle, rgba(0,201,141,.07) 0%, transparent 70%);
    bottom: -80px; left: -60px; pointer-events: none;
}
.tut-hero-inner {
    position: relative; z-index: 2;
    max-width: 1200px; margin: 0 auto; padding: 0 32px 60px;
    display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center;
}
.tut-hero-badge {
    display: inline-flex; align-items: center; gap: 8px;
    background: rgba(0,201,141,.12); border: 1px solid rgba(0,201,141,.25);
    border-radius: 30px; padding: 6px 16px;
    font-size: 12px; font-weight: 700; letter-spacing: 1.5px;
    text-transform: uppercase; color: var(--teal); margin-bottom: 20px;
}
.tut-hero-badge span { width: 6px; height: 6px; border-radius: 50%; background: var(--teal); animation: blink 1.8s infinite; }
@keyframes blink { 0%,100%{opacity:1}50%{opacity:.3} }
.tut-hero-title {
    font-family: 'Syne', sans-serif; font-weight: 800;
    font-size: clamp(32px, 4vw, 54px); line-height: 1.1;
    letter-spacing: -1px; color: #fff; margin-bottom: 20px;
}
.tut-hero-title .teal { color: var(--teal); }
.tut-hero-sub { font-size: 16px; line-height: 1.75; color: var(--muted); max-width: 480px; margin-bottom: 32px; }

/* Steps pill row */
.tut-steps-row {
    display: flex; gap: 12px; flex-wrap: wrap;
}
.tut-step-pill {
    display: inline-flex; align-items: center; gap: 8px;
    background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.1);
    border-radius: 30px; padding: 8px 16px;
    font-size: 13px; color: var(--muted);
}
.tut-step-pill i { color: var(--teal); }

/* Right side — Plan cards */
.hero-plan-cards { display: flex; flex-direction: column; gap: 14px; }
.hero-plan-card {
    background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.08);
    border-radius: 16px; padding: 20px 22px;
    display: flex; align-items: center; gap: 16px;
    transition: all .25s;
}
.hero-plan-card:hover { background: rgba(255,255,255,.09); border-color: rgba(0,201,141,.3); }
.hero-plan-icon {
    width: 48px; height: 48px; border-radius: 12px; flex-shrink: 0;
    background: var(--teal-low); border: 1px solid rgba(0,201,141,.2);
    display: flex; align-items: center; justify-content: center;
    color: var(--teal); font-size: 22px;
}
.hero-plan-icon.sky-icon { background: var(--sky-low); border-color: rgba(0,170,255,.2); color: var(--sky); }
.hero-plan-icon.gold-icon { background: rgba(255,183,0,.1); border-color: rgba(255,183,0,.2); color: #ffb700; }
.hero-plan-name { font-family: 'Syne', sans-serif; font-weight: 700; font-size: 16px; color: #fff; margin-bottom: 3px; }
.hero-plan-desc { font-size: 13px; color: var(--muted); }
.plan-badge-free {
    margin-left: auto; background: rgba(0,201,141,.15); border: 1px solid rgba(0,201,141,.3);
    border-radius: 20px; padding: 4px 12px; font-size: 11px; font-weight: 700;
    color: var(--teal); white-space: nowrap;
}
.plan-badge-paid {
    margin-left: auto; background: rgba(255,183,0,.1); border: 1px solid rgba(255,183,0,.25);
    border-radius: 20px; padding: 4px 12px; font-size: 11px; font-weight: 700;
    color: #ffb700; white-space: nowrap;
}

/* Wave divider */
.wave-divider { display: block; width: 100%; height: 60px; }

/* ── BREADCRUMB ── */
.tut-breadcrumb {
    background: #fff; border-bottom: 1px solid #e8edf5; padding: 14px 0;
}
.tut-breadcrumb-inner {
    max-width: 1200px; margin: 0 auto; padding: 0 32px;
    display: flex; align-items: center; gap: 8px; font-size: 13px; color: #94a3b8;
}
.tut-breadcrumb-inner a { color: var(--teal); text-decoration: none; }
.tut-breadcrumb-inner i { font-size: 12px; }

/* ── PROGRESS BAR ── */
.tut-progress {
    background: var(--deep); padding: 20px 0; border-bottom: 1px solid var(--border);
    position: sticky; top: 0; z-index: 100;
}
.tut-progress-inner {
    max-width: 1200px; margin: 0 auto; padding: 0 32px;
    display: flex; align-items: center; gap: 0;
    overflow-x: auto; scrollbar-width: none;
}
.tut-progress-inner::-webkit-scrollbar { display: none; }
.prog-step {
    display: flex; align-items: center; gap: 8px;
    padding: 6px 12px; border-radius: 8px; cursor: pointer;
    white-space: nowrap; font-size: 12px; font-weight: 600;
    color: var(--muted); transition: all .2s; flex-shrink: 0;
}
.prog-step.active { color: #fff; background: rgba(0,201,141,.12); }
.prog-step.done { color: var(--teal); }
.prog-step i { font-size: 14px; }
.prog-step .step-num {
    width: 22px; height: 22px; border-radius: 50%;
    background: rgba(255,255,255,.08); display: flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 700;
}
.prog-step.active .step-num { background: var(--teal); color: var(--navy); }
.prog-step.done .step-num { background: rgba(0,201,141,.2); color: var(--teal); }
.prog-divider { width: 28px; height: 1px; background: rgba(255,255,255,.1); flex-shrink: 0; margin: 0 2px; }

/* ── OVERVIEW STRIP ── */
.overview-strip { background: var(--navy); padding: 48px 0; }
.overview-grid {
    display: grid; grid-template-columns: repeat(4,1fr);
    gap: 0;
}
.ov-item {
    text-align: center; padding: 16px 24px;
    border-right: 1px solid var(--border);
}
.ov-item:last-child { border-right: none; }
.ov-num { font-family: 'Syne', sans-serif; font-weight: 800; font-size: 36px; color: #fff; }
.ov-num em { color: var(--teal); font-style: normal; }
.ov-lbl { font-size: 13px; color: var(--muted); margin-top: 4px; }

/* ── ALERT BOX ── */
.tut-alert {
    display: flex; gap: 14px; padding: 18px 22px;
    border-radius: 14px; border-left: 4px solid; margin-bottom: 28px;
}
.tut-alert.info { background: rgba(0,170,255,.08); border-color: var(--sky); }
.tut-alert.success { background: rgba(0,201,141,.08); border-color: var(--teal); }
.tut-alert.warning { background: rgba(255,183,0,.08); border-color: #ffb700; }
.tut-alert i { font-size: 20px; flex-shrink: 0; margin-top: 2px; }
.tut-alert.info i { color: var(--sky); }
.tut-alert.success i { color: var(--teal); }
.tut-alert.warning i { color: #ffb700; }
.tut-alert-title { font-family: 'Syne', sans-serif; font-weight: 700; font-size: 14px; color: var(--navy); margin-bottom: 4px; }
.tut-alert p { font-size: 13.5px; color: #4a5568; line-height: 1.6; margin: 0; }

/* ── SECTION INTRO (before each major topic) ── */
.sec-intro {
    display: flex; gap: 20px; align-items: flex-start; margin-bottom: 48px;
    padding-bottom: 28px; border-bottom: 1px solid #e8edf5;
}
.sec-intro-icon {
    width: 60px; height: 60px; border-radius: 16px; flex-shrink: 0;
    background: var(--teal-low); border: 1px solid rgba(0,201,141,.2);
    display: flex; align-items: center; justify-content: center;
    color: var(--teal); font-size: 28px;
}
.sec-intro-text .label { font-size: 12px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: var(--teal); margin-bottom: 8px; }
.sec-intro-text h2 { font-family: 'Syne', sans-serif; font-weight: 800; font-size: clamp(22px,3vw,32px); color: var(--navy); margin-bottom: 8px; }
.sec-intro-text p { font-size: 15px; color: #4a5568; line-height: 1.7; }

/* ── STEP CARDS ── */
.step-list { display: flex; flex-direction: column; gap: 20px; }
.step-card {
    display: flex; gap: 24px;
    background: #fff; border: 1px solid #e8edf5; border-radius: 18px; padding: 28px 24px;
    transition: all .25s; position: relative; overflow: hidden;
}
.step-card::before {
    content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 4px;
    background: linear-gradient(180deg, var(--teal), var(--sky));
    border-radius: 4px 0 0 4px;
}
.step-card:hover { box-shadow: 0 8px 30px rgba(0,201,141,.1); transform: translateX(4px); border-color: rgba(0,201,141,.2); }
.step-num-circle {
    width: 44px; height: 44px; border-radius: 50%; flex-shrink: 0;
    background: var(--teal-low); border: 2px solid rgba(0,201,141,.25);
    display: flex; align-items: center; justify-content: center;
    font-family: 'Syne', sans-serif; font-weight: 800; font-size: 16px; color: var(--teal);
}
.step-card-title { font-family: 'Syne', sans-serif; font-weight: 700; font-size: 17px; color: var(--navy); margin-bottom: 8px; }
.step-card-desc { font-size: 14px; line-height: 1.75; color: #4a5568; }
.step-tip {
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(0,201,141,.08); border-radius: 8px;
    padding: 6px 12px; margin-top: 12px;
    font-size: 12.5px; color: #2d6a56;
}
.step-tip i { color: var(--teal); font-size: 13px; }

/* ── SCREENSHOT PLACEHOLDER ── */
.screenshot-wrap {
    background: var(--deep); border: 1px solid var(--border);
    border-radius: 16px; overflow: hidden; margin: 24px 0;
    position: relative;
}
.screenshot-topbar {
    background: rgba(255,255,255,.05); padding: 12px 18px;
    display: flex; align-items: center; gap: 8px;
    border-bottom: 1px solid var(--border);
}
.dot-r { width: 12px; height: 12px; border-radius: 50%; background: #ff5f57; }
.dot-y { width: 12px; height: 12px; border-radius: 50%; background: #febc2e; }
.dot-g { width: 12px; height: 12px; border-radius: 50%; background: #28c840; }
.screenshot-url {
    background: rgba(255,255,255,.06); border-radius: 6px; padding: 4px 14px;
    font-size: 12px; color: var(--muted); flex: 1; max-width: 400px;
}
.screenshot-body {
    padding: 32px 28px; min-height: 220px;
    display: flex; align-items: center; justify-content: center;
    position: relative;
}
.screenshot-body img {
    width: 100%; border-radius: 8px; display: block;
    object-fit: cover;
}
.screenshot-placeholder {
    text-align: center; color: var(--muted);
}
.screenshot-placeholder i { font-size: 40px; margin-bottom: 10px; opacity: .5; }
.screenshot-placeholder p { font-size: 13px; }

/* ── FORM MOCKUP ── */
.form-mockup {
    background: #fff; border: 1px solid #e8edf5; border-radius: 16px; padding: 28px;
    max-width: 520px;
}
.form-mockup-title { font-family: 'Syne', sans-serif; font-weight: 700; font-size: 18px; color: var(--navy); margin-bottom: 20px; }
.form-row { margin-bottom: 16px; }
.form-label { font-size: 12px; font-weight: 700; letter-spacing: .5px; color: #4a5568; margin-bottom: 6px; display: block; }
.form-input {
    width: 100%; padding: 11px 14px; border: 1.5px solid #e8edf5; border-radius: 10px;
    font-family: 'DM Sans', sans-serif; font-size: 14px; color: var(--navy); background: #f8faff;
    pointer-events: none;
}
.form-input.filled { border-color: rgba(0,201,141,.3); background: #f0fdf9; }
.form-hint { font-size: 12px; color: #94a3b8; margin-top: 4px; }
.form-btn {
    background: linear-gradient(135deg, var(--teal), #00e0a8);
    color: var(--navy); border: none; border-radius: 10px; padding: 13px 24px;
    font-family: 'DM Sans', sans-serif; font-weight: 700; font-size: 14px;
    cursor: pointer; display: inline-flex; align-items: center; gap: 8px;
    box-shadow: 0 4px 16px rgba(0,201,141,.3);
}

/* ── HIGHLIGHT BOX ── */
.highlight-box {
    background: linear-gradient(135deg, var(--navy) 0%, var(--deep) 100%);
    border-radius: 20px; padding: 36px 32px; margin: 32px 0;
    position: relative; overflow: hidden;
}
.highlight-box::before {
    content: ''; position: absolute; top: -60px; right: -60px;
    width: 200px; height: 200px; border-radius: 50%;
    background: radial-gradient(circle, rgba(0,201,141,.12) 0%, transparent 70%);
}
.highlight-box-title { font-family: 'Syne', sans-serif; font-weight: 800; font-size: 22px; color: #fff; margin-bottom: 12px; }
.highlight-box-title em { color: var(--teal); font-style: normal; }
.highlight-box p { font-size: 14px; color: var(--muted); line-height: 1.7; margin: 0 0 20px; }
.highlight-box-list { list-style: none; display: flex; flex-direction: column; gap: 10px; }
.highlight-box-list li {
    display: flex; align-items: flex-start; gap: 10px;
    font-size: 14px; color: #c5d5ef;
}
.highlight-box-list li i { color: var(--teal); font-size: 16px; flex-shrink: 0; margin-top: 2px; }

/* ── FAQ ── */
.faq-list { display: flex; flex-direction: column; gap: 12px; }
.faq-item {
    background: #fff; border: 1px solid #e8edf5; border-radius: 14px; overflow: hidden;
}
.faq-q {
    padding: 18px 22px; display: flex; align-items: center; justify-content: space-between;
    cursor: pointer; gap: 12px;
}
.faq-q-text { font-family: 'Syne', sans-serif; font-weight: 700; font-size: 15px; color: var(--navy); }
.faq-toggle {
    width: 30px; height: 30px; border-radius: 50%; background: var(--teal-low);
    display: flex; align-items: center; justify-content: center;
    color: var(--teal); font-size: 18px; flex-shrink: 0; transition: transform .25s;
}
.faq-a { padding: 0 22px 18px; font-size: 14px; color: #4a5568; line-height: 1.75; display: none; }
.faq-item.open .faq-a { display: block; }
.faq-item.open .faq-toggle { transform: rotate(45deg); }

/* ── PLAN COMPARE TABLE ── */
.plan-table-wrap { overflow-x: auto; }
.plan-table {
    width: 100%; border-collapse: collapse; min-width: 700px;
}
.plan-table th {
    padding: 16px 20px; background: var(--navy); color: #fff;
    font-family: 'Syne', sans-serif; font-weight: 700; font-size: 14px; text-align: left;
}
.plan-table th:first-child { border-radius: 12px 0 0 0; }
.plan-table th:last-child { border-radius: 0 12px 0 0; }
.plan-table th.teal-col { color: var(--teal); }
.plan-table td {
    padding: 14px 20px; border-bottom: 1px solid #e8edf5;
    font-size: 14px; color: #4a5568;
}
.plan-table tr:hover td { background: #f7faff; }
.plan-table td:first-child { font-weight: 600; color: var(--navy); }
.plan-table .check { color: var(--teal); font-size: 16px; }
.plan-table .cross { color: #cbd5e1; font-size: 16px; }
.plan-table .pro-col { background: rgba(0,201,141,.03); }

/* ── VIDEO CARD ── */
.video-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 20px; margin-top: 32px; }
.video-card {
    background: #fff; border: 1px solid #e8edf5; border-radius: 16px; overflow: hidden;
    transition: all .25s;
}
.video-card:hover { transform: translateY(-4px); box-shadow: 0 12px 30px rgba(0,0,0,.08); border-color: rgba(0,201,141,.2); }
.video-thumb {
    background: var(--deep); height: 160px; position: relative;
    display: flex; align-items: center; justify-content: center;
}
.video-thumb-bg {
    position: absolute; inset: 0;
    background-image: radial-gradient(rgba(0,201,141,.05) 1px, transparent 1px);
    background-size: 24px 24px;
}
.video-play {
    width: 52px; height: 52px; border-radius: 50%;
    background: rgba(0,201,141,.15); border: 2px solid rgba(0,201,141,.3);
    display: flex; align-items: center; justify-content: center;
    color: var(--teal); font-size: 20px; position: relative; z-index: 1;
    transition: all .2s;
}
.video-card:hover .video-play { background: var(--teal); color: var(--navy); }
.video-duration {
    position: absolute; bottom: 10px; right: 12px;
    background: rgba(0,0,0,.6); color: #fff; font-size: 11px; font-weight: 700;
    padding: 3px 8px; border-radius: 6px;
}
.video-body { padding: 16px 18px; }
.video-tag {
    display: inline-flex; align-items: center; gap: 5px;
    background: var(--teal-low); border-radius: 6px; padding: 3px 10px;
    font-size: 11px; font-weight: 700; color: #1a7a5e; margin-bottom: 10px;
}
.video-title { font-family: 'Syne', sans-serif; font-weight: 700; font-size: 14px; color: var(--navy); line-height: 1.4; }

/* ── CHECKLIST ── */
.checklist { display: flex; flex-direction: column; gap: 10px; }
.checklist-item {
    display: flex; align-items: flex-start; gap: 12px;
    background: #fff; border: 1px solid #e8edf5; border-radius: 12px; padding: 14px 16px;
}
.checklist-item.done { background: #f0fdf9; border-color: rgba(0,201,141,.2); }
.check-icon {
    width: 24px; height: 24px; border-radius: 50%; flex-shrink: 0;
    background: #e8edf5; display: flex; align-items: center; justify-content: center;
    font-size: 13px; color: #94a3b8;
}
.checklist-item.done .check-icon { background: rgba(0,201,141,.15); color: var(--teal); }
.check-text { font-size: 14px; color: #4a5568; line-height: 1.5; }
.checklist-item.done .check-text { color: #2d6a56; }

/* ── TABS ── */
.tut-tabs { display: flex; gap: 4px; background: #f0f4ff; border-radius: 12px; padding: 4px; margin-bottom: 28px; }
.tut-tab {
    flex: 1; padding: 10px 16px; border-radius: 9px; border: none;
    background: transparent; font-family: 'DM Sans', sans-serif; font-size: 13px; font-weight: 600;
    color: #6b7280; cursor: pointer; transition: all .2s;
}
.tut-tab.active { background: #fff; color: var(--navy); box-shadow: 0 2px 8px rgba(0,0,0,.06); }
.tut-tab-panel { display: none; }
.tut-tab-panel.active { display: block; }

/* ── CTA STRIP ── */
.cta-strip {
    background: linear-gradient(135deg, var(--navy), var(--deep));
    border-radius: 24px; padding: 48px 40px;
    display: grid; grid-template-columns: 1fr auto; gap: 32px; align-items: center;
    position: relative; overflow: hidden;
}
.cta-strip::before {
    content: ''; position: absolute; top: -60px; right: -40px;
    width: 250px; height: 250px; border-radius: 50%;
    background: radial-gradient(circle, rgba(0,201,141,.1) 0%, transparent 70%);
}
.cta-strip-title { font-family: 'Syne', sans-serif; font-weight: 800; font-size: clamp(22px,2.5vw,30px); color: #fff; margin-bottom: 8px; }
.cta-strip-title em { color: var(--teal); font-style: normal; }
.cta-strip p { font-size: 14px; color: var(--muted); margin: 0; }
.btn-teal {
    display: inline-flex; align-items: center; gap: 8px;
    background: linear-gradient(135deg, var(--teal), #00e0a8);
    color: var(--navy); font-family: 'DM Sans', sans-serif; font-weight: 700; font-size: 14px;
    border: none; border-radius: 12px; padding: 14px 26px; text-decoration: none;
    box-shadow: 0 4px 18px rgba(0,201,141,.35); transition: all .2s; white-space: nowrap;
}
.btn-teal:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(0,201,141,.5); color: var(--navy); }

/* ── SIDEBAR LAYOUT ── */
.tut-layout { display: grid; grid-template-columns: 260px 1fr; gap: 40px; align-items: start; }
.tut-sidebar { position: sticky; top: 80px; }
.sidebar-card {
    background: #fff; border: 1px solid #e8edf5; border-radius: 16px; overflow: hidden;
}
.sidebar-header { background: var(--navy); padding: 16px 20px; }
.sidebar-header-title { font-family: 'Syne', sans-serif; font-weight: 700; font-size: 13px; color: #fff; }
.sidebar-nav { padding: 8px; }
.sidebar-link {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 12px; border-radius: 10px; text-decoration: none;
    font-size: 13px; font-weight: 500; color: #4a5568; transition: all .2s;
}
.sidebar-link:hover { background: var(--teal-low); color: #1a7a5e; }
.sidebar-link.active { background: var(--teal-low); color: #1a7a5e; font-weight: 600; }
.sidebar-link i { font-size: 15px; color: var(--teal); width: 18px; text-align: center; }
.sidebar-divider { height: 1px; background: #e8edf5; margin: 6px 8px; }
.sidebar-badge {
    margin-left: auto; background: var(--teal-low); border-radius: 10px;
    padding: 2px 8px; font-size: 10px; font-weight: 700; color: #1a7a5e;
}

/* ── RESPONSIVE ── */
@media(max-width:1024px) {
    .tut-hero-inner { grid-template-columns: 1fr; gap: 40px; }
    .tut-layout { grid-template-columns: 1fr; }
    .tut-sidebar { position: static; }
    .overview-grid { grid-template-columns: repeat(2,1fr); }
    .ov-item:nth-child(2) { border-right: none; }
    .video-grid { grid-template-columns: repeat(2,1fr); }
    .cta-strip { grid-template-columns: 1fr; text-align: center; }
    .cta-strip .btn-teal { width: 100%; justify-content: center; }
}
@media(max-width:768px) {
    .tp-section { padding: 56px 0; }
    .tp-container { padding: 0 20px; }
    .tut-hero-inner { padding: 0 20px 48px; }
    .tut-hero-title { font-size: clamp(28px,6vw,40px); }
    .overview-grid { grid-template-columns: repeat(2,1fr); gap: 0; }
    .video-grid { grid-template-columns: 1fr; }
    .step-card { flex-direction: column; gap: 16px; }
    .cta-strip { padding: 32px 24px; }
    .tut-tabs { flex-wrap: wrap; }
    .sec-intro { flex-direction: column; }
    .tut-breadcrumb-inner { padding: 0 20px; }
}
@media(max-width:480px) {
    .tut-hero-inner { padding: 0 16px 36px; }
    .tp-container { padding: 0 16px; }
    .hero-plan-card { flex-direction: column; text-align: center; }
    .plan-badge-free, .plan-badge-paid { margin-left: 0; }
    .tut-steps-row { flex-direction: column; }
}
</style>

{{-- ██████████████████████████████████████████
     SECTION 1: HERO
██████████████████████████████████████████ --}}
<section class="tut-hero" style="background-image: radial-gradient(rgba(255,255,255,.025) 1px, transparent 1px); background-size: 28px 28px;">
    <div class="tut-hero-inner">
        <div>
            <div class="tut-hero-badge">
                <span></span> Complete A–Z Tutorial
            </div>
            <h1 class="tut-hero-title">
                Master <span class="teal">EcomgstPro</span><br>
                in Under 30 Minutes
            </h1>
            <p class="tut-hero-sub">
                Step-by-step tutorials for every feature — from creating your account to filing your first GSTR-1. Designed for CAs, accountants, and business owners.
            </p>
            <div class="tut-steps-row">
                <div class="tut-step-pill"><i class="bi bi-person-plus"></i> Register Free</div>
                <div class="tut-step-pill"><i class="bi bi-credit-card"></i> Choose a Plan</div>
                <div class="tut-step-pill"><i class="bi bi-lightning-charge"></i> Start Filing</div>
            </div>
        </div>
        <div class="hero-plan-cards">
            <div class="hero-plan-card">
                <div class="hero-plan-icon"><i class="bi bi-gift-fill"></i></div>
                <div>
                    <div class="hero-plan-name">Free Forever</div>
                    <div class="hero-plan-desc">1 GSTIN · 100 invoices/month</div>
                </div>
                <div class="plan-badge-free">₹0</div>
            </div>
            <div class="hero-plan-card">
                <div class="hero-plan-icon sky-icon"><i class="bi bi-briefcase-fill"></i></div>
                <div>
                    <div class="hero-plan-name">Professional</div>
                    <div class="hero-plan-desc">5 GSTINs · Unlimited invoices</div>
                </div>
                <div class="plan-badge-paid">₹999/mo</div>
            </div>
            <div class="hero-plan-card">
                <div class="hero-plan-icon gold-icon"><i class="bi bi-building-fill"></i></div>
                <div>
                    <div class="hero-plan-name">Enterprise</div>
                    <div class="hero-plan-desc">Unlimited GSTINs · Custom SLA</div>
                </div>
                <div class="plan-badge-paid">Custom</div>
            </div>
        </div>
    </div>
    <svg class="wave-divider" viewBox="0 0 1440 60" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none"><path d="M0,60 L0,30 Q360,0 720,30 Q1080,60 1440,30 L1440,60 Z" fill="#f0f4ff"/></svg>
</section>

{{-- BREADCRUMB --}}
<div class="tut-breadcrumb">
    <div class="tut-breadcrumb-inner">
        <a href="{{ route('home') }}">Home</a>
        <i class="bi bi-chevron-right"></i>
        <span>Tutorials</span>
    </div>
</div>

{{-- STICKY PROGRESS NAV --}}
<nav class="tut-progress">
    <div class="tut-progress-inner">
        <a class="prog-step done" href="#register"><span class="step-num"><i class="bi bi-check-lg"></i></span> Account Setup</a>
        <div class="prog-divider"></div>
        <a class="prog-step done" href="#dashboard"><span class="step-num"><i class="bi bi-check-lg"></i></span> Dashboard</a>
        <div class="prog-divider"></div>
        <a class="prog-step active" href="#gstin"><span class="step-num">3</span> GSTIN Setup</a>
        <div class="prog-divider"></div>
        <a class="prog-step" href="#bank-converter"><span class="step-num">4</span> Bank Converter</a>
        <div class="prog-divider"></div>
        <a class="prog-step" href="#ecommerce"><span class="step-num">5</span> E-Commerce GST</a>
        <div class="prog-divider"></div>
        <a class="prog-step" href="#reconciliation"><span class="step-num">6</span> Reconciliation</a>
        <div class="prog-divider"></div>
        <a class="prog-step" href="#filing"><span class="step-num">7</span> Filing</a>
        <div class="prog-divider"></div>
        <a class="prog-step" href="#plans"><span class="step-num">8</span> Plans</a>
    </div>
</nav>

{{-- STATS BAR --}}
<div class="overview-strip">
    <div class="tp-container">
        <div class="overview-grid">
            <div class="ov-item">
                <div class="ov-num">8<em>+</em></div>
                <div class="ov-lbl">Tutorial Sections</div>
            </div>
            <div class="ov-item">
                <div class="ov-num">30<em>min</em></div>
                <div class="ov-lbl">Avg. Completion Time</div>
            </div>
            <div class="ov-item">
                <div class="ov-num">5<em>min</em></div>
                <div class="ov-lbl">To File First Return</div>
            </div>
            <div class="ov-item">
                <div class="ov-num">10<em>K+</em></div>
                <div class="ov-lbl">Users Already Trained</div>
            </div>
        </div>
    </div>
</div>


{{-- ██████████████████████████████████████████
     MAIN LAYOUT: SIDEBAR + CONTENT
██████████████████████████████████████████ --}}
<section class="tp-section" style="background: #f0f4ff;">
<div class="tp-container">
<div class="tut-layout">

    {{-- ── SIDEBAR ── --}}
    <aside class="tut-sidebar">
        <div class="sidebar-card">
            <div class="sidebar-header">
                <div class="sidebar-header-title"><i class="bi bi-journal-bookmark-fill me-2" style="color:var(--teal)"></i> Tutorial Index</div>
            </div>
            <nav class="sidebar-nav">
                <a href="#register" class="sidebar-link">
                    <i class="bi bi-person-plus-fill"></i> Account Setup
                </a>
                <a href="#dashboard" class="sidebar-link">
                    <i class="bi bi-speedometer2"></i> Dashboard Tour
                </a>
                <div class="sidebar-divider"></div>
                <a href="#gstin" class="sidebar-link active">
                    <i class="bi bi-qr-code"></i> GSTIN Setup
                </a>
                <a href="#bank-converter" class="sidebar-link">
                    <i class="bi bi-file-pdf-fill"></i> Bank Statement Converter
                </a>
                <a href="#ecommerce" class="sidebar-link">
                    <i class="bi bi-shop"></i> E-Commerce GST
                    <span class="sidebar-badge">Popular</span>
                </a>
                <a href="#reconciliation" class="sidebar-link">
                    <i class="bi bi-arrow-left-right"></i> 2A/2B Reconciliation
                </a>
                <a href="#filing" class="sidebar-link">
                    <i class="bi bi-send-check-fill"></i> Filing Suite
                </a>
                <div class="sidebar-divider"></div>
                <a href="#plans" class="sidebar-link">
                    <i class="bi bi-credit-card-2-front"></i> Plans & Billing
                    <span class="sidebar-badge">New</span>
                </a>
                <a href="#faq" class="sidebar-link">
                    <i class="bi bi-question-circle"></i> FAQs
                </a>
            </nav>
        </div>
        {{-- quick help card --}}
        <div style="background: var(--deep); border-radius: 16px; padding: 20px; margin-top: 16px; border: 1px solid var(--border);">
            <div style="font-family:'Syne',sans-serif; font-weight:700; font-size:14px; color:#fff; margin-bottom:8px;">
                <i class="bi bi-headset" style="color:var(--teal)"></i> Need Help?
            </div>
            <p style="font-size:12.5px; color:var(--muted); margin-bottom:14px; line-height:1.6;">Our CA support team is available Mon–Sat, 9AM–7PM</p>
            <a href="#" style="display:flex; align-items:center; gap:6px; background:rgba(0,201,141,.15); border:1px solid rgba(0,201,141,.2); border-radius:8px; padding:8px 14px; text-decoration:none; font-size:12px; font-weight:700; color:var(--teal);">
                <i class="bi bi-whatsapp"></i> Chat on WhatsApp
            </a>
        </div>
    </aside>

    {{-- ── MAIN CONTENT ── --}}
    <main>

    {{-- ======================================================
         STEP 1: ACCOUNT SETUP / REGISTER
    ====================================================== --}}
    <div id="register" style="margin-bottom: 64px;">
        <div class="sec-intro">
            <div class="sec-intro-icon"><i class="bi bi-person-plus-fill"></i></div>
            <div class="sec-intro-text">
                <div class="label">Step 01 — Getting Started</div>
                <h2>Create Your Account & Login</h2>
                <p>Everything starts here. Creating a EcomgstPro account takes under 2 minutes. No credit card required at signup — start for free and upgrade only when you need more.</p>
            </div>
        </div>

        <div class="tut-alert info">
            <i class="bi bi-info-circle-fill"></i>
            <div>
                <div class="tut-alert-title">Before you start</div>
                <p>Keep your GSTIN number handy. You will need it in Step 3 to connect your GST data. You do not need it to create your account.</p>
            </div>
        </div>

        <div class="step-list">
            <div class="step-card">
                <div class="step-num-circle">1</div>
                <div>
                    <div class="step-card-title">Visit EcomgstPro.com and click "Sign Up Free"</div>
                    <p class="step-card-desc">On the home page, click the green <strong>Sign Up Free</strong> button in the top navigation or the hero section. This takes you to the registration form.</p>
                    <div class="step-tip"><i class="bi bi-lightbulb-fill"></i> You can also sign up via Google for faster access.</div>
                </div>
            </div>

            <div class="step-card">
                <div class="step-num-circle">2</div>
                <div>
                    <div class="step-card-title">Fill in your registration details</div>
                    <p class="step-card-desc">Enter your full name, email address, mobile number, and create a secure password. Make sure your mobile number is active — you will receive an OTP to verify it.</p>
                    <div class="form-mockup" style="margin-top:16px">
                        <div class="form-mockup-title">Create your account</div>
                        <div class="form-row">
                            <label class="form-label">Full Name</label>
                            <input class="form-input filled" type="text" value="Ramesh Kumar" readonly>
                        </div>
                        <div class="form-row">
                            <label class="form-label">Email Address</label>
                            <input class="form-input filled" type="email" value="ramesh@mybusiness.com" readonly>
                        </div>
                        <div class="form-row">
                            <label class="form-label">Mobile Number</label>
                            <input class="form-input filled" type="tel" value="+91 98765 43210" readonly>
                            <div class="form-hint">OTP will be sent to this number</div>
                        </div>
                        <div class="form-row">
                            <label class="form-label">Password</label>
                            <input class="form-input" type="password" value="••••••••••" readonly>
                            <div class="form-hint">Minimum 8 characters with 1 number and 1 special character</div>
                        </div>
                        <button class="form-btn"><i class="bi bi-person-check-fill"></i> Create Account</button>
                    </div>
                </div>
            </div>

            <div class="step-card">
                <div class="step-num-circle">3</div>
                <div>
                    <div class="step-card-title">Verify your email and mobile OTP</div>
                    <p class="step-card-desc">After submitting the form, check your email inbox for a verification link. Separately, enter the 6-digit OTP sent to your mobile number. Both verifications must be completed to activate your account.</p>
                    <div class="step-tip"><i class="bi bi-clock-fill"></i> OTP expires in 10 minutes. Check spam/junk if you don't see the email.</div>
                </div>
            </div>

            <div class="step-card">
                <div class="step-num-circle">4</div>
                <div>
                    <div class="step-card-title">Login to your new account</div>
                    <p class="step-card-desc">Once verified, click the login button on the homepage. Enter your registered email and password. After logging in, you will land on your personal dashboard.</p>
                </div>
            </div>
        </div>

        <div class="tut-alert success" style="margin-top:24px">
            <i class="bi bi-check-circle-fill"></i>
            <div>
                <div class="tut-alert-title">Account created successfully!</div>
                <p>You are now on the <strong>Free Plan</strong> — 1 GSTIN and 100 invoices per month included at no cost. To unlock more, see the Plans & Billing section below.</p>
            </div>
        </div>
    </div>

    {{-- ======================================================
         STEP 2: DASHBOARD TOUR
    ====================================================== --}}
    <div id="dashboard" style="margin-bottom: 64px;">
        <div class="sec-intro">
            <div class="sec-intro-icon"><i class="bi bi-speedometer2"></i></div>
            <div class="sec-intro-text">
                <div class="label">Step 02 — Know Your Workspace</div>
                <h2>Understanding the Dashboard</h2>
                <p>Your dashboard is the control centre. Everything — GSTINs, filing status, ITC health, reports — is accessible from here. Let's walk through each section.</p>
            </div>
        </div>

        <div class="screenshot-wrap">
            <div class="screenshot-topbar">
                <span class="dot-r"></span><span class="dot-y"></span><span class="dot-g"></span>
                <span class="screenshot-url">app.EcomgstPro.com/dashboard</span>
            </div>
            <div class="screenshot-body" style="padding:20px 20px 0; display:block;">
                {{-- Dashboard Mockup --}}
                <div style="background:var(--navy); border-radius:12px 12px 0 0; padding:20px;">
                    {{-- Top row --}}
                    <div style="display:flex; gap:12px; margin-bottom:16px;">
                        <div style="flex:1; background:rgba(0,201,141,.1); border:1px solid rgba(0,201,141,.2); border-radius:12px; padding:16px;">
                            <div style="font-size:11px; color:var(--muted); margin-bottom:4px;">GSTIN Connected</div>
                            <div style="font-family:'Syne',sans-serif; font-weight:800; font-size:24px; color:var(--teal);">1 / 1</div>
                        </div>
                        <div style="flex:1; background:rgba(0,170,255,.08); border:1px solid rgba(0,170,255,.15); border-radius:12px; padding:16px;">
                            <div style="font-size:11px; color:var(--muted); margin-bottom:4px;">Invoices This Month</div>
                            <div style="font-family:'Syne',sans-serif; font-weight:800; font-size:24px; color:var(--sky);">84 / 100</div>
                        </div>
                        <div style="flex:1; background:rgba(255,183,0,.07); border:1px solid rgba(255,183,0,.15); border-radius:12px; padding:16px;">
                            <div style="font-size:11px; color:var(--muted); margin-bottom:4px;">ITC Unclaimed</div>
                            <div style="font-family:'Syne',sans-serif; font-weight:800; font-size:24px; color:#ffb700;">₹12,400</div>
                        </div>
                        <div style="flex:1; background:rgba(0,201,141,.08); border:1px solid rgba(0,201,141,.15); border-radius:12px; padding:16px;">
                            <div style="font-size:11px; color:var(--muted); margin-bottom:4px;">Next Due Date</div>
                            <div style="font-family:'Syne',sans-serif; font-weight:800; font-size:20px; color:#fff;">11 July</div>
                        </div>
                    </div>
                    {{-- Filing status --}}
                    <div style="background:rgba(255,255,255,.04); border-radius:10px; padding:14px 16px;">
                        <div style="font-size:12px; font-weight:700; color:#fff; margin-bottom:12px;">Filing Status — June 2025</div>
                        <div style="display:flex; gap:10px;">
                            @foreach(['GSTR-1' => 'Filed','GSTR-3B' => 'Pending','GSTR-2A' => 'Reconciled','GSTR-9' => 'Due Dec'] as $form => $status)
                            @php $color = $status=='Filed'||$status=='Reconciled' ? 'var(--teal)' : ($status=='Pending'?'#ffb700':'var(--sky)'); @endphp
                            <div style="flex:1; background:rgba(255,255,255,.04); border-radius:8px; padding:10px; text-align:center;">
                                <div style="font-family:'Syne',sans-serif; font-weight:700; font-size:13px; color:#fff;">{{ $form }}</div>
                                <div style="font-size:11px; color:{{ $color }}; margin-top:4px;">{{ $status }}</div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div style="display:grid; grid-template-columns:repeat(2,1fr); gap:16px; margin-top:24px;">
            @php
            $panels = [
                ['bi-grid-fill','Left Sidebar','Quick navigation to all modules — Bank Converter, E-Commerce GST, Reconciliation, and Reports.'],
                ['bi-bell-fill','Notification Bell','Get alerts for upcoming due dates, mismatches found, and filing confirmations.'],
                ['bi-people-fill','User Management','Add team members or your CA/accountant with role-based access (view, edit, or admin).'],
                ['bi-download','Export Centre','Download filed returns, reconciliation reports, and ITC summaries in Excel or PDF.'],
            ];
            @endphp
            @foreach($panels as [$icon,$title,$desc])
            <div style="background:#fff; border:1px solid #e8edf5; border-radius:14px; padding:18px; display:flex; gap:12px; align-items:flex-start;">
                <div style="width:40px; height:40px; border-radius:10px; background:var(--teal-low); display:flex; align-items:center; justify-content:center; color:var(--teal); font-size:18px; flex-shrink:0;">
                    <i class="bi {{ $icon }}"></i>
                </div>
                <div>
                    <div style="font-family:'Syne',sans-serif; font-weight:700; font-size:14px; color:var(--navy); margin-bottom:4px;">{{ $title }}</div>
                    <p style="font-size:12.5px; color:#4a5568; line-height:1.6; margin:0;">{{ $desc }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ======================================================
         STEP 3: GSTIN SETUP
    ====================================================== --}}
    <div id="gstin" style="margin-bottom: 64px;">
        <div class="sec-intro">
            <div class="sec-intro-icon"><i class="bi bi-qr-code"></i></div>
            <div class="sec-intro-text">
                <div class="label">Step 03 — Connect Your Business</div>
                <h2>Add & Verify Your GSTIN</h2>
                <p>Connecting your GSTIN gives EcomgstPro secure, read-only access to the GST portal. This enables auto-fetch of your GSTR-2A/2B, return status, and liability data.</p>
            </div>
        </div>

        <div class="tut-alert warning">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div>
                <div class="tut-alert-title">Free Plan Limit</div>
                <p>Free plan allows <strong>1 GSTIN</strong>. To add more GSTINs, upgrade to Professional (5 GSTINs) or Enterprise (unlimited). See Plans section.</p>
            </div>
        </div>

        <div class="step-list">
            <div class="step-card">
                <div class="step-num-circle">1</div>
                <div>
                    <div class="step-card-title">Go to Settings → GSTIN Management</div>
                    <p class="step-card-desc">From the left sidebar click <strong>Settings</strong>, then select <strong>GSTIN Management</strong>. You will see the "Add New GSTIN" button in the top right.</p>
                </div>
            </div>
            <div class="step-card">
                <div class="step-num-circle">2</div>
                <div>
                    <div class="step-card-title">Enter your 15-digit GSTIN</div>
                    <p class="step-card-desc">Type or paste your GSTIN. The system will auto-validate the format and show your business name from the GST portal for confirmation.</p>
                    <div class="form-mockup" style="margin-top:16px; max-width:100%;">
                        <div class="form-row">
                            <label class="form-label">GSTIN Number</label>
                            <input class="form-input filled" type="text" value="07AAFCS1234D1ZC" readonly>
                            <div class="form-hint" style="color:#1a7a5e; font-weight:600;">✓ Valid GSTIN — SHARMA TRADERS PRIVATE LIMITED, Delhi</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="step-card">
                <div class="step-num-circle">3</div>
                <div>
                    <div class="step-card-title">Authorize GSP Access via OTP</div>
                    <p class="step-card-desc">EcomgstPro is a GSP-certified provider. You will receive an OTP on the registered mobile number linked to your GSTIN. Enter this OTP to authorize secure read-only access. This is a one-time step.</p>
                    <div class="step-tip"><i class="bi bi-shield-check-fill"></i> Read-only access only. EcomgstPro never files without your explicit approval.</div>
                </div>
            </div>
            <div class="step-card">
                <div class="step-num-circle">4</div>
                <div>
                    <div class="step-card-title">GSTIN is now live on your dashboard</div>
                    <p class="step-card-desc">After OTP verification, your GSTIN appears on the dashboard with status indicators for filing, ITC, and upcoming due dates. Data refresh happens every 24 hours automatically.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ======================================================
         STEP 4: BANK STATEMENT CONVERTER
    ====================================================== --}}
    <div id="bank-converter" style="margin-bottom: 64px;">
        <div class="sec-intro">
            <div class="sec-intro-icon"><i class="bi bi-file-pdf-fill"></i></div>
            <div class="sec-intro-text">
                <div class="label">Step 04 — PDF to Tally XML</div>
                <h2>Bank Statement Converter</h2>
                <p>Convert any Indian bank statement PDF into Tally-compatible XML in seconds. Supports 690+ banks. Saves hours of manual data entry every month.</p>
            </div>
        </div>

        {{-- Tabs: by bank type --}}
        <div class="tut-tabs" id="bankTabs">
            <button class="tut-tab active" onclick="switchTab('bank','public')">Public Sector Banks</button>
            <button class="tut-tab" onclick="switchTab('bank','private')">Private Banks</button>
            <button class="tut-tab" onclick="switchTab('bank','foreign')">Foreign Banks</button>
        </div>

        <div id="bank-public" class="tut-tab-panel active">
            <div style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:20px;">
                @foreach(['SBI','PNB','Bank of Baroda','Canara Bank','Union Bank','Indian Bank','UCO Bank','Central Bank','Bank of India'] as $b)
                <span style="background:#f0f4ff; border:1px solid #e8edf5; border-radius:20px; padding:5px 14px; font-size:12px; font-weight:600; color:var(--navy);">{{ $b }}</span>
                @endforeach
                <span style="background:var(--teal-low); border:1px solid rgba(0,201,141,.2); border-radius:20px; padding:5px 14px; font-size:12px; font-weight:600; color:#1a7a5e;">+200 more</span>
            </div>
        </div>
        <div id="bank-private" class="tut-tab-panel">
            <div style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:20px;">
                @foreach(['HDFC','ICICI','Axis','Kotak','Yes Bank','IDFC First','RBL','IndusInd','Federal','South Indian'] as $b)
                <span style="background:#f0f4ff; border:1px solid #e8edf5; border-radius:20px; padding:5px 14px; font-size:12px; font-weight:600; color:var(--navy);">{{ $b }}</span>
                @endforeach
            </div>
        </div>
        <div id="bank-foreign" class="tut-tab-panel">
            <div style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:20px;">
                @foreach(['HSBC','DBS','Deutsche Bank','Barclays','Standard Chartered','Citibank'] as $b)
                <span style="background:#f0f4ff; border:1px solid #e8edf5; border-radius:20px; padding:5px 14px; font-size:12px; font-weight:600; color:var(--navy);">{{ $b }}</span>
                @endforeach
            </div>
        </div>

        <div class="step-list">
            <div class="step-card">
                <div class="step-num-circle">1</div>
                <div>
                    <div class="step-card-title">Navigate to Bank Statement Converter</div>
                    <p class="step-card-desc">From the left sidebar, click <strong>Bank Converter</strong>. Or from the dashboard, click on the "PDF Bank Statement Converter" solution card.</p>
                </div>
            </div>
            <div class="step-card">
                <div class="step-num-circle">2</div>
                <div>
                    <div class="step-card-title">Select your bank from the dropdown</div>
                    <p class="step-card-desc">Use the search-enabled dropdown to find your bank. Type the first 3 letters for quick search. We support 690+ Indian banks including all cooperative and rural banks.</p>
                </div>
            </div>
            <div class="step-card">
                <div class="step-num-circle">3</div>
                <div>
                    <div class="step-card-title">Upload the bank statement PDF</div>
                    <p class="step-card-desc">Drag & drop your PDF file or click "Upload". Accepted formats: PDF, Excel (.xlsx), CSV. Maximum file size: 25MB. Password-protected PDFs are also supported — you will be prompted for the password.</p>
                    <div class="step-tip"><i class="bi bi-lock-fill"></i> Password-protected PDFs are decrypted locally on our servers and never stored.</div>
                </div>
            </div>
            <div class="step-card">
                <div class="step-num-circle">4</div>
                <div>
                    <div class="step-card-title">Review extracted transactions</div>
                    <p class="step-card-desc">The system extracts all transactions and shows a preview table. Review debits, credits, dates, and narrations. You can edit any entry before export.</p>
                </div>
            </div>
            <div class="step-card">
                <div class="step-num-circle">5</div>
                <div>
                    <div class="step-card-title">Export as Tally XML or Excel</div>
                    <p class="step-card-desc">Click <strong>Export to Tally XML</strong> for direct import into Tally ERP/Prime. Or download as Excel for manual review. The Tally XML includes proper ledger mappings.</p>
                    <div class="step-tip"><i class="bi bi-check-circle-fill"></i> Compatible with Tally ERP 9, Tally Prime, and Busy Accounting.</div>
                </div>
            </div>
        </div>

        <div class="highlight-box">
            <div class="highlight-box-title">Supported <em>Output Formats</em></div>
            <p>Choose the format that works best for your accounting workflow.</p>
            <ul class="highlight-box-list">
                <li><i class="bi bi-check-circle-fill"></i> Tally XML — Direct import into Tally ERP 9 / Tally Prime</li>
                <li><i class="bi bi-check-circle-fill"></i> Excel XLSX — Custom column mapping available</li>
                <li><i class="bi bi-check-circle-fill"></i> CSV — For any accounting software</li>
                <li><i class="bi bi-check-circle-fill"></i> Busy XML — Compatible with Busy accounting software</li>
                <li><i class="bi bi-check-circle-fill"></i> Zoho Books format — Auto-import compatible</li>
            </ul>
        </div>
    </div>

    {{-- ======================================================
         STEP 5: E-COMMERCE GST
    ====================================================== --}}
    <div id="ecommerce" style="margin-bottom: 64px;">
        <div class="sec-intro">
            <div class="sec-intro-icon"><i class="bi bi-shop"></i></div>
            <div class="sec-intro-text">
                <div class="label">Step 05 — Marketplace Sellers</div>
                <h2>E-Commerce Seller GSTR-1</h2>
                <p>Filing GSTR-1 as an e-commerce seller on Amazon, Flipkart, Meesho, or other platforms used to be complex. EcomgstPro automates the entire process in 3 steps.</p>
            </div>
        </div>

        <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:14px; margin-bottom:32px;">
            @foreach(['Amazon','Flipkart','Meesho','Snapdeal','Myntra','Ajio','Nykaa','JioMart','Glowroad','Shop 101','Citymall','LimeRoad'] as $platform)
            <div style="background:#fff; border:1px solid #e8edf5; border-radius:12px; padding:12px 16px; display:flex; align-items:center; gap:10px;">
                <i class="bi bi-bag-check-fill" style="color:var(--teal); font-size:16px;"></i>
                <span style="font-size:13px; font-weight:600; color:var(--navy);">{{ $platform }}</span>
            </div>
            @endforeach
        </div>

        <div class="step-list">
            <div class="step-card">
                <div class="step-num-circle">1</div>
                <div>
                    <div class="step-card-title">Download your Seller Report from the marketplace</div>
                    <p class="step-card-desc">Log into your seller account (Amazon Seller Central, Flipkart Seller Hub, etc.) and download the <strong>Tax Report</strong> or <strong>Settlement Report</strong> for the relevant month. Save it as Excel or CSV.</p>
                    <div style="background:#f7faff; border-radius:10px; padding:14px 16px; margin-top:12px; display:flex; flex-direction:column; gap:8px;">
                        <div style="font-size:12px; font-weight:700; color:var(--navy); margin-bottom:2px;">Quick Download Paths</div>
                        <div style="font-size:12.5px; color:#4a5568;"><strong>Amazon:</strong> Seller Central → Reports → Tax Document Library</div>
                        <div style="font-size:12.5px; color:#4a5568;"><strong>Flipkart:</strong> Seller Hub → Finance → Tax Invoices</div>
                        <div style="font-size:12.5px; color:#4a5568;"><strong>Meesho:</strong> Supplier Panel → Payments → Tax Documents</div>
                    </div>
                </div>
            </div>
            <div class="step-card">
                <div class="step-num-circle">2</div>
                <div>
                    <div class="step-card-title">Upload the report to EcomgstPro E-Commerce Module</div>
                    <p class="step-card-desc">In EcomgstPro, go to <strong>E-Commerce GST</strong> from the sidebar. Select your platform from the dropdown, then upload the report file. The AI parser will auto-detect B2B, B2C, and export transactions.</p>
                    <div class="step-tip"><i class="bi bi-robot"></i> Our AI handles different column structures across marketplace report formats automatically.</div>
                </div>
            </div>
            <div class="step-card">
                <div class="step-num-circle">3</div>
                <div>
                    <div class="step-card-title">Review GSTR-1 auto-generated data</div>
                    <p class="step-card-desc">EcomgstPro auto-classifies invoices into tables: B2B (4A), B2C Large (5), B2C Small (7), and CDNR. Review each table, make corrections if needed, and download the GSTR-1 JSON file.</p>
                </div>
            </div>
            <div class="step-card">
                <div class="step-num-circle">4</div>
                <div>
                    <div class="step-card-title">File directly on the GST Portal</div>
                    <p class="step-card-desc">Upload the generated JSON file on the GST portal, or use EcomgstPro's one-click direct filing (Professional plan and above) to push the return without leaving the platform.</p>
                    <div class="step-tip"><i class="bi bi-lightning-charge-fill"></i> Direct filing with DSC or EVC is available on Professional and Enterprise plans.</div>
                </div>
            </div>
        </div>

        <div class="tut-alert info">
            <i class="bi bi-info-circle-fill"></i>
            <div>
                <div class="tut-alert-title">TCS Reconciliation for E-Commerce Sellers</div>
                <p>EcomgstPro automatically reconciles TCS (Tax Collected at Source) deducted by marketplaces with your GSTR-2A. This ensures you never miss claiming TCS credits.</p>
            </div>
        </div>
    </div>

    {{-- ======================================================
         STEP 6: 2A/2B RECONCILIATION
    ====================================================== --}}
    <div id="reconciliation" style="margin-bottom: 64px;">
        <div class="sec-intro">
            <div class="sec-intro-icon"><i class="bi bi-arrow-left-right"></i></div>
            <div class="sec-intro-text">
                <div class="label">Step 06 — Maximize Your ITC</div>
                <h2>2A / 2B Reconciliation</h2>
                <p>Match your purchase register against GSTR-2A and GSTR-2B to find missing invoices, excess claims, and mismatches. Recover every rupee of ITC you are entitled to.</p>
            </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:28px;">
            <div style="background:var(--deep); border:1px solid var(--border); border-radius:16px; padding:24px;">
                <div style="font-size:12px; font-weight:700; letter-spacing:1.5px; text-transform:uppercase; color:var(--teal); margin-bottom:10px;">GSTR-2A</div>
                <div style="font-family:'Syne',sans-serif; font-weight:700; font-size:16px; color:#fff; margin-bottom:8px;">Auto-drafted from Supplier Filings</div>
                <p style="font-size:13px; color:var(--muted); line-height:1.6;">Dynamic — updates as suppliers file their GSTR-1. Use for real-time tracking.</p>
            </div>
            <div style="background:var(--deep); border:1px solid rgba(0,201,141,.25); border-radius:16px; padding:24px;">
                <div style="font-size:12px; font-weight:700; letter-spacing:1.5px; text-transform:uppercase; color:var(--teal); margin-bottom:10px;">GSTR-2B</div>
                <div style="font-family:'Syne',sans-serif; font-weight:700; font-size:16px; color:#fff; margin-bottom:8px;">Static Monthly Statement</div>
                <p style="font-size:13px; color:var(--muted); line-height:1.6;">Fixed on the cut-off date. This is what you use to claim ITC in GSTR-3B.</p>
            </div>
        </div>

        <div class="step-list">
            <div class="step-card">
                <div class="step-num-circle">1</div>
                <div>
                    <div class="step-card-title">Upload your Purchase Register</div>
                    <p class="step-card-desc">Go to <strong>Reconciliation</strong> from the sidebar. Upload your purchase register as Excel or CSV. Columns needed: Invoice Number, Date, Supplier GSTIN, Invoice Value, IGST/CGST/SGST amounts.</p>
                    <div class="step-tip"><i class="bi bi-download"></i> Download our standard Purchase Register template from the upload page.</div>
                </div>
            </div>
            <div class="step-card">
                <div class="step-num-circle">2</div>
                <div>
                    <div class="step-card-title">EcomgstPro fetches GSTR-2A and 2B automatically</div>
                    <p class="step-card-desc">Using your connected GSTIN, EcomgstPro auto-fetches the latest GSTR-2A/2B data from the GST portal. No manual download needed. Select the month and click <strong>Start Reconciliation</strong>.</p>
                </div>
            </div>
            <div class="step-card">
                <div class="step-num-circle">3</div>
                <div>
                    <div class="step-card-title">Review the reconciliation results</div>
                    <p class="step-card-desc">Results are categorised into 4 buckets: Matched (claim ITC), In Books Only (missing from portal), In 2B Only (supplier filed but not in your books), and Mismatched (amount or GSTIN differs).</p>
                    <div style="display:grid; grid-template-columns:repeat(2,1fr); gap:10px; margin-top:12px;">
                        @php
                        $buckets = [
                            ['Matched','124 invoices','₹2,14,800 ITC claimable','var(--teal)'],
                            ['In Books Only','18 invoices','Follow up with suppliers','#ffb700'],
                            ['In 2B Only','7 invoices','Add to purchase register','var(--sky)'],
                            ['Mismatched','3 invoices','Amount difference detected','#ef4444'],
                        ];
                        @endphp
                        @foreach($buckets as [$label,$count,$note,$color])
                        <div style="background:#fff; border:1px solid #e8edf5; border-radius:10px; padding:12px 14px; border-left:4px solid {{ $color }};">
                            <div style="font-family:'Syne',sans-serif; font-weight:700; font-size:13px; color:var(--navy);">{{ $label }}</div>
                            <div style="font-size:15px; font-weight:700; color:{{ $color }}; margin:4px 0;">{{ $count }}</div>
                            <div style="font-size:11.5px; color:#6b7280;">{{ $note }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="step-card">
                <div class="step-num-circle">4</div>
                <div>
                    <div class="step-card-title">Download reconciliation report and file GSTR-3B</div>
                    <p class="step-card-desc">Export a detailed Excel report with all categories. Use the reconciled ITC figure in your GSTR-3B Table 4. EcomgstPro can pre-fill GSTR-3B with the correct ITC amount directly.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ======================================================
         STEP 7: FILING SUITE
    ====================================================== --}}
    <div id="filing" style="margin-bottom: 64px;">
        <div class="sec-intro">
            <div class="sec-intro-icon"><i class="bi bi-send-check-fill"></i></div>
            <div class="sec-intro-text">
                <div class="label">Step 07 — End-to-End Filing</div>
                <h2>GSTR Filing Suite</h2>
                <p>File GSTR-1, GSTR-3B, GSTR-9, and GSTR-9C directly from EcomgstPro with built-in validations, error checks, and audit trails.</p>
            </div>
        </div>

        <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:32px;">
            @php
            $forms = [
                ['GSTR-1','Monthly/Quarterly','Outward supplies','var(--teal)'],
                ['GSTR-3B','Monthly','Summary return','var(--sky)'],
                ['GSTR-9','Annual','Annual return','#ffb700'],
                ['GSTR-9C','Annual','Reconciliation stmt','#c084fc'],
            ];
            @endphp
            @foreach($forms as [$form,$freq,$desc,$color])
            <div style="background:#fff; border:1px solid #e8edf5; border-radius:14px; padding:18px 14px; text-align:center; border-top:3px solid {{ $color }};">
                <div style="font-family:'Syne',sans-serif; font-weight:800; font-size:20px; color:var(--navy);">{{ $form }}</div>
                <div style="font-size:11px; font-weight:700; color:{{ $color }}; text-transform:uppercase; letter-spacing:1px; margin:6px 0 4px;">{{ $freq }}</div>
                <div style="font-size:12px; color:#6b7280;">{{ $desc }}</div>
            </div>
            @endforeach
        </div>

        {{-- GSTR-1 sub-steps --}}
        <div style="font-family:'Syne',sans-serif; font-weight:700; font-size:17px; color:var(--navy); margin-bottom:16px;">
            <i class="bi bi-arrow-right-circle-fill" style="color:var(--teal)"></i> Filing GSTR-1: Step by Step
        </div>
        <div class="step-list" style="margin-bottom:28px;">
            <div class="step-card">
                <div class="step-num-circle">1</div>
                <div>
                    <div class="step-card-title">Import invoice data</div>
                    <p class="step-card-desc">Go to <strong>GSTR-1 Filing</strong>. Import your sales invoices via Excel upload or connect your accounting software (Tally, Zoho Books, QuickBooks). All B2B, B2C, exports, and credit notes are auto-classified.</p>
                </div>
            </div>
            <div class="step-card">
                <div class="step-num-circle">2</div>
                <div>
                    <div class="step-card-title">Run the error checker</div>
                    <p class="step-card-desc">Click <strong>Validate</strong>. EcomgstPro checks for invalid GSTINs, HSN code errors, missing mandatory fields, and tax calculation mismatches. Errors are shown with one-click fix suggestions.</p>
                </div>
            </div>
            <div class="step-card">
                <div class="step-num-circle">3</div>
                <div>
                    <div class="step-card-title">Preview and file</div>
                    <p class="step-card-desc">Review the pre-filled return summary. Sign with DSC (Digital Signature Certificate) or EVC (Electronic Verification Code) and click <strong>File on GST Portal</strong>. You will receive the ARN (Acknowledgment Reference Number) within seconds.</p>
                    <div class="step-tip"><i class="bi bi-lock-fill"></i> DSC signing requires Professional plan or above.</div>
                </div>
            </div>
        </div>

        <div class="checklist">
            <div class="checklist-item done">
                <div class="check-icon"><i class="bi bi-check-lg"></i></div>
                <div class="check-text">All outward B2B invoices uploaded and validated</div>
            </div>
            <div class="checklist-item done">
                <div class="check-icon"><i class="bi bi-check-lg"></i></div>
                <div class="check-text">HSN-wise summary generated</div>
            </div>
            <div class="checklist-item done">
                <div class="check-icon"><i class="bi bi-check-lg"></i></div>
                <div class="check-text">Credit/debit notes reconciled</div>
            </div>
            <div class="checklist-item">
                <div class="check-icon"><i class="bi bi-circle"></i></div>
                <div class="check-text">File GSTR-1 before 11th of next month</div>
            </div>
        </div>
    </div>

    {{-- ======================================================
         STEP 8: PLANS & BILLING
    ====================================================== --}}
    <div id="plans" style="margin-bottom: 64px;">
        <div class="sec-intro">
            <div class="sec-intro-icon"><i class="bi bi-credit-card-2-front"></i></div>
            <div class="sec-intro-text">
                <div class="label">Step 08 — Upgrade When You Need More</div>
                <h2>Plans & Billing Guide</h2>
                <p>Start for free. Upgrade only when your needs grow. No contracts, no hidden charges. Cancel anytime.</p>
            </div>
        </div>

        <div class="tut-alert success">
            <i class="bi bi-check-circle-fill"></i>
            <div>
                <div class="tut-alert-title">Free Plan — What You Get Without Paying Anything</div>
                <p>After registration you are automatically on the Free plan: 1 GSTIN, 100 invoices/month, GSTR-1 and 3B filing, bank statement converter (5 uploads/month), and email support. No time limit — use it as long as you like.</p>
            </div>
        </div>

        <div class="plan-table-wrap" style="margin: 28px 0;">
            <table class="plan-table">
                <thead>
                    <tr>
                        <th>Feature</th>
                        <th>Free</th>
                        <th class="teal-col">Professional ★</th>
                        <th>Enterprise</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>GSTINs</td><td>1</td><td class="pro-col">Up to 5</td><td>Unlimited</td></tr>
                    <tr><td>Invoices/month</td><td>100</td><td class="pro-col">Unlimited</td><td>Unlimited</td></tr>
                    <tr><td>GSTR-1 Filing</td><td><i class="bi bi-check-circle-fill check"></i></td><td class="pro-col"><i class="bi bi-check-circle-fill check"></i></td><td><i class="bi bi-check-circle-fill check"></i></td></tr>
                    <tr><td>GSTR-3B Filing</td><td><i class="bi bi-check-circle-fill check"></i></td><td class="pro-col"><i class="bi bi-check-circle-fill check"></i></td><td><i class="bi bi-check-circle-fill check"></i></td></tr>
                    <tr><td>2A/2B Reconciliation</td><td><i class="bi bi-x-circle cross"></i></td><td class="pro-col"><i class="bi bi-check-circle-fill check"></i></td><td><i class="bi bi-check-circle-fill check"></i></td></tr>
                    <tr><td>Bank Statement Converter</td><td>5/month</td><td class="pro-col">Unlimited</td><td>Unlimited</td></tr>
                    <tr><td>E-Commerce Module</td><td><i class="bi bi-x-circle cross"></i></td><td class="pro-col"><i class="bi bi-check-circle-fill check"></i></td><td><i class="bi bi-check-circle-fill check"></i></td></tr>
                    <tr><td>GSTR-9 / 9C</td><td><i class="bi bi-x-circle cross"></i></td><td class="pro-col"><i class="bi bi-check-circle-fill check"></i></td><td><i class="bi bi-check-circle-fill check"></i></td></tr>
                    <tr><td>Direct Filing (DSC/EVC)</td><td><i class="bi bi-x-circle cross"></i></td><td class="pro-col"><i class="bi bi-check-circle-fill check"></i></td><td><i class="bi bi-check-circle-fill check"></i></td></tr>
                    <tr><td>API Access</td><td><i class="bi bi-x-circle cross"></i></td><td class="pro-col"><i class="bi bi-x-circle cross"></i></td><td><i class="bi bi-check-circle-fill check"></i></td></tr>
                    <tr><td>Dedicated Account Manager</td><td><i class="bi bi-x-circle cross"></i></td><td class="pro-col"><i class="bi bi-x-circle cross"></i></td><td><i class="bi bi-check-circle-fill check"></i></td></tr>
                    <tr><td>Price</td><td><strong>Free</strong></td><td class="pro-col"><strong>₹999/GSTIN/month</strong></td><td><strong>Custom</strong></td></tr>
                </tbody>
            </table>
        </div>

        <div class="step-list">
            <div class="step-card">
                <div class="step-num-circle">1</div>
                <div>
                    <div class="step-card-title">How to upgrade from Free to Professional</div>
                    <p class="step-card-desc">Click <strong>Upgrade</strong> in the top navigation bar or go to <strong>Settings → Plans & Billing</strong>. Select Professional, choose monthly or annual billing (annual saves 20%), and complete payment via UPI, Net Banking, or Credit Card.</p>
                    <div class="step-tip"><i class="bi bi-tag-fill"></i> Annual billing discount: ₹9,999/GSTIN/year instead of ₹11,988. Save ₹1,989.</div>
                </div>
            </div>
            <div class="step-card">
                <div class="step-num-circle">2</div>
                <div>
                    <div class="step-card-title">Adding GSTINs on Professional plan</div>
                    <p class="step-card-desc">On Professional, you can add up to 5 GSTINs. Each additional GSTIN (after the first) is billed at ₹999/month. Go to Settings → GSTIN Management → Add GSTIN to add more. CAs can manage all client GSTINs from a single login.</p>
                </div>
            </div>
            <div class="step-card">
                <div class="step-num-circle">3</div>
                <div>
                    <div class="step-card-title">Cancellation and refund policy</div>
                    <p class="step-card-desc">You can cancel your subscription anytime from Settings → Plans & Billing → Cancel Plan. You retain access until the end of the billing period. Monthly plans are non-refundable after billing date. Annual plans are refundable within 7 days.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ======================================================
         VIDEO TUTORIALS
    ====================================================== --}}
    <div style="margin-bottom: 64px;">
        <div class="tp-eyebrow">Video Guides</div>
        <h2 class="tp-h2">Watch & <em>Learn</em></h2>
        <p class="tp-lead">Short video walkthroughs for every major feature. Each video is under 5 minutes.</p>
        <div class="video-grid" style="margin-top:24px;">
            @php
            $videos = [
                ['Account Setup in 2 Min','Getting Started','2:14'],
                ['Add Your First GSTIN','GSTIN Setup','3:42'],
                ['Bank Statement to Tally XML','Bank Converter','4:10'],
                ['Amazon GSTR-1 Auto-Generate','E-Commerce','4:55'],
                ['2B Reconciliation Walkthrough','Reconciliation','5:30'],
                ['File GSTR-3B in 60 Seconds','Filing','1:58'],
            ];
            @endphp
            @foreach($videos as [$title,$tag,$duration])
            <div class="video-card">
                <div class="video-thumb">
                    <div class="video-thumb-bg"></div>
                    <div class="video-play"><i class="bi bi-play-fill"></i></div>
                    <div class="video-duration">{{ $duration }}</div>
                </div>
                <div class="video-body">
                    <div class="video-tag"><i class="bi bi-tag-fill"></i> {{ $tag }}</div>
                    <div class="video-title">{{ $title }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ======================================================
         FAQ
    ====================================================== --}}
    <div id="faq" style="margin-bottom: 48px;">
        <div class="tp-eyebrow">Common Questions</div>
        <h2 class="tp-h2">Frequently Asked <em>Questions</em></h2>
        <p class="tp-lead" style="margin-bottom:28px;">Answers to the most common questions from new users.</p>

        <div class="faq-list">
            @php
            $faqs = [
                ['Can I use EcomgstPro for free forever?','Yes. The Free plan has no time limit. You can use 1 GSTIN, file GSTR-1 and 3B, and convert up to 5 bank statements per month — completely free, forever. You only need to upgrade when your business grows beyond these limits.'],
                ['What happens after I register — do I need to pay immediately?','No payment is required at registration. You are automatically placed on the Free plan. You will only be asked to pay if you choose to upgrade to Professional or Enterprise.'],
                ['Is my GST data safe with EcomgstPro?','EcomgstPro is ISO 27001 certified and uses bank-grade AES-256 encryption for all data. We are a GSP (GST Suvidha Provider) certified by GSTN, which means we have passed stringent government security audits. We never store your DSC private keys.'],
                ['Can multiple users (like my CA) access the same account?','Yes. On Professional and Enterprise plans, you can invite team members with role-based access — view-only, editor, or admin. Your CA can manage filings without seeing your full business dashboard.'],
                ['Does EcomgstPro support businesses with multiple GSTINs?','The Free plan supports 1 GSTIN. Professional supports up to 5 GSTINs. Enterprise supports unlimited GSTINs. CAs managing multiple clients can create a separate client workspace for each GSTIN or manage them all from a single dashboard.'],
                ['What file formats does the Bank Statement Converter accept?','PDF (including password-protected), Excel (.xlsx, .xls), and CSV. The output can be Tally XML, Excel, CSV, Busy XML, or Zoho Books format.'],
                ['How do I file GSTR-1 on the Free plan?','On the Free plan, EcomgstPro generates the GSTR-1 JSON file which you can manually upload on the GST portal (gst.gov.in). Direct one-click filing to the portal is available on Professional and Enterprise plans only.'],
                ['Can I switch from Professional back to Free?','Yes. Cancel your Professional subscription from Settings → Plans & Billing. At the end of your billing cycle, your account reverts to the Free plan. Your data is retained for 6 months.'],
            ];
            @endphp
            @foreach($faqs as [$q,$a])
            <div class="faq-item">
                <div class="faq-q">
                    <div class="faq-q-text">{{ $q }}</div>
                    <div class="faq-toggle"><i class="bi bi-plus"></i></div>
                </div>
                <div class="faq-a">{{ $a }}</div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- CTA STRIP --}}
    <div class="cta-strip">
        <div>
            <div class="cta-strip-title">Ready to file your first return? <em>It's free.</em></div>
            <p>Join 10,000+ businesses already saving hours every month with EcomgstPro.</p>
        </div>
        <a href="{{ route('register') }}" class="btn-teal">
            <i class="bi bi-person-plus-fill"></i> Create Free Account
        </a>
    </div>

    </main>
</div>
</div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// FAQ Toggle
document.querySelectorAll('.faq-q').forEach(function(q) {
    q.addEventListener('click', function() {
        var item = this.closest('.faq-item');
        document.querySelectorAll('.faq-item.open').forEach(function(o) {
            if (o !== item) o.classList.remove('open');
        });
        item.classList.toggle('open');
    });
});

// Tab switcher
function switchTab(group, key) {
    document.querySelectorAll('#bankTabs .tut-tab').forEach(function(t) { t.classList.remove('active'); });
    document.querySelectorAll('[id^="bank-"]').forEach(function(p) { p.classList.remove('active'); });
    event.target.classList.add('active');
    document.getElementById('bank-' + key).classList.add('active');
}

// Sticky progress highlight on scroll
var sections = ['register','dashboard','gstin','bank-converter','ecommerce','reconciliation','filing','plans'];
window.addEventListener('scroll', function() {
    var scrollY = window.scrollY + 120;
    sections.forEach(function(id, i) {
        var el = document.getElementById(id);
        if (!el) return;
        if (el.offsetTop <= scrollY) {
            document.querySelectorAll('.prog-step').forEach(function(s, j) {
                s.classList.remove('active');
                if (j < i) s.classList.add('done');
                else s.classList.remove('done');
            });
            var active = document.querySelectorAll('.prog-step')[i];
            if (active) active.classList.add('active');
        }
    });
});
</script>

@include('layouts.footer')
@endsection
