@extends('admin.layout.admin')
@section('title', 'Analytics Dashboard — Admin')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<style>
html, body { overflow-x: hidden; }

.dashboard-container {
    padding: 24px;
    background: #f0f2f8;
    width: 100%;
    box-sizing: border-box;
}

/* ── HEADER ── */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 12px;
}
.page-header h2 {
    font-size: 22px;
    font-weight: 700;
    color: #1a202c;
    margin: 0 0 4px;
}
.page-header p { color: #718096; margin: 0; font-size: 13px; }

.header-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    padding: 6px 14px;
    font-size: 13px;
    color: #4a5568;
    box-shadow: 0 1px 4px rgba(0,0,0,.06);
    white-space: nowrap;
}
.header-badge .dot {
    width: 8px; height: 8px;
    background: #10b981;
    border-radius: 50%;
    animation: pulse 2s infinite;
}
@keyframes pulse { 0%,100%{opacity:1;} 50%{opacity:.4;} }

/* ── REVENUE HERO BANNER ── */
.revenue-hero {
    background: linear-gradient(135deg,#0f172a 0%,#1e293b 55%,#0f766e 130%);
    border-radius: 18px;
    padding: 26px 28px;
    color: #fff;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 18px;
    margin-bottom: 22px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(15,23,42,.25);
}
.revenue-hero::before {
    content: '';
    position: absolute;
    width: 240px; height: 240px;
    background: radial-gradient(circle, rgba(20,184,166,.25), transparent 70%);
    top: -80px; right: -60px;
}
.revenue-hero-left { position: relative; z-index: 1; }
.revenue-hero-label {
    font-size: 12px; text-transform: uppercase; letter-spacing: .8px;
    color: #94e8d5; font-weight: 700; margin-bottom: 6px;
    display: flex; align-items: center; gap: 6px;
}
.revenue-hero-value {
    font-size: 40px; font-weight: 800; line-height: 1;
    margin-bottom: 8px;
}
.revenue-hero-sub { font-size: 13px; color: #cbd5e1; }
.revenue-hero-right {
    display: flex; gap: 28px; position: relative; z-index: 1; flex-wrap: wrap;
}
.rh-mini-label { font-size: 11px; text-transform: uppercase; letter-spacing:.5px; color:#94a3b8; margin-bottom:4px; font-weight:600; }
.rh-mini-value { font-size: 22px; font-weight: 800; color: #fff; }
.rh-divider { width: 1px; background: rgba(255,255,255,.15); }

/* ── STAT CARDS ── */
.stats-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 20px;
}

.stat-card {
    border-radius: 14px;
    padding: 20px 18px;
    color: #fff;
    position: relative;
    overflow: hidden;
    box-shadow: 0 4px 14px rgba(0,0,0,.12);
    transition: transform .25s, box-shadow .25s;
}
.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 24px rgba(0,0,0,.18);
}
.stat-card::after {
    content: '';
    position: absolute;
    width: 90px; height: 90px;
    border-radius: 50%;
    background: rgba(255,255,255,.08);
    top: -18px; right: -18px;
}
.stat-card.purple { background: linear-gradient(135deg,#667eea,#764ba2); }
.stat-card.orange { background: linear-gradient(135deg,#f59e0b,#d97706); }
.stat-card.green  { background: linear-gradient(135deg,#10b981,#059669); }
.stat-card.blue   { background: linear-gradient(135deg,#3b82f6,#1d4ed8); }
.stat-card.pink   { background: linear-gradient(135deg,#ec4899,#be185d); }
.stat-card.teal   { background: linear-gradient(135deg,#14b8a6,#0f766e); }
.stat-card.gold   { background: linear-gradient(135deg,#fbbf24,#b45309); }
.stat-card.indigo { background: linear-gradient(135deg,#6366f1,#4338ca); }

.stat-label { font-size: 12px; opacity:.85; font-weight:500; margin-bottom:8px; }
.stat-value { font-size: 30px; font-weight:800; margin-bottom:4px; line-height:1; }
.stat-sub   { font-size: 12px; opacity:.75; }
.stat-icon  { position:absolute; right:16px; bottom:14px; font-size:32px; opacity:.18; }
.stat-trend {
    position: absolute; top: 16px; right: 16px;
    font-size: 10.5px; font-weight: 700;
    background: rgba(255,255,255,.18);
    padding: 3px 8px; border-radius: 20px;
    display: flex; align-items: center; gap: 3px;
}

/* ── 2 COL GRID ── */
.grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 20px;
}

/* ── WHITE CARD ── */
.card-white {
    background: #fff;
    border-radius: 14px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,.07);
    width: 100%;
    box-sizing: border-box;
}
.card-title {
    font-size: 14px;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
    border-bottom: 2px solid #f3f4f6;
    padding-bottom: 10px;
}
.card-title i { color: #667eea; }

/* ── BAR CHART ── */
.bar-chart { display:flex; align-items:flex-end; gap:8px; height:110px; }
.bar-wrap  { display:flex; flex-direction:column; align-items:center; flex:1; height:100%; justify-content:flex-end; }
.bar { width:100%; border-radius:5px 5px 0 0; background:linear-gradient(180deg,#667eea,#764ba2); transition:height .6s ease; min-height:4px; }
.bar-label { font-size:10px; color:#718096; margin-top:5px; }

/* ── ACTIVITY TABLE ── */
.act-table { width:100%; border-collapse:collapse; }
.act-table thead th {
    text-align:left; padding:9px 10px;
    font-size:11px; font-weight:700;
    color:#718096; text-transform:uppercase;
    letter-spacing:.5px;
    border-bottom:2px solid #f3f4f6;
}
.act-table tbody td {
    padding:10px; border-bottom:1px solid #f9fafb;
    font-size:13px; color:#2d3748;
    vertical-align:middle;
}
.act-table tbody tr:hover { background:#f9fafb; }
.act-table tbody tr:last-child td { border-bottom:none; }

.badge-green  { background:#d1fae5; color:#065f46; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; }
.badge-red    { background:#fee2e2; color:#991b1b; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; }
.badge-gold   { background:#fef3c7; color:#92400e; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; }

.avatar-sm {
    width:30px; height:30px; border-radius:50%;
    background:linear-gradient(135deg,#667eea,#764ba2);
    display:inline-flex; align-items:center; justify-content:center;
    color:#fff; font-weight:700; font-size:12px; flex-shrink:0;
}
.avatar-sm.gold { background:linear-gradient(135deg,#fbbf24,#b45309); }

/* ── ACTIVITY CARDS — 3 per row ── */
.activity-cards-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-bottom: 20px;
}
.activity-mini-card {
    background: #fff;
    border-radius: 12px;
    padding: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,.07);
    display: flex;
    align-items: center;
    gap: 14px;
    transition: transform .2s;
}
.activity-mini-card:hover { transform: translateY(-2px); }
.activity-mini-icon {
    width: 46px; height: 46px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}
.activity-mini-icon.purple { background:linear-gradient(135deg,#667eea,#764ba2); color:#fff; }
.activity-mini-icon.green  { background:linear-gradient(135deg,#10b981,#059669); color:#fff; }
.activity-mini-icon.orange { background:linear-gradient(135deg,#f59e0b,#d97706); color:#fff; }
.activity-mini-icon.blue   { background:linear-gradient(135deg,#3b82f6,#1d4ed8); color:#fff; }
.activity-mini-icon.pink   { background:linear-gradient(135deg,#ec4899,#be185d); color:#fff; }
.activity-mini-icon.teal   { background:linear-gradient(135deg,#14b8a6,#0f766e); color:#fff; }
.activity-mini-content h4  { margin:0; font-size:20px; font-weight:800; color:#2d3748; }
.activity-mini-content p   { margin:4px 0 0; font-size:12px; color:#718096; }

/* ── SYSTEM INFO ── */
.sys-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
}
.sys-item {
    background: #f9fafb;
    border-radius: 10px;
    padding: 14px 16px;
    border-left: 3px solid #667eea;
}
.sys-item .sys-label { font-size:11px; color:#718096; margin-bottom:4px; font-weight:600; text-transform:uppercase; }
.sys-item .sys-val   { font-size:15px; font-weight:700; color:#2d3748; }

.empty-state { text-align:center; padding:30px; color:#a0aec0; }
.empty-state i { font-size:36px; display:block; margin-bottom:10px; opacity:.35; }

@media(max-width:1200px) {
    .stats-row { grid-template-columns: repeat(3,1fr); }
}
@media(max-width:1100px) {
    .stats-row { grid-template-columns: repeat(2,1fr); }
    .activity-cards-row { grid-template-columns: repeat(2,1fr); }
    .sys-grid { grid-template-columns: repeat(2,1fr); }
    .revenue-hero { flex-direction: column; align-items: flex-start; }
}
@media(max-width:768px) {
    .dashboard-container { padding:14px; }
    .stats-row { grid-template-columns:1fr 1fr; }
    .grid-2 { grid-template-columns:1fr; }
    .activity-cards-row { grid-template-columns:1fr; }
    .sys-grid { grid-template-columns:1fr; }
    .page-header { flex-direction:column; align-items:flex-start; }
    .revenue-hero-right { gap: 16px; }
}
@media(max-width:480px) {
    .stats-row { grid-template-columns:1fr; }
}
</style>
@endpush

@section('content')
<div class="dashboard-container">

    {{-- HEADER --}}
    <div class="page-header">
        <div>
            <h2><i class="fas fa-chart-line" style="color:#667eea;margin-right:8px;"></i>Analytics Dashboard</h2>
            <p>EcomgstPro — Admin Control Panel &nbsp;|&nbsp; {{ now()->format('l, d M Y') }}</p>
        </div>
        <div class="header-badge">
            <span class="dot"></span>
            System Online &nbsp;|&nbsp; {{ now()->format('h:i A') }}
        </div>
    </div>

    {{-- REVENUE HERO BANNER --}}
    <div class="revenue-hero">
        <div class="revenue-hero-left">
            <div class="revenue-hero-label"><i class="fas fa-wallet"></i> Total Revenue Collected</div>
            <div class="revenue-hero-value">₹{{ number_format($total_revenue ?? 0, 2) }}</div>
            <div class="revenue-hero-sub">From {{ number_format($total_purchases ?? 0) }} paid subscription{{ ($total_purchases ?? 0) == 1 ? '' : 's' }} — all time</div>
        </div>
        <div class="revenue-hero-right">
            <div>
                <div class="rh-mini-label">This Month</div>
                <div class="rh-mini-value">₹{{ number_format($revenue_this_month ?? 0) }}</div>
            </div>
            <div class="rh-divider"></div>
            <div>
                <div class="rh-mini-label">Active Subscribers</div>
                <div class="rh-mini-value">{{ number_format($active_subscribers ?? 0) }}</div>
            </div>
            <div class="rh-divider"></div>
            <div>
                <div class="rh-mini-label">Avg. Order Value</div>
                <div class="rh-mini-value">₹{{ number_format(($total_purchases ?? 0) > 0 ? ($total_revenue ?? 0) / $total_purchases : 0) }}</div>
            </div>
        </div>
    </div>

    {{-- STATS — real numbers only, no dummy zeros --}}
    <div class="stats-row">
        <div class="stat-card purple">
            <div class="stat-label">Total Users</div>
            <div class="stat-value">{{ number_format($total_users ?? 0) }}</div>
            <div class="stat-sub">Registered accounts</div>
            <i class="fas fa-users stat-icon"></i>
        </div>
        <div class="stat-card blue">
            <div class="stat-label">New Today</div>
            <div class="stat-value">{{ number_format($users_today ?? 0) }}</div>
            <div class="stat-sub">Signups today</div>
            <i class="fas fa-user-plus stat-icon"></i>
        </div>
        <div class="stat-card teal">
            <div class="stat-label">Logins Today</div>
            <div class="stat-value">{{ number_format($logins_today ?? 0) }}</div>
            <div class="stat-sub">Active sessions today</div>
            <i class="fas fa-sign-in-alt stat-icon"></i>
        </div>
        <div class="stat-card green">
            <div class="stat-label">Total Logins</div>
            <div class="stat-value">{{ number_format($total_logins ?? 0) }}</div>
            <div class="stat-sub">All time login count</div>
            <i class="fas fa-history stat-icon"></i>
        </div>
        <div class="stat-card gold">
            <div class="stat-label">Total Revenue</div>
            <div class="stat-value">₹{{ number_format($total_revenue ?? 0) }}</div>
            <div class="stat-sub">All-time collected</div>
            <i class="fas fa-rupee-sign stat-icon"></i>
        </div>
        <div class="stat-card indigo">
            <div class="stat-label">Subscriptions Sold</div>
            <div class="stat-value">{{ number_format($total_purchases ?? 0) }}</div>
            <div class="stat-sub">Paid plan purchases</div>
            <i class="fas fa-credit-card stat-icon"></i>
        </div>
        <div class="stat-card orange">
            <div class="stat-label">Pending Demos</div>
            <div class="stat-value">{{ number_format($pending_demos ?? 0) }}</div>
            <div class="stat-sub">Awaiting confirmation</div>
            <i class="fas fa-calendar-check stat-icon"></i>
        </div>
        <div class="stat-card pink">
            <div class="stat-label">Total Queries</div>
            <div class="stat-value">{{ number_format($total_queries ?? 0) }}</div>
            <div class="stat-sub">Customer inquiries</div>
            <i class="fas fa-comments stat-icon"></i>
        </div>
    </div>

    {{-- ACTIVITY MINI CARDS — 3 per row --}}
    <div class="activity-cards-row">
        <div class="activity-mini-card">
            <div class="activity-mini-icon purple"><i class="fas fa-boxes"></i></div>
            <div class="activity-mini-content">
                <h4>{{ $total_products ?? 0 }}</h4>
                <p>Active Products</p>
            </div>
        </div>
        <div class="activity-mini-card">
            <div class="activity-mini-icon orange"><i class="fas fa-tags"></i></div>
            <div class="activity-mini-content">
                <h4>{{ $total_categories ?? 0 }}</h4>
                <p>Categories</p>
            </div>
        </div>
        <div class="activity-mini-card">
            <div class="activity-mini-icon teal"><i class="fas fa-comments"></i></div>
            <div class="activity-mini-content">
                <h4>{{ $total_queries ?? 0 }}</h4>
                <p>Customer Queries</p>
            </div>
        </div>
        <div class="activity-mini-card">
            <div class="activity-mini-icon green"><i class="fas fa-user-check"></i></div>
            <div class="activity-mini-content">
                <h4>{{ $total_users ?? 0 }}</h4>
                <p>Total Members</p>
            </div>
        </div>
        <div class="activity-mini-card">
            <div class="activity-mini-icon blue"><i class="fas fa-calendar-alt"></i></div>
            <div class="activity-mini-content">
                <h4>{{ $pending_demos ?? 0 }}</h4>
                <p>Demo Requests</p>
            </div>
        </div>
        <div class="activity-mini-card">
            <div class="activity-mini-icon pink"><i class="fas fa-chart-bar"></i></div>
            <div class="activity-mini-content">
                <h4>{{ $logins_today ?? 0 }}</h4>
                <p>Today's Sessions</p>
            </div>
        </div>
    </div>

    {{-- CHART + RECENT USERS --}}
    <div class="grid-2" style="margin-bottom:20px;">
        <div class="card-white">
            <div class="card-title">
                <i class="fas fa-chart-bar"></i> New Signups — Last 7 Days
            </div>
            @php
                $maxVal = max(array_column($signups_week ?? [['count'=>1]], 'count') ?: [1]);
            @endphp
            <div class="bar-chart">
                @forelse($signups_week ?? [] as $day)
                    @php $h = $maxVal > 0 ? max(round(($day['count']/$maxVal)*100),4) : 4; @endphp
                    <div class="bar-wrap">
                        <div class="bar" style="height:{{ $h }}%;" title="{{ $day['count'] }} signups"></div>
                        <div class="bar-label">{{ \Carbon\Carbon::parse($day['date'])->format('D') }}</div>
                    </div>
                @empty
                    <div style="color:#a0aec0;font-size:13px;margin:auto;">No signup data yet</div>
                @endforelse
            </div>
        </div>

        <div class="card-white">
            <div class="card-title">
                <i class="fas fa-user-clock"></i> Recent Registrations
            </div>
            <table class="act-table">
                <thead>
                    <tr><th>User</th><th>Email</th><th>Joined</th></tr>
                </thead>
                <tbody>
                    @forelse($recent_users ?? [] as $user)
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <div class="avatar-sm">{{ strtoupper(substr($user->name ?? 'U',0,1)) }}</div>
                                <span style="font-weight:600;">{{ $user->name ?? '—' }}</span>
                            </div>
                        </td>
                        <td style="color:#718096;font-size:12px;">{{ $user->email ?? '—' }}</td>
                        <td style="color:#718096;font-size:12px;">{{ \Carbon\Carbon::parse($user->created_at)->diffForHumans() }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3"><div class="empty-state"><i class="fas fa-users"></i>No users yet</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- RECENT SUBSCRIPTION PURCHASES --}}
    <div class="card-white" style="margin-bottom:20px;">
        <div class="card-title">
            <i class="fas fa-receipt"></i> Recent Subscription Purchases
            <span style="margin-left:auto;font-size:12px;color:#718096;font-weight:400;">
                <i class="fas fa-rupee-sign"></i> Latest 8 payments
            </span>
        </div>
        <table class="act-table">
            <thead>
                <tr>
                    <th>User</th><th>Plan</th><th>Amount</th><th>Date</th><th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recent_payments ?? [] as $payment)
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div class="avatar-sm gold">{{ strtoupper(substr($payment->user->name ?? 'U',0,1)) }}</div>
                            <span style="font-weight:600;">{{ $payment->user->name ?? '—' }}</span>
                        </div>
                    </td>
                    <td style="color:#718096;font-size:12px;">{{ ucfirst($payment->plan->name ?? $payment->plan_type ?? '—') }}</td>
                    <td style="font-weight:700;">₹{{ number_format($payment->amount ?? 0, 2) }}</td>
                    <td style="color:#718096;font-size:12px;">{{ optional($payment->created_at)->format('d M, h:i A') ?? '—' }}</td>
                    <td><span class="badge-gold">Paid</span></td>
                </tr>
                @empty
                <tr>
                    <td colspan="5">
                        <div class="empty-state">
                            <i class="fas fa-receipt"></i>No purchases yet
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ACTIVITY LOG --}}
    <div class="card-white" style="margin-bottom:20px;">
        <div class="card-title">
            <i class="fas fa-history"></i> Recent Admin Activity
            <span style="margin-left:auto;font-size:12px;color:#718096;font-weight:400;">
                <i class="fas fa-clock"></i> Last 24 hours
            </span>
        </div>
        <table class="act-table">
            <thead>
                <tr>
                    <th>#</th><th>Admin</th><th>Action</th>
                    <th>IP Address</th><th>Time</th><th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($admin_activities ?? [] as $activity)
                <tr>
                    <td style="color:#a0aec0;">{{ $loop->iteration }}</td>
                    <td>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div class="avatar-sm">{{ strtoupper(substr($activity['admin_name'] ?? 'A',0,1)) }}</div>
                            <strong>{{ $activity['admin_name'] ?? 'Admin' }}</strong>
                        </div>
                    </td>
                    <td>
                        <i class="fas fa-{{ ($activity['action']??'') == 'Login' ? 'sign-in-alt':'sign-out-alt' }}"
                           style="color:{{ ($activity['action']??'') == 'Login' ? '#10b981':'#ef4444' }};margin-right:5px;"></i>
                        {{ $activity['action'] ?? 'Login' }}
                    </td>
                    <td style="color:#718096;font-size:12px;">{{ $activity['ip_address'] ?? '—' }}</td>
                    <td style="color:#718096;font-size:12px;">{{ \Carbon\Carbon::parse($activity['created_at'] ?? now())->format('d M, h:i A') }}</td>
                    <td>
                        @if(($activity['action']??'') == 'Login')
                            <span class="badge-green">Login</span>
                        @else
                            <span class="badge-red">Logout</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <i class="fas fa-info-circle"></i>No recent activity found
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- SYSTEM INFO — 3 per row --}}
    <div class="card-white">
        <div class="card-title">
            <i class="fas fa-server"></i> System Information
        </div>
        <div class="sys-grid">
            <div class="sys-item">
                <div class="sys-label">Logged In As</div>
                <div class="sys-val">
                    <i class="fas fa-user-shield" style="color:#667eea;margin-right:6px;"></i>
                    {{ Auth::guard('admin')->user()->name ?? 'Admin' }}
                </div>
            </div>
            <div class="sys-item">
                <div class="sys-label">Role</div>
                <div class="sys-val">
                    <i class="fas fa-crown" style="color:#f59e0b;margin-right:6px;"></i>
                    {{ ucfirst(Auth::guard('admin')->user()->role ?? 'super_admin') }}
                </div>
            </div>
            <div class="sys-item">
                <div class="sys-label">Server Time</div>
                <div class="sys-val">
                    <i class="fas fa-clock" style="color:#10b981;margin-right:6px;"></i>
                    {{ now()->format('d M Y, h:i A') }}
                </div>
            </div>
            <div class="sys-item">
                <div class="sys-label">System Status</div>
                <div class="sys-val" style="color:#10b981;">
                    <i class="fas fa-check-circle" style="margin-right:6px;"></i> All Systems Active
                </div>
            </div>
            <div class="sys-item">
                <div class="sys-label">PHP Version</div>
                <div class="sys-val">
                    <i class="fab fa-php" style="color:#764ba2;margin-right:6px;"></i>
                    {{ phpversion() }}
                </div>
            </div>
            <div class="sys-item">
                <div class="sys-label">Laravel Version</div>
                <div class="sys-val">
                    <i class="fas fa-code" style="color:#3b82f6;margin-right:6px;"></i>
                    {{ app()->version() }}
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const cards = document.querySelectorAll('.stat-card, .card-white, .activity-mini-card');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    });
    cards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(16px)';
        card.style.transition = 'all 0.4s ease';
        observer.observe(card);
    });
});
</script>
@endpush
