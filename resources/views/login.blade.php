@include('layouts.header')
@extends('layouts.app')
@section('content')

<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

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

/* HERO */
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

.tool-wrap{max-width:1200px;margin:0 auto;padding:40px 32px 80px}

/* STEP CARDS */
.step-card{background:#fff;border-radius:20px;border:1px solid #e8edf5;margin-bottom:24px;overflow:hidden;box-shadow:0 2px 16px rgba(0,0,0,.04);transition:box-shadow .2s;}
.step-card:hover{box-shadow:0 6px 30px rgba(0,0,0,.08)}
.step-header{display:flex;align-items:center;gap:16px;padding:20px 28px;border-bottom:1px solid #f0f4ff;background:linear-gradient(90deg,#fafcff,#fff);}
.step-num{width:36px;height:36px;border-radius:10px;background:var(--teal-low);border:1px solid rgba(0,201,141,.2);display:flex;align-items:center;justify-content:center;font-family:'Syne',sans-serif;font-weight:800;font-size:15px;color:var(--teal);flex-shrink:0;}
.step-title{font-family:'Syne',sans-serif;font-weight:700;font-size:17px;color:var(--navy)}
.step-desc{font-size:13px;color:#6b7a99;margin-top:2px}
.step-body{padding:24px 28px}

/* FORMS */
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px}
.form-row-3{grid-template-columns:1fr 1fr 1fr}
.form-group{display:flex;flex-direction:column;gap:6px}
.form-label{font-size:12px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:#6b7a99;}
.form-label i{color:var(--teal);margin-right:4px}
.form-control-gst{padding:11px 14px;border:1.5px solid #e8edf5;border-radius:12px;font-family:'DM Sans',sans-serif;font-size:14px;color:var(--navy);background:#fff;transition:all .2s;outline:none;}
.form-control-gst:focus{border-color:var(--teal);box-shadow:0 0 0 3px rgba(0,201,141,.1)}
.form-control-gst::placeholder{color:#b0bcd4}
select.form-control-gst{cursor:pointer;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%2300c98d' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 12px center;appearance:none;padding-right:36px;}

/* ── CLIENT MANAGER ── */
.client-manager-wrap{background:linear-gradient(135deg,#f7faff 0%,#eef3fc 100%);border:1.5px solid #dde6f5;border-radius:20px;padding:24px 28px;margin-bottom:24px;}
.client-manager-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:12px;}
.client-manager-title{font-family:'Syne',sans-serif;font-weight:800;font-size:18px;color:var(--navy)}

/* Search Bar */
.client-search-wrap{position:relative;margin-bottom:16px}
.client-search-wrap i{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:15px;pointer-events:none}
.client-search-input{width:100%;padding:10px 14px 10px 38px;border:1.5px solid #e8edf5;border-radius:12px;font-family:'DM Sans',sans-serif;font-size:14px;color:var(--navy);background:#fff;transition:all .2s;outline:none;}
.client-search-input:focus{border-color:var(--teal);box-shadow:0 0 0 3px rgba(0,201,141,.1)}
.client-search-input::placeholder{color:#b0bcd4}

.client-list{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px;margin-bottom:16px;}
.client-card{background:#fff;border:1.5px solid #e8edf5;border-radius:14px;padding:14px 16px;cursor:pointer;transition:all .2s;display:flex;align-items:center;gap:12px;position:relative;}
.client-card:hover{border-color:var(--teal);box-shadow:0 4px 16px rgba(0,201,141,.12)}
.client-card.selected{border-color:var(--teal);background:rgba(0,201,141,.04);box-shadow:0 4px 20px rgba(0,201,141,.18)}
.client-card.selected::after{content:'✓';position:absolute;top:8px;right:10px;width:20px;height:20px;border-radius:50%;background:var(--teal);color:#fff;font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;line-height:20px;text-align:center;}
.client-avatar{width:40px;height:40px;border-radius:12px;background:var(--teal-low);border:1px solid rgba(0,201,141,.2);display:flex;align-items:center;justify-content:center;font-family:'Syne',sans-serif;font-weight:800;font-size:14px;color:var(--teal);flex-shrink:0;}
.client-name{font-weight:600;font-size:13px;color:var(--navy);line-height:1.3}
.client-gstin{font-size:11px;color:#94a3b8;margin-top:2px;font-family:'Courier New',monospace}
.client-del{position:absolute;top:6px;right:6px;width:18px;height:18px;background:#fee2e2;border:none;border-radius:50%;cursor:pointer;display:none;align-items:center;justify-content:center;font-size:10px;color:#ef4444;transition:all .2s;}
.client-card:hover .client-del{display:flex}
.client-card.selected .client-del{right:32px}
.no-clients{text-align:center;padding:30px;color:#94a3b8;font-size:14px;border:2px dashed #e8edf5;border-radius:14px;}

/* ── MODALS (general) ── */
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;overflow-y:auto;padding:16px;}
.modal-overlay.show{display:flex}
.modal-box{background:#fff;border-radius:20px;padding:32px;width:100%;max-width:480px;box-shadow:0 20px 60px rgba(0,0,0,.2);position:relative;margin:auto;}
.modal-title{font-family:'Syne',sans-serif;font-weight:800;font-size:20px;color:var(--navy);margin-bottom:20px}
.modal-close{position:absolute;top:16px;right:16px;background:none;border:none;cursor:pointer;font-size:18px;color:#94a3b8;padding:4px}
.modal-close:hover{color:var(--navy)}

/* Client Detail Modal */
.detail-modal-box{max-width:520px}
.detail-row{display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:1px solid #f0f4ff;}
.detail-row:last-child{border-bottom:none}
.detail-label{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#94a3b8}
.detail-value{font-size:14px;font-weight:600;color:var(--navy);text-align:right;font-family:'Courier New',monospace}
.detail-avatar-lg{width:64px;height:64px;border-radius:18px;background:var(--teal-low);border:2px solid rgba(0,201,141,.2);display:flex;align-items:center;justify-content:center;font-family:'Syne',sans-serif;font-weight:800;font-size:24px;color:var(--teal);margin:0 auto 16px;flex-shrink:0;}

/* ── UPLOAD GRID ── */
.upload-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
.upload-zone{border:2px dashed #dde3ef;border-radius:16px;padding:20px 16px;text-align:center;cursor:pointer;transition:all .2s;background:#fafcff;position:relative;overflow:hidden;}
.upload-zone:hover,.upload-zone.drag-over{border-color:var(--teal);background:rgba(0,201,141,.03);}
.upload-zone.has-file{border-color:var(--teal);border-style:solid;background:rgba(0,201,141,.05);}
.upload-zone input[type=file]{position:absolute;inset:0;opacity:0;cursor:pointer;z-index:2;}
.uz-icon{width:44px;height:44px;border-radius:12px;background:rgba(0,201,141,.1);display:flex;align-items:center;justify-content:center;margin:0 auto 10px;font-size:20px;color:var(--teal);transition:all .2s;}
.upload-zone:hover .uz-icon{background:rgba(0,201,141,.2)}
.upload-zone.has-file .uz-icon{background:rgba(0,201,141,.2)}
.uz-name{font-family:'Syne',sans-serif;font-weight:700;font-size:13px;color:var(--navy);margin-bottom:4px}
.uz-hint{font-size:11px;color:#94a3b8;line-height:1.5}
.uz-filename{margin-top:8px;font-size:11px;color:var(--teal);font-weight:600;display:none;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:100%;}
.upload-zone.has-file .uz-filename{display:block}
.uz-tag{display:inline-block;font-size:9px;font-weight:700;letter-spacing:.8px;text-transform:uppercase;padding:2px 8px;border-radius:20px;margin-bottom:6px;}
.uz-tag.amazon{background:rgba(255,153,0,.12);color:#c07000}
.uz-tag.flipkart{background:rgba(100,149,237,.12);color:#3a66c7}
.uz-tag.meesho{background:rgba(160,32,240,.12);color:#8020d0}
.uz-tag.tcs{background:rgba(0,201,141,.12);color:#008060}
.uz-tag.optional{background:rgba(148,163,184,.12);color:#64748b}

/* ── BULK UPLOAD SECTION ── */
.bulk-section{background:linear-gradient(135deg,#f7faff,#edf0ff);border:1.5px solid #dde6f5;border-radius:20px;padding:24px 28px;margin-bottom:24px;}
.bulk-drop-zone{border:2px dashed #dde3ef;border-radius:16px;padding:40px 24px;text-align:center;cursor:pointer;transition:all .2s;background:#fff;position:relative;}
.bulk-drop-zone.drag-active{border-color:var(--teal);background:rgba(0,201,141,.04);}
.bulk-drop-zone input[type=file]{position:absolute;inset:0;opacity:0;cursor:pointer;z-index:2;}
.bulk-icon{width:56px;height:56px;border-radius:16px;background:rgba(0,170,255,.1);display:flex;align-items:center;justify-content:center;margin:0 auto 14px;font-size:26px;color:var(--sky);}
.bulk-file-list{margin-top:16px;display:flex;flex-direction:column;gap:8px;max-height:260px;overflow-y:auto;}
.bulk-file-item{display:flex;align-items:center;gap:10px;background:#fff;border:1px solid #e8edf5;border-radius:10px;padding:10px 14px;}
.bulk-file-icon{width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0;}
.bulk-file-name{font-size:13px;font-weight:600;color:var(--navy);flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.bulk-file-type{font-size:11px;font-weight:700;padding:2px 8px;border-radius:20px;flex-shrink:0;}
.bulk-file-remove{background:none;border:none;cursor:pointer;color:#94a3b8;font-size:14px;padding:2px 4px;transition:color .2s;}
.bulk-file-remove:hover{color:#ef4444}
.bulk-detect-badge{display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:700;padding:2px 10px;border-radius:20px;background:rgba(0,201,141,.1);color:#008060;border:1px solid rgba(0,201,141,.2);}

/* ── BUTTONS ── */
.btn-teal{font-family:'DM Sans',sans-serif;font-weight:700;font-size:14px;color:var(--navy);background:linear-gradient(135deg,var(--teal) 0%,#00e0a8 100%);border:none;border-radius:12px;padding:12px 24px;cursor:pointer;display:inline-flex;align-items:center;gap:8px;box-shadow:0 4px 18px rgba(0,201,141,.3);transition:all .2s;text-decoration:none;}
.btn-teal:hover{transform:translateY(-2px);box-shadow:0 8px 28px rgba(0,201,141,.45);color:var(--navy)}
.btn-teal:disabled{opacity:.4;cursor:not-allowed;transform:none}
.btn-navy{font-family:'DM Sans',sans-serif;font-weight:700;font-size:14px;color:#fff;background:var(--navy);border:none;border-radius:12px;padding:12px 24px;cursor:pointer;display:inline-flex;align-items:center;gap:8px;transition:all .2s;text-decoration:none;}
.btn-navy:hover{background:var(--deep);transform:translateY(-1px);color:#fff}
.btn-navy:disabled{opacity:.4;cursor:not-allowed;transform:none}
.btn-outline-teal{font-family:'DM Sans',sans-serif;font-weight:700;font-size:14px;color:var(--teal);background:transparent;border:1.5px solid var(--teal);border-radius:12px;padding:11px 22px;cursor:pointer;display:inline-flex;align-items:center;gap:8px;transition:all .2s;text-decoration:none;}
.btn-outline-teal:hover{background:var(--teal-low);transform:translateY(-1px)}
.btn-outline-teal:disabled{opacity:.4;cursor:not-allowed}
.btn-sm{padding:7px 14px;font-size:12px;border-radius:10px}
.action-row{display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-top:8px}

/* COMPUTE BAR */
.compute-bar{background:linear-gradient(135deg,var(--navy) 0%,var(--deep) 100%);border-radius:20px;padding:28px 32px;display:flex;align-items:center;justify-content:space-between;gap:20px;flex-wrap:wrap;margin-bottom:24px;border:1px solid var(--border);}
.compute-bar-left h3{font-family:'Syne',sans-serif;font-weight:800;font-size:18px;color:#fff;margin-bottom:4px;}
.compute-bar-left p{font-size:13px;color:var(--muted)}

.status-badge{display:inline-flex;align-items:center;gap:6px;padding:4px 12px;border-radius:20px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;}
.status-idle{background:rgba(148,163,184,.12);color:#94a3b8}
.status-processing{background:rgba(0,170,255,.12);color:var(--sky)}
.status-done{background:rgba(0,201,141,.12);color:var(--teal)}
.status-error{background:rgba(239,68,68,.12);color:#ef4444}

/* PLATFORM REPORT */
.platform-report-block{border:1px solid #e8edf5;border-radius:16px;overflow:hidden;margin-bottom:20px;}
.platform-report-header{display:flex;align-items:center;gap:14px;padding:16px 20px;background:#f7faff;border-bottom:1px solid #e8edf5;}
.platform-icon-wrap{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;}
.platform-report-title{font-family:'Syne',sans-serif;font-weight:700;font-size:15px;color:var(--navy)}
.platform-report-meta{font-size:12px;color:#6b7a99;margin-top:2px}
.platform-status-pill{margin-left:auto;padding:4px 12px;border-radius:20px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;}
.psp-ok{background:rgba(0,201,141,.1);color:#008060}
.psp-warn{background:rgba(245,158,11,.1);color:#92500a}
.psp-nil{background:rgba(148,163,184,.1);color:#64748b}
.platform-table-wrap{overflow-x:auto;padding:0}

/* PREVIEW */
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

/* TABLES */
.table-wrap{overflow-x:auto;border-radius:16px;border:1px solid #e8edf5}
table.gst-table{width:100%;border-collapse:collapse;font-size:13px}
.gst-table thead th{background:#f7faff;padding:10px 14px;font-weight:700;font-size:11px;letter-spacing:.5px;text-transform:uppercase;color:#6b7a99;border-bottom:1px solid #e8edf5;white-space:nowrap;text-align:right;}
.gst-table thead th:first-child,.gst-table thead th:nth-child(2),.gst-table thead th:nth-child(3),.gst-table thead th:nth-child(4){text-align:left}
.gst-table tbody td{padding:10px 14px;border-bottom:1px solid #f0f4ff;color:var(--navy);vertical-align:middle;text-align:right;}
.gst-table tbody td:first-child,.gst-table tbody td:nth-child(2),.gst-table tbody td:nth-child(3),.gst-table tbody td:nth-child(4){text-align:left}
.gst-table tbody tr:hover{background:#f9fbff}
.gst-table tbody tr:last-child td{border-bottom:none}
.badge-intra{display:inline-block;font-size:10px;font-weight:700;padding:2px 8px;border-radius:20px;background:rgba(0,201,141,.1);color:#008060;}
.badge-inter{display:inline-block;font-size:10px;font-weight:700;padding:2px 8px;border-radius:20px;background:rgba(0,170,255,.1);color:#005fa0;}
.rate-pill{display:inline-block;font-size:10px;font-weight:700;padding:2px 8px;border-radius:20px;background:#f0f4ff;color:var(--navy);}
.tfoot-row td{font-weight:700;background:#f7faff;border-top:2px solid #e8edf5;color:var(--navy);}
.neg-val{color:#ef4444}

/* JSON BOX */
.json-box{background:var(--navy);border-radius:16px;border:1px solid var(--border);overflow:hidden;margin-top:16px;}
.json-box-header{display:flex;align-items:center;justify-content:space-between;padding:12px 20px;border-bottom:1px solid var(--border);}
.json-box-header span{font-family:'Syne',sans-serif;font-weight:700;font-size:13px;color:var(--muted);}
.json-pre{padding:20px;font-family:'Courier New',monospace;font-size:12px;line-height:1.7;color:#8be9fd;max-height:360px;overflow-y:auto;white-space:pre-wrap;word-break:break-all;}
.json-pre::-webkit-scrollbar{width:4px}
.json-pre::-webkit-scrollbar-track{background:rgba(255,255,255,.05)}
.json-pre::-webkit-scrollbar-thumb{background:var(--teal);border-radius:4px}

/* PROGRESS / LOG */
.progress-bar-wrap{background:#e8edf5;border-radius:8px;height:6px;overflow:hidden;margin-top:12px;display:none;}
.progress-bar-fill{height:100%;border-radius:8px;width:0%;background:linear-gradient(90deg,var(--teal),var(--sky));transition:width .3s ease;}
.log-box{background:#f7faff;border-radius:12px;padding:14px 16px;margin-top:14px;font-size:12px;line-height:1.9;color:#6b7a99;font-family:'Courier New',monospace;max-height:140px;overflow-y:auto;display:none;border:1px solid #e8edf5;}

/* ALERTS */
.alert-gst{padding:12px 16px;border-radius:12px;font-size:13px;display:flex;align-items:flex-start;gap:10px;margin-bottom:16px;}
.alert-info{background:rgba(0,170,255,.08);border:1px solid rgba(0,170,255,.2);color:#005fa0}
.alert-success{background:rgba(0,201,141,.08);border:1px solid rgba(0,201,141,.2);color:#007050}
.alert-warn{background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.2);color:#92500a}
.alert-error{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);color:#9b1c1c}
.alert-gst i{font-size:15px;flex-shrink:0;margin-top:1px}

/* SELECTED CLIENT BAR */
.selected-client-bar{background:linear-gradient(135deg,rgba(0,201,141,.08),rgba(0,170,255,.06));border:1.5px solid rgba(0,201,141,.25);border-radius:14px;padding:14px 20px;display:flex;align-items:center;gap:14px;margin-bottom:20px;}
.scb-avatar{width:44px;height:44px;border-radius:12px;background:var(--teal-low);border:1px solid rgba(0,201,141,.2);display:flex;align-items:center;justify-content:center;font-family:'Syne',sans-serif;font-weight:800;font-size:16px;color:var(--teal);}
.scb-name{font-family:'Syne',sans-serif;font-weight:700;font-size:16px;color:var(--navy)}
.scb-gstin{font-size:12px;color:#6b7a99;font-family:'Courier New',monospace;margin-top:2px}
.scb-change{margin-left:auto}

/* MODE TABS */
.mode-tabs{display:flex;gap:4px;background:#f0f4ff;border-radius:14px;padding:4px;margin-bottom:20px;}
.mode-tab{flex:1;text-align:center;padding:9px 12px;border-radius:11px;font-weight:700;font-size:13px;cursor:pointer;transition:all .2s;border:none;color:#6b7a99;background:transparent;}
.mode-tab.active{background:#fff;color:var(--navy);box-shadow:0 2px 10px rgba(0,0,0,.08)}

/* Bulk result tabs */
.result-tabs{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px}
.result-tab{padding:7px 16px;border-radius:20px;font-size:12px;font-weight:700;cursor:pointer;transition:all .2s;border:1.5px solid #e8edf5;background:#fff;color:#6b7a99;}
.result-tab.active{background:var(--teal-low);border-color:var(--teal);color:#007050}

/* TOAST */
.toast-wrap{position:fixed;bottom:24px;right:24px;z-index:9999;display:flex;flex-direction:column;gap:8px;}
.toast{background:var(--navy);color:#fff;padding:12px 20px;border-radius:14px;font-size:13px;font-weight:600;display:flex;align-items:center;gap:10px;box-shadow:0 8px 30px rgba(0,0,0,.25);animation:slideUp .3s ease;max-width:320px;}
.toast.success{background:#00935e}
.toast.error{background:#c0392b}
.toast.warn{background:#b45309}
@keyframes slideUp{from{transform:translateY(20px);opacity:0}to{transform:translateY(0);opacity:1}}

@media(max-width:900px){.upload-grid{grid-template-columns:1fr 1fr}.form-row-3{grid-template-columns:1fr 1fr}.client-list{grid-template-columns:repeat(auto-fill,minmax(160px,1fr))}}
@media(max-width:640px){.tool-wrap{padding:24px 16px 60px}.hero-inner{padding:0 16px}.upload-grid{grid-template-columns:1fr}.form-row,.form-row-3{grid-template-columns:1fr}.step-body{padding:18px 16px}.step-header{padding:14px 16px}.compute-bar{padding:20px}.action-row{flex-direction:column;align-items:stretch}.action-row .btn-teal,.action-row .btn-navy,.action-row .btn-outline-teal{justify-content:center}.mode-tabs{flex-direction:column}}
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
        <p class="tool-sub">Client-wise GSTR-1 generator — add clients, select one, upload marketplace reports, get B2CS data platform-wise. Now with <strong>Bulk Mode</strong> for unlimited files.</p>
    </div>
</section>

<!-- TOAST CONTAINER -->
<div class="toast-wrap" id="toastWrap"></div>

<!-- MODAL: Add / Edit Client -->
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
                    <option value="32" selected>32 – Kerala</option>
                    <option value="01">01 – Jammu &amp; Kashmir</option>
                    <option value="02">02 – Himachal Pradesh</option>
                    <option value="03">03 – Punjab</option>
                    <option value="04">04 – Chandigarh</option>
                    <option value="05">05 – Uttarakhand</option>
                    <option value="06">06 – Haryana</option>
                    <option value="07">07 – Delhi</option>
                    <option value="08">08 – Rajasthan</option>
                    <option value="09">09 – Uttar Pradesh</option>
                    <option value="10">10 – Bihar</option>
                    <option value="11">11 – Sikkim</option>
                    <option value="12">12 – Arunachal Pradesh</option>
                    <option value="13">13 – Nagaland</option>
                    <option value="14">14 – Manipur</option>
                    <option value="15">15 – Mizoram</option>
                    <option value="16">16 – Tripura</option>
                    <option value="17">17 – Meghalaya</option>
                    <option value="18">18 – Assam</option>
                    <option value="19">19 – West Bengal</option>
                    <option value="20">20 – Jharkhand</option>
                    <option value="21">21 – Odisha</option>
                    <option value="22">22 – Chhattisgarh</option>
                    <option value="23">23 – Madhya Pradesh</option>
                    <option value="24">24 – Gujarat</option>
                    <option value="26">26 – Dadra &amp; Nagar Haveli</option>
                    <option value="27">27 – Maharashtra</option>
                    <option value="28">28 – Puducherry</option>
                    <option value="29">29 – Karnataka</option>
                    <option value="30">30 – Goa</option>
                    <option value="31">31 – Lakshadweep</option>
                    <option value="33">33 – Tamil Nadu</option>
                    <option value="34">34 – Andaman &amp; Nicobar</option>
                    <option value="35">35 – Telangana</option>
                    <option value="36">36 – Andhra Pradesh</option>
                    <option value="37">37 – Andhra Pradesh (new)</option>
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

<!-- MODAL: Client Detail View -->
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
            <button class="btn-teal btn-sm" onclick="selectFromDetail()" style="flex:1;justify-content:center">
                <i class="bi bi-check2-circle"></i> Select This Client
            </button>
            <button class="btn-outline-teal btn-sm" onclick="editFromDetail()">
                <i class="bi bi-pencil"></i> Edit
            </button>
            <button class="btn-outline-teal btn-sm" style="color:#ef4444;border-color:#ef4444" onclick="deleteFromDetail()">
                <i class="bi bi-trash3"></i> Delete
            </button>
        </div>
    </div>
</div>

<!-- MAIN TOOL -->
<div class="tool-wrap dot-grid" id="toolWrap">

    <div class="alert-gst alert-info">
        <i class="bi bi-info-circle-fill"></i>
        <div><strong>How it works:</strong> (1) Add clients &amp; select one → (2) Enter filing period → (3) Upload files (Single or <strong>Bulk Mode</strong>) → (4) Click <strong>Compute B2CS</strong> → Download CSV + JSON per client.</div>
    </div>

    <!-- ══ CLIENT MANAGER ══ -->
    <div class="client-manager-wrap">
        <div class="client-manager-header">
            <div>
                <div class="client-manager-title"><i class="bi bi-people-fill" style="color:var(--teal);margin-right:8px"></i>Client Manager</div>
                <div style="font-size:13px;color:#6b7a99;margin-top:4px">Click a client card to view details &amp; select</div>
            </div>
            <button class="btn-teal btn-sm" onclick="openClientModal()">
                <i class="bi bi-plus-lg"></i> Add Client
            </button>
        </div>

        <!-- Search -->
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
                <div>
                    <div class="scb-name" id="scbName">—</div>
                    <div class="scb-gstin" id="scbGstin">—</div>
                </div>
                <div style="margin-left:auto;display:flex;gap:8px;flex-wrap:wrap">
                    <button class="btn-outline-teal btn-sm" onclick="openDetailModal(selectedClient.id)">
                        <i class="bi bi-eye"></i> Details
                    </button>
                    <button class="btn-outline-teal btn-sm" onclick="clearSelectedClient()">
                        <i class="bi bi-arrow-repeat"></i> Change
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ══ STEP 1: FILING PERIOD ══ -->
    <div class="step-card">
        <div class="step-header">
            <div class="step-num">1</div>
            <div>
                <div class="step-title">Filing Period</div>
                <div class="step-desc">Select the month and year for GSTR-1</div>
            </div>
        </div>
        <div class="step-body">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label"><i class="bi bi-calendar3"></i> Filing Month</label>
                    <select id="inputMonth" class="form-control-gst">
                        <option value="01">January</option><option value="02" selected>February</option>
                        <option value="03">March</option><option value="04">April</option>
                        <option value="05">May</option><option value="06">June</option>
                        <option value="07">July</option><option value="08">August</option>
                        <option value="09">September</option><option value="10">October</option>
                        <option value="11">November</option><option value="12">December</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label"><i class="bi bi-calendar-year"></i> Filing Year</label>
                    <select id="inputYear" class="form-control-gst">
                        <option value="2024">2024</option>
                        <option value="2025">2025</option>
                        <option value="2026" selected>2026</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- ══ STEP 2: UPLOAD FILES ══ -->
    <div class="step-card">
        <div class="step-header">
            <div class="step-num">2</div>
            <div>
                <div class="step-title">Upload Marketplace Reports</div>
                <div class="step-desc">Single file per platform, or use Bulk Mode for multiple files at once</div>
            </div>
        </div>
        <div class="step-body">
            <!-- Mode Tabs -->
            <div class="mode-tabs">
                <button class="mode-tab active" id="tabSingle" onclick="switchMode('single')">
                    <i class="bi bi-file-earmark-spreadsheet"></i> Single Mode
                </button>
                <button class="mode-tab" id="tabBulk" onclick="switchMode('bulk')">
                    <i class="bi bi-files"></i> Bulk Mode <span style="font-size:10px;background:rgba(0,201,141,.15);color:#008060;padding:1px 7px;border-radius:10px;margin-left:4px">UNLIMITED</span>
                </button>
            </div>

            <!-- SINGLE MODE -->
            <div id="singleMode">
                <div class="upload-grid">
                    <div class="upload-zone" id="zone-amazon" onclick="document.getElementById('file-amazon').click()">
                        <input type="file" id="file-amazon" accept=".xlsx,.xls" onchange="handleFile(this,'amazon')">
                        <div class="uz-icon"><i class="bi bi-amazon"></i></div>
                        <div class="uz-tag amazon">Amazon</div>
                        <div class="uz-name">GSTR-1 Ready Report</div>
                        <div class="uz-hint">Amazon GST Ready-to-File Report (.xlsx)</div>
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
                        <div class="uz-name">Sales Report</div>
                        <div class="uz-hint">Meesho Monthly Sales Report (.xlsx)</div>
                        <div class="uz-filename" id="fn-meesho"></div>
                    </div>
                    <div class="upload-zone" id="zone-tcs-sales" onclick="document.getElementById('file-tcs-sales').click()">
                        <input type="file" id="file-tcs-sales" accept=".xlsx,.xls,.csv" onchange="handleFile(this,'tcs_sales')">
                        <div class="uz-icon"><i class="bi bi-receipt"></i></div>
                        <div class="uz-tag tcs">TCS Sales</div>
                        <div class="uz-name">TCS Sales Report</div>
                        <div class="uz-hint">Meesho / Other TCS Sales (.xlsx / .csv)</div>
                        <div class="uz-filename" id="fn-tcs_sales"></div>
                    </div>
                    <div class="upload-zone" id="zone-tcs-return" onclick="document.getElementById('file-tcs-return').click()">
                        <input type="file" id="file-tcs-return" accept=".xlsx,.xls,.csv" onchange="handleFile(this,'tcs_return')">
                        <div class="uz-icon" style="color:#e87a00;background:rgba(232,122,0,.1)"><i class="bi bi-receipt-cutoff"></i></div>
                        <div class="uz-tag optional">TCS Return</div>
                        <div class="uz-name">TCS Return / Refund</div>
                        <div class="uz-hint">TCS Returns / Cancellations (.xlsx / .csv)</div>
                        <div class="uz-filename" id="fn-tcs_return"></div>
                    </div>
                    <div class="upload-zone" id="zone-b2cs" onclick="document.getElementById('file-b2cs').click()">
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

            <!-- BULK MODE -->
            <div id="bulkMode" style="display:none">
                <div class="alert-gst alert-info" style="margin-bottom:16px">
                    <i class="bi bi-lightbulb-fill"></i>
                    <div>Drop <strong>unlimited files</strong> here. Auto-detects platform (Amazon / Flipkart / Meesho / TCS) from filename &amp; sheet names. Each file is processed separately and shown in a tabbed report.</div>
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

    <!-- ══ STEP 3: PREVIEW ══ -->
    <div class="preview-section" id="previewSection">
        <div class="step-card">
            <div class="step-header">
                <div class="step-num">3</div>
                <div>
                    <div class="step-title">Platform-wise B2CS Report &amp; Download</div>
                    <div class="step-desc">Data shown platform-by-platform in sequence, then combined grand total</div>
                </div>
            </div>
            <div class="step-body">
                <div class="preview-header">
                    <div>
                        <div class="preview-title" id="previewTitle">B2CS Summary</div>
                        <div class="preview-subtitle" id="previewSubtitle"></div>
                    </div>
                    <div class="action-row">
                        <button class="btn-outline-teal" onclick="downloadCSV()"><i class="bi bi-filetype-csv"></i> Download B2CS CSV</button>
                        <button class="btn-navy" onclick="downloadJSON()"><i class="bi bi-filetype-json"></i> Download JSON for Portal</button>
                    </div>
                </div>

                <div class="summary-chips" id="summaryChips"></div>

                <!-- Result tabs for bulk mode -->
                <div id="resultTabsWrap" style="display:none">
                    <div class="result-tabs" id="resultTabs"></div>
                </div>

                <!-- Platform-wise sections -->
                <div id="platformReports"></div>

                <!-- Grand Total Table -->
                <div style="margin-top:24px">
                    <div style="font-family:'Syne',sans-serif;font-weight:700;font-size:16px;color:var(--navy);margin-bottom:12px;display:flex;align-items:center;gap:8px">
                        <i class="bi bi-sigma" style="color:var(--teal)"></i> Grand Total (All Platforms Combined)
                    </div>
                    <div class="table-wrap">
                        <table class="gst-table">
                            <thead>
                                <tr>
                                    <th>Type</th><th>State</th><th>GST Rate</th>
                                    <th>Taxable Value (₹)</th><th>IGST (₹)</th>
                                    <th>CGST (₹)</th><th>SGST (₹)</th><th>Total Tax (₹)</th>
                                </tr>
                            </thead>
                            <tbody id="previewTableBody"></tbody>
                            <tfoot>
                                <tr class="tfoot-row">
                                    <td colspan="3"><strong>GRAND TOTAL</strong></td>
                                    <td id="totTxval">—</td><td id="totIamt">—</td>
                                    <td id="totCamt">—</td><td id="totSamt">—</td>
                                    <td id="totTax">—</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- JSON Preview -->
                <div style="margin-top:24px">
                    <div style="font-family:'Syne',sans-serif;font-weight:700;font-size:15px;color:var(--navy);margin-bottom:12px">
                        <i class="bi bi-braces" style="color:var(--teal)"></i> JSON Preview (GST Portal Format)
                    </div>
                    <div class="json-box">
                        <div class="json-box-header">
                            <span id="jsonFileName">gstr1_b2cs.json</span>
                            <button class="btn-outline-teal" style="padding:5px 14px;font-size:12px" onclick="copyJSON()">
                                <i class="bi bi-clipboard"></i> Copy
                            </button>
                        </div>
                        <pre class="json-pre" id="jsonPreview">// JSON will appear here after computing...</pre>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
/* ═══════════════════════════════════════════════════════════════════
   GSTR-1 E-Commerce Tool v2 — Client Manager + Bulk Mode + Detail View
═══════════════════════════════════════════════════════════════════ */

// ── TOAST ───────────────────────────────────────────────────────────
function toast(msg, type='success', dur=3000) {
    const wrap = document.getElementById('toastWrap');
    const icons = { success:'bi-check-circle-fill', error:'bi-x-circle-fill', warn:'bi-exclamation-triangle-fill', info:'bi-info-circle-fill' };
    const t = document.createElement('div');
    t.className = `toast ${type}`;
    t.innerHTML = `<i class="bi ${icons[type]||icons.info}"></i> ${msg}`;
    wrap.appendChild(t);
    setTimeout(() => { t.style.opacity='0'; t.style.transform='translateY(20px)'; t.style.transition='all .3s'; setTimeout(()=>t.remove(),300); }, dur);
}

// ── CLIENT MANAGER ───────────────────────────────────────────────────
let clients = JSON.parse(localStorage.getItem('gst_clients_v2') || '[]');
let selectedClient = null;
let detailClientId = null;

function saveClientsToStorage() {
    localStorage.setItem('gst_clients_v2', JSON.stringify(clients));
}

function initials(name) {
    if (!name) return '--';
    const parts = name.trim().split(/\s+/);
    if (parts.length >= 2) return (parts[0][0] + parts[1][0]).toUpperCase();
    return name.substring(0, 2).toUpperCase();
}

function getStateName(code) {
    const sel = document.getElementById('newClientState');
    for (const opt of sel.options) {
        if (opt.value === code) return opt.text;
    }
    return code;
}

function renderClientList() {
    const list = document.getElementById('clientList');
    const noMsg = document.getElementById('noClientsMsg');
    const query = (document.getElementById('clientSearch').value || '').toLowerCase().trim();

    if (clients.length === 0) {
        list.innerHTML = '';
        list.appendChild(noMsg);
        noMsg.style.display = 'block';
        return;
    }
    noMsg.style.display = 'none';

    const filtered = query
        ? clients.filter(c => c.name.toLowerCase().includes(query) || (c.gstin||'').toLowerCase().includes(query))
        : clients;

    if (filtered.length === 0) {
        list.innerHTML = `<div class="no-clients" style="grid-column:1/-1"><i class="bi bi-search" style="font-size:24px;display:block;margin-bottom:8px;color:#dde3ef"></i>No clients match "<strong>${query}</strong>"</div>`;
        return;
    }

    list.innerHTML = filtered.map(c => `
        <div class="client-card ${selectedClient && selectedClient.id === c.id ? 'selected' : ''}" onclick="openDetailModal('${c.id}')">
            <div class="client-avatar">${initials(c.name)}</div>
            <div style="min-width:0">
                <div class="client-name">${highlight(c.name, query)}</div>
                <div class="client-gstin">${highlight(c.gstin || 'No GSTIN', query)}</div>
            </div>
            <button class="client-del" onclick="deleteClient(event,'${c.id}')" title="Delete"><i class="bi bi-trash3"></i></button>
        </div>
    `).join('');
}

function highlight(text, query) {
    if (!query) return text;
    const idx = text.toLowerCase().indexOf(query);
    if (idx < 0) return text;
    return text.substring(0, idx) + `<mark style="background:rgba(0,201,141,.2);border-radius:3px;padding:0 2px">${text.substring(idx, idx+query.length)}</mark>` + text.substring(idx+query.length);
}

function openClientModal(editId) {
    const isEdit = !!editId;
    document.getElementById('clientModalTitle').innerHTML = isEdit
        ? '<i class="bi bi-pencil-fill" style="color:var(--teal);margin-right:8px"></i>Edit Client'
        : '<i class="bi bi-person-plus-fill" style="color:var(--teal);margin-right:8px"></i>Add New Client';
    document.getElementById('saveClientBtnText').textContent = isEdit ? 'Update Client' : 'Save Client';
    document.getElementById('editClientId').value = editId || '';
    document.getElementById('gstinError').style.display = 'none';

    if (isEdit) {
        const c = clients.find(x => x.id === editId);
        if (c) {
            document.getElementById('newClientName').value = c.name;
            document.getElementById('newClientGstin').value = c.gstin || '';
            document.getElementById('newClientState').value = c.state || '32';
            document.getElementById('newClientPhone').value = c.phone || '';
            document.getElementById('newClientEmail').value = c.email || '';
        }
    } else {
        document.getElementById('newClientName').value = '';
        document.getElementById('newClientGstin').value = '';
        document.getElementById('newClientPhone').value = '';
        document.getElementById('newClientEmail').value = '';
        document.getElementById('newClientState').value = '32';
    }
    document.getElementById('clientDetailModal').classList.remove('show');
    document.getElementById('clientModal').classList.add('show');
    setTimeout(() => document.getElementById('newClientName').focus(), 100);
}

function closeClientModal() {
    document.getElementById('clientModal').classList.remove('show');
}

document.getElementById('clientModal').addEventListener('click', function(e) {
    if (e.target === this) closeClientModal();
});
document.getElementById('newClientGstin').addEventListener('input', function() {
    this.value = this.value.toUpperCase();
    document.getElementById('gstinError').style.display = 'none';
});

function validateGSTIN(g) {
    return /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/.test(g);
}

function saveClient() {
    const editId = document.getElementById('editClientId').value;
    const name = document.getElementById('newClientName').value.trim();
    const gstin = document.getElementById('newClientGstin').value.trim().toUpperCase();
    const state = document.getElementById('newClientState').value;
    const phone = document.getElementById('newClientPhone').value.trim();
    const email = document.getElementById('newClientEmail').value.trim();

    if (!name) { toast('Please enter client name', 'warn'); return; }
    if (!gstin) { toast('GSTIN is required', 'warn'); return; }

    // Validate GSTIN format
    if (!validateGSTIN(gstin)) {
        document.getElementById('gstinError').style.display = 'block';
        document.getElementById('gstinError').textContent = 'Invalid GSTIN format. Must be like 29ABCDE1234F1Z5';
        return;
    }

    // Check duplicate GSTIN (exclude self if editing)
    const duplicate = clients.find(c => c.gstin === gstin && c.id !== editId);
    if (duplicate) {
        document.getElementById('gstinError').style.display = 'block';
        document.getElementById('gstinError').textContent = `GSTIN already exists for client: ${duplicate.name}`;
        return;
    }

    if (editId) {
        const idx = clients.findIndex(c => c.id === editId);
        if (idx >= 0) {
            clients[idx] = { ...clients[idx], name, gstin, state, phone, email, updatedAt: new Date().toISOString() };
            if (selectedClient && selectedClient.id === editId) {
                selectedClient = clients[idx];
                updateSelectedClientBar();
            }
            toast(`Client "${name}" updated`, 'success');
        }
    } else {
        const client = { id: Date.now().toString(), name, gstin, state, phone, email, createdAt: new Date().toISOString() };
        clients.push(client);
        toast(`Client "${name}" added`, 'success');
        if (clients.length === 1) selectClient(client.id);
    }

    saveClientsToStorage();
    renderClientList();
    closeClientModal();
}

function deleteClient(e, id) {
    if (e) e.stopPropagation();
    const c = clients.find(x => x.id === id);
    if (!c) return;
    if (!confirm(`Delete "${c.name}"?`)) return;
    clients = clients.filter(x => x.id !== id);
    if (selectedClient && selectedClient.id === id) clearSelectedClient();
    saveClientsToStorage();
    renderClientList(); // Instant re-render, no refresh needed
    toast(`"${c.name}" deleted`, 'warn');
}

function selectClient(id) {
    selectedClient = clients.find(c => c.id === id) || null;
    renderClientList();
    updateSelectedClientBar();
    updateComputeBtn();
    updateComputeBarDesc();
}

function clearSelectedClient() {
    selectedClient = null;
    renderClientList();
    updateSelectedClientBar();
    updateComputeBtn();
    updateComputeBarDesc();
}

function updateSelectedClientBar() {
    const bar = document.getElementById('selectedClientBar');
    if (!selectedClient) { bar.style.display = 'none'; return; }
    bar.style.display = 'block';
    document.getElementById('scbAvatar').textContent = initials(selectedClient.name);
    document.getElementById('scbName').textContent = selectedClient.name;
    document.getElementById('scbGstin').textContent = selectedClient.gstin || 'GSTIN not set';
}

// Client Detail Modal
function openDetailModal(id) {
    const c = clients.find(x => x.id === id);
    if (!c) return;
    detailClientId = id;
    document.getElementById('detailAvatar').textContent = initials(c.name);
    document.getElementById('detailName').textContent = c.name;
    document.getElementById('detailCreated').textContent = `Added: ${c.createdAt ? new Date(c.createdAt).toLocaleDateString('en-IN',{day:'2-digit',month:'short',year:'numeric'}) : 'Unknown'}`;

    const rows = [
        { label:'GSTIN', value: c.gstin || '—' },
        { label:'Home State', value: c.state ? getStateName(c.state) : '—' },
        { label:'Phone', value: c.phone || '—' },
        { label:'Email', value: c.email || '—' },
        { label:'Client ID', value: c.id },
    ];
    document.getElementById('detailRows').innerHTML = rows.map(r => `
        <div class="detail-row">
            <span class="detail-label">${r.label}</span>
            <span class="detail-value">${r.value}</span>
        </div>
    `).join('');

    document.getElementById('clientDetailModal').classList.add('show');
}
function closeDetailModal() {
    document.getElementById('clientDetailModal').classList.remove('show');
    detailClientId = null;
}
document.getElementById('clientDetailModal').addEventListener('click', function(e) {
    if (e.target === this) closeDetailModal();
});
function selectFromDetail() {
    if (!detailClientId) return;
    selectClient(detailClientId);
    closeDetailModal();
    toast(`Client selected`, 'success');
}
function editFromDetail() {
    const id = detailClientId;
    closeDetailModal();
    openClientModal(id);
}
function deleteFromDetail() {
    const id = detailClientId;
    closeDetailModal();
    deleteClient(null, id);
}

// ── UPLOAD MODE TOGGLE ──────────────────────────────────────────────
let currentMode = 'single';
function switchMode(mode) {
    currentMode = mode;
    document.getElementById('singleMode').style.display = mode === 'single' ? 'block' : 'none';
    document.getElementById('bulkMode').style.display = mode === 'bulk' ? 'block' : 'none';
    document.getElementById('tabSingle').classList.toggle('active', mode === 'single');
    document.getElementById('tabBulk').classList.toggle('active', mode === 'bulk');
    updateComputeBtn();
    updateComputeBarDesc();
}

// ── STATE / PLATFORM DATA ────────────────────────────────────────────
const STATE_MAP = {
    "ANDHRA PRADESH":"37","ARUNACHAL PRADESH":"12","ASSAM":"18",
    "BIHAR":"10","CHANDIGARH":"04","CHHATTISGARH":"22","DELHI":"07",
    "GOA":"30","GUJARAT":"24","HARYANA":"06","HIMACHAL PRADESH":"02",
    "JAMMU & KASHMIR":"01","JAMMU AND KASHMIR":"01","JHARKHAND":"20",
    "KARNATAKA":"29","KERALA":"32","MADHYA PRADESH":"23","MAHARASHTRA":"27",
    "MANIPUR":"14","MEGHALAYA":"17","MIZORAM":"15","NAGALAND":"13",
    "ODISHA":"21","PONDICHERRY":"28","PUDUCHERRY":"28","PUNJAB":"03",
    "RAJASTHAN":"08","SIKKIM":"11","TAMIL NADU":"33","TELANGANA":"36",
    "TRIPURA":"16","UTTAR PRADESH":"09","UTTARAKHAND":"05","WEST BENGAL":"19",
    "DADAR & NAGAR HAVELI":"26","DADAR AND NAGAR HAVELI":"26",
    "DADRA AND NAGAR HAVELI":"26","DADRA & NAGAR HAVELI":"26",
    "LAKSHADWEEP":"31","ANDAMAN AND NICOBAR":"35","ANDAMAN & NICOBAR":"35",
    "JHARKAND":"20","ORRISA":"21","UTTARANCHAL":"05","ARUNACHAL":"12","J & K":"01","J&K":"01"
};
const POS_TO_NAME = {
    "01":"01-Jammu & Kashmir","02":"02-Himachal Pradesh","03":"03-Punjab","04":"04-Chandigarh",
    "05":"05-Uttarakhand","06":"06-Haryana","07":"07-Delhi","08":"08-Rajasthan",
    "09":"09-Uttar Pradesh","10":"10-Bihar","11":"11-Sikkim","12":"12-Arunachal Pradesh",
    "13":"13-Nagaland","14":"14-Manipur","15":"15-Mizoram","16":"16-Tripura",
    "17":"17-Meghalaya","18":"18-Assam","19":"19-West Bengal","20":"20-Jharkhand",
    "21":"21-Odisha","22":"22-Chhattisgarh","23":"23-Madhya Pradesh","24":"24-Gujarat",
    "26":"26-Dadra & N.Haveli","27":"27-Maharashtra","28":"28-Puducherry","29":"29-Karnataka",
    "30":"30-Goa","31":"31-Lakshadweep","32":"32-Kerala","33":"33-Tamil Nadu",
    "34":"34-Andaman & Nicobar","35":"35-Telangana","36":"36-Andhra Pradesh (new)","37":"37-Andhra Pradesh"
};
const PLATFORM_ORDER = ['meesho','amazon','flipkart','tcs_sales','tcs_return','b2cs_csv'];
const PLATFORM_META = {
    meesho:    { label:'Meesho',     icon:'bi-shop',          color:'#8020d0', bg:'rgba(160,32,240,.1)',   pillClass:'pp-meesho' },
    amazon:    { label:'Amazon',     icon:'bi-amazon',        color:'#c07000', bg:'rgba(255,153,0,.1)',    pillClass:'pp-amazon' },
    flipkart:  { label:'Flipkart',   icon:'bi-basket3-fill',  color:'#3a66c7', bg:'rgba(100,149,237,.1)',  pillClass:'pp-flipkart' },
    tcs_sales: { label:'TCS Sales',  icon:'bi-receipt',       color:'#008060', bg:'rgba(0,201,141,.1)',    pillClass:'pp-tcs' },
    tcs_return:{ label:'TCS Return', icon:'bi-receipt-cutoff',color:'#e87a00', bg:'rgba(232,122,0,.1)',    pillClass:'pp-tcs' },
    b2cs_csv:  { label:'B2CS CSV',   icon:'bi-filetype-csv',  color:'#0080aa', bg:'rgba(0,128,170,.1)',    pillClass:'pp-tcs' }
};

// ── FILE STATE ─────────────────────────────────────────────────────
const loadedFiles = {};       // single mode: type -> File
let bulkFiles = [];           // bulk mode: [{file, detectedType, label}]
let platformB2CS = {};
let b2csData = {};

// ── UI HELPERS ──────────────────────────────────────────────────────
function log(msg) {
    const box = document.getElementById('logBox');
    box.style.display = 'block';
    box.innerHTML += `<span style="color:var(--teal)">→</span> ${msg}<br>`;
    box.scrollTop = box.scrollHeight;
}
function setProgress(pct) {
    document.getElementById('progressWrap').style.display = 'block';
    document.getElementById('progressFill').style.width = pct + '%';
}
function setStatus(state, text) {
    const s = document.getElementById('computeStatus');
    s.className = 'status-badge status-' + state;
    const icons = { idle:'bi-circle', processing:'bi-arrow-repeat', done:'bi-check-circle-fill', error:'bi-x-circle-fill' };
    s.innerHTML = `<i class="bi ${icons[state]}"></i> ${text}`;
}
function fmt(n) {
    if (n === null || n === undefined) return '—';
    const v = parseFloat(n);
    if (isNaN(v)) return '—';
    const cls = v < 0 ? ' class="neg-val"' : '';
    return `<span${cls}>₹${Math.abs(v).toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2})}${v<0?' CR':''}</span>`;
}
function updateComputeBarDesc() {
    const el = document.getElementById('computeBarDesc');
    const fileCount = currentMode === 'bulk' ? bulkFiles.length : Object.keys(loadedFiles).length;
    if (!selectedClient && fileCount === 0) el.textContent = 'Select a client and upload at least one file to begin';
    else if (!selectedClient) el.textContent = `${fileCount} file(s) uploaded — now select a client`;
    else if (fileCount === 0) el.textContent = `Client: ${selectedClient.name} — upload at least one file to begin`;
    else el.textContent = `Client: ${selectedClient.name} | ${fileCount} file(s) — ready!`;
}

// ── SINGLE MODE FILE HANDLER ────────────────────────────────────────
function handleFile(input, type) {
    const file = input.files[0];
    if (!file) return;
    const zoneId = 'zone-' + type.replace(/_/g,'-');
    document.getElementById(zoneId).classList.add('has-file');
    document.getElementById('fn-' + type).textContent = '✓ ' + file.name;
    loadedFiles[type] = file;
    updateComputeBtn();
    updateComputeBarDesc();
    showUploadStatus();
}
function showUploadStatus() {
    const count = Object.keys(loadedFiles).length;
    const el = document.getElementById('uploadStatus');
    if (count === 0) { el.innerHTML = ''; return; }
    el.innerHTML = `<div class="alert-gst alert-success" style="margin-bottom:0"><i class="bi bi-check-circle-fill"></i> <strong>${count} file${count>1?'s':''} loaded</strong> — ${Object.keys(loadedFiles).join(', ').replace(/_/g,' ')}</div>`;
}

// ── BULK MODE ───────────────────────────────────────────────────────
function detectPlatformFromFile(file) {
    const name = file.name.toLowerCase();
    if (name.includes('amazon')) return 'amazon';
    if (name.includes('flipkart') || name.includes('fk')) return 'flipkart';
    if (name.includes('meesho')) return 'meesho';
    if (name.includes('tcs') && (name.includes('return') || name.includes('refund') || name.includes('cancel'))) return 'tcs_return';
    if (name.includes('tcs')) return 'tcs_sales';
    if (name.includes('b2cs')) return 'b2cs_csv';
    return 'unknown';
}

function platformLabel(type) {
    const meta = PLATFORM_META[type];
    return meta ? meta.label : 'Unknown';
}

function platformBgColor(type) {
    const colors = { amazon:'rgba(255,153,0,.15)', flipkart:'rgba(100,149,237,.15)', meesho:'rgba(160,32,240,.15)', tcs_sales:'rgba(0,201,141,.15)', tcs_return:'rgba(232,122,0,.15)', b2cs_csv:'rgba(0,128,170,.15)', unknown:'rgba(148,163,184,.15)' };
    return colors[type] || colors.unknown;
}
function platformTextColor(type) {
    const colors = { amazon:'#c07000', flipkart:'#3a66c7', meesho:'#8020d0', tcs_sales:'#008060', tcs_return:'#e87a00', b2cs_csv:'#0080aa', unknown:'#64748b' };
    return colors[type] || colors.unknown;
}

function handleBulkFiles(files) {
    for (const file of files) {
        const dup = bulkFiles.find(f => f.file.name === file.name && f.file.size === file.size);
        if (dup) { toast(`"${file.name}" already added`, 'warn'); continue; }
        const detectedType = detectPlatformFromFile(file);
        bulkFiles.push({ file, detectedType, id: Date.now() + Math.random() });
    }
    renderBulkFileList();
    updateComputeBtn();
    updateComputeBarDesc();
}

function renderBulkFileList() {
    const list = document.getElementById('bulkFileList');
    if (bulkFiles.length === 0) { list.innerHTML = ''; return; }
    list.innerHTML = bulkFiles.map((item, idx) => `
        <div class="bulk-file-item">
            <div class="bulk-file-icon" style="background:${platformBgColor(item.detectedType)};color:${platformTextColor(item.detectedType)}">
                <i class="bi ${PLATFORM_META[item.detectedType]?.icon || 'bi-file-earmark'}"></i>
            </div>
            <div class="bulk-file-name" title="${item.file.name}">${item.file.name}</div>
            <select class="bulk-platform-sel" onchange="bulkFiles[${idx}].detectedType=this.value;renderBulkFileList()" style="font-size:11px;font-weight:700;padding:3px 8px;border-radius:8px;border:1px solid #e8edf5;background:${platformBgColor(item.detectedType)};color:${platformTextColor(item.detectedType)};cursor:pointer;outline:none;max-width:110px">
                ${['amazon','flipkart','meesho','tcs_sales','tcs_return','b2cs_csv','unknown'].map(t =>
                    `<option value="${t}" ${item.detectedType===t?'selected':''}>${platformLabel(t)||t}</option>`
                ).join('')}
            </select>
            <div class="bulk-detect-badge" style="flex-shrink:0">${item.detectedType !== 'unknown' ? '✓ Auto' : '? Manual'}</div>
            <button class="bulk-file-remove" onclick="removeBulkFile(${idx})" title="Remove"><i class="bi bi-x-lg"></i></button>
        </div>
    `).join('');
}

function removeBulkFile(idx) {
    bulkFiles.splice(idx, 1);
    renderBulkFileList();
    updateComputeBtn();
    updateComputeBarDesc();
}

// Drag & Drop for bulk
const bulkDrop = document.getElementById('bulkDropZone');
bulkDrop.addEventListener('dragover', e => { e.preventDefault(); bulkDrop.classList.add('drag-active'); });
bulkDrop.addEventListener('dragleave', () => bulkDrop.classList.remove('drag-active'));
bulkDrop.addEventListener('drop', e => {
    e.preventDefault();
    bulkDrop.classList.remove('drag-active');
    handleBulkFiles(e.dataTransfer.files);
});

function updateComputeBtn() {
    const count = currentMode === 'bulk' ? bulkFiles.length : Object.keys(loadedFiles).length;
    document.getElementById('btnCompute').disabled = !(count > 0 && selectedClient);
}

// ── XLSX/CSV READERS ─────────────────────────────────────────────────
function readXLSX(file) {
    return new Promise((res, rej) => {
        const reader = new FileReader();
        reader.onload = e => { try { res(XLSX.read(new Uint8Array(e.target.result),{type:'array'})); } catch(err){rej(err);} };
        reader.onerror = rej;
        reader.readAsArrayBuffer(file);
    });
}
function readCSV(file) {
    return new Promise((res, rej) => {
        const reader = new FileReader();
        reader.onload = e => res(e.target.result);
        reader.onerror = rej;
        reader.readAsText(file);
    });
}
function sheetToRows(ws) { return XLSX.utils.sheet_to_json(ws, {header:1, defval:''}); }

// ── STATE NORMALIZER ──────────────────────────────────────────────────
function normState(raw) {
    if (!raw) return null;
    const s = String(raw).toUpperCase().trim();
    if (STATE_MAP[s]) return STATE_MAP[s];
    const stripped = s.replace(/^IN-/,'').trim();
    for (const [k, v] of Object.entries(STATE_MAP)) {
        if (k === stripped) return v;
        if (k.startsWith(stripped) || stripped.startsWith(k.substring(0,6))) return v;
    }
    // Try numeric code
    const m = s.match(/^(\d{2})/);
    if (m) return m[1];
    return null;
}
function extractCodeFromPlaceOfSupply(pos) {
    if (!pos) return null;
    const s = String(pos).trim();
    const m = s.match(/^(\d{2})/);
    if (m) return m[1];
    return normState(s);
}

// ── B2CS AGGREGATOR ──────────────────────────────────────────────────
function round2(n) { return Math.round(n * 100) / 100; }

function addToB2CS(platform, pos, rt, txval, homeState) {
    if (!pos || txval === 0) return;
    const rtNum = parseFloat(rt) || 0;
    const key = `${pos}|${rtNum}`;
    const isIntra = (pos === homeState);
    if (!platformB2CS[platform]) platformB2CS[platform] = {};
    if (!platformB2CS[platform][key]) {
        platformB2CS[platform][key] = { pos, rt:rtNum, sply_ty:isIntra?'INTRA':'INTER', txval:0, iamt:0, camt:0, samt:0 };
    }
    const pr = platformB2CS[platform][key];
    const v = parseFloat(txval) || 0;
    pr.txval = round2(pr.txval + v);
    if (isIntra) { const t = round2(v*rtNum/200); pr.camt=round2(pr.camt+t); pr.samt=round2(pr.samt+t); }
    else { pr.iamt=round2(pr.iamt+round2(v*rtNum/100)); }
    if (!b2csData[key]) {
        b2csData[key] = { pos, rt:rtNum, sply_ty:isIntra?'INTRA':'INTER', txval:0, iamt:0, camt:0, samt:0 };
    }
    const cr = b2csData[key];
    cr.txval=round2(cr.txval+v);
    if (isIntra) { const t=round2(v*rtNum/200); cr.camt=round2(cr.camt+t); cr.samt=round2(cr.samt+t); }
    else { cr.iamt=round2(cr.iamt+round2(v*rtNum/100)); }
}

// ── PLATFORM PARSERS ──────────────────────────────────────────────────
async function parseAmazon(file) {
    log(`Amazon: ${file.name}`);
    const wb = await readXLSX(file);
    const homeState = selectedClient ? selectedClient.state : '32';
    let count = 0;
    const b2csSheet = wb.SheetNames.find(n => n.toLowerCase().includes('b2c small') || n.toLowerCase() === 'b2cs');
    if (b2csSheet) {
        const rows = sheetToRows(wb.Sheets[b2csSheet]);
        let hIdx = -1;
        for (let i = 0; i < rows.length; i++) {
            const r = rows[i].map(c => String(c).toLowerCase());
            if (r.some(c => c.includes('place of supply')) && r.some(c => c.includes('rate'))) { hIdx=i; break; }
        }
        if (hIdx < 0) { log('⚠ Header not found in Amazon B2C Small sheet'); return count; }
        const headers = rows[hIdx].map(c => String(c).toLowerCase().trim());
        const posIdx = headers.findIndex(h => h.includes('place of supply'));
        const rateIdx = headers.findIndex(h => h==='rate'||(h.includes('rate')&&!h.includes('applicable')&&!h.includes('cess')));
        const txvalIdx = headers.findIndex(h => h.includes('taxable value'));
        if (posIdx<0||rateIdx<0||txvalIdx<0) { log('⚠ Required columns not found in Amazon'); return count; }
        for (let i=hIdx+1; i<rows.length; i++) {
            const r=rows[i];
            if (!r[txvalIdx]&&r[txvalIdx]!==0) continue;
            const pos=extractCodeFromPlaceOfSupply(r[posIdx]);
            const rt=parseFloat(String(r[rateIdx]).replace('%',''))||0;
            const txval=parseFloat(String(r[txvalIdx]).replace(/,/g,''))||0;
            if (!pos) continue;
            addToB2CS('amazon',pos,rt,txval,homeState); count++;
        }
    } else log('⚠ B2C Small sheet not found in Amazon file');
    log(`✓ Amazon: ${count} rows`);
    return count;
}

async function parseFlipkart(file) {
    log(`Flipkart: ${file.name}`);
    const wb = await readXLSX(file);
    const homeState = selectedClient ? selectedClient.state : '32';
    let count = 0;
    for (const sheetName of wb.SheetNames) {
        const sn = sheetName.toLowerCase();
        if (sn.includes('7')&&sn.includes('a')&&sn.includes('2')) {
            const rows=sheetToRows(wb.Sheets[sheetName]);
            let hIdx=-1;
            for (let i=0;i<rows.length;i++){const r=rows[i].map(c=>String(c).toLowerCase());if(r.some(c=>c.includes('taxable'))&&r.some(c=>c.includes('cgst'))){hIdx=i;break;}}
            if (hIdx<0) continue;
            const headers=rows[hIdx].map(c=>String(c).toLowerCase().trim());
            const aggIdx=headers.findIndex(h=>h.includes('aggregate'));
            const cgstRateIdx=headers.findIndex(h=>h.includes('cgst')&&h.includes('%'));
            if (aggIdx<0||cgstRateIdx<0) continue;
            for (let i=hIdx+1;i<rows.length;i++){
                const r=rows[i];
                if (!r[aggIdx]&&r[aggIdx]!==0) continue;
                const txval=parseFloat(String(r[aggIdx]).replace(/,/g,''))||0;
                const cgstRate=parseFloat(String(r[cgstRateIdx]).replace('%',''))||0;
                if (txval===0) continue;
                addToB2CS('flipkart',homeState,cgstRate*2,txval,homeState); count++;
            }
        }

        // section work
        if (sn.includes('7')&&sn.includes('b')&&sn.includes('2')) {
            const rows=sheetToRows(wb.Sheets[sheetName]);
            let hIdx=-1;
            for (let i=0;i<rows.length;i++){const r=rows[i].map(c=>String(c).toLowerCase());if(r.some(c=>c.includes('aggregate'))&&r.some(c=>c.includes('igst'))){hIdx=i;break;}}
            if (hIdx<0) continue;
            const headers=rows[hIdx].map(c=>String(c).toLowerCase().trim());
            // const posIdx=headers.findIndex(h=>h.includes('place of supply')||h.includes('delivered state'));
            const aggIdx=headers.findIndex(h=>h.includes('aggregate'));
            const igstRateIdx=headers.findIndex(h=>h.includes('igst')&&h.includes('%'));
            const stateIdx=headers.findIndex(h=>h.includes('delivered state')&&!h.includes('code'));
            const stateCodeIdx=headers.findIndex(h=>h.includes('state code')||(h.includes('delivered')&&h.includes('code')));
            if (aggIdx<0||igstRateIdx<0) continue;
            for (let i=hIdx+1;i<rows.length;i++){
                const r=rows[i];
                if (!r[aggIdx]&&r[aggIdx]!==0) continue;
                const txval=parseFloat(String(r[aggIdx]).replace(/,/g,''))||0;
                const igstRate=parseFloat(String(r[igstRateIdx]).replace('%',''))||0;
                if (txval===0) continue;
                let pos=null;
                if (stateCodeIdx>=0&&r[stateCodeIdx]) pos=normState(String(r[stateCodeIdx]).replace('IN-','').trim());
                if (!pos&&stateIdx>=0&&r[stateIdx]) pos=normState(String(r[stateIdx]));
                if (!pos) continue;
                addToB2CS('flipkart',pos,igstRate,txval,homeState); count++;
            }
        }
    }
    log(`✓ Flipkart: ${count} rows`);
    return count;
}

async function parseMeesho(file) {
    log(`Meesho: ${file.name}`);
    const wb = await readXLSX(file);
    const homeState = selectedClient ? selectedClient.state : '32';
    let count = 0;
    const sheet = wb.Sheets[wb.SheetNames.find(n=>n.toLowerCase().includes('sales report'))||wb.SheetNames[0]];
    const rows = sheetToRows(sheet);
    let hIdx=-1;
    for (let i=0;i<rows.length;i++){const r=rows[i].map(c=>String(c).toLowerCase());if(r.some(c=>c.includes('taxable value'))&&r.some(c=>c.includes('igst rate'))){hIdx=i;break;}}
    if (hIdx<0) { log('⚠ Header not found in Meesho file'); return count; }
    const headers=rows[hIdx].map(c=>String(c).toLowerCase().trim());
    const txvalIdx=headers.findIndex(h=>h.includes('taxable value'));
    const igstRateIdx=headers.findIndex(h=>h==='igst rate'||h.includes('igst rate'));
    const cgstRateIdx=headers.findIndex(h=>h==='cgst rate'||h.includes('cgst rate'));
    const stateIdx=headers.findIndex(h=>h.includes("customer's delivery")||h.includes("customer's billing state")||h.includes('billing state'));
    for (let i=hIdx+1;i<rows.length;i++){
        const r=rows[i];
        const txval=parseFloat(String(r[txvalIdx]||0).replace(/,/g,''))||0;
        if (txval===0) continue;
        let pos=null;
        if (stateIdx>=0&&r[stateIdx]) pos=normState(String(r[stateIdx]));
        if (!pos) continue;
        let gstRate=0;
        const igstRate=parseFloat(String(r[igstRateIdx]||0).replace('%',''));
        const cgstRate=parseFloat(String(r[cgstRateIdx]||0).replace('%',''));
        if (!isNaN(igstRate)&&igstRate>0) gstRate=igstRate;
        else if (!isNaN(cgstRate)&&cgstRate>0) gstRate=cgstRate*2;
        addToB2CS('meesho',pos,gstRate,txval,homeState); count++;
    }
    log(`✓ Meesho: ${count} rows`);
    return count;
}

async function parseTCS(file, isReturn) {
    const pKey = isReturn ? 'tcs_return' : 'tcs_sales';
    log(`TCS ${isReturn?'Return':'Sales'}: ${file.name}`);
    let rows=[];
    const homeState = selectedClient ? selectedClient.state : '32';
    let count=0;
    if (file.name.endsWith('.csv')) {
        const text=await readCSV(file);
        const lines=text.split('\n').filter(l=>l.trim());
        rows=lines.map(l=>l.split(',').map(c=>c.replace(/^"|"$/g,'').trim()));
    } else {
        const wb=await readXLSX(file);
        rows=sheetToRows(wb.Sheets[wb.SheetNames[0]]);
    }
    let hIdx=-1;
    for (let i=0;i<rows.length;i++){
        const r=rows[i].map(c=>String(c).toLowerCase());
        if (r.some(c=>c.includes('taxable')||c==='total_taxable_sale_value')&&r.some(c=>c.includes('state')||c==='end_customer_state_new')){hIdx=i;break;}
    }
    if (hIdx<0) { log('⚠ Header not found in TCS file'); return count; }
    const headers=rows[hIdx].map(c=>String(c).toLowerCase().trim());
    const stateIdx=headers.findIndex(h=>h.includes('end_customer_state')||h.includes('state'));
    const txvalIdx=headers.findIndex(h=>h==='total_taxable_sale_value'||(h.includes('taxable')&&h.includes('sale')));
    const rateIdx=headers.findIndex(h=>h==='gst_rate'||(h.includes('gst')&&h.includes('rate')));
    if (stateIdx<0||txvalIdx<0) { log('⚠ Required columns not found in TCS file'); return count; }
    for (let i=hIdx+1;i<rows.length;i++){
        const r=rows[i];
        if (!r[txvalIdx]) continue;
        const txval=parseFloat(String(r[txvalIdx]).replace(/,/g,''))||0;
        if (txval===0) continue;
        const pos=normState(String(r[stateIdx]||'').toUpperCase().trim());
        if (!pos) continue;
        const rt=rateIdx>=0?parseFloat(String(r[rateIdx]).replace('%',''))||0:0;
        addToB2CS(pKey,pos,rt,isReturn?-txval:txval,homeState); count++;
    }
    log(`✓ TCS ${isReturn?'Return':'Sales'}: ${count} rows`);
    return count;
}

async function parseB2CSCSV(file) {
    log(`B2CS CSV: ${file.name}`);
    const text=await readCSV(file);
    const homeState = selectedClient ? selectedClient.state : '32';
    const lines=text.split('\n').filter(l=>l.trim());
    if (lines.length<2) { log('⚠ B2CS CSV appears empty'); return 0; }
    const hdr=lines[0].split(',').map(c=>c.replace(/\r/,'').trim().toLowerCase());
    const posIdx=hdr.findIndex(h=>h.includes('place of supply'));
    const rateIdx=hdr.findIndex(h=>h==='rate');
    const txvalIdx=hdr.findIndex(h=>h.includes('taxable value'));
    if (posIdx<0||rateIdx<0||txvalIdx<0) { log('⚠ Invalid B2CS CSV format'); return 0; }
    let count=0;
    for (let i=1;i<lines.length;i++){
        const parts=lines[i].split(',');
        if (!parts[txvalIdx]) continue;
        const pos=extractCodeFromPlaceOfSupply(parts[posIdx]);
        const rt=parseFloat(parts[rateIdx])||0;
        const txval=parseFloat(String(parts[txvalIdx]).replace(/,/g,''))||0;
        if (!pos) continue;
        addToB2CS('b2cs_csv',pos,rt,txval,homeState); count++;
    }
    log(`✓ B2CS CSV: ${count} rows`);
    return count;
}

// Dispatcher
async function parseFileByType(file, type) {
    try {
        switch (type) {
            case 'amazon':     return await parseAmazon(file);
            case 'flipkart':   return await parseFlipkart(file);
            case 'meesho':     return await parseMeesho(file);
            case 'tcs_sales':  return await parseTCS(file, false);
            case 'tcs_return': return await parseTCS(file, true);
            case 'b2cs_csv':   return await parseB2CSCSV(file);
            default:
                log(`⚠ Unknown type "${type}" for ${file.name} — attempting auto-detect`);
                return await autoDetectAndParse(file);
        }
    } catch(e) {
        log(`✗ Error parsing ${file.name}: ${e.message}`);
        return 0;
    }
}

async function autoDetectAndParse(file) {
    const detected = detectPlatformFromFile(file);
    if (detected !== 'unknown') return await parseFileByType(file, detected);
    log(`⚠ Could not auto-detect platform for ${file.name}`);
    return 0;
}

// ── MAIN COMPUTE ─────────────────────────────────────────────────────
async function computeB2CS() {
    if (!selectedClient) { toast('Please select a client first', 'warn'); return; }
    b2csData = {};
    platformB2CS = {};
    const logBox = document.getElementById('logBox');
    logBox.innerHTML = '';
    logBox.style.display = 'block';
    document.getElementById('progressWrap').style.display = 'block';
    document.getElementById('previewSection').classList.remove('visible');
    setStatus('processing', 'Processing...');
    document.getElementById('btnCompute').disabled = true;
    log(`Client: ${selectedClient.name} | GSTIN: ${selectedClient.gstin || 'N/A'} | Home State: ${selectedClient.state}`);

    try {
        if (currentMode === 'bulk') {
            // BULK MODE
            const total = bulkFiles.length;
            log(`Bulk mode: ${total} file(s) to process`);
            for (let i = 0; i < bulkFiles.length; i++) {
                const item = bulkFiles[i];
                await parseFileByType(item.file, item.detectedType);
                setProgress(((i+1)/total)*90);
            }
        } else {
            // SINGLE MODE
            const files = loadedFiles;
            let step = 0;
            const total = Object.keys(files).length;
            if (files.meesho)    { await parseMeesho(files.meesho);         setProgress(++step/total*90); }
            if (files.amazon)    { await parseAmazon(files.amazon);         setProgress(++step/total*90); }
            if (files.flipkart)  { await parseFlipkart(files.flipkart);     setProgress(++step/total*90); }
            if (files.tcs_sales) { await parseTCS(files.tcs_sales, false);  setProgress(++step/total*90); }
            if (files.tcs_return){ await parseTCS(files.tcs_return, true);  setProgress(++step/total*90); }
            if (files.b2cs_csv)  { await parseB2CSCSV(files.b2cs_csv);      setProgress(++step/total*90); }
        }

        setProgress(95);
        log('Building B2CS report...');
        renderPreview();
        setProgress(100);
        setStatus('done', 'Done ✓');
        document.getElementById('previewSection').classList.add('visible');
        document.getElementById('previewSection').scrollIntoView({behavior:'smooth',block:'start'});
        toast('Computation complete! Download CSV or JSON below.', 'success');
    } catch(err) {
        log('✗ Error: ' + err.message);
        setStatus('error', 'Error');
        toast('Error: ' + err.message, 'error');
        console.error(err);
    }
    document.getElementById('btnCompute').disabled = false;
}

// ── PLATFORM TABLE HTML ───────────────────────────────────────────────
function buildPlatformTableHTML(platform) {
    const data = platformB2CS[platform];
    if (!data || Object.keys(data).length === 0) return '';
    const meta = PLATFORM_META[platform];
    const rows = Object.values(data).sort((a,b) => {
        if (a.sply_ty!==b.sply_ty) return a.sply_ty>b.sply_ty?1:-1;
        return a.pos.localeCompare(b.pos)||a.rt-b.rt;
    });
    const totTx=rows.reduce((s,r)=>s+r.txval,0);
    const totTax=rows.reduce((s,r)=>s+r.iamt+r.camt+r.samt,0);
    const totI=rows.reduce((s,r)=>s+r.iamt,0);
    const totC=rows.reduce((s,r)=>s+r.camt,0);
    const totS=rows.reduce((s,r)=>s+r.samt,0);
    let tbodyHtml='';
    for (const r of rows) {
        const stateName=POS_TO_NAME[r.pos]||r.pos;
        const typeBadge=r.sply_ty==='INTRA'?'<span class="badge-intra">INTRA</span>':'<span class="badge-inter">INTER</span>';
        tbodyHtml+=`<tr>
            <td>${typeBadge}</td>
            <td>${stateName}</td>
            <td><span class="rate-pill">${r.rt}%</span></td>
            <td>${fmt(r.txval)}</td>
            <td>${r.sply_ty==='INTER'?fmt(r.iamt):'—'}</td>
            <td>${r.sply_ty==='INTRA'?fmt(r.camt):'—'}</td>
            <td>${r.sply_ty==='INTRA'?fmt(r.samt):'—'}</td>
            <td><strong>${fmt(r.iamt+r.camt+r.samt)}</strong></td>
        </tr>`;
    }
    return `<div class="platform-report-block">
        <div class="platform-report-header">
            <div class="platform-icon-wrap" style="background:${meta.bg};color:${meta.color}"><i class="bi ${meta.icon}"></i></div>
            <div>
                <div class="platform-report-title">${meta.label}</div>
                <div class="platform-report-meta">${rows.length} row${rows.length!==1?'s':''} · ${new Set(rows.map(r=>r.pos)).size} state${new Set(rows.map(r=>r.pos)).size!==1?'s':''}</div>
            </div>
            <span class="platform-status-pill ${totTx>=0?'psp-ok':'psp-warn'}">Taxable: ₹${Math.abs(totTx).toLocaleString('en-IN',{maximumFractionDigits:0})}${totTx<0?' CR':''}</span>
        </div>
        <div class="platform-table-wrap">
            <table class="gst-table">
                <thead><tr>
                    <th style="text-align:left">Type</th><th style="text-align:left">State</th><th style="text-align:left">GST Rate</th>
                    <th>Taxable Value (₹)</th><th>IGST (₹)</th><th>CGST (₹)</th><th>SGST (₹)</th><th>Total Tax (₹)</th>
                </tr></thead>
                <tbody>${tbodyHtml}</tbody>
                <tfoot>
                    <tr class="tfoot-row">
                        <td colspan="3" style="text-align:left"><strong>${meta.label} Total</strong></td>
                        <td>${fmt(totTx)}</td><td>${fmt(totI)}</td><td>${fmt(totC)}</td><td>${fmt(totS)}</td>
                        <td><strong>${fmt(totTax)}</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>`;
}

// ── RENDER PREVIEW ────────────────────────────────────────────────────
function renderPreview() {
    const gstin = selectedClient ? (selectedClient.gstin || '—') : '—';
    const clientName = selectedClient ? selectedClient.name : '—';
    const mm = document.getElementById('inputMonth').value;
    const yyyy = document.getElementById('inputYear').value;
    const allRows = Object.values(b2csData).sort((a,b) => {
        if (a.sply_ty!==b.sply_ty) return a.sply_ty>b.sply_ty?1:-1;
        return a.pos.localeCompare(b.pos)||a.rt-b.rt;
    });
    const totalTxval=allRows.reduce((s,r)=>s+r.txval,0);
    const totalTax=allRows.reduce((s,r)=>s+r.iamt+r.camt+r.samt,0);
    const stateCount=new Set(allRows.map(r=>r.pos)).size;
    const platformCount=Object.keys(platformB2CS).length;

    document.getElementById('previewTitle').textContent=`B2CS Summary — ${clientName}`;
    document.getElementById('previewSubtitle').textContent=`GSTIN: ${gstin} | Filing Period: ${mm}/${yyyy} | Generated by EcomgstPro`;
    document.getElementById('jsonFileName').textContent=`gstr1_b2cs_${gstin}_${mm}${yyyy}.json`;

    document.getElementById('summaryChips').innerHTML=`
        <div class="chip chip-purple"><i class="bi bi-person-fill"></i> ${clientName}</div>
        <div class="chip chip-teal"><i class="bi bi-layers-fill"></i> ${platformCount} Platform${platformCount!==1?'s':''}</div>
        <div class="chip chip-navy"><i class="bi bi-geo-alt-fill"></i> ${stateCount} States</div>
        <div class="chip chip-navy"><i class="bi bi-list-ul"></i> ${allRows.length} Rows</div>
        <div class="chip chip-sky"><i class="bi bi-cash-stack"></i> ₹${totalTxval.toLocaleString('en-IN',{maximumFractionDigits:0})} Taxable</div>
        <div class="chip chip-sky"><i class="bi bi-percent"></i> ₹${totalTax.toLocaleString('en-IN',{maximumFractionDigits:0})} Tax</div>
    `;

    // Platform sections in order
    let platformHtml='';
    for (const platform of PLATFORM_ORDER) {
        if (platformB2CS[platform]&&Object.keys(platformB2CS[platform]).length>0) {
            platformHtml+=buildPlatformTableHTML(platform);
        }
    }
    document.getElementById('platformReports').innerHTML=platformHtml;

    // Grand total table
    let tbodyHtml='',totTx=0,totI=0,totC=0,totS=0;
    for (const r of allRows) {
        const stateName=POS_TO_NAME[r.pos]||r.pos;
        const typeBadge=r.sply_ty==='INTRA'?'<span class="badge-intra">INTRA</span>':'<span class="badge-inter">INTER</span>';
        const tax=r.iamt+r.camt+r.samt;
        totTx+=r.txval; totI+=r.iamt; totC+=r.camt; totS+=r.samt;
        tbodyHtml+=`<tr>
            <td>${typeBadge}</td>
            <td>${stateName}</td>
            <td><span class="rate-pill">${r.rt}%</span></td>
            <td>${fmt(r.txval)}</td>
            <td>${r.sply_ty==='INTER'?fmt(r.iamt):'—'}</td>
            <td>${r.sply_ty==='INTRA'?fmt(r.camt):'—'}</td>
            <td>${r.sply_ty==='INTRA'?fmt(r.samt):'—'}</td>
            <td><strong>${fmt(tax)}</strong></td>
        </tr>`;
    }
    document.getElementById('previewTableBody').innerHTML=tbodyHtml||'<tr><td colspan="8" style="text-align:center;padding:20px;color:#94a3b8">No data found</td></tr>';
    document.getElementById('totTxval').innerHTML=fmt(totTx);
    document.getElementById('totIamt').innerHTML=fmt(totI);
    document.getElementById('totCamt').innerHTML=fmt(totC);
    document.getElementById('totSamt').innerHTML=fmt(totS);
    document.getElementById('totTax').innerHTML=`<strong>${fmt(totI+totC+totS)}</strong>`;
    document.getElementById('jsonPreview').textContent=JSON.stringify(buildJSON(),null,2);
}

// ── JSON BUILDER ──────────────────────────────────────────────────────
function buildJSON() {
    const gstin=selectedClient?(selectedClient.gstin||'GSTIN_HERE'):'GSTIN_HERE';
    const mm=document.getElementById('inputMonth').value;
    const yyyy=document.getElementById('inputYear').value;
    const fp=mm+yyyy;
    const b2cs=Object.values(b2csData).filter(r=>r.txval!==0).map(r => {
        if (r.sply_ty==='INTRA') return {sply_ty:'INTRA',rt:r.rt,typ:'OE',pos:r.pos,txval:round2(r.txval),camt:round2(r.camt),samt:round2(r.samt),csamt:0};
        else return {sply_ty:'INTER',rt:r.rt,typ:'OE',pos:r.pos,txval:round2(r.txval),iamt:round2(r.iamt),csamt:0};
    });
    return {gstin,fp,version:'GST3.2.4',hash:'hash',b2cs};
}

// ── DOWNLOADS ─────────────────────────────────────────────────────────
function downloadJSON() {
    const gstin=selectedClient?(selectedClient.gstin||'GSTIN'):'GSTIN';
    const mm=document.getElementById('inputMonth').value;
    const yyyy=document.getElementById('inputYear').value;
    const blob=new Blob([JSON.stringify(buildJSON())],{type:'application/json'});
    const a=document.createElement('a');
    a.href=URL.createObjectURL(blob);
    a.download=`gstr1_b2cs_${gstin}_${mm}${yyyy}.json`;
    a.click();
    toast('JSON downloaded!', 'success');
}
function downloadCSV() {
    const gstin=selectedClient?(selectedClient.gstin||'GSTIN'):'GSTIN';
    const mm=document.getElementById('inputMonth').value;
    const yyyy=document.getElementById('inputYear').value;
    let csv='Type,Place Of Supply,Rate,Applicable % of Tax Rate,Taxable Value,Cess Amount,E-Commerce GSTIN\r\n';
    for (const r of Object.values(b2csData)) {
        csv+=`OE,${POS_TO_NAME[r.pos]||r.pos},${r.rt},,${round2(r.txval)},,\r\n`;
    }
    const blob=new Blob([csv],{type:'text/csv'});
    const a=document.createElement('a');
    a.href=URL.createObjectURL(blob);
    a.download=`b2cs_${gstin}_${mm}${yyyy}.csv`;
    a.click();
    toast('CSV downloaded!', 'success');
}
function copyJSON() {
    navigator.clipboard.writeText(document.getElementById('jsonPreview').textContent).then(() => {
        const btn = event.target.closest('button');
        btn.innerHTML='<i class="bi bi-check-circle-fill"></i> Copied!';
        setTimeout(()=>btn.innerHTML='<i class="bi bi-clipboard"></i> Copy',2000);
        toast('JSON copied to clipboard', 'success');
    });
}

// ── INIT ──────────────────────────────────────────────────────────────
renderClientList();
updateComputeBtn();
updateComputeBarDesc();
</script>

@include('layouts.footer')
@endsection
