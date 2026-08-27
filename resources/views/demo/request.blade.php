@extends('layouts.app')
@section('title', 'Request a Demo — EcomgstPro')

@push('styles')
<style>
:root {
    --navy: #0a1628; --deep: #0d1f3c; --teal: #00c98d;
    --sky: #00aaff; --muted: #8a9bbf; --border: rgba(255,255,255,.08); --danger: #ff5c7a;
}
.demo-page {
    min-height: 100vh;
    background: linear-gradient(135deg, var(--navy) 0%, var(--deep) 60%, #0a2240 100%);
    display: flex; align-items: center; justify-content: center;
    padding: 60px 16px;
    position: relative; overflow: hidden;
}
.demo-page::before {
    content:''; position:absolute; width:600px; height:600px; border-radius:50%;
    background:radial-gradient(circle,rgba(0,170,255,.06) 0%,transparent 70%);
    top:-150px; right:-150px; pointer-events:none;
}
.demo-page::after {
    content:''; position:absolute; width:400px; height:400px; border-radius:50%;
    background:radial-gradient(circle,rgba(0,201,141,.07) 0%,transparent 70%);
    bottom:-100px; left:-100px; pointer-events:none;
}
.dot-bg { background-image:radial-gradient(rgba(255,255,255,.025) 1px,transparent 1px); background-size:26px 26px; }

.demo-wrap {
    display: grid; grid-template-columns: 1fr 1fr;
    gap: 40px; max-width: 1000px; width: 100%; position: relative; z-index: 2;
}
@media(max-width:768px) { .demo-wrap { grid-template-columns: 1fr; } .demo-left { display: none; } }

/* LEFT INFO PANEL */
.demo-left { display: flex; flex-direction: column; justify-content: center; color: #fff; }
.demo-left .badge-pill {
    display: inline-flex; align-items: center; gap: 8px;
    background: rgba(0,201,141,.12); border: 1px solid rgba(0,201,141,.25);
    color: var(--teal); border-radius: 50px; padding: 6px 16px;
    font-size: 13px; font-weight: 600; margin-bottom: 24px; width: fit-content;
}
.demo-left h1 { font-family:'Syne',sans-serif; font-weight:800; font-size:36px; line-height:1.2; margin-bottom:16px; }
.demo-left h1 span { color: var(--teal); }
.demo-left p { color: var(--muted); font-size:15px; line-height:1.7; margin-bottom:28px; }
.feature-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 14px; }
.feature-list li { display: flex; align-items: flex-start; gap: 12px; font-size:14px; color: #c0cfe8; }
.feature-list li i { color: var(--teal); font-size: 18px; margin-top: 1px; flex-shrink: 0; }

/* RIGHT FORM CARD */
.demo-card {
    background: rgba(13,31,60,.85); backdrop-filter: blur(20px);
    border: 1px solid var(--border); border-radius: 24px;
    padding: 40px 36px; box-shadow: 0 30px 60px rgba(0,0,0,.4);
}
.demo-card h2 { font-family:'Syne',sans-serif; font-weight:800; font-size:22px; color:#fff; margin-bottom:6px; }
.demo-card .sub { font-size:13.5px; color:var(--muted); margin-bottom:28px; }

.form-group { margin-bottom: 18px; }
.form-label { display:block; font-size:13px; font-weight:600; color:#c0cfe8; margin-bottom:7px; }
.form-label .req { color:var(--teal); }
.input-wrap { position:relative; }
.input-wrap > i { position:absolute; left:13px; top:50%; transform:translateY(-50%); color:var(--muted); font-size:15px; pointer-events:none; }
.fc {
    width:100%; padding:11px 14px 11px 40px;
    background:rgba(255,255,255,.04); border:1.5px solid rgba(255,255,255,.1);
    border-radius:11px; color:#fff; font-family:'DM Sans',sans-serif; font-size:14px;
    transition:all .2s; outline:none; box-sizing:border-box;
}
.fc::placeholder { color:rgba(255,255,255,.3); }
.fc:focus { border-color:var(--teal); background:rgba(0,201,141,.04); box-shadow:0 0 0 3px rgba(0,201,141,.1); }
.fc.is-invalid { border-color:var(--danger) !important; }
textarea.fc { resize: vertical; min-height: 80px; padding-top: 12px; }
.fc-date { color: rgba(255,255,255,.7); }
.fc-date::-webkit-calendar-picker-indicator { filter: invert(1) opacity(0.5); cursor:pointer; }

.date-time-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
@media(max-width:480px) { .date-time-row { grid-template-columns: 1fr; } }

select.fc { appearance: none; -webkit-appearance: none; }

.btn-submit {
    width:100%; padding:13px; font-family:'DM Sans',sans-serif; font-weight:700;
    font-size:15px; color:var(--navy);
    background:linear-gradient(135deg,var(--teal) 0%,#00e0a8 100%);
    border:none; border-radius:12px; cursor:pointer; transition:all .25s;
    display:flex; align-items:center; justify-content:center; gap:8px;
    box-shadow:0 4px 20px rgba(0,201,141,.3); margin-top:6px;
}
.btn-submit:hover:not(:disabled) { transform:translateY(-2px); box-shadow:0 8px 30px rgba(0,201,141,.45); }
.btn-submit:disabled { opacity:.6; cursor:not-allowed; }

.spin { width:16px;height:16px;border:2px solid rgba(10,22,40,.3);border-top-color:var(--navy);border-radius:50%;animation:spin .7s linear infinite;display:inline-block; }
@keyframes spin{to{transform:rotate(360deg);}}

.field-error { font-size:12px; color:var(--danger); margin-top:5px; display:flex; align-items:center; gap:4px; }
.alert-box { border-radius:12px; padding:12px 16px; margin-bottom:16px; font-size:13.5px; display:flex; align-items:flex-start; gap:10px; }
.alert-danger  { background:rgba(255,92,122,.1); border:1px solid rgba(255,92,122,.25); color:#ffb3c1; }
.alert-success { background:rgba(0,201,141,.1); border:1px solid rgba(0,201,141,.25); color:#a0f5dc; }
</style>
@endpush

@section('content')
<div class="demo-page dot-bg">
    <div class="demo-wrap">

        {{-- LEFT INFO --}}
        <div class="demo-left">
            <div class="badge-pill"><i class="bi bi-play-circle-fill"></i> Free Demo</div>
            <h1>See <span>EcomgstPro</span> in Action</h1>
            <p>Book a personalised demo with our GST experts and discover how EcomgstPro can automate your compliance workflow in minutes.</p>
            <ul class="feature-list">
                <li><i class="bi bi-check-circle-fill"></i> Live walkthrough of all features</li>
                <li><i class="bi bi-check-circle-fill"></i> Personalised to your business type</li>
                <li><i class="bi bi-check-circle-fill"></i> Q&A with our GST experts</li>
                <li><i class="bi bi-check-circle-fill"></i> Setup guidance included</li>
                <li><i class="bi bi-check-circle-fill"></i> No commitment required</li>
            </ul>
        </div>

        {{-- RIGHT FORM --}}
        <div class="demo-card">
            <h2>Book Your Demo</h2>
            <p class="sub">Fill in the details — we'll confirm your slot shortly</p>

            @if(session('success'))
                <div class="alert-box alert-success">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <div id="globalError" class="alert-box alert-danger" style="display:none;"></div>
            <div id="globalSuccess" class="alert-box alert-success" style="display:none;"></div>

            <form id="demoForm" novalidate>
                @csrf
                <div class="form-group">
                    <label class="form-label">Full Name <span class="req">*</span></label>
                    <div class="input-wrap">
                        <i class="bi bi-person-fill"></i>
                        <input type="text" name="name" id="f_name" class="fc" placeholder="e.g. Rahul Sharma" required>
                    </div>
                    <div class="field-error" id="err-name" style="display:none;"></div>
                </div>

                <div class="form-group">
                    <label class="form-label">Email Address <span class="req">*</span></label>
                    <div class="input-wrap">
                        <i class="bi bi-envelope-fill"></i>
                        <input type="email" name="email" id="f_email" class="fc" placeholder="you@company.com" required>
                    </div>
                    <div class="field-error" id="err-email" style="display:none;"></div>
                </div>

                <div class="form-group">
                    <label class="form-label">Phone Number <span class="req">*</span></label>
                    <div class="input-wrap">
                        <i class="bi bi-phone-fill"></i>
                        <input type="tel" name="phone" id="f_phone" class="fc" placeholder="+91 98765 43210" maxlength="15" required>
                    </div>
                    <div class="field-error" id="err-phone" style="display:none;"></div>
                </div>

                <div class="form-group">
                    <label class="form-label">Company / Business Name</label>
                    <div class="input-wrap">
                        <i class="bi bi-building"></i>
                        <input type="text" name="company_name" class="fc" placeholder="Your Business Pvt Ltd">
                    </div>
                </div>

                <div class="date-time-row">
                    <div class="form-group">
                        <label class="form-label">Preferred Date <span class="req">*</span></label>
                        <div class="input-wrap">
                            <i class="bi bi-calendar3"></i>
                            <input type="date" name="requested_date" id="f_date" class="fc fc-date" required>
                        </div>
                        <div class="field-error" id="err-requested_date" style="display:none;"></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Preferred Time <span class="req">*</span></label>
                        <div class="input-wrap">
                            <i class="bi bi-clock-fill"></i>
                            <select name="requested_time" id="f_time" class="fc" required>
                                <option value="">Select time</option>
                                <option>09:00 AM – 10:00 AM</option>
                                <option>10:00 AM – 11:00 AM</option>
                                <option>11:00 AM – 12:00 PM</option>
                                <option>12:00 PM – 01:00 PM</option>
                                <option>02:00 PM – 03:00 PM</option>
                                <option>03:00 PM – 04:00 PM</option>
                                <option>04:00 PM – 05:00 PM</option>
                                <option>05:00 PM – 06:00 PM</option>
                            </select>
                        </div>
                        <div class="field-error" id="err-requested_time" style="display:none;"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Message (Optional)</label>
                    <div class="input-wrap">
                        <i class="bi bi-chat-left-text-fill" style="top:16px;transform:none;"></i>
                        <textarea name="message" class="fc" placeholder="Any specific features you'd like to see?"></textarea>
                    </div>
                </div>

                <button type="submit" class="btn-submit" id="submitBtn">
                    <i class="bi bi-calendar-check-fill"></i> Book My Demo
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Set min date to today
document.addEventListener('DOMContentLoaded', function () {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('f_date').setAttribute('min', today);
});

function showErr(id, msg) {
    const el = document.getElementById('err-' + id);
    if (el) { el.innerHTML = '<i class="bi bi-exclamation-circle"></i> ' + msg; el.style.display = 'flex'; }
    const inp = document.querySelector('[name="' + id + '"]');
    if (inp) inp.classList.add('is-invalid');
}
function clearErrors() {
    document.querySelectorAll('.field-error').forEach(e => { e.style.display='none'; e.innerHTML=''; });
    document.querySelectorAll('.fc').forEach(e => e.classList.remove('is-invalid'));
    document.getElementById('globalError').style.display = 'none';
    document.getElementById('globalSuccess').style.display = 'none';
}

document.getElementById('demoForm').addEventListener('submit', function(e) {
    e.preventDefault();
    clearErrors();

    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spin"></span> Submitting...';

    fetch('{{ route("demo.submit") }}', {
        method: 'POST',
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: new FormData(this)
    })
    .then(r => r.json().then(data => ({ status: r.status, data })))
    .then(({ status, data }) => {
        if (data.success) {
            const box = document.getElementById('globalSuccess');
            box.innerHTML = '<i class="bi bi-check-circle-fill"></i> ' + data.message;
            box.style.display = 'flex';
            document.getElementById('demoForm').reset();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } else {
            if (data.errors) {
                Object.entries(data.errors).forEach(([field, msgs]) => showErr(field, msgs[0]));
            } else {
                const box = document.getElementById('globalError');
                box.innerHTML = '<i class="bi bi-exclamation-circle-fill"></i> ' + (data.message || 'Something went wrong.');
                box.style.display = 'flex';
            }
        }
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-calendar-check-fill"></i> Book My Demo';
    })
    .catch(() => {
        const box = document.getElementById('globalError');
        box.innerHTML = '<i class="bi bi-exclamation-circle-fill"></i> Network error. Please try again.';
        box.style.display = 'flex';
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-calendar-check-fill"></i> Book My Demo';
    });
});
</script>
@endpush
