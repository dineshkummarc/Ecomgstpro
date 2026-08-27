<?php

namespace App\Parsers;

use App\Parsers\BaseParser;

/**
 * ══════════════════════════════════════════════════════════════════════
 *  MEESHO — TCS SALES RETURN SHEET PARSER (dedicated, Return only)
 * ══════════════════════════════════════════════════════════════════════
 *  ⚠️ STRICT SLOT CHECK: whatever file lands in this slot MUST have a
 *  'cancel_return_date' column — otherwise it's a Sales file that was
 *  dropped into the Return slot by mistake, and it is rejected outright
 *  (never silently negated and processed as if it were a return).
 *
 *  Taxable value is always stored NEGATIVE (a return = a negative sale).
 *
 *  hsn_code / invoice_number are NOT set here either — they only come
 *  from the Tax Invoice file (matched via order_number).
 *
 *  NOTE: order_number here is read as-is from whichever column
 *  config/gstr/meesho.php's 'order_number' aliases resolve to (e.g. Sub
 *  Order No). It does not need to match the Tax Invoice file's raw text
 *  exactly — MeeshoParserService::mergeTaxInvoiceData() normalizes both
 *  sides via BaseParser::normalizeOrderKey() before comparing, so minor
 *  formatting differences between the two files don't break the match.
 * ══════════════════════════════════════════════════════════════════════
 */
class MeeshoReturnParser extends BaseParser
{
    protected string $platform = 'meesho';

    public function __construct(string $sellerStateCode = '', string $clientGstin = '')
    {
        parent::__construct($sellerStateCode, $clientGstin);

        if (empty($this->cfg['columns'])) {
            $this->cfg = array_replace_recursive([
                'sheets' => ['return' => ['return', 'tcs return', 'tcs_return', 'returns', 'cancel', 'refund']],
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
                    'igst_amount'        => ['igst_paid', 'igst amount'],
                    'cgst_amount'        => ['cgst_paid', 'cgst amount'],
                    'sgst_amount'        => ['sgst_paid', 'sgst amount'],
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
            $this->warn("Header row not found. Expected columns: gstin, sub_order_num, gst_rate, total_taxable_sale_value, end_customer_state_new, cancel_return_date");
            return ['transactions' => [], 'warnings' => $this->warnings];
        }

        $headers = array_map('strval', $targetRows[$hIdx]);
        $cm      = $this->buildColMap($headers);

        // ── STRICT SLOT CHECK ────────────────────────────────────────
        $indicator = strtolower($this->cfg['return_indicator_column'] ?? 'cancel_return_date');
        $hasIndicator = false;
        foreach ($headers as $h) {
            if (str_contains(strtolower((string)$h), $indicator)) { $hasIndicator = true; break; }
        }
        if (!$hasIndicator) {
            return [
                'success'    => false,
                'wrong_slot' => true,
                'error'      => "This is the 'TCS Sales Return Sheet' slot, but the file has no '{$indicator}' column — it looks like a Sales file. Please upload it in the 'TCS Sales Sheet' slot instead.",
                'transactions' => [],
                'warnings'     => $this->warnings,
            ];
        }

        if (($cm['taxable_value'] ?? -1) < 0) {
            $this->warn("'total_taxable_sale_value' column not found");
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
            if ($txval == 0) continue; // Skip zero-value rows
            // Return values must always be negative.
            $txval = -abs($txval);

            $rate = $this->num($this->val($row, $cm, 'gst_rate'));
            if ($rate < 0) continue;

            $posRaw = $this->val($row, $cm, 'place_of_supply');
            $pos    = $this->resolveStateCode($posRaw);
            if ($pos === '') {
                $this->warn("Row {$i}: Cannot resolve state '{$posRaw}'");
                continue;
            }

            $qty         = max(1, (int)$this->num($this->val($row, $cm, 'quantity') ?? 1));

            $orderNumberRaw = $this->val($row, $cm, 'order_number');
            $orderNumber    = self::normalizeOrderKey($orderNumberRaw);
            if ($orderNumber === '' && trim((string)$orderNumberRaw) !== '') {
                $this->warn("Row {$i}: order_number normalized to empty from raw '{$orderNumberRaw}'");
            }

            // ── FIX: Read tax amounts directly from file if available ──
            // Read as-is from the file; makeTransaction will handle the sign.
            $igst = ($cm['igst_amount'] ?? -1) >= 0 ? $this->num($this->val($row, $cm, 'igst_amount')) : null;
            $cgst = ($cm['cgst_amount'] ?? -1) >= 0 ? $this->num($this->val($row, $cm, 'cgst_amount')) : null;
            $sgst = ($cm['sgst_amount'] ?? -1) >= 0 ? $this->num($this->val($row, $cm, 'sgst_amount')) : null;

            $t = $this->makeTransaction(
                pos:          $pos,
                rate:         $rate,
                taxableValue: $txval,
                igstAmt:      $igst,
                cgstAmt:      $cgst,
                sgstAmt:      $sgst,
                invoiceNo:    null,
                invoiceDate:  $this->date($this->val($row, $cm, 'order_date')),
                qty:          $qty,
                hsn:          null,
            );

            if (!empty($t)) {
                $t['order_number'] = $orderNumber ?: null;
                $t['source_file']  = 'meesho_return';
                $transactions[]    = $t;
            }
        }

        return [
            'transactions' => array_values(array_filter($transactions)),
            'file_type'    => 'meesho_return',
            'warnings'     => $this->warnings,
        ];
    }

    private function findBestSheet(array $sheets): array
    {
        $aliases = $this->cfg['sheets']['return'] ?? ['return', 'sheet1'];
        foreach ($aliases as $alias) {
            foreach ($sheets as $name => $rows) {
                if (str_contains(strtolower($name), strtolower($alias))) return $rows;
            }
        }
        // ⚠️ No silent fallback — require cancel_return_date column present
        // in the header before accepting a sheet as the return sheet.
        foreach ($sheets as $rows) {
            $headerCandidates = array_slice($rows, 0, 3);
            foreach ($headerCandidates as $r) {
                $flat = strtolower(implode(',', array_map('strval', $r)));
                if (str_contains($flat, 'cancel_return_date')) {
                    return $rows;
                }
            }
        }
        return [];
    }
}
