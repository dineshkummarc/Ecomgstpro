<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Contact Form Submission</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DM Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background-color: #f0f4ff;
            line-height: 1.6;
            color: #374151;
        }

        .email-container {
            max-width: 700px;
            margin: 0 auto;
            background: #ffffff;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
        }

        .email-header {
            background: linear-gradient(135deg, #0a1628 0%, #0d1f3c 100%);
            padding: 30px;
            text-align: center;
            border-bottom: 4px solid #00aaff;
        }

        .email-logo {
            font-size: 20px;
            font-weight: 800;
            color: #ffffff;
            margin-bottom: 8px;
            font-family: 'Syne', sans-serif;
        }

        .email-logo span {
            color: #00aaff;
        }

        .email-header-subtitle {
            font-size: 12px;
            color: #8a9bbf;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .email-content {
            padding: 30px;
        }

        .alert-box {
            background: #dbeafe;
            border-left: 4px solid #00aaff;
            padding: 16px;
            margin-bottom: 24px;
            border-radius: 6px;
        }

        .alert-box strong {
            color: #0369a1;
            display: block;
            margin-bottom: 4px;
        }

        .alert-box p {
            color: #0c4a6e;
            font-size: 13px;
            margin: 0;
        }

        .submission-card {
            border: 1px solid #e8edf5;
            border-radius: 10px;
            overflow: hidden;
            margin: 24px 0;
        }

        .submission-header {
            background: #f7faff;
            padding: 16px 20px;
            border-bottom: 1px solid #e8edf5;
        }

        .submission-title {
            font-family: 'Syne', sans-serif;
            font-size: 16px;
            font-weight: 800;
            color: #0a1628;
            margin: 0;
        }

        .submission-body {
            padding: 20px;
        }

        .info-row {
            display: flex;
            gap: 20px;
            margin-bottom: 16px;
            padding-bottom: 16px;
            border-bottom: 1px solid #f3f4f6;
        }

        .info-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .info-label {
            font-weight: 700;
            color: #6b7280;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            min-width: 100px;
        }

        .info-value {
            color: #1f2937;
            font-size: 14px;
            word-break: break-word;
        }

        .info-value a {
            color: #00c98d;
            text-decoration: none;
        }

        .info-value a:hover {
            text-decoration: underline;
        }

        .message-section {
            background: #fef9e7;
            border-left: 4px solid #f59e0b;
            padding: 16px;
            border-radius: 6px;
            margin: 20px 0;
        }

        .message-section h3 {
            font-size: 13px;
            font-weight: 700;
            color: #92400e;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .message-content {
            background: #ffffff;
            padding: 12px;
            border-radius: 4px;
            font-size: 13px;
            line-height: 1.7;
            color: #374151;
            white-space: pre-wrap;
            word-wrap: break-word;
            max-height: 300px;
            overflow-y: auto;
        }

        .action-buttons {
            display: flex;
            gap: 12px;
            margin-top: 24px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: linear-gradient(135deg, #00c98d 0%, #00e0a8 100%);
            color: #0a1628;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(0, 201, 141, 0.5);
        }

        .btn-secondary {
            background: #e5e7eb;
            color: #374151;
        }

        .btn-secondary:hover {
            background: #d1d5db;
        }

        .quick-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin: 20px 0;
        }

        .stat {
            background: #f7faff;
            padding: 12px;
            border-radius: 6px;
            border-left: 3px solid #00c98d;
        }

        .stat-label {
            font-size: 11px;
            color: #6b7280;
            text-transform: uppercase;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .stat-value {
            font-size: 13px;
            color: #1f2937;
            font-weight: 600;
        }

        .email-footer {
            background: #f7faff;
            padding: 20px 30px;
            border-top: 1px solid #e8edf5;
            text-align: center;
            font-size: 12px;
            color: #8a9bbf;
        }

        .footer-note {
            background: #fef3e2;
            border: 1px solid #fde68a;
            padding: 12px;
            border-radius: 6px;
            margin-top: 16px;
            font-size: 12px;
        }

        .footer-note strong {
            color: #92400e;
        }

        @media (max-width: 600px) {
            .email-container {
                border-radius: 0;
            }

            .email-content {
                padding: 16px;
            }

            .action-buttons {
                flex-direction: column;
            }

            .quick-stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">

        <!-- Header -->
        <div class="email-header">
            <div class="email-logo">GST<span>pilot</span> ADMIN</div>
            <div class="email-header-subtitle">New Contact Form Submission</div>
        </div>

        <!-- Content -->
        <div class="email-content">

            <!-- Alert Box -->
            <div class="alert-box">
                <strong>📬 New Contact Message Received</strong>
                <p>A new inquiry has been submitted through the contact form. Please review and respond accordingly.</p>
            </div>

            <!-- Submission Details -->
            <div class="submission-card">
                <div class="submission-header">
                    <h2 class="submission-title">{{ $subject }}</h2>
                </div>

                <div class="submission-body">

                    <!-- Quick Stats -->
                    <div class="quick-stats">
                        <div class="stat">
                            <div class="stat-label">Status</div>
                            <div class="stat-value" style="color: #00c98d;">🟢 NEW</div>
                        </div>
                        <div class="stat">
                            <div class="stat-label">Message ID</div>
                            <div class="stat-value">#{{ $messageId }}</div>
                        </div>
                    </div>

                    <!-- Contact Info -->
                    <div class="info-row">
                        <span class="info-label">Name</span>
                        <span class="info-value">{{ $name }}</span>
                    </div>

                    <div class="info-row">
                        <span class="info-label">Email</span>
                        <span class="info-value"><a href="mailto:{{ $email }}">{{ $email }}</a></span>
                    </div>

                    @if($phone)
                    <div class="info-row">
                        <span class="info-label">Phone</span>
                        <span class="info-value"><a href="tel:{{ $phone }}">{{ $phone }}</a></span>
                    </div>
                    @endif

                    <div class="info-row">
                        <span class="info-label">Subject</span>
                        <span class="info-value">{{ $subject }}</span>
                    </div>

                </div>
            </div>

            <!-- Message Content -->
            <div class="message-section">
                <h3>📝 Message Content</h3>
                <div class="message-content">{{ $message }}</div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="http://localhost:8000/admin/contact/{{ $messageId }}" class="btn btn-primary">
                    ✓ View in Dashboard
                </a>
                <a href="mailto:{{ $email }}" class="btn btn-secondary">
                    ✉️ Reply
                </a>
            </div>

            <!-- Footer Note -->
            <div class="footer-note">
                <strong>⚡ Quick Tip:</strong> Make sure to respond within 2 hours to maintain our commitment to customer service excellence.
            </div>

        </div>

        <!-- Footer -->
        <div class="email-footer">
            <p>
                <strong>EcomGstPro Admin Panel</strong><br>
                This is an automated notification. Do not reply to this email.<br>
                <a href="#">View all messages</a>
            </p>
        </div>

    </div>
</body>
</html>
