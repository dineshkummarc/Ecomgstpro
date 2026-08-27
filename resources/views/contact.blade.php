@extends('layouts.app')

{{-- ============================================================
     GST EcomgstPro — contact.blade.php (IMPROVED)
     Theme : Dark Navy #0a1628 + Teal #00c98d + Sky #00aaff
     Features: Hero background image, side form image, FAQ, SEO
     ============================================================ --}}

@section('content')

<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    html, body { overflow-x: hidden; }

    :root {
        --navy: #0a1628;
        --deep: #0d1f3c;
        --mid: #132240;
        --teal: #00c98d;
        --sky: #00aaff;
        --teal-low: rgba(0,201,141,.12);
        --sky-low: rgba(0,170,255,.10);
        --txt: #e8f0fe;
        --muted: #8a9bbf;
        --border: rgba(255,255,255,.08);
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'DM Sans', sans-serif; background: #f0f4ff; color: var(--navy); }

    .contact-section { padding: 96px 0; }
    .contact-container { max-width: 1200px; margin: 0 auto; padding: 0 32px; }

    /* ═══ HERO WITH BACKGROUND IMAGE ═══ */
    .contact-hero {
        background: linear-gradient(135deg, rgba(10,22,40,0.85) 0%, rgba(13,31,60,0.85) 55%, rgba(10,34,64,0.85) 100%),
                    url('https://images.unsplash.com/photo-1552664730-d307ca884978?w=1200&h=600&fit=crop') center/cover;
        padding: 140px 0 100px;
        position: relative;
        overflow: hidden;
        text-align: center;
        min-height: 600px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .contact-hero::before {
        content: '';
        position: absolute;
        width: 700px;
        height: 700px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(0,170,255,.08) 0%, transparent 70%);
        top: -220px;
        right: -160px;
        pointer-events: none;
    }

    .contact-hero::after {
        content: '';
        position: absolute;
        width: 500px;
        height: 500px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(0,201,141,.1) 0%, transparent 70%);
        bottom: -180px;
        left: -120px;
        pointer-events: none;
    }

    .contact-hero-inner {
        position: relative;
        z-index: 2;
        max-width: 820px;
        margin: 0 auto;
        padding: 0 32px;
    }

    .contact-hero h1 {
        font-family: 'Syne', sans-serif;
        font-weight: 800;
        font-size: clamp(40px, 5.5vw, 64px);
        line-height: 1.1;
        letter-spacing: -1.5px;
        color: #fff;
        margin-bottom: 24px;
    }

    .contact-hero h1 .teal { color: var(--teal); }

    .contact-hero-sub {
        font-size: 18px;
        line-height: 1.8;
        color: var(--muted);
        max-width: 680px;
        margin: 0 auto 32px;
    }

    /* ═══ CONTACT WRAPPER WITH SIDE IMAGE ═══ */
    .contact-wrapper {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 64px;
        align-items: stretch;
        margin-top: 80px;
    }

    /* Side Image */
    .contact-side-image {
        position: relative;
        border-radius: 20px;
        overflow: hidden;
        height: auto;
        min-height: 600px;
        box-shadow: 0 20px 60px rgba(0,0,0,.15);
    }

    .contact-side-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .contact-image-badge {
        position: absolute;
        bottom: 30px;
        left: 30px;
        right: 30px;
        background: linear-gradient(135deg, rgba(0,201,141,0.95) 0%, rgba(0,224,168,0.95) 100%);
        padding: 24px;
        border-radius: 16px;
        color: var(--navy);
        z-index: 10;
    }

    .contact-image-badge h3 {
        font-family: 'Syne', sans-serif;
        font-weight: 800;
        font-size: 20px;
        margin: 0 0 8px 0;
    }

    .contact-image-badge p {
        font-size: 13px;
        margin: 0;
        opacity: 0.95;
    }

    /* Contact Form Section */
    .contact-form-section {
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
    }

    .contact-form-wrapper {
        background: linear-gradient(135deg, var(--navy) 0%, var(--deep) 100%);
        border-radius: 20px;
        padding: 48px;
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(0,201,141,.15);
        height: 100%;
    }

    .contact-form-wrapper::before {
        content: '';
        position: absolute;
        top: -40%;
        right: -20%;
        width: 300px;
        height: 300px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(0,201,141,.08) 0%, transparent 70%);
        pointer-events: none;
    }

    .contact-form-wrapper::after {
        content: '';
        position: absolute;
        bottom: -30%;
        left: -15%;
        width: 250px;
        height: 250px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(0,170,255,.06) 0%, transparent 70%);
        pointer-events: none;
    }

    .form-content { position: relative; z-index: 2; }

    .form-content h2 {
        font-family: 'Syne', sans-serif;
        font-weight: 800;
        font-size: 32px;
        color: #fff;
        margin-bottom: 8px;
    }

    .form-content > p {
        font-size: 14px;
        color: var(--muted);
        margin-bottom: 24px;
    }

    .form-group { margin-bottom: 22px; }

    .form-group label {
        display: block;
        font-size: 12px;
        font-weight: 700;
        color: var(--txt);
        margin-bottom: 8px;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }

    .form-group input,
    .form-group textarea {
        width: 100%;
        padding: 14px 16px;
        font-size: 14px;
        font-family: 'DM Sans', sans-serif;
        background: rgba(255,255,255,.05);
        border: 1px solid rgba(255,255,255,.12);
        border-radius: 10px;
        color: #fff;
        transition: all .2s;
    }

    .form-group input::placeholder,
    .form-group textarea::placeholder {
        color: rgba(255,255,255,.4);
    }

    .form-group input:focus,
    .form-group textarea:focus {
        outline: none;
        background: rgba(255,255,255,.08);
        border-color: var(--teal);
        box-shadow: 0 0 0 3px rgba(0,201,141,.15);
    }

    .form-group textarea {
        resize: vertical;
        min-height: 120px;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    .btn-submit {
        width: 100%;
        padding: 15px 28px;
        font-size: 15px;
        font-weight: 700;
        font-family: 'DM Sans', sans-serif;
        background: linear-gradient(135deg, var(--teal) 0%, #00e0a8 100%);
        color: var(--navy);
        border: none;
        border-radius: 10px;
        cursor: pointer;
        transition: all .2s;
        text-transform: uppercase;
        letter-spacing: 1px;
        box-shadow: 0 4px 22px rgba(0,201,141,.35);
        margin-top: 16px;
    }

    .btn-submit:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(0,201,141,.5);
    }

    .btn-submit:disabled {
        opacity: 0.7;
        cursor: not-allowed;
        transform: none;
    }

    /* Alert Messages */
    .alert-success {
        background: rgba(0,201,141,.12);
        border: 1px solid rgba(0,201,141,.3);
        color: var(--teal);
        padding: 14px 16px;
        border-radius: 10px;
        margin-bottom: 20px;
        display: none;
        font-size: 14px;
        animation: slideIn 0.3s ease;
    }

    .alert-error {
        background: rgba(239,68,68,.12);
        border: 1px solid rgba(239,68,68,.3);
        color: #ef4444;
        padding: 14px 16px;
        border-radius: 10px;
        margin-bottom: 20px;
        display: none;
        font-size: 14px;
        animation: slideIn 0.3s ease;
    }

    @keyframes slideIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* ═══ WHY CHOOSE SECTION ═══ */
    .why-choose-section {
        background: #f9fafc;
        padding: 80px 0;
    }

    .why-choose-section h2 {
        font-family: 'Syne', sans-serif;
        font-weight: 800;
        font-size: clamp(32px, 4vw, 48px);
        color: var(--navy);
        margin-bottom: 12px;
        text-align: center;
    }

    .why-choose-section h2 em {
        color: var(--teal);
        font-style: normal;
    }

    .why-choose-intro {
        text-align: center;
        margin-bottom: 48px;
        max-width: 700px;
        margin-left: auto;
        margin-right: auto;
        font-size: 16px;
        line-height: 1.8;
        color: #4a5568;
    }

    .why-choose-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 28px;
    }

    .why-card {
        background: #fff;
        padding: 32px;
        border-radius: 16px;
        border: 1px solid #e8edf5;
        transition: all .3s;
    }

    .why-card:hover {
        border-color: rgba(0,201,141,.3);
        box-shadow: 0 8px 40px rgba(0,201,141,.10);
        transform: translateY(-4px);
    }

    .why-card-icon {
        width: 56px;
        height: 56px;
        background: var(--teal-low);
        border: 1px solid rgba(0,201,141,.2);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--teal);
        font-size: 28px;
        margin-bottom: 20px;
    }

    .why-card h3 {
        font-family: 'Syne', sans-serif;
        font-weight: 800;
        font-size: 18px;
        color: var(--navy);
        margin-bottom: 12px;
    }

    .why-card p {
        font-size: 14px;
        line-height: 1.7;
        color: #4a5568;
        margin: 0;
    }

    /* ═══ FAQ SECTION ═══ */
    .faq-section {
        background: linear-gradient(135deg, var(--navy) 0%, var(--deep) 100%);
        padding: 80px 0;
    }

    .faq-header {
        text-align: center;
        margin-bottom: 56px;
    }

    .faq-header h2 {
        font-family: 'Syne', sans-serif;
        font-weight: 800;
        font-size: clamp(32px, 4vw, 48px);
        color: #fff;
        margin-bottom: 12px;
    }

    .faq-header h2 em {
        color: var(--teal);
        font-style: normal;
    }

    .faq-header p {
        font-size: 16px;
        line-height: 1.8;
        color: var(--muted);
        max-width: 700px;
        margin: 0 auto;
    }

    .faq-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
    }

    .faq-item {
        background: rgba(255,255,255,.05);
        border: 1px solid rgba(255,255,255,.1);
        border-radius: 12px;
        padding: 0;
        overflow: hidden;
    }

    .faq-question {
        padding: 24px;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: rgba(255,255,255,.03);
        transition: all .3s;
        user-select: none;
    }

    .faq-question:hover {
        background: rgba(0,201,141,.1);
    }

    .faq-question-text {
        font-size: 15px;
        font-weight: 700;
        color: #fff;
        margin: 0;
        flex: 1;
        font-family: 'Syne', sans-serif;
    }

    .faq-toggle {
        width: 28px;
        height: 28px;
        background: rgba(0,201,141,.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--teal);
        transition: all .3s;
        margin-left: 16px;
        flex-shrink: 0;
    }

    .faq-item.active .faq-toggle {
        background: var(--teal);
        color: var(--navy);
        transform: rotate(180deg);
    }

    .faq-answer {
        display: none;
        padding: 0 24px 24px 24px;
        color: var(--muted);
        font-size: 14px;
        line-height: 1.8;
        border-top: 1px solid rgba(255,255,255,.1);
    }

    .faq-item.active .faq-answer {
        display: block;
        animation: slideDown 0.3s ease;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* ═══ CTA SECTION ═══ */
    .cta-final {
        background: linear-gradient(135deg, var(--teal) 0%, #00e0a8 100%);
        padding: 60px 0;
        text-align: center;
        color: var(--navy);
    }

    .cta-final h2 {
        font-family: 'Syne', sans-serif;
        font-weight: 800;
        font-size: 32px;
        margin-bottom: 16px;
    }

    .cta-final p {
        font-size: 16px;
        margin-bottom: 28px;
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
    }

    .cta-button {
        display: inline-block;
        background: var(--navy);
        color: var(--teal);
        padding: 14px 32px;
        border-radius: 10px;
        text-decoration: none;
        font-weight: 700;
        transition: all .2s;
    }

    .cta-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(0,0,0,.2);
    }

    /* ═══ RESPONSIVE ═══ */
    @media(max-width: 1024px) {
        .contact-wrapper {
            grid-template-columns: 1fr;
            gap: 48px;
        }

        .contact-side-image {
            min-height: 400px;
        }

        .faq-grid {
            grid-template-columns: 1fr;
        }

        .why-choose-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media(max-width: 768px) {
        .contact-section { padding: 56px 0; }
        .contact-hero { padding: 80px 0; }
        .contact-container { padding: 0 20px; }
        .contact-form-wrapper { padding: 32px 20px; }
        .form-row { grid-template-columns: 1fr; }
        .contact-hero h1 { font-size: clamp(28px, 6vw, 42px); }
        .contact-side-image { min-height: 300px; }
        .why-choose-grid { grid-template-columns: 1fr; }
        .faq-item { margin-bottom: 12px; }
        .contact-image-badge {
            bottom: 20px;
            left: 20px;
            right: 20px;
            padding: 18px;
        }
    }
</style>

<!-- ═══ HERO WITH BACKGROUND IMAGE ═══ -->
<section class="contact-hero">
    <div class="contact-hero-inner">
        <h1>Your <span class="teal">GST Compliance</span> Made Simple</h1>
        <p class="contact-hero-sub">Get expert support for your GST filing, reconciliation, ITC recovery, and compliance needs. Our team is just one message away.</p>
    </div>
</section>

<!-- ═══ MAIN CONTACT SECTION ═══ -->
<section class="contact-section" style="background: #fff;">
    <div class="contact-container">
        <div class="contact-wrapper">

            <!-- Left: Side Image -->
            <div class="contact-side-image">
                <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?w=600&h=800&fit=crop" alt="GSTpilot Team Working on GST Compliance">
                <div class="contact-image-badge">
                    <h3>💼 Expert Support</h3>
                    <p>Dedicated team ready to assist your GST compliance journey</p>
                </div>
            </div>

            <!-- Right: Contact Form -->
            <div class="contact-form-section">
                <div class="contact-form-wrapper">
                    <div class="form-content">
                        <h2>Get in Touch</h2>
                        <p>Fill out the form and our team will respond within 2 hours</p>

                        <div class="alert-success" id="successAlert">
                            <i class="bi bi-check-circle-fill"></i> Message sent successfully! We'll contact you soon.
                        </div>

                        <div class="alert-error" id="errorAlert"></div>

                        <form id="contactForm" method="POST" action="{{ route('contact.submit') }}" novalidate>
                            @csrf

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="name">Full Name *</label>
                                    <input
                                        type="text"
                                        id="name"
                                        name="name"
                                        placeholder="Rajesh Kumar"
                                        required
                                        value="{{ old('name') }}"
                                    >
                                    <small class="error-text"></small>
                                </div>

                                <div class="form-group">
                                    <label for="email">Email Address *</label>
                                    <input
                                        type="email"
                                        id="email"
                                        name="email"
                                        placeholder="rajesh@company.com"
                                        required
                                        value="{{ old('email') }}"
                                    >
                                    <small class="error-text"></small>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="phone">Phone Number</label>
                                    <input
                                        type="tel"
                                        id="phone"
                                        name="phone"
                                        placeholder="+91-9876543210"
                                        value="{{ old('phone') }}"
                                    >
                                    <small class="error-text"></small>
                                </div>

                                <div class="form-group">
                                    <label for="subject">Subject *</label>
                                    <input
                                        type="text"
                                        id="subject"
                                        name="subject"
                                        placeholder="GST Filing Support"
                                        required
                                        value="{{ old('subject') }}"
                                    >
                                    <small class="error-text"></small>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="message">Message *</label>
                                <textarea
                                    id="message"
                                    name="message"
                                    placeholder="Tell us about your GST compliance needs..."
                                    required
                                >{{ old('message') }}</textarea>
                                <small class="error-text"></small>
                            </div>

                            <button type="submit" class="btn-submit" id="submitBtn">
                                <i class="bi bi-send-fill"></i> Send Message
                            </button>
                        </form>

                        <!-- Quick Contact Info -->
                        <div style="margin-top: 32px; padding-top: 24px; border-top: 1px solid rgba(255,255,255,.1);">
                            <p style="font-size: 12px; color: var(--muted); margin-bottom: 16px; text-transform: uppercase; letter-spacing: 1px;">Quick Contact</p>
                            <div style="display: flex; gap: 16px; flex-wrap: wrap;">
                                <a href="tel:+918076865836" style="color: var(--teal); text-decoration: none; font-weight: 600; font-size: 13px; display: inline-flex; align-items: center; gap: 6px;">
                                    <i class="bi bi-telephone"></i> +91-8076865836
                                </a>
                                <a href="https://wa.me/918076865836" target="_blank" style="color: var(--teal); text-decoration: none; font-weight: 600; font-size: 13px; display: inline-flex; align-items: center; gap: 6px;">
                                    <i class="bi bi-chat-dots"></i> WhatsApp
                                </a>
                                <a href="mailto:support@ecomgstpro.com" style="color: var(--teal); text-decoration: none; font-weight: 600; font-size: 13px; display: inline-flex; align-items: center; gap: 6px;">
                                    <i class="bi bi-envelope"></i> Email
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ═══ WHY CHOOSE GSTPILOT ═══ -->
<section class="why-choose-section">
    <div class="contact-container">
        <h2>Why Choose EcomgstPro for <em>GST Compliance</em>?</h2>
        <p class="why-choose-intro">
            EcomgstPro is the leading GST software solution trusted by 10,000+ businesses across India. We provide end-to-end GST compliance tools that save time, reduce errors, and ensure filing accuracy.
        </p>

        <div class="why-choose-grid">
            <div class="why-card">
                <div class="why-card-icon">⚡</div>
                <h3>Lightning Fast</h3>
                <p>Process 10,000+ invoices in minutes. Automated reconciliation, matching, and filing.</p>
            </div>

            <div class="why-card">
                <div class="why-card-icon">🎯</div>
                <h3>98% Accuracy</h3>
                <p>Built-in validation catches errors before filing. Less rejections, more smooth compliance.</p>
            </div>

            <div class="why-card">
                <div class="why-card-icon">💰</div>
                <h3>Recover ITC</h3>
                <p>Recover ₹50,000+ per GSTIN annually. Find unclaimed input tax credit automatically.</p>
            </div>

            <div class="why-card">
                <div class="why-card-icon">🏦</div>
                <h3>Bank Statement Converter</h3>
                <p>Convert 690+ banks' statements into clean GST-categorized ledgers instantly.</p>
            </div>

            <div class="why-card">
                <div class="why-card-icon">📊</div>
                <h3>Real-Time Dashboard</h3>
                <p>Monitor all GSTINs, filings, and compliance status from one unified dashboard.</p>
            </div>

            <div class="why-card">
                <div class="why-card-icon">🔒</div>
                <h3>Bank-Grade Security</h3>
                <p>GSP certified, SOC 2 compliant, ISO 27001 certified. Your data is safe with us.</p>
            </div>
        </div>
    </div>
</section>

<!-- ═══ FAQ SECTION ═══ -->
<section class="faq-section">
    <div class="contact-container">
        <div class="faq-header">
            <h2>Frequently Asked <em>Questions</em></h2>
            <p>Everything you need to know about EcomgstPro and GST compliance</p>
        </div>

        <div class="faq-grid">
            <!-- FAQ 1 -->
            <div class="faq-item">
                <div class="faq-question">
                    <p class="faq-question-text">What is EcomgstPro?</p>
                    <div class="faq-toggle"><i class="bi bi-plus"></i></div>
                </div>
                <div class="faq-answer">
                    EcomgstPro is an all-in-one GST compliance software designed specifically for Indian businesses. It automates GST filing, reconciliation, ITC recovery, and compliance tracking with AI-powered tools. Perfect for startups, SMEs, and enterprises handling GST.
                </div>
            </div>

            <!-- FAQ 2 -->
            <div class="faq-item">
                <div class="faq-question">
                    <p class="faq-question-text">Is EcomgstPro secure?</p>
                    <div class="faq-toggle"><i class="bi bi-plus"></i></div>
                </div>
                <div class="faq-answer">
                    Absolutely. EcomgstPro is GSP certified, SOC 2 Type II compliant, and ISO 27001 certified. We use bank-grade encryption, secure APIs, and follow industry best practices for data protection.
                </div>
            </div>

            <!-- FAQ 3 -->
            <div class="faq-item">
                <div class="faq-question">
                    <p class="faq-question-text">How much can I save with EcomgstPro?</p>
                    <div class="faq-toggle"><i class="bi bi-plus"></i></div>
                </div>
                <div class="faq-answer">
                    On average, businesses recover ₹50,000+ in unclaimed ITC per GSTIN annually. Plus, you save 20-30 hours per month on GST filing and reconciliation tasks.
                </div>
            </div>

            <!-- FAQ 4 -->
            <div class="faq-item">
                <div class="faq-question">
                    <p class="faq-question-text">Which GST forms does EcomgstPro support?</p>
                    <div class="faq-toggle"><i class="bi bi-plus"></i></div>
                </div>
                <div class="faq-answer">
                    EcomgstPro supports GSTR-1, GSTR-2A/2B reconciliation, GSTR-3B, GSTR-9, GSTR-9C, and all major GST forms. We also handle e-commerce, import-export, and job work scenarios.
                </div>
            </div>

            <!-- FAQ 5 -->
            <div class="faq-item">
                <div class="faq-question">
                    <p class="faq-question-text">Can I import data from my existing software?</p>
                    <div class="faq-toggle"><i class="bi bi-plus"></i></div>
                </div>
                <div class="faq-answer">
                    Yes! GSTEcomgstPro supports import from Tally, QuickBooks, Excel, and most accounting software. Our team can help migrate your data securely and without downtime.
                </div>
            </div>

            <!-- FAQ 6 -->
            <div class="faq-item">
                <div class="faq-question">
                    <p class="faq-question-text">What support do you provide?</p>
                    <div class="faq-toggle"><i class="bi bi-plus"></i></div>
                </div>
                <div class="faq-answer">
                    We offer email support (2-hour response), WhatsApp support for urgent queries, video tutorials, and webinars. Premium plans include dedicated account managers and priority support.
                </div>
            </div>

            <!-- FAQ 7 -->
            <div class="faq-item">
                <div class="faq-question">
                    <p class="faq-question-text">How does the Bank Statement Converter work?</p>
                    <div class="faq-toggle"><i class="bi bi-plus"></i></div>
                </div>
                <div class="faq-answer">
                    Upload your bank statement (PDF or CSV) from 690+ supported banks. Our AI automatically categorizes transactions into GST components and creates a clean ledger ready for Tally or your accounting system.
                </div>
            </div>

            <!-- FAQ 8 -->
            <div class="faq-item">
                <div class="faq-question">
                    <p class="faq-question-text">Is there a free trial available?</p>
                    <div class="faq-toggle"><i class="bi bi-plus"></i></div>
                </div>
                <div class="faq-answer">
                    Yes! Start with our 14-day free trial. No credit card required. Access all features and see how GSTEcomgstPro can transform your GST compliance process.
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═══ FINAL CTA ═══ -->
<section class="cta-final">
    <div class="contact-container">
        <h2>Ready to Master GST Compliance?</h2>
        <p>Join 10,000+ businesses saving time and recovering ITC with GSTEcomgstPro. Start your free trial today.</p>
        <a href="{{ route('register') }}" class="cta-button">
            <i class="bi bi-lightning-charge-fill"></i> Start Free Trial
        </a>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Contact Form Handler
document.getElementById('contactForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const submitBtn = document.getElementById('submitBtn');
    const originalText = submitBtn.innerHTML;
    const successAlert = document.getElementById('successAlert');
    const errorAlert = document.getElementById('errorAlert');

    // Reset alerts
    successAlert.style.display = 'none';
    errorAlert.style.display = 'none';

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Sending...';

    const formData = new FormData(this);

    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            successAlert.style.display = 'block';
            document.getElementById('contactForm').reset();

            setTimeout(() => {
                successAlert.style.display = 'none';
            }, 5000);
        } else {
            throw new Error(data.message || 'Submission failed');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        errorAlert.textContent = error.message || 'Error sending message. Please try again or call us directly at +91-8076865836';
        errorAlert.style.display = 'block';
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});

// FAQ Accordion
document.querySelectorAll('.faq-question').forEach(question => {
    question.addEventListener('click', function() {
        const item = this.parentElement;
        const wasActive = item.classList.contains('active');

        // Close all other items
        document.querySelectorAll('.faq-item').forEach(el => {
            el.classList.remove('active');
        });

        // Toggle current item
        if (!wasActive) {
            item.classList.add('active');
        }
    });
});
</script>

@endsection
