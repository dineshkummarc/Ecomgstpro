@extends('layouts.app')
@section('title', 'Pricing — EcomgstPro')

@push('styles')
<style>
:root{
    --navy:#0a1628; --deep:#0d1f3c; --teal:#00c98d; --sky:#00aaff;
    --muted:#8a9bbf; --border:rgba(255,255,255,.08);
}
.pricing-page{
    background:linear-gradient(135deg,var(--navy) 0%,var(--deep) 60%,#0a2240 100%);
    min-height:100vh; padding:64px 16px;
}
.pp-header{ text-align:center; max-width:640px; margin:0 auto 20px; }
.pp-badge{
    display:inline-flex; align-items:center; gap:8px;
    background:rgba(0,201,141,.12); border:1px solid rgba(0,201,141,.25);
    border-radius:30px; padding:6px 16px; font-size:11px; font-weight:700;
    letter-spacing:1.5px; text-transform:uppercase; color:var(--teal); margin-bottom:16px;
}
.pp-title{ font-family:'Syne',sans-serif; font-weight:800; font-size:clamp(26px,4vw,38px); color:#fff; margin-bottom:10px; }
.pp-title em{ color:var(--teal); font-style:normal; }
.pp-sub{ font-size:14.5px; color:var(--muted); }

.trial-status{
    max-width:640px; margin:0 auto 36px; text-align:center;
    background:rgba(255,255,255,.04); border:1px solid var(--border);
    border-radius:14px; padding:14px 20px; font-size:13.5px; color:#e8f0fe;
}
.trial-status strong{ color:var(--teal); }

.plans-grid{
    max-width:1000px; margin:0 auto; display:grid;
    grid-template-columns:repeat(3,1fr); gap:22px;
}
.plan-card{
    background:rgba(255,255,255,.04); border:1.5px solid var(--border);
    border-radius:20px; padding:32px 26px; position:relative; transition:all .25s;
}
.plan-card.popular{ border-color:var(--teal); box-shadow:0 10px 40px rgba(0,201,141,.15); }
.plan-badge{
    position:absolute; top:-13px; left:50%; transform:translateX(-50%);
    background:linear-gradient(135deg,var(--teal),var(--sky)); color:var(--navy);
    font-size:11px; font-weight:800; letter-spacing:1px; text-transform:uppercase;
    padding:5px 16px; border-radius:20px; white-space:nowrap;
}
.plan-name{ font-family:'Syne',sans-serif; font-weight:700; font-size:18px; color:#fff; margin-bottom:6px; }
.plan-price{ font-family:'Syne',sans-serif; font-weight:800; font-size:38px; color:#fff; line-height:1; }
.plan-price sup{ font-size:16px; }
.plan-per{ font-size:12.5px; color:var(--muted); margin-bottom:6px; }
.plan-total{ font-size:12px; color:var(--muted); margin-bottom:18px; }
.plan-divider{ height:1px; background:var(--border); margin:18px 0; }
.plan-feats{ list-style:none; padding:0; margin:0 0 24px; display:flex; flex-direction:column; gap:10px; }
.plan-feats li{ display:flex; gap:8px; font-size:12.5px; color:#e8f0fe; align-items:flex-start; line-height:1.4; }
.plan-feats li i{ color:var(--teal); margin-top:2px; flex-shrink:0; }
.btn-pay{
    width:100%; padding:13px; border:none; border-radius:12px; cursor:pointer;
    font-family:'DM Sans',sans-serif; font-weight:700; font-size:14.5px;
    background:linear-gradient(135deg,var(--teal),#00e0a8); color:var(--navy);
    display:flex; align-items:center; justify-content:center; gap:8px; transition:all .2s;
}
.btn-pay:hover:not(:disabled){ transform:translateY(-2px); box-shadow:0 8px 26px rgba(0,201,141,.4); }
.btn-pay:disabled{ opacity:.6; cursor:not-allowed; }

@media(max-width:900px){ .plans-grid{ grid-template-columns:1fr; max-width:420px; } }

/* ===== BILLING DETAILS MODAL ===== */
.bd-overlay{
    display:none; position:fixed; inset:0; z-index:2000;
    background:rgba(4,10,22,.72);
    align-items:center; justify-content:center; padding:16px;
}
.bd-overlay.show{ display:flex; }
.bd-box{
    width:100%; max-width:520px; max-height:90vh; overflow-y:auto;
    background:linear-gradient(160deg,#0d1f3c,#0a1628);
    border:1.5px solid var(--border); border-radius:20px;
    padding:30px 28px; position:relative;
    box-shadow:0 30px 80px rgba(0,0,0,.5);
}
.bd-close{
    position:absolute; top:16px; right:16px; background:none; border:none;
    color:var(--muted); font-size:20px; cursor:pointer;
}
.bd-close:hover{ color:#fff; }
.bd-title{
    font-family:'Syne',sans-serif; font-weight:800; font-size:19px; color:#fff;
    display:flex; align-items:center; gap:10px; margin-bottom:4px;
}
.bd-subtitle{ font-size:12.5px; color:var(--muted); margin-bottom:22px; }
.bd-plan-chip{
    display:inline-flex; align-items:center; gap:8px;
    background:rgba(0,201,141,.1); border:1px solid rgba(0,201,141,.25);
    border-radius:10px; padding:8px 14px; font-size:12.5px; color:#e8f0fe;
    margin-bottom:20px;
}
.bd-plan-chip strong{ color:var(--teal); }
.bd-row{ display:grid; grid-template-columns:1fr 1fr; gap:14px; }
.bd-group{ margin-bottom:14px; }
.bd-label{
    display:block; font-size:11px; font-weight:700; text-transform:uppercase;
    letter-spacing:.5px; color:var(--muted); margin-bottom:6px;
}
.bd-input{
    width:100%; padding:11px 13px; border-radius:10px;
    background:rgba(255,255,255,.05); border:1.5px solid var(--border);
    color:#fff; font-size:13.5px; font-family:'DM Sans',sans-serif;
    outline:none; transition:border-color .2s; box-sizing:border-box;
}
.bd-input::placeholder{ color:#5b6d8f; }
.bd-input:focus{ border-color:var(--teal); box-shadow:0 0 0 3px rgba(0,201,141,.12); }
.bd-input.is-invalid{ border-color:#ff5c7a; box-shadow:0 0 0 3px rgba(255,92,122,.12); }
.bd-error{ font-size:11px; color:#ff8fa3; margin-top:5px; display:none; }
.bd-error.show{ display:block; }
.bd-note{ font-size:11px; color:var(--muted); margin:2px 0 18px; }
.bd-actions{ display:flex; gap:12px; margin-top:6px; }
.bd-actions .btn-pay{ flex:1; }
.bd-cancel{
    flex:1; padding:13px; border-radius:12px; cursor:pointer;
    font-family:'DM Sans',sans-serif; font-weight:700; font-size:14.5px;
    background:transparent; border:1.5px solid var(--border); color:#e8f0fe;
}
.bd-cancel:hover{ border-color:var(--muted); }

@media(max-width:520px){ .bd-row{ grid-template-columns:1fr; } }
</style>
@endpush

@section('content')
<div class="pricing-page">

    <div class="pp-header">
        <div class="pp-badge"><i class="bi bi-lightning-charge-fill"></i> Simple, Transparent Pricing</div>
        <h1 class="pp-title">Pick a plan and keep <em>filing without interruption</em></h1>
        <p class="pp-sub">Cancel anytime. No hidden charges. Instant activation after payment.</p>
    </div>

    @if($user)
        <div class="trial-status">
            @if($user->isOnTrial())
                You're on your free trial — <strong>{{ $user->trialDaysLeft() }} day(s) left</strong> · up to 5 GST numbers.
            @elseif($user->hasActiveSubscription())
                You're on the <strong>{{ ucfirst($user->plan_type) }}</strong> plan — active until
                <strong>{{ $user->subscription_ends_at->format('d M Y') }}</strong>.
            @else
                Your trial has ended. Choose a plan below to reactivate your account.
            @endif
        </div>
    @endif

    @if(session('error'))
        <div class="trial-status" style="border-color:#ff5c7a;color:#ffb3c1;">{{ session('error') }}</div>
    @endif

    <div class="plans-grid" id="plansGrid">
        @foreach($plans as $plan)
        <div class="plan-card {{ $plan->slug === 'yearly' ? 'popular' : '' }}">
            @if($plan->slug === 'yearly')
                <span class="plan-badge">Best Value</span>
            @endif
            <div class="plan-name">{{ $plan->name }}</div>
            <div class="plan-price"><sup>₹</sup>{{ number_format($plan->price_per_month, 0) }}<span style="font-size:14px;color:var(--muted);">/mo</span></div>
            <div class="plan-per">Billed {{ $plan->duration_months == 1 ? 'monthly' : ($plan->duration_months == 3 ? 'quarterly' : 'yearly') }}</div>
            <div class="plan-total">₹{{ number_format($plan->total_price, 0) }} total @if($plan->discountLabel()) · <span style="color:var(--teal)">{{ $plan->discountLabel() }}</span> @endif</div>
            <div class="plan-divider"></div>
            <ul class="plan-feats">
                <li><i class="bi bi-check-circle-fill"></i> Unlimited GST filings</li>
                <li><i class="bi bi-check-circle-fill"></i> Unlimited &amp; multiple GST numbers</li>
                <li><i class="bi bi-check-circle-fill"></i> Amazon B2B/B2C sales &amp; stock reports</li>
                <li><i class="bi bi-check-circle-fill"></i> Flipkart sales &amp; GST reports</li>
                <li><i class="bi bi-check-circle-fill"></i> Meesho sales &amp; return reports</li>
                <li><i class="bi bi-check-circle-fill"></i> Myntra &amp; JioMart platform support</li>
                 <li><i class="bi bi-check-circle-fill"></i> Priority support</li>
            </ul>
            <button class="btn-pay"
                onclick="openBillingModal('{{ $plan->slug }}', '{{ addslashes($plan->name) }}', {{ (int) $plan->total_price }})">
                <i class="bi bi-credit-card-fill"></i> Pay ₹{{ number_format($plan->total_price, 0) }}
            </button>
        </div>
        @endforeach
    </div>

</div>

{{-- ===== BILLING DETAILS MODAL ===== --}}
<div class="bd-overlay" id="billingModal">
    <div class="bd-box">
        <button type="button" class="bd-close" onclick="closeBillingModal()"><i class="bi bi-x-lg"></i></button>
        <div class="bd-title"><i class="bi bi-receipt" style="color:var(--teal)"></i> Billing Details</div>
        <p class="bd-subtitle">This information will be used for your invoice and payment receipt.</p>

        <div class="bd-plan-chip">
            <i class="bi bi-bookmark-check-fill" style="color:var(--teal)"></i>
            <span id="bdPlanLabel">Plan</span>
        </div>

        <form id="billingForm" onsubmit="return false;">
            <div class="bd-group">
                <label class="bd-label">Full Name *</label>
                <input type="text" id="bdName" class="bd-input" placeholder="e.g. Ravi Kumar"
                       value="{{ $user->name ?? '' }}">
                <div class="bd-error" id="err-bdName">Please enter your full name.</div>
            </div>

            <div class="bd-row">
                <div class="bd-group">
                    <label class="bd-label">Email *</label>
                    <input type="email" id="bdEmail" class="bd-input" placeholder="you@example.com"
                           value="{{ $user->email ?? '' }}">
                    <div class="bd-error" id="err-bdEmail">Enter a valid email address.</div>
                </div>
                <div class="bd-group">
                    <label class="bd-label">Phone *</label>
                    <input type="tel" id="bdPhone" class="bd-input" placeholder="9876543210" maxlength="10">
                    <div class="bd-error" id="err-bdPhone">Enter a valid 10-digit phone number.</div>
                </div>
            </div>

            <div class="bd-group">
                <label class="bd-label">Billing Address *</label>
                <input type="text" id="bdAddress" class="bd-input" placeholder="Building, street, area">
                <div class="bd-error" id="err-bdAddress">Please enter your billing address.</div>
            </div>

            <div class="bd-row">
                <div class="bd-group">
                    <label class="bd-label">City *</label>
                    <input type="text" id="bdCity" class="bd-input" placeholder="e.g. Delhi">
                    <div class="bd-error" id="err-bdCity">Please enter your city.</div>
                </div>
                <div class="bd-group">
                    <label class="bd-label">State *</label>
                    <input type="text" id="bdState" class="bd-input" placeholder="e.g. Delhi">
                    <div class="bd-error" id="err-bdState">Please enter your state.</div>
                </div>
            </div>

            <div class="bd-row">
                <div class="bd-group">
                    <label class="bd-label">Pincode *</label>
                    <input type="text" id="bdPincode" class="bd-input" placeholder="110034" maxlength="6">
                    <div class="bd-error" id="err-bdPincode">Enter a valid 6-digit pincode.</div>
                </div>
                <div class="bd-group">
                    <label class="bd-label">GSTIN (Optional)</label>
                    <input type="text" id="bdGstin" class="bd-input" placeholder="For GST invoice" maxlength="15" style="text-transform:uppercase">
                </div>
            </div>

            <p class="bd-note">* Required. This address will appear on your invoice, not your account login details.</p>

            <div class="bd-actions">
                <button type="button" class="bd-cancel" onclick="closeBillingModal()">Cancel</button>
                <button type="button" class="btn-pay" id="bdContinueBtn" onclick="submitBillingAndPay()">
                    <i class="bi bi-credit-card-fill"></i> Continue to Pay
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
let selectedPlanSlug = null;
let selectedPlanName = null;
let selectedPlanAmount = null;
let activePayBtn = null;

function openBillingModal(planSlug, planName, totalPrice) {
    selectedPlanSlug = planSlug;
    selectedPlanName = planName;
    selectedPlanAmount = totalPrice;

    document.getElementById('bdPlanLabel').innerHTML =
        planName + ' Plan — <strong>₹' + totalPrice.toLocaleString('en-IN') + '</strong>';

    document.querySelectorAll('.bd-input').forEach(el => el.classList.remove('is-invalid'));
    document.querySelectorAll('.bd-error').forEach(el => el.classList.remove('show'));

    document.getElementById('billingModal').classList.add('show');
}

function closeBillingModal() {
    document.getElementById('billingModal').classList.remove('show');
    if (activePayBtn) {
        activePayBtn.disabled = false;
    }
}

document.getElementById('billingModal').addEventListener('click', function (e) {
    if (e.target === this) closeBillingModal();
});

function validateBillingForm() {
    let valid = true;
    const fields = [
        { id: 'bdName', test: v => v.trim().length >= 2 },
        { id: 'bdEmail', test: v => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v.trim()) },
        { id: 'bdPhone', test: v => /^[6-9]\d{9}$/.test(v.trim()) },
        { id: 'bdAddress', test: v => v.trim().length >= 5 },
        { id: 'bdCity', test: v => v.trim().length >= 2 },
        { id: 'bdState', test: v => v.trim().length >= 2 },
        { id: 'bdPincode', test: v => /^\d{6}$/.test(v.trim()) },
    ];

    fields.forEach(f => {
        const el = document.getElementById(f.id);
        const errEl = document.getElementById('err-' + f.id);
        const ok = f.test(el.value);
        el.classList.toggle('is-invalid', !ok);
        if (errEl) errEl.classList.toggle('show', !ok);
        if (!ok) valid = false;
    });

    return valid;
}

function submitBillingAndPay() {
    if (!validateBillingForm()) return;

    const billing = {
        name: document.getElementById('bdName').value.trim(),
        email: document.getElementById('bdEmail').value.trim(),
        phone: document.getElementById('bdPhone').value.trim(),
        address: document.getElementById('bdAddress').value.trim(),
        city: document.getElementById('bdCity').value.trim(),
        state: document.getElementById('bdState').value.trim(),
        pincode: document.getElementById('bdPincode').value.trim(),
        gstin: document.getElementById('bdGstin').value.trim().toUpperCase(),
    };

    const continueBtn = document.getElementById('bdContinueBtn');
    continueBtn.disabled = true;
    continueBtn.innerHTML = 'Please wait...';

    fetch('{{ route("subscription.createOrder") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            plan_slug: selectedPlanSlug,
            billing_name: billing.name,
            billing_email: billing.email,
            billing_phone: billing.phone,
            billing_address: billing.address,
            billing_city: billing.city,
            billing_state: billing.state,
            billing_pincode: billing.pincode,
            billing_gstin: billing.gstin
        })
    })
    .then(r => r.json())
    .then(data => {
        continueBtn.disabled = false;
        continueBtn.innerHTML = '<i class="bi bi-credit-card-fill"></i> Continue to Pay';

        if (!data.success) {
            alert('Could not start payment. Please try again.');
            return;
        }

        closeBillingModal();

        const options = {
            key: data.key,
            amount: data.amount,
            currency: data.currency,
            name: 'EcomgstPro',
            description: data.plan_name + ' Plan',
            order_id: data.order_id,
            prefill: {
                name: billing.name,
                email: billing.email,
                contact: billing.phone
            },
            notes: {
                billing_address: billing.address + ', ' + billing.city + ', ' + billing.state + ' - ' + billing.pincode,
                gstin: billing.gstin
            },
            theme: { color: '#00c98d' },
            handler: function (response) {
                fetch('{{ route("subscription.verifyPayment") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        razorpay_payment_id: response.razorpay_payment_id,
                        razorpay_order_id: response.razorpay_order_id,
                        razorpay_signature: response.razorpay_signature,
                        payment_id: data.payment_id
                    })
                })
                .then(r => r.json())
                .then(result => {
                    if (result.success) {
                        alert(result.message);
                        window.location.href = result.redirect;
                    } else {
                        alert(result.message || 'Payment verification failed.');
                    }
                });
            },
            modal: {
                ondismiss: function () {}
            }
        };

        const rzp = new Razorpay(options);
        rzp.open();
    })
    .catch(() => {
        continueBtn.disabled = false;
        continueBtn.innerHTML = '<i class="bi bi-credit-card-fill"></i> Continue to Pay';
        alert('Network error. Please try again.');
    });
}
</script>
@endsection
