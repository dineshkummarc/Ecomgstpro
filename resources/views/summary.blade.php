@extends('layouts.gstr-main')

@section('title', 'GSTR-1 Summary Report')

@section('styles')
<style>
/* ── Reset ── */
*, *::before, *::after { box-sizing: border-box; }

/* ── KPI Row ── */
.kpi-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-bottom: 20px;
}
.kpi-card {
    background: white;
    border: 1px solid #e8edf5;
    border-radius: 12px;
    padding: 16px;
    position: relative;
    overflow: hidden;
}
.kpi-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    border-radius: 12px 12px 0 0;
}
.kpi-card.teal::before  { background: #00c98d; }
.kpi-card.sky::before   { background: #00aaff; }
.kpi-card.amber::before { background: #f59e0b; }
.kpi-card.rose::before  { background: #f43f5e; }
.kpi-icon {
    width: 36px; height: 36px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px; margin-bottom: 10px;
}
.kpi-card.teal  .kpi-icon { background: rgba(0,201,141,.1); color: #00c98d; }
.kpi-card.sky   .kpi-icon { background: rgba(0,170,255,.1); color: #00aaff; }
.kpi-card.amber .kpi-icon { background: rgba(245,158,11,.1); color: #f59e0b; }
.kpi-card.rose  .kpi-icon { background: rgba(244,63,94,.1);  color: #f43f5e; }
.kpi-value {
    font-family: 'Syne', sans-serif;
    font-weight: 800; font-size: 22px;
    color: var(--navy); line-height: 1;
}
.kpi-label { font-size: 11px; color: #94a3b8; text-transform: uppercase; letter-spacing: .5px; margin-top: 4px; }
.kpi-sub   { font-size: 11px; color: #6b7a99; margin-top: 2px; }

/* ── Header ── */
.summary-header {
    background: linear-gradient(135deg, rgba(0,201,141,.08), rgba(0,170,255,.06));
    border: 1.5px solid rgba(0,201,141,.25);
    border-radius: 16px; padding: 20px 24px;
    margin-bottom: 20px;
    display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap;
}
.client-name  { font-family: 'Syne', sans-serif; font-weight: 800; font-size: 18px; color: var(--navy); }
.client-gstin { font-family: 'Courier New', monospace; font-size: 12px; color: #94a3b8; }
.period-info  { font-size: 12px; color: #6b7a99; margin-top: 4px; }
.dl-btns      { display: flex; gap: 8px; flex-wrap: wrap; }
.btn-dl {
    padding: 8px 14px; border-radius: 10px; font-size: 12px; font-weight: 600;
    border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: all .2s;
}
.btn-dl.csv   { background: #667eea; color: white; }
.btn-dl.excel { background: #4ade80; color: white; }
.btn-dl.json  { background: var(--navy); color: white; }
.btn-dl.doc   { background: #f59e0b; color: white; }
.btn-dl:hover { transform: translateY(-2px); opacity: .9; }

/* ── Combined-total verification strip ── */
.combo-verify {
    display: flex; align-items: center; gap: 18px; flex-wrap: wrap;
    background: rgba(0,201,141,.06); border: 1.5px dashed rgba(0,201,141,.35);
    border-radius: 12px; padding: 10px 16px; margin-bottom: 20px; font-size: 12px; color: #005a3c;
}
.combo-verify .cv-item { display: flex; align-items: center; gap: 6px; }
.combo-verify .cv-label { color: #6b7a99; }
.combo-verify .cv-val   { font-family: 'Courier New', monospace; font-weight: 700; }
.combo-verify .cv-total { font-weight: 800; font-size: 13px; }
.combo-verify .cv-sep   { color: #94a3b8; }

/* ── Tabs ── */
.report-tabs {
    display: flex; gap: 4px;
    background: #f0f4ff; border-radius: 14px; padding: 4px;
    margin-bottom: 20px; overflow-x: auto;
}
.report-tab {
    padding: 9px 16px; border-radius: 11px; font-weight: 700; font-size: 13px;
    cursor: pointer; border: none; background: transparent; color: #6b7a99; white-space: nowrap; transition: all .2s;
}
.report-tab.active { background: white; color: var(--navy); box-shadow: 0 2px 10px rgba(0,0,0,.08); }
.tab-content { display: none; }
.tab-content.active { display: block; }

/* ── Cards ── */
.table-card {
    background: white; border: 1px solid #e8edf5;
    border-radius: 16px; overflow: hidden; margin-bottom: 20px;
}
.table-header {
    background: #f7faff; border-bottom: 1px solid #e8edf5;
    padding: 14px 20px; display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: 8px;
}
.table-title { font-family: 'Syne', sans-serif; font-weight: 700; font-size: 14px; color: var(--navy); margin: 0; }
.table-scroll { overflow-x: auto; }

/* ── Tables ── */
table { width: 100%; border-collapse: collapse; font-size: 13px; }
table thead th {
    background: #f7faff; padding: 10px 12px; font-weight: 700; font-size: 11px;
    letter-spacing: .5px; text-transform: uppercase; color: #6b7a99;
    border-bottom: 1px solid #e8edf5; text-align: left; white-space: nowrap;
}
table tbody td { padding: 9px 12px; border-bottom: 1px solid #f0f4ff; color: var(--navy); }
table tbody tr:hover { background: #f9fbff; }
table tbody tr:last-child td { border-bottom: none; }
table tfoot td {
    font-weight: 700; background: #f7faff;
    border-top: 2px solid #e8edf5; color: var(--navy); padding: 9px 12px;
}
.num { text-align: right; font-family: 'Courier New', monospace; font-size: 12px; }
.badge-inter { display: inline-block; font-size: 10px; font-weight: 700; padding: 2px 7px; border-radius: 5px; background: #dbeafe; color: #1d4ed8; }
.badge-intra { display: inline-block; font-size: 10px; font-weight: 700; padding: 2px 7px; border-radius: 5px; background: #dcfce7; color: #15803d; }
.badge-edited { display: inline-block; font-size: 9px; font-weight: 700; padding: 1px 5px; border-radius: 4px; background: #fef3c7; color: #92400e; margin-left: 4px; }
.badge-cancel { display: inline-block; font-size: 10px; font-weight: 700; padding: 2px 7px; border-radius: 5px; background: #fee2e2; color: #b91c1c; }
.badge-net    { display: inline-block; font-size: 10px; font-weight: 700; padding: 2px 7px; border-radius: 5px; background: #dcfce7; color: #15803d; }

/* ── Edit button (hover) ── */
.edit-trigger { opacity: 0; transition: opacity .15s; }
tr:hover .edit-trigger { opacity: 1; }
.btn-edit {
    padding: 3px 8px; font-size: 10px; font-weight: 600; border-radius: 6px;
    border: 1px solid #cbd5e1; background: white; color: #475569; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;
}
.btn-edit:hover { background: #f0f4ff; border-color: #667eea; color: #667eea; }

/* ── View more ── */
.view-more-row td {
    text-align: center; padding: 10px;
    background: #f7faff; border-top: 1px solid #e8edf5;
}
.btn-view-more {
    background: none; border: 1px solid #cbd5e1; color: #6b7a99;
    padding: 6px 20px; border-radius: 8px; font-size: 12px; cursor: pointer; transition: all .15s;
}
.btn-view-more:hover { background: #f0f4ff; color: var(--navy); border-color: #667eea; }

/* ── Charts grid ── */
.charts-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px; }
.chart-card  { background: white; border: 1px solid #e8edf5; border-radius: 16px; padding: 16px; }
.chart-card-full { background: white; border: 1px solid #e8edf5; border-radius: 16px; padding: 16px; margin-bottom: 16px; }
.chart-title { font-family: 'Syne', sans-serif; font-weight: 700; font-size: 13px; color: var(--navy); margin-bottom: 12px; }
.chart-legend { display: flex; gap: 16px; flex-wrap: wrap; margin-bottom: 8px; font-size: 11px; color: #6b7a99; }
.leg-sq { width: 10px; height: 10px; border-radius: 2px; display: inline-block; margin-right: 4px; vertical-align: middle; }

/* ── JSON box ── */
.json-pre {
    background: var(--navy); border-radius: 12px; padding: 16px;
    font-family: 'Courier New', monospace; font-size: 11px; line-height: 1.7;
    color: #8be9fd; max-height: 450px; overflow-y: auto;
    white-space: pre-wrap; word-break: break-all;
}
.btn-copy {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 14px; border-radius: 8px; font-size: 12px; font-weight: 600;
    border: 1px solid #e2e8f0; background: white; color: #475569; cursor: pointer;
}
.btn-copy:hover { background: #f8fafc; }

/* ── Doc Issue summary card ── */
.doc-summary-strip { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 16px; }
.doc-mini-card { background: #f7faff; border: 1px solid #e8edf5; border-radius: 12px; padding: 12px 14px; text-align: center; }
.doc-mini-val   { font-family: 'Syne', sans-serif; font-weight: 800; font-size: 18px; color: var(--navy); }
.doc-mini-label { font-size: 10px; color: #94a3b8; text-transform: uppercase; letter-spacing: .4px; margin-top: 2px; }
.doc-empty { text-align: center; padding: 30px; color: #94a3b8; font-size: 12px; }
.doc-empty i { font-size: 26px; display: block; margin-bottom: 8px; color: #cbd5e1; }

/* ── Modal ── */
.modal-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,.45); z-index: 9999;
    align-items: center; justify-content: center;
}
.modal-overlay.open { display: flex; }
.modal-box {
    background: white; border-radius: 16px; padding: 28px;
    min-width: 400px; max-width: 92vw; box-shadow: 0 20px 60px rgba(0,0,0,.2);
}
.modal-box h3 { font-family: 'Syne', sans-serif; font-weight: 800; font-size: 16px; color: var(--navy); margin-bottom: 4px; }
.modal-box .modal-sub { font-size: 12px; color: #94a3b8; margin-bottom: 20px; }
.form-group { margin-bottom: 14px; }
.form-group label { display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 5px; }
.form-group input {
    width: 100%; padding: 9px 12px; border: 1.5px solid #e2e8f0; border-radius: 8px;
    font-size: 13px; color: var(--navy); background: #f8fafc; outline: none; transition: border .15s;
}
.form-group input:focus { border-color: #667eea; background: white; }
.form-row { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; }
.modal-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px; }
.btn-save   { padding: 9px 20px; background: #667eea; color: white; border: none; border-radius: 9px; font-size: 13px; font-weight: 700; cursor: pointer; }
.btn-save:hover { background: #5a6fd6; }
.btn-cancel { padding: 9px 16px; background: none; border: 1.5px solid #e2e8f0; color: #475569; border-radius: 9px; font-size: 13px; cursor: pointer; }

@media (max-width: 768px) {
    .kpi-row { grid-template-columns: repeat(2, 1fr); }
    .doc-summary-strip { grid-template-columns: repeat(2, 1fr); }
    .charts-grid { grid-template-columns: 1fr; }
    .form-row { grid-template-columns: 1fr; }
    .summary-header { flex-direction: column; }
}
</style>
@endsection

@section('content')
@php
    $stateMap = [
        "01"=>"Jammu & Kashmir","02"=>"Himachal Pradesh","03"=>"Punjab","04"=>"Chandigarh",
        "05"=>"Uttarakhand","06"=>"Haryana","07"=>"Delhi","08"=>"Rajasthan",
        "09"=>"Uttar Pradesh","10"=>"Bihar","11"=>"Sikkim","12"=>"Arunachal Pradesh",
        "13"=>"Nagaland","14"=>"Manipur","15"=>"Mizoram","16"=>"Tripura",
        "17"=>"Meghalaya","18"=>"Assam","19"=>"West Bengal","20"=>"Jharkhand",
        "21"=>"Odisha","22"=>"Chhattisgarh","23"=>"Madhya Pradesh","24"=>"Gujarat",
        "26"=>"Dadra & Nagar Haveli","27"=>"Maharashtra","29"=>"Karnataka",
        "30"=>"Goa","31"=>"Lakshadweep","32"=>"Kerala","33"=>"Tamil Nadu",
        "34"=>"Puducherry","35"=>"Andaman & Nicobar","36"=>"Telangana",
        "37"=>"Andhra Pradesh","38"=>"Ladakh",
    ];
    $totTxval = array_sum(array_column($summary, 'txval'));
    $totIamt  = array_sum(array_column($summary, 'iamt'));
    $totCamt  = array_sum(array_column($summary, 'camt'));
    $totSamt  = array_sum(array_column($summary, 'samt'));
    $totTax   = $totIamt + $totCamt + $totSamt;
    $stateCount = count(array_unique(array_column($summary, 'pos')));

    // ── Doc Issued rollups ──
    $docIssue    = $docIssue ?? [];
    $docRows     = $docIssue['doc_det'][0]['docs'] ?? [];
    $docTotIssue = array_sum(array_column($docRows, 'totnum'));
    $docTotCancel= array_sum(array_column($docRows, 'cancel'));
    $docTotNet   = array_sum(array_column($docRows, 'net_issue'));

    // ── HSN rollups ──
    $hsnData   = $hsnData ?? ['hsn_b2b' => [], 'hsn_b2c' => []];
    $hsnB2B    = $hsnData['hsn_b2b'] ?? [];
    $hsnB2C    = $hsnData['hsn_b2c'] ?? [];

    // ── NEW: B2B rollups (for the B2B tab's grand total footer AND the
    // combined-total verification strip). B2CS tab already excludes valid-
    // GSTIN transactions on purpose (GST portal reports B2CS and B2B in
    // separate sections — see GstrSummaryController::computeSummary()).
    // This does NOT re-include B2B into the B2CS grand total; it simply
    // sums B2B on its own so the two totals can be shown side-by-side and
    // cross-checked against the total uploaded amount.
    $b2bTotTxval = 0; $b2bTotIamt = 0; $b2bTotCamt = 0; $b2bTotSamt = 0; $b2bTotVal = 0;
    foreach ($b2bData as $party) {
        foreach ($party['inv'] as $inv) {
            $itm = $inv['itms'][0]['itm_det'];
            $b2bTotTxval += $itm['txval'];
            $b2bTotIamt  += $itm['iamt'];
            $b2bTotCamt  += $itm['camt'] ?? 0;
            $b2bTotSamt  += $itm['samt'] ?? 0;
            $b2bTotVal   += $inv['val'];
        }
    }
    $b2bTotTax    = $b2bTotIamt + $b2bTotCamt + $b2bTotSamt;
    $combinedTxval = $totTxval + $b2bTotTxval;
@endphp

<div id="reportContent">

    {{-- ── Header ── --}}
    <div class="summary-header">
        <div>
            <div class="client-name">{{ $client['name'] }}</div>
            <div class="client-gstin">{{ $client['gstin'] }}</div>
            <div class="period-info">Period: {{ $period }} &nbsp;|&nbsp; Transactions: {{ $transactionCount }} &nbsp;|&nbsp; B2B: {{ $b2bCount }}</div>
        </div>
        {{-- <div class="dl-btns">
            <button class="btn-dl csv"   onclick="location.href='{{ route('gstr.summary.downloadCSV') }}'"><i class="bi bi-filetype-csv"></i> B2CS CSV</button>
            <button class="btn-dl excel" onclick="location.href='{{ route('gstr.summary.downloadSummaryExcel') }}'"><i class="bi bi-file-earmark-excel-fill"></i> Summary Excel</button>
            <button class="btn-dl excel" onclick="location.href='{{ route('gstr.summary.downloadDetailedExcel') }}'"><i class="bi bi-file-earmark-excel-fill"></i> Detail Excel</button>
            @if(!empty($docRows))
            <button class="btn-dl doc"   onclick="location.href='{{ route('gstr.summary.downloadDocIssueCSV') }}'"><i class="bi bi-file-earmark-text-fill"></i> Doc Issue CSV</button>
            @endif
            <button class="btn-dl json" onclick="openJsonExportModal()"><i class="bi bi-filetype-json"></i> JSON</button>
        </div> --}}


        <div class="dl-btns">
    <button class="btn-dl csv"   onclick="location.href='{{ route('gstr.summary.downloadCSV') }}'"><i class="bi bi-filetype-csv"></i> B2CS CSV</button>
    <button class="btn-dl excel" onclick="location.href='{{ route('gstr.summary.downloadSummaryExcel') }}'"><i class="bi bi-file-earmark-excel-fill"></i> Summary Excel</button>
    <button class="btn-dl excel" onclick="location.href='{{ route('gstr.summary.downloadDetailedExcel') }}'"><i class="bi bi-file-earmark-excel-fill"></i> Detail Excel</button>
    @if(!empty($docRows))
    <button class="btn-dl doc"   onclick="location.href='{{ route('gstr.summary.downloadDocIssueCSV') }}'"><i class="bi bi-file-earmark-text-fill"></i> Doc Issue CSV</button>
    @endif
    <button class="btn-dl csv"   onclick="location.href='{{ route('gstr.summary.downloadStateSummaryCSV') }}'"><i class="bi bi-geo-alt-fill"></i> State Summary CSV</button>
    <button class="btn-dl json" onclick="openJsonExportModal()"><i class="bi bi-filetype-json"></i> JSON</button>
</div>
    </div>

    {{-- ── NEW: Combined-total verification strip ──
         B2CS Grand Total excludes B2B by design (GST portal requirement —
         see computeSummary() in the controller). This strip lets you
         cross-check that nothing actually went missing: B2CS + B2B should
         always equal the total amount you saw at upload time. ── --}}
    <!-- <div class="combo-verify">
        <div class="cv-item"><span class="cv-label">B2CS taxable:</span> <span class="cv-val">₹{{ number_format($totTxval, 2) }}</span></div>
        <span class="cv-sep">+</span>
        <div class="cv-item"><span class="cv-label">B2B taxable:</span> <span class="cv-val">₹{{ number_format($b2bTotTxval, 2) }}</span></div>
        <span class="cv-sep">=</span>
        <div class="cv-item"><span class="cv-label">Combined total:</span> <span class="cv-val cv-total">₹{{ number_format($combinedTxval, 2) }}</span></div>
        <span style="color:#94a3b8;font-size:11px">— this should match the total amount shown at upload time. B2B is reported separately as required by the GST portal.</span>
    </div> -->

    {{-- ── KPI Cards ── --}}
    <div class="kpi-row">
        <div class="kpi-card teal">
            <div class="kpi-icon"><i class="bi bi-cash-stack"></i></div>
            <div class="kpi-value">₹{{ number_format($totTxval / 100000, 2) }}L</div>
            <div class="kpi-label">Taxable value</div>
            <div class="kpi-sub">B2CS total</div>
        </div>
        <div class="kpi-card sky">
            <div class="kpi-icon"><i class="bi bi-percent"></i></div>
            <div class="kpi-value">₹{{ number_format($totTax / 100000, 2) }}L</div>
            <div class="kpi-label">Total tax</div>
            <div class="kpi-sub">IGST + CGST + SGST</div>
        </div>
        <div class="kpi-card amber">
            <div class="kpi-icon"><i class="bi bi-layers-fill"></i></div>
            <div class="kpi-value">{{ $stateCount }}</div>
            <div class="kpi-label">States covered</div>
            <div class="kpi-sub">{{ count($summary) }} B2CS rows</div>
        </div>
        <div class="kpi-card rose">
            <div class="kpi-icon"><i class="bi bi-building"></i></div>
            <div class="kpi-value">{{ $b2bCount }}</div>
            <div class="kpi-label">B2B invoices</div>
            <div class="kpi-sub">{{ count($b2bData) }} parties</div>
        </div>
    </div>

    {{-- ── Tabs ── --}}
    <div class="report-tabs">
        <button class="report-tab active" onclick="switchTab('b2cs', this)"><i class="bi bi-list-ul"></i> B2CS Summary</button>
        <button class="report-tab"        onclick="switchTab('b2b',  this)"><i class="bi bi-building"></i> B2B Invoices</button>
        <button class="report-tab"        onclick="switchTab('detail',this)"><i class="bi bi-table"></i> Transactions</button>
        <button class="report-tab"        onclick="switchTab('statewise',this)"><i class="bi bi-geo-alt-fill"></i> State Summary</button>
        <button class="report-tab"        onclick="switchTab('docissue',this)"><i class="bi bi-file-earmark-text"></i> Doc Issued</button>
        <button class="report-tab"        onclick="switchTab('hsn',this)"><i class="bi bi-upc-scan"></i> HSN Summary</button>
        <button class="report-tab"        onclick="switchTab('charts',this)"><i class="bi bi-bar-chart-fill"></i> Charts</button>
        <button class="report-tab"        onclick="switchTab('json',  this)"><i class="bi bi-braces"></i> JSON Preview</button>
    </div>

    {{-- ══════════════════════════════════════════════
         TAB: B2CS Summary
    ══════════════════════════════════════════════ --}}
    <div id="tab-b2cs" class="tab-content active">
        <div class="table-card">
            <div class="table-header">
                <h3 class="table-title">B2CS — grouped by state & rate</h3>
                <small style="color:#94a3b8;font-size:11px">Hover on a row to edit values manually</small>
            </div>
            <div class="table-scroll">
                <table id="b2csTable">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>State</th>
                            <th>Rate %</th>
                            <th class="num">Taxable (₹)</th>
                            <th class="num">IGST (₹)</th>
                            <th class="num">CGST (₹)</th>
                            <th class="num">SGST (₹)</th>
                            <th class="num">Total tax (₹)</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="b2csBody">
                        @foreach($summary as $row)
                        <tr data-key="{{ $row['key'] }}"
                            data-txval="{{ $row['txval'] }}"
                            data-iamt="{{ $row['iamt'] }}"
                            data-camt="{{ $row['camt'] }}"
                            data-samt="{{ $row['samt'] }}"
                            data-rt="{{ $row['rt'] }}"
                            data-pos="{{ $row['pos'] }}">
                            <td>
                                <span class="badge-{{ strtolower($row['sply_ty']) === 'inter' ? 'inter' : 'intra' }}">
                                    {{ $row['sply_ty'] }}
                                </span>
                                @if($row['edited'])
                                    <span class="badge-edited">edited</span>
                                @endif
                            </td>
                            <td>{{ $stateMap[$row['pos']] ?? $row['pos'] }}</td>
                            <td>{{ $row['rt'] }}%</td>
                            <td class="num val-txval">₹{{ number_format($row['txval'], 2) }}</td>
                            <td class="num val-iamt">{{ $row['iamt'] > 0 ? '₹' . number_format($row['iamt'], 2) : '—' }}</td>
                            <td class="num val-camt">{{ ($row['camt'] ?? 0) > 0 ? '₹' . number_format($row['camt'], 2) : '—' }}</td>
                            <td class="num val-samt">{{ ($row['samt'] ?? 0) > 0 ? '₹' . number_format($row['samt'], 2) : '—' }}</td>
                            <td class="num val-tax"><strong>₹{{ number_format(($row['iamt'] ?? 0) + ($row['camt'] ?? 0) + ($row['samt'] ?? 0), 2) }}</strong></td>
                            <td class="edit-trigger">
                                <button class="btn-edit" onclick="openEdit(this.closest('tr'))">
                                    <i class="bi bi-pencil-fill"></i> Edit
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr id="b2csTotRow">
                            <td colspan="3"><strong>Grand total</strong></td>
                            <td class="num" id="tot-txval">₹{{ number_format($totTxval, 2) }}</td>
                            <td class="num" id="tot-iamt">₹{{ number_format($totIamt, 2) }}</td>
                            <td class="num" id="tot-camt">₹{{ number_format($totCamt, 2) }}</td>
                            <td class="num" id="tot-samt">₹{{ number_format($totSamt, 2) }}</td>
                            <td class="num" id="tot-tax"><strong>₹{{ number_format($totTax, 2) }}</strong></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            {{-- View more --}}
            @if(count($summary) > 8)
            <div style="text-align:center;padding:10px;background:#f7faff;border-top:1px solid #e8edf5" id="b2csMoreWrap">
                <button class="btn-view-more" onclick="toggleB2CSRows(this)">
                    <i class="bi bi-chevron-down"></i> View all {{ count($summary) - 8 }} more rows
                </button>
            </div>
            @endif
        </div>
    </div>

    {{-- ══════════════════════════════════════════════
         TAB: B2B Invoices
    ══════════════════════════════════════════════ --}}
    <div id="tab-b2b" class="tab-content">
        <div class="table-card">
            <div class="table-header">
                <h3 class="table-title">B2B Invoices — party wise</h3>
                <small style="color:#94a3b8;font-size:11px">{{ $b2bCount }} invoices / {{ count($b2bData) }} parties</small>
            </div>
            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Party GSTIN</th>
                            <th>Invoice #</th>
                            <th>Date</th>
                            <th>State</th>
                            <th class="num">Rate %</th>
                            <th class="num">Taxable (₹)</th>
                            <th class="num">IGST (₹)</th>
                            <th class="num">CGST (₹)</th>
                            <th class="num">SGST (₹)</th>
                            <th class="num">Invoice val (₹)</th>
                        </tr>
                    </thead>
                    <tbody id="b2bBody">
                        @php $b2bIdx = 1; @endphp
                        @forelse($b2bData as $party)
                            @foreach($party['inv'] as $inv)
                                @php
                                    $itm = $inv['itms'][0]['itm_det'];
                                @endphp
                                <tr class="b2b-row" style="{{ $b2bIdx > 8 ? 'display:none' : '' }}">
                                    <td>{{ $b2bIdx }}</td>
                                    <td style="font-family:'Courier New',monospace;font-size:11px">{{ $party['ctin'] }}</td>
                                    <td>{{ $inv['inum'] }}</td>
                                    <td>{{ $inv['idt'] }}</td>
                                    <td>{{ $stateMap[$inv['pos']] ?? $inv['pos'] }}</td>
                                    <td class="num">{{ $itm['rt'] }}%</td>
                                    <td class="num">₹{{ number_format($itm['txval'], 2) }}</td>
                                    <td class="num">{{ $itm['iamt'] > 0 ? '₹' . number_format($itm['iamt'], 2) : '—' }}</td>
                                    <td class="num">{{ ($itm['camt'] ?? 0) > 0 ? '₹' . number_format($itm['camt'], 2) : '—' }}</td>
                                    <td class="num">{{ ($itm['samt'] ?? 0) > 0 ? '₹' . number_format($itm['samt'], 2) : '—' }}</td>
                                    <td class="num">₹{{ number_format($inv['val'], 2) }}</td>
                                </tr>
                                @php $b2bIdx++; @endphp
                            @endforeach
                        @empty
                            <tr><td colspan="11" style="text-align:center;padding:24px;color:#94a3b8">No B2B invoices found — buyer_gstin column may not be populated yet</td></tr>
                        @endforelse
                    </tbody>
                    {{-- ── NEW: Grand total footer, same pattern as the B2CS tab,
                         so B2B totals can be verified at a glance without
                         manually adding up every row. ── --}}
                    @if($b2bCount > 0)
                    <tfoot>
                        <tr>
                            <td colspan="6"><strong>Grand total</strong></td>
                            <td class="num"><strong>₹{{ number_format($b2bTotTxval, 2) }}</strong></td>
                            <td class="num">{{ $b2bTotIamt > 0 ? '₹' . number_format($b2bTotIamt, 2) : '—' }}</td>
                            <td class="num">{{ $b2bTotCamt > 0 ? '₹' . number_format($b2bTotCamt, 2) : '—' }}</td>
                            <td class="num">{{ $b2bTotSamt > 0 ? '₹' . number_format($b2bTotSamt, 2) : '—' }}</td>
                            <td class="num"><strong>₹{{ number_format($b2bTotVal, 2) }}</strong></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
            @if($b2bCount > 8)
            <div style="text-align:center;padding:10px;background:#f7faff;border-top:1px solid #e8edf5">
                <button class="btn-view-more" onclick="toggleB2BRows(this)">
                    <i class="bi bi-chevron-down"></i> View all {{ $b2bCount - 8 }} more rows
                </button>
            </div>
            @endif
        </div>
    </div>

    {{-- ══════════════════════════════════════════════
         TAB: Transaction Detail
    ══════════════════════════════════════════════ --}}
    <div id="tab-detail" class="tab-content">
        <div class="table-card">
            <div class="table-header">
                <h3 class="table-title">Transaction-wise detail — all rows</h3>
            </div>
            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>#</th><th>Platform</th><th>Period</th><th>Invoice No</th><th>State</th>
                            <th>Nature</th><th>HSN</th>
                            <th class="num">Qty</th><th class="num">Taxable (₹)</th>
                            <th class="num">Rate %</th><th class="num">CGST (₹)</th>
                            <th class="num">SGST (₹)</th><th class="num">IGST (₹)</th>
                            <th class="num">Invoice (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $dTotTax=$dTotTxval=$dTotCGST=$dTotSGST=$dTotIGST=$dTotInv=0;
                        @endphp
                        @foreach($detailedTransactions as $t)
                        @php
                            $dTotTxval += $t['taxable'];
                            $dTotCGST  += $t['cgst'];
                            $dTotSGST  += $t['sgst'];
                            $dTotIGST  += $t['igst'];
                            $dTotInv   += $t['invoice'];
                        @endphp
                        <tr>
                            <td>{{ $t['idx'] }}</td>
                            <td>{{ $t['platform'] }}</td>
                            <td>{{ $t['period'] }}</td>
                            <td style="font-family:'Courier New',monospace;font-size:11px">{{ $t['invoice_number'] ?? '—' }}</td>
                            <td>{{ $stateMap[$t['state']] ?? $t['state'] }}</td>
                            <td><span class="badge-{{ strtolower($t['nature']) === 'inter' ? 'inter' : 'intra' }}">{{ $t['nature'] }}</span></td>
                            <td>{{ $t['hsn'] ?? '—' }}</td>
                            <td class="num">{{ $t['qty'] }}</td>
                            <td class="num">₹{{ number_format($t['taxable'], 2) }}</td>
                            <td class="num">{{ $t['rate'] }}%</td>
                            <td class="num">{{ $t['cgst'] > 0 ? '₹' . number_format($t['cgst'], 2) : '—' }}</td>
                            <td class="num">{{ $t['sgst'] > 0 ? '₹' . number_format($t['sgst'], 2) : '—' }}</td>
                            <td class="num">{{ $t['igst'] > 0 ? '₹' . number_format($t['igst'], 2) : '—' }}</td>
                            <td class="num">₹{{ number_format($t['invoice'], 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="8"><strong>Totals</strong></td>
                            <td class="num"><strong>₹{{ number_format($dTotTxval, 2) }}</strong></td>
                            <td></td>
                            <td class="num"><strong>₹{{ number_format($dTotCGST, 2) }}</strong></td>
                            <td class="num"><strong>₹{{ number_format($dTotSGST, 2) }}</strong></td>
                            <td class="num"><strong>₹{{ number_format($dTotIGST, 2) }}</strong></td>
                            <td class="num"><strong>₹{{ number_format($dTotInv, 2) }}</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════
         TAB: Doc Issued Summary  ← NEW
    ══════════════════════════════════════════════ --}}
    <div id="tab-docissue" class="tab-content">
        <div class="table-card">
            <div class="table-header">
                <h3 class="table-title">Documents Issued — invoice number series</h3>
                <small style="color:#94a3b8;font-size:11px">Cancelled = numbers inside the range not found in uploaded data</small>
            </div>
            <div style="padding:16px 20px 0">
                @if(!empty($docRows))
                <div class="doc-summary-strip">
                    <div class="doc-mini-card">
                        <div class="doc-mini-val">{{ count($docRows) }}</div>
                        <div class="doc-mini-label">Series found</div>
                    </div>
                    <div class="doc-mini-card">
                        <div class="doc-mini-val">{{ $docTotIssue }}</div>
                        <div class="doc-mini-label">Total range</div>
                    </div>
                    <div class="doc-mini-card">
                        <div class="doc-mini-val" style="color:#b91c1c">{{ $docTotCancel }}</div>
                        <div class="doc-mini-label">Cancelled</div>
                    </div>
                    <div class="doc-mini-card">
                        <div class="doc-mini-val" style="color:#15803d">{{ $docTotNet }}</div>
                        <div class="doc-mini-label">Net issued</div>
                    </div>
                </div>
                @endif
            </div>
            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Sr No</th>
                            <th>From</th>
                            <th>To</th>
                            <th class="num">Total number</th>
                            <th class="num">Cancelled</th>
                            <th class="num">Net issued</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($docRows as $d)
                        <tr>
                            <td>{{ $d['num'] }}</td>
                            <td style="font-family:'Courier New',monospace">{{ $d['from'] }}</td>
                            <td style="font-family:'Courier New',monospace">{{ $d['to'] }}</td>
                            <td class="num">{{ $d['totnum'] }}</td>
                            <td class="num"><span class="badge-cancel">{{ $d['cancel'] }}</span></td>
                            <td class="num"><span class="badge-net">{{ $d['net_issue'] }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="6">
                            <div class="doc-empty">
                                <i class="bi bi-file-earmark-x"></i>
                                Invoice numbers mein numeric part nahi mila, isliye range/cancel calculate nahi ho saka.
                                Invoice numbers ka format check karein (e.g. INV-0001).
                            </div>
                        </td></tr>
                        @endforelse
                    </tbody>
                    @if(!empty($docRows))
                    <tfoot>
                        <tr>
                            <td colspan="3"><strong>Grand total</strong></td>
                            <td class="num"><strong>{{ $docTotIssue }}</strong></td>
                            <td class="num"><strong>{{ $docTotCancel }}</strong></td>
                            <td class="num"><strong>{{ $docTotNet }}</strong></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>



    {{-- ══════════════════════════════════════════════
     TAB: State-wise Summary  ← NEW (no repeated states)
══════════════════════════════════════════════ --}}
<div id="tab-statewise" class="tab-content">
    <div class="table-card">
        <div class="table-header">
            <h3 class="table-title">State-wise Summary — grouped by platform & state</h3>
            <small style="color:#94a3b8;font-size:11px">Har state sirf ek baar — quantity aur value totals rolled up</small>
        </div>
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>#</th><th>Platform</th><th>State</th>
                        <th class="num">Transactions</th><th class="num">Qty</th>
                        <th class="num">Taxable (₹)</th>
                        <th class="num">CGST (₹)</th><th class="num">SGST (₹)</th><th class="num">IGST (₹)</th>
                        <th class="num">Total tax (₹)</th><th class="num">Invoice total (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $sIdx = 1;
                        $sTotQty=$sTotTxval=$sTotCgst=$sTotSgst=$sTotIgst=$sTotTax=$sTotInv=0;
                    @endphp
                    @forelse($stateSummary as $s)
                        @php
                            $sTotQty   += $s['qty'];
                            $sTotTxval += $s['txval'];
                            $sTotCgst  += $s['cgst'];
                            $sTotSgst  += $s['sgst'];
                            $sTotIgst  += $s['igst'];
                            $sTotTax   += $s['total_tax'];
                            $sTotInv   += $s['invoice_total'];
                        @endphp
                        <tr>
                            <td>{{ $sIdx++ }}</td>
                            <td>{{ $s['platform'] }}</td>
                            <td>{{ $s['state_name'] }}</td>
                            <td class="num">{{ $s['txn_count'] }}</td>
                            <td class="num">{{ $s['qty'] }}</td>
                            <td class="num">₹{{ number_format($s['txval'], 2) }}</td>
                            <td class="num">{{ $s['cgst'] > 0 ? '₹' . number_format($s['cgst'], 2) : '—' }}</td>
                            <td class="num">{{ $s['sgst'] > 0 ? '₹' . number_format($s['sgst'], 2) : '—' }}</td>
                            <td class="num">{{ $s['igst'] > 0 ? '₹' . number_format($s['igst'], 2) : '—' }}</td>
                            <td class="num"><strong>₹{{ number_format($s['total_tax'], 2) }}</strong></td>
                            <td class="num">₹{{ number_format($s['invoice_total'], 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="11" style="text-align:center;padding:24px;color:#94a3b8">No data found</td></tr>
                    @endforelse
                </tbody>
                @if(count($stateSummary) > 0)
                <tfoot>
                    <tr>
                        <td colspan="4"><strong>Grand total</strong></td>
                        <td class="num"><strong>{{ $sTotQty }}</strong></td>
                        <td class="num"><strong>₹{{ number_format($sTotTxval, 2) }}</strong></td>
                        <td class="num">₹{{ number_format($sTotCgst, 2) }}</td>
                        <td class="num">₹{{ number_format($sTotSgst, 2) }}</td>
                        <td class="num">₹{{ number_format($sTotIgst, 2) }}</td>
                        <td class="num"><strong>₹{{ number_format($sTotTax, 2) }}</strong></td>
                        <td class="num"><strong>₹{{ number_format($sTotInv, 2) }}</strong></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>

    {{-- ══════════════════════════════════════════════
         TAB: HSN Summary  ← NEW
    ══════════════════════════════════════════════ --}}
    <div id="tab-hsn" class="tab-content">
        <div class="table-card">
            <div class="table-header">
                <h3 class="table-title">HSN Summary — B2B</h3>
                <small style="color:#94a3b8;font-size:11px">{{ count($hsnB2B) }} HSN/rate combinations</small>
            </div>
            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>#</th><th>HSN</th><th>Description</th><th>UQC</th>
                            <th class="num">Qty</th><th class="num">Rate %</th>
                            <th class="num">Taxable (₹)</th><th class="num">IGST (₹)</th>
                            <th class="num">CGST (₹)</th><th class="num">SGST (₹)</th><th class="num">Total (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($hsnB2B as $h)
                        <tr>
                            <td>{{ $h['num'] }}</td>
                            <td style="font-family:'Courier New',monospace">{{ $h['hsn_sc'] }}</td>
                            <td>{{ $h['desc'] }}</td>
                            <td>{{ $h['uqc'] }}</td>
                            <td class="num">{{ $h['qty'] }}</td>
                            <td class="num">{{ $h['rt'] }}%</td>
                            <td class="num">₹{{ number_format($h['txval'], 2) }}</td>
                            <td class="num">{{ $h['iamt'] > 0 ? '₹' . number_format($h['iamt'], 2) : '—' }}</td>
                            <td class="num">{{ $h['camt'] > 0 ? '₹' . number_format($h['camt'], 2) : '—' }}</td>
                            <td class="num">{{ $h['samt'] > 0 ? '₹' . number_format($h['samt'], 2) : '—' }}</td>
                            <td class="num"><strong>₹{{ number_format($h['val'], 2) }}</strong></td>
                        </tr>
                        @empty
                        <tr><td colspan="11" style="text-align:center;padding:20px;color:#94a3b8">No B2B HSN data (hsn_code column empty for B2B rows)</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="table-card">
            <div class="table-header">
                <h3 class="table-title">HSN Summary — B2C</h3>
                <small style="color:#94a3b8;font-size:11px">{{ count($hsnB2C) }} HSN/rate combinations</small>
            </div>
            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>#</th><th>HSN</th><th>Description</th><th>UQC</th>
                            <th class="num">Qty</th><th class="num">Rate %</th>
                            <th class="num">Taxable (₹)</th><th class="num">IGST (₹)</th>
                            <th class="num">CGST (₹)</th><th class="num">SGST (₹)</th><th class="num">Total (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($hsnB2C as $h)
                        <tr>
                            <td>{{ $h['num'] }}</td>
                            <td style="font-family:'Courier New',monospace">{{ $h['hsn_sc'] }}</td>
                            <td>{{ $h['desc'] }}</td>
                            <td>{{ $h['uqc'] }}</td>
                            <td class="num">{{ $h['qty'] }}</td>
                            <td class="num">{{ $h['rt'] }}%</td>
                            <td class="num">₹{{ number_format($h['txval'], 2) }}</td>
                            <td class="num">{{ $h['iamt'] > 0 ? '₹' . number_format($h['iamt'], 2) : '—' }}</td>
                            <td class="num">{{ $h['camt'] > 0 ? '₹' . number_format($h['camt'], 2) : '—' }}</td>
                            <td class="num">{{ $h['samt'] > 0 ? '₹' . number_format($h['samt'], 2) : '—' }}</td>
                            <td class="num"><strong>₹{{ number_format($h['val'], 2) }}</strong></td>
                        </tr>
                        @empty
                        <tr><td colspan="11" style="text-align:center;padding:20px;color:#94a3b8">No B2C HSN data (hsn_code column empty for B2C rows)</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════
         TAB: Charts
    ══════════════════════════════════════════════ --}}
    <div id="tab-charts" class="tab-content">
        <div class="charts-grid">
            <div class="chart-card">
                <div class="chart-title">Taxable value by state (top 10)</div>
                <div style="position:relative;height:260px">
                    <canvas id="chartState" role="img" aria-label="Bar chart of taxable value by top 10 states"></canvas>
                </div>
            </div>
            <div class="chart-card">
                <div class="chart-title">Tax components — IGST vs CGST vs SGST</div>
                <div class="chart-legend">
                    <span><span class="leg-sq" style="background:#2a78d6"></span>IGST</span>
                    <span><span class="leg-sq" style="background:#1baf7a"></span>CGST</span>
                    <span><span class="leg-sq" style="background:#eda100"></span>SGST</span>
                </div>
                <div style="position:relative;height:240px">
                    <canvas id="chartTax" role="img" aria-label="Stacked bar chart of tax components by state"></canvas>
                </div>
            </div>
        </div>
        <div class="charts-grid">
            <div class="chart-card">
                <div class="chart-title">Rate-wise split — taxable value</div>
                <div style="position:relative;height:220px">
                    <canvas id="chartRate" role="img" aria-label="Doughnut chart of taxable value by GST rate"></canvas>
                </div>
            </div>
            <div class="chart-card">
                <div class="chart-title">INTRA vs INTER supply</div>
                <div style="position:relative;height:220px">
                    <canvas id="chartSupply" role="img" aria-label="Doughnut chart of intra vs inter state supply"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════
         TAB: JSON Preview
    ══════════════════════════════════════════════ --}}
    <div id="tab-json" class="tab-content">
        <div class="table-card">
            <div class="table-header">
                <h3 class="table-title">JSON — GST portal upload format</h3>
                <button class="btn-copy" onclick="copyJSON()"><i class="bi bi-clipboard"></i> Copy JSON</button>
            </div>
            <div style="padding:16px">
                <div class="json-pre" id="jsonPreview"></div>
            </div>
        </div>
    </div>

</div>{{-- /reportContent --}}

{{-- ══════════════════════════════════════════════
     EDIT MODAL
══════════════════════════════════════════════ --}}
<div class="modal-overlay" id="editModal" onclick="if(event.target===this)closeEdit()">
    <div class="modal-box">
        <h3>Edit B2CS row</h3>
        <div class="modal-sub" id="editModalSub">Maharashtra — 18%</div>
        <div class="form-group">
            <label>Taxable value (₹)</label>
            <input type="number" id="fTxval" step="0.01" placeholder="0.00">
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>IGST (₹)</label>
                <input type="number" id="fIamt" step="0.01" placeholder="0.00">
            </div>
            <div class="form-group">
                <label>CGST (₹)</label>
                <input type="number" id="fCamt" step="0.01" placeholder="0.00">
            </div>
            <div class="form-group">
                <label>SGST (₹)</label>
                <input type="number" id="fSamt" step="0.01" placeholder="0.00">
            </div>
        </div>
        <div class="modal-actions">
            <button class="btn-cancel" onclick="closeEdit()">Cancel</button>
            <button class="btn-save"   onclick="saveEdit()"><i class="bi bi-check-lg"></i> Save changes</button>
        </div>
    </div>
</div>




<!--new modal added from here with fro seleted json downloaded -->

{{-- ══════════════════════════════════════════════
     JSON EXPORT — SECTION SELECT MODAL
══════════════════════════════════════════════ --}}
<div class="modal-overlay" id="jsonExportModal" onclick="if(event.target===this)closeJsonExportModal()">
    <div class="modal-box">
        <h3>Export JSON</h3>
        <div class="modal-sub">Select karein ki JSON me kya kya chahiye</div>

        <div class="form-group">
            <label style="display:flex;align-items:center;gap:8px;font-weight:400;cursor:pointer">
                <input type="checkbox" id="secB2cs" checked style="width:auto"> B2CS Summary
            </label>
        </div>
        <div class="form-group">
            <label style="display:flex;align-items:center;gap:8px;font-weight:400;cursor:pointer">
                <input type="checkbox" id="secB2b" checked style="width:auto"> B2B Invoices
            </label>
        </div>
        <div class="form-group">
            <label style="display:flex;align-items:center;gap:8px;font-weight:400;cursor:pointer">
                <input type="checkbox" id="secHsn" checked style="width:auto"> HSN Summary
            </label>
        </div>
        <div class="form-group">
            <label style="display:flex;align-items:center;gap:8px;font-weight:400;cursor:pointer">
                <input type="checkbox" id="secDoc" checked style="width:auto"> Document Issued Summary
            </label>
        </div>

        <div class="modal-actions">
            <button class="btn-cancel" onclick="closeJsonExportModal()">Cancel</button>
            <button class="btn-save" onclick="applyJsonExport()"><i class="bi bi-download"></i> Apply &amp; Download</button>
        </div>
    </div>
</div>



@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
// ── Data from server ──────────────────────────────────────────────────────
const jsonData     = @json($jsonData);
const stateNames   = @json($stateMap ?? []);
let   currentEditRow = null;
let   chartsBuilt  = false;

// ── Tab switching ─────────────────────────────────────────────────────────
function switchTab(name, el) {
    document.querySelectorAll('.tab-content').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.report-tab').forEach(t => t.classList.remove('active'));
    document.getElementById('tab-' + name).classList.add('active');
    el.classList.add('active');
    if (name === 'json')   renderJSON();
    if (name === 'charts') buildCharts();
}

// ── View more / less — B2CS ───────────────────────────────────────────────
(function initB2CSRows() {
    const rows = document.querySelectorAll('#b2csBody tr');
    rows.forEach((r, i) => { if (i >= 8) r.style.display = 'none'; });
})();

function toggleB2CSRows(btn) {
    const rows = document.querySelectorAll('#b2csBody tr');
    const hidden = [...rows].some((r, i) => i >= 8 && r.style.display === 'none');
    rows.forEach((r, i) => { if (i >= 8) r.style.display = hidden ? '' : 'none'; });
    btn.innerHTML = hidden
        ? '<i class="bi bi-chevron-up"></i> Show less'
        : '<i class="bi bi-chevron-down"></i> View all {{ count($summary) - 8 }} more rows';
}

// ── View more / less — B2B ────────────────────────────────────────────────
let b2bExpanded = false;
function toggleB2BRows(btn) {
    b2bExpanded = !b2bExpanded;
    document.querySelectorAll('.b2b-row').forEach((r, i) => {
        r.style.display = (b2bExpanded || i < 8) ? '' : 'none';
    });
    btn.innerHTML = b2bExpanded
        ? '<i class="bi bi-chevron-up"></i> Show less'
        : '<i class="bi bi-chevron-down"></i> View all {{ $b2bCount - 8 }} more rows';
}

// ── Edit modal ────────────────────────────────────────────────────────────
function openEdit(tr) {
    currentEditRow = tr;
    const pos = tr.dataset.pos;
    const rt  = tr.dataset.rt;
    document.getElementById('editModalSub').textContent =
        (stateNames[pos] || pos) + ' — ' + rt + '%';
    document.getElementById('fTxval').value = tr.dataset.txval;
    document.getElementById('fIamt').value  = tr.dataset.iamt;
    document.getElementById('fCamt').value  = tr.dataset.camt;
    document.getElementById('fSamt').value  = tr.dataset.samt;
    document.getElementById('editModal').classList.add('open');
    document.getElementById('fTxval').focus();
}

function closeEdit() {
    document.getElementById('editModal').classList.remove('open');
    currentEditRow = null;
}

function saveEdit() {
    if (!currentEditRow) return;
    const key   = currentEditRow.dataset.key;
    const txval = parseFloat(document.getElementById('fTxval').value) || 0;
    const iamt  = parseFloat(document.getElementById('fIamt').value)  || 0;
    const camt  = parseFloat(document.getElementById('fCamt').value)  || 0;
    const samt  = parseFloat(document.getElementById('fSamt').value)  || 0;

    fetch('{{ route("gstr.summary.editRow") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ key, txval, iamt, camt, samt }),
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) return;
        // Update row cells
        currentEditRow.dataset.txval = txval;
        currentEditRow.dataset.iamt  = iamt;
        currentEditRow.dataset.camt  = camt;
        currentEditRow.dataset.samt  = samt;
        const fmt = n => '₹' + Number(n).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        currentEditRow.querySelector('.val-txval').textContent = fmt(txval);
        currentEditRow.querySelector('.val-iamt').textContent  = iamt > 0 ? fmt(iamt) : '—';
        currentEditRow.querySelector('.val-camt').textContent  = camt > 0 ? fmt(camt) : '—';
        currentEditRow.querySelector('.val-samt').textContent  = samt > 0 ? fmt(samt) : '—';
        currentEditRow.querySelector('.val-tax').innerHTML     = '<strong>' + fmt(iamt + camt + samt) + '</strong>';
        // Add "edited" badge if not there
        if (!currentEditRow.querySelector('.badge-edited')) {
            currentEditRow.querySelector('td:first-child').insertAdjacentHTML(
                'beforeend', '<span class="badge-edited">edited</span>'
            );
        }
        // Update footer totals
        const t = data.totals;
        document.getElementById('tot-txval').textContent = fmt(t.txval);
        document.getElementById('tot-iamt').textContent  = fmt(t.iamt);
        document.getElementById('tot-camt').textContent  = fmt(t.camt);
        document.getElementById('tot-samt').textContent  = fmt(t.samt);
        document.getElementById('tot-tax').innerHTML     = '<strong>' + fmt(t.iamt + t.camt + t.samt) + '</strong>';
        // Update jsonData for preview
        Object.assign(jsonData, data.jsonData);
        chartsBuilt = false; // force chart rebuild
        closeEdit();
        if (typeof toast === 'function') toast('Row updated successfully', 'success');
    })
    .catch(() => alert('Error saving changes. Please try again.'));
}

// ── JSON preview ──────────────────────────────────────────────────────────
function renderJSON() {
    document.getElementById('jsonPreview').textContent = JSON.stringify(jsonData, null, 2);
}

function copyJSON() {
    renderJSON();
    navigator.clipboard.writeText(document.getElementById('jsonPreview').textContent)
        .then(() => { if (typeof toast === 'function') toast('JSON copied!', 'success'); });
}


// seleted json funstion new added from here 




// ── JSON export modal ──────────────────────────────────────────────────────
function openJsonExportModal() {
    document.getElementById('jsonExportModal').classList.add('open');
}
function closeJsonExportModal() {
    document.getElementById('jsonExportModal').classList.remove('open');
}
function applyJsonExport() {
    const sections = [];
    if (document.getElementById('secB2cs').checked) sections.push('b2cs');
    if (document.getElementById('secB2b').checked)  sections.push('b2b');
    if (document.getElementById('secHsn').checked)  sections.push('hsn');
    if (document.getElementById('secDoc').checked)  sections.push('doc_issue');

    if (sections.length === 0) {
        alert('Kam se kam ek section select karein.');
        return;
    }

    const url = '{{ route("gstr.summary.downloadJSON") }}' + '?sections=' + encodeURIComponent(sections.join(','));
    location.href = url;
    closeJsonExportModal();
}





// ── Charts ────────────────────────────────────────────────────────────────
function buildCharts() {
    if (chartsBuilt) return;
    chartsBuilt = true;

    // aggregate b2cs by state
    const byState = {};
    jsonData.b2cs.forEach(r => {
        const s = stateNames[r.pos] || r.pos;
        if (!byState[s]) byState[s] = { tx: 0, i: 0, c: 0, s: 0 };
        byState[s].tx += r.txval;
        byState[s].i  += r.iamt || 0;
        byState[s].c  += r.camt || 0;
        byState[s].s  += r.samt || 0;
    });
    const top10 = Object.entries(byState)
        .sort((a, b) => b[1].tx - a[1].tx)
        .slice(0, 10);

    new Chart(document.getElementById('chartState'), {
        type: 'bar',
        data: {
            labels: top10.map(x => x[0]),
            datasets: [{ label: 'Taxable value', data: top10.map(x => Math.round(x[1].tx)), backgroundColor: '#2a78d6', borderRadius: 4 }],
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { ticks: { font: { size: 10 }, autoSkip: false, maxRotation: 40 } },
                y: { ticks: { callback: v => '₹' + (v / 1000).toFixed(0) + 'K' } },
            },
        },
    });

    new Chart(document.getElementById('chartTax'), {
        type: 'bar',
        data: {
            labels: top10.map(x => x[0]),
            datasets: [
                { label: 'IGST', data: top10.map(x => Math.round(x[1].i)), backgroundColor: '#2a78d6' },
                { label: 'CGST', data: top10.map(x => Math.round(x[1].c)), backgroundColor: '#1baf7a' },
                { label: 'SGST', data: top10.map(x => Math.round(x[1].s)), backgroundColor: '#eda100' },
            ],
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { stacked: true, ticks: { font: { size: 10 }, autoSkip: false, maxRotation: 40 } },
                y: { stacked: true, ticks: { callback: v => '₹' + (v / 1000).toFixed(0) + 'K' } },
            },
        },
    });

    const r5  = jsonData.b2cs.filter(r => r.rt === 5).reduce((a, r) => a + r.txval, 0);
    const r18 = jsonData.b2cs.filter(r => r.rt === 18).reduce((a, r) => a + r.txval, 0);
    const rOt = jsonData.b2cs.filter(r => r.rt !== 5 && r.rt !== 18).reduce((a, r) => a + r.txval, 0);
    new Chart(document.getElementById('chartRate'), {
        type: 'doughnut',
        data: {
            labels: ['5% GST', '18% GST', 'Other'],
            datasets: [{ data: [Math.round(r5), Math.round(r18), Math.round(rOt)], backgroundColor: ['#2a78d6', '#1baf7a', '#eda100'], borderWidth: 2 }],
        },
        options: { responsive: true, maintainAspectRatio: false, cutout: '62%', plugins: { legend: { position: 'bottom', labels: { font: { size: 11 }, usePointStyle: true, pointStyle: 'rect' } } } },
    });

    const intra = jsonData.b2cs.filter(r => r.sply_ty === 'INTRA').reduce((a, r) => a + r.txval, 0);
    const inter = jsonData.b2cs.filter(r => r.sply_ty === 'INTER').reduce((a, r) => a + r.txval, 0);
    new Chart(document.getElementById('chartSupply'), {
        type: 'doughnut',
        data: {
            labels: ['INTRA state', 'INTER state'],
            datasets: [{ data: [Math.round(intra), Math.round(inter)], backgroundColor: ['#4ade80', '#667eea'], borderWidth: 2 }],
        },
        options: { responsive: true, maintainAspectRatio: false, cutout: '62%', plugins: { legend: { position: 'bottom', labels: { font: { size: 11 }, usePointStyle: true, pointStyle: 'rect' } } } },
    });
}
</script>
@endsection
