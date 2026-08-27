<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\SubscriptionPlan;
use App\Models\Invoice;
use App\Mail\PaymentSuccessMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Razorpay\Api\Api;

/**
 * Requires: composer require razorpay/razorpay
 * .env keys needed:
 *   RAZORPAY_KEY=rzp_test_xxxxxxxx
 *   RAZORPAY_SECRET=xxxxxxxxxxxxxxxx
 */
class SubscriptionController extends Controller
{
    /** Pricing / checkout page */
    public function pricing()
    {
        $plans = SubscriptionPlan::where('is_active', true)
            ->orderBy('duration_months')
            ->get();

        $user = Auth::user();

        return view('subscription.pricing', compact('plans', 'user'));
    }

    /** Create a Razorpay order (called via AJAX from the pricing page) */
    public function createOrder(Request $request)
    {
        $request->validate([
            'plan_slug'       => 'required|string|exists:subscription_plans,slug',
            'billing_name'    => 'required|string|max:150',
            'billing_email'   => 'required|email|max:150',
            'billing_phone'   => 'required|string|regex:/^[6-9]\d{9}$/',
            'billing_address' => 'required|string|max:255',
            'billing_city'    => 'required|string|max:100',
            'billing_state'   => 'required|string|max:100',
            'billing_pincode' => 'required|string|regex:/^\d{6}$/',
            'billing_gstin'   => 'nullable|string|max:20',
        ]);

        $user = Auth::user();
        $plan = SubscriptionPlan::where('slug', $request->plan_slug)->firstOrFail();

        $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));

        $amountInPaise = (int) round($plan->total_price * 100);

        $razorpayOrder = $api->order->create([
            'receipt'  => 'user_' . $user->id . '_' . time(),
            'amount'   => $amountInPaise,
            'currency' => 'INR',
            'notes'    => [
                'user_id'   => $user->id,
                'plan_slug' => $plan->slug,
            ],
        ]);

        $payment = Payment::create([
            'user_id'           => $user->id,
            'plan_id'           => $plan->id,
            'razorpay_order_id' => $razorpayOrder['id'],
            'amount'            => $plan->total_price,
            'currency'          => 'INR',
            'status'            => 'created',
            'billing_name'      => $request->billing_name,
            'billing_email'     => $request->billing_email,
            'billing_phone'     => $request->billing_phone,
            'billing_address'   => $request->billing_address,
            'billing_city'      => $request->billing_city,
            'billing_state'     => $request->billing_state,
            'billing_pincode'   => $request->billing_pincode,
            'billing_gstin'     => $request->billing_gstin,
        ]);

        return response()->json([
            'success'   => true,
            'order_id'  => $razorpayOrder['id'],
            'amount'    => $amountInPaise,
            'currency'  => 'INR',
            'key'       => config('services.razorpay.key'),
            'payment_id'=> $payment->id,
            'plan_name' => $plan->name,
            'user_name' => $user->name,
            'user_email'=> $user->email,
            'user_phone'=> $user->phone,
        ]);
    }

    /** Verify payment + activate the plan (called after Razorpay checkout success) */
    public function verifyPayment(Request $request)
    {
        $request->validate([
            'razorpay_payment_id' => 'required|string',
            'razorpay_order_id'   => 'required|string',
            'razorpay_signature'  => 'required|string',
            'payment_id'          => 'required|exists:payments,id',
        ]);

        $payment = Payment::with('plan')->findOrFail($request->payment_id);

        $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));

        try {
            $api->utility->verifyPaymentSignature([
                'razorpay_order_id'   => $request->razorpay_order_id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature'  => $request->razorpay_signature,
            ]);
        } catch (\Exception $e) {
            $payment->update(['status' => 'failed']);
            return response()->json(['success' => false, 'message' => 'Payment verification failed. Please contact support if the amount was debited.'], 422);
        }

        $plan = $payment->plan;
        $planStart = Carbon::now();
        $planEnd   = Carbon::now()->addMonths($plan->duration_months);

        $payment->update([
            'razorpay_payment_id' => $request->razorpay_payment_id,
            'razorpay_signature'  => $request->razorpay_signature,
            'status'              => 'paid',
            'plan_started_at'     => $planStart,
            'plan_ends_at'        => $planEnd,
        ]);

        $user = $payment->user;
        $user->plan_type            = $plan->slug;
        $user->subscription_status  = 'active';
        $user->subscription_ends_at = $planEnd;
        $user->gstin_limit          = $plan->gstin_limit;
        $user->save();

        // Generate the invoice record, then email the PDF to the user.
        $invoice = Invoice::create([
            'invoice_number' => Invoice::generateNumber(),
            'user_id'        => $user->id,
            'payment_id'     => $payment->id,
            'plan_id'        => $plan->id,
            'amount'         => $payment->amount,
            'currency'       => $payment->currency,
            'issued_at'      => Carbon::now(),
        ]);

        Mail::to($user->email)->send(new PaymentSuccessMail($invoice));

        return response()->json([
            'success'  => true,
            'message'  => 'Payment successful! Your ' . $plan->name . ' plan is now active. Your invoice has been emailed to you.',
            'redirect' => route('dashboard'),
        ]);
    }
}
