{{-- resources/views/admin/layout/footer.blade.php --}}

<style>
.admin-footer {
    background: linear-gradient(135deg, #0a1628 0%, #0d1f3c 100%);
    border-top: 1px solid rgba(255,255,255,.07);
    padding: 18px 28px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
    width: 100%;
    box-sizing: border-box;
}
.af-left {
    font-size: 13px;
    color: #8a9bbf;
    display: flex;
    align-items: center;
    gap: 8px;
}
.af-left strong { color: #00c98d; font-family: 'Syne', sans-serif; }
.af-right {
    display: flex;
    align-items: center;
    gap: 16px;
}
.af-right a {
    font-size: 12px;
    color: #8a9bbf;
    text-decoration: none;
    transition: color .2s;
}
.af-right a:hover { color: #00c98d; }
.af-version {
    background: rgba(0,201,141,.1);
    border: 1px solid rgba(0,201,141,.2);
    color: #00c98d;
    font-size: 11px;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 50px;
}
</style>

<footer class="admin-footer">
    <div class="af-left">
        <i class="bi bi-shield-check" style="color:#00c98d;font-size:16px;"></i>
        &copy; {{ date('Y') }} <strong>EcomgstPro</strong> &nbsp;—&nbsp; Admin Panel
        &nbsp;·&nbsp; Built for CA Professionals
    </div>
    <div class="af-right">
        <a href="{{ route('home') }}" target="_blank">
            <i class="bi bi-globe2"></i> View Site
        </a>
        <a href="{{ route('admin.demo.index') }}">
            <i class="bi bi-calendar-check"></i> Demo Requests
        </a>
        <span class="af-version">v1.0</span>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
