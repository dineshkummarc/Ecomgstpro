<?php

namespace App\Parsers;

/**
 * ══════════════════════════════════════════════════════════════════════
 *  MEESHO PARSER SERVICE — orchestration layer
 * ══════════════════════════════════════════════════════════════════════
 *  This class does NOT parse anything itself — it only combines 3
 *  separate parse results:
 *    1. MeeshoSalesParser       → transactions (invoice_number/hsn_code = null)
 *    2. MeeshoReturnParser      → transactions (invoice_number/hsn_code = null)
 *    3. MeeshoTaxInvoiceParser  → lookup [order_number => {invoice_number, hsn_code}]
 *
 *  mergeTaxInvoiceData() looks up each Sales+Return row's `order_number`
 *  field in the Tax Invoice lookup, and when a match is found fills in
 *  invoice_number + hsn_code on that row. Rows that don't match keep
 *  invoice_number/hsn_code as null (a fake invoice number is never
 *  fabricated from sub_order_num).
 *
 *  ⚠️ NEW FIX (order_number key mismatch): the match used to compare the
 *  raw trimmed order_number string against the lookup's raw keys. Since
 *  MeeshoTaxInvoiceParser now builds its lookup keys with
 *  BaseParser::normalizeOrderKey() (to survive spacing/case/Excel
 *  scientific-notation differences between files), this side must
 *  normalize the exact same way before looking the key up — otherwise
 *  the two would never agree and every row would silently fail to merge.
 * ══════════════════════════════════════════════════════════════════════
 */
class MeeshoParserService
{
    /**
     * @param array $transactions Sales + Return transactions (both must
     *                            carry an 'order_number' field — both
     *                            MeeshoSalesParser/MeeshoReturnParser set
     *                            it)
     * @param array $lookup       MeeshoTaxInvoiceParser::parse()['lookup']
     */
    public static function mergeTaxInvoiceData(array $transactions, array $lookup): array
    {
        if (empty($lookup)) {
            \Log::warning('MeeshoParserService: lookup is EMPTY — nothing to merge.');
            return $transactions;
        }

        $stats = [
            'total_meesho_rows' => 0,
            'empty_order_num'   => 0,
            'not_in_lookup'     => 0,
            'entry_null'        => 0,
            'matched'           => 0,
        ];
        $sampleMisses = [];

        foreach ($transactions as &$t) {
            $platform = strtolower((string)($t['platform'] ?? ($t['source_file'] ?? '')));
            if (!str_contains($platform, 'meesho')) {
                continue;
            }
            $stats['total_meesho_rows']++;

            $orderNum = BaseParser::normalizeOrderKey($t['order_number'] ?? '');

            if ($orderNum === '') {
                $stats['empty_order_num']++;
                continue;
            }

            if (!isset($lookup[$orderNum])) {
                $stats['not_in_lookup']++;
                if (count($sampleMisses) < 8) $sampleMisses[] = $orderNum;
                continue;
            }

            if (!is_array($lookup[$orderNum])) {
                continue;
            }

            $isReturn = isset($t['taxable_value']) && (float)$t['taxable_value'] < 0;
            $wantType = $isReturn ? 'CREDIT NOTE' : 'INVOICE';

            $entry = $lookup[$orderNum][$wantType]
                  ?? $lookup[$orderNum]['INVOICE']
                  ?? $lookup[$orderNum]['CREDIT NOTE']
                  ?? (count($lookup[$orderNum]) > 0 ? reset($lookup[$orderNum]) : null);

            if ($entry === null || !is_array($entry)) {
                $stats['entry_null']++;
                continue;
            }

            $stats['matched']++;

            if (!empty($entry['invoice_number']) && is_string($entry['invoice_number'])) {
                $t['invoice_number'] = trim($entry['invoice_number']);
            }
            if (empty($t['hsn_code']) && !empty($entry['hsn_code']) && is_string($entry['hsn_code'])) {
                $t['hsn_code'] = $entry['hsn_code'];
            }
        }
        unset($t);

        \Log::info('MeeshoParserService::mergeTaxInvoiceData STATS', $stats + [
            'sample_misses'      => $sampleMisses,
            'sample_lookup_keys' => array_slice(array_keys($lookup), 0, 8),
        ]);

        return $transactions;
    }

    /**

     *expects a plain array internally)
     */
    public static function computeDocIssueFromTransactions(array $transactions): array
    {
        // Step 1: Collect only valid, non-null invoice numbers
        $invoiceNumbers = [];
        foreach ($transactions as $t) {
            $inv = trim((string)($t['invoice_number'] ?? ''));
            if ($inv !== '') {
                // Use key to auto-dedupe
                $invoiceNumbers[$inv] = true;
            }
        }
        $uniqueInvoices = array_keys($invoiceNumbers);
        if (empty($uniqueInvoices)) {
            return [];
        }


        // Step 2: Split each invoice into a prefix and a numeric part
        $series = [];
        foreach ($uniqueInvoices as $inv) {
            // Regex to find the last block of digits and the prefix before it
            if (!preg_match('/^(.*?)(\d+)$/', $inv, $m)) {
                continue; // Skip invoices that don't end with a number
            }
            $prefix = $m[1];
            $numStr = $m[2];
            $num    = (int)$numStr;
            $width  = strlen($numStr);

            // Group by prefix
            if (!isset($series[$prefix])) {
                $series[$prefix] = ['nums' => [], 'widths' => []];
            }
            $series[$prefix]['nums'][] = $num;
            $series[$prefix]['widths'][] = $width;
        }

        if (empty($series)) {
            return [];
        }

        // Step 3: For each series, calculate min, max, total, cancel, net
        $docs = [];
        $sr   = 1;
        foreach ($series as $prefix => $data) {
            $nums = $data['nums'];
            if (empty($nums)) continue;
            sort($nums);

            $min    = min($nums);
            $max    = max($nums);
            $pad    = max($data['widths']); // Use max padding for consistency
            $totnum = $max - $min + 1;
            $actual = count($nums);
            $cancel = max(0, $totnum - $actual);

            $docs[] = [
                'num'       => $sr,
                'from'      => self::sanitizeDocNumber($prefix . str_pad((string)$min, $pad, '0', STR_PAD_LEFT)),
                'to'        => self::sanitizeDocNumber($prefix . str_pad((string)$max, $pad, '0', STR_PAD_LEFT)),
                'totnum'    => $totnum,
                'cancel'    => $cancel,
                'net_issue' => $actual,
            ];
            $sr++;
        }

        if (empty($docs)) {
            return [];
        }

        return [
            'doc_det' => [
                ['doc_num' => 1, 'docs' => $docs],
            ],
        ];
    }

    private static function sanitizeDocNumber(string $val): string
    {
        $clean = preg_replace('/[^A-Za-z0-9\/\-]/', '', $val);
        return substr($clean, 0, 16);
    }
}
