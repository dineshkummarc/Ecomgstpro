<?php

namespace App\Http\Controllers;

use App\Models\GstrTransaction;
use App\Models\GstrBatch;
use App\Parsers\AmazonParser;
use App\Parsers\FlipkartParser;
use App\Parsers\MeeshoParser;
use App\Parsers\MyntraParser;
use App\Parsers\AjioParser;
use App\Parsers\JioMartParser;
use App\Parsers\SnapdealParser;
use App\Parsers\GstPortalParser;
use App\Parsers\BaseParser;
use App\Support\GstrPeriodHelper;
use App\Support\GstinFileScanner; // ← NEW (v2): universal, platform-agnostic
                                    // "does this GSTIN appear ANYWHERE in the
                                    // file" check — see GstinFileScanner.php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class GstrUploadController extends Controller
{
    // ════════════════════════════════════════════════════════════════════════
    //  INDEX
    // ════════════════════════════════════════════════════════════════════════

    public function index()
    {
        $selectedClient = session('gstr_selected_client') ?? session('selected_client');
        if (!$selectedClient) {
            return redirect()->route('gstr.clients.index')
                             ->with('error', 'Please select a client first.');
        }

        // Client's filing frequency (monthly / quarterly)
        $filingFrequency = $selectedClient['filing_frequency'] ?? 'monthly';

        $existingPeriodsQuery = GstrBatch::where('user_id', Auth::id())
            ->where('client_gstin', $selectedClient['gstin']);

        // For a quarterly client, the "existing" list is built from
        // filing_period (e.g. "Q1/2025-26"); for a monthly client, it's
        // built from period (MM/YYYY) as before.
        if ($filingFrequency === 'quarterly') {
            $existingPeriods = (clone $existingPeriodsQuery)
                ->whereNotNull('filing_period')
                ->pluck('filing_period')
                ->unique()
                ->values()
                ->toArray();
        } else {
            $existingPeriods = (clone $existingPeriodsQuery)
                ->pluck('period')
                ->toArray();
        }

        return view('index', [
            'client'          => $selectedClient,
            'existingPeriods' => $existingPeriods,
            'filingFrequency' => $filingFrequency,
        ]);
    }

    // ════════════════════════════════════════════════════════════════════════
    //  CHECK PERIOD
    // ════════════════════════════════════════════════════════════════════════

    public function checkPeriod(Request $request)
    {
        $client = session('gstr_selected_client') ?? session('selected_client');
        if (!$client) return response()->json(['error' => 'Client not selected'], 400);

        $period       = $request->input('period');
        $filingPeriod = $request->input('filing_period'); // e.g. "Q1/2025-26" for quarterly clients

        if (!$period && !$filingPeriod) {
            return response()->json(['error' => 'Period required'], 400);
        }

        $query = GstrBatch::where('user_id', Auth::id())
            ->where('client_gstin', $client['gstin']);

        // For quarterly, look up by filing_period; otherwise by period as before
        $existing = $filingPeriod
            ? (clone $query)->where('filing_period', $filingPeriod)->first()
            : (clone $query)->where('period', $period)->first();

        if ($existing) {
            return response()->json([
                'exists'   => true,
                'batch_id' => $existing->batch_id,
                'period'   => $existing->filing_period ?? $existing->period,
                'tx_count' => $existing->transaction_count,
                'taxable'  => $existing->taxable_value,
                'message'  => "Data for {$client['name']} already exists for " . ($existing->filing_period ?? $existing->period) . ".",
            ]);
        }

        return response()->json(['exists' => false]);
    }


    // ════════════════════════════════════════════════════════════════════════
//  PERIOD-WISE PLATFORM STATUS — ← NEW
//  Selected client + period (aur quarterly ho to filing_period) ke liye
//  DB me jo platforms ka data already save hai, unka platform-wise
//  tx_count aur taxable_value wapas bhejta hai. Frontend isse period
//  select hone par row-lock aur "already uploaded" badge dikhata hai.
// ════════════════════════════════════════════════════════════════════════

public function periodStatus(Request $request)
{
    $client = session('gstr_selected_client') ?? session('selected_client');
    if (!$client) return response()->json(['error' => 'Client not selected'], 400);

    $period       = $request->input('period');
    $filingPeriod = $request->input('filing_period');

    if (!$period) {
        return response()->json(['error' => 'Period required'], 400);
    }

    $query = GstrTransaction::where('user_id', Auth::id())
        ->where('gstin', $client['gstin'])
        ->where('status', 'processed')
        ->where('period', $period);

    if ($filingPeriod) {
        $query->where('filing_period', $filingPeriod);
    }

    $rows = $query->selectRaw('platform, COUNT(*) as tx_count, SUM(taxable_value) as taxable_value')
        ->groupBy('platform')
        ->get();

    // Har raw platform value (jaise "amazon_b2c") ko uske base platform
    // (jaise "amazon") ke bucket me daalo — yehi base keys upload page ke
    // rows (amazon/flipkart/meesho/myntra/jiomart/snapdeal/shopify/glowroad/other)
    // se match karte hain.
    $baseKeys = ['amazon','flipkart','meesho','myntra','jiomart','snapdeal','shopify','glowroad','other','govexcel','gstportal'];

    $buckets = [];
    foreach ($rows as $r) {
        $raw = strtolower(trim($r->platform));
        $base = $raw;
        foreach ($baseKeys as $bk) {
            if (str_starts_with($raw, $bk)) { $base = $bk; break; }
        }
        if (!isset($buckets[$base])) {
            $buckets[$base] = ['platform' => $base, 'tx_count' => 0, 'taxable_value' => 0];
        }
        $buckets[$base]['tx_count']      += (int) $r->tx_count;
        $buckets[$base]['taxable_value'] += (float) $r->taxable_value;
    }

    return response()->json([
        'success'       => true,
        'period'        => $period,
        'filing_period' => $filingPeriod,
        'platforms'     => array_values($buckets),
    ]);
}

// ════════════════════════════════════════════════════════════════════════
//  DELETE PLATFORM DATA FOR A PERIOD — ← NEW
//  Ek specific platform (base key) ka, selected client ke, is period
//  (aur quarterly ho to filing_period) ka poora data delete karta hai —
//  taaki user usi platform ko dobara upload kar sake. Agar us
//  period/filing_period ka koi aur transaction bacha hi nahi to
//  corresponding GstrBatch row bhi hata deta hai (orphan batch na bane).
// ════════════════════════════════════════════════════════════════════════

public function deletePlatformPeriod(Request $request)
{
    try {
        $client = session('gstr_selected_client') ?? session('selected_client');
        if (!$client) return response()->json(['error' => 'Client not selected'], 400);

        $platform     = strtolower(trim($request->input('platform', '')));
        $period       = $request->input('period');
        $filingPeriod = $request->input('filing_period');

        if (!$platform || !$period) {
            return response()->json(['error' => 'platform and period are required'], 400);
        }

        $query = GstrTransaction::where('user_id', Auth::id())
            ->where('gstin', $client['gstin'])
            ->where('period', $period)
            ->where('platform', 'like', $platform . '%');

        if ($filingPeriod) {
            $query->where('filing_period', $filingPeriod);
        }

        $deleted = $query->delete();

        $remainingQuery = GstrTransaction::where('user_id', Auth::id())
            ->where('gstin', $client['gstin'])
            ->where('status', 'processed');

        if ($filingPeriod) {
            $remainingQuery->where('filing_period', $filingPeriod);
        } else {
            $remainingQuery->where('period', $period);
        }

        $stillHasData = $remainingQuery->exists();

        if (!$stillHasData) {
            $batchQuery = GstrBatch::where('user_id', Auth::id())
                ->where('client_gstin', $client['gstin']);

            if ($filingPeriod) {
                $batchQuery->where('filing_period', $filingPeriod);
            } else {
                $batchQuery->where('period', $period)->whereNull('filing_period');
            }
            $batchQuery->delete();
        }

        Log::info('Platform period data deleted', [
            'platform' => $platform, 'period' => $period,
            'filing_period' => $filingPeriod, 'rows_deleted' => $deleted,
        ]);

        return response()->json(['success' => true, 'deleted' => $deleted]);

    } catch (\Throwable $e) {
        Log::error('Delete Platform Period Error', ['message' => $e->getMessage()]);
        return response()->json(['error' => 'Delete Error: ' . $e->getMessage()], 400);
    }
}

    // ════════════════════════════════════════════════════════════════════════
    //  UPLOAD FILE — Main entry point
    // ════════════════════════════════════════════════════════════════════════

    public function uploadFile(Request $request)
    {
        set_time_limit(600);
        ini_set('memory_limit', '1024M');

        if (function_exists('apache_getenv')) {
            @apache_setenv('no-gzip', 1);
        }

        try {
            $startTime = microtime(true);

            $request->validate([
                'file'          => 'required|file|max:20480',
                'platform'      => 'required|string',
                'period'        => 'required|string',
                'file_role'     => 'nullable|string',
                'filing_period' => 'nullable|string',
                'calc_base'     => 'nullable|string|in:base_value,base_amount',
            ]);

            $file         = $request->file('file');
            $platform     = strtolower(trim($request->input('platform')));
            $period       = $request->input('period');
            $fileRole     = $request->input('file_role', 'single');
            $filingPeriod = $request->input('filing_period');
            $calcBase     = $request->input('calc_base'); // null | 'base_value' | 'base_amount'
            $originalName = $file->getClientOriginalName();
            $ext          = strtolower($file->getClientOriginalExtension());

            Log::info('GSTR Upload Started', [
                'file'          => $originalName,
                'size'          => $file->getSize(),
                'platform'      => $platform,
                'period'        => $period,
                'filing_period' => $filingPeriod,
                'calc_base'     => $calcBase,
            ]);

            if (!preg_match('/^\d{2}\/\d{4}$/', $period)) {
                return response()->json(['error' => 'Invalid period format. Use MM/YYYY'], 400);
            }

            // Validate filing_period format too, if it was sent
            if ($filingPeriod && !preg_match('/^Q[1-4]\/\d{4}-\d{2}$/', $filingPeriod)) {
                return response()->json(['error' => 'Invalid filing_period format. Use Q1/2025-26'], 400);
            }

            if (!in_array($ext, ['xlsx', 'xls', 'csv'])) {
                return response()->json(['error' => 'Only xlsx, xls, csv files allowed'], 400);
            }

            $client = session('gstr_selected_client') ?? session('selected_client');
            if (!$client) {
                return response()->json(['error' => 'Client not selected.'], 400);
            }

            $clientGstin     = strtoupper(trim($client['gstin'] ?? ''));
            $sellerStateCode = $client['state_code'] ?? '';

            $basePlatform = preg_replace('/-(b2c|b2b|sales|gst|gstr1)$/', '', $platform);
            if (in_array($basePlatform, ['auto', 'other', ''])) {
                $basePlatform = BaseParser::detectPlatformFromFilename($originalName);
            }

            $tempPath = $this->saveTempFile($file, $ext);
            if (!$tempPath) {
                return response()->json(['error' => 'File save failed'], 400);
            }

            try {
                $result = $this->dispatchParser(
                    $tempPath, $originalName, $ext,
                    $basePlatform, $period, $fileRole,
                    $sellerStateCode, $clientGstin,
                    $startTime, $calcBase
                );
            } finally {
                if (file_exists($tempPath)) @unlink($tempPath);
            }

            // If the file needs a calculation-base choice, hand that back
            // to the frontend immediately — no parsing has been committed.
            if (($result['needs_calc_base'] ?? false) === true) {
                return response()->json($result);
            }

            if ($fileRole === 'invoice') {
                if (($result['success'] ?? false) === true) {
                    // Session key is now built from filing_period-or-period
                    // instead of just period, so a quarterly upload doesn't
                    // scatter its invoice lookup across three separate
                    // months and processTransactions() can pick up the
                    // right lookup.
                    $lookupPeriodKey = $filingPeriod ?: $period;
                    $sessionKey = "gstr_tax_invoice_lookup_{$clientGstin}_{$lookupPeriodKey}";

                    // ⚠️ FIX: array_merge() re-indexes purely-numeric string
                    // keys (e.g. "3035979087781697281") into sequential
                    // integer keys (0,1,2,...) — this was silently destroying
                    // every order_number key in the lookup table, so
                    // mergeTaxInvoiceData() could never find a match.
                    // The `+` union operator preserves all keys, numeric or not.
                    $existingLookup = session($sessionKey, []);
                    $newLookup      = $result['lookup'] ?? [];
                    session([$sessionKey => $existingLookup + $newLookup]);

                    Log::info('Tax Invoice lookup stored in session', [
                        'key'   => $sessionKey,
                        'count' => count($newLookup + $existingLookup),
                    ]);
                }

                $elapsed = microtime(true) - $startTime;
                Log::info('GSTR Upload Completed (tax_invoice)', ['elapsed' => $elapsed]);

                return response()->json($result);
            }

            // ── Block duplicate-format upload ──────────────────────────
            if (($result['success'] ?? false) === true) {
                $dupError = $this->checkDuplicateFormat(
                    $clientGstin,
                    $period,
                    $basePlatform,
                    $result['file_type'] ?? '',
                    $filingPeriod
                );
                if ($dupError) {
                    return response()->json($dupError, 409);
                }

                $result['source_format'] = $result['file_type'] ?? $basePlatform;
            }
            // ─────────────────────────────────────────────────────────────

            $elapsed = microtime(true) - $startTime;
            Log::info('GSTR Upload Completed', ['elapsed' => $elapsed, 'platform' => $basePlatform]);

            return response()->json($result);

        } catch (\Illuminate\Validation\ValidationException $e) {
            $error = implode(' ', $e->errors()['file'] ?? ['Validation failed']);
            Log::warning('GSTR Validation Error', ['error' => $error]);
            return response()->json(['error' => $error], 400);

        } catch (\Throwable $e) {
            Log::error('GSTR Upload Fatal Error', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            $errMsg = $e->getMessage();

            if (str_contains($errMsg, 'No valid data sheets') || str_contains($errMsg, 'No valid sheets found')) {
                // Keep the message as-is
            } elseif (str_contains($errMsg, 'Allowed memory')) {
                $errMsg = 'File too large. Please split into smaller chunks (max 15MB).';
            } elseif (str_contains($errMsg, 'Call stack')) {
                $errMsg = 'File structure too complex. Contact support.';
            }

            return response()->json(['error' => 'Parse Error: ' . $errMsg], 400);
        }
    }

    // ════════════════════════════════════════════════════════════════════════
    //  DUPLICATE-FORMAT GUARD
    // ════════════════════════════════════════════════════════════════════════

    private function fileTypeFamily(?string $fileType): string
    {
        if (!$fileType) return 'unknown';

        return match(true) {
            str_contains($fileType, 'gstr1') => 'gstr1_ready',
            str_contains($fileType, 'mtr')   => 'sales_report',
            default                          => 'other',
        };
    }

    private function familyLabel(string $family): string
    {
        return match($family) {
            'gstr1_ready'  => 'GSTR-1 Ready file',
            'sales_report' => 'Sales Report / MTR file',
            default        => 'file',
        };
    }

    // $filingPeriod is an optional 5th param. When given (quarterly
    // client), the duplicate check runs against the WHOLE QUARTER, not
    // just the current month — otherwise uploading Flipkart-Sales in Apr
    // and Flipkart-GST in May would have been allowed (which is wrong,
    // both are duplicates of the same invoices).
    private function checkDuplicateFormat(
        string  $clientGstin,
        string  $period,
        string  $platform,
        ?string $newFileType,
        ?string $filingPeriod = null
    ): ?array {
        if (!$newFileType) return null;

        $newFamily    = $this->fileTypeFamily($newFileType);
        $basePlatform = explode('_', $platform)[0];
        if ($newFamily === 'other' || $newFamily === 'unknown') return null;

        $query = GstrTransaction::where('user_id', Auth::id())
            ->where('gstin', $clientGstin)
            ->where('platform', 'like', $basePlatform . '%')
            ->where('status', 'processed')
            ->whereNotNull('source_format');

        // For quarterly, match by filing_period; otherwise by period (old behaviour)
        if ($filingPeriod) {
            $query->where('filing_period', $filingPeriod);
        } else {
            $query->where('period', $period);
        }

        $existingFormats = $query->distinct('source_format')->pluck('source_format');

        foreach ($existingFormats as $existingFileType) {
            $existingFamily = $this->fileTypeFamily($existingFileType);

            if ($existingFamily !== 'other' && $existingFamily !== 'unknown' && $existingFamily !== $newFamily) {
                $periodLabel = $filingPeriod ?: $period;
                return [
                    'success'           => false,
                    'error'             =>
                        "A {$this->familyLabel($existingFamily)} for {$platform} has already been uploaded for {$periodLabel}. "
                        . "This new file is a {$this->familyLabel($newFamily)} — both files are duplicates of the same data "
                        . "(same invoices, just a different format). Uploading both would DOUBLE the transactions. "
                        . "Please upload only one format — either GSTR-1 Ready OR Sales Report/MTR, not both. "
                        . "To change format, first delete/replace the existing data for this period/platform.",
                    'existing_format'   => $existingFileType,
                    'existing_family'   => $existingFamily,
                    'new_format'        => $newFileType,
                    'new_family'        => $newFamily,
                ];
            }
        }

        return null;
    }

    // ════════════════════════════════════════════════════════════════════════
    //  GSTIN VALIDATE — for CSV rows (raw array, no PhpSpreadsheet object)
    // ════════════════════════════════════════════════════════════════════════
    //
    //  ⚠️ SUPERSEDED (v2): this method is no longer CALLED anywhere in this
    //  class — dispatchParser() now does the check inline using
    //  GstinFileScanner (see below), which checks the WHOLE file for the
    //  client's GSTIN instead of comparing against a single sampled value.
    //  Left in place, unused, in case anything outside this class still
    //  references it; safe to delete once confirmed nothing else calls it.
    // ════════════════════════════════════════════════════════════════════════

    private function validateGstinCsv(array $rows, string $platform, string $clientGstin): ?array
    {
        if (!$clientGstin) return null;

        try {
            $fileGstin = BaseParser::extractGstinFromCsvRows($rows, $platform);
            if (!$fileGstin) return null;

            $fileGstinClean = strtoupper(trim($fileGstin));
            if ($fileGstinClean !== $clientGstin) {
                return [
                    'success'      => false,
                    'error'        => "GSTIN Mismatch! File: {$fileGstinClean} | Client: {$clientGstin}. Please upload the correct file.",
                    'gstin_error'  => true,
                    'file_gstin'   => $fileGstinClean,
                    'client_gstin' => $clientGstin,
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('GSTIN extraction failed (CSV)', ['error' => $e->getMessage()]);
        }

        return null;
    }

    // ════════════════════════════════════════════════════════════════════════
    //  DISPATCH PARSER
    //
    //  ← CHANGED (v2) — GSTIN CHECK NOW "APPEARS ANYWHERE IN FILE", NOT
    //  "SINGLE SAMPLE MUST MATCH":
    //
    //  Previously (both CSV and Excel branches below) sampled exactly ONE
    //  GSTIN out of the uploaded file (via BaseParser::extractGstinFromCsvRows
    //  / extractGstinFromSpreadsheet — typically whichever GSTIN-shaped
    //  value the sampler ran into first) and rejected the ENTIRE upload if
    //  that one sample didn't equal the selected client's GSTIN.
    //
    //  Real-world exports from Amazon/Flipkart/Meesho/Myntra/JioMart/
    //  Snapdeal/Ajio can legitimately bundle MULTIPLE state-GSTINs of the
    //  same PAN into one consolidated file. In that case the single
    //  sampled row might belong to a DIFFERENT state's GSTIN than the one
    //  selected, even though the selected client's GSTIN has plenty of
    //  its own rows further down in the same file — and the upload would
    //  get wrongly blocked with a "GSTIN Mismatch" popup.
    //
    //  Fix: use GstinFileScanner::fileContainsGstin() to search EVERY
    //  cell of EVERY row (across however many sheets the file has) for
    //  the selected client's GSTIN. This is platform-agnostic (a GSTIN's
    //  shape is fixed, so this needs no per-platform column-name list) —
    //  the SAME check now guards every parser branch below without
    //  needing one validation routine per platform.
    //    - Found anywhere  → proceed. The chosen platform parser is
    //      responsible for filtering OUT any other-GSTIN rows itself
    //      (see AmazonParser's per-row 'seller_gstin' filter for the
    //      reference pattern this relies on).
    //    - Found nowhere   → genuinely the wrong file; block exactly as
    //      before, with a clearer message.
    //
    //  ← CHANGED (v3) — CALCULATION-BASE SELECTION FOR MYNTRA:
    //
    //  Myntra sheets can carry BOTH base_value and base_amount columns
    //  with different totals (base_amount includes platform fees that
    //  base_value nets out). Previously we silently always preferred
    //  base_value. Now, for excel Myntra uploads where the caller hasn't
    //  told us which one to use ($calcBase === null) and the sheet
    //  actually has both columns, we stop BEFORE parsing and return a
    //  'needs_calc_base' response with a quick preview total for each
    //  option, so the frontend can ask the user which column to use —
    //  same idea as a competitor tool's "Select Calculation Base" prompt,
    //  worded and styled as our own. Once the user picks (calc_base is
    //  sent on the resubmit), we skip this and parse directly using their
    //  choice.
    // ════════════════════════════════════════════════════════════════════════

    private function dispatchParser(
        string $tempPath,
        string $originalName,
        string $ext,
        string $platform,
        string $period,
        string $fileRole,
        string $sellerStateCode,
        string $clientGstin,
        float $startTime = 0,
        ?string $calcBase = null
    ): array {

        $maxTime = 300;
        $elapsed = microtime(true) - $startTime;
        if ($elapsed > $maxTime) {
            throw new \Exception("Upload timeout exceeded ({$elapsed}s). File too large or complex.");
        }

        $parserClass = match(true) {
            // Use dedicated Meesho parsers first
            $platform === 'meesho' && $fileRole === 'invoice' => \App\Parsers\MeeshoTaxInvoiceParser::class,
            $platform === 'meesho' && $fileRole === 'sales'   => \App\Parsers\MeeshoSalesParser::class,
            $platform === 'meesho' && $fileRole === 'return'  => \App\Parsers\MeeshoReturnParser::class,
            // Fallback to generic invoice parser for other platforms
            $fileRole === 'invoice'              => \App\Parsers\TaxInvoiceParser::class, // Kept for non-Meesho platforms
            str_contains($platform, 'amazon')    => AmazonParser::class,
            str_contains($platform, 'flipkart')  => FlipkartParser::class,
            str_contains($platform, 'myntra')    => MyntraParser::class, // Meesho handled above
            str_contains($platform, 'ajio')      => AjioParser::class,
            str_contains($platform, 'jiomart')   => JioMartParser::class,
            str_contains($platform, 'snapdeal')  => SnapdealParser::class,
            // str_contains($platform, 'shopify')   => ShopifyParser::class,
            str_contains($platform, 'shopify')   => \App\Parsers\ShopifyParser::class,
            str_contains($platform, 'gstportal') => GstPortalParser::class,
            default                              => null,
        };

        if ($ext === 'csv') {
            if (!$parserClass) {
                // Auto-detect for Meesho if platform is generic 'meesho' but file role is specific
                if ($platform === 'meesho') {
                    $parserClass = $this->autoDetectMeeshoParserFromRole($fileRole);
                }
            }
            if (!$parserClass) {
                return ['success' => false, 'error' => 'For CSV files, include platform name in filename (e.g., myntra_report.csv).'];
            }

            Log::info('Parsing CSV', ['platform' => $platform]);

            $parser = ($parserClass === MyntraParser::class)
                ? new $parserClass($sellerStateCode, $clientGstin, $calcBase)
                : new $parserClass($sellerStateCode, $clientGstin);

            if ($fileRole !== 'invoice' && $clientGstin) {
                try {
                    // Read every sheet the CSV reader gives us (not just
                    // 'Sheet1') so the scan below isn't limited to one
                    // sheet name assumption.
                    $csvSheets = $parser->readCsv($tempPath);

                    // ← FIXED (v2, universal — see class-level doc-comment
                    // above dispatchParser()): check if the client's GSTIN
                    // appears ANYWHERE in the file, instead of comparing
                    // against a single sampled GSTIN.
                    if (!GstinFileScanner::fileContainsGstin($csvSheets, $clientGstin)) {
                        $foundGstin = GstinFileScanner::firstGstinFound($csvSheets);
                        Log::warning('GSTIN not found anywhere in file (CSV)', [
                            'client_gstin' => $clientGstin,
                            'found_gstin'  => $foundGstin,
                        ]);
                        return [
                            'success'      => false,
                            'error'        => $foundGstin
                                ? "GSTIN Mismatch! This file has no rows for {$clientGstin} — it contains {$foundGstin}"
                                    . (GstinFileScanner::hasMultipleGstins($csvSheets) ? ' (and other GSTINs)' : '')
                                    . ". Please upload the correct file."
                                : "Could not find any GSTIN in this file to verify against {$clientGstin}. Please check the file.",
                            'gstin_error'  => true,
                            'file_gstin'   => $foundGstin,
                            'client_gstin' => $clientGstin,
                        ];
                    }

                    // Calculation-base prompt for Myntra CSVs with dual base columns.
                    if ($parserClass === MyntraParser::class && $calcBase === null) {
                        foreach ($csvSheets as $rows) {
                            if (empty($rows)) continue;
                            $headers = array_map('strval', $rows[0] ?? []);
                            if (MyntraParser::headerHasDualBase($headers)) {
                                return [
                                    'success'         => true,
                                    'needs_calc_base' => true,
                                    'options'         => [
                                        'base_value'  => MyntraParser::previewColumnTotal($csvSheets, 'base_value'),
                                        'base_amount' => MyntraParser::previewColumnTotal($csvSheets, 'base_amount'),
                                    ],
                                ];
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    Log::warning('GSTIN scan failed (CSV)', ['error' => $e->getMessage()]);
                }
            }

            $result = $parser->parseCsv($tempPath, $period, $fileRole);
            return $this->normalizeResult($result, $platform, $fileRole);
        }

        Log::info('Loading Excel file', ['file' => basename($tempPath)]);

        try {
            $spreadsheet = IOFactory::load($tempPath);
        } catch (\Throwable $e) {
            Log::error('Failed to load Excel file', ['error' => $e->getMessage()]);
            throw new \Exception("Cannot read Excel file: " . $e->getMessage());
        }

        if (!$parserClass) {
            Log::info('Auto-detecting parser');
            $parserClass = BaseParser::detectParserFromContent($spreadsheet);
            // Auto-detect for Meesho if platform is generic 'meesho' but file role is specific
            if (!$parserClass && $platform === 'meesho') {
                $parserClass = $this->autoDetectMeeshoParserFromRole($fileRole);
            }

            if (!$parserClass) {
                return $this->unknownFormatError($spreadsheet);
            }
        }

        Log::info('Using parser', ['parser' => class_basename($parserClass)]);

        $elapsed = microtime(true) - $startTime;
        if ($elapsed > 240) {
            throw new \Exception("Processing timeout. File too large.");
        }

        Log::info('Converting spreadsheet to array');
        $sheetsResult = $this->spreadsheetToArrayDetailed($spreadsheet);

        if (!$sheetsResult['success']) {
            return $sheetsResult['error_response'];
        }

        $sheets = $sheetsResult['sheets'];
        unset($spreadsheet);
        gc_collect_cycles();

        // ← MOVED + FIXED (v2, universal — see class-level doc-comment
        // above dispatchParser()): this GSTIN check used to run BEFORE
        // spreadsheetToArrayDetailed() and compared a single sampled
        // GSTIN (via validateGstin()/extractGstinFromSpreadsheet()).
        // It now runs AFTER conversion, directly against the already-
        // built $sheets array — so it's one spreadsheet read instead of
        // two, and it checks the WHOLE file (every sheet, every row,
        // every cell) for the client's GSTIN instead of one sample.
        if ($fileRole !== 'invoice' && $clientGstin) {
            if (!GstinFileScanner::fileContainsGstin($sheets, $clientGstin)) {
                $foundGstin = GstinFileScanner::firstGstinFound($sheets);
                Log::warning('GSTIN not found anywhere in file', [
                    'client_gstin' => $clientGstin,
                    'found_gstin'  => $foundGstin,
                ]);
                return [
                    'success'      => false,
                    'error'        => $foundGstin
                        ? "GSTIN Mismatch! This file has no rows for {$clientGstin} — it contains {$foundGstin}"
                            . (GstinFileScanner::hasMultipleGstins($sheets) ? ' (and other GSTINs)' : '')
                            . ". Please upload the correct file."
                        : "Could not find any GSTIN in this file to verify against {$clientGstin}. Please check the file.",
                    'gstin_error'  => true,
                    'file_gstin'   => $foundGstin,
                    'client_gstin' => $clientGstin,
                ];
            }
        }

        Log::info('Sheets extracted', ['count' => count($sheets), 'names' => array_keys($sheets)]);

        // Calculation-base prompt for Myntra Excel files with dual base columns.
        if ($parserClass === MyntraParser::class && $calcBase === null) {
            foreach ($sheets as $rows) {
                if (empty($rows)) continue;
                $headers = array_map('strval', $rows[0] ?? []);
                if (MyntraParser::headerHasDualBase($headers)) {
                    return [
                        'success'         => true,
                        'needs_calc_base' => true,
                        'options'         => [
                            'base_value'  => MyntraParser::previewColumnTotal($sheets, 'base_value'),
                            'base_amount' => MyntraParser::previewColumnTotal($sheets, 'base_amount'),
                        ],
                    ];
                }
            }
        }

        Log::info('Starting parser', ['sheets' => count($sheets)]);
        $parser = ($parserClass === MyntraParser::class)
            ? new $parserClass($sellerStateCode, $clientGstin, $calcBase)
            : new $parserClass($sellerStateCode, $clientGstin);

        try {
            $result = $parser->parse($sheets, $sellerStateCode, $period, $fileRole);
        } catch (\Throwable $e) {
            Log::error('Parser error', ['error' => $e->getMessage(), 'parser' => class_basename($parserClass)]);
            throw new \Exception("Parser failed: " . $e->getMessage());
        }

        return $this->normalizeResult($result, $platform, $fileRole);
    }

    private function autoDetectMeeshoParserFromRole(string $fileRole): ?string
    {
        return match($fileRole) {
            'sales'   => \App\Parsers\MeeshoSalesParser::class,
            'return'  => \App\Parsers\MeeshoReturnParser::class,
            'invoice' => \App\Parsers\MeeshoTaxInvoiceParser::class,
            default   => null,
        };
    }

    // ════════════════════════════════════════════════════════════════════════
    //  SPREADSHEET → ARRAY   (UNCHANGED)
    // ════════════════════════════════════════════════════════════════════════

    private function spreadsheetToArrayDetailed($spreadsheet): array
    {
        $sheets        = [];
        $sheetCount    = count($spreadsheet->getAllSheets());
        $allSheetNames = [];

        Log::info("Processing {$sheetCount} sheets");

        foreach ($spreadsheet->getAllSheets() as $idx => $sheet) {
            $sheetName       = $sheet->getTitle();
            $allSheetNames[] = $sheetName;

            try {
                $sheet->setAutoCalculate(false);

                $rows = $sheet->toArray(
                    nullValue:         '',
                    calculateFormulas: false,
                    formatData:        false,
                    returnCellRef:     false
                );

                if (empty($rows)) continue;

                $filteredRows = [];
                foreach ($rows as $row) {
                    foreach ($row as $cell) {
                        if ($cell !== null && trim((string)$cell) !== '') {
                            $filteredRows[] = $row;
                            break;
                        }
                    }
                }

                if (empty($filteredRows)) continue;

                $sheets[$sheetName] = $filteredRows;

            } catch (\Throwable $e) {
                Log::warning("Error processing sheet", ['name' => $sheetName, 'error' => $e->getMessage()]);
                continue;
            }
        }

        if (empty($sheets) && $sheetCount > 0) {
            foreach ($spreadsheet->getAllSheets() as $sheet) {
                $sheetName = $sheet->getTitle();
                try {
                    $rows = $sheet->toArray('', false, true, false);
                    if (!empty($rows)) {
                        $sheets[$sheetName] = $rows;
                    }
                } catch (\Throwable $e) {
                    continue;
                }
            }
        }

        if (empty($sheets)) {
            $namesStr = !empty($allSheetNames) ? implode(', ', $allSheetNames) : 'No sheets found in file';
            return [
                'success'        => false,
                'error_response' => [
                    'success'     => false,
                    'error'       => "No valid data sheets found. Sheets found in the file: [{$namesStr}]. "
                                  . "Expected: B2C Small, B2C Large, B2B, Section 7A, 7B, MTR, etc.",
                    'sheet_names' => $allSheetNames,
                    'details'     => "Sheets found in file: {$namesStr}",
                ],
            ];
        }

        return ['success' => true, 'sheets' => $sheets];
    }

    // ════════════════════════════════════════════════════════════════════════
    //  NORMALIZE RESULT   (UNCHANGED)
    // ════════════════════════════════════════════════════════════════════════

    private function normalizeResult(array $result, string $platform, string $fileRole = 'single'): array
    {
        if (isset($result['success']) && $result['success'] === false) {
            return $result;
        }

        if ($fileRole === 'invoice') {
            $lookup   = $result['lookup']   ?? [];
            $warnings = $result['warnings'] ?? [];

            if (empty($lookup) && !empty($warnings)) {
                return ['success' => false, 'error' => implode(' | ', array_slice($warnings, 0, 3)), 'warnings' => $warnings];
            }

            return [
                'success'        => true,
                'is_tax_invoice' => true,
                'lookup'         => $lookup,
                'lookup_count'   => count($lookup),
                'warnings'       => $warnings,
                'platform'       => $result['platform'] ?? $platform,
                'file_type'      => $result['file_type'] ?? 'meesho_tax_invoice',
            ];
        }

        $transactions = $result['transactions'] ?? [];
        $warnings     = $result['warnings']     ?? [];

        if (empty($transactions)) {
            $errMsg = !empty($warnings)
                ? 'No transactions found. Warnings: ' . implode(' | ', array_slice($warnings, 0, 3))
                : 'No valid transactions found in this file. Please upload the correct file.';
            return ['success' => false, 'error' => $errMsg, 'warnings' => $warnings];
        }

        // return [
        //     'success'            => true,
        //     'transactions'       => $transactions,
        //     'transactions_count' => count($transactions),
        //     'warnings'           => $warnings,
        //     'platform'           => $platform,
        //     'file_type'          => $result['file_type'] ?? null,
        // ];

        return [
            'success'             => true,
            'transactions'        => $transactions,
            'transactions_count'  => count($transactions),
            'warnings'            => $warnings,
            'platform'            => $platform,
            'file_type'           => $result['file_type'] ?? null,
            'doc_issue_override'  => $result['doc_issue_override'] ?? [],
            'hsn_override'        => $result['hsn_override'] ?? [],
        ];
    }

    // ════════════════════════════════════════════════════════════════════════
    //  GSTIN VALIDATE — Excel
    // ════════════════════════════════════════════════════════════════════════
    //
    //  ⚠️ SUPERSEDED (v2): this method is no longer CALLED anywhere in this
    //  class — dispatchParser()'s Excel branch now does the check inline
    //  using GstinFileScanner against the already-converted $sheets array
    //  (see class-level doc-comment above dispatchParser()). Left in
    //  place, unused, in case anything outside this class still
    //  references it; safe to delete once confirmed nothing else calls it.
    // ════════════════════════════════════════════════════════════════════════

    private function validateGstin($spreadsheet, string $platform, string $clientGstin): ?array
    {
        if (!$clientGstin) return null;

        try {
            $fileGstin = BaseParser::extractGstinFromSpreadsheet($spreadsheet, $platform);
            if (!$fileGstin) return null;

            $fileGstinClean = strtoupper(trim($fileGstin));
            if ($fileGstinClean !== $clientGstin) {
                return [
                    'success'      => false,
                    'error'        => "GSTIN Mismatch! File: {$fileGstinClean} | Client: {$clientGstin}. Please upload the correct file.",
                    'gstin_error'  => true,
                    'file_gstin'   => $fileGstinClean,
                    'client_gstin' => $clientGstin,
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('GSTIN extraction failed', ['error' => $e->getMessage()]);
        }

        return null;
    }

    private function unknownFormatError($spreadsheet): array
    {
        $sheetNames = array_map(fn($s) => $s->getTitle(), $spreadsheet->getAllSheets());
        return [
            'success' => false,
            'error'   => 'Unrecognized file format. Sheets found: ' . implode(', ', $sheetNames),
            'sheets'  => $sheetNames,
            'hint'    => 'File must be a valid e-commerce report (Amazon MTR/GSTR-1, Flipkart, Meesho, etc.)',
        ];
    }

    private function saveTempFile($file, string $ext): ?string
    {
        $tempDir = storage_path('app/temp/gstr');
        if (!file_exists($tempDir)) mkdir($tempDir, 0755, true);

        $filename = uniqid('gstr_', true) . '.' . $ext;
        $file->move($tempDir, $filename);
        $fullPath = $tempDir . DIRECTORY_SEPARATOR . $filename;

        if (!file_exists($fullPath)) {
            Log::error('Failed to save temp file', ['path' => $fullPath]);
            return null;
        }

        return $fullPath;
    }

    // ════════════════════════════════════════════════════════════════════════
    //  PROCESS TRANSACTIONS   (UNCHANGED)
    // ════════════════════════════════════════════════════════════════════════

     public function processTransactions(Request $request)
{
    set_time_limit(300);
    ini_set('memory_limit', '512M');

    try {
        $client = session('gstr_selected_client') ?? session('selected_client');
        if (!$client) return response()->json(['error' => 'Client not selected'], 400);

        $batchId       = $request->input('batch_id');
        $transactions  = $request->input('transactions', []);
        $period        = $request->input('period');
        $filingPeriod  = $request->input('filing_period'); // "Q1/2025-26" or null
        $replacePeriod = $request->boolean('replace_period', false);
        $sourceFormat  = $request->input('source_format');

        $docIssueOverride = $request->input('doc_issue_override');
        $hsnOverride      = $request->input('hsn_override');

        if (!$batchId)            return response()->json(['error' => 'batch_id missing'], 400);
        if (empty($transactions)) return response()->json(['error' => 'No transactions to save.'], 400);
        if ($period && !preg_match('/^\d{2}\/\d{4}$/', $period)) {
            return response()->json(['error' => 'Invalid period format.'], 400);
        }
        if ($filingPeriod && !preg_match('/^Q[1-4]\/\d{4}-\d{2}$/', $filingPeriod)) {
            return response()->json(['error' => 'Invalid filing_period format.'], 400);
        }

        Log::info('Processing transactions', [
            'count' => count($transactions), 'batch_id' => $batchId, 'filing_period' => $filingPeriod,
        ]);

        $clientGstinUpper = strtoupper(trim($client['gstin'] ?? ''));

        // ════════════════════════════════════════════════════════════
        // ════════════════════════════════════════════════════════════
        $lookupPeriodKey  = $filingPeriod ?: $period;
        $lookupSessionKey = "gstr_tax_invoice_lookup_{$clientGstinUpper}_{$lookupPeriodKey}";
        $taxInvoiceLookup = session($lookupSessionKey);

        if (!empty($taxInvoiceLookup)) {
            $transactions = \App\Parsers\MeeshoParserService::mergeTaxInvoiceData($transactions, $taxInvoiceLookup);
            Log::info('Tax Invoice lookup merged into transactions', [
                'lookup_count' => count($taxInvoiceLookup),
            ]);
        }

        // ════════════════════════════════════════════════════════════════════
        //  MYNTRA CROSS-FILE DEDUP
        // ════════════════════════════════════════════════════════════════════
        //
        //  ⚠️ FIX: the old key here was JUST item_id. That's wrong — a
        //  sale row (Packed/Sales file, positive taxable_value) and its
        //  later post-delivery return (RT file, negative taxable_value)
        //  legitimately SHARE the same item_id (that's the whole point —
        //  it's how we match a return back to the sale it belongs to).
        //  Keying on item_id alone meant that whichever row happened to
        //  land first in the array (usually the sale, since Sales/Packed
        //  is uploaded first) survived, and the matching return row was
        //  thrown away as a "duplicate" — so the return NEVER netted off
        //  the sale, silently inflating taxable value (this is exactly
        //  the failure mode called out in MyntraParser's class doc-
        //  comment for the RT file type).
        //
        //  The only rows that ARE true duplicates are ones with the SAME
        //  item_id AND the SAME sign — e.g. the live Packed file already
        //  containing an IC/RTO row that also appears in the dedicated
        //  RTO file (both negative, same event reported twice). Keying
        //  on item_id + sign keeps a sale and its return as two distinct
        //  transactions (so they net off correctly at summary time)
        //  while still collapsing genuine same-event duplicates.
        // ════════════════════════════════════════════════════════════════════
        $myntraDupCount = 0;
        $seenMyntraKeys = [];
        $dedupedTransactions = [];

        foreach ($transactions as $trans) {
            $plat = strtolower(trim($trans['platform'] ?? ''));
            $iid  = trim((string)($trans['item_id'] ?? ''));

            if (str_starts_with($plat, 'myntra') && $iid !== '') {
                $sign = ((float)($trans['taxable_value'] ?? 0)) >= 0 ? 'POS' : 'NEG';
                $key  = $iid . '|' . $sign;

                if (isset($seenMyntraKeys[$key])) {
                    $myntraDupCount++;
                    continue;
                }
                $seenMyntraKeys[$key] = true;
            }

            $dedupedTransactions[] = $trans;
        }
        $transactions = $dedupedTransactions;

        if ($myntraDupCount > 0) {
            Log::info('Myntra duplicate rows skipped across Packed/RT/RTO uploads', [
                'duplicates_removed' => $myntraDupCount,
            ]);
        }

        // ════════════════════════════════════════════════════════════
        //  MEESHO ORDER-LEVEL DEDUP
        // ════════════════════════════════════════════════════════════
        $meeshoDupCount = 0;
        $seenMeeshoKeys = [];
        $dedupedTransactions2 = [];

        foreach ($transactions as $trans) {
            $plat = strtolower(trim($trans['platform'] ?? ''));
            $src  = strtolower(trim($trans['source_file'] ?? ''));
            $ord  = BaseParser::normalizeOrderKey($trans['order_number'] ?? '');

            if (str_starts_with($plat, 'meesho') && $ord !== '' && in_array($src, ['meesho_sales', 'meesho_return'], true)) {
                $txval = round((float)($trans['taxable_value'] ?? 0), 2);
                $key = $ord . '|' . $src . '|' . $txval;
                if (isset($seenMeeshoKeys[$key])) {
                    $meeshoDupCount++;
                    continue;
                }
                $seenMeeshoKeys[$key] = true;
            }

            $dedupedTransactions2[] = $trans;
        }
        $transactions = $dedupedTransactions2;

        if ($meeshoDupCount > 0) {
            Log::info('Meesho duplicate order rows skipped', [
                'duplicates_removed' => $meeshoDupCount,
            ]);
        }

        // ═════════════════════════════════════════════════════════════
        //  DUPLICATE-FORMAT CHECK (per platform, per its own month)
        // ═════════════════════════════════════════════════════════════
        if (!$replacePeriod) {
            $platformsInPayload = collect($transactions)->pluck('platform')->filter()->unique();
            foreach ($platformsInPayload as $plat) {
                $sampleTrans  = collect($transactions)->firstWhere('platform', $plat);
                $samplePeriod = $sampleTrans['period'] ?? $period;

                $dupError = $this->checkDuplicateFormat(
                    $clientGstinUpper,
                    $samplePeriod,
                    $plat,
                    $sourceFormat,
                    $filingPeriod
                );
                if ($dupError) {
                    return response()->json($dupError, 409);
                }
            }
        }

        DB::beginTransaction();

        // ═════════════════════════════════════════════════════════════
        // ← FIXED: REPLACE LOGIC IS NOW PER-PLATFORM + PER-MONTH, NOT
        // PER-FILING_PERIOD (QUARTER). Previously this deleted the ENTIRE
        // quarter's batch (all 3 months) whenever ANY single month was
        // re-uploaded with replace_period=true — that is exactly why
        // uploading May was wiping out April, and uploading June was
        // wiping out May. Now we only delete transactions belonging to
        // the SAME platform(s) AND the SAME month that is actually being
        // re-uploaded, leaving the other months of the quarter untouched.
        //
        // We also STOP deleting the GstrBatch row here. For quarterly
        // filing, one GstrBatch/batch_id spans all 3 months — deleting it
        // would orphan the other two months' transactions (summary page
        // looks them up by batch_id). GstrBatch is upserted later in
        // computeB2CS() via updateOrCreate(), so it's safe to leave alone.
        // ═════════════════════════════════════════════════════════════
        if ($replacePeriod) {
            $platformsInPayload = collect($transactions)->pluck('platform')->filter()->unique();

            foreach ($platformsInPayload as $plat) {
                $sampleTrans  = collect($transactions)->firstWhere('platform', $plat);
                $samplePeriod = $sampleTrans['period'] ?? $period;

                $deleted = GstrTransaction::where('user_id', Auth::id())
                    ->where('gstin', $client['gstin'])
                    ->where('platform', $plat)
                    ->where('period', $samplePeriod)
                    //->when($filingPeriod, fn($q) => $q->where('filing_period', $filingPeriod))
                    ->delete();

                    //                     ->when($filingPeriod, fn($q) => $q->where('filing_period', $filingPeriod))


                Log::info('Replaced existing period data (scoped to platform+month)', [
                    'platform'      => $plat,
                    'period'        => $samplePeriod,
                    'filing_period' => $filingPeriod,
                    'rows_deleted'  => $deleted,
                ]);
            }
        }

        // ═════════════════════════════════════════════════════════════
        //  ENSURE ALL MONTHS OF A QUARTER SHARE ONE batch_id
        //
        //  If a GstrBatch already exists for this client+filing_period
        //  (i.e. April was already uploaded under some batch_id), reuse
        //  THAT batch_id for this upload too, instead of trusting
        //  whatever batch_id the frontend happened to send. This is what
        //  guarantees April + May + June transactions all end up under
        //  the SAME batch_id, so GstrSummaryController (which queries by
        //  a single batch_id) can see all three months together.
        // ═════════════════════════════════════════════════════════════
        if ($filingPeriod) {
            $existingBatch = GstrBatch::where('user_id', Auth::id())
                ->where('client_gstin', $client['gstin'])
                ->where('filing_period', $filingPeriod)
                ->first();

            if ($existingBatch) {
                $batchId = $existingBatch->batch_id;
            }
        }

        $savedCount = 0;
        foreach ($transactions as $trans) {
            $t                  = new GstrTransaction();
            $t->batch_id        = $batchId;
            $t->client_id       = $client['id']    ?? null;
            $t->user_id         = Auth::id();
            $t->gstin           = $client['gstin'] ?? '';
            $t->period          = $trans['period'] ?? $period;
            $t->filing_period   = $filingPeriod;
            $t->platform        = $trans['platform']       ?? 'unknown';
            $t->source_format   = $sourceFormat ?? ($trans['source_format'] ?? null);
            $t->place_of_supply = BaseParser::normalizePos($trans['place_of_supply'] ?? '');
            $t->taxable_value   = $trans['taxable_value']  ?? 0;
            $t->gst_rate        = $trans['gst_rate']       ?? 0;
            $t->igst_amount     = $trans['igst_amount']    ?? 0;
            $t->cgst_amount     = $trans['cgst_amount']    ?? 0;
            $t->sgst_amount     = $trans['sgst_amount']    ?? 0;
            $t->invoice_number  = $trans['invoice_number'] ?? null;
            $t->invoice_date    = BaseParser::sanitizeDate($trans['invoice_date'] ?? null);
            $t->quantity        = isset($trans['quantity']) ? (int)$trans['quantity'] : 0;
            $t->hsn_code        = $trans['hsn_code']       ?? null;
            $t->buyer_gstin     = $trans['buyer_gstin']    ?? null;
            $t->status          = 'processed';
            $t->save();
            $savedCount++;
        }

        DB::commit();

        if (!empty($taxInvoiceLookup)) {
            session()->forget($lookupSessionKey);
        }

        Log::info('Transactions saved', ['count' => $savedCount, 'batch_id' => $batchId]);

        return response()->json([
            'success'  => true,
            'message'  => "{$savedCount} transactions saved",
            'batch_id' => $batchId,
            'duplicates_removed' => $myntraDupCount + $meeshoDupCount,
        ]);

    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error('Process Transactions Error', ['message' => $e->getMessage()]);
        return response()->json(['error' => 'DB Error: ' . $e->getMessage()], 400);
    }
}

    // ════════════════════════════════════════════════════════════════════════
    //  COMPUTE B2CS   (UNCHANGED)
    // ════════════════════════════════════════════════════════════════════════

    public function computeB2CS(Request $request)
{
    try {
        $batchId = $request->input('batch_id');
        $client  = session('gstr_selected_client') ?? session('selected_client');

        if (!$client)  return response()->json(['error' => 'Client not selected.'], 400);
        if (!$batchId) return response()->json(['error' => 'batch_id missing'], 400);

        $transactions = GstrTransaction::where('user_id', Auth::id())
            ->where('batch_id', $batchId)
            ->where('status', 'processed')
            ->get();

        if ($transactions->isEmpty()) {
            return response()->json(['error' => "No transactions found for batch '{$batchId}'."], 400);
        }

        $platforms = $transactions->pluck('platform')->unique()->filter()->implode(', ');
        $totalTax  = $transactions->sum('igst_amount')
                   + $transactions->sum('cgst_amount')
                   + $transactions->sum('sgst_amount');

        // filing_period is read from the transactions themselves. Since
        // the fix above guarantees all months of a quarter share the same
        // batch_id, this will now correctly reflect the WHOLE quarter's
        // filing_period even if this particular request only just saved
        // one month's worth of rows.
        $filingPeriod = $transactions->first()->filing_period ?? null;

        $updateData = [
            'user_id'           => Auth::id(),
            'client_id'         => $client['id']    ?? 0,
            'client_name'       => $client['name']  ?? '',
            'client_gstin'      => $client['gstin'] ?? '',
            'period'            => $transactions->first()->period,
            'filing_period'     => $filingPeriod,
            'frequency'         => $filingPeriod ? 'quarterly' : 'monthly',
            'platforms'         => $platforms,
            'transaction_count' => $transactions->count(),
            'taxable_value'     => round($transactions->sum('taxable_value'), 2),
            'total_tax'         => round($totalTax, 2),
        ];

        $docIssueOverrideReq = $request->input('doc_issue_override');
        $hsnOverrideReq      = $request->input('hsn_override');

        if (!empty($docIssueOverrideReq)) {
            $updateData['doc_issue_override'] = json_encode($docIssueOverrideReq);
        }
        if (!empty($hsnOverrideReq)) {
            $updateData['hsn_override'] = json_encode($hsnOverrideReq);
        }

        // Note: updateOrCreate matches on batch_id. Because
        // processTransactions() now forces all months of the same
        // filing_period onto the SAME batch_id, this always updates the
        // one existing quarter-level GstrBatch row instead of creating a
        // second, orphaned one.
        GstrBatch::updateOrCreate(['batch_id' => $batchId], $updateData);

        $period  = $transactions->first()->period;
        $summary = $transactions
            ->groupBy(fn($t) => strtoupper($t->place_of_supply) . '_' . $t->gst_rate)
            ->map(function ($group) {
                $first   = $group->first();
                $isIntra = ($group->sum('igst_amount') == 0);
                return [
                    'place_of_supply' => strtoupper($first->place_of_supply),
                    'gst_rate'        => (float)$first->gst_rate,
                    'sply_ty'         => $isIntra ? 'INTRA' : 'INTER',
                    'taxable_value'   => round($group->sum('taxable_value'), 2),
                    'cgst'            => round($group->sum('cgst_amount'), 2),
                    'sgst'            => round($group->sum('sgst_amount'), 2),
                    'igst'            => round($group->sum('igst_amount'), 2),
                    'cess'            => 0,
                    'count'           => $group->count(),
                    'total_quantity'  => (int)$group->sum('quantity'),
                ];
            })
            ->values()
            ->toArray();

        session([
            'gstr_selected_client' => $client,
            'selected_client'      => $client,
            'gstr_batch_id'        => $batchId,
            'gstr_summary'         => $summary,
            'gstr_period'          => $period,
            'gstr_filing_period'   => $filingPeriod,
        ]);

        Log::info('B2CS computed', ['batch_id' => $batchId, 'summary_rows' => count($summary)]);

        return response()->json([
            'success'           => true,
            'message'           => 'B2CS computed successfully',
            'summary'           => $summary,
            'transaction_count' => $transactions->count(),
            'redirect'          => route('gstr.summary'),
        ]);

    } catch (\Throwable $e) {
        Log::error('Compute B2CS Error', ['message' => $e->getMessage()]);
        return response()->json(['error' => 'Compute Error: ' . $e->getMessage()], 400);
    }
}

    // ════════════════════════════════════════════════════════════════════════
    //  DEBUG FILE   (UNCHANGED)
    // ════════════════════════════════════════════════════════════════════════

    public function debugFile(Request $request)
    {
        try {
            $request->validate(['file' => 'required|file']);

            $file = $request->file('file');
            $ext  = strtolower($file->getClientOriginalExtension());

            $tempDir = storage_path('app/temp/gstr');
            if (!is_dir($tempDir)) mkdir($tempDir, 0755, true);

            $tempPath = $tempDir . '/' . uniqid('debug_', true) . '.' . $ext;
            $file->move($tempDir, basename($tempPath));

            $spreadsheet = IOFactory::load($tempPath);

            $allSheets = [];
            $summary   = [];

            foreach ($spreadsheet->getAllSheets() as $sheet) {
                $sheetName = $sheet->getTitle();
                $rows = $sheet->toArray('', false, false, false);

                $nonEmptyCount = count(array_filter($rows, function ($row) {
                    foreach ($row as $cell) {
                        if ($cell !== null && trim((string)$cell) !== '') return true;
                    }
                    return false;
                }));

                $allSheets[$sheetName] = array_slice($rows, 0, 5);
                $summary[] = [
                    'name'       => $sheetName,
                    'total_rows' => count($rows),
                    'data_rows'  => $nonEmptyCount,
                    'columns'    => count($rows[0] ?? []),
                ];
            }

            @unlink($tempPath);

            return response()->json([
                'filename'       => $file->getClientOriginalName(),
                'sheets_summary' => $summary,
                'sheets'         => $allSheets,
            ]);

        } catch (\Throwable $e) {
            Log::error('Debug File Error', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}