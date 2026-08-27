<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>We Received Your Message</title>
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
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
        }

        .email-header {
            background: linear-gradient(135deg, #0a1628 0%, #0d1f3c 100%);
            padding: 40px 30px;
            text-align: center;
            border-bottom: 4px solid #00c98d;
        }

        .email-logo {
            font-size: 24px;
            font-weight: 800;
            color: #ffffff;
            margin-bottom: 10px;
            font-family: 'Syne', sans-serif;
        }

        .email-logo span {
            color: #00c98d;
        }

        .email-header-subtitle {
            font-size: 13px;
            color: #8a9bbf;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .email-content {
            padding: 40px 30px;
        }

        .email-content h1 {
            font-family: 'Syne', sans-serif;
            font-size: 24px;
            color: #0a1628;
            margin-bottom: 16px;
            font-weight: 800;
        }

        .email-content p {
            font-size: 15px;
            line-height: 1.8;
            color: #4a5568;
            margin-bottom: 20px;
        }

        .message-box {
            background: #f7faff;
            border-left: 4px solid #00c98d;
            padding: 20px;
            margin: 30px 0;
            border-radius: 8px;
        }

        .message-box .label {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            color: #00c98d;
            margin-bottom: 8px;
            letter-spacing: 1px;
        }

        .message-box .content {
            font-size: 14px;
            color: #374151;
            line-height: 1.6;
        }

        .message-preview {
            background: #ffffff;
            border: 1px solid #e8edf5;
            padding: 16px;
            border-radius: 8px;
            margin: 20px 0;
            font-size: 13px;
            color: #5a6874;
            line-height: 1.6;
            max-height: 200px;
            overflow-y: auto;
        }

        .timeline {
            background: #f7faff;
            padding: 24px;
            border-radius: 12px;
            margin: 30px 0;
        }

        .timeline-item {
            display: flex;
            gap: 16px;
            margin-bottom: 16px;
        }

        .timeline-item:last-child {
            margin-bottom: 0;
        }

        .timeline-icon {
            width: 36px;
            height: 36px;
            background: #00c98d;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #0a1628;
            font-size: 16px;
            flex-shrink: 0;
        }

        .timeline-content h3 {
            font-size: 14px;
            font-weight: 600;
            color: #0a1628;
            margin-bottom: 4px;
        }

        .timeline-content p {
            font-size: 13px;
            color: #5a6874;
            margin: 0;
        }

        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #00c98d 0%, #00e0a8 100%);
            color: #0a1628;
            padding: 14px 28px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
            transition: transform 0.2s;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(0, 201, 141, 0.5);
        }

        .email-footer {
            background: #f7faff;
            padding: 30px;
            border-top: 1px solid #e8edf5;
            text-align: center;
            font-size: 12px;
            color: #8a9bbf;
        }

        .email-footer a {
            color: #00c98d;
            text-decoration: none;
        }

        .social-links {
            margin: 16px 0;
        }

        .social-links a {
            display: inline-block;
            width: 32px;
            height: 32px;
            background: rgba(0, 201, 141, 0.1);
            border-radius: 50%;
            text-align: center;
            line-height: 32px;
            color: #00c98d;
            text-decoration: none;
            margin: 0 6px;
            transition: all 0.2s;
        }

        .social-links a:hover {
            background: #00c98d;
            color: #ffffff;
        }

        .support-contact {
            background: #fef3e2;
            border: 1px solid #fde68a;
            padding: 16px;
            border-radius: 8px;
            margin: 20px 0;
        }

        .support-contact strong {
            color: #92400e;
        }

        @media (max-width: 600px) {
            .email-container {
                border-radius: 0;
            }

            .email-content {
                padding: 24px 16px;
            }

            .email-header {
                padding: 30px 16px;
            }

            .timeline {
                padding: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">

        <!-- Header -->
        <div class="email-header">
            <div class="email-logo">GST<span>pilot</span></div>
            <div class="email-header-subtitle">Message Received</div>
        </div>

        <!-- Content -->
        <div class="email-content">
            <h1>✓ We Got Your Message!</h1>

            <p>Hi {{ $name }},</p>

            <p>Thank you for reaching out to GSTpilot! We've received your message and appreciate you taking the time to connect with us.</p>

            <!-- Message Summary -->
            <div class="message-box">
                <div class="label">Your Subject</div>
                <div class="content">{{ $subject }}</div>
            </div>

            <!-- Timeline -->
            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-icon">✓</div>
                    <div class="timeline-content">
                        <h3>Message Received</h3>
                        <p>We've securely received your message and stored it in our system.</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-icon">📧</div>
                    <div class="timeline-content">
                        <h3>Review & Response</h3>
                        <p>Our team will review your inquiry and get back to you within 2 hours during business hours (Mon-Fri, 9 AM - 6 PM IST).</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-icon">💬</div>
                    <div class="timeline-content">
                        <h3>Quick Support</h3>
                        <p>For urgent matters, reach us via WhatsApp at +91-8076865836 for instant assistance.</p>
                    </div>
                </div>
            </div>

            <!-- Support Contact -->
            <div class="support-contact">
                <strong>Need faster assistance?</strong><br>
                <span style="font-size: 13px;">Connect with our team on WhatsApp: <a href="https://wa.me/918076865836" style="color: #92400e; text-decoration: underline;">+91-8076865836</a></span>
            </div>

            <p><strong>What happens next:</strong></p>
            <p>One of our support specialists will review your message and send you a detailed response via email. We're committed to helping you succeed with GSTpilot!</p>

            <!-- CTA -->
            <center style="margin: 30px 0;">
                <a href="https://gstpilot.in" class="cta-button">👉 Visit Our Dashboard</a>
            </center>

        </div>

        <!-- Footer -->
        <div class="email-footer">
            <p>
                <strong>GSTpilot</strong><br>
                Office No. 458, 4th Floor<br>
                Agarwal Metro Heights Tower<br>
                Netaji Subhash Place, Pitampura<br>
                Delhi – 110034, India
            </p>

            <div class="social-links">
                <a href="https://gstpilot.in" title="Website">🌐</a>
                <a href="mailto:support@gstpilot.in" title="Email">✉️</a>
                <a href="https://wa.me/918076865836" title="WhatsApp">💬</a>
            </div>

            <p style="margin-top: 16px;">
                This is an automated response. Please do not reply to this email.<br>
                <a href="#">Unsubscribe from marketing emails</a>
            </p>
        </div>

    </div>
</body>
</html>
