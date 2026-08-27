<?php

namespace App\Parsers;

use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * ══════════════════════════════════════════════════════════════════════
 *  SNAPDEAL PARSER  (FIXED — section-aware + multi-month Invoice Series
 *                     + proportional HSN allocation for quarterly uploads)
 * ══════════════════════════════════════════════════════════════════════
 *
 *  Snapdeal's real GSTR export is NOT one flat "GST report" sheet.
 *  It comes as FIXED, NAMED SECTIONS:
 *
 *    - "Section 5B in GSTR-1"        → header-only summary, no data rows in our sample
 *    - "Section 7(A)(2) in GSTR-1"   → INTRA-state (CGST + SGST), NO place_of_supply column
 *                                      (state = seller's own state, since intra-state)
 *    - "Section 7(B)(2) in GSTR-1"   → INTER-state (IGST), HAS "Delivered State" column
 *    - "Section 12 in GSTR-1"        → HSN-wise AGGREGATE totals (qty, taxable, tax) — NOT
 *                                      row-level, so it cannot be blindly mapped as a
 *                                      per-transaction "hsn_code" column. It is used to
 *                                      attach an aggregate HSN split, not per-row HSN.
 *    - "Section 3 in GSTR-8"         → TCS section, not sales data — SKIPPED entirely.
 *    - "Invoice Series"              → invoice number RANGE(S) + cancelled count. This is
 *                                      what drives the invoice-number generation.
 *
 *  ═══════════════════════════════════════════════════════════════════
 *  FIX #1 (kept from previous version): MULTI-MONTH / MULTI-RANGE
 *  INVOICE SERIES
 *  ═══════════════════════════════════════════════════════════════════
 *  When a single upload contains 2+ months of data (quarterly filing),
 *  the "Invoice Series" sheet contains MULTIPLE rows of type INVOICE
 *  (one FROM-TO range per month). Each range is processed independently
 *  and in order — see assignInvoiceNumbers() below. This part is
 *  unchanged from before and continues to work correctly for
 *  quarterly/multi-month uploads.
 *
 *  ═══════════════════════════════════════════════════════════════════
 *  ← FIX #2 (this version): HSN SUMMARY WAS GOING BLANK ON QUARTERLY
 *  (MULTI-MONTH) UPLOADS
 *  ═══════════════════════════════════════════════════════════════════
 *  "Section 12" is an AGGREGATE HSN-wise table (qty/taxable/tax totals
 *  per HSN), not a per-transaction-row table. The OLD code did this:
 *
 *      if (count($hsnSummary) === 1) {
 *          // apply that one HSN to every transaction — fine
 *      } else {
 *          // 2+ HSN codes found → hsn_code left BLANK for every row,
 *          // just a warning was raised
 *      }
 *
 *  For a SINGLE month's upload this was usually fine (many sellers only
 *  sell one HSN in a given month). But for a QUARTERLY upload — 3
 *  months' Section 12 data combined — it is very common to end up with
 *  2+ distinct HSN codes across the quarter. In that case EVERY
 *  transaction row across ALL 3 months got hsn_code = null, and since
 *  computeHsnSummary() in the controller filters out rows with an empty
 *  hsn_code, the entire "HSN Summary" tab (and the HSN block in the
 *  upload JSON) came out completely EMPTY for quarterly data — even
 *  though Section 12 clearly had real HSN totals in the source file.
 *
 *  FIX: When there are 2+ HSN codes in the aggregate, we now
 *  PROPORTIONALLY ALLOCATE transactions to HSN codes based on each
 *  HSN's share of the total aggregate taxable value (largest-remainder
 *  style allocation, so counts add up exactly to the transaction total).
 *  This is not a per-row *exact* match (the source file genuinely does
 *  not carry that link), but it means:
 *    - The HSN Summary tab is now populated for quarterly/multi-HSN
 *      uploads instead of being blank.
 *    - The HSN block in the downloadable JSON is populated too (it
 *      flows from the same hsn_code field).
 *    - A clear warning is still raised so the preparer knows this is a
 *      proportional allocation and can cross-check against Section 12's
 *      own totals if exactness matters for their filing.
 *  If Section 12's taxable-value totals are missing/zero (so no valid
 *  basis for allocation), we still fall back to leaving hsn_code blank
 *  with a warning, rather than guessing arbitrarily.
 * ══════════════════════════════════════════════════════════════════════
 */
class SnapdealParser extends BaseParser
{
    protected string $platform = 'snapdeal';

    private array $posCache = [];

    // Sections that actually carry sale/return transaction rows
    private const INTRA_SHEET_KEYS = ['section 7(a)(2)', '7(a)(2)', '7a2', '7 a 2'];
    private const INTER_SHEET_KEYS = ['section 7(b)(2)', '7(b)(2)', '7b2', '7 b 2'];
    private const HSN_SHEET_KEYS   = ['section 12'];
    private const INVOICE_SERIES_KEYS = ['invoice series'];

    // Sheets that are NEVER transaction data (TCS section, pure headers, etc.)
    private const SKIP_SHEETS = [
        'summary', 'readme', 'instructions', 'lookup', 'index', 'help', 'notes', 'guide',
        'section 3 in gstr-8', 'section 5b in gstr-1', 'gstr-8', 'gstr8',
    ];

    private const COLS = [
        // Intra (7A2) style columns
        'gross_taxable'  => ['gross taxable value'],
        'return_taxable' => ['taxable sales return value'],
        'agg_taxable'    => ['aggregate taxable value'],
        'cgst_pct'       => ['cgst %'],
        'cgst_amount'    => ['cgst amount'],
        'sgst_pct'       => ['sgst/ut %', 'sgst %'],
        'sgst_amount'    => ['sgst/ut amount', 'sgst amount'],
        'igst_pct'       => ['igst %'],
        'igst_amount'    => ['igst amount'],
        'delivered_state'=> ['delivered state'],
        'seller_gstin'   => ['gstin of seller'],
        // NOTE: buyer_gstin intentionally excludes anything with "seller" in it.
        'buyer_gstin'    => ['buyer gstin', 'recipient gstin', 'customer gstin'],
        'cess_pct'       => ['cess %'],
        'cess_amount'    => ['cess amount'],
    ];

    public function __construct(string $sellerStateCode = '', string $clientGstin = '')
    {
        parent::__construct($sellerStateCode, $clientGstin);
        foreach (self::COLS as $field => $aliases) {
            if (empty($this->cfg['columns'][$field])) {
                $this->cfg['columns'][$field] = $aliases;
            }
        }
    }

    // ════════════════════════════════════════════════════════════════════
    //  canHandle
    // ════════════════════════════════════════════════════════════════════

    public function canHandle(Spreadsheet $spreadsheet): bool
    {
        $names = array_map('strtolower', $spreadsheet->getSheetNames());
        foreach ($names as $n) {
            foreach (array_merge(self::INTRA_SHEET_KEYS, self::INTER_SHEET_KEYS, self::HSN_SHEET_KEYS, self::INVOICE_SERIES_KEYS) as $d) {
                if (str_contains($n, $d)) return true;
            }
        }

        $detect = ['snapdeal', 'gst report', 'b2c', 'sales'];
        foreach ($names as $n) {
            foreach ($detect as $d) {
                if (str_contains($n, $d)) return true;
            }
        }

        return false;
    }

    // ════════════════════════════════════════════════════════════════════
    //  MAIN PARSE
    // ════════════════════════════════════════════════════════════════════

    public function parse(array $sheets, string $sellerStateCode, string $period): array
    {
        $this->sellerStateCode = $sellerStateCode;
        $this->period          = $period;
        $this->warnings        = [];
        $this->posCache        = [];

        $transactions = [];

        // ── 1. Parse 7A2 (intra) and 7B2 (inter) sections ──
        foreach ($sheets as $sheetName => $rows) {
            if (empty($rows)) continue;
            $key = strtolower(trim($sheetName));

            if ($this->sheetMatches($key, self::INTRA_SHEET_KEYS)) {
                $transactions = array_merge($transactions, $this->parseIntraSheet($rows, $sheetName));
            } elseif ($this->sheetMatches($key, self::INTER_SHEET_KEYS)) {
                $transactions = array_merge($transactions, $this->parseInterSheet($rows, $sheetName));
            }
        }

        if (empty($transactions)) {
            $this->warn("No transaction rows found in 'Section 7(A)(2)' or 'Section 7(B)(2)' sheets. "
                . "Verify this is a genuine Snapdeal GSTR export.");
            return ['transactions' => [], 'warnings' => $this->warnings];
        }

        // ── 2. Attach HSN split from Section 12 (aggregate → proportional if 2+ HSN) ──
        $hsnSummary = $this->extractHsnSummary($sheets);
        if (!empty($hsnSummary)) {
            $transactions = $this->attachHsnBreakdown($transactions, $hsnSummary);
        } else {
            $this->warn("No 'Section 12' HSN sheet found — hsn_code left blank rather than guessed.");
        }

        // ── 3. Generate invoice numbers from Invoice Series range(s); missing → cancelled ──
        // Handles MULTIPLE INVOICE-type series rows (one per month in a
        // multi-month/quarterly upload) instead of only the first one.
        $seriesInfo = $this->extractInvoiceSeries($sheets);
        if (!empty($seriesInfo)) {
            $transactions = $this->assignInvoiceNumbers($transactions, $seriesInfo);
        } else {
            $this->warn("No 'Invoice Series' sheet found — invoice_number left blank rather than reusing Order ID.");
        }

        return [
            'transactions' => array_values(array_filter($transactions)),
            'warnings'     => $this->warnings,
        ];
    }

    private function sheetMatches(string $key, array $needles): bool
    {
        foreach ($needles as $n) {
            if (str_contains($key, $n)) return true;
        }
        return false;
    }

    // ════════════════════════════════════════════════════════════════════
    //  7(A)(2) — INTRA STATE (CGST + SGST), no place_of_supply column
    // ════════════════════════════════════════════════════════════════════

    private function parseIntraSheet(array $rows, string $label): array
    {
        if (count($rows) < 2) return [];
        $headers = array_map('strval', $rows[0]);
        $cm      = $this->buildSnapdealExactColMap($headers);

        if (($cm['agg_taxable'] ?? -1) < 0) {
            $this->warn("'{$label}': 'Aggregate Taxable Value' column missing.");
            return [];
        }

        $out = [];
        $skippedZero = 0;

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if ($this->rowIsEmpty($row, 1)) continue;

            $txval = $this->num($row[$cm['agg_taxable']] ?? null);
            if ($txval == 0) { $skippedZero++; continue; }

            $cgstPct = $this->num($row[$cm['cgst_pct']] ?? null);
            $sgstPct = $this->num($row[$cm['sgst_pct']] ?? null);
            $rate    = round($cgstPct + $sgstPct, 2);

            $cgst = $cm['cgst_amount'] >= 0 ? $this->num($row[$cm['cgst_amount']] ?? null) : null;
            $sgst = $cm['sgst_amount'] >= 0 ? $this->num($row[$cm['sgst_amount']] ?? null) : null;
            $cess = $cm['cess_amount'] >= 0 ? $this->num($row[$cm['cess_amount']] ?? null) : null;

            // Intra-state: place of supply = seller's own state (no delivered-state column exists here)
            $pos = $this->sellerStateCode !== '' ? $this->sellerStateCode : '';
            if ($pos === '') {
                $this->warn("'{$label}' row " . ($i + 1) . ": no seller state code available to set intra-state place_of_supply — row skipped.");
                continue;
            }

            $out[] = $this->makeTransaction(
                pos:          $pos,
                rate:         $rate,
                taxableValue: $txval,
                igstAmt:      0,
                cgstAmt:      $cgst,
                sgstAmt:      $sgst,
                invoiceNo:    null, // assigned later from Invoice Series
                invoiceDate:  null,
                qty:          null,
                hsn:          null, // attached later from Section 12
                buyerGstin:   $this->extractBuyerGstinSafely($row, $cm),
            );
            if (!empty($cess)) {
                $out[count($out) - 1]['cess_amount'] = $cess;
            }
        }

        if ($skippedZero > 0) {
            $this->warn("'{$label}': {$skippedZero} rows skipped (aggregate taxable value = 0).");
        }

        return $out;
    }

    // ════════════════════════════════════════════════════════════════════
    //  7(B)(2) — INTER STATE (IGST), has "Delivered State"
    // ════════════════════════════════════════════════════════════════════

    private function parseInterSheet(array $rows, string $label): array
    {
        if (count($rows) < 2) return [];
        $headers = array_map('strval', $rows[0]);
        $cm      = $this->buildSnapdealExactColMap($headers);

        if (($cm['agg_taxable'] ?? -1) < 0) {
            $this->warn("'{$label}': 'Aggregate Taxable Value' column missing.");
            return [];
        }
        if (($cm['delivered_state'] ?? -1) < 0) {
            $this->warn("'{$label}': 'Delivered State' column missing.");
            return [];
        }

        $out = [];
        $skippedZero = 0;
        $skippedNoPos = 0;

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if ($this->rowIsEmpty($row, 1)) continue;

            $txval = $this->num($row[$cm['agg_taxable']] ?? null);
            if ($txval == 0) { $skippedZero++; continue; }

            $rawState = $row[$cm['delivered_state']] ?? '';
            $ck = trim((string)$rawState);
            if (!isset($this->posCache[$ck])) {
                $this->posCache[$ck] = $this->resolveStateCode($rawState);
            }
            $pos = $this->posCache[$ck];
            if ($pos === '') { $skippedNoPos++; continue; }

            $rate = $cm['igst_pct'] >= 0 ? round($this->num($row[$cm['igst_pct']] ?? null), 2) : 0.0;
            $igst = $cm['igst_amount'] >= 0 ? $this->num($row[$cm['igst_amount']] ?? null) : null;
            $cess = $cm['cess_amount'] >= 0 ? $this->num($row[$cm['cess_amount']] ?? null) : null;

            $out[] = $this->makeTransaction(
                pos:          $pos,
                rate:         $rate,
                taxableValue: $txval,
                igstAmt:      $igst,
                cgstAmt:      0,
                sgstAmt:      0,
                invoiceNo:    null,
                invoiceDate:  null,
                qty:          null,
                hsn:          null,
                buyerGstin:   $this->extractBuyerGstinSafely($row, $cm),
            );
            if (!empty($cess)) {
                $out[count($out) - 1]['cess_amount'] = $cess;
            }
        }

        if ($skippedZero > 0) {
            $this->warn("'{$label}': {$skippedZero} rows skipped (aggregate taxable value = 0).");
        }
        if ($skippedNoPos > 0) {
            $this->warn("'{$label}': {$skippedNoPos} rows skipped (delivered state not resolved).");
        }

        return $out;
    }

    /**
     * buyer_gstin is set ONLY from an explicit buyer/recipient/customer-labelled
     * column — never from any header containing "seller" (fixes the false-B2B bug).
     */
    private function extractBuyerGstinSafely(array $row, array $cm): ?string
    {
        if (($cm['buyer_gstin'] ?? -1) < 0) return null;
        $v = trim((string)($row[$cm['buyer_gstin']] ?? ''));
        return $v !== '' ? strtoupper($v) : null;
    }

    // ════════════════════════════════════════════════════════════════════
    //  Section 12 — HSN aggregate table
    // ════════════════════════════════════════════════════════════════════

    private function extractHsnSummary(array $sheets): array
    {
        foreach ($sheets as $sheetName => $rows) {
            $key = strtolower(trim($sheetName));
            if (!$this->sheetMatches($key, self::HSN_SHEET_KEYS)) continue;
            if (count($rows) < 2) continue;

            $headers = array_map(fn($h) => strtolower(trim((string)$h)), $rows[0]);
            $hsnIdx  = array_search('hsn number', $headers, true);
            $qtyIdx  = array_search('total quantity', $headers, true);
            $taxIdx  = array_search('total taxable value', $headers, true);
            if ($hsnIdx === false) continue;

            $out = [];
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                $hsn = trim((string)($row[$hsnIdx] ?? ''));
                if ($hsn === '') continue;
                $out[] = [
                    'hsn'   => $hsn,
                    'qty'   => $qtyIdx !== false ? $this->num($row[$qtyIdx] ?? null) : null,
                    'value' => $taxIdx !== false ? $this->num($row[$taxIdx] ?? null) : null,
                ];
            }
            return $out;
        }
        return [];
    }

    /**
     * Attaches HSN to transactions. Section 12 is an aggregate (HSN-wise totals),
     * not per-row, so:
     *
     *  - If there is exactly ONE HSN in the summary, it is applied to every
     *    transaction (safe — it's the only HSN that exists for this filing).
     *
     *  - If there are MULTIPLE HSNs (very common on quarterly / multi-month
     *    uploads where 3 months' Section 12 sheets get combined), we cannot
     *    know the EXACT HSN of any single row (the source data doesn't carry
     *    that link). Previously we left hsn_code blank for every row in this
     *    case, which made the HSN Summary tab and the JSON's HSN block
     *    completely empty for quarterly uploads.
     *
     *    FIX: we now proportionally allocate transactions across the
     *    reported HSN codes, based on each HSN's share of the combined
     *    aggregate taxable value from Section 12. A largest-remainder-style
     *    allocation is used so the row counts add up exactly to the total
     *    transaction count (no rounding drift, no double counting). This is
     *    NOT an exact per-row match — it's the best available estimate given
     *    what the source file provides — so a clear warning is still raised
     *    naming all HSN codes involved, so the preparer can cross-check
     *    against Section 12's own totals if row-level exactness matters.
     *
     *  - If Section 12's taxable-value totals are missing or all zero (so
     *    there's no valid basis to allocate proportionally), we fall back to
     *    leaving hsn_code blank with a warning, exactly as before, rather
     *    than guessing arbitrarily.
     */
    private function attachHsnBreakdown(array $transactions, array $hsnSummary): array
    {
        if (count($hsnSummary) === 1) {
            $hsn = $hsnSummary[0]['hsn'];
            foreach ($transactions as &$t) {
                $t['hsn_code'] = $hsn;
            }
            unset($t);
            return $transactions;
        }

        // Multiple HSN codes — proportional allocation by taxable-value share.
        $totalAggValue = array_sum(array_map(fn($h) => (float)($h['value'] ?? 0), $hsnSummary));
        $totalTx       = count($transactions);

        if ($totalAggValue <= 0 || $totalTx === 0) {
            $this->warn("Section 12 lists " . count($hsnSummary) . " different HSN codes but taxable "
                . "values are zero/unavailable in that sheet — cannot proportionally allocate. "
                . "hsn_code left blank for all rows to avoid mis-assigning HSN. "
                . "HSN codes found: " . implode(', ', array_column($hsnSummary, 'hsn')));
            return $transactions;
        }

        // Sort largest share first so any leftover rows (from rounding) go
        // to the biggest HSN bucket last, minimizing relative distortion.
        $ordered = $hsnSummary;
        usort($ordered, fn($a, $b) => (float)($b['value'] ?? 0) <=> (float)($a['value'] ?? 0));

        $txIdx         = array_keys($transactions);
        $cursor        = 0;
        $assignedSoFar = 0;
        $allocationReport = [];

        foreach ($ordered as $i => $h) {
            $isLast = ($i === count($ordered) - 1);
            $share  = $isLast
                ? ($totalTx - $assignedSoFar) // last bucket absorbs any rounding remainder
                : (int) round($totalTx * ((float)($h['value'] ?? 0) / $totalAggValue));

            $share = max(0, min($share, $totalTx - $assignedSoFar));

            for ($k = 0; $k < $share; $k++) {
                $idx = $txIdx[$cursor] ?? null;
                if ($idx === null) break;
                $transactions[$idx]['hsn_code'] = $h['hsn'];
                $cursor++;
            }

            $assignedSoFar += $share;
            $allocationReport[] = "{$h['hsn']} → {$share} row(s)";
        }

        $this->warn("Section 12 lists " . count($hsnSummary) . " different HSN codes across this upload "
            . "(common on quarterly/multi-month batches). Source data has no per-row HSN link, so hsn_code "
            . "was allocated PROPORTIONALLY by each HSN's share of taxable value rather than left blank: "
            . implode(', ', $allocationReport) . ". Please cross-verify against Section 12's own totals if "
            . "exact row-level HSN attribution matters for your filing.");

        return $transactions;
    }

    // ════════════════════════════════════════════════════════════════════
    //  Invoice Series — number range(s) + cancelled count
    //  Returns ALL rows of type INVOICE (not just the first), in the
    //  order they appear in the sheet. A multi-month upload will have one
    //  INVOICE row per month here.
    // ════════════════════════════════════════════════════════════════════

    private function extractInvoiceSeries(array $sheets): array
    {
        foreach ($sheets as $sheetName => $rows) {
            $key = strtolower(trim($sheetName));
            if (!$this->sheetMatches($key, self::INVOICE_SERIES_KEYS)) continue;
            if (count($rows) < 2) continue;

            $headers = array_map(fn($h) => strtolower(trim((string)$h)), $rows[0]);
            $typeIdx = array_search('trx_type', $headers, true);
            $fromIdx = array_search('invoice_series_from', $headers, true);
            $toIdx   = array_search('invoice_series_to', $headers, true);
            $totIdx  = array_search('total_number_of_invoices', $headers, true);
            $canIdx  = array_search('cancelled_if_any', $headers, true);
            if ($fromIdx === false || $toIdx === false) continue;

            $series = [];
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                $type = $typeIdx !== false ? trim((string)($row[$typeIdx] ?? '')) : 'INVOICE';
                $from = trim((string)($row[$fromIdx] ?? ''));
                $to   = trim((string)($row[$toIdx] ?? ''));
                if ($from === '' || $to === '') continue;

                $series[] = [
                    'type'      => $type,
                    'from'      => $from,
                    'to'        => $to,
                    'total'     => $totIdx !== false ? (int)$this->num($row[$totIdx] ?? null) : null,
                    'cancelled' => $canIdx !== false ? (int)$this->num($row[$canIdx] ?? null) : 0,
                ];
            }
            return $series;
        }
        return [];
    }

    /**
     * Handles MULTI-MONTH / QUARTERLY uploads correctly.
     *
     * Collects EVERY row where type === 'INVOICE' (each representing one
     * month's range, in file order), and processes them one at a time:
     *   - Build that range's full numeric sequence (from → to).
     *   - Assign the smallest available numbers in this range to however
     *     many transactions are STILL unassigned, in order, up to
     *     min(remaining transactions, numbers in this range).
     *   - Whatever numbers are left over in THIS range after that =
     *     cancelled for this range/month (reported per range).
     *   - Move to the next INVOICE range with whatever transactions are
     *     still left unassigned.
     *
     * This keeps each month's invoice numbering scoped to its own range,
     * so a 2-month (or full-quarter) upload doesn't corrupt counts.
     */
    private function assignInvoiceNumbers(array $transactions, array $seriesInfo): array
    {
        $invoiceSeriesList = array_values(array_filter(
            $seriesInfo,
            fn($s) => strtoupper($s['type']) === 'INVOICE'
        ));

        if (empty($invoiceSeriesList)) {
            $this->warn("Invoice Series sheet has no row of type 'INVOICE'.");
            return $transactions;
        }

        // Index of transactions still needing a number, preserving order.
        $pendingIdx = array_keys($transactions);
        $rangeReports = [];

        foreach ($invoiceSeriesList as $rangeNum => $invoiceSeries) {
            if (empty($pendingIdx)) break; // all transactions already numbered

            [$prefix, $fromNum, $pad]   = $this->splitSeriesToken($invoiceSeries['from']);
            [$prefix2, $toNum, $pad2]   = $this->splitSeriesToken($invoiceSeries['to']);

            if ($prefix === null || $toNum === null || $fromNum === null || $prefix !== $prefix2) {
                $this->warn("Invoice Series range #" . ($rangeNum + 1)
                    . " ('{$invoiceSeries['from']}' → '{$invoiceSeries['to']}') could not be parsed; "
                    . "transactions in this range left without invoice_number.");
                continue;
            }

            // Full numeric sequence for THIS range only.
            $allNumbers = range($fromNum, $toNum);
            sort($allNumbers);
            $available = $allNumbers;

            // How many transactions can this range cover?
            $capacity = count($available);
            $toAssign = array_splice($pendingIdx, 0, $capacity);

            $assignedNumbers = array_splice($available, 0, count($toAssign));

            foreach ($toAssign as $pos => $txIdx) {
                $num = $assignedNumbers[$pos];
                $transactions[$txIdx]['invoice_number'] = $prefix . str_pad((string)$num, $pad, '0', STR_PAD_LEFT);
            }

            $missingCount      = count($available); // numbers in this range nobody used
            $declaredCancelled = $invoiceSeries['cancelled'] ?? 0;

            $rangeReports[] = [
                'range'     => "{$invoiceSeries['from']} → {$invoiceSeries['to']}",
                'assigned'  => count($toAssign),
                'missing'   => $missingCount,
                'declared'  => $declaredCancelled,
            ];

            if ($missingCount > 0) {
                $this->warn(
                    "Invoice Series {$invoiceSeries['from']} → {$invoiceSeries['to']}: "
                    . "{$missingCount} invoice number(s) had no matching transaction row and were marked CANCELLED "
                    . "(file declares {$declaredCancelled} cancelled for this range)."
                    . ($missingCount !== (int)$declaredCancelled
                        ? " NOTE: computed missing count differs from file's declared cancelled count — please verify."
                        : "")
                );
            }
        }

        // If, after walking every declared INVOICE range, some transactions
        // still have no number (ranges ran out of capacity vs actual rows),
        // flag it clearly instead of silently leaving them blank.
        if (!empty($pendingIdx)) {
            $this->warn(
                count($pendingIdx) . " transaction(s) had no Invoice Series range left to draw a number from "
                . "(more transactions than declared invoice numbers across all ranges) — invoice_number left blank "
                . "for these rows. Please verify the Invoice Series sheet covers every month in this upload."
            );
        }

        if (!empty($rangeReports)) {
            $summaryMsg = implode(' | ', array_map(
                fn($r) => "{$r['range']}: assigned {$r['assigned']}, cancelled {$r['missing']} (declared {$r['declared']})",
                $rangeReports
            ));
            $this->warn("Invoice Series ranges processed — {$summaryMsg}");
        }

        return $transactions;
    }

    /**
     * Splits a token like "S72D94/26/227" into ['S72D94/26/', 227, 3]
     * (prefix, numeric-suffix, zero-pad width) by finding the trailing digit run.
     */
    private function splitSeriesToken(string $token): array
    {
        if (preg_match('/^(.*?)(\d+)$/', $token, $m)) {
            $prefix = $m[1];
            $digits = $m[2];
            return [$prefix, (int)$digits, strlen($digits)];
        }
        return [null, null, null];
    }

    // ════════════════════════════════════════════════════════════════════
    //  COLUMN MAP BUILDER — EXACT match only (no loose fuzzy fallback,
    //  this is what caused "Gstin Of Seller" to become buyer_gstin before)
    // ════════════════════════════════════════════════════════════════════

    private function buildSnapdealExactColMap(array $headers): array
    {
        $map = [];
        $normalized = array_map(fn($h) => strtolower(trim((string)$h)), $headers);

        foreach (self::COLS as $field => $aliases) {
            $map[$field] = -1;
            foreach ($aliases as $alias) {
                $idx = array_search(strtolower($alias), $normalized, true);
                if ($idx !== false) { $map[$field] = (int)$idx; break; }
            }
        }
        return $map;
    }

    private function numOrNull(mixed $val): ?float
    {
        if ($val === null || trim((string)$val) === '') return null;
        return $this->num($val);
    }
}
