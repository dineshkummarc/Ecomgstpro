<?php

namespace App\Parsers;

use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * ══════════════════════════════════════════════════════════════════════
 *  GST PORTAL PARSER — Official GSTR-1 Format Support
 * ══════════════════════════════════════════════════════════════════════
 *
 *  Supports:
 *  1. B2CS (B2C Summary) — intra + inter state
 *  2. B2CL (B2C Large invoices)
 *  3. B2B (invoices with GSTIN)
 *  4. HSN (HSN-wise summary)
 *
 *  Two formats supported:
 *  - FORMAT 1: Simple CSV (Type | Place | Rate | Taxable Value | Cess | Ecom GSTIN)
 *  - FORMAT 2: Gov Excel (Multi-sheet GSTR-1 with proper IGST/CGST/SGST columns)
 * ══════════════════════════════════════════════════════════════════════
 */
class GstPortalParser extends BaseParser
{
    protected string $platform = 'gstportal';

    // Hardcoded column aliases (from config/gstr/gstportal.php)
    private const COL_ALIASES = [
        'supply_type'      => ['type', 'supply type', 'transaction type'],
        'place_of_supply'  => ['place of supply', 'place_of_supply', 'pos', 'state'],
        'gst_rate'         => ['rate', 'gst rate', 'applicable % of tax rate', 'rate(%)', 'gst%', 'tax rate'],
        'applicable_pct'   => ['applicable % of tax rate', 'applicable %', 'tax applicable %'],
        'taxable_value'    => ['taxable value', 'taxable_value', 'taxable amount', 'aggregate taxable value'],
        'cess_amount'      => ['cess amount', 'cess', 'compensation cess'],
        'ecommerce_gstin'  => ['e-commerce gstin', 'ecommerce gstin', 'e commerce gstin', 'eco gstin'],
        
        // B2B columns
        'buyer_gstin'      => ['gstin/uin of recipient', 'gstin', 'buyer gstin', 'recipient gstin'],
        'invoice_number'   => ['invoice number', 'invoice no.', 'invoice no', 'invoice_number'],
        'invoice_date'     => ['invoice date', 'date of invoice', 'invoice dt'],
        'invoice_value'    => ['invoice value', 'total invoice value', 'bill amount'],
        
        // Tax columns
        'igst_amount'      => ['integrated tax amount', 'igst amount', 'igst'],
        'cgst_amount'      => ['central tax amount', 'cgst amount', 'cgst'],
        'sgst_amount'      => ['state/ut tax amount', 'sgst amount', 'sgst/utgst amount', 'sgst'],
    ];

    public function __construct(string $sellerStateCode = '', string $clientGstin = '')
    {
        parent::__construct($sellerStateCode, $clientGstin);

        // Config missing hone par bhi kaam kare
        if (empty($this->cfg['columns'])) {
            $this->cfg['columns'] = self::COL_ALIASES;
        } else {
            foreach (self::COL_ALIASES as $field => $aliases) {
                if (empty($this->cfg['columns'][$field])) {
                    $this->cfg['columns'][$field] = $aliases;
                }
            }
        }
    }

    public function canHandle(Spreadsheet $spreadsheet): bool
    {
        $names = array_map('strtolower', $spreadsheet->getSheetNames());
        
        // Detect by filename or sheet names
        $detect = ['gstr1', 'gstr-1', 'gst_return', 'gst return', 'b2cs', 'gov_excel', 'government', 'portal', 'offline_tool'];
        
        foreach ($names as $n) {
            foreach ($detect as $d) {
                if (str_contains($n, $d)) return true;
            }
        }
        
        // Detect by sheet pattern (7. B2CS, etc.)
        $sheetPatterns = ['7. b2cs', 'b2cs', 'b2cl', '7. b2cl', 'b2b', '6. b2b', 'hsn'];
        foreach ($names as $n) {
            foreach ($sheetPatterns as $p) {
                if (str_contains($n, $p)) return true;
            }
        }
        
        return false;
    }

    // ════════════════════════════════════════════════════════════════════
    //  MAIN PARSE — Auto-detect format
    // ════════════════════════════════════════════════════════════════════

    public function parse(array $sheets, string $sellerStateCode, string $period): array
    {
        $this->sellerStateCode = $sellerStateCode;
        $this->period          = $period;
        $this->warnings        = [];

        $sheetNamesLower = array_map('strtolower', array_keys($sheets));
        $sheetMap = [];
        
        foreach ($sheets as $name => $rows) {
            $sheetMap[strtolower(trim($name))] = ['name' => $name, 'rows' => $rows];
        }

        $transactions = [];

        // ── B2CS (B2C Summary) ──
        $b2csRows = $this->findSheetByPatterns($sheetMap, [
            '7. b2cs (others)', 'b2cs (others)', 'b2c(others)', '7b2cs', 'b2cs', 'b2c others',
        ]);
        
        if ($b2csRows) {
            $transactions = array_merge($transactions, $this->parseB2CS($b2csRows));
        }

        // ── B2CL (B2C Large) ──
        $b2clRows = $this->findSheetByPatterns($sheetMap, [
            '6. b2cl (large invoices)', 'b2cl', 'b2c large', '6b2cl',
        ]);
        
        if ($b2clRows) {
            $transactions = array_merge($transactions, $this->parseB2CL($b2clRows));
        }

        // ── B2B (with GSTIN) ──
        $b2bRows = $this->findSheetByPatterns($sheetMap, [
            '4a,4b,6b,6c. b2b invoices', 'b2b', 'b2b invoices',
        ]);
        
        if ($b2bRows) {
            $transactions = array_merge($transactions, $this->parseB2B($b2bRows));
        }

        // ── HSN (Summary) ──
        // HSN is typically just summary, not individual transactions
        // Optionally parse if needed

        if (empty($transactions) && !empty($sheets)) {
            $this->warn("GST Portal: No standard sheets found. Trying all sheets.");
            foreach ($sheets as $name => $rows) {
                $parsed = $this->parseGenericSheet($rows, $name);
                $transactions = array_merge($transactions, $parsed);
            }
        }

        return [
            'transactions' => array_values(array_filter($transactions)),
            'warnings'     => $this->warnings,
            'file_type'    => 'gstportal_gstr1',
        ];
    }

    // ════════════════════════════════════════════════════════════════════
    //  B2CS — B2C Summary (both INTRA and INTER)
    // ════════════════════════════════════════════════════════════════════

    private function parseB2CS(array $rows): array
    {
        if (empty($rows)) return [];

        // Header detection
        $hIdx = $this->findHeaderRow($rows, ['taxable', 'place'], 1);
        if ($hIdx < 0) {
            $this->warn("B2CS: Header row not found (looking for 'taxable' and 'place')");
            return [];
        }

        $headers = array_map('strval', $rows[$hIdx]);
        $cm = $this->buildColMap($headers);

        // Required columns
        if (($cm['taxable_value'] ?? -1) < 0) {
            $this->warn("B2CS: 'Taxable Value' column not found");
            return [];
        }
        if (($cm['place_of_supply'] ?? -1) < 0) {
            $this->warn("B2CS: 'Place of Supply' column not found");
            return [];
        }

        $transactions = [];
        
        for ($i = $hIdx + 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            
            if ($this->rowIsEmpty($row, 1)) continue;

            $txval = $this->num($row[$cm['taxable_value']] ?? null);
            if ($txval == 0) continue;

            // Place of Supply
            $posRaw = trim((string)($row[$cm['place_of_supply']] ?? ''));
            $pos = $this->resolveStateCode($posRaw);
            
            if ($pos === '') {
                $this->warn("B2CS row {$i}: Cannot resolve state '{$posRaw}'");
                continue;
            }

            // Rate — can be either single rate or separate IGST/CGST
            $gstRate = ($cm['gst_rate'] ?? -1) >= 0 ? $this->num($row[$cm['gst_rate']] ?? null) : 0;
            
            // Tax amounts
            $igstAmt = ($cm['igst_amount'] ?? -1) >= 0 ? $this->num($row[$cm['igst_amount']] ?? null) : 0;
            $cgstAmt = ($cm['cgst_amount'] ?? -1) >= 0 ? $this->num($row[$cm['cgst_amount']] ?? null) : 0;
            $sgstAmt = ($cm['sgst_amount'] ?? -1) >= 0 ? $this->num($row[$cm['sgst_amount']] ?? null) : 0;
            $cessAmt = ($cm['cess_amount'] ?? -1) >= 0 ? $this->num($row[$cm['cess_amount']] ?? null) : 0;

            // Determine if INTRA or INTER
            $isInter = $igstAmt > 0 || ($gstRate > 0 && $cgstAmt == 0 && $sgstAmt == 0);

            if ($isInter) {
                // INTER-STATE (IGST)
                $rate = $gstRate > 0 ? $gstRate : ($igstAmt > 0 ? round(abs($igstAmt) / abs($txval) * 100, 2) : 0);
                
                if ($igstAmt == 0 && $rate > 0) {
                    $igstAmt = round(abs($txval) * $rate / 100, 2);
                    if ($txval < 0) $igstAmt = -$igstAmt;
                }

                $t = $this->makeTransaction(
                    pos:          $pos,
                    rate:         $rate,
                    taxableValue: $txval,
                    igstAmt:      $igstAmt,
                    cgstAmt:      0.0,
                    sgstAmt:      0.0,
                    cessAmt:      $cessAmt,
                    ecomGstin:    ($cm['ecommerce_gstin'] ?? -1) >= 0 ? (string)($row[$cm['ecommerce_gstin']] ?? '') : null,
                );
            } else {
                // INTRA-STATE (CGST + SGST)
                $rate = $gstRate > 0 ? $gstRate : (($cgstAmt + $sgstAmt) > 0 ? round(($cgstAmt + $sgstAmt) / abs($txval) * 100, 2) : 0);
                
                if (($cgstAmt == 0 && $sgstAmt == 0) && $rate > 0) {
                    $half = round(abs($txval) * $rate / 200, 2);
                    $cgstAmt = $txval < 0 ? -$half : $half;
                    $sgstAmt = $cgstAmt;
                }

                $t = $this->makeTransaction(
                    pos:          $pos,
                    rate:         $rate,
                    taxableValue: $txval,
                    igstAmt:      0.0,
                    cgstAmt:      $cgstAmt,
                    sgstAmt:      $sgstAmt,
                    cessAmt:      $cessAmt,
                    ecomGstin:    ($cm['ecommerce_gstin'] ?? -1) >= 0 ? (string)($row[$cm['ecommerce_gstin']] ?? '') : null,
                );
            }

            if (!empty($t)) $transactions[] = $t;
        }

        return $transactions;
    }

    // ════════════════════════════════════════════════════════════════════
    //  B2CL — B2C Large Invoices (with invoice details)
    // ════════════════════════════════════════════════════════════════════

    private function parseB2CL(array $rows): array
    {
        if (empty($rows)) return [];

        $hIdx = $this->findHeaderRow($rows, ['invoice', 'taxable'], 1);
        if ($hIdx < 0) $hIdx = $this->findHeaderRow($rows, ['taxable'], 1);
        if ($hIdx < 0) {
            $this->warn("B2CL: Header row not found");
            return [];
        }

        $headers = array_map('strval', $rows[$hIdx]);
        $cm = $this->buildColMap($headers);

        if (($cm['taxable_value'] ?? -1) < 0) {
            $this->warn("B2CL: 'Taxable Value' column not found");
            return [];
        }

        $transactions = [];

        for ($i = $hIdx + 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            
            if ($this->rowIsEmpty($row, 1)) continue;

            $txval = $this->num($row[$cm['taxable_value']] ?? null);
            if ($txval == 0) continue;

            // Place of Supply — required for B2CL
            $posRaw = trim((string)($row[$cm['place_of_supply']] ?? ''));
            $pos = $posRaw ? $this->resolveStateCode($posRaw) : '';
            
            if ($pos === '') {
                $this->warn("B2CL row {$i}: Place of Supply not found");
                continue;
            }

            // Rate & Amounts
            $gstRate = ($cm['gst_rate'] ?? -1) >= 0 ? $this->num($row[$cm['gst_rate']] ?? null) : 0;
            $igstAmt = ($cm['igst_amount'] ?? -1) >= 0 ? $this->num($row[$cm['igst_amount']] ?? null) : 0;
            $cgstAmt = ($cm['cgst_amount'] ?? -1) >= 0 ? $this->num($row[$cm['cgst_amount']] ?? null) : 0;
            $sgstAmt = ($cm['sgst_amount'] ?? -1) >= 0 ? $this->num($row[$cm['sgst_amount']] ?? null) : 0;

            // Determine INTRA vs INTER
            $isInter = $igstAmt > 0 || ($gstRate > 0 && $cgstAmt == 0 && $sgstAmt == 0);

            if ($isInter) {
                $rate = $gstRate > 0 ? $gstRate : ($igstAmt > 0 ? round(abs($igstAmt) / abs($txval) * 100, 2) : 0);
                if ($igstAmt == 0 && $rate > 0) {
                    $igstAmt = round(abs($txval) * $rate / 100, 2);
                    if ($txval < 0) $igstAmt = -$igstAmt;
                }
                $cgstAmt = 0;
                $sgstAmt = 0;
            } else {
                $rate = $gstRate > 0 ? $gstRate : (($cgstAmt + $sgstAmt) > 0 ? round(($cgstAmt + $sgstAmt) / abs($txval) * 100, 2) : 0);
                if (($cgstAmt == 0 && $sgstAmt == 0) && $rate > 0) {
                    $half = round(abs($txval) * $rate / 200, 2);
                    $cgstAmt = $txval < 0 ? -$half : $half;
                    $sgstAmt = $cgstAmt;
                }
                $igstAmt = 0;
            }

            $t = $this->makeTransaction(
                pos:          $pos,
                rate:         $rate,
                taxableValue: $txval,
                igstAmt:      $igstAmt,
                cgstAmt:      $cgstAmt,
                sgstAmt:      $sgstAmt,
                invoiceNo:    ($cm['invoice_number'] ?? -1) >= 0 ? (string)($row[$cm['invoice_number']] ?? '') : null,
                invoiceDate:  ($cm['invoice_date'] ?? -1) >= 0 ? $this->date($row[$cm['invoice_date']] ?? null) : null,
                buyerGstin:   ($cm['buyer_gstin'] ?? -1) >= 0 ? (string)($row[$cm['buyer_gstin']] ?? '') : null,
            );

            if (!empty($t)) $transactions[] = $t;
        }

        return $transactions;
    }

    // ════════════════════════════════════════════════════════════════════
    //  B2B — Business-to-Business (with GSTIN/UIN)
    // ════════════════════════════════════════════════════════════════════

    private function parseB2B(array $rows): array
    {
        if (empty($rows)) return [];

        $hIdx = $this->findHeaderRow($rows, ['gstin', 'invoice', 'taxable'], 2);
        if ($hIdx < 0) $hIdx = $this->findHeaderRow($rows, ['gstin', 'taxable'], 1);
        if ($hIdx < 0) {
            $this->warn("B2B: Header row not found (looking for GSTIN/taxable)");
            return [];
        }

        $headers = array_map('strval', $rows[$hIdx]);
        $cm = $this->buildColMap($headers);

        if (($cm['taxable_value'] ?? -1) < 0) {
            $this->warn("B2B: 'Taxable Value' column not found");
            return [];
        }

        $transactions = [];

        for ($i = $hIdx + 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            
            if ($this->rowIsEmpty($row, 1)) continue;

            $txval = $this->num($row[$cm['taxable_value']] ?? null);
            if ($txval == 0) continue;

            // Buyer GSTIN (required for B2B)
            $buyerGstin = trim((string)($row[$cm['buyer_gstin']] ?? ''));
            if ($buyerGstin === '' || strlen($buyerGstin) < 2) {
                $this->warn("B2B row {$i}: Invalid or missing Buyer GSTIN");
                continue;
            }

            // Extract state from GSTIN (first 2 digits)
            $pos = str_pad((string)(int)substr($buyerGstin, 0, 2), 2, '0', STR_PAD_LEFT);
            if ($pos === '00') {
                $this->warn("B2B row {$i}: Cannot extract state from GSTIN '{$buyerGstin}'");
                continue;
            }

            // Rate & Amounts
            $gstRate = ($cm['gst_rate'] ?? -1) >= 0 ? $this->num($row[$cm['gst_rate']] ?? null) : 0;
            $igstAmt = ($cm['igst_amount'] ?? -1) >= 0 ? $this->num($row[$cm['igst_amount']] ?? null) : 0;
            $cgstAmt = ($cm['cgst_amount'] ?? -1) >= 0 ? $this->num($row[$cm['cgst_amount']] ?? null) : 0;
            $sgstAmt = ($cm['sgst_amount'] ?? -1) >= 0 ? $this->num($row[$cm['sgst_amount']] ?? null) : 0;

            // Determine INTRA vs INTER
            $isInter = $igstAmt > 0 || ($gstRate > 0 && $cgstAmt == 0 && $sgstAmt == 0);

            if ($isInter) {
                $rate = $gstRate > 0 ? $gstRate : ($igstAmt > 0 ? round(abs($igstAmt) / abs($txval) * 100, 2) : 0);
                if ($igstAmt == 0 && $rate > 0) {
                    $igstAmt = round(abs($txval) * $rate / 100, 2);
                    if ($txval < 0) $igstAmt = -$igstAmt;
                }
                $cgstAmt = 0;
                $sgstAmt = 0;
            } else {
                $rate = $gstRate > 0 ? $gstRate : (($cgstAmt + $sgstAmt) > 0 ? round(($cgstAmt + $sgstAmt) / abs($txval) * 100, 2) : 0);
                if (($cgstAmt == 0 && $sgstAmt == 0) && $rate > 0) {
                    $half = round(abs($txval) * $rate / 200, 2);
                    $cgstAmt = $txval < 0 ? -$half : $half;
                    $sgstAmt = $cgstAmt;
                }
                $igstAmt = 0;
            }

            $t = $this->makeTransaction(
                pos:          $pos,
                rate:         $rate,
                taxableValue: $txval,
                igstAmt:      $igstAmt,
                cgstAmt:      $cgstAmt,
                sgstAmt:      $sgstAmt,
                invoiceNo:    ($cm['invoice_number'] ?? -1) >= 0 ? (string)($row[$cm['invoice_number']] ?? '') : null,
                invoiceDate:  ($cm['invoice_date'] ?? -1) >= 0 ? $this->date($row[$cm['invoice_date']] ?? null) : null,
                buyerGstin:   $buyerGstin,
            );

            if (!empty($t)) $transactions[] = $t;
        }

        return $transactions;
    }

    // ════════════════════════════════════════════════════════════════════
    //  GENERIC SHEET PARSER — fallback jab sheet type unknown ho
    // ════════════════════════════════════════════════════════════════════

    private function parseGenericSheet(array $rows, string $sheetName): array
    {
        if (empty($rows)) return [];

        // Skip known non-data sheets
        $skipPatterns = ['read me', 'readme', 'instructions', 'guide', 'help', 'notes', 'coversheet', 'summary'];
        $nlower = strtolower($sheetName);
        
        foreach ($skipPatterns as $p) {
            if (str_contains($nlower, $p)) return [];
        }

        // Header detection
        $hIdx = $this->findHeaderRow($rows, ['taxable', 'place'], 1);
        if ($hIdx < 0) $hIdx = $this->findHeaderRow($rows, ['taxable'], 1);
        if ($hIdx < 0) return [];

        $headers = array_map('strval', $rows[$hIdx]);
        $cm = $this->buildColMap($headers);

        if (($cm['taxable_value'] ?? -1) < 0) {
            return [];
        }

        $transactions = [];

        for ($i = $hIdx + 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            
            if ($this->rowIsEmpty($row, 1)) continue;

            $txval = $this->num($row[$cm['taxable_value']] ?? null);
            if ($txval == 0) continue;

            // Try to get POS
            $posRaw = trim((string)($row[$cm['place_of_supply'] ?? -1] ?? ''));
            $pos = $posRaw ? $this->resolveStateCode($posRaw) : '';
            
            // If no POS, try from GSTIN
            if ($pos === '' && ($cm['buyer_gstin'] ?? -1) >= 0) {
                $gstin = trim((string)($row[$cm['buyer_gstin']] ?? ''));
                if (strlen($gstin) >= 2) {
                    $pos = str_pad((string)(int)substr($gstin, 0, 2), 2, '0', STR_PAD_LEFT);
                }
            }

            // If still no POS, use seller state
            if ($pos === '') {
                $pos = $this->sellerStateCode;
            }

            if ($pos === '') {
                continue;
            }

            // Rate & Amounts
            $gstRate = ($cm['gst_rate'] ?? -1) >= 0 ? $this->num($row[$cm['gst_rate']] ?? null) : 0;
            $igstAmt = ($cm['igst_amount'] ?? -1) >= 0 ? $this->num($row[$cm['igst_amount']] ?? null) : 0;
            $cgstAmt = ($cm['cgst_amount'] ?? -1) >= 0 ? $this->num($row[$cm['cgst_amount']] ?? null) : 0;
            $sgstAmt = ($cm['sgst_amount'] ?? -1) >= 0 ? $this->num($row[$cm['sgst_amount']] ?? null) : 0;

            $isInter = $igstAmt > 0 || ($gstRate > 0 && $cgstAmt == 0 && $sgstAmt == 0);

            if ($isInter) {
                $rate = $gstRate > 0 ? $gstRate : ($igstAmt > 0 ? round(abs($igstAmt) / abs($txval) * 100, 2) : 0);
                if ($igstAmt == 0 && $rate > 0) {
                    $igstAmt = round(abs($txval) * $rate / 100, 2);
                    if ($txval < 0) $igstAmt = -$igstAmt;
                }
                $cgstAmt = 0;
                $sgstAmt = 0;
            } else {
                $rate = $gstRate > 0 ? $gstRate : (($cgstAmt + $sgstAmt) > 0 ? round(($cgstAmt + $sgstAmt) / abs($txval) * 100, 2) : 0);
                if (($cgstAmt == 0 && $sgstAmt == 0) && $rate > 0) {
                    $half = round(abs($txval) * $rate / 200, 2);
                    $cgstAmt = $txval < 0 ? -$half : $half;
                    $sgstAmt = $cgstAmt;
                }
                $igstAmt = 0;
            }

            $t = $this->makeTransaction(
                pos:          $pos,
                rate:         $rate,
                taxableValue: $txval,
                igstAmt:      $igstAmt,
                cgstAmt:      $cgstAmt,
                sgstAmt:      $sgstAmt,
                invoiceNo:    ($cm['invoice_number'] ?? -1) >= 0 ? (string)($row[$cm['invoice_number']] ?? '') : null,
                invoiceDate:  ($cm['invoice_date'] ?? -1) >= 0 ? $this->date($row[$cm['invoice_date']] ?? null) : null,
                buyerGstin:   ($cm['buyer_gstin'] ?? -1) >= 0 ? (string)($row[$cm['buyer_gstin']] ?? '') : null,
            );

            if (!empty($t)) $transactions[] = $t;
        }

        return $transactions;
    }

    // ════════════════════════════════════════════════════════════════════
    //  HELPER METHODS
    // ════════════════════════════════════════════════════════════════════

    private function buildColMap(array $headers): array
    {
        $map = [];
        $normalized = array_map(fn($h) => strtolower(trim((string)$h)), $headers);

        foreach (self::COL_ALIASES as $field => $aliases) {
            $map[$field] = -1;

            // Exact match first
            foreach ($aliases as $alias) {
                $idx = array_search(strtolower($alias), $normalized, true);
                if ($idx !== false) {
                    $map[$field] = (int)$idx;
                    break;
                }
            }

            // Contains match
            if ($map[$field] < 0) {
                foreach ($aliases as $alias) {
                    $al = strtolower($alias);
                    foreach ($normalized as $i => $h) {
                        if ($h !== '' && (str_contains($h, $al) || str_contains($al, $h))) {
                            $map[$field] = $i;
                            break 2;
                        }
                    }
                }
            }
        }

        return $map;
    }

    private function findSheetByPatterns(array $sheetMap, array $patterns): ?array
    {
        foreach ($patterns as $pattern) {
            $p = strtolower(trim($pattern));
            
            // Exact match
            if (isset($sheetMap[$p])) {
                return $sheetMap[$p]['rows'];
            }

            // Contains match
            foreach ($sheetMap as $key => $data) {
                if (str_contains($key, $p)) {
                    return $data['rows'];
                }
            }
        }

        return null;
    }
}