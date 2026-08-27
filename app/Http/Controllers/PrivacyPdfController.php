<?php
// ============================================================
// app/Http/Controllers/PrivacyPdfController.php
//
// Generates a downloadable PDF of the Privacy & Refund Policy.
// Uses barryvdh/laravel-dompdf (install: composer require barryvdh/laravel-dompdf)
//
// Route to add in routes/web.php:
//   Route::get('/privacy/pdf', [PrivacyPdfController::class, 'download'])->name('privacy.pdf');
// ============================================================

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PrivacyPdfController extends Controller
{
    public function download()
    {
        // Load the dedicated PDF blade view (see privacy-policy-pdf.blade.php)
        $pdf = Pdf::loadView('privacy-policy-pdf')
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont'       => 'DejaVu Sans',  // supports ₹ and Unicode
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'   => false,           // no external images (security)
                'dpi'               => 150,
                'defaultPaperSize'  => 'a4',
            ]);

        return $pdf->download('GSTpilot-Privacy-Refund-Policy.pdf');
    }
}
