<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    /** Download a single invoice as PDF (owner or admin only) */
    public function download(Invoice $invoice)
    {
        $user = Auth::user();

        // Only the invoice's own user can download it (admins bypass this
        // check in the admin.invoices.download route further below).
        if ($invoice->user_id !== $user->id) {
            abort(403);
        }

        $pdf = Pdf::loadView('invoices.pdf', [
            'invoice' => $invoice,
            'payment' => $invoice->payment,
            'plan'    => $invoice->plan,
            'user'    => $invoice->user,
        ])->setPaper('a4');

        return $pdf->download($invoice->invoice_number . '.pdf');
    }

    /** Admin: download any invoice regardless of owner */
    public function adminDownload(Invoice $invoice)
    {
        $pdf = Pdf::loadView('invoices.pdf', [
            'invoice' => $invoice,
            'payment' => $invoice->payment,
            'plan'    => $invoice->plan,
            'user'    => $invoice->user,
        ])->setPaper('a4');

        return $pdf->download($invoice->invoice_number . '.pdf');
    }
}