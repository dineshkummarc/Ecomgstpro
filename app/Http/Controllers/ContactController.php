<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Mail;

class ContactController extends Controller
{
    /**
     * Show contact page
     */
    public function show()
    {
        return view('contact');
    }

    /**
     * Handle contact form submission
     */
    public function submit(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:160',
            'phone' => 'nullable|string|max:20',
            'subject' => 'required|string|max:160',
            'message' => 'required|string|max:5000',
        ], [
            'name.required' => 'Please enter your name.',
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',
            'subject.required' => 'Please enter a subject.',
            'message.required' => 'Please enter your message.',
        ]);

        try {
            // Save to database
            $contactMessage = ContactMessage::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'subject' => $validated['subject'],
                'message' => $validated['message'],
                'status' => 'new',
            ]);

            // Send confirmation email to user (wrapped in try-catch)
            try {
                Mail::send('contact-confirmation', [
                    'name' => $contactMessage->name,
                    'subject' => $contactMessage->subject
                ], function ($mail) use ($contactMessage) {
                    $mail->to($contactMessage->email)
                         ->subject('We Received Your Message - EcomgstPro');
                });
            } catch (\Exception $mailError) {
                \Log::error('User confirmation email error: ' . $mailError->getMessage());
                // Don't fail the entire request if confirmation email fails
            }

            // Send notification email to admin (wrapped in try-catch)
            try {
                Mail::send('admin.contact-notification', [
                    'messageId' => $contactMessage->id,
                    'name' => $contactMessage->name,
                    'email' => $contactMessage->email,
                    'phone' => $contactMessage->phone,
                    'subject' => $contactMessage->subject,
                    'message' => $contactMessage->message,
                    'created_at' => $contactMessage->created_at
                ], function ($mail) use ($contactMessage) {
                    $mail->to(env('ADMIN_EMAIL', 'admin@ecomgstpro.in'))
                         ->subject('New Contact Form Submission - ' . $contactMessage->subject);
                });
            } catch (\Exception $mailError) {
                \Log::error('Admin notification email error: ' . $mailError->getMessage());
                // Don't fail if admin notification fails
            }

            return response()->json([
                'success' => true,
                'message' => 'Your message has been sent successfully! We will contact you soon.'
            ]);

        } catch (\Exception $e) {
            \Log::error('Contact form error: ' . $e->getMessage() . ' | ' . $e->getFile() . ':' . $e->getLine());

            return response()->json([
                'success' => false,
                'message' => 'Failed to save message. Please try again or contact us at +91-8076865836'
            ], 500);
        }
    }

    /**
     * Admin: View all contact messages
     */
    public function adminIndex(Request $request)
    {
        // Get status filter from URL query parameter
        $status = $request->query('status');

        // Build query with optional status filter
        $query = ContactMessage::query();

        // Only filter if status is provided and valid
        if ($status && in_array($status, ['new', 'read', 'replied'])) {
            $query->where('status', $status);
        }

        // Get paginated contacts ordered by newest first
        $contacts = $query->orderBy('created_at', 'desc')->paginate(20);

        // Count messages by status for the statistics/tabs
        $counts = [
            'all' => ContactMessage::count(),
            'new' => ContactMessage::where('status', 'new')->count(),
            'read' => ContactMessage::where('status', 'read')->count(),
            'replied' => ContactMessage::where('status', 'replied')->count(),
        ];

        return view('admin.contact-index', compact('contacts', 'status', 'counts'));
    }

    /**
     * Admin: View single message
     */
    public function adminShow($id)
    {
        $message = ContactMessage::findOrFail($id); // Corrected variable name

        // Mark as read if not already read
        if ($message->status === 'new') {
            $message->update(['status' => 'read']);
        }

        return view('admin.contact-show', compact('message'));
    }

    /**
     * Admin: Delete message
     */
    public function adminDestroy($id)
    {
        $message = ContactMessage::findOrFail($id);
        $message->delete();

        return redirect()->route('admin.contact.index')
            ->with('success', 'Contact message deleted successfully.');
    }

    /**
     * Alternative destroy method with route model binding
     */
    public function destroy(ContactMessage $contact)
    {
        $contact->delete();

        return redirect()->route('admin.contact.index')
            ->with('success', 'Contact message deleted successfully.');
    }

    /**
     * Admin: Mark as replied
     */
    public function adminMarkReplied($id)
    {
        $message = ContactMessage::findOrFail($id);
        $message->update(['status' => 'replied']);

        return redirect()->back()
            ->with('success', 'Message marked as replied.');
    }

    /**
     * Alternative mark replied method with route model binding
     */
    public function markReplied(ContactMessage $contact)
    {
        $contact->update(['status' => 'replied']);

        return redirect()->back()
            ->with('success', 'Message marked as replied.');
    }
}
