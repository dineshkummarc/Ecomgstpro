@extends('layouts.app')
@section('title', 'Reset Password — EcomgstPro')

@push('styles')
<style>
:root{--navy:#0a1628;--deep:#0d1f3c;--teal:#00c98d;--muted:#8a9bbf;--border:rgba(255,255,255,.08);--danger:#ff5c7a;}
.auth-page{min-height:100vh;background:linear-gradient(135deg,var(--navy) 0%,var(--deep) 60%,#0a2240 100%);display:flex;align-items:center;justify-content:center;padding:40px 16px;position:relative;overflow:hidden;}
.auth-page::before{content:'';position:absolute;width:600px;height:600px;border-radius:50%;background:radial-gradient(circle,rgba(0,170,255,.06) 0%,transparent 70%);top:-150px;right:-150px;pointer-events:none;}
.auth-page::after{content:'';position:absolute;width:400px;height:400px;border-radius:50%;background:radial-gradient(circle,rgba(0,201,141,.07) 0%,transparent 70%);bottom:-100px;left:-100px;pointer-events:none;}
.dot-bg{background-image:radial-gradient(rgba(255,255,255,.025) 1px,transparent 1px);background-size:26px 26px;}
.auth-card{background:rgba(13,31,60,.85);backdrop-filter:blur(20px);border:1px solid var(--border);border-radius:24px;padding:44px 40px;width:100%;max-width:460px;box-shadow:0 30px 60px rgba(0,0,0,.4);position:relative;z-index:2;}
.auth-logo{display:flex;align-items:center;gap:12px;justify-content:center;margin-bottom:32px;text-decoration:none;}
.auth-logo img{height:44px;border-radius:10px;filter:drop-shadow(0 0 10px rgba(0,201,141,.3));}
.auth-logo .brand{font-family:'Syne',sans-serif;font-weight:800;font-size:24px;color:#fff;}
.auth-logo .brand span{color:var(--teal);}
.auth-heading{text-align:center;margin-bottom:30px;}
.auth-heading h1{font-family:'Syne',sans-serif;font-weight:800;font-size:26px;color:#fff;margin-bottom:8px;line-height:1.2;}
.auth-heading p{font-size:14px;color:var(--muted);}
.icon-circle{width:72px;height:72px;border-radius:50%;background:rgba(0,201,141,.1);border:1.5px solid rgba(0,201,141,.25);display:inline-flex;align-items:center;justify-content:center;margin:0 auto 20px;display:flex;}
.form-group{margin-bottom:20px;}
.form-label{display:block;font-size:13px;font-weight:600;color:#c0cfe8;margin-bottom:8px;}
.input-wrap{position:relative;}
.input-wrap i.left-icon{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:16px;pointer-events:none;}
.form-control-gp{width:100%;padding:12px 14px 12px 42px;background:rgba(255,255,255,.04);border:1.5px solid rgba(255,255,255,.1);border-radius:12px;color:#fff;font-family:'DM Sans',sans-serif;font-size:14.5px;transition:all .2s;outline:none;}
.form-control-gp::placeholder{color:rgba(255,255,255,.3);}
.form-control-gp:focus{border-color:var(--teal);background:rgba(0,201,141,.04);box-shadow:0 0 0 3px rgba(0,201,141,.12);}
.btn-submit{width:100%;padding:14px;font-family:'DM Sans',sans-serif;font-weight:700;font-size:15px;color:var(--navy);background:linear-gradient(135deg,var(--teal) 0%,#00e0a8 100%);border:none;border-radius:13px;cursor:pointer;transition:all .25s;display:flex;align-items:center;justify-content:center;gap:8px;box-shadow:0 4px 20px rgba(0,201,141,.3);margin-top:8px;}
.btn-submit:hover{transform:translateY(-2px);box-shadow:0 8px 30px rgba(0,201,141,.45);}
.auth-footer{text-align:center;margin-top:22px;font-size:13.5px;color:var(--muted);}
.auth-footer a{color:var(--teal);text-decoration:none;font-weight:600;}
.alert-gp{border-radius:12px;padding:12px 16px;margin-bottom:20px;font-size:13.5px;display:flex;align-items:flex-start;gap:10px;}
.alert-danger-gp{background:rgba(255,92,122,.1);border:1px solid rgba(255,92,122,.25);color:#ffb3c1;}
.alert-success-gp{background:rgba(0,201,141,.1);border:1px solid rgba(0,201,141,.25);color:#a0f5dc;}
.otp-boxes{display:flex;gap:10px;justify-content:center;margin:8px 0;}
.otp-box{width:52px;height:58px;background:rgba(255,255,255,.04);border:1.5px solid rgba(255,255,255,.1);border-radius:12px;text-align:center;font-size:22px;font-weight:700;color:#fff;font-family:'Syne',sans-serif;transition:all .2s;outline:none;}
.otp-box:focus{border-color:var(--teal);box-shadow:0 0 0 3px rgba(0,201,141,.15);}
.pw-toggle{position:absolute;right:14px;top:50%;transform:translateY(-50%);color:var(--muted);cursor:pointer;font-size:16px;background:none;border:none;padding:0;}
.pw-toggle:hover{color:#fff;}
.step-panel{display:none;}
.step-panel.active{display:block;}
@media(max-width:480px){.auth-card{padding:32px 20px;}.otp-box{width:44px;height:52px;font-size:20px;}}
</style>
@endpush

@section('content')
<div class="auth-page dot-bg">
    <div class="auth-card">

        <a href="{{ route('home') }}" class="auth-logo">
            <img src="{{ asset('images/gstpilot-logo.png') }}" alt="GSTpilot"
                 onerror="this.src='https://placehold.co/44x44/00c98d/0a1628?text=GP'">
            <span class="brand">EcomGst<span>Pro</span></span>
        </a>

        @if(session('status'))
            <div class="alert-gp alert-success-gp">
                <i class="bi bi-check-circle-fill"></i> {{ session('status') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert-gp alert-danger-gp">
                <i class="bi bi-exclamation-circle-fill"></i> {{ session('error') }}
            </div>
        @endif

        {{-- PANEL 1: Enter Email --}}
        <div class="step-panel active" id="panel1">
            <div style="text-align:center;margin-bottom:24px;">
                <div class="icon-circle">
                    <i class="bi bi-shield-lock-fill" style="font-size:30px;color:var(--teal);"></i>
                </div>
                <h1 style="font-family:'Syne',sans-serif;font-weight:800;font-size:24px;color:#fff;margin-bottom:8px;">Forgot Password?</h1>
                <p style="font-size:14px;color:var(--muted);">No worries! Enter your email and we'll send you a reset OTP.</p>
            </div>

            <form action="{{ route('password.email') }}" method="POST" id="forgotForm">
                @csrf
                <div class="form-group">
                    <label class="form-label">Registered Email</label>
                    <div class="input-wrap">
                        <i class="bi bi-envelope-fill left-icon"></i>
                        <input type="email" name="email" id="forgotEmail"
                               class="form-control-gp" placeholder="you@company.com"
                               value="{{ old('email') }}" required>
                    </div>
                    @error('email')<div class="field-error" style="color:var(--danger);font-size:12px;margin-top:5px;">{{ $message }}</div>@enderror
                </div>

                <button type="submit" class="btn-submit">
                    <i class="bi bi-send-fill"></i> Send Reset OTP
                </button>
            </form>
        </div>

        {{-- PANEL 2: Enter OTP + New Password --}}
        <div class="step-panel" id="panel2">
            <div style="text-align:center;margin-bottom:24px;">
                <div class="icon-circle">
                    <i class="bi bi-key-fill" style="font-size:30px;color:var(--teal);"></i>
                </div>
                <h1 style="font-family:'Syne',sans-serif;font-weight:800;font-size:24px;color:#fff;margin-bottom:8px;">Reset Password</h1>
                <p style="font-size:14px;color:var(--muted);">Enter the OTP sent to your email and set a new password.</p>
            </div>

            <form action="{{ route('password.update') }}" method="POST">
                @csrf
                <input type="hidden" name="token" value="{{ $token ?? '' }}">
                <input type="hidden" name="email" id="resetEmail" value="{{ request()->email ?? old('email') }}">

                <div class="form-group">
                    <label class="form-label">Enter OTP</label>
                    <div class="otp-boxes">
                        <input type="text" class="otp-box" maxlength="1" inputmode="numeric" id="rotp1">
                        <input type="text" class="otp-box" maxlength="1" inputmode="numeric" id="rotp2">
                        <input type="text" class="otp-box" maxlength="1" inputmode="numeric" id="rotp3">
                        <input type="text" class="otp-box" maxlength="1" inputmode="numeric" id="rotp4">
                        <input type="text" class="otp-box" maxlength="1" inputmode="numeric" id="rotp5">
                        <input type="text" class="otp-box" maxlength="1" inputmode="numeric" id="rotp6">
                    </div>
                    <input type="hidden" name="otp" id="resetOtpVal">
                </div>

                <div class="form-group">
                    <label class="form-label">New Password</label>
                    <div class="input-wrap">
                        <i class="bi bi-lock-fill left-icon"></i>
                        <input type="password" name="password" id="newPw"
                               class="form-control-gp" placeholder="Min. 8 characters"
                               style="padding-right:44px;" required>
                        <button type="button" class="pw-toggle" onclick="togglePw('newPw','npIcon')">
                            <i class="bi bi-eye-slash" id="npIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Confirm New Password</label>
                    <div class="input-wrap">
                        <i class="bi bi-lock-fill left-icon"></i>
                        <input type="password" name="password_confirmation" id="confPw"
                               class="form-control-gp" placeholder="Repeat password"
                               style="padding-right:44px;" required>
                        <button type="button" class="pw-toggle" onclick="togglePw('confPw','cpIcon')">
                            <i class="bi bi-eye-slash" id="cpIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-submit" onclick="collectResetOtp()">
                    <i class="bi bi-check-circle-fill"></i> Reset Password
                </button>
            </form>
        </div>

        <div class="auth-footer">
            <a href="{{ route('login') }}"><i class="bi bi-arrow-left"></i> Back to Sign In</a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Check if we should show panel 2 (after OTP sent or token present)
@if(isset($token) || request()->has('otp_sent'))
    document.getElementById('panel1').classList.remove('active');
    document.getElementById('panel2').classList.add('active');
@endif

// OTP box navigation
const resetBoxes = document.querySelectorAll('#panel2 .otp-box');
resetBoxes.forEach((box, i) => {
    box.addEventListener('input', () => { if (box.value && i < resetBoxes.length - 1) resetBoxes[i+1].focus(); });
    box.addEventListener('keydown', (e) => { if (e.key === 'Backspace' && !box.value && i > 0) resetBoxes[i-1].focus(); });
});

function collectResetOtp() {
    const val = [1,2,3,4,5,6].map(i => document.getElementById('rotp'+i).value).join('');
    document.getElementById('resetOtpVal').value = val;
}

function togglePw(id, iconId) {
    const inp = document.getElementById(id);
    const icon = document.getElementById(iconId);
    inp.type = inp.type === 'password' ? 'text' : 'password';
    icon.className = inp.type === 'password' ? 'bi bi-eye-slash' : 'bi bi-eye';
}
</script>
@endpush
