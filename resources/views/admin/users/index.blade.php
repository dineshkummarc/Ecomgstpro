@extends('admin.layout.admin')
@section('title', 'Registered Users — Admin')

@push('styles')
<style>

:root { --navy:#0a1628;--deep:#0d1f3c;--teal:#00c98d;--muted:#8a9bbf;--border:rgba(255,255,255,.08);--danger:#ff5c7a; }
.admin-page { min-height:100vh; background:linear-gradient(135deg,var(--navy) 0%,var(--deep) 60%,#0a2240 100%); padding:40px 20px; }
.admin-card { max-width:1200px; margin:0 auto; background:rgba(13,31,60,.85); backdrop-filter:blur(20px); border:1px solid var(--border); border-radius:20px; padding:32px; box-shadow:0 20px 50px rgba(0,0,0,.4); }
.page-title { font-family:'Syne',sans-serif; font-weight:800; font-size:24px; color:#fff; margin-bottom:6px; }
.page-sub { color:var(--muted); font-size:14px; margin-bottom:24px; }

.stats-row { display:grid; grid-template-columns:repeat(3,1fr); gap:14px; margin-bottom:24px; }
.stat-box { background:rgba(255,255,255,.03); border:1px solid var(--border); border-radius:14px; padding:16px 18px; }
.stat-box .lbl { font-size:12px; color:var(--muted); font-weight:600; text-transform:uppercase; margin-bottom:6px; }
.stat-box .val { font-size:26px; font-weight:800; color:#fff; }
.stat-box.green .val { color:var(--teal); }
.stat-box.red .val   { color:var(--danger); }

.toolbar { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:20px; align-items:center; }
.search-input {
    flex:1; min-width:220px; padding:10px 14px; background:rgba(255,255,255,.05);
    border:1.5px solid rgba(255,255,255,.1); border-radius:10px; color:#fff;
    font-family:'DM Sans',sans-serif; font-size:14px; outline:none;
}
.search-input:focus { border-color:var(--teal); }
.search-input::placeholder { color:rgba(255,255,255,.3); }
.filter-tabs { display:flex; gap:8px; }
.tab-btn { padding:9px 16px; border-radius:8px; border:1.5px solid rgba(255,255,255,.12); background:transparent; color:var(--muted); font-family:'DM Sans',sans-serif; font-weight:600; font-size:13px; cursor:pointer; text-decoration:none; }
.tab-btn.active { background:var(--teal); border-color:var(--teal); color:#0a1628; }
.btn-search { padding:10px 18px; background:var(--teal); color:#0a1628; border:none; border-radius:10px; font-weight:700; font-size:13.5px; cursor:pointer; }

table { width:100%; border-collapse:collapse; }
th { font-size:12px; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:.6px; padding:10px 14px; border-bottom:1px solid var(--border); text-align:left; }
td { padding:14px 14px; border-bottom:1px solid rgba(255,255,255,.04); font-size:13.5px; color:#c0cfe8; vertical-align:top; }
tr:last-child td { border-bottom:none; }
tr:hover td { background:rgba(255,255,255,.02); }

.status-pill { display:inline-flex; align-items:center; gap:5px; padding:4px 12px; border-radius:50px; font-size:12px; font-weight:700; }
.status-pill.active   { background:rgba(0,201,141,.12); color:var(--teal); border:1px solid rgba(0,201,141,.25); }
.status-pill.inactive { background:rgba(255,92,122,.12); color:var(--danger); border:1px solid rgba(255,92,122,.25); }

.batch-badge { background:rgba(0,170,255,.12); color:#00aaff; border:1px solid rgba(0,170,255,.25); border-radius:50px; padding:2px 10px; font-size:12px; font-weight:700; }
.btn-view { padding:6px 14px; border-radius:8px; font-size:12.5px; font-weight:600; cursor:pointer; border:1px solid rgba(0,201,141,.25); background:rgba(0,201,141,.15); color:var(--teal); text-decoration:none; display:inline-block; }
.btn-view:hover { background:var(--teal); color:#0a1628; }
.empty-state { text-align:center; padding:50px 20px; color:var(--muted); }
@media(max-width:768px){ .stats-row{grid-template-columns:1fr;} table{font-size:12.5px;} td,th{padding:10px 8px;} }

/* ===== PAGINATION FIX =====
   Laravel's default pagination view ships SVG arrow icons sized with
   Tailwind classes (h-5 w-5 etc). This admin panel doesn't load Tailwind,
   so those classes do nothing and the SVGs render at their raw/huge
   default size, causing the big black shape below the table.
   This forces the pagination SVGs (and the whole nav) back to a sane size
   and restyles the wrapper to match the rest of the admin UI. */
.admin-card nav[role="navigation"] { margin-top: 4px; }
.admin-card nav[role="navigation"] svg {
    width: 16px !important;
    height: 16px !important;
    display: inline-block;
    vertical-align: middle;
}
.admin-card nav[role="navigation"] span,
.admin-card nav[role="navigation"] a {
    font-family: 'DM Sans', sans-serif;
    font-size: 13px;
    color: var(--muted);
}
.admin-card nav[role="navigation"] a:hover {
    color: #fff;
}
.admin-card nav[role="navigation"] .relative.z-0.inline-flex,
.admin-card nav[role="navigation"] > div {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}
.admin-card nav[role="navigation"] a[aria-current="page"] span,
.admin-card nav[role="navigation"] span[aria-current="page"] span {
    background: var(--teal) !important;
    color: #0a1628 !important;
    border-radius: 8px;
    padding: 6px 12px;
    font-weight: 700;
}
</style>
@endpush

@section('content')
<div class="admin-page">
<div class="admin-card">

    <div class="page-title">Registered Users</div>
    <p class="page-sub">Manage all users registered on the platform</p>

    <div class="stats-row">
        <div class="stat-box"><div class="lbl">Total Users</div><div class="val">{{ $stats['total_users'] }}</div></div>
        <div class="stat-box green"><div class="lbl">Active</div><div class="val">{{ $stats['active_users'] }}</div></div>
        <div class="stat-box red"><div class="lbl">Inactive</div><div class="val">{{ $stats['inactive_users'] }}</div></div>
    </div>

    <form method="GET" action="{{ route('admin.users.index') }}" class="toolbar">
        <input type="text" name="search" class="search-input" placeholder="Search by name, email, phone, company..." value="{{ $search }}">
        <div class="filter-tabs">
            <button type="submit" name="status" value="all"      class="tab-btn {{ $status==='all'?'active':'' }}">All</button>
            <button type="submit" name="status" value="active"   class="tab-btn {{ $status==='active'?'active':'' }}">Active</button>
            <button type="submit" name="status" value="inactive" class="tab-btn {{ $status==='inactive'?'active':'' }}">Inactive</button>
        </div>
        <button type="submit" class="btn-search"><i class="bi bi-search"></i> Search</button>
    </form>

    @if($users->count())
    <div style="overflow-x:auto;">
    <table>
        <thead>
            <tr>
                <th>Name / Contact</th>
                <th class="hide-mobile">Company</th>
                <th>Registered</th>
                <th>Batches</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $u)
            <tr>
                <td>
                    <div style="font-weight:600;color:#fff;">{{ $u->name }}</div>
                    <div style="font-size:12px;color:var(--muted);">{{ $u->email }}</div>
                    <div style="font-size:12px;color:var(--muted);">{{ $u->phone ?? '—' }}</div>
                </td>
                <td>{{ $u->company_name ?? '—' }}</td>
                <td>{{ $u->created_at->format('d M Y') }}</td>
                <td><span class="batch-badge">{{ $u->batches_count }} batches</span></td>
                <td>
                    <span class="status-pill {{ $u->is_active ? 'active' : 'inactive' }}">
                        @if($u->is_active) <i class="bi bi-check-circle-fill"></i> Active
                        @else <i class="bi bi-x-circle-fill"></i> Inactive
                        @endif
                    </span>
                </td>
                <td>
                    <a href="{{ route('admin.users.show', $u->id) }}" class="btn-view">
                        <i class="bi bi-eye"></i> View
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    <div style="margin-top:20px;">{{ $users->links() }}</div>
    @else
        <div class="empty-state"><i class="bi bi-people" style="font-size:44px;display:block;margin-bottom:10px;"></i>No users found.</div>
    @endif

</div>
</div>
@endsection