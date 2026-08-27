<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to GSTpilot</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; background-color: #f4f6f9; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 14px; overflow: hidden; box-shadow: 0 6px 25px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #0a1628, #0d1f3c); padding: 32px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 28px; font-weight: 800; }
        .header .logo { font-family: 'Syne', sans-serif; color: #00c98d; font-weight: 800; font-size: 32px; text-decoration: none; }
        .body { padding: 32px; font-size: 15px; line-height: 1.7; color: #4a5568; }
        .body h2 { color: #0a1628; font-size: 22px; margin: 0 0 16px; font-weight: 700; }
        .body p { margin: 0 0 16px; }
        .body .highlight { color: #00c98d; font-weight: 700; }
        .cta-button { display: inline-block; background: linear-gradient(135deg, #00c98d, #00e0a8); color: #0a1628; font-weight: 700; font-size: 15px; padding: 14px 28px; border-radius: 12px; text-decoration: none; margin: 10px 0; box-shadow: 0 4px 18px rgba(0,201,141,.3); }
        .trial-box { background: rgba(0, 201, 141, 0.08); border-left: 4px solid #00c98d; padding: 18px 22px; margin: 24px 0; border-radius: 0 8px 8px 0; }
        .trial-box h3 { margin: 0 0 8px; font-size: 16px; color: #007050; font-weight: 700; }
        .trial-box p { margin: 0; font-size: 14px; color: #2d6a56; }
        .feature-list { list-style: none; padding: 0; margin: 20px 0; }
        .feature-list li { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 10px; font-size: 14px; }
        .feature-list i { color: #00c98d; font-size: 16px; margin-top: 3px; }
        .footer { background-color: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #94a3b8; }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="{{ url('/') }}" class="logo">GSTpilot</a>
        </div>
        <div class="body">
            <h2>Welcome to GSTpilot, {{ $user->name }}!</h2>
            <p>Your account has been created successfully. We are excited to help you make GST compliance easy, fast, and error-free.</p>

            <div class="trial-box">
                <h3><i class="bi bi-stars"></i> Your 30-Day Professional Trial Starts Now!</h3>
                <p>You have been granted full access to our <span class="highlight">Professional Plan</span>, completely free. Explore all premium features for the next 30 days.</p>
            </div>

            <p>Your trial includes:</p>
            <ul class="feature-list">
                <li><i class="bi bi-check-circle-fill"></i> <strong>Unlimited</strong> Invoice Processing</li>
                <li><i class="bi bi-check-circle-fill"></i> <strong>E-Commerce Module</strong> (Amazon, Flipkart, etc.)</li>
                <li><i class="bi bi-check-circle-fill"></i> <strong>2A/2B Reconciliation</strong> for Maximum ITC</li>
                <li><i class="bi bi-check-circle-fill"></i> <strong>Bank Statement Converter</strong> (PDF to Tally)</li>
                <li><i class="bi bi-check-circle-fill"></i> <strong>Direct GSTR-1/3B Filing</strong> with DSC/EVC</li>
            </ul>

            <p style="text-align: center;">
                <a href="{{ route('dashboard') }}" class="cta-button">Go to Your Dashboard</a>
            </p>

            <p>If you need any assistance, please do not hesitate to contact our support team.</p>
            <p>Happy Filing!<br>The GSTpilot Team</p>
        </div>
        <div class="footer">
            © {{ date('Y') }} GSTpilot. All rights reserved.
        </div>
    </div>
</body>
</html>
