@extends('admin.layout.admin')
@section('title', 'Subscriptions — Admin')

@push('styles')
<style>
.sub-container{ padding:24px; background:#f0f2f8; }
.sub-cards{ display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:20px; }
.sub-card{ background:#fff; border-radius:14px; padding:18px 20px; box-shadow:0 2px 8px rgba(0,0,0,.07); }
.sub-card .lbl{ font-size:12px; color:#718096; font-weight:600; margin-bottom:6px; }
.sub-card .val{ font-size:26px; font-weight:800; color:#1a202c; }
.sub-card.trial .val{ color:#f59e0b; }
.sub-card.active .val{ color:#10b981; }
.sub-card.expired .val{ color:#ef4444; }
.sub-card.revenue .val{ color:#3b82f6; }

.filter-bar{ display:flex; gap:8px; margin-bottom:16px; }
.filter-pill{
    padding:7px 16px; border-radius:20px; font-size:12.5px; font-weight:600;
    text-decoration:none; color:#4a5568; background:#fff; border:1px solid #e2e8f0;
}
.filter-pill.active{ background:#0a1628; color:#fff; border-color:#0a1628; }

.table-card{ background:#fff; border-radius:14px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.07); }
table{ width:100%; border-collapse:collapse; font-size:13px; }
thead th{ background:#f7faff; padding:11px 14px; text-align:left; font-size:11px; font-weight:700; text-transform:uppercase; color:#718096; border-bottom:1.5px solid #e8edf5; }
tbody td{ padding:12px 14px; border-bottom:1px solid #f0f4ff; color:#2d3748; }
tbody tr:hover{ background:#fafcff; }

.status-pill{ font-size:11px; font-weight:700; padding:3px 10px; border-radius:20px; }
.status-trial{ background:#fef3c7; color:#92400e; }
.status-active{ background:#d1fae5; color:#065f46; }
.status-expired{ background:#fee2e2; color:#991b1b; }
.status-cancelled{ background:#e5e7eb; color:#374151; }
.status-pending{ background:#e0e7ff; color:#3730a3; }
.btn{ padding: 5px 10px; background: #667eea; color: white; border-radius: 4px; text-decoration: none; font-size: 12px; }

@media(max-width:900px){ .sub-cards{ grid-template-columns:1fr 1fr; } }
</style>
@endpush

@section('content')
<div class="sub-container">

    <h2 style="margin:0 0 18px;font-weight:700;color:#1a202c;">
        <i class="fas fa-credit-card" style="color:#667eea;margin-right:8px;"></i> Subscriptions &amp; Payments
        <a href="{{ route('admin.subscription.report') }}" style="float:right; text-decoration:none; background:#667eea; color:#fff; padding:6px 12px; border-radius:6px; font-size:12px;">View Full Report</a>

    </h2>

    <div class="sub-cards">
        <div class="sub-card trial"><div class="lbl">On Free Trial</div><div class="val">{{ $counts['trial'] }}</div></div>
        <div class="sub-card active"><div class="lbl">Active (Paid)</div><div class="val">{{ $counts['active'] }}</div></div>
        <div class="sub-card pending"><div class="lbl">Pending Payments</div><div class="val">{{ $counts['pending'] }}</div></div>
        <div class="sub-card expired"><div class="lbl">Expired / Locked</div><div class="val">{{ $counts['expired'] }}</div></div>
        <div class="sub-card revenue"><div class="lbl">Total Revenue</div><div class="val">₹{{ number_format($counts['revenue'], 0) }}</div></div>
    </div>

    <div class="filter-bar">
        <a href="{{ route('admin.subscription.index') }}" class="filter-pill {{ $filter=='all'?'active':'' }}">All</a>
        <a href="{{ route('admin.subscription.index', ['filter'=>'trial']) }}" class="filter-pill {{ $filter=='trial'?'active':'' }}">Trial</a>
        <a href="{{ route('admin.subscription.index', ['filter'=>'active']) }}" class="filter-pill {{ $filter=='active'?'active':'' }}">Paid / Active</a>
        <a href="{{ route('admin.subscription.index', ['filter'=>'expired']) }}" class="filter-pill {{ $filter=='expired'?'active':'' }}">Expired</a>
    </div>

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>User</th><th>Plan</th><th>Status</th>
                    <th>Trial Ends</th><th>Subscription Ends</th>
                    <th>GST Limit</th><th>Payments</th><th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $u)
                <tr>
                    <td>
                        <strong>{{ $u->name }}</strong><br>
                        <span style="color:#94a3b8;font-size:11.5px;">{{ $u->email }}</span>
                    </td>
                    <td>{{ ucfirst($u->plan_type ?? 'N/A') }}</td>
                    <td><span class="status-pill status-{{ $u->subscription_status }}">{{ ucfirst($u->subscription_status) }}</span></td>
                    <td>{{ $u->trial_ends_at ? $u->trial_ends_at->format('d M Y') : '—' }}</td>
                    <td>{{ $u->subscription_ends_at ? $u->subscription_ends_at->format('d M Y') : '—' }}</td>
                    <td>{{ $u->subscription_status === 'active' ? 'Unlimited (50)' : ($u->gstin_limit ?? 'N/A') }}</td>
                    <td>{{ $u->payments_count ?? 0 }}</td>
                    <td><a href="{{ route('admin.users.show', $u->id) }}" class="btn">View Details</a></td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center;padding:30px;color:#a0aec0;">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($pendingPayments->isNotEmpty())
    <h3 style="margin: 20px 0;">Pending Payments</h3>
    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>User</th><th>Plan</th><th>Amount</th><th>Status</th><th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pendingPayments as $p)
                <tr>
                    <td>{{ $p->user->name ?? 'Unknown' }}</td>
                    <td>{{ $p->plan->name ?? 'N/A' }}</td>
                    <td>₹{{ number_format($p->amount, 2) }}</td>
                    <td><span class="status-pill status-pending">Pending</span></td>
                    <td><a href="{{ route('admin.users.show', $p->user_id) }}" class="btn btn-sm">View Details</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div style="margin-top:16px;">{{ $users->links() }}</div>

</div>
@endsection