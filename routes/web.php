<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\OtpAuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\GstrUploadController;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\GstrSummaryController;
use App\Http\Controllers\DemoRequestController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\SubscriptionController;      // ← NEW
use App\Http\Controllers\InvoiceController;             // ← NEW
use App\Http\Controllers\AdminSubscriptionController; // ← FIXED


// ============ function is here start ====================
// ──────────────────────────────────────────────
// Main
// ──────────────────────────────────────────────
Route::get('/', function () {
    return view('welcome');
})->name('home');

// ──────────────────────────────────────────────
// Auth — Password Login
// ──────────────────────────────────────────────
Route::get('/login',  [OtpAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [OtpAuthController::class, 'sendLoginOtp'])->name('login.post');

// ──────────────────────────────────────────────
// Auth — OTP-only Login  ("Sign in with OTP")
// ──────────────────────────────────────────────
Route::get('/login/otp',  [OtpAuthController::class, 'showOtpLogin'])->name('otp.login');
Route::post('/login/otp', [OtpAuthController::class, 'sendOtpLoginEmail'])->name('otp.login.post');

// ──────────────────────────────────────────────
// Auth — Register
// ──────────────────────────────────────────────
Route::get('/register',  [OtpAuthController::class, 'showRegister'])->name('register');
Route::post('/register', [OtpAuthController::class, 'sendRegisterOtp'])->name('register.post');

// ──────────────────────────────────────────────
// Auth — Logout
// ──────────────────────────────────────────────
Route::post('/logout', [OtpAuthController::class, 'logout'])->name('logout');

// ──────────────────────────────────────────────
// Auth — OTP Verify + Resend  (register & login)
// ──────────────────────────────────────────────
Route::get('/verify-otp',  [OtpAuthController::class, 'showOtpForm'])->name('otp.verify.form');
Route::post('/verify-otp', [OtpAuthController::class, 'verifyOtp'])->name('otp.verify.post');
Route::post('/resend-otp', [OtpAuthController::class, 'resendOtp'])->name('otp.resend');

// ──────────────────────────────────────────────
// Auth — Forgot / Reset Password  (OTP-based)
// ──────────────────────────────────────────────
Route::get('/forgot-password',        [OtpAuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password',       [OtpAuthController::class, 'sendPasswordResetOtp'])->name('password.email');
Route::get('/reset-password/verify',  [OtpAuthController::class, 'showPasswordOtpForm'])->name('password.otp.form');
Route::post('/reset-password/verify', [OtpAuthController::class, 'resetPasswordWithOtp'])->name('password.update');

// ──────────────────────────────────────────────
// Dashboard + Settings
// ──────────────────────────────────────────────
Route::get('/dashboard', function () {
    return "Welcome to User Dashboard";
})->name('dashboard')->middleware('auth');

Route::get('/settings', function () {
    return "User settings page.";
})->name('settings')->middleware('auth');

// ──────────────────────────────────────────────
// Static pages
// ──────────────────────────────────────────────
Route::get('/about',        function () { return view('about'); })->name('about');
Route::get('/features',     function () { return view('features'); })->name('features');
Route::get('/pricing',      function () { return view('welcome'); })->name('pricing');
Route::get('/tutorials',    function () { return view('tutorials'); })->name('tutorials');
Route::get('/privacy-policy', function () { return view('privacy_policy'); })->name('privacy');
Route::get('/guides',       function () { return view('guides'); })->name('guides'); // Added this line
Route::get('/terms-and-conditions', function () { return view('terms_&_conditions'); })->name('terms');
Route::get('/refund-policy', function () { return view('refund_policy'); })->name('refund');
Route::get('/privacy/pdf', [\App\Http\Controllers\PrivacyPdfController::class, 'download'])->name('privacy.pdf');

// ──────────────────────────────────────────────
// Demo Request
// ──────────────────────────────────────────────
Route::get('/demo-request',  [DemoRequestController::class, 'showForm'])->name('demo.request');
Route::post('/demo-request', [DemoRequestController::class, 'submit'])->name('demo.submit');

// ──────────────────────────────────────────────
// Services  (auth required)
// ──────────────────────────────────────────────
Route::prefix('services')->name('services.')->middleware('auth')->group(function () {
    Route::get('/bank-statement-converter', function () { return view('welcome'); })->name('bank-statement-converter');
    Route::get('/ecommerce-gstr',           function () { return view('ecommerce-gstr'); })->name('ecommerce-gstr');
    Route::get('/reconciliation',           function () { return view('welcome'); })->name('reconciliation');
    Route::get('/itc-optimizer',            function () { return view('welcome'); })->name('itc-optimizer');
    Route::get('/bulk-upload',              function () { return view('welcome'); })->name('bulk-upload');
    Route::get('/gstr-auto',                function () { return view('welcome'); })->name('gstr-auto');
});

// ──────────────────────────────────────────────
// Subscription / Pricing  (auth required)  ← NEW
// ──────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/subscription/pricing',          [SubscriptionController::class, 'pricing'])->name('subscription.pricing');
    Route::post('/subscription/create-order',    [SubscriptionController::class, 'createOrder'])->name('subscription.createOrder');
    Route::match(['get', 'post'], '/subscription/checkout/{plan?}', function () {
        return redirect()->route('subscription.pricing');
    })->name('subscription.checkout');
    Route::post('/subscription/verify-payment',  [SubscriptionController::class, 'verifyPayment'])->name('subscription.verifyPayment');
    Route::get('/invoice/{invoice}/download',    [InvoiceController::class, 'download'])->name('invoice.download');
});

// Razorpay Webhook — CSRF/auth ke BAHAR, Razorpay ka server isse hit karega  ← NEW
Route::post('/razorpay/webhook', [SubscriptionController::class, 'webhook'])
    ->name('subscription.webhook')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

// ──────────────────────────────────────────────
// GSTR Tool  (auth + subscription + single-device required)  ← UPDATED middleware
// ──────────────────────────────────────────────
Route::prefix('gstr')->name('gstr.')->middleware(['auth', 'subscription', 'single_device'])->group(function () {

    // Clients
    Route::get('/clients',                  [ClientController::class, 'index'])  ->name('clients.index');
    Route::post('/clients',                 [ClientController::class, 'store'])  ->name('clients.store');
    Route::put('/clients/{client}',         [ClientController::class, 'update']) ->name('clients.update');
    Route::delete('/clients/{client}',      [ClientController::class, 'destroy'])->name('clients.destroy');
    Route::post('/clients/{client}/select', [ClientController::class, 'select']) ->name('clients.select');
    Route::get('/clients/{client}/history', [ClientController::class, 'history'])->name('clients.history');
    Route::delete('/batch/{batchId}',       [ClientController::class, 'destroyBatch'])->name('clients.batch.destroy');

    // Upload
    Route::get('/upload',                       [GstrUploadController::class, 'index'])               ->name('upload.index');
    Route::post('/upload/upload-file',          [GstrUploadController::class, 'uploadFile'])          ->name('upload.uploadFile');
    Route::post('/upload/check-period',         [GstrUploadController::class, 'checkPeriod'])         ->name('upload.checkPeriod');
    Route::post('/upload/process-transactions', [GstrUploadController::class, 'processTransactions']) ->name('upload.processTransactions');
    Route::post('/upload/compute-b2cs',         [GstrUploadController::class, 'computeB2CS'])         ->name('upload.computeB2CS');

    // Summary
    Route::get('/summary',                        [GstrSummaryController::class, 'index'])               ->name('summary');
    Route::post('/summary/edit-row',              [GstrSummaryController::class, 'editRow'])             ->name('summary.editRow');
    Route::get('/summary/download-csv',           [GstrSummaryController::class, 'downloadCSV'])         ->name('summary.downloadCSV');
    Route::get('/summary/download-json',          [GstrSummaryController::class, 'downloadJSON'])        ->name('summary.downloadJSON');
    Route::get('/summary/download-summary-excel', [GstrSummaryController::class, 'downloadSummaryExcel'])->name('summary.downloadSummaryExcel');
    Route::get('/summary/download-detail-excel',  [GstrSummaryController::class, 'downloadDetailedExcel'])->name('summary.downloadDetailedExcel');
    Route::get('/summary/download-doc-issue-csv', [GstrSummaryController::class, 'downloadDocIssueCSV'])->name('summary.downloadDocIssueCSV');
    Route::get('/summary/data',                   [GstrSummaryController::class, 'getSummaryData'])      ->name('summary.data');
    Route::get('/summary/clear',                  [GstrSummaryController::class, 'clearAndGoBack'])      ->name('summary.clear');

    // Manual B2B-invoice exclusion (e.g. portal error "GSTIN of
    // receiver is cancelled" — RET191360) + excluded-returns CSV export.
    // See GstrSummaryController::excludeB2BInvoice()/includeB2BInvoice()/
    // downloadExcludedReturnsCSV(), and summary.blade.php's "Excluded" tab.
    Route::post('/summary/exclude-b2b-invoice',          [GstrSummaryController::class, 'excludeB2BInvoice'])          ->name('summary.excludeB2BInvoice');
    Route::post('/summary/include-b2b-invoice',          [GstrSummaryController::class, 'includeB2BInvoice'])          ->name('summary.includeB2BInvoice');
    Route::get('/summary/download-excluded-returns-csv', [GstrSummaryController::class, 'downloadExcludedReturnsCSV']) ->name('summary.downloadExcludedReturnsCSV');

    // GSTR Dashboard
    Route::get('/dashboard', function () {
        return view('welcome');
    })->name('dashboard');

    Route::post('/gstr/debug-file', [GstrUploadController::class, 'debugFile'])->name('gstr.debug');
});

// ──────────────────────────────────────────────
// Batch History Downloads  (auth required)
// ──────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/history/{batchId}/csv',           [GstrSummaryController::class, 'downloadCSVByBatch'])          ->name('gstr.history.downloadCSV');
    Route::get('/history/{batchId}/summary-excel', [GstrSummaryController::class, 'downloadSummaryExcelByBatch']) ->name('gstr.history.downloadSummaryExcel');
    Route::get('/history/{batchId}/detail-excel',  [GstrSummaryController::class, 'downloadDetailExcelByBatch'])  ->name('gstr.history.downloadDetailExcel');
    Route::get('/history/{batchId}/json',          [GstrSummaryController::class, 'downloadJSONByBatch'])         ->name('gstr.history.downloadJSON');
});

// ──────────────────────────────────────────────
// Admin Auth
// ──────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login',  [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.post');
    Route::post('/logout',[AdminAuthController::class, 'logout'])->name('logout');
});

// ──────────────────────────────────────────────
// Admin Panel  (admin auth required)
// ──────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->middleware('auth:admin')->group(function () {

    Route::get('/', function () {
        $stats = [
            'total_revenue'      => \App\Models\Payment::whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(status)'), ['captured'])->sum('amount'),
            'total_purchases'    => \App\Models\Payment::whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(status)'), ['captured'])->count(),
            'revenue_this_month' => \App\Models\Payment::whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(status)'), ['captured'])->whereMonth('created_at', now()->month)->sum('amount'),
            'active_subscribers' => \App\Models\User::where(function($q) {
                                        $q->where('subscription_status', 'active')
                                          ->orWhere('plan', '!=', 'trial');
                                    })->count(),
            'total_users'        => \App\Models\User::count(),
            'users_today'        => \App\Models\User::whereDate('created_at', now())->count(),
            'logins_today'       => 0,
            'total_logins'       => 0,
            'pending_subs'       => \App\Models\Payment::whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(status)'), ['pending', 'created'])->count(),
            'pending_demos'      => \App\Models\DemoRequest::where('status', 'pending')->count(),
            'total_queries'      => \Illuminate\Support\Facades\Schema::hasTable('contact_messages') ? \App\Models\ContactMessage::count() : 0,
            'total_products'     => \Illuminate\Support\Facades\Schema::hasTable('invoices') ? \App\Models\Invoice::count() : \App\Models\Payment::count(), 
            'total_categories'   => 0, 
            'signups_week'       => \App\Models\User::select(\Illuminate\Support\Facades\DB::raw('DATE(created_at) as date'), \Illuminate\Support\Facades\DB::raw('count(*) as count'))
                                        ->where('created_at', '>=', now()->subDays(7))
                                        ->groupBy('date')
                                        ->get()
                                        ->toArray(),
            'recent_users'       => \App\Models\User::latest()->take(5)->get(),
            'recent_payments'    => \App\Models\Payment::with('user', 'plan')->latest()->take(8)->get(),
        ];
        return view('admin.index', $stats);
    })->name('dashboard');

    Route::get('/demo-requests',                 [DemoRequestController::class, 'adminIndex'])->name('demo.index');
    Route::post('/demo-requests/{demo}/confirm', [DemoRequestController::class, 'confirm'])->name('demo.confirm')->withTrashed();
    Route::post('/demo-requests/{demo}/cancel',  [DemoRequestController::class, 'cancel'])->name('demo.cancel');

    // ──────────────────────────────────────────────
    // Admin — Registered Users (list, detail, activate/deactivate)
    // ──────────────────────────────────────────────
    Route::get('/users',                       [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}',                [AdminUserController::class, 'show'])->name('users.show');
    Route::post('/users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('users.toggleStatus');

    // ──────────────────────────────────────────────
    // Admin — Subscriptions overview  ← NEW
    // ──────────────────────────────────────────────
    Route::get('/subscription', [AdminSubscriptionController::class, 'index'])->name('subscription.index');
    Route::get('/subscription/report', [AdminSubscriptionController::class, 'report'])->name('subscription.report');

});


/**
 * ═══════════════════════════════════════════════════════════════
 * ADD THESE ROUTES TO YOUR routes/web.php FILE
 * ═══════════════════════════════════════════════════════════════
 */

// Public Contact Page Routes
Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact/submit', [ContactController::class, 'submit'])->name('contact.submit');

/**
 * ═══════════════════════════════════════════════════════════════
 * ADMIN ROUTES - Add these inside your admin middleware group
 * ═══════════════════════════════════════════════════════════════
 */
/*
Route::middleware(['auth', 'admin'])->prefix('admin/contact')->name('admin.contact.')->group(function () {
    Route::get('/', 'ContactController@adminIndex')->name('index');
    Route::get('/{id}', 'ContactController@adminShow')->name('show');
    Route::post('/{id}/mark-replied', 'ContactController@adminMarkReplied')->name('mark-replied');
    Route::delete('/{id}', 'ContactController@adminDestroy')->name('destroy');
});

*/

// Admin Contact Routes (Corrected and placed inside the existing admin group)
Route::prefix('admin')->name('admin.')->middleware('auth:admin')->group(function () {
    // ... (your existing admin routes)
    Route::get('/contact', [ContactController::class, 'adminIndex'])->name('contact.index');
    Route::get('/contact/{id}', [ContactController::class, 'adminShow'])->name('contact.show');
    Route::post('/contact/{id}/mark-replied', [ContactController::class, 'adminMarkReplied'])->name('contact.mark-replied');
    Route::delete('/contact/{id}', [ContactController::class, 'adminDestroy'])->name('contact.destroy');
});


Route::get('/gstr/summary/download-state-summary-csv', [GstrSummaryController::class, 'downloadStateSummaryCSV'])
    ->name('gstr.summary.downloadStateSummaryCSV');

    Route::get('/gstr/summary/download-json', [GstrSummaryController::class, 'downloadJSON'])
    ->name('gstr.summary.downloadJSON');

      Route::post('/gstr/upload/period-status', [\App\Http\Controllers\GstrUploadController::class, 'periodStatus'])
    ->name('gstr.upload.periodStatus');

Route::post('/gstr/upload/delete-platform-period', [\App\Http\Controllers\GstrUploadController::class, 'deletePlatformPeriod'])
    ->name('gstr.upload.deletePlatformPeriod');

    Route::get('/subscription', [AdminSubscriptionController::class, 'index'])->name('subscription.index');
