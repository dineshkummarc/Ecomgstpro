{{-- resources/views/layouts/header.blade.php --}}
<!DOCTYPE html>
<html lang="en">
     
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'EcomgstPro - Best GST Filing Tool | Ecommerce to JSON Converter for Amazon, Flipkart, Meesho')</title>
    
    <meta name="google-site-verification" content="fADzVZxLXSWcULUPUHPiNKZwpAqy2osrAnERpUy3Urw" />

<meta name="description" content="@yield('meta_description', 'EcomgstPro is the best GST tool for e-commerce sellers - convert Amazon, Flipkart, Meesho, Myntra sales data to GST portal-ready JSON instantly. Automate GSTR-1 filing, GSTIN validation & 2A/2B reconciliation in one platform.')">

<meta name="keywords" content="@yield('meta_keywords', 'best GST tool, best GST filing software, best ecommerce GST software, GST automation tool, GSTR-1 filing software, ecommerce data to JSON converter, ecommerce to JSON converter, Amazon sales to JSON, Flipkart sales to JSON, Meesho data to JSON, GST portal JSON generator, GST JSON converter online, Amazon GST filing software, Flipkart GSTR-1 filing, Meesho GST return filing, Myntra GST filing tool, Snapdeal GST filing, Nykaa GST automation, JioMart GST software, Glowroad GST filing, ecommerce GST compliance software, ecommerce GST return filing, best GSTR-1 automation tool, GST reconciliation software, 2A 2B reconciliation tool, GSTIN validation tool, B2CS GST filing software, e-commerce operator GST tool, GST filing for online sellers, multi platform GST filing, bulk GST data upload, GST return automation India, online seller GST software, GST compliance SaaS India, ecommerce seller tax filing tool, GST JSON schema generator, GST filing automation software, best GST software for sellers, GST return filing tool India, ecommerce sales data converter, marketplace sales to GST JSON, automated GSTR filing tool, GST tax filing software online, best tool for GST return, online GST filing platform, ecommerce GST automation platform, GST filing tool for Amazon sellers, GST filing tool for Flipkart sellers, sales data to GST format, GST compliance tool for ecommerce, best GSTR-1 software India, GST JSON upload tool')">

<meta name="robots" content="index, follow">
<meta name="author" content="EcomgstPro">

    <!-- Favicon -->
<link rel="icon" type="image/png" href="{{ asset('images/EcomgstPro-logo.png?v=1') }}">
<link rel="shortcut icon" href="{{ asset('images/EcomgstPro-logo.png?v=1') }}">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --navy:      #0a1628;
            --deep:      #0d1f3c;
            --teal:      #00c98d;
            --teal-glow: rgba(0, 201, 141, 0.25);
            --sky:       #00aaff;
            --text-light:#e8f0fe;
            --text-muted:#8a9bbf;
            --border:    rgba(255,255,255,0.08);
            --danger:    #ff5c7a;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        html {
            overflow-x: hidden;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: #f0f4ff;
            overflow-x: hidden;
            max-width: 100vw;
            padding-top: 70px; /* header is position:fixed, height 70px */
        }

        /* ── HEADER SHELL ── */
        .gp-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: linear-gradient(135deg, var(--navy) 0%, var(--deep) 60%, #0a2240 100%);
            border-bottom: 1px solid var(--border);
            box-shadow: 0 4px 40px rgba(0,0,0,0.35);
            width: 100%;
            /* IMPORTANT: do NOT add overflow:hidden here — it clips the
               Services dropdown menu, which is positioned absolutely just
               below the header. Horizontal scroll prevention is handled
               on html/body above instead. */
        }

        .gp-header::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse 420px 180px at 15% 50%, rgba(0,170,255,0.07) 0%, transparent 70%),
                radial-gradient(ellipse 300px 120px at 85% 50%, rgba(0,201,141,0.08) 0%, transparent 70%);
            pointer-events: none;
        }

        .gp-inner {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 20px;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            position: relative;
            width: 100%;
        }

        /* ── LOGO AREA ── */
        .gp-logo-wrap {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            flex-shrink: 0;
        }

        .gp-logo-img {
            height: 140px;
            width: auto;
            border-radius: 10px;
            filter: drop-shadow(0 0 10px var(--teal-glow));
        }

        .gp-logo-text {
            display: flex;
            flex-direction: column;
            line-height: 1;
        }

        .gp-logo-text .brand {
            font-family: 'Syne', sans-serif;
            font-weight: 800;
            font-size: 22px;
            letter-spacing: -0.5px;
            color: #fff;
        }

        .gp-logo-text .brand span {
            color: var(--teal);
        }

        .gp-logo-text .tagline {
            font-size: 9.5px;
            font-weight: 500;
            letter-spacing: 1.8px;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-top: 2px;
        }

        @media (max-width: 480px) {
            .gp-logo-text .tagline { display: none; }
            .gp-logo-img { height: 36px; }
            .gp-logo-text .brand { font-size: 19px; }
        }

        /* ── DIVIDER ── */
        .gp-divider {
            width: 1px;
            height: 28px;
            background: var(--border);
            flex-shrink: 0;
        }

        /* ── NAV LINKS (with DROPDOWN) ── */
        .gp-nav {
            display: flex;
            align-items: center;
            gap: 2px;
            list-style: none;
            margin: 0;
            padding: 0;
            flex-shrink: 1;
            min-width: 0;
        }

        .gp-nav li {
            position: relative;
        }

        .gp-nav a {
            font-family: 'DM Sans', sans-serif;
            font-weight: 500;
            font-size: 14px;
            color: var(--text-muted);
            text-decoration: none;
            padding: 6px 11px;
            border-radius: 8px;
            transition: color 0.2s, background 0.2s;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .gp-nav a:hover {
            color: #fff;
            background: rgba(255,255,255,0.06);
        }

        .gp-nav a.active {
            color: var(--teal);
        }

        /* dropdown indicator */
        .has-drop > a::after {
            content: '▾';
            font-size: 10px;
            margin-left: 4px;
            opacity: 0.7;
            transition: transform 0.2s;
        }

        .has-drop:hover > a::after {
            transform: rotate(180deg);
        }

        /* Custom Dropdown Menu */
        .gp-dropdown-menu {
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            min-width: 280px;
            background: linear-gradient(135deg, #0d1f3c 0%, #0a1628 100%);
            backdrop-filter: blur(12px);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 8px 0;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-8px);
            transition: all 0.25s ease;
            z-index: 1050;
            box-shadow: 0 18px 35px -12px rgba(0,0,0,0.4);
        }

        .has-drop:hover .gp-dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .gp-dropdown-menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 18px;
            font-size: 13.5px;
            color: var(--text-muted);
            white-space: normal;
            border-radius: 0;
            background: transparent;
        }

        .gp-dropdown-menu a i {
            font-size: 18px;
            width: 24px;
            color: var(--teal);
        }

        .gp-dropdown-menu a:hover {
            background: rgba(0,201,141,0.08);
            color: #fff;
        }

        .gp-dropdown-divider {
            height: 1px;
            background: var(--border);
            margin: 6px 0;
        }

        /* ── SPACER ── */
        .gp-spacer { flex: 0 1 20px; }

        /* ── RIGHT ACTIONS ── */
        .gp-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }

        .gp-btn-ghost {
            font-family: 'DM Sans', sans-serif;
            font-weight: 600;
            font-size: 13.5px;
            color: var(--text-light);
            background: transparent;
            border: 1px solid var(--border);
            border-radius: 9px;
            padding: 8px 16px;
            text-decoration: none;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .gp-btn-ghost:hover {
            color: #fff;
            border-color: rgba(255,255,255,0.2);
            background: rgba(255,255,255,0.05);
        }

        .gp-btn-demo {
            font-family: 'DM Sans', sans-serif;
            font-weight: 600;
            font-size: 13.5px;
            color: var(--navy);
            background: linear-gradient(135deg, var(--teal) 0%, #00e0a8 100%);
            border: none;
            border-radius: 9px;
            padding: 9px 18px;
            text-decoration: none;
            transition: all 0.2s;
            box-shadow: 0 4px 18px var(--teal-glow);
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .gp-btn-demo:hover {
            opacity: 0.9;
            transform: translateY(-1px);
            box-shadow: 0 6px 24px rgba(0, 201, 141, 0.4);
            color: var(--navy);
        }

        /* Hide the "Request Demo" button slightly earlier than the full nav
           collapse, so Sign In / Sign Up / Profile never get pushed off-screen
           on laptop-sized viewports (1024px–1200px). */
        @media (max-width: 1200px) {
            .gp-actions > a.gp-btn-demo[href*="demo"] {
                display: none;
            }
        }

        /* ── PROFILE / AVATAR DROPDOWN (Desktop) ── */
        .gp-profile {
            position: relative;
            flex-shrink: 0;
        }

        .gp-profile-trigger {
            display: flex;
            align-items: center;
            gap: 9px;
            background: rgba(255,255,255,0.04);
            border: 1px solid var(--border);
            border-radius: 50px;
            padding: 5px 14px 5px 5px;
            cursor: pointer;
            transition: all 0.2s;
            user-select: none;
        }

        .gp-profile-trigger:hover {
            background: rgba(255,255,255,0.08);
            border-color: rgba(255,255,255,0.18);
        }

        .gp-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--teal);
            flex-shrink: 0;
            background: linear-gradient(135deg, var(--teal), var(--sky));
        }

        .gp-avatar-fallback {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            border: 2px solid var(--teal);
            flex-shrink: 0;
            background: linear-gradient(135deg, var(--teal), var(--sky));
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Syne', sans-serif;
            font-weight: 700;
            font-size: 13px;
            color: var(--navy);
        }

        .gp-profile-name {
            font-size: 13.5px;
            font-weight: 600;
            color: var(--text-light);
            max-width: 90px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .gp-profile-trigger i.bi-chevron-down {
            font-size: 11px;
            color: var(--text-muted);
            transition: transform 0.2s;
        }

        .gp-profile.open .gp-profile-trigger i.bi-chevron-down {
            transform: rotate(180deg);
        }

        .gp-profile-menu {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            min-width: 240px;
            background: linear-gradient(135deg, #0d1f3c 0%, #0a1628 100%);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 10px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-8px);
            transition: all 0.22s ease;
            z-index: 1050;
            box-shadow: 0 18px 35px -12px rgba(0,0,0,0.45);
        }

        .gp-profile.open .gp-profile-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .gp-profile-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 6px 10px 14px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 8px;
        }

        .gp-profile-header .gp-avatar,
        .gp-profile-header .gp-avatar-fallback {
            width: 42px;
            height: 42px;
            font-size: 15px;
        }

        .gp-profile-header .ph-info {
            line-height: 1.3;
            overflow: hidden;
        }

        .gp-profile-header .ph-name {
            font-weight: 700;
            font-size: 14px;
            color: #fff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .gp-profile-header .ph-email {
            font-size: 12px;
            color: var(--text-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .gp-profile-menu a,
        .gp-profile-menu button {
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
            padding: 10px 12px;
            font-size: 13.5px;
            font-family: 'DM Sans', sans-serif;
            font-weight: 500;
            color: var(--text-muted);
            text-decoration: none;
            background: transparent;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            text-align: left;
            transition: all 0.18s;
        }

        .gp-profile-menu a i,
        .gp-profile-menu button i {
            font-size: 17px;
            width: 22px;
            color: var(--teal);
        }

        .gp-profile-menu a:hover,
        .gp-profile-menu button:hover {
            background: rgba(0,201,141,0.08);
            color: #fff;
        }

        .gp-profile-menu .logout-item {
            margin-top: 4px;
            border-top: 1px solid var(--border);
            padding-top: 12px;
        }

        .gp-profile-menu .logout-item button {
            color: var(--danger);
        }

        .gp-profile-menu .logout-item button i {
            color: var(--danger);
        }

        .gp-profile-menu .logout-item button:hover {
            background: rgba(255,92,122,0.1);
            color: #ff8aa0;
        }

        /* ── MOBILE TOGGLE ── */
        .gp-toggle {
            display: none;
            flex-direction: column;
            gap: 5px;
            cursor: pointer;
            padding: 4px;
            background: none;
            border: none;
            z-index: 1101;
        }

        .gp-toggle span {
            display: block;
            width: 22px;
            height: 2px;
            background: var(--text-light);
            border-radius: 2px;
            transition: all 0.3s;
        }

        /* ── MOBILE: AVATAR ALWAYS VISIBLE NEXT TO TOGGLE ── */
        .gp-mobile-avatar-btn {
            display: none;
        }

        /* ── MOBILE SLIDE-IN DRAWER ── */
        .gp-drawer-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(5, 10, 20, 0.6);
            backdrop-filter: blur(2px);
            z-index: 1090;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .gp-drawer-overlay.open {
            display: block;
            opacity: 1;
        }

        .gp-mobile-menu {
            position: fixed;
            top: 0;
            right: 0;
            height: 100%;
            width: min(320px, 84vw);
            background: linear-gradient(160deg, var(--deep) 0%, var(--navy) 100%);
            border-left: 1px solid var(--border);
            z-index: 1100;
            display: flex;
            flex-direction: column;
            transform: translateX(100%);
            transition: transform 0.32s cubic-bezier(0.22, 1, 0.36, 1);
            box-shadow: -10px 0 40px rgba(0,0,0,0.4);
            overflow-y: auto;
        }

        .gp-mobile-menu.open {
            transform: translateX(0);
        }

        .gp-drawer-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 18px 14px;
            border-bottom: 1px solid var(--border);
            flex-shrink: 0;
        }

        .gp-drawer-close {
            background: rgba(255,255,255,0.06);
            border: 1px solid var(--border);
            color: var(--text-light);
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 18px;
            transition: all 0.2s;
        }

        .gp-drawer-close:hover {
            background: rgba(255,255,255,0.12);
            color: #fff;
        }

        .gp-drawer-user {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 18px;
            border-bottom: 1px solid var(--border);
            flex-shrink: 0;
        }

        .gp-drawer-user .gp-avatar,
        .gp-drawer-user .gp-avatar-fallback {
            width: 46px;
            height: 46px;
            font-size: 16px;
        }

        .gp-drawer-user .du-info {
            overflow: hidden;
            line-height: 1.3;
        }

        .gp-drawer-user .du-name {
            font-weight: 700;
            font-size: 14.5px;
            color: #fff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .gp-drawer-user .du-email {
            font-size: 12px;
            color: var(--text-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .gp-drawer-links {
            display: flex;
            flex-direction: column;
            padding: 14px 14px 18px;
            gap: 2px;
            flex: 1;
        }

        .gp-mobile-menu a,
        .gp-mobile-menu button.gp-mobile-link {
            font-size: 15px;
            font-weight: 500;
            color: var(--text-muted);
            text-decoration: none;
            padding: 12px 14px;
            border-radius: 10px;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 12px;
            background: transparent;
            border: none;
            width: 100%;
            text-align: left;
            font-family: 'DM Sans', sans-serif;
            cursor: pointer;
        }

        .gp-mobile-menu a i,
        .gp-mobile-menu button.gp-mobile-link i {
            width: 24px;
            font-size: 18px;
            color: var(--teal);
            flex-shrink: 0;
        }

        .gp-mobile-menu a:hover,
        .gp-mobile-menu button.gp-mobile-link:hover {
            color: #fff;
            background: rgba(255,255,255,0.06);
        }

        .gp-mobile-menu a.active {
            color: var(--teal);
            background: rgba(0,201,141,0.08);
        }

        .gp-mobile-divider {
            height: 1px;
            background: var(--border);
            margin: 10px 4px;
        }

        .gp-drawer-footer {
            padding: 14px 18px 20px;
            border-top: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            gap: 10px;
            flex-shrink: 0;
        }

        .gp-mobile-menu .gp-btn-demo,
        .gp-mobile-menu .gp-btn-ghost {
            justify-content: center;
            text-align: center;
        }

        .mobile-dropdown-toggle {
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
        }

        .mobile-dropdown-toggle > span {
            display: flex;
            align-items: center;
            gap: 12px;
            flex: 1;
            color: var(--text-muted);
            font-size: 15px;
            font-weight: 500;
        }

        .mobile-dropdown-toggle > span i.bi-grid-3x3-gap-fill {
            width: 24px;
            font-size: 18px;
            color: var(--teal);
        }

        .mobile-dropdown-toggle:hover > span {
            color: #fff;
        }

        .mobile-dropdown-toggle {
            padding: 12px 14px;
            border-radius: 10px;
            transition: background 0.2s;
        }

        .mobile-dropdown-toggle:hover {
            background: rgba(255,255,255,0.06);
        }

        #mobileChevron {
            color: var(--teal);
            font-size: 13px;
            transition: transform 0.25s;
            padding: 6px;
        }

        #mobileChevron.rotated {
            transform: rotate(180deg);
        }

        .mobile-submenu {
            display: none;
            flex-direction: column;
            padding-left: 30px;
            margin-top: 2px;
            margin-bottom: 4px;
            gap: 1px;
            overflow: hidden;
        }

        .mobile-submenu a {
            font-size: 13.5px;
            padding: 9px 12px;
        }

        .mobile-submenu.open {
            display: flex;
        }

        /* Lock body scroll when drawer open */
        body.gp-no-scroll {
            overflow: hidden;
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 960px) {
            .gp-nav, .gp-divider, .gp-spacer { display: none; }
            .gp-toggle { display: flex; }
            .gp-actions .gp-btn-ghost,
            .gp-actions .gp-btn-demo,
            .gp-actions .gp-profile { display: none; }
            .gp-mobile-avatar-btn { display: flex; }
        }

        .gp-mobile-avatar-btn {
            align-items: center;
            justify-content: center;
            background: none;
            border: none;
            padding: 0;
            cursor: pointer;
        }

        @media (min-width: 961px) {
            .gp-drawer-overlay, .gp-mobile-menu { display: none !important; }
        }
    </style>

    @stack('styles')
</head>
<body>

@php
    // If we're on admin routes, we MUST use the `admin` guard.
    // Otherwise, for all other routes, use the default `web` guard.
    // This ensures the correct user (admin or regular user) is fetched.
    $authUser = request()->is('admin/*') || request()->routeIs('admin.*')
        ? Auth::guard('admin')->user()
        : Auth::guard('web')->user();
    $avatarUrl = null;
    $initials = 'U';
    if ($authUser) {
        $avatarUrl = $authUser->avatar
            ? (str_starts_with($authUser->avatar, 'http') ? $authUser->avatar : asset('storage/' . $authUser->avatar))
            : null;
        $nameForInitials = $authUser->name ?? $authUser->email ?? 'User';
        $parts = explode(' ', trim($nameForInitials));
        $initials = strtoupper(substr($parts[0] ?? 'U', 0, 1) . substr($parts[1] ?? '', 0, 1));
    }
@endphp

<header class="gp-header">
    <div class="gp-inner">

        {{-- LOGO --}}
        <a href="{{ route('home') }}" class="gp-logo-wrap">
            <img src="{{ asset('images/EcomgstPro-logo.png') }}" alt="EcomgstPro" class="gp-logo-img" onerror="this.src='https://placehold.co/42x42/00c98d/0a1628?text=GP'">
            <!-- <div class="gp-logo-text">
                <span class="brand">GST<span>Pilot</span></span>
                <span class="tagline">Analyze · Automate · Accelerate</span>
            </div> -->
        </a>

        {{-- DIVIDER --}}
        <div class="gp-divider"></div>

        {{-- DESKTOP NAVIGATION with SERVICES DROPDOWN --}}
        <ul class="gp-nav">
            <li><a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Home</a></li>
            <li class="has-drop">
                <a href="#"><i class="bi bi-grid-3x3-gap-fill"></i> Services</a>
                <div class="gp-dropdown-menu">
                    <!--<a href="{{ route('services.bank-statement-converter') }}">-->
                    <!--    <i class="bi bi-file-pdf-fill"></i> PDF Bank Statement Converter-->
                    <!--</a>-->
                    <!-- <a href="{{ route('services.ecommerce-gstr') }}">
                        <i class="bi bi-shop"></i> E-Commerce GSTR-1
                    </a> -->
                    <a href="{{ route('gstr.clients.index') }}">
                        <i class="bi bi-people-fill"></i>E-Commerce_GSTR-1
                    </a>
                    <!-- <a href="{{ route('services.reconciliation') }}">
                        <i class="bi bi-arrow-left-right"></i> 2A/2B Reconciliation
                    </a>
                    <a href="{{ route('services.itc-optimizer') }}">
                        <i class="bi bi-calculator-fill"></i> ITC Optimizer
                    </a>
                    <div class="gp-dropdown-divider"></div>
                    <a href="{{ route('services.bulk-upload') }}">
                        <i class="bi bi-file-spreadsheet-fill"></i> Bulk Upload Tool
                    </a>
                    <a href="{{ route('services.gstr-auto') }}">
                        <i class="bi bi-graph-up"></i> GSTR-9/9C Auto
                    </a> -->
                </div>
            </li>
            <li><a href="{{ route('features') }}" class="{{ request()->routeIs('features') ? 'active' : '' }}">Features</a></li>
            <!--<li><a href="{{ route('pricing') }}" class="{{ request()->routeIs('pricing') ? 'active' : '' }}">Pricing</a></li>-->
            <!-- <li><a href="{{ route('tutorials') }}" class="{{ request()->routeIs('tutorials') ? 'active' : '' }}">Tutorials</a></li> -->
            <li><a href="{{ route('contact') }}" class="{{ request()->routeIs('contact') ? 'active' : '' }}">Contact</a></li>
            <li><a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'active' : '' }}">About</a></li>
        </ul>

        <div class="gp-spacer"></div>

        {{-- RIGHT ACTIONS (DESKTOP) --}}
        <div class="gp-actions">
            @if($authUser)
                {{-- PROFILE AVATAR DROPDOWN --}}

                <div class="gp-profile" id="gpProfile">
                    <div class="gp-profile-trigger" id="gpProfileTrigger">
                        @if($avatarUrl)
                            <img src="{{ $avatarUrl }}" alt="{{ $authUser->name }}" class="gp-avatar">
                        @else
                            <div class="gp-avatar-fallback">{{ $initials }}</div>
                        @endif
                        <span class="gp-profile-name">{{ $authUser->name ?? 'Account' }}</span>
                        <i class="bi bi-chevron-down"></i>
                    </div>

                    <div class="gp-profile-menu" id="gpProfileMenu">
                        <div class="gp-profile-header">
                            @if($avatarUrl)
                                <img src="{{ $avatarUrl }}" alt="{{ $authUser->name }}" class="gp-avatar">
                            @else
                                <div class="gp-avatar-fallback">{{ $initials }}</div>
                            @endif
                            <div class="ph-info">
                                <div class="ph-name">{{ $authUser->name ?? 'Account' }}</div>
                                <div class="ph-email">{{ $authUser->email ?? '' }}</div>
                            </div>
                        </div>

                        <a href="{{ route('dashboard') }}">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                        <a href="{{ route('settings') }}">
                            <i class="bi bi-gear-fill"></i> Settings
                        </a>

                        <div class="logout-item">
                            <form action="{{ route('logout') }}" method="POST" id="logout-form">
                                @csrf
                                <button type="submit">
                                    <i class="bi bi-box-arrow-right"></i> Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}" class="gp-btn-ghost">
                    <i class="bi bi-box-arrow-in-right"></i> Sign In
                </a>
                <a href="{{ route('register') }}" class="gp-btn-demo">
                    <i class="bi bi-person-plus-fill"></i> Sign Up
                </a>
            @endif
            <a href="{{ route('demo.request') }}" class="gp-btn-demo">
                <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px">
                    <polygon points="5,3 13,8 5,13"/>
                </svg>
                Request Demo
            </a>
        </div>

        {{-- MOBILE: avatar (if logged in) + hamburger toggle --}}
        <div style="display:flex; align-items:center; gap:12px;">
            @if($authUser)
                <button class="gp-mobile-avatar-btn" id="gpMobileAvatarBtn" aria-label="Open menu">
                    @if($avatarUrl)
                        <img src="{{ $avatarUrl }}" alt="{{ $authUser->name }}" class="gp-avatar" style="width:32px;height:32px;">
                    @else
                        <div class="gp-avatar-fallback" style="width:32px;height:32px;font-size:12px;">{{ $initials }}</div>
                    @endif
                </button>
            @endif

            <button class="gp-toggle" id="gpToggle" aria-label="Menu">
                <span></span><span></span><span></span>
            </button>
        </div>

    </div>
</header>

{{-- DRAWER OVERLAY --}}
<div class="gp-drawer-overlay" id="gpDrawerOverlay"></div>

{{-- MOBILE SLIDE-IN DRAWER --}}
<nav class="gp-mobile-menu" id="gpMobileMenu">

    <div class="gp-drawer-top">
        <span style="font-family:'Syne',sans-serif; font-weight:800; font-size:17px; color:#fff;">
            EcomGstpro<span style="color:var(--teal)"></span>
        </span>
        <button class="gp-drawer-close" id="gpDrawerClose" aria-label="Close menu">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    @if($authUser)
        <div class="gp-drawer-user">
            @if($avatarUrl)
                <img src="{{ $avatarUrl }}" alt="{{ $authUser->name }}" class="gp-avatar">
            @else
                <div class="gp-avatar-fallback">{{ $initials }}</div>
            @endif
            <div class="du-info">
                <div class="du-name">{{ $authUser->name ?? 'Account' }}</div>
                <div class="du-email">{{ $authUser->email ?? '' }}</div>
            </div>
        </div>
    @endif

    <div class="gp-drawer-links">
        <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">
            <i class="bi bi-house-door-fill"></i> Home
        </a>

        {{-- Services with dropdown for mobile --}}
        <div class="mobile-dropdown-toggle" id="mobileServicesToggle">
            <span><i class="bi bi-grid-3x3-gap-fill"></i> Services</span>
            <i class="bi bi-chevron-down" id="mobileChevron"></i>
        </div>
        <div class="mobile-submenu" id="mobileSubmenu">
            <!--<a href="{{ route('services.bank-statement-converter') }}"><i class="bi bi-file-pdf-fill"></i> PDF Bank Statement Converter</a>-->
            <a href="{{ route('gstr.clients.index') }}"><i class="bi bi-shop"></i> E-Commerce GSTR-1</a>
            
            <!--no any use this -->
            <!--<a href="{{ route('services.reconciliation') }}"><i class="bi bi-arrow-left-right"></i> 2A/2B Reconciliation</a>-->
            <!--<a href="{{ route('services.itc-optimizer') }}"><i class="bi bi-calculator-fill"></i> ITC Optimizer</a>-->
            <!--<a href="{{ route('services.bulk-upload') }}"><i class="bi bi-file-spreadsheet-fill"></i> Bulk Upload Tool</a>-->
            <!--<a href="{{ route('services.gstr-auto') }}"><i class="bi bi-graph-up"></i> GSTR-9/9C Auto</a>-->
        </div>

        <a href="{{ route('features') }}" class="{{ request()->routeIs('features') ? 'active' : '' }}"><i class="bi bi-star-fill"></i> Features</a>
        <a href="{{ route('pricing') }}" class="{{ request()->routeIs('pricing') ? 'active' : '' }}"><i class="bi bi-tag-fill"></i> Pricing</a>
        <a href="{{ route('tutorials') }}" class="{{ request()->routeIs('tutorials') ? 'active' : '' }}"><i class="bi bi-play-circle-fill"></i> Tutorials</a>
        <a href="{{ route('contact') }}" class="{{ request()->routeIs('contact') ? 'active' : '' }}"><i class="bi bi-envelope-fill"></i> Contact</a>
        <a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'active' : '' }}"><i class="bi bi-info-circle-fill"></i> About</a>

        @auth
            <div class="gp-mobile-divider"></div>
            <a href="{{ route('dashboard') }}"><i class="bi bi-speedometer2"></i> Dashboard</a>
            <a href="{{ route('settings') }}"><i class="bi bi-gear-fill"></i> Settings</a>
        @endauth
    </div>

    <div class="gp-drawer-footer">
        @auth
            <form action="{{ route('logout') }}" method="POST" id="logout-form-mobile">
                @csrf
                <button type="submit" class="gp-mobile-link" style="color:var(--danger);">
                    <i class="bi bi-box-arrow-right" style="color:var(--danger);"></i> Logout
                </button>
            </form>
        @else
            <a href="{{ route('login') }}" class="gp-btn-ghost">
                <i class="bi bi-box-arrow-in-right"></i> Sign In
            </a>
            <a href="{{ route('register') }}" class="gp-btn-demo">
                <i class="bi bi-person-plus-fill"></i> Sign Up
            </a>
        @endauth
        <a href="{{ route('demo.request') }}" class="gp-btn-demo">
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px">
                <polygon points="5,3 13,8 5,13"/>
            </svg>
            Request Demo
        </a>
    </div>
</nav>

<script>
(function () {
    // ── MOBILE DRAWER OPEN/CLOSE ──
    const toggle      = document.getElementById('gpToggle');
    const avatarBtn    = document.getElementById('gpMobileAvatarBtn');
    const drawer       = document.getElementById('gpMobileMenu');
    const overlay      = document.getElementById('gpDrawerOverlay');
    const closeBtn     = document.getElementById('gpDrawerClose');
    const body         = document.body;

    function openDrawer() {
        drawer.classList.add('open');
        overlay.classList.add('open');
        body.classList.add('gp-no-scroll');
        const spans = toggle.querySelectorAll('span');
        spans[0].style.transform = 'rotate(45deg) translate(5px, 5px)';
        spans[1].style.opacity   = '0';
        spans[2].style.transform = 'rotate(-45deg) translate(5px, -5px)';
    }

    function closeDrawer() {
        drawer.classList.remove('open');
        overlay.classList.remove('open');
        body.classList.remove('gp-no-scroll');
        const spans = toggle.querySelectorAll('span');
        spans.forEach(s => { s.style.transform = ''; s.style.opacity = ''; });
    }

    function toggleDrawer() {
        if (drawer.classList.contains('open')) {
            closeDrawer();
        } else {
            openDrawer();
        }
    }

    if (toggle) toggle.addEventListener('click', toggleDrawer);
    if (avatarBtn) avatarBtn.addEventListener('click', openDrawer);
    if (closeBtn) closeBtn.addEventListener('click', closeDrawer);
    if (overlay) overlay.addEventListener('click', closeDrawer);

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeDrawer();
    });

    // ── MOBILE SERVICES SUBMENU TOGGLE ──
    const mobileServicesToggle = document.getElementById('mobileServicesToggle');
    const mobileSubmenu        = document.getElementById('mobileSubmenu');
    const mobileChevron        = document.getElementById('mobileChevron');

    if (mobileServicesToggle && mobileSubmenu) {
        mobileServicesToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            mobileSubmenu.classList.toggle('open');
            if (mobileChevron) mobileChevron.classList.toggle('rotated');
        });
    }

    // ── DESKTOP PROFILE DROPDOWN (click to open, click outside to close) ──
    const gpProfile        = document.getElementById('gpProfile');
    const gpProfileTrigger  = document.getElementById('gpProfileTrigger');

    if (gpProfile && gpProfileTrigger) {
        gpProfileTrigger.addEventListener('click', (e) => {
            e.stopPropagation();
            e.preventDefault();
            gpProfile.classList.toggle('open');
        });

        document.addEventListener('click', (e) => {
            if (!gpProfile.contains(e.target)) {
                gpProfile.classList.remove('open');
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') gpProfile.classList.remove('open');
        });
    } else {
        // Helpful console hint during development: if the profile block
        // didn't render at all, #gpProfile won't exist in the DOM, which
        // means auth()->user() was null when this page was rendered.
        console.warn('[EcomgstPro Header] Profile dropdown elements not found - ' +
            'this usually means the user is NOT authenticated server-side ' +
            '(auth()->user() returned null), so the auth-only block in the ' +
            'header never rendered the avatar/dropdown markup.');
    }
})();
</script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

@stack('scripts')
</body>
</html>
