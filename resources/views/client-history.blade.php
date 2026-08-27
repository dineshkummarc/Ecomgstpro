{{-- resources/views/client-history.blade.php --}}
@extends('layouts.gstr-main')

@section('title', 'Filing History — ' . $client->name)

@section('styles')
<style>
    .page-header { margin-bottom: 28px; }
    .back-link {
        display: inline-flex; align-items: center; gap: 6px;
        font-size: 13px; color: #6b7a99; text-decoration: none;
        margin-bottom: 14px; transition: color .15s;
    }
    .back-link:hover { color: var(--teal); }

    .client-hero {
        background: white;
        border: 1.5px solid #e8edf5;
        border-radius: 16px;
        padding: 20px 24px;
        display: flex; align-items: center; gap: 16px;
        margin-bottom: 28px;
        flex-wrap: wrap;
    }
    .hero-avatar {
        width: 52px; height: 52px; border-radius: 14px;
        background: linear-gradient(135deg, var(--teal) 0%, var(--sky) 100%);
        display: flex; align-items: center; justify-content: center;
        font-family: 'Syne', sans-serif; font-weight: 800;
        font-size: 18px; color: white; flex-shrink: 0;
    }
    .hero-info { flex: 1; }
    .hero-name {
        font-family: 'Syne', sans-serif; font-weight: 800;
        font-size: 20px; color: var(--navy); margin: 0 0 4px;
    }
    .hero-meta { display: flex; gap: 16px; flex-wrap: wrap; }
    .hero-meta span {
        font-size: 12px; color: #6b7a99;
        display: flex; align-items: center; gap: 5px;
    }
    .hero-meta i { color: var(--teal); }
    .hero-actions { display: flex; gap: 8px; }

    /* stats strip */
    .stats-strip {
        display: grid; grid-template-columns: repeat(3, 1fr);
        gap: 12px; margin-bottom: 28px;
    }
    .stat-box {
        background: white; border: 1.5px solid #e8edf5;
        border-radius: 14px; padding: 16px 20px;
        text-align: center;
    }
    .stat-val {
        font-family: 'Syne', sans-serif; font-weight: 800;
        font-size: 22px; color: var(--navy);
    }
    .stat-lbl { font-size: 11px; color: #94a3b8; margin-top: 3px; }

    /* batch cards */
    .section-title {
        font-family: 'Syne', sans-serif; font-weight: 700;
        font-size: 16px; color: var(--navy);
        margin-bottom: 16px;
        display: flex; align-items: center; gap: 8px;
    }
    .section-title i { color: var(--teal); }

    .batch-card {
        background: white;
        border: 1.5px solid #e8edf5;
        border-radius: 16px;
        margin-bottom: 14px;
        overflow: hidden;
        transition: box-shadow .2s;
    }
    .batch-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,.07); }

    .batch-header {
        display: flex; align-items: center;
        padding: 16px 20px; gap: 16px;
        flex-wrap: wrap;
        cursor: pointer;
        user-select: none;
    }
    .batch-header:hover { background: #fafcff; }

    .period-badge {
        background: linear-gradient(135deg, var(--teal), var(--sky));
        color: white; border-radius: 10px;
        padding: 6px 14px;
        font-family: 'Syne', sans-serif;
        font-weight: 800; font-size: 15px;
        flex-shrink: 0;
    }

    .batch-meta { flex: 1; min-width: 0; }
    .batch-date { font-size: 12px; color: #94a3b8; }
    .batch-platforms {
        font-size: 12px; color: #6b7a99;
        margin-top: 3px;
        display: flex; gap: 6px; flex-wrap: wrap;
    }
    .plat-tag {
        background: #f0f4ff; border-radius: 6px;
        padding: 1px 7px; font-size: 11px; font-weight: 600;
        color: #3b5bdb;
    }

    .batch-numbers {
        display: flex; gap: 20px; flex-shrink: 0;
    }
    .bnum { text-align: center; }
    .bnum-val {
        font-family: 'Syne', sans-serif; font-weight: 700;
        font-size: 15px; color: var(--navy);
    }
    .bnum-lbl { font-size: 10px; color: #94a3b8; margin-top: 1px; }

    .batch-toggle {
        color: #94a3b8; font-size: 18px; transition: transform .2s;
        flex-shrink: 0;
    }
    .batch-card.open .batch-toggle { transform: rotate(180deg); }

    /* batch body (downloads) */
    .batch-body {
        display: none;
        border-top: 1px solid #f0f4ff;
        padding: 16px 20px;
        gap: 10px;
        flex-wrap: wrap;
        align-items: center;
    }
    .batch-card.open .batch-body { display: flex; }

    .dl-btn {
        font-size: 12px; font-weight: 600;
        padding: 8px 14px; border-radius: 10px;
        border: 1.5px solid; cursor: pointer;
        display: inline-flex; align-items: center; gap: 6px;
        text-decoration: none; transition: all .15s;
    }
    .dl-btn:hover { transform: translateY(-1px); }
    .dl-csv  { background: #eeedfe; border-color: #afa9ec; color: #3c3489; }
    .dl-xl   { background: rgba(0,201,141,.08); border-color: var(--teal); color: #007050; }
    .dl-xl2  { background: #eaf3de; border-color: #97c459; color: #3b6d11; }
    .dl-json { background: #e6f1fb; border-color: #85b7eb; color: #185fa5; }

    .dl-separator {
        width: 1px; height: 28px;
        background: #e8edf5; margin: 0 4px;
        flex-shrink: 0;
    }

    .del-batch-btn {
        margin-left: auto;
        display: inline-flex; align-items: center; gap: 6px;
        font-size: 12px; font-weight: 600;
        padding: 8px 14px; border-radius: 10px;
        border: 1.5px solid #fecaca;
        background: #fff5f5; color: #dc2626;
        cursor: pointer; transition: all .15s;
    }
    .del-batch-btn:hover {
        background: #fee2e2; border-color: #ef4444;
        transform: translateY(-1px);
    }

    /* load batch button */
    .load-btn {
        display: inline-flex; align-items: center; gap: 6px;
        font-size: 12px; font-weight: 600;
        padding: 8px 14px; border-radius: 10px;
        border: 1.5px solid rgba(0,201,141,.3);
        background: rgba(0,201,141,.06); color: #007050;
        cursor: pointer; transition: all .15s;
        text-decoration: none;
    }
    .load-btn:hover { background: rgba(0,201,141,.12); border-color: var(--teal); }

    /* empty */
    .empty-state {
        text-align: center; padding: 60px 20px;
        background: white; border: 1.5px solid #e8edf5;
        border-radius: 16px;
    }
    .empty-state i { font-size: 48px; color: #dde3ef; display: block; margin-bottom: 14px; }
    .empty-state h3 { font-family: 'Syne', sans-serif; font-weight: 700; font-size: 17px; color: var(--navy); margin-bottom: 8px; }
    .empty-state p { font-size: 13px; color: #94a3b8; }

    .alert-box {
        padding: 14px 16px; border-radius: 12px;
        display: flex; align-items: flex-start; gap: 10px;
        margin-bottom: 20px; font-size: 13px;
    }
    .alert-success { background: rgba(0,201,141,.08); border: 1px solid rgba(0,201,141,.2); color: #005a3c; }
    .alert-error   { background: rgba(239,68,68,.08);  border: 1px solid rgba(239,68,68,.2);  color: #7f1d1d; }

    @media (max-width: 768px) {
        .stats-strip { grid-template-columns: 1fr 1fr; }
        .batch-numbers { display: none; }
        .client-hero { flex-direction: column; align-items: flex-start; }
    }
</style>
@endsection

@section('content')

@if(session('success'))
<div class="alert-box alert-success">
    <i class="bi bi-check-circle-fill" style="color:var(--teal)"></i>
    <div>{{ session('success') }}</div>
</div>
@endif
@if(session('error'))
<div class="alert-box alert-error">
    <i class="bi bi-exclamation-circle-fill" style="color:#ef4444"></i>
    <div>{{ session('error') }}</div>
</div>
@endif

<a href="{{ route('gstr.clients.index') }}" class="back-link">
    <i class="bi bi-arrow-left"></i> Back to Clients
</a>

<!-- CLIENT HERO -->
<div class="client-hero">
    <div class="hero-avatar">{{ $client->initials }}</div>
    <div class="hero-info">
        <h1 class="hero-name">{{ $client->name }}</h1>
        <div class="hero-meta">
            <span><i class="bi bi-upc-scan"></i> {{ $client->gstin }}</span>
            <span><i class="bi bi-geo-alt-fill"></i> {{ $client->state_code }} – {{ $client->state_name }}</span>
            <span><i class="bi bi-archive"></i> {{ $batches->count() }} batch{{ $batches->count() != 1 ? 'es' : '' }}</span>
        </div>
    </div>
    <div class="hero-actions">
        <a href="{{ route('gstr.upload.index') }}" class="btn-teal" style="font-size:13px;padding:9px 16px">
            <i class="bi bi-upload"></i> New Upload
        </a>
    </div>
</div>

<!-- STATS STRIP -->
@if($batches->count() > 0)
@php
    $totalTxns    = $batches->sum('transaction_count');
    $totalTaxable = $batches->sum('taxable_value');
    $totalTax     = $batches->sum('total_tax');
@endphp
<div class="stats-strip">
    <div class="stat-box">
        <div class="stat-val">{{ number_format($totalTxns) }}</div>
        <div class="stat-lbl">Total Transactions</div>
    </div>
    <div class="stat-box">
        <div class="stat-val">₹{{ number_format($totalTaxable / 100000, 1) }}L</div>
        <div class="stat-lbl">Total Taxable Value</div>
    </div>
    <div class="stat-box">
        <div class="stat-val">₹{{ number_format($totalTax / 1000, 1) }}K</div>
        <div class="stat-lbl">Total Tax</div>
    </div>
</div>
@endif

<!-- BATCH LIST -->
<div class="section-title">
    <i class="bi bi-clock-history"></i> Filing Batches
</div>

@forelse($batches as $batch)
@php
    $platforms = array_map('trim', explode(',', $batch->platforms));
@endphp
<div class="batch-card" id="batch-{{ $loop->index }}">
    <div class="batch-header" onclick="toggleBatch({{ $loop->index }})">

        <div class="period-badge">{{ $batch->period }}</div>

        <div class="batch-meta">
            <div class="batch-date">
                <i class="bi bi-calendar3" style="color:var(--teal)"></i>
                {{ \Carbon\Carbon::parse($batch->created_at)->format('d M Y, h:i A') }}
            </div>
            <div class="batch-platforms">
                @foreach($platforms as $p)
                    <span class="plat-tag">{{ ucfirst($p) }}</span>
                @endforeach
            </div>
        </div>

        <div class="batch-numbers">
            <div class="bnum">
                <div class="bnum-val">{{ number_format($batch->transaction_count) }}</div>
                <div class="bnum-lbl">Txns</div>
            </div>
            <div class="bnum">
                <div class="bnum-val">₹{{ number_format($batch->taxable_value / 100000, 1) }}L</div>
                <div class="bnum-lbl">Taxable</div>
            </div>
            <div class="bnum">
                <div class="bnum-val">₹{{ number_format($batch->total_tax / 1000, 1) }}K</div>
                <div class="bnum-lbl">Tax</div>
            </div>
        </div>

        <i class="bi bi-chevron-down batch-toggle"></i>
    </div>

    <div class="batch-body">
        {{-- Load into summary --}}
        <form method="POST" action="{{ route('gstr.clients.select', $client->id) }}" style="margin:0">
            @csrf
            <button type="submit" class="load-btn">
                <i class="bi bi-lightning-fill"></i> Load to Summary
            </button>
        </form>

        <div class="dl-separator"></div>

        {{-- Downloads --}}
        <a href="{{ route('gstr.history.downloadCSV',          $batch->batch_id) }}" class="dl-btn dl-csv">
            <i class="bi bi-filetype-csv"></i> B2CS CSV
        </a>
        <a href="{{ route('gstr.history.downloadSummaryExcel', $batch->batch_id) }}" class="dl-btn dl-xl">
            <i class="bi bi-file-earmark-excel-fill"></i> Summary XL
        </a>
        <a href="{{ route('gstr.history.downloadDetailExcel',  $batch->batch_id) }}" class="dl-btn dl-xl2">
            <i class="bi bi-file-earmark-excel-fill"></i> Detail XL
        </a>
        <a href="{{ route('gstr.history.downloadJSON',         $batch->batch_id) }}" class="dl-btn dl-json">
            <i class="bi bi-braces"></i> JSON
        </a>

        {{-- Delete batch --}}
        <form method="POST"
              action="{{ route('gstr.clients.batch.destroy', $batch->batch_id) }}"
              onsubmit="return confirm('Delete this batch ({{ $batch->period }})? This cannot be undone.')"
              style="margin:0">
            @csrf @method('DELETE')
            <button type="submit" class="del-batch-btn">
                <i class="bi bi-trash3"></i> Delete Batch
            </button>
        </form>
    </div>
</div>
@empty
<div class="empty-state">
    <i class="bi bi-archive"></i>
    <h3>No filing batches yet</h3>
    <p>Upload e-commerce reports to create your first filing batch for {{ $client->name }}.</p>
    <a href="{{ route('gstr.upload.index') }}" class="btn-teal" style="margin-top:16px;display:inline-flex">
        <i class="bi bi-upload"></i> Upload Reports
    </a>
</div>
@endforelse

@endsection

@section('scripts')
<script>
function toggleBatch(idx) {
    const card = document.getElementById('batch-' + idx);
    card.classList.toggle('open');
}
</script>
@endsection
