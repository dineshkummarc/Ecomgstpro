{{-- resources/views/client.blade.php --}}
@extends('layouts.gstr-main')

@section('title', 'Manage Clients')

@section('styles')
<style>
    .page-header { margin-bottom: 24px; }
    .page-title {
        font-family: 'Syne', sans-serif;
        font-weight: 800; font-size: 26px;
        color: var(--navy); margin: 0 0 4px;
    }
    .page-subtitle { color: #6b7a99; font-size: 14px; margin: 0; }

    .topbar {
        display: flex; align-items: center;
        justify-content: space-between;
        flex-wrap: wrap; gap: 12px;
        margin-bottom: 20px;
    }

    .search-box {
        display: flex; align-items: center; gap: 10px;
        background: white; border: 1.5px solid #e8edf5;
        border-radius: 10px; padding: 9px 14px;
        min-width: 260px; flex: 1;
    }
    .search-box i { color: #94a3b8; }
    .search-box input {
        border: none; outline: none;
        font-size: 13px; font-family: 'DM Sans', sans-serif;
        background: transparent; color: var(--navy);
        width: 100%;
    }

    .table-card {
        background: white;
        border: 1.5px solid #e8edf5;
        border-radius: 16px;
        overflow: hidden;
        margin-bottom: 32px;
    }

    table { width: 100%; border-collapse: collapse; font-size: 13px; }

    thead th {
        background: #f7faff;
        padding: 11px 16px;
        text-align: left;
        font-size: 11px; font-weight: 700;
        letter-spacing: .5px; text-transform: uppercase;
        color: #6b7a99;
        border-bottom: 1.5px solid #e8edf5;
        white-space: nowrap;
    }

    tbody td {
        padding: 13px 16px;
        border-bottom: 1px solid #f0f4ff;
        color: var(--navy); vertical-align: middle;
    }
    tbody tr:last-child td { border-bottom: none; }
    tbody tr:hover { background: #fafcff; }

    .client-cell { display: flex; align-items: center; gap: 10px; }
    .cl-avatar {
        width: 36px; height: 36px; border-radius: 10px;
        background: linear-gradient(135deg, var(--teal) 0%, var(--sky) 100%);
        display: flex; align-items: center; justify-content: center;
        font-family: 'Syne', sans-serif; font-weight: 800;
        font-size: 13px; color: white; flex-shrink: 0;
    }
    .cl-name  { font-weight: 700; font-size: 13px; color: var(--navy); }
    .cl-state { font-size: 11px; color: #94a3b8; margin-top: 1px; }

    .gstin-mono {
        font-family: 'Courier New', monospace;
        font-size: 12px; color: #6b7a99; letter-spacing: .3px;
    }

    .clickable-select {
        all: unset;
        cursor: pointer;
        display: block;
        width: 100%;
        text-align: left;
        transition: color .15s ease;
    }
    .clickable-select:hover .cl-name { color: var(--teal); }
    .clickable-select:hover .cl-state { color: #5eead4; }
    .gstin-clickable {
        all: unset;
        cursor: pointer;
        font-family: 'Courier New', monospace;
        font-size: 12px;
        color: #6b7a99;
        letter-spacing: .3px;
        transition: color .15s ease;
    }
    .gstin-clickable:hover { color: var(--teal); }

    .stt-badge {
        display: inline-block; font-size: 10px; font-weight: 700;
        padding: 3px 9px; border-radius: 20px;
        background: rgba(0,201,141,.1); color: #007050;
    }

    tr.is-selected { background: rgba(0,201,141,.04) !important; }
    tr.is-selected td:first-child { border-left: 3px solid var(--teal); }

    .selected-tag {
        display: inline-flex; align-items: center; gap: 4px;
        font-size: 10px; font-weight: 700;
        background: var(--teal); color: white;
        padding: 2px 8px; border-radius: 20px; margin-left: 6px;
    }

    .act-row { display: flex; gap: 6px; align-items: center; }
    .act-btn {
        width: 32px; height: 32px; border-radius: 8px;
        border: 1px solid #e8edf5; background: white;
        display: flex; align-items: center; justify-content: center;
        font-size: 14px; cursor: pointer; transition: all .15s;
        text-decoration: none;
    }
    .act-hist { color: #0284c7; }
    .act-hist:hover { background: #e0f2fe; border-color: #0284c7; }
    .act-edit { color: #7c3aed; }
    .act-edit:hover { background: #ede9fe; border-color: #7c3aed; }
    .act-del  { color: #ef4444; }
    .act-del:hover  { background: #fee2e2; border-color: #ef4444; }

    .batch-pill {
        display: inline-flex; align-items: center; gap: 4px;
        font-size: 11px; font-weight: 600; color: #6b7a99;
        background: #f0f4ff; border-radius: 20px;
        padding: 3px 10px;
    }
    .batch-pill.has-batches { color: #185fa5; background: #e6f1fb; }

    .pagination-bar {
        display: flex; align-items: center;
        justify-content: space-between;
        padding: 12px 16px;
        border-top: 1px solid #f0f4ff;
        font-size: 12px; color: #6b7a99;
    }
    .pg-btns { display: flex; gap: 4px; }
    .pg-btn {
        width: 28px; height: 28px; border-radius: 7px;
        border: 1px solid #e8edf5; background: white;
        font-size: 12px; cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        color: var(--navy); text-decoration: none;
    }
    .pg-btn.active { background: var(--teal); border-color: var(--teal); color: white; font-weight: 700; }

    .modal-overlay {
        display: none; position: fixed; inset: 0;
        background: rgba(0,0,0,.5); z-index: 1000;
        align-items: center; justify-content: center;
        padding: 16px;
    }
    .modal-overlay.show { display: flex; }
    .modal-box {
        background: white; border-radius: 20px;
        padding: 32px; width: 100%; max-width: 500px;
        position: relative; margin: auto;
        box-shadow: 0 20px 60px rgba(0,0,0,.2);
        max-height: 90vh; overflow-y: auto;
    }
    .modal-close {
        position: absolute; top: 16px; right: 16px;
        background: none; border: none; cursor: pointer;
        font-size: 22px; color: #94a3b8;
    }
    .modal-title {
        font-family: 'Syne', sans-serif; font-weight: 800;
        font-size: 20px; color: var(--navy);
        margin-bottom: 24px;
        display: flex; align-items: center; gap: 10px;
    }
    .form-group { margin-bottom: 16px; }
    .form-label {
        display: block; font-size: 11px; font-weight: 700;
        text-transform: uppercase; letter-spacing: .5px;
        color: #6b7a99; margin-bottom: 6px;
    }
    .form-control {
        width: 100%; padding: 11px 14px;
        border: 1.5px solid #e8edf5; border-radius: 12px;
        font-size: 14px; font-family: 'DM Sans', sans-serif;
        color: var(--navy); outline: none; transition: border-color .2s;
        box-sizing: border-box;
    }
    .form-control:focus { border-color: var(--teal); box-shadow: 0 0 0 3px rgba(0,201,141,.1); }
    .form-control.is-invalid { border-color: #ef4444; box-shadow: 0 0 0 3px rgba(239,68,68,.08); }
    .field-error {
        font-size: 11px; color: #b91c1c; margin-top: 5px;
        display: flex; align-items: center; gap: 4px;
    }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .modal-actions { display: flex; gap: 12px; margin-top: 24px; }
    .modal-actions button { flex: 1; justify-content: center; }

    .alert-box {
        padding: 14px 16px; border-radius: 12px;
        display: flex; align-items: flex-start; gap: 10px;
        margin-bottom: 20px; font-size: 13px;
    }
    .alert-success { background: rgba(0,201,141,.08); border: 1px solid rgba(0,201,141,.2); color: #005a3c; }
    .alert-error   { background: rgba(239,68,68,.08);  border: 1px solid rgba(239,68,68,.2);  color: #7f1d1d; }
    .alert-error ul { margin: 4px 0 0; padding-left: 18px; }

    /* ===== TOP ACCOUNT BAR (profile icon + dropdown) ===== */
    .top-account-bar {
        display: flex; align-items: center; justify-content: flex-end;
        margin-bottom: 14px;
    }
    .user-profile-widget { position: relative; }
    .user-profile-btn {
        display: flex; align-items: center; gap: 10px;
        background: white; border: 1.5px solid #e8edf5;
        border-radius: 30px; padding: 6px 14px 6px 6px;
        cursor: pointer; transition: all .15s ease;
    }
    .user-profile-btn:hover, .user-profile-btn.open {
        border-color: var(--teal);
        box-shadow: 0 4px 14px rgba(0,201,141,.12);
    }
    .user-avatar-sm {
        width: 34px; height: 34px; border-radius: 50%;
        background: linear-gradient(135deg, var(--teal) 0%, var(--sky) 100%);
        display: flex; align-items: center; justify-content: center;
        font-family: 'Syne', sans-serif; font-weight: 800;
        font-size: 13px; color: white; flex-shrink: 0;
    }
    .user-info-sm { text-align: left; line-height: 1.25; }
    .user-name-sm { font-size: 13px; font-weight: 700; color: var(--navy); }
    .user-plan-tag-sm { font-size: 11px; color: #6b7a99; }
    .user-chevron {
        font-size: 12px; color: #94a3b8; transition: transform .2s ease;
        margin-left: 2px;
    }
    .user-profile-btn.open .user-chevron { transform: rotate(180deg); color: var(--teal); }

    .user-dropdown {
        display: none;
        position: absolute; top: calc(100% + 10px); right: 0;
        width: 460px; max-width: 90vw;
        background: white; border: 1.5px solid #e8edf5; border-radius: 16px;
        box-shadow: 0 20px 50px rgba(10,22,40,.14);
        z-index: 500; overflow: hidden;
    }
    .user-dropdown.show { display: block; }

    .user-dropdown-header {
        display: flex; align-items: center; gap: 12px;
        padding: 18px 20px; border-bottom: 1px solid #f0f4ff;
        background: #f7faff;
    }
    .user-avatar-lg {
        width: 46px; height: 46px; border-radius: 50%;
        background: linear-gradient(135deg, var(--teal) 0%, var(--sky) 100%);
        display: flex; align-items: center; justify-content: center;
        font-family: 'Syne', sans-serif; font-weight: 800;
        font-size: 15px; color: white; flex-shrink: 0;
    }
    .user-dropdown-name { font-size: 14px; font-weight: 700; color: var(--navy); }
    .user-dropdown-email { font-size: 12px; color: #6b7a99; margin-top: 1px; }

    .profile-tabs { display: flex; gap: 4px; border-bottom: 1.5px solid #eef2f8; padding: 0 12px; flex-wrap: wrap; }
    .profile-tab {
        padding: 12px 12px; font-size: 12.5px; font-weight: 600; color: #6b7a99;
        cursor: pointer; border-bottom: 2.5px solid transparent;
        background: none; border-top: none; border-left: none; border-right: none;
    }
    .profile-tab.active { color: var(--teal); border-bottom-color: var(--teal); }
    .profile-panel { display: none; padding: 18px 20px; max-height: 60vh; overflow-y: auto; }
    .profile-panel.active { display: block; }

    .pinfo-grid { display: grid; grid-template-columns: repeat(2,1fr); gap: 12px; margin-bottom: 6px; }
    .pinfo-item { background: #f7faff; border-radius: 12px; padding: 12px 14px; }
    .pinfo-label { font-size: 10.5px; color: #8a9bbf; font-weight: 700; text-transform: uppercase; margin-bottom: 4px; }
    .pinfo-val { font-size: 14px; font-weight: 700; color: #0a1628; }

    .billing-table { width: 100%; border-collapse: collapse; font-size: 12.5px; margin-top: 6px; }
    .billing-table thead th {
        background: #f7faff; padding: 9px 10px; text-align: left; font-size: 10.5px;
        font-weight: 700; text-transform: uppercase; color: #6b7a99; border-bottom: 1.5px solid #e8edf5;
    }
    .billing-table tbody td { padding: 10px 10px; border-bottom: 1px solid #f0f4ff; color: #0a1628; }
    .billing-status { font-size: 10.5px; font-weight: 700; padding: 3px 9px; border-radius: 20px; background: #d1fae5; color: #065f46; }
    .btn-inv-download {
        display: inline-flex; align-items: center; gap: 6px; font-size: 11.5px; font-weight: 700;
        color: var(--teal); text-decoration: none; border: 1px solid rgba(0,201,141,.3);
        border-radius: 8px; padding: 5px 10px; transition: all .15s;
    }
    .btn-inv-download:hover { background: rgba(0,201,141,.08); }
    .no-billing { text-align: center; padding: 26px; color: #a0aec0; font-size: 12.5px; }

    .plan-cards-grid { display: grid; grid-template-columns: 1fr; gap: 10px; margin-top: 4px; }
    .plan-card {
        border: 1.5px solid #e8edf5; border-radius: 12px; padding: 14px 16px;
        display: flex; align-items: center; justify-content: space-between;
        gap: 12px; position: relative; transition: all .15s;
    }
    .plan-card.current { border-color: var(--teal); background: rgba(0,201,141,.04); }
    .plan-card.popular { border-color: #7c3aed; }
    .plan-card-badge {
        position: absolute; top: -9px; left: 16px;
        background: #7c3aed; color: white; font-size: 9.5px; font-weight: 700;
        padding: 2px 10px; border-radius: 20px;
    }
    .plan-card-name { font-family: 'Syne', sans-serif; font-weight: 800; font-size: 13.5px; color: var(--navy); }
    .plan-card-save { font-size: 10.5px; color: #007050; font-weight: 600; margin-top: 2px; }
    .plan-card-price { font-family: 'Syne', sans-serif; font-weight: 800; font-size: 17px; color: var(--navy); white-space: nowrap; }
    .plan-card-price span { font-size: 11px; font-weight: 600; color: #94a3b8; }
    .plan-card-btn {
        display: inline-block; text-align: center; padding: 7px 14px; border-radius: 9px;
        font-size: 12px; font-weight: 700; text-decoration: none; white-space: nowrap;
        background: var(--teal); color: white; box-sizing: border-box;
    }
    .plan-card-btn.outline { background: none; border: 1.5px solid #e8edf5; color: var(--navy); }
    .plan-card-btn.disabled { background: #f0f4ff; color: #94a3b8; pointer-events: none; }
    .plan-card-right { display: flex; flex-direction: column; align-items: flex-end; gap: 6px; }

    .dropdown-contact-note { font-size: 11.5px; color: #94a3b8; margin-top: 12px; text-align: center; }
    .dropdown-contact-note a { color: var(--teal); font-weight: 700; }

    /* ===== PLAN OVERVIEW SECTION (always visible on page) ===== */
    .plan-overview {
        display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: 16px;
        background: white; border: 1.5px solid #e8edf5; border-radius: 16px;
        padding: 18px 22px; margin-bottom: 22px;
    }
    .plan-overview.is-expired { border-color: rgba(239,68,68,.35); background: linear-gradient(180deg, rgba(239,68,68,.03), white 40%); }
    .plan-overview.is-trial   { border-color: rgba(217,119,6,.3);  background: linear-gradient(180deg, rgba(217,119,6,.03), white 40%); }
    .plan-overview.is-active  { border-color: rgba(0,201,141,.3);  background: linear-gradient(180deg, rgba(0,201,141,.03), white 40%); }

    .plan-overview-left { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; }
    .plan-overview-icon {
        width: 46px; height: 46px; border-radius: 12px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; font-size: 20px;
    }
    .plan-overview.is-expired .plan-overview-icon { background: rgba(239,68,68,.1); color: #dc2626; }
    .plan-overview.is-trial .plan-overview-icon   { background: rgba(217,119,6,.1); color: #b45309; }
    .plan-overview.is-active .plan-overview-icon  { background: rgba(0,201,141,.1); color: #007050; }

    .plan-overview-stats { display: flex; align-items: center; gap: 26px; flex-wrap: wrap; }
    .po-stat-label { font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .4px; color: #94a3b8; margin-bottom: 3px; }
    .po-stat-val { font-size: 15px; font-weight: 700; color: var(--navy); }
    .po-stat-val.text-warn { color: #b45309; }
    .po-stat-val.text-danger { color: #dc2626; }
    .po-stat-val.text-good { color: #007050; }
    .po-divider { width: 1px; height: 30px; background: #eef2f8; }

    .plan-upgrade-link {
        font-size: 12.5px; font-weight: 700; color: white; text-decoration: none;
        display: inline-flex; align-items: center; gap: 6px;
        background: var(--teal); padding: 9px 18px; border-radius: 10px;
        white-space: nowrap;
    }
    .plan-overview.is-expired .plan-upgrade-link { background: #dc2626; }
    .plan-overview.is-trial .plan-upgrade-link { background: #b45309; }

    @media (max-width: 768px) {
        .topbar { flex-direction: column; }
        .form-row { grid-template-columns: 1fr; }
        .gstin-mono { display: none; }
        .pinfo-grid { grid-template-columns: 1fr; }
        .plan-overview { flex-direction: column; align-items: stretch; }
        .plan-overview-stats { gap: 16px; }
        .user-dropdown { width: 92vw; right: -10px; }
        .user-info-sm { display: none; }
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

@if($errors->any())
<div class="alert-box alert-error">
    <i class="bi bi-exclamation-circle-fill" style="color:#ef4444"></i>
    <div>
        <strong>Please fix the following:</strong>
        <ul>
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
</div>
@endif

{{-- ===== TOP ACCOUNT BAR: profile icon + name -> dropdown with subscription/history/change-plan ===== --}}
@php
    $userInitials = collect(preg_split('/\s+/', trim($user->name ?? 'U')))
        ->filter()
        ->map(fn($n) => strtoupper(substr($n, 0, 1)))
        ->take(2)
        ->implode('');
    $planLabel = $user->hasActiveSubscription()
        ? ucfirst($user->plan_type).' Plan'
        : ($user->isOnTrial() ? 'Free Trial' : 'Expired Plan');
    $usedGstinCount = \App\Models\Client::where('user_id', $user->id)->count();
@endphp
<div class="top-account-bar">
    <div class="user-profile-widget" id="userProfileWidget">
        <button type="button" class="user-profile-btn" id="userProfileBtn" onclick="toggleUserDropdown(event)">
            <div class="user-avatar-sm">{{ $userInitials }}</div>
            <div class="user-info-sm">
                <div class="user-name-sm">{{ $user->name }}</div>
                <div class="user-plan-tag-sm">{{ $planLabel }}</div>
            </div>
            <i class="bi bi-chevron-down user-chevron"></i>
        </button>

        <div class="user-dropdown" id="userDropdown">
            <div class="user-dropdown-header">
                <div class="user-avatar-lg">{{ $userInitials }}</div>
                <div>
                    <div class="user-dropdown-name">{{ $user->name }}</div>
                    <div class="user-dropdown-email">{{ $user->email }}</div>
                </div>
            </div>

            <div class="profile-tabs">
                <button class="profile-tab active" onclick="switchProfileTab('sub', this)">Subscription</button>
                <button class="profile-tab" onclick="switchProfileTab('history', this)">Plan History &amp; Invoices</button>
                <button class="profile-tab" onclick="switchProfileTab('change', this)">Change Plan</button>
            </div>

            {{-- TAB 1: current subscription --}}
            <div class="profile-panel active" id="profileTab-sub">
                <div class="pinfo-grid">
                    <div class="pinfo-item">
                        <div class="pinfo-label">Current Plan</div>
                        <div class="pinfo-val">
                            @if($user->hasActiveSubscription())
                                {{ ucfirst($user->plan_type) }}
                            @elseif($user->isOnTrial())
                                Free Trial
                            @else
                                Expired
                            @endif
                        </div>
                    </div>
                    <div class="pinfo-item">
                        <div class="pinfo-label">Valid Until</div>
                        <div class="pinfo-val">
                            {{ $user->hasActiveSubscription() ? $user->subscription_ends_at->format('d M Y') : ($user->isOnTrial() ? $user->trial_ends_at->format('d M Y') : '—') }}
                        </div>
                    </div>
                    <div class="pinfo-item">
                        <div class="pinfo-label">GST Numbers Allowed</div>
                        <div class="pinfo-val">{{ $user->gstinLimitLabel() }}</div>
                    </div>
                    <div class="pinfo-item">
                        <div class="pinfo-label">GST Numbers Used</div>
                        <div class="pinfo-val">{{ $usedGstinCount }}</div>
                    </div>
                </div>

                @if(!$user->hasActiveSubscription())
                <a href="{{ route('subscription.pricing') }}" style="display:inline-block;margin-top:14px;background:linear-gradient(135deg,var(--teal),#00e0a8);color:var(--navy);font-weight:700;font-size:12.5px;padding:9px 18px;border-radius:10px;text-decoration:none;">
                    Upgrade / Renew Plan →
                </a>
                @endif
            </div>

            {{-- TAB 2: plan history + invoice downloads --}}
            <div class="profile-panel" id="profileTab-history">
                @if($billingHistory->count())
                <table class="billing-table">
                    <thead>
                        <tr>
                            <th>Plan</th><th>Amount</th><th>Period</th><th>Status</th><th>Invoice</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($billingHistory as $payment)
                        <tr>
                            <td>{{ $payment->plan->name ?? '—' }}</td>
                            <td>₹{{ number_format($payment->amount, 2) }}</td>
                            <td>
                                @if($payment->plan_started_at && $payment->plan_ends_at)
                                    {{ $payment->plan_started_at->format('d M Y') }} – {{ $payment->plan_ends_at->format('d M Y') }}
                                @else — @endif
                            </td>
                            <td><span class="billing-status">Paid</span></td>
                            <td>
                                @if($payment->invoice)
                                <a href="{{ route('invoice.download', $payment->invoice->id) }}" class="btn-inv-download">
                                    <i class="bi bi-download"></i> {{ $payment->invoice->invoice_number }}
                                </a>
                                @else
                                <span style="color:#a0aec0;font-size:11.5px;">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <div class="no-billing">
                    <i class="bi bi-receipt" style="font-size:28px;display:block;margin-bottom:8px;color:#dde3ef;"></i>
                    No purchases yet. Once you upgrade, your plan history and invoices will appear here.
                </div>
                @endif
            </div>

            {{-- TAB 3: change / upgrade plan --}}
            <div class="profile-panel" id="profileTab-change">
                <div class="plan-cards-grid">
                    {{-- MONTHLY --}}
                    <div class="plan-card {{ $user->hasActiveSubscription() && $user->plan_type === 'monthly' ? 'current' : '' }}">
                        <div>
                            <div class="plan-card-name">Monthly</div>
                            <div class="plan-card-save">&nbsp;</div>
                        </div>
                        <div class="plan-card-right">
                            <div class="plan-card-price">₹99 <span>/mo</span></div>
                            @if($user->hasActiveSubscription() && $user->plan_type === 'monthly')
                                <span class="plan-card-btn disabled">Current Plan</span>
                            @else
                                <a href="{{ route('subscription.pricing') }}" class="plan-card-btn outline">Choose</a>
                            @endif
                        </div>
                    </div>

                    {{-- QUARTERLY --}}
                    <div class="plan-card popular {{ $user->hasActiveSubscription() && $user->plan_type === 'quarterly' ? 'current' : '' }}">
                        <span class="plan-card-badge">Most Popular</span>
                        <div>
                            <div class="plan-card-name">Quarterly</div>
                            <div class="plan-card-save">Billed ₹285 every 3 months</div>
                        </div>
                        <div class="plan-card-right">
                            <div class="plan-card-price">₹95 <span>/mo</span></div>
                            @if($user->hasActiveSubscription() && $user->plan_type === 'quarterly')
                                <span class="plan-card-btn disabled">Current Plan</span>
                            @else
                                <a href="{{ route('subscription.pricing') }}" class="plan-card-btn">Choose</a>
                            @endif
                        </div>
                    </div>

                    {{-- YEARLY --}}
                    <div class="plan-card {{ $user->hasActiveSubscription() && $user->plan_type === 'yearly' ? 'current' : '' }}">
                        <div>
                            <div class="plan-card-name">Yearly</div>
                            <div class="plan-card-save">Billed ₹1,044 every year</div>
                        </div>
                        <div class="plan-card-right">
                            <div class="plan-card-price">₹87 <span>/mo</span></div>
                            @if($user->hasActiveSubscription() && $user->plan_type === 'yearly')
                                <span class="plan-card-btn disabled">Current Plan</span>
                            @else
                                <a href="{{ route('subscription.pricing') }}" class="plan-card-btn outline">Choose</a>
                            @endif
                        </div>
                    </div>
                </div>
                <p class="dropdown-contact-note">
                    Switching plans applies from your next payment. Need a custom plan? <a href="{{ route('contact') }}">Contact us</a>.
                </p>
            </div>
        </div>
    </div>
</div>

<div class="page-header">
    <h1 class="page-title">
        <i class="bi bi-people-fill" style="color:var(--teal);margin-right:8px"></i>Manage <em>Clients</em>
    </h1>
    <p class="page-subtitle">Click on client name or GSTIN to select • or add a new one</p>
</div>

{{-- ===== PLAN OVERVIEW SECTION (always visible) ===== --}}
@php
    $overviewState = $user->hasActiveSubscription() ? 'active' : ($user->isOnTrial() ? 'trial' : 'expired');
    $daysLeft = null;
    if ($overviewState === 'active') {
        $daysLeft = now()->startOfDay()->diffInDays($user->subscription_ends_at->copy()->startOfDay(), false);
    } elseif ($overviewState === 'trial') {
        $daysLeft = $user->trialDaysLeft();
    }
@endphp
<div class="plan-overview is-{{ $overviewState }}">
    <div class="plan-overview-left">
        <div class="plan-overview-icon">
            @if($overviewState === 'active')
                <i class="bi bi-shield-check"></i>
            @elseif($overviewState === 'trial')
                <i class="bi bi-hourglass-split"></i>
            @else
                <i class="bi bi-exclamation-triangle"></i>
            @endif
        </div>
        <div class="plan-overview-stats">
            <div>
                <div class="po-stat-label">Plan</div>
                <div class="po-stat-val">
                    @if($overviewState === 'active')
                        {{ ucfirst($user->plan_type) }}
                    @elseif($overviewState === 'trial')
                        Free Trial
                    @else
                        Expired
                    @endif
                </div>
            </div>
            <div class="po-divider"></div>
            <div>
                <div class="po-stat-label">{{ $overviewState === 'expired' ? 'Expired On' : 'Expires On' }}</div>
                <div class="po-stat-val">
                    @if($overviewState === 'active')
                        {{ $user->subscription_ends_at->format('d M Y') }}
                    @elseif($overviewState === 'trial')
                        {{ $user->trial_ends_at->format('d M Y') }}
                    @else
                        —
                    @endif
                </div>
            </div>
            <div class="po-divider"></div>
            <div>
                <div class="po-stat-label">Days Left</div>
                <div class="po-stat-val {{ $overviewState === 'trial' ? 'text-warn' : ($overviewState === 'expired' ? 'text-danger' : 'text-good') }}">
                    @if($overviewState === 'expired')
                        Access Paused
                    @else
                        {{ $daysLeft }} day{{ $daysLeft == 1 ? '' : 's' }}
                    @endif
                </div>
            </div>
            <div class="po-divider"></div>
            <div>
                <div class="po-stat-label">GST Numbers</div>
                <div class="po-stat-val">{{ $usedGstinCount }} / {{ $user->gstinLimitLabel() }}</div>
            </div>
        </div>
    </div>
    @if($overviewState !== 'active')
        <a href="{{ route('subscription.pricing') }}" class="plan-upgrade-link">
            {{ $overviewState === 'expired' ? 'Upgrade Now' : 'Upgrade Plan' }} <i class="bi bi-arrow-right"></i>
        </a>
    @endif
</div>

<!-- TOP BAR -->
<div class="topbar">
    <form method="GET" action="{{ route('gstr.clients.index') }}" style="flex:1;display:flex">
        <div class="search-box">
            <i class="bi bi-search"></i>
            <input type="text" name="search"
                   value="{{ request('search') }}"
                   placeholder="Search by name, GSTIN, state…"
                   onchange="this.form.submit()">
        </div>
    </form>
    <button class="btn-teal" onclick="openAddModal()" style="white-space:nowrap">
        <i class="bi bi-plus-lg"></i> Add Client
    </button>
</div>

<!-- CLIENTS TABLE -->
<div class="table-card">
    <table>
        <thead>
            <tr>
                <th style="width:40px">Sr.</th>
                <th>Client</th>
                <th>GSTIN</th>
                <th>State</th>
                <th>Batches</th>
                <th style="width:120px">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($clients as $i => $client)
            @php $isSelected = ($selectedClientId == $client->id); @endphp
            <tr class="{{ $isSelected ? 'is-selected' : '' }}">
                <td style="color:#94a3b8;font-size:12px">{{ $clients->firstItem() + $i }}</td>

                {{-- CLIENT NAME (clickable) --}}
                <td>
                    <form method="POST" action="{{ route('gstr.clients.select', $client->id) }}" style="margin:0;">
                        @csrf
                        <button type="submit" class="clickable-select" title="Click to select this client">
                            <div class="client-cell">
                                <div class="cl-avatar">{{ $client->initials }}</div>
                                <div>
                                    <div class="cl-name">
                                        {{ $client->name }}
                                        @if($isSelected)
                                            <span class="selected-tag"><i class="bi bi-check2"></i> Active</span>
                                        @endif
                                    </div>
                                    <div class="cl-state">{{ $client->state_name }}</div>
                                </div>
                            </div>
                        </button>
                    </form>
                </td>

                {{-- GSTIN (clickable) --}}
                <td>
                    <form method="POST" action="{{ route('gstr.clients.select', $client->id) }}" style="margin:0;">
                        @csrf
                        <button type="submit" class="gstin-clickable" title="Click to select this client">
                            {{ $client->gstin }}
                        </button>
                    </form>
                </td>

                <td>
                    <span class="stt-badge">{{ $client->state_code }} – {{ $client->state_name }}</span>
                </td>
                <td>
                    @php $bc = $client->batch_count ?? 0; @endphp
                    <span class="batch-pill {{ $bc > 0 ? 'has-batches' : '' }}">
                        <i class="bi bi-archive"></i>
                        {{ $bc }} batch{{ $bc != 1 ? 'es' : '' }}
                    </span>
                </td>
                <td>
                    <div class="act-row">
                        {{-- HISTORY --}}
                        <a href="{{ route('gstr.clients.history', $client->id) }}"
                           class="act-btn act-hist" title="View filing history">
                            <i class="bi bi-clock-history"></i>
                        </a>
                        {{-- EDIT --}}
                        <button class="act-btn act-edit"
                            onclick="editClient({{ $client->id }}, '{{ addslashes($client->name) }}', '{{ $client->gstin }}', '{{ $client->state_code }}', '{{ $client->filing_frequency }}')"
                            title="Edit client">
                            <i class="bi bi-pencil"></i>
                        </button>
                        {{-- DELETE --}}
                        <form method="POST" action="{{ route('gstr.clients.destroy', $client->id) }}" style="margin:0;"
                              onsubmit="return confirm('Delete client \'{{ addslashes($client->name) }}\'? All filing data will also be deleted.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="act-btn act-del" title="Delete client">
                                <i class="bi bi-trash3"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align:center;padding:48px;color:#94a3b8">
                    <i class="bi bi-people" style="font-size:40px;display:block;margin-bottom:12px;color:#dde3ef"></i>
                    No clients yet. Click <strong>Add Client</strong> to get started.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($clients instanceof \Illuminate\Pagination\LengthAwarePaginator && $clients->hasPages())
    <div class="pagination-bar">
        <div>Showing {{ $clients->firstItem() }}–{{ $clients->lastItem() }} of {{ $clients->total() }} clients</div>
        <div class="pg-btns">
            @foreach($clients->getUrlRange(1, $clients->lastPage()) as $page => $url)
            <a href="{{ $url }}" class="pg-btn {{ $page == $clients->currentPage() ? 'active' : '' }}">{{ $page }}</a>
            @endforeach
        </div>
    </div>
    @else
    <div class="pagination-bar">
        <div>{{ $clients->count() }} client{{ $clients->count() != 1 ? 's' : '' }}</div>
    </div>
    @endif
</div>

<!-- ADD/EDIT MODAL -->
@php
    $upHasValidationError = $errors->any();
    $upOldClientId        = old('client_id');
@endphp
<div class="modal-overlay {{ $upHasValidationError ? 'show' : '' }}" id="clientModal">
    <div class="modal-box">
        <button class="modal-close" onclick="closeModal()"><i class="bi bi-x-lg"></i></button>
        <div class="modal-title">
            <i class="bi bi-person-plus-fill" style="color:var(--teal)"></i>
            <span id="modalTitle">{{ $upOldClientId ? 'Edit Client' : 'Add New Client' }}</span>
        </div>
        <form id="clientForm" method="POST"
              action="{{ $upOldClientId ? route('gstr.clients.update', $upOldClientId) : route('gstr.clients.store') }}">
            @csrf
            @if($upOldClientId)
                @method('PUT')
            @endif
            <span id="methodField"></span>
            <input type="hidden" id="clientId" name="client_id" value="{{ $upOldClientId }}">

            <div class="form-group">
                <label class="form-label">Client / Business Name *</label>
                <input type="text" name="name" id="clientName"
                       class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                       placeholder="e.g. Ravi Textiles Pvt Ltd" value="{{ old('name') }}" required>
                @error('name')
                    <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label class="form-label">GSTIN *</label>
                <input type="text" name="gstin" id="clientGstin"
                       class="form-control {{ $errors->has('gstin') ? 'is-invalid' : '' }}"
                       placeholder="e.g. 29QRRPS3983N1ZD" maxlength="15"
                       style="text-transform:uppercase" value="{{ old('gstin') }}" required>
                @error('gstin')
                    <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div>
                @enderror
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Home State</label>
                    <select name="state_code" id="clientState" class="form-control">
                        <option value="01" {{ old('state_code') == '01' ? 'selected' : '' }}>01 – Jammu & Kashmir</option>
                        <option value="02" {{ old('state_code') == '02' ? 'selected' : '' }}>02 – Himachal Pradesh</option>
                        <option value="03" {{ old('state_code') == '03' ? 'selected' : '' }}>03 – Punjab</option>
                        <option value="04" {{ old('state_code') == '04' ? 'selected' : '' }}>04 – Chandigarh</option>
                        <option value="05" {{ old('state_code') == '05' ? 'selected' : '' }}>05 – Uttarakhand</option>
                        <option value="06" {{ old('state_code') == '06' ? 'selected' : '' }}>06 – Haryana</option>
                        <option value="07" {{ old('state_code') == '07' ? 'selected' : '' }}>07 – Delhi</option>
                        <option value="08" {{ old('state_code') == '08' ? 'selected' : '' }}>08 – Rajasthan</option>
                        <option value="09" {{ old('state_code') == '09' ? 'selected' : '' }}>09 – Uttar Pradesh</option>
                        <option value="10" {{ old('state_code') == '10' ? 'selected' : '' }}>10 – Bihar</option>
                        <option value="11" {{ old('state_code') == '11' ? 'selected' : '' }}>11 – Sikkim</option>
                        <option value="12" {{ old('state_code') == '12' ? 'selected' : '' }}>12 – Arunachal Pradesh</option>
                        <option value="13" {{ old('state_code') == '13' ? 'selected' : '' }}>13 – Nagaland</option>
                        <option value="14" {{ old('state_code') == '14' ? 'selected' : '' }}>14 – Manipur</option>
                        <option value="15" {{ old('state_code') == '15' ? 'selected' : '' }}>15 – Mizoram</option>
                        <option value="16" {{ old('state_code') == '16' ? 'selected' : '' }}>16 – Tripura</option>
                        <option value="17" {{ old('state_code') == '17' ? 'selected' : '' }}>17 – Meghalaya</option>
                        <option value="18" {{ old('state_code') == '18' ? 'selected' : '' }}>18 – Assam</option>
                        <option value="19" {{ old('state_code') == '19' ? 'selected' : '' }}>19 – West Bengal</option>
                        <option value="20" {{ old('state_code') == '20' ? 'selected' : '' }}>20 – Jharkhand</option>
                        <option value="21" {{ old('state_code') == '21' ? 'selected' : '' }}>21 – Odisha</option>
                        <option value="22" {{ old('state_code') == '22' ? 'selected' : '' }}>22 – Chhattisgarh</option>
                        <option value="23" {{ old('state_code') == '23' ? 'selected' : '' }}>23 – Madhya Pradesh</option>
                        <option value="24" {{ old('state_code') == '24' ? 'selected' : '' }}>24 – Gujarat</option>
                        <option value="26" {{ old('state_code') == '26' ? 'selected' : '' }}>26 – Dadra & Nagar Haveli</option>
                        <option value="27" {{ old('state_code') == '27' ? 'selected' : '' }}>27 – Maharashtra</option>
                        <option value="29" {{ old('state_code') == '29' ? 'selected' : '' }}>29 – Karnataka</option>
                        <option value="30" {{ old('state_code') == '30' ? 'selected' : '' }}>30 – Goa</option>
                        <option value="31" {{ old('state_code') == '31' ? 'selected' : '' }}>31 – Lakshadweep</option>
                        <option value="32" {{ old('state_code', '32') == '32' ? 'selected' : '' }}>32 – Kerala</option>
                        <option value="33" {{ old('state_code') == '33' ? 'selected' : '' }}>33 – Tamil Nadu</option>
                        <option value="34" {{ old('state_code') == '34' ? 'selected' : '' }}>34 – Puducherry</option>
                        <option value="35" {{ old('state_code') == '35' ? 'selected' : '' }}>35 – Andaman & Nicobar</option>
                        <option value="36" {{ old('state_code') == '36' ? 'selected' : '' }}>36 – Telangana</option>
                        <option value="37" {{ old('state_code') == '37' ? 'selected' : '' }}>37 – Andhra Pradesh</option>
                        <option value="38" {{ old('state_code') == '38' ? 'selected' : '' }}>38 – Ladakh</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Phone (Optional)</label>
                    <input type="tel" name="phone" id="clientPhone" class="form-control" placeholder="9876543210" value="{{ old('phone') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Filing Frequency *</label>
                    <select name="filing_frequency" id="clientFrequency" class="form-control" required>
                        <option value="monthly" {{ old('filing_frequency', 'monthly') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                        <option value="quarterly" {{ old('filing_frequency') == 'quarterly' ? 'selected' : '' }}>Quarterly (QRMP)</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Email (Optional)</label>
                <input type="email" name="email" id="clientEmail" class="form-control" placeholder="example@domain.com" value="{{ old('email') }}">
            </div>
            <div class="modal-actions">
                <button type="submit" class="btn-teal" style="justify-content:center">
                    <i class="bi bi-check-circle-fill"></i>
                    <span id="submitBtnText">{{ $upOldClientId ? 'Update Client' : 'Save Client' }}</span>
                </button>
                <button type="button" class="btn-outline-teal" onclick="closeModal()" style="justify-content:center">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
/* ===== USER PROFILE DROPDOWN ===== */
function toggleUserDropdown(e) {
    e.stopPropagation();
    document.getElementById('userDropdown').classList.toggle('show');
    document.getElementById('userProfileBtn').classList.toggle('open');
}
document.addEventListener('click', function(e) {
    const widget = document.getElementById('userProfileWidget');
    if (widget && !widget.contains(e.target)) {
        document.getElementById('userDropdown').classList.remove('show');
        document.getElementById('userProfileBtn').classList.remove('open');
    }
});

/* ===== PROFILE TABS (inside dropdown) ===== */
function switchProfileTab(tab, btn) {
    document.querySelectorAll('.profile-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.profile-panel').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('profileTab-' + tab).classList.add('active');
}

function openAddModal() {
    document.getElementById('clientForm').action = "{{ route('gstr.clients.store') }}";
    document.getElementById('methodField').innerHTML = '';
    document.getElementById('clientId').value = '';
    document.getElementById('clientForm').reset();
    document.getElementById('modalTitle').textContent = 'Add New Client';
    document.getElementById('submitBtnText').textContent = 'Save Client';
    document.getElementById('clientFrequency').value = 'monthly';
    document.getElementById('clientState').value = '07';
    document.getElementById('clientModal').classList.add('show');
    setTimeout(() => document.getElementById('clientName').focus(), 100);
}

function editClient(id, name, gstin, state, frequency) {
    document.getElementById('clientForm').action = `/gstpilot/public/gstr/clients/${id}`;
    document.getElementById('methodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    document.getElementById('clientId').value = id;
    document.getElementById('clientName').value = name;
    document.getElementById('clientGstin').value = gstin;
    document.getElementById('clientState').value = state;
    document.getElementById('clientFrequency').value = frequency || 'monthly';
    document.getElementById('modalTitle').textContent = 'Edit Client';
    document.getElementById('submitBtnText').textContent = 'Update Client';
    document.getElementById('clientModal').classList.add('show');
    setTimeout(() => document.getElementById('clientName').focus(), 100);
}

function closeModal() {
    document.getElementById('clientModal').classList.remove('show');
}

document.getElementById('clientModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

document.getElementById('clientGstin').addEventListener('input', function() {
    this.value = this.value.toUpperCase();
});

@if($errors->has('gstin'))
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('clientGstin').focus();
});
@endif
</script>
@endsection
