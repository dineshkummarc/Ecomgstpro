@extends('admin.layout.admin')
@section('title', 'Subscription Report — Admin')

@push('styles')
<style>
    .report-container { padding: 24px; background: #f0f2f8; }
    .stats-cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 20px; }
    .stat-card { background: #fff; border-radius: 14px; padding: 18px 20px; box-shadow: 0 2px 8px rgba(0,0,0,.07); }
    .stat-card .lbl { font-size: 12px; color: #718096; font-weight: 600; margin-bottom: 6px; }
    .stat-card .val { font-size: 26px; font-weight: 800; color: #1a202c; }
    .table-card { background: #fff; border-radius: 14px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.07); }
    table { width: 100%; border-collapse: collapse; font-size: 13px; }
    thead th { background: #f7faff; padding: 12px 14px; text-align: left; font-size: 11px; font-weight: 700; text-transform: uppercase; color: #718096; border-bottom: 1.5px solid #e8edf5; }
    tbody td { padding: 12px 14px; border-bottom: 1px solid #f0f4ff; color: #2d3748; }
    .status-pill { font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 20px; }
    .status-captured { background: #d1fae5; color: #065f46; }
    .status-pending { background: #e0e7ff; color: #3730a3; }
</style>
@endpush

@section('content')
<div class="report-container">
    <h2 style="margin: 0 0 18px; font-weight: 700; color: #1a202c;">Subscription Purchase Report</h2>

    <div class="stats-cards">
        <div class="stat-card"><div class="lbl">Total Successful Payments</div><div class="val">{{ $stats['successful'] }}</div></div>
        <div class="stat-card"><div class="lbl">Pending Payments</div><div class="val">{{ $stats['pending'] }}</div></div>
        <div class="stat-card"><div class="lbl">Total Revenue</div><div class="val">₹{{ number_format($stats['total_revenue'], 0) }}</div></div>
    </div>

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>User</th>
                    <th>Plan</th>
                    <th>Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments as $payment)
                <tr>
                    <td>{{ $payment->created_at->format('d M Y, H:i') }}</td>
                    <td>{{ $payment->user->name ?? 'Unknown' }}</td>
                    <td>{{ $payment->plan->name ?? 'N/A' }}</td>
                    <td>₹{{ number_format($payment->amount, 2) }}</td>
                    <td><span class="status-pill status-{{ in_array($payment->status, ['pending', 'created']) ? 'pending' : $payment->status }}">{{ ucfirst($payment->status) }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div style="margin-top: 16px;">{{ $payments->links() }}</div>
</div>
@endsection
 
