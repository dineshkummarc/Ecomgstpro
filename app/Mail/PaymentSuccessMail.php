<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

class PaymentSuccessMail extends Mailable
{
    use Queueable, SerializesModels;

    public Invoice $invoice;

    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    public function build()
    {
        $pdfContent = Pdf::loadView('invoices.pdf', [
            'invoice' => $this->invoice,
            'payment' => $this->invoice->payment,
            'plan'    => $this->invoice->plan,
            'user'    => $this->invoice->user,
        ])->setPaper('a4')->output();

        return $this->subject('Payment Received — Invoice ' . $this->invoice->invoice_number)
            ->view('emails.payment_success')
            ->with([
                'name'       => $this->invoice->user->name,
                'planName'   => $this->invoice->plan->name,
                'amount'     => $this->invoice->amount,
                'invoiceNo'  => $this->invoice->invoice_number,
                'validTill'  => $this->invoice->payment->plan_ends_at->format('d M Y'),
                'dashboardUrl' => route('dashboard'),
            ])
            ->attachData($pdfContent, $this->invoice->invoice_number . '.pdf', [
                'mime' => 'application/pdf',
            ]);
    }
}