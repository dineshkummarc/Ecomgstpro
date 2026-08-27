<?php

namespace App\Parsers;

use App\Parsers\BaseParser;

/**
 * ══════════════════════════════════════════════════════════════════════
 *  MEESHO — TCS SALES SHEET PARSER (dedicated, Sales only)
 * ══════════════════════════════════════════════════════════════════════
 *  This parser has exactly one job: parse whatever file lands in the
 *  "TCS Sales Sheet" slot.
 *
 *  ⚠️ FIX (slot-mismatch bug): previously, MeeshoParser decided for
 *  itself whether a file was sales or return by checking columns (looking
 *  for the cancel_return_date column). This meant that if a user
 *  accidentally dropped a Return file into the "Sales" slot, it would
 *  quietly get treated as "sales" too — the totals would come out wrong.
 *  This parser is now STRICT: if the file has a cancel_return_date
 *  column, it clearly refuses ("wrong_slot") so the upload controller can
 *  tell the user the correct slot right away.
 *
 *  ⚠️ FIX (HSN / invoice_number pollution): this parser no longer sets
 *  `invoice_number` and `hsn_code` on the transaction. Only `order_number`
 *  (sub_order_num) is set as a separate field. The real invoice_number
 *  and HSN come only from the Tax Invoice file (MeeshoTaxInvoiceParser),
 *  merged later via order_number
 *  (see MeeshoParserService::mergeTaxInvoiceData()).
 *
 *  ⚠️⚠️ NEW FIX (forced-positive taxable_value bug — REMOVED):
 *  This parser previously did `$txval = abs($txval);` unconditionally on
 *  every row, on the assumption that "sales = always positive". In
 *  practice, Meesho's own Sales-sheet export sometimes contains rows
 *  whose `total_taxable_sale_value` is genuinely NEGATIVE (adjustment /
 *  correction rows that Meesho places inside the Sales sheet itself,
 *  separate from the dedicated Return sheet). Forcing abs() on those
 *  rows silently flipped a real -X into +X — a swing of 2X per affected
 *  row — which inflated the grand total (observed real-world case: 126
 *  such rows totalling -₹3,190.99 got flipped, inflating the batch total
 *  by ~₹6,381.98).
 *
 *  The fix: taxable_value is now read and stored EXACTLY as the file has
 *  it — sign included. Whatever number is really in the
 *  `total_taxable_sale_value` column of the Sales file is what gets used
 *  downstream, with no forced sign correction. Only a true zero value is
 *  still skipped (a 0 taxable-value row carries no tax information).
 *
 *  NOTE: order_number here is read as-is from whichever column
 *  config/gstr/meesho.php's 'order_number' aliases resolve to (e.g. Sub
 *  Order No). It does not need to match the Tax Invoice file's raw text
 *  exactly — MeeshoParserService::mergeTaxInvoiceData() normalizes both
 *  sides via BaseParser::normalizeOrderKey() before comparing, so minor
 *  formatting differences between the two files don't break the match.
 * ══════════════════════════════════════════════════════════════════════
 */
class MeeshoSalesParser extends BaseParser
{
    protected string $platform = 'meesho';

    public function __construct(string $sellerStateCode = '', string $clientGstin = '')
    {
        parent::__construct($sellerStateCode, $clientGstin);

        if (empty($this->cfg['columns'])) {
            $this->cfg = array_replace_recursive([
                'sheets' => ['sales' => ['sales', 'tcs sales', 'tcs_sales', 'sheet1']],
                'return_indicator_column' => 'cancel_return_date',
                'columns' => [
                    'gstin'              => ['gstin'],
                    'order_number'       => [
                        'sub_order_num',
                        'suborder_num',
                        'sub_order_no',
                        'sub order no',
                        'sub order num',
                        'sub order number',
                        'suborder no',
                        'suborder number',
                        'order_number',
                        'order number',
                        'order no',
                        'order id',
                    ],
                    'taxable_value'      => ['total_taxable_sale_value', 'taxable value'],
                    'gst_rate'           => ['gst_rate', 'gst rate', 'tax rate', 'rate'],
                    'place_of_supply'    => ['end_customer_state_new', 'customer state', 'place of supply'],
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

        $targetRows = $this->findBestSheet($sheets);
        if (empty($targetRows)) {
            $this->warn("No data rows found in any sheet");
            return ['transactions' => [], 'warnings' => $this->warnings];
        }

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

        // ── STRICT SLOT CHECK ────────────────────────────────────────
        // If a cancel_return_date column is found, this is a Return file
        // that was mistakenly put into the "Sales" slot.
        $indicator = strtolower($this->cfg['return_indicator_column'] ?? 'cancel_return_date');
        foreach ($headers as $h) {
            if (str_contains(strtolower((string)$h), $indicator)) {
                return [
                    'success'    => false,
                    'wrong_slot' => true,
                    'error'      => "This is the 'TCS Sales Sheet' slot, but the file has a '{$indicator}' column — it looks like a Return file. Please upload it in the 'TCS Sales Return Sheet' slot instead.",
                    'transactions' => [],
                    'warnings'     => $this->warnings,
                ];
            }
        }

        if (($cm['taxable_value'] ?? -1) < 0) {
            $this->warn("'total_taxable_sale_value' column not found. Columns detected: " . implode(', ', array_filter($headers)));
            return ['transactions' => [], 'warnings' => $this->warnings];
        }
        if (($cm['place_of_supply'] ?? -1) < 0) {
            $this->warn("'end_customer_state_new' column not found");
            return ['transactions' => [], 'warnings' => $this->warnings];
        }

        $transactions = [];
        for ($i = $hIdx + 1; $i < count($targetRows); $i++) {
            $row = $targetRows[$i];
            if ($this->rowIsEmpty($row, 4)) continue;

            $gstin = trim((string)($this->val($row, $cm, 'gstin') ?? ''));
            if ($gstin === '') continue;

            $txval = $this->num($this->val($row, $cm, 'taxable_value'));
            if ($txval == 0) continue;
            // ⚠️ FIX: DO NOT force abs() here. The Sales file's
            // total_taxable_sale_value is used exactly as it appears in
            // the file — including its real sign. Some genuine Sales-sheet
            // rows carry a negative value (Meesho-side adjustments), and
            // flipping those to positive silently inflated totals.

            $rate = $this->num($this->val($row, $cm, 'gst_rate'));
            if ($rate < 0) continue;

            $posRaw = $this->val($row, $cm, 'place_of_supply');
            $pos    = $this->resolveStateCode($posRaw);
            if ($pos === '') {
                $this->warn("Row {$i}: Cannot resolve state '{$posRaw}'");
                continue;
            }

            $qty            = max(1, (int)$this->num($this->val($row, $cm, 'quantity') ?? 1));
            $orderNumberRaw = $this->val($row, $cm, 'order_number');

            $orderNumber = self::normalizeOrderKey($orderNumberRaw);
            if ($orderNumber === '' && trim((string)$orderNumberRaw) !== '') {
                $this->warn("Row {$i}: order_number normalized to empty from raw '{$orderNumberRaw}'");
            }

            // ⚠️ IMPORTANT: hsn_code and invoice_number are NOT set here.
            // They only come from the Tax Invoice file merge.
            $t = $this->makeTransaction(
                pos:          $pos,
                rate:         $rate,
                taxableValue: $txval,
                invoiceNo:    null,
                invoiceDate:  $this->date($this->val($row, $cm, 'order_date')),
                qty:          $qty,
                hsn:          null,
            );

            if (!empty($t)) {
                $t['order_number'] = $orderNumber ?: null;
                $t['source_file']  = 'meesho_sales';
                $transactions[]    = $t;
            }
        }

        if (empty($transactions)) {
            $this->warn("Upload the Tax Invoice file to see the correct HSN/invoice_number — only the Sales sheet has been parsed so far.");
        }

        return [
            'transactions' => array_values(array_filter($transactions)),
            'file_type'    => 'meesho_sales',
            'warnings'     => $this->warnings,
        ];
    }

     private function findBestSheet(array $sheets): array
    {
        $aliases = $this->cfg['sheets']['sales'] ?? ['sales', 'sheet1'];
        foreach ($aliases as $alias) {
            foreach ($sheets as $name => $rows) {
                if (str_contains(strtolower($name), strtolower($alias))) return $rows;
            }
        }
        // ⚠️ No silent fallback: content-check instead of blindly grabbing
        // the first sheet, so a stray extra sheet in the workbook can't
        // get parsed as "sales" by accident.
        foreach ($sheets as $rows) {
            $headerCandidates = array_slice($rows, 0, 3);
            foreach ($headerCandidates as $r) {
                if (self::isMeeshoFile(array_map('strval', $r))) {
                    return $rows;
                }
            }
        }
        return [];
    }

    public static function isMeeshoFile(array $headers): bool
    {
        $s = strtolower(implode(',', $headers));
        return str_contains($s, 'total_taxable_sale_value')
            && str_contains($s, 'end_customer_state_new')
            && str_contains($s, 'sub_order_num');
    }
}
