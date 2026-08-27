@php
    $currentRoute = request()->route()->getName();
@endphp

<style>
:root {
    --navy: #0a1628; --deep: #0d1f3c; --teal: #00c98d;
    --muted: #8a9bbf; --border: rgba(255,255,255,0.08); --danger: #ff5c7a;
    --sidebar: 240px; --header: 64px;
}

.admin-sidebar {
    position: fixed;
    top: var(--header);
    left: 0;
    width: var(--sidebar);
    height: calc(100vh - var(--header));
    background: linear-gradient(180deg, var(--navy) 0%, #08111f 100%);
    border-right: 1px solid var(--border);
    overflow-y: auto;
    overflow-x: hidden;
    z-index: 999;
    display: flex;
    flex-direction: column;
    transition: transform .3s cubic-bezier(.22,1,.36,1);
    scrollbar-width: thin;
    scrollbar-color: rgba(255,255,255,.08) transparent;
}
.admin-sidebar::-webkit-scrollbar { width: 4px; }
.admin-sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,.08); border-radius: 4px; }

/* OVERLAY for mobile */
.sidebar-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(5,10,20,.6);
    backdrop-filter: blur(2px);
    z-index: 998;
}
.sidebar-overlay.open { display: block; }

/* NAV SECTION */
.sb-section {
    padding: 20px 12px 8px;
}
.sb-section-label {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 1.4px;
    text-transform: uppercase;
    color: rgba(138,155,191,.5);
    padding: 0 10px;
    margin-bottom: 6px;
}

/* NAV ITEM */
.sb-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    border-radius: 10px;
    color: var(--muted);
    text-decoration: none;
    font-size: 13.5px;
    font-weight: 500;
    font-family: 'DM Sans', sans-serif;
    transition: all .2s;
    margin-bottom: 2px;
    position: relative;
}
.sb-item i {
    font-size: 17px;
    width: 20px;
    flex-shrink: 0;
    text-align: center;
}
.sb-item:hover {
    background: rgba(255,255,255,.05);
    color: #fff;
}
.sb-item.active {
    background: rgba(0,201,141,.12);
    color: var(--teal);
    border: 1px solid rgba(0,201,141,.18);
}
.sb-item.active i { color: var(--teal); }
.sb-item.active::before {
    content: '';
    position: absolute;
    left: 0; top: 25%; bottom: 25%;
    width: 3px;
    background: var(--teal);
    border-radius: 0 3px 3px 0;
}

/* BADGE */
.sb-badge {
    margin-left: auto;
    background: var(--danger);
    color: #fff;
    font-size: 10px;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 50px;
    min-width: 18px;
    text-align: center;
}
.sb-badge.teal { background: var(--teal); color: var(--navy); }

/* DIVIDER */
.sb-divider {
    height: 1px;
    background: var(--border);
    margin: 10px 12px;
}

/* BOTTOM */
.sb-bottom {
    margin-top: auto;
    padding: 12px;
    border-top: 1px solid var(--border);
}
.sb-bottom a, .sb-bottom button {
    display: flex; align-items: center; gap: 10px;
    width: 100%; padding: 10px 12px;
    font-size: 13px; font-weight: 500;
    font-family: 'DM Sans', sans-serif;
    color: var(--muted); text-decoration: none;
    background: transparent; border: none;
    border-radius: 10px; cursor: pointer;
    transition: all .2s; text-align: left;
}
.sb-bottom a i, .sb-bottom button i { font-size: 16px; width: 20px; text-align: center; }
.sb-bottom a:hover { background: rgba(255,255,255,.05); color: #fff; }
.sb-bottom button { color: var(--danger); }
.sb-bottom button i { color: var(--danger); }
.sb-bottom button:hover { background: rgba(255,92,122,.1); color: #ff8aa0; }

/* MAIN CONTENT PUSH */
.admin-main {
    margin-left: var(--sidebar);
    min-height: calc(100vh - var(--header));
    transition: margin-left .3s;
}

@media (max-width: 960px) {
    .admin-sidebar { transform: translateX(-100%); }
    .admin-sidebar.open { transform: translateX(0); }
    .admin-main { margin-left: 0; }
}
</style>

{{-- Overlay --}}
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<aside class="admin-sidebar" id="adminSidebar">

    {{-- MAIN NAV --}}
    <div class="sb-section">
        <div class="sb-section-label">Main</div>

        <a href="{{ route('admin.dashboard') }}"
           class="sb-item {{ $currentRoute === 'admin.dashboard' ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        <a href="{{ route('admin.demo.index') }}"
           class="sb-item {{ str_starts_with($currentRoute, 'admin.demo') ? 'active' : '' }}">
            <i class="bi bi-calendar-check-fill"></i> Demo Requests
            @php $pd = \App\Models\DemoRequest::where('status','pending')->count(); @endphp
            @if($pd > 0)
                <span class="sb-badge">{{ $pd }}</span>
            @endif
        </a>

        <a href="{{ route('admin.contact.index') }}"
           class="sb-item {{ str_starts_with($currentRoute, 'admin.contact') ? 'active' : '' }}">
            <i class="bi bi-envelope-paper-fill"></i> Contact Queries
        </a>
    </div>

    <!--  <a href="{{ route('gstr.clients.index') }}"-->
    <!--   class="sb-item {{ str_starts_with($currentRoute, 'gstr.clients') ? 'active' : '' }}">-->
    <!--    <i class="bi bi-building-fill"></i>users-->
    <!--</a>-->

    <a href="{{ route('admin.users.index') }}"

    <i class="bi bi-people-fill"></i> Users
</a>

<a href="{{ route('admin.subscription.index') }}"

    <i class="bi bi-people-fill"></i> Subscription_Users
</a>

<a href="{{ route('admin.subscription.report') }}"

    <i class="bi bi-people-fill"></i> Subscription_Report
</a>


<!-- class="sb-item {{ str_starts_with($currentRoute, 'admin.users') ? 'active' : '' }}">-->

    <div class="sb-divider"></div>

    {{-- GST TOOLS --}}
    <div class="sb-section">
        <div class="sb-section-label">GST Tools</div>

        <!-- <a href="{{ route('gstr.clients.index') }}"
           class="sb-item {{ str_starts_with($currentRoute, 'gstr.clients') ? 'active' : '' }}">
            <i class="bi bi-building-fill"></i> Clients
        </a> -->

        <!-- <a href="{{ route('gstr.upload.index') }}"
           class="sb-item {{ str_starts_with($currentRoute, 'gstr.upload') ? 'active' : '' }}">
            <i class="bi bi-cloud-upload-fill"></i> GSTR Upload
        </a> -->

        <!-- <a href="{{ route('gstr.summary') }}"
           class="sb-item {{ $currentRoute === 'gstr.summary' ? 'active' : '' }}">
            <i class="bi bi-file-earmark-bar-graph-fill"></i> GSTR Summary
        </a> -->
    </div>

    <div class="sb-divider"></div>

    {{-- SITE --}}
    <div class="sb-section">
        <div class="sb-section-label">Site</div>

        <a href="{{ route('home') }}" target="_blank" class="sb-item">
            <i class="bi bi-globe2"></i> View Website
            <i class="bi bi-box-arrow-up-right" style="font-size:11px;margin-left:auto;opacity:.5;"></i>
        </a>
    </div>

    {{-- BOTTOM --}}
    <div class="sb-bottom">
        <a href="{{ route('dashboard') }}">
            <i class="bi bi-arrow-left-circle-fill"></i> User Dashboard
        </a>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit">
                <i class="bi bi-box-arrow-right"></i> Logout
            </button>
        </form>
    </div>

</aside>

<script>
(function () {
    const toggle  = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('sidebarOverlay');

    function openSidebar()  { sidebar.classList.add('open');  overlay.classList.add('open');  }
    function closeSidebar() { sidebar.classList.remove('open'); overlay.classList.remove('open'); }

    if (toggle)  toggle.addEventListener('click', () => sidebar.classList.contains('open') ? closeSidebar() : openSidebar());
    if (overlay) overlay.addEventListener('click', closeSidebar);
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeSidebar(); });
})();
</script>
