<?php

/**
 * ══════════════════════════════════════════════════════════════════════
 *  AMAZON COLUMN MAPPING CONFIG
 * ══════════════════════════════════════════════════════════════════════
 *
 *  ⚠ IMPORTANT — READ BEFORE EDITING THIS FILE ⚠
 *  ──────────────────────────────────────────────
 *  This file is documentation / a reference of the column names Amazon
 *  reports use. It is loaded into BaseParser's `$this->cfg` via the
 *  constructor merge in AmazonParser:
 *
 *      foreach (self::COLS as $field => $aliases) {
 *          if (empty($this->cfg['columns'][$field])) {
 *              $this->cfg['columns'][$field] = $aliases;
 *          }
 *      }
 *
 *  BUT the actual column-detection logic in AmazonParser.php does NOT read
 *  `$this->cfg['columns']` when mapping a sheet's headers:
 *    - buildAmazonColMap() (used for the GSTR-1 style B2C Small/B2C Large/
 *      B2B named-sheet workbook) iterates the class's own hardcoded
 *      `self::COLS` constant, never this config array.
 *    - detectDetailedMtrColumns() / parseDetailedMtrRows() (used for the
 *      real per-transaction MTR B2B/B2C CSV export, v6) do an independent
 *      exact-header-name lookup and don't consult self::COLS or this file
 *      either.
 *  So editing the alias lists below will NOT change what the parser
 *  actually does — the source of truth for column aliases lives in
 *  AmazonParser.php itself. This file is kept in sync as a reference /
 *  in case other tooling reads it, but it is not on the parsing hot path.
 *  If you need to change how a column is detected, edit AmazonParser.php.
 *
 *  Supported Amazon report types (as actually implemented in the parser):
 *
 *  1. GSTR-1 Ready File (Amazon-generated, combined workbook)
 *     Sheet: "B2C Small" — Columns: Type, Place Of Supply, Applicable % of Tax Rate, Rate, Taxable Value, Cess Amount, E-Commerce GSTIN
 *     Sheet: "B2C Large" — Columns: Invoice Number, Invoice date, Invoice Value, Place Of Supply, Applicable % of Tax Rate, Rate, Taxable Value, Cess Amount
 *     Sheet: "B2B"       — Columns: GSTIN/UIN of Recipient, Receiver Name, Invoice Number, Invoice date, Invoice Value, Place Of Supply, Reverse Charge, Applicable % of Tax Rate, Invoice Type, E-Commerce GSTIN, Rate, Taxable Value, Cess Amount
 *     Detected when the workbook contains these real Amazon sheet names — file_type = 'amazon_gstr1'.
 *
 *  2. MTR (Merchant Tax Report) — older summary-style format
 *     Sheet: "B2C Small" or "B2C(Small)" — has Invoice Number, Invoice Date, Quantity
 *     Sheet: "B2B" or "B2B Invoices"
 *     Sheet: "B2C Large" or "B2CL CN (cdnur)"
 *
 *  3. Detailed MTR B2B/B2C CSV export (v6, current real-world Amazon download)
 *     Single unnamed sheet (e.g. "Sheet1") — one file per role (B2B or B2C),
 *     uploaded into the matching UI slot so `file_role` tells the parser
 *     which one it is. Key columns: Seller Gstin, Invoice Number,
 *     Invoice Date, Ship To State (place of supply), Tax Exclusive Gross
 *     (taxable value), Total Tax Amount (net tax, already promo-adjusted),
 *     Cgst Rate / Sgst Rate / Utgst Rate / Igst Rate, Hsn/sac, Quantity,
 *     Customer Bill To Gstid / Customer Ship To Gstid (buyer GSTIN, B2B
 *     only). file_type = 'amazon_mtr_b2c' or 'amazon_mtr_b2b'.
 *     See AmazonParser::detectDetailedMtrColumns() / parseDetailedMtrRows()
 *     for the exact, independent header-matching + calculation logic.
 *
 *  NOTE: In GSTR-1 B2C Small, there is NO Invoice Number / Date / Quantity.
 *        The parser detects format automatically and does not default qty to 1.
 *
 *  Each key maps to an array of possible header names (case-insensitive).
 *  Where the parser DOES consult alias lists (self::COLS in AmazonParser.php,
 *  for the GSTR-1/old-MTR path only), it tries each alias in order — first
 *  match wins.
 * ══════════════════════════════════════════════════════════════════════
 */

return [

    // ── Detection ───────────────────────────────────────────────────────
    'detect_by_filename' => [
        'amazon', 'amzn', 'mtr', 'merchant_tax', 'merchant tax', 'gstr1',
    ],

    'detect_by_sheet' => [
        'b2c small', 'b2c(small)', 'b2b invoices', 'b2c large', 'b2cl',
        'marketplace facilitator', 'b2b', 'hsn summary',
    ],

    // ── Sheet Routing (GSTR-1 / old-MTR named-sheet workbook only) ──────
    'sheets' => [
        'b2c_small' => [
            'b2c small', 'b2c(small)', 'small b2c', 'b2cs', 'b2c small',
        ],
        'b2b' => [
            'b2b', 'b2b invoices', 'business to business', 'registered',
        ],
        'b2c_large' => [
            'b2c large', 'b2cl', 'b2c(large)',
            'b2cl cn (cdnur)', 'cdnur', 'b2c large invoice',
        ],
    ],

    // ── Column Aliases ───────────────────────────────────────────────────
    // Reference only — see file-level note above. Not consulted by
    // buildAmazonColMap() or the v6 detailed-MTR detection/parsing.
    'columns' => [

        'place_of_supply' => [
            'place of supply',
            'place_of_supply',
            'pos',
            'delivery state',
            'customer state',
            'ship to state',           // detailed MTR CSV (v6)
            'shipping state',
            'buyer state',
            'destination state',
            'state of supply',
            'delivered state',
        ],

        // NOTE: Amazon GSTR-1 has TWO rate columns:
        //   "Applicable % of Tax Rate" — often blank/empty
        //   "Rate"                     — actual decimal value (0.18 = 18%)
        // We list "rate" first so it wins over the blank "applicable" column.
        // The detailed MTR CSV (v6) instead has 4 separate rate columns
        // (Cgst Rate / Sgst Rate / Utgst Rate / Igst Rate) that the parser
        // sums and splits itself — listed here for reference only.
        'gst_rate' => [
            'rate',
            'gst rate',
            'gst%',
            'tax rate',
            'igst rate',                // detailed MTR CSV (v6)
            'cgst rate',                 // detailed MTR CSV (v6)
            'sgst rate',                 // detailed MTR CSV (v6)
            'utgst rate',                // detailed MTR CSV (v6)
            'applicable % of tax rate',
            'applicable tax rate',
            'rate (%)',
            'rate(%)',
            'gst_rate',
            'tax_rate',
        ],

        'taxable_value' => [
            'taxable value',
            'taxable_value',
            'taxable amount',
            'net taxable value',
            'invoice taxable value',
            'item taxable value',
            'total taxable value',
            'taxable amt',
            'base amount',
            'tax exclusive gross',      // detailed MTR CSV (v6) — already promo-netted
        ],

        'igst_amount' => [
            'igst',
            'igst amount',
            'integrated tax amount',
            'igst_amount',
            'igst amt',
        ],

        'cgst_amount' => [
            'cgst',
            'cgst amount',
            'central tax amount',
            'cgst_amount',
            'cgst amt',
        ],

        'sgst_amount' => [
            'sgst',
            'sgst amount',
            'state tax amount',
            'sgst_amount',
            'sgst amt',
            'ut tax amount',
            'utgst amount',
            'state/ut tax amount',
        ],

        // The detailed MTR CSV (v6) does NOT use these three amount fields
        // directly — it computes them from 'total_tax_amount' below split
        // by which rate column is populated, to keep tax figures promo-
        // netted and consistent with taxable_value. See
        // AmazonParser::parseDetailedMtrRows() for the exact logic.
        'total_tax_amount' => [
            'total tax amount',          // detailed MTR CSV (v6) — already promo-netted
        ],

        'invoice_number' => [
            'invoice number',
            'invoice_number',
            'invoice no',
            'invoice no.',
            'inv number',
            'inv no',
            'bill number',
            'shipment id',
        ],

        'invoice_date' => [
            'invoice date',
            'invoice_date',
            'date',
            'order date',
            'bill date',
            'transaction date',
            'invoice dt',
        ],

        'invoice_value' => [
            'invoice value',
            'invoice_value',
            'invoice amount',
            'total invoice value',
            'bill amount',
            'grand total',
        ],

        // NOTE: quantity is absent in GSTR-1 B2C Small.
        // Parser sets qty = null when this column is absent (never defaults to 1).
        'quantity' => [
            'no. of items',
            'no of items',
            'quantity',                  // also detailed MTR CSV (v6), exact match "Quantity"
            'qty',
            'item count',
            'units',
            'total quantity',
        ],

        // B2B specific
        'buyer_gstin' => [
            'gstin/uin of recipient',
            'gstin',
            'buyer gstin',
            'customer gstin',
            'party gstin',
            'receiver gstin',
            'buyer_gstin',
            'recipient gstin',
            'customer bill to gstid',    // detailed MTR CSV (v6), B2B only
            'customer ship to gstid',    // detailed MTR CSV (v6), B2B only, fallback
        ],

        'hsn_code' => [
            'hsn',
            'hsn code',
            'hsn/sac',                   // also detailed MTR CSV (v6), exact match "Hsn/sac"
            'hsn_code',
            'hsn sac',
            'hsn/sac code',
            'product code',
            'tariff code',
        ],

        'supply_type' => [
            'type',
            'supply type',
            'type of supply',
            'invoice type',
            'transaction type',
            'note supply type',
        ],

        'cess_amount' => [
            'cess',
            'cess amount',
            'compensation cess',
            'cess amt',
        ],
    ],

    // ── Defaults ─────────────────────────────────────────────────────────
    // qty is intentionally NOT defaulted here — parser sets null when column absent
    'defaults' => [
        'supply_type' => 'B2C',
        'cess_amount' => 0,
    ],

    // ── Validation ───────────────────────────────────────────────────────
    'required_columns' => [
        'taxable_value',
    ],
    'b2c_indicator_columns' => [
        'place_of_supply',
        'gst_rate',
    ],
];