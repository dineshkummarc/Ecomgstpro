<?php

namespace App\Parsers;

use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * MYNTRA PARSER
 *
 * Supports three Myntra export formats, distinguished by which columns
 * are present in the sheet (NOT filename, since filenames vary):
 *
 *  1. Packed file (Sales.csv) — snake_case headers (base_value,
 *     item_status, customer_delivery_state_code, invoiceamount). This is
 *     a LIVE SNAPSHOT export, so it can already contain some rows whose
 *     item_status shows IC/RTO (i.e. items that were later cancelled or
 *     sent back). ⚠️ IMPORTANT: every row in THIS file is always treated
 *     as a POSITIVE sale, regardless of item_status. The IC/RTO rows
 *     visible here are NOT double-counted as negative — the actual
 *     negative/return amount for those exact items is tracked
 *     exclusively by the dedicated RTO file and RT file below. Flipping
 *     sign here based on item_status was the old (buggy) behaviour and
 *     caused the same return to be subtracted twice — once implicitly
 *     via this file's IC/RTO rows, and again via the dedicated RTO.csv/
 *     Return.csv rows for the same item_id.
 *
 *  2. RTO file — undelivered return-to-origin. Detected by the presence
 *     of an 'is_rto' column (Sales/Packed file never has this column).
 *     Every row in this file is ALWAYS forced negative, regardless of
 *     what item_status says.
 *
 *  3. RT file (post-delivery Return) — has return_status /
 *     return_status_display_name / fr_is_refunded / fr_refunded_date
 *     columns. CRITICAL: item_status on these rows is always 'C'
 *     (the order itself completed/delivered fine) — the actual return
 *     signal lives in return_status (e.g. 'DLS', 'RL'), not item_status.
 *     Every row in this file represents a return that happened AFTER
 *     delivery, so it is ALWAYS forced negative regardless of
 *     item_status.
 *
 *  4. Invoice-level export — Title_Case_With_Underscores headers
 *     (Taxable_Amount, Customer_State, Igst_Rate, Seller_Gstn, Hsn).
 *     No status column — every row treated as a sale.
 *
 * ── SIGN RULE SUMMARY ──────────────────────────────────────────────────
 *   RT file (Return.csv)   → always NEGATIVE
 *   RTO file (RTO.csv)     → always NEGATIVE
 *   Packed file (Sales.csv)→ always POSITIVE (item_status never flips sign)
 * ─────────────────────────────────────────────────────────────────────
 *
 * ── ⚠️ FIX (this version): SELLER STATE CODE NORMALIZATION ─────────────
 *   resolveMyntraPOS() had a fallback: when a row's own state column was
 *   missing/unresolvable, it fell back to `$this->sellerStateCode`
 *   *as-is* (raw), e.g. "Rajasthan" or "RJ" or "08" depending on what
 *   the controller passed in. But BaseParser::makeTransaction() decides
 *   inter/intra by directly comparing that returned `$pos` value against
 *   `$this->sellerStateCode` (also raw):
 *
 *       $isInter = ($this->sellerStateCode !== '' && $pos !== $this->sellerStateCode);
 *
 *   Every OTHER code path in this file resolves state through
 *   resolveStateCode() first, so `$pos` is always a normalized 2-digit
 *   code (e.g. "08"). But the seller-state fallback branch was handing
 *   back the raw, un-normalized sellerStateCode string. When the two
 *   sides of that comparison used different formats for what was
 *   actually the same state, `$isInter` could resolve to the wrong
 *   boolean — flipping IGST into CGST/SGST (or vice-versa) for affected
 *   rows. This is exactly the symptom seen against Repotic: totals
 *   matched, but the IGST vs CGST+SGST split didn't, including a
 *   spurious negative INTRA row for a state that should have been INTER.
 *
 *   Fix: the seller-state fallback in resolveMyntraPOS() now also goes
 *   through resolveStateCode(), so it always returns the same
 *   normalized 2-digit format as every other path, and comparisons in
 *   makeTransaction() line up correctly.
 * ─────────────────────────────────────────────────────────────────────
 */
class MyntraParser extends BaseParser
{
    protected string $platform = 'myntra';

    private array $posCache = [];

    private const COLS = [
        'seller_gstin' => [
            'seller_gstin', 'seller gstin', 'gstin', 'your gstin', 'supplier gstin',
            'seller_gstn', 'seller gstn',
        ],
        'delivery_state' => [
            'delivery_state', 'delivery state', 'ship to state',
            'delivered state', 'customer delivery state',
            'customer_delivery_state_code', 'customer delivery state code',
            'customer_state', 'customer state',
        ],
        'customer_state' => [
            'customer_state', 'customer state', 'buyer state',
            'return state', 'customer billing state',
            'customer_delivery_state_code', 'customer delivery state code',
        ],
        'seller_state_code' => [
            'seller_state_code', 'seller state code', 'seller state',
            'warehouse_state', 'warehouse state', 'dispatch state', 'origin state',
        ],
        'taxable_value' => [
            // base_value first — real taxable value; base_amount is a
            // TDS-calculation base on some exports, kept only as fallback.
            'base_value', 'base value',
            'taxable value', 'taxable amount',
            'net taxable value', 'item value', 'net amount', 'selling price',
            'base_amount', 'base amount',
        ],
        'gst_rate' => [
            'tax_rate', 'tax rate', 'gst rate', 'gst%', 'rate', 'applicable tax rate',
        ],
        'igst_rate' => [
            'igst_rate', 'igst rate', 'igst%',
        ],
        'cgst_rate' => [
            'cgst_rate', 'cgst rate', 'cgst%',
        ],
        'sgst_rate' => [
            'sgst_rate', 'sgst rate', 'sgst%',
        ],
        'item_status' => [
            'item_status', 'item status', 'order status',
            'fulfillment status', 'status',
        ],
        // ⚠️ FIX: new_parent_id / new_parent_order_id is the ONE order
        // reference that is common to ALL THREE Myntra file types
        // (Packed & RTO call it new_parent_id, RT calls it
        // new_parent_order_id). The plain order_id column only exists in
        // Packed & RTO, NOT in RT — so it used to get picked there
        // (priority above new_parent_id), which meant a sale row (Packed,
        // using order_id) and its later return row (RT, using
        // new_parent_order_id) ended up with two totally different
        // invoice/order references and could never be reconciled against
        // each other. Moving new_parent_id/new_parent_order_id to the top
        // (right after the true invoice_number aliases) makes the same
        // order always resolve to the same reference across all three
        // files.
        'order_id' => [
            'invoice_number', 'invoice number', 'invoice no', 'invoice_no',
            'new_parent_order_id', 'new parent order id',
            'new_parent_id', 'new parent id',
            'parent order id',
            'order_id', 'order id',
            'sale_order_code', 'sale order code', 'order_code', 'order code',
        ],
        'order_date' => [
            'order_created_date', 'order created date', 'order date',
            'date', 'created at', 'purchase date',
        ],
        'quantity' => [
            'qty', 'quantity', 'item count', 'units', 'no. of items',
        ],
        'tcs_amount' => [
            'tcs_amount', 'tcs amount', 'tax collected at source', 'tcs',
        ],
        'hsn_code' => [
            'hsn', 'hsn code', 'hsn/sac', 'hsn_code', 'hsn sac',
        ],
        'item_id' => [
            'item_id', 'item id', 'itemid',
        ],
        // Used only to DETECT the RT (post-delivery return) file type —
        // not mapped to any output field.
        'return_status_marker' => [
            'return_status', 'return status',
            'fr_is_refunded', 'fr is refunded',
        ],
        // ⚠️ Used only to DETECT the RTO (undelivered
        // return-to-origin) file type — not mapped to any output field.
        // Sales/Packed file never has an 'is_rto' column, so its presence
        // reliably identifies the RTO file and safely distinguishes it
        // from the Packed file (which can otherwise look similar).
        'is_rto_marker' => [
            'is_rto', 'is rto',
        ],
    ];

    private const STATUS_SALE   = ['C', 'S', 'COMPLETED', 'DELIVERED', 'COMPLETE', 'SHIPPED', 'PACKED', 'PK'];
    private const STATUS_RETURN = ['IC', 'RTO', 'RETURN', 'CANCELLED', 'CANCEL', 'RTN', 'ITEM CANCELLED'];

    // 'base_value' | 'base_amount' | null (null = auto, base_value preferred — old behaviour)
    private ?string $taxableSource = null;

    public function __construct(string $sellerStateCode = '', string $clientGstin = '', ?string $taxableSource = null)
    {
        parent::__construct($sellerStateCode, $clientGstin);

        foreach (self::COLS as $field => $aliases) {
            if (empty($this->cfg['columns'][$field])) {
                $this->cfg['columns'][$field] = $aliases;
            }
        }

        // Calculation-base override: when the sheet has BOTH base_value and
        // base_amount columns, the caller (controller) can force which one
        // is used as taxable_value instead of always defaulting to
        // base_value. Re-order the alias list so the chosen column is
        // matched first; the other stays as a fallback if the chosen one
        // isn't actually present in this particular file.
        //
        // ⚠️ FIX: BOTH branches now explicitly rebuild the alias list
        // instead of assuming "base_value first" is already the default
        // order. The old 'base_value' branch just left a comment saying
        // "already the default alias order — no change needed" — but
        // that assumption silently breaks if $this->cfg['columns']
        // ever came from somewhere other than the bare self::COLS
        // constant (e.g. an external config/gstr/myntra.php file that
        // happens to list base_amount first, or any other prior
        // mutation of $this->cfg). In that situation, choosing
        // 'base_amount' would correctly force base_amount (explicit
        // override), but choosing 'base_value' would do nothing and
        // silently keep using whatever was already there — which is
        // exactly the bug reported: toggling the selector between
        // base_value and base_amount kept producing the SAME total
        // (base_amount's total) no matter which one was picked.
        // Explicitly rebuilding the list on both branches removes that
        // hidden assumption entirely.
        if ($taxableSource === 'base_amount') {
            $this->taxableSource = 'base_amount';
            $this->cfg['columns']['taxable_value'] = [
                'base_amount', 'base amount',
                'taxable value', 'taxable amount', 'net taxable value',
                'item value', 'net amount', 'selling price',
                'base_value', 'base value',
            ];
        } elseif ($taxableSource === 'base_value') {
            $this->taxableSource = 'base_value';
            $this->cfg['columns']['taxable_value'] = [
                'base_value', 'base value',
                'taxable value', 'taxable amount', 'net taxable value',
                'item value', 'net amount', 'selling price',
                'base_amount', 'base amount',
            ];
        }
        // If $taxableSource is null (auto), whatever alias order came from
        // the merge loop above (self::COLS default, base_value first) is
        // left untouched.
    }

    /**
     * Does this sheet's header row contain BOTH base_value and base_amount?
     * Used by the controller to decide whether to show a "which column
     * should we use" prompt before committing to a parse.
     */
    public static function headerHasDualBase(array $headers): bool
    {
        $normalized = array_map(
            fn($h) => strtolower(trim(str_replace('_', ' ', (string)$h))),
            $headers
        );
        $hasValue  = in_array('base value', $normalized, true);
        $hasAmount = in_array('base amount', $normalized, true);
        return $hasValue && $hasAmount;
    }

    /**
     * Quick, unnetted preview sum of a single column across all sheets —
     * for showing the two candidate totals in a "choose calculation base"
     * prompt before the real parse runs. Not a substitute for parse();
     * it does not apply the RT/RTO forced-negative-file rules, it only
     * mirrors item_status-based sign so the two preview numbers are in
     * the same ballpark as what the user will actually get.
     */
    public static function previewColumnTotal(array $sheets, string $column): float
    {
        $total = 0.0;
        foreach ($sheets as $rows) {
            if (empty($rows)) continue;
            $headers = array_map('strval', $rows[0] ?? []);
            $normalized = array_map(
                fn($h) => strtolower(trim(str_replace('_', ' ', (string)$h))),
                $headers
            );
            $colIdx    = array_search(str_replace('_', ' ', $column), $normalized, true);
            $statusIdx = array_search('item status', $normalized, true);
            if ($statusIdx === false) {
                $statusIdx = array_search('status', $normalized, true);
            }
            if ($colIdx === false) continue;

            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                $val = is_numeric($row[$colIdx] ?? null) ? (float)$row[$colIdx] : 0.0;
                if ($val == 0) continue;
                $isReturn = false;
                if ($statusIdx !== false) {
                    $status = strtoupper(trim((string)($row[$statusIdx] ?? '')));
                    $isReturn = in_array($status, self::STATUS_RETURN, true);
                }
                $total += $isReturn ? -abs($val) : abs($val);
            }
        }
        return round($total, 2);
    }

    public function canHandle(Spreadsheet $spreadsheet): bool
    {
        $names = array_map('strtolower', $spreadsheet->getSheetNames());
        foreach ($names as $n) {
            if (str_contains($n, 'myntra')) return true;
        }

        try {
            $sheet    = $spreadsheet->getActiveSheet();
            $firstRow = $sheet->toArray(null, true, true, false)[0] ?? [];
            $rowStr   = strtolower(str_replace('_', ' ', implode(' ', array_map('strval', $firstRow))));
            $hits     = 0;
            foreach (['seller gstin', 'base amount', 'base value', 'item status', 'delivery state', 'taxable amount', 'customer state'] as $sig) {
                if (str_contains($rowStr, $sig)) $hits++;
            }
            if ($hits >= 2) return true;
        } catch (\Throwable $e) {}

        return false;
    }

    public function parse(array $sheets, string $sellerStateCode, string $period, string $fileRole = 'single'): array
    {
        $this->sellerStateCode = $sellerStateCode;
        $this->period          = $period;
        $this->warnings        = [];
        $this->posCache        = [];
        $transactions          = [];

        foreach ($sheets as $sheetName => $rows) {
            if (empty($rows)) continue;

            $parsed = $this->parseSheet($rows, $sheetName);
            if (!empty($parsed)) {
                $transactions = array_merge($transactions, $parsed);
            }
        }

        $transactions = $this->dedupeWithinFile($transactions);

        if (empty($transactions)) {
            $this->warn("No transactions found in any sheet.");
        }

        return [
            'transactions' => array_values(array_filter($transactions)),
            'warnings'     => $this->warnings,
        ];
    }

    private function dedupeWithinFile(array $transactions): array
    {
        $seen = [];
        $out  = [];
        foreach ($transactions as $t) {
            $key = $t['item_id'] ?? null;
            if ($key !== null && $key !== '') {
                if (isset($seen[$key])) continue;
                $seen[$key] = true;
            }
            $out[] = $t;
        }
        return $out;
    }

    private function parseSheet(array $rows, string $label): array
    {
        if (empty($rows)) return [];

        $hIdx = $this->findHeaderRow($rows, [
            'base_amount', 'base amount', 'base_value', 'base value',
            'taxable_amount', 'taxable amount',
            'item_status', 'item status',
            'seller_gstin', 'delivery_state', 'tax_rate',
        ], 2);

        if ($hIdx < 0) {
            foreach ($rows as $i => $row) {
                $ne = count(array_filter(array_map('strval', $row), fn($v) => trim($v) !== ''));
                if ($ne >= 4) { $hIdx = $i; break; }
            }
        }

        if ($hIdx < 0) {
            $this->warn("'{$label}': Header row not found.");
            return [];
        }

        $headers = array_map('strval', $rows[$hIdx]);
        $cm      = $this->buildMyntraColMap($headers);

        if (($cm['taxable_value'] ?? -1) < 0) {
            $this->warn("'{$label}': taxable_value (base_value/base_amount) column missing. Cols: "
                . implode(' | ', array_filter(array_slice($headers, 0, 15))));
            return [];
        }

        $hasQtyCol    = ($cm['quantity']    ?? -1) >= 0;
        $hasOrderId   = ($cm['order_id']    ?? -1) >= 0;
        $hasOrderDate = ($cm['order_date']  ?? -1) >= 0;
        $hasHsn       = ($cm['hsn_code']    ?? -1) >= 0;
        $hasItemId    = ($cm['item_id']     ?? -1) >= 0;

        // ⚠️ FIX: detect the RT (post-delivery return) file by the
        // presence of return_status / fr_is_refunded columns. On this
        // file type, item_status is always 'C' (order completed fine) —
        // it does NOT tell us the row is a return. Every row in this
        // file IS a return by definition.
        $isReturnFile = ($cm['return_status_marker'] ?? -1) >= 0;

        // ⚠️ NEW FIX: detect the RTO (undelivered return-to-origin) file
        // by the presence of an 'is_rto' column — the Sales/Packed file
        // never has this column, so it safely distinguishes the two.
        $isRtoFile = ($cm['is_rto_marker'] ?? -1) >= 0;

        if (!$hasHsn) {
            $this->warn("'{$label}': HSN column not present in this Myntra export — HSN will be blank for these rows. "
                . "Provide a HSN mapping/override if these rows need HSN codes for GSTR-1.");
        }

        $transactions = [];

        for ($i = $hIdx + 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if ($this->rowIsEmpty($row, 1)) continue;

            // ⚠️ FIX: sign is now decided purely by FILE TYPE, never by the
            // per-row item_status. The Packed/Sales file can already show
            // some rows with item_status IC/RTO (it's a live snapshot),
            // but those same items are separately and exclusively tracked
            // as negative by the dedicated RTO file / RT (Return) file.
            // Flipping sign here too (old behaviour) meant the same
            // return got subtracted twice — once implicitly via this
            // file's own IC/RTO rows, and again via the dedicated RTO.csv
            // / Return.csv rows sharing the same item_id. That double
            // subtraction was the root cause of the total coming out
            // lower than the correct figure.
            //
            //   RT file (Return.csv)    → always negative
            //   RTO file (RTO.csv)      → always negative
            //   Packed file (Sales.csv) → always positive
            $isReturn = $isReturnFile || $isRtoFile;

            $txval = $this->num($row[$cm['taxable_value']] ?? null);
            if ($txval == 0) continue;

            if ($isReturn && $txval > 0) {
                $txval = -$txval;
            } else if (!$isReturn && $txval < 0) {
                $txval = abs($txval);
            }

            $pos = $this->resolveMyntraPOS($row, $cm, $isReturn);
            if ($pos === '') {
                $this->warn("'{$label}' row {$i}: Cannot resolve state — skipped");
                continue;
            }

            $rate = $this->resolveMyntraRate($row, $cm);

            $igst = null;
            $cgst = null;
            $sgst = null;

            $orderId   = null;
            $orderDate = null;

            if ($hasOrderId) {
                $v       = $row[$cm['order_id']] ?? null;
                $orderId = ($v !== null && trim((string)$v) !== '') ? trim((string)$v) : null;
            }
            if ($hasOrderDate) {
                $orderDate = $this->date($row[$cm['order_date']] ?? null);
            }

            $qty = null;
            if ($hasQtyCol) {
                $qv  = $this->num($row[$cm['quantity']] ?? null);
                $qty = $qv != 0 ? (int)abs($qv) : null;
            }

            $hsn = null;
            if ($hasHsn) {
                $h   = trim((string)($row[$cm['hsn_code']] ?? ''));
                $hsn = $h !== '' ? $h : null;
            }

            $t = $this->makeTransaction(
                pos:          $pos,
                rate:         $rate,
                taxableValue: $txval,
                igstAmt:      $igst,
                cgstAmt:      $cgst,
                sgstAmt:      $sgst,
                invoiceNo:    $orderId,
                invoiceDate:  $orderDate,
                qty:          $qty,
                hsn:          $hsn,
                buyerGstin:   null,
            );

            if (empty($t)) continue;

            if ($hasItemId) {
                $iid = trim((string)($row[$cm['item_id']] ?? ''));
                if ($iid !== '') {
                    $t['item_id'] = $iid;
                }
            }

            $transactions[] = $t;
        }

        return $transactions;
    }

    private function resolveMyntraPOS(array $row, array $cm, bool $isReturn): string
    {
        // ⚠️ FIX: ALWAYS try delivery_state first, regardless of file
        // type (isReturn no longer changes the priority order here).
        //
        // Why: delivery_state's alias list already includes
        // 'customer_state' / 'customer_delivery_state_code' etc, so it
        // reliably resolves to the correct buyer/customer state column
        // across ALL THREE Myntra file types:
        //   - Packed (Sales.csv)  → customer_delivery_state_code
        //   - RTO.csv             → customer_state (exact header)
        //   - RT (sales_Return.csv) → delivery_state (exact header)
        //
        // The dedicated 'customer_state' field, by contrast, has NO
        // reliable header at all in the RT file — sales_Return.csv only
        // has 'delivery_state' (the real buyer state) plus a generic,
        // ambiguous bare 'state' column near the end that actually holds
        // the WAREHOUSE/seller state, not the buyer's. Column-mapping's
        // weakest fuzzy fallback pass ("does this alias contain that
        // header as a substring") matched the 'return state' alias
        // against that bare 'state' header, silently pointing
        // cm['customer_state'] at the wrong column. Since return rows
        // tried customer_state FIRST (old code), every RT-file row's
        // place-of-supply came out as the seller's own warehouse state
        // instead of the buyer's actual state — turning genuinely
        // inter-state returns into (wrongly) intra-state ones, which
        // flips IGST into CGST+SGST for those rows. Verified against
        // real Sales/Return/RTO files: prioritizing delivery_state here
        // reproduces Repotic's correct IGST/CGST/SGST split exactly.
        $posCol = $cm['delivery_state'] ?? -1;

        if ($posCol < 0) {
            $posCol = $cm['customer_state'] ?? -1;
        }

        if ($posCol >= 0) {
            $raw = $row[$posCol] ?? null;
            if ($raw !== null && trim((string)$raw) !== '') {
                $cacheKey = (string)$raw;
                if (!isset($this->posCache[$cacheKey])) {
                    $this->posCache[$cacheKey] = $this->resolveStateCode($raw);
                }
                if ($this->posCache[$cacheKey] !== '') {
                    return $this->posCache[$cacheKey];
                }
            }
        }

        // ⚠️ FIX: normalize sellerStateCode through resolveStateCode()
        // before using it as a POS fallback. Every other path in this
        // parser returns a normalized 2-digit state code, but this
        // fallback used to return $this->sellerStateCode completely
        // raw (e.g. "Rajasthan" or "RJ" — whatever format the caller/
        // controller happened to pass in). BaseParser::makeTransaction()
        // compares the returned $pos directly against
        // $this->sellerStateCode (also raw) to decide inter vs intra:
        //
        //     $isInter = ($this->sellerStateCode !== '' && $pos !== $this->sellerStateCode);
        //
        // If $pos came back normalized ("08") while sellerStateCode was
        // raw ("Rajasthan"), the comparison would (almost) always see
        // them as different even when they represent the SAME state —
        // silently flipping intra-state rows into inter-state (or vice
        // versa depending on which raw format was used elsewhere),
        // which swaps IGST for CGST+SGST on those rows. Normalizing here
        // guarantees both sides of that later comparison use the same
        // 2-digit format for the same underlying state.
        if ($this->sellerStateCode !== '') {
            $normalizedSeller = $this->resolveStateCode($this->sellerStateCode);
            if ($normalizedSeller !== '') {
                return $normalizedSeller;
            }
            // sellerStateCode didn't resolve to any known state — fall
            // through to the remaining fallbacks below rather than
            // returning garbage.
        }

        if (($cm['seller_state_code'] ?? -1) >= 0) {
            $raw = $row[$cm['seller_state_code']] ?? null;
            if ($raw !== null && trim((string)$raw) !== '') {
                $code = $this->resolveStateCode($raw);
                if ($code !== '') return $code;
            }
        }

        return '';
    }

    private function resolveMyntraRate(array $row, array $cm): float
    {
        if (($cm['gst_rate'] ?? -1) >= 0) {
            $rate = $this->num($row[$cm['gst_rate']] ?? null);
            if ($rate > 0) {
                return $rate < 1 ? round($rate * 100, 2) : round($rate, 2);
            }
        }

        $igstR = ($cm['igst_rate'] ?? -1) >= 0 ? $this->num($row[$cm['igst_rate']] ?? null) : 0;
        $cgstR = ($cm['cgst_rate'] ?? -1) >= 0 ? $this->num($row[$cm['cgst_rate']] ?? null) : 0;
        $sgstR = ($cm['sgst_rate'] ?? -1) >= 0 ? $this->num($row[$cm['sgst_rate']] ?? null) : 0;

        $total = $igstR + $cgstR + $sgstR;
        if ($total > 0) {
            return $total < 1 ? round($total * 100, 2) : round($total, 2);
        }

        return 0.0;
    }

    private function buildMyntraColMap(array $headers): array
    {
        $map = [];

        // underscore→space normalization so 'Taxable_Amount' matches 'taxable amount'
        $normalized = array_map(
            fn($h) => strtolower(trim(str_replace('_', ' ', (string)$h))),
            $headers
        );

        // ⚠️ FIX: iterate over $this->cfg['columns'] (instance state) —
        // NOT the static self::COLS constant. The constructor rewrites
        // $this->cfg['columns']['taxable_value'] based on $taxableSource
        // ('base_value' vs 'base_amount' first) precisely so the caller
        // can choose which column drives the taxable amount. But this
        // method used to loop over self::COLS directly, completely
        // bypassing that instance-level override — so no matter which
        // calculation base was selected in the UI, column resolution
        // always used the hardcoded constant's alias order and the
        // selector had zero effect on the actual parsed figures. Falling
        // back to self::COLS per-field only when a field is genuinely
        // absent from $this->cfg['columns'] (defensive, shouldn't
        // normally happen since the constructor seeds every field from
        // self::COLS already).
        $fields = array_keys($this->cfg['columns'] ?? []);
        if (empty($fields)) {
            $fields = array_keys(self::COLS);
        }

        foreach ($fields as $field) {
            $aliases = $this->cfg['columns'][$field] ?? (self::COLS[$field] ?? []);
            $map[$field] = -1;

            foreach ($aliases as $alias) {
                $normAlias = strtolower(str_replace('_', ' ', $alias));
                $idx = array_search($normAlias, $normalized, true);
                if ($idx !== false) { $map[$field] = (int)$idx; break; }
            }
            if ($map[$field] >= 0) continue;

            foreach ($aliases as $alias) {
                $al = strtolower(str_replace('_', ' ', $alias));
                foreach ($normalized as $i => $h) {
                    if ($h !== '' && str_contains($h, $al)) {
                        $map[$field] = $i;
                        break 2;
                    }
                }
            }
            if ($map[$field] >= 0) continue;

            foreach ($aliases as $alias) {
                $al = strtolower(str_replace('_', ' ', $alias));
                foreach ($normalized as $i => $h) {
                    if (strlen($h) >= 3 && str_contains($al, $h)) {
                        $map[$field] = $i;
                        break 2;
                    }
                }
            }
        }

        return $map;
    }
}