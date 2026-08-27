@extends('admin.layout.admin')
@section('title', 'Demo Requests — Admin')

@push('styles')
<style>
:root { --navy:#0a1628;--deep:#0d1f3c;--teal:#00c98d;--muted:#8a9bbf;--border:rgba(255,255,255,.08);--danger:#ff5c7a; }
.admin-page { min-height:100vh; background:linear-gradient(135deg,var(--navy) 0%,var(--deep) 60%,#0a2240 100%); padding:40px 20px; }
.admin-card { max-width:1100px; margin:0 auto; background:rgba(13,31,60,.85); backdrop-filter:blur(20px); border:1px solid var(--border); border-radius:20px; padding:32px; box-shadow:0 20px 50px rgba(0,0,0,.4); }
.page-title { font-family:'Syne',sans-serif; font-weight:800; font-size:24px; color:#fff; margin-bottom:6px; }
.page-sub { color:var(--muted); font-size:14px; margin-bottom:28px; }

.filter-tabs { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:24px; }
.tab-btn { padding:7px 18px; border-radius:50px; border:1.5px solid rgba(255,255,255,.12); background:transparent; color:var(--muted); font-family:'DM Sans',sans-serif; font-weight:600; font-size:13px; cursor:pointer; text-decoration:none; transition:all .2s; }
.tab-btn:hover,.tab-btn.active { background:var(--teal); border-color:var(--teal); color:#0a1628; }

.pending-badge { background:rgba(0,201,141,.12); color:var(--teal); border:1px solid rgba(0,201,141,.25); border-radius:50px; padding:2px 10px; font-size:12px; font-weight:700; margin-left:6px; }

table { width:100%; border-collapse:collapse; }
th { font-size:12px; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:.6px; padding:10px 14px; border-bottom:1px solid var(--border); text-align:left; }
td { padding:14px 14px; border-bottom:1px solid rgba(255,255,255,.04); font-size:13.5px; color:#c0cfe8; vertical-align:top; }
tr:last-child td { border-bottom:none; }
tr:hover td { background:rgba(255,255,255,.02); }

.status-pill { display:inline-flex; align-items:center; gap:5px; padding:4px 12px; border-radius:50px; font-size:12px; font-weight:700; }
.status-pill.pending   { background:rgba(251,191,36,.12); color:#fbbf24; border:1px solid rgba(251,191,36,.25); }
.status-pill.confirmed { background:rgba(0,201,141,.12); color:var(--teal); border:1px solid rgba(0,201,141,.25); }
.status-pill.cancelled { background:rgba(255,92,122,.12); color:var(--danger); border:1px solid rgba(255,92,122,.25); }

.btn-sm { padding:6px 14px; border-radius:8px; font-size:12.5px; font-weight:600; cursor:pointer; border:none; font-family:'DM Sans',sans-serif; transition:all .2s; }
.btn-confirm { background:rgba(0,201,141,.15); color:var(--teal); border:1px solid rgba(0,201,141,.25); }
.btn-confirm:hover { background:var(--teal); color:#0a1628; }
.btn-cancel  { background:rgba(255,92,122,.12); color:var(--danger); border:1px solid rgba(255,92,122,.25); }
.btn-cancel:hover  { background:var(--danger); color:#fff; }

/* MODAL */
.modal-bg { position:fixed; inset:0; background:rgba(5,10,20,.7); backdrop-filter:blur(4px); z-index:2000; display:flex; align-items:center; justify-content:center; padding:20px; opacity:0; visibility:hidden; transition:all .25s; }
.modal-bg.open { opacity:1; visibility:visible; }
.modal-box { background:#0d1f3c; border:1px solid var(--border); border-radius:20px; padding:32px; width:100%; max-width:440px; box-shadow:0 20px 50px rgba(0,0,0,.5); }
.modal-title { font-family:'Syne',sans-serif; font-weight:800; font-size:20px; color:#fff; margin-bottom:6px; }
.modal-sub { color:var(--muted); font-size:13px; margin-bottom:24px; }
.modal-label { display:block; font-size:13px; font-weight:600; color:#c0cfe8; margin-bottom:7px; }
.modal-input {
    width:100%; padding:10px 14px; background:rgba(255,255,255,.05); border:1.5px solid rgba(255,255,255,.1);
    border-radius:10px; color:#fff; font-family:'DM Sans',sans-serif; font-size:14px;
    outline:none; box-sizing:border-box; margin-bottom:16px;
}
.modal-input:focus { border-color:var(--teal); }
.modal-input::-webkit-calendar-picker-indicator { filter:invert(1) opacity(.5); cursor:pointer; }
.modal-actions { display:flex; gap:10px; margin-top:6px; }
.btn-modal-confirm { flex:1; padding:11px; background:var(--teal); color:#0a1628; font-weight:700; font-size:14px; border:none; border-radius:10px; cursor:pointer; font-family:'DM Sans',sans-serif; transition:all .2s; }
.btn-modal-confirm:hover { opacity:.9; }
.btn-modal-cancel { flex:1; padding:11px; background:rgba(255,255,255,.05); color:var(--muted); font-weight:600; font-size:14px; border:1px solid rgba(255,255,255,.1); border-radius:10px; cursor:pointer; font-family:'DM Sans',sans-serif; transition:all .2s; }
.btn-modal-cancel:hover { background:rgba(255,255,255,.1); color:#fff; }
.spin { width:16px;height:16px;border:2px solid rgba(10,22,40,.3);border-top-color:#0a1628;border-radius:50%;animation:spin .7s linear infinite;display:inline-block; }
@keyframes spin{to{transform:rotate(360deg);}}
.empty-state { text-align:center; padding:50px 20px; color:var(--muted); }
.empty-state i { font-size:48px; margin-bottom:14px; display:block; color:rgba(138,155,191,.3); }
@media(max-width:768px){ .hide-mobile { display:none; } table { font-size:12.5px; } td,th { padding:10px 8px; } }
</style>
@endpush

@section('content')
<div class="admin-page">
<div class="admin-card">

    <div class="page-title">
        Demo Requests
        @if($pending > 0)
            <span class="pending-badge">{{ $pending }} pending</span>
        @endif
    </div>
    <p class="page-sub">Manage and confirm user demo bookings</p>

    <div class="filter-tabs">
        <a href="{{ route('admin.demo.index') }}" class="tab-btn {{ $status === 'all' ? 'active' : '' }}">All</a>
        <a href="{{ route('admin.demo.index', ['status'=>'pending']) }}"   class="tab-btn {{ $status === 'pending'   ? 'active' : '' }}">Pending</a>
        <a href="{{ route('admin.demo.index', ['status'=>'confirmed']) }}" class="tab-btn {{ $status === 'confirmed' ? 'active' : '' }}">Confirmed</a>
        <a href="{{ route('admin.demo.index', ['status'=>'cancelled']) }}" class="tab-btn {{ $status === 'cancelled' ? 'active' : '' }}">Cancelled</a>
    </div>

    @if($demos->count())
    <div style="overflow-x:auto;">
    <table>
        <thead>
            <tr>
                <th>Name / Contact</th>
                <th class="hide-mobile">Company</th>
                <th>Requested Slot</th>
                <th class="hide-mobile">Confirmed At</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($demos as $demo)
            <tr>
                <td>
                    <div style="font-weight:600;color:#fff;">{{ $demo->name }}</div>
                    <div style="font-size:12px;color:var(--muted);">{{ $demo->email }}</div>
                    <div style="font-size:12px;color:var(--muted);">{{ $demo->phone }}</div>
                </td>
                <td class="hide-mobile">{{ $demo->company_name ?? '—' }}</td>
                <td>
                    <div>{{ \Carbon\Carbon::parse($demo->requested_date)->format('d M Y') }}</div>
                    <div style="font-size:12px;color:var(--muted);">{{ $demo->requested_time }}</div>
                </td>
                <td class="hide-mobile">
                    {{ $demo->confirmed_datetime ? \Carbon\Carbon::parse($demo->confirmed_datetime)->format('d M Y h:i A') : '—' }}
                </td>
                <td>
                    <span class="status-pill {{ $demo->status }}">
                        @if($demo->status === 'pending')   <i class="bi bi-hourglass-split"></i> Pending
                        @elseif($demo->status === 'confirmed') <i class="bi bi-check-circle-fill"></i> Confirmed
                        @else <i class="bi bi-x-circle-fill"></i> Cancelled
                        @endif
                    </span>
                </td>
                <td>
                    @if($demo->status === 'pending')
                        <button type="button" class="btn-sm btn-confirm" onclick="openConfirmModal({{ $demo->id }}, '{{ $demo->name }}', '{{ $demo->requested_date }}', '{{ addslashes($demo->requested_time) }}')">
                            <i class="bi bi-check-lg"></i> Confirm
                        </button>
                        <button type="button" class="btn-sm btn-cancel" style="margin-top:6px;" onclick="cancelDemo({{ $demo->id }}, this)">
                            <i class="bi bi-x-lg"></i> Cancel
                        </button>
                    @elseif($demo->status === 'confirmed')
                        <span style="font-size:12px;color:var(--teal);"><i class="bi bi-check-circle-fill"></i> Email sent</span>
                    @else
                        <span style="font-size:12px;color:var(--muted);">—</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    <div style="margin-top:20px;">{{ $demos->links() }}</div>
    @else
        <div class="empty-state">
            <i class="bi bi-calendar-x"></i>
            <p>No demo requests found.</p>
        </div>
    @endif

</div>
</div>

{{-- CONFIRM MODAL --}}
<div class="modal-bg" id="confirmModal">
    <div class="modal-box">
        <div class="modal-title">Confirm Demo</div>
        <p class="modal-sub" id="modalSubtitle">Set the confirmed date & time for the user</p>

        <label class="modal-label">Confirmed Date & Time <span style="color:var(--teal)">*</span></label>
        <input type="datetime-local" id="confirmedDatetime" class="modal-input" required>

        <label class="modal-label">Note to User (optional)</label>
        <textarea id="adminNote" class="modal-input" rows="3" placeholder="e.g. Join via Google Meet link we'll share..."></textarea>

        <div id="modalError" style="color:var(--danger);font-size:13px;margin-bottom:10px;display:none;"></div>

        <div class="modal-actions">
            <button type="button" class="btn-modal-cancel" onclick="closeModal()">Cancel</button>
            <button type="button" class="btn-modal-confirm" id="confirmBtn" onclick="submitConfirm()">
                <i class="bi bi-check-circle-fill"></i> Confirm & Send Email
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentDemoId = null;

function openConfirmModal(id, name, date, time) {
    console.log('Opening modal for:', { id, name, date, time });
    currentDemoId = id;

    const subtitle = document.getElementById('modalSubtitle');
    if (subtitle) {
        subtitle.textContent = 'Confirming demo for ' + name;
    }

    // Better date parsing - handle different formats
    let dateObj;
    try {
        // Try parsing as YYYY-MM-DD format
        if (date && date.includes('-')) {
            dateObj = new Date(date + 'T09:00:00');
        } else {
            dateObj = new Date(date);
        }

        if (isNaN(dateObj.getTime())) {
            throw new Error('Invalid date');
        }

        // Set to local datetime input format
        const localDate = new Date(dateObj.getTime() - (dateObj.getTimezoneOffset() * 60000));
        document.getElementById('confirmedDatetime').value = localDate.toISOString().slice(0, 16);
    } catch (e) {
        console.error('Date parsing error:', e, 'Using today instead');
        const today = new Date();
        const localDate = new Date(today.getTime() - (today.getTimezoneOffset() * 60000));
        document.getElementById('confirmedDatetime').value = localDate.toISOString().slice(0, 16);
    }

    document.getElementById('adminNote').value = '';
    document.getElementById('modalError').style.display = 'none';

    const modal = document.getElementById('confirmModal');
    if (modal) {
        modal.classList.add('open');
    }
}

function closeModal() {
    const modal = document.getElementById('confirmModal');
    if (modal) {
        modal.classList.remove('open');
    }
    currentDemoId = null;
}

// Close modal on background click - setup on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('confirmModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    }
});

function submitConfirm() {
    const dt   = document.getElementById('confirmedDatetime').value;
    const note = document.getElementById('adminNote').value;
    const btn  = document.getElementById('confirmBtn');
    const err  = document.getElementById('modalError');

    console.log('Submitting confirm with:', { demoId: currentDemoId, dt, note });

    if (!dt) {
        err.textContent = 'Please select a confirmed date & time.';
        err.style.display = 'block';
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="spin"></span> Sending...';
    err.style.display = 'none';

    // Get CSRF token from meta tag or use the blade token
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const appUrl = document.querySelector('meta[name="app-url"]').getAttribute('content');

    if (!csrfToken) {
        err.textContent = 'CSRF Token not found. Please refresh the page.';
        err.style.display = 'block';
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Confirm & Send Email';
        return;
    }

    fetch(`${appUrl}/admin/demo-requests/${currentDemoId}/confirm`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ confirmed_datetime: dt, admin_note: note })
    })
    .then(async r => {
        console.log('Response status:', r.status);
        const text = await r.text();
        console.log('Response body:', text.substring(0, 200));

        try {
            return JSON.parse(text);
        } catch(e) {
            console.error('JSON parse error:', e);
            throw new Error('Invalid response format');
        }
    })
    .then(data => {
        console.log('Success response:', data);
        if (data.success) {
            closeModal();
            setTimeout(() => location.reload(), 500);
        } else {
            err.textContent = data.message || 'Something went wrong.';
            err.style.display = 'block';
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Confirm & Send Email';
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        err.textContent = 'Error: ' + error.message;
        err.style.display = 'block';
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Confirm & Send Email';
    });
}

function cancelDemo(id, btn) {
    console.log('Cancelling demo:', id);
    if (!confirm('Are you sure you want to cancel this demo request?')) return;

    btn.disabled = true;
    const originalHTML = btn.innerHTML;
    btn.innerHTML = '<span class="spin"></span>';

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const appUrl = document.querySelector('meta[name="app-url"]').getAttribute('content');

    if (!csrfToken) {
        alert('Error: CSRF Token not found. Please refresh the page.');
        btn.disabled = false;
        btn.innerHTML = originalHTML;
        return;
    }

    fetch(`${appUrl}/admin/demo-requests/${id}/cancel`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ admin_note: 'Cancelled by admin.' })
    })
    .then(async r => {
        console.log('Cancel response status:', r.status);
        const text = await r.text();
        console.log('Cancel response:', text.substring(0, 200));
        try {
            return JSON.parse(text);
        } catch(e) {
            console.error('JSON parse error:', e);
            throw new Error('Invalid response format');
        }
    })
    .then(data => {
        console.log('Cancel success:', data);
        if (data.success) {
            setTimeout(() => location.reload(), 500);
        } else {
            btn.disabled = false;
            btn.innerHTML = originalHTML;
            alert('Error: ' + (data.message || 'Failed to cancel'));
        }
    })
    .catch(error => {
        console.error('Cancel fetch error:', error);
        btn.disabled = false;
        btn.innerHTML = originalHTML;
        alert('Error: ' + error.message);
    });
}
</script>
@endpush
