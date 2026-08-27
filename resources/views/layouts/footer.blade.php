{{-- ============================================================
     GST Pilot — Footer Component (footer.blade.php)
     Color: Dark navy #0a1628 + teal #00c98d accent (matches header)
     Features:
       • 4-column footer grid (brand / quick links / contact / policy)
       • Animated top-border teal glow strip
       • Bottom bar: copyright left, social icons right
       • LEFT floating WhatsApp button
       • RIGHT floating Call button
     ============================================================ --}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ecomgstpro — Footer Preview</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'DM Sans',sans-serif;background:#f0f4ff}

        /* ─── FOOTER SHELL ─── */
        .gp-footer{
            background:#0a1628;
            position:relative;
            overflow:hidden;
        }

        /* animated teal accent line at top */
        .gp-footer::before{
            content:'';
            display:block;
            height:3px;
            background:linear-gradient(90deg,transparent 0%,#00c98d 30%,#00aaff 70%,transparent 100%);
            animation:scanline 4s ease-in-out infinite alternate;
        }
        @keyframes scanline{
            0%  {background-position:0% 50%}
            100%{background-position:100% 50%}
        }

        /*
        /* subtle dot-grid texture */
        .gp-footer::after{
            content:'';
            position:absolute;
            inset:0;
            background-image:radial-gradient(rgba(255,255,255,0.03) 1px, transparent 1px);
            background-size:28px 28px;
            pointer-events:none;
        }

        /* ─── MAIN CONTENT ─── */
        .gp-footer-main{
            position:relative;
            z-index:1;
            max-width:1280px;
            margin:0 auto;
            padding:56px 32px 40px;
            display:grid;
            grid-template-columns:1.6fr 1fr 1fr 1fr;
            gap:48px;
        }

        /* ─── BRAND COLUMN ─── */
        .gp-brand-logo{
            display:flex;
            align-items:center;
            gap:12px;
            margin-bottom:20px;
            text-decoration:none;
        }
        .gp-brand-icon{
            width:44px;height:44px;border-radius:10px;
            background:linear-gradient(135deg,#00aaff,#00c98d);
            display:flex;align-items:center;justify-content:center;
            font-family:'Syne',sans-serif;font-weight:800;font-size:22px;color:#fff;
            flex-shrink:0;
        }
        .gp-brand-name{
            font-family:'Syne',sans-serif;font-weight:800;font-size:22px;
            letter-spacing:-0.4px;color:#fff;line-height:1;
        }
        .gp-brand-name em{color:#00c98d;font-style:normal}

        .gp-brand-tagline{
            font-size:11px;font-weight:600;letter-spacing:2.5px;
            text-transform:uppercase;color:#00c98d;
            border-left:2px solid #00c98d;padding-left:10px;
            margin-bottom:18px;
        }

        .gp-brand-desc{
            font-size:14px;line-height:1.75;color:#8a9bbf;
            margin-bottom:24px;max-width:280px;
        }

        /* social icons */
        .gp-socials{display:flex;gap:10px}
        .gp-socials a{
            width:36px;height:36px;border-radius:8px;
            border:1px solid rgba(255,255,255,0.10);
            display:flex;align-items:center;justify-content:center;
            color:#8a9bbf;font-size:16px;text-decoration:none;
            transition:all .2s;
        }
        .gp-socials a:hover{
            color:#fff;border-color:#00c98d;background:rgba(0,201,141,0.12);
        }

        /* ─── LINK COLUMNS ─── */
        .gp-col-title{
            font-family:'Syne',sans-serif;font-weight:700;font-size:13px;
            letter-spacing:2px;text-transform:uppercase;
            color:#00c98d;margin-bottom:20px;
            position:relative;padding-bottom:12px;
        }
        .gp-col-title::after{
            content:'';position:absolute;bottom:0;left:0;
            width:24px;height:2px;background:#00c98d;border-radius:2px;
        }
        .gp-col-links{list-style:none;display:flex;flex-direction:column;gap:10px}
        .gp-col-links a{
            font-size:14px;color:#8a9bbf;text-decoration:none;
            display:flex;align-items:center;gap:8px;
            transition:color .2s,gap .2s;
        }
        .gp-col-links a::before{
            content:'';width:4px;height:4px;border-radius:50%;
            background:#00c98d;opacity:0;flex-shrink:0;
            transition:opacity .2s;
        }
        .gp-col-links a:hover{color:#fff;gap:12px}
        .gp-col-links a:hover::before{opacity:1}

        /* contact items */
        .gp-contact-item{
            display:flex;align-items:center;gap:12px;
            margin-bottom:14px;
        }
        .gp-contact-icon{
            width:34px;height:34px;border-radius:8px;flex-shrink:0;
            background:rgba(0,201,141,0.10);border:1px solid rgba(0,201,141,0.2);
            display:flex;align-items:center;justify-content:center;
            color:#00c98d;font-size:15px;
        }
        .gp-contact-text{font-size:13.5px;color:#c8d6f0;line-height:1.5}
        .gp-contact-text small{display:block;font-size:11px;color:#8a9bbf;margin-bottom:1px}

        /* ─── BOTTOM BAR ─── */
        .gp-footer-bar{
            position:relative;z-index:1;
            border-top:1px solid rgba(255,255,255,0.07);
            max-width:1280px;margin:0 auto;
            padding:20px 32px;
            display:flex;align-items:center;justify-content:space-between;
            flex-wrap:wrap;gap:12px;
        }
        .gp-copyright{font-size:13px;color:#8a9bbf}
        .gp-copyright strong{color:#c8d6f0;font-weight:600}

        .gp-badges{display:flex;align-items:center;gap:10px}
        .gp-badge-pill{
            font-size:11px;font-weight:600;letter-spacing:.5px;
            padding:4px 12px;border-radius:20px;
            border:1px solid rgba(0,201,141,0.25);
            color:#00c98d;background:rgba(0,201,141,0.08);
        }

        /* ─── FLOATING BUTTONS ─── */
        .gp-float{
            position:fixed;z-index:9999;
            display:flex;flex-direction:column;align-items:center;
            gap:4px;
        }
        .gp-float-left{ bottom:28px;left:20px;align-items:flex-start; }
        .gp-float-right{ bottom:28px;right:20px;align-items:flex-end; }

        .gp-float-btn{
            width:54px;height:54px;border-radius:50%;
            display:flex;align-items:center;justify-content:center;
            text-decoration:none;font-size:22px;
            box-shadow:0 4px 20px rgba(0,0,0,0.35);
            transition:transform .2s,box-shadow .2s;
        }
        .gp-float-btn:hover{transform:scale(1.10);box-shadow:0 6px 28px rgba(0,0,0,0.45)}

        .gp-float-wa{background:#25d366;color:#fff}
        .gp-float-wa:hover{color:#fff}

        .gp-float-call{background:linear-gradient(135deg,#00aaff,#00c98d);color:#fff}
        .gp-float-call:hover{color:#fff}

        .gp-float-label{
            font-family:'DM Sans',sans-serif;
            font-size:10px;font-weight:700;letter-spacing:.8px;
            text-transform:uppercase;
            background:#1a2d4a;color:#c8d6f0;
            padding:3px 9px;border-radius:4px;
            white-space:nowrap;
        }

        /* pulse ring */
        @keyframes pulse-ring{
            0%  {box-shadow:0 0 0 0 rgba(0,201,141,0.5)}
            70% {box-shadow:0 0 0 12px rgba(0,201,141,0)}
            100%{box-shadow:0 0 0 0 rgba(0,201,141,0)}
        }
        .gp-float-call{animation:pulse-ring 2.2s infinite}

        @keyframes pulse-wa{
            0%  {box-shadow:0 0 0 0 rgba(37,211,102,0.5)}
            70% {box-shadow:0 0 0 12px rgba(37,211,102,0)}
            100%{box-shadow:0 0 0 0 rgba(37,211,102,0)}
        }
        .gp-float-wa{animation:pulse-wa 2.2s infinite}

        /* ─── RESPONSIVE ─── */
        @media(max-width:900px){
            .gp-footer-main{grid-template-columns:1fr 1fr;gap:36px}
        }
        @media(max-width:560px){
            .gp-footer-main{grid-template-columns:1fr;gap:28px;padding:36px 20px 24px}
            .gp-footer-bar{flex-direction:column;align-items:flex-start;padding:16px 20px}
        }
    </style>
</head>
<body>



{{-- ╔══════════════════════════════════════╗
     ║  FLOATING BUTTONS (both sides)       ║
     ╚══════════════════════════════════════╝ --}}

{{-- LEFT: WhatsApp --}}
<div class="gp-float gp-float-left">
    <a href="https://wa.me/918076865836" target="_blank"
       class="gp-float-btn gp-float-wa" aria-label="Chat on WhatsApp">
        <i class="bi bi-whatsapp"></i>
    </a>
    <span class="gp-float-label">WhatsApp</span>
</div>

{{-- RIGHT: Call --}}
<div class="gp-float gp-float-right">
    <a href="tel:+918076865836"
       class="gp-float-btn gp-float-call" aria-label="Call us">
        <i class="bi bi-telephone-fill"></i>
    </a>
    <span class="gp-float-label">Call Us</span>
</div>





{{-- ============================================================
     FinTaxPro — Auto Popup Ad Widget
     • Appears every 10 seconds (auto show, auto hide after 6s)
     • Clickable card -> redirects to FinTaxPro site
     • Matches GST Pilot dark navy (#0a1628) + teal (#00c98d) theme
     • Close (X) button to dismiss manually
     ============================================================ --}}

<style>
    /* ─── FINTAXPRO POPUP AD ─── */
    .ftp-ad-wrap{
        position:fixed;
        bottom:100px;
        left:20px;
        z-index:9998;
        max-width:300px;
        opacity:0;
        transform:translateY(20px) scale(.95);
        pointer-events:none;
        transition:opacity .35s ease, transform .35s ease;
    }
    .ftp-ad-wrap.ftp-show{
        opacity:1;
        transform:translateY(0) scale(1);
        pointer-events:auto;
    }

    .ftp-ad-card{
        position:relative;
        display:flex;
        align-items:center;
        gap:14px;
        background:linear-gradient(135deg,#0e1f38,#0a1628);
        border:1px solid rgba(0,201,141,0.25);
        border-radius:14px;
        padding:14px 16px;
        box-shadow:0 10px 32px rgba(0,0,0,0.4), 0 0 0 1px rgba(0,201,141,0.08);
        text-decoration:none;
        cursor:pointer;
        overflow:hidden;
    }

    /* animated teal glow sweep */
    .ftp-ad-card::before{
        content:'';
        position:absolute;
        inset:0;
        background:linear-gradient(120deg,transparent 20%,rgba(0,201,141,0.12) 50%,transparent 80%);
        background-size:200% 100%;
        animation:ftp-sweep 3s linear infinite;
        pointer-events:none;
    }
    @keyframes ftp-sweep{
        0%{background-position:200% 0}
        100%{background-position:-200% 0}
    }

    .fintax-logo-medal{
        position:relative;z-index:1;
        width:52px;height:52px;
        border-radius:50%;
        flex-shrink:0;
        overflow:hidden;
        border:2px solid #00c98d;
        box-shadow:0 0 0 3px rgba(0,201,141,0.15);
        background:#fff;
    }
    .fintax-logo-medal img{
        width:100%;height:100%;
        object-fit:cover;
        display:block;
    }

    .ftp-ad-text{position:relative;z-index:1;flex:1;min-width:0}
    .ftp-ad-badge{
        font-family:'DM Sans',sans-serif;
        font-size:9.5px;font-weight:700;letter-spacing:1.2px;
        text-transform:uppercase;color:#00c98d;
        margin-bottom:3px;
    }
    .ftp-ad-title{
        font-family:'Syne',sans-serif;
        font-size:14.5px;font-weight:800;color:#fff;
        line-height:1.25;margin-bottom:3px;
    }
    .ftp-ad-sub{
        font-family:'DM Sans',sans-serif;
        font-size:11px;color:#8a9bbf;line-height:1.4;
    }
    .ftp-ad-sub strong{color:#c8d6f0;font-weight:600}

    .ftp-ad-arrow{
        position:relative;z-index:1;
        width:26px;height:26px;border-radius:50%;
        background:rgba(0,201,141,0.12);
        border:1px solid rgba(0,201,141,0.3);
        display:flex;align-items:center;justify-content:center;
        color:#00c98d;font-size:12px;flex-shrink:0;
        transition:transform .2s;
    }
    .ftp-ad-card:hover .ftp-ad-arrow{transform:translateX(3px)}

    .ftp-ad-close{
        position:absolute;top:6px;right:6px;z-index:2;
        width:20px;height:20px;border-radius:50%;
        background:rgba(255,255,255,0.06);
        border:none;color:#8a9bbf;font-size:12px;
        display:flex;align-items:center;justify-content:center;
        cursor:pointer;transition:all .2s;
    }
    .ftp-ad-close:hover{background:rgba(255,80,80,0.15);color:#ff8080}

    @media(max-width:560px){
        .ftp-ad-wrap{left:12px;right:12px;max-width:none;bottom:96px}
    }
</style>

{{-- ── POPUP MARKUP ── --}}
<div class="ftp-ad-wrap" id="ftpAdWrap">
    <a href="https://fintaxpro.in" target="_blank" rel="noopener" class="ftp-ad-card" id="ftpAdCard">
        <button type="button" class="ftp-ad-close" id="ftpAdClose" aria-label="Close ad">
            <i class="bi bi-x-lg"></i>
        </button>

        <div class="fintax-logo-medal">
            <img src="{{ asset('images/fintaxlogo.jpg') }}" alt="FinTaxPro Logo">
        </div>

        <div class="ftp-ad-text">
            <p class="ftp-ad-badge">Powered By</p>
            <p class="ftp-ad-title">FinTaxPro Advisory LLP</p>
            <p class="ftp-ad-sub">Founded by <strong>CA Rajat Garg</strong> — Trusted Tax &amp; Compliance Experts</p>
        </div>

        <div class="ftp-ad-arrow"><i class="bi bi-arrow-up-right"></i></div>
    </a>
</div>

<script>
(function(){
    var wrap   = document.getElementById('ftpAdWrap');
    var card   = document.getElementById('ftpAdCard');
    var closeBtn = document.getElementById('ftpAdClose');

    var SHOW_EVERY_MS = 10000;   // popup triggers every 10s
    var VISIBLE_MS    = 6000;    // stays visible for 6s each time
    var dismissed     = false;   // true once user manually closes
    var hideTimer     = null;

    function showAd(){
        if (dismissed) return;
        wrap.classList.add('ftp-show');
        clearTimeout(hideTimer);
        hideTimer = setTimeout(function(){
            wrap.classList.remove('ftp-show');
        }, VISIBLE_MS);
    }

    // close button: dismiss for rest of session
    closeBtn.addEventListener('click', function(e){
        e.preventDefault();
        e.stopPropagation();
        wrap.classList.remove('ftp-show');
        dismissed = true;
        clearInterval(intervalId);
    });

    // first appearance after 3s, then repeats every 10s
    setTimeout(showAd, 3000);
    var intervalId = setInterval(showAd, SHOW_EVERY_MS);
})();
</script>

{{-- ╔══════════════════════════════════════╗
     ║  FOOTER                              ║
     ╚══════════════════════════════════════╝ --}}
<footer class="gp-footer">

    <div class="gp-footer-main">

        {{-- ── COLUMN 1: Brand ── --}}
        <div>
              <a href="{{ route('home') }}" class="gp-logo-wrap">
            <img src="{{ asset('images/EcomgstPro-logo.png') }}" alt="GST Pilot" class="gp-logo-img" onerror="this.src='https://placehold.co/42x42/00c98d/0a1628?text=GP'">
            <!-- <div class="gp-logo-text">
                <span class="brand">GST<span>Pilot</span></span>
                <span class="tagline">Analyze · Automate · Accelerate</span>
            </div> -->
        </a>

            <p class="gp-brand-tagline">Analyze · Automate · Accelerate</p>

            <p class="gp-brand-desc">
                Simplifying GST compliance for businesses across India. Smart automation, real‑time reconciliation, and end-to-end filing — all in one platform.
            </p>

            <div class="gp-socials">
                <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                <a href="#" aria-label="YouTube"><i class="bi bi-youtube"></i></a>
                <a href="#" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
            </div>
        </div>

        {{-- ── COLUMN 2: Quick Links ── --}}
        <div>
            <p class="gp-col-title">Quick Links</p>
            <ul class="gp-col-links">
                <li><a href="{{ route('about') }}">About Us</a></li>
                <li><a href="{{ route('contact') }}">Contact Us</a></li>
                <!-- <li><a href="#">Bank Statement Converter</a></li> -->
                <li><a href="{{ route('gstr.clients.index') }}">E-Commerce Seller GSTR‑1</a></li>
                <!-- <li><a href="#">Tutorials</a></li> -->
                <li><a href="{{ route('features') }}">Features</a></li>
            </ul>
        </div>

        {{-- ── COLUMN 3: Contact ── --}}
        <div>
            <p class="gp-col-title">Contact Us</p>

            <div class="gp-contact-item">
                <div class="gp-contact-icon"><i class="bi bi-telephone-fill"></i></div>
                <div class="gp-contact-text">
                    <small>Phone</small>
                    +91-8076865836
                </div>
            </div>

            <div class="gp-contact-item">
                <div class="gp-contact-icon"><i class="bi bi-whatsapp"></i></div>
                <div class="gp-contact-text">
                    <small>WhatsApp</small>
                    +91-8076865836
                </div>
            </div>

            <div class="gp-contact-item">
                <div class="gp-contact-icon"><i class="bi bi-envelope-fill"></i></div>
                <div class="gp-contact-text">
                    <small>Email</small>
                    support@ecomgstpro.com
                </div>
            </div>

            <div class="gp-contact-item" style="align-items:flex-start">
                <div class="gp-contact-icon" style="margin-top:2px"><i class="bi bi-geo-alt-fill"></i></div>
                <div class="gp-contact-text">
                    <small>Address</small>
                    Office No. 458, 4th Floor Agarwal Metro Heights Tower,<br>
                    Netaji Subhash Place, Pitampura,<br>
                    Delhi – 110034
                </div>
            </div>
        </div>

        {{-- ── COLUMN 4: Policy ── --}}
        <div>
            <p class="gp-col-title">Policy</p>
            <ul class="gp-col-links">
                <li><a href="{{ route('terms') }}">Terms &amp; Conditions</a></li>
                <li><a href="{{ route('privacy') }}">Privacy Policy</a></li>
                <li><a href="{{ route('refund') }}">Refund Policy</a></li>
            </ul>

            <div style="margin-top:28px;padding:16px;border-radius:10px;border:1px solid rgba(0,201,141,0.2);background:rgba(0,201,141,0.05)">
                <p style="font-size:12px;color:#8a9bbf;margin-bottom:6px;letter-spacing:.5px;text-transform:uppercase;font-weight:600">GST Certified</p>
                <p style="font-size:13px;color:#c8d6f0;line-height:1.6">
                    Approved GST Suvidha Provider &amp; trusted by 10,000+ businesses.
                </p>
            </div>
        </div>

    </div>

    {{-- ── BOTTOM BAR ── --}}
    <div class="gp-footer-bar">
        <p class="gp-copyright">
            © 2026 <strong>EcomgstPro</strong> —FinTaxPro Advisory LLP. All rights reserved.
        </p>
        <div class="gp-badges">
            <span class="gp-badge-pill">GSP Certified</span>
            <span class="gp-badge-pill">ISO 27001</span>
            <span class="gp-badge-pill">DIPP Startup</span>
        </div>
    </div>

</footer>

</body>
</html>
