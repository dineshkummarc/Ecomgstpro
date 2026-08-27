<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class GstrBatch extends Model
{
    protected $table    = 'gstr_batches';
    protected $fillable = [
        'user_id', 'batch_id', 'client_id', 'client_name', 'client_gstin',
        'period', 'platforms', 'transaction_count',
        'taxable_value', 'total_tax',
        // ← FIX: yeh 4 fields GstrUploadController::computeB2CS() me
        // $updateData array ke through save kiye jaate hain, lekin
        // $fillable me na hone ki wajah se Laravel Mass Assignment
        // Protection inhe chup-chaap DROP kar raha tha (koi error nahi
        // aata) — isi wajah se doc_issue_override / hsn_override kabhi
        // DB me save hi nahi ho paa rahe the, aur GstrSummaryController
        // ko hamesha null milta tha (fallback message dikhta rehta tha).
        // filing_period / frequency bhi isi wajah se save nahi ho rahe
        // the (quarterly clients ke liye zaroori hain).
        'filing_period',
        'frequency',
        'doc_issue_override',
        'hsn_override',
    ];
}
