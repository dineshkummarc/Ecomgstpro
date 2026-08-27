<?php

namespace App\Parsers;

use PhpOffice\PhpSpreadsheet\Spreadsheet;

class AmazonParser extends BaseParser
{
    protected string $platform = 'amazon';

    private array $posCache = [];

    private const COLS = [
        'place_of_supply' => [
            'place of supply', 'place_of_supply', 'pos',
            'delivery state', 'customer state', 'ship to state',
            'shipping state', 'buyer state', 'destination state',
            'state of supply', 'delivered state', 'state',
        ],
        'gst_rate' => [
            'rate', 'gst rate', 'gst%', 'tax rate', 'igst rate',
            'applicable % of tax rate', 'applicable tax rate',
            'rate (%)', 'rate(%)', 'gst_rate', 'tax_rate',
            'integrated tax rate', 'central tax rate',
        ],
        'taxable_value' => [
            'taxable value', 'taxable_value', 'taxable amount',
            'net taxable value', 'invoice taxable value',
            'item taxable value', 'total taxable value',
            'taxable amt', 'base amount', 'taxable',
        ],
        'igst_amount' => [
            'igst amount', 'integrated tax amount', 'igst_amount',
            'igst amt', 'integrated tax', 'igst',
        ],
        'cgst_amount' => [
            'cgst amount', 'central tax amount', 'cgst_amount',
            'cgst amt', 'central tax', 'cgst',
        ],
        'sgst_amount' => [
            'sgst amount', 'state tax amount', 'sgst_amount',
            'sgst amt', 'ut tax amount', 'utgst amount',
            'state/ut tax amount', 'state tax',
            'utgst', 'sgst/utgst amount', 'sgst',
        ],
        'invoice_number' => [
            'invoice number', 'invoice_number', 'invoice no',
            'invoice no.', 'inv number', 'inv no',
            'bill number', 'shipment id', 'order id',
            'document number', 'reference number',
        ],
        'invoice_date' => [
            'invoice date', 'invoice_date', 'date',
            'order date', 'bill date', 'transaction date',
            'invoice dt', 'shipment date',
        ],
        // ← NEW (v8): credit note issue date, used to decide the PERIOD
        // for Refund/credit-note rows instead of the original invoice
        // date. Kept deliberately specific ("credit note ...") so it
        // never collides with the invoice_date aliases above.
        'credit_note_date' => [
            'credit note date', 'credit_note_date', 'creditnotedate',
            'cn date', 'credit note dt',
        ],
        'quantity' => [
            'no. of items', 'no of items', 'quantity', 'qty',
            'item count', 'units', 'total quantity', 'item quantity',
        ],
        'buyer_gstin' => [
            'gstin/uin of recipient', 'gstin', 'buyer gstin',
            'customer gstin', 'party gstin', 'receiver gstin',
            'buyer_gstin', 'recipient gstin', 'business gst number',
        ],
        // Seller's own GSTIN column, used to filter out rows belonging
        // to a DIFFERENT seller when a file contains a mix of GSTINs.
        // Kept deliberately specific ("seller ...") so it can never
        // collide with the buyer_gstin aliases above.
        'seller_gstin' => [
            'seller gstin', 'seller_gstin', 'gstin of supplier',
            'supplier gstin', 'seller gstin number',
        ],
        'hsn_code' => [
            'hsn', 'hsn code', 'hsn/sac', 'hsn_code',
            'hsn sac', 'hsn/sac code', 'product code', 'tariff code',
        ],
    ];

    // Fields whose column must NEVER be a rate/% column, even if an
    // alias substring happens to overlap (see fix note at top of class).
    private const AMOUNT_FIELDS = ['igst_amount', 'cgst_amount', 'sgst_amount'];

    private const VALID_STATE_CODES = [
        '01','02','03','04','05','06','07','08','09','10',
        '11','12','13','14','15','16','17','18','19','20',
        '21','22','23','24','26','27','29','30','31','32',
        '33','34','35','36','37','38',
    ];

    // ════════════════════════════════════════════════════════════════════
    //  ← NEW: MTR STOCK TRANSFER REPORT — column aliases
    //
    //  Separate header map from COLS above, because this is a completely
    //  different Amazon export (inter-warehouse FC_TRANSFER / FC_REMOVAL
    //  movement of the seller's OWN inventory — not a sale to a customer).
    //  Fingerprint columns: "Gstin Of Supplier" + "Gstin Of Receiver" —
    //  these never appear together in the GSTR-1 Ready or MTR B2B/B2C
    //  formats above, so detection never collides with the existing path.
    // ════════════════════════════════════════════════════════════════════
    private const STOCK_COLS = [
        'gstin_of_receiver' => [
            'gstin of receiver', 'receiver gstin', 'receiver gstin number',
            'gstin/uin of receiver', 'customer ship to gstin', 'customer bill to gstin',
        ],
        'gstin_of_supplier' => [
            'gstin of supplier', 'seller gstin', 'supplier gstin',
            'gstin of seller', 'gstin/uin of supplier', 'company gstin',
        ],
        'transaction_id'    => ['transaction id', 'transaction identifier', 'transaction reference'],
        'invoice_number'    => ['invoice number', 'invoice no', 'order id', 'shipment id'],
        'invoice_date'      => ['invoice date', 'invoice dt', 'date', 'transaction date', 'shipment date'],
        'quantity'          => ['quantity', 'qty', 'no. of items', 'no of items', 'units'],
        'hsn_code'          => ['hsn code', 'hsn/sac', 'hsn', 'hsn sac'],
        'taxable_value'     => ['taxable value', 'taxable_value', 'taxable amount', 'net taxable value', 'total taxable value', 'base amount'],
        'igst_rate'         => ['igst rate', 'integrated tax rate', 'igst %', 'igst%'],
        'igst_amount'       => ['igst amount', 'integrated tax amount', 'igst amt', 'igst'],
        'sgst_rate'         => ['sgst rate', 'state tax rate', 'sgst %', 'sgst%'],
        'sgst_amount'       => ['sgst amount', 'state tax amount', 'utgst amount', 'sgst/utgst amount', 'state/ut tax amount', 'sgst amt', 'utgst'],
        'utgst_rate'        => ['utgst rate', 'utgst %', 'utgst%'],
        'utgst_amount'      => ['utgst amount', 'utgst amt'],
        'cgst_rate'         => ['cgst rate', 'central tax rate', 'cgst %', 'cgst%'],
        'cgst_amount'       => ['cgst amount', 'central tax amount', 'cgst amt', 'cgst'],
        'irn_filing_status' => ['irn filing status', 'irs filing status', 'irn status'],
    ];

    public function __construct(string $sellerStateCode = '', string $clientGstin = '')
    {
        parent::__construct($sellerStateCode, $clientGstin);

        // Merge hardcoded aliases into cfg (config takes priority if exists)
        foreach (self::COLS as $field => $aliases) {
            if (empty($this->cfg['columns'][$field])) {
                $this->cfg['columns'][$field] = $aliases;
            }
        }
    }

    public function canHandle(Spreadsheet $spreadsheet): bool
    {
        $names  = array_map('strtolower', $spreadsheet->getSheetNames());
        $detect = [
            'b2c small', 'b2c(small)', 'b2b invoices', 'b2c large',
            'b2cl', 'marketplace facilitator', 'b2b', 'hsn summary', 'mtr',
        ];
        foreach ($names as $n) {
            foreach ($detect as $d) {
                if (str_contains($n, $d)) return true;
            }
        }
        return false;
    }

    // ════════════════════════════════════════════════════════════════════
    //  CSV ENTRY POINT — forwards $fileRole (v6, unchanged)
    // ════════════════════════════════════════════════════════════════════

    public function parseCsv(string $filePath, string $period, string $fileRole = 'single'): array
    {
        $sheets = $this->readCsv($filePath);
        return $this->parse($sheets, $this->sellerStateCode, $period, $fileRole);
    }

    // ════════════════════════════════════════════════════════════════════
    //  MAIN PARSE
    // ════════════════════════════════════════════════════════════════════

    public function parse(array $sheets, string $sellerStateCode, string $period, string $fileRole = 'single'): array
    {
        $this->sellerStateCode = $sellerStateCode;
        $this->period          = $period;
        $this->warnings        = [];
        $this->posCache        = [];
        $transactions          = [];

        // ← NEW: Stock Transfer report is a totally different export
        // (own-inventory FC_TRANSFER/FC_REMOVAL, not a customer sale) —
        // route it to its own parser entirely, before any of the
        // GSTR-1/MTR sheet-name detection below runs.
        if ($fileRole === 'stock') {
            return $this->parseStockTransferFile($sheets);
        }

        // Lowercase key map
        $sheetMap = [];
        foreach ($sheets as $name => $rows) {
            $sheetMap[strtolower(trim($name))] = ['name' => $name, 'rows' => $rows];
        }

        // Route known Amazon sheet names
        $b2cSmall = $this->findSheet($sheetMap, [
            'b2c small', 'b2c(small)', 'small b2c', 'b2cs', 'b2c small invoices',
        ]);
        $b2cLarge = $this->findSheet($sheetMap, [
            'b2c large', 'b2cl', 'b2c(large)', 'b2cl cn', 'cdnur', 'b2c large invoice',
        ]);
        $b2b = $this->findSheet($sheetMap, [
            'b2b', 'b2b invoices', 'business to business', 'registered', 'b2b invoice',
        ]);

        // Did we match this file via REAL Amazon sheet names? Only ever
        // true for a genuine combined GSTR-1 workbook.
        $viaNamedSheets = ($b2cSmall !== null) || ($b2cLarge !== null) || ($b2b !== null);

        if ($b2cSmall) $transactions = array_merge($transactions, $this->parseSheet($b2cSmall, 'B2C Small', false, $fileRole));
        if ($b2cLarge) $transactions = array_merge($transactions, $this->parseSheet($b2cLarge, 'B2C Large', false, $fileRole));
        if ($b2b)      $transactions = array_merge($transactions, $this->parseSheet($b2b,      'B2B',       true,  $fileRole));

        // Fallback: no standard sheets matched → try every sheet. This is
        // the path a plain MTR B2C/B2B CSV always takes (single "Sheet1").
        if (empty($transactions)) {
            $this->warn("No standard Amazon sheet names found. Trying all sheets (file_role: {$fileRole}).");
            $isB2BFallback = ($fileRole === 'b2b');
            foreach ($sheets as $name => $rows) {
                $parsed = $this->parseSheet($rows, $name, $isB2BFallback, $fileRole);
                if (!empty($parsed)) {
                    $transactions = array_merge($transactions, $parsed);
                    $this->warn("Sheet '{$name}': " . count($parsed) . " transactions via generic parse (role: {$fileRole}).");
                }
            }
        }

        // Classify file_type for the duplicate-format guard (v6, unchanged).
        if ($viaNamedSheets) {
            $fileType = 'amazon_gstr1';
        } elseif ($fileRole === 'b2b') {
            $fileType = 'amazon_mtr_b2b';
        } elseif ($fileRole === 'b2c') {
            $fileType = 'amazon_mtr_b2c';
        } else {
            $fileType = !empty($transactions) ? 'amazon_mtr_single' : 'amazon_unknown';
        }

        return [
            'transactions' => array_values(array_filter($transactions)),
            'warnings'     => $this->warnings,
            'file_type'    => $fileType,
        ];
    }

    // ════════════════════════════════════════════════════════════════════
    //  ← NEW: MTR STOCK TRANSFER — FILE-LEVEL ENTRY
    //
    //  Amazon's Stock Transfer export can (and usually does) bundle EVERY
    //  state-GSTIN of the seller's PAN into one file (own inventory moving
    //  between the seller's own fulfilment centres across states). The
    //  whole point of this parser is to make sure ONLY the currently
    //  selected client's own outward leg gets picked up — see
    //  parseStockRows() below for the actual filter.
    // ════════════════════════════════════════════════════════════════════

    private function parseStockTransferFile(array $sheets): array
    {
        foreach ($sheets as $name => $rows) {
            if (empty($rows)) continue;

            $hIdx = $this->findStockHeaderRow($rows);
            if ($hIdx < 0) continue;

            $headers = array_map('strval', $rows[$hIdx]);
            $cm      = $this->buildStockColMap($headers);

            if (($cm['gstin_of_supplier'] ?? -1) < 0
                || ($cm['gstin_of_receiver'] ?? -1) < 0
                || ($cm['taxable_value']     ?? -1) < 0) {
                continue; // not the Stock Transfer sheet
            }

            $transactions = $this->parseStockRows($rows, $hIdx, $cm, $name);

            return [
                'transactions' => array_values(array_filter($transactions)),
                'warnings'     => $this->warnings,
                'file_type'    => 'amazon_stock_transfer', // never matches 'gstr1'/'mtr' → never trips the duplicate-format guard
            ];
        }

        $this->warn("This does not look like an Amazon MTR Stock Transfer file — required columns "
            . "(Gstin Of Supplier / Gstin Of Receiver / Taxable Value) were not found.");

        return ['transactions' => [], 'warnings' => $this->warnings, 'file_type' => 'amazon_stock_transfer'];
    }

    private function findStockHeaderRow(array $rows): int
    {
        $required = ['gstin of supplier', 'gstin of receiver', 'seller gstin', 'receiver gstin'];

        foreach ($rows as $i => $row) {
            $normalized = array_map(fn($cell) => strtolower(trim((string)$cell)), $row);
            $rowStr = implode(' ', $normalized);

            $hasSupplier = str_contains($rowStr, 'gstin of supplier')
                || str_contains($rowStr, 'seller gstin')
                || str_contains($rowStr, 'supplier gstin')
                || str_contains($rowStr, 'gstin of seller');
            $hasReceiver = str_contains($rowStr, 'gstin of receiver')
                || str_contains($rowStr, 'receiver gstin')
                || str_contains($rowStr, 'customer ship to gstin')
                || str_contains($rowStr, 'customer bill to gstin');

            if ($hasSupplier && $hasReceiver) {
                return $i;
            }
            if ($i > 7) break; // header is always within the first few rows
        }
        return -1;
    }

    private function buildStockColMap(array $headers): array
    {
        $normalized = array_map(fn($h) => strtolower(trim((string)$h)), $headers);
        $map = [];
        foreach (self::STOCK_COLS as $field => $aliases) {
            $map[$field] = -1;
            foreach ($aliases as $alias) {
                $idx = array_search($alias, $normalized, true);
                if ($idx !== false) { $map[$field] = (int)$idx; break; }
            }
        }
        return $map;
    }

    // ════════════════════════════════════════════════════════════════════
    //  ← NEW: MTR STOCK TRANSFER — ROW EXTRACTION
    //
    //  Rules, in order:
    //   1. GSTIN filter — ONLY rows where "Gstin Of Supplier" equals the
    //      SELECTED CLIENT's GSTIN are kept. This is the outward leg —
    //      the one that belongs on THIS GSTIN's GSTR-1. A row where the
    //      client's GSTIN only appears as the RECEIVER is the SENDING
    //      state's outward supply, not this client's — it is skipped.
    //   2. Same-state guard — supplier === receiver means an FC-to-FC
    //      move inside ONE state registration. A GSTIN can't invoice
    //      itself, so there's no "distinct person" supply — skipped.
    //   3. Period filter — same rule the GSTR-1/MTR path above already
    //      uses: Amazon's own report can include a few rows dated
    //      OUTSIDE the report's nominal month (see e.g. a row dated
    //      22-May sitting inside a file named "JUNE-2026" in practice).
    //      Only rows whose Invoice Date falls in the selected MM/YYYY
    //      period are kept, exactly like the rest of this parser.
    //   4. Every kept row becomes a B2B transaction — buyer_gstin =
    //      "Gstin Of Receiver" (a valid GSTIN of the same PAN, different
    //      state) — makeTransaction()/isValidGstin() already treat that
    //      as a normal, valid B2B counterparty. Nothing else in the app
    //      needs to change for this to land in the B2B section correctly.
    // ════════════════════════════════════════════════════════════════════

    private function parseStockRows(array $rows, int $hIdx, array $cm, string $label): array
    {
        $transactions       = [];
        $skippedOther       = 0;
        $skippedSameGst     = 0;
        $skippedWrongPeriod = 0;
        $rejectedIrn        = 0;

        $clientGstinUp = strtoupper(trim($this->clientGstin));

        for ($i = $hIdx + 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if ($this->rowIsEmpty($row, 1)) continue;

            $supplierGstin = strtoupper(trim((string)($row[$cm['gstin_of_supplier']] ?? '')));
            $receiverGstin = strtoupper(trim((string)($row[$cm['gstin_of_receiver']] ?? '')));

            if (!BaseParser::isValidGstin($supplierGstin) || !BaseParser::isValidGstin($receiverGstin)) {
                continue; // blank/garbled row
            }

            // ── THE key filter: only this client's own OUTWARD leg ──────
            if ($clientGstinUp !== '' && $supplierGstin !== $clientGstinUp) {
                $skippedOther++;
                continue;
            }

            // ── Same-state internal FC movement — nothing to report ─────
            if ($supplierGstin === $receiverGstin) {
                $skippedSameGst++;
                continue;
            }

            // ── Period filter (same rule as the rest of this parser) ────
            $invoiceDate = ($cm['invoice_date'] ?? -1) >= 0
                ? $this->date($row[$cm['invoice_date']] ?? null)
                : null;
            if ($invoiceDate && $this->period !== '') {
                $rowPeriod = date('m/Y', strtotime($invoiceDate));
                if ($rowPeriod !== $this->period) {
                    $skippedWrongPeriod++;
                    continue;
                }
            }

            $txval = $this->num($row[$cm['taxable_value']] ?? null);
            if ($txval == 0) continue;

            // Amazon reports rate columns as a FRACTION (0.18 = 18%),
            // same convention resolveRate()/parseDetailedMtrRows() above
            // already normalize — mirrored here for consistency.
            $igstRateFrac  = ($cm['igst_rate']  ?? -1) >= 0 ? $this->num($row[$cm['igst_rate']]  ?? null) : 0.0;
            $cgstRateFrac  = ($cm['cgst_rate']  ?? -1) >= 0 ? $this->num($row[$cm['cgst_rate']]  ?? null) : 0.0;
            $sgstRateFrac  = ($cm['sgst_rate']  ?? -1) >= 0 ? $this->num($row[$cm['sgst_rate']]  ?? null) : 0.0;
            $utgstRateFrac = ($cm['utgst_rate'] ?? -1) >= 0 ? $this->num($row[$cm['utgst_rate']] ?? null) : 0.0;

            $rateRaw = $igstRateFrac > 0 ? $igstRateFrac : ($cgstRateFrac + $sgstRateFrac + $utgstRateFrac);
            $rate    = $rateRaw < 1 ? round($rateRaw * 100, 2) : round($rateRaw, 2);

            $igstAmt  = ($cm['igst_amount']  ?? -1) >= 0 ? $this->num($row[$cm['igst_amount']]  ?? null) : 0.0;
            $cgstAmt  = ($cm['cgst_amount']  ?? -1) >= 0 ? $this->num($row[$cm['cgst_amount']]  ?? null) : 0.0;
            $sgstAmt  = ($cm['sgst_amount']  ?? -1) >= 0 ? $this->num($row[$cm['sgst_amount']]  ?? null) : 0.0;
            $utgstAmt = ($cm['utgst_amount'] ?? -1) >= 0 ? $this->num($row[$cm['utgst_amount']] ?? null) : 0.0;
            $sgstAmt += $utgstAmt; // UTGST folded into the sgst_amount bucket — matches this class's existing convention (see COLS['sgst_amount'])

            $posCode = str_pad((string)(int)substr($receiverGstin, 0, 2), 2, '0', STR_PAD_LEFT);
            if (!in_array($posCode, self::VALID_STATE_CODES, true)) {
                $this->warn("'{$label}' row {$i}: Cannot resolve state from receiver GSTIN — skipped");
                continue;
            }

            $irnStatus = ($cm['irn_filing_status'] ?? -1) >= 0
                ? strtoupper(trim((string)($row[$cm['irn_filing_status']] ?? '')))
                : '';
            if ($irnStatus === 'GOVT_REJECTED') $rejectedIrn++;

            $invoiceNo = null;
            if (($cm['invoice_number'] ?? -1) >= 0) {
                $v = trim((string)($row[$cm['invoice_number']] ?? ''));
                $invoiceNo = $v !== '' ? $v : null;
            }
            if (!$invoiceNo && ($cm['transaction_id'] ?? -1) >= 0) {
                $v = trim((string)($row[$cm['transaction_id']] ?? ''));
                $invoiceNo = $v !== '' ? $v : null;
            }

            $qty = null;
            if (($cm['quantity'] ?? -1) >= 0) {
                $qv  = $this->num($row[$cm['quantity']] ?? null);
                $qty = $qv != 0 ? (int)abs($qv) : null;
            }

            $hsn = ($cm['hsn_code'] ?? -1) >= 0
                ? trim((string)($row[$cm['hsn_code']] ?? ''))
                : null;

            $t = $this->makeTransaction(
                pos:          $posCode,
                rate:         $rate,
                taxableValue: $txval,
                igstAmt:      round($igstAmt, 2),
                cgstAmt:      round($cgstAmt, 2),
                sgstAmt:      round($sgstAmt, 2),
                invoiceNo:    $invoiceNo,
                invoiceDate:  $invoiceDate,
                qty:          $qty,
                hsn:          $hsn ?: null,
                buyerGstin:   $receiverGstin,
            );

            if (!empty($t)) $transactions[] = $t;
        }

        if ($skippedOther > 0) {
            $this->warn("'{$label}': {$skippedOther} row(s) skipped — belong to a different GSTIN (this file covers other states of the same PAN too).");
        }
        if ($skippedSameGst > 0) {
            $this->warn("'{$label}': {$skippedSameGst} row(s) skipped — same-state internal FC movement, no GST supply to report.");
        }
        if ($skippedWrongPeriod > 0) {
            $this->warn("'{$label}': {$skippedWrongPeriod} row(s) skipped — invoice date fell outside the selected period ({$this->period}).");
        }
        if ($rejectedIrn > 0) {
            $this->warn("{$rejectedIrn} invoice(s) included have a GOVT_REJECTED e-invoice (IRN) status on Amazon's side — please verify these on the portal before filing.");
        }
        if (empty($transactions)) {
            $this->warn($this->clientGstin !== ''
                ? "No stock-transfer rows found where '{$this->clientGstin}' is the supplying GSTIN for period {$this->period}."
                : 'No valid stock-transfer rows found.');
        }

        return $transactions;
    }

    // ════════════════════════════════════════════════════════════════════
    //  UNIVERSAL SHEET PARSER
    // ════════════════════════════════════════════════════════════════════

    private function parseSheet(array $rows, string $label, bool $isB2B, string $fileRole = 'single'): array
    {
        if (empty($rows)) return [];

        $hIdx = $this->findHeaderRowAmazon($rows);
        if ($hIdx < 0) {
            $this->warn("'{$label}': Header row not found.");
            return [];
        }

        $headers = array_map('strval', $rows[$hIdx]);

        // Detect Amazon's detailed per-transaction MTR B2B/B2C export
        // (fingerprint: "Tax Exclusive Gross" + "Total Tax Amount").
        $mtrCols = $this->detectDetailedMtrColumns($headers);
        if ($mtrCols !== null) {
            return $this->parseDetailedMtrRows($rows, $hIdx, $mtrCols, $label, $isB2B);
        }

        // ────────────────────────────────────────────────────────────
        //  EXISTING GSTR-1 STYLE PARSING
        // ────────────────────────────────────────────────────────────

        $cm = $this->buildAmazonColMap($headers);

        if (($cm['taxable_value'] ?? -1) < 0) {
            $this->warn("'{$label}': Taxable value column missing. Cols: "
                . implode(' | ', array_filter(array_slice($headers, 0, 12))));
            return [];
        }

        // ── FIX (v4): warn loudly if place_of_supply column not found.
        if (($cm['place_of_supply'] ?? -1) < 0) {
            $this->warn("'{$label}': 'Place Of Supply' column NOT FOUND (header row index {$hIdx}). "
                . "ALL rows in this sheet will fall back to the seller's own state — "
                . "state-wise totals will be WRONG. Header row detected: "
                . implode(' | ', array_filter(array_slice($headers, 0, 12))));
        }

        // MTR format: has invoice/date columns
        $hasMtrCols = ($cm['invoice_number'] ?? -1) >= 0
                   || ($cm['invoice_date']   ?? -1) >= 0;

        // Quantity column present?
        $hasQtyCol = ($cm['quantity'] ?? -1) >= 0;

        $transactions        = [];
        $skippedWrongSeller  = 0;
        $skippedWrongPeriod  = 0;

        for ($i = $hIdx + 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if ($this->rowIsEmpty($row, 1)) continue;

            // ── Taxable value ──
            // Skip only if exactly zero; negative (returns/CN) are valid
            $txval = $this->num($row[$cm['taxable_value']] ?? null);
            if ($txval == 0) continue;

            // ── Seller GSTIN filter ────────────────────────────────
            // File may contain rows for multiple sellers/GSTINs. Only
            // keep rows belonging to the currently-selected client.
            if (($cm['seller_gstin'] ?? -1) >= 0 && $this->clientGstin !== '') {
                $rowSellerGstin = strtoupper(trim((string)($row[$cm['seller_gstin']] ?? '')));
                if ($rowSellerGstin !== '' && $rowSellerGstin !== strtoupper($this->clientGstin)) {
                    $skippedWrongSeller++;
                    continue;
                }
            }

            // ── Period (month) filter ──────────────────────────────
            // Only keep rows whose relevant date falls in the selected
            // MM/YYYY period. If the date can't be parsed, do NOT
            // filter it out on this basis (avoid false negatives).
            //
            // ← NEW (v8): if the row carries a Credit Note Date, that
            // date — not the original Invoice Date — decides the
            // period. A credit note belongs to the period it was
            // ISSUED in, regardless of when the original sale invoice
            // was raised (e.g. invoice in May, credit note in June →
            // this row is a June transaction for GST purposes).
            $periodCheckDate = null;
            if (($cm['invoice_date'] ?? -1) >= 0) {
                $invDateRaw = $row[$cm['invoice_date']] ?? null;
                $periodCheckDate = $invDateRaw !== null ? $this->date($invDateRaw) : null;
            }
            if (($cm['credit_note_date'] ?? -1) >= 0) {
                $cnDateRaw = $row[$cm['credit_note_date']] ?? null;
                if ($cnDateRaw !== null && trim((string)$cnDateRaw) !== '') {
                    $cnDateParsed = $this->date($cnDateRaw);
                    if ($cnDateParsed) $periodCheckDate = $cnDateParsed; // credit note date wins
                }
            }
            if ($periodCheckDate && $this->period !== '') {
                $rowPeriod = date('m/Y', strtotime($periodCheckDate));
                if ($rowPeriod !== $this->period) {
                    $skippedWrongPeriod++;
                    continue;
                }
            }

            // ── POS resolve ──
            $pos = $this->resolvePOS($row, $cm, $isB2B);
            if ($pos === '') {
                $this->warn("'{$label}' row {$i}: Cannot resolve state — skipped");
                continue;
            }

            // ── GST Rate ──
            $rate = $this->resolveRate($row, $cm, $txval);

            // ── Tax amounts — read from file; null if column absent ──
            $igst = ($cm['igst_amount'] ?? -1) >= 0
                ? $this->numOrNull($row[$cm['igst_amount']] ?? null)
                : null;
            $cgst = ($cm['cgst_amount'] ?? -1) >= 0
                ? $this->numOrNull($row[$cm['cgst_amount']] ?? null)
                : null;
            $sgst = ($cm['sgst_amount'] ?? -1) >= 0
                ? $this->numOrNull($row[$cm['sgst_amount']] ?? null)
                : null;

            // ── Invoice details (MTR only) ──
            $invoiceNo   = null;
            $invoiceDate = null;

            if ($hasMtrCols) {
                if (($cm['invoice_number'] ?? -1) >= 0) {
                    $v = $row[$cm['invoice_number']] ?? null;
                    $invoiceNo = ($v !== null && trim((string)$v) !== '') ? trim((string)$v) : null;
                }
                if (($cm['invoice_date'] ?? -1) >= 0) {
                    $invoiceDate = $this->date($row[$cm['invoice_date']] ?? null);
                }
            }

            // ── Quantity ──
            $qty = null;
            if ($hasQtyCol) {
                $qv  = $this->num($row[$cm['quantity']] ?? null);
                $qty = $qv != 0 ? (int)abs($qv) : null;
            }

            // ── Buyer GSTIN (B2B) ──
            $buyerGstin = null;
            if (($cm['buyer_gstin'] ?? -1) >= 0) {
                $bg = trim((string)($row[$cm['buyer_gstin']] ?? ''));
                if ($bg !== '') {
                    $buyerGstin = strtoupper($bg);
                } elseif ($isB2B) {
                    $this->warn("'{$label}' row {$i}: B2B row missing buyer GSTIN — skipped");
                    continue;
                }
            }

            // ── HSN ──
            $hsn = ($cm['hsn_code'] ?? -1) >= 0
                ? trim((string)($row[$cm['hsn_code']] ?? ''))
                : null;

            $t = $this->makeTransaction(
                pos:          $pos,
                rate:         $rate,
                taxableValue: $txval,
                igstAmt:      $igst,
                cgstAmt:      $cgst,
                sgstAmt:      $sgst,
                invoiceNo:    $invoiceNo,
                invoiceDate:  $invoiceDate,
                qty:          $qty,
                hsn:          $hsn ?: null,
                buyerGstin:   $buyerGstin,
            );

            if (!empty($t)) $transactions[] = $t;
        }

        // One summary warning per sheet, not one per row.
        if ($skippedWrongSeller > 0) {
            $this->warn("'{$label}': {$skippedWrongSeller} row(s) skipped — belonged to a different seller GSTIN than the selected client ({$this->clientGstin}).");
        }
        if ($skippedWrongPeriod > 0) {
            $this->warn("'{$label}': {$skippedWrongPeriod} row(s) skipped — date (invoice date, or credit note date where present) fell outside the selected period ({$this->period}).");
        }

        return $transactions;
    }

    // ════════════════════════════════════════════════════════════════════
    //  DETAILED MTR FORMAT DETECTION
    // ════════════════════════════════════════════════════════════════════

    private function detectDetailedMtrColumns(array $headers): ?array
    {
        $normalized = array_map(fn($h) => strtolower(trim((string)$h)), $headers);

        $find = function(string $name) use ($normalized): int {
            $idx = array_search(strtolower($name), $normalized, true);
            return $idx === false ? -1 : (int)$idx;
        };

        $taxExclGross = $find('Tax Exclusive Gross');
        $totalTaxAmt  = $find('Total Tax Amount');

        // Unique fingerprint — these two columns together never appear in
        // the GSTR-1 Ready format, only in Amazon's detailed MTR export.
        if ($taxExclGross < 0 || $totalTaxAmt < 0) {
            return null;
        }

        $buyerGstin = $find('Customer Bill To Gstid');
        if ($buyerGstin < 0) $buyerGstin = $find('Customer Ship To Gstid');
        if ($buyerGstin < 0) $buyerGstin = $find('Bill To Gstid');

        // Seller's own GSTIN column for this export format.
        $sellerGstin = $find('Seller Gstin');

        // ← NEW (v8): credit note issue date. Amazon's detailed MTR
        // export names this column "Credit Note Date" exactly.
        $creditNoteDate = $find('Credit Note Date');

        return [
            'taxable_value'     => $taxExclGross,
            'total_tax'         => $totalTaxAmt,
            'cgst_rate'         => $find('Cgst Rate'),
            'sgst_rate'         => $find('Sgst Rate'),
            'utgst_rate'        => $find('Utgst Rate'),
            'igst_rate'         => $find('Igst Rate'),
            'place_of_supply'   => $find('Ship To State'),
            'invoice_number'    => $find('Invoice Number'),
            'invoice_date'      => $find('Invoice Date'),
            'credit_note_date'  => $creditNoteDate, // ← NEW (v8)
            'quantity'          => $find('Quantity'),
            'hsn_code'          => $find('Hsn/sac'),
            'buyer_gstin'       => $buyerGstin,
            'seller_gstin'      => $sellerGstin,
        ];
    }

    // ════════════════════════════════════════════════════════════════════
    //  DETAILED MTR ROW EXTRACTION
    // ════════════════════════════════════════════════════════════════════

    private function parseDetailedMtrRows(array $rows, int $hIdx, array $cm, string $label, bool $isB2B): array
    {
        $transactions       = [];
        $skippedWrongSeller = 0;
        $skippedWrongPeriod = 0;

        for ($i = $hIdx + 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if ($this->rowIsEmpty($row, 1)) continue;

            // ── Taxable value — read as-is, sign preserved (returns/CN negative) ──
            $txval = $this->num($row[$cm['taxable_value']] ?? null);
            if ($txval == 0) continue; // Cancel rows / zero-value rows

            // ── Seller GSTIN filter ────────────────────────────────
            // File may contain rows for multiple sellers/GSTINs. Only
            // keep rows belonging to the currently-selected client.
            if (($cm['seller_gstin'] ?? -1) >= 0 && $this->clientGstin !== '') {
                $rowSellerGstin = strtoupper(trim((string)($row[$cm['seller_gstin']] ?? '')));
                if ($rowSellerGstin !== '' && $rowSellerGstin !== strtoupper($this->clientGstin)) {
                    $skippedWrongSeller++;
                    continue;
                }
            }

            // ── Invoice date (kept separately — this is what we store
            //    on the transaction record regardless of which date
            //    decided the period; the transaction's "invoice date"
            //    field should still show the ORIGINAL invoice date). ──
            $invoiceDateResolved = null;
            if (($cm['invoice_date'] ?? -1) >= 0) {
                $invoiceDateResolved = $this->date($row[$cm['invoice_date']] ?? null);
            }

            // ── Period (month) filter ──────────────────────────────
            // Only keep rows whose relevant date falls in the selected
            // MM/YYYY period. If unparseable, don't filter on this basis.
            //
            // ← NEW (v8): if the row carries a Credit Note Date, that
            // date — not the original Invoice Date — decides the
            // period. A credit note (Refund row) belongs to the period
            // it was ISSUED in, regardless of when the original sale
            // invoice was raised. Example: invoice dated 27-May, credit
            // note dated 01-Jun → for a June upload this row IS a June
            // row and its (negative) value must be netted into June's
            // totals — filtering it out on Invoice Date alone silently
            // drops a valid refund and overstates the June total.
            $periodCheckDate = $invoiceDateResolved;
            if (($cm['credit_note_date'] ?? -1) >= 0) {
                $cnDateRaw = $row[$cm['credit_note_date']] ?? null;
                if ($cnDateRaw !== null && trim((string)$cnDateRaw) !== '') {
                    $cnDateParsed = $this->date($cnDateRaw);
                    if ($cnDateParsed) $periodCheckDate = $cnDateParsed; // credit note date wins
                }
            }
            if ($periodCheckDate && $this->period !== '') {
                $rowPeriod = date('m/Y', strtotime($periodCheckDate));
                if ($rowPeriod !== $this->period) {
                    $skippedWrongPeriod++;
                    continue;
                }
            }

            // ── Place of supply ──
            $pos = '';
            if (($cm['place_of_supply'] ?? -1) >= 0) {
                $raw = $row[$cm['place_of_supply']] ?? null;
                if ($raw !== null && trim((string)$raw) !== '') {
                    $cacheKey = (string)$raw;
                    if (!isset($this->posCache[$cacheKey])) {
                        $this->posCache[$cacheKey] = $this->resolveStateCode($raw);
                    }
                    $pos = $this->posCache[$cacheKey];
                }
            }
            // Fallback: buyer GSTIN's first 2 digits
            if ($pos === '' && ($cm['buyer_gstin'] ?? -1) >= 0) {
                $gstin = trim((string)($row[$cm['buyer_gstin']] ?? ''));
                if (strlen($gstin) >= 2) {
                    $code = str_pad((string)(int)substr($gstin, 0, 2), 2, '0', STR_PAD_LEFT);
                    if (in_array($code, self::VALID_STATE_CODES, true)) $pos = $code;
                }
            }
            // Fallback: intra-state B2C sale → seller's own state
            if ($pos === '' && !$isB2B && $this->sellerStateCode !== '') {
                $pos = $this->sellerStateCode;
            }
            if ($pos === '') {
                $this->warn("'{$label}' row {$i}: Cannot resolve state — skipped");
                continue;
            }

            // ── Rates (decimal fractions, e.g. 0.09 = 9%) ──
            $cgstRate  = ($cm['cgst_rate']  ?? -1) >= 0 ? $this->num($row[$cm['cgst_rate']]  ?? null) : 0.0;
            $sgstRate  = ($cm['sgst_rate']  ?? -1) >= 0 ? $this->num($row[$cm['sgst_rate']]  ?? null) : 0.0;
            $utgstRate = ($cm['utgst_rate'] ?? -1) >= 0 ? $this->num($row[$cm['utgst_rate']] ?? null) : 0.0;
            $igstRate  = ($cm['igst_rate']  ?? -1) >= 0 ? $this->num($row[$cm['igst_rate']]  ?? null) : 0.0;

            $rateRaw = $cgstRate + $sgstRate + $utgstRate + $igstRate;
            $rate    = $rateRaw < 1 ? round($rateRaw * 100, 2) : round($rateRaw, 2);

            // ── Total tax — read as-is from file (already netted, sign preserved) ──
            $totalTax = ($cm['total_tax'] ?? -1) >= 0 ? $this->num($row[$cm['total_tax']] ?? null) : 0.0;

            // ── Split total_tax into igst/cgst/sgst based on which rate applies ──
            $igst = 0.0; $cgst = 0.0; $sgst = 0.0;
            if ($igstRate > 0) {
                $igst = round($totalTax, 2);
            } elseif ($utgstRate > 0 && $cgstRate == 0 && $sgstRate == 0) {
                // UTGST is folded into the sgst_amount bucket, matching this
                // codebase's existing convention (see COLS['sgst_amount']).
                $sgst = round($totalTax, 2);
            } elseif ($cgstRate > 0 || $sgstRate > 0) {
                $half = round($totalTax / 2, 2);
                $cgst = $half;
                $sgst = round($totalTax - $half, 2); // avoids rounding drift, total stays exact
            }
            // else: nil-rated / no rate found → igst=cgst=sgst=0 (correct: no tax)

            // ── Invoice number / date ──
            $invoiceNo = null;
            if (($cm['invoice_number'] ?? -1) >= 0) {
                $v = $row[$cm['invoice_number']] ?? null;
                $invoiceNo = ($v !== null && trim((string)$v) !== '') ? trim((string)$v) : null;
            }
            $invoiceDate = $invoiceDateResolved;

            // ── Quantity ──
            $qty = null;
            if (($cm['quantity'] ?? -1) >= 0) {
                $qv  = $this->num($row[$cm['quantity']] ?? null);
                $qty = $qv != 0 ? (int)abs($qv) : null;
            }

            // ── Buyer GSTIN — validated centrally in makeTransaction() via
            //    BaseParser::isValidGstin(); invalid/garbage becomes null. ──
            $buyerGstin = null;
            if (($cm['buyer_gstin'] ?? -1) >= 0) {
                $bg = trim((string)($row[$cm['buyer_gstin']] ?? ''));
                if ($bg !== '') {
                    $buyerGstin = strtoupper($bg);
                } elseif ($isB2B) {
                    $this->warn("'{$label}' row {$i}: B2B row missing buyer GSTIN — skipped");
                    continue;
                }
            }

            // ── HSN ──
            $hsn = ($cm['hsn_code'] ?? -1) >= 0
                ? trim((string)($row[$cm['hsn_code']] ?? ''))
                : null;

            $t = $this->makeTransaction(
                pos:          $pos,
                rate:         $rate,
                taxableValue: $txval,
                igstAmt:      $igst,
                cgstAmt:      $cgst,
                sgstAmt:      $sgst,
                invoiceNo:    $invoiceNo,
                invoiceDate:  $invoiceDate,
                qty:          $qty,
                hsn:          $hsn ?: null,
                buyerGstin:   $buyerGstin,
            );

            if (!empty($t)) $transactions[] = $t;
        }

        // One summary warning per sheet, not one per row.
        if ($skippedWrongSeller > 0) {
            $this->warn("'{$label}': {$skippedWrongSeller} row(s) skipped — belonged to a different seller GSTIN than the selected client ({$this->clientGstin}).");
        }
        if ($skippedWrongPeriod > 0) {
            $this->warn("'{$label}': {$skippedWrongPeriod} row(s) skipped — date (invoice date, or credit note date where present) fell outside the selected period ({$this->period}).");
        }

        return $transactions;
    }

    // ════════════════════════════════════════════════════════════════════
    //  POS RESOLVER  (UNCHANGED — used by the GSTR-1 style path only)
    // ════════════════════════════════════════════════════════════════════

    private function resolvePOS(array $row, array $cm, bool $isB2B): string
    {
        // 1. place_of_supply column
        if (($cm['place_of_supply'] ?? -1) >= 0) {
            $raw = $row[$cm['place_of_supply']] ?? null;
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

        // 2. Buyer GSTIN first 2 digits = state code
        if (($cm['buyer_gstin'] ?? -1) >= 0) {
            $gstin = trim((string)($row[$cm['buyer_gstin']] ?? ''));
            if (strlen($gstin) >= 2) {
                $code = str_pad((string)(int)substr($gstin, 0, 2), 2, '0', STR_PAD_LEFT);
                if (in_array($code, self::VALID_STATE_CODES, true)) {
                    return $code;
                }
            }
        }

        // 3. INTRA sale: seller's own state (B2C only)
        if (!$isB2B && $this->sellerStateCode !== '') {
            return $this->sellerStateCode;
        }

        return '';
    }

    // ════════════════════════════════════════════════════════════════════
    //  RATE RESOLVER  (UNCHANGED — used by the GSTR-1 style path only)
    // ════════════════════════════════════════════════════════════════════

    private function resolveRate(array $row, array $cm, float $txval): float
    {
        // 1. Rate column se
        if (($cm['gst_rate'] ?? -1) >= 0) {
            $rate = $this->num($row[$cm['gst_rate']] ?? null);
            if ($rate > 0) {
                // Amazon GSTR-1: decimal form (0.18 = 18%)
                return $rate < 1 ? round($rate * 100, 2) : round($rate, 2);
            }
        }

        // 2. Back-calculate from tax amounts
        $igst = ($cm['igst_amount'] ?? -1) >= 0 ? abs($this->num($row[$cm['igst_amount']] ?? null)) : 0;
        $cgst = ($cm['cgst_amount'] ?? -1) >= 0 ? abs($this->num($row[$cm['cgst_amount']] ?? null)) : 0;
        $sgst = ($cm['sgst_amount'] ?? -1) >= 0 ? abs($this->num($row[$cm['sgst_amount']] ?? null)) : 0;

        $totalTax = $igst + $cgst + $sgst;
        if ($totalTax > 0 && abs($txval) > 0) {
            return round($totalTax / abs($txval) * 100, 2);
        }

        return 0.0;
    }

    // ════════════════════════════════════════════════════════════════════
    //  numOrNull — returns null if value is empty/missing, float otherwise
    //  (UNCHANGED)
    // ════════════════════════════════════════════════════════════════════

    private function numOrNull(mixed $val): ?float
    {
        if ($val === null || trim((string)$val) === '') return null;
        return $this->num($val);
    }

    // ════════════════════════════════════════════════════════════════════
    //  HEADER FINDER  (UNCHANGED FROM v4)
    // ════════════════════════════════════════════════════════════════════

    private function findHeaderRowAmazon(array $rows): int
    {
        $groups = [
            ['taxable value', 'taxable amount'],
            ['place of supply', 'delivery state', 'destination state', 'ship to state', 'customer state'],
            ['igst', 'cgst', 'sgst', 'gst rate', 'applicable % of tax rate'],
            ['invoice number', 'invoice no', 'shipment id', 'order id'],
            ['gstin', 'buyer'],
        ];

        foreach ($rows as $i => $row) {
            $cells    = array_map('strval', $row);
            $nonEmpty = count(array_filter($cells, fn($v) => trim($v) !== ''));

            // Real header rows have most/all columns labeled.
            if ($nonEmpty < 4) continue;

            $rowStr  = strtolower(implode(' ', $cells));
            $matched = 0;
            foreach ($groups as $group) {
                foreach ($group as $kw) {
                    if (str_contains($rowStr, $kw)) { $matched++; break; }
                }
            }
            if ($matched >= 2) return $i;
        }

        // Fallback: first row with 4+ non-empty cells
        foreach ($rows as $i => $row) {
            $nonEmpty = count(array_filter(array_map('strval', $row), fn($v) => trim($v) !== ''));
            if ($nonEmpty >= 4) return $i;
        }

        return -1;
    }

    // ════════════════════════════════════════════════════════════════════
    //  RATE-HEADER GUARD  (UNCHANGED FROM v5)
    // ════════════════════════════════════════════════════════════════════

    private function looksLikeRateHeader(string $normalizedHeader): bool
    {
        return str_contains($normalizedHeader, 'rate') || str_contains($normalizedHeader, '%');
    }

    // ════════════════════════════════════════════════════════════════════
    //  COLUMN MAP  (UNCHANGED FROM v5 — GSTR-1 style path only)
    // ════════════════════════════════════════════════════════════════════

    private function buildAmazonColMap(array $headers): array
    {
        $map         = [];
        $normalized  = array_map(fn($h) => strtolower(trim((string)$h)), $headers);
        $usedIndexes = [];

        foreach (self::COLS as $field => $aliases) {
            $map[$field]   = -1;
            $isAmountField = in_array($field, self::AMOUNT_FIELDS, true);

            // Pass 1: exact match
            foreach ($aliases as $alias) {
                $idx = array_search(strtolower($alias), $normalized, true);
                if ($idx === false || in_array($idx, $usedIndexes, true)) continue;
                if ($isAmountField && $this->looksLikeRateHeader($normalized[$idx])) continue;
                $map[$field] = (int)$idx;
                break;
            }
            if ($map[$field] >= 0) { $usedIndexes[] = $map[$field]; continue; }

            // Pass 2: header contains alias
            foreach ($aliases as $alias) {
                $al = strtolower($alias);
                foreach ($normalized as $i => $h) {
                    if ($h === '' || in_array($i, $usedIndexes, true)) continue;
                    if ($isAmountField && $this->looksLikeRateHeader($h)) continue;
                    if (str_contains($h, $al)) {
                        $map[$field] = $i;
                        break 2;
                    }
                }
            }
            if ($map[$field] >= 0) { $usedIndexes[] = $map[$field]; continue; }

            // Pass 3: alias contains header (short headers like "rate", "state")
            foreach ($aliases as $alias) {
                $al = strtolower($alias);
                foreach ($normalized as $i => $h) {
                    if (in_array($i, $usedIndexes, true)) continue;
                    if ($isAmountField && $this->looksLikeRateHeader($h)) continue;
                    if (strlen($h) >= 3 && str_contains($al, $h)) {
                        $map[$field] = $i;
                        break 2;
                    }
                }
            }
            if ($map[$field] >= 0) $usedIndexes[] = $map[$field];
        }

        return $map;
    }

    // ════════════════════════════════════════════════════════════════════
    //  SHEET FINDER  (UNCHANGED)
    // ════════════════════════════════════════════════════════════════════

    private function findSheet(array $sheetMap, array $patterns): ?array
    {
        foreach ($patterns as $pattern) {
            $p = strtolower(trim($pattern));
            if (isset($sheetMap[$p])) return $sheetMap[$p]['rows'];
            foreach ($sheetMap as $key => $data) {
                if (str_contains($key, $p)) return $data['rows'];
            }
        }
        return null;
    }
}
