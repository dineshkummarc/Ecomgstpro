<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\GstrBatch;
use App\Models\GstrTransaction;
use App\Mail\UserDeactivatedMail;
use App\Mail\UserActivatedMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class AdminUserController extends Controller
{
    // ──────────────────────────────────────────────
    // LIST — all registered users, search + status filter
    // ──────────────────────────────────────────────
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));
        $status = $request->input('status', 'all'); // all | active | inactive

        $query = User::query()->orderByDesc('created_at');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        $users = $query->paginate(20)->withQueryString();

        // Har user ke batches ka count nikaalo (relation add kiye bina, simple groupBy)
        $userIds = $users->pluck('id');
        $batchCounts = GstrBatch::whereIn('user_id', $userIds)
            ->selectRaw('user_id, count(*) as cnt')
            ->groupBy('user_id')
            ->pluck('cnt', 'user_id');

        foreach ($users as $u) {
            $u->batches_count = $batchCounts[$u->id] ?? 0;
        }

        $stats = [
            'total_users'    => User::count(),
            'active_users'   => User::where('is_active', true)->count(),
            'inactive_users' => User::where('is_active', false)->count(),
        ];

        return view('admin.users.index', compact('users', 'search', 'status', 'stats'));
    }

    // ──────────────────────────────────────────────
    // SHOW — single user ki detail: batches, transactions, stats
    // ──────────────────────────────────────────────
    public function show(User $user)
    {
        $batches = GstrBatch::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        $totalTransactions = GstrTransaction::where('user_id', $user->id)
            ->where('status', 'processed')
            ->count();

        $totalTaxableValue = round($batches->sum('taxable_value'), 2);
        $totalTax          = round($batches->sum('total_tax'), 2);

        $platformsUsed = GstrTransaction::where('user_id', $user->id)
            ->where('status', 'processed')
            ->distinct()
            ->pluck('platform')
            ->filter()
            ->values();

        return view('admin.users.show', compact(
            'user', 'batches', 'totalTransactions',
            'totalTaxableValue', 'totalTax', 'platformsUsed'
        ));
    }

    // ──────────────────────────────────────────────
    // TOGGLE STATUS — Deactivate (reason required + email) / Activate
    // ──────────────────────────────────────────────
    public function toggleStatus(Request $request, User $user)
    {
        $activating = !$user->is_active; // agar abhi inactive hai to ye "activate" request hai

        if (!$activating) {
            // Deactivate karte waqt reason mandatory hai
            $validated = $request->validate([
                'reason' => 'required|string|min:5|max:500',
            ]);

            $user->is_active           = false;
            $user->deactivated_at      = now();
            $user->deactivation_reason = $validated['reason'];
            $user->save();

            try {
                Mail::to($user->email)->send(new UserDeactivatedMail($user->name, $validated['reason']));
            } catch (\Throwable $e) {
                Log::error('Failed to send deactivation email', [
                    'user_id' => $user->id,
                    'error'   => $e->getMessage(),
                ]);
            }

            return back()->with('success', "{$user->name} ko deactivate kar diya gaya hai aur email bhej di gayi hai.");
        }

        // Reactivate
        $user->is_active           = true;
        $user->deactivated_at      = null;
        $user->deactivation_reason = null;
        $user->save();

        try {
            Mail::to($user->email)->send(new UserActivatedMail($user->name));
        } catch (\Throwable $e) {
            Log::error('Failed to send activation email', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);
        }

        return back()->with('success', "{$user->name} ko wapas activate kar diya gaya hai.");
    }
}