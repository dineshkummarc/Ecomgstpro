@extends('layouts.gstr-main')

@section('title', 'Upload E-Commerce Data')

@section('styles')
<style>
/* ── Reset & Base ── */
*, *::before, *::after { box-sizing: border-box; }

/* ── Page Layout ── */
.up-page-header { margin-bottom: 20px; }
.up-page-title  { font-family: 'Syne', sans-serif; font-weight: 800; font-size: 22px; color: var(--navy); display: flex; align-items: center; gap: 9px; margin: 0 0 3px; }
.up-page-sub    { font-size: 13px; color: #6b7a99; margin: 0; }

/* ── Client Banner ── */
.up-client-banner {
    background: #fff; border: 1px solid #e8edf5; border-radius: 14px;
    padding: 13px 18px; display: flex; align-items: center; gap: 13px;
    margin-bottom: 16px; flex-wrap: wrap;
}
.up-client-avatar {
    width: 42px; height: 42px; border-radius: 10px;
    background: linear-gradient(135deg, var(--teal), var(--sky));
    display: flex; align-items: center; justify-content: center;
    font-family: 'Syne', sans-serif; font-weight: 800; font-size: 15px; color: #fff; flex-shrink: 0;
}
.up-client-name { font-family: 'Syne', sans-serif; font-weight: 700; font-size: 14px; color: var(--navy); }
.up-client-meta { font-size: 11px; color: #6b7a99; margin-top: 2px; font-family: 'Courier New', monospace; }
.up-btn-outline { display: inline-flex; align-items: center; gap: 6px; padding: 7px 13px; border-radius: 9px; border: 1.5px solid #c5d0e6; background: #fff; color: #4a5568; font-size: 11px; font-weight: 700; cursor: pointer; transition: all .2s; margin-left: auto; }
.up-btn-outline:hover { border-color: var(--teal); color: var(--teal); }

/* ── Period Bar ── */
.up-period-bar {
    background: #fff; border: 1px solid #e8edf5; border-radius: 12px;
    padding: 11px 18px; margin-bottom: 16px;
    display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
}
.up-period-label { font-family: 'Syne', sans-serif; font-weight: 700; font-size: 12px; color: var(--navy); display: flex; align-items: center; gap: 5px; }
.up-period-label i { color: var(--teal); }
.up-period-select { padding: 6px 10px; border: 1.5px solid #e8edf5; border-radius: 8px; font-size: 12px; color: var(--navy); background: #fff; outline: none; cursor: pointer; }
.up-period-select:focus { border-color: var(--teal); }
.up-period-chip { display: inline-flex; align-items: center; gap: 5px; padding: 5px 11px; background: rgba(0,201,141,.1); border: 1px solid rgba(0,201,141,.25); border-radius: 8px; font-size: 11px; font-weight: 700; color: #007050; font-family: 'Courier New', monospace; }
.up-status-badge { display: inline-flex; align-items: center; gap: 5px; padding: 5px 11px; border-radius: 20px; font-size: 11px; font-weight: 700; background: #f1f5fb; border: 1px solid #e8edf5; color: #94a3b8; margin-left: auto; }
.up-status-badge.ready { background: rgba(0,201,141,.1); border-color: rgba(0,201,141,.3); color: #007050; }
.up-status-badge.processing { background: rgba(0,170,255,.1); border-color: rgba(0,170,255,.3); color: #005fa0; }

/* ── NEW: Quarter Month Tabs ── */
.up-qtr-tabs { display: none; align-items: center; gap: 6px; width: 100%; margin-top: 8px; padding-top: 8px; border-top: 1px dashed #e8edf5; }
.up-qtr-tabs.show { display: flex; flex-wrap: wrap; }
.up-qtr-tab-label { font-size: 11px; color: #6b7a99; font-weight: 700; margin-right: 4px; }
.up-qtr-tab {
    padding: 6px 13px; border-radius: 20px; font-size: 11px; font-weight: 700;
    border: 1.5px solid #e8edf5; background: #fff; color: #6b7a99; cursor: pointer;
    display: inline-flex; align-items: center; gap: 5px; transition: all .15s;
}
.up-qtr-tab:hover { border-color: var(--teal); color: var(--teal); }
.up-qtr-tab.active { background: var(--navy); border-color: var(--navy); color: #fff; }
.up-qtr-tab .dot { width: 6px; height: 6px; border-radius: 50%; background: #cbd5e1; }
.up-qtr-tab.has-data .dot { background: var(--teal); }
.up-qtr-tab.active .dot { background: #fff; }

/* ── Duplicate Warning ── */
.up-dup-warn { display: none; background: rgba(234,88,12,.07); border: 1.5px solid rgba(234,88,12,.3); border-radius: 10px; padding: 10px 14px; margin-top: 8px; font-size: 12px; color: #7c2d12; width: 100%; }
.up-dup-warn.show { display: flex; gap: 9px; align-items: flex-start; }
.up-dup-warn i { color: #ea580c; flex-shrink: 0; margin-top: 2px; }
.up-dw-actions { margin-top: 7px; display: flex; gap: 7px; }
.up-dw-btn { padding: 4px 11px; border-radius: 6px; font-size: 11px; font-weight: 700; cursor: pointer; border: 1.5px solid; background: #fff; }
.up-dw-replace  { border-color: #ea580c; color: #7c2d12; }
.up-dw-keepboth { border-color: var(--teal); color: #007050; }

/* ── Existing Period Pills ── */
.up-ep-pills { display: flex; flex-wrap: wrap; gap: 5px; }
.up-ep-pill  { font-size: 10px; padding: 2px 8px; border-radius: 20px; background: rgba(0,201,141,.08); border: 1px solid rgba(0,201,141,.2); color: #007050; font-family: 'Courier New', monospace; }

/* ── Upload Card ── */
.up-card { background: #fff; border: 1px solid #e8edf5; border-radius: 16px; overflow: hidden; margin-bottom: 16px; }
.up-card-header {
    padding: 12px 18px; border-bottom: 1px solid #f0f4fa;
    display: flex; align-items: center; justify-content: space-between;
    background: #fafcff;
}
.up-card-title { font-family: 'Syne', sans-serif; font-weight: 700; font-size: 13px; color: var(--navy); display: flex; align-items: center; gap: 7px; }
.up-card-title i { color: var(--teal); }
.up-card-hint { font-size: 11px; color: #94a3b8; }

/* ── Platform Row ── */
.up-plat-row {
    display: grid;
    grid-template-columns: 40px minmax(130px,1fr) 210px 120px;
    align-items: start; column-gap: 14px; padding: 11px 18px;
    border-bottom: 1px solid #f0f4fa; transition: background .15s;
    min-height: 58px;
}
.up-plat-row:last-child { border-bottom: none; }
.up-plat-row:hover { background: #fafcff; }
.up-plat-row.has-file  { background: rgba(0,201,141,.03); }
.up-plat-row.has-error { background: rgba(239,68,68,.03); }

.up-plat-icon { width: 36px; height: 36px; border-radius: 9px; display: flex; align-items: center; justify-content: center; font-size: 17px; flex-shrink: 0; overflow: hidden; margin-top: 2px; }
.up-plat-icon img { height: 18px; width: auto; object-fit: contain; display: block; }
.up-plat-name { font-family: 'Syne', sans-serif; font-weight: 700; font-size: 12px; color: var(--navy); }
.up-plat-desc { font-size: 10px; color: #94a3b8; margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

/* ── Upload Zone (col 3) ── */
.up-zone { display: flex; flex-direction: column; gap: 5px; padding-top: 2px; }

/* ── Upload Button ── */
.up-btn {
    display: flex; align-items: center; justify-content: center; gap: 5px;
    padding: 7px 12px; border-radius: 9px; font-size: 11px; font-weight: 700; cursor: pointer;
    border: 1.5px dashed #c5d0e6; background: #fff; color: #4a5568;
    transition: all .2s; white-space: nowrap; position: relative; overflow: hidden;
    user-select: none; width: 100%;
}
.up-btn:hover  { border-color: var(--teal); color: var(--teal); background: rgba(0,201,141,.04); }
.up-btn.loaded { border-style: solid; border-color: var(--teal); background: rgba(0,201,141,.08); color: #007050; }
.up-btn.error  { border-style: solid; border-color: #ef4444; background: rgba(239,68,68,.07); color: #b91c1c; }
.up-btn input[type="file"] { position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%; font-size: 0; }

/* ── Delete Button (was Re-upload) ── */
.up-reupload-btn {
    display: none; align-items: center; justify-content: center; gap: 5px;
    padding: 4px 10px; border-radius: 7px; font-size: 10px; font-weight: 700; cursor: pointer;
    border: 1.5px solid #c5d0e6; background: #fff; color: #6b7a99;
    transition: all .2s; white-space: nowrap; position: relative; overflow: hidden; width: 100%;
}
.up-reupload-btn:hover { border-color: var(--sky); color: var(--sky); }
.up-reupload-btn.show { display: flex; }
.up-reupload-btn input[type="file"] { position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%; font-size: 0; }
/* ← NEW: ab yeh button "Delete file" hai (pehle "Re-upload" tha) — red hover to signal a destructive action */
.up-reupload-btn.up-delete-btn:hover { border-color: #ef4444; color: #b91c1c; }

/* ── File Status ── */
.up-file-status { font-size: 10px; display: none; line-height: 1.4; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.up-file-status.show { display: block; }
.up-file-status.ok  { color: #007050; }
.up-file-status.err { color: #b91c1c; }

/* ── Amount Column ── */
.up-amt-col { text-align: right; padding-top: 3px; }
.up-amt-label { font-size: 9px; color: #94a3b8; text-transform: uppercase; letter-spacing: .5px; }
.up-amt-val   { font-family: 'Courier New', monospace; font-size: 13px; font-weight: 700; color: #b0bec5; transition: color .3s; }
.up-amt-val.active { color: var(--teal); }
.up-tcs-badge { display: none; font-size: 9px; background: rgba(0,170,255,.12); color: #005fa0; border-radius: 4px; padding: 1px 5px; font-weight: 700; margin-left: 3px; vertical-align: middle; }

/* ── HSN Panel ── */
.up-hsn-panel { display: none; border-top: 1.5px solid #e8edf5; background: #f7faff; padding: 16px 20px; }
.up-hsn-panel.show { display: block; }
.up-hsn-head { font-family: 'Syne', sans-serif; font-weight: 700; font-size: 12px; color: var(--navy); margin-bottom: 4px; display: flex; align-items: center; gap: 7px; }
.up-hsn-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px,1fr)); gap: 9px; margin-top: 10px; }
.up-hsn-item { background: #fff; border: 1.5px solid #e8edf5; border-radius: 10px; padding: 9px 11px; }
.up-hsn-meta  { font-size: 10px; color: #6b7a99; margin-bottom: 3px; }
.up-hsn-label { font-size: 11px; font-weight: 700; color: var(--navy); margin-bottom: 6px; }
.up-hsn-input { width: 100%; padding: 5px 8px; border: 1.5px solid #e0e7ef; border-radius: 6px; font-size: 11px; font-family: 'Courier New', monospace; outline: none; transition: border-color .2s; box-sizing: border-box; }
.up-hsn-input:focus { border-color: var(--teal); }
.up-btn-apply-hsn { margin-top: 10px; padding: 7px 14px; background: var(--teal); color: #fff; border: none; border-radius: 8px; font-weight: 700; font-size: 11px; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; }
.up-btn-hsn { display: inline-flex; align-items: center; gap: 5px; padding: 6px 12px; border-radius: 8px; font-size: 11px; font-weight: 700; cursor: pointer; border: 1.5px solid #c5d0e6; background: #fff; color: #4a5568; transition: all .2s; }
.up-btn-hsn:hover { border-color: var(--teal); color: var(--teal); }
.up-btn-hsn.active { border-color: var(--teal); background: rgba(0,201,141,.08); color: #007050; }

/* ── Modal ── */
.up-modal-overlay { display: none; position: fixed; inset: 0; background: rgba(10,20,40,.5); z-index: 9000; align-items: center; justify-content: center; }
.up-modal-overlay.show { display: flex; }
.up-modal-box { background: #fff; border-radius: 18px; padding: 24px; width: 460px; max-width: 95vw; box-shadow: 0 24px 60px rgba(0,0,0,.18); max-height: 90vh; overflow-y: auto; }
.up-modal-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; }
.up-modal-title { font-family: 'Syne', sans-serif; font-weight: 800; font-size: 17px; color: var(--navy); }
.up-modal-close { width: 32px; height: 32px; border-radius: 50%; border: 1.5px solid #e8edf5; background: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center; color: #6b7a99; font-size: 17px; }
.up-modal-close:hover { background: #fee2e2; border-color: #ef4444; color: #ef4444; }
.up-modal-gstin-row { display: flex; align-items: center; gap: 9px; margin-bottom: 14px; padding: 10px 12px; background: rgba(0,201,141,.05); border: 1.5px solid rgba(0,201,141,.2); border-radius: 10px; flex-wrap: wrap; }
.up-modal-gstin-label { font-size: 11px; color: #6b7a99; }
.up-modal-gstin-val   { font-family: 'Courier New', monospace; font-size: 12px; font-weight: 700; color: #007050; flex: 1; }
.up-btn-gstin-edit    { background: rgba(0,201,141,.1); border: 1px solid rgba(0,201,141,.3); border-radius: 6px; padding: 3px 9px; font-size: 11px; color: #007050; cursor: pointer; font-weight: 700; }
.up-gstin-edit-wrap   { display: none; margin-bottom: 12px; }
.up-gstin-edit-input  { width: 100%; padding: 7px 10px; border: 1.5px solid #e0e7ef; border-radius: 8px; font-size: 12px; font-family: 'Courier New', monospace; outline: none; box-sizing: border-box; }
.up-modal-field { margin-bottom: 14px; }
.up-modal-field-label { font-family: 'Syne', sans-serif; font-weight: 700; font-size: 12px; color: var(--navy); margin-bottom: 4px; display: flex; align-items: center; gap: 6px; }
.up-tag-req { font-size: 10px; background: rgba(239,68,68,.1); color: #b91c1c; padding: 1px 6px; border-radius: 5px; font-weight: 700; }
.up-tag-opt { font-size: 10px; background: rgba(0,201,141,.1); color: #007050; padding: 1px 6px; border-radius: 5px; font-weight: 700; }
.up-modal-field-desc { font-size: 10px; color: #94a3b8; margin-bottom: 7px; }
.up-mfs-slot { display: flex; align-items: center; gap: 9px; padding: 10px 13px; border: 1.5px dashed #c5d0e6; border-radius: 10px; cursor: pointer; transition: all .2s; background: #fafcff; position: relative; }
.up-mfs-slot:hover  { border-color: var(--teal); background: rgba(0,201,141,.04); }
.up-mfs-slot.loaded { border-style: solid; border-color: var(--teal); background: rgba(0,201,141,.06); }
.up-mfs-slot.error  { border-style: solid; border-color: #ef4444; background: rgba(239,68,68,.04); }
.up-mfs-slot input[type="file"] { position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%; font-size: 0; }
.up-mfs-icon { width: 34px; height: 34px; border-radius: 7px; background: #f0f4fa; display: flex; align-items: center; justify-content: center; font-size: 16px; color: #6b7a99; flex-shrink: 0; }
.up-mfs-slot.loaded .up-mfs-icon { background: rgba(0,201,141,.1); color: #007050; }
.up-mfs-slot.error  .up-mfs-icon { background: rgba(239,68,68,.1); color: #ef4444; }
.up-mfs-name { font-size: 11px; font-weight: 600; color: var(--navy); }
.up-mfs-sub  { font-size: 10px; color: #94a3b8; margin-top: 1px; }
.up-mfs-stat { font-size: 10px; margin-top: 2px; }
.up-mfs-stat.ok { color: #007050; } .up-mfs-stat.err { color: #b91c1c; }
.up-mfs-slot.disabled { opacity: .4; pointer-events: none; }
.up-mfs-note { font-size: 10px; color: #ea580c; margin: -8px 0 10px; display: none; }
.up-mfs-note.show { display: block; }
.up-modal-actions { display: flex; gap: 9px; margin-top: 20px; }
.up-btn-modal-cancel { flex: 1; padding: 10px; border: 1.5px solid #e8edf5; border-radius: 10px; font-size: 12px; font-weight: 700; color: #6b7a99; background: #fff; cursor: pointer; }
.up-btn-modal-submit { flex: 2; padding: 10px; border: none; border-radius: 10px; font-size: 12px; font-weight: 700; color: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; }
.up-btn-modal-submit:disabled { opacity: .4; cursor: not-allowed; }

/* ── Compute Bar ── */
.up-compute-bar { background: linear-gradient(135deg, var(--navy), var(--deep)); border-radius: 14px; padding: 18px 22px; display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
.up-compute-bar h3 { font-family: 'Syne', sans-serif; font-weight: 800; font-size: 14px; color: #fff; margin: 0; }
.up-compute-bar p  { font-size: 11px; color: rgba(255,255,255,.5); margin: 3px 0 0; }
.up-btn-sky { font-weight: 700; font-size: 12px; color: #fff; background: linear-gradient(135deg, var(--sky), #0088dd); border: none; border-radius: 10px; padding: 10px 20px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: all .2s; }
.up-btn-sky:hover:not(:disabled) { transform: translateY(-1px); }
.up-btn-sky:disabled { opacity: .4; cursor: not-allowed; }
.up-progress-wrap { background: #e8edf5; border-radius: 8px; height: 5px; overflow: hidden; margin-top: 12px; display: none; }
.up-progress-fill { height: 100%; background: linear-gradient(90deg, var(--teal), var(--sky)); width: 0%; transition: width .4s; border-radius: 8px; }
.up-step-log { margin-top: 7px; font-size: 11px; color: var(--muted); min-height: 15px; text-align: center; }

/* ── Global Upload Overlay with Balls ── */
.up-overlay {
    display: none; position: fixed; inset: 0; z-index: 99999;
    background: rgba(8, 14, 30, 0.75);
    backdrop-filter: blur(5px);
    -webkit-backdrop-filter: blur(5px);
    align-items: center; justify-content: center; flex-direction: column; gap: 22px;
}
.up-overlay.show { display: flex; }
.up-balls-wrap { position: relative; width: 90px; height: 90px; }
.up-ball {
    position: absolute; width: 13px; height: 13px; border-radius: 50%;
    animation: upBallPulse 1.4s ease-in-out infinite;
}
.up-ball:nth-child(1) { top: 0; left: 50%; margin-left: -6.5px; background: #00c98d; animation-delay: 0s; }
.up-ball:nth-child(2) { top: 13%; right: 13%; background: #00aaff; animation-delay: 0.175s; }
.up-ball:nth-child(3) { top: 50%; right: 0; margin-top: -6.5px; background: #a855f7; animation-delay: 0.35s; }
.up-ball:nth-child(4) { bottom: 13%; right: 13%; background: #f59e0b; animation-delay: 0.525s; }
.up-ball:nth-child(5) { bottom: 0; left: 50%; margin-left: -6.5px; background: #ef4444; animation-delay: 0.7s; }
.up-ball:nth-child(6) { bottom: 13%; left: 13%; background: #ec4899; animation-delay: 0.875s; }
.up-ball:nth-child(7) { top: 50%; left: 0; margin-top: -6.5px; background: #14b8a6; animation-delay: 1.05s; }
.up-ball:nth-child(8) { top: 13%; left: 13%; background: #84cc16; animation-delay: 1.225s; }
@keyframes upBallPulse {
    0%, 80%, 100% { transform: scale(0.5); opacity: 0.35; }
    40%            { transform: scale(1.3); opacity: 1; }
}
.up-overlay-title { font-family: 'Syne', sans-serif; font-weight: 700; font-size: 16px; color: rgba(255,255,255,.95); letter-spacing: .3px; }
.up-overlay-sub   { font-size: 12px; color: rgba(255,255,255,.45); margin-top: -12px; }

/* ── Spin helper ── */
.up-spin { display: inline-block; width: 11px; height: 11px; border: 2px solid rgba(255,255,255,.3); border-top-color: currentColor; border-radius: 50%; animation: upSpin .7s linear infinite; }
@keyframes upSpin { to { transform: rotate(360deg); } }

/* ── Toast ── */
.up-toast-wrap { position: fixed; bottom: 22px; right: 22px; z-index: 999999; display: flex; flex-direction: column; gap: 8px; pointer-events: none; }
.up-toast { padding: 11px 18px; border-radius: 11px; font-size: 12px; font-weight: 600; color: #fff; animation: upToastIn .3s ease; max-width: 340px; pointer-events: all; }
.up-toast.success { background: #00c98d; }
.up-toast.error   { background: #ef4444; }
@keyframes upToastIn { from { transform: translateY(16px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

/* ── Alert banners ── */
.up-alert { display: flex; gap: 8px; padding: 10px 14px; border-radius: 10px; font-size: 12px; margin-bottom: 12px; }
.up-alert-success { background: rgba(0,201,141,.08); border: 1px solid rgba(0,201,141,.2); color: #005a3c; }
.up-alert-error   { background: rgba(239,68,68,.08); border: 1px solid rgba(239,68,68,.2); color: #7f1d1d; }
.up-alert i { flex-shrink: 0; margin-top: 1px; }

/* ── NEW: Existing-period lock badge & row lock state ── */
.up-existing-badge { display:none; align-items:flex-start; gap:8px; background:rgba(234,88,12,.08); border:1.5px solid rgba(234,88,12,.3); border-radius:9px; padding:7px 10px; margin-top:6px; font-size:11px; color:#7c2d12; }
.up-existing-badge.show { display:flex; }
.up-existing-badge .ex-info { flex:1; line-height:1.4; }
.up-existing-badge .ex-del { flex-shrink:0; border:1.5px solid #ef4444; background:#fff; color:#b91c1c; border-radius:7px; padding:4px 9px; font-size:10px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:4px; }
.up-existing-badge .ex-del:hover { background:#fee2e2; }
.up-row-locked .up-btn, .up-row-locked .up-reupload-btn, .up-row-locked input[type="file"] { pointer-events:none !important; opacity:.4 !important; }
.up-row-locked { background: rgba(234,88,12,.02); }
</style>
@endsection

@section('content')

{{-- ── Page Header ── --}}
<div class="up-page-header">
    <h1 class="up-page-title"><i class="bi bi-cloud-upload-fill"></i> Upload E-Commerce Data</h1>
    <p class="up-page-sub">Upload your platform files — format is auto-detected</p>
</div>

{{-- ── Flash Messages ── --}}
@if(session('success'))
<div class="up-alert up-alert-success">
    <i class="bi bi-check-circle-fill" style="color:var(--teal)"></i>
    <div>{{ session('success') }}</div>
</div>
@endif
@if(session('error'))
<div class="up-alert up-alert-error">
    <i class="bi bi-exclamation-circle-fill" style="color:#ef4444"></i>
    <div>{{ session('error') }}</div>
</div>
@endif

{{-- ── Client Banner ── --}}
<div class="up-client-banner">
    <div class="up-client-avatar">
        @php
            $cn = session('gstr_selected_client.name') ?? session('selected_client.name', '--');
            $p  = explode(' ', $cn);
            echo strtoupper(substr($p[0] ?? '-', 0, 1)) . strtoupper(substr($p[1] ?? '', 0, 1));
        @endphp
    </div>
    <div>
        <div class="up-client-name">{{ session('gstr_selected_client.name') ?? session('selected_client.name', '--') }}</div>
        <div class="up-client-meta">
            GSTIN: <strong>{{ session('gstr_selected_client.gstin') ?? session('selected_client.gstin', '--') }}</strong>
            &nbsp;|&nbsp; State: {{ session('gstr_selected_client.state_code') ?? session('selected_client.state_code', '--') }}
            {{-- ← NEW: Filing frequency badge --}}
            &nbsp;|&nbsp; Filing: <strong>{{ ucfirst($filingFrequency ?? 'monthly') }}</strong>
        </div>
    </div>
    <a href="{{ route('gstr.clients.index') }}" class="up-btn-outline">
        <i class="bi bi-arrow-repeat"></i> Change Client
    </a>
</div>

{{-- ── Period Bar ── --}}
<div class="up-period-bar">

    {{-- ← NEW: filingFrequency == monthly hone par purana Month/Year selector --}}
    <div id="upMonthlyControls" style="display:{{ ($filingFrequency ?? 'monthly') === 'quarterly' ? 'none' : 'flex' }};align-items:center;gap:10px;flex-wrap:wrap">
        <div class="up-period-label"><i class="bi bi-calendar3"></i> Period</div>
        <select class="up-period-select" id="periodMonth" onchange="upUpdatePeriod()">
            @foreach(['01'=>'January','02'=>'February','03'=>'March','04'=>'April','05'=>'May','06'=>'June','07'=>'July','08'=>'August','09'=>'September','10'=>'October','11'=>'November','12'=>'December'] as $v=>$l)
            <option value="{{ $v }}">{{ $l }}</option>
            @endforeach
        </select>
        <select class="up-period-select" id="periodYear" onchange="upUpdatePeriod()">
            @for($y = date('Y'); $y >= 2020; $y--)
            <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
        </select>
    </div>

    {{-- ← NEW: filingFrequency == quarterly hone par Quarter+FY selector --}}
    <div id="upQuarterlyControls" style="display:{{ ($filingFrequency ?? 'monthly') === 'quarterly' ? 'flex' : 'none' }};align-items:center;gap:10px;flex-wrap:wrap">
        <div class="up-period-label"><i class="bi bi-calendar3-range"></i> Quarter</div>
        <select class="up-period-select" id="qtrQuarter" onchange="upUpdatePeriod()">
            <option value="1">Q1 (Apr-Jun)</option>
            <option value="2">Q2 (Jul-Sep)</option>
            <option value="3">Q3 (Oct-Dec)</option>
            <option value="4">Q4 (Jan-Mar)</option>
        </select>
        <select class="up-period-select" id="qtrFyStart" onchange="upUpdatePeriod()">
            @for($y = date('Y'); $y >= 2020; $y--)
            <option value="{{ $y }}">{{ $y }}-{{ substr((string)($y+1),2,2) }}</option>
            @endfor
        </select>
    </div>

    <div class="up-period-chip"><i class="bi bi-calendar-check-fill"></i> <span id="upPeriodText">--/----</span></div>

    @if(!empty($existingPeriods))
    <div class="up-ep-pills">
        @foreach($existingPeriods as $ep)
        <span class="up-ep-pill"><i class="bi bi-check2"></i> {{ $ep }}</span>
        @endforeach
    </div>
    @endif

    <div class="up-status-badge" id="upStatusBadge" style="margin-left:auto">
        <i class="bi bi-circle"></i> Idle
    </div>

    {{-- ← NEW: quarterly hone par teeno months ke tabs — user har month ke
         liye files upload karta hai, phir sabhi months combine hoke ek
         quarter ka B2CS/B2B/Summary/JSON bante hain --}}
    <div class="up-qtr-tabs" id="upQtrTabs">
        <span class="up-qtr-tab-label">Month:</span>
        <div id="upQtrTabButtons" style="display:flex;gap:6px;flex-wrap:wrap"></div>
    </div>

    <div class="up-dup-warn" id="upDupWarn" style="margin-top:8px">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <div>
            <strong>Duplicate Period Detected</strong>
            <div id="upDupMsg" style="margin-top:3px;font-size:11px"></div>
            <div class="up-dw-actions">
                <button class="up-dw-btn up-dw-replace"  onclick="upSetPeriodAction('replace')"><i class="bi bi-arrow-repeat"></i> Replace existing</button>
                <button class="up-dw-btn up-dw-keepboth" onclick="upSetPeriodAction('keepboth')"><i class="bi bi-plus-circle"></i> Add to existing</button>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════ UPLOAD CARD ══════════════════ --}}
<div class="up-card">
    <div class="up-card-header">
        <div class="up-card-title"><i class="bi bi-cloud-upload-fill"></i> Import Marketplace Data</div>
        <div style="display:flex;align-items:center;gap:8px">
            <span class="up-card-hint" id="upCardHint">Click any row to upload</span>
            <button class="up-btn-hsn" id="upBtnHsn" onclick="upToggleHsn()">
                <i class="bi bi-pencil-square"></i> Change HSN
            </button>
        </div>
    </div>

    {{-- Gov. Excel --}}
    <!--<div class="up-plat-row" id="row-govexcel">-->
    <!--    <div class="up-plat-icon" style="background:rgba(22,101,52,.1)">-->
    <!--        <i class="bi bi-file-earmark-spreadsheet-fill" style="color:#166534"></i>-->
    <!--    </div>-->
    <!--    <div>-->
    <!--        <div class="up-plat-name">Gov. Excel</div>-->
    <!--        <div class="up-plat-desc">Official government B2C / B2B Excel format</div>-->
    <!--    </div>-->
    <!--    <div class="up-zone">-->
    <!--        <label class="up-btn" id="btn-govexcel">-->
    <!--            <i class="bi bi-upload"></i> Upload Gov. Excel-->
    <!--            <input type="file" accept=".xlsx,.xls" onchange="upHandleFile('govexcel',this)">-->
    <!--        </label>-->
    <!--        <label class="up-reupload-btn" id="reup-govexcel">-->
    <!--            <i class="bi bi-arrow-repeat"></i> Re-upload file-->
    <!--            <input type="file" accept=".xlsx,.xls" onchange="upHandleFile('govexcel',this)">-->
    <!--        </label>-->
    <!--        <div class="up-file-status" id="fs-govexcel"></div>-->
    <!--    </div>-->
    <!--    <div class="up-amt-col">-->
    <!--        <div class="up-amt-label">Amount</div>-->
    <!--        <div class="up-amt-val" id="amt-govexcel">0</div>-->
    <!--    </div>-->
    <!--</div>-->

    {{-- Other --}}
    <div class="up-plat-row" id="row-other">
        <div class="up-plat-icon" style="background:rgba(0,201,141,.1)">
            <i class="bi bi-receipt" style="color:var(--teal)"></i>
        </div>
        <div>
            <div class="up-plat-name">Upload_Other</div>
            <div class="up-plat-desc">Generic B2C Sheet &nbsp;|&nbsp; B2B Sheet</div>

            
        </div>
        <div class="up-zone">
            <label class="up-btn" id="btn-other-b2c">
                <i class="bi bi-upload"></i> Upload B2C Sheet
                <input type="file" accept=".xlsx,.xls,.csv" onchange="upHandleFile('other-b2c',this)">
            </label>
           
            <button type="button" class="up-reupload-btn up-delete-btn" id="reup-other-b2c" onclick="upDeleteFile('other-b2c')">
                <i class="bi bi-trash3-fill"></i> Delete file
            </button>
            <div class="up-file-status" id="fs-other-b2c"></div>
            <label class="up-btn" id="btn-other-b2b" style="margin-top:2px">
                <i class="bi bi-upload"></i> Upload B2B Sheet
                <input type="file" accept=".xlsx,.xls,.csv" onchange="upHandleFile('other-b2b',this)">
            </label>

        
            <button type="button" class="up-reupload-btn up-delete-btn" id="reup-other-b2b" onclick="upDeleteFile('other-b2b')">
                <i class="bi bi-trash3-fill"></i> Delete file
            </button>
            <div class="up-file-status" id="fs-other-b2b"></div>
        </div>
        <div class="up-amt-col">
            <div class="up-amt-label">Amount</div>
            <div class="up-amt-val" id="amt-other">0</div>
        </div>
    </div>

    {{-- Amazon --}}
    <div class="up-plat-row" id="row-amazon">
        <div class="up-plat-icon" style="background:rgba(255,153,0,.1)">
            <img src="https://upload.wikimedia.org/wikipedia/commons/a/a9/Amazon_logo.svg" alt="Amazon"
                 onerror="this.style.display='none';this.insertAdjacentHTML('afterend','<i class=\'bi bi-amazon\' style=\'color:#e07b00;font-size:16px\'></i>')">
        </div>
        <div>
            <div class="up-plat-name">Amazon</div>
            <div class="up-plat-desc">GSTR-1 Ready-to-File &nbsp;|&nbsp; MTR Report — B2B + B2C &nbsp;|&nbsp; Stock Transfer</div>
        </div>
        <div class="up-zone">
            <button class="up-btn" id="btn-amazon" onclick="upOpenModal('amazonModal')" style="border-style:solid">
                <i class="bi bi-upload"></i> Upload Amazon Files
            </button>
            <div class="up-file-status" id="fs-amazon"></div>
        </div>
        <div class="up-amt-col">
            <div class="up-amt-label">Amount</div>
            <div class="up-amt-val" id="amt-amazon">0</div>
        </div>
    </div>

    {{-- Flipkart --}}
    <div class="up-plat-row" id="row-flipkart">
        <div class="up-plat-icon" style="background:rgba(40,116,240,.1)">
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/6/6b/Flipkart_logo.svg/120px-Flipkart_logo.svg.png" alt="Flipkart"
                 onerror="this.style.display='none';this.insertAdjacentHTML('afterend','<i class=\'bi bi-basket3-fill\' style=\'color:#2874f0;font-size:16px\'></i>')">
        </div>
        <div>
            <div class="up-plat-name">Flipkart</div>
            <div class="up-plat-desc">Sales Report (MTR) &nbsp;|&nbsp; GST Report (7A/7B) — upload either or both</div>
        </div>
        <div class="up-zone">
            <label class="up-btn" id="btn-flipkart-sales">
                <i class="bi bi-upload"></i> Upload Sales Report
                <input type="file" accept=".xlsx,.xls" onchange="upHandleFile('flipkart-sales',this)">
            </label>
            <button type="button" class="up-reupload-btn up-delete-btn" id="reup-flipkart-sales" onclick="upDeleteFile('flipkart-sales')">
                <i class="bi bi-trash3-fill"></i> Delete file
            </button>
            <div class="up-file-status" id="fs-flipkart-sales"></div>

            <label class="up-btn" id="btn-flipkart-gst" style="margin-top:2px">
                <i class="bi bi-upload"></i> Upload GST Report
                <input type="file" accept=".xlsx,.xls" onchange="upHandleFile('flipkart-gst',this)">
            </label>
            <button type="button" class="up-reupload-btn up-delete-btn" id="reup-flipkart-gst" onclick="upDeleteFile('flipkart-gst')">
                <i class="bi bi-trash3-fill"></i> Delete file
            </button>
            <div class="up-file-status" id="fs-flipkart-gst"></div>
        </div>
        <div class="up-amt-col">
            <div class="up-amt-label">Amount</div>
            <div class="up-amt-val" id="amt-flipkart">0<span class="up-tcs-badge" id="tcs-flipkart">TCS</span></div>
        </div>
    </div>

    {{-- Meesho --}}
    <div class="up-plat-row" id="row-meesho">
        <div class="up-plat-icon" style="background:rgba(160,32,240,.1)">
            <i class="bi bi-shop" style="color:#8020d0;font-size:16px"></i>
        </div>
        <div>
            <div class="up-plat-name">Meesho</div>
            <div class="up-plat-desc">TCS Sales + Return + Tax Invoice — opens in popup</div>
        </div>
        <div class="up-zone">
            <button class="up-btn" id="btn-meesho" onclick="upOpenModal('meeshoModal')" style="border-style:solid">
                <i class="bi bi-upload"></i> Upload Meesho Files
            </button>
            <div class="up-file-status" id="fs-meesho"></div>
        </div>
        <div class="up-amt-col">
            <div class="up-amt-label">Amount</div>
            <div class="up-amt-val" id="amt-meesho">0<span class="up-tcs-badge" id="tcs-meesho">TCS</span></div>
        </div>
    </div>

    {{-- Myntra --}}
    {{-- Myntra --}}
<div class="up-plat-row" id="row-myntra">
    <div class="up-plat-icon" style="background:rgba(194,65,12,.1)">
        <i class="bi bi-handbag-fill" style="color:#c2410c;font-size:16px"></i>
    </div>
    <div>
        <div class="up-plat-name">Myntra</div>
        <div class="up-plat-desc">Packed Sheet &nbsp;|&nbsp; RT Sheet &nbsp;|&nbsp;</div>
    </div>
    <div class="up-zone">
        <button class="up-btn" id="btn-myntra" onclick="upOpenModal('myntraModal')" style="border-style:solid">
            <i class="bi bi-upload"></i> Upload Myntra Files
        </button>
        <div class="up-file-status" id="fs-myntra"></div>
    </div>
    <div class="up-amt-col">
        <div class="up-amt-label">Amount</div>
        <div class="up-amt-val" id="amt-myntra">0</div>
    </div>
</div>






    

    {{-- JioMart --}}
    <div class="up-plat-row" id="row-jiomart">
        <div class="up-plat-icon" style="background:rgba(29,78,216,.1)">
            <i class="bi bi-cart4" style="color:#1d4ed8;font-size:16px"></i>
        </div>
        <div>
            <div class="up-plat-name">JioMart</div>
            <div class="up-plat-desc">Invoice Report</div>
        </div>
        <div class="up-zone">
            <label class="up-btn" id="btn-jiomart">
                <i class="bi bi-upload"></i> Upload Invoice Report
                <input type="file" accept=".xlsx,.xls,.csv" onchange="upHandleFile('jiomart',this)">
            </label>
            <button type="button" class="up-reupload-btn up-delete-btn" id="reup-jiomart" onclick="upDeleteFile('jiomart')">
                <i class="bi bi-trash3-fill"></i> Delete file
            </button>
            <div class="up-file-status" id="fs-jiomart"></div>
        </div>
        <div class="up-amt-col">
            <div class="up-amt-label">Amount</div>
            <div class="up-amt-val" id="amt-jiomart">0</div>
        </div>
    </div>

     

    {{-- Snapdeal --}}
    <div class="up-plat-row" id="row-snapdeal">
        <div class="up-plat-icon" style="background:rgba(228,94,0,.1)">
            <i class="bi bi-bag-check" style="color:#e45e00;font-size:16px"></i>
        </div>
        <div>
            <div class="up-plat-name">Snapdeal</div>
            <div class="up-plat-desc">GST Report</div>
        </div>
        <div class="up-zone">
            <label class="up-btn" id="btn-snapdeal">
                <i class="bi bi-upload"></i> Upload GST Report
                <input type="file" accept=".xlsx,.xls,.csv" onchange="upHandleFile('snapdeal',this)">
            </label>
            <button type="button" class="up-reupload-btn up-delete-btn" id="reup-snapdeal" onclick="upDeleteFile('snapdeal')">
                <i class="bi bi-trash3-fill"></i> Delete file
            </button>
            <div class="up-file-status" id="fs-snapdeal"></div>
        </div>
        <div class="up-amt-col">
            <div class="up-amt-label">Amount</div>
            <div class="up-amt-val" id="amt-snapdeal">0</div>
        </div>
    </div>


    <!-- shopify  -->

    {{-- Shopify --}}
<div class="up-plat-row" id="row-shopify">
    <div class="up-plat-icon" style="background:rgba(149,225,159,.15)">
        <i class="bi bi-bag-fill" style="color:#1a936f;font-size:16px"></i>
    </div>
    <div>
        <div class="up-plat-name">Shopify</div>
        <div class="up-plat-desc">Orders Export — CSV / Excel (Admin &rarr; Orders &rarr; Export)</div>
    </div>
    <div class="up-zone">
        <label class="up-btn" id="btn-shopify">
            <i class="bi bi-upload"></i> Upload Orders Export
            <input type="file" accept=".csv,.xlsx,.xls" onchange="upHandleFile('shopify',this)">
        </label>
        <button type="button" class="up-reupload-btn up-delete-btn" id="reup-shopify" onclick="upDeleteFile('shopify')">
            <i class="bi bi-trash3-fill"></i> Delete file
        </button>
        <div class="up-file-status" id="fs-shopify"></div>
    </div>
    <div class="up-amt-col">
        <div class="up-amt-label">Amount</div>
        <div class="up-amt-val" id="amt-shopify">0</div>
    </div>
</div>

  

    {{-- HSN Panel --}}
    <div class="up-hsn-panel" id="upHsnPanel">
        <div class="up-hsn-head">
            <i class="bi bi-pencil-square" style="color:var(--teal)"></i> HSN / Rate Override
            <span style="font-size:10px;color:#94a3b8;font-weight:400">— HSN is read from file. Edit here only to override.</span>
        </div>
        <div class="up-hsn-grid" id="upHsnGrid">
            <div style="font-size:11px;color:#94a3b8;grid-column:1/-1">Upload a file first — HSN groups will appear here.</div>
        </div>
        <button class="up-btn-apply-hsn" onclick="upApplyHsn()"><i class="bi bi-check2-circle"></i> Apply HSN Changes</button>
    </div>
</div>{{-- /up-card --}}


{{-- ══ AMAZON MODAL ══ --}}
<div class="up-modal-overlay" id="amazonModal" onclick="upCloseModalOutside(event,'amazonModal')">
    <div class="up-modal-box">
        <div class="up-modal-header">
            <div class="up-modal-title"><i class="bi bi-amazon" style="color:#e07b00;margin-right:7px"></i>Amazon Report</div>
            <button class="up-modal-close" onclick="upCloseModal('amazonModal')"><i class="bi bi-x"></i></button>
        </div>

        <div style="font-size:11px;color:#6b7a99;background:#f7faff;border:1px solid #e8edf5;border-radius:9px;padding:9px 11px;margin-bottom:14px">
            <i class="bi bi-info-circle-fill" style="color:var(--sky)"></i>
            Amazon gives the same sales data in two forms — pick <strong>one</strong>: either the combined
            <strong>GSTR-1 Ready file</strong>, or the <strong>MTR B2C / B2B report(s)</strong>. Uploading both
            would double every transaction, so once you load one, the other option locks.
        </div>

        <div class="up-modal-field">
            <div class="up-modal-field-label">GSTR-1 Ready File <span class="up-tag-opt">Option A</span></div>
            <div class="up-modal-field-desc">Combined workbook with B2C Small + B2C Large + B2B sheets in one Excel file</div>
            <div class="up-mfs-slot" id="mfs-amz-gstr1">
                <input type="file" id="amzfile-gstr1" accept=".xlsx,.xls" onchange="upHandleAmazonFile('gstr1',this)">
                <div class="up-mfs-icon" id="amzicon-gstr1"><i class="bi bi-file-earmark-spreadsheet"></i></div>
                <div>
                    <div class="up-mfs-name" id="amzname-gstr1">Choose file</div>
                    <div class="up-mfs-sub">Excel (.xlsx / .xls)</div>
                    <div class="up-mfs-stat" id="amzstat-gstr1"></div>
                </div>
                <i class="bi bi-upload" style="color:#c5d0e6;margin-left:auto"></i>
            </div>
        </div>

        <div style="text-align:center;font-size:10px;color:#94a3b8;font-weight:700;margin:-4px 0 12px">— OR —</div>

        <div class="up-modal-field">
            <div class="up-modal-field-label">MTR B2C Report <span class="up-tag-opt">Option B</span></div>
            <div class="up-modal-field-desc">Upload either B2C, B2B, or both — never together with the GSTR-1 file above</div>
            <div class="up-mfs-slot" id="mfs-amz-b2c">
                <input type="file" id="amzfile-b2c" accept=".xlsx,.xls,.csv" onchange="upHandleAmazonFile('b2c',this)">
                <div class="up-mfs-icon" id="amzicon-b2c"><i class="bi bi-file-earmark-spreadsheet"></i></div>
                <div>
                    <div class="up-mfs-name" id="amzname-b2c">Choose file</div>
                    <div class="up-mfs-sub">CSV or Excel (.csv / .xlsx / .xls)</div>
                    <div class="up-mfs-stat" id="amzstat-b2c"></div>
                </div>
                <i class="bi bi-upload" style="color:#c5d0e6;margin-left:auto"></i>
            </div>
        </div>
        <div class="up-modal-field">
            <div class="up-modal-field-label">MTR B2B Report <span class="up-tag-opt">Option B</span></div>
            <div class="up-modal-field-desc">Upload either B2C, B2B, or both — never together with the GSTR-1 file above</div>
            <div class="up-mfs-slot" id="mfs-amz-b2b">
                <input type="file" id="amzfile-b2b" accept=".xlsx,.xls,.csv" onchange="upHandleAmazonFile('b2b',this)">
                <div class="up-mfs-icon" id="amzicon-b2b"><i class="bi bi-file-earmark-spreadsheet"></i></div>
                <div>
                    <div class="up-mfs-name" id="amzname-b2b">Choose file</div>
                    <div class="up-mfs-sub">CSV or Excel (.csv / .xlsx / .xls)</div>
                    <div class="up-mfs-stat" id="amzstat-b2b"></div>
                </div>
                <i class="bi bi-upload" style="color:#c5d0e6;margin-left:auto"></i>
            </div>
        </div>

        <div style="text-align:center;font-size:10px;color:#94a3b8;font-weight:700;margin:12px 0 12px">— ADDITIONAL —</div>

        <div class="up-modal-field">
            <div class="up-modal-field-label">Stock Transfer Report <span class="up-tag-opt">Optional</span></div>
            
            
            <div class="up-mfs-slot" id="mfs-amz-stock">
                <input type="file" id="amzfile-stock" accept=".xlsx,.xls,.csv" onchange="upHandleAmazonFile('stock',this)">
                <div class="up-mfs-icon" id="amzicon-stock"><i class="bi bi-arrow-left-right"></i></div>
                <div>
                    <div class="up-mfs-name" id="amzname-stock">Choose file</div>
                    <div class="up-mfs-sub">CSV or Excel (.csv / .xlsx / .xls)</div>
                    <div class="up-mfs-stat" id="amzstat-stock"></div>
                </div>
                <i class="bi bi-upload" style="color:#c5d0e6;margin-left:auto"></i>
            </div>
        </div>

        <div class="up-modal-actions">
            <button class="up-btn-modal-cancel" onclick="upCloseModal('amazonModal')">Cancel</button>
            <button class="up-btn-modal-submit" id="btnAmazonSubmit" onclick="upSubmitAmazon()" disabled
                    style="background:linear-gradient(135deg,#e07b00,#ff9900)">
                <i class="bi bi-check2-circle"></i> Submit
            </button>
        </div>
    </div>
</div>

{{-- ══ MEESHO MODAL ══ --}}
<div class="up-modal-overlay" id="meeshoModal" onclick="upCloseModalOutside(event,'meeshoModal')">
    <div class="up-modal-box">
        <div class="up-modal-header">
            <div class="up-modal-title"><i class="bi bi-shop" style="color:#8020d0;margin-right:7px"></i>Meesho Report</div>
            <button class="up-modal-close" onclick="upCloseModal('meeshoModal')"><i class="bi bi-x"></i></button>
        </div>
        <div class="up-modal-gstin-row">
            <span class="up-modal-gstin-label">E-commerce operator GSTIN</span>
            <span class="up-modal-gstin-val" id="meeshoGstinDisplay">09AARCM9332R1CM</span>
            <button class="up-btn-gstin-edit" onclick="upToggleGstinEdit()"><i class="bi bi-pencil-fill"></i> Edit</button>
        </div>
        <div class="up-gstin-edit-wrap" id="upGstinEditWrap">
            <input class="up-gstin-edit-input" type="text" id="meeshoGstinInput" value="09AARCM9332R1CM"
                   oninput="document.getElementById('meeshoGstinDisplay').textContent=this.value">
        </div>

        <div class="up-modal-field">
            <div class="up-modal-field-label">TCS Sales Sheet <span class="up-tag-req">Required</span></div>
            <div class="up-modal-field-desc">Columns: gstin, sub_order_num, gst_rate, total_taxable_sale_value, end_customer_state_new</div>
            <div class="up-mfs-slot" id="mfs-sales">
                <input type="file" id="mfile-sales" accept=".xlsx,.xls" onchange="upHandleMeeshoFile('sales',this)">
                <div class="up-mfs-icon" id="micon-sales"><i class="bi bi-file-earmark-spreadsheet"></i></div>
                <div>
                    <div class="up-mfs-name" id="mname-sales">Choose file</div>
                    <div class="up-mfs-sub">Excel (.xlsx / .xls)</div>
                    <div class="up-mfs-stat" id="mstat-sales"></div>
                </div>
                <i class="bi bi-upload" style="color:#c5d0e6;margin-left:auto"></i>
            </div>
        </div>
        <div class="up-modal-field">
            <div class="up-modal-field-label">TCS Sales Return Sheet <span class="up-tag-req">Required</span></div>
            <div class="up-mfs-slot" id="mfs-return">
                <input type="file" id="mfile-return" accept=".xlsx,.xls" onchange="upHandleMeeshoFile('return',this)">
                <div class="up-mfs-icon" id="micon-return"><i class="bi bi-file-earmark-spreadsheet"></i></div>
                <div>
                    <div class="up-mfs-name" id="mname-return">Choose file</div>
                    <div class="up-mfs-sub">Excel (.xlsx / .xls)</div>
                    <div class="up-mfs-stat" id="mstat-return"></div>
                </div>
                <i class="bi bi-upload" style="color:#c5d0e6;margin-left:auto"></i>
            </div>
        </div>
        <div class="up-modal-field">
            <div class="up-modal-field-label">Tax Invoice <span class="up-tag-opt">Optional</span></div>
            <div class="up-mfs-slot" id="mfs-invoice">
                <input type="file" id="mfile-invoice" accept=".xlsx,.xls" onchange="upHandleMeeshoFile('invoice',this)">
                <div class="up-mfs-icon" id="micon-invoice"><i class="bi bi-file-earmark-text"></i></div>
                <div>
                    <div class="up-mfs-name" id="mname-invoice">Choose file</div>
                    <div class="up-mfs-sub">Excel (.xlsx / .xls) — Optional</div>
                    <div class="up-mfs-stat" id="mstat-invoice"></div>
                </div>
                <i class="bi bi-upload" style="color:#c5d0e6;margin-left:auto"></i>
            </div>
        </div>
        <div class="up-modal-actions">
            <button class="up-btn-modal-cancel" onclick="upCloseModal('meeshoModal')">Cancel</button>
            <button class="up-btn-modal-submit" id="btnMeeshoSubmit" onclick="upSubmitMeesho()" disabled
                    style="background:linear-gradient(135deg,#8020d0,#a040f0)">
                <i class="bi bi-check2-circle"></i> Submit
            </button>
        </div>
    </div>
</div>


{{-- ══ MYNTRA MODAL ══ --}}
<div class="up-modal-overlay" id="myntraModal" onclick="upCloseModalOutside(event,'myntraModal')">
    <div class="up-modal-box">
        <div class="up-modal-header">
            <div class="up-modal-title"><i class="bi bi-handbag-fill" style="color:#c2410c;margin-right:7px"></i>Myntra Report</div>
            <button class="up-modal-close" onclick="upCloseModal('myntraModal')"><i class="bi bi-x"></i></button>
        </div>
        <div class="up-modal-gstin-row">
            <span class="up-modal-gstin-label">E-commerce operator GSTIN</span>
            <span class="up-modal-gstin-val">{{ session('gstr_selected_client.gstin') ?? session('selected_client.gstin', '--') }}</span>
        </div>

        <!-- <div style="font-size:11px;color:#6b7a99;background:#f7faff;border:1px solid #e8edf5;border-radius:9px;padding:9px 11px;margin-bottom:14px">
            <i class="bi bi-info-circle-fill" style="color:var(--sky)"></i>
            Teeno sheets independent hain — koi 1, 2, ya 3 saari upload karein, jo bhi mile combine hoke ek Myntra amount ban jayega.
        </div> -->

        <div class="up-modal-field">
            <div class="up-modal-field-label">Packed Sheet <span class="up-tag-opt">Optional</span></div>
            <div class="up-modal-field-desc">Dispatched / sale items</div>
            <div class="up-mfs-slot" id="mfs-myn-packed">
                <input type="file" id="mynfile-packed" accept=".csv,.xlsx,.xls" onchange="upHandleMyntraFile('packed',this)">
                <div class="up-mfs-icon" id="mynicon-packed"><i class="bi bi-file-earmark-spreadsheet"></i></div>
                <div>
                    <div class="up-mfs-name" id="mynname-packed">Choose file</div>
                    <div class="up-mfs-sub">CSV or Excel</div>
                    <div class="up-mfs-stat" id="mynstat-packed"></div>
                </div>
                <i class="bi bi-upload" style="color:#c5d0e6;margin-left:auto"></i>
            </div>
        </div>

        <div class="up-modal-field">
            <div class="up-modal-field-label">RT Sheet <span class="up-tag-opt">Optional</span></div>
            <div class="up-modal-field-desc">Returned items</div>
            <div class="up-mfs-slot" id="mfs-myn-rt">
                <input type="file" id="mynfile-rt" accept=".csv,.xlsx,.xls" onchange="upHandleMyntraFile('rt',this)">
                <div class="up-mfs-icon" id="mynicon-rt"><i class="bi bi-file-earmark-spreadsheet"></i></div>
                <div>
                    <div class="up-mfs-name" id="mynname-rt">Choose file</div>
                    <div class="up-mfs-sub">CSV or Excel</div>
                    <div class="up-mfs-stat" id="mynstat-rt"></div>
                </div>
                <i class="bi bi-upload" style="color:#c5d0e6;margin-left:auto"></i>
            </div>
        </div>

        <div class="up-modal-field">
            <div class="up-modal-field-label">RTO Sheet <span class="up-tag-opt">Optional</span></div>
            <div class="up-modal-field-desc">Return-to-origin items</div>
            <div class="up-mfs-slot" id="mfs-myn-rto">
                <input type="file" id="mynfile-rto" accept=".csv,.xlsx,.xls" onchange="upHandleMyntraFile('rto',this)">
                <div class="up-mfs-icon" id="mynicon-rto"><i class="bi bi-file-earmark-spreadsheet"></i></div>
                <div>
                    <div class="up-mfs-name" id="mynname-rto">Choose file</div>
                    <div class="up-mfs-sub">CSV or Excel</div>
                    <div class="up-mfs-stat" id="mynstat-rto"></div>
                </div>
                <i class="bi bi-upload" style="color:#c5d0e6;margin-left:auto"></i>
            </div>
        </div>

        <div class="up-modal-actions">
            <button class="up-btn-modal-cancel" onclick="upCloseModal('myntraModal')">Cancel</button>
            <button class="up-btn-modal-submit" id="btnMyntraSubmit" onclick="upSubmitMyntra()" disabled
                    style="background:linear-gradient(135deg,#c2410c,#ea580c)">
                <i class="bi bi-check2-circle"></i> Submit
            </button>
        </div>
    </div>
</div>

{{-- ══ GLOWROAD MODAL ══ --}}
<div class="up-modal-overlay" id="glowRoadModal" onclick="upCloseModalOutside(event,'glowRoadModal')">
    <div class="up-modal-box">
        <div class="up-modal-header">
            <div class="up-modal-title"><i class="bi bi-brightness-high" style="color:#0f766e;margin-right:7px"></i>GlowRoad Report</div>
            <button class="up-modal-close" onclick="upCloseModal('glowRoadModal')"><i class="bi bi-x"></i></button>
        </div>
        <div class="up-modal-field">
            <div class="up-modal-field-label">B2C TCS Report <span class="up-tag-req">Required</span></div>
            <div class="up-modal-field-desc">B2C customer sales with TCS data</div>
            <div class="up-mfs-slot" id="mfs-gr-b2c">
                <input type="file" id="grfile-b2c" accept=".xlsx,.xls,.csv" onchange="upHandleGlowRoadFile('b2c',this)">
                <div class="up-mfs-icon" id="gricon-b2c"><i class="bi bi-file-earmark-spreadsheet"></i></div>
                <div>
                    <div class="up-mfs-name" id="grname-b2c">Choose file</div>
                    <div class="up-mfs-sub">Excel or CSV</div>
                    <div class="up-mfs-stat" id="grstat-b2c"></div>
                </div>
                <i class="bi bi-upload" style="color:#c5d0e6;margin-left:auto"></i>
            </div>
        </div>
        <div class="up-modal-field">
            <div class="up-modal-field-label">B2B TCS Report <span class="up-tag-opt">Optional</span></div>
            <div class="up-modal-field-desc">B2B business sales with TCS data</div>
            <div class="up-mfs-slot" id="mfs-gr-b2b">
                <input type="file" id="grfile-b2b" accept=".xlsx,.xls,.csv" onchange="upHandleGlowRoadFile('b2b',this)">
                <div class="up-mfs-icon" id="gricon-b2b"><i class="bi bi-file-earmark-spreadsheet"></i></div>
                <div>
                    <div class="up-mfs-name" id="grname-b2b">Choose file</div>
                    <div class="up-mfs-sub">Excel or CSV — Optional</div>
                    <div class="up-mfs-stat" id="grstat-b2b"></div>
                </div>
                <i class="bi bi-upload" style="color:#c5d0e6;margin-left:auto"></i>
            </div>
        </div>
        <div class="up-modal-actions">
            <button class="up-btn-modal-cancel" onclick="upCloseModal('glowRoadModal')">Cancel</button>
            <button class="up-btn-modal-submit" id="btnGlowRoadSubmit" onclick="upSubmitGlowRoad()" disabled
                    style="background:linear-gradient(135deg,#0f766e,#14b8a6)">
                <i class="bi bi-check2-circle"></i> Submit
            </button>
        </div>
    </div>
</div>

{{-- ══ GSTIN MISMATCH MODAL ══ --}}
<div class="up-modal-overlay" id="gstinMismatchModal" onclick="upCloseModalOutside(event,'gstinMismatchModal')">
    <div class="up-modal-box" style="width:420px">
        <div class="up-modal-header">
            <div class="up-modal-title" style="color:#b91c1c">
                <i class="bi bi-exclamation-triangle-fill" style="margin-right:7px"></i>GSTIN Mismatch
            </div>
            <button class="up-modal-close" onclick="upCloseModal('gstinMismatchModal')"><i class="bi bi-x"></i></button>
        </div>

        <p style="font-size:12px;color:#6b7a99;margin:0 0 16px">
            The GSTIN in the uploaded file does not match the selected client's GSTIN. Please upload the correct file.
        </p>

        <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:8px">
            <div style="background:#fff5f5;border:1.5px solid rgba(239,68,68,.3);border-radius:10px;padding:11px 14px">
                <div style="font-size:10px;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;font-weight:700">GSTIN Found in the File</div>
                <div id="gstinMismatchFile" style="font-family:'Courier New',monospace;font-size:16px;font-weight:800;color:#b91c1c;margin-top:4px"></div>
            </div>
            <div style="background:rgba(0,201,141,.06);border:1.5px solid rgba(0,201,141,.3);border-radius:10px;padding:11px 14px">
                <div style="font-size:10px;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;font-weight:700">Selected Client's GSTIN</div>
                <div id="gstinMismatchClient" style="font-family:'Courier New',monospace;font-size:16px;font-weight:800;color:#007050;margin-top:4px"></div>
            </div>
        </div>

        <div class="up-modal-actions">
            <button class="up-btn-modal-cancel" onclick="upCloseModal('gstinMismatchModal')">Close</button>
            <a href="{{ route('gstr.clients.index') }}" class="up-btn-modal-submit" style="background:linear-gradient(135deg,#b91c1c,#ef4444);text-decoration:none;display:flex">
                <i class="bi bi-arrow-repeat"></i> Change Client
            </a>
        </div>
    </div>
</div>

{{-- ══ COMPUTE BAR ══ --}}
<div class="up-compute-bar">
    <div>
        <h3>Ready to Compute B2CS?</h3>
        <p id="upComputeStatus">Select a period and upload at least one platform file</p>
    </div>
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
        <button class="up-btn-sky" id="btnCompute" onclick="upStartCompute()" disabled>
            <i class="bi bi-calculator-fill"></i> Compute B2CS
        </button>
    </div>
</div>
<div class="up-progress-wrap" id="upProgressWrap"><div class="up-progress-fill" id="upProgressFill"></div></div>
<div class="up-step-log" id="upStepLog"></div>

{{-- ══ GLOBAL UPLOAD OVERLAY ══ --}}
<div class="up-overlay" id="upOverlay" aria-live="polite">
    <div class="up-balls-wrap">
        <div class="up-ball"></div>
        <div class="up-ball"></div>
        <div class="up-ball"></div>
        <div class="up-ball"></div>
        <div class="up-ball"></div>
        <div class="up-ball"></div>
        <div class="up-ball"></div>
        <div class="up-ball"></div>
    </div>
    <div class="up-overlay-title" id="upOverlayTitle">Uploading file…</div>
    <div class="up-overlay-sub" id="upOverlaySub">Parsing and validating data</div>
</div>

{{-- ══ TOAST CONTAINER ══ --}}
<div class="up-toast-wrap" id="upToastWrap"></div>

@endsection

@section('scripts')
<script>
// ── Constants ──────────────────────────────────────────────────────────────────
const UP_BATCH_ID       = 'batch_' + Date.now() + '_' + Math.random().toString(36).slice(2,9);
const UP_EXISTING       = @json($existingPeriods ?? []);
const UP_FREQUENCY      = @json($filingFrequency ?? 'monthly'); // ← NEW: 'monthly' | 'quarterly'
const UP_CSRF           = () => document.querySelector('meta[name="csrf-token"]').content;
const UP_UPLOAD_URL     = '{{ route('gstr.upload.uploadFile') }}';
const UP_PERIOD_CHK_URL = '{{ route('gstr.upload.checkPeriod') }}';
const UP_SAVE_URL       = '{{ route('gstr.upload.processTransactions') }}';
const UP_COMPUTE_URL    = '{{ route('gstr.upload.computeB2CS') }}';
const UP_PERIOD_STATUS_URL   = '{{ route('gstr.upload.periodStatus') }}';       // ← NEW
const UP_DELETE_PLATFORM_URL = '{{ route('gstr.upload.deletePlatformPeriod') }}'; // ← NEW

// ── State ──────────────────────────────────────────────────────────────────────
let upParsedData    = {};     // ← key ab "platform__MM/YYYY" hoga (monthly + quarterly dono ke liye)
let upFileNames     = {};     // ← NEW: key same as upParsedData, value = original uploaded filename
                               //        (modal repaint ke liye — is se pichhle month ki file
                               //        naam wapas dikha sakte hain jab tab switch karke wapas aao)
let upPeriodAction  = 'keepboth';
let upPeriodHasDup  = false;
let upPeriodChecked = false;
let upHsnOpen       = false;
let upAmazonDone    = { gstr1: false, b2c: false, b2b: false, stock: false };
let upMeeshoDone    = { sales: false, return: false, invoice: false };
let upGlowRoadDone  = { b2c: false, b2b: false };
let upExistingPlatforms = {}; // ← NEW: baseKey -> {platform,tx_count,taxable_value} — active period ka DB me pehle se saved data

// new added from here start

let upDocIssueOverride = null;   // ← NEW: Flipkart Section 13 se aaya override
let upHsnOverrideData  = null;   // ← NEW: Flipkart Section 12 se aaya override
// new added end from here

// ← NEW: quarterly state
let upQtrMonths       = [];      // e.g. ["04/2025","05/2025","06/2025"]
let upActiveQtrMonth  = null;    // currently selected tab, e.g. "04/2025"

// ── Overlay ────────────────────────────────────────────────────────────────────
function upShowOverlay(filename, sub) {
    document.getElementById('upOverlayTitle').textContent = filename ? 'Uploading ' + filename + '…' : 'Processing…';
    document.getElementById('upOverlaySub').textContent   = sub || 'Parsing and validating data';
    document.getElementById('upOverlay').classList.add('show');
}
function upHideOverlay() {
    document.getElementById('upOverlay').classList.remove('show');
}

// ── Period (MONTHLY) ────────────────────────────────────────────────────────────
function upMonthlyPeriod() {
    return document.getElementById('periodMonth').value + '/' + document.getElementById('periodYear').value;
}

// ── Period (QUARTERLY) — ← NEW ───────────────────────────────────────────────────
function upQuarterFyStart() {
    return parseInt(document.getElementById('qtrFyStart').value, 10);
}
function upQuarterNum() {
    return parseInt(document.getElementById('qtrQuarter').value, 10);
}
function upMonthsInQuarter(q, fyStart) {
    const map = {
        1: [`04/${fyStart}`, `05/${fyStart}`, `06/${fyStart}`],
        2: [`07/${fyStart}`, `08/${fyStart}`, `09/${fyStart}`],
        3: [`10/${fyStart}`, `11/${fyStart}`, `12/${fyStart}`],
        4: [`01/${fyStart+1}`, `02/${fyStart+1}`, `03/${fyStart+1}`],
    };
    return map[q];
}
function upFilingPeriodKey() {
    if (UP_FREQUENCY !== 'quarterly') return null;
    const q = upQuarterNum(), fyStart = upQuarterFyStart();
    const fyLabel = fyStart + '-' + String(fyStart + 1).slice(-2);
    return `Q${q}/${fyLabel}`;
}
const UP_MONTH_NAMES = {'01':'Jan','02':'Feb','03':'Mar','04':'Apr','05':'May','06':'Jun','07':'Jul','08':'Aug','09':'Sep','10':'Oct','11':'Nov','12':'Dec'};

function upRenderQtrTabs() {
    const wrap = document.getElementById('upQtrTabs');
    const btns = document.getElementById('upQtrTabButtons');
    if (UP_FREQUENCY !== 'quarterly') { wrap.classList.remove('show'); return; }

    const q = upQuarterNum(), fyStart = upQuarterFyStart();
    upQtrMonths = upMonthsInQuarter(q, fyStart);
    if (!upActiveQtrMonth || !upQtrMonths.includes(upActiveQtrMonth)) {
        upActiveQtrMonth = upQtrMonths[0];
    }

    btns.innerHTML = upQtrMonths.map(m => {
        const [mm, yyyy] = m.split('/');
        const hasData = Object.keys(upParsedData).some(k => k.endsWith('__' + m));
        const active  = (m === upActiveQtrMonth) ? 'active' : '';
        const dataCls = hasData ? 'has-data' : '';
        return `<button type="button" class="up-qtr-tab ${active} ${dataCls}" onclick="upSwitchQtrMonth('${m}')">
                    <span class="dot"></span> ${UP_MONTH_NAMES[mm]} ${yyyy}
                </button>`;
    }).join('');

    wrap.classList.add('show');
    document.getElementById('upCardHint').textContent =
        'Uploading for: ' + UP_MONTH_NAMES[upActiveQtrMonth.split('/')[0]] + ' ' + upActiveQtrMonth.split('/')[1];
}

function upSwitchQtrMonth(m) {
    upActiveQtrMonth = m;
    upRenderQtrTabs();
    upRefreshAllRowsForActiveMonth(); // ← re-paint row status/amounts for the tab now in view
    upFetchPeriodStatus(); // ← NEW: fetch DB-saved status for the newly active month

    // ← NEW: agar koi modal is waqt khula hai, usko bhi turant naye active
    // month ke data se repaint karo — warna modal khula rehte hue tab switch
    // karne par stale (pichhle month ki) file dikhti rehti thi.
    if (document.getElementById('amazonModal').classList.contains('show'))   upRefreshAmazonModalUI();
    if (document.getElementById('meeshoModal').classList.contains('show'))   upRefreshMeeshoModalUI();
    if (document.getElementById('glowRoadModal').classList.contains('show')) upRefreshGlowRoadModalUI();
}

// ── Active period resolver — ← NEW: single source of truth used everywhere below
function upActivePeriod() {
    return (UP_FREQUENCY === 'quarterly') ? upActiveQtrMonth : upMonthlyPeriod();
}
function upDataKey(platform) {
    // ← NEW: composite key so switching quarter-months (or monthly period) never overwrites previous period's data
    return platform + '__' + upActivePeriod();
}

// ← NEW: sirf ACTIVE PERIOD (quarterly ho to current quarter ke teeno months) ka
// data return karta hai. Isse period/month switch karte hi UI aur Compute dono
// hamesha SIRF usi period ka data dekhte hain — kisi purane/dusre period ka
// data kabhi mix ya visible nahi hota, chahe woh isi session mein pehle upload
// kiya gaya ho.
function upScopeKeys() {
    if (UP_FREQUENCY === 'quarterly') {
        return Object.keys(upParsedData).filter(k => upQtrMonths.includes(k.split('__')[1]));
    }
    const activeP = upActivePeriod();
    return Object.keys(upParsedData).filter(k => k.split('__')[1] === activeP);
}

function upUpdatePeriod() {
    if (UP_FREQUENCY === 'quarterly') {
        upRenderQtrTabs();
    } else {
        document.getElementById('upPeriodText').textContent = upMonthlyPeriod();
    }
    document.getElementById('upPeriodText').textContent =
        UP_FREQUENCY === 'quarterly' ? upFilingPeriodKey() : upMonthlyPeriod();

    upPeriodChecked = false;

    const checkKey = UP_FREQUENCY === 'quarterly' ? upFilingPeriodKey() : upMonthlyPeriod();
    if (UP_EXISTING.includes(checkKey)) upShowDup(checkKey, null); else upHideDup();

    upRefreshAllRowsForActiveMonth();
    upFetchPeriodStatus(); // ← NEW: period change hote hi DB se us period ka platform-wise status laao
    upCheckStatus();
}

function upShowDup(p, info) {
    upPeriodHasDup = true;
    let msg = `Period <strong>${p}</strong> already has existing data.`;
    if (info) msg += ` Transactions: ${info.tx_count}`;
    document.getElementById('upDupMsg').innerHTML = msg;
    document.getElementById('upDupWarn').classList.add('show');
}
function upHideDup() {
    upPeriodHasDup = false;
    document.getElementById('upDupWarn').classList.remove('show');
}
function upSetPeriodAction(a) {
    upPeriodAction = a;
    const msg  = document.getElementById('upDupMsg');
    const note = a === 'replace'
        ? '<br><strong style="color:#dc2626">— Old data will be replaced.</strong>'
        : '<br><strong style="color:#007050">— New data will be added to existing.</strong>';
    if (!msg.innerHTML.includes('—')) msg.innerHTML += note;
    upCheckStatus();
}
async function upCheckPeriodOnce() {
    if (upPeriodChecked) return;
    try {
        const body = UP_FREQUENCY === 'quarterly'
            ? { period: upActivePeriod(), filing_period: upFilingPeriodKey() }
            : { period: upActivePeriod() };

        const r = await fetch(UP_PERIOD_CHK_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': UP_CSRF() },
            body: JSON.stringify(body)
        });
        const d = await r.json();
        upPeriodChecked = true;
        const key = UP_FREQUENCY === 'quarterly' ? upFilingPeriodKey() : upActivePeriod();
        if (d.exists) upShowDup(key, d); else upHideDup();
    } catch(e) {}
}

// ── NEW: Existing (DB) platform-wise data for the active period ────────────────
// Selected client ke DB me is (active) period/filing_period ke liye jo bhi
// platforms ka data pehle se saved hai, usko fetch karke rows par lock +
// badge lagata hai — jab tak wo data delete na ho, dobara upload nahi hone deta.
const UP_BASE_PLATFORM_KEYS = ['amazon','flipkart','meesho','myntra','jiomart','snapdeal','shopify','glowroad','other','govexcel'];

async function upFetchPeriodStatus() {
    const period = upActivePeriod();
    if (!period) return;
    const filingPeriod = UP_FREQUENCY === 'quarterly' ? upFilingPeriodKey() : null;
    try {
        const body = filingPeriod ? { period, filing_period: filingPeriod } : { period };
        const r = await fetch(UP_PERIOD_STATUS_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': UP_CSRF() },
            body: JSON.stringify(body)
        });
        const d = await r.json();
        upExistingPlatforms = {};
        if (d.success && Array.isArray(d.platforms)) {
            d.platforms.forEach(p => { upExistingPlatforms[p.platform] = p; });
        }
    } catch(e) {
        upExistingPlatforms = {};
    }
    upApplyExistingStatusToRows();
    upCheckStatus();
}

function upIsPlatformLocked(baseKey) {
    return !!upExistingPlatforms[baseKey];
}

function upEnsureExistingBadge(baseKey) {
    let badge = document.getElementById('existing-' + baseKey);
    if (badge) return badge;
    const row = document.getElementById('row-' + baseKey);
    if (!row) return null;
    const zone = row.querySelector('.up-zone');
    if (!zone) return null;
    badge = document.createElement('div');
    badge.className = 'up-existing-badge';
    badge.id = 'existing-' + baseKey;
    badge.innerHTML = `<i class="bi bi-lock-fill"></i><div class="ex-info" id="exinfo-${baseKey}"></div>
        <button type="button" class="ex-del" onclick="upDeleteExistingPlatformData('${baseKey}')"><i class="bi bi-trash3-fill"></i> Delete</button>`;
    zone.appendChild(badge);
    return badge;
}

function upApplyExistingStatusToRows() {
    UP_BASE_PLATFORM_KEYS.forEach(baseKey => {
        const row = document.getElementById('row-' + baseKey);
        if (!row) return;
        const badge = upEnsureExistingBadge(baseKey);
        const info  = upExistingPlatforms[baseKey];
        const amtE  = document.getElementById('amt-' + baseKey);

        if (info) {
            row.classList.add('up-row-locked');
            if (badge) {
                badge.classList.add('show');
                const infoEl = document.getElementById('exinfo-' + baseKey);
                if (infoEl) infoEl.innerHTML = `<strong>${info.tx_count} transaction(s)</strong> already uploaded for this period — delete to re-upload.`;
            }
            if (amtE) {
                const val = '₹' + Math.abs(info.taxable_value || 0).toLocaleString('en-IN', { minimumFractionDigits:2, maximumFractionDigits:2 });
                const tcsBadge = amtE.querySelector('.up-tcs-badge');
                if (tcsBadge) { amtE.childNodes[0].textContent = val; } else { amtE.textContent = val; }
                amtE.classList.add('active');
            }
        } else {
            row.classList.remove('up-row-locked');
            if (badge) badge.classList.remove('show');
        }
    });
}

async function upDeleteExistingPlatformData(baseKey) {
    if (!confirm('Is period ke liye ' + baseKey + ' ka pura uploaded data delete kar dein?')) return;
    const period = upActivePeriod();
    const filingPeriod = UP_FREQUENCY === 'quarterly' ? upFilingPeriodKey() : null;
    upShowOverlay('', 'Deleting existing data…');
    try {
        const body = filingPeriod
            ? { platform: baseKey, period, filing_period: filingPeriod }
            : { platform: baseKey, period };
        const r = await fetch(UP_DELETE_PLATFORM_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': UP_CSRF() },
            body: JSON.stringify(body)
        });
        const d = await r.json();
        if (!r.ok || d.success === false) throw new Error(d.error || 'Delete failed');

        delete upExistingPlatforms[baseKey];
        upApplyExistingStatusToRows();
        upRefreshAmount(baseKey);
        upCheckStatus();
        if (UP_FREQUENCY === 'quarterly') upRenderQtrTabs();
        upToast(baseKey + ' ka existing data delete ho gaya — ab dobara upload kar sakte hain.', 'success');
    } catch(err) {
        upToast('Delete failed: ' + err.message, 'error');
    } finally {
        upHideOverlay();
    }
}

// ── HSN Panel — combines ALL months/platforms (poore quarter ka HSN together) ──
function upToggleHsn() {
    upHsnOpen = !upHsnOpen;
    document.getElementById('upHsnPanel').classList.toggle('show', upHsnOpen);
    const b = document.getElementById('upBtnHsn');
    b.classList.toggle('active', upHsnOpen);
    b.innerHTML = upHsnOpen
        ? '<i class="bi bi-x-circle"></i> Close HSN'
        : '<i class="bi bi-pencil-square"></i> Change HSN';
    if (upHsnOpen) upBuildHsnGrid();
}
function upBuildHsnGrid() {
    const all  = upScopeKeys().flatMap(k => upParsedData[k]); // ← FIX: sirf active period/quarter ka data
    const grid = document.getElementById('upHsnGrid');
    if (!all.length) {
        grid.innerHTML = '<div style="font-size:11px;color:#94a3b8;grid-column:1/-1">Upload a file first.</div>';
        return;
    }
    const groups = {};
    all.forEach(tx => {
        const k = tx.place_of_supply + '|' + tx.gst_rate;
        if (!groups[k]) groups[k] = { pos: tx.place_of_supply, rate: tx.gst_rate, hsn: tx.hsn_code || '', n: 0 };
        groups[k].n++;
        if (tx.hsn_code && !groups[k].hsn) groups[k].hsn = tx.hsn_code;
    });
    grid.innerHTML = Object.entries(groups).map(([k, g]) => `
        <div class="up-hsn-item">
            <div class="up-hsn-meta">${g.n} transactions</div>
            <div class="up-hsn-label">${g.pos} @ ${g.rate}%</div>
            <input class="up-hsn-input" placeholder="HSN Code" value="${g.hsn}" data-key="${k}">
        </div>`).join('');
}
function upApplyHsn() {
    const scope = upScopeKeys(); // ← FIX: sirf active period ke transactions par HSN apply ho
    document.querySelectorAll('.up-hsn-input[data-key]').forEach(inp => {
        const [pos, rate] = inp.dataset.key.split('|');
        const v = inp.value.trim();
        if (!v) return;
        scope.forEach(k => {
            upParsedData[k] = upParsedData[k].map(t =>
                (t.place_of_supply === pos && String(t.gst_rate) === rate) ? {...t, hsn_code: v} : t);
        });
    });
    upToast('HSN changes applied.', 'success');
}

// ── GSTIN Mismatch Modal ────────────────────────────────────────────────────────
function upShowGstinMismatchModal(fileGstin, clientGstin) {
    document.getElementById('gstinMismatchFile').textContent   = fileGstin   || '—';
    document.getElementById('gstinMismatchClient').textContent = clientGstin || '—';
    upOpenModal('gstinMismatchModal');
}

// ── Generic File Upload ────────────────────────────────────────────────────────
async function upHandleFile(platform, input) {
    const file = input.files[0];
    input.value = '';
    if (!file) return;

    // ← NEW: agar is period ke liye is platform ka data already DB me hai to block
    const lockBaseKey = platform.replace(/-(b2c|b2b|sales|gst|gstr1)$/, '');
    if (upIsPlatformLocked(lockBaseKey)) {
        upToast('Is period ke liye ' + lockBaseKey + ' ka data pehle se maujood hai. Pehle use delete karein.', 'error');
        return;
    }

    const activePeriod = upActivePeriod(); // ← NEW: quarterly ho to active tab ka month

    // ── Client-side duplicate format check (per active month) ──────────
    const fileTypeFamily = (p) => {
        if (p.includes('-gst')) return 'gstr';
        if (p.includes('-sales')) return 'sales';
        return 'other';
    };

    const newFamily = fileTypeFamily(platform);
    if (newFamily !== 'other') {
        const basePlatformKey = platform.split('-')[0];
        for (const existingKey in upParsedData) {
            // ← NEW: existingKey ab "platform__month" hai, isliye pehle platform-part nikalo
            const existingPlatform = existingKey.split('__')[0];
            const existingMonth    = existingKey.split('__')[1];
            if (existingMonth !== activePeriod) continue; // sirf isi month ke against check karo
            if (existingPlatform.startsWith(basePlatformKey) && fileTypeFamily(existingPlatform) !== newFamily) {
                upToast(`Duplicate report! '${fileTypeFamily(existingPlatform)}' is already uploaded for this month. Uploading both files will duplicate data.`, 'error');
                return;
            }
        }
    }

    const baseKey = platform.replace(/-(b2c|b2b|sales|gst)$/, '');
    const btn     = document.getElementById('btn-'  + platform);
    const fs      = document.getElementById('fs-'   + platform);
    const reup    = document.getElementById('reup-' + platform);
    const row     = document.getElementById('row-'  + baseKey);

    upSlotState(btn, fs, 'loading', '');
    upShowOverlay(file.name, 'Parsing and validating data…');
    await upCheckPeriodOnce();

    try {
        const result = await upParseFile(file, platform, activePeriod);
        const key = upDataKey(platform); // ← NEW: "platform__MM/YYYY"
        upParsedData[key] = result.transactions.map(t => ({ ...t, period: activePeriod }));
        upFileNames[key]  = file.name; // ← NEW: remember filename for this exact month/platform


        // ← NEW: agar is file (jaise Flipkart GST Report) se Section 13/12
        // override mila hai to usko yaad rakho — compute call ke waqt bhejna hai
        if (result.doc_issue_override && Object.keys(result.doc_issue_override).length) {
            upDocIssueOverride = result.doc_issue_override;
        }
        if (result.hsn_override && result.hsn_override.length) {
            upHsnOverrideData = result.hsn_override;
        }





        upSlotState(btn, fs, 'ok', '✓ ' + file.name + ' — ' + result.transactions_count + ' rows'
            + (UP_FREQUENCY === 'quarterly' ? ' (' + activePeriod + ')' : ''));
        if (reup) reup.classList.add('show');
        if (row) { row.classList.add('has-file'); row.classList.remove('has-error'); }
        upRefreshAmount(baseKey);
        if (upHsnOpen) upBuildHsnGrid();
        upCheckStatus();
        if (UP_FREQUENCY === 'quarterly') upRenderQtrTabs(); // dot indicator update
        upToast(file.name + ' uploaded — ' + result.transactions_count + ' transactions', 'success');
    } catch(err) {
        upSlotState(btn, fs, 'error', '✗ ' + err.message);
        if (reup) reup.classList.add('show');
        if (row) { row.classList.add('has-error'); row.classList.remove('has-file'); }
        delete upParsedData[upDataKey(platform)];
        delete upFileNames[upDataKey(platform)]; // ← NEW
        upCheckStatus();
        upToast('Upload failed: ' + err.message, 'error');
        if (err.gstin_error) upShowGstinMismatchModal(err.file_gstin, err.client_gstin);
    } finally {
        upHideOverlay();
    }
}

// ← NEW: 'Delete file' button ka handler. Pehle failed/success dono states mein
// 'Re-upload' button dikhta tha — ab uski jagah 'Delete file' hai. Click karte hi
// is exact platform + ACTIVE PERIOD ka data clear ho jata hai aur row wapas apni
// original "Upload" state mein aa jata hai, taaki user upar wale asli upload
// button se seedha ek naya/dusra file select kar sake.
function upDeleteFile(platform) {
    const baseKey = platform.replace(/-(b2c|b2b|sales|gst)$/, '');
    const btn   = document.getElementById('btn-'  + platform);
    const fs    = document.getElementById('fs-'   + platform);
    const reup  = document.getElementById('reup-' + platform);
    const row   = document.getElementById('row-'  + baseKey);
    const key   = upDataKey(platform);

    delete upParsedData[key];
    delete upFileNames[key];

    if (btn) {
        if (!btn.dataset.origLabel) btn.dataset.origLabel = btn.innerHTML;
        btn.classList.remove('loaded', 'error');
        btn.innerHTML = btn.dataset.origLabel;
    }
    if (fs)   { fs.classList.remove('show', 'ok', 'err'); fs.textContent = ''; }
    if (reup) reup.classList.remove('show');
    if (row)  row.classList.remove('has-file', 'has-error');

    upRefreshAmount(baseKey);
    if (upHsnOpen) upBuildHsnGrid();
    upCheckStatus();
    if (UP_FREQUENCY === 'quarterly') upRenderQtrTabs();
    upToast('File removed — you can upload a new one.', 'success');
}

// ← NEW: amount ab uss platform ke ACTIVE PERIOD/QUARTER ka sum hai (dusre months ka nahi)
function upRefreshAmount(baseKey) {
    const keys  = upScopeKeys().filter(k => { // ← FIX: sirf active period/quarter ka amount count ho
        const plat = k.split('__')[0];
        return plat === baseKey || plat.startsWith(baseKey + '-');
    });
    const total = keys.reduce((s, k) => s + upParsedData[k].reduce((a, t) => a + (t.taxable_value || 0), 0), 0);
    const el    = document.getElementById('amt-' + baseKey);
    if (!el) return;
    const badge = el.querySelector('.up-tcs-badge');
    const fmt   = total === 0 ? '0' : '₹' + Math.abs(total).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    if (badge) { el.childNodes[0].textContent = fmt; } else { el.textContent = fmt; }
    el.classList.toggle('active', total !== 0);
}

// ← FIXED: jab period switch ho (monthly month/year, ya quarterly tab), har row
// ka button/status uss ACTIVE PERIOD ke data ke hisaab se re-paint karo. Pehle
// yeh function sirf QUARTERLY clients ke liye chalta tha — MONTHLY clients ke
// liye Month/Year dropdown badalne se rows kabhi repaint hi nahi hote the,
// isliye purane month ka "Loaded" status hamesha dikhta reh jata tha chahe naye
// selected month mein koi file ho ya na ho. Ab yeh dono frequencies ke liye
// chalta hai — upDataKey() already active period ke hisaab se sahi key deta
// hai, isliye har row automatically sirf currently-selected period ka hi data
// dikhayega.
function upRefreshAllRowsForActiveMonth() {
    // ── Plain file-input platforms ──
    const platformInputs = [
        'govexcel','other-b2c','other-b2b','flipkart-sales','flipkart-gst',
        'jiomart','limeroad','citymall','paytm','snapdeal','reselling','shopify' // ← FIX: 'shopify' missing tha
    ];
    platformInputs.forEach(platform => {
        const baseKey = platform.replace(/-(b2c|b2b|sales|gst)$/, '');
        const btn  = document.getElementById('btn-'  + platform);
        const fs   = document.getElementById('fs-'   + platform);
        const reup = document.getElementById('reup-' + platform);
        const row  = document.getElementById('row-'  + baseKey);
        if (!btn) return; // row might not exist on this page
        const key  = upDataKey(platform);

        if (upParsedData[key] && upParsedData[key].length) {
            upSlotState(btn, fs, 'ok', '✓ ' + upParsedData[key].length + ' rows (' + upActivePeriod() + ')');
            if (reup) reup.classList.add('show');
            if (row) { row.classList.add('has-file'); row.classList.remove('has-error'); }
        } else {
            if (!btn.dataset.origLabel) btn.dataset.origLabel = btn.innerHTML;
            btn.classList.remove('loaded', 'error');
            btn.innerHTML = btn.dataset.origLabel;
            if (fs) { fs.classList.remove('show','ok','err'); fs.textContent=''; }
            if (reup) reup.classList.remove('show');
            if (row) { row.classList.remove('has-file','has-error'); }
        }
        upRefreshAmount(baseKey);
    });

    // ── Amazon (modal-based) — re-paint row for active period ──
    (function refreshAmazonRow() {
        const btn = document.getElementById('btn-amazon');
        const fs  = document.getElementById('fs-amazon');
        const row = document.getElementById('row-amazon');
        if (!btn) return;
        if (!btn.dataset.origLabel) btn.dataset.origLabel = btn.innerHTML;

        const gstr1Key = upDataKey('amazon-gstr1');
        const b2cKey   = upDataKey('amazon-b2c');
        const b2bKey   = upDataKey('amazon-b2b');
        const stockKey = upDataKey('amazon-stock');
        const hasGstr1 = !!(upParsedData[gstr1Key] && upParsedData[gstr1Key].length);
        const hasB2c   = !!(upParsedData[b2cKey]   && upParsedData[b2cKey].length);
        const hasB2b   = !!(upParsedData[b2bKey]   && upParsedData[b2bKey].length);
        const hasStock = !!(upParsedData[stockKey] && upParsedData[stockKey].length);

        // Keep the modal's internal "already uploaded" flags in sync with the active period
        upAmazonDone.gstr1 = hasGstr1;
        upAmazonDone.b2c   = hasB2c;
        upAmazonDone.b2b   = hasB2b;
        upAmazonDone.stock = hasStock;

        if (hasGstr1 || hasB2c || hasB2b || hasStock) {
            const parts = [];
            if (hasGstr1) parts.push('GSTR-1: ' + upParsedData[gstr1Key].length);
            if (hasB2c)   parts.push('B2C: '    + upParsedData[b2cKey].length);
            if (hasB2b)   parts.push('B2B: '    + upParsedData[b2bKey].length);
            if (hasStock) parts.push('Stock: '  + upParsedData[stockKey].length);
            btn.classList.add('loaded');
            btn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Loaded';
            fs.textContent = '✓ ' + parts.join(' | ') + ' (' + upActivePeriod() + ')';
            fs.className   = 'up-file-status show ok';
            row.classList.add('has-file'); row.classList.remove('has-error');
        } else {
            btn.classList.remove('loaded', 'error');
            btn.innerHTML = btn.dataset.origLabel;
            fs.classList.remove('show','ok','err'); fs.textContent = '';
            row.classList.remove('has-file','has-error');
        }
        upRefreshAmount('amazon');
    })();

    // ── Meesho (modal-based) — re-paint row for active period ──
    (function refreshMeeshoRow() {
        const btn = document.getElementById('btn-meesho');
        const fs  = document.getElementById('fs-meesho');
        const row = document.getElementById('row-meesho');
        if (!btn) return;
        if (!btn.dataset.origLabel) btn.dataset.origLabel = btn.innerHTML;

        const salesKey = upDataKey('meesho-sales');
        const retKey   = upDataKey('meesho-return');
        const invKey   = upDataKey('meesho-invoice');
        const hasSales = !!(upParsedData[salesKey] && upParsedData[salesKey].length);
        const hasRet   = !!(upParsedData[retKey]   && upParsedData[retKey].length);
        const hasInv   = !!(upParsedData[invKey]   && upParsedData[invKey].length);

        upMeeshoDone.sales   = hasSales;
        upMeeshoDone.return  = hasRet;
        upMeeshoDone.invoice = hasInv;

        if (hasSales) {
            fs.textContent = '✓ Sales: ' + upParsedData[salesKey].length
                + ' | Return: ' + (hasRet ? upParsedData[retKey].length : 0)
                + (hasInv ? ' | Invoice: ✓' : '')
                + ' (' + upActivePeriod() + ')';
            btn.classList.add('loaded');
            btn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Loaded';
            fs.className  = 'up-file-status show ok';
            row.classList.add('has-file'); row.classList.remove('has-error');
        } else {
            btn.classList.remove('loaded', 'error');
            btn.innerHTML = btn.dataset.origLabel;
            fs.classList.remove('show','ok','err'); fs.textContent = '';
            row.classList.remove('has-file','has-error');
        }
        upRefreshAmount('meesho');
    })();

    // ── GlowRoad (modal-based) — re-paint row for active period ──
    (function refreshGlowRoadRow() {
        const btn = document.getElementById('btn-glowroad');
        const fs  = document.getElementById('fs-glowroad');
        const row = document.getElementById('row-glowroad');
        if (!btn) return; // row not present on this page
        if (!btn.dataset.origLabel) btn.dataset.origLabel = btn.innerHTML;

        const b2cKey = upDataKey('glowroad-b2c');
        const b2bKey = upDataKey('glowroad-b2b');
        const hasB2c = !!(upParsedData[b2cKey] && upParsedData[b2cKey].length);
        const hasB2b = !!(upParsedData[b2bKey] && upParsedData[b2bKey].length);

        upGlowRoadDone.b2c = hasB2c;
        upGlowRoadDone.b2b = hasB2b;

        if (hasB2c) {
            fs.textContent = '✓ B2C: ' + upParsedData[b2cKey].length
                + (hasB2b ? ' | B2B: ' + upParsedData[b2bKey].length : '')
                + ' (' + upActivePeriod() + ')';
            btn.classList.add('loaded');
            btn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Loaded';
            fs.className  = 'up-file-status show ok';
            row.classList.add('has-file'); row.classList.remove('has-error');
        } else {
            btn.classList.remove('loaded', 'error');
            btn.innerHTML = btn.dataset.origLabel;
            fs.classList.remove('show','ok','err'); fs.textContent = '';
            row.classList.remove('has-file','has-error');
        }
        upRefreshAmount('glowroad');
    })();

    // ── Myntra (modal-based) — re-paint row for active period ──
    (function refreshMyntraRow() {
        const btn = document.getElementById('btn-myntra');
        const fs  = document.getElementById('fs-myntra');
        const row = document.getElementById('row-myntra');
        if (!btn) return;
        if (!btn.dataset.origLabel) btn.dataset.origLabel = btn.innerHTML;

        const packedKey = upDataKey('myntra-packed');
        const rtKey     = upDataKey('myntra-rt');
        const rtoKey    = upDataKey('myntra-rto');
        const hasPacked = !!(upParsedData[packedKey] && upParsedData[packedKey].length);
        const hasRt     = !!(upParsedData[rtKey]     && upParsedData[rtKey].length);
        const hasRto    = !!(upParsedData[rtoKey]    && upParsedData[rtoKey].length);

        upMyntraDone.packed = hasPacked;
        upMyntraDone.rt     = hasRt;
        upMyntraDone.rto    = hasRto;

        if (hasPacked || hasRt || hasRto) {
            let parts = [];
            if (hasPacked) parts.push('Packed: ' + upParsedData[packedKey].length);
            if (hasRt)     parts.push('RT: '     + upParsedData[rtKey].length);
            if (hasRto)    parts.push('RTO: '    + upParsedData[rtoKey].length);

            fs.textContent = '✓ ' + parts.join(' | ')
                + (UP_FREQUENCY === 'quarterly' ? ' (' + upActivePeriod() + ')' : '');
            btn.classList.add('loaded');
            btn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Loaded';
            fs.className  = 'up-file-status show ok';
            row.classList.add('has-file'); row.classList.remove('has-error');
        } else {
            btn.classList.remove('loaded', 'error');
            btn.innerHTML = btn.dataset.origLabel;
            fs.classList.remove('show','ok','err'); fs.textContent = '';
            row.classList.remove('has-file','has-error');
        }
        upRefreshAmount('myntra');
    })();
}

async function upParseFile(file, platform, per, role = 'single', calcBase = null) {
    const fd = new FormData();
    fd.append('file',      file);
    fd.append('platform',  platform);
    fd.append('period',    per);
    fd.append('file_role', role);
    fd.append('_token',    UP_CSRF());
    if (UP_FREQUENCY === 'quarterly') {
        fd.append('filing_period', upFilingPeriodKey());
    }
    if (calcBase) {
        fd.append('calc_base', calcBase);
    }

    const resp = await fetch(UP_UPLOAD_URL, { method: 'POST', headers: { 'X-CSRF-TOKEN': UP_CSRF() }, body: fd });
    const data = await resp.json();

    if (!resp.ok || data.success === false) {
        if (data.gstin_error) {
            const gErr = new Error('GSTIN mismatch — File: ' + data.file_gstin + ' / Client: ' + data.client_gstin);
            gErr.gstin_error  = true;
            gErr.file_gstin   = data.file_gstin;
            gErr.client_gstin = data.client_gstin;
            throw gErr;
        }
        throw new Error(data.error || 'Parse failed');
    }
    if (data.needs_calc_base) {
        return data;
    }
    if (role !== 'invoice' && !data.transactions?.length) {
        throw new Error(data.error || 'No transactions found in file');
    }
    return data;
}

// ── Slot State Helper ──────────────────────────────────────────────────────────
function upSlotState(btn, fs, state, msg) {
    if (!btn) return;
    if (!btn.dataset.origLabel) btn.dataset.origLabel = btn.innerHTML; // ← NEW: remember original for reset
    btn.classList.remove('loaded', 'error');
    if (fs) fs.classList.remove('show', 'ok', 'err');
    if (state === 'loading') {
        btn.innerHTML = '<span class="up-spin"></span> Parsing…';
    } else if (state === 'ok') {
        btn.classList.add('loaded');
        btn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Loaded';
        if (fs) { fs.textContent = msg; fs.className = 'up-file-status show ok'; }
    } else {
        btn.classList.add('error');
        btn.innerHTML = '<i class="bi bi-exclamation-circle-fill"></i> Error — re-upload';
        if (fs) { fs.textContent = msg; fs.className = 'up-file-status show err'; }
    }
}

// ── Modal Helpers ──────────────────────────────────────────────────────────────

// ← NEW: repaint a single "choose file" slot back to its EMPTY state
function upResetMfsSlot(slotId, iconId, nameId, statId, defaultIcon) {
    const slot = document.getElementById(slotId);
    if (!slot) return;
    slot.className = 'up-mfs-slot';
    document.getElementById(iconId).innerHTML   = defaultIcon;
    document.getElementById(nameId).textContent = 'Choose file';
    const statE = document.getElementById(statId);
    statE.textContent = '';
    statE.className   = 'up-mfs-stat';
}

// ← NEW: repaint a single "choose file" slot into its LOADED state, using
// the remembered filename + row count for that exact platform/month combo
function upPaintMfsSlot(slotId, iconId, nameId, statId, filename, count, isInvoice) {
    const slot = document.getElementById(slotId);
    if (!slot) return;
    slot.className = 'up-mfs-slot loaded';
    document.getElementById(iconId).innerHTML   = '<i class="bi bi-check-circle-fill" style="color:#007050"></i>';
    document.getElementById(nameId).textContent = filename || 'Uploaded file';
    const statE = document.getElementById(statId);
    statE.textContent = isInvoice ? ('✓ ' + count + ' invoice numbers matched') : ('✓ ' + count + ' transactions');
    statE.className   = 'up-mfs-stat ok';
}

// ← NEW: called every time the Amazon modal is opened OR the quarter-tab is
// switched while it's open — repaints all 3 slots strictly from the ACTIVE
// month's data, so April's file never bleeds into the May view and vice versa.
function upRefreshAmazonModalUI() {
    ['gstr1','b2c','b2b','stock'].forEach(role => {
        const key  = upDataKey('amazon-' + role);
        const data = upParsedData[key];
        const icon = role === 'stock'
            ? '<i class="bi bi-arrow-left-right"></i>'
            : '<i class="bi bi-file-earmark-spreadsheet"></i>';
        if (data && data.length) {
            upPaintMfsSlot('mfs-amz-'+role, 'amzicon-'+role, 'amzname-'+role, 'amzstat-'+role, upFileNames[key], data.length);
        } else {
            upResetMfsSlot('mfs-amz-'+role, 'amzicon-'+role, 'amzname-'+role, 'amzstat-'+role, icon);
        }
    });
    document.getElementById('btnAmazonSubmit').disabled = !(upAmazonDone.gstr1 || upAmazonDone.b2c || upAmazonDone.b2b || upAmazonDone.stock);
    upSyncAmazonSlotState();
}

// ← NEW: same idea for Meesho modal (sales / return / invoice slots)
function upRefreshMeeshoModalUI() {
    ['sales','return','invoice'].forEach(role => {
        const key  = upDataKey('meesho-' + role);
        const data = upParsedData[key];
        if (data && data.length) {
            upPaintMfsSlot('mfs-'+role, 'micon-'+role, 'mname-'+role, 'mstat-'+role, upFileNames[key], data.length, role === 'invoice');
        } else {
            const icon = role === 'invoice' ? '<i class="bi bi-file-earmark-text"></i>' : '<i class="bi bi-file-earmark-spreadsheet"></i>';
            upResetMfsSlot('mfs-'+role, 'micon-'+role, 'mname-'+role, 'mstat-'+role, icon);
        }
    });
    document.getElementById('btnMeeshoSubmit').disabled = !upMeeshoDone.sales;
}

// ← NEW: same idea for GlowRoad modal (b2c / b2b slots)
function upRefreshGlowRoadModalUI() {
    ['b2c','b2b'].forEach(role => {
        const key  = upDataKey('glowroad-' + role);
        const data = upParsedData[key];
        if (data && data.length) {
            upPaintMfsSlot('mfs-gr-'+role, 'gricon-'+role, 'grname-'+role, 'grstat-'+role, upFileNames[key], data.length);
        } else {
            upResetMfsSlot('mfs-gr-'+role, 'gricon-'+role, 'grname-'+role, 'grstat-'+role, '<i class="bi bi-file-earmark-spreadsheet"></i>');
        }
    });
    document.getElementById('btnGlowRoadSubmit').disabled = !upGlowRoadDone.b2c;
}

function upOpenModal(id)  {
    // ← NEW: modal-based platforms bhi lock check karein — is period ke liye
    // agar DB me pehle se data hai to modal khulne hi na de.
    const modalBaseMap = { amazonModal:'amazon', meeshoModal:'meesho', myntraModal:'myntra', glowRoadModal:'glowroad' };
    const lockKey = modalBaseMap[id];
    if (lockKey && upIsPlatformLocked(lockKey)) {
        upToast('Is period ke liye ' + lockKey + ' ka data pehle se maujood hai. Pehle use delete karein.', 'error');
        return;
    }

    document.getElementById(id).classList.add('show');
    document.body.style.overflow = 'hidden';
    // ← NEW: repaint the modal for the CURRENT active period every time it opens,
    // so old period's uploaded-file view never lingers when you reopen it later.
    if (id === 'amazonModal')   upRefreshAmazonModalUI();
    if (id === 'myntraModal')   upRefreshMyntraModalUI();
    if (id === 'meeshoModal')   upRefreshMeeshoModalUI();
    if (id === 'glowRoadModal') upRefreshGlowRoadModalUI();
}
function upCloseModal(id) { document.getElementById(id).classList.remove('show'); document.body.style.overflow = ''; }
function upCloseModalOutside(e, id) {
    if (e.target === document.getElementById(id)) upCloseModal(id);
}

// ── Amazon Modal ───────────────────────────────────────────────────────────────
function upSyncAmazonSlotState() {
    const gstr1Active = upAmazonDone.gstr1;
    const mtrActive   = upAmazonDone.b2c || upAmazonDone.b2b;

    const b2cInput  = document.getElementById('amzfile-b2c');
    const b2bInput  = document.getElementById('amzfile-b2b');
    const gstrInput = document.getElementById('amzfile-gstr1');
    const b2cSlot   = document.getElementById('mfs-amz-b2c');
    const b2bSlot   = document.getElementById('mfs-amz-b2b');
    const gstrSlot  = document.getElementById('mfs-amz-gstr1');

    if (b2cInput)  b2cInput.disabled  = gstr1Active;
    if (b2bInput)  b2bInput.disabled  = gstr1Active;
    if (gstrInput) gstrInput.disabled = mtrActive;

    if (b2cSlot)  b2cSlot.classList.toggle('disabled', gstr1Active);
    if (b2bSlot)  b2bSlot.classList.toggle('disabled', gstr1Active);
    if (gstrSlot) gstrSlot.classList.toggle('disabled', mtrActive);
}

async function upHandleAmazonFile(role, input) {
    const file = input.files[0];
    input.value = '';
    if (!file) return;

    if (role !== 'stock') {
        const isGstr1Role  = (role === 'gstr1');
        const mtrLoaded    = upAmazonDone.b2c || upAmazonDone.b2b;
        if (isGstr1Role && mtrLoaded) {
            upToast('Aap pehle hi MTR B2C/B2B report upload kar chuke hain — GSTR-1 file isi data ka duplicate hai. Dono ek sath upload nahi ho sakte.', 'error');
            return;
        }
        if (!isGstr1Role && upAmazonDone.gstr1) {
            upToast('Aap pehle hi GSTR-1 file upload kar chuke hain — MTR report isi data ka duplicate hai. Dono ek sath upload nahi ho sakte.', 'error');
            return;
        }
    }

    const slot  = document.getElementById('mfs-amz-' + role);
    const icon  = document.getElementById('amzicon-' + role);
    const nameE = document.getElementById('amzname-' + role);
    const statE = document.getElementById('amzstat-' + role);

    icon.innerHTML    = '<span class="up-spin" style="color:var(--teal)"></span>';
    nameE.textContent = file.name;
    statE.textContent = 'Parsing…';
    statE.className   = 'up-mfs-stat';
    slot.className    = 'up-mfs-slot';

    const roleLabel = role === 'gstr1' ? 'GSTR-1 Ready' : role === 'stock' ? 'Stock Transfer' : role.toUpperCase();
    upShowOverlay(file.name, 'Parsing Amazon ' + roleLabel + ' report…');

    try {
        const result = await upParseFile(file, 'amazon-' + role, upActivePeriod(), role); // ← uses active month
        const key = upDataKey('amazon-' + role); // ← NEW composite key
        upParsedData[key] = result.transactions.map(t => ({ ...t, period: upActivePeriod() }));
        upFileNames[key]  = file.name; // ← NEW
        upAmazonDone[role]   = true;
        icon.innerHTML       = '<i class="bi bi-check-circle-fill" style="color:#007050"></i>';
        statE.textContent    = '✓ ' + result.transactions_count + ' transactions';
        statE.className      = 'up-mfs-stat ok';
        slot.className        = 'up-mfs-slot loaded';
    } catch(err) {
        delete upParsedData[upDataKey('amazon-' + role)];
        delete upFileNames[upDataKey('amazon-' + role)]; // ← NEW
        upAmazonDone[role] = false;
        icon.innerHTML     = '<i class="bi bi-exclamation-circle-fill" style="color:#ef4444"></i>';
        statE.textContent  = '✗ ' + err.message;
        statE.className    = 'up-mfs-stat err';
        slot.className     = 'up-mfs-slot error';
        if (err.gstin_error) upShowGstinMismatchModal(err.file_gstin, err.client_gstin);
    } finally {
        upHideOverlay();
        upSyncAmazonSlotState();
    }
    document.getElementById('btnAmazonSubmit').disabled = !(upAmazonDone.gstr1 || upAmazonDone.b2c || upAmazonDone.b2b || upAmazonDone.stock);
}

function upSubmitAmazon() {
    if (!upAmazonDone.gstr1 && !upAmazonDone.b2c && !upAmazonDone.b2b && !upAmazonDone.stock) {
        upToast('Upload at least one file — GSTR-1 Ready, MTR B2C, MTR B2B, or Stock Transfer.', 'error');
        return;
    }
    const gstrTx = (upParsedData[upDataKey('amazon-gstr1')] || []).reduce((s,t) => s + (t.taxable_value||0), 0);
    const b2cTx  = (upParsedData[upDataKey('amazon-b2c')]   || []).reduce((s,t) => s + (t.taxable_value||0), 0);
    const b2bTx  = (upParsedData[upDataKey('amazon-b2b')]   || []).reduce((s,t) => s + (t.taxable_value||0), 0);
    const stockTx = (upParsedData[upDataKey('amazon-stock')] || []).reduce((s,t) => s + (t.taxable_value||0), 0);
    const net    = gstrTx + b2cTx + b2bTx + stockTx;
    const gstrN  = (upParsedData[upDataKey('amazon-gstr1')] || []).length;
    const b2cN   = (upParsedData[upDataKey('amazon-b2c')]   || []).length;
    const b2bN   = (upParsedData[upDataKey('amazon-b2b')]   || []).length;
    const stockN = (upParsedData[upDataKey('amazon-stock')] || []).length;

    const btn  = document.getElementById('btn-amazon');
    const fs   = document.getElementById('fs-amazon');
    const row  = document.getElementById('row-amazon');
    const amtE = document.getElementById('amt-amazon');

    btn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Loaded';
    btn.classList.add('loaded');

    let statusParts = [];
    if (upAmazonDone.gstr1) statusParts.push('GSTR-1: ' + gstrN);
    if (upAmazonDone.b2c)   statusParts.push('B2C: ' + b2cN);
    if (upAmazonDone.b2b)   statusParts.push('B2B: ' + b2bN);
    if (upAmazonDone.stock) statusParts.push('Stock Transfer: ' + stockN);
    fs.textContent = '✓ ' + statusParts.join(' | ') + (UP_FREQUENCY === 'quarterly' ? ' (' + upActivePeriod() + ')' : '');
    fs.className   = 'up-file-status show ok';
    row.classList.add('has-file'); row.classList.remove('has-error');

    // ← NEW: amount total ab amazon ke SIRF ACTIVE period/quarter ka sum hai
    const allAmzKeys = upScopeKeys().filter(k => k.split('__')[0].startsWith('amazon'));
    const totalNet   = allAmzKeys.reduce((s,k) => s + upParsedData[k].reduce((a,t)=>a+(t.taxable_value||0),0), 0);
    amtE.textContent = '₹' + Math.abs(totalNet).toLocaleString('en-IN', { minimumFractionDigits:2, maximumFractionDigits:2 });
    amtE.classList.add('active');

    if (upHsnOpen) upBuildHsnGrid();
    upCheckStatus();
    if (UP_FREQUENCY === 'quarterly') upRenderQtrTabs();
    upCloseModal('amazonModal');
    upToast('Amazon loaded — ' + statusParts.join(', '), 'success');
}

// ── Meesho Modal ───────────────────────────────────────────────────────────────
function upToggleGstinEdit() {
    const w = document.getElementById('upGstinEditWrap');
    w.style.display = (w.style.display === 'none' || !w.style.display) ? 'block' : 'none';
}

async function upHandleMeeshoFile(role, input) {
    const file = input.files[0];
    input.value = '';
    if (!file) return;

    const slot  = document.getElementById('mfs-'   + role);
    const icon  = document.getElementById('micon-' + role);
    const nameE = document.getElementById('mname-' + role);
    const statE = document.getElementById('mstat-' + role);

    icon.innerHTML    = '<span class="up-spin" style="color:var(--teal)"></span>';
    nameE.textContent = file.name;
    statE.textContent = 'Parsing…';
    statE.className   = 'up-mfs-stat';
    slot.className    = 'up-mfs-slot';

    upShowOverlay(file.name, 'Parsing Meesho ' + role + ' sheet…');

    try {
        const result = await upParseFile(file, 'meesho', upActivePeriod(), role);
        const key = upDataKey('meesho-' + role); // ← NEW composite key
        upParsedData[key] = (result.transactions || []).map(t => ({ ...t, period: upActivePeriod() }));
        upFileNames[key]  = file.name; // ← NEW
        upMeeshoDone[role]    = true;
        icon.innerHTML        = '<i class="bi bi-check-circle-fill" style="color:#007050"></i>';
        statE.textContent     = (role === 'invoice')
            ? '✓ ' + (result.lookup_count ?? 0) + ' invoice numbers matched'
            : '✓ ' + result.transactions_count + ' transactions';
        statE.className       = 'up-mfs-stat ok';
        slot.className        = 'up-mfs-slot loaded';
    } catch(err) {
        delete upParsedData[upDataKey('meesho-' + role)];
        delete upFileNames[upDataKey('meesho-' + role)]; // ← NEW
        upMeeshoDone[role] = false;
        icon.innerHTML     = '<i class="bi bi-exclamation-circle-fill" style="color:#ef4444"></i>';
        statE.textContent  = '✗ ' + err.message;
        statE.className    = 'up-mfs-stat err';
        slot.className     = 'up-mfs-slot error';
        if (err.gstin_error) upShowGstinMismatchModal(err.file_gstin, err.client_gstin);
    } finally {
        upHideOverlay();
    }
    document.getElementById('btnMeeshoSubmit').disabled = !upMeeshoDone.sales;
}

function upSubmitMeesho() {
    if (!upMeeshoDone.sales) { upToast('TCS Sales Sheet is required.', 'error'); return; }
    const salesTx = (upParsedData[upDataKey('meesho-sales')]  || []).reduce((s,t) => s + (t.taxable_value||0), 0);
    const retTx   = (upParsedData[upDataKey('meesho-return')] || []).reduce((s,t) => s + (t.taxable_value||0), 0);
    const saleN   = (upParsedData[upDataKey('meesho-sales')]  || []).length;
    const retN    = (upParsedData[upDataKey('meesho-return')] || []).length;

    const btn  = document.getElementById('btn-meesho');
    const fs   = document.getElementById('fs-meesho');
    const row  = document.getElementById('row-meesho');
    const amtE = document.getElementById('amt-meesho');
    const tcs  = document.getElementById('tcs-meesho');

    btn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Loaded';
    btn.classList.add('loaded');
    fs.textContent = '✓ Sales: ' + saleN + ' | Return: ' + retN + (upMeeshoDone.invoice ? ' | Invoice: ✓' : '')
                    + (UP_FREQUENCY === 'quarterly' ? ' (' + upActivePeriod() + ')' : '');
    fs.className   = 'up-file-status show ok';
    row.classList.add('has-file'); row.classList.remove('has-error');

    // ← NEW: total = meesho ke SIRF ACTIVE period/quarter ka sum
    const allKeys  = upScopeKeys().filter(k => k.split('__')[0].startsWith('meesho'));
    const totalNet = allKeys.reduce((s,k) => s + upParsedData[k].reduce((a,t)=>a+(t.taxable_value||0),0), 0);
    amtE.childNodes[0].textContent = '₹' + Math.abs(totalNet).toLocaleString('en-IN', { minimumFractionDigits:2, maximumFractionDigits:2 });
    amtE.classList.add('active');
    tcs.style.display = 'inline-block';

    if (upHsnOpen) upBuildHsnGrid();
    upCheckStatus();
    if (UP_FREQUENCY === 'quarterly') upRenderQtrTabs();
    upCloseModal('meeshoModal');
    upToast('Meesho loaded — Sales: ' + saleN + ', Return: ' + retN, 'success');
}

let upMyntraDone = { packed: false, rt: false, rto: false };

// at function added from here 

let upMyntraCalcBase   = null;
let upCalcBaseResolver = null;

function upAskCalcBase(options) {
    return new Promise((resolve) => {
        const fmt = (v) => '₹' + Math.abs(v || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        const choice = prompt(
            `Two calculation-based columns have been found:\n\nbase_value: ${fmt(options?.base_value)}\nbase_amount: ${fmt(options?.base_amount)}\n\nType 'base_value' or 'base_amount':`
        );
        resolve(choice === 'base_amount' ? 'base_amount' : (choice === 'base_value' ? 'base_value' : null));
    });
}

async function upHandleMyntraFile(role, input) {
    const file = input.files[0];
    input.value = '';
    if (!file) return;

    const slot  = document.getElementById('mfs-myn-'  + role);
    const icon  = document.getElementById('mynicon-'  + role);
    const nameE = document.getElementById('mynname-'  + role);
    const statE = document.getElementById('mynstat-'  + role);

    icon.innerHTML    = '<span class="up-spin" style="color:var(--teal)"></span>';
    nameE.textContent = file.name;
    statE.textContent = 'Parsing…';
    statE.className   = 'up-mfs-stat';
    slot.className    = 'up-mfs-slot';

    upShowOverlay(file.name, 'Parsing Myntra ' + role.toUpperCase() + ' sheet…');
    await upCheckPeriodOnce();

    try {
        let result = await upParseFile(file, 'myntra', upActivePeriod(), role, upMyntraCalcBase);

        if (result.needs_calc_base) {
            statE.textContent = 'Waiting for column choice…';
            const chosen = await upAskCalcBase(result.options);
            if (!chosen) {
                throw new Error('Calculation base selection cancelled.');
            }
            upMyntraCalcBase = chosen;
            upShowOverlay(file.name, 'Parsing Myntra ' + role.toUpperCase() + ' sheet…');
            result = await upParseFile(file, 'myntra', upActivePeriod(), role, chosen);
        }

        const key = upDataKey('myntra-' + role);
        upParsedData[key] = result.transactions.map(t => ({ ...t, period: upActivePeriod() }));
        upFileNames[key]  = file.name;
        upMyntraDone[role] = true;
        icon.innerHTML     = '<i class="bi bi-check-circle-fill" style="color:#007050"></i>';
        statE.textContent  = '✓ ' + result.transactions_count + ' transactions';
        statE.className    = 'up-mfs-stat ok';
        slot.className     = 'up-mfs-slot loaded';
    } catch(err) {
        delete upParsedData[upDataKey('myntra-' + role)];
        delete upFileNames[upDataKey('myntra-' + role)];
        upMyntraDone[role] = false;
        icon.innerHTML     = '<i class="bi bi-exclamation-circle-fill" style="color:#ef4444"></i>';
        statE.textContent  = '✗ ' + err.message;
        statE.className    = 'up-mfs-stat err';
        slot.className     = 'up-mfs-slot error';
        if (err.gstin_error) upShowGstinMismatchModal(err.file_gstin, err.client_gstin);
    } finally {
        upHideOverlay();
    }
    document.getElementById('btnMyntraSubmit').disabled = !(upMyntraDone.packed || upMyntraDone.rt || upMyntraDone.rto);
}



function upSubmitMyntra() {
    if (!upMyntraDone.packed && !upMyntraDone.rt && !upMyntraDone.rto) {
        upToast('Upload at least one file — Packed, RT, ya RTO sheet.', 'error');
        return;
    }
    const packedN = (upParsedData[upDataKey('myntra-packed')] || []).length;
    const rtN     = (upParsedData[upDataKey('myntra-rt')]     || []).length;
    const rtoN    = (upParsedData[upDataKey('myntra-rto')]    || []).length;

    const btn  = document.getElementById('btn-myntra');
    const fs   = document.getElementById('fs-myntra');
    const row  = document.getElementById('row-myntra');
    const amtE = document.getElementById('amt-myntra');

    btn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Loaded';
    btn.classList.add('loaded');

    let parts = [];
    if (upMyntraDone.packed) parts.push('Packed: ' + packedN);
    if (upMyntraDone.rt)     parts.push('RT: '     + rtN);
    if (upMyntraDone.rto)    parts.push('RTO: '    + rtoN);
    fs.textContent = '✓ ' + parts.join(' | ') + (UP_FREQUENCY === 'quarterly' ? ' (' + upActivePeriod() + ')' : '');
    fs.className   = 'up-file-status show ok';
    row.classList.add('has-file'); row.classList.remove('has-error');

    // ← FIX: amount total ab myntra ke SIRF ACTIVE period/quarter ka sum hai
    const allKeys  = upScopeKeys().filter(k => k.split('__')[0].startsWith('myntra'));
    const totalNet = allKeys.reduce((s,k) => s + upParsedData[k].reduce((a,t)=>a+(t.taxable_value||0),0), 0);
    amtE.textContent = totalNet === 0 ? '0' : '₹' + Math.abs(totalNet).toLocaleString('en-IN', { minimumFractionDigits:2, maximumFractionDigits:2 });
    amtE.classList.toggle('active', totalNet !== 0);

    if (upHsnOpen) upBuildHsnGrid();
    upCheckStatus();                                  // ← FIX: yeh missing tha — Compute button enable karta hai
    if (UP_FREQUENCY === 'quarterly') upRenderQtrTabs();
    upCloseModal('myntraModal');
    upToast('Myntra loaded — ' + parts.join(', '), 'success');
}

// ── GlowRoad Modal ─────────────────────────────────────────────────────────────
async function upHandleGlowRoadFile(role, input) {
    const file = input.files[0];
    input.value = '';
    if (!file) return;

    const slot  = document.getElementById('mfs-gr-'  + role);
    const icon  = document.getElementById('gricon-'  + role);
    const nameE = document.getElementById('grname-'  + role);
    const statE = document.getElementById('grstat-'  + role);

    icon.innerHTML    = '<span class="up-spin" style="color:var(--teal)"></span>';
    nameE.textContent = file.name;
    statE.textContent = 'Parsing…';
    statE.className   = 'up-mfs-stat';
    slot.className    = 'up-mfs-slot';

    upShowOverlay(file.name, 'Parsing GlowRoad ' + role.toUpperCase() + ' report…');

    try {
        const result = await upParseFile(file, 'glowroad-' + role, upActivePeriod(), role);
        const key = upDataKey('glowroad-' + role); // ← NEW composite key
        upParsedData[key] = result.transactions.map(t => ({ ...t, period: upActivePeriod() }));
        upFileNames[key]  = file.name; // ← NEW
        upGlowRoadDone[role]  = true;
        icon.innerHTML        = '<i class="bi bi-check-circle-fill" style="color:#007050"></i>';
        statE.textContent     = '✓ ' + result.transactions_count + ' transactions';
        statE.className       = 'up-mfs-stat ok';
        slot.className        = 'up-mfs-slot loaded';
    } catch(err) {
        delete upParsedData[upDataKey('glowroad-' + role)];
        delete upFileNames[upDataKey('glowroad-' + role)]; // ← NEW
        upGlowRoadDone[role] = false;
        icon.innerHTML       = '<i class="bi bi-exclamation-circle-fill" style="color:#ef4444"></i>';
        statE.textContent    = '✗ ' + err.message;
        statE.className      = 'up-mfs-stat err';
        slot.className       = 'up-mfs-slot error';
        if (err.gstin_error) upShowGstinMismatchModal(err.file_gstin, err.client_gstin);
    } finally {
        upHideOverlay();
    }
    document.getElementById('btnGlowRoadSubmit').disabled = !upGlowRoadDone.b2c;
}

function upSubmitGlowRoad() {
    if (!upGlowRoadDone.b2c) { upToast('B2C TCS Report is required.', 'error'); return; }
    const b2cN  = (upParsedData[upDataKey('glowroad-b2c')] || []).length;
    const b2bN  = (upParsedData[upDataKey('glowroad-b2b')] || []).length;

    const btn  = document.getElementById('btn-glowroad');
    const fs   = document.getElementById('fs-glowroad');
    const row  = document.getElementById('row-glowroad');
    const amtE = document.getElementById('amt-glowroad');
    const tcs  = document.getElementById('tcs-glowroad');

    btn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Loaded';
    btn.classList.add('loaded');
    fs.textContent = '✓ B2C: ' + b2cN + (upGlowRoadDone.b2b ? ' | B2B: ' + b2bN : '')
                    + (UP_FREQUENCY === 'quarterly' ? ' (' + upActivePeriod() + ')' : '');
    fs.className   = 'up-file-status show ok';
    row.classList.add('has-file'); row.classList.remove('has-error');

    const allKeys  = upScopeKeys().filter(k => k.split('__')[0].startsWith('glowroad')); // ← FIX: scoped to active period
    const totalNet = allKeys.reduce((s,k) => s + upParsedData[k].reduce((a,t)=>a+(t.taxable_value||0),0), 0);
    amtE.childNodes[0].textContent = '₹' + Math.abs(totalNet).toLocaleString('en-IN', { minimumFractionDigits:2, maximumFractionDigits:2 });
    amtE.classList.add('active');
    tcs.style.display = 'inline-block';

    if (upHsnOpen) upBuildHsnGrid();
    upCheckStatus();
    if (UP_FREQUENCY === 'quarterly') upRenderQtrTabs();
    upCloseModal('glowRoadModal');
    upToast('GlowRoad loaded — B2C: ' + b2cN + (upGlowRoadDone.b2b ? ', B2B: ' + b2bN : ''), 'success');
}

// ── Status Check ───────────────────────────────────────────────────────────────
function upCheckStatus() {
    const all   = upScopeKeys().flatMap(k => upParsedData[k]); // ← FIX: sirf active period ka data
    const btn   = document.getElementById('btnCompute');
    const stat  = document.getElementById('upComputeStatus');
    const badge = document.getElementById('upStatusBadge');
    const p     = UP_FREQUENCY === 'quarterly' ? upFilingPeriodKey() : upActivePeriod();
    const ok    = all.length > 0 && (!upPeriodHasDup || upPeriodAction);
    btn.disabled = !ok;
    if (!all.length) {
        stat.textContent  = 'Period: ' + p + ' — upload at least one platform file';
        badge.className   = 'up-status-badge';
        badge.innerHTML   = '<i class="bi bi-circle"></i> Idle';
    } else {
        const note = upPeriodHasDup
            ? ' (' + (upPeriodAction === 'replace' ? 'replacing' : 'adding to') + ' existing)'
            : '';
        // ← NEW: "platform(s)" ab distinct base-platforms count karta hai, sirf active period ke liye
        const distinctPlatforms = new Set(upScopeKeys().map(k => k.split('__')[0].split('-')[0])); // ← FIX: scoped
        stat.textContent = all.length + ' transactions · ' + distinctPlatforms.size + ' platform(s) · ' + p + note;
        badge.className  = 'up-status-badge ready';
        badge.innerHTML  = '<i class="bi bi-check-circle-fill"></i> Ready';
    }
}

// ── Compute ────────────────────────────────────────────────────────────────────
 // ── Compute ────────────────────────────────────────────────────────────────────
async function upStartCompute() {
    const btn   = document.getElementById('btnCompute');
    const pWrap = document.getElementById('upProgressWrap');
    const pFill = document.getElementById('upProgressFill');
    const log   = document.getElementById('upStepLog');
    const badge = document.getElementById('upStatusBadge');
    const p     = upActivePeriod();
    const filingPeriod = UP_FREQUENCY === 'quarterly' ? upFilingPeriodKey() : null; // ← NEW
    const all   = upScopeKeys().flatMap(k => upParsedData[k]); // ← FIX: sirf active period/quarter ka data compute ho

    if (!p.match(/^\d{2}\/\d{4}$/)) { upToast('Invalid period.', 'error'); return; }
    if (!all.length) { upToast('No files uploaded.', 'error'); return; }

    // ← NEW: quarterly mein warn karo agar kam se kam 1 month mein bhi kuch upload nahi hua
    if (UP_FREQUENCY === 'quarterly') {
        const monthsWithData = new Set(all.map(t => t.period));
        const missing = upQtrMonths.filter(m => !monthsWithData.has(m));
        if (missing.length === upQtrMonths.length) {
            upToast('Kam se kam ek month ka data upload karein.', 'error');
            return;
        }
        // missing months ke liye sirf informational — block nahi karte, kyunki kisi month
        // mein genuinely zero sales ho sakti hai.
    }

    btn.disabled = true;
    pWrap.style.display = 'block';
    pFill.style.width   = '5%';
    badge.className  = 'up-status-badge processing';
    badge.innerHTML  = '<span class="up-spin"></span> Processing…';
    upShowOverlay('', 'Computing B2CS grouping…');

    try {
        log.textContent   = 'Saving ' + all.length + ' transactions…';
        pFill.style.width = '40%';

        const sr = await fetch(UP_SAVE_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': UP_CSRF() },
            body: JSON.stringify({
                batch_id: UP_BATCH_ID,
                period: p,
                filing_period: filingPeriod, // ← NEW
                replace_period: upPeriodHasDup && upPeriodAction === 'replace',
                transactions: all
            })
        });
        const sd = await sr.json();
        if (!sr.ok) throw new Error(sd.error || 'Save failed');

        // ← FIX: server ne quarterly client ke liye batch_id reuse kiya ho sakta hai
        // (agar us filing_period ka batch pehle se DB me exist karta hai — dekho
        // processTransactions() ka "ENSURE ALL MONTHS OF A QUARTER SHARE ONE
        // batch_id" block). Us case me transactions DB me EXISTING batch_id ke
        // against save hote hain, na ki frontend ke UP_BATCH_ID ke against.
        // Isliye compute call ko bhi wahi effective batch_id bhejna zaroori hai,
        // warna "No transactions found for batch ..." error aayega.
        const effectiveBatchId = sd.batch_id || UP_BATCH_ID;

        if (sd.duplicates_removed > 0) {
            upToast(sd.duplicates_removed + ' duplicate Myntra rows skipped (already counted from another uploaded file).', 'success');
        }

        log.textContent   = 'Computing B2CS grouping…';
        pFill.style.width = '80%';

       const cr = await fetch(UP_COMPUTE_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': UP_CSRF() },
            body: JSON.stringify({
                batch_id: effectiveBatchId, // ← FIX: was UP_BATCH_ID
                doc_issue_override: upDocIssueOverride,
                hsn_override: upHsnOverrideData
            })
        });

        const cd = await cr.json();
        if (!cr.ok) throw new Error(cd.error || 'Compute failed');

        pFill.style.width = '100%';
        log.textContent   = '✓ Done! Redirecting…';
        upHideOverlay();
        upToast('B2CS computed for ' + (filingPeriod || p) + '. Redirecting…', 'success');
        setTimeout(() => { window.location.href = cd.redirect; }, 900);

    } catch(err) {
        upHideOverlay();
        upToast('Error: ' + err.message, 'error');
        log.textContent  = '✗ ' + err.message;
        badge.className  = 'up-status-badge';
        badge.innerHTML  = '<i class="bi bi-x-circle"></i> Failed';
        btn.disabled     = false;
        setTimeout(() => { pWrap.style.display = 'none'; }, 5000);
    }
}

// ── Toast ──────────────────────────────────────────────────────────────────────
function upToast(msg, type) {
    const wrap = document.getElementById('upToastWrap');
    const el   = document.createElement('div');
    el.className   = 'up-toast ' + type;
    el.textContent = msg;
    wrap.appendChild(el);
    setTimeout(() => el.remove(), 4000);
}

// ── Init ───────────────────────────────────────────────────────────────────────
(function upInit() {
    const prev = new Date(new Date().getFullYear(), new Date().getMonth() - 1, 1);
    const m    = String(prev.getMonth() + 1).padStart(2, '0');
    const y    = prev.getFullYear();

    if (UP_FREQUENCY === 'quarterly') {
        const now = new Date();
        const curMonth = now.getMonth() + 1, curYear = now.getFullYear();
        let quarter, fyStart;
        if ([4,5,6].includes(curMonth))      { quarter = 1; fyStart = curYear; }
        else if ([7,8,9].includes(curMonth)) { quarter = 2; fyStart = curYear; }
        else if ([10,11,12].includes(curMonth)) { quarter = 3; fyStart = curYear; }
        else { quarter = 4; fyStart = curYear - 1; }

        document.getElementById('qtrQuarter').value = quarter;
        document.getElementById('qtrFyStart').value = fyStart;
        upRenderQtrTabs();

        const fk = upFilingPeriodKey();
        document.getElementById('upPeriodText').textContent = fk;
        if (UP_EXISTING.includes(fk)) upShowDup(fk, null);
    } else {
        document.getElementById('periodMonth').value = m;
        document.getElementById('periodYear').value  = y;
        document.getElementById('upPeriodText').textContent = m + '/' + y;
        if (UP_EXISTING.includes(m + '/' + y)) upShowDup(m + '/' + y, null);
    }

    upCheckStatus();
    upFetchPeriodStatus(); // ← NEW: page load par bhi default (active) period ka existing-data status DB se laao
})();
</script>
@endsection