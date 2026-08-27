<?php

namespace App\Parsers;

/**
 * ══════════════════════════════════════════════════════════════════════
 *  MEESHO PARSER (legacy — superseded by MeeshoSalesParser /
 *  MeeshoReturnParser / MeeshoTaxInvoiceParser + MeeshoParserService)
 * ══════════════════════════════════════════════════════════════════════
 *
 *  Processes TWO files (called separately, merged by upload controller):
 *
 *  Sales file:  rows with positive taxable values
 *  Return file: rows with NEGATIVE taxable values (auto-detected via
 *               presence of 'cancel_return_date' column)
 *
 *  Detection: isMeeshoFile() checks for the 3 strong indicator columns:
 *    total_taxable_sale_value + end_customer_state_new + sub_order_num
 *
 *  NOTE: GstrUploadController::dispatchParser() no longer routes Meesho
 *  uploads to this class — it uses the dedicated MeeshoSalesParser /
 *  MeeshoReturnParser / MeeshoTaxInvoiceParser instead, so that
 *  invoice_number/hsn_code only ever come from the Tax Invoice file.
 *  This class is kept only as a fallback/reference.
 * ══════════════════════════════════════════════════════════════════════
 */
class MeeshoParser extends BaseParser
{
    // ✅ FIX: platform property is now properly declared + initialized.
    // Previously this was missing, so BaseParser::__construct(), while
    // checking `if ($this->platform)`, threw a PHP fatal \Error
    // ("Typed property must not be accessed before initialization") that
    // wasn't caught by the \Exception catch block -> the browser got a
    // Laravel HTML error page instead of JSON.
    protected string $platform = 'meesho';

    public function __construct(string $sellerStateCode = '', string $clientGstin = '')
    {
        parent::__construct($sellerStateCode, $clientGstin);

        // ✅ Safety net: even if config/gstr/meesho.php fails to load
        // (missing/wrong path), the parser still won't crash — a built-in
        // fallback column map is used instead.
        if (empty($this->cfg['columns'])) {
            $this->warn("config/gstr/meesho.php not found or empty — using built-in fallback column map. Please add the config file for full alias support.");
            $this->cfg = array_replace_recursive([
                'sheets' => ['sales' => ['sales', 'sheet1']],
                'return_indicator_column' => 'cancel_return_date',
                'columns' => [
                    'gstin'              => ['gstin'],
                    'order_number'       => ['sub_order_num', 'order number', 'order id'],
                    'taxable_value'      => ['total_taxable_sale_value', 'taxable value'],
                    'gst_rate'           => ['gst_rate', 'gst rate', 'tax rate', 'rate'],
                    'place_of_supply'    => ['end_customer_state_new', 'customer state', 'place of supply'],
                    'hsn_code'           => ['hsn_code', 'hsn'],
                    'quantity'           => ['quantity', 'qty'],
                    'order_date'         => ['order_date', 'order date'],
                    'cancel_return_date' => ['cancel_return_date'],
                ],
            ], $this->cfg ?? []);
        }
    }

    public function parse(array $sheets, string $sellerStateCode, string $period): array
    {
        $this->sellerStateCode = $sellerStateCode;
        $this->period          = $period;
        $this->warnings        = [];
        $transactions          = [];

        // Find the correct sheet (prefer 'sales', fallback to first)
        $targetRows = $this->findBestSheet($sheets);

        if (empty($targetRows)) {
            $this->warn("No data rows found in any sheet");
            return ['transactions' => [], 'warnings' => $this->warnings];
        }

        // Find header row
        $hIdx = $this->findHeaderRow($targetRows, [
            'total_taxable_sale_value', 'end_customer_state_new', 'sub_order_num'
        ], 2);

        if ($hIdx < 0) {
            $hIdx = $this->findHeaderRow($targetRows, ['taxable', 'state', 'gst_rate'], 2);
        }

        if ($hIdx < 0) {
            $this->warn("Header row not found. Expected columns: gstin, sub_order_num, gst_rate, total_taxable_sale_value, end_customer_state_new");
            return ['transactions' => [], 'warnings' => $this->warnings];
        }

        $headers = array_map('strval', $targetRows[$hIdx]);
        $cm      = $this->buildColMap($headers);

        // Validate required columns (?? -1 so a missing/empty config never crashes)
        if (($cm['taxable_value'] ?? -1) < 0) {
            $this->warn("'total_taxable_sale_value' column not found (check config/gstr/meesho.php exists). Columns detected: " . implode(', ', array_filter($headers)));
            return ['transactions' => [], 'warnings' => $this->warnings];
        }
        if (($cm['place_of_supply'] ?? -1) < 0) {
            $this->warn("'end_customer_state_new' column not found (check config/gstr/meesho.php exists)");
            return ['transactions' => [], 'warnings' => $this->warnings];
        }

        // Detect if this is a RETURN file
        $isReturn = $this->detectIsReturn($headers);

        // Parse rows
        for ($i = $hIdx + 1; $i < count($targetRows); $i++) {
            $row = $targetRows[$i];
            if ($this->rowIsEmpty($row, 4)) continue;

            // Skip rows without GSTIN (footer/summary rows)
            $gstin = trim((string)($this->val($row, $cm, 'gstin') ?? ''));
            if ($gstin === '') continue;

            $txval = $this->num($this->val($row, $cm, 'taxable_value'));
            if ($txval == 0) continue;

            // Returns: always negative
            if ($isReturn) $txval = -abs($txval);

            $rate = $this->num($this->val($row, $cm, 'gst_rate'));
            if ($rate < 0) continue; // 0% GST is valid

            $posRaw = $this->val($row, $cm, 'place_of_supply');
            $pos    = $this->resolveStateCode($posRaw);
            if ($pos === '') {
                $this->warn("Row {$i}: Cannot resolve state '{$posRaw}'");
                continue;
            }

            // Quantity: the actual column value is used, 1 is only used
            // as a default when the column itself is missing/empty
            // (fallback safety).
            $qty = max(1, (int)$this->num($this->val($row, $cm, 'quantity') ?? 1));
            $hsn = $this->val($row, $cm, 'hsn_code');

            $t = $this->makeTransaction(
                pos:         $pos,
                rate:        $rate,
                taxableValue:$txval,
                invoiceNo:   $this->val($row, $cm, 'order_number') ? (string)$this->val($row, $cm, 'order_number') : null,
                invoiceDate: $this->date($this->val($row, $cm, 'order_date')),
                qty:         $qty,
                hsn:         $hsn ? (string)$hsn : null,
            );

            if (!empty($t)) $transactions[] = $t;
        }

        return [
            'transactions' => array_values(array_filter($transactions)),
            'file_type'    => $isReturn ? 'return' : 'sales',
            'warnings'     => $this->warnings,
        ];
    }

    // ────────────────────────────────────────────────────────────────────
    //  Helpers
    // ────────────────────────────────────────────────────────────────────

    private function findBestSheet(array $sheets): array
    {
        $salesAliases = $this->cfg['sheets']['sales'] ?? ['sales', 'sheet1'];
        foreach ($salesAliases as $alias) {
            foreach ($sheets as $name => $rows) {
                if (str_contains(strtolower($name), strtolower($alias))) {
                    return $rows;
                }
            }
        }
        // Fallback: first sheet
        return array_values($sheets)[0] ?? [];
    }

    /**
     * A file is a RETURN file if it has 'cancel_return_date' column in headers
     */
    public function detectIsReturn(array $headers): bool
    {
        $indicator = strtolower($this->cfg['return_indicator_column'] ?? 'cancel_return_date');
        foreach ($headers as $h) {
            if (str_contains(strtolower((string)$h), $indicator)) return true;
        }
        return false;
    }

    /**
     * Static check: does this file look like a Meesho TCS file?
     * Used by ParserFactory for detection.
     */
    public static function isMeeshoFile(array $headers): bool
    {
        $headerStr = strtolower(implode(',', $headers));
        return str_contains($headerStr, 'total_taxable_sale_value')
            && str_contains($headerStr, 'end_customer_state_new')
            && str_contains($headerStr, 'sub_order_num');
    }
}
