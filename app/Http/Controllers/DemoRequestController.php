<?php

namespace App\Http\Controllers;

use App\Models\DemoRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class DemoRequestController extends Controller
{
    // ─────────────────────────────────────────
    // PUBLIC: Show booking form
    // ─────────────────────────────────────────
    public function showForm()
    {
        return view('demo.request');
    }

    // ─────────────────────────────────────────
    // PUBLIC: Submit booking
    // ─────────────────────────────────────────
    public function submit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|max:255',
            'phone'          => 'required|string|max:15',
            'company_name'   => 'nullable|string|max:255',
            'message'        => 'nullable|string|max:1000',
            'requested_date' => 'required|date|after_or_equal:today',
            'requested_time' => 'required|string',
        ], [
            'requested_date.after_or_equal' => 'Please select today or a future date.',
        ]);

        if ($validator->fails()) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'errors' => $validator->errors()], 422)
                : back()->withErrors($validator)->withInput();
        }

        $demo = DemoRequest::create([
            'name'           => $request->name,
            'email'          => $request->email,
            'phone'          => $request->phone,
            'company_name'   => $request->company_name,
            'message'        => $request->message,
            'requested_date' => $request->requested_date,
            'requested_time' => $request->requested_time,
            'status'         => 'pending',
        ]);

        // Email to user — confirmation of receipt
        $this->sendUserAcknowledgement($demo);

        // Email to admin — new request notification
        $this->notifyAdmin($demo);

        return $request->expectsJson()
            ? response()->json(['success' => true, 'message' => 'Demo request submitted successfully!'])
            : redirect()->route('demo.request')->with('success', 'Your demo request has been submitted! We will confirm shortly.');
    }

    // ─────────────────────────────────────────
    // ADMIN: List all demo requests
    // ─────────────────────────────────────────
    public function adminIndex(Request $request)
    {
        $status = $request->query('status', 'all');

        $query = DemoRequest::orderBy('created_at', 'desc');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $demos   = $query->paginate(15);
        $pending = DemoRequest::where('status', 'pending')->count();

        return view('admin.demo-requests.index', compact('demos', 'status', 'pending'));
    }

    // ─────────────────────────────────────────
    // ADMIN: Confirm a demo request
    // ─────────────────────────────────────────
    public function confirm(Request $request, DemoRequest $demo)
    {
        try {
            // Accept both formats: "2026-06-19T07:46" and "2026-06-19 07:46"
            $dateTimeString = $request->input('confirmed_datetime');

            // Validate format
            if (!$dateTimeString) {
                return response()->json([
                    'success' => false,
                    'message' => 'Confirmed date & time is required'
                ], 422);
            }

            // Parse the datetime - handle both ISO and standard formats
            try {
                // Try parsing ISO format first (from HTML datetime-local input)
                $confirmedDateTime = Carbon::createFromFormat('Y-m-d\TH:i', $dateTimeString);
            } catch (\Exception $e) {
                // Try standard format
                try {
                    $confirmedDateTime = Carbon::createFromFormat('Y-m-d H:i', $dateTimeString);
                } catch (\Exception $e2) {
                    // Try with seconds
                    $confirmedDateTime = Carbon::parse($dateTimeString);
                }
            }

            // Check if datetime is in future
            if ($confirmedDateTime <= now()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Confirmed date & time must be in the future'
                ], 422);
            }

            // Check if demo is still pending
            if ($demo->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending demos can be confirmed'
                ], 422);
            }

            // Update the demo
            $demo->update([
                'status'             => 'confirmed',
                'confirmed_datetime' => $confirmedDateTime,
                'admin_note'         => $request->input('admin_note'),
            ]);

            // Send confirmation email to user
            $this->sendConfirmationEmail($demo);

            return response()->json([
                'success' => true,
                'message' => "Demo confirmed! Email sent to {$demo->email}",
                'data' => [
                    'id' => $demo->id,
                    'status' => $demo->status,
                    'confirmed_datetime' => $demo->confirmed_datetime->format('d M Y h:i A')
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Demo confirm error: ' . $e->getMessage(), [
                'demo_id' => $demo->id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error confirming demo: ' . $e->getMessage()
            ], 500);
        }
    }

    // ─────────────────────────────────────────
    // ADMIN: Cancel a demo request
    // ─────────────────────────────────────────
    public function cancel(Request $request, DemoRequest $demo)
    {
        try {
            // Check if already cancelled
            if ($demo->status === 'cancelled') {
                return response()->json([
                    'success' => false,
                    'message' => 'This demo is already cancelled'
                ], 422);
            }

            // Update the demo
            $demo->update([
                'status'     => 'cancelled',
                'admin_note' => $request->input('admin_note', 'Cancelled by admin'),
            ]);

            // Send cancellation email to user
            $this->sendCancellationEmail($demo);

            return response()->json([
                'success' => true,
                'message' => 'Demo request cancelled. Email sent to user.'
            ]);

        } catch (\Exception $e) {
            \Log::error('Demo cancel error: ' . $e->getMessage(), [
                'demo_id' => $demo->id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error cancelling demo: ' . $e->getMessage()
            ], 500);
        }
    }

    // ═══════════════════════════════════════════
    // PRIVATE EMAIL HELPERS
    // ═══════════════════════════════════════════

    private function sendUserAcknowledgement(DemoRequest $demo): void
    {
        try {
            $html = $this->emailTemplate(
                title: 'Demo Request Received!',
                subtitle: 'We have received your demo request',
                bodyHtml: "
                    <p>Hi <strong>{$demo->name}</strong>,</p>
                    <p>Thank you for requesting a demo of <strong>GSTpilot</strong>. We have received your request and our team will confirm your slot shortly.</p>
                    <div class='info-box'>
                        <div class='info-row'><span class='label'>Preferred Date</span><span class='value'>" . Carbon::parse($demo->requested_date)->format('d M Y') . "</span></div>
                        <div class='info-row'><span class='label'>Preferred Time</span><span class='value'>{$demo->requested_time}</span></div>
                        <div class='info-row'><span class='label'>Status</span><span class='value badge pending'>Pending Confirmation</span></div>
                    </div>
                    <p>You will receive another email once your demo is confirmed.</p>
                "
            );

            Mail::html($html, fn($m) => $m->to($demo->email)->subject('GSTpilot — Demo Request Received'));
        } catch (\Exception $e) {
            \Log::error('Demo ack email error: ' . $e->getMessage());
        }
    }

    private function notifyAdmin(DemoRequest $demo): void
    {
        $adminEmail = config('mail.from.address');
        try {
            $html = $this->emailTemplate(
                title: 'New Demo Request',
                subtitle: 'A new demo has been requested',
                bodyHtml: "
                    <div class='info-box'>
                        <div class='info-row'><span class='label'>Name</span><span class='value'>{$demo->name}</span></div>
                        <div class='info-row'><span class='label'>Email</span><span class='value'>{$demo->email}</span></div>
                        <div class='info-row'><span class='label'>Phone</span><span class='value'>{$demo->phone}</span></div>
                        <div class='info-row'><span class='label'>Company</span><span class='value'>" . ($demo->company_name ?? '—') . "</span></div>
                        <div class='info-row'><span class='label'>Preferred Date</span><span class='value'>" . Carbon::parse($demo->requested_date)->format('d M Y') . "</span></div>
                        <div class='info-row'><span class='label'>Preferred Time</span><span class='value'>{$demo->requested_time}</span></div>
                        <div class='info-row'><span class='label'>Message</span><span class='value'>" . ($demo->message ?? '—') . "</span></div>
                    </div>
                    <p style='text-align:center;'>
                        <a href='" . route('admin.demo.index') . "' class='btn-email'>Go to Admin Panel</a>
                    </p>
                "
            );

            Mail::html($html, fn($m) => $m->to($adminEmail)->subject('GSTpilot — New Demo Request from ' . $demo->name));
        } catch (\Exception $e) {
            \Log::error('Admin notify email error: ' . $e->getMessage());
        }
    }

    private function sendConfirmationEmail(DemoRequest $demo): void
    {
        try {
            $confirmedDt = $demo->confirmed_datetime;
            $html = $this->emailTemplate(
                title: 'Demo Confirmed!',
                subtitle: 'Your demo slot has been confirmed',
                bodyHtml: "
                    <p>Hi <strong>{$demo->name}</strong>,</p>
                    <p>Great news! Your GSTpilot demo has been <strong>confirmed</strong>. Please join at the scheduled time below.</p>
                    <div class='info-box'>
                        <div class='info-row'><span class='label'>Date</span><span class='value'>{$confirmedDt->format('d M Y')}</span></div>
                        <div class='info-row'><span class='label'>Time</span><span class='value'>{$confirmedDt->format('h:i A')}</span></div>
                        <div class='info-row'><span class='label'>Status</span><span class='value badge confirmed'>Confirmed</span></div>
                    </div>
                    " . ($demo->admin_note ? "<div class='note-box'><strong>Note from our team:</strong><br>{$demo->admin_note}</div>" : "") . "
                    <p>Please keep this email handy. Our team will reach out just before the demo with the meeting link.</p>
                    <p>Looking forward to showing you GSTpilot!</p>
                "
            );

            Mail::html($html, fn($m) => $m->to($demo->email)->subject('GSTpilot — Your Demo is Confirmed on ' . $confirmedDt->format('d M Y')));
        } catch (\Exception $e) {
            \Log::error('Confirmation email error: ' . $e->getMessage());
        }
    }

    private function sendCancellationEmail(DemoRequest $demo): void
    {
        try {
            $html = $this->emailTemplate(
                title: 'Demo Request Cancelled',
                subtitle: 'Regarding your demo request',
                bodyHtml: "
                    <p>Hi <strong>{$demo->name}</strong>,</p>
                    <p>We regret to inform you that your demo request has been <strong>cancelled</strong>.</p>
                    " . ($demo->admin_note ? "<div class='note-box'><strong>Reason:</strong><br>{$demo->admin_note}</div>" : "") . "
                    <p>We apologise for the inconvenience. Please feel free to submit a new demo request and we will do our best to accommodate you.</p>
                    <p style='text-align:center;'>
                        <a href='" . route('demo.request') . "' class='btn-email'>Request a New Demo</a>
                    </p>
                "
            );

            Mail::html($html, fn($m) => $m->to($demo->email)->subject('GSTpilot — Demo Request Update'));
        } catch (\Exception $e) {
            \Log::error('Cancellation email error: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────
    // BASE EMAIL TEMPLATE
    // ─────────────────────────────────────────
    private function emailTemplate(string $title, string $subtitle, string $bodyHtml): string
    {
        return <<<HTML
        <!DOCTYPE html><html><head><meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; background: #f4f6f9; margin: 0; padding: 20px; color: #333; }
            .container { max-width: 520px; margin: 0 auto; background: #fff; border-radius: 14px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
            .header { background: linear-gradient(135deg, #0a1628, #0d1f3c); padding: 28px 30px; text-align: center; }
            .header h1 { color: #fff; margin: 0 0 4px; font-size: 22px; }
            .header p  { color: #8a9bbf; margin: 0; font-size: 13px; }
            .body { padding: 30px 28px; font-size: 14.5px; line-height: 1.7; color: #444; }
            .body p { margin: 0 0 14px; }
            .info-box { background: #f0f4ff; border-radius: 10px; padding: 16px 18px; margin: 18px 0; }
            .info-row { display: flex; justify-content: space-between; padding: 7px 0; border-bottom: 1px solid #e0e6f0; font-size: 13.5px; }
            .info-row:last-child { border-bottom: none; }
            .label { color: #888; font-weight: 600; }
            .value { color: #1a1a2e; font-weight: 500; text-align: right; }
            .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 700; }
            .badge.pending   { background: #fff3cd; color: #856404; }
            .badge.confirmed { background: #d1f5e0; color: #0a6640; }
            .note-box { background: #fff8e1; border-left: 4px solid #ffc107; border-radius: 6px; padding: 12px 16px; margin: 16px 0; font-size: 13.5px; color: #555; }
            .btn-email { display: inline-block; background: #00c98d; color: #0a1628; font-weight: 700; font-size: 14px; padding: 12px 28px; border-radius: 10px; text-decoration: none; margin: 10px 0; }
            .footer { background: #f8f9fa; padding: 14px; text-align: center; font-size: 12px; color: #aaa; }
        </style></head><body>
        <div class="container">
            <div class="header">
                <h1>GSTpilot</h1>
                <p>{$subtitle}</p>
            </div>
            <div class="body">
                <h2 style="font-size:20px;color:#0a1628;margin:0 0 16px;">{$title}</h2>
                {$bodyHtml}
            </div>
            <div class="footer">© 2024 GSTpilot. All rights reserved.</div>
        </div>
        </body></html>
        HTML;
    }
}
