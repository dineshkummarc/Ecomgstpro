{{-- resources/views/layouts/gstr-main.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') | GSTR-1 E-Commerce Tool</title>

    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

    <style>
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

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            overflow-x: hidden;
            font-family: 'DM Sans', sans-serif;
            background: #f0f4ff;
            color: var(--navy);
        }

        /* ── HEADER ────────────────────────────────────────── */
        .gstr-header {
            background: linear-gradient(135deg, var(--navy) 0%, var(--deep) 100%);
            border-bottom: 1px solid var(--border);
            padding: 20px 0;
            box-shadow: 0 4px 20px rgba(0,0,0,.08);
        }

        .header-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            flex-wrap: wrap;
        }

        .header-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .logo-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--teal) 0%, var(--sky) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
            flex-shrink: 0;
        }

        .logo-text {
            font-family: 'Syne', sans-serif;
            font-weight: 800;
            font-size: 20px;
            color: white;
        }

        .logo-text em {
            color: var(--teal);
            font-style: normal;
        }

        .header-nav {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-left: auto;
        }

        .nav-link {
            padding: 10px 16px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            color: var(--muted);
            transition: all .2s;
            border: 1px solid transparent;
        }

        .nav-link:hover {
            color: var(--teal);
            background: rgba(0,201,141,.08);
        }

        .nav-link.active {
            color: var(--teal);
            border-color: var(--teal);
            background: rgba(0,201,141,.12);
        }

        /* ── MAIN CONTENT ────────────────────────────────────── */
        .gstr-main {
            min-height: calc(100vh - 180px);
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 32px;
        }

        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 32px;
            flex-wrap: wrap;
        }

        .page-title {
            font-family: 'Syne', sans-serif;
            font-weight: 800;
            font-size: clamp(28px, 4vw, 42px);
            color: var(--navy);
            line-height: 1.2;
        }

        .page-title em {
            color: var(--teal);
            font-style: normal;
        }

        .page-subtitle {
            font-size: 14px;
            color: var(--muted);
            margin-top: 6px;
        }

        .page-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        /* ── BUTTONS ────────────────────────────────────────── */
        .btn-teal {
            font-family: 'DM Sans', sans-serif;
            font-weight: 700;
            font-size: 14px;
            color: var(--navy);
            background: linear-gradient(135deg, var(--teal) 0%, #00e0a8 100%);
            border: none;
            border-radius: 12px;
            padding: 12px 24px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 18px rgba(0,201,141,.3);
            transition: all .2s;
            text-decoration: none;
        }

        .btn-teal:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(0,201,141,.45);
        }

        .btn-teal:disabled {
            opacity: .4;
            cursor: not-allowed;
        }

        .btn-navy {
            font-family: 'DM Sans', sans-serif;
            font-weight: 700;
            font-size: 14px;
            color: white;
            background: var(--navy);
            border: none;
            border-radius: 12px;
            padding: 12px 24px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all .2s;
            text-decoration: none;
        }

        .btn-navy:hover {
            background: var(--deep);
            transform: translateY(-1px);
        }

        .btn-outline-teal {
            font-family: 'DM Sans', sans-serif;
            font-weight: 700;
            font-size: 14px;
            color: var(--teal);
            background: transparent;
            border: 1.5px solid var(--teal);
            border-radius: 12px;
            padding: 11px 22px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all .2s;
            text-decoration: none;
        }

        .btn-outline-teal:hover {
            background: var(--teal-low);
            transform: translateY(-1px);
        }

        /* ── FOOTER ────────────────────────────────────────── */
        .gstr-footer {
            background: linear-gradient(135deg, var(--navy) 0%, var(--deep) 100%);
            border-top: 1px solid var(--border);
            padding: 32px 0;
            margin-top: 60px;
            color: var(--muted);
            font-size: 13px;
        }

        .footer-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 32px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 32px;
            margin-bottom: 32px;
        }

        .footer-section h4 {
            font-family: 'Syne', sans-serif;
            font-weight: 700;
            color: white;
            margin-bottom: 12px;
            font-size: 14px;
        }

        .footer-section ul {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .footer-section a {
            color: var(--muted);
            text-decoration: none;
            transition: color .2s;
        }

        .footer-section a:hover {
            color: var(--teal);
        }

        .footer-bottom {
            border-top: 1px solid var(--border);
            padding-top: 24px;
            text-align: center;
            color: var(--muted);
            font-size: 12px;
        }

        /* ── TOAST ────────────────────────────────────────── */
        .toast-wrap {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .toast {
            background: var(--navy);
            color: white;
            padding: 12px 20px;
            border-radius: 14px;
            font-size: 13px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 8px 30px rgba(0,0,0,.25);
            animation: slideUp .3s ease;
            max-width: 320px;
        }

        .toast.success {
            background: #00935e;
        }

        .toast.error {
            background: #c0392b;
        }

        .toast.warn {
            background: #b45309;
        }

        .toast.info {
            background: var(--sky);
        }

        @keyframes slideUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* ── RESPONSIVE ────────────────────────────────────── */
        @media (max-width: 768px) {
            .gstr-main {
                padding: 24px 16px;
            }

            .header-container {
                padding: 0 16px;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .page-actions {
                width: 100%;
            }

            .page-actions .btn-teal,
            .page-actions .btn-navy {
                flex: 1;
                justify-content: center;
            }
        }
    </style>

    @yield('styles')
</head>
<body>
    <!-- HEADER -->
    <header class="gstr-header">
        <div class="header-container">
            <a href="{{ route('gstr.dashboard') }}" class="header-logo">
                <div class="logo-icon">
                    <i class="bi bi-receipt-cutoff"></i>
                </div>
                <div>
                    <div class="logo-text">GSTR-<em>1</em></div>
                    <div style="font-size:11px;color:var(--muted);margin-top:2px">E-Commerce Suite</div>
                </div>
            </a>

            <nav class="header-nav">
                <a href="{{ route('gstr.clients.index') }}" class="nav-link @if(request()->routeIs('gstr.clients.*')) active @endif">
                    <i class="bi bi-people-fill"></i> Clients
                </a>
                <a href="{{ route('gstr.upload.index') }}" class="nav-link @if(request()->routeIs('gstr.upload.*')) active @endif">
                    <i class="bi bi-cloud-upload"></i> Upload Data
                </a>
                <a href="#" class="nav-link">
                    <i class="bi bi-question-circle"></i> Help
                </a>
            </nav>
        </div>
    </header>

    <!-- MAIN CONTENT -->
    <main class="gstr-main">
        @yield('content')
    </main>

    <!-- FOOTER -->
    <footer class="gstr-footer">
        <div class="footer-container">
            <div class="footer-section">
                <h4>Product</h4>
                <ul>
                <li><a href="{{ route('features')}}">Features</a></li>
                    <!-- <li><a href="#">Pricing</a></li> -->
                    <!-- <li><a href="#">Security</a></li> -->
                </ul>
            </div>
            <div class="footer-section">
                <h4>Resources</h4>
                <ul>
                    <li><a href="#">Documentation</a></li>
                <li><a href="{{ route('guides') }}">Guides</a></li>
                    <li><a href="{{ route('contact') }}">Support</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Company</h4>
                <ul>
                    <li><a href="{{ route('about') }}">About</a></li>
                    <!-- <li><a href="#">Blog</a></li> -->
                    <li><a href="{{ route('contact') }}">Contact</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Legal</h4>
                <ul>
                    <li><a href="{{ route('privacy') }}">Privacy</a></li>
                    <li><a href="{{ route('terms') }}">Terms</a></li>
                    <!-- <li><a href="#">Compliance</a></li> -->
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; {{ date('Y') }} GSTR-1 E-Commerce Tool. All rights reserved. | GST v3.2.4</p>
        </div>
    </footer>

    <!-- TOAST CONTAINER -->
    <div class="toast-wrap" id="toastWrap"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function toast(msg, type = 'success', duration = 3000) {
            const wrap = document.getElementById('toastWrap');
            const icons = {
                success: 'bi-check-circle-fill',
                error: 'bi-x-circle-fill',
                warn: 'bi-exclamation-triangle-fill',
                info: 'bi-info-circle-fill'
            };
            const t = document.createElement('div');
            t.className = `toast ${type}`;
            t.innerHTML = `<i class="bi ${icons[type] || icons.info}"></i> ${msg}`;
            wrap.appendChild(t);
            setTimeout(() => {
                t.style.opacity = '0';
                t.style.transform = 'translateY(20px)';
                t.style.transition = 'all .3s';
                setTimeout(() => t.remove(), 300);
            }, duration);
        }
    </script>

    @yield('scripts')
</body>
</html>
