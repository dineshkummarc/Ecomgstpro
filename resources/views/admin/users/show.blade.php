@extends('admin.layout.admin')
@section('title', $user->name . ' — User Detail')

@push('styles')
<style>

:root { --navy:#0a1628;--deep:#0d1f3c;--teal:#00c98d;--muted:#8a9bbf;--border:rgba(255,255,255,.08);--danger:#ff5c7a; }
.admin-page { min-height:100vh; background:linear-gradient(135deg,var(--navy) 0%,var(--deep) 60%,#0a2240 100%); padding:40px 20px; }
.admin-card { max-width:1100px; margin:0 auto; background:rgba(13,31,60,.85); backdrop-filter:blur(20px); border:1px solid var(--border); border-radius:20px; padding:32px; box-shadow:0 20px 50px rgba(0,0,0,.4); }
.back-link { color:var(--muted); text-decoration:none; font-size:13px; display:inline-flex; align-items:center; gap:6px; margin-bottom:18px; }
.back-link:hover { color:#fff; }

.profile-header { display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:16px; margin-bottom:24px; }
.profile-name { font-family:'Syne',sans-serif; font-weight:800; font-size:24px; color:#fff; margin-bottom:4px; }
.profile-email { color:var(--muted); font-size:14px; }
.status-pill { display:inline-flex; align-items:center; gap:5px; padding:5px 14px; border-radius:50px; font-size:12.5px; font-weight:700; margin-top:8px; }
.status-pill.active   { background:rgba(0,201,141,.12); color:var(--teal); border:1px solid rgba(0,201,141,.25); }
.status-pill.inactive { background:rgba(255,92,122,.12); color:var(--danger); border:1px solid rgba(255,92,122,.25); }

.btn-danger  { padding:10px 20px; background:rgba(255,92,122,.15); color:var(--danger); border:1px solid rgba(255,92,122,.3); border-radius:10px; font-weight:700; font-size:13.5px; cursor:pointer; }
.btn-danger:hover { background:var(--danger); color:#fff; }
.btn-success { padding:10px 20px; background:rgba(0,201,141,.15); color:var(--teal); border:1px solid rgba(0,201,141,.3); border-radius:10px; font-weight:700; font-size:13.5px; cursor:pointer; }
.btn-success:hover { background:var(--teal); color:#0a1628; }

.info-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:14px; margin-bottom:24px; }
.info-box { background:rgba(255,255,255,.03); border:1px solid var(--border); border-radius:14px; padding:16px 18px; }
.info-box .lbl { font-size:11px; color:var(--muted); font-weight:600; text-transform:uppercase; margin-bottom:6px; }
.info-box .val { font-size:15px; font-weight:700; color:#fff; }

.deactivation-note { background:rgba(255,92,122,.08); border:1px solid rgba(255,92,122,.25); border-radius:12px; padding:16px; margin-bottom:24px; }
.deactivation-note .lbl { font-size:12px; color:var(--danger); font-weight:700; text-transform:uppercase; margin-bottom:6px; }
.deactivation-note .val { color:#ffb3c1; font-size:14px; }

.section-title { font-family:'Syne',sans-serif; font-weight:700; font-size:17px; color:#fff; margin:28px 0 14px; }

table { width:100%; border-collapse:collapse; }
th { font-size:12px; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:.6px; padding:10px 12px; border-bottom:1px solid var(--border); text-align:left; }
td { padding:12px; border-bottom:1px solid rgba(255,255,255,.04); font-size:13px; color:#c0cfe8; }
tr:last-child td { border-bottom:none; }
.empty-state { text-align:center; padding:30px; color:var(--muted); }

/* MODAL */
.modal-bg { position:fixed; inset:0; background:rgba(5,10,20,.7); backdrop-filter:blur(4px); z-index:2000; display:flex; align-items:center; justify-content:center; padding:20px; opacity:0; visibility:hidden; transition:all .25s; }
.modal-bg.open { opacity:1; visibility:visible; }
.modal-box { background:#0d1f3c; border:1px solid var(--border); border-radius:20px; padding:32px; width:100%; max-width:440px; box-shadow:0 20px 50px rgba(0,0,0,.5); }
.modal-title { font-family:'Syne',sans-serif; font-weight:800; font-size:20px; color:#fff; margin-bottom:6px; }
.modal-sub { color:var(--muted); font-size:13px; margin-bottom:20px; }
.modal-label { display:block; font-size:13px; font-weight:600; color:#c0cfe8; margin-bottom:7px; }
.modal-input {
    width:100%; padding:10px 14px; background:rgba(255,255,255,.05); border:1.5px solid rgba(255,255,255,.1);
    border-radius:10px; color:#fff; font-family:'DM Sans',sans-serif; font-size:14px;
    outline:none; box-sizing:border-box; margin-bottom:16px; resize:vertical;
}
.modal-input:focus { border-color:var(--danger); }
.modal-actions { display:flex; gap:10px; }
.btn-modal-confirm { flex:1; padding:11px; background:var(--danger); color:#fff; font-weight:700; font-size:14px; border:none; border-radius:10px; cursor:pointer; }
.btn-modal-cancel { flex:1; padding:11px; background:rgba(255,255,255,.05); color:var(--muted); font-weight:600; font-size:14px; border:1px solid rgba(255,255,255,.1); border-radius:10px; cursor:pointer; }
@media(max-width:768px){ .info-grid{grid-template-columns:1fr;} }
</style>
@endpush

@section('content')
<div class="admin-page">
<div class="admin-card">

    <a href="{{ route('admin.users.index') }}" class="back-link"><i class="bi bi-arrow-left"></i> Back to Users</a>

    <div class="profile-header">
        <div>
            <div class="profile-name">{{ $user->name }}</div>
            <div class="profile-email">{{ $user->email }}</div>
            <span class="status-pill {{ $user->is_active ? 'active' : 'inactive' }}">
                @if($user->is_active) <i class="bi bi-check-circle-fill"></i> Active
                @else <i class="bi bi-x-circle-fill"></i> Inactive
                @endif
            </span>
        </div>
        <div>
            @if($user->is_active)
                <button type="button" class="btn-danger" onclick="openDeactivateModal()">
                    <i class="bi bi-slash-circle"></i> Deactivate User
                </button>
            @else
                <form action="{{ route('admin.users.toggleStatus', $user->id) }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn-success">
                        <i class="bi bi-check-circle"></i> Activate User
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div style="background:rgba(0,201,141,.1);border:1px solid rgba(0,201,141,.25);color:#a0f5dc;padding:12px 16px;border-radius:12px;font-size:13.5px;margin-bottom:20px;">
            <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
        </div>
    @endif

    @if(!$user->is_active && $user->deactivation_reason)
        <div class="deactivation-note">
            <div class="lbl">Deactivation Reason</div>
            <div class="val">{{ $user->deactivation_reason }}</div>
            <div style="color:var(--muted);font-size:12px;margin-top:6px;">
                Deactivated on {{ optional($user->deactivated_at)->format('d M Y, h:i A') }}
            </div>
        </div>
    @endif

    <div class="info-grid">
        <div class="info-box"><div class="lbl">Phone</div><div class="val">{{ $user->phone ?? '—' }}</div></div>
        <div class="info-box"><div class="lbl">Company</div><div class="val">{{ $user->company_name ?? '—' }}</div></div>
        <div class="info-box"><div class="lbl">Registered On</div><div class="val">{{ $user->created_at->format('d M Y') }}</div></div>
        <div class="info-box"><div class="lbl">Total Batches</div><div class="val">{{ $batches->count() }}</div></div>
        <div class="info-box"><div class="lbl">Total Transactions</div><div class="val">{{ $totalTransactions }}</div></div>
        <div class="info-box"><div class="lbl">Total Taxable Value</div><div class="val">₹{{ number_format($totalTaxableValue, 2) }}</div></div>
    </div>

    @if($platformsUsed->count())
    <div class="section-title">Platforms Used</div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:10px;">
        @foreach($platformsUsed as $p)
            <span class="batch-badge" style="background:rgba(0,170,255,.12);color:#00aaff;border:1px solid rgba(0,170,255,.25);border-radius:50px;padding:4px 12px;font-size:12px;font-weight:700;">{{ ucfirst($p) }}</span>
        @endforeach
    </div>
    @endif

    <div class="section-title">Batch History ({{ $batches->count() }})</div>
    @if($batches->count())
    <div style="overflow-x:auto;">
    <table>
        <thead>
            <tr>
                <th>Batch ID</th><th>Client</th><th>Period</th><th>Platforms</th>
                <th>Transactions</th><th>Taxable Value</th><th>Total Tax</th><th>Created</th>
            </tr>
        </thead>
        <tbody>
            @foreach($batches as $b)
            <tr>
                <td style="color:var(--muted);font-size:11.5px;">{{ $b->batch_id }}</td>
                <td>{{ $b->client_name }}</td>
                <td>{{ $b->period }}</td>
                <td>{{ $b->platforms }}</td>
                <td>{{ $b->transaction_count }}</td>
                <td>₹{{ number_format($b->taxable_value, 2) }}</td>
                <td>₹{{ number_format($b->total_tax, 2) }}</td>
                <td>{{ $b->created_at->format('d M Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    @else
        <div class="empty-state">No batches created yet.</div>
    @endif

</div>
</div>

{{-- DEACTIVATE MODAL --}}
<div class="modal-bg" id="deactivateModal">
    <div class="modal-box">
        <div class="modal-title">Deactivate {{ $user->name }}</div>
        <p class="modal-sub">This will block the user from logging in. An email with the reason will be sent to them.</p>
        <form action="{{ route('admin.users.toggleStatus', $user->id) }}" method="POST">
            @csrf
            <label class="modal-label">Reason <span style="color:var(--danger)">*</span></label>
            <textarea name="reason" class="modal-input" rows="4" placeholder="e.g. Suspicious activity detected on the account" required minlength="5"></textarea>
            <div class="modal-actions">
                <button type="button" class="btn-modal-cancel" onclick="closeDeactivateModal()">Cancel</button>
                <button type="submit" class="btn-modal-confirm"><i class="bi bi-slash-circle"></i> Deactivate & Notify</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openDeactivateModal() { document.getElementById('deactivateModal').classList.add('open'); }
function closeDeactivateModal() { document.getElementById('deactivateModal').classList.remove('open'); }
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('deactivateModal');
    modal.addEventListener('click', function (e) { if (e.target === this) closeDeactivateModal(); });
});
</script>
@endpush
