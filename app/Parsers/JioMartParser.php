<?php

namespace App\Parsers;

use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * ══════════════════════════════════════════════════════════════════════
 *  JIOMART PARSER  (Reliance Jio)
 * ══════════════════════════════════════════════════════════════════════
 *
 *  Format: Excel / CSV — Invoice Report
 *
 *  Columns (jo file mein ho sakti hain):
 *    Invoice Number, Invoice Date, Place of Supply, HSN,
 *    Taxable Value, GST Rate, IGST Amount, CGST Amount, SGST Amount,
 *    Buyer GSTIN, Quantity
 *
 *  RULES:
 *  - taxable_value == 0  → skip
 *  - taxable_value < 0   → counted (returns / credit notes)
 *  - quantity            → file se jo aaye wahi, column absent = null
 *  - tax amounts         → file se aaye toh use, warna rate se calculate
 *  - No hardcoded amount defaults
 *  - JioMart is a B2C-only marketplace report — it never carries a real
 *    buyer GSTIN column. buyer_gstin is therefore ALWAYS null for this
 *    platform (see note above COLS['buyer_gstin'] below) so every row
 *    correctly lands in B2CS, never B2B.
 * ══════════════════════════════════════════════════════════════════════
 */
class JioMartParser extends BaseParser
{
    protected string $platform = 'jiomart';

    private array $posCache = [];

    private const COLS = [
        'taxable_value' => [
            'taxable value (final invoice amount -taxes)',
            'taxable value', 'taxable_value', 'taxable amount',
            'base amount', 'item taxable value', 'net value',
            'net taxable value', 'selling price', 'item value',
        ],
        'gst_rate' => [
            'igst rate', 'cgst rate', 'gst rate', 'tax rate', 'rate', 'gst%',
            'gst_rate', 'tax_rate', 'applicable tax rate',
        ],
        'place_of_supply' => [
            // Real JioMart header — customer's delivery state is the
            // correct place-of-supply field (billing state is a decoy —
            // for e-comm sales POS = ship-to state, not bill-to state).
            "customer's delivery state", 'customer delivery state',
            'place of supply', 'delivery state', 'customer state',
            'state', 'ship to state', 'pos', 'place_of_supply',
            'shipping state', 'buyer state', 'destination state',
        ],
        'igst_amount' => [
            'igst amount', 'igst', 'integrated tax',
            'igst_amount', 'igst amt', 'integrated tax amount',
        ],
        'cgst_amount' => [
            'cgst amount', 'cgst', 'central tax',
            'cgst_amount', 'cgst amt', 'central tax amount',
        ],
        'sgst_amount' => [
            // Real header: "SGST Amount (Or UTGST as applicable)"
            'sgst amount (or utgst as applicable)',
            'sgst amount', 'sgst', 'state tax',
            'sgst_amount', 'sgst amt', 'state tax amount',
            'utgst', 'utgst amount', 'sgst/utgst amount',
        ],
        'invoice_number' => [
            // Real JioMart headers, checked FIRST and in this exact
            // priority order — "Order ID" must never win over these,
            // since Order ID is a per-item shipment id, not the invoice
            // number, and using it breaks Doc-Issued min/max/cancel/net.

            // UNCOMMENT FROM HERE  FOR DOCUMNET ISSUE
            'buyer invoice id',
            'original invoice id',
            'invoice number', 'invoice no', 'invoice_number',
            'jio invoice number', 'invoice no.',
            'reference number', 'document number',


            // 'order id' intentionally NOT included — it is a shipment
            // identifier in JioMart's export, not an invoice number.
        ],
        'invoice_date' => [
            // Real header
            'buyer invoice date',
            'invoice date', 'order date', 'date',
            'invoice_date', 'order_date', 'transaction date',
            'invoice dt', 'bill date',
        ],
        'hsn_code' => [
            'hsn code',
            'hsn', 'hsn/sac',
            'hsn_code', 'hsn sac', 'tariff code',
        ],
        'quantity' => [
            'item quantity',
            'quantity', 'qty', 'units',
            'no. of items', 'no of items', 'item count',
            'total quantity', 'item quantity',
        ],
        // ── buyer_gstin — INTENTIONALLY LEFT EMPTY ──────────────────────
        // JioMart's Invoice Report has NO buyer-GSTIN column; it is a
        // B2C-only report. Previously this list contained a generic
        // 'gstin' alias, which false-matched columns like "Seller GSTIN"
        // via substring search and wrongly pushed genuine B2C rows into
        // the B2B tab. buyer_gstin is now resolved to null unconditionally
        // for this platform (see parseSheet() below) instead of via
        // column lookup, so this key intentionally has no aliases.
        'buyer_gstin' => [],
    ];

    // Sheets to skip
    private const SKIP_SHEETS = [
        'summary', 'readme', 'instructions', 'lookup',
        'index', 'help', 'notes',
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

    public function canHandle(Spreadsheet $spreadsheet): bool
    {
        // Sheet name check
        $names  = array_map('strtolower', $spreadsheet->getSheetNames());
        $detect = ['jiomart', 'jio mart', 'invoice report', 'invoices', 'gst report'];
        foreach ($names as $n) {
            foreach ($detect as $d) {
                if (str_contains($n, $d)) return true;
            }
        }

        // Header signature check
        try {
            $sheet    = $spreadsheet->getActiveSheet();
            $allRows  = $sheet->toArray(null, true, true, false);
            foreach (array_slice($allRows, 0, 5) as $row) {
                $rowStr = strtolower(implode(' ', array_map('strval', $row)));
                if ((str_contains($rowStr, 'jio') || str_contains($rowStr, 'jiomart'))
                 && str_contains($rowStr, 'invoice')) {
                    return true;
                }
                // Column signature: taxable value + place of supply + invoice
                $hits = 0;
                foreach (['taxable value', 'taxable_value', 'base amount'] as $sig) {
                    if (str_contains($rowStr, $sig)) $hits++;
                }
                foreach (['place of supply', 'delivery state', 'customer state'] as $sig) {
                    if (str_contains($rowStr, $sig)) $hits++;
                }
                foreach (['invoice number', 'invoice no', 'buyer invoice id', 'order id'] as $sig) {
                    if (str_contains($rowStr, $sig)) $hits++;
                }
                if ($hits >= 2) return true;
            }
        } catch (\Throwable $e) {}

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
        $transactions          = [];

        foreach ($sheets as $sheetName => $rows) {
            if (empty($rows)) continue;
            if ($this->shouldSkipByName($sheetName)) continue;

            $parsed = $this->parseSheet($rows, $sheetName);
            if (!empty($parsed)) {
                $transactions = array_merge($transactions, $parsed);
                $this->warn("Sheet '{$sheetName}': " . count($parsed) . " transactions parsed.");
            }
        }

        if (empty($transactions)) {
            $this->warn("No transactions found. Check file format.");
        }

        return [
            'transactions' => array_values(array_filter($transactions)),
            'warnings'     => $this->warnings,
        ];
    }

    // ════════════════════════════════════════════════════════════════════
    //  SHEET PARSER
    // ════════════════════════════════════════════════════════════════════

    private function parseSheet(array $rows, string $label): array
    {
        if (empty($rows)) return [];

        // Find header row
        $hIdx = $this->findHeaderRowJioMart($rows);
        if ($hIdx < 0) {
            $this->warn("'{$label}': Header row not found.");
            return [];
        }

        $headers = array_map('strval', $rows[$hIdx]);
        $cm      = $this->buildJioMartColMap($headers);

        if (($cm['taxable_value'] ?? -1) < 0) {
            $this->warn("'{$label}': taxable_value column missing. Cols: "
                . implode(' | ', array_filter(array_slice($headers, 0, 15))));
            return [];
        }

        $hasInvoiceNo   = ($cm['invoice_number'] ?? -1) >= 0;
        $hasInvoiceDate = ($cm['invoice_date']   ?? -1) >= 0;
        $hasHsn         = ($cm['hsn_code']       ?? -1) >= 0;
        $hasQty         = ($cm['quantity']        ?? -1) >= 0;
        $hasPOS         = ($cm['place_of_supply'] ?? -1) >= 0;

        // buyer_gstin is NEVER read from any column for JioMart — this
        // report has no such field. Every transaction is B2C. See the
        // note above COLS['buyer_gstin'] for why.

        $transactions = [];

        for ($i = $hIdx + 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if ($this->rowIsEmpty($row, 1)) continue;

            // ── Taxable Value ──
            $txval = $this->num($row[$cm['taxable_value']] ?? null);
            if ($txval == 0) continue;

            // ── Place of Supply ──
            $pos = '';
            if ($hasPOS) {
                $raw = $row[$cm['place_of_supply']] ?? null;
                if ($raw !== null && trim((string)$raw) !== '') {
                    $cacheKey = (string)$raw;
                    if (!isset($this->posCache[$cacheKey])) {
                        $this->posCache[$cacheKey] = $this->resolveStateCode($raw);
                    }
                    $pos = $this->posCache[$cacheKey];
                }
            }

            // Fallback to seller state
            if ($pos === '' && $this->sellerStateCode !== '') {
                $pos = $this->sellerStateCode;
            }

            if ($pos === '') {
                $this->warn("'{$label}' row {$i}: Cannot resolve state — skipped");
                continue;
            }

            // ── GST Rate ──
            $rate = 0.0;
            if (($cm['gst_rate'] ?? -1) >= 0) {
                $r = $this->num($row[$cm['gst_rate']] ?? null);
                if ($r > 0) {
                    $rate = $r < 1 ? round($r * 100, 2) : round($r, 2);
                }
            }

            // ── Tax Amounts — read from file if columns exist ──
            $igst = ($cm['igst_amount'] ?? -1) >= 0
                ? $this->numOrNull($row[$cm['igst_amount']] ?? null)
                : null;
            $cgst = ($cm['cgst_amount'] ?? -1) >= 0
                ? $this->numOrNull($row[$cm['cgst_amount']] ?? null)
                : null;
            $sgst = ($cm['sgst_amount'] ?? -1) >= 0
                ? $this->numOrNull($row[$cm['sgst_amount']] ?? null)
                : null;

            // ── Invoice Number ──
            $invoiceNo = null;
            if ($hasInvoiceNo) {
                $v         = $row[$cm['invoice_number']] ?? null;
                $invoiceNo = ($v !== null && trim((string)$v) !== '') ? trim((string)$v) : null;
            }

            // ── Invoice Date ──
            $invoiceDate = null;
            if ($hasInvoiceDate) {
                $invoiceDate = $this->date($row[$cm['invoice_date']] ?? null);
            }

            // ── Quantity ──
            $qty = null;
            if ($hasQty) {
                $qv  = $this->num($row[$cm['quantity']] ?? null);
                $qty = $qv != 0 ? (int)abs($qv) : null;
            }

            // ── HSN ──
            $hsn = null;
            if ($hasHsn) {
                $h   = trim((string)($row[$cm['hsn_code']] ?? ''));
                $hsn = $h !== '' ? $h : null;
            }

            // ── Buyer GSTIN — always null for JioMart (B2C-only report) ──
            $buyerGstin = null;

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
                hsn:          $hsn,
                buyerGstin:   $buyerGstin,
            );

            if (!empty($t)) $transactions[] = $t;
        }

        return $transactions;
    }

    // ════════════════════════════════════════════════════════════════════
    //  HEADER ROW FINDER
    // ════════════════════════════════════════════════════════════════════

    private function findHeaderRowJioMart(array $rows): int
    {
        $groups = [
            ['taxable value', 'taxable amount', 'base amount', 'net value', 'taxable_value'],
            ['place of supply', 'delivery state', 'customer state', 'state', 'pos'],
            ['igst', 'cgst', 'sgst', 'gst rate', 'tax rate', 'rate'],
            ['invoice number', 'invoice no', 'buyer invoice id', 'order id', 'invoice_number'],
            ['hsn', 'hsn code', 'quantity', 'qty'],
        ];

        foreach ($rows as $i => $row) {
            $rowStr  = strtolower(implode(' ', array_map('strval', $row)));
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
            $ne = count(array_filter(array_map('strval', $row), fn($v) => trim($v) !== ''));
            if ($ne >= 4) return $i;
        }

        return -1;
    }

    // ════════════════════════════════════════════════════════════════════
    //  COLUMN MAP BUILDER
    // ════════════════════════════════════════════════════════════════════

    private function buildJioMartColMap(array $headers): array
    {
        $map        = [];
        $normalized = array_map(fn($h) => strtolower(trim((string)$h)), $headers);

        foreach (self::COLS as $field => $aliases) {
            $map[$field] = -1;

            // buyer_gstin has no aliases by design for JioMart — never
            // resolved from any column (see note above COLS['buyer_gstin']).
            if (empty($aliases)) continue;

            // Pass 1: exact
            foreach ($aliases as $alias) {
                $idx = array_search(strtolower($alias), $normalized, true);
                if ($idx !== false) { $map[$field] = (int)$idx; break; }
            }
            if ($map[$field] >= 0) continue;

            // Pass 2: header contains alias
            foreach ($aliases as $alias) {
                $al = strtolower($alias);
                foreach ($normalized as $i => $h) {
                    if ($h !== '' && str_contains($h, $al)) {
                        $map[$field] = $i;
                        break 2;
                    }
                }
            }
            if ($map[$field] >= 0) continue;

            // Pass 3: alias contains header (short headers like "rate", "state")
            foreach ($aliases as $alias) {
                $al = strtolower($alias);
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

    // ════════════════════════════════════════════════════════════════════
    //  HELPERS
    // ════════════════════════════════════════════════════════════════════

    /**
     * Returns null if value is empty/missing, float otherwise.
     * Used for tax columns: distinguish "0 in file" vs "column absent".
     */
    private function numOrNull(mixed $val): ?float
    {
        if ($val === null || trim((string)$val) === '') return null;
        return $this->num($val);
    }

    private function shouldSkipByName(string $sheetName): bool
    {
        $sn = strtolower(trim($sheetName));
        foreach (self::SKIP_SHEETS as $skip) {
            if (str_contains($sn, $skip)) return true;
        }
        return false;
    }
}
