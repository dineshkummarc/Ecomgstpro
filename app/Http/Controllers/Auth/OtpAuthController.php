<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\User;

class OtpAuthController extends Controller
{
    // ─────────────────────────────────────────
    // REGISTER: Step 1 — Show form
    // ─────────────────────────────────────────
    public function showRegister()
    {
        return view('auth.register');
    }

    // ─────────────────────────────────────────
    // REGISTER: Step 2 — Validate, send OTP
    // ─────────────────────────────────────────
    public function sendRegisterOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email',
            'phone'        => 'nullable|string|max:15',
            'company_name' => 'nullable|string|max:255',
            'password'     => 'required|min:8|confirmed',
            'terms'        => 'accepted',
        ], [
            'email.unique'       => 'This email is already registered.',
            'password.confirmed' => 'Password and Confirm Password do not match.',
            'terms.accepted'     => 'You must agree to the Terms of Service.',
        ]);

        if ($validator->fails()) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'errors' => $validator->errors()], 422)
                : redirect()->back()->withErrors($validator)->withInput();
        }

        session([
            'reg_name'     => $request->name,
            'reg_email'    => $request->email,
            'reg_phone'    => $request->phone,
            'reg_company'  => $request->company_name,
            'reg_password' => Hash::make($request->password),
        ]);

        $otp  = $this->generateAndStoreOtp($request->email, 'register');
        $sent = $this->sendOtpEmail($request->email, $otp, 'Register');

        if (!$sent) {
            $msg = 'Failed to send OTP email. Please check your mail settings in .env';
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $msg], 500)
                : redirect()->back()->withErrors(['email' => $msg])->withInput();
        }

        return $request->expectsJson()
            ? response()->json(['success' => true, 'message' => "OTP sent to {$request->email}."])
            : redirect()->route('otp.verify.form', ['type' => 'register'])
                ->with('success', "OTP sent to: {$request->email}");
    }

    // ─────────────────────────────────────────
    // LOGIN (Password): Show form
    // ─────────────────────────────────────────
    public function showLogin()
    {
        return view('auth.login');
    }

    // ─────────────────────────────────────────
    // LOGIN (Password): Verify credentials, send OTP
    // ─────────────────────────────────────────
    public function sendLoginOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors(['email' => 'Invalid email or password.'])->withInput();
        }

        if ($user->status === 'suspended') {
            return back()->withErrors(['email' => 'Your account has been suspended.'])->withInput();
        }

        // ← NEW: Block login if admin has deactivated this account
        if (!$user->is_active) {
            $reasonMsg = $user->deactivation_reason
                ? ' Reason: ' . $user->deactivation_reason
                : '';
            return back()->withErrors([
                'email' => 'Your account has been deactivated by the admin.' . $reasonMsg . ' Please contact support.',
            ])->withInput();
        }

        // Log the user in directly
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('home'))
            ->with('success', 'Login successful! Welcome back.');
    }

    // ─────────────────────────────────────────
    // LOGIN (OTP only): Show email form
    // ─────────────────────────────────────────
    public function showOtpLogin()
    {
        return view('auth.otp-login');
    }

    // ─────────────────────────────────────────
    // LOGIN (OTP only): Send OTP without password
    // ─────────────────────────────────────────
    public function sendOtpLoginEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ], [
            'email.exists' => 'No account found with this email address.',
        ]);

        if ($validator->fails()) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'errors' => $validator->errors()], 422)
                : back()->withErrors($validator)->withInput();
        }

        $user = User::where('email', $request->email)->first();

        if ($user->status === 'suspended') {
            $msg = 'Your account has been suspended.';
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $msg], 403)
                : back()->withErrors(['email' => $msg])->withInput();
        }

        // ← NEW: Block OTP-login flow if admin has deactivated this account
        if (!$user->is_active) {
            $msg = 'Your account has been deactivated by the admin.'
                 . ($user->deactivation_reason ? ' Reason: ' . $user->deactivation_reason : '')
                 . ' Please contact support.';
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $msg], 403)
                : back()->withErrors(['email' => $msg])->withInput();
        }

        session(['login_email' => $request->email]);

        $otp  = $this->generateAndStoreOtp($request->email, 'login');
        $sent = $this->sendOtpEmail($request->email, $otp, 'Login');

        if (!$sent) {
            $msg = 'Failed to send OTP. Please try again.';
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $msg], 500)
                : back()->withErrors(['email' => $msg])->withInput();
        }

        return $request->expectsJson()
            ? response()->json(['success' => true, 'message' => "OTP sent to {$request->email}."])
            : redirect()->route('otp.verify.form', ['type' => 'login'])
                ->with('success', "OTP sent to: {$request->email}");
    }

    // ─────────────────────────────────────────
    // OTP VERIFY: Show form
    // ─────────────────────────────────────────
    public function showOtpForm(Request $request)
    {
        $type  = $request->query('type', 'login');
        $email = $type === 'register' ? session('reg_email') : session('login_email');

        if (!$email) {
            return redirect()->route($type === 'register' ? 'register' : 'login')
                ->withErrors(['email' => 'Session expired. Please try again.']);
        }

        return view('auth.verify-otp', compact('type'));
    }

    // ─────────────────────────────────────────
    // OTP VERIFY: Validate OTP
    // ─────────────────────────────────────────
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp'  => 'required|digits:6',
            'type' => 'required|in:register,login',
        ]);

        $type  = $request->type;
        $email = $type === 'register' ? session('reg_email') : session('login_email');

        if (!$email) {
            $msg = 'Session has expired. Please try again.';
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $msg])
                : redirect()->route($type === 'register' ? 'register' : 'login')
                    ->withErrors(['otp' => $msg]);
        }

        $otpRecord = DB::table('otp_verifications')
            ->where('email', $email)
            ->where('otp', $request->otp)
            ->where('type', $type)
            ->where('is_used', 0)
            ->where('expires_at', '>', Carbon::now())
            ->latest()
            ->first();

        if (!$otpRecord) {
            $msg = 'Invalid or expired OTP. Please try again.';
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $msg])
                : back()->withErrors(['otp' => $msg]);
        }

        DB::table('otp_verifications')
            ->where('id', $otpRecord->id)
            ->update(['is_used' => 1]);

        if ($type === 'register') {
            $user = User::create([
                'name'              => session('reg_name'),
                'email'             => session('reg_email'),
                'phone'             => session('reg_phone'),
                'company_name'      => session('reg_company'),
                'password'          => session('reg_password'),
                'email_verified_at' => Carbon::now(),
                'plan'              => 'starter',
                'status'            => 'active',
            ]);

            session()->forget(['reg_name', 'reg_email', 'reg_password', 'reg_phone', 'reg_company']);
            Auth::login($user);

            return $request->expectsJson()
                ? response()->json(['success' => true, 'redirect' => route('home')])
                : redirect()->route('home')->with('success', 'Registration successful! Welcome to EcomgstPro.');
        } else {
            $user = User::where('email', $email)->first();

            // ← NEW: Block login at the final OTP-verify step too — this
            // is the actual point where the session gets created for BOTH
            // the password-login and OTP-only-login flows, so this check
            // guarantees a deactivated account can never reach Auth::login()
            // even if it somehow slipped past the earlier checks.
            if (!$user->is_active) {
                session()->forget('login_email');
                $msg = 'Your account has been deactivated by the admin.'
                     . ($user->deactivation_reason ? ' Reason: ' . $user->deactivation_reason : '')
                     . ' Please contact support.';
                return $request->expectsJson()
                    ? response()->json(['success' => false, 'message' => $msg], 403)
                    : redirect()->route('login')->withErrors(['email' => $msg]);
            }

            session()->forget('login_email');
            Auth::login($user);

            return $request->expectsJson()
                ? response()->json(['success' => true, 'redirect' => route('home')])
                : redirect()->route('home')->with('success', 'Login successful!');
        }
    }

    // ─────────────────────────────────────────
    // OTP RESEND
    // ─────────────────────────────────────────
    public function resendOtp(Request $request)
    {
        $type  = $request->type;
        $email = $type === 'register' ? session('reg_email') : session('login_email');

        if (!$email) {
            return response()->json(['success' => false, 'message' => 'Session has expired. Please start again.']);
        }

        $otp  = $this->generateAndStoreOtp($email, $type);
        $sent = $this->sendOtpEmail($email, $otp, ucfirst($type));

        return response()->json([
            'success' => $sent,
            'message' => $sent ? 'A new OTP has been sent to your email.' : 'Failed to send email. Please try again.',
        ]);
    }

    // ─────────────────────────────────────────
    // FORGOT PASSWORD: Show email form
    // ─────────────────────────────────────────
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    // ─────────────────────────────────────────
    // FORGOT PASSWORD: Send OTP to email
    // ─────────────────────────────────────────
    public function sendPasswordResetOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ], [
            'email.exists' => 'No account found with this email address.',
        ]);

        if ($validator->fails()) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'errors' => $validator->errors()], 422)
                : back()->withErrors($validator)->withInput();
        }

        session(['reset_email' => $request->email]);

        $otp  = $this->generateAndStoreOtp($request->email, 'password_reset');
        $sent = $this->sendOtpEmail($request->email, $otp, 'Password Reset');

        if (!$sent) {
            $msg = 'Failed to send OTP. Please try again.';
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $msg], 500)
                : back()->withErrors(['email' => $msg])->withInput();
        }

        return $request->expectsJson()
            ? response()->json(['success' => true, 'message' => "OTP sent to {$request->email}."])
            : redirect()->route('password.otp.form')
                ->with('success', "OTP sent to: {$request->email}");
    }

    // ─────────────────────────────────────────
    // FORGOT PASSWORD: Show OTP + new password form
    // ─────────────────────────────────────────
    public function showPasswordOtpForm()
    {
        $email = session('reset_email');

        if (!$email) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Session expired. Please request a new OTP.']);
        }

        return view('auth.verify-otp', ['type' => 'reset']);
    }

    // ─────────────────────────────────────────
    // FORGOT PASSWORD: Verify OTP + update password
    // ─────────────────────────────────────────
    public function resetPasswordWithOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'otp'                   => 'required|digits:6',
            'password'              => 'required|min:8|confirmed',
            'password_confirmation' => 'required',
        ], [
            'password.confirmed' => 'Passwords do not match.',
        ]);

        if ($validator->fails()) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'errors' => $validator->errors()], 422)
                : back()->withErrors($validator)->withInput();
        }

        $email = session('reset_email');

        if (!$email) {
            $msg = 'Session expired. Please request a new OTP.';
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $msg])
                : redirect()->route('password.request')->withErrors(['email' => $msg]);
        }

        $otpRecord = DB::table('otp_verifications')
            ->where('email', $email)
            ->where('otp', $request->otp)
            ->where('type', 'password_reset')
            ->where('is_used', 0)
            ->where('expires_at', '>', Carbon::now())
            ->latest()
            ->first();

        if (!$otpRecord) {
            $msg = 'Invalid or expired OTP. Please try again.';
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $msg])
                : back()->withErrors(['otp' => $msg]);
        }

        DB::table('otp_verifications')
            ->where('id', $otpRecord->id)
            ->update(['is_used' => 1]);

        $user = User::where('email', $email)->first();
        $user->update(['password' => Hash::make($request->password)]);

        session()->forget('reset_email');

        return $request->expectsJson()
            ? response()->json(['success' => true, 'redirect' => route('login')])
            : redirect()->route('login')
                ->with('success', 'Password reset successfully! Please sign in with your new password.');
    }

    // ─────────────────────────────────────────
    // LOGOUT
    // ─────────────────────────────────────────
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('success', 'You have been logged out successfully.');
    }

    // ═══════════════════════════════════════════
    // PRIVATE HELPERS
    // ═══════════════════════════════════════════

    private function generateAndStoreOtp(string $email, string $type): string
    {
        DB::table('otp_verifications')
            ->where('email', $email)
            ->where('type', $type)
            ->where('is_used', 0)
            ->update(['is_used' => 1]);

        $otp = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        DB::table('otp_verifications')->insert([
            'email'      => $email,
            'otp'        => $otp,
            'type'       => $type,
            'is_used'    => 0,
            'expires_at' => Carbon::now()->addMinutes(10),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        return $otp;
    }

    private function sendOtpEmail(string $email, string $otp, string $actionType): bool
    {
        try {
            Mail::html($this->otpEmailTemplate($otp, $actionType), function ($message) use ($email, $actionType) {
                $message->to($email)->subject("EcomgstPro — Your {$actionType} OTP");
            });
            return true;
        } catch (\Exception $e) {
            \Log::error('OTP Email Error: ' . $e->getMessage());
            return false;
        }
    }

    private function otpEmailTemplate(string $otp, string $actionType): string
    {
        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; background: #f4f6f9; margin: 0; padding: 20px; }
                .container { max-width: 500px; margin: 0 auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
                .header { background: linear-gradient(135deg, #0a1628, #0d1f3c); padding: 30px; text-align: center; }
                .header h1 { color: #00c98d; margin: 0; font-size: 24px; }
                .header p { color: #8a9bbf; margin: 5px 0 0; font-size: 14px; }
                .body { padding: 35px 30px; text-align: center; }
                .body p { color: #555; font-size: 15px; margin-bottom: 20px; }
                .otp-box { background: #f0faf6; border: 2px dashed #00c98d; border-radius: 10px; padding: 20px; margin: 20px 0; }
                .otp-code { font-size: 42px; font-weight: bold; color: #00c98d; letter-spacing: 10px; }
                .note { font-size: 13px; color: #999; margin-top: 25px; }
                .footer { background: #f8f9fa; padding: 15px; text-align: center; font-size: 12px; color: #aaa; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>EcomgstPro</h1>
                    <p>GST Management Platform</p>
                </div>
                <div class="body">
                    <p>You requested an OTP for <strong>{$actionType}</strong>. Use the code below:</p>
                    <div class="otp-box">
                        <div class="otp-code">{$otp}</div>
                    </div>
                    <p>This OTP expires in <strong>10 minutes</strong>.</p>
                    <p class="note">If you did not request this, please ignore this email.<br>Never share your OTP with anyone.</p>
                </div>
                <div class="footer">© 2025 EcomgstPro. All rights reserved.</div>
            </div>
        </body>
        </html>
        HTML;
    }
}