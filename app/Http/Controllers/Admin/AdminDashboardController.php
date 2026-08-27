<?php
// app/Http/Controllers/Admin/AdminDashboardController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\DemoRequest;
use App\Models\Payment;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users'      => 0,
            'users_today'      => 0,
            'logins_today'     => 0,
            'total_logins'     => 0,
            'pending_demos'    => 0,
            'total_queries'    => 0,
            'total_categories' => 0,
            'total_products'   => 0,
            'total_revenue'    => 0,
            'total_purchases'  => 0,
            'signups_week'     => [],
            'recent_users'     => collect(),
            'admin_activities' => [],
        ];

        try {
            // ── Users ──
            if (Schema::hasTable('users')) {
                $stats['total_users'] = User::count();
                $stats['users_today'] = User::whereDate('created_at', today())->count();

                // Last 7 days signups (for the bar chart)
                $signups = User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                    ->where('created_at', '>=', Carbon::now()->subDays(6)->startOfDay())
                    ->groupBy('date')
                    ->pluck('count', 'date');

                $stats['signups_week'] = collect(range(0, 6))->map(function ($i) use ($signups) {
                    $date = Carbon::now()->subDays(6 - $i)->format('Y-m-d');
                    return ['date' => $date, 'count' => (int) ($signups[$date] ?? 0)];
                })->toArray();

                $stats['recent_users'] = User::latest()->take(6)->get();

                // Logins — only works if you track last_login_at (see step 3 below)
                if (Schema::hasColumn('users', 'last_login_at')) {
                    $stats['logins_today'] = User::whereDate('last_login_at', today())->count();
                }
            }

            // Total logins (all-time) — needs a login_logs table, see step 3
            if (Schema::hasTable('login_logs')) {
                $stats['total_logins'] = DB::table('login_logs')->count();
                if (!Schema::hasColumn('users', 'last_login_at')) {
                    $stats['logins_today'] = DB::table('login_logs')->whereDate('created_at', today())->count();
                }
            }

            // ── Pending demos ──
            if (Schema::hasTable('demo_requests')) {
                $stats['pending_demos'] = DemoRequest::whereNull('confirmed_at')->count();
            }

            // ── Products / categories / queries (existing) ──
            if (Schema::hasTable('queries')) {
                $stats['total_queries'] = DB::table('queries')->count();
            }
            if (Schema::hasTable('categories')) {
                $stats['total_categories'] = DB::table('categories')->where('is_active', 1)->count();
            }
            if (Schema::hasTable('products')) {
                $stats['total_products'] = DB::table('products')->where('is_active', 1)->count();
            }

            // ── Revenue + subscription purchases ──
            if (Schema::hasTable('payments')) {
                $stats['total_revenue']   = (float) Payment::where('status', 'paid')->sum('amount');
                $stats['total_purchases'] = Payment::where('status', 'paid')->count();
            }

        } catch (\Exception $e) {
            \Log::error('Admin dashboard stats error: ' . $e->getMessage());
        }

        return view('admin.index', $stats);
    }
}
