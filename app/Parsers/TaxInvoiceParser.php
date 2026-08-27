<?php

namespace App\Parsers;

/**
 * ══════════════════════════════════════════════════════════════════════
 *  TAX INVOICE PARSER  (Meesho "Tax Invoice" optional upload slot)
 * ══════════════════════════════════════════════════════════════════════
 *
 *  This file is NOT a sales/return sheet. It has NO taxable value, NO
 *  GST rate, NO quantity, NO place of supply — those always come from
 *  the Sales/Return sheet via MeeshoParser. This file only supplies the
 *  REAL invoice number (and HSN) per sub-order.
 *
 *  Expected columns (as seen in the actual Meesho export):
 *      Type | Order Date | Suborder No. | Product Description | HSN | Invoice No.
 *
 *  'Type' is either "INVOICE" or "CREDIT NOTE" — INVOICE rows belong to
 *  the sales-side transaction, CREDIT NOTE rows belong to the
 *  return-side transaction for the same sub-order.
 *
 *  IMPORTANT: MeeshoParser currently stores the sub_order_num into the
 *  transaction's `invoice_number` field as a PLACEHOLDER (see
 *  MeeshoParser::parse() -> makeTransaction(invoiceNo: sub_order_num)).
 *  So the merge key here is: transaction['invoice_number'] (= sub_order_num)
 *  ↔ this parser's lookup[sub_order_num]. After merging, invoice_number
 *  is overwritten with the REAL invoice number from this file.
 * ══════════════════════════════════════════════════════════════════════
 */
class TaxInvoiceParser extends BaseParser
{
    protected string $platform = 'meesho_tax_invoice';

    public function __construct(string $sellerStateCode = '', string $clientGstin = '')
    {
        parent::__construct($sellerStateCode, $clientGstin);

        // config/gstr/meesho_tax_invoice.php is optional — fallback map
        // used if it doesn't exist, so this parser never hard-crashes.
        if (empty($this->cfg['columns'])) {
            $this->cfg['columns'] = [
                'type'           => ['type'],
                'order_date'     => ['order date', 'order_date'],
                'order_number'   => ['suborder no.', 'suborder no', 'sub_order_num', 'sub order no'],
                'description'    => ['product description', 'description'],
                'hsn_code'       => ['hsn', 'hsn_code'],
                'invoice_number' => ['invoice no.', 'invoice no', 'invoice_number'],
            ];
        }
    }

    /**
     * Note the 4th optional $fileRole param — added purely so the
     * Controller can call parse($sheets, $sellerStateCode, $period, $fileRole)
     * the same way it does for every other parser. This parser doesn't
     * need to branch on it internally.
     *
     * Returns:
     *   'transactions' => []      (always empty — this file produces no
     *                               ledger rows of its own)
     *   'lookup'       => [ sub_order_num => ['INVOICE' => [...], 'CREDIT NOTE' => [...]] ]
     *   'file_type'    => 'meesho_tax_invoice'
     *   'warnings'     => [...]
     */
    public function parse(array $sheets, string $sellerStateCode, string $period, string $fileRole = 'tax_invoice'): array
    {
        $this->sellerStateCode = $sellerStateCode;
        $this->period          = $period;
        $this->warnings        = [];
        $lookup                = [];

        $targetRows = $this->findBestSheet($sheets);

        if (empty($targetRows)) {
            $this->warn("Tax Invoice: no data rows found in any sheet");
            return ['transactions' => [], 'lookup' => [], 'file_type' => 'meesho_tax_invoice', 'warnings' => $this->warnings];
        }

        $hIdx = $this->findHeaderRow($targetRows, ['suborder no', 'invoice no', 'hsn'], 2);
        if ($hIdx < 0) {
            $hIdx = $this->findHeaderRow($targetRows, ['suborder', 'invoice'], 2);
        }
        if ($hIdx < 0) {
            $this->warn("Tax Invoice: header row not found. Expected columns: Type, Order Date, Suborder No., Product Description, HSN, Invoice No.");
            return ['transactions' => [], 'lookup' => [], 'file_type' => 'meesho_tax_invoice', 'warnings' => $this->warnings];
        }

        $headers = array_map('strval', $targetRows[$hIdx]);
        $cm      = $this->buildColMap($headers);

        if (($cm['order_number'] ?? -1) < 0) {
            $this->warn("Tax Invoice: 'Suborder No.' column not found. Columns detected: " . implode(', ', array_filter($headers)));
            return ['transactions' => [], 'lookup' => [], 'file_type' => 'meesho_tax_invoice', 'warnings' => $this->warnings];
        }
        if (($cm['invoice_number'] ?? -1) < 0) {
            $this->warn("Tax Invoice: 'Invoice No.' column not found. Columns detected: " . implode(', ', array_filter($headers)));
            return ['transactions' => [], 'lookup' => [], 'file_type' => 'meesho_tax_invoice', 'warnings' => $this->warnings];
        }

        for ($i = $hIdx + 1; $i < count($targetRows); $i++) {
            $row = $targetRows[$i];
            if ($this->rowIsEmpty($row, 3)) continue;

            $orderNum = trim((string)($this->val($row, $cm, 'order_number') ?? ''));
            if ($orderNum === '') continue;

            $invoiceNo = $this->val($row, $cm, 'invoice_number');
            $invoiceNo = ($invoiceNo !== null && $invoiceNo !== '') ? trim((string)$invoiceNo) : null;
            if ($invoiceNo === null) continue;

            $type = strtoupper(trim((string)($this->val($row, $cm, 'type') ?? 'INVOICE')));
            $hsn  = $this->val($row, $cm, 'hsn_code');
            $date = $this->date($this->val($row, $cm, 'order_date'));

            // A sub-order can appear twice: once as INVOICE, once as
            // CREDIT NOTE (if later returned) — keep both.
            $lookup[$orderNum][$type] = [
                'invoice_number' => $invoiceNo,
                'hsn_code'       => ($hsn !== null && $hsn !== '') ? (string)$hsn : null,
                'order_date'     => $date,
            ];
        }

        if (empty($lookup)) {
            $this->warn("Tax Invoice: file parsed but no usable Invoice No. rows found.");
        }

        return [
            'transactions' => [],           // this file never produces its own ledger rows
            'lookup'       => $lookup,
            'file_type'    => 'meesho_tax_invoice',
            'warnings'     => $this->warnings,
        ];
    }

    /**
     * Merges real invoice_number/hsn_code into already-parsed Meesho
     * transactions. ONLY these two fields are ever touched — quantity,
     * taxable_value, gst_rate, place_of_supply are never read from this
     * file and are left completely untouched.
     *
     * Match key: $t['invoice_number'] as currently stored by MeeshoParser
     * equals the sub_order_num (placeholder). Sign of taxable_value
     * decides whether to use the INVOICE or CREDIT NOTE entry.
     */
    public static function mergeInvoiceNumbers(array $transactions, array $lookup): array
    {
        foreach ($transactions as &$t) {
            $key = (string)($t['invoice_number'] ?? '');
            if ($key === '' || !isset($lookup[$key])) continue;

            $isReturn = isset($t['taxable_value']) && (float)$t['taxable_value'] < 0;
            $wantType = $isReturn ? 'CREDIT NOTE' : 'INVOICE';

            $entry = $lookup[$key][$wantType]
                  ?? $lookup[$key]['INVOICE']
                  ?? $lookup[$key]['CREDIT NOTE']
                  ?? null;

            if ($entry === null) continue;

            if (!empty($entry['invoice_number'])) {
                $t['invoice_number'] = $entry['invoice_number'];
            }
            if (!empty($entry['hsn_code']) && empty($t['hsn_code'])) {
                $t['hsn_code'] = $entry['hsn_code'];
            }
            // quantity / taxable_value / gst_rate / place_of_supply: untouched
        }
        unset($t);

        return $transactions;
    }

    // ────────────────────────────────────────────────────────────────────
    //  Helpers
    // ────────────────────────────────────────────────────────────────────

    private function findBestSheet(array $sheets): array
    {
        $aliases = $this->cfg['sheets']['tax_invoice'] ?? ['invoice_info', 'invoice info', 'sheet1'];
        foreach ($aliases as $alias) {
            foreach ($sheets as $name => $rows) {
                if (str_contains(strtolower($name), strtolower($alias))) {
                    return $rows;
                }
            }
        }
        return array_values($sheets)[0] ?? [];
    }

    /**
     * Static check for ParserFactory-style auto-detection, mirroring
     * MeeshoParser::isMeeshoFile().
     */
    public static function isTaxInvoiceFile(array $headers): bool
    {
        $headerStr = strtolower(implode(',', $headers));
        return str_contains($headerStr, 'suborder no')
            && str_contains($headerStr, 'invoice no')
            && !str_contains($headerStr, 'total_taxable_sale_value');
    }
}