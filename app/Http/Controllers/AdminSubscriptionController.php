<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Payment;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminSubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->input('filter', 'all');

        $counts = [
            'trial'   => User::where('plan', 'trial')->count(),
            'active'  => Payment::where('status', 'captured')->where('plan_ends_at', '>', Carbon::now())->count(),
            'expired' => Payment::where('status', 'captured')->where('plan_ends_at', '<', Carbon::now())->count(),
            'pending' => Payment::where('status', 'pending')->count(), // Added pending count
            'revenue' => Payment::where('status', 'captured')->sum('amount'),
        ];

        // Fetch paginated list of subscriptions/users
        $query = User::query(); 
        
        // If filter is pending, we might need a different approach, 
        // but for now let's just show users and include a status for pending payments.
        $users = $query->paginate(20);
        
        // Fetch pending payments separately to display
        $pendingPayments = Payment::where('status', 'pending')->with('user')->get();

        return view('admin.subscription.index', [
            'counts'          => $counts,
            'filter'          => $filter,
            'users'           => $users,
            'pendingPayments' => $pendingPayments, // Passed to view
        ]);
    }
    public function report()
    {
        // Use lowercase for comparison to be safe
        $payments = Payment::with('user', 'plan')->orderByDesc('created_at')->paginate(50);
        
        $stats = [
            'total_revenue' => Payment::whereRaw('LOWER(status) = ?', ['captured'])->sum('amount'),
            'successful'    => Payment::whereRaw('LOWER(status) = ?', ['captured'])->count(),
            'pending'       => Payment::whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(status)'), ['pending', 'created'])->count(),
        ];

        return view('admin.subscription.report', compact('payments', 'stats'));
    }

}
