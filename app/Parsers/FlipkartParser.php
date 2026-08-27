<?php

namespace App\Parsers;

use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * ══════════════════════════════════════════════════════════════════════
 *  FLIPKART PARSER — FIXED v13
 *
 *  ← NEW in v13 (this fix):
 *  9. BUG: Section 13 (Document Issued) sheet me GST portal ka real
 *     export already PRE-AGGREGATED format me aata hai — columns:
 *     GSTIN | Invoice Series From | Invoice Series To |
 *     Total Number of Invoices | Cancelled if any | Net invoices Issued.
 *     Purana parseSection13DocIssue() sirf ek raw per-row "Invoice
 *     Number" column dhoondta tha (jisse khud From/To range banata
 *     tha) — is naye/asli format me aisa column hota hi nahi, isliye
 *     "'Invoice Number' column not found" warning ke saath poora
 *     Document Issued block skip ho jaata tha aur invoice numbers
 *     kabhi nahi aate the.
 *
 *     FIX: parseSection13DocIssue() ab pehle "Invoice Series From/To" +
 *     "Total/Cancelled/Net" columns detect karta hai — agar mil jaayein
 *     (jo asal portal file me hamesha milenge), to unhi values ko
 *     SEEDHA (as-is / same to same) use karta hai, koi rebuild nahi.
 *     GSTIN column se sirf client ki apni GSTIN wali row filter hoti
 *     hai (isOwnGstinRow) — taaki multi-state file me galat state ka
 *     row na mix ho. Agar yeh naya format na mile to purana per-row
 *     "Invoice Number" wala fallback logic bilkul waise hi chalta hai
 *     jaisa pehle tha — koi existing behavior nahi toota.
 *
 *     MTR / Sales Report path (parseMtrFormat → buildDocIssuedFromInvoiceNumbers
 *     with $suppressSingletons = true) is fix se UNCHANGED hai — woh
 *     already sahi se invoice numbers deta hai, yahaan koi change nahi
 *     kiya gaya.
 *
 *  ← v12 (pehle se):
 *  8. MTR (Sales Report) upload ke "Document Issued" table me un
 *     invoice-number prefixes ke liye bhi ek row ban jaati thi jinki
 *     asal me koi series/range nahi banti — file me wo prefix sirf EK
 *     baar aaya hota tha (e.g. "LIA76DK270000003" → From=To=khud, Total=1,
 *     Cancelled=0, Net=1). Yeh noisy tha aur galat impression deta tha
 *     ki yeh ek "series" hai.
 *
 *     FIX: buildDocIssuedFromInvoiceNumbers() me ek naya optional
 *     $suppressSingletons parameter add kiya hai. Jab true (sirf MTR /
 *     Sales Report ke doc-issued call se), to jis prefix ka count file
 *     me sirf 1 hai (koi real series/range nahi bani), uska row poori
 *     tarah drop ho jaata hai — sirf wahi series dikhti hain jinke
 *     multiple continuous invoice numbers hain.
 *
 *     GSTR-1 file wale Section 13 (parseSection13DocIssue) ka call is
 *     parameter ko pass hi nahi karta (default false), isliye uska
 *     behavior bilkul pehle jaisa hi rahega — wahan koi change nahi.
 * ══════════════════════════════════════════════════════════════════════
 */
class FlipkartParser extends BaseParser
{
    protected string $platform = 'flipkart';

    // ── Hardcoded column aliases (config pe fallback nahi karna padega) ──
    private const COL_ALIASES = [
        // GSTR-1 format columns

        'taxable_value'  => [
            'aggregate taxable value', 'net taxable value', 'taxable value', 'taxablevalue',
            'taxable amt', 'taxable amount', 'net value', 'taxable', 'total taxable',
        ],
        'cgst_rate'      => ['cgst rate', 'cgst %', 'cgst%', 'cgst rate(%)', 'central tax rate'],
        'sgst_rate'      => [
            'sgst rate', 'sgst %', 'sgst%', 'sgst rate(%)', 'sgst/ut %', 'sgst/ut rate',
            'state tax rate', 'utgst rate',
        ],
        'igst_rate'      => ['igst rate', 'igst %', 'igst%', 'igst rate(%)', 'integrated tax rate', 'tax rate'],
        'cgst_amount'    => ['cgst amount', 'cgst amt', 'central tax amount'],
        'sgst_amount'    => [
            'sgst amount', 'sgst amt', 'sgst/ut amount', 'sgst /ut amount',
            'state tax amount', 'utgst amount',
        ],
        'igst_amount'    => ['igst amount', 'igst amt', 'integrated tax amount'],
        'place_of_supply'=> [
            'place of supply', 'pos', 'delivered state (pos)', 'delivery state', 'delivered state',
            'buyer state', 'ship to state', 'ship-to state',
            'delivered state code', 'delivery state code',
        ],
        'gstin'          => ['gstin', 'seller gstin', 'gstin of supplier'],
        'invoice_number' => ['invoice number', 'invoice no', 'invoice no.', 'order id', 'document number'],
        'invoice_date'   => ['invoice date', 'invoice dt', 'order date', 'document date'],
        'quantity'       => ['quantity', 'qty', 'no. of items', 'units'],
        'hsn_code'       => ['hsn', 'hsn code', 'hsn/sac'],
        'buyer_gstin'    => ['buyer gstin', 'recipient gstin', 'customer gstin'],
        // MTR format
        'igst_rate_mtr'  => ['igst rate'],
        'cgst_rate_mtr'  => ['cgst rate'],
        'delivery_state' => ["customer's delivery state", 'delivery state', 'customer delivery state', 'ship to state'],
        'billing_state'  => ["customer's billing state", 'billing state'],
        'seller_gstin'   => ['seller gstin'],
        'event_type'     => ['event type'],
        'invoice_no_mtr' => ['buyer invoice id', 'invoice number', 'invoice no'],
        'inv_date_mtr'   => ['buyer invoice date', 'invoice date'],
        'qty_mtr'        => ['item quantity', 'quantity', 'qty'],
        'hsn_mtr'        => ['hsn code', 'hsn'],
        'buyer_gstin_mtr'=> ['business gst number', 'buyer gstin'],
    ];

    private const AMOUNT_FIELDS = ['igst_amount', 'cgst_amount', 'sgst_amount'];

    public function __construct(string $sellerStateCode = '', string $clientGstin = '')
    {
        parent::__construct($sellerStateCode, $clientGstin);

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
        $names  = array_map('strtolower', $spreadsheet->getSheetNames());
        $detect = [
            '7(a)(2)', '7(b)(2)', '7a(2)', '7b(2)', '7a2', '7b2',
            'flipkart', 'shopsy', 'sales report', 'cashback',
            'section 7a', 'section 7b', '7a', '7b', 'b2cs', 'b2cl',
        ];
        foreach ($names as $n) {
            foreach ($detect as $d) {
                if (str_contains($n, $d)) return true;
            }
        }
        return false;
    }

    // ════════════════════════════════════════════════════════════════════
    //  MAIN PARSE — format auto-detect
    // ════════════════════════════════════════════════════════════════════

    public function parse(array $sheets, string $sellerStateCode, string $period): array
    {
        $this->sellerStateCode = $sellerStateCode;
        $this->period          = $period;
        $this->warnings        = [];

        $sheetNamesLower = array_map('strtolower', array_keys($sheets));

        $hasSalesReport = false;
        foreach ($sheetNamesLower as $sn) {
            if (str_contains($sn, 'sales report')) {
                $hasSalesReport = true;
                break;
            }
        }

        if ($hasSalesReport) {
            return $this->parseMtrFormat($sheets);
        }

        return $this->parseGstr1Format($sheets);
    }

    // ════════════════════════════════════════════════════════════════════
    //  ← NEW (v9): normalize a GSTIN for comparison, and check whether a
    //  row's Seller GSTIN belongs to the current client. When
    //  $this->clientGstin is empty, filtering is a no-op (returns true)
    //  so callers that never pass a clientGstin keep old behavior.
    // ════════════════════════════════════════════════════════════════════

    private function normGstin(?string $g): string
    {
        return strtoupper(trim((string)$g));
    }

    private function isOwnGstinRow(?string $rowGstin): bool
    {
        $client = $this->normGstin($this->clientGstin ?? '');
        if ($client === '') return true; // no client GSTIN configured — don't filter
        $row = $this->normGstin($rowGstin);
        if ($row === '') return true; // sheet has no seller-gstin column/value — can't filter, let it through
        return $row === $client;
    }

    // ════════════════════════════════════════════════════════════════════
    //  ← NEW (v11, updated v12): shared Document-Issued builder.
    //
    //  Takes a flat list of raw invoice-number strings (as they appear in
    //  the sheet, e.g. "INV/24-25/1042", "FA123045", "1042") and returns
    //  the same doc_det structure the app expects:
    //     from      = smallest invoice number in the series
    //     to        = largest invoice number in the series
    //     totnum    = total invoices that SHOULD exist in that from→to range
    //     cancel    = how many numbers in that range are missing
    //                 (i.e. not actually present in the data)
    //     net_issue = totnum - cancel
    //
    //  Numbers are grouped by their non-numeric prefix (e.g. everything
    //  before the trailing digits) so that different invoice series
    //  (different prefixes) each get their own From/To/Total/Cancel row,
    //  instead of being wrongly mixed into one range.
    //
    //  ← NEW (v12): $suppressSingletons — when true, a prefix group that
    //  appears only ONCE in the whole input (no real series/range, just a
    //  stray one-off invoice number) is dropped entirely instead of
    //  producing a noisy "From=To, Total=1, Cancelled=0, Net=1" row.
    //  Used ONLY by the MTR / Sales Report path (parseMtrFormat). The
    //  GSTR-1 Section 13 call (parseSection13DocIssue) does NOT pass this
    //  flag, so its behavior is completely unchanged.
    // ════════════════════════════════════════════════════════════════════

    private function buildDocIssuedFromInvoiceNumbers(array $invoiceNumbers, bool $suppressSingletons = false): array
    {
        $entries = [];
        foreach ($invoiceNumbers as $raw) {
            $raw = trim((string)$raw);
            if ($raw === '') continue;

            if (preg_match('/^(.*?)(\d+)$/', $raw, $m)) {
                $entries[] = [
                    'raw'    => $raw,
                    'prefix' => $m[1],
                    'num'    => (int)$m[2],
                    'width'  => strlen($m[2]),
                ];
            } else {
                $entries[] = ['raw' => $raw, 'prefix' => $raw, 'num' => null, 'width' => 0];
            }
        }

        if (empty($entries)) return [];

        $groups = [];
        foreach ($entries as $e) {
            $groups[$e['prefix']][] = $e;
        }

        $docs = [];
        $sr   = 1;
        foreach ($groups as $prefix => $group) {

            // ← NEW (v12): a prefix that appears only ONCE in the whole
            // file is a stray one-off invoice number, not a real series
            // (from == to, total == 1, cancelled == 0 every time). When
            // $suppressSingletons is true (MTR / Sales Report upload),
            // drop these entirely instead of showing a noisy "1 / 0 / 1"
            // row for each one.
            if ($suppressSingletons && count($group) <= 1) {
                continue;
            }

            $numeric = array_filter($group, fn($e) => $e['num'] !== null);

            if (empty($numeric)) {
                // No numeric part detected — just list distinct raw values as-is.
                $unique = array_values(array_unique(array_column($group, 'raw')));
                sort($unique);

                // ← NEW (v12): same singleton rule applies even when there's
                // no numeric part to the invoice number at all.
                if ($suppressSingletons && count($unique) <= 1) {
                    continue;
                }

                $docs[] = [
                    'num'       => $sr,
                    'from'      => $unique[0],
                    'to'        => end($unique),
                    'totnum'    => count($group),
                    'cancel'    => count($group) - count($unique),
                    'net_issue' => count($unique),
                ];
                $sr++;
                continue;
            }

            $nums  = array_column($numeric, 'num');
            $min   = min($nums);
            $max   = max($nums);
            $width = $numeric[array_key_first($numeric)]['width'];

            $totalIssued      = $max - $min + 1; // expected count across the full range
            $distinctPresent  = count(array_unique($nums));
            $cancelled        = max(0, $totalIssued - $distinctPresent); // gaps in the range = cancelled

            // ← NEW (v12): if min == max (i.e. effectively only one distinct
            // invoice number even after the numeric parse), that's still a
            // singleton — no real range/series was formed.
            if ($suppressSingletons && $min === $max) {
                continue;
            }

            $fromStr = $prefix . str_pad((string)$min, $width, '0', STR_PAD_LEFT);
            $toStr   = $prefix . str_pad((string)$max, $width, '0', STR_PAD_LEFT);

            $docs[] = [
                'num'       => $sr,
                'from'      => $fromStr,
                'to'        => $toStr,
                'totnum'    => $totalIssued,
                'cancel'    => $cancelled,
                'net_issue' => $totalIssued - $cancelled,
            ];
            $sr++;
        }

        if (empty($docs)) return [];

        return ['doc_det' => [['doc_num' => 1, 'docs' => $docs]]];
    }

    // ════════════════════════════════════════════════════════════════════
    //  FORMAT 1: GSTR-1 Ready File
    // ════════════════════════════════════════════════════════════════════

    private function parseGstr1Format(array $sheets): array
    {
        $transactions = [];

        $sheetMap = [];
        foreach ($sheets as $name => $rows) {
            $sheetMap[strtolower(trim($name))] = ['name' => $name, 'rows' => $rows];
        }

        $intraRows = $this->findSheetByPatterns($sheetMap, [
            '7(a)(2)', '7a(2)', '7a2', '7 a 2',
            'section 7a', 'sec 7a', '7a',
            'b2cs intra', 'intra', 'b2cs',
        ]);

        $interRows = $this->findSheetByPatterns($sheetMap, [
            '7(b)(2)', '7b(2)', '7b2', '7 b 2',
            'section 7b', 'sec 7b', '7b',
            'b2cs inter', 'inter',
        ]);

        $b2clRows = $this->findSheetByPatterns($sheetMap, [
            '5b', 'section 5b', 'sec 5b', 'b2cl',
        ]);

        $section13Rows = $this->findSheetByPatterns($sheetMap, [
            'section 13', 'sec 13', 'sec. 13',
            'document issued', 'documents issued', 'doc issued', 'doc. issued',
            '13.', '13 -', '13-',
        ]);
        if (!$section13Rows) {
            foreach ($sheetMap as $data) {
                if ($this->findHeaderRow($data['rows'], ['invoice series'], 1) >= 0) {
                    $section13Rows = $data['rows'];
                    break;
                }
            }
        }
        // ← v13: parseSection13DocIssue() now handles BOTH the
        // pre-aggregated "Invoice Series From/To" portal format AND the
        // older per-row "Invoice Number" format automatically.
        $docIssueOverride = $section13Rows ? $this->parseSection13DocIssue($section13Rows) : [];
        if (!$section13Rows) {
            $this->warn("Section 13 (Document Issued) sheet not found — neither by name nor by "
                . "'Invoice Series' header. Document Issued skipped. Sheets in file: ["
                . implode(', ', array_keys($sheetMap)) . "]");
        }

        $section12Rows = $this->findSheetByPatterns($sheetMap, [
            'section 12', 'sec 12', 'sec. 12',
            'hsn summary', 'hsn-wise summary', 'hsn wise summary',
            '12.', '12 -', '12-',
        ]);
        if (!$section12Rows) {
            foreach ($sheetMap as $data) {
                if ($this->findHeaderRow($data['rows'], ['hsn'], 1) >= 0) {
                    $section12Rows = $data['rows'];
                    break;
                }
            }
        }
        $hsnOverride = $section12Rows ? $this->parseSection12Hsn($section12Rows) : [];
        if (!$section12Rows) {
            $this->warn("Section 12 (HSN Summary) sheet not found — neither by name nor by "
                . "'HSN' header. HSN Summary skipped. Sheets in file: ["
                . implode(', ', array_keys($sheetMap)) . "]");
        }

        if (!$intraRows && !$interRows && !$b2clRows) {
            $allNames = implode(', ', array_keys($sheetMap));
            $this->warn("Flipkart GSTR-1: No standard sheets found. Sheets: [{$allNames}]. Trying all sheets as generic B2C data.");

            foreach ($sheets as $name => $rows) {
                $parsed = $this->parseGenericB2CSheet($rows, $name);
                $transactions = array_merge($transactions, $parsed);
            }

            return [
                'transactions'       => array_values(array_filter($transactions)),
                'warnings'           => $this->warnings,
                'file_type'          => 'flipkart_gstr1_generic',
                'doc_issue_override' => $docIssueOverride,
                'hsn_override'       => $hsnOverride,
            ];
        }

        if ($intraRows) {
            $transactions = array_merge($transactions, $this->parse7A2($intraRows));
        } else {
            $this->warn("Sheet '7(A)(2)' not found — INTRA state data missing");
        }

        if ($interRows) {
            $transactions = array_merge($transactions, $this->parse7B2($interRows));
        } else {
            $this->warn("Sheet '7(B)(2)' not found — INTER state data missing");
        }

        if ($b2clRows) {
            $transactions = array_merge($transactions, $this->parse5B($b2clRows));
        }

        return [
            'transactions'       => array_values(array_filter($transactions)),
            'warnings'           => $this->warnings,
            'file_type'          => 'flipkart_gstr1',
            'doc_issue_override' => $docIssueOverride,
            'hsn_override'       => $hsnOverride,
        ];
    }

    // ════════════════════════════════════════════════════════════════════
    //  SECTION 13 — DOCUMENT ISSUED (GSTR-1 file)
    //
    //  ← FIXED (v13): the real GST-portal Section 13 export is already
    //  PRE-AGGREGATED — one row per GSTIN with:
    //     GSTIN | Invoice Series From | Invoice Series To |
    //     Total Number of Invoices | Cancelled if any | Net invoices Issued
    //  There is no per-row "Invoice Number" column in this format, so the
    //  old logic (which only looked for that column) always skipped
    //  Document Issued entirely for real portal files.
    //
    //  Now: if "Invoice Series From/To" columns are found, their values —
    //  along with Total/Cancelled/Net — are used DIRECTLY (as-is / same
    //  to same, no rebuilding), filtered to the client's own GSTIN via
    //  isOwnGstinRow() so a multi-state file doesn't mix another
    //  registration's series in.
    //
    //  Fallback (older/raw exports with a genuine per-row "Invoice
    //  Number" list instead of pre-aggregated ranges) is UNCHANGED from
    //  before — From/To/Cancelled/Net are still derived via
    //  buildDocIssuedFromInvoiceNumbers().
    // ════════════════════════════════════════════════════════════════════

    private function parseSection13DocIssue(array $rows): array
    {
        if (empty($rows)) return [];

        $hIdx = $this->findHeaderRow($rows, ['gstin', 'invoice series'], 1);
        if ($hIdx < 0) $hIdx = $this->findHeaderRow($rows, ['invoice series'], 1);
        if ($hIdx < 0) $hIdx = $this->findHeaderRow($rows, ['invoice number'], 1);
        if ($hIdx < 0) {
            $this->warn("Section 13: header row not found — Document Issued skipped (not calculated elsewhere either).");
            return [];
        }

        $headers = array_map('strval', $rows[$hIdx]);
        $lower   = array_map(fn($h) => strtolower(trim(preg_replace('/\s+/', ' ', $h))), $headers);

        $find = function (array $aliases) use ($lower) {
            foreach ($aliases as $a) {
                $idx = array_search($a, $lower, true);
                if ($idx !== false) return $idx;
            }
            foreach ($aliases as $a) {
                foreach ($lower as $i => $h) {
                    if ($h !== '' && str_contains($h, $a)) return $i;
                }
            }
            return -1;
        };

        $cGstin  = $find(['gstin']);
        $cFrom   = $find(['invoice series from', 'series from']);
        $cTo     = $find(['invoice series to', 'series to']);
        $cTotal  = $find(['total number of invoices', 'total number of invoice', 'total invoices']);
        $cCancel = $find(['cancelled if any', 'cancelled', 'cancel']);
        $cNet    = $find(['net invoices issued', 'net invoice issued', 'net issued']);

        // ← NEW (v13): pre-aggregated portal format — read as-is.
        if ($cFrom >= 0 && $cTo >= 0) {
            $docs = [];
            $sr   = 1;
            $skippedForeignGstin = 0;

            for ($i = $hIdx + 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                if ($this->rowIsEmpty($row, 1)) continue;

                if ($cGstin >= 0 && !$this->isOwnGstinRow((string)($row[$cGstin] ?? ''))) {
                    $skippedForeignGstin++;
                    continue;
                }

                $from = trim((string)($row[$cFrom] ?? ''));
                $to   = trim((string)($row[$cTo]   ?? ''));
                if ($from === '' && $to === '') continue;

                $total  = $cTotal  >= 0 ? (int)$this->num($row[$cTotal]  ?? 0) : null;
                $cancel = $cCancel >= 0 ? (int)$this->num($row[$cCancel] ?? 0) : 0;
                $net    = $cNet    >= 0 ? (int)$this->num($row[$cNet]    ?? 0) : null;

                if ($total === null) $total = ($net !== null ? $net + $cancel : 1);
                if ($net   === null) $net   = max(0, $total - $cancel);

                $docs[] = [
                    'num'       => $sr,
                    'from'      => $from,
                    'to'        => $to !== '' ? $to : $from,
                    'totnum'    => $total,
                    'cancel'    => $cancel,
                    'net_issue' => $net,
                ];
                $sr++;
            }

            if ($skippedForeignGstin > 0) {
                $this->warn("Section 13: {$skippedForeignGstin} row(s) skipped — GSTIN did not match client GSTIN (data for a different registration).");
            }

            if (empty($docs)) return [];
            return ['doc_det' => [['doc_num' => 1, 'docs' => $docs]]];
        }

        // Fallback — genuine per-row "Invoice Number" list (older/raw
        // exports). Same logic as before: derive From/To/Cancelled/Net
        // ourselves via buildDocIssuedFromInvoiceNumbers().
        $cInvNo = $find(['invoice number', 'invoice no', 'invoice no.', 'buyer invoice id', 'document number']);

        if ($cInvNo < 0) {
            $this->warn("Section 13: neither 'Invoice Series From/To' nor 'Invoice Number' column found — Document Issued skipped.");
            return [];
        }

        $invoiceNumbers = [];
        $skippedForeignGstin = 0;
        for ($i = $hIdx + 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if ($this->rowIsEmpty($row, 1)) continue;

            if ($cGstin >= 0 && !$this->isOwnGstinRow((string)($row[$cGstin] ?? ''))) {
                $skippedForeignGstin++;
                continue;
            }

            $raw = trim((string)($row[$cInvNo] ?? ''));
            if ($raw === '') continue;

            $invoiceNumbers[] = $raw;
        }

        if ($skippedForeignGstin > 0) {
            $this->warn("Section 13: {$skippedForeignGstin} row(s) skipped — GSTIN did not match client GSTIN (data for a different registration).");
        }

        if (empty($invoiceNumbers)) return [];

        // ← UNCHANGED: no $suppressSingletons flag passed — GSTR-1's
        // Document Issued keeps every prefix, including one-off ones,
        // exactly as before.
        return $this->buildDocIssuedFromInvoiceNumbers($invoiceNumbers);
    }

    // ════════════════════════════════════════════════════════════════════
    //  SECTION 12 — HSN SUMMARY (direct read)
    // ════════════════════════════════════════════════════════════════════

    private function parseSection12Hsn(array $rows): array
    {
        if (empty($rows)) return [];

        $hIdx = $this->findHeaderRow($rows, ['hsn'], 1);
        if ($hIdx < 0) {
            $this->warn("Section 12: header row not found (no 'HSN' column detected) — HSN Summary skipped.");
            return [];
        }

        $headers = array_map('strval', $rows[$hIdx]);
        $lower   = array_map(fn($h) => strtolower(trim(preg_replace('/\s+/', ' ', $h))), $headers);
        $normed  = array_map(fn($h) => preg_replace('/[^a-z0-9]/', '', strtolower((string)$h)) ?? '', $headers);

        $find = function (array $aliases) use ($lower, $normed) {
            foreach ($aliases as $a) {
                $idx = array_search(strtolower($a), $lower, true);
                if ($idx !== false) return $idx;
            }
            foreach ($aliases as $a) {
                $al = strtolower($a);
                foreach ($lower as $i => $h) {
                    if ($h !== '' && (str_contains($h, $al) || str_contains($al, $h))) return $i;
                }
            }
            foreach ($aliases as $a) {
                $an = preg_replace('/[^a-z0-9]/', '', strtolower($a)) ?? '';
                if ($an === '') continue;
                foreach ($normed as $i => $h) {
                    if ($h !== '' && (str_contains($h, $an) || str_contains($an, $h))) return $i;
                }
            }
            return -1;
        };

        $cHsn   = $find(['hsn', 'hsn code', 'hsn/sac', 'hsn/sac code']);
        $cDesc  = $find(['description', 'description of goods', 'goods description']);
        $cUqc   = $find(['uqc', 'unit', 'unit quantity code']);
        $cQty   = $find(['total quantity', 'quantity']);
        $cRate  = $find(['rate of tax', 'rate', 'gst rate', 'tax rate']);
        $cTxval = $find(['taxable value', 'total taxable value']);
        $cVal   = $find(['total value', 'value', 'invoice value']);
        $cIamt  = $find(['integrated tax amount', 'igst amount', 'integrated tax']);
        $cCamt  = $find(['central tax amount', 'cgst amount', 'central tax']);
        $cSamt  = $find(['state/ut tax amount', 'state tax amount', 'sgst amount', 'state/ut tax', 'utgst amount']);
        $cCsamt = $find(['cess amount', 'cess']);

        if ($cHsn < 0 || $cTxval < 0) {
            $this->warn("Section 12: 'HSN' or 'Taxable Value' column not found (headers: "
                . implode(' | ', array_filter(array_slice($headers, 0, 12))) . ") — HSN Summary skipped.");
            return [];
        }

        $rowsOut = [];
        $sr = 1;
        for ($i = $hIdx + 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if ($this->rowIsEmpty($row, 1)) continue;

            $hsn = trim((string)($row[$cHsn] ?? ''));
            if ($hsn === '') continue;

            $txval = $this->num($row[$cTxval] ?? 0);
            $val   = $cVal >= 0 ? $this->num($row[$cVal] ?? 0) : $txval;
            if ($txval == 0 && $val == 0) continue;

            $rowsOut[] = [
                'num'    => $sr,
                'hsn_sc' => $hsn,
                'desc'   => $cDesc >= 0 ? (trim((string)($row[$cDesc] ?? '')) ?: 'Goods') : 'Goods',
                'uqc'    => $cUqc  >= 0 ? (trim((string)($row[$cUqc]  ?? '')) ?: 'NOS')   : 'NOS',
                'qty'    => $this->hsnNum($cQty  >= 0 ? $this->num($row[$cQty]  ?? 0) : 0),
                'rt'     => $this->hsnNum($cRate >= 0 ? $this->num($row[$cRate] ?? 0) : 0),
                'txval'  => $this->hsnNum($txval),
                'val'    => $this->hsnNum($val),
                'iamt'   => $this->hsnNum($cIamt  >= 0 ? $this->num($row[$cIamt]  ?? 0) : 0),
                'camt'   => $this->hsnNum($cCamt  >= 0 ? $this->num($row[$cCamt]  ?? 0) : 0),
                'samt'   => $this->hsnNum($cSamt  >= 0 ? $this->num($row[$cSamt]  ?? 0) : 0),
                'csamt'  => $this->hsnNum($cCsamt >= 0 ? $this->num($row[$cCsamt] ?? 0) : 0),
            ];
            $sr++;
        }

        return $rowsOut;
    }

    private function hsnNum($val): int|float
    {
        $rounded = round((float)$val, 2);
        return ($rounded == (int)$rounded) ? (int)$rounded : $rounded;
    }

    // ════════════════════════════════════════════════════════════════════
    //  GENERIC B2C SHEET PARSER
    // ════════════════════════════════════════════════════════════════════

    private function parseGenericB2CSheet(array $rows, string $sheetName): array
    {
        if (empty($rows)) return [];

        $hIdx = $this->findHeaderRow($rows, ['taxable'], 1);
        if ($hIdx < 0) return [];

        $headers = array_map('strval', $rows[$hIdx]);
        $cm      = $this->buildColMapFromAliases($headers);

        if (($cm['taxable_value'] ?? -1) < 0) {
            $this->warn("Sheet '{$sheetName}': taxable value column not found. Cols: " . implode(' | ', array_filter(array_slice($headers, 0, 10))));
            return [];
        }

        $transactions = [];
        $skippedForeignGstin = 0;
        for ($i = $hIdx + 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if ($this->rowIsEmpty($row, 1)) continue;

            // ← NEW (v9): skip rows that belong to a different Seller GSTIN
            if (($cm['gstin'] ?? -1) >= 0 && !$this->isOwnGstinRow((string)($row[$cm['gstin']] ?? ''))) {
                $skippedForeignGstin++;
                continue;
            }

            $txval = $this->num($row[$cm['taxable_value']] ?? null);
            if ($txval == 0) continue;

            $posRaw = ($cm['place_of_supply'] ?? -1) >= 0 ? ($row[$cm['place_of_supply']] ?? null) : null;
            $pos    = $posRaw ? $this->resolveStateCode($posRaw) : $this->sellerStateCode;
            if ($pos === '') {
                $this->warn("Sheet '{$sheetName}' row {$i}: cannot resolve state '{$posRaw}'");
                continue;
            }

            $igstRate = ($cm['igst_rate'] ?? -1) >= 0 ? $this->num($row[$cm['igst_rate']] ?? null) : 0;
            $cgstRate = ($cm['cgst_rate'] ?? -1) >= 0 ? $this->num($row[$cm['cgst_rate']] ?? null) : 0;
            $rate     = $igstRate > 0 ? $igstRate : round($cgstRate * 2, 2);

            $igstAmt = ($cm['igst_amount'] ?? -1) >= 0 ? $this->num($row[$cm['igst_amount']] ?? null) : null;
            $cgstAmt = ($cm['cgst_amount'] ?? -1) >= 0 ? $this->num($row[$cm['cgst_amount']] ?? null) : null;
            $sgstAmt = ($cm['sgst_amount'] ?? -1) >= 0 ? $this->num($row[$cm['sgst_amount']] ?? null) : null;

            $t = $this->makeTransaction(
                pos:          $pos,
                rate:         $rate,
                taxableValue: $txval,
                igstAmt:      $igstAmt,
                cgstAmt:      $cgstAmt,
                sgstAmt:      $sgstAmt,
                invoiceNo:    ($cm['invoice_number'] ?? -1) >= 0 ? (string)($row[$cm['invoice_number']] ?? '') : null,
                invoiceDate:  ($cm['invoice_date'] ?? -1) >= 0 ? $this->date($row[$cm['invoice_date']] ?? null) : null,
                qty:          max(1, (int)$this->num($row[$cm['quantity'] ?? -1] ?? 1)),
                hsn:          ($cm['hsn_code'] ?? -1) >= 0 ? (string)($row[$cm['hsn_code']] ?? '') : null,
                buyerGstin:   ($cm['buyer_gstin'] ?? -1) >= 0 ? (string)($row[$cm['buyer_gstin']] ?? '') : null,
            );

            if (!empty($t)) $transactions[] = $t;
        }

        if ($skippedForeignGstin > 0) {
            $this->warn("Sheet '{$sheetName}': {$skippedForeignGstin} row(s) skipped — Seller GSTIN did not match client GSTIN.");
        }
        if (!empty($transactions)) {
            $this->warn("Sheet '{$sheetName}': " . count($transactions) . " rows parsed as generic B2C data.");
        }

        return $transactions;
    }

    // ════════════════════════════════════════════════════════════════════
    //  buildColMap using hardcoded aliases (config-independent)
    // ════════════════════════════════════════════════════════════════════

    private function normKey(string $s): string
    {
        return preg_replace('/[^a-z0-9]/', '', strtolower(trim($s))) ?? '';
    }

    private function looksLikeRateHeader(string $normalizedHeader): bool
    {
        return str_contains($normalizedHeader, 'rate') || str_contains($normalizedHeader, '%');
    }

    private function buildColMapFromAliases(array $headers): array
    {
        $map         = [];
        $normalized  = array_map(fn($h) => strtolower(trim(preg_replace('/\s+/', ' ', (string)$h))), $headers);
        $normKeys    = array_map(fn($h) => $this->normKey((string)$h), $headers);
        $usedIndexes = [];

        foreach (self::COL_ALIASES as $field => $aliases) {
            $map[$field]   = -1;
            $isAmountField = in_array($field, self::AMOUNT_FIELDS, true);

            foreach ($aliases as $alias) {
                $idx = array_search(strtolower($alias), $normalized, true);
                if ($idx === false || in_array($idx, $usedIndexes, true)) continue;
                if ($isAmountField && $this->looksLikeRateHeader($normalized[$idx])) continue;
                $map[$field] = (int)$idx;
                break;
            }
            if ($map[$field] >= 0) { $usedIndexes[] = $map[$field]; continue; }

            foreach ($aliases as $alias) {
                $al = strtolower($alias);
                foreach ($normalized as $i => $h) {
                    if ($h === '' || in_array($i, $usedIndexes, true)) continue;
                    if ($isAmountField && $this->looksLikeRateHeader($h)) continue;
                    if (str_contains($h, $al) || str_contains($al, $h)) {
                        $map[$field] = $i;
                        break 2;
                    }
                }
            }
            if ($map[$field] >= 0) { $usedIndexes[] = $map[$field]; continue; }

            foreach ($aliases as $alias) {
                $alN = $this->normKey($alias);
                if ($alN === '') continue;
                foreach ($normKeys as $i => $hN) {
                    if ($hN === '' || in_array($i, $usedIndexes, true)) continue;
                    if ($isAmountField && $this->looksLikeRateHeader($normalized[$i] ?? '')) continue;
                    if (str_contains($hN, $alN) || str_contains($alN, $hN)) {
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
    //  findSheetByPatterns — contains match, case-insensitive
    // ════════════════════════════════════════════════════════════════════

    private function findSheetByPatterns(array $sheetMap, array $patterns): ?array
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

    // ════════════════════════════════════════════════════════════════════
    //  7(A)(2) — INTRA (CGST + SGST)
    // ════════════════════════════════════════════════════════════════════

    private function parse7A2(array $rows): array
    {
        if (empty($rows)) return [];

        $hIdx = $this->findHeaderRow($rows, ['taxable', 'cgst'], 1);
        if ($hIdx < 0) $hIdx = $this->findHeaderRow($rows, ['taxable'], 1);
        if ($hIdx < 0) {
            $this->warn("7(A)(2): Header row not found");
            return [];
        }

        $headers = array_map('strval', $rows[$hIdx]);
        $cm      = $this->buildColMapFromAliases($headers);

        if (($cm['taxable_value'] ?? -1) < 0) {
            $this->warn("7(A)(2): 'Taxable Value' column not found. Cols: " . implode(' | ', array_filter(array_slice($headers, 0, 10))));
            return [];
        }

        $transactions = [];
        $skippedForeignGstin = 0;
        for ($i = $hIdx + 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if ($this->rowIsEmpty($row, 1)) continue;

            // ← NEW (v9): skip rows that belong to a different Seller GSTIN
            if (($cm['gstin'] ?? -1) >= 0 && !$this->isOwnGstinRow((string)($row[$cm['gstin']] ?? ''))) {
                $skippedForeignGstin++;
                continue;
            }

            $txval = $this->num($row[$cm['taxable_value']] ?? null);
            if ($txval == 0) continue;

            $pos = $this->sellerStateCode;
            if ($pos === '') {
                $gstin = trim((string)($row[$cm['gstin'] ?? -1] ?? ''));
                if (strlen($gstin) >= 2) {
                    $pos = str_pad((string)(int)substr($gstin, 0, 2), 2, '0', STR_PAD_LEFT);
                }
            }
            if ($pos === '') {
                $this->warn("7(A)(2) row {$i}: seller state code unknown — skip");
                continue;
            }

            $cgstRate = ($cm['cgst_rate'] ?? -1) >= 0 ? $this->num($row[$cm['cgst_rate']] ?? null) : -1;
            $rate     = $cgstRate >= 0 ? round($cgstRate * 2, 2) : 0;

            $cgstAmt = ($cm['cgst_amount'] ?? -1) >= 0 ? $this->num($row[$cm['cgst_amount']] ?? null) : 0;
            $sgstAmt = ($cm['sgst_amount'] ?? -1) >= 0 ? $this->num($row[$cm['sgst_amount']] ?? null) : 0;

            if ($rate == 0 && $cgstAmt != 0 && $txval != 0) {
                $rate = round(abs($cgstAmt) / abs($txval) * 200, 2);
            } else if ($cgstAmt == 0 && $sgstAmt == 0 && $rate > 0) {
                $half    = round(abs($txval) * $rate / 200, 2);
                $cgstAmt = $txval < 0 ? -$half : $half;
                $sgstAmt = $cgstAmt;
            }

            if ($rate == 0 && $cgstAmt != 0 && $txval != 0) {
                $rate = round(abs($cgstAmt) / abs($txval) * 200, 2);
            }

            $qty = null;
            if (($cm['quantity'] ?? -1) >= 0) {
                $qtyVal = $this->num($row[$cm['quantity']] ?? null);
                $qty = $qtyVal != 0 ? (int)abs($qtyVal) : null;
            }

            $t = $this->makeTransaction(
                pos:          $pos,
                rate:         $rate,
                taxableValue: $txval,
                igstAmt:      0.0,
                cgstAmt:      $cgstAmt,
                sgstAmt:      $sgstAmt,
                qty:          $qty,
                invoiceNo:    ($cm['invoice_number'] ?? -1) >= 0 ? (string)($row[$cm['invoice_number']] ?? '') : null,
                invoiceDate:  ($cm['invoice_date'] ?? -1) >= 0 ? $this->date($row[$cm['invoice_date']] ?? null) : null,
                hsn:          ($cm['hsn_code'] ?? -1) >= 0 ? (string)($row[$cm['hsn_code']] ?? '') : null,
            );

            if (!empty($t)) $transactions[] = $t;
        }

        if ($skippedForeignGstin > 0) {
            $this->warn("7(A)(2): {$skippedForeignGstin} row(s) skipped — Seller GSTIN did not match client GSTIN.");
        }

        return $transactions;
    }

    // ════════════════════════════════════════════════════════════════════
    //  7(B)(2) — INTER (IGST)
    // ════════════════════════════════════════════════════════════════════

    private function parse7B2(array $rows): array
    {
        if (empty($rows)) return [];

        $hIdx = $this->findHeaderRow($rows, ['taxable', 'igst'], 1);
        if ($hIdx < 0) $hIdx = $this->findHeaderRow($rows, ['taxable'], 1);
        if ($hIdx < 0) {
            $this->warn("7(B)(2): Header row not found");
            return [];
        }

        $headers = array_map('strval', $rows[$hIdx]);
        $cm      = $this->buildColMapFromAliases($headers);

        if (($cm['taxable_value'] ?? -1) < 0) {
            $this->warn("7(B)(2): 'Taxable Value' column not found. Cols: " . implode(' | ', array_filter(array_slice($headers, 0, 10))));
            return [];
        }

        $transactions = [];
        $skippedForeignGstin = 0;
        for ($i = $hIdx + 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if ($this->rowIsEmpty($row, 1)) continue;

            // ← NEW (v9): skip rows that belong to a different Seller GSTIN
            if (($cm['gstin'] ?? -1) >= 0 && !$this->isOwnGstinRow((string)($row[$cm['gstin']] ?? ''))) {
                $skippedForeignGstin++;
                continue;
            }

            $txval = $this->num($row[$cm['taxable_value']] ?? null);
            if ($txval == 0) continue;

            $posRaw = null;
            if (($cm['place_of_supply'] ?? -1) >= 0) $posRaw = $row[$cm['place_of_supply']] ?? null;
            if (!$posRaw || trim((string)$posRaw) === '') {
                if (($cm['delivery_state'] ?? -1) >= 0) $posRaw = $row[$cm['delivery_state']] ?? null;
            }

            $pos = $this->resolveStateCode($posRaw);
            if ($pos === '') {
                $this->warn("7(B)(2) row {$i}: Cannot resolve state '{$posRaw}'");
                continue;
            }

            $igstAmt  = ($cm['igst_amount'] ?? -1) >= 0 ? $this->num($row[$cm['igst_amount']] ?? null) : 0;
            $igstRate = ($cm['igst_rate']   ?? -1) >= 0 ? $this->num($row[$cm['igst_rate']   ?? null]) : -1;

            if ($igstRate < 0 && $igstAmt != 0 && $txval != 0) {
                $igstRate = round(abs($igstAmt) / abs($txval) * 100, 2);
            } else if ($igstRate < 0) {
                $igstRate = 0;
            }

            if ($igstAmt == 0 && $igstRate > 0) {
                $igstAmt = round(abs($txval) * $igstRate / 100, 2);
                if ($txval < 0) $igstAmt = -$igstAmt;
            }
            if ($igstRate <= 0) continue;

            if ($igstAmt == 0) {
                $igstAmt = round(abs($txval) * $igstRate / 100, 2);
                if ($txval < 0) $igstAmt = -$igstAmt;
            }

            $qty = null;
            if (($cm['quantity'] ?? -1) >= 0) {
                $qtyVal = $this->num($row[$cm['quantity']] ?? null);
                $qty = $qtyVal != 0 ? (int)abs($qtyVal) : null;
            }

            $t = $this->makeTransaction(
                pos:          $pos,
                rate:         $igstRate,
                taxableValue: $txval,
                igstAmt:      $igstAmt,
                cgstAmt:      0.0,
                sgstAmt:      0.0,
                qty:          $qty,
                invoiceNo:    ($cm['invoice_number'] ?? -1) >= 0 ? (string)($row[$cm['invoice_number']] ?? '') : null,
                invoiceDate:  ($cm['invoice_date'] ?? -1) >= 0 ? $this->date($row[$cm['invoice_date']] ?? null) : null,
                hsn:          ($cm['hsn_code'] ?? -1) >= 0 ? (string)($row[$cm['hsn_code']] ?? '') : null,
            );

            if (!empty($t)) $transactions[] = $t;
        }

        if ($skippedForeignGstin > 0) {
            $this->warn("7(B)(2): {$skippedForeignGstin} row(s) skipped — Seller GSTIN did not match client GSTIN.");
        }

        return $transactions;
    }

    // ════════════════════════════════════════════════════════════════════
    //  5B — B2CL (large inter-state invoices)
    // ════════════════════════════════════════════════════════════════════

    private function parse5B(array $rows): array
    {
        if (empty($rows)) return [];

        $hIdx = $this->findHeaderRow($rows, ['invoice', 'taxable', 'igst'], 2);
        if ($hIdx < 0) $hIdx = $this->findHeaderRow($rows, ['taxable'], 1);
        if ($hIdx < 0) return [];

        $headers = array_map('strval', $rows[$hIdx]);
        $cm      = $this->buildColMapFromAliases($headers);
        if (($cm['taxable_value'] ?? -1) < 0) return [];

        $transactions = [];
        for ($i = $hIdx + 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if ($this->rowIsEmpty($row, 1)) continue;

            // ← NEW (v9): skip rows that belong to a different Seller GSTIN
            if (($cm['gstin'] ?? -1) >= 0 && !$this->isOwnGstinRow((string)($row[$cm['gstin']] ?? ''))) {
                continue;
            }

            $txval = $this->num($row[$cm['taxable_value']] ?? null);
            if ($txval <= 0) continue;

            $posRaw = null;
            if (($cm['place_of_supply'] ?? -1) >= 0) $posRaw = $row[$cm['place_of_supply']] ?? null;
            if (!$posRaw && ($cm['delivery_state'] ?? -1) >= 0) $posRaw = $row[$cm['delivery_state']] ?? null;

            $pos = $this->resolveStateCode($posRaw);
            if ($pos === '') continue;

            $igstAmt  = ($cm['igst_amount'] ?? -1) >= 0 ? $this->num($row[$cm['igst_amount']] ?? null) : 0;
            $igstRate = ($cm['igst_rate']   ?? -1) >= 0 ? $this->num($row[$cm['igst_rate']]   ?? null) : 0;

            if ($igstRate <= 0 && $igstAmt != 0 && $txval != 0) {
                $igstRate = round(abs($igstAmt) / abs($txval) * 100, 2);
            }
            if ($igstRate <= 0) continue;

            if ($igstAmt == 0) $igstAmt = round($txval * $igstRate / 100, 2);

            $t = $this->makeTransaction(
                pos:          $pos,
                rate:         $igstRate,
                taxableValue: $txval,
                igstAmt:      $igstAmt,
                invoiceNo:    ($cm['invoice_number'] ?? -1) >= 0 ? (string)($row[$cm['invoice_number']] ?? '') : null,
                invoiceDate:  ($cm['invoice_date'] ?? -1) >= 0 ? $this->date($row[$cm['invoice_date']] ?? null) : null,
                hsn:          ($cm['hsn_code'] ?? -1) >= 0 ? (string)($row[$cm['hsn_code']] ?? '') : null,
            );

            if (!empty($t)) $transactions[] = $t;
        }

        return $transactions;
    }

    // ════════════════════════════════════════════════════════════════════
    //  FORMAT 2: MTR Sales Report
    // ════════════════════════════════════════════════════════════════════

    private function parseMtrFormat(array $sheets): array
    {
        $transactions = [];

        // ← NEW (v9): build an Order-Item-ID → {hsn, invoice_no, invoice_date}
        // lookup from the Sales Report FIRST, so the Cash Back Report
        // (which has no HSN / Invoice Number columns of its own) can borrow
        // these details for the same order item.
        $orderItemMap = [];
        foreach ($sheets as $name => $rows) {
            if (str_contains(strtolower($name), 'sales report')) {
                $orderItemMap = array_merge($orderItemMap, $this->buildOrderItemLookup($rows));
            }
        }

        // ← NEW (v10): collect every distinct Seller GSTIN actually present
        // in the file (across both sheets) BEFORE filtering, purely so we
        // can give a precise, actionable warning if the selected/client
        // GSTIN doesn't match any of them.
        $gstinsInFile = [];
        foreach ($sheets as $name => $rows) {
            $nl = strtolower($name);
            if (str_contains($nl, 'sales report') || str_contains($nl, 'cash back') || str_contains($nl, 'cashback')) {
                $gstinsInFile = array_merge($gstinsInFile, $this->collectDistinctSellerGstins($rows));
            }
        }
        $gstinsInFile = array_values(array_unique($gstinsInFile));

        // ← NEW (v11): collect the Invoice Numbers actually kept for THIS
        // client (i.e. after the Seller-GSTIN filter, so a different
        // registration's numbers don't pollute the From/To range), purely
        // to build the Document Issued (doc_issue_override) block for
        // Sales Report / MTR uploads.
        $keptInvoiceNumbers = [];

        foreach ($sheets as $name => $rows) {
            if (str_contains(strtolower($name), 'sales report')) {
                $transactions = array_merge(
                    $transactions,
                    $this->parseMtrSalesSheet($rows, $keptInvoiceNumbers)
                );
            }
        }

        foreach ($sheets as $name => $rows) {
            $nl = strtolower($name);
            if (str_contains($nl, 'cash back') || str_contains($nl, 'cashback')) {
                $transactions = array_merge($transactions, $this->parseMtrCashbackSheet($rows, $orderItemMap));
            }
        }

        // ← v12 (UNCHANGED in v13): pass suppressSingletons = true here —
        // this is the MTR / Sales Report path. Any invoice-number prefix
        // that appears only once (no real series/range) is dropped from
        // Document Issued entirely, instead of showing a "1 / 0 / 1" row.
        // This part already works correctly and is not touched by the
        // v13 Section 13 fix.
        $docIssueOverride = !empty($keptInvoiceNumbers)
            ? $this->buildDocIssuedFromInvoiceNumbers($keptInvoiceNumbers, true)
            : [];
        if (empty($keptInvoiceNumbers)) {
            $this->warn("MTR Sales Report: no Invoice Number values found — Document Issued not calculated for this upload.");
        }

        if (empty($transactions)) {
            $client = $this->normGstin($this->clientGstin ?? '');
            if ($client !== '' && !empty($gstinsInFile) && !in_array($client, $gstinsInFile, true)) {
                // ← NEW (v10): selected GSTIN doesn't appear in this file at
                // all — tell the user exactly which GSTINs ARE in the file,
                // instead of a generic "no transactions" message.
                $this->warn("Selected GSTIN '{$this->clientGstin}' ka is file me EK BHI transaction nahi mila. "
                    . "File me yeh Seller GSTIN(s) hain: [" . implode(', ', $gstinsInFile) . "]. "
                    . "Confirm karein ki sahi GSTIN select kiya hai, ya sahi file upload hui hai.");
            } else {
                $this->warn("MTR format: No transactions found in Sales Report / Cash Back Report.");
            }
        }

        return [
            'transactions'        => array_values(array_filter($transactions)),
            'warnings'            => $this->warnings,
            'file_type'           => 'flipkart_mtr',
            'doc_issue_override'  => $docIssueOverride,
            'hsn_override'        => [],
        ];
    }

    // ← NEW (v10): quick scan of a sheet's Seller GSTIN column, just to
    // report which GSTIN(s) exist in the file (used only for the
    // "selected GSTIN not found" warning above — does no filtering itself).
    private function collectDistinctSellerGstins(array $rows): array
    {
        if (empty($rows)) return [];

        $hIdx = $this->findHeaderRow($rows, ['seller gstin'], 1);
        if ($hIdx < 0) return [];

        $headers = array_map('strval', $rows[$hIdx]);
        $lower   = array_map(fn($h) => strtolower(trim($h)), $headers);
        $col     = array_search('seller gstin', $lower, true);
        if ($col === false) return [];

        $found = [];
        for ($i = $hIdx + 1; $i < count($rows); $i++) {
            $g = $this->normGstin((string)($rows[$i][$col] ?? ''));
            if ($g !== '') $found[$g] = true;
        }

        return array_keys($found);
    }

    // ← NEW (v9): scan the Sales Report once and index every row by its
    // Order Item ID (falling back to Order ID) so Cash Back rows can look
    // up HSN / Invoice Number / Invoice Date for the same order item.
    private function buildOrderItemLookup(array $rows): array
    {
        if (empty($rows)) return [];

        $hIdx = $this->findHeaderRow($rows, ['seller gstin', 'event type', 'taxable'], 2);
        if ($hIdx < 0) $hIdx = $this->findHeaderRow($rows, ['event type', 'taxable'], 1);
        if ($hIdx < 0) return [];

        $headers = array_map('strval', $rows[$hIdx]);
        $cm      = $this->buildMtrColMap($headers);

        $map = [];
        for ($i = $hIdx + 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if ($this->rowIsEmpty($row, 1)) continue;

            $orderItemId = trim((string)($row[$cm['order_item_id'] ?? -1] ?? ''));
            $orderId     = trim((string)($row[$cm['order_id'] ?? -1] ?? ''));
            $key         = $orderItemId !== '' ? $orderItemId : $orderId;
            if ($key === '') continue;

            $entry = [
                'hsn'          => ($cm['hsn_code'] ?? -1)     >= 0 ? trim((string)($row[$cm['hsn_code']]     ?? '')) : '',
                'invoice_no'   => ($cm['invoice_no'] ?? -1)   >= 0 ? trim((string)($row[$cm['invoice_no']]   ?? '')) : '',
                'invoice_date' => ($cm['invoice_date'] ?? -1) >= 0 ? ($row[$cm['invoice_date']] ?? null) : null,
            ];

            // Prefer entries that actually carry an HSN/invoice number over
            // a blank one, in case the same order item appears twice
            // (e.g. Sale + Return rows).
            if (!isset($map[$key]) || ($map[$key]['hsn'] === '' && $entry['hsn'] !== '')) {
                $map[$key] = $entry;
            }
            if ($orderItemId !== '' && $orderId !== '' && !isset($map[$orderId])) {
                $map[$orderId] = $entry; // also index by Order ID as a fallback key
            }
        }

        return $map;
    }

    // ← UPDATED (v11): accepts $keptInvoiceNumbers by reference and
    // appends the Invoice Number of every row that SURVIVES the
    // Seller-GSTIN filter (i.e. actually belongs to this client), so the
    // caller can build a proper Document Issued (From/To/Cancel/Net) block
    // from real, filtered data — not mixed with another registration's
    // invoice numbers.
    private function parseMtrSalesSheet(array $rows, array &$keptInvoiceNumbers = []): array
    {
        if (empty($rows)) return [];

        $hIdx = $this->findHeaderRow($rows, ['seller gstin', 'event type', 'taxable'], 2);
        if ($hIdx < 0) $hIdx = $this->findHeaderRow($rows, ['event type', 'taxable'], 1);
        if ($hIdx < 0) {
            $this->warn("MTR Sales Report: Header row not found");
            return [];
        }

        $headers = array_map('strval', $rows[$hIdx]);
        $cm      = $this->buildMtrColMap($headers);

        $transactions = [];
        $skippedForeignGstin = 0;
        for ($i = $hIdx + 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if ($this->rowIsEmpty($row, 1)) continue;

            // ← NEW (v9): skip rows belonging to a Seller GSTIN other than
            // the current client's own GSTIN (file may contain multiple
            // state-registrations' data mixed together).
            if (!$this->isOwnGstinRow((string)($row[$cm['seller_gstin'] ?? -1] ?? ''))) {
                $skippedForeignGstin++;
                continue;
            }

            // ← NEW (v11): collect this row's invoice number for the
            // Document Issued calculation, regardless of event type.
            $invNoRaw = ($cm['invoice_no'] ?? -1) >= 0 ? trim((string)($row[$cm['invoice_no']] ?? '')) : '';
            if ($invNoRaw !== '') $keptInvoiceNumbers[] = $invNoRaw;

            $eventType = strtolower(trim((string)($row[$cm['event_type'] ?? -1] ?? '')));
            if ($eventType === '' || str_contains($eventType, 'cancel')) continue;

            $t = $this->buildMtrTransaction($row, $cm, false);
            if (!empty($t)) $transactions[] = $t;
        }

        if ($skippedForeignGstin > 0) {
            $this->warn("MTR Sales Report: {$skippedForeignGstin} row(s) skipped — Seller GSTIN did not match client GSTIN (data for a different registration).");
        }

        return $transactions;
    }

    private function parseMtrCashbackSheet(array $rows, array $orderItemMap = []): array
    {
        if (empty($rows)) return [];

        $hIdx = $this->findHeaderRow($rows, ['seller gstin', 'taxable'], 1);
        if ($hIdx < 0) $hIdx = $this->findHeaderRow($rows, ['taxable'], 1);
        if ($hIdx < 0) return [];

        $headers = array_map('strval', $rows[$hIdx]);
        $cm      = $this->buildMtrColMap($headers);

        $transactions = [];
        $skippedForeignGstin = 0;
        for ($i = $hIdx + 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if ($this->rowIsEmpty($row, 1)) continue;

            // ← NEW (v9): same Seller GSTIN filter as the Sales Report —
            // Cash Back Report can also contain rows for a different
            // registration's GSTIN.
            if (!$this->isOwnGstinRow((string)($row[$cm['seller_gstin'] ?? -1] ?? ''))) {
                $skippedForeignGstin++;
                continue;
            }

            $t = $this->buildMtrTransaction($row, $cm, false, $orderItemMap);
            if (!empty($t)) $transactions[] = $t;
        }

        if ($skippedForeignGstin > 0) {
            $this->warn("MTR Cash Back Report: {$skippedForeignGstin} row(s) skipped — Seller GSTIN did not match client GSTIN (data for a different registration).");
        }

        return $transactions;
    }

    private function buildMtrTransaction(array $row, array $cm, bool $isReturn, array $orderItemMap = []): array
    {
        $txval = $this->num($row[$cm['taxable_value'] ?? -1] ?? null);
        if ($txval == 0) return [];
        if ($isReturn && $txval > 0) $txval = -$txval;

        $stateRaw = $row[$cm['delivery_state'] ?? -1] ?? null;
        if (!$stateRaw || trim((string)$stateRaw) === '') {
            $stateRaw = $row[$cm['billing_state'] ?? -1] ?? null;
        }
        $pos = $this->resolveStateCode($stateRaw);
        if ($pos === '') {
            $gstin = trim((string)($row[$cm['seller_gstin'] ?? -1] ?? ''));
            $pos   = $this->sellerStateCode ?: (strlen($gstin) >= 2
                ? str_pad((string)(int)substr($gstin, 0, 2), 2, '0', STR_PAD_LEFT)
                : '');
        }
        if ($pos === '') return [];

        $igstAmt  = $this->num($row[$cm['igst_amount'] ?? -1] ?? null);
        $cgstAmt  = $this->num($row[$cm['cgst_amount'] ?? -1] ?? null);
        $sgstAmt  = $this->num($row[$cm['sgst_amount'] ?? -1] ?? null);
        $igstRate = $this->num($row[$cm['igst_rate']   ?? -1] ?? null);
        $cgstRate = $this->num($row[$cm['cgst_rate']   ?? -1] ?? null);
        $rate     = $igstRate > 0 ? $igstRate : round($cgstRate * 2, 2);

        if ($rate == 0 && ($igstAmt != 0 || $cgstAmt != 0)) {
            $rate = $igstAmt != 0
                ? round(abs($igstAmt) / abs($txval) * 100, 2)
                : round(abs($cgstAmt) / abs($txval) * 200, 2);
        }

        // ← NEW (v9): HSN Code / Invoice Number direct from this sheet's
        // own columns (Sales Report has these). If this sheet doesn't
        // have them (Cash Back Report), fall back to the Order-Item-ID
        // lookup built from the Sales Report.
        $hsn        = ($cm['hsn_code'] ?? -1)   >= 0 ? trim((string)($row[$cm['hsn_code']]   ?? '')) : '';
        $invoiceNo  = ($cm['invoice_no'] ?? -1) >= 0 ? trim((string)($row[$cm['invoice_no']] ?? '')) : '';
        $invoiceDt  = ($cm['invoice_date'] ?? -1) >= 0 ? ($row[$cm['invoice_date']] ?? null) : null;

        if (($hsn === '' || $invoiceNo === '') && !empty($orderItemMap)) {
            $orderItemId = trim((string)($row[$cm['order_item_id'] ?? -1] ?? ''));
            $orderId     = trim((string)($row[$cm['order_id'] ?? -1] ?? ''));
            $lookupKey   = $orderItemId !== '' && isset($orderItemMap[$orderItemId]) ? $orderItemId : $orderId;

            if ($lookupKey !== '' && isset($orderItemMap[$lookupKey])) {
                $entry = $orderItemMap[$lookupKey];
                if ($hsn === '')       $hsn       = $entry['hsn'] ?? '';
                if ($invoiceNo === '') $invoiceNo = $entry['invoice_no'] ?? '';
                if ($invoiceDt === null) $invoiceDt = $entry['invoice_date'] ?? null;
            }
        }

        // Cash Back Report has no true invoice number of its own — if we
        // still don't have one, use the Credit/Debit Note ID as the
        // document reference so the transaction isn't left blank.
        if ($invoiceNo === '' && ($cm['credit_debit_note_id'] ?? -1) >= 0) {
            $invoiceNo = trim((string)($row[$cm['credit_debit_note_id']] ?? ''));
        }

        return $this->makeTransaction(
            pos:          $pos,
            rate:         $rate,
            taxableValue: $txval,
            igstAmt:      $igstAmt,
            cgstAmt:      $cgstAmt,
            sgstAmt:      $sgstAmt,
            invoiceNo:    $invoiceNo !== '' ? $invoiceNo : null,
            invoiceDate:  $invoiceDt !== null ? $this->date($invoiceDt) : null,
            qty:          max(1, (int)$this->num($row[$cm['quantity'] ?? -1] ?? 1)),
            hsn:          $hsn !== '' ? $hsn : null,
            buyerGstin:   ($cm['buyer_gstin'] ?? -1)  >= 0 ? (string)($row[$cm['buyer_gstin']] ?? '') : null,
        );
    }

    // ── MTR-specific column map (exact Flipkart MTR header names) ──
    private function buildMtrColMap(array $headers): array
    {
        $map   = [];
        $lower = array_map(fn($h) => strtolower(trim($h)), $headers);

        $lookup = [
            'seller_gstin'  => ['seller gstin'],
            'event_type'    => ['event type'],
            'order_id'      => ['order id'],
            'order_item_id' => ['order item id'], // ← NEW (v9): join key for Sales↔Cashback HSN/invoice lookup
            'invoice_no'    => ['buyer invoice id', 'invoice number', 'invoice no'],
            'invoice_date'  => ['buyer invoice date', 'invoice date'],
            'quantity'      => ['item quantity', 'quantity', 'qty'],
            'hsn_code'      => ['hsn code', 'hsn'],
            'taxable_value' => ['taxable value (final invoice amount -taxes)', 'taxable value', 'net taxable value'],
            'igst_rate'     => ['igst rate'],
            'igst_amount'   => ['igst amount'],
            'cgst_rate'     => ['cgst rate'],
            'cgst_amount'   => ['cgst amount'],
            'sgst_rate'     => ['sgst rate (or utgst as applicable)', 'sgst rate'],
            'sgst_amount'   => ['sgst amount (or utgst as applicable)', 'sgst amount'],
            'delivery_state'=> ["customer's delivery state", 'delivery state', 'customer delivery state', 'ship to state'],
            'billing_state' => ["customer's billing state", 'billing state'],
            'buyer_gstin'   => ['business gst number', 'buyer gstin'],
            // ← NEW (v9): Cash Back Report's only per-row document
            // reference — used as invoice-number fallback when no real
            // invoice number can be found via the Order-Item-ID lookup.
            'credit_debit_note_id' => ['credit note id/ debit note id', 'credit note id/debit note id'],
        ];

        foreach ($lookup as $field => $aliases) {
            $map[$field] = -1;
            foreach ($aliases as $alias) {
                $idx = array_search(strtolower($alias), $lower, true);
                if ($idx !== false) { $map[$field] = $idx; break; }
            }
            if ($map[$field] < 0) {
                foreach ($aliases as $alias) {
                    foreach ($lower as $i => $h) {
                        if ($h !== '' && str_contains($h, strtolower($alias))) {
                            $map[$field] = $i;
                            break 2;
                        }
                    }
                }
            }
        }

        return $map;
    }
}