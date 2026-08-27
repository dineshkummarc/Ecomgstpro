{{-- resources/views/admin/login.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Login — EcomgstPro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--navy:#0a1628;--deep:#0d1f3c;--teal:#00c98d;--muted:#8a9bbf;--danger:#ff5c7a;}
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'DM Sans',sans-serif;background:linear-gradient(135deg,#060e1c 0%,#0a1628 50%,#060e1c 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px 16px;position:relative;overflow:hidden;}
body::before{content:'';position:fixed;inset:0;background-image:linear-gradient(rgba(0,201,141,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(0,201,141,.04) 1px,transparent 1px);background-size:50px 50px;pointer-events:none;}
.orb{position:fixed;border-radius:50%;pointer-events:none;filter:blur(80px);}
.orb1{width:400px;height:400px;background:rgba(0,170,255,.06);top:-100px;right:-80px;}
.orb2{width:300px;height:300px;background:rgba(0,201,141,.07);bottom:-80px;left:-60px;}

/* CARD — smaller */
.admin-card{background:rgba(10,22,40,.92);backdrop-filter:blur(24px);border:1px solid rgba(0,201,141,.15);border-radius:20px;padding:30px 28px;width:100%;max-width:400px;box-shadow:0 0 0 1px rgba(255,255,255,.04),0 30px 60px rgba(0,0,0,.5);position:relative;z-index:2;}

/* shield */
.admin-shield{width:58px;height:58px;border-radius:50%;background:linear-gradient(135deg,rgba(0,201,141,.12),rgba(0,170,255,.08));border:1.5px solid rgba(0,201,141,.25);display:flex;align-items:center;justify-content:center;margin:0 auto 14px;animation:shieldPulse 3s ease-in-out infinite;}
@keyframes shieldPulse{0%,100%{box-shadow:0 0 15px rgba(0,201,141,.15);}50%{box-shadow:0 0 30px rgba(0,201,141,.3);}}
.admin-shield i{font-size:26px;color:var(--teal);}

/* logo & heading */
.admin-logo{display:flex;align-items:center;gap:8px;justify-content:center;margin-bottom:4px;text-decoration:none;}
.admin-logo .brand{font-family:'Syne',sans-serif;font-weight:800;font-size:18px;color:#fff;}
.admin-logo .brand span{color:var(--teal);}
.admin-title{font-family:'Syne',sans-serif;font-weight:800;font-size:18px;color:#fff;text-align:center;margin-bottom:4px;}
.admin-sub{font-size:12px;color:var(--muted);text-align:center;margin-bottom:14px;}
.admin-badge{display:inline-flex;align-items:center;gap:5px;background:rgba(255,92,122,.1);border:1px solid rgba(255,92,122,.2);border-radius:20px;padding:3px 12px;font-size:10px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:#ff8aa0;margin-bottom:18px;}

/* form */
.form-group{margin-bottom:14px;}
.form-label{display:block;font-size:12px;font-weight:600;color:#c0cfe8;margin-bottom:6px;}
.input-wrap{position:relative;}
.input-wrap i.li{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:14px;pointer-events:none;}
.form-control-adm{width:100%;padding:10px 12px 10px 38px;background:rgba(255,255,255,.04);border:1.5px solid rgba(255,255,255,.08);border-radius:10px;color:#fff;font-family:'DM Sans',sans-serif;font-size:13.5px;transition:all .2s;outline:none;}
.form-control-adm::placeholder{color:rgba(255,255,255,.25);}
.form-control-adm:focus{border-color:var(--teal);background:rgba(0,201,141,.04);box-shadow:0 0 0 3px rgba(0,201,141,.12);}
.form-control-adm.err{border-color:var(--danger);}
.pw-toggle{position:absolute;right:12px;top:50%;transform:translateY(-50%);color:var(--muted);cursor:pointer;font-size:15px;background:none;border:none;padding:0;z-index:5;}
.pw-toggle:hover{color:#fff;}

/* remember forgot */
.rem-row{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;font-size:12px;color:var(--muted);}
.rem-row label{display:flex;align-items:center;gap:6px;cursor:pointer;}
.rem-row a{color:var(--teal);text-decoration:none;}

/* SUBMIT BUTTON — fixed */
.btn-admin{
    display:flex !important;
    align-items:center !important;
    justify-content:center !important;
    gap:8px;
    width:100%;
    padding:12px;
    font-family:'DM Sans',sans-serif;
    font-weight:700;
    font-size:14px;
    color:#0a1628 !important;
    background:linear-gradient(135deg,#00c98d 0%,#00e0a8 100%) !important;
    border:none !important;
    border-radius:10px;
    cursor:pointer;
    transition:all .25s;
    box-shadow:0 4px 20px rgba(0,201,141,.35);
    margin-top:4px;
    opacity:1 !important;
    visibility:visible !important;
    position:relative;
    z-index:10;
}
.btn-admin:hover{transform:translateY(-2px);box-shadow:0 8px 28px rgba(0,201,141,.5);}
.btn-admin i{font-size:15px;}

/* alerts */
.alert-adm{border-radius:10px;padding:10px 14px;margin-bottom:14px;font-size:13px;display:flex;align-items:center;gap:8px;}
.adm-danger{background:rgba(255,92,122,.1);border:1px solid rgba(255,92,122,.25);color:#ffb3c1;}
.adm-success{background:rgba(0,201,141,.1);border:1px solid rgba(0,201,141,.25);color:#a0f5dc;}

/* security tip */
.security-tip{background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.06);border-radius:10px;padding:10px 12px;margin-top:14px;font-size:11px;color:var(--muted);display:flex;align-items:flex-start;gap:7px;}
.security-tip i{color:var(--teal);flex-shrink:0;margin-top:1px;}

/* footer */
.adm-footer{text-align:center;margin-top:14px;font-size:12px;color:var(--muted);}
.adm-footer a{color:var(--teal);text-decoration:none;font-weight:500;}

@media(max-width:480px){.admin-card{padding:24px 16px;}}
</style>
</head>
<body>
<div class="orb orb1"></div>
<div class="orb orb2"></div>

<div class="admin-card">

    {{-- Shield Icon --}}
    <div class="admin-shield">
        <i class="bi bi-shield-lock-fill"></i>
    </div>

    {{-- Logo --}}
    <a href="{{ route('home') }}" class="admin-logo">
        <span class="brand">Ecom<span>GstPro</span></span>
    </a>

    <div class="admin-title">Admin Portal</div>
    <div class="admin-sub">Restricted access — authorised personnel only</div>

    <div style="text-align:center;">
        <span class="admin-badge">
            <i class="bi bi-exclamation-triangle-fill"></i> Secure Zone
        </span>
    </div>

    {{-- Alerts --}}
    @if(session('error'))
        <div class="alert-adm adm-danger">
            <i class="bi bi-x-circle-fill"></i> {{ session('error') }}
        </div>
    @endif
    @if($errors->any())
        <div class="alert-adm adm-danger">
            <i class="bi bi-x-circle-fill"></i> {{ $errors->first() }}
        </div>
    @endif

    {{-- Login Form --}}
    <form action="{{ route('admin.login.post') }}" method="POST" style="position:relative;z-index:5;">
        @csrf

        <div class="form-group">
            <label class="form-label">Admin Email</label>
            <div class="input-wrap">
                <i class="bi bi-person-badge-fill li"></i>
                <input type="email" name="email"
                       class="form-control-adm {{ $errors->has('email') ? 'err' : '' }}"
                       placeholder="admin@ecomgstpro.in"
                       value="{{ old('email') }}" required autofocus>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Password</label>
            <div class="input-wrap">
                <i class="bi bi-key-fill li"></i>
                <input type="password" name="password" id="adminPw"
                       class="form-control-adm {{ $errors->has('password') ? 'err' : '' }}"
                       placeholder="Admin password"
                       style="padding-right:40px;" required>
                <button type="button" class="pw-toggle" onclick="toggleAdminPw()">
                    <i class="bi bi-eye-slash" id="adminPwIcon"></i>
                </button>
            </div>
        </div>

        <div class="rem-row">
            <label>
                <input type="checkbox" name="remember" style="accent-color:#00c98d;">
                Remember this device
            </label>
            <a href="#">Forgot?</a>
        </div>

        {{-- SUBMIT BUTTON --}}
        <button type="submit" class="btn-admin">
            <i class="bi bi-shield-check-fill"></i>
            Authenticate & Enter
        </button>

    </form>

    <div class="security-tip">
        <i class="bi bi-info-circle-fill"></i>
        <span>This portal is monitored. All login attempts are logged with IP and timestamp.</span>
    </div>

    <div class="adm-footer">
        <a href="{{ route('login') }}">
            <i class="bi bi-arrow-left"></i> Back to User Login
        </a>
    </div>

</div>

<script>
function toggleAdminPw() {
    const inp = document.getElementById('adminPw');
    const icon = document.getElementById('adminPwIcon');
    inp.type = inp.type === 'password' ? 'text' : 'password';
    icon.className = inp.type === 'password' ? 'bi bi-eye-slash' : 'bi bi-eye';
}
</script>
</body>
</html>
