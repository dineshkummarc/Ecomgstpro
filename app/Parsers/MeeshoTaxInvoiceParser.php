<?php

namespace App\Parsers;

use App\Parsers\BaseParser;

/**
 * ══════════════════════════════════════════════════════════════════════
 *  MEESHO — TAX INVOICE SHEET PARSER (dedicated, Invoice only)
 * ══════════════════════════════════════════════════════════════════════
 *  This parser has exactly one job: build an
 *      order_number → { invoice_number, hsn_code }
 *  lookup table from the Tax Invoice file.
 *
 *  ⚠️ IMPORTANT: this parser does NOT build any `transactions` — those
 *  only come from the TCS Sales and TCS Return sheets. This just returns
 *  a "lookup" that is later merged into the Sales/Return rows via
 *  order_number, by MeeshoParserService::mergeTaxInvoiceData().
 *
 *  Because of this, HSN and invoice_number ONLY ever come from the Tax
 *  Invoice file — the sub_order_num from the Sales/Return sheet is never
 *  treated as an invoice number.
 *
 *  ⚠️ FIX (Type column was being ignored): previously the 'type' column
 *  wasn't mapped in buildTaxInvoiceColMap() at all, so `$this->val($row,
 *  $cm, 'type')` always returned null and every row silently defaulted to
 *  'INVOICE' — regardless of what the file's Type column actually said.
 *  For a sub-order that has both an INVOICE row and a CREDIT NOTE row
 *  (i.e. an item that was later returned), both rows collapsed into the
 *  same lookup['INVOICE'] bucket, and the CREDIT NOTE invoice number was
 *  silently dropped. That meant return transactions in
 *  MeeshoParserService::mergeTaxInvoiceData() would get the ORIGINAL sale
 *  invoice number instead of the credit note number. The Type column is
 *  now read and mapped properly, so INVOICE and CREDIT NOTE rows land in
 *  separate buckets as intended. Other type values seen in real Meesho
 *  exports (e.g. CREDIT_DISCOUNT, CREDIT_CONVERSION) are stored under
 *  their own literal type key too — they simply aren't looked up by
 *  mergeTaxInvoiceData() (which only asks for 'INVOICE' or 'CREDIT
 *  NOTE'), so they don't interfere with the sale/return matching.
 *
 *  ⚠️ NEW FIX (order_number key mismatch): the lookup key used to be the
 *  raw trimmed order_number cell value. Because the Sales/Return files
 *  and this Tax Invoice file can represent the same order id with tiny
 *  textual differences (extra spaces, case, Excel scientific notation
 *  for long numeric IDs), a raw-string lookup key silently failed to
 *  match on the Sales/Return side — invoice_number and hsn_code then
 *  never got merged in, even though this parser had read them correctly.
 *  The lookup key is now built with BaseParser::normalizeOrderKey(), the
 *  same normalizer MeeshoParserService::mergeTaxInvoiceData() uses when
 *  looking a transaction's order_number up in this table — so both sides
 *  always agree on the same key for the same underlying order.
 * ══════════════════════════════════════════════════════════════════════
 */
class MeeshoTaxInvoiceParser extends BaseParser
{
    protected string $platform = 'meesho';

    public function __construct(string $sellerStateCode = '', string $clientGstin = '')
    {
        parent::__construct($sellerStateCode, $clientGstin);
    }

    /**
     * @return array{
     *   success: bool,
     *   lookup: array<string,array{invoice_number:?string,hsn_code:?string}>,
     *   file_type: string,
     *   warnings: array
     * }
     */
    public function parse(array $sheets, string $sellerStateCode, string $period, string $fileRole = 'invoice'): array
    {
        $this->warnings = [];

        $targetRows = $this->findBestSheet($sheets);
        if (empty($targetRows)) {
            $this->warn("No data rows found in the Tax Invoice file.");
            return ['success' => false, 'lookup' => [], 'file_type' => 'meesho_tax_invoice', 'warnings' => $this->warnings];
        }

        $hIdx = $this->findHeaderRow($targetRows, ['invoice_number', 'sub_order_num', 'hsn_code'], 3);
        if ($hIdx < 0) {
            $hIdx = $this->findHeaderRow($targetRows, ['invoice', 'order', 'hsn'], 3);
        }
        if ($hIdx < 0) {
            $this->warn("Header row not found in Tax Invoice file. Expected columns: order number, invoice number, HSN code.");
            return ['success' => false, 'lookup' => [], 'file_type' => 'meesho_tax_invoice', 'warnings' => $this->warnings];
        }

        $headers = array_map('strval', $targetRows[$hIdx]);
        $cm      = $this->buildTaxInvoiceColMap($headers);

        // ── STRICT SLOT CHECK ────────────────────────────────────────
        // If no order-number column is found at all, this is the wrong
        // file — a Sales/Return file may have been dropped here by mistake.
        if (($cm['order_number'] ?? -1) < 0) {
            return [
                'success'    => false,
                'wrong_slot' => true,
                'error'      => "This is the 'Tax Invoice' slot, but no order-number column was found in the file. Please upload the correct Tax Invoice file.",
                'lookup'     => [],
                'file_type'  => 'meesho_tax_invoice',
                'warnings'   => $this->warnings,
            ];
        }
        if (($cm['invoice_number'] ?? -1) < 0 && ($cm['hsn_code'] ?? -1) < 0) {
            $this->warn("Neither invoice_number nor hsn_code column found — this doesn't look like a Tax Invoice file.");
        }

        $lookup = [];
        for ($i = $hIdx + 1; $i < count($targetRows); $i++) {
            $row = $targetRows[$i];
            if ($this->rowIsEmpty($row, 2)) continue;

            // ⚠️ FIX: lookup key ab normalized hai (BaseParser::normalizeOrderKey)
            // taaki Sales/Return side ke order_number se reliably match ho —
            // raw string compare extra spaces / case / Excel scientific
            // notation ki wajah se silently miss ho jaata tha.
           $orderNumRaw = $this->val($row, $cm, 'order_number');
            $orderNum    = self::normalizeOrderKey($orderNumRaw);
            if ($orderNum === '') {
                \Log::warning('MeeshoTaxInvoiceParser: empty normalized order_number', [
                    'row_index' => $i,
                    'raw_value' => $orderNumRaw,
                ]);
                continue;
            }

            $invNum = trim((string)($this->val($row, $cm, 'invoice_number') ?? ''));
            $hsn    = trim((string)($this->val($row, $cm, 'hsn_code') ?? ''));

            // Type (INVOICE / CREDIT NOTE / CREDIT_DISCOUNT / CREDIT_CONVERSION / ...)
            // Only default to 'INVOICE' when the Type column itself is
            // genuinely missing from the file — never when it's present
            // but the row's Type is something else.
            $typeRaw = ($cm['type'] ?? -1) >= 0 ? $this->val($row, $cm, 'type') : null;
            $type    = strtoupper(trim((string)($typeRaw ?? 'INVOICE')));
            if ($type === '') {
                $type = 'INVOICE';
            }

            if ($invNum === '' && $hsn === '') continue;

            // ⚠️ If the same order_number has multiple rows (multi-item
            // invoice), keep the first non-empty value — never overwrite.

            // If an entry for that type (INVOICE/CREDIT NOTE/...) doesn't
            // already exist, add it. If it does, only fill in whichever of
            // HSN/Invoice No. is still null.
            if (!isset($lookup[$orderNum])) {
                $lookup[$orderNum] = [];
            }
            if (!isset($lookup[$orderNum][$type])) {
                $lookup[$orderNum][$type] = ['invoice_number' => $invNum ?: null, 'hsn_code' => $hsn ?: null];
            } else {
                if (empty($lookup[$orderNum][$type]['invoice_number']) && $invNum !== '') {
                    $lookup[$orderNum][$type]['invoice_number'] = $invNum;
                }
                if (empty($lookup[$orderNum][$type]['hsn_code']) && $hsn !== '') {
                    $lookup[$orderNum][$type]['hsn_code'] = $hsn;
                }
            }
        }

        if (empty($lookup)) {
            $this->warn("Tax Invoice file parsed but no valid invoice_number/hsn_code row was found.");
        }

        return [
            'success'   => true,
            'lookup'    => $lookup,
            'file_type' => 'meesho_tax_invoice',
            'warnings'  => $this->warnings,
        ];
    }

    private function buildTaxInvoiceColMap(array $headers): array
    {
        $aliases = [
            'order_number'   => ['sub_order_num', 'suborder_num', 'sub_order_no', 'sub order no', 'order_number', 'order number', 'order no', 'order id', 'suborder no', 'suborder number'],
            'invoice_number' => ['invoice_number', 'invoice no', 'invoice_no', 'inv_no', 'invoice number'],
            'hsn_code'       => ['hsn_code', 'hsn', 'hsn code', 'hsn/sac', 'hsn_sac'],
            // ⚠️ FIX: 'type' column was previously not mapped at all, so
            // every row silently defaulted to 'INVOICE' regardless of its
            // actual Type value. Now mapped so INVOICE vs CREDIT NOTE
            // (and any other type value) land in the correct lookup bucket.
            'type'           => ['type', 'invoice type', 'invoice_type', 'doc type', 'doc_type', 'document type'],
        ];

        $map = [];
        foreach ($aliases as $field => $names) {
            $map[$field] = -1;
            foreach ($headers as $idx => $h) {
                $hClean = strtolower(trim((string)$h));
                if ($hClean === '') continue;
                foreach ($names as $n) {
                    if ($hClean === strtolower($n) || str_contains($hClean, strtolower($n))) {
                        $map[$field] = $idx;
                        continue 2;
                    }
                }
            }
        }
        return $map;
    }

    private function findBestSheet(array $sheets): array
    {
        $aliases = ['tax invoice', 'tax_invoice', 'taxinvoice', 'invoice', 'sheet1'];
        foreach ($aliases as $alias) {
            foreach ($sheets as $name => $rows) {
                if (str_contains(strtolower($name), $alias)) return $rows;
            }
        }
        return array_values($sheets)[0] ?? [];
    }
}
