<?php
// app/Http/Controllers/Auth/AuthController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AuthController extends Controller
{
    // ──────────────────────────────────────────────────────────
    // REGISTER: Show Form
    // ──────────────────────────────────────────────────────────
    public function showRegister()
    {
        return view('auth.register');
    }

    // ──────────────────────────────────────────────────────────
    // REGISTER: Send OTP via Email (AJAX)
    // ──────────────────────────────────────────────────────────
    public function sendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // Check if email already exists
        if (User::where('email', $request->email)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'This email is already registered. Please sign in.'
            ]);
        }

        $otp = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = Carbon::now()->addMinutes(10);

        // Store OTP temporarily in session
        session([
            'reg_otp'        => $otp,
            'reg_otp_email'  => $request->email,
            'reg_otp_expiry' => $expiresAt,
        ]);

        // Send OTP Email
        try {
            Mail::send('emails.otp', [
                'otp'  => $otp,
                'type' => 'registration',
            ], function ($message) use ($request) {
                $message->to($request->email)
                        ->subject('GSTpilot — Your Verification OTP');
            });

            return response()->json(['success' => true, 'message' => 'OTP sent successfully!']);
        } catch (\Exception $e) {
            // In development: log and show OTP in response (REMOVE IN PRODUCTION!)
            \Log::info("OTP for {$request->email}: {$otp}");
            return response()->json([
                'success' => true,
                'message' => 'OTP sent! (Dev mode: ' . $otp . ')',
                'otp_dev' => config('app.debug') ? $otp : null,
            ]);
        }
    }

    // ──────────────────────────────────────────────────────────
    // REGISTER: Verify OTP and Create User
    // ──────────────────────────────────────────────────────────
    public function verifyOtpAndRegister(Request $request)
    {
        $request->validate([
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|email|unique:users,email',
            'phone'                 => 'nullable|string|max:15',
            'company_name'          => 'nullable|string|max:255',
            'password'              => 'required|string|min:8|confirmed',
            'otp'                   => 'required|string|size:6',
        ]);

        // Validate OTP
        $sessionOtp    = session('reg_otp');
        $sessionEmail  = session('reg_otp_email');
        $sessionExpiry = session('reg_otp_expiry');

        if (
            !$sessionOtp ||
            $sessionEmail !== $request->email ||
            $sessionOtp !== $request->otp ||
            Carbon::now()->isAfter($sessionExpiry)
        ) {
            return back()
                ->withErrors(['otp' => 'Invalid or expired OTP. Please try again.'])
                ->withInput();
        }

        // Create user
        $user = User::create([
            'name'               => $request->name,
            'email'              => $request->email,
            'phone'              => $request->phone,
            'company_name'       => $request->company_name,
            'password'           => Hash::make($request->password),
            'email_verified_at'  => now(),
            'plan'               => 'starter',
            'status'             => 'active',
        ]);

        // Clear OTP from session
        session()->forget(['reg_otp', 'reg_otp_email', 'reg_otp_expiry']);

        // Log the user in
        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Welcome to GSTpilot! Your account is ready.');
    }

    // ──────────────────────────────────────────────────────────
    // LOGIN: Show Form
    // ──────────────────────────────────────────────────────────
    public function showLogin()
    {
        return view('auth.login');
    }

    // ──────────────────────────────────────────────────────────
    // LOGIN: Authenticate
    // ──────────────────────────────────────────────────────────
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');
        $remember    = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();

            if ($user->status === 'suspended') {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Your account has been suspended. Contact support.'
                ]);
            }

            return redirect()->intended(route('dashboard'))
                             ->with('success', 'Welcome back, ' . $user->name . '!');
        }

        return back()
            ->withErrors(['email' => 'These credentials do not match our records.'])
            ->withInput($request->only('email', 'remember'));
    }

    // ──────────────────────────────────────────────────────────
    // LOGOUT
    // ──────────────────────────────────────────────────────────
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been logged out successfully.');
    }
}
