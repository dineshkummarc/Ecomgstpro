<?php

namespace App\Http\Controllers;

use App\Models\GstrTransaction;
use App\Parsers\MeeshoParserService;
use App\Models\GstrBatch;
use App\Parsers\BaseParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class GstrSummaryController extends Controller
{
    // HSN section is kept OUT of the portal-upload JSON until a portal-confirmed
    // schema sample is available. buildHsnUploadArray() is dormant but kept ready.
    private const INCLUDE_HSN_IN_UPLOAD_JSON = true;

    private function hasNegativeAmounts($t): bool
    {
        return (float)($t->taxable_value ?? 0) < 0
            || (float)($t->igst_amount   ?? 0) < 0
            || (float)($t->cgst_amount   ?? 0) < 0
            || (float)($t->sgst_amount   ?? 0) < 0;
    }

    private function b2bExclusionKey(string $ctin, string $inum): string
    {
        return strtoupper(trim($ctin)) . '|' . trim($inum);
    }

    private function isManuallyExcludedB2B($t): bool
    {
        $excluded = session('gstr_b2b_exclusions', []);
        if (empty($excluded)) return false;

        $key = $this->b2bExclusionKey(
            $t->buyer_gstin ?? '',
            trim($t->invoice_number ?? ('INV-' . $t->id))
        );
        return in_array($key, $excluded, true);
    }

    // ════════════════════════════════════════════════════════════════════════
    //  ← NEW — FILING FREQUENCY HELPER (Monthly vs Quarterly/QRMP)
    //
    //  Reads $client['filing_frequency'] (set to 'monthly' or 'quarterly').
    //  If the key is absent (older sessions/batches that never set it), we
    //  default to 'monthly' — i.e. 100% backward compatible, fp generation
    //  behaves exactly as before unless a client is explicitly marked
    //  quarterly.
    // ════════════════════════════════════════════════════════════════════════

    private function isQuarterlyClient(array $client): bool
    {
        return strtolower(trim($client['filing_frequency'] ?? 'monthly')) === 'quarterly';
    }

    // ════════════════════════════════════════════════════════════════════════
    //  ← NEW — MAP PERIOD TO CORRECT GST-PORTAL RETURN PERIOD (fp)
    //
    //  Monthly filers: fp = the transaction month itself, unchanged from
    //  before (e.g. "04/2026" -> "042026").
    //
    //  Quarterly (QRMP) filers: the GST portal's GSTR-1 return period
    //  dropdown ONLY has quarter-end months (Jun/Sep/Dec/Mar) — there is no
    //  standalone "April 2026" return period for a QRMP taxpayer. So for
    //  quarterly clients we remap whatever month the data belongs to, to
    //  that quarter's last month, keeping the year as-is:
    //      Apr/May/Jun -> Jun
    //      Jul/Aug/Sep -> Sep
    //      Oct/Nov/Dec -> Dec
    //      Jan/Feb/Mar -> Mar
    //  This ONLY changes the fp label sent to the portal — it does NOT
    //  touch any transaction data, amounts, or grouping logic anywhere else.
    // ════════════════════════════════════════════════════════════════════════

    private function mapToQuarterEndFp(string $period, bool $isQuarterly): string
    {
        $clean = str_replace('/', '', $period);

        if (!$isQuarterly || strlen($clean) < 6 || !preg_match('/^\d{6}$/', $clean)) {
            return $clean;
        }

        $month = (int)substr($clean, 0, 2);
        $year  = substr($clean, 2, 4);

        $quarterEndMonth = match (true) {
            $month >= 4  && $month <= 6  => '06', // Apr-Jun
            $month >= 7  && $month <= 9  => '09', // Jul-Sep
            $month >= 10 && $month <= 12 => '12', // Oct-Dec
            default                      => '03', // Jan-Mar
        };

        return $quarterEndMonth . $year;
    }

    // ════════════════════════════════════════════════════════════════════════
    //  MAIN SUMMARY VIEW
    // ════════════════════════════════════════════════════════════════════════

    public function index()
    {
        $client  = session('gstr_selected_client');
        $batchId = session('gstr_batch_id');

        if (!$client || !$batchId) {
            return redirect()->route('gstr.upload.index')
                             ->with('error', 'Please upload data first');
        }

        $transactions = GstrTransaction::where('user_id', Auth::id())
            ->where('batch_id', $batchId)
            ->where('status', 'processed')
            ->orderBy('place_of_supply')
            ->get();

        if ($transactions->isEmpty()) {
            return redirect()->route('gstr.upload.index')
                             ->with('error', 'No transactions found');
        }

        $period          = $transactions->first()->period;
        $gstin           = $client['gstin'] ?? '';
        $isQuarterly     = $this->isQuarterlyClient($client);
        $summary         = $this->computeSummary($transactions, $gstin);
        $b2bData         = $this->computeB2B($transactions, $gstin);
        $jsonData        = $this->generateJSON($gstin, $period, $summary, $b2bData, $transactions, $isQuarterly);
        $docIssue        = $this->computeDocIssue($transactions, $gstin);
        $hsnData         = $this->computeHsnSummary($transactions);
        $stateSummary    = $this->computeStateSummary($transactions);
        $excludedReturns = $this->computeExcludedReturns($transactions);
        $excludedB2BInvoices = $this->computeExcludedB2BInvoices($transactions);

        return view('summary', [
            'client'               => $client,
            'period'               => $period,
            'summary'              => $summary,
            'b2bData'              => $b2bData,
            'detailedTransactions' => $this->getDetailedTransactions($transactions),
            'jsonData'             => $jsonData,
            'docIssue'             => $docIssue,
            'hsnData'              => $hsnData,
            'stateSummary'         => $stateSummary,
            'excludedReturns'      => $excludedReturns,
            'excludedB2BInvoices'  => $excludedB2BInvoices,
            'batchId'              => $batchId,
            'transactionCount'     => $transactions->count(),
            'b2bCount'             => collect($b2bData)->sum(fn($p) => count($p['inv'])),
        ]);
    }

    // ════════════════════════════════════════════════════════════════════════
    //  AJAX — Manual row edit
    //  POST /gstr/summary/edit-row
    //  Body: { key: "27_18", txval, iamt, camt, samt }
    // ════════════════════════════════════════════════════════════════════════

    public function editRow(Request $request)
    {
        $validated = $request->validate([
            'key'   => 'required|string',
            'txval' => 'required|numeric',
            'iamt'  => 'required|numeric',
            'camt'  => 'required|numeric',
            'samt'  => 'required|numeric',
        ]);

        $overrides = session('gstr_b2cs_overrides', []);
        $overrides[$validated['key']] = [
            'txval' => round((float)$validated['txval'], 2),
            'iamt'  => round((float)$validated['iamt'],  2),
            'camt'  => round((float)$validated['camt'],  2),
            'samt'  => round((float)$validated['samt'],  2),
        ];
        session(['gstr_b2cs_overrides' => $overrides]);

        $client  = session('gstr_selected_client');
        $batchId = session('gstr_batch_id');

        $transactions = GstrTransaction::where('user_id', Auth::id())
            ->where('batch_id', $batchId)
            ->where('status', 'processed')
            ->get();

        $gstin       = $client['gstin'] ?? '';
        $isQuarterly = $this->isQuarterlyClient($client ?? []);
        $summary     = $this->computeSummary($transactions, $gstin);
        $b2bData     = $this->computeB2B($transactions, $gstin);
        $period      = $transactions->first()->period;
        $jsonData    = $this->generateJSON($gstin, $period, $summary, $b2bData, $transactions, $isQuarterly);

        return response()->json([
            'success'  => true,
            'summary'  => $summary,
            'jsonData' => $jsonData,
            'totals'   => [
                'txval' => round(array_sum(array_column($summary, 'txval')), 2),
                'iamt'  => round(array_sum(array_column($summary, 'iamt')),  2),
                'camt'  => round(array_sum(array_column($summary, 'camt')),  2),
                'samt'  => round(array_sum(array_column($summary, 'samt')),  2),
            ],
        ]);
    }

    // ════════════════════════════════════════════════════════════════════════
    //  B2B / B2C SPLIT HELPER
    //  Single source of truth for whether a transaction belongs in B2B or
    //  B2CS. Decision is based ONLY on whether buyer_gstin is a valid GSTIN.
    // ════════════════════════════════════════════════════════════════════════

    private function isB2BTransaction($t): bool
    {
        return BaseParser::isValidGstin($t->buyer_gstin ?? null);
    }

    private function sellerStateCodeFromGstin(string $gstin): string
    {
        $g = strtoupper(trim($gstin));
        return (strlen($g) >= 2 && preg_match('/^\d{2}/', $g)) ? substr($g, 0, 2) : '';
    }

    private function sanitizeDocNumber(string $val): string
    {
        $clean = preg_replace('/[^A-Za-z0-9\/\-]/', '', $val);
        return substr($clean, 0, 16);
    }

    // ════════════════════════════════════════════════════════════════════════
    //  COMPUTE B2CS SUMMARY
    //
    //  All non-B2B transactions (invalid/missing buyer GSTIN), grouped by
    //  POS + rate. Every transaction is included as-is — sale and return
    //  rows for the same state/rate net out naturally when summed, so this
    //  total always matches the Transactions/State Summary tabs.
    //
    //  sply_ty (INTRA/INTER) is decided by comparing the client GSTIN's own
    //  state code against each row's POS (not by trusting the parser's
    //  upstream igst/cgst split), so the JSON never generates an invalid
    //  "INTRA + POS != supplier state" combination.
    // ════════════════════════════════════════════════════════════════════════

    private function computeSummary($transactions, string $gstin = ''): array
    {
        $overrides       = session('gstr_b2cs_overrides', []);
        $sellerStateCode = $this->sellerStateCodeFromGstin($gstin);

        $b2cTransactions = $transactions->filter(
            fn($t) => !$this->isB2BTransaction($t)
        );

        return $b2cTransactions
            ->groupBy(fn($t) => $this->extractStateCode($t->place_of_supply) . '_' . $t->gst_rate)
            ->map(function ($group, $key) use ($overrides, $sellerStateCode) {
                $first = $group->first();
                $pos   = $this->extractStateCode($first->place_of_supply);

                $txval    = round($group->sum('taxable_value'), 2);
                $igstSum  = round($group->sum('igst_amount'), 2);
                $cgstSum  = round($group->sum('cgst_amount'), 2);
                $sgstSum  = round($group->sum('sgst_amount'), 2);
                $totalTax = round($igstSum + $cgstSum + $sgstSum, 2);

                if ($sellerStateCode !== '') {
                    $isIntra = ($pos === $sellerStateCode);

                    if ($isIntra) {
                        $iamt = 0.0;
                        $camt = round($totalTax / 2, 2);
                        $samt = round($totalTax - $camt, 2);
                    } else {
                        $iamt = $totalTax;
                        $camt = 0.0;
                        $samt = 0.0;
                    }
                } else {
                    $isIntra = ($igstSum == 0);
                    $iamt    = $igstSum;
                    $camt    = $cgstSum;
                    $samt    = $sgstSum;
                }

                $row = [
                    'key'     => $key,
                    'pos'     => $pos,
                    'rt'      => (float)$first->gst_rate,
                    'sply_ty' => $isIntra ? 'INTRA' : 'INTER',
                    'txval'   => $txval,
                    'iamt'    => round($iamt, 2),
                    'camt'    => round($camt, 2),
                    'samt'    => round($samt, 2),
                    'csamt'   => 0,
                    'count'   => $group->count(),
                    'edited'  => false,
                ];

                if (isset($overrides[$key])) {
                    $row['txval']  = $overrides[$key]['txval'];
                    $row['iamt']   = $overrides[$key]['iamt'];
                    $row['camt']   = $overrides[$key]['camt'];
                    $row['samt']   = $overrides[$key]['samt'];
                    $row['edited'] = true;
                }

                return $row;
            })
            ->values()
            ->toArray();
    }

    // ════════════════════════════════════════════════════════════════════════
    //  COMPUTE B2B
    //
    //  All B2B transactions (valid buyer GSTIN, not manually excluded),
    //  grouped by buyer GSTIN then by invoice number (so multi-item orders
    //  from the same invoice merge into a single 'inv' entry — required to
    //  avoid "Duplicate Invoice number" portal errors).
    //
    //  sply_ty is decided the same way as computeSummary(): POS vs seller's
    //  own state code, so invoice-level tax split is always correct
    //  regardless of what the upstream parser put in igst/cgst/sgst.
    // ════════════════════════════════════════════════════════════════════════

    private function computeB2B($transactions, string $gstin = ''): array
    {
        $sellerStateCode = $this->sellerStateCodeFromGstin($gstin);

        $b2bTrans = $transactions->filter(
            fn($t) => $this->isB2BTransaction($t) && !$this->isManuallyExcludedB2B($t)
        );
        if ($b2bTrans->isEmpty()) return [];

        return $b2bTrans
            ->groupBy(fn($t) => strtoupper(trim($t->buyer_gstin)))
            ->map(function ($group, $buyerGstin) use ($sellerStateCode) {
                $invList = $group
                    ->groupBy(fn($t) => trim($t->invoice_number ?? ('INV-' . $t->id)))
                    ->map(function ($invGroup, $inum) use ($sellerStateCode) {
                        $first = $invGroup->first();
                        $pos   = $this->extractStateCode($first->place_of_supply);

                        $isIntra = ($sellerStateCode !== '') ? ($pos === $sellerStateCode) : null;

                        $itms = $invGroup
                            ->groupBy(fn($t) => (float)$t->gst_rate)
                            ->values()
                            ->map(function ($rateGroup, $idx) use ($isIntra) {
                                $txval    = round($rateGroup->sum('taxable_value'), 2);
                                $totalTax = round(
                                    $rateGroup->sum('igst_amount')
                                    + $rateGroup->sum('cgst_amount')
                                    + $rateGroup->sum('sgst_amount'),
                                    2
                                );

                                if ($isIntra === true) {
                                    $iamt = 0.0;
                                    $camt = round($totalTax / 2, 2);
                                    $samt = round($totalTax - $camt, 2);
                                } elseif ($isIntra === false) {
                                    $iamt = $totalTax;
                                    $camt = 0.0;
                                    $samt = 0.0;
                                } else {
                                    $iamt = round($rateGroup->sum('igst_amount'), 2);
                                    $camt = round($rateGroup->sum('cgst_amount'), 2);
                                    $samt = round($rateGroup->sum('sgst_amount'), 2);
                                }

                                return [
                                    'num'     => $idx + 1,
                                    // Field order matches confirmed-working reference:
                                    // txval, rt, iamt, camt, samt, csamt
                                    'itm_det' => [
                                        'txval' => $txval,
                                        'rt'    => (float)$rateGroup->first()->gst_rate,
                                        'iamt'  => round($iamt, 2),
                                        'camt'  => round($camt, 2),
                                        'samt'  => round($samt, 2),
                                        'csamt' => 0,
                                    ],
                                ];
                            })
                            ->values()
                            ->toArray();

                        $val = round(
                            collect($itms)->sum(fn($i) =>
                                $i['itm_det']['txval'] + $i['itm_det']['iamt']
                                + $i['itm_det']['camt'] + $i['itm_det']['samt']
                            ),
                            2
                        );

                        return [
                            'inum'    => $inum,
                            'idt'     => $first->invoice_date
                                       ? \Carbon\Carbon::parse($first->invoice_date)->format('d-m-Y')
                                       : now()->format('d-m-Y'),
                            'val'     => $val,
                            'pos'     => $pos,
                            'rchrg'   => 'N',
                            'inv_typ' => 'R',
                            'itms'    => $itms,
                        ];
                    })
                    ->values()
                    ->toArray();

                return ['ctin' => $buyerGstin, 'inv' => $invList];
            })
            ->values()
            ->toArray();
    }

    // ════════════════════════════════════════════════════════════════════════
    //  EXCLUDED RETURN/REFUND ROWS (negative amounts) — reference only.
    //  These rows are NOT removed from B2B/B2CS/Summary totals, and (as of
    //  this fix) they are no longer stripped from the upload JSON either —
    //  the confirmed-working portal reference file keeps negative-net
    //  b2cs rows as-is (see generateJSON()). This list still exists purely
    //  so the user can see which individual invoices were negative-value
    //  (returns), in case a manual CDNR entry is wanted on the portal.
    // ════════════════════════════════════════════════════════════════════════

    private function computeExcludedReturns($transactions): array
    {
        $negativeTx = $transactions->filter(fn($t) => $this->hasNegativeAmounts($t));
        if ($negativeTx->isEmpty()) return [];

        return $negativeTx
            ->map(function ($t) {
                $igst  = round((float)($t->igst_amount ?? 0), 2);
                $cgst  = round((float)($t->cgst_amount ?? 0), 2);
                $sgst  = round((float)($t->sgst_amount ?? 0), 2);
                $txval = round((float)($t->taxable_value ?? 0), 2);

                return [
                    'invoice_number' => $t->invoice_number ?? ('ID-' . $t->id),
                    'invoice_date'   => $t->invoice_date
                                       ? \Carbon\Carbon::parse($t->invoice_date)->format('d-m-Y')
                                       : null,
                    'buyer_gstin'    => $t->buyer_gstin ?? null,
                    'is_b2b'         => $this->isB2BTransaction($t),
                    'pos'            => $this->extractStateCode($t->place_of_supply),
                    'rate'           => (float)$t->gst_rate,
                    'txval'          => $txval,
                    'iamt'           => $igst,
                    'camt'           => $cgst,
                    'samt'           => $sgst,
                    'total'          => round($txval + $igst + $cgst + $sgst, 2),
                ];
            })
            ->values()
            ->toArray();
    }

    // ════════════════════════════════════════════════════════════════════════
    //  MANUALLY-EXCLUDED B2B INVOICES (e.g. cancelled buyer GSTIN)
    //  User-driven exclusion — see isManuallyExcludedB2B() / computeB2B().
    // ════════════════════════════════════════════════════════════════════════

    private function computeExcludedB2BInvoices($transactions): array
    {
        $excludedTx = $transactions->filter(
            fn($t) => $this->isB2BTransaction($t) && $this->isManuallyExcludedB2B($t)
        );
        if ($excludedTx->isEmpty()) return [];

        return $excludedTx
            ->groupBy(fn($t) => trim($t->invoice_number ?? ('INV-' . $t->id)))
            ->map(function ($group) {
                $first = $group->first();
                $txval = round($group->sum('taxable_value'), 2);
                $igst  = round($group->sum('igst_amount'), 2);
                $cgst  = round($group->sum('cgst_amount'), 2);
                $sgst  = round($group->sum('sgst_amount'), 2);

                return [
                    'invoice_number' => $first->invoice_number ?? ('INV-' . $first->id),
                    'invoice_date'   => $first->invoice_date
                                       ? \Carbon\Carbon::parse($first->invoice_date)->format('d-m-Y')
                                       : null,
                    'buyer_gstin'    => strtoupper(trim($first->buyer_gstin)),
                    'pos'            => $this->extractStateCode($first->place_of_supply),
                    'txval'          => $txval,
                    'iamt'           => $igst,
                    'camt'           => $cgst,
                    'samt'           => $sgst,
                    'total'          => round($txval + $igst + $cgst + $sgst, 2),
                ];
            })
            ->values()
            ->toArray();
    }

    // ════════════════════════════════════════════════════════════════════════
    //  AJAX — Manually exclude / re-include a B2B invoice
    //  POST /gstr/summary/exclude-b2b-invoice   Body: { ctin, inum }
    //  POST /gstr/summary/include-b2b-invoice   Body: { ctin, inum }
    // ════════════════════════════════════════════════════════════════════════

    public function excludeB2BInvoice(Request $request)
    {
        $validated = $request->validate([
            'ctin' => 'required|string',
            'inum' => 'required|string',
        ]);

        $key = $this->b2bExclusionKey($validated['ctin'], $validated['inum']);
        $excluded = session('gstr_b2b_exclusions', []);
        if (!in_array($key, $excluded, true)) {
            $excluded[] = $key;
        }
        session(['gstr_b2b_exclusions' => $excluded]);

        return $this->getSummaryData();
    }

    public function includeB2BInvoice(Request $request)
    {
        $validated = $request->validate([
            'ctin' => 'required|string',
            'inum' => 'required|string',
        ]);

        $key = $this->b2bExclusionKey($validated['ctin'], $validated['inum']);
        $excluded = session('gstr_b2b_exclusions', []);
        $excluded = array_values(array_filter($excluded, fn($k) => $k !== $key));
        session(['gstr_b2b_exclusions' => $excluded]);

        return $this->getSummaryData();
    }

    // ════════════════════════════════════════════════════════════════════════
    //  DETAILED TRANSACTIONS — Excel detail tab / Transactions tab.
    //  Every row is included as-is (sale + return rows net out naturally).
    // ════════════════════════════════════════════════════════════════════════

    private function getDetailedTransactions($transactions): array
    {
        return $transactions
            ->values()
            ->map(function ($t, $idx) {
                $isB2B = $this->isB2BTransaction($t);
                return [
                    'idx'      => $idx + 1,
                    'platform' => ucfirst($t->platform ?? 'Unknown'),
                    'period'   => $t->period,
                    'state'    => $this->extractStateCode($t->place_of_supply),
                    'nature'   => ($t->igst_amount != 0) ? 'INTER' : 'INTRA',
                    'hsn'      => $t->hsn_code ?? null,
                    'qty'      => (float)($t->quantity ?? 1),
                    'taxable'  => round($t->taxable_value, 2),
                    'rate'     => (float)$t->gst_rate,
                    'cgst'     => round($t->cgst_amount ?? 0, 2),
                    'sgst'     => round($t->sgst_amount ?? 0, 2),
                    'igst'     => round($t->igst_amount ?? 0, 2),
                    'invoice'  => round($t->taxable_value + ($t->igst_amount ?? 0) + ($t->cgst_amount ?? 0) + ($t->sgst_amount ?? 0), 2),
                    'invoice_number' => $t->invoice_number ?? null,
                    'tcs' => 0,
                    'is_b2b'   => $isB2B,
                    'is_return' => $this->hasNegativeAmounts($t),
                ];
            })->toArray();
    }

    // ════════════════════════════════════════════════════════════════════════
    //  DOC ISSUED SUMMARY — computed from the CURRENT BATCH only (scoped by
    //  batch_id), so uploading a single platform (e.g. Snapdeal) never pulls
    //  in invoice series from a different platform/period's old batch.
    //
    //  FIXED (v3): auto-calculate doc-issue ranges from every processed
    //  transaction in the batch (all platforms) first, then — if a direct-
    //  read override exists — MERGE it in additively instead of replacing
    //  the whole result, so every platform's invoice numbers show up
    //  together.
    //
    //  FIXED (v4, this pass): added the mandatory 'doc_typ' field to the
    //  doc_det wrapper. The confirmed-working portal reference always
    //  carries "doc_typ":"Invoices for outward supply" alongside doc_num
    //  for this table — it was missing before, which is schema-invalid on
    //  the portal side.
    // ════════════════════════════════════════════════════════════════════════

    private function computeDocIssue(Collection $transactions, string $gstin): array
    {
        // For Meesho, use the new service to compute from current batch transactions
        $hasMeesho = $transactions->contains(fn($t) => str_contains(strtolower($t->platform ?? ''), 'meesho'));
        if ($hasMeesho) {
            return MeeshoParserService::computeDocIssueFromTransactions($transactions->toArray());
        }

        // Always auto-calculate first, from every processed transaction in
        // this batch (i.e. every platform uploaded so far) — this is the
        // same prefix/range logic as before, just extracted into its own
        // method so it can be merged with any override instead of being
        // short-circuited by it.
        $autoDocs = $this->autoCalculateDocIssue($transactions, $gstin);

        // If a direct-read override exists for this batch (e.g. Flipkart's
        // own GSTR-1 Ready doc-issue sheet), merge it WITH the
        // auto-calculated docs instead of replacing the whole result.
        $batchId = session('gstr_batch_id');
        if ($batchId) {
            $batch = GstrBatch::where('user_id', Auth::id())
                ->where('batch_id', $batchId)
                ->first();
            if ($batch && !empty($batch->doc_issue_override)) {
                $decoded = json_decode($batch->doc_issue_override, true);
                $overrideDocs = $decoded['doc_det'][0]['docs'] ?? [];
                if (!empty($overrideDocs)) {
                    $autoDocs = $this->mergeDocIssueLists($overrideDocs, $autoDocs);
                }
            }
        }

        if (empty($autoDocs)) return [];

        return [
            'doc_det' => [
                [
                    'doc_num' => 1,
                    'doc_typ' => 'Invoices for outward supply',
                    'docs'    => $autoDocs,
                ],
            ],
        ];
    }

    // ════════════════════════════════════════════════════════════════════════
    //  ← auto-calculates document-issued number ranges from invoice numbers
    //  found across every processed transaction in the current batch.
    //  Returns a plain 'docs' array (not wrapped in 'doc_det') so it can be
    //  merged with any override in computeDocIssue().
    // ════════════════════════════════════════════════════════════════════════

    private function autoCalculateDocIssue(Collection $transactions, string $gstin): array
    {
        if (trim($gstin) === '') return [];

        $batchId = session('gstr_batch_id');

        // Query batch_id se scoped hai, taaki current upload ke alawa kisi
        // aur platform/period ke invoice numbers is batch ki Document
        // Issued Summary me leak na ho.
        $query = GstrTransaction::where('user_id', Auth::id())
            ->where('gstin', strtoupper(trim($gstin)))
            ->where('status', 'processed')
            ->whereNotNull('invoice_number');

        if ($batchId) {
            $query->where('batch_id', $batchId);
        }

        $allInvoiceNumbers = $query
            ->pluck('invoice_number')
            ->map(fn($inv) => trim((string)$inv))
            ->filter(fn($inv) => $inv !== '')
            ->unique()
            ->values();

        $series = [];

        foreach ($allInvoiceNumbers as $inv) {
            if (!preg_match('/^(.*?)(\d+)$/', $inv, $m)) {
                continue;
            }

            $prefix = $m[1];
            $numStr = $m[2];
            $num    = (int)$numStr;
            $width  = strlen($numStr);

            if (!isset($series[$prefix])) {
                $series[$prefix] = ['items' => []];
            }

            $series[$prefix]['items'][$num] = max($series[$prefix]['items'][$num] ?? 0, $width);
        }

        if (empty($series)) return [];

        $docs = [];
        $sr   = 1;

        // Ek prefix = ek hi row. Min-to-max poora range liya jata hai,
        // beech me jitne numbers uploaded data me nahi mile wo 'cancel'
        // me count ho jate hain — har prefix ka sirf ek row banega.
        foreach ($series as $prefix => $data) {
            $items = $data['items'];
            $nums  = array_keys($items);
            if (empty($nums)) continue;
            sort($nums);

            $min    = $nums[0];
            $max    = end($nums);
            $pad    = max($items);
            $totnum = $max - $min + 1;
            $actual = count($nums);
            $cancel = max(0, $totnum - $actual);

            $docs[] = [
                'num'       => $sr,
                'from'      => $this->sanitizeDocNumber($prefix . str_pad((string)$min, $pad, '0', STR_PAD_LEFT)),
                'to'        => $this->sanitizeDocNumber($prefix . str_pad((string)$max, $pad, '0', STR_PAD_LEFT)),
                'totnum'    => $totnum,
                'cancel'    => $cancel,
                'net_issue' => $actual,
            ];
            $sr++;
        }

        return $docs;
    }

    // ════════════════════════════════════════════════════════════════════════
    //  ← merges a direct-read override's docs with auto-calculated docs,
    //  WITHOUT dropping either side. Dedupe is by exact from+to range match
    //  (if both sides already agree on the same range, the override entry
    //  wins since it came straight from the source file); everything else
    //  from both lists is kept. 'num' is renumbered sequentially at the end
    //  so the sr-no column stays clean and continuous.
    // ════════════════════════════════════════════════════════════════════════

    private function mergeDocIssueLists(array $overrideDocs, array $autoDocs): array
    {
        $seen   = [];
        $merged = [];

        foreach ($overrideDocs as $d) {
            $key = ($d['from'] ?? '') . '|' . ($d['to'] ?? '');
            if (isset($seen[$key])) continue;
            $seen[$key] = true;
            $merged[]   = $d;
        }

        foreach ($autoDocs as $d) {
            $key = ($d['from'] ?? '') . '|' . ($d['to'] ?? '');
            if (isset($seen[$key])) continue;
            $seen[$key] = true;
            $merged[]   = $d;
        }

        foreach ($merged as $i => &$d) {
            $d['num'] = $i + 1;
        }
        unset($d);

        return $merged;
    }

    // ════════════════════════════════════════════════════════════════════════
    //  HSN SUMMARY (UI tab — includes 'desc' + 'val' for display).
    //  See buildHsnUploadArray() for the portal-schema-matching version.
    //
    //  FIXED (this pass) — two bugs were causing the GST-portal error
    //  "Combination of HSN, Uqc and Rate cannot be repeatable":
    //
    //  1) A direct-read Section-12 override was being used AS-IS, with no
    //     filter for the client's own GSTIN. Section-12 sheets (e.g.
    //     Flipkart's) can carry rows for MULTIPLE GSTINs of the same PAN
    //     (different states) — a row belonging to a different GSTIN was
    //     leaking into this client's HSN summary.
    //
    //  2) The override REPLACED the whole computed HSN summary instead of
    //     being merged into it — so a second platform's Section-12 sheet
    //     (e.g. Snapdeal) uploaded into the same batch either got dropped
    //     entirely, or produced a second, un-aggregated row for the same
    //     HSN + rate + UQC combination, which is exactly what the portal
    //     rejects.
    //
    //  Fix: override rows are now (a) filtered to the client's own GSTIN,
    //  and (b) merged + aggregated with the transaction-computed rows via
    //  mergeAndAggregateHsn(), keyed by hsn_sc + uqc + rt (hsnGroupKey()),
    //  so every combination reaches the portal exactly once.
    // ════════════════════════════════════════════════════════════════════════

    private function computeHsnSummary($transactions): array
    {
        $client   = session('gstr_selected_client');
        $ownGstin = strtoupper(trim($client['gstin'] ?? ''));

        $b2bTx = $transactions->filter(fn($t) => $this->isB2BTransaction($t));
        $b2cTx = $transactions->filter(fn($t) => !$this->isB2BTransaction($t));

        $build = function (Collection $tx): array {
            return $tx
                ->filter(fn($t) => !empty($t->hsn_code))
                ->groupBy(fn($t) => $this->hsnGroupKey($t->hsn_code, $t->gst_rate))
                ->values()
                ->map(function ($group, $idx) {
                    $first = $group->first();
                    $txval = round($group->sum('taxable_value'), 2);
                    $iamt  = round($group->sum('igst_amount'), 2);
                    $camt  = round($group->sum('cgst_amount'), 2);
                    $samt  = round($group->sum('sgst_amount'), 2);
                    $csamt = round($group->sum('cess_amount'), 2);

                    return [
                        'num'    => $idx + 1,
                        'hsn_sc' => trim((string)$first->hsn_code),
                        'desc'   => $first->product_description ?? 'Goods',
                        'uqc'    => 'NOS',
                        'qty'    => $this->jsonNum($group->sum('quantity')),
                        'rt'     => $this->jsonNum($first->gst_rate ?? 0),
                        'txval'  => $this->jsonNum($txval),
                        'val'    => $this->jsonNum($txval + $iamt + $camt + $samt),
                        'iamt'   => $this->jsonNum($iamt),
                        'camt'   => $this->jsonNum($camt),
                        'samt'   => $this->jsonNum($samt),
                        'csamt'  => $this->jsonNum($csamt),
                    ];
                })
                ->values()
                ->toArray();
        };

        $autoB2b = $build($b2bTx);
        $autoB2c = $build($b2cTx);

        // ← agar Section 12 se direct-read HSN override maujood hai, use
        // client's own GSTIN se filter karke aur transaction-computed
        // rows ke saath merge/aggregate karke lagao — poori tarah replace
        // nahi karna (varna dusre platform ka data drop ho jata hai aur
        // duplicate HSN+rate combination portal ko bhej dete hain).
        $batchId = session('gstr_batch_id');
        if ($batchId && $ownGstin !== '') {
            $batch = GstrBatch::where('user_id', Auth::id())
                ->where('batch_id', $batchId)
                ->first();

            if ($batch && !empty($batch->hsn_override)) {
                $decoded = json_decode($batch->hsn_override, true) ?: [];

                $overrideForClient = collect($decoded)->filter(function ($row) use ($ownGstin) {
                    $rowGstin = strtoupper(trim($row['gstin'] ?? $row['seller_gstin'] ?? ''));
                    // Agar override row me gstin field hi nahi hai (purana
                    // single-GSTIN format), use rakho; warna sirf client
                    // ki apni GSTIN wali row hi allow karo.
                    return $rowGstin === '' || $rowGstin === $ownGstin;
                })->values()->toArray();

                if (!empty($overrideForClient)) {
                    $autoB2c = $this->mergeAndAggregateHsn($autoB2c, $overrideForClient);
                }
            }
        }

        return [
            'flag'    => 'D',
            'hsn_b2b' => $autoB2b,
            'hsn_b2c' => $autoB2c,
        ];
    }

    // ════════════════════════════════════════════════════════════════════════
    //  ← NEW — Normalized group key for HSN aggregation. Trims the HSN
    //  code and treats a null/blank rate as 0, so "21069099_" (null rate)
    //  and "21069099_0" (explicit zero rate) never split into two separate
    //  rows — this is what caused the portal's "cannot be repeatable"
    //  error.
    // ════════════════════════════════════════════════════════════════════════

    private function hsnGroupKey($hsnCode, $rate): string
    {
        $code = trim((string)$hsnCode);
        $rt   = round((float)($rate ?? 0), 2);
        return $code . '_' . $rt . '_NOS';
    }

    // ════════════════════════════════════════════════════════════════════════
    //  ← NEW — Merges direct-read override HSN rows (from one or more
    //  platforms' Section-12 sheets) with the transaction-computed rows,
    //  aggregating anything that shares hsn_sc + uqc + rt into a single
    //  row (summing qty/txval/iamt/camt/samt/csamt), so the portal never
    //  receives a repeated HSN+UQC+Rate combination.
    // ════════════════════════════════════════════════════════════════════════

    private function mergeAndAggregateHsn(array $autoRows, array $overrideRows): array
    {
        $buckets = [];

        $addRow = function (array $r) use (&$buckets) {
            $key = $this->hsnGroupKey($r['hsn_sc'] ?? '', $r['rt'] ?? 0);
            if (!isset($buckets[$key])) {
                $buckets[$key] = [
                    'hsn_sc' => trim((string)($r['hsn_sc'] ?? '')),
                    'desc'   => $r['desc'] ?? 'Goods',
                    'uqc'    => 'NOS',
                    'rt'     => round((float)($r['rt'] ?? 0), 2),
                    'qty'    => 0.0, 'txval' => 0.0,
                    'iamt'   => 0.0, 'camt'  => 0.0,
                    'samt'   => 0.0, 'csamt' => 0.0,
                ];
            }
            $buckets[$key]['qty']   += (float)($r['qty']   ?? $r['quantity'] ?? 0);
            $buckets[$key]['txval'] += (float)($r['txval'] ?? $r['taxable_value'] ?? 0);
            $buckets[$key]['iamt']  += (float)($r['iamt']  ?? $r['igst_amount']  ?? 0);
            $buckets[$key]['camt']  += (float)($r['camt']  ?? $r['cgst_amount']  ?? 0);
            $buckets[$key]['samt']  += (float)($r['samt']  ?? $r['sgst_amount']  ?? 0);
            $buckets[$key]['csamt'] += (float)($r['csamt'] ?? $r['cess_amount']  ?? 0);
        };

        foreach ($autoRows as $r)     $addRow($r);
        foreach ($overrideRows as $r) $addRow($r);

        $out = [];
        $num = 1;
        foreach ($buckets as $b) {
            $txval = round($b['txval'], 2);
            $iamt  = round($b['iamt'], 2);
            $camt  = round($b['camt'], 2);
            $samt  = round($b['samt'], 2);

            $out[] = [
                'num'    => $num++,
                'hsn_sc' => $b['hsn_sc'],
                'desc'   => $b['desc'],
                'uqc'    => $b['uqc'],
                'qty'    => $this->jsonNum($b['qty']),
                'rt'     => $this->jsonNum($b['rt']),
                'txval'  => $this->jsonNum($txval),
                'val'    => $this->jsonNum($txval + $iamt + $camt + $samt),
                'iamt'   => $this->jsonNum($iamt),
                'camt'   => $this->jsonNum($camt),
                'samt'   => $this->jsonNum($samt),
                'csamt'  => $this->jsonNum(round($b['csamt'], 2)),
            ];
        }

        return $out;
    }

    // ════════════════════════════════════════════════════════════════════════
    //  HSN → GST-PORTAL UPLOAD FORMAT SANITIZER (dormant — see
    //  INCLUDE_HSN_IN_UPLOAD_JSON). Re-shapes computeHsnSummary()'s output
    //  into the portal-reference schema: no 'desc'/'val', field order
    //  num, hsn_sc, uqc, qty, rt, txval, iamt, samt, camt, csamt.
    // ════════════════════════════════════════════════════════════════════════

    //  FIXED (this pass) — two things were making hsn diverge from the
    //  confirmed-working reference:
    //  1) hsn_sc must be a numeric HSN/SAC code (4/6/8 digits). Some
    //     source rows have a non-numeric fallback (e.g. a bare product
    //     name like "Adapter") when the parser couldn't read a real HSN
    //     code — the portal rejects the whole file if it sees that, so
    //     such rows are dropped from the UPLOAD json only (they still
    //     show up in the UI hsnData tab so the user can see & fix them).
    //  2) 'num' now continues sequentially across hsn_b2c into hsn_b2b
    //     (e.g. 5 items in hsn_b2c -> hsn_b2b starts at 6), matching the
    //     reference file exactly, instead of each restarting at 1.
    private function buildHsnUploadArray(array $hsnData): array
    {
        $isValidHsn = fn($h) => (bool)preg_match('/^\d{4,8}$/', trim((string)($h['hsn_sc'] ?? '')));

        $cleanB2c = array_values(array_filter($hsnData['hsn_b2c'] ?? [], $isValidHsn));
        $cleanB2b = array_values(array_filter($hsnData['hsn_b2b'] ?? [], $isValidHsn));

        $sanitizeRow = function (array $h, int $num): array {
            return [
                'num'    => $num,
                'hsn_sc' => (string)$h['hsn_sc'],
                'uqc'    => $h['uqc'] ?? 'NOS',
                'qty'    => $this->jsonNum($h['qty']   ?? 0),
                'rt'     => $this->jsonNum($h['rt']    ?? 0),
                'txval'  => $this->jsonNum($h['txval'] ?? 0),
                'iamt'   => $this->jsonNum($h['iamt']  ?? 0),
                'samt'   => $this->jsonNum($h['samt']  ?? 0),
                'camt'   => $this->jsonNum($h['camt']  ?? 0),
                'csamt'  => $this->jsonNum($h['csamt'] ?? 0),
            ];
        };

        $num    = 1;
        $hsnB2c = [];
        foreach ($cleanB2c as $h) {
            $hsnB2c[] = $sanitizeRow($h, $num);
            $num++;
        }

        $hsnB2b = [];
        foreach ($cleanB2b as $h) {
            $hsnB2b[] = $sanitizeRow($h, $num);
            $num++;
        }

        return [
            'hsn_b2c' => $hsnB2c,
            'hsn_b2b' => $hsnB2b,
        ];
    }

    // ════════════════════════════════════════════════════════════════════════
    //  NUMBER SANITIZER FOR GST-PORTAL JSON — round first, then decide
    //  int-vs-float on the rounded value so whole numbers serialize as
    //  "18" instead of "18.0".
    // ════════════════════════════════════════════════════════════════════════

    private function jsonNum($val): int|float
    {
        $rounded = round((float)$val, 2);
        return ($rounded == (int)$rounded) ? (int)$rounded : $rounded;
    }

    // ════════════════════════════════════════════════════════════════════════
    //  GENERATE JSON — GSTR-1 API upload format.
    //
    //  FIXED (this pass) — two things were making the generated JSON
    //  diverge from the confirmed-working portal reference file:
    //
    //  1) b2cs negative-txval rows were being dropped entirely. The
    //     reference file keeps net-negative POS+rate groups as-is (e.g. a
    //     state/rate where returns outweighed sales that month shows up
    //     as a negative txval row) — same as how Summary/State Summary/
    //     Transactions tabs already treat returns (netted in, never
    //     dropped). B2CS now matches: every group from computeSummary()
    //     is included, full stop. (B2B is different: a single invoice's
    //     value can't legally be negative, so that per-invoice filter
    //     stays.)
    //
    //  2) top-level key order now matches the reference exactly:
    //     gstin, fp, b2b, b2cs, doc_issue, hsn.
    //
    //  $isQuarterly param (default false = monthly). When true, fp is
    //  remapped to the QRMP quarter-end month via mapToQuarterEndFp()
    //  instead of being the raw transaction month — unchanged from before.
    // ════════════════════════════════════════════════════════════════════════

    private function generateJSON(string $gstin, string $period, array $summary, array $b2bData, $transactions, bool $isQuarterly = false): array
    {
        $fp = $this->mapToQuarterEndFp($period, $isQuarterly);

        // Field order matches confirmed-working reference:
        // sply_ty, rt, typ, pos, txval, iamt, samt, camt, csamt
        // Every group is included as-is — negative-net groups are NOT
        // skipped (see doc-comment above).
        $b2csArray = [];
        foreach ($summary as $r) {
            $b2csArray[] = [
                'sply_ty' => $r['sply_ty'] ?? 'OE',
                'rt'      => $this->jsonNum($r['rt'] ?? 0),
                'typ'     => 'OE',
                'pos'     => str_pad((string)($r['pos'] ?? ''), 2, '0', STR_PAD_LEFT),
                'txval'   => $this->jsonNum($r['txval'] ?? 0),
                'iamt'    => $this->jsonNum($r['iamt'] ?? 0),
                'samt'    => $this->jsonNum($r['samt'] ?? 0),
                'camt'    => $this->jsonNum($r['camt'] ?? 0),
                'csamt'   => $this->jsonNum($r['csamt'] ?? 0),
            ];
        }

        // B2B — invoice-level value genuinely can't be negative OR zero on
        // a "regular" invoice type (an invoice that fully nets to 0, e.g.
        // a sale fully cancelled by a same-period return, isn't a valid
        // invoice to declare — it's excluded here the same way a
        // negative one is, instead of being sent through as val:0, which
        // the portal rejects).
        $sanB2b = [];
        if (!empty($b2bData)) {
            foreach ($b2bData as $party) {
                if (!BaseParser::isValidGstin($party['ctin'] ?? null)) {
                    continue;
                }

                $invList = [];
                foreach ($party['inv'] as $inv) {
                    if ((float)($inv['val'] ?? 0) <= 0) {
                        continue;
                    }

                    $invSan = $inv;
                    $invSan['val'] = $this->jsonNum($inv['val'] ?? 0);
                    $invSan['pos'] = str_pad((string)($inv['pos'] ?? ''), 2, '0', STR_PAD_LEFT);

                    if (!empty($invSan['itms']) && is_array($invSan['itms'])) {
                        foreach ($invSan['itms'] as &$itm) {
                            if (!empty($itm['itm_det'])) {
                                $det = $itm['itm_det'];
                                $itm['itm_det'] = [
                                    'txval' => $this->jsonNum($det['txval'] ?? 0),
                                    'rt'    => $this->jsonNum($det['rt']    ?? 0),
                                    'iamt'  => $this->jsonNum($det['iamt']  ?? 0),
                                    'camt'  => $this->jsonNum($det['camt']  ?? 0),
                                    'samt'  => $this->jsonNum($det['samt']  ?? 0),
                                    'csamt' => $this->jsonNum($det['csamt'] ?? 0),
                                ];
                            }
                        }
                        unset($itm);
                    }

                    $invList[] = $invSan;
                }

                if (!empty($invList)) {
                    $sanB2b[] = ['ctin' => strtoupper(trim($party['ctin'])), 'inv' => $invList];
                }
            }
        }

        $docIssue = $this->computeDocIssue($transactions, $gstin);

        $hsnJson = null;
        if (self::INCLUDE_HSN_IN_UPLOAD_JSON) {
            $hsnRaw = $this->computeHsnSummary($transactions);
            $hsnSan = $this->buildHsnUploadArray($hsnRaw);
            if (!empty($hsnSan['hsn_b2c']) || !empty($hsnSan['hsn_b2b'])) {
                $hsnJson = $hsnSan;
            }
        }

        // Top-level key order matches the confirmed-working portal
        // reference exactly: gstin, fp, b2b, b2cs, doc_issue, hsn.
        $json = [
            'gstin' => $gstin,
            'fp'    => $fp,
        ];

        if (!empty($sanB2b)) {
            $json['b2b'] = $sanB2b;
        }

        $json['b2cs'] = $b2csArray;

        if (!empty($docIssue)) {
            $json['doc_issue'] = $docIssue;
        }

        if ($hsnJson !== null) {
            $json['hsn'] = $hsnJson;
        }

        return $json;
    }

    // ════════════════════════════════════════════════════════════════════════
    //  STATE-WISE SUMMARY — every row included as-is; sale + return net out.
    // ════════════════════════════════════════════════════════════════════════

    private function computeStateSummary($transactions): array
    {
        $stateMap = $this->stateMap();

        return $transactions
            ->groupBy(function ($t) {
                $platform = strtolower(trim($t->platform ?? 'unknown'));
                $state    = $this->extractStateCode($t->place_of_supply);
                return $platform . '||' . $state;
            })
            ->map(function ($group) use ($stateMap) {
                $first     = $group->first();
                $stateCode = $this->extractStateCode($first->place_of_supply);

                $qty   = (int) $group->sum(fn($t) => (int)($t->quantity ?? 0));
                $txval = round($group->sum('taxable_value'), 2);
                $igst  = round($group->sum('igst_amount'), 2);
                $cgst  = round($group->sum('cgst_amount'), 2);
                $sgst  = round($group->sum('sgst_amount'), 2);
                $tax   = round($igst + $cgst + $sgst, 2);

                return [
                    'platform'      => ucfirst($first->platform ?? 'Unknown'),
                    'state_code'    => $stateCode,
                    'state_name'    => $stateMap[$stateCode] ?? $stateCode,
                    'txn_count'     => $group->count(),
                    'qty'           => $qty,
                    'txval'         => $txval,
                    'igst'          => $igst,
                    'cgst'          => $cgst,
                    'sgst'          => $sgst,
                    'total_tax'     => $tax,
                    'invoice_total' => round($txval + $tax, 2),
                ];
            })
            ->sortBy([['platform', 'asc'], ['state_name', 'asc']])
            ->values()
            ->toArray();
    }

    // ════════════════════════════════════════════════════════════════════════
    //  STATE-WISE SUMMARY — CSV download
    // ════════════════════════════════════════════════════════════════════════

    public function downloadStateSummaryCSV()
    {
        $client  = session('gstr_selected_client');
        $batchId = session('gstr_batch_id');
        if (!$client || !$batchId) return redirect()->route('gstr.upload.index');

        $transactions = GstrTransaction::where('user_id', Auth::id())
            ->where('batch_id', $batchId)
            ->where('status', 'processed')
            ->get();
        if ($transactions->isEmpty()) return redirect()->back()->with('error', 'No transactions found');

        $stateSummary = $this->computeStateSummary($transactions);
        $period       = $transactions->first()->period;

        $csv  = "Platform,State,Transactions,Qty,Taxable Value,CGST,SGST,IGST,Total Tax,Invoice Total\r\n";
        foreach ($stateSummary as $s) {
            $csv .= "{$s['platform']},{$s['state_name']},{$s['txn_count']},{$s['qty']},"
                  . "{$s['txval']},{$s['cgst']},{$s['sgst']},{$s['igst']},{$s['total_tax']},{$s['invoice_total']}\r\n";
        }

        $filename = "state_summary_{$client['gstin']}_{$this->safePeriod($period)}.csv";
        return Response::streamDownload(fn() => print($csv), $filename, ['Content-Type' => 'text/csv']);
    }

    // ════════════════════════════════════════════════════════════════════════
    //  EXCLUDED RETURNS — standalone download (CSV)
    // ════════════════════════════════════════════════════════════════════════

    public function downloadExcludedReturnsCSV()
    {
        $client  = session('gstr_selected_client');
        $batchId = session('gstr_batch_id');
        if (!$client || !$batchId) return redirect()->route('gstr.upload.index');

        $transactions = GstrTransaction::where('user_id', Auth::id())
            ->where('batch_id', $batchId)
            ->where('status', 'processed')
            ->get();
        if ($transactions->isEmpty()) return redirect()->back()->with('error', 'No transactions found');

        $excluded = $this->computeExcludedReturns($transactions);
        $period   = $transactions->first()->period;

        $csv = "Invoice Number,Invoice Date,Buyer GSTIN,Is B2B,POS,Rate,Taxable Value,IGST,CGST,SGST,Total\r\n";
        foreach ($excluded as $e) {
            $csv .= "{$e['invoice_number']},{$e['invoice_date']},{$e['buyer_gstin']},"
                  . ($e['is_b2b'] ? 'Yes' : 'No') . ",{$e['pos']},{$e['rate']},"
                  . "{$e['txval']},{$e['iamt']},{$e['camt']},{$e['samt']},{$e['total']}\r\n";
        }

        $filename = "excluded_returns_{$client['gstin']}_{$this->safePeriod($period)}.csv";
        return Response::streamDownload(fn() => print($csv), $filename, ['Content-Type' => 'text/csv']);
    }

    // ════════════════════════════════════════════════════════════════════════
    //  DOC ISSUED SUMMARY — standalone download (CSV)
    // ════════════════════════════════════════════════════════════════════════

    public function downloadDocIssueCSV()
    {
        $client  = session('gstr_selected_client');
        $batchId = session('gstr_batch_id');
        if (!$client || !$batchId) return redirect()->route('gstr.upload.index');

        $transactions = GstrTransaction::where('user_id', Auth::id())
            ->where('batch_id', $batchId)
            ->where('status', 'processed')
            ->get();
        if ($transactions->isEmpty()) return redirect()->back()->with('error', 'No transactions found');

        $docIssue = $this->computeDocIssue($transactions, $client['gstin'] ?? '');
        $period   = $transactions->first()->period;

        $csv = "Sr No,From,To,Total Number,Cancelled,Net Issued\r\n";
        foreach (($docIssue['doc_det'][0]['docs'] ?? []) as $d) {
            $csv .= "{$d['num']},{$d['from']},{$d['to']},{$d['totnum']},{$d['cancel']},{$d['net_issue']}\r\n";
        }

        $filename = "doc_issue_{$client['gstin']}_{$this->safePeriod($period)}.csv";
        return Response::streamDownload(fn() => print($csv), $filename, ['Content-Type' => 'text/csv']);
    }

    // ════════════════════════════════════════════════════════════════════════
    //  HELPERS
    // ════════════════════════════════════════════════════════════════════════

    private function extractStateCode(string $pos): string
    {
        $pos = trim($pos);
        if (preg_match('/^(\d{2})[\s\-]/', $pos, $m)) return $m[1];
        if (preg_match('/^(\d{2})$/',       $pos, $m)) return $m[1];
        return $pos;
    }

    private function safePeriod(string $period): string
    {
        return str_replace(['/', '\\'], '-', $period);
    }

    private function colRow(int $col, int $row): string
    {
        return Coordinate::stringFromColumnIndex($col) . $row;
    }

    private function stateMap(): array
    {
        return [
            '01'=>'Jammu & Kashmir',   '02'=>'Himachal Pradesh',
            '03'=>'Punjab',            '04'=>'Chandigarh',
            '05'=>'Uttarakhand',       '06'=>'Haryana',
            '07'=>'Delhi',             '08'=>'Rajasthan',
            '09'=>'Uttar Pradesh',     '10'=>'Bihar',
            '11'=>'Sikkim',            '12'=>'Arunachal Pradesh',
            '13'=>'Nagaland',          '14'=>'Manipur',
            '15'=>'Mizoram',           '16'=>'Tripura',
            '17'=>'Meghalaya',         '18'=>'Assam',
            '19'=>'West Bengal',       '20'=>'Jharkhand',
            '21'=>'Odisha',            '22'=>'Chhattisgarh',
            '23'=>'Madhya Pradesh',    '24'=>'Gujarat',
            '26'=>'Dadra & Nagar Haveli',
            '27'=>'Maharashtra',       '29'=>'Karnataka',
            '30'=>'Goa',               '31'=>'Lakshadweep',
            '32'=>'Kerala',            '33'=>'Tamil Nadu',
            '34'=>'Puducherry',        '35'=>'Andaman & Nicobar',
            '36'=>'Telangana',         '37'=>'Andhra Pradesh',
            '38'=>'Ladakh',
        ];
    }

    // ════════════════════════════════════════════════════════════════════════
    //  DOWNLOADS — Current session batch
    // ════════════════════════════════════════════════════════════════════════

    public function downloadCSV()
    {
        $client  = session('gstr_selected_client');
        $batchId = session('gstr_batch_id');
        if (!$client || !$batchId) return redirect()->route('gstr.upload.index');

        $transactions = GstrTransaction::where('user_id', Auth::id())
            ->where('batch_id', $batchId)
            ->where('status', 'processed')
            ->get();
        if ($transactions->isEmpty()) return redirect()->back()->with('error', 'No transactions found');

        $summary  = $this->computeSummary($transactions, $client['gstin'] ?? '');
        $period   = $transactions->first()->period;
        $stateMap = $this->stateMap();

        $csv = "Type,Place Of Supply,Rate,Applicable % of Tax Rate,Taxable Value,Cess Amount,E-Commerce GSTIN\r\n";
        foreach ($summary as $r) {
            $stateName = $r['pos'] . '-' . ($stateMap[$r['pos']] ?? $r['pos']);
            $csv .= "OE,{$stateName},{$r['rt']},,{$r['txval']},,\r\n";
        }

        $filename = "b2cs_{$client['gstin']}_{$this->safePeriod($period)}.csv";
        return Response::streamDownload(fn() => print($csv), $filename, ['Content-Type' => 'text/csv']);
    }

    public function downloadJSON()
    {
        $client  = session('gstr_selected_client');
        $batchId = session('gstr_batch_id');
        if (!$client || !$batchId) return redirect()->route('gstr.upload.index');

        $transactions = GstrTransaction::where('user_id', Auth::id())
            ->where('batch_id', $batchId)
            ->where('status', 'processed')
            ->get();
        if ($transactions->isEmpty()) return redirect()->back()->with('error', 'No transactions found');

        $gstin       = $client['gstin'] ?? '';
        $isQuarterly = $this->isQuarterlyClient($client);
        $summary     = $this->computeSummary($transactions, $gstin);
        $b2bData     = $this->computeB2B($transactions, $gstin);
        $period      = $transactions->first()->period;
        $jsonData    = $this->generateJSON($gstin, $period, $summary, $b2bData, $transactions, $isQuarterly);
        $filename    = "gstr1_{$client['gstin']}_{$this->safePeriod($period)}.json";

        return Response::streamDownload(
            fn() => print(json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)),
            $filename,
            ['Content-Type' => 'application/json']
        );
    }

    public function downloadSummaryExcel()
    {
        $client  = session('gstr_selected_client');
        $batchId = session('gstr_batch_id');
        if (!$client || !$batchId) return redirect()->route('gstr.upload.index');

        $transactions = GstrTransaction::where('user_id', Auth::id())
            ->where('batch_id', $batchId)
            ->where('status', 'processed')
            ->get();
        if ($transactions->isEmpty()) return redirect()->back()->with('error', 'No transactions found');

        $summary  = $this->computeSummary($transactions, $client['gstin'] ?? '');
        $period   = $transactions->first()->period;
        $stateMap = $this->stateMap();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('B2CS Summary');

        $sheet->setCellValue('A1', 'B2CS Summary Report');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->setCellValue('A2', 'Client: ' . $client['name']);
        $sheet->setCellValue('A3', 'GSTIN: '  . $client['gstin']);
        $sheet->setCellValue('A4', 'Period: ' . $period);

        $headers = ['Type', 'Place of Supply', 'GST Rate %', 'Taxable Value', 'IGST', 'CGST', 'SGST', 'Total Tax'];
        foreach ($headers as $i => $h) {
            $cellRef = $this->colRow($i + 1, 6);
            $sheet->setCellValue($cellRef, $h);
            $sheet->getStyle($cellRef)->getFont()->setBold(true);
        }

        $row = 7;
        foreach ($summary as $r) {
            $stateName = $r['pos'] . '-' . ($stateMap[$r['pos']] ?? $r['pos']);
            $sheet->setCellValue($this->colRow(1, $row), $r['sply_ty']);
            $sheet->setCellValue($this->colRow(2, $row), $stateName);
            $sheet->setCellValue($this->colRow(3, $row), $r['rt']);
            $sheet->setCellValue($this->colRow(4, $row), $r['txval']);
            $sheet->setCellValue($this->colRow(5, $row), $r['iamt']);
            $sheet->setCellValue($this->colRow(6, $row), $r['camt']);
            $sheet->setCellValue($this->colRow(7, $row), $r['samt']);
            $sheet->setCellValue($this->colRow(8, $row), $r['iamt'] + $r['camt'] + $r['samt']);
            $row++;
        }

        foreach (range('A', 'H') as $col) $sheet->getColumnDimension($col)->setAutoSize(true);

        $filename = "b2cs_summary_{$client['gstin']}_{$this->safePeriod($period)}.xlsx";
        $writer   = new XlsxWriter($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        $writer->save('php://output');
        exit;
    }

    public function downloadDetailedExcel()
    {
        $client  = session('gstr_selected_client');
        $batchId = session('gstr_batch_id');
        if (!$client || !$batchId) return redirect()->route('gstr.upload.index');

        $transactions = GstrTransaction::where('user_id', Auth::id())
            ->where('batch_id', $batchId)
            ->where('status', 'processed')
            ->get();
        if ($transactions->isEmpty()) return redirect()->back()->with('error', 'No transactions found');

        $period   = $transactions->first()->period;
        $detailed = $this->getDetailedTransactions($transactions);
        $stateMap = $this->stateMap();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Transaction Detail');

        $sheet->setCellValue('A1', 'Transaction Detail Report');
        $sheet->mergeCells('A1:O1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->setCellValue('A2', 'Client: ' . $client['name']);
        $sheet->setCellValue('A3', 'GSTIN: '  . $client['gstin']);
        $sheet->setCellValue('A4', 'Period: ' . $period);

        $headers = ['#','Platform','Period','Invoice No','State','Nature','HSN','Qty','Taxable','Rate%','CGST','SGST','IGST','Invoice','TCS'];
        foreach ($headers as $i => $h) {
            $cellRef = $this->colRow($i + 1, 6);
            $sheet->setCellValue($cellRef, $h);
            $sheet->getStyle($cellRef)->getFont()->setBold(true);
        }

        $row = 7;
        foreach ($detailed as $t) {
            $stateName = $t['state'] . '-' . ($stateMap[$t['state']] ?? $t['state']);
            $values    = [
                $t['idx'], $t['platform'], $t['period'], $t['invoice_number'] ?? '-', $stateName,
                $t['nature'], $t['hsn'] ?? '-', $t['qty'], $t['taxable'],
                $t['rate'], $t['cgst'], $t['sgst'], $t['igst'], $t['invoice'], $t['tcs'],
            ];
            foreach ($values as $i => $v) {
                $sheet->setCellValue($this->colRow($i + 1, $row), $v);
            }
            $row++;
        }

        foreach (range('A', 'O') as $col) $sheet->getColumnDimension($col)->setAutoSize(true);

        $filename = "b2cs_detail_{$client['gstin']}_{$this->safePeriod($period)}.xlsx";
        $writer   = new XlsxWriter($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        $writer->save('php://output');
        exit;
    }

    // ════════════════════════════════════════════════════════════════════════
    //  BATCH-WISE DOWNLOADS — History page
    // ════════════════════════════════════════════════════════════════════════

    private function getClientForBatch(string $batchId): array
    {
        $batch = GstrBatch::where('user_id', Auth::id())
                          ->where('batch_id', $batchId)
                          ->firstOrFail();
        return [
            'name'              => $batch->client_name,
            'gstin'             => $batch->client_gstin,
            'state_code'        => '',
            // Read filing_frequency off the batch/client if the column
            // exists; Eloquent returns null for undefined attributes
            // (never throws), so this is safe even if the column hasn't
            // been added to gstr_batches yet — it will just fall back to
            // 'monthly' via isQuarterlyClient().
            'filing_frequency'  => $batch->filing_frequency ?? 'monthly',
        ];
    }

    public function downloadCSVByBatch(string $batchId)
    {
        session(['gstr_batch_id' => $batchId,
                 'gstr_selected_client' => $this->getClientForBatch($batchId)]);
        return $this->downloadCSV();
    }

    public function downloadSummaryExcelByBatch(string $batchId)
    {
        session(['gstr_batch_id' => $batchId,
                 'gstr_selected_client' => $this->getClientForBatch($batchId)]);
        return $this->downloadSummaryExcel();
    }

    public function downloadDetailExcelByBatch(string $batchId)
    {
        session(['gstr_batch_id' => $batchId,
                 'gstr_selected_client' => $this->getClientForBatch($batchId)]);
        return $this->downloadDetailedExcel();
    }

    public function downloadJSONByBatch(string $batchId)
    {
        $transactions = GstrTransaction::where('user_id', Auth::id())
            ->where('batch_id', $batchId)
            ->where('status', 'processed')
            ->get();
        if ($transactions->isEmpty()) abort(404);

        $client      = $this->getClientForBatch($batchId);
        $gstin       = $client['gstin'] ?? '';
        $isQuarterly = $this->isQuarterlyClient($client);
        $summary     = $this->computeSummary($transactions, $gstin);
        $b2bData     = $this->computeB2B($transactions, $gstin);
        $period      = $transactions->first()->period;
        $jsonData    = $this->generateJSON($gstin, $period, $summary, $b2bData, $transactions, $isQuarterly);
        $filename    = "gstr1_{$client['gstin']}_{$this->safePeriod($period)}.json";

        return Response::streamDownload(
            fn() => print(json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)),
            $filename,
            ['Content-Type' => 'application/json']
        );
    }

    // ════════════════════════════════════════════════════════════════════════
    //  MISC
    // ════════════════════════════════════════════════════════════════════════

    public function getSummaryData()
    {
        $client  = session('gstr_selected_client');
        $batchId = session('gstr_batch_id');
        if (!$client || !$batchId) return response()->json(['error' => 'Session expired'], 401);

        $transactions = GstrTransaction::where('user_id', Auth::id())
            ->where('batch_id', $batchId)
            ->where('status', 'processed')
            ->get();
        if ($transactions->isEmpty()) return response()->json(['error' => 'No transactions found'], 404);

        $gstin               = $client['gstin'] ?? '';
        $isQuarterly         = $this->isQuarterlyClient($client);
        $summary             = $this->computeSummary($transactions, $gstin);
        $b2bData             = $this->computeB2B($transactions, $gstin);
        $period              = $transactions->first()->period;
        $jsonData            = $this->generateJSON($gstin, $period, $summary, $b2bData, $transactions, $isQuarterly);
        $docIssue            = $this->computeDocIssue($transactions, $gstin);
        $hsnData             = $this->computeHsnSummary($transactions);
        $stateSummary        = $this->computeStateSummary($transactions);
        $excludedReturns     = $this->computeExcludedReturns($transactions);
        $excludedB2BInvoices = $this->computeExcludedB2BInvoices($transactions);

        return response()->json([
            'client'           => $client,
            'period'           => $period,
            'summary'          => $summary,
            'b2bData'          => $b2bData,
            'jsonData'         => $jsonData,
            'docIssue'         => $docIssue,
            'hsnData'          => $hsnData,
            'stateSummary'     => $stateSummary,
            'excludedReturns'  => $excludedReturns,
            'excludedB2BInvoices' => $excludedB2BInvoices,
            'transactionCount' => $transactions->count(),
        ]);
    }

    public function clearAndGoBack()
    {
        $batchId = session('gstr_batch_id');
        if ($batchId) GstrTransaction::where('batch_id', $batchId)->delete();
        session()->forget([
            'gstr_batch_id', 'gstr_summary', 'gstr_transactions',
            'gstr_selected_client', 'gstr_period', 'gstr_b2cs_overrides',
            'gstr_b2b_exclusions',
        ]);
        return redirect()->route('gstr.upload.index')->with('success', 'Data cleared');
    }
}