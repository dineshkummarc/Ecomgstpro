@extends('admin.layout.admin')

@section('content')

<style>
    :root {
        --navy: #0a1628;
        --deep: #0d1f3c;
        --teal: #00c98d;
        --sky: #00aaff;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--sky);
        text-decoration: none;
        font-weight: 700;
        margin-bottom: 24px;
        transition: all 0.2s;
    }

    .back-link:hover {
        gap: 12px;
    }

    .message-header {
        background: linear-gradient(135deg, var(--navy) 0%, var(--deep) 100%);
        color: #fff;
        padding: 30px;
        border-radius: 12px;
        margin-bottom: 30px;
        display: flex;
        justify-content: space-between;
        align-items: start;
    }

    .message-title {
        font-size: 28px;
        font-weight: 800;
        margin: 0 0 16px 0;
    }

    .message-meta {
        display: flex;
        gap: 24px;
        flex-wrap: wrap;
    }

    .meta-item {
        font-size: 13px;
    }

    .meta-label {
        color: #8a9bbf;
        text-transform: uppercase;
        letter-spacing: 1px;
        display: block;
        margin-bottom: 4px;
    }

    .meta-value {
        color: #fff;
        font-weight: 700;
    }

    .status-badge {
        display: inline-block;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-new {
        background: #dbeafe;
        color: #0369a1;
    }

    .status-read {
        background: #fef3e2;
        color: #92400e;
    }

    .status-replied {
        background: #dcfce7;
        color: #166534;
    }

    .message-content {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
    }

    .content-card {
        background: #fff;
        border: 1px solid #e8edf5;
        border-radius: 12px;
        padding: 24px;
    }

    .content-card h3 {
        font-size: 14px;
        font-weight: 800;
        color: var(--navy);
        text-transform: uppercase;
        letter-spacing: 1px;
        margin: 0 0 20px 0;
        padding-bottom: 16px;
        border-bottom: 2px solid #f3f4f6;
    }

    .info-group {
        margin-bottom: 20px;
    }

    .info-group:last-child {
        margin-bottom: 0;
    }

    .info-label {
        font-size: 12px;
        color: #8a9bbf;
        text-transform: uppercase;
        letter-spacing: 1px;
        display: block;
        margin-bottom: 6px;
        font-weight: 700;
    }

    .info-value {
        font-size: 15px;
        color: #1f2937;
        font-weight: 600;
        word-break: break-word;
    }

    .info-value a {
        color: var(--teal);
        text-decoration: none;
    }

    .info-value a:hover {
        text-decoration: underline;
    }

    .message-text {
        background: #f7faff;
        padding: 16px;
        border-radius: 8px;
        font-size: 14px;
        line-height: 1.7;
        color: #374151;
        white-space: pre-wrap;
        word-wrap: break-word;
    }

    .action-buttons {
        display: flex;
        gap: 12px;
        margin-top: 24px;
        padding-top: 24px;
        border-top: 1px solid #e8edf5;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px 20px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 700;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 1px;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--teal) 0%, #00e0a8 100%);
        color: var(--navy);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(0, 201, 141, 0.5);
    }

    .btn-secondary {
        background: var(--sky);
        color: #fff;
    }

    .btn-secondary:hover {
        background: #0089cc;
    }

    .btn-danger {
        background: #fee2e2;
        color: #991b1b;
    }

    .btn-danger:hover {
        background: #fecaca;
    }

    .timeline {
        background: #f7faff;
        padding: 20px;
        border-radius: 8px;
        border-left: 4px solid var(--teal);
    }

    .timeline-item {
        display: flex;
        gap: 12px;
        margin-bottom: 12px;
    }

    .timeline-item:last-child {
        margin-bottom: 0;
    }

    .timeline-icon {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: var(--teal);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        flex-shrink: 0;
    }

    .timeline-content {
        flex: 1;
    }

    .timeline-content strong {
        font-weight: 700;
        color: var(--navy);
    }

    .timeline-content small {
        color: #8a9bbf;
        display: block;
        margin-top: 2px;
    }

    @media(max-width: 1024px) {
        .message-content {
            grid-template-columns: 1fr;
        }

        .message-meta {
            flex-direction: column;
            gap: 16px;
        }
    }
</style>

<div class="admin-container" style="padding: 30px;">

    <a href="{{ route('admin.contact.index') }}" class="back-link">
        ← Back to All Messages
    </a>

    <!-- Message Header -->
    <div class="message-header">
        <div>
            <h1 class="message-title">{{ $message->subject }}</h1>
            <div class="message-meta">
                <div class="meta-item">
                    <span class="meta-label">From</span>
                    <span class="meta-value">{{ $message->name }}</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Received</span>
                    <span class="meta-value">{{ $message->created_at->format('M d, Y H:i') }}</span>
                </div>
                <div class="meta-item">
                    <span class="status-badge status-{{ $message->status }}">
                        @if($message->status === 'new')
                            🔴 New
                        @elseif($message->status === 'read')
                            🟡 Read
                        @else
                            🟢 Replied
                        @endif
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Message Content -->
    <div class="message-content">

        <!-- Left: Contact Info -->
        <div class="content-card">
            <h3>👤 Contact Information</h3>

            <div class="info-group">
                <span class="info-label">Full Name</span>
                <span class="info-value">{{ $message->name }}</span>
            </div>

            <div class="info-group">
                <span class="info-label">Email Address</span>
                <span class="info-value">
                    <a href="mailto:{{ $message->email }}">{{ $message->email }}</a>
                </span>
            </div>

            @if($message->phone)
            <div class="info-group">
                <span class="info-label">Phone Number</span>
                <span class="info-value">
                    <a href="tel:{{ $message->phone }}">{{ $message->phone }}</a>
                </span>
            </div>
            @endif

            <div class="info-group">
                <span class="info-label">Message ID</span>
                <span class="info-value">#{{ $message->id }}</span>
            </div>

            <div class="info-group">
                <span class="info-label">Status</span>
                <span class="info-value" style="font-size: 12px;">
                    <span class="status-badge status-{{ $message->status }}">
                        @if($message->status === 'new')
                            🔴 New
                        @elseif($message->status === 'read')
                            🟡 Read
                        @else
                            🟢 Replied
                        @endif
                    </span>
                </span>
            </div>

            <!-- Timeline -->
            <div class="timeline" style="margin-top: 24px;">
                <div class="timeline-item">
                    <div class="timeline-icon">📬</div>
                    <div class="timeline-content">
                        <strong>Message Received</strong>
                        <small>{{ $message->created_at->format('M d, Y H:i') }}</small>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-icon">{{ $message->isRead() ? '👁️' : '⏳' }}</div>
                    <div class="timeline-content">
                        <strong>{{ $message->isRead() ? 'Message Read' : 'Awaiting Review' }}</strong>
                        @if($message->isRead())
                            <small>{{ $message->updated_at->format('M d, Y H:i') }}</small>
                        @endif
                    </div>
                </div>
                @if($message->isReplied())
                <div class="timeline-item">
                    <div class="timeline-icon">✓</div>
                    <div class="timeline-content">
                        <strong>Reply Sent</strong>
                        <small>{{ $message->updated_at->format('M d, Y H:i') }}</small>
                    </div>
                </div>
                @endif
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="mailto:{{ $message->email }}" class="btn btn-secondary">
                    ✉️ Reply via Email
                </a>
                @if(!$message->isReplied())
                <form action="{{ route('admin.contact.mark-replied', $message->id) }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check2-circle"></i> Mark as Replied
                    </button>
                </form>
                @endif
                <form action="{{ route('admin.contact.destroy', $message->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this message?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        🗑️ Delete
                    </button>
                </form>
            </div>
        </div>

        <!-- Right: Message Content -->
        <div class="content-card">
            <h3>💬 Message</h3>
            <div class="message-text">{{ $message->message }}</div>
        </div>

    </div>

</div>

@endsection
