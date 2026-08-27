<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-url" content="{{ config('app.url') }}">
    <title>@yield('title', 'Admin Panel — EcomgstPro')</title>

    <!-- Favicon -->
<link rel="icon" type="image/png" href="{{ asset('images/EcomgstPro-logo.png') }}">
<link rel="shortcut icon" href="{{ asset('images/EcomgstPro-logo.png') }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --navy:    #0a1628;
            --deep:    #0d1f3c;
            --teal:    #00c98d;
            --sky:     #00aaff;
            --muted:   #8a9bbf;
            --border:  rgba(255,255,255,0.08);
            --danger:  #ff5c7a;
            --sidebar: 240px;
            --header:  64px;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; overflow-x: hidden; }
        body {
            font-family: 'DM Sans', sans-serif;
            background: #f0f4ff;
            padding-top: var(--header);
        }

        /* ── TOPBAR ── */
        .admin-topbar {
            position: fixed;
            top: 0; left: 0; right: 0;
            height: var(--header);
            background: linear-gradient(135deg, var(--navy) 0%, var(--deep) 100%);
            border-bottom: 1px solid var(--border);
            box-shadow: 0 2px 20px rgba(0,0,0,0.3);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px 0 0;
        }

        /* Logo area */
        .topbar-logo {
            width: var(--sidebar);
            height: 100%;
            display: flex;
            align-items: center;
            padding: 0 20px;
            gap: 10px;
            border-right: 1px solid var(--border);
            text-decoration: none;
            flex-shrink: 0;
        }
        .topbar-logo .brand {
            font-family: 'Syne', sans-serif;
            font-weight: 800;
            font-size: 20px;
            color: #fff;
        }
        .topbar-logo .brand span { color: var(--teal); }
        .topbar-logo .tag {
            font-size: 9px;
            font-weight: 600;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--muted);
        }

        /* Topbar center */
        .topbar-center {
            flex: 1;
            display: flex;
            align-items: center;
            padding: 0 20px;
            gap: 12px;
        }

        .topbar-toggle {
            display: none;
            background: none;
            border: none;
            color: #fff;
            font-size: 20px;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 8px;
            transition: background .2s;
        }
        .topbar-toggle:hover { background: rgba(255,255,255,.08); }

        .topbar-breadcrumb {
            font-size: 13px;
            color: var(--muted);
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .topbar-breadcrumb span { color: #fff; font-weight: 600; }

        /* Topbar right */
        .topbar-right {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .topbar-icon-btn {
            position: relative;
            width: 38px; height: 38px;
            border-radius: 10px;
            background: rgba(255,255,255,.05);
            border: 1px solid var(--border);
            display: flex; align-items: center; justify-content: center;
            color: var(--muted);
            font-size: 17px;
            cursor: pointer;
            transition: all .2s;
            text-decoration: none;
        }
        .topbar-icon-btn:hover { background: rgba(255,255,255,.1); color: #fff; }

        .notif-dot {
            position: absolute;
            top: 7px; right: 7px;
            width: 8px; height: 8px;
            background: var(--danger);
            border-radius: 50%;
            border: 2px solid var(--navy);
        }

        /* Admin profile pill */
        .topbar-profile {
            position: relative;
        }
        .topbar-profile-trigger {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 5px 12px 5px 5px;
            background: rgba(255,255,255,.05);
            border: 1px solid var(--border);
            border-radius: 50px;
            cursor: pointer;
            transition: all .2s;
            user-select: none;
        }
        .topbar-profile-trigger:hover { background: rgba(255,255,255,.1); border-color: rgba(255,255,255,.2); }

        .tp-avatar {
            width: 32px; height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--teal), var(--sky));
            display: flex; align-items: center; justify-content: center;
            font-family: 'Syne', sans-serif;
            font-weight: 700; font-size: 13px;
            color: var(--navy);
            flex-shrink: 0;
        }
        .tp-name {
            font-size: 13px; font-weight: 600;
            color: #e8f0fe;
            max-width: 100px;
            overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
        }
        .tp-role {
            font-size: 10px; color: var(--teal); font-weight: 600;
            text-transform: uppercase; letter-spacing: .8px;
        }

        .topbar-profile-menu {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            min-width: 220px;
            background: var(--deep);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 8px;
            opacity: 0; visibility: hidden;
            transform: translateY(-8px);
            transition: all .22s ease;
            z-index: 1050;
            box-shadow: 0 16px 40px rgba(0,0,0,.45);
        }
        .topbar-profile.open .topbar-profile-menu { opacity: 1; visibility: visible; transform: translateY(0); }

        .tpm-header {
            padding: 8px 10px 12px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 6px;
        }
        .tpm-header .tpm-name { font-weight: 700; font-size: 14px; color: #fff; }
        .tpm-header .tpm-email { font-size: 12px; color: var(--muted); }

        .topbar-profile-menu a,
        .topbar-profile-menu button {
            display: flex; align-items: center; gap: 10px;
            width: 100%; padding: 9px 10px;
            font-size: 13px; font-family: 'DM Sans', sans-serif; font-weight: 500;
            color: var(--muted); text-decoration: none;
            background: transparent; border: none;
            border-radius: 8px; cursor: pointer;
            transition: all .18s; text-align: left;
        }
        .topbar-profile-menu a i, .topbar-profile-menu button i { font-size: 16px; color: var(--teal); width: 18px; }
        .topbar-profile-menu a:hover, .topbar-profile-menu button:hover { background: rgba(0,201,141,.08); color: #fff; }
        .tpm-logout { margin-top: 4px; border-top: 1px solid var(--border); padding-top: 8px; }
        .tpm-logout button { color: var(--danger); }
        .tpm-logout button i { color: var(--danger); }
        .tpm-logout button:hover { background: rgba(255,92,122,.1); color: #ff8aa0; }

        @media (max-width: 960px) {
            .topbar-logo { width: auto; border-right: none; }
            .topbar-toggle { display: flex; }
            .topbar-breadcrumb { display: none; }
            .tp-name, .tp-role { display: none; }
        }
    </style>

    @stack('styles')
</head>
<body>

@php
    $admin = Auth::guard('admin')->user();
    $initials = 'A';
    if ($admin) {
        $parts = explode(' ', trim($admin->name ?? 'Admin'));
        $initials = strtoupper(substr($parts[0] ?? 'A', 0, 1) . substr($parts[1] ?? '', 0, 1));
    }
@endphp

<header class="admin-topbar">

    {{-- LOGO --}}
    <a href="{{ route('admin.dashboard') }}" class="topbar-logo">
        <div style="display:flex;flex-direction:column;line-height:1;">
            <span class="brand">EcomGst<span>Pro</span></span>
            <span class="tag">Admin Panel</span>
        </div>
    </a>

    {{-- CENTER --}}
    <div class="topbar-center">
        <button class="topbar-toggle" id="sidebarToggle">
            <i class="bi bi-list"></i>
        </button>
        <div class="topbar-breadcrumb">
            <i class="bi bi-house-door"></i>
            Admin
            <i class="bi bi-chevron-right" style="font-size:10px;"></i>
            <span>@yield('breadcrumb', 'Dashboard')</span>
        </div>
    </div>

    {{-- RIGHT --}}
    <div class="topbar-right">

        {{-- Demo requests notification --}}
        <a href="{{ route('admin.demo.index') }}" class="topbar-icon-btn" title="Demo Requests">
            <i class="bi bi-calendar-check"></i>
            @php $pendingDemo = \App\Models\DemoRequest::where('status','pending')->count(); @endphp
            @if($pendingDemo > 0)
                <span class="notif-dot"></span>
            @endif
        </a>

        {{-- Home --}}
        <a href="{{ route('home') }}" class="topbar-icon-btn" title="View Site" target="_blank">
            <i class="bi bi-box-arrow-up-right"></i>
        </a>

        {{-- Profile Dropdown --}}
        <div class="topbar-profile" id="tpProfile">
            <div class="topbar-profile-trigger" id="tpTrigger">
                <div class="tp-avatar">{{ $initials }}</div>
                <div>
                    <div class="tp-name">{{ $admin->name ?? 'Admin' }}</div>
                    <div class="tp-role">Administrator</div>
                </div>
                <i class="bi bi-chevron-down" style="font-size:11px;color:var(--muted);transition:transform .2s;" id="tpChevron"></i>
            </div>
            <div class="topbar-profile-menu" id="tpMenu">
                <div class="tpm-header">
                    <div class="tpm-name">{{ $admin->name ?? 'Admin' }}</div>
                    <div class="tpm-email">{{ $admin->email ?? '' }}</div>
                </div>
                <a href="{{ route('dashboard') }}"><i class="bi bi-speedometer2"></i> Main Dashboard</a>
                <div class="tpm-logout">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit"><i class="bi bi-box-arrow-right"></i> Logout</button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</header>

<script>
(function () {
    const tp = document.getElementById('tpProfile');
    const trigger = document.getElementById('tpTrigger');
    const chevron = document.getElementById('tpChevron');
    if (tp && trigger) {
        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            tp.classList.toggle('open');
            if (chevron) chevron.style.transform = tp.classList.contains('open') ? 'rotate(180deg)' : '';
        });
        document.addEventListener('click', () => {
            tp.classList.remove('open');
            if (chevron) chevron.style.transform = '';
        });
    }
})();
</script>
