@extends('layouts.app')
@section('title', 'Sign In — EcomgstPro')

@push('styles')
<style>
:root {
    --navy:     #0a1628;
    --deep:     #0d1f3c;
    --teal:     #00c98d;
    --sky:      #00aaff;
    --teal-low: rgba(0,201,141,.10);
    --muted:    #8a9bbf;
    --border:   rgba(255,255,255,.08);
    --danger:   #ff5c7a;
}

/* ── PAGE SHELL ── */
.auth-page {
    min-height: 100vh;
    background: linear-gradient(135deg, var(--navy) 0%, var(--deep) 60%, #0a2240 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 16px;
    position: relative;
    overflow: hidden;
}

.auth-page::before {
    content:'';position:absolute;width:600px;height:600px;border-radius:50%;
    background:radial-gradient(circle,rgba(0,170,255,.06) 0%,transparent 70%);
    top:-150px;right:-150px;pointer-events:none;
}
.auth-page::after {
    content:'';position:absolute;width:400px;height:400px;border-radius:50%;
    background:radial-gradient(circle,rgba(0,201,141,.07) 0%,transparent 70%);
    bottom:-100px;left:-100px;pointer-events:none;
}

.dot-bg {
    background-image:radial-gradient(rgba(255,255,255,.025) 1px,transparent 1px);
    background-size:26px 26px;
}

/* ── CARD ── */
.auth-card {
    background: rgba(13,31,60,.85);
    backdrop-filter: blur(20px);
    border: 1px solid var(--border);
    border-radius: 24px;
    padding: 44px 40px;
    width: 100%;
    max-width: 500px;
    box-shadow: 0 30px 60px rgba(0,0,0,.4);
    position: relative;
    z-index: 2;
}

/* ── LOGO ── */
.auth-logo {
    display: flex;
    align-items: center;
    gap: 12px;
    justify-content: center;
    margin-bottom: 32px;
    text-decoration: none;
}
.auth-logo img { height: 140px; border-radius: 10px; filter: drop-shadow(0 0 10px rgba(0,201,141,.3)); }
.auth-logo .brand { font-family:'Syne',sans-serif; font-weight:800; font-size:24px; color:#fff; }
.auth-logo .brand span { color:var(--teal); }

/* ── HEADING ── */
.auth-heading {
    text-align: center;
    margin-bottom: 30px;
}
.auth-heading h1 {
    font-family:'Syne',sans-serif; font-weight:800; font-size:26px;
    color:#fff; margin-bottom:8px; line-height:1.2;
}
.auth-heading p { font-size:14px; color:var(--muted); }

/* ── FORM ELEMENTS ── */
.form-group { margin-bottom:20px; }
.form-label {
    display:block; font-size:13px; font-weight:600;
    color:#c0cfe8; margin-bottom:8px;
}
.form-label .req { color:var(--teal); }

.input-wrap { position:relative; }
.input-wrap i {
    position:absolute; left:14px; top:50%; transform:translateY(-50%);
    color:var(--muted); font-size:16px; pointer-events:none;
}

.form-control-gp {
    width:100%; padding:12px 14px 12px 42px;
    background:rgba(255,255,255,.04); border:1.5px solid rgba(255,255,255,.1);
    border-radius:12px; color:#fff; font-family:'DM Sans',sans-serif; font-size:14.5px;
    transition:all .2s; outline:none;
}
.form-control-gp::placeholder { color:rgba(255,255,255,.3); }
.form-control-gp:focus {
    border-color:var(--teal); background:rgba(0,201,141,.04);
    box-shadow:0 0 0 3px rgba(0,201,141,.12);
}
.form-control-gp.is-invalid { border-color:var(--danger); }

/* ── PASSWORD TOGGLE ── */
.pw-toggle {
    position:absolute; right:14px; top:50%; transform:translateY(-50%);
    color:var(--muted); cursor:pointer; font-size:16px; transition:color .2s;
    background:none; border:none; padding:0;
}
.pw-toggle:hover { color:#fff; }

/* ── REMEMBER & FORGOT ── */
.remember-forgot {
    display:flex; justify-content:space-between; align-items:center;
    font-size:13px; margin-bottom:20px; gap:10px;
}
.remember-forgot input[type="checkbox"] { accent-color:var(--teal); cursor:pointer; }
.remember-forgot label { color:var(--muted); cursor:pointer; }
.forgot-link { color:var(--teal); text-decoration:none; transition:opacity .2s; }
.forgot-link:hover { opacity:.8; text-decoration:underline; }

/* ── SINGLE DEVICE NOTE ── */
.device-note {
    display:flex; align-items:flex-start; gap:8px;
    font-size:12px; color:var(--muted);
    background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.07);
    border-radius:10px; padding:10px 12px; margin-bottom:20px;
}
.device-note i { color:var(--sky); font-size:14px; margin-top:1px; flex-shrink:0; }

/* ── SUBMIT BTN ── */
.btn-submit {
    width:100%; padding:14px; font-family:'DM Sans',sans-serif; font-weight:700;
    font-size:15px; color:var(--navy);
    background:linear-gradient(135deg,var(--teal) 0%,#00e0a8 100%);
    border:none; border-radius:13px; cursor:pointer; transition:all .25s;
    display:flex; align-items:center; justify-content:center; gap:8px;
    box-shadow:0 4px 20px rgba(0,201,141,.3);
    margin-top:8px;
}
.btn-submit:hover { transform:translateY(-2px); box-shadow:0 8px 30px rgba(0,201,141,.45); }
.btn-submit:disabled { opacity:.6; cursor:not-allowed; transform:none; }

/* ── DIVIDER ── */
.auth-divider {
    display:flex; align-items:center; gap:12px; margin:22px 0;
}
.auth-divider span { font-size:12px; color:var(--muted); white-space:nowrap; }
.auth-divider hr { flex:1; border:none; border-top:1px solid rgba(255,255,255,.08); }

/* ── FOOTER ── */
.auth-footer { text-align:center; margin-top:22px; font-size:13.5px; color:var(--muted); }
.auth-footer a { color:var(--teal); text-decoration:none; font-weight:600; }
.auth-footer a:hover { text-decoration:underline; }

/* ── ERRORS ── */
.field-error { font-size:12px; color:var(--danger); margin-top:5px; display:flex; align-items:center; gap:4px; }
.alert-gp {
    border-radius:12px; padding:12px 16px; margin-bottom:20px;
    font-size:13.5px; display:flex; align-items:flex-start; gap:10px;
}
.alert-danger-gp { background:rgba(255,92,122,.1); border:1px solid rgba(255,92,122,.25); color:#ffb3c1; }
.alert-success-gp { background:rgba(0,201,141,.1); border:1px solid rgba(0,201,141,.25); color:#a0f5dc; }

@media (max-width:480px) {
    .auth-card { padding:32px 20px; }
    .remember-forgot { flex-direction:column; align-items:flex-start; }
}
</style>
@endpush

@section('content')
<div class="auth-page dot-bg">
    <div class="auth-card">

        {{-- LOGO --}}
       <a href="{{ route('home') }}" class="auth-logo">
    <img src="{{ asset('images/EcomgstPro-logo.png') }}" alt="EcomGSTPro">
</a>
        {{-- HEADING --}}
        <div class="auth-heading">
            <h1>Welcome Back</h1>
            <p>Sign in to your EcomgstPro account</p>
        </div>

        {{-- ALERTS --}}
        @if(session('error'))
            <div class="alert-gp alert-danger-gp">
                <i class="bi bi-exclamation-circle-fill"></i> {{ session('error') }}
            </div>
        @endif
        @if(session('success'))
            <div class="alert-gp alert-success-gp">
                <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
            </div>
        @endif

        {{-- LOGIN FORM --}}
        <form action="{{ route('login.post') }}" method="POST" novalidate>
            @csrf

            <div class="form-group">
                <label class="form-label">Email Address <span class="req">*</span></label>
                <div class="input-wrap">
                    <i class="bi bi-envelope-fill"></i>
                    <input type="email" name="email" id="emailInput"
                           class="form-control-gp {{ $errors->has('email') ? 'is-invalid' : '' }}"
                           placeholder="you@company.com" value="{{ old('email') }}" required autofocus>
                </div>
                @error('email')<div class="field-error"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">Password <span class="req">*</span></label>
                <div class="input-wrap">
                    <i class="bi bi-lock-fill"></i>
                    <input type="password" name="password" id="passwordInput"
                           class="form-control-gp {{ $errors->has('password') ? 'is-invalid' : '' }}"
                           placeholder="Enter your password" style="padding-right:44px;" required>
                    <button type="button" class="pw-toggle" onclick="togglePw('passwordInput',this)">
                        <i class="bi bi-eye-slash"></i>
                    </button>
                </div>
                @error('password')<div class="field-error"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>@enderror
            </div>

            {{-- ← NEW: single-device login notice --}}
            <div class="device-note">
                <i class="bi bi-shield-lock-fill"></i>
                <span>For your account's security, logging in here will automatically sign you out of any other device where you're currently logged in.</span>
            </div>

            <div class="remember-forgot">
                <label>
                    <input type="checkbox" name="remember" id="rememberCheck" {{ old('remember') ? 'checked' : '' }}>
                    <span>Remember me</span>
                </label>
                <a href="{{ route('password.request') }}" class="forgot-link">Forgot password?</a>
            </div>

            <button type="submit" class="btn-submit">
                <i class="bi bi-box-arrow-in-right"></i> Sign In
            </button>
        </form>

        {{-- DIVIDER --}}
        <div class="auth-divider">
            <hr>
            <span>OR</span>
            <hr>
        </div>

        {{-- OTP ALTERNATIVE --}}
        <a href="{{ route('login') }}" style="width:100%;padding:12px;text-align:center;border:1.5px solid rgba(0,201,141,.3);border-radius:12px;color:var(--teal);text-decoration:none;font-weight:600;display:block;transition:all .2s;font-size:14px;"
           onmouseover="this.style.background='rgba(0,201,141,.08)';this.style.borderColor='var(--teal)'"
           onmouseout="this.style.background='transparent';this.style.borderColor='rgba(0,201,141,.3)'">
            <i class="bi bi-envelope-fill" style="margin-right:8px;"></i>Sign in with OTP
        </a>

        {{-- FOOTER --}}
        <div class="auth-footer">
            Don't have an account? <a href="{{ route('register') }}">Create Account</a>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
// ── PASSWORD TOGGLE ──
function togglePw(id, btn) {
    const inp = document.getElementById(id);
    const isText = inp.type === 'text';
    inp.type = isText ? 'password' : 'text';
    btn.querySelector('i').className = isText ? 'bi bi-eye-slash' : 'bi bi-eye';
}
</script>
@endpush