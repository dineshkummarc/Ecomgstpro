@extends('admin.layout.admin') 

{{-- ============================================================
     Admin — Contact Messages List
     ============================================================ --}}

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

<style>
:root{
    --navy:#0a1628; --deep:#0d1f3c; --teal:#00c98d; --sky:#00aaff;
    --teal-low:rgba(0,201,141,.12); --muted:#8a9bbf; --border:#e8edf5;
}
.ac-wrap{font-family:'DM Sans',sans-serif;padding:32px;background:#f4f7fc;min-height:100vh}
.ac-header{display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:16px;margin-bottom:28px}
.ac-title{font-family:'Syne',sans-serif;font-weight:800;font-size:28px;color:var(--navy)}
.ac-sub{font-size:13.5px;color:#64748b;margin-top:4px}

.ac-tabs{display:flex;gap:10px;margin-bottom:24px;flex-wrap:wrap}
.ac-tab{
    display:inline-flex;align-items:center;gap:8px;padding:9px 18px;border-radius:30px;
    font-size:13px;font-weight:600;text-decoration:none;color:#475569;background:#fff;
    border:1px solid var(--border);transition:all .2s;
}
.ac-tab:hover{border-color:var(--teal);color:var(--teal)}
.ac-tab.active{background:var(--navy);color:#fff;border-color:var(--navy)}
.ac-tab .badge-count{
    background:rgba(255,255,255,.2);border-radius:20px;padding:1px 8px;font-size:11px;
}
.ac-tab:not(.active) .badge-count{background:#eef2f8;color:#64748b}

.ac-table-card{background:#fff;border-radius:18px;border:1px solid var(--border);overflow:hidden;box-shadow:0 6px 24px rgba(10,22,40,.04)}
table.ac-table{width:100%;border-collapse:collapse}
table.ac-table thead th{
    background:#f7faff;color:#64748b;font-size:11.5px;text-transform:uppercase;letter-spacing:.5px;
    font-weight:700;padding:14px 20px;text-align:left;border-bottom:1px solid var(--border);
}
table.ac-table tbody td{padding:16px 20px;font-size:13.5px;color:#374151;border-bottom:1px solid #f1f5f9;vertical-align:middle}
table.ac-table tbody tr:hover{background:#f8fafc}
table.ac-table tbody tr:last-child td{border-bottom:none}

.row-name{font-weight:700;color:var(--navy);font-family:'Syne',sans-serif;font-size:14px}
.row-email{color:#64748b;font-size:12.5px}
.row-msg-preview{color:#64748b;max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:block}

.status-pill{
    display:inline-flex;align-items:center;gap:6px;font-size:11.5px;font-weight:700;
    padding:4px 12px;border-radius:20px;text-transform:capitalize;
}
.status-pill.new{background:rgba(239,68,68,.1);color:#dc2626}
.status-pill.read{background:rgba(245,158,11,.1);color:#b45309}
.status-pill.replied{background:rgba(0,201,141,.12);color:#0d9b6c}

.btn-view{
    display:inline-flex;align-items:center;gap:6px;font-size:12.5px;font-weight:700;
    color:var(--teal);text-decoration:none;background:var(--teal-low);padding:7px 14px;
    border-radius:8px;transition:all .2s;
}
.btn-view:hover{background:var(--teal);color:#fff}

.ac-empty{padding:60px 20px;text-align:center;color:#94a3b8}
.ac-empty i{font-size:42px;margin-bottom:14px;display:block;color:#cbd5e1}

.ac-pagination{padding:18px 20px;display:flex;justify-content:center}

.alert-success-admin{
    background:rgba(0,201,141,.1);border:1px solid rgba(0,201,141,.3);color:#067a5a;
    border-radius:10px;padding:12px 18px;font-size:13.5px;margin-bottom:18px;
}
</style>

<div class="ac-wrap">

    <div class="ac-header">
        <div>
            <div class="ac-title">Contact Messages</div>
            <div class="ac-sub">All messages submitted through the EcomGstpro contact form.</div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert-success-admin">{{ session('success') }}</div>
    @endif

    <div class="ac-tabs">
        <a href="{{ route('admin.contact.index') }}" class="ac-tab {{ !request('status') ? 'active' : '' }}">
            All <span class="badge-count">{{ $counts['all'] }}</span>
        </a>
        <a href="{{ route('admin.contact.index', ['status' => 'new']) }}" class="ac-tab {{ request('status') === 'new' ? 'active' : '' }}">
            New <span class="badge-count">{{ $counts['new'] }}</span>
        </a>
        <a href="{{ route('admin.contact.index', ['status' => 'read']) }}" class="ac-tab {{ request('status') === 'read' ? 'active' : '' }}">
            Read <span class="badge-count">{{ $counts['read'] }}</span>
        </a>
        <a href="{{ route('admin.contact.index', ['status' => 'replied']) }}" class="ac-tab {{ request('status') === 'replied' ? 'active' : '' }}">
            Replied <span class="badge-count">{{ $counts['replied'] }}</span>
        </a>
    </div>

    <div class="ac-table-card">
        @if($contacts->count())
        <table class="ac-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Message</th>
                    <th>Status</th>
                    <th>Received</th>
                    <th style="text-align:right">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($contacts as $contact)
                <tr>
                    <td>
                        <div class="row-name">{{ $contact->name }}</div>
                        <div class="row-email">{{ $contact->email }}</div>
                    </td>
                    <td>
                        @if($contact->subject)
                            <strong style="color:#0a1628;font-size:13px;">{{ $contact->subject }}</strong><br>
                        @endif
                        <span class="row-msg-preview">{{ \Illuminate\Support\Str::limit($contact->message, 60) }}</span>
                    </td>
                    <td><span class="status-pill {{ $contact->status }}">{{ $contact->status }}</span></td>
                    <td>{{ $contact->created_at->format('d M Y, h:i A') }}</td>
                    <td style="text-align:right">
                        <a href="{{ route('admin.contact.show', $contact) }}" class="btn-view">
                            <i class="bi bi-eye-fill"></i> View
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="ac-pagination">{{ $contacts->links() }}</div>
        @else
        <div class="ac-empty">
            <i class="bi bi-inbox"></i>
            No contact messages found.
        </div>
        @endif
    </div>

</div>
@endsection
