@extends('layouts.app')
@section('title', 'Create Account — EcomgstPro')

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

.auth-logo {
    display: flex;
    align-items: center;
    gap: 12px;
    justify-content: center;
    margin-bottom: 32px;
    text-decoration: none;
}
.auth-logo img { height: 160px; border-radius: 10px; filter: drop-shadow(0 0 10px rgba(0,201,141,.3)); }
.auth-logo .brand { font-family:'Syne',sans-serif; font-weight:800; font-size:24px; color:#fff; }
.auth-logo .brand span { color:var(--teal); }

.auth-heading {
    text-align: center;
    margin-bottom: 30px;
}
.auth-heading h1 {
    font-family:'Syne',sans-serif; font-weight:800; font-size:26px;
    color:#fff; margin-bottom:8px; line-height:1.2;
}
.auth-heading p { font-size:14px; color:var(--muted); }

/* ── TRIAL BADGE ── */
.trial-badge {
    display:flex; align-items:center; justify-content:center; gap:8px;
    background:linear-gradient(135deg, rgba(0,201,141,.14), rgba(0,170,255,.10));
    border:1px solid rgba(0,201,141,.3);
    border-radius:12px; padding:10px 16px; margin-bottom:26px;
    font-size:13px; font-weight:700; color:#a0f5dc;
    text-align:center;
}
.trial-badge i { color:var(--teal); font-size:15px; }

.step-indicator {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0;
    margin-bottom: 32px;
}
.step {
    display: flex; align-items: center; gap: 8px;
    font-size:13px; font-weight:600; color:var(--muted);
}
.step.active { color:var(--teal); }
.step.done   { color:var(--teal); opacity:.8; }
.step-dot {
    width:32px; height:32px; border-radius:50%;
    background:rgba(255,255,255,.06); border:1.5px solid rgba(255,255,255,.15);
    display:flex; align-items:center; justify-content:center;
    font-size:13px; font-weight:700; color:var(--muted);
    transition:all .3s;
}
.step.active .step-dot { background:var(--teal); border-color:var(--teal); color:var(--navy); }
.step.done   .step-dot { background:rgba(0,201,141,.2); border-color:var(--teal); color:var(--teal); }
.step-line { width:48px; height:1.5px; background:rgba(255,255,255,.1); flex-shrink:0; transition:background .3s; }
.step-line.done { background:var(--teal); }

.form-group { margin-bottom:20px; }
.form-label {
    display:block; font-size:13px; font-weight:600;
    color:#c0cfe8; margin-bottom:8px;
}
.form-label .req { color:var(--teal); }

.input-wrap { position:relative; }
.input-wrap > i.icon-left {
    position:absolute; left:14px; top:50%; transform:translateY(-50%);
    color:var(--muted); font-size:16px; pointer-events:none;
}

.form-control-gp {
    width:100%; padding:12px 14px 12px 42px;
    background:rgba(255,255,255,.04); border:1.5px solid rgba(255,255,255,.1);
    border-radius:12px; color:#fff; font-family:'DM Sans',sans-serif; font-size:14.5px;
    transition:all .2s; outline:none; box-sizing:border-box;
}
.form-control-gp::placeholder { color:rgba(255,255,255,.3); }
.form-control-gp:focus {
    border-color:var(--teal); background:rgba(0,201,141,.04);
    box-shadow:0 0 0 3px rgba(0,201,141,.12);
}
.form-control-gp.is-invalid { border-color:var(--danger) !important; }

.otp-boxes { display:flex; gap:10px; justify-content:center; margin:8px 0; }
.otp-digit {
    width:52px; height:58px;
    background:rgba(255,255,255,.04); border:1.5px solid rgba(255,255,255,.1);
    border-radius:12px; text-align:center; font-size:22px; font-weight:700;
    color:#fff; font-family:'Syne',sans-serif;
    transition:all .2s; outline:none; caret-color:var(--teal);
}
.otp-digit:focus { border-color:var(--teal); box-shadow:0 0 0 3px rgba(0,201,141,.15); }
.otp-digit.filled { border-color:rgba(0,201,141,.4); background:rgba(0,201,141,.06); }

.otp-timer { text-align:center; font-size:13px; color:var(--muted); margin-top:12px; }
.otp-timer .timer-count { color:var(--teal); font-weight:700; }
.resend-link {
    color:var(--teal); cursor:pointer; text-decoration:underline;
    font-weight:600; display:none;
}

.pw-strength { margin-top:8px; }
.pw-bars { display:flex; gap:4px; margin-bottom:4px; }
.pw-bar { flex:1; height:3px; border-radius:2px; background:rgba(255,255,255,.08); transition:all .3s; }
.pw-bar.weak   { background:#ff5c7a; }
.pw-bar.medium { background:#fbbf24; }
.pw-bar.strong { background:var(--teal); }
.pw-text { font-size:12px; color:var(--muted); }

.pw-toggle {
    position:absolute; right:14px; top:50%; transform:translateY(-50%);
    color:var(--muted); cursor:pointer; font-size:16px; transition:color .2s;
    background:none; border:none; padding:0;
}
.pw-toggle:hover { color:#fff; }

.btn-submit {
    width:100%; padding:14px; font-family:'DM Sans',sans-serif; font-weight:700;
    font-size:15px; color:var(--navy);
    background:linear-gradient(135deg,var(--teal) 0%,#00e0a8 100%);
    border:none; border-radius:13px; cursor:pointer; transition:all .25s;
    display:flex; align-items:center; justify-content:center; gap:8px;
    box-shadow:0 4px 20px rgba(0,201,141,.3);
    margin-top:8px;
}
.btn-submit:hover:not(:disabled) { transform:translateY(-2px); box-shadow:0 8px 30px rgba(0,201,141,.45); }
.btn-submit:disabled { opacity:.6; cursor:not-allowed; transform:none; }

.spin {
    width:16px; height:16px; border:2px solid rgba(10,22,40,.3);
    border-top-color:var(--navy); border-radius:50%;
    animation:spin .7s linear infinite; display:inline-block;
}
@keyframes spin { to { transform:rotate(360deg); } }

.field-error { font-size:12px; color:var(--danger); margin-top:5px; display:flex; align-items:center; gap:4px; }
.alert-gp {
    border-radius:12px; padding:12px 16px; margin-bottom:16px;
    font-size:13.5px; display:flex; align-items:flex-start; gap:10px;
}
.alert-danger-gp  { background:rgba(255,92,122,.1);  border:1px solid rgba(255,92,122,.25);  color:#ffb3c1; }
.alert-success-gp { background:rgba(0,201,141,.1);   border:1px solid rgba(0,201,141,.25);   color:#a0f5dc; }

.auth-step { display:none; }
.auth-step.active { display:block; }

.terms-check { display:flex; gap:10px; align-items:flex-start; }
.terms-check input[type="checkbox"] { margin-top:3px; accent-color:var(--teal); flex-shrink:0; }
.terms-check label { font-size:13px; color:var(--muted); }
.terms-check a { color:var(--teal); }

.auth-footer { text-align:center; margin-top:22px; font-size:13.5px; color:var(--muted); }
.auth-footer a { color:var(--teal); text-decoration:none; font-weight:600; }
.auth-footer a:hover { text-decoration:underline; }

.btn-back {
    width:100%; margin-top:12px; padding:11px;
    background:transparent; border:1px solid rgba(255,255,255,.1);
    border-radius:12px; color:var(--muted); cursor:pointer;
    font-family:'DM Sans',sans-serif; font-size:14px; transition:all .2s;
}
.btn-back:hover { border-color:rgba(255,255,255,.25); color:#fff; }

.success-screen { text-align:center; padding:20px 0 10px; }
.success-screen .check-ring {
    width:80px; height:80px; border-radius:50%;
    background:rgba(0,201,141,.12); border:2px solid var(--teal);
    display:inline-flex; align-items:center; justify-content:center;
    margin-bottom:20px;
}
.success-screen h3 {
    font-family:'Syne',sans-serif; font-weight:800; font-size:24px;
    color:#fff; margin-bottom:10px;
}
.success-screen p { color:var(--muted); font-size:14px; }

@media (max-width:480px) {
    .auth-card { padding:32px 20px; }
    .otp-digit { width:44px; height:52px; font-size:20px; }
    .step-line { width:28px; }
}

</style>
@endpush

@section('content')
<div class="auth-page dot-bg">
    <div class="auth-card">

        {{-- ── LOGO ── --}}
<a href="{{ route('home') }}" class="auth-logo">
    <img src="{{ asset('images/EcomgstPro-logo.png') }}" alt="EcomGSTPro">
</a>

        {{-- ── HEADING ── --}}
        <div class="auth-heading">
            <h1>Create Your Account</h1>
            <p>Start automating GST compliance in minutes</p>
        </div>

        {{-- ← NEW: trial badge --}}
        <div class="trial-badge">
            <i class="bi bi-gift-fill"></i> 11 Days Free Trial — No Credit Card Required
        </div>

        {{-- ── STEP INDICATOR ── --}}
        <div class="step-indicator">
            <div class="step active" id="si1">
                <div class="step-dot" id="sd1">1</div>
                <span>Details</span>
            </div>
            <div class="step-line" id="sl1"></div>
            <div class="step" id="si2">
                <div class="step-dot" id="sd2">2</div>
                <span>Verify OTP</span>
            </div>
            <div class="step-line" id="sl2"></div>
            <div class="step" id="si3">
                <div class="step-dot" id="sd3">3</div>
                <span>Done</span>
            </div>
        </div>

        {{-- ═══════════════════════════════════════
             STEP 1 — User Details
        ════════════════════════════════════════ --}}
        <div class="auth-step active" id="step1">

            <div id="formErrorBox" class="alert-gp alert-danger-gp" style="display:none;"></div>

            <form id="registerForm" novalidate>
                @csrf

                <div class="form-group">
                    <label class="form-label">Full Name <span class="req">*</span></label>
                    <div class="input-wrap">
                        <i class="bi bi-person-fill icon-left"></i>
                        <input type="text" name="name" id="nameInput"
                               class="form-control-gp"
                               placeholder="e.g. Rahul Sharma"
                               value="{{ old('name') }}" autocomplete="name" required>
                    </div>
                    <div class="field-error" id="err-name" style="display:none;"></div>
                </div>

                <div class="form-group">
                    <label class="form-label">Email Address <span class="req">*</span></label>
                    <div class="input-wrap">
                        <i class="bi bi-envelope-fill icon-left"></i>
                        <input type="email" name="email" id="emailInput"
                               class="form-control-gp"
                               placeholder="you@company.com"
                               value="{{ old('email') }}" autocomplete="email" required>
                    </div>
                    <div class="field-error" id="err-email" style="display:none;"></div>
                </div>

                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <div class="input-wrap">
                        <i class="bi bi-phone-fill icon-left"></i>
                        <input type="tel" name="phone"
                               class="form-control-gp"
                               placeholder="+91 98765 43210"
                               value="{{ old('phone') }}" maxlength="15">
                    </div>
                    <div class="field-error" id="err-phone" style="display:none;"></div>
                </div>

                <div class="form-group">
                    <label class="form-label">Company / Business Name</label>
                    <div class="input-wrap">
                        <i class="bi bi-building icon-left"></i>
                        <input type="text" name="company_name"
                               class="form-control-gp"
                               placeholder="Your Business Pvt Ltd"
                               value="{{ old('company_name') }}">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Password <span class="req">*</span></label>
                    <div class="input-wrap">
                        <i class="bi bi-lock-fill icon-left"></i>
                        <input type="password" name="password" id="passwordInput"
                               class="form-control-gp"
                               placeholder="Min. 8 characters"
                               style="padding-right:44px;"
                               oninput="checkPwStrength(this.value)"
                               autocomplete="new-password" required>
                        <button type="button" class="pw-toggle" onclick="togglePw('passwordInput',this)">
                            <i class="bi bi-eye-slash"></i>
                        </button>
                    </div>
                    <div class="pw-strength">
                        <div class="pw-bars">
                            <div class="pw-bar" id="pb1"></div>
                            <div class="pw-bar" id="pb2"></div>
                            <div class="pw-bar" id="pb3"></div>
                            <div class="pw-bar" id="pb4"></div>
                        </div>
                        <span class="pw-text" id="pwText">Enter a password</span>
                    </div>
                    <div class="field-error" id="err-password" style="display:none;"></div>
                </div>

                <div class="form-group">
                    <label class="form-label">Confirm Password <span class="req">*</span></label>
                    <div class="input-wrap">
                        <i class="bi bi-lock-fill icon-left"></i>
                        <input type="password" name="password_confirmation" id="confirmInput"
                               class="form-control-gp"
                               placeholder="Re-enter password"
                               style="padding-right:44px;"
                               autocomplete="new-password" required>
                        <button type="button" class="pw-toggle" onclick="togglePw('confirmInput',this)">
                            <i class="bi bi-eye-slash"></i>
                        </button>
                    </div>
                </div>

                <div class="terms-check form-group">
                    <input type="checkbox" id="termsCheck" name="terms">
                    <label for="termsCheck">
                        I agree to the <a href="{{ route('terms') }}" target="_blank">Terms of Service</a>
                        and <a href="{{ route('privacy') }}" target="_blank">Privacy Policy</a>
                    </label>
                </div>
                <div class="field-error" id="err-terms" style="display:none;">
                    <i class="bi bi-exclamation-circle"></i> You must agree to the Terms of Service.
                </div>

                <button type="submit" class="btn-submit" id="submitBtn">
                    <i class="bi bi-arrow-right-circle-fill"></i> Send OTP & Continue
                </button>
            </form>
        </div>

        {{-- ═══════════════════════════════════════
             STEP 2 — OTP Verification
        ════════════════════════════════════════ --}}
        <div class="auth-step" id="step2">

            <div style="text-align:center;margin-bottom:24px;">
                <div style="width:64px;height:64px;border-radius:50%;background:rgba(0,201,141,.1);border:1.5px solid rgba(0,201,141,.25);display:inline-flex;align-items:center;justify-content:center;margin-bottom:14px;">
                    <i class="bi bi-envelope-check-fill" style="font-size:28px;color:var(--teal);"></i>
                </div>
                <h3 style="font-family:'Syne',sans-serif;font-weight:700;font-size:20px;color:#fff;margin-bottom:8px;">
                    Check Your Email
                </h3>
                <p style="font-size:14px;color:var(--muted);">
                    We sent a 6-digit OTP to <strong style="color:#fff;" id="displayEmail"></strong>
                </p>
            </div>

            <div id="otpErrorBox" class="alert-gp alert-danger-gp" style="display:none;"></div>

            <form id="otpForm" novalidate>
                <div class="form-group">
                    <label class="form-label" style="text-align:center;display:block;">
                        Enter 6-digit OTP
                    </label>
                    <div class="otp-boxes">
                        <input type="text" class="otp-digit" maxlength="1" inputmode="numeric" id="otp1">
                        <input type="text" class="otp-digit" maxlength="1" inputmode="numeric" id="otp2">
                        <input type="text" class="otp-digit" maxlength="1" inputmode="numeric" id="otp3">
                        <input type="text" class="otp-digit" maxlength="1" inputmode="numeric" id="otp4">
                        <input type="text" class="otp-digit" maxlength="1" inputmode="numeric" id="otp5">
                        <input type="text" class="otp-digit" maxlength="1" inputmode="numeric" id="otp6">
                    </div>
                    <input type="hidden" id="otpValue">
                </div>

                <div class="otp-timer" id="timerWrap">
                    OTP expires in <span class="timer-count" id="timerDisplay">10:00</span>
                    &nbsp;·&nbsp;
                    <span class="resend-link" id="resendLink" onclick="resendOtp()">Resend OTP</span>
                </div>

                <button type="submit" class="btn-submit" id="verifyBtn" style="margin-top:20px;">
                    <i class="bi bi-check-circle-fill"></i> Verify & Create Account
                </button>
            </form>

            <button class="btn-back" onclick="backToStep1()">
                <i class="bi bi-arrow-left"></i> Back to Details
            </button>
        </div>

        {{-- ═══════════════════════════════════════
             STEP 3 — Success
        ════════════════════════════════════════ --}}
        <div class="auth-step" id="step3">
            <div class="success-screen">
                <div class="check-ring">
                    <i class="bi bi-check-lg" style="font-size:38px;color:var(--teal);"></i>
                </div>
                <h3>Registration Successful!</h3>
                <p>Your 11-day free trial has started.<br>Redirecting to your dashboard...</p>
                <div style="margin-top:20px;">
                    <div class="spin" style="margin:0 auto;border-top-color:var(--teal);border-color:rgba(0,201,141,.2);width:24px;height:24px;"></div>
                </div>
            </div>
        </div>

        {{-- ── FOOTER ── --}}
        <div class="auth-footer">
            Already have an account? <a href="{{ route('login') }}">Sign In</a>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
function checkPwStrength(pw) {
    const bars = ['pb1','pb2','pb3','pb4'];
    const txt  = document.getElementById('pwText');
    bars.forEach(id => { document.getElementById(id).className = 'pw-bar'; });

    if (!pw) { txt.textContent = 'Enter a password'; txt.style.color = ''; return; }

    let score = 0;
    if (pw.length >= 8)          score++;
    if (/[A-Z]/.test(pw))        score++;
    if (/[0-9]/.test(pw))        score++;
    if (/[^A-Za-z0-9]/.test(pw)) score++;

    const cls    = score <= 1 ? 'weak' : score <= 3 ? 'medium' : 'strong';
    const labels = ['', 'Weak', 'Fair', 'Good', 'Strong'];
    const colors = { weak:'#ff5c7a', medium:'#fbbf24', strong:'#00c98d' };

    for (let i = 0; i < score; i++) document.getElementById(bars[i]).classList.add(cls);
    txt.textContent = labels[score] || '';
    txt.style.color = colors[cls];
}

function togglePw(id, btn) {
    const inp    = document.getElementById(id);
    const isText = inp.type === 'text';
    inp.type     = isText ? 'password' : 'text';
    btn.querySelector('i').className = isText ? 'bi bi-eye-slash' : 'bi bi-eye';
}

(function initOtpBoxes() {
    document.addEventListener('DOMContentLoaded', function () {
        const boxes = Array.from(document.querySelectorAll('.otp-digit'));

        boxes.forEach((box, i) => {
            box.addEventListener('input', () => {
                box.value = box.value.replace(/\D/g, '').slice(-1);
                box.classList.toggle('filled', !!box.value);
                if (box.value && i < boxes.length - 1) boxes[i + 1].focus();
            });

            box.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !box.value && i > 0) {
                    boxes[i - 1].value = '';
                    boxes[i - 1].classList.remove('filled');
                    boxes[i - 1].focus();
                }
            });

            box.addEventListener('paste', (e) => {
                e.preventDefault();
                const text = (e.clipboardData || window.clipboardData)
                    .getData('text').replace(/\D/g, '');
                boxes.forEach((b, j) => {
                    b.value = text[j] || '';
                    b.classList.toggle('filled', !!b.value);
                });
                const focusIdx = Math.min(text.length, boxes.length - 1);
                boxes[focusIdx].focus();
            });
        });
    });
})();

function collectOtp() {
    return [1,2,3,4,5,6].map(i => document.getElementById('otp' + i).value).join('');
}

function goToStep2() {
    const si1 = document.getElementById('si1');
    const sl1 = document.getElementById('sl1');
    const si2 = document.getElementById('si2');
    const sd1 = document.getElementById('sd1');

    si1.classList.remove('active'); si1.classList.add('done');
    sd1.innerHTML = '<i class="bi bi-check-lg"></i>';
    sl1.classList.add('done');
    si2.classList.add('active');

    document.getElementById('step1').classList.remove('active');
    document.getElementById('step2').classList.add('active');

    setTimeout(() => document.getElementById('otp1').focus(), 100);
}

function goToStep3(redirectUrl) {
    const si2 = document.getElementById('si2');
    const sl2 = document.getElementById('sl2');
    const si3 = document.getElementById('si3');
    const sd2 = document.getElementById('sd2');
    const sd3 = document.getElementById('sd3');

    si2.classList.remove('active'); si2.classList.add('done');
    sd2.innerHTML = '<i class="bi bi-check-lg"></i>';
    sl2.classList.add('done');
    si3.classList.add('active'); si3.classList.add('done');
    sd3.innerHTML = '<i class="bi bi-check-lg"></i>';

    document.getElementById('step2').classList.remove('active');
    document.getElementById('step3').classList.add('active');

    setTimeout(() => { window.location.href = redirectUrl; }, 1800);
}

function backToStep1() {
    document.getElementById('step2').classList.remove('active');
    document.getElementById('step1').classList.add('active');

    const si1 = document.getElementById('si1');
    const si2 = document.getElementById('si2');
    const sl1 = document.getElementById('sl1');
    const sd1 = document.getElementById('sd1');

    si1.classList.remove('done'); si1.classList.add('active');
    sd1.textContent = '1';
    sl1.classList.remove('done');
    si2.classList.remove('active');

    hideError('otpErrorBox');
    if (timerInterval) clearInterval(timerInterval);
}

let timerInterval = null;

function startTimer() {
    if (timerInterval) clearInterval(timerInterval);

    let seconds   = 600;
    const display = document.getElementById('timerDisplay');
    const resend  = document.getElementById('resendLink');
    const wrap    = document.getElementById('timerWrap');

    if (display) display.textContent = '10:00';
    if (resend)  resend.style.display = 'none';
    if (wrap)    wrap.style.display   = 'block';

    timerInterval = setInterval(() => {
        seconds--;
        if (seconds <= 0) {
            clearInterval(timerInterval);
            if (wrap) {
                wrap.innerHTML =
                    '<span class="resend-link" id="resendLink" style="display:inline;" onclick="resendOtp()">' +
                    '<i class="bi bi-arrow-clockwise"></i> OTP expired — Click to resend</span>';
            }
            return;
        }
        const m = String(Math.floor(seconds / 60)).padStart(2, '0');
        const s = String(seconds % 60).padStart(2, '0');
        if (display) display.textContent = m + ':' + s;

        if (seconds <= 480 && resend) resend.style.display = 'inline';
    }, 1000);
}

function resendOtp() {
    fetch('{{ route("otp.resend") }}', {
        method : 'POST',
        headers: {
            'Accept'      : 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ type: 'register' })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            [1,2,3,4,5,6].forEach(i => {
                const b = document.getElementById('otp' + i);
                b.value = '';
                b.classList.remove('filled');
            });
            document.getElementById('otp1').focus();
            hideError('otpErrorBox');
            startTimer();
        } else {
            showError('otpErrorBox', data.message || 'Failed to resend OTP.');
        }
    })
    .catch(() => showError('otpErrorBox', 'Network error. Please try again.'));
}

function showError(boxId, msg) {
    const el = document.getElementById(boxId);
    if (el) {
        el.innerHTML = '<i class="bi bi-exclamation-circle-fill"></i>&nbsp;' + msg;
        el.style.display = 'flex';
    }
}
function hideError(boxId) {
    const el = document.getElementById(boxId);
    if (el) el.style.display = 'none';
}
function showFieldError(id, msg) {
    const el = document.getElementById(id);
    if (!el) return;
    el.innerHTML = '<i class="bi bi-exclamation-circle"></i> ' + msg;
    el.style.display = 'flex';
}
function clearFieldErrors() {
    document.querySelectorAll('.field-error').forEach(el => {
        el.style.display = 'none'; el.innerHTML = '';
    });
    document.querySelectorAll('.form-control-gp').forEach(el => el.classList.remove('is-invalid'));
    hideError('formErrorBox');
}

document.addEventListener('DOMContentLoaded', function () {

    document.getElementById('registerForm').addEventListener('submit', function (e) {
        e.preventDefault();
        clearFieldErrors();

        if (!document.getElementById('termsCheck').checked) {
            document.getElementById('err-terms').style.display = 'flex';
            return;
        }

        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spin"></span> Sending OTP...';

        fetch('{{ route("register.post") }}', {
            method : 'POST',
            headers: {
                'Accept'      : 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: new FormData(this)
        })
        .then(r => r.json().then(data => ({ status: r.status, data })))
        .then(({ status, data }) => {
            if (data.success) {
                document.getElementById('displayEmail').textContent =
                    document.getElementById('emailInput').value;
                goToStep2();
                startTimer();
            } else {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-arrow-right-circle-fill"></i> Send OTP & Continue';

                if (data.errors) {
                    const fieldMap = {
                        name    : 'err-name',
                        email   : 'err-email',
                        phone   : 'err-phone',
                        password: 'err-password',
                    };
                    let hasFieldErr = false;
                    Object.entries(data.errors).forEach(([field, msgs]) => {
                        const errId = fieldMap[field];
                        if (errId) {
                            showFieldError(errId, msgs[0]);
                            const input = document.querySelector('[name="' + field + '"]');
                            if (input) input.classList.add('is-invalid');
                            hasFieldErr = true;
                        }
                    });
                    if (!hasFieldErr) {
                        const allMsgs = Object.values(data.errors).flat().join(' · ');
                        showError('formErrorBox', allMsgs);
                    }
                } else {
                    showError('formErrorBox', data.message || 'Something went wrong. Please try again.');
                }
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-arrow-right-circle-fill"></i> Send OTP & Continue';
            showError('formErrorBox', 'Network error. Please check your internet connection.');
        });
    });

    document.getElementById('otpForm').addEventListener('submit', function (e) {
        e.preventDefault();
        hideError('otpErrorBox');

        const otp = collectOtp();
        if (otp.length !== 6 || !/^\d{6}$/.test(otp)) {
            showError('otpErrorBox', 'Please enter the complete 6-digit OTP.');
            return;
        }

        document.getElementById('otpValue').value = otp;

        const btn = document.getElementById('verifyBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spin"></span> Verifying...';

        fetch('{{ route("otp.verify.post") }}', {
            method : 'POST',
            headers: {
                'Accept'      : 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ otp: otp, type: 'register' })
        })
        .then(r => r.json().then(data => ({ status: r.status, data })))
        .then(({ status, data }) => {
            if (data.success) {
                if (timerInterval) clearInterval(timerInterval);
                goToStep3(data.redirect || '{{ route("dashboard") }}');
            } else {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Verify & Create Account';
                showError('otpErrorBox', data.message || 'Invalid or expired OTP.');
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Verify & Create Account';
            showError('otpErrorBox', 'Network error. Please try again.');
        });
    });

});
</script>
@endpush