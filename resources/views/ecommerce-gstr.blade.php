{{--
    GSTR-1 E-Commerce Tool v5 — COMPLETE
    CHANGES vs v4:
    1. SIDEBAR FIX: Empty space removed, sticky only on desktop, clean overflow
    2. CLIENT PAGE: Separate client management section (same file, scroll-based)
    3. DETAILED TRANSACTION TABLE: New Step 3 with all requested columns:
       Platform | Period | FY Year | Nature | Destination State | HSN | Qty |
       Taxable Amt | Rate | SGST | CGST | IGST | Invoice Amt | TCS
    4. B2CS CSV: Correct columns — Type, Place of Supply, Applicable % of Tax Rate,
       Taxable Value, Cess Amount, E-Commerce GSTIN
    5. DUPLICATE addToB2CS CALLS REMOVED in parseFlipkart & parseGenericPlatform
    6. DUPLICATE VARIABLE DECLARATIONS removed in parseTCS & parseAmazon
--}}
@extends('layouts.app')
@section('content')

<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
:root{
    --navy:#0a1628;--deep:#0d1f3c;--mid:#132240;
    --teal:#00c98d;--sky:#00aaff;
    --teal-low:rgba(0,201,141,.12);--sky-low:rgba(0,170,255,.10);
    --txt:#e8f0fe;--muted:#8a9bbf;--border:rgba(255,255,255,.08);
}
*{box-sizing:border-box;margin:0;padding:0}
html,body{overflow-x:hidden}
body{font-family:'DM Sans',sans-serif;background:#f0f4ff;color:var(--navy)}
.dot-grid{background-image:radial-gradient(rgba(0,0,0,.04) 1px,transparent 1px);background-size:26px 26px}
.tool-hero{background:linear-gradient(135deg,var(--navy) 0%,var(--deep) 55%,#0a2240 100%);padding:56px 0 40px;position:relative;overflow:hidden;}
.tool-hero::before{content:'';position:absolute;width:600px;height:600px;border-radius:50%;background:radial-gradient(circle,rgba(0,170,255,.07) 0%,transparent 70%);top:-180px;right:-120px;pointer-events:none;}
.tool-hero::after{content:'';position:absolute;width:400px;height:400px;border-radius:50%;background:radial-gradient(circle,rgba(0,201,141,.08) 0%,transparent 70%);bottom:-100px;left:-80px;pointer-events:none;}
.hero-inner{max-width:1200px;margin:0 auto;padding:0 32px;position:relative;z-index:2}
.hero-badge{display:inline-flex;align-items:center;gap:8px;background:rgba(0,201,141,.12);border:1px solid rgba(0,201,141,.25);border-radius:30px;padding:5px 14px;font-size:11px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--teal);margin-bottom:16px;}
.hero-badge span{width:6px;height:6px;border-radius:50%;background:var(--teal);animation:blink 1.8s infinite}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.3}}
.tool-title{font-family:'Syne',sans-serif;font-weight:800;font-size:clamp(28px,4vw,48px);line-height:1.1;letter-spacing:-1px;color:#fff;margin-bottom:12px;}
.tool-title em{color:var(--teal);font-style:normal}
.tool-sub{font-size:15px;line-height:1.7;color:var(--muted);max-width:680px}
.breadcrumb-gst{display:flex;align-items:center;gap:8px;font-size:12px;color:var(--muted);margin-bottom:20px;}
.breadcrumb-gst a{color:var(--teal);text-decoration:none}
.breadcrumb-gst i{font-size:10px}

/* ── LAYOUT ──────────────────────────────────────────── */
.tool-shell{max-width:1400px;margin:0 auto;padding:40px 32px 80px;display:grid;grid-template-columns:1fr 300px;gap:24px;align-items:start;}
.tool-wrap{min-width:0}

/* SIDEBAR FIX: sticky only on large screens, no empty space */
.sidebar-col{
    display:flex;flex-direction:column;gap:16px;
    align-self:start;
}
@media(min-width:1101px){
    .sidebar-col{position:sticky;top:20px;}
}
@media(max-width:1100px){
    .tool-shell{grid-template-columns:1fr}
    .sidebar-col{position:static;order:-1}
}
@media(max-width:900px){.upload-grid,.upload-grid-secondary{grid-template-columns:repeat(2,1fr)}.form-row-3{grid-template-columns:1fr 1fr}.client-list{grid-template-columns:repeat(auto-fill,minmax(160px,1fr))}}
@media(max-width:640px){.tool-shell{padding:24px 16px 60px}.hero-inner{padding:0 16px}.upload-grid,.upload-grid-secondary{grid-template-columns:1fr 1fr}.form-row,.form-row-3{grid-template-columns:1fr}.step-body{padding:18px 16px}.step-header{padding:14px 16px}.compute-bar{padding:20px}.action-row{flex-direction:column;align-items:stretch}.mode-tabs{flex-direction:column}}

.step-card{background:#fff;border-radius:20px;border:1px solid #e8edf5;margin-bottom:24px;overflow:hidden;box-shadow:0 2px 16px rgba(0,0,0,.04);transition:box-shadow .2s;}
.step-card:hover{box-shadow:0 6px 30px rgba(0,0,0,.08)}
.step-header{display:flex;align-items:center;gap:16px;padding:20px 28px;border-bottom:1px solid #f0f4ff;background:linear-gradient(90deg,#fafcff,#fff);}
.step-num{width:36px;height:36px;border-radius:10px;background:var(--teal-low);border:1px solid rgba(0,201,141,.2);display:flex;align-items:center;justify-content:center;font-family:'Syne',sans-serif;font-weight:800;font-size:15px;color:var(--teal);flex-shrink:0;}
.step-title{font-family:'Syne',sans-serif;font-weight:700;font-size:17px;color:var(--navy)}
.step-desc{font-size:13px;color:#6b7a99;margin-top:2px}
.step-body{padding:24px 28px}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px}
.form-row-3{grid-template-columns:1fr 1fr 1fr}
.form-group{display:flex;flex-direction:column;gap:6px}
.form-label{font-size:12px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:#6b7a99;}
.form-label i{color:var(--teal);margin-right:4px}
.form-control-gst{padding:11px 14px;border:1.5px solid #e8edf5;border-radius:12px;font-family:'DM Sans',sans-serif;font-size:14px;color:var(--navy);background:#fff;transition:all .2s;outline:none;}
.form-control-gst:focus{border-color:var(--teal);box-shadow:0 0 0 3px rgba(0,201,141,.1)}
.form-control-gst::placeholder{color:#b0bcd4}
select.form-control-gst{cursor:pointer;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%2300c98d' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 12px center;appearance:none;padding-right:36px;}

/* Client Manager */
.client-manager-wrap{background:linear-gradient(135deg,#f7faff 0%,#eef3fc 100%);border:1.5px solid #dde6f5;border-radius:20px;padding:24px 28px;margin-bottom:24px;}
.client-manager-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:12px;}
.client-manager-title{font-family:'Syne',sans-serif;font-weight:800;font-size:18px;color:var(--navy)}
.client-search-wrap{position:relative;margin-bottom:16px}
.client-search-wrap i{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:15px;pointer-events:none}
.client-search-input{width:100%;padding:10px 14px 10px 38px;border:1.5px solid #e8edf5;border-radius:12px;font-family:'DM Sans',sans-serif;font-size:14px;color:var(--navy);background:#fff;transition:all .2s;outline:none;}
.client-search-input:focus{border-color:var(--teal);box-shadow:0 0 0 3px rgba(0,201,141,.1)}
.client-list{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px;margin-bottom:16px;}
.client-card{background:#fff;border:1.5px solid #e8edf5;border-radius:14px;padding:14px 16px;cursor:pointer;transition:all .2s;display:flex;align-items:center;gap:12px;position:relative;}
.client-card:hover{border-color:var(--teal);box-shadow:0 4px 16px rgba(0,201,141,.12)}
.client-card.selected{border-color:var(--teal);background:rgba(0,201,141,.04);box-shadow:0 4px 20px rgba(0,201,141,.18)}
.client-card.selected::after{content:'✓';position:absolute;top:8px;right:10px;width:20px;height:20px;border-radius:50%;background:var(--teal);color:#fff;font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;line-height:20px;text-align:center;}
.client-avatar{width:40px;height:40px;border-radius:12px;background:var(--teal-low);border:1px solid rgba(0,201,141,.2);display:flex;align-items:center;justify-content:center;font-family:'Syne',sans-serif;font-weight:800;font-size:14px;color:var(--teal);flex-shrink:0;}
.client-name{font-weight:600;font-size:13px;color:var(--navy);line-height:1.3}
.client-gstin{font-size:11px;color:#94a3b8;margin-top:2px;font-family:'Courier New',monospace}
.client-info,.client-del{position:absolute;top:6px;width:18px;height:18px;border:none;border-radius:50%;display:none;align-items:center;justify-content:center;font-size:10px;cursor:pointer;transition:all .2s;}
.client-info{right:32px;background:#e0f2fe;color:#0284c7;}
.client-del{right:6px;background:#fee2e2;color:#ef4444;}
.client-card:hover .client-info,.client-card:hover .client-del{display:flex}
.client-card.selected .client-del{right:54px}
.no-clients{text-align:center;padding:30px;color:#94a3b8;font-size:14px;border:2px dashed #e8edf5;border-radius:14px;}

/* Modals */
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;overflow-y:auto;padding:16px;}
.modal-overlay.show{display:flex}
.modal-box{background:#fff;border-radius:20px;padding:32px;width:100%;max-width:480px;box-shadow:0 20px 60px rgba(0,0,0,.2);position:relative;margin:auto;}
.modal-title{font-family:'Syne',sans-serif;font-weight:800;font-size:20px;color:var(--navy);margin-bottom:20px}
.modal-close{position:absolute;top:16px;right:16px;background:none;border:none;cursor:pointer;font-size:18px;color:#94a3b8;padding:4px}
.modal-close:hover{color:var(--navy)}
.detail-modal-box{max-width:520px}
.detail-row{display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:1px solid #f0f4ff;}
.detail-row:last-child{border-bottom:none}
.detail-label{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#94a3b8}
.detail-value{font-size:14px;font-weight:600;color:var(--navy);text-align:right;font-family:'Courier New',monospace}
.detail-avatar-lg{width:64px;height:64px;border-radius:18px;background:var(--teal-low);border:2px solid rgba(0,201,141,.2);display:flex;align-items:center;justify-content:center;font-family:'Syne',sans-serif;font-weight:800;font-size:24px;color:var(--teal);margin:0 auto 16px;flex-shrink:0;}

/* Upload zones */
.upload-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}
.upload-grid-secondary{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-top:14px}
.platform-group-label{font-family:'Syne',sans-serif;font-weight:700;font-size:12px;letter-spacing:1px;text-transform:uppercase;color:#6b7a99;padding:10px 0 6px;border-bottom:1px solid #f0f4ff;margin-bottom:12px;display:flex;align-items:center;gap:8px}
.platform-group-label i{color:var(--teal)}
.upload-zone{border:2px dashed #dde3ef;border-radius:16px;padding:18px 12px;text-align:center;cursor:pointer;transition:all .2s;background:#fafcff;position:relative;overflow:hidden;}
.upload-zone:hover,.upload-zone.drag-over{border-color:var(--teal);background:rgba(0,201,141,.03);}
.upload-zone.has-file{border-color:var(--teal);border-style:solid;background:rgba(0,201,141,.05);}
.upload-zone input[type=file]{position:absolute;inset:0;opacity:0;cursor:pointer;z-index:2;}
.uz-icon{width:40px;height:40px;border-radius:12px;background:rgba(0,201,141,.1);display:flex;align-items:center;justify-content:center;margin:0 auto 8px;font-size:18px;color:var(--teal);transition:all .2s;}
.upload-zone:hover .uz-icon,.upload-zone.has-file .uz-icon{background:rgba(0,201,141,.2)}
.uz-name{font-family:'Syne',sans-serif;font-weight:700;font-size:12px;color:var(--navy);margin-bottom:3px}
.uz-hint{font-size:10px;color:#94a3b8;line-height:1.4}
.uz-filename{margin-top:6px;font-size:10px;color:var(--teal);font-weight:600;display:none;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:100%;}
.upload-zone.has-file .uz-filename{display:block}
.uz-tag{display:inline-block;font-size:9px;font-weight:700;letter-spacing:.8px;text-transform:uppercase;padding:2px 8px;border-radius:20px;margin-bottom:5px;}
.uz-tag.amazon{background:rgba(255,153,0,.12);color:#c07000}
.uz-tag.flipkart{background:rgba(100,149,237,.12);color:#3a66c7}
.uz-tag.meesho{background:rgba(160,32,240,.12);color:#8020d0}
.uz-tag.tcs{background:rgba(0,201,141,.12);color:#008060}
.uz-tag.optional{background:rgba(148,163,184,.12);color:#64748b}
.uz-tag.ajio{background:rgba(220,38,38,.12);color:#b91c1c}
.uz-tag.myntra{background:rgba(234,88,12,.12);color:#c2410c}
.uz-tag.snapdeal{background:rgba(220,38,38,.12);color:#991b1b}
.uz-tag.nykaa{background:rgba(236,72,153,.12);color:#be185d}
.uz-tag.jiomart{background:rgba(37,99,235,.12);color:#1d4ed8}
.uz-tag.glowroad{background:rgba(5,150,105,.12);color:#047857}
.uz-tag.shopsy{background:rgba(79,70,229,.12);color:#4338ca}
.parse-mode-wrap{display:flex;align-items:center;gap:8px;margin-bottom:16px;padding:10px 14px;background:rgba(0,170,255,.06);border:1px solid rgba(0,170,255,.15);border-radius:12px;flex-wrap:wrap;}
.parse-mode-badge{font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px;}
.parse-mode-badge.backend{background:rgba(0,201,141,.15);color:#007050}
.parse-mode-badge.frontend{background:rgba(0,170,255,.15);color:#005fa0}

/* Bulk */
.bulk-drop-zone{border:2px dashed #dde3ef;border-radius:16px;padding:40px 24px;text-align:center;cursor:pointer;transition:all .2s;background:#fff;position:relative;}
.bulk-drop-zone.drag-active{border-color:var(--teal);background:rgba(0,201,141,.04);}
.bulk-drop-zone input[type=file]{position:absolute;inset:0;opacity:0;cursor:pointer;z-index:2;}
.bulk-icon{width:56px;height:56px;border-radius:16px;background:rgba(0,170,255,.1);display:flex;align-items:center;justify-content:center;margin:0 auto 14px;font-size:26px;color:var(--sky);}
.bulk-file-list{margin-top:16px;display:flex;flex-direction:column;gap:8px;max-height:260px;overflow-y:auto;}
.bulk-file-item{display:flex;align-items:center;gap:10px;background:#fff;border:1px solid #e8edf5;border-radius:10px;padding:10px 14px;}
.bulk-file-icon{width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0;}
.bulk-file-name{font-size:13px;font-weight:600;color:var(--navy);flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.bulk-file-remove{background:none;border:none;cursor:pointer;color:#94a3b8;font-size:14px;padding:2px 4px;transition:color .2s;}
.bulk-file-remove:hover{color:#ef4444}
.bulk-detect-badge{display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:700;padding:2px 10px;border-radius:20px;background:rgba(0,201,141,.1);color:#008060;border:1px solid rgba(0,201,141,.2);}

/* Buttons */
.btn-teal{font-family:'DM Sans',sans-serif;font-weight:700;font-size:14px;color:var(--navy);background:linear-gradient(135deg,var(--teal) 0%,#00e0a8 100%);border:none;border-radius:12px;padding:12px 24px;cursor:pointer;display:inline-flex;align-items:center;gap:8px;box-shadow:0 4px 18px rgba(0,201,141,.3);transition:all .2s;text-decoration:none;}
.btn-teal:hover{transform:translateY(-2px);box-shadow:0 8px 28px rgba(0,201,141,.45);color:var(--navy)}
.btn-teal:disabled{opacity:.4;cursor:not-allowed;transform:none}
.btn-navy{font-family:'DM Sans',sans-serif;font-weight:700;font-size:14px;color:#fff;background:var(--navy);border:none;border-radius:12px;padding:12px 24px;cursor:pointer;display:inline-flex;align-items:center;gap:8px;transition:all .2s;text-decoration:none;}
.btn-navy:hover{background:var(--deep);transform:translateY(-1px);color:#fff}
.btn-navy:disabled{opacity:.4;cursor:not-allowed;transform:none}
.btn-outline-teal{font-family:'DM Sans',sans-serif;font-weight:700;font-size:14px;color:var(--teal);background:transparent;border:1.5px solid var(--teal);border-radius:12px;padding:11px 22px;cursor:pointer;display:inline-flex;align-items:center;gap:8px;transition:all .2s;text-decoration:none;}
.btn-outline-teal:hover{background:var(--teal-low);transform:translateY(-1px)}
.btn-outline-teal:disabled{opacity:.4;cursor:not-allowed}
.btn-sky{font-family:'DM Sans',sans-serif;font-weight:700;font-size:14px;color:#fff;background:linear-gradient(135deg,var(--sky) 0%,#0088dd 100%);border:none;border-radius:12px;padding:12px 24px;cursor:pointer;display:inline-flex;align-items:center;gap:8px;box-shadow:0 4px 18px rgba(0,170,255,.3);transition:all .2s;text-decoration:none;}
.btn-sky:hover{transform:translateY(-2px);box-shadow:0 8px 28px rgba(0,170,255,.45);color:#fff}
.btn-sm{padding:7px 14px;font-size:12px;border-radius:10px}
.action-row{display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-top:8px}

/* Compute bar */
.compute-bar{background:linear-gradient(135deg,var(--navy) 0%,var(--deep) 100%);border-radius:20px;padding:28px 32px;display:flex;align-items:center;justify-content:space-between;gap:20px;flex-wrap:wrap;margin-bottom:24px;border:1px solid var(--border);}
.compute-bar-left h3{font-family:'Syne',sans-serif;font-weight:800;font-size:18px;color:#fff;margin-bottom:4px;}
.compute-bar-left p{font-size:13px;color:var(--muted)}
.status-badge{display:inline-flex;align-items:center;gap:6px;padding:4px 12px;border-radius:20px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;}
.status-idle{background:rgba(148,163,184,.12);color:#94a3b8}
.status-processing{background:rgba(0,170,255,.12);color:var(--sky)}
.status-done{background:rgba(0,201,141,.12);color:var(--teal)}
.status-error{background:rgba(239,68,68,.12);color:#ef4444}

/* Tables */
.platform-report-block{border:1px solid #e8edf5;border-radius:16px;overflow:hidden;margin-bottom:20px;}
.platform-report-header{display:flex;align-items:center;gap:14px;padding:16px 20px;background:#f7faff;border-bottom:1px solid #e8edf5;}
.platform-icon-wrap{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;}
.platform-report-title{font-family:'Syne',sans-serif;font-weight:700;font-size:15px;color:var(--navy)}
.platform-report-meta{font-size:12px;color:#6b7a99;margin-top:2px}
.platform-status-pill{margin-left:auto;padding:4px 12px;border-radius:20px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;}
.psp-ok{background:rgba(0,201,141,.1);color:#008060}
.psp-warn{background:rgba(245,158,11,.1);color:#92500a}
.preview-section{display:none}
.preview-section.visible{display:block}
.preview-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:12px;}
.preview-title{font-family:'Syne',sans-serif;font-weight:700;font-size:18px;color:var(--navy);}
.preview-subtitle{font-size:13px;color:#6b7a99;margin-top:2px}
.summary-chips{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px}
.chip{display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:20px;font-size:12px;font-weight:600;border:1px solid;}
.chip-teal{background:rgba(0,201,141,.08);border-color:rgba(0,201,141,.2);color:#00a070}
.chip-sky{background:rgba(0,170,255,.08);border-color:rgba(0,170,255,.2);color:#0070bb}
.chip-navy{background:rgba(10,22,40,.06);border-color:rgba(10,22,40,.12);color:var(--navy)}
.chip-purple{background:rgba(160,32,240,.08);border-color:rgba(160,32,240,.2);color:#6010a0}
.chip-warn{background:rgba(245,158,11,.08);border-color:rgba(245,158,11,.2);color:#92500a}
.table-wrap{overflow-x:auto;border-radius:16px;border:1px solid #e8edf5}
table.gst-table{width:100%;border-collapse:collapse;font-size:13px}
.gst-table thead th{background:#f7faff;padding:10px 14px;font-weight:700;font-size:11px;letter-spacing:.5px;text-transform:uppercase;color:#6b7a99;border-bottom:1px solid #e8edf5;white-space:nowrap;text-align:right;}
.gst-table thead th:first-child,.gst-table thead th:nth-child(2),.gst-table thead th:nth-child(3),.gst-table thead th:nth-child(4){text-align:left}
.gst-table tbody td{padding:10px 14px;border-bottom:1px solid #f0f4ff;color:var(--navy);vertical-align:middle;text-align:right;}
.gst-table tbody td:first-child,.gst-table tbody td:nth-child(2),.gst-table tbody td:nth-child(3),.gst-table tbody td:nth-child(4){text-align:left}
.gst-table tbody tr:hover{background:#f9fbff}
.gst-table tbody tr:last-child td{border-bottom:none}

/* Detailed transaction table — wider columns, left-align all */
table.detail-table{width:100%;border-collapse:collapse;font-size:12px;min-width:1200px}
.detail-table thead th{background:#f0f4ff;padding:9px 12px;font-weight:700;font-size:10px;letter-spacing:.5px;text-transform:uppercase;color:#6b7a99;border-bottom:1.5px solid #e8edf5;white-space:nowrap;text-align:left;}
.detail-table thead th.num{text-align:right}
.detail-table tbody td{padding:9px 12px;border-bottom:1px solid #f0f4ff;color:var(--navy);vertical-align:middle;font-size:12px;text-align:left;}
.detail-table tbody td.num{text-align:right;font-family:'Courier New',monospace;font-size:11px;}
.detail-table tbody tr:hover{background:#f9fbff}
.detail-table tfoot td{font-weight:700;background:#f7faff;border-top:2px solid #e8edf5;color:var(--navy);font-size:12px;text-align:right;padding:9px 12px;}
.detail-table tfoot td:first-child{text-align:left}

.badge-intra{display:inline-block;font-size:10px;font-weight:700;padding:2px 8px;border-radius:20px;background:rgba(0,201,141,.1);color:#008060;}
.badge-inter{display:inline-block;font-size:10px;font-weight:700;padding:2px 8px;border-radius:20px;background:rgba(0,170,255,.1);color:#005fa0;}
.rate-pill{display:inline-block;font-size:10px;font-weight:700;padding:2px 8px;border-radius:20px;background:#f0f4ff;color:var(--navy);}
.tfoot-row td{font-weight:700;background:#f7faff;border-top:2px solid #e8edf5;color:var(--navy);}
.neg-val{color:#ef4444}
.json-box{background:var(--navy);border-radius:16px;border:1px solid var(--border);overflow:hidden;margin-top:16px;}
.json-box-header{display:flex;align-items:center;justify-content:space-between;padding:12px 20px;border-bottom:1px solid var(--border);}
.json-box-header span{font-family:'Syne',sans-serif;font-weight:700;font-size:13px;color:var(--muted);}
.json-pre{padding:20px;font-family:'Courier New',monospace;font-size:12px;line-height:1.7;color:#8be9fd;max-height:360px;overflow-y:auto;white-space:pre-wrap;word-break:break-all;}
.json-pre::-webkit-scrollbar{width:4px}
.json-pre::-webkit-scrollbar-track{background:rgba(255,255,255,.05)}
.json-pre::-webkit-scrollbar-thumb{background:var(--teal);border-radius:4px}
.progress-bar-wrap{background:#e8edf5;border-radius:8px;height:6px;overflow:hidden;margin-top:12px;display:none;}
.progress-bar-fill{height:100%;border-radius:8px;width:0%;background:linear-gradient(90deg,var(--teal),var(--sky));transition:width .3s ease;}
.log-box{background:#f7faff;border-radius:12px;padding:14px 16px;margin-top:14px;font-size:12px;line-height:1.9;color:#6b7a99;font-family:'Courier New',monospace;max-height:220px;overflow-y:auto;display:none;border:1px solid #e8edf5;}
.log-box .log-warn{color:#b45309;font-weight:600}
.log-box .log-err{color:#c0392b;font-weight:600}
.alert-gst{padding:12px 16px;border-radius:12px;font-size:13px;display:flex;align-items:flex-start;gap:10px;margin-bottom:16px;}
.alert-info{background:rgba(0,170,255,.08);border:1px solid rgba(0,170,255,.2);color:#005fa0}
.alert-success{background:rgba(0,201,141,.08);border:1px solid rgba(0,201,141,.2);color:#007050}
.alert-warn{background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.2);color:#92500a}
.alert-error{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);color:#9b1c1c}
.alert-gst i{font-size:15px;flex-shrink:0;margin-top:1px}
.selected-client-bar{background:linear-gradient(135deg,rgba(0,201,141,.08),rgba(0,170,255,.06));border:1.5px solid rgba(0,201,141,.25);border-radius:14px;padding:14px 20px;display:flex;align-items:center;gap:14px;margin-bottom:20px;}
.scb-avatar{width:44px;height:44px;border-radius:12px;background:var(--teal-low);border:1px solid rgba(0,201,141,.2);display:flex;align-items:center;justify-content:center;font-family:'Syne',sans-serif;font-weight:800;font-size:16px;color:var(--teal);}
.scb-name{font-family:'Syne',sans-serif;font-weight:700;font-size:16px;color:var(--navy)}
.scb-gstin{font-size:12px;color:#6b7a99;font-family:'Courier New',monospace;margin-top:2px}
.mode-tabs{display:flex;gap:4px;background:#f0f4ff;border-radius:14px;padding:4px;margin-bottom:20px;}
.mode-tab{flex:1;text-align:center;padding:9px 12px;border-radius:11px;font-weight:700;font-size:13px;cursor:pointer;transition:all .2s;border:none;color:#6b7a99;background:transparent;}
.mode-tab.active{background:#fff;color:var(--navy);box-shadow:0 2px 10px rgba(0,0,0,.08)}
.toast-wrap{position:fixed;bottom:24px;right:24px;z-index:9999;display:flex;flex-direction:column;gap:8px;}
.toast{background:var(--navy);color:#fff;padding:12px 20px;border-radius:14px;font-size:13px;font-weight:600;display:flex;align-items:center;gap:10px;box-shadow:0 8px 30px rgba(0,0,0,.25);animation:slideUp .3s ease;max-width:320px;}
.toast.success{background:#00935e}.toast.error{background:#c0392b}.toast.warn{background:#b45309}
@keyframes slideUp{from{transform:translateY(20px);opacity:0}to{transform:translateY(0);opacity:1}}

/* ── SIDEBAR ─────────────────────────────────────────── */
.sb-card{background:#fff;border-radius:18px;border:1px solid #e8edf5;box-shadow:0 2px 16px rgba(0,0,0,.04);overflow:hidden;}
.sb-card-header{display:flex;align-items:center;gap:10px;padding:16px 18px;border-bottom:1px solid #f0f4ff;background:linear-gradient(90deg,#fafcff,#fff);}
.sb-card-header i{color:var(--teal);font-size:16px}
.sb-card-title{font-family:'Syne',sans-serif;font-weight:700;font-size:14px;color:var(--navy)}
.sb-card-sub{font-size:11px;color:#94a3b8;margin-top:1px}
/* FIX: max-height auto, no extra bottom padding causing empty space */
.sb-card-body{padding:14px 18px;max-height:400px;overflow-y:auto;}
.sb-empty{text-align:center;padding:20px 10px;color:#94a3b8;font-size:12px;}
.sb-empty i{font-size:22px;display:block;margin-bottom:8px;color:#dde3ef}
.sb-client-row{display:flex;align-items:center;gap:10px;padding:8px 8px;border-radius:10px;cursor:pointer;transition:background .15s;}
.sb-client-row:hover{background:#f7faff}
.sb-client-row.active{background:rgba(0,201,141,.08)}
.sb-client-avatar{width:30px;height:30px;border-radius:9px;background:var(--teal-low);display:flex;align-items:center;justify-content:center;font-family:'Syne',sans-serif;font-weight:800;font-size:11px;color:var(--teal);flex-shrink:0;}
.sb-client-name{font-size:12.5px;font-weight:700;color:var(--navy);line-height:1.3}
.sb-client-state{font-size:10.5px;color:#94a3b8;margin-top:1px}
.sb-state-name{font-size:11px;font-weight:700;color:#6b7a99;text-transform:uppercase;letter-spacing:.4px;padding:6px 8px 4px;}
.sb-state-group{margin-bottom:8px;}

/* Section divider */
.section-divider{display:flex;align-items:center;gap:14px;margin:32px 0 24px;}
.section-divider-line{flex:1;height:1px;background:#e8edf5}
.section-divider-title{font-family:'Syne',sans-serif;font-weight:800;font-size:20px;color:var(--navy);white-space:nowrap}

/* Detail report tabs */
.report-tabs{display:flex;gap:4px;background:#f0f4ff;border-radius:14px;padding:4px;margin-bottom:20px;flex-wrap:wrap;}
.report-tab{flex:1;min-width:120px;text-align:center;padding:9px 12px;border-radius:11px;font-weight:700;font-size:13px;cursor:pointer;transition:all .2s;border:none;color:#6b7a99;background:transparent;}
.report-tab.active{background:#fff;color:var(--navy);box-shadow:0 2px 10px rgba(0,0,0,.08)}
</style>

<!-- HERO -->
<section class="tool-hero">
    <div class="hero-inner">
        <div class="breadcrumb-gst">
            <a href="{{ route('home') }}">Home</a>
            <i class="bi bi-chevron-right"></i>
            <a href="#">E-Commerce GST Suite</a>
            <i class="bi bi-chevron-right"></i>
            <span style="color:#fff">GSTR-1 Generator</span>
        </div>
        <div class="hero-badge"><span></span> E-Commerce GSTR-1 Tool</div>
        <h1 class="tool-title">One-Click <em>GSTR-1</em><br>for E-Commerce Sellers</h1>
        <p class="tool-sub">Client-wise GSTR-1 generator — supports <strong>10+ platforms</strong>: Amazon, Flipkart, Meesho, Ajio, Myntra, Snapdeal, Nykaa, JioMart, Glowroad, Shopsy. Smart ML parsing — zero column errors.</p>
    </div>
</section>

<div class="toast-wrap" id="toastWrap"></div>

<!-- MODAL: Add/Edit Client -->
<div class="modal-overlay" id="clientModal">
    <div class="modal-box">
        <button class="modal-close" onclick="closeClientModal()"><i class="bi bi-x-lg"></i></button>
        <div class="modal-title" id="clientModalTitle"><i class="bi bi-person-plus-fill" style="color:var(--teal);margin-right:8px"></i>Add New Client</div>
        <input type="hidden" id="editClientId">
        <div class="form-group" style="margin-bottom:14px">
            <label class="form-label"><i class="bi bi-person"></i> Client / Business Name *</label>
            <input type="text" id="newClientName" class="form-control-gst" placeholder="e.g. Ravi Textiles Pvt Ltd">
        </div>
        <div class="form-group" style="margin-bottom:14px">
            <label class="form-label"><i class="bi bi-building"></i> GSTIN *</label>
            <input type="text" id="newClientGstin" class="form-control-gst" placeholder="e.g. 32QRRPS3983N1ZD" maxlength="15" style="text-transform:uppercase">
            <div id="gstinError" style="color:#ef4444;font-size:12px;margin-top:4px;display:none"></div>
        </div>
        <div class="form-row" style="margin-bottom:14px">
            <div class="form-group">
                <label class="form-label"><i class="bi bi-geo-alt"></i> Home State</label>
                <select id="newClientState" class="form-control-gst">
                    <option value="01">01 – Jammu & Kashmir</option><option value="02">02 – Himachal Pradesh</option>
                    <option value="03">03 – Punjab</option><option value="04">04 – Chandigarh</option>
                    <option value="05">05 – Uttarakhand</option><option value="06">06 – Haryana</option>
                    <option value="07">07 – Delhi</option><option value="08">08 – Rajasthan</option>
                    <option value="09">09 – Uttar Pradesh</option><option value="10">10 – Bihar</option>
                    <option value="11">11 – Sikkim</option><option value="12">12 – Arunachal Pradesh</option>
                    <option value="13">13 – Nagaland</option><option value="14">14 – Manipur</option>
                    <option value="15">15 – Mizoram</option><option value="16">16 – Tripura</option>
                    <option value="17">17 – Meghalaya</option><option value="18">18 – Assam</option>
                    <option value="19">19 – West Bengal</option><option value="20">20 – Jharkhand</option>
                    <option value="21">21 – Odisha</option><option value="22">22 – Chhattisgarh</option>
                    <option value="23">23 – Madhya Pradesh</option><option value="24">24 – Gujarat</option>
                    <option value="26">26 – Dadra & Nagar Haveli and Daman & Diu</option>
                    <option value="27">27 – Maharashtra</option><option value="29">29 – Karnataka</option>
                    <option value="30">30 – Goa</option><option value="31">31 – Lakshadweep</option>
                    <option value="32">32 – Kerala</option><option value="33">33 – Tamil Nadu</option>
                    <option value="34">34 – Puducherry</option><option value="35">35 – Andaman & Nicobar Islands</option>
                    <option value="36">36 – Telangana</option><option value="37">37 – Andhra Pradesh</option>
                    <option value="38">38 – Ladakh</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label"><i class="bi bi-telephone"></i> Phone (Optional)</label>
                <input type="text" id="newClientPhone" class="form-control-gst" placeholder="9876543210">
            </div>
        </div>
        <div class="form-group" style="margin-bottom:20px">
            <label class="form-label"><i class="bi bi-envelope"></i> Email (Optional)</label>
            <input type="email" id="newClientEmail" class="form-control-gst" placeholder="example@domain.com">
        </div>
        <div style="display:flex;gap:10px">
            <button class="btn-teal" onclick="saveClient()" style="flex:1;justify-content:center">
                <i class="bi bi-check2-circle"></i> <span id="saveClientBtnText">Save Client</span>
            </button>
            <button class="btn-outline-teal" onclick="closeClientModal()">Cancel</button>
        </div>
    </div>
</div>

<!-- MODAL: Client Detail -->
<div class="modal-overlay" id="clientDetailModal">
    <div class="modal-box detail-modal-box">
        <button class="modal-close" onclick="closeDetailModal()"><i class="bi bi-x-lg"></i></button>
        <div style="text-align:center;margin-bottom:20px">
            <div class="detail-avatar-lg" id="detailAvatar">--</div>
            <div style="font-family:'Syne',sans-serif;font-weight:800;font-size:20px;color:var(--navy)" id="detailName">—</div>
            <div style="font-size:12px;color:#94a3b8;margin-top:4px" id="detailCreated">—</div>
        </div>
        <div id="detailRows"></div>
        <div style="display:flex;gap:10px;margin-top:20px;flex-wrap:wrap">
            <button class="btn-teal btn-sm" onclick="selectFromDetail()" style="flex:1;justify-content:center"><i class="bi bi-check2-circle"></i> Select This Client</button>
            <button class="btn-outline-teal btn-sm" onclick="editFromDetail()"><i class="bi bi-pencil"></i> Edit</button>
            <button class="btn-outline-teal btn-sm" style="color:#ef4444;border-color:#ef4444" onclick="deleteFromDetail()"><i class="bi bi-trash3"></i> Delete</button>
        </div>
    </div>
</div>

<!-- TOOL SHELL -->
<div class="tool-shell dot-grid" id="toolShell">

<div class="tool-wrap" id="toolWrap">

    <div class="alert-gst alert-info">
        <i class="bi bi-info-circle-fill"></i>
        <div><strong>How it works:</strong> (1) Add clients &amp; select one → (2) Enter filing period → (3) Upload files (Single or <strong>Bulk Mode</strong>) → (4) Click <strong>Compute B2CS</strong> → Download CSV, Excel or JSON.</div>
    </div>

    <!-- CLIENT MANAGER -->
    <div class="client-manager-wrap">
        <div class="client-manager-header">
            <div>
                <div class="client-manager-title"><i class="bi bi-people-fill" style="color:var(--teal);margin-right:8px"></i>Client Manager</div>
                <div style="font-size:13px;color:#6b7a99;margin-top:4px">Click a card to select · hover for quick actions</div>
            </div>
            <button class="btn-teal btn-sm" onclick="openClientModal()"><i class="bi bi-plus-lg"></i> Add Client</button>
        </div>
        <div class="client-search-wrap">
            <i class="bi bi-search"></i>
            <input type="text" class="client-search-input" id="clientSearch" placeholder="Search by name or GSTIN..." oninput="renderClientList()">
        </div>
        <div class="client-list" id="clientList">
            <div class="no-clients" id="noClientsMsg">
                <i class="bi bi-person-x" style="font-size:28px;display:block;margin-bottom:8px;color:#dde3ef"></i>
                No clients yet. Click <strong>+ Add Client</strong> to get started.
            </div>
        </div>
        <div id="selectedClientBar" style="display:none">
            <div class="selected-client-bar">
                <div class="scb-avatar" id="scbAvatar">--</div>
                <div><div class="scb-name" id="scbName">—</div><div class="scb-gstin" id="scbGstin">—</div></div>
                <div style="margin-left:auto;display:flex;gap:8px;flex-wrap:wrap">
                    <button class="btn-outline-teal btn-sm" onclick="openDetailModal(selectedClient.id)"><i class="bi bi-eye"></i> Details</button>
                    <button class="btn-outline-teal btn-sm" onclick="clearSelectedClient()"><i class="bi bi-arrow-repeat"></i> Change</button>
                </div>
            </div>
        </div>
    </div>

    <!-- STEP 1 -->
    <div class="step-card">
        <div class="step-header"><div class="step-num">1</div><div><div class="step-title">Filing Period</div><div class="step-desc">Select the month and year for GSTR-1</div></div></div>
        <div class="step-body">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label"><i class="bi bi-calendar3"></i> Filing Month</label>
                    <select id="inputMonth" class="form-control-gst">
                        <option value="01">January</option><option value="02">February</option>
                        <option value="03">March</option><option value="04">April</option>
                        <option value="05">May</option><option value="06" selected>June</option>
                        <option value="07">July</option><option value="08">August</option>
                        <option value="09">September</option><option value="10">October</option>
                        <option value="11">November</option><option value="12">December</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label"><i class="bi bi-calendar-year"></i> Filing Year</label>
                    <select id="inputYear" class="form-control-gst">
                        <option value="2024">2024</option><option value="2025">2025</option><option value="2026" selected>2026</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- STEP 2 -->
    <div class="step-card">
        <div class="step-header">
            <div class="step-num">2</div>
            <div><div class="step-title">Upload Marketplace Reports</div><div class="step-desc">10+ platforms — Single Mode or Bulk Mode</div></div>
        </div>
        <div class="step-body">
            <div class="parse-mode-wrap">
                <i class="bi bi-cpu-fill" style="color:var(--sky)"></i>
                <span style="font-size:13px;color:#005fa0;font-weight:600">Parsing Engine:</span>
                <span class="parse-mode-badge" id="parseModeLabel">Checking...</span>
                <span style="font-size:12px;color:#6b7a99;margin-left:4px" id="parseModeDesc"></span>
            </div>
            <div class="alert-gst alert-warn" style="margin-bottom:16px">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div><strong>Avoid double counting:</strong> Upload <em>either</em> TCS Sales/Return <em>or</em> that platform's native order report — never both for the same month.</div>
            </div>
            <div class="mode-tabs">
                <button class="mode-tab active" id="tabSingle" onclick="switchMode('single')"><i class="bi bi-file-earmark-spreadsheet"></i> Single Mode</button>
                <button class="mode-tab" id="tabBulk" onclick="switchMode('bulk')"><i class="bi bi-files"></i> Bulk Mode <span style="font-size:10px;background:rgba(0,201,141,.15);color:#008060;padding:1px 7px;border-radius:10px;margin-left:4px">UNLIMITED</span></button>
            </div>

            <div id="singleMode">
                <div class="platform-group-label"><i class="bi bi-star-fill"></i> Core Platforms</div>
                <div class="upload-grid">
                    <div class="upload-zone" id="zone-amazon" onclick="document.getElementById('file-amazon').click()">
                        <input type="file" id="file-amazon" accept=".xlsx,.xls" onchange="handleFile(this,'amazon')">
                        <div class="uz-icon"><i class="bi bi-amazon"></i></div>
                        <div class="uz-tag amazon">Amazon</div>
                        <div class="uz-name">GSTR-1 Ready Report</div>
                        <div class="uz-hint">Amazon GST Ready-to-File (.xlsx)</div>
                        <div class="uz-filename" id="fn-amazon"></div>
                    </div>
                    <div class="upload-zone" id="zone-flipkart" onclick="document.getElementById('file-flipkart').click()">
                        <input type="file" id="file-flipkart" accept=".xlsx,.xls" onchange="handleFile(this,'flipkart')">
                        <div class="uz-icon"><i class="bi bi-basket3-fill"></i></div>
                        <div class="uz-tag flipkart">Flipkart</div>
                        <div class="uz-name">GSTR-1 Section Report</div>
                        <div class="uz-hint">Flipkart GST Ready Report (.xlsx)</div>
                        <div class="uz-filename" id="fn-flipkart"></div>
                    </div>
                    <div class="upload-zone" id="zone-meesho" onclick="document.getElementById('file-meesho').click()">
                        <input type="file" id="file-meesho" accept=".xlsx,.xls" onchange="handleFile(this,'meesho')">
                        <div class="uz-icon" style="color:#8020d0;background:rgba(160,32,240,.1)"><i class="bi bi-shop"></i></div>
                        <div class="uz-tag meesho">Meesho</div>
                        <div class="uz-name">Monthly Sales Report</div>
                        <div class="uz-hint">Skip if uploading Meesho's TCS Sales below</div>
                        <div class="uz-filename" id="fn-meesho"></div>
                    </div>
                    <div class="upload-zone" id="zone-tcs-sales" onclick="document.getElementById('file-tcs-sales').click()">
                        <input type="file" id="file-tcs-sales" accept=".xlsx,.xls,.csv" onchange="handleFile(this,'tcs_sales')">
                        <div class="uz-icon"><i class="bi bi-receipt"></i></div>
                        <div class="uz-tag tcs">TCS Sales</div>
                        <div class="uz-name">TCS Sales Report</div>
                        <div class="uz-hint">TCS Sales (.xlsx/.csv)</div>
                        <div class="uz-filename" id="fn-tcs_sales"></div>
                    </div>
                </div>
                <div class="platform-group-label" style="margin-top:20px"><i class="bi bi-plus-circle-fill"></i> Additional Platforms</div>
                <div class="upload-grid-secondary">
                    <div class="upload-zone" id="zone-ajio" onclick="document.getElementById('file-ajio').click()">
                        <input type="file" id="file-ajio" accept=".xlsx,.xls,.csv" onchange="handleFile(this,'ajio')">
                        <div class="uz-icon" style="color:#b91c1c;background:rgba(220,38,38,.1)"><i class="bi bi-bag-fill"></i></div>
                        <div class="uz-tag ajio">Ajio</div><div class="uz-name">GST Report</div>
                        <div class="uz-hint">Ajio / Reliance (.xlsx)</div>
                        <div class="uz-filename" id="fn-ajio"></div>
                    </div>
                    <div class="upload-zone" id="zone-myntra" onclick="document.getElementById('file-myntra').click()">
                        <input type="file" id="file-myntra" accept=".xlsx,.xls,.csv" onchange="handleFile(this,'myntra')">
                        <div class="uz-icon" style="color:#c2410c;background:rgba(234,88,12,.1)"><i class="bi bi-handbag-fill"></i></div>
                        <div class="uz-tag myntra">Myntra</div><div class="uz-name">GST Tax Report</div>
                        <div class="uz-hint">Myntra GST Report (.xlsx)</div>
                        <div class="uz-filename" id="fn-myntra"></div>
                    </div>
                    <div class="upload-zone" id="zone-snapdeal" onclick="document.getElementById('file-snapdeal').click()">
                        <input type="file" id="file-snapdeal" accept=".xlsx,.xls,.csv" onchange="handleFile(this,'snapdeal')">
                        <div class="uz-icon" style="color:#991b1b;background:rgba(220,38,38,.1)"><i class="bi bi-tags-fill"></i></div>
                        <div class="uz-tag snapdeal">Snapdeal</div><div class="uz-name">Sales GST Report</div>
                        <div class="uz-hint">Snapdeal GST Report (.xlsx)</div>
                        <div class="uz-filename" id="fn-snapdeal"></div>
                    </div>
                    <div class="upload-zone" id="zone-nykaa" onclick="document.getElementById('file-nykaa').click()">
                        <input type="file" id="file-nykaa" accept=".xlsx,.xls,.csv" onchange="handleFile(this,'nykaa')">
                        <div class="uz-icon" style="color:#be185d;background:rgba(236,72,153,.1)"><i class="bi bi-flower2"></i></div>
                        <div class="uz-tag nykaa">Nykaa</div><div class="uz-name">GST Report</div>
                        <div class="uz-hint">Nykaa GST Report (.xlsx)</div>
                        <div class="uz-filename" id="fn-nykaa"></div>
                    </div>
                    <div class="upload-zone" id="zone-jiomart" onclick="document.getElementById('file-jiomart').click()">
                        <input type="file" id="file-jiomart" accept=".xlsx,.xls,.csv" onchange="handleFile(this,'jiomart')">
                        <div class="uz-icon" style="color:#1d4ed8;background:rgba(37,99,235,.1)"><i class="bi bi-cart4"></i></div>
                        <div class="uz-tag jiomart">JioMart</div><div class="uz-name">GST Tax Report</div>
                        <div class="uz-hint">JioMart GST Report (.xlsx)</div>
                        <div class="uz-filename" id="fn-jiomart"></div>
                    </div>
                    <div class="upload-zone" id="zone-glowroad" onclick="document.getElementById('file-glowroad').click()">
                        <input type="file" id="file-glowroad" accept=".xlsx,.xls,.csv" onchange="handleFile(this,'glowroad')">
                        <div class="uz-icon" style="color:#047857;background:rgba(5,150,105,.1)"><i class="bi bi-stars"></i></div>
                        <div class="uz-tag glowroad">Glowroad</div><div class="uz-name">Sales Report</div>
                        <div class="uz-hint">Glowroad GST Report (.xlsx)</div>
                        <div class="uz-filename" id="fn-glowroad"></div>
                    </div>
                    <div class="upload-zone" id="zone-shopsy" onclick="document.getElementById('file-shopsy').click()">
                        <input type="file" id="file-shopsy" accept=".xlsx,.xls,.csv" onchange="handleFile(this,'shopsy')">
                        <div class="uz-icon" style="color:#4338ca;background:rgba(79,70,229,.1)"><i class="bi bi-bag-heart-fill"></i></div>
                        <div class="uz-tag shopsy">Shopsy</div><div class="uz-name">GST Report</div>
                        <div class="uz-hint">Shopsy / FK GST Report (.xlsx)</div>
                        <div class="uz-filename" id="fn-shopsy"></div>
                    </div>
                    <div class="upload-zone" id="zone-tcs-return" onclick="document.getElementById('file-tcs-return').click()">
                        <input type="file" id="file-tcs-return" accept=".xlsx,.xls,.csv" onchange="handleFile(this,'tcs_return')">
                        <div class="uz-icon" style="color:#e87a00;background:rgba(232,122,0,.1)"><i class="bi bi-receipt-cutoff"></i></div>
                        <div class="uz-tag optional">TCS Return</div><div class="uz-name">TCS Return / Refund</div>
                        <div class="uz-hint">Pair with TCS Sales for same marketplace</div>
                        <div class="uz-filename" id="fn-tcs_return"></div>
                    </div>
                </div>
                <div style="margin-top:14px">
                    <div class="upload-zone" style="max-width:320px" id="zone-b2cs" onclick="document.getElementById('file-b2cs').click()">
                        <input type="file" id="file-b2cs" accept=".csv" onchange="handleFile(this,'b2cs_csv')">
                        <div class="uz-icon" style="color:#0080aa;background:rgba(0,128,170,.1)"><i class="bi bi-filetype-csv"></i></div>
                        <div class="uz-tag optional">Optional</div>
                        <div class="uz-name">Existing B2CS CSV</div>
                        <div class="uz-hint">Already computed B2CS CSV (direct JSON convert)</div>
                        <div class="uz-filename" id="fn-b2cs_csv"></div>
                    </div>
                </div>
                <div id="uploadStatus" style="margin-top:14px"></div>
            </div>

            <div id="bulkMode" style="display:none">
                <div class="alert-gst alert-info" style="margin-bottom:16px">
                    <i class="bi bi-lightbulb-fill"></i>
                    <div>Drop <strong>unlimited files</strong> here. Platform auto-detected from filename. Re-check the tag on each file.</div>
                </div>
                <div class="bulk-drop-zone" id="bulkDropZone">
                    <input type="file" id="bulkFileInput" accept=".xlsx,.xls,.csv" multiple onchange="handleBulkFiles(this.files)">
                    <div class="bulk-icon"><i class="bi bi-cloud-arrow-up-fill"></i></div>
                    <div style="font-family:'Syne',sans-serif;font-weight:800;font-size:16px;color:var(--navy);margin-bottom:8px">Drop files here or click to browse</div>
                    <div style="font-size:13px;color:#94a3b8">Supports .xlsx, .xls, .csv — any number of files at once</div>
                </div>
                <div id="bulkFileList" class="bulk-file-list"></div>
                <div id="bulkStatus" style="margin-top:14px"></div>
            </div>
        </div>
    </div>

    <!-- COMPUTE BAR -->
    <div class="compute-bar">
        <div class="compute-bar-left">
            <h3>Ready to Compute?</h3>
            <p id="computeBarDesc">Select a client and upload at least one file to begin</p>
        </div>
        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
            <span class="status-badge status-idle" id="computeStatus"><i class="bi bi-circle"></i> Idle</span>
            <button class="btn-teal" id="btnCompute" onclick="computeB2CS()" disabled>
                <i class="bi bi-calculator-fill"></i> Compute B2CS
            </button>
        </div>
    </div>

    <div class="progress-bar-wrap" id="progressWrap"><div class="progress-bar-fill" id="progressFill"></div></div>
    <div class="log-box" id="logBox"></div>

    <!-- STEP 3: RESULTS -->
    <div class="preview-section" id="previewSection">
        <div class="step-card">
            <div class="step-header">
                <div class="step-num">3</div>
                <div><div class="step-title">Computation Report &amp; Downloads</div><div class="step-desc">Detailed transactions · B2CS summary · GST Portal JSON</div></div>
            </div>
            <div class="step-body">
                <div class="preview-header">
                    <div><div class="preview-title" id="previewTitle">B2CS Summary</div><div class="preview-subtitle" id="previewSubtitle"></div></div>
                    <div class="action-row">
                        <button class="btn-outline-teal" onclick="downloadCSV()"><i class="bi bi-filetype-csv"></i> B2CS CSV</button>
                        <button class="btn-sky" onclick="downloadDetailExcel()"><i class="bi bi-file-earmark-excel-fill"></i> Detail Excel</button>
                        <button class="btn-sky" onclick="downloadExcel()"><i class="bi bi-file-earmark-excel-fill"></i> Summary Excel</button>
                        <button class="btn-navy" onclick="downloadJSON()"><i class="bi bi-filetype-json"></i> JSON (Portal)</button>
                    </div>
                </div>
                <div class="summary-chips" id="summaryChips"></div>

                <!-- REPORT TABS -->
                <div class="report-tabs">
                    <button class="report-tab active" id="rtabDetail" onclick="switchReportTab('detail')"><i class="bi bi-list-ul"></i> Detailed Transactions</button>
                    <button class="report-tab" id="rtabPlatform" onclick="switchReportTab('platform')"><i class="bi bi-layers"></i> Platform-wise B2CS</button>
                    <button class="report-tab" id="rtabGrand" onclick="switchReportTab('grand')"><i class="bi bi-sigma"></i> Grand Total</button>
                    <button class="report-tab" id="rtabJson" onclick="switchReportTab('json')"><i class="bi bi-braces"></i> JSON Preview</button>
                </div>

                <!-- TAB: DETAILED TRANSACTIONS -->
                <div id="tabContent-detail">
                    <div style="font-family:'Syne',sans-serif;font-weight:700;font-size:15px;color:var(--navy);margin-bottom:12px;display:flex;align-items:center;gap:8px">
                        <i class="bi bi-list-ul" style="color:var(--teal)"></i> Transaction-wise Detail
                        <span style="font-size:12px;font-weight:400;color:#6b7a99;margin-left:8px">All parsed rows before aggregation</span>
                    </div>
                    <div class="table-wrap">
                        <table class="detail-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Platform</th>
                                    <th>Period</th>
                                    <th>FY</th>
                                    <th>Nature</th>
                                    <th>Dest. State</th>
                                    <th>HSN</th>
                                    <th class="num">Qty</th>
                                    <th class="num">Taxable Amt (₹)</th>
                                    <th class="num">Rate %</th>
                                    <th class="num">CGST (₹)</th>
                                    <th class="num">SGST (₹)</th>
                                    <th class="num">IGST (₹)</th>
                                    <th class="num">Invoice Amt (₹)</th>
                                    <th class="num">TCS (₹)</th>
                                </tr>
                            </thead>
                            <tbody id="detailTableBody"></tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="7">Total</td>
                                    <td class="num" id="dtotQty">—</td>
                                    <td class="num" id="dtotTxval">—</td>
                                    <td></td>
                                    <td class="num" id="dtotCamt">—</td>
                                    <td class="num" id="dtotSamt">—</td>
                                    <td class="num" id="dtotIamt">—</td>
                                    <td class="num" id="dtotInvoice">—</td>
                                    <td class="num" id="dtotTcs">—</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- TAB: PLATFORM-WISE B2CS -->
                <div id="tabContent-platform" style="display:none">
                    <div id="platformReports"></div>
                </div>

                <!-- TAB: GRAND TOTAL -->
                <div id="tabContent-grand" style="display:none">
                    <div style="font-family:'Syne',sans-serif;font-weight:700;font-size:16px;color:var(--navy);margin-bottom:12px;display:flex;align-items:center;gap:8px">
                        <i class="bi bi-sigma" style="color:var(--teal)"></i> Grand Total (All Platforms Combined)
                    </div>
                    <div class="table-wrap">
                        <table class="gst-table">
                            <thead><tr><th>Type</th><th>State</th><th>GST Rate</th><th>Taxable Value (₹)</th><th>IGST (₹)</th><th>CGST (₹)</th><th>SGST (₹)</th><th>Total Tax (₹)</th></tr></thead>
                            <tbody id="previewTableBody"></tbody>
                            <tfoot><tr class="tfoot-row"><td colspan="3"><strong>GRAND TOTAL</strong></td><td id="totTxval">—</td><td id="totIamt">—</td><td id="totCamt">—</td><td id="totSamt">—</td><td id="totTax">—</td></tr></tfoot>
                        </table>
                    </div>
                </div>

                <!-- TAB: JSON PREVIEW -->
                <div id="tabContent-json" style="display:none">
                    <div class="json-box">
                        <div class="json-box-header">
                            <span id="jsonFileName">gstr1_b2cs.json</span>
                            <button class="btn-outline-teal" style="padding:5px 14px;font-size:12px" onclick="copyJSON()"><i class="bi bi-clipboard"></i> Copy</button>
                        </div>
                        <pre class="json-pre" id="jsonPreview">// JSON will appear here after computing...</pre>
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>{{-- /.tool-wrap --}}

<!-- SIDEBAR -->
<div class="sidebar-col">
    <!-- Clients by State -->
    <div class="sb-card">
        <div class="sb-card-header">
            <i class="bi bi-geo-alt-fill"></i>
            <div>
                <div class="sb-card-title">Clients by State</div>
                <div class="sb-card-sub" id="sbSubtitle">Updates instantly</div>
            </div>
        </div>
        <div class="sb-card-body" id="sbStateBody">
            <div class="sb-empty"><i class="bi bi-person-x"></i>No clients yet</div>
        </div>
    </div>
    <!-- All Clients -->
    <div class="sb-card">
        <div class="sb-card-header">
            <i class="bi bi-people-fill"></i>
            <div>
                <div class="sb-card-title">All Clients</div>
                <div class="sb-card-sub" id="sbCountSub">0 clients</div>
            </div>
        </div>
        <div class="sb-card-body" id="sbAllClientsBody">
            <div class="sb-empty"><i class="bi bi-person-x"></i>No clients yet</div>
        </div>
    </div>
</div>

</div>{{-- /.tool-shell --}}

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
/* ═══════════════════════════════════════════════════════════════════
   GSTR-1 E-Commerce Tool v5
   KEY CHANGES:
   1. SIDEBAR: No empty space — max-height:auto per card, sticky only desktop
   2. DETAILED TABLE: 15 columns — Platform, Period, FY, Nature, State, HSN,
      Qty, Taxable Amt, Rate, CGST, SGST, IGST, Invoice Amt, TCS
   3. B2CS CSV: Type, Place of Supply, Applicable % of Tax Rate, Taxable Value,
      Cess Amount, E-Commerce GSTIN (exact GST portal B2CS format)
   4. Report tabs: Detail | Platform-wise | Grand Total | JSON
   5. Bug fix: duplicate addToB2CS calls removed in parseFlipkart & parseGenericPlatform
   6. Bug fix: duplicate var declarations removed in parseTCS & parseAmazon
═══════════════════════════════════════════════════════════════════ */

// ── CONFIG ───────────────────────────────────────────────────────────
const API_BASE = '/api/gstr';
const CSRF     = document.querySelector('meta[name=csrf-token]')?.content || '';
let useBackend = false;

const GST_CONFIG = {
    VERSION: 'GST3.2.4',
    VALID_RATES: [0, 0.1, 0.25, 1, 1.5, 3, 5, 6, 7.5, 12, 18, 28],
};

function normalizeRate(raw) {
    const rt = parseFloat(String(raw).replace('%','').trim()) || 0;
    const closest = GST_CONFIG.VALID_RATES.reduce((a,b) => Math.abs(b-rt) < Math.abs(a-rt) ? b : a);
    return Math.abs(closest - rt) <= 0.26 ? closest : rt;
}

// ── BACKEND DETECT ───────────────────────────────────────────────────
async function detectBackend() {
    try {
        const r = await fetch(API_BASE+'/ping',{method:'GET',headers:{'X-CSRF-TOKEN':CSRF},signal:AbortSignal.timeout(3000)});
        useBackend = r.ok;
    } catch { useBackend = false; }
    const lbl=document.getElementById('parseModeLabel'), desc=document.getElementById('parseModeDesc');
    if (useBackend) { lbl.textContent='⚡ Smart ML (Laravel)';lbl.className='parse-mode-badge backend';desc.textContent='Fuzzy column matching'; }
    else { lbl.textContent='🖥 Client-side (JS)';lbl.className='parse-mode-badge frontend';desc.textContent='Local parsing — works offline'; }
}
detectBackend();

// ── TOAST ────────────────────────────────────────────────────────────
function toast(msg,type='success',dur=3000){
    const wrap=document.getElementById('toastWrap');
    const icons={success:'bi-check-circle-fill',error:'bi-x-circle-fill',warn:'bi-exclamation-triangle-fill',info:'bi-info-circle-fill'};
    const t=document.createElement('div');t.className=`toast ${type}`;
    t.innerHTML=`<i class="bi ${icons[type]||icons.info}"></i> ${msg}`;
    wrap.appendChild(t);
    setTimeout(()=>{t.style.opacity='0';t.style.transform='translateY(20px)';t.style.transition='all .3s';setTimeout(()=>t.remove(),300);},dur);
}

// ── STATE MAPS ───────────────────────────────────────────────────────
const STATE_MAP = {
    "JAMMU & KASHMIR":"01","JAMMU AND KASHMIR":"01","J & K":"01","J&K":"01",
    "HIMACHAL PRADESH":"02","PUNJAB":"03","CHANDIGARH":"04",
    "UTTARAKHAND":"05","UTTARANCHAL":"05","HARYANA":"06",
    "DELHI":"07","NEW DELHI":"07","RAJASTHAN":"08","UTTAR PRADESH":"09",
    "BIHAR":"10","SIKKIM":"11","ARUNACHAL PRADESH":"12","NAGALAND":"13",
    "MANIPUR":"14","MIZORAM":"15","TRIPURA":"16","MEGHALAYA":"17",
    "ASSAM":"18","WEST BENGAL":"19","JHARKHAND":"20","JHARKAND":"20",
    "ODISHA":"21","ORISSA":"21","ORRISA":"21",
    "CHHATTISGARH":"22","CHATTISGARH":"22","MADHYA PRADESH":"23","GUJARAT":"24",
    "DADRA AND NAGAR HAVELI":"26","DADAR & NAGAR HAVELI":"26",
    "DADRA & NAGAR HAVELI":"26","DADRA AND NAGAR HAVELI AND DAMAN AND DIU":"26",
    "DAMAN AND DIU":"26","DAMAN & DIU":"26","MAHARASHTRA":"27","KARNATAKA":"29",
    "GOA":"30","LAKSHADWEEP":"31","KERALA":"32","TAMIL NADU":"33","TAMILNADU":"33",
    "PUDUCHERRY":"34","PONDICHERRY":"34","ANDAMAN AND NICOBAR":"35",
    "ANDAMAN & NICOBAR":"35","ANDAMAN AND NICOBAR ISLANDS":"35",
    "ANDAMAN & NICOBAR ISLANDS":"35","TELANGANA":"36","ANDHRA PRADESH":"37","LADAKH":"38"
};

const CODE_MAP = {
    "JK":"01","HP":"02","PB":"03","CH":"04","UT":"05","UK":"05","HR":"06","DL":"07",
    "RJ":"08","UP":"09","BR":"10","SK":"11","AR":"12","NL":"13","MN":"14","MZ":"15",
    "TR":"16","ML":"17","AS":"18","WB":"19","JH":"20","OR":"21","CT":"22","CG":"22",
    "MP":"23","GJ":"24","DN":"26","DD":"26","MH":"27","KA":"29","GA":"30","LD":"31",
    "KL":"32","TN":"33","PY":"34","AN":"35","TS":"36","TG":"36","AP":"37","LA":"38"
};

const POS_TO_NAME = {
    "01":"01-Jammu & Kashmir","02":"02-Himachal Pradesh","03":"03-Punjab",
    "04":"04-Chandigarh","05":"05-Uttarakhand","06":"06-Haryana","07":"07-Delhi",
    "08":"08-Rajasthan","09":"09-Uttar Pradesh","10":"10-Bihar","11":"11-Sikkim",
    "12":"12-Arunachal Pradesh","13":"13-Nagaland","14":"14-Manipur","15":"15-Mizoram",
    "16":"16-Tripura","17":"17-Meghalaya","18":"18-Assam","19":"19-West Bengal",
    "20":"20-Jharkhand","21":"21-Odisha","22":"22-Chhattisgarh",
    "23":"23-Madhya Pradesh","24":"24-Gujarat",
    "26":"26-Dadra & Nagar Haveli and Daman & Diu","27":"27-Maharashtra",
    "29":"29-Karnataka","30":"30-Goa","31":"31-Lakshadweep","32":"32-Kerala",
    "33":"33-Tamil Nadu","34":"34-Puducherry","35":"35-Andaman & Nicobar Islands",
    "36":"36-Telangana","37":"37-Andhra Pradesh","38":"38-Ladakh"
};

// FY helper — filing year to FY string
function getFY(year) {
    const y = parseInt(year);
    return `${y}-${String(y+1).slice(2)}`;
}

const PLATFORM_ORDER=['meesho','amazon','flipkart','ajio','myntra','snapdeal','nykaa','jiomart','glowroad','shopsy','tcs_sales','tcs_return','b2cs_csv'];
const PLATFORM_META={
    meesho:    {label:'Meesho',    icon:'bi-shop',            color:'#8020d0',bg:'rgba(160,32,240,.1)'},
    amazon:    {label:'Amazon',    icon:'bi-amazon',          color:'#c07000',bg:'rgba(255,153,0,.1)'},
    flipkart:  {label:'Flipkart',  icon:'bi-basket3-fill',    color:'#3a66c7',bg:'rgba(100,149,237,.1)'},
    ajio:      {label:'Ajio',      icon:'bi-bag-fill',        color:'#b91c1c',bg:'rgba(220,38,38,.1)'},
    myntra:    {label:'Myntra',    icon:'bi-handbag-fill',    color:'#c2410c',bg:'rgba(234,88,12,.1)'},
    snapdeal:  {label:'Snapdeal',  icon:'bi-tags-fill',       color:'#991b1b',bg:'rgba(220,38,38,.1)'},
    nykaa:     {label:'Nykaa',     icon:'bi-flower2',         color:'#be185d',bg:'rgba(236,72,153,.1)'},
    jiomart:   {label:'JioMart',   icon:'bi-cart4',           color:'#1d4ed8',bg:'rgba(37,99,235,.1)'},
    glowroad:  {label:'Glowroad',  icon:'bi-stars',           color:'#047857',bg:'rgba(5,150,105,.1)'},
    shopsy:    {label:'Shopsy',    icon:'bi-bag-heart-fill',  color:'#4338ca',bg:'rgba(79,70,229,.1)'},
    tcs_sales: {label:'TCS Sales', icon:'bi-receipt',         color:'#008060',bg:'rgba(0,201,141,.1)'},
    tcs_return:{label:'TCS Return',icon:'bi-receipt-cutoff',  color:'#e87a00',bg:'rgba(232,122,0,.1)'},
    b2cs_csv:  {label:'B2CS CSV',  icon:'bi-filetype-csv',    color:'#0080aa',bg:'rgba(0,128,170,.1)'},
};

// ── STATE ────────────────────────────────────────────────────────────
let clients=JSON.parse(localStorage.getItem('gst_clients_v2')||'[]');
let selectedClient=null;
let detailClientId=null;
const loadedFiles={};
let bulkFiles=[];
let platformB2CS={};
let b2csData={};
let unresolvedLog=[];
let allParsedTransactions=[];

// ── HELPERS ──────────────────────────────────────────────────────────
function saveClientsToStorage(){localStorage.setItem('gst_clients_v2',JSON.stringify(clients));}
function initials(name){if(!name)return'--';const p=name.trim().split(/\s+/);return p.length>=2?(p[0][0]+p[1][0]).toUpperCase():name.substring(0,2).toUpperCase();}
function getStateName(code){for(const o of document.getElementById('newClientState').options)if(o.value===code)return o.text;return code;}
function highlight(text,query){if(!query)return text;const idx=text.toLowerCase().indexOf(query);if(idx<0)return text;return text.substring(0,idx)+`<mark style="background:rgba(0,201,141,.2);border-radius:3px;padding:0 2px">${text.substring(idx,idx+query.length)}</mark>`+text.substring(idx+query.length);}
function round2(n){return Math.round(n*100)/100;}
function fmtN(n){if(n===null||n===undefined||n==='')return'—';const v=parseFloat(n);if(isNaN(v))return'—';return v.toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2});}
function fmt(n){if(n===null||n===undefined)return'—';const v=parseFloat(n);if(isNaN(v))return'—';const cls=v<0?' class="neg-val"':'';return`<span${cls}>₹${Math.abs(v).toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2})}${v<0?' CR':''}</span>`;}
function log(msg,cls){const box=document.getElementById('logBox');box.style.display='block';const span=cls?`<span class="${cls}">${msg}</span>`:msg;box.innerHTML+=`<span style="color:var(--teal)">→</span> ${span}<br>`;box.scrollTop=box.scrollHeight;}
function setProgress(pct){document.getElementById('progressWrap').style.display='block';document.getElementById('progressFill').style.width=pct+'%';}
function setStatus(state,text){const s=document.getElementById('computeStatus');s.className='status-badge status-'+state;const icons={idle:'bi-circle',processing:'bi-arrow-repeat',done:'bi-check-circle-fill',error:'bi-x-circle-fill'};s.innerHTML=`<i class="bi ${icons[state]}"></i> ${text}`;}
function updateComputeBarDesc(){const el=document.getElementById('computeBarDesc');const fc=currentMode==='bulk'?bulkFiles.length:Object.keys(loadedFiles).length;if(!selectedClient&&fc===0)el.textContent='Select a client and upload at least one file to begin';else if(!selectedClient)el.textContent=`${fc} file(s) uploaded — now select a client`;else if(fc===0)el.textContent=`Client: ${selectedClient.name} — upload at least one file`;else el.textContent=`Client: ${selectedClient.name} | ${fc} file(s) — ready!`;}
function updateComputeBtn(){const count=currentMode==='bulk'?bulkFiles.length:Object.keys(loadedFiles).length;document.getElementById('btnCompute').disabled=!(count>0&&selectedClient);}

// ── REPORT TABS ──────────────────────────────────────────────────────
function switchReportTab(tab) {
    ['detail','platform','grand','json'].forEach(t => {
        document.getElementById('tabContent-'+t).style.display = t===tab ? 'block' : 'none';
        document.getElementById('rtab'+t.charAt(0).toUpperCase()+t.slice(1)).classList.toggle('active', t===tab);
    });
}

// ── CLIENT MANAGER ───────────────────────────────────────────────────
function renderClientList(){
    const list=document.getElementById('clientList');const noMsg=document.getElementById('noClientsMsg');
    const query=(document.getElementById('clientSearch').value||'').toLowerCase().trim();
    if(clients.length===0){list.innerHTML='';list.appendChild(noMsg);noMsg.style.display='block';renderSidebar();return;}
    noMsg.style.display='none';
    const filtered=query?clients.filter(c=>c.name.toLowerCase().includes(query)||(c.gstin||'').toLowerCase().includes(query)):clients;
    if(filtered.length===0){list.innerHTML=`<div class="no-clients" style="grid-column:1/-1"><i class="bi bi-search" style="font-size:24px;display:block;margin-bottom:8px;color:#dde3ef"></i>No clients match "<strong>${query}</strong>"</div>`;renderSidebar();return;}
    list.innerHTML=filtered.map(c=>`
        <div class="client-card ${selectedClient&&selectedClient.id===c.id?'selected':''}" onclick="selectClient('${c.id}')">
            <div class="client-avatar">${initials(c.name)}</div>
            <div style="min-width:0"><div class="client-name">${highlight(c.name,query)}</div><div class="client-gstin">${highlight(c.gstin||'No GSTIN',query)}</div></div>
            <button class="client-info" onclick="event.stopPropagation();openDetailModal('${c.id}')" title="View details"><i class="bi bi-eye"></i></button>
            <button class="client-del" onclick="deleteClient(event,'${c.id}')" title="Delete"><i class="bi bi-trash3"></i></button>
        </div>`).join('');
    renderSidebar();
}

// ── SIDEBAR ──────────────────────────────────────────────────────────
function renderSidebar(){
    const stateBody=document.getElementById('sbStateBody');const allBody=document.getElementById('sbAllClientsBody');
    if(!stateBody||!allBody)return;
    if(clients.length===0){
        stateBody.innerHTML='<div class="sb-empty"><i class="bi bi-person-x"></i>No clients yet</div>';
        allBody.innerHTML='<div class="sb-empty"><i class="bi bi-person-x"></i>No clients yet</div>';
        document.getElementById('sbSubtitle').textContent='Updates instantly';
        document.getElementById('sbCountSub').textContent='0 clients';return;
    }
    const byState={};
    for(const c of clients){const code=c.state||'??';if(!byState[code])byState[code]=[];byState[code].push(c);}
    const stateCodes=Object.keys(byState).sort((a,b)=>(POS_TO_NAME[a]||a).localeCompare(POS_TO_NAME[b]||b));
    document.getElementById('sbSubtitle').textContent=`${clients.length} client${clients.length!==1?'s':''} · ${stateCodes.length} state${stateCodes.length!==1?'s':''}`;
    document.getElementById('sbCountSub').textContent=`${clients.length} client${clients.length!==1?'s':''}`;
    let html='';
    for(const code of stateCodes){
        const list=byState[code].slice().sort((a,b)=>a.name.localeCompare(b.name));
        const sn=POS_TO_NAME[code]?POS_TO_NAME[code].split('-').slice(1).join('-'):'Unknown';
        html+=`<div class="sb-state-group"><div class="sb-state-name">${sn} <span style="color:#c0cce0">(${list.length})</span></div>`;
        for(const c of list){const isActive=selectedClient&&selectedClient.id===c.id;
            html+=`<div class="sb-client-row ${isActive?'active':''}" onclick="selectClient('${c.id}')"><div class="sb-client-avatar">${initials(c.name)}</div><div style="min-width:0;overflow:hidden"><div class="sb-client-name" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${c.name}</div><div class="sb-client-state" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${c.gstin||'No GSTIN'}</div></div></div>`;
        }html+=`</div>`;
    }
    stateBody.innerHTML=html;
    const sortedAll=clients.slice().sort((a,b)=>a.name.localeCompare(b.name));
    allBody.innerHTML=sortedAll.map(c=>{const isActive=selectedClient&&selectedClient.id===c.id;const sn=POS_TO_NAME[c.state]?POS_TO_NAME[c.state].split('-').slice(1).join('-'):'—';
        return`<div class="sb-client-row ${isActive?'active':''}" onclick="selectClient('${c.id}')"><div class="sb-client-avatar">${initials(c.name)}</div><div style="min-width:0;overflow:hidden"><div class="sb-client-name" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${c.name}</div><div class="sb-client-state">${sn}</div></div></div>`;
    }).join('');
}

function openClientModal(editId){
    const isEdit=!!editId;
    document.getElementById('clientModalTitle').innerHTML=isEdit?'<i class="bi bi-pencil-fill" style="color:var(--teal);margin-right:8px"></i>Edit Client':'<i class="bi bi-person-plus-fill" style="color:var(--teal);margin-right:8px"></i>Add New Client';
    document.getElementById('saveClientBtnText').textContent=isEdit?'Update Client':'Save Client';
    document.getElementById('editClientId').value=editId||'';
    document.getElementById('gstinError').style.display='none';
    if(isEdit){const c=clients.find(x=>x.id===editId);if(c){document.getElementById('newClientName').value=c.name;document.getElementById('newClientGstin').value=c.gstin||'';document.getElementById('newClientState').value=c.state||'32';document.getElementById('newClientPhone').value=c.phone||'';document.getElementById('newClientEmail').value=c.email||'';}}
    else{['newClientName','newClientGstin','newClientPhone','newClientEmail'].forEach(id=>document.getElementById(id).value='');document.getElementById('newClientState').value='32';}
    document.getElementById('clientDetailModal').classList.remove('show');
    document.getElementById('clientModal').classList.add('show');
    setTimeout(()=>document.getElementById('newClientName').focus(),100);
}
function closeClientModal(){document.getElementById('clientModal').classList.remove('show');}
document.getElementById('clientModal').addEventListener('click',function(e){if(e.target===this)closeClientModal();});
document.getElementById('newClientGstin').addEventListener('input',function(){this.value=this.value.toUpperCase();document.getElementById('gstinError').style.display='none';});
function validateGSTIN(g){return /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/.test(g);}
function saveClient(){
    const editId=document.getElementById('editClientId').value;
    const name=document.getElementById('newClientName').value.trim();
    const gstin=document.getElementById('newClientGstin').value.trim().toUpperCase();
    const state=document.getElementById('newClientState').value;
    const phone=document.getElementById('newClientPhone').value.trim();
    const email=document.getElementById('newClientEmail').value.trim();
    if(!name){toast('Please enter client name','warn');return;}
    if(!gstin){toast('GSTIN is required','warn');return;}
    if(!validateGSTIN(gstin)){document.getElementById('gstinError').style.display='block';document.getElementById('gstinError').textContent='Invalid GSTIN format. e.g. 29ABCDE1234F1Z5';return;}
    const dup=clients.find(c=>c.gstin===gstin&&c.id!==editId);
    if(dup){document.getElementById('gstinError').style.display='block';document.getElementById('gstinError').textContent=`GSTIN already used by: ${dup.name}`;return;}
    if(editId){const idx=clients.findIndex(c=>c.id===editId);if(idx>=0){clients[idx]={...clients[idx],name,gstin,state,phone,email,updatedAt:new Date().toISOString()};if(selectedClient&&selectedClient.id===editId){selectedClient=clients[idx];updateSelectedClientBar();}toast(`Client "${name}" updated`,'success');}}
    else{const client={id:Date.now().toString(),name,gstin,state,phone,email,createdAt:new Date().toISOString()};clients.push(client);toast(`Client "${name}" added`,'success');selectClient(client.id);}
    saveClientsToStorage();renderClientList();closeClientModal();
}
function deleteClient(e,id){if(e)e.stopPropagation();const c=clients.find(x=>x.id===id);if(!c)return;if(!confirm(`Delete "${c.name}"?`))return;clients=clients.filter(x=>x.id!==id);if(selectedClient&&selectedClient.id===id)clearSelectedClient();saveClientsToStorage();renderClientList();toast(`"${c.name}" deleted`,'warn');}
function selectClient(id){selectedClient=clients.find(c=>c.id===id)||null;renderClientList();updateSelectedClientBar();updateComputeBtn();updateComputeBarDesc();if(selectedClient){toast(`✓ ${selectedClient.name} selected`,'success',2000);}}
function clearSelectedClient(){selectedClient=null;renderClientList();updateSelectedClientBar();updateComputeBtn();updateComputeBarDesc();}
function updateSelectedClientBar(){const bar=document.getElementById('selectedClientBar');if(!selectedClient){bar.style.display='none';return;}bar.style.display='block';document.getElementById('scbAvatar').textContent=initials(selectedClient.name);document.getElementById('scbName').textContent=selectedClient.name;document.getElementById('scbGstin').textContent=selectedClient.gstin||'GSTIN not set';}
function openDetailModal(id){const c=clients.find(x=>x.id===id);if(!c)return;detailClientId=id;document.getElementById('detailAvatar').textContent=initials(c.name);document.getElementById('detailName').textContent=c.name;document.getElementById('detailCreated').textContent=`Added: ${c.createdAt?new Date(c.createdAt).toLocaleDateString('en-IN',{day:'2-digit',month:'short',year:'numeric'}):'Unknown'}`;const rows=[{label:'GSTIN',value:c.gstin||'—'},{label:'Home State',value:c.state?getStateName(c.state):'—'},{label:'Phone',value:c.phone||'—'},{label:'Email',value:c.email||'—'},{label:'Client ID',value:c.id}];document.getElementById('detailRows').innerHTML=rows.map(r=>`<div class="detail-row"><span class="detail-label">${r.label}</span><span class="detail-value">${r.value}</span></div>`).join('');document.getElementById('clientDetailModal').classList.add('show');}
function closeDetailModal(){document.getElementById('clientDetailModal').classList.remove('show');detailClientId=null;}
document.getElementById('clientDetailModal').addEventListener('click',function(e){if(e.target===this)closeDetailModal();});
function selectFromDetail(){if(!detailClientId)return;selectClient(detailClientId);closeDetailModal();}
function editFromDetail(){const id=detailClientId;closeDetailModal();openClientModal(id);}
function deleteFromDetail(){const id=detailClientId;closeDetailModal();deleteClient(null,id);}

// ── UPLOAD MODE ──────────────────────────────────────────────────────
let currentMode='single';
function switchMode(mode){currentMode=mode;document.getElementById('singleMode').style.display=mode==='single'?'block':'none';document.getElementById('bulkMode').style.display=mode==='bulk'?'block':'none';document.getElementById('tabSingle').classList.toggle('active',mode==='single');document.getElementById('tabBulk').classList.toggle('active',mode==='bulk');updateComputeBtn();updateComputeBarDesc();}
function handleFile(input,type){
    const file=input.files[0];if(!file)return;
    const zoneId='zone-'+type.replace(/_/g,'-');
    const zoneEl=document.getElementById(zoneId);if(zoneEl)zoneEl.classList.add('has-file');
    const fnEl=document.getElementById('fn-'+type);if(fnEl)fnEl.textContent='✓ '+file.name;
    loadedFiles[type]=file;updateComputeBtn();updateComputeBarDesc();showUploadStatus();
}
function showUploadStatus(){
    const count=Object.keys(loadedFiles).length;const el=document.getElementById('uploadStatus');
    if(count===0){el.innerHTML='';return;}
    const hasBoth=loadedFiles['meesho']&&loadedFiles['tcs_sales'];
    const warn=hasBoth?`<div class="alert-gst alert-warn" style="margin-top:10px;margin-bottom:0"><i class="bi bi-exclamation-triangle-fill"></i> Both <strong>Meesho</strong> and <strong>TCS Sales</strong> loaded — may double count.</div>`:'';
    el.innerHTML=`<div class="alert-gst alert-success" style="margin-bottom:0"><i class="bi bi-check-circle-fill"></i> <strong>${count} file${count>1?'s':''} loaded</strong> — ${Object.keys(loadedFiles).map(k=>PLATFORM_META[k]?.label||k).join(', ')}</div>${warn}`;
}

// ── BULK MODE ────────────────────────────────────────────────────────
function detectPlatformFromFile(file){
    const name=file.name.toLowerCase();
    const patterns={amazon:['amazon'],flipkart:['flipkart','fkm'],meesho:['meesho'],ajio:['ajio'],myntra:['myntra'],snapdeal:['snapdeal'],nykaa:['nykaa'],jiomart:['jiomart'],glowroad:['glowroad'],shopsy:['shopsy'],tcs_return:['return','refund','cancel'],tcs_sales:['tcs'],b2cs_csv:['b2cs']};
    for(const[platform,keys]of Object.entries(patterns))for(const k of keys)if(name.includes(k))return platform;
    return'unknown';
}
function handleBulkFiles(files){
    for(const file of files){const dup=bulkFiles.find(f=>f.file.name===file.name&&f.file.size===file.size);if(dup){toast(`"${file.name}" already added`,'warn');continue;}bulkFiles.push({file,detectedType:detectPlatformFromFile(file),id:Date.now()+Math.random()});}
    renderBulkFileList();updateComputeBtn();updateComputeBarDesc();
}
function renderBulkFileList(){
    const list=document.getElementById('bulkFileList');if(bulkFiles.length===0){list.innerHTML='';return;}
    const allTypes=['amazon','flipkart','meesho','ajio','myntra','snapdeal','nykaa','jiomart','glowroad','shopsy','tcs_sales','tcs_return','b2cs_csv','unknown'];
    list.innerHTML=bulkFiles.map((item,idx)=>{const meta=PLATFORM_META[item.detectedType]||{icon:'bi-file-earmark',color:'#64748b',bg:'rgba(148,163,184,.15)'};
        return`<div class="bulk-file-item">
            <div class="bulk-file-icon" style="background:${meta.bg};color:${meta.color}"><i class="bi ${meta.icon}"></i></div>
            <div class="bulk-file-name" title="${item.file.name}">${item.file.name}</div>
            <select onchange="bulkFiles[${idx}].detectedType=this.value;renderBulkFileList()" style="font-size:11px;font-weight:700;padding:3px 8px;border-radius:8px;border:1px solid #e8edf5;cursor:pointer;outline:none;max-width:110px">
                ${allTypes.map(t=>`<option value="${t}" ${item.detectedType===t?'selected':''}>${PLATFORM_META[t]?.label||t}</option>`).join('')}
            </select>
            <div class="bulk-detect-badge" style="flex-shrink:0">${item.detectedType!=='unknown'?'✓ Auto':'? Manual'}</div>
            <button class="bulk-file-remove" onclick="removeBulkFile(${idx})"><i class="bi bi-x-lg"></i></button>
        </div>`;}).join('');
}
function removeBulkFile(idx){bulkFiles.splice(idx,1);renderBulkFileList();updateComputeBtn();updateComputeBarDesc();}
const bulkDrop=document.getElementById('bulkDropZone');
bulkDrop.addEventListener('dragover',e=>{e.preventDefault();bulkDrop.classList.add('drag-active');});
bulkDrop.addEventListener('dragleave',()=>bulkDrop.classList.remove('drag-active'));
bulkDrop.addEventListener('drop',e=>{e.preventDefault();bulkDrop.classList.remove('drag-active');handleBulkFiles(e.dataTransfer.files);});

// ── XLSX/CSV READERS ─────────────────────────────────────────────────
function readXLSX(file){return new Promise((res,rej)=>{const r=new FileReader();r.onload=e=>{try{res(XLSX.read(new Uint8Array(e.target.result),{type:'array'}));}catch(err){rej(err);}};r.onerror=rej;r.readAsArrayBuffer(file);});}
function readCSV(file){return new Promise((res,rej)=>{const r=new FileReader();r.onload=e=>res(e.target.result);r.onerror=rej;r.readAsText(file);});}
function sheetToRows(ws){return XLSX.utils.sheet_to_json(ws,{header:1,defval:''});}

// ── STATE NORMALIZER ─────────────────────────────────────────────────
function normState(raw){
    if(!raw)return null;
    let s=String(raw).toUpperCase().trim();
    s=s.replace(/^IN-?/,'');
    if(CODE_MAP[s])return CODE_MAP[s];
    if(STATE_MAP[s])return STATE_MAP[s];
    const m=s.match(/^(\d{2})/);if(m)return m[1];
    for(const[k,v]of Object.entries(STATE_MAP)){if(k.length>3&&(k.startsWith(s.substring(0,6))||s.startsWith(k.substring(0,6))))return v;}
    return null;
}
function extractCodeFromPOS(pos){if(!pos)return null;const s=String(pos).trim();const m=s.match(/^(\d{2})/);if(m)return m[1];return normState(s);}

// ── B2CS ACCUMULATOR ─────────────────────────────────────────────────
function addToB2CS(platform, pos, rt, txval, homeState, hsn_code='', qty=0, tcs_amount=0) {
    if(!pos){
        unresolvedLog.push(`${platform}: state null, txval=${txval}`);
        log(`⚠ Could not resolve state for ${platform} row — txval ₹${txval} skipped`,'log-warn');
        return;
    }
    const v = parseFloat(txval) || 0;
    if (v === 0) return;

    const rtNum = normalizeRate(rt);
    const isIntra = (pos === homeState);
    const sply_ty = isIntra ? 'INTRA' : 'INTER';

    let iamt=0, camt=0, samt=0;
    if (isIntra) {
        const t = round2(v * rtNum / 200);
        camt = t; samt = t;
    } else {
        iamt = round2(v * rtNum / 100);
    }

    const mm = document.getElementById('inputMonth').value;
    const yyyy = document.getElementById('inputYear').value;

    // Store individual transaction for detailed report
    allParsedTransactions.push({
        platform, pos,
        period: mm + '/' + yyyy,
        fy: getFY(yyyy),
        nature: 'B2C',
        txval: round2(v),
        rt: rtNum,
        iamt, camt, samt,
        invoice_amt: round2(v + iamt + camt + samt),
        tcs_amount: round2(parseFloat(tcs_amount)||0),
        hsn_code: hsn_code || '—',
        qty: parseFloat(qty)||0,
    });

    // Aggregate for B2CS summary
    const key = `${pos}|${rtNum}`;
    if (!platformB2CS[platform]) platformB2CS[platform] = {};
    if (!platformB2CS[platform][key]) platformB2CS[platform][key] = { pos, rt: rtNum, sply_ty, txval: 0, iamt: 0, camt: 0, samt: 0 };
    const pr = platformB2CS[platform][key];
    pr.txval = round2(pr.txval + v);
    pr.iamt  = round2(pr.iamt + iamt);
    pr.camt  = round2(pr.camt + camt);
    pr.samt  = round2(pr.samt + samt);

    if (!b2csData[key]) b2csData[key] = { pos, rt: rtNum, sply_ty, txval: 0, iamt: 0, camt: 0, samt: 0 };
    const cr = b2csData[key];
    cr.txval = round2(cr.txval + v);
    cr.iamt  = round2(cr.iamt + iamt);
    cr.camt  = round2(cr.camt + camt);
    cr.samt  = round2(cr.samt + samt);
}

// ── PARSERS ──────────────────────────────────────────────────────────
async function parseViaBackend(file,type){
    const fd=new FormData();fd.append('file',file);fd.append('platform',type);fd.append('home_state',selectedClient.state);
    fd.append('filing_month',document.getElementById('inputMonth').value);fd.append('filing_year',document.getElementById('inputYear').value);
    const resp=await fetch(API_BASE+'/parse',{method:'POST',headers:{'X-CSRF-TOKEN':CSRF,'Accept':'application/json'},body:fd});
    if(!resp.ok)throw new Error(`Backend error ${resp.status}`);
    const data=await resp.json();if(!data.success)throw new Error(data.error||'Backend parsing failed');
    for(const row of data.rows||[])addToB2CS(row.platform||type,row.pos,row.rt,row.txval,selectedClient.state,row.hsn||'',row.qty||0,row.tcs||0);
    if(data.warnings?.length)data.warnings.forEach(w=>log(`⚠ ${w}`,'log-warn'));
    return data.row_count||0;
}

async function parseAmazon(file){
    log(`Amazon: ${file.name}`);const wb=await readXLSX(file);const homeState=selectedClient.state;let count=0;
    const sheetName=wb.SheetNames.find(n=>n.toLowerCase().includes('b2c small')||n.toLowerCase()==='b2cs');
    if(!sheetName){log('⚠ B2C Small sheet not found in Amazon file','log-warn');return 0;}
    const rows=sheetToRows(wb.Sheets[sheetName]);let hIdx=-1;
    for(let i=0;i<rows.length;i++){const r=rows[i].map(c=>String(c).toLowerCase());if(r.some(c=>c.includes('place of supply'))&&r.some(c=>c.includes('rate'))){hIdx=i;break;}}
    if(hIdx<0){log('⚠ Header not found in Amazon sheet','log-warn');return 0;}
    const hdr=rows[hIdx].map(c=>String(c).toLowerCase().trim());
    const posIdx=hdr.findIndex(h=>h.includes('place of supply'));
    const rateIdx=hdr.findIndex(h=>h==='rate'||(h.includes('rate')&&!h.includes('applicable')&&!h.includes('cess')));
    const txvalIdx=hdr.findIndex(h=>h.includes('taxable value'));
    const hsnIdx=hdr.findIndex(h=>h.includes('hsn'));
    const qtyIdx=hdr.findIndex(h=>h.includes('quantity')||h.includes('qty'));
    const tcsAmtIdx=hdr.findIndex(h=>h.includes('tcs')&&h.includes('amount'));
    if(posIdx<0||rateIdx<0||txvalIdx<0){log('⚠ Required columns not found in Amazon sheet','log-warn');return 0;}
    for(let i=hIdx+1;i<rows.length;i++){
        const r=rows[i];const txval=parseFloat(String(r[txvalIdx]||0).replace(/,/g,''))||0;if(txval===0)continue;
        const pos=extractCodeFromPOS(r[posIdx]);
        const hsn=hsnIdx>=0?String(r[hsnIdx]):'';
        const qty=qtyIdx>=0?parseFloat(r[qtyIdx])||0:0;
        const tcs=tcsAmtIdx>=0?parseFloat(r[tcsAmtIdx])||0:0;
        addToB2CS('amazon',pos,r[rateIdx],txval,homeState,hsn,qty,tcs);count++;
    }
    log(`✓ Amazon: ${count} rows`);return count;
}

async function parseFlipkart(file){
    log(`Flipkart: ${file.name}`);const wb=await readXLSX(file);const homeState=selectedClient.state;let count=0;
    for(const sheetName of wb.SheetNames){const sn=sheetName.toLowerCase();
        // Section 7A2 — INTRA
        if(sn.includes('7')&&sn.includes('a')&&sn.includes('2')){
            const rows=sheetToRows(wb.Sheets[sheetName]);let hIdx=-1;
            for(let i=0;i<rows.length;i++){const r=rows[i].map(c=>String(c).toLowerCase());if(r.some(c=>c.includes('aggregate'))&&r.some(c=>c.includes('cgst'))){hIdx=i;break;}}
            if(hIdx<0)continue;const hdr=rows[hIdx].map(c=>String(c).toLowerCase().trim());
            const aggIdx=hdr.findIndex(h=>h.includes('aggregate'));
            const cgstRateIdx=hdr.findIndex(h=>h.includes('cgst')&&h.includes('%'));
            const hsnIdx=hdr.findIndex(h=>h.includes('hsn'));
            const qtyIdx=hdr.findIndex(h=>h.includes('qty')||h.includes('quantity'));
            if(aggIdx<0||cgstRateIdx<0)continue;
            for(let i=hIdx+1;i<rows.length;i++){
                const r=rows[i];const txval=parseFloat(String(r[aggIdx]||0).replace(/,/g,''))||0;if(txval===0)continue;
                const cgstRate=normalizeRate(r[cgstRateIdx]);
                const hsn=hsnIdx>=0?String(r[hsnIdx]):'';
                const qty=qtyIdx>=0?parseFloat(r[qtyIdx])||0:0;
                addToB2CS('flipkart',homeState,cgstRate*2,txval,homeState,hsn,qty,0);count++;
            }
        }
        // Section 7B2 — INTER
        if(sn.includes('7')&&sn.includes('b')&&sn.includes('2')){
            const rows=sheetToRows(wb.Sheets[sheetName]);let hIdx=-1;
            for(let i=0;i<rows.length;i++){const r=rows[i].map(c=>String(c).toLowerCase());if(r.some(c=>c.includes('aggregate'))&&r.some(c=>c.includes('igst'))){hIdx=i;break;}}
            if(hIdx<0)continue;const hdr=rows[hIdx].map(c=>String(c).toLowerCase().trim());
            const aggIdx=hdr.findIndex(h=>h.includes('aggregate'));
            const igstRateIdx=hdr.findIndex(h=>h.includes('igst')&&h.includes('%'));
            const hsnIdx=hdr.findIndex(h=>h.includes('hsn'));
            const qtyIdx=hdr.findIndex(h=>h.includes('qty')||h.includes('quantity'));
            const stateCodeIdx=hdr.findIndex(h=>h.includes('state code')||(h.includes('delivered')&&h.includes('code')));
            const stateIdx=hdr.findIndex(h=>h.includes('delivered state')&&!h.includes('code'));
            if(aggIdx<0||igstRateIdx<0)continue;
            for(let i=hIdx+1;i<rows.length;i++){
                const r=rows[i];const txval=parseFloat(String(r[aggIdx]||0).replace(/,/g,''))||0;if(txval===0)continue;
                const igstRate=normalizeRate(r[igstRateIdx]);
                let pos=null;
                if(stateCodeIdx>=0&&r[stateCodeIdx])pos=normState(String(r[stateCodeIdx]));
                if(!pos&&stateIdx>=0&&r[stateIdx])pos=normState(String(r[stateIdx]));
                const hsn=hsnIdx>=0?String(r[hsnIdx]):'';
                const qty=qtyIdx>=0?parseFloat(r[qtyIdx])||0:0;
                addToB2CS('flipkart',pos,igstRate,txval,homeState,hsn,qty,0);count++;
            }
        }
    }
    log(`✓ Flipkart: ${count} rows`);return count;
}

async function parseMeesho(file){
    log(`Meesho: ${file.name}`);const wb=await readXLSX(file);const homeState=selectedClient.state;let count=0;
    const sheetName=wb.SheetNames.find(n=>n.toLowerCase().includes('sales'))||wb.SheetNames[0];
    const rows=sheetToRows(wb.Sheets[sheetName]);let hIdx=-1;
    for(let i=0;i<rows.length;i++){const r=rows[i].map(c=>String(c).toLowerCase());if(r.some(c=>c.includes('taxable value'))&&r.some(c=>c.includes('igst rate'))){hIdx=i;break;}}
    if(hIdx<0){log('⚠ Header not found in Meesho file','log-warn');return 0;}
    const hdr=rows[hIdx].map(c=>String(c).toLowerCase().trim());
    const txvalIdx=hdr.findIndex(h=>h.includes('taxable value'));
    const igstRateIdx=hdr.findIndex(h=>h.includes('igst rate'));
    const cgstRateIdx=hdr.findIndex(h=>h.includes('cgst rate'));
    const hsnIdx=hdr.findIndex(h=>h.includes('hsn'));
    const qtyIdx=hdr.findIndex(h=>h.includes('qty')||h.includes('quantity'));
    const tcsIdx=hdr.findIndex(h=>h.includes('tcs'));
    const stateAliasesPriority=["customer's delivery","delivery state","customer state","billing state"];
    let stateIdx=-1;
    for(const alias of stateAliasesPriority){const idx=hdr.findIndex(h=>h.includes(alias));if(idx>=0){stateIdx=idx;break;}}
    for(let i=hIdx+1;i<rows.length;i++){
        const r=rows[i];const txval=parseFloat(String(r[txvalIdx]||0).replace(/,/g,''))||0;if(txval===0)continue;
        let pos=null;if(stateIdx>=0&&r[stateIdx])pos=normState(String(r[stateIdx]));
        const igstRate=normalizeRate(r[igstRateIdx]||0);const cgstRate=normalizeRate(r[cgstRateIdx]||0);
        const gstRate=igstRate>0?igstRate:cgstRate*2;
        const hsn=hsnIdx>=0?String(r[hsnIdx]):'';
        const qty=qtyIdx>=0?parseFloat(r[qtyIdx])||0:0;
        const tcs=tcsIdx>=0?parseFloat(r[tcsIdx])||0:0;
        addToB2CS('meesho',pos,gstRate,txval,homeState,hsn,qty,tcs);count++;
    }
    log(`✓ Meesho: ${count} rows`);return count;
}

async function parseTCS(file,isReturn){
    const pKey=isReturn?'tcs_return':'tcs_sales';
    log(`TCS ${isReturn?'Return':'Sales'}: ${file.name}`);
    const homeState=selectedClient.state;let rows=[];let count=0;
    if(file.name.toLowerCase().endsWith('.csv')){const text=await readCSV(file);const lines=text.split('\n').filter(l=>l.trim());rows=lines.map(l=>l.split(',').map(c=>c.replace(/^"|"$/g,'').trim()));}
    else{const wb=await readXLSX(file);rows=sheetToRows(wb.Sheets[wb.SheetNames[0]]);}
    let hIdx=-1;
    for(let i=0;i<rows.length;i++){const r=rows[i].map(c=>String(c).toLowerCase().trim());
        if(r.some(c=>c==='total_taxable_sale_value'||c.includes('taxable'))&&r.some(c=>c==='end_customer_state_new'||c.includes('state'))){hIdx=i;break;}}
    if(hIdx<0){log('⚠ Header not found in TCS file','log-warn');return 0;}
    const hdr=rows[hIdx].map(c=>String(c).toLowerCase().trim());
    const stateIdx=hdr.findIndex(h=>h==='end_customer_state_new'||h.includes('end_customer_state')||h.includes('customer_state')||(h.includes('state')&&!h.includes('gstin')&&!h.includes('code')));
    const txvalIdx=hdr.findIndex(h=>h==='total_taxable_sale_value'||(h.includes('taxable')&&h.includes('sale')&&h.includes('value')));
    const shipIdx=hdr.findIndex(h=>h==='taxable_shipping'||h.includes('taxable_ship'));
    const rateIdx=hdr.findIndex(h=>h==='gst_rate'||(h.includes('gst')&&h.includes('rate')));
    const hsnIdx=hdr.findIndex(h=>h.includes('hsn'));
    const qtyIdx=hdr.findIndex(h=>h.includes('qty')||h.includes('quantity'));
    const tcsAmtIdx=hdr.findIndex(h=>h.includes('tcs_amount')||h.includes('tcs_amt')||(h.includes('tcs')&&!h.includes('rate')));
    if(stateIdx<0||txvalIdx<0){log('⚠ Required columns not found in TCS file','log-warn');return 0;}
    for(let i=hIdx+1;i<rows.length;i++){
        const r=rows[i];if(!r[txvalIdx]&&r[txvalIdx]!=='0')continue;
        let txval=parseFloat(String(r[txvalIdx]).replace(/,/g,''))||0;
        if(shipIdx>=0&&r[shipIdx])txval=round2(txval+(parseFloat(String(r[shipIdx]).replace(/,/g,''))||0));
        if(txval===0)continue;
        const pos=normState(String(r[stateIdx]||'').toUpperCase().trim());
        const rt=rateIdx>=0?normalizeRate(r[rateIdx]):0;
        const hsn=hsnIdx>=0?String(r[hsnIdx]):'';
        const qty=qtyIdx>=0?parseFloat(r[qtyIdx])||0:0;
        const tcs=tcsAmtIdx>=0?parseFloat(r[tcsAmtIdx])||0:0;
        addToB2CS(pKey,pos,rt,isReturn?-txval:txval,homeState,hsn,qty,tcs);count++;
    }
    log(`✓ TCS ${isReturn?'Return':'Sales'}: ${count} rows`);return count;
}

async function parseGenericPlatform(file,platform){
    log(`${PLATFORM_META[platform]?.label||platform}: ${file.name}`);
    const wb=await readXLSX(file);const homeState=selectedClient.state;let count=0;
    const txAliases=['taxable value','taxable amount','taxable_value','net taxable','aggregate taxable','base amount','taxable sale value','total_taxable_sale_value','selling price'];
    const rtAliases=['rate','gst rate','gst%','tax rate','igst rate','cgst rate','applicable rate','gst_rate'];
    const stAliases=['place of supply','delivery state','ship to state','customer state','billing state','end_customer_state','ship state','buyer state','destination state','state'];
    const hsnAliases=['hsn','hsn_code','hsn sac'];
    const qtyAliases=['quantity','qty','units','count'];
    const tcsAliases=['tcs_amount','tcs_amt','tcs'];
    function findByAliases(headers,aliases){for(const a of aliases){const idx=headers.findIndex(h=>h.includes(a));if(idx>=0)return idx;}return-1;}
    for(const sheetName of wb.SheetNames){const sn=sheetName.toLowerCase();if(sn.includes('summary')||sn.includes('lookup'))continue;
        const rows=sheetToRows(wb.Sheets[sheetName]);let hIdx=-1;
        for(let i=0;i<rows.length;i++){const r=rows[i].map(c=>String(c).toLowerCase());if(r.some(c=>txAliases.some(a=>c.includes(a)))&&r.some(c=>stAliases.some(a=>c.includes(a)))){hIdx=i;break;}}
        if(hIdx<0){for(let i=0;i<rows.length;i++){const r=rows[i].map(c=>String(c).toLowerCase());if(r.some(c=>txAliases.some(a=>c.includes(a)))){hIdx=i;break;}}}
        if(hIdx<0)continue;
        const hdr=rows[hIdx].map(c=>String(c).toLowerCase().trim());
        const txIdx=findByAliases(hdr,txAliases);const rtIdx=findByAliases(hdr,rtAliases);const stIdx=findByAliases(hdr,stAliases);
        const hsnIdx=findByAliases(hdr,hsnAliases);const qtyIdx=findByAliases(hdr,qtyAliases);const tcsIdx=findByAliases(hdr,tcsAliases);
        if(txIdx<0)continue;
        for(let i=hIdx+1;i<rows.length;i++){
            const r=rows[i];const txval=parseFloat(String(r[txIdx]||0).replace(/,/g,''))||0;if(txval===0)continue;
            let pos=stIdx>=0?normState(String(r[stIdx]||'')):null;if(!pos)pos=homeState;
            const hsn=hsnIdx>=0?String(r[hsnIdx]):'';
            const qty=qtyIdx>=0?parseFloat(r[qtyIdx])||0:0;
            const tcs=tcsIdx>=0?parseFloat(r[tcsIdx])||0:0;
            addToB2CS(platform,pos,rtIdx>=0?r[rtIdx]:0,txval,homeState,hsn,qty,tcs);count++;
        }
    }
    log(`✓ ${PLATFORM_META[platform]?.label||platform}: ${count} rows`);return count;
}

async function parseB2CSCSV(file){
    log(`B2CS CSV: ${file.name}`);const text=await readCSV(file);const homeState=selectedClient.state;
    const lines=text.split('\n').filter(l=>l.trim());if(lines.length<2){log('⚠ Empty B2CS CSV','log-warn');return 0;}
    const hdr=lines[0].split(',').map(c=>c.replace(/\r/,'').trim().toLowerCase());
    const posIdx=hdr.findIndex(h=>h.includes('place of supply'));const rateIdx=hdr.findIndex(h=>h==='rate');const txvalIdx=hdr.findIndex(h=>h.includes('taxable value'));
    if(posIdx<0||rateIdx<0||txvalIdx<0){log('⚠ Invalid B2CS CSV — expected Place of Supply, Rate, Taxable Value','log-warn');return 0;}
    let count=0;for(let i=1;i<lines.length;i++){const parts=lines[i].split(',');if(!parts[txvalIdx])continue;const pos=extractCodeFromPOS(parts[posIdx]);const txval=parseFloat(String(parts[txvalIdx]).replace(/,/g,''))||0;if(!pos)continue;addToB2CS('b2cs_csv',pos,parts[rateIdx],txval,homeState);count++;}
    log(`✓ B2CS CSV: ${count} rows`);return count;
}

async function parseFileByType(file,type){
    if(useBackend){try{return await parseViaBackend(file,type);}catch(e){log(`⚠ Backend failed for ${file.name}: ${e.message} — JS fallback`,'log-warn');}}
    switch(type){case'amazon':return await parseAmazon(file);case'flipkart':return await parseFlipkart(file);case'meesho':return await parseMeesho(file);case'tcs_sales':return await parseTCS(file,false);case'tcs_return':return await parseTCS(file,true);case'b2cs_csv':return await parseB2CSCSV(file);default:return await parseGenericPlatform(file,type);}
}

// ── MAIN COMPUTE ─────────────────────────────────────────────────────
async function computeB2CS(){
    if(!selectedClient){toast('Please select a client first','warn');return;}
    allParsedTransactions=[];b2csData={};platformB2CS={};unresolvedLog=[];
    const logBox=document.getElementById('logBox');logBox.innerHTML='';logBox.style.display='block';
    document.getElementById('progressWrap').style.display='block';document.getElementById('previewSection').classList.remove('visible');
    setStatus('processing','Processing...');document.getElementById('btnCompute').disabled=true;
    log(`Client: ${selectedClient.name} | GSTIN: ${selectedClient.gstin||'N/A'} | Home State: ${POS_TO_NAME[selectedClient.state]||selectedClient.state}`);
    try{
        if(currentMode==='bulk'){
            for(let i=0;i<bulkFiles.length;i++){await parseFileByType(bulkFiles[i].file,bulkFiles[i].detectedType);setProgress(((i+1)/bulkFiles.length)*90);}
        }else{
            const files=loadedFiles;let step=0;const total=Object.keys(files).length;
            for(const platform of PLATFORM_ORDER){if(files[platform]){await parseFileByType(files[platform],platform);setProgress((++step/total)*90);}}
        }
        setProgress(95);log('Building report...');
        if(unresolvedLog.length>0)log(`⚠ ${unresolvedLog.length} rows had unresolvable state — excluded.`,'log-warn');
        renderPreview();setProgress(100);setStatus('done','Done ✓');
        document.getElementById('previewSection').classList.add('visible');
        document.getElementById('previewSection').scrollIntoView({behavior:'smooth',block:'start'});
        toast('Computation complete!','success');
    }catch(err){log('✗ Error: '+err.message,'log-err');setStatus('error','Error');toast('Error: '+err.message,'error');console.error(err);}
    document.getElementById('btnCompute').disabled=false;
}

// ── RENDER PREVIEW ────────────────────────────────────────────────────
function sortedRows(dataObj){return Object.values(dataObj).sort((a,b)=>{if(a.sply_ty!==b.sply_ty)return a.sply_ty>b.sply_ty?1:-1;return a.pos.localeCompare(b.pos)||a.rt-b.rt;});}

function renderDetailedTransactions(){
    const tbody=document.getElementById('detailTableBody');
    if(allParsedTransactions.length===0){
        tbody.innerHTML='<tr><td colspan="15" style="text-align:center;padding:20px;color:#94a3b8">No transactions found</td></tr>';
        return;
    }
    const sorted=[...allParsedTransactions].sort((a,b)=>{
        if(a.platform!==b.platform)return a.platform.localeCompare(b.platform);
        return a.pos.localeCompare(b.pos)||a.rt-b.rt;
    });
    let totTxval=0,totCamt=0,totSamt=0,totIamt=0,totInvoice=0,totTcs=0,totQty=0;
    let html='';
    sorted.forEach((t,idx)=>{
        const isIntra=(t.pos===selectedClient.state);
        const stateName=POS_TO_NAME[t.pos]||t.pos||'—';
        const platMeta=PLATFORM_META[t.platform]||{label:t.platform,color:'#6b7a99'};
        totTxval+=t.txval;totCamt+=t.camt;totSamt+=t.samt;totIamt+=t.iamt;
        totInvoice+=t.invoice_amt;totTcs+=t.tcs_amount;totQty+=t.qty;
        html+=`<tr>
            <td style="color:#94a3b8;font-size:11px">${idx+1}</td>
            <td><span style="font-size:11px;font-weight:700;padding:2px 8px;border-radius:8px;background:${platMeta.bg||'#f0f4ff'};color:${platMeta.color}">${platMeta.label}</span></td>
            <td style="white-space:nowrap">${t.period}</td>
            <td style="white-space:nowrap;font-size:11px">${t.fy}</td>
            <td><span style="font-size:10px;font-weight:700;padding:2px 7px;border-radius:6px;background:#f0f7ff;color:#3a66c7">${t.nature}</span></td>
            <td style="font-size:11px;white-space:nowrap">${stateName}</td>
            <td style="font-family:'Courier New',monospace;font-size:11px">${t.hsn_code}</td>
            <td class="num">${t.qty>0?t.qty:'—'}</td>
            <td class="num">${fmtN(t.txval)}</td>
            <td class="num"><span class="rate-pill">${t.rt}%</span></td>
            <td class="num">${t.camt>0?fmtN(t.camt):'—'}</td>
            <td class="num">${t.samt>0?fmtN(t.samt):'—'}</td>
            <td class="num">${t.iamt>0?fmtN(t.iamt):'—'}</td>
            <td class="num"><strong>${fmtN(t.invoice_amt)}</strong></td>
            <td class="num">${t.tcs_amount>0?fmtN(t.tcs_amount):'—'}</td>
        </tr>`;
    });
    tbody.innerHTML=html;
    document.getElementById('dtotQty').textContent=totQty>0?totQty.toLocaleString('en-IN'):'—';
    document.getElementById('dtotTxval').textContent='₹'+fmtN(totTxval);
    document.getElementById('dtotCamt').textContent='₹'+fmtN(totCamt);
    document.getElementById('dtotSamt').textContent='₹'+fmtN(totSamt);
    document.getElementById('dtotIamt').textContent='₹'+fmtN(totIamt);
    document.getElementById('dtotInvoice').textContent='₹'+fmtN(totInvoice);
    document.getElementById('dtotTcs').textContent=totTcs>0?'₹'+fmtN(totTcs):'—';
}

function buildPlatformTableHTML(platform){
    const data=platformB2CS[platform];if(!data||Object.keys(data).length===0)return'';
    const meta=PLATFORM_META[platform]||{label:platform,icon:'bi-box',color:'#6b7a99',bg:'rgba(107,122,153,.1)'};
    const rows=sortedRows(data);
    const totTx=rows.reduce((s,r)=>s+r.txval,0);const totTax=rows.reduce((s,r)=>s+r.iamt+r.camt+r.samt,0);
    const totI=rows.reduce((s,r)=>s+r.iamt,0);const totC=rows.reduce((s,r)=>s+r.camt,0);const totS=rows.reduce((s,r)=>s+r.samt,0);
    let tbody='';for(const r of rows){const sn=POS_TO_NAME[r.pos]||r.pos;tbody+=`<tr><td>${r.sply_ty==='INTRA'?'<span class="badge-intra">INTRA</span>':'<span class="badge-inter">INTER</span>'}</td><td>${sn}</td><td><span class="rate-pill">${r.rt}%</span></td><td>${fmt(r.txval)}</td><td>${r.sply_ty==='INTER'?fmt(r.iamt):'—'}</td><td>${r.sply_ty==='INTRA'?fmt(r.camt):'—'}</td><td>${r.sply_ty==='INTRA'?fmt(r.samt):'—'}</td><td><strong>${fmt(r.iamt+r.camt+r.samt)}</strong></td></tr>`;}
    return`<div class="platform-report-block"><div class="platform-report-header"><div class="platform-icon-wrap" style="background:${meta.bg};color:${meta.color}"><i class="bi ${meta.icon}"></i></div><div><div class="platform-report-title">${meta.label}</div><div class="platform-report-meta">${rows.length} row${rows.length!==1?'s':''} · ${new Set(rows.map(r=>r.pos)).size} state${new Set(rows.map(r=>r.pos)).size!==1?'s':''}</div></div><span class="platform-status-pill ${totTx>=0?'psp-ok':'psp-warn'}">₹${Math.abs(totTx).toLocaleString('en-IN',{maximumFractionDigits:0})}${totTx<0?' CR':''}</span></div><div class="platform-table-wrap"><table class="gst-table"><thead><tr><th style="text-align:left">Type</th><th style="text-align:left">State</th><th style="text-align:left">GST Rate</th><th>Taxable (₹)</th><th>IGST (₹)</th><th>CGST (₹)</th><th>SGST (₹)</th><th>Total Tax (₹)</th></tr></thead><tbody>${tbody}</tbody><tfoot><tr class="tfoot-row"><td colspan="3" style="text-align:left"><strong>${meta.label} Total</strong></td><td>${fmt(totTx)}</td><td>${fmt(totI)}</td><td>${fmt(totC)}</td><td>${fmt(totS)}</td><td><strong>${fmt(totTax)}</strong></td></tr></tfoot></table></div></div>`;
}

function renderPreview(){
    const gstin=selectedClient.gstin||'—';const clientName=selectedClient.name;
    const mm=document.getElementById('inputMonth').value;const yyyy=document.getElementById('inputYear').value;
    const allRows=sortedRows(b2csData);
    const totalTxval=allRows.reduce((s,r)=>s+r.txval,0);const totalTax=allRows.reduce((s,r)=>s+r.iamt+r.camt+r.samt,0);
    const stateCount=new Set(allRows.map(r=>r.pos)).size;const platformCount=Object.keys(platformB2CS).length;
    document.getElementById('previewTitle').textContent=`B2CS Summary — ${clientName}`;
    document.getElementById('previewSubtitle').textContent=`GSTIN: ${gstin} | Period: ${mm}/${yyyy} | FY: ${getFY(yyyy)}`;
    document.getElementById('jsonFileName').textContent=`gstr1_b2cs_${gstin}_${mm}${yyyy}.json`;
    document.getElementById('summaryChips').innerHTML=`
        <div class="chip chip-purple"><i class="bi bi-person-fill"></i> ${clientName}</div>
        <div class="chip chip-teal"><i class="bi bi-layers-fill"></i> ${platformCount} Platform${platformCount!==1?'s':''}</div>
        <div class="chip chip-navy"><i class="bi bi-geo-alt-fill"></i> ${stateCount} States</div>
        <div class="chip chip-navy"><i class="bi bi-list-ul"></i> ${allParsedTransactions.length} Transactions</div>
        <div class="chip chip-sky"><i class="bi bi-cash-stack"></i> ₹${totalTxval.toLocaleString('en-IN',{maximumFractionDigits:0})} Taxable</div>
        <div class="chip chip-sky"><i class="bi bi-percent"></i> ₹${totalTax.toLocaleString('en-IN',{maximumFractionDigits:0})} Tax</div>
        ${unresolvedLog.length>0?`<div class="chip chip-warn"><i class="bi bi-exclamation-triangle-fill"></i> ${unresolvedLog.length} rows dropped</div>`:''}`;

    // Detailed transactions tab
    renderDetailedTransactions();

    // Platform-wise tab
    let platHtml='';for(const p of PLATFORM_ORDER){if(platformB2CS[p]&&Object.keys(platformB2CS[p]).length>0)platHtml+=buildPlatformTableHTML(p);}
    document.getElementById('platformReports').innerHTML=platHtml||'<div style="padding:20px;text-align:center;color:#94a3b8">No platform data</div>';

    // Grand total tab
    let tbody='',totTx=0,totI=0,totC=0,totS=0;
    for(const r of allRows){const sn=POS_TO_NAME[r.pos]||r.pos;const tax=r.iamt+r.camt+r.samt;totTx+=r.txval;totI+=r.iamt;totC+=r.camt;totS+=r.samt;tbody+=`<tr><td>${r.sply_ty==='INTRA'?'<span class="badge-intra">INTRA</span>':'<span class="badge-inter">INTER</span>'}</td><td>${sn}</td><td><span class="rate-pill">${r.rt}%</span></td><td>${fmt(r.txval)}</td><td>${r.sply_ty==='INTER'?fmt(r.iamt):'—'}</td><td>${r.sply_ty==='INTRA'?fmt(r.camt):'—'}</td><td>${r.sply_ty==='INTRA'?fmt(r.samt):'—'}</td><td><strong>${fmt(tax)}</strong></td></tr>`;}
    document.getElementById('previewTableBody').innerHTML=tbody||'<tr><td colspan="8" style="text-align:center;padding:20px;color:#94a3b8">No data found</td></tr>';
    document.getElementById('totTxval').innerHTML=fmt(totTx);document.getElementById('totIamt').innerHTML=fmt(totI);document.getElementById('totCamt').innerHTML=fmt(totC);document.getElementById('totSamt').innerHTML=fmt(totS);document.getElementById('totTax').innerHTML=`<strong>${fmt(totI+totC+totS)}</strong>`;

    // JSON tab
    document.getElementById('jsonPreview').textContent=JSON.stringify(buildJSONPayload(),null,2);

    // Default to detail tab
    switchReportTab('detail');
}

// ── JSON BUILDER ─────────────────────────────────────────────────────
function buildJSONPayload(){
    const mm=document.getElementById('inputMonth').value;const yyyy=document.getElementById('inputYear').value;
    const fp=mm+yyyy;
    const b2cs=Object.values(b2csData).filter(r=>r.txval!==0).map(r=>{
        const entry={sply_ty:r.sply_ty,pos:r.pos,rt:Number(r.rt),txval:round2(r.txval),typ:'OE',csamt:0.0};
        if(r.sply_ty==='INTER'){entry.iamt=round2(r.iamt);}
        else{entry.camt=round2(r.camt);entry.samt=round2(r.samt);}
        return entry;
    });
    return{gstin:selectedClient?.gstin||'',fp,version:'1.1',hash:'hash',b2cs};
}

// ── DOWNLOADS ────────────────────────────────────────────────────────
function downloadCSV(){
    const gstin=selectedClient?.gstin||'GSTIN';const mm=document.getElementById('inputMonth').value;const yyyy=document.getElementById('inputYear').value;
    // B2CS CSV — exact GST portal B2CS format columns
    const headers=['Type','Place Of Supply','Applicable % of Tax Rate','Taxable Value','Cess Amount','E-Commerce GSTIN'];
    let csv=headers.join(',')+'\r\n';
    for(const r of Object.values(b2csData)){
        const pos=POS_TO_NAME[r.pos]||r.pos;
        csv+=`OE,${pos},,${round2(r.txval)},,\r\n`;
    }
    const a=document.createElement('a');a.href=URL.createObjectURL(new Blob([csv],{type:'text/csv'}));
    a.download=`b2cs_${gstin}_${mm}${yyyy}.csv`;a.click();
    toast('B2CS CSV downloaded!','success');
}

function downloadDetailExcel(){
    if(!XLSX){toast('SheetJS not loaded','error');return;}
    const gstin=selectedClient?.gstin||'GSTIN';const mm=document.getElementById('inputMonth').value;const yyyy=document.getElementById('inputYear').value;
    const wb=XLSX.utils.book_new();
    // Detail sheet — all 15 columns
    const headers=['#','Platform','Period','FY','Nature','Destination State','HSN Code','Qty','Taxable Amount','GST Rate %','CGST','SGST','IGST','Invoice Amount','TCS'];
    const sorted=[...allParsedTransactions].sort((a,b)=>a.platform.localeCompare(b.platform)||a.pos.localeCompare(b.pos));
    const detailRows=[headers,...sorted.map((t,i)=>[
        i+1,
        PLATFORM_META[t.platform]?.label||t.platform,
        t.period,
        t.fy,
        t.nature,
        POS_TO_NAME[t.pos]||t.pos||'—',
        t.hsn_code,
        t.qty||0,
        round2(t.txval),
        t.rt,
        round2(t.camt),
        round2(t.samt),
        round2(t.iamt),
        round2(t.invoice_amt),
        round2(t.tcs_amount)
    ])];
    const ws=XLSX.utils.aoa_to_sheet(detailRows);
    ws['!cols']=[{wch:5},{wch:12},{wch:10},{wch:12},{wch:8},{wch:30},{wch:12},{wch:8},{wch:16},{wch:10},{wch:12},{wch:12},{wch:12},{wch:16},{wch:12}];
    XLSX.utils.book_append_sheet(wb,ws,'Detailed Transactions');
    // B2CS summary sheet
    const b2csHeaders=['Type','Place of Supply','Applicable % of Tax Rate','Taxable Value','Cess Amount','E-Commerce GSTIN'];
    const b2csRows=[b2csHeaders,...Object.values(b2csData).map(r=>['OE',POS_TO_NAME[r.pos]||r.pos,'',round2(r.txval),'',''])];
    const wsB2CS=XLSX.utils.aoa_to_sheet(b2csRows);wsB2CS['!cols']=[{wch:8},{wch:36},{wch:22},{wch:16},{wch:14},{wch:20}];
    XLSX.utils.book_append_sheet(wb,wsB2CS,'B2CS Summary');
    XLSX.writeFile(wb,`GSTR1_Detail_${gstin}_${mm}${yyyy}.xlsx`);
    toast('Detail Excel downloaded!','success');
}

function downloadExcel(){
    if(!XLSX){toast('SheetJS not loaded','error');return;}
    const gstin=selectedClient?.gstin||'GSTIN';const mm=document.getElementById('inputMonth').value;const yyyy=document.getElementById('inputYear').value;
    const wb=XLSX.utils.book_new();
    const headers=['Supply Type','Place of Supply','GST Rate %','Taxable Value (₹)','IGST (₹)','CGST (₹)','SGST (₹)','Total Tax (₹)'];
    function rowsForData(dataObj){return sortedRows(dataObj).map(r=>[r.sply_ty,POS_TO_NAME[r.pos]||r.pos,r.rt,round2(r.txval),round2(r.iamt),round2(r.camt),round2(r.samt),round2(r.iamt+r.camt+r.samt)]);}
    for(const platform of PLATFORM_ORDER){if(!platformB2CS[platform]||Object.keys(platformB2CS[platform]).length===0)continue;const data=[headers,...rowsForData(platformB2CS[platform])];const ws=XLSX.utils.aoa_to_sheet(data);ws['!cols']=[{wch:12},{wch:36},{wch:10},{wch:18},{wch:14},{wch:14},{wch:14},{wch:14}];XLSX.utils.book_append_sheet(wb,ws,(PLATFORM_META[platform]?.label||platform).substring(0,31));}
    const grandData=[headers,...rowsForData(b2csData)];const wsGrand=XLSX.utils.aoa_to_sheet(grandData);wsGrand['!cols']=[{wch:12},{wch:36},{wch:10},{wch:18},{wch:14},{wch:14},{wch:14},{wch:14}];XLSX.utils.book_append_sheet(wb,wsGrand,'Grand Total');
    XLSX.writeFile(wb,`GSTR1_Summary_${gstin}_${mm}${yyyy}.xlsx`);
    toast('Summary Excel downloaded!','success');
}

function downloadJSON(){
    const gstin=selectedClient?.gstin||'GSTIN';const mm=document.getElementById('inputMonth').value;const yyyy=document.getElementById('inputYear').value;
    const a=document.createElement('a');a.href=URL.createObjectURL(new Blob([JSON.stringify(buildJSONPayload(),null,2)],{type:'application/json'}));a.download=`gstr1_b2cs_${gstin}_${mm}${yyyy}.json`;a.click();
    toast('JSON downloaded!','success');
}

function copyJSON(){
    navigator.clipboard.writeText(document.getElementById('jsonPreview').textContent).then(()=>{
        const btn=event.target.closest('button');btn.innerHTML='<i class="bi bi-check-circle-fill"></i> Copied!';
        setTimeout(()=>btn.innerHTML='<i class="bi bi-clipboard"></i> Copy',2000);
        toast('JSON copied!','success');
    });
}

// ── INIT ─────────────────────────────────────────────────────────────
renderClientList();updateComputeBtn();updateComputeBarDesc();
</script>
@endsection
