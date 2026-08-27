<?php

namespace App\Parsers;

use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * ══════════════════════════════════════════════════════════════════════
 *  AJIO PARSER  (Reliance Retail)
 * ══════════════════════════════════════════════════════════════════════
 *
 *  Format: Excel / CSV — Single or Multi-sheet
 *
 *  Possible Columns:
 *    Order ID, Order Date, State / Place of Supply, HSN,
 *    GST Rate, Taxable Amount, IGST, CGST, SGST, Quantity
 *
 *  STRICT RULES (GST Portal accuracy):
 *  - taxable_value == 0          → skip (not counted)
 *  - taxable_value < 0           → counted as-is (returns / CN)
 *  - quantity                    → ONLY from file; null if column absent
 *  - quantity NEVER defaulted    → no max(1,...), no hardcoded 1
 *  - tax amounts                 → file se jo aaye wahi use karo
 *                                   (null = column absent = calculate from rate)
 *  - state resolution            → file cell se, galt nahi hona chahiye
 *  - No hardcoded amount defaults
 * ══════════════════════════════════════════════════════════════════════
 */
class AjioParser extends BaseParser
{
    protected string $platform = 'ajio';

    private array $posCache = [];

    private const COLS = [
        'taxable_value' => [
            'taxable amount', 'taxable value', 'taxable_value',
            'base amount', 'order taxable value', 'invoice taxable value',
            'net amount', 'taxable amt', 'item taxable value',
            'net taxable value', 'taxable', 'selling price',
        ],
        'gst_rate' => [
            'gst rate', 'gst%', 'tax rate', 'rate',
            'gst_rate', 'applicable gst rate', 'gst rate (%)',
            'tax rate (%)', 'rate (%)',
        ],
        'place_of_supply' => [
            'state', 'customer state', 'delivery state',
            'ship to state', 'buyer state', 'place of supply',
            'pos', 'order state', 'shipping state',
            'place_of_supply', 'destination state', 'delivered state',
        ],
        'igst_amount' => [
            'igst', 'igst amount', 'integrated tax amount',
            'igst_amount', 'igst amt', 'integrated tax',
        ],
        'cgst_amount' => [
            'cgst', 'cgst amount', 'central tax amount',
            'cgst_amount', 'cgst amt', 'central tax',
        ],
        'sgst_amount' => [
            'sgst', 'sgst amount', 'state tax amount',
            'sgst_amount', 'sgst amt', 'state tax',
            'utgst', 'utgst amount', 'sgst/utgst amount',
        ],
        'hsn_code' => [
            'hsn', 'hsn code', 'hsn/sac', 'sku hsn',
            'hsn_code', 'hsn sac', 'tariff code',
        ],
        'invoice_number' => [
            'order id', 'order_id', 'invoice number', 'invoice no',
            'bill number', 'invoice_number', 'order number',
            'reference number', 'document number', 'shipment id',
        ],
        'invoice_date' => [
            'order date', 'date', 'invoice date', 'sale date',
            'order_date', 'invoice_date', 'transaction date',
            'bill date', 'shipment date',
        ],
        'quantity' => [
            'quantity', 'qty', 'units', 'no. of items',
            'no of items', 'item count', 'total quantity',
            'item quantity', 'order quantity',
        ],
        'buyer_gstin' => [
            'buyer gstin', 'customer gstin', 'gstin',
            'buyer_gstin', 'gstin/uin', 'recipient gstin',
        ],
    ];

    private const SKIP_SHEETS = [
        'summary', 'lookup', 'readme', 'instructions',
        'guide', 'read me', 'index', 'help', 'notes',
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
    //  canHandle — Ajio file detect karo
    // ════════════════════════════════════════════════════════════════════

    public function canHandle(Spreadsheet $spreadsheet): bool
    {
        // Sheet name check
        $names  = array_map('strtolower', $spreadsheet->getSheetNames());
        $detect = ['ajio', 'reliance', 'b2c data', 'gst data'];
        foreach ($names as $n) {
            foreach ($detect as $d) {
                if (str_contains($n, $d)) return true;
            }
        }

        // Header signature check — pehli 5 rows mein dekho
        try {
            $sheet   = $spreadsheet->getActiveSheet();
            $allRows = $sheet->toArray(null, true, true, false);
            foreach (array_slice($allRows, 0, 5) as $row) {
                $rowStr = strtolower(implode(' ', array_map('strval', $row)));

                // Ajio-specific columns
                if (str_contains($rowStr, 'seller_sku')
                 || str_contains($rowStr, 'forward_shipment')
                 || str_contains($rowStr, 'ajio')) {
                    return true;
                }

                // Generic GST columns check (taxable + state + gst)
                $hits = 0;
                foreach (['taxable amount', 'taxable value', 'base amount', 'taxable_value'] as $sig) {
                    if (str_contains($rowStr, $sig)) { $hits++; break; }
                }
                foreach (['state', 'place of supply', 'delivery state', 'customer state'] as $sig) {
                    if (str_contains($rowStr, $sig)) { $hits++; break; }
                }
                foreach (['igst', 'cgst', 'sgst', 'gst rate', 'tax rate'] as $sig) {
                    if (str_contains($rowStr, $sig)) { $hits++; break; }
                }
                if ($hits >= 3) return true;
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
            }
        }

        // Fallback: agar koi bhi sheet se data nahi mila —
        // sab sheets try karo (skip list ignore karke)
        if (empty($transactions)) {
            $this->warn("No data found in standard sheets. Trying all sheets.");
            foreach ($sheets as $sheetName => $rows) {
                if (empty($rows)) continue;
                $parsed = $this->parseSheet($rows, $sheetName);
                if (!empty($parsed)) {
                    $transactions = array_merge($transactions, $parsed);
                    $this->warn("Sheet '{$sheetName}': " . count($parsed) . " transactions (fallback).");
                }
            }
        }

        if (empty($transactions)) {
            $this->warn("No valid transactions found. Verify file format.");
        }

        return [
            'transactions' => array_values(array_filter($transactions)),
            'warnings'     => $this->warnings,
        ];
    }

    // ════════════════════════════════════════════════════════════════════
    //  SHEET PARSER — actual file read karke count karo
    // ════════════════════════════════════════════════════════════════════

    private function parseSheet(array $rows, string $label): array
    {
        if (empty($rows)) return [];

        // Header row dhundo
        $hIdx = $this->findHeaderRowAjio($rows);
        if ($hIdx < 0) {
            $this->warn("'{$label}': Header row not found.");
            return [];
        }

        $headers = array_map('strval', $rows[$hIdx]);
        $cm      = $this->buildAjioColMap($headers);

        // taxable_value column mandatory hai
        if (($cm['taxable_value'] ?? -1) < 0) {
            $this->warn("'{$label}': taxable_value column missing. Found cols: "
                . implode(' | ', array_filter(array_slice($headers, 0, 15))));
            return [];
        }

        // Kaunse columns file mein hain — pehle check karo
        $hasPOS         = ($cm['place_of_supply'] ?? -1) >= 0;
        $hasRate        = ($cm['gst_rate']         ?? -1) >= 0;
        $hasIgst        = ($cm['igst_amount']      ?? -1) >= 0;
        $hasCgst        = ($cm['cgst_amount']      ?? -1) >= 0;
        $hasSgst        = ($cm['sgst_amount']      ?? -1) >= 0;
        $hasInvoiceNo   = ($cm['invoice_number']   ?? -1) >= 0;
        $hasInvoiceDate = ($cm['invoice_date']     ?? -1) >= 0;
        $hasQty         = ($cm['quantity']         ?? -1) >= 0;
        $hasHsn         = ($cm['hsn_code']         ?? -1) >= 0;
        $hasBuyerGstin  = ($cm['buyer_gstin']      ?? -1) >= 0;

        $transactions = [];
        $skippedZero  = 0;
        $skippedNoPos = 0;

        for ($i = $hIdx + 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if ($this->rowIsEmpty($row, 1)) continue;

            // ── Taxable Value — FILE SE PADHO ──
            $txval = $this->num($row[$cm['taxable_value']] ?? null);

            // Strict zero skip — 0 matlab koi taxable transaction nahi
            if ($txval == 0) {
                $skippedZero++;
                continue;
            }
            // Negative allowed — returns / credit notes

            // ── Place of Supply — FILE SE PADHO ──
            $pos = '';
            if ($hasPOS) {
                $raw = $row[$cm['place_of_supply']] ?? null;
                if ($raw !== null && trim((string)$raw) !== '') {
                    $cacheKey = trim((string)$raw);
                    if (!isset($this->posCache[$cacheKey])) {
                        $this->posCache[$cacheKey] = $this->resolveStateCode($raw);
                    }
                    $pos = $this->posCache[$cacheKey];
                }
            }

            // Fallback: seller state (intra-state assumption)
            if ($pos === '' && $this->sellerStateCode !== '') {
                $pos = $this->sellerStateCode;
            }

            if ($pos === '') {
                $skippedNoPos++;
                continue;
            }

            // ── GST Rate — FILE SE PADHO ──
            $rate = 0.0;
            if ($hasRate) {
                $r = $this->num($row[$cm['gst_rate']] ?? null);
                if ($r > 0) {
                    // Decimal form check: 0.18 = 18%
                    $rate = $r < 1 ? round($r * 100, 2) : round($r, 2);
                }
            }

            // Rate nahi mila → tax amounts se back-calculate
            if ($rate == 0 && abs($txval) > 0) {
                $ig = $hasIgst ? abs($this->num($row[$cm['igst_amount']] ?? null)) : 0;
                $cg = $hasCgst ? abs($this->num($row[$cm['cgst_amount']] ?? null)) : 0;
                $sg = $hasSgst ? abs($this->num($row[$cm['sgst_amount']] ?? null)) : 0;
                $tt = $ig + $cg + $sg;
                if ($tt > 0) {
                    $rate = round($tt / abs($txval) * 100, 2);
                }
            }

            // ── Tax Amounts — FILE SE PADHO ──
            // null = column absent → BaseParser calculate karega rate se
            // 0.0  = column present but value 0 → as-is pass karo
            $igst = $hasIgst ? $this->numOrNull($row[$cm['igst_amount']] ?? null) : null;
            $cgst = $hasCgst ? $this->numOrNull($row[$cm['cgst_amount']] ?? null) : null;
            $sgst = $hasSgst ? $this->numOrNull($row[$cm['sgst_amount']] ?? null) : null;

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

            // ── Quantity — SIRF FILE SE, KOI DEFAULT NAHI ──
            // Column absent → null (GST Portal ke liye quantity mandatory nahi)
            // Column present → actual file value, abs() kyunki returns mein negative ho sakta hai
            $qty = null;
            if ($hasQty) {
                $qv  = $this->num($row[$cm['quantity']] ?? null);
                $qty = $qv != 0 ? (int)abs($qv) : null;
            }

            // ── HSN Code ──
            $hsn = null;
            if ($hasHsn) {
                $h   = trim((string)($row[$cm['hsn_code']] ?? ''));
                $hsn = $h !== '' ? $h : null;
            }

            // ── Buyer GSTIN (B2B) ──
            $buyerGstin = null;
            if ($hasBuyerGstin) {
                $bg         = trim((string)($row[$cm['buyer_gstin']] ?? ''));
                $buyerGstin = $bg !== '' ? strtoupper($bg) : null;
            }

            // ── Transaction build karo ──
            $t = $this->makeTransaction(
                pos:          $pos,
                rate:         $rate,
                taxableValue: $txval,
                igstAmt:      $igst,
                cgstAmt:      $cgst,
                sgstAmt:      $sgst,
                invoiceNo:    $invoiceNo,
                invoiceDate:  $invoiceDate,
                qty:          $qty,        // ?int — null if column absent
                hsn:          $hsn,
                buyerGstin:   $buyerGstin,
            );

            if (!empty($t)) $transactions[] = $t;
        }

        // Summary warnings
        if ($skippedZero > 0) {
            $this->warn("'{$label}': {$skippedZero} rows skipped (taxable_value = 0).");
        }
        if ($skippedNoPos > 0) {
            $this->warn("'{$label}': {$skippedNoPos} rows skipped (state could not be resolved).");
        }

        return $transactions;
    }

    // ════════════════════════════════════════════════════════════════════
    //  HEADER ROW FINDER
    // ════════════════════════════════════════════════════════════════════

    private function findHeaderRowAjio(array $rows): int
    {
        // Group-based detection — 2+ groups match = header row
        $groups = [
            ['taxable amount', 'taxable value', 'base amount', 'taxable_value', 'net amount'],
            ['state', 'place of supply', 'delivery state', 'customer state', 'order state'],
            ['igst', 'cgst', 'sgst', 'gst rate', 'tax rate', 'rate'],
            ['order id', 'invoice number', 'invoice no', 'order number', 'shipment id'],
            ['hsn', 'quantity', 'qty', 'units'],
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

    private function buildAjioColMap(array $headers): array
    {
        $map        = [];
        $normalized = array_map(fn($h) => strtolower(trim((string)$h)), $headers);

        foreach (self::COLS as $field => $aliases) {
            $map[$field] = -1;

            // Pass 1: exact match
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
     * null  → column absent / empty cell  (BaseParser will calculate tax)
     * 0.0   → column present, value is 0  (use as-is)
     * float → actual amount from file
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