{{-- resources/views/auth/verify-otp.blade.php --}}
@extends('layouts.app')

@section('title', 'Verify OTP — EcomgstPro')

@section('content')
<style>
    .otp-page {
        min-height: 80vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 16px;
        background: radial-gradient(circle at top, #0d1b2a 0%, #050b14 100%);
    }

    .otp-card {
        width: 100%;
        max-width: 380px;
        background: rgba(18, 32, 47, 0.85);
        border: 1px solid rgba(0, 200, 180, 0.25);
        border-radius: 18px;
        padding: 32px 28px;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.45), 0 0 0 1px rgba(0,200,180,0.05);
        text-align: center;
        backdrop-filter: blur(8px);
    }

    .otp-logo {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin-bottom: 18px;
    }

    .otp-logo span {
        font-weight: 700;
        font-size: 18px;
        color: #e8f6f4;
        letter-spacing: 0.3px;
    }

    .otp-shield {
        width: 52px;
        height: 52px;
        margin: 0 auto 14px;
        border-radius: 50%;
        background: linear-gradient(135deg, #00c8b4, #0a8f80);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        box-shadow: 0 0 0 6px rgba(0, 200, 180, 0.08);
    }

    .otp-title {
        font-size: 21px;
        font-weight: 700;
        color: #f1f7f6;
        margin-bottom: 6px;
    }

    .otp-subtitle {
        font-size: 13.5px;
        color: #9fb3b0;
        line-height: 1.5;
        margin-bottom: 22px;
    }

    .otp-subtitle strong {
        color: #2dd4c4;
    }

    .alert {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        padding: 10px 14px;
        border-radius: 10px;
        margin-bottom: 16px;
        text-align: left;
    }

    .alert-success {
        background: rgba(45, 212, 196, 0.12);
        color: #2dd4c4;
        border: 1px solid rgba(45, 212, 196, 0.3);
    }

    .alert-error {
        background: rgba(239, 68, 68, 0.12);
        color: #f87171;
        border: 1px solid rgba(239, 68, 68, 0.3);
    }

    .alert svg { flex-shrink: 0; }

    .otp-inputs {
        display: flex;
        justify-content: center;
        gap: 8px;
        margin-bottom: 18px;
    }

    .otp-box {
        width: 44px;
        height: 50px;
        text-align: center;
        font-size: 20px;
        font-weight: 700;
        color: #f1f7f6;
        background: rgba(255, 255, 255, 0.04);
        border: 1.5px solid rgba(0, 200, 180, 0.25);
        border-radius: 10px;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .otp-box:focus {
        border-color: #2dd4c4;
        box-shadow: 0 0 0 3px rgba(45, 212, 196, 0.18);
    }

    .otp-timer {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        font-size: 12.5px;
        color: #9fb3b0;
        margin-bottom: 20px;
    }

    .timer-count {
        font-weight: 700;
        color: #2dd4c4;
    }

    .btn-primary {
        width: 100%;
        padding: 12px 0;
        background: linear-gradient(135deg, #00c8b4, #079e8c);
        color: #06151a;
        font-weight: 700;
        font-size: 14.5px;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        transition: transform 0.15s, box-shadow 0.15s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 8px 20px rgba(0,200,180,0.25); }
    .btn-primary:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

    .spin { animation: spin 0.8s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg); } }

    .resend-section {
        margin-top: 18px;
        font-size: 13px;
        color: #9fb3b0;
    }

    .btn-resend {
        background: none;
        border: none;
        color: #2dd4c4;
        font-weight: 600;
        cursor: pointer;
        margin-left: 4px;
    }

    .btn-resend:disabled { color: #4f6663; cursor: not-allowed; }

    .back-link { margin-top: 20px; }
    .back-link a {
        font-size: 13px;
        color: #6fa8a0;
        text-decoration: none;
    }
    .back-link a:hover { color: #2dd4c4; }
</style>

<div class="otp-page">
    <div class="otp-card">

        <div class="otp-logo">
            <svg width="32" height="32" viewBox="0 0 40 40" fill="none">
                <rect width="40" height="40" rx="10" fill="#00c8b4"/>
                <path d="M20 8C13.4 8 8 13.4 8 20s5.4 12 12 12c2.2 0 4.3-.6 6.1-1.7l-2.2-2.2C22.7 28.7 21.4 29 20 29c-5 0-9-4-9-9s4-9 9-9c2.2 0 4.2.8 5.8 2.1L23.6 15c-1-1-2.3-1.6-3.6-1.6-3.3 0-6 2.7-6 6s2.7 6 6 6c2.5 0 4.6-1.5 5.5-3.7H20v-3h9c.1.6.1 1.1.1 1.7C29.1 26.2 25 30 20 30" fill="#06151a"/>
            </svg>
            <span>EcomgstPro</span>
        </div>

        <div class="otp-shield">🔐</div>
        <h2 class="otp-title">Verify OTP</h2>
        <p class="otp-subtitle">
            A 6-digit code has been sent to your email.<br>
            It will expire in <strong>10 minutes</strong>.
        </p>

        @if(session('success'))
            <div class="alert alert-success">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-error">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('otp.verify.post') }}" method="POST" id="otpForm">
            @csrf
            <input type="hidden" name="type" value="{{ $type }}">

            <div class="otp-inputs">
                @for($i = 1; $i <= 6; $i++)
                    <input type="text" class="otp-box" maxlength="1" inputmode="numeric"
                        id="otp{{ $i }}" data-index="{{ $i }}" autocomplete="off">
                @endfor
            </div>
            <input type="hidden" name="otp" id="otpHidden">

            <div class="otp-timer" id="timerWrap">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                Expires in: <span id="timer" class="timer-count">10:00</span>
            </div>

            <button type="submit" class="btn-primary" id="verifyBtn">
                <span class="btn-text">✓ Verify OTP</span>
                <span class="btn-loader" style="display:none">
                    <svg class="spin" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                    Verifying...
                </span>
            </button>
        </form>

        <div class="resend-section">
            <span id="resendText">Didn't receive the OTP?</span>
            <button type="button" class="btn-resend" id="resendBtn" onclick="resendOtp()" disabled>
                Resend
            </button>
            <span id="resendTimer"> (<span id="resendCount">30</span>s)</span>
        </div>

        <div class="back-link">
            <a href="{{ $type === 'register' ? route('register') : route('login') }}">
                ← Go back
            </a>
        </div>

    </div>
</div>

@push('scripts')
<script>
const boxes = document.querySelectorAll('.otp-box');

boxes.forEach((box, idx) => {
    box.addEventListener('input', function(e) {
        const val = e.target.value.replace(/\D/g, '');
        e.target.value = val;
        if (val && idx < boxes.length - 1) {
            boxes[idx + 1].focus();
        }
        updateHidden();
    });

    box.addEventListener('keydown', function(e) {
        if (e.key === 'Backspace' && !e.target.value && idx > 0) {
            boxes[idx - 1].focus();
        }
    });

    box.addEventListener('paste', function(e) {
        e.preventDefault();
        const pasted = e.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6);
        pasted.split('').forEach((ch, i) => {
            if (boxes[i]) boxes[i].value = ch;
        });
        if (boxes[pasted.length - 1]) boxes[pasted.length - 1].focus();
        updateHidden();
    });
});

function updateHidden() {
    document.getElementById('otpHidden').value =
        [...boxes].map(b => b.value).join('');
}

document.getElementById('otpForm').addEventListener('submit', function(e) {
    const otp = document.getElementById('otpHidden').value;
    if (otp.length !== 6) {
        e.preventDefault();
        alert('Please enter the complete 6-digit OTP.');
        return;
    }
    document.querySelector('.btn-text').style.display = 'none';
    document.querySelector('.btn-loader').style.display = 'flex';
    document.getElementById('verifyBtn').disabled = true;
});

let seconds = 600;
const timerEl = document.getElementById('timer');

const countdown = setInterval(() => {
    seconds--;
    const m = Math.floor(seconds / 60).toString().padStart(2, '0');
    const s = (seconds % 60).toString().padStart(2, '0');
    timerEl.textContent = `${m}:${s}`;

    if (seconds <= 60) timerEl.style.color = '#f87171';
    if (seconds <= 0) {
        clearInterval(countdown);
        timerEl.textContent = 'Expired!';
        document.getElementById('verifyBtn').disabled = true;
    }
}, 1000);

let resendSec = 30;
const resendCount = document.getElementById('resendCount');
const resendBtn   = document.getElementById('resendBtn');
const resendTimer = document.getElementById('resendTimer');

const resendCountdown = setInterval(() => {
    resendSec--;
    resendCount.textContent = resendSec;
    if (resendSec <= 0) {
        clearInterval(resendCountdown);
        resendBtn.disabled = false;
        resendTimer.style.display = 'none';
    }
}, 1000);

function resendOtp() {
    resendBtn.disabled = true;
    resendBtn.textContent = 'Sending...';

    fetch('{{ route("otp.resend") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ type: '{{ $type }}' })
    })
    .then(r => r.json())
    .then(data => {
        resendBtn.textContent = 'Resend';
        if (data.success) {
            showAlert('success', data.message);
        } else {
            showAlert('error', data.message);
            resendBtn.disabled = false;
        }
    })
    .catch(() => {
        resendBtn.textContent = 'Resend';
        resendBtn.disabled = false;
        showAlert('error', 'Something went wrong. Please try again.');
    });
}

function showAlert(type, msg) {
    const existing = document.querySelector('.alert');
    if (existing) existing.remove();
    const div = document.createElement('div');
    div.className = `alert alert-${type}`;
    div.textContent = msg;
    document.getElementById('otpForm').insertAdjacentElement('beforebegin', div);
}
</script>
@endpush
@endsection
