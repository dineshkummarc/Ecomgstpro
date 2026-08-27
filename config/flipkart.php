<?php

/**
 * ══════════════════════════════════════════════════════════════════════
 *  FLIPKART COLUMN MAPPING CONFIG
 * ══════════════════════════════════════════════════════════════════════
 *
 *  Flipkart sends a multi-sheet GSTR-1 ready Excel file.
 *
 *  REQUIRED SHEETS:
 *  ┌──────────────────────────────────┬──────────────────────────────────┐
 *  │ Sheet Name (exact)               │ Content                          │
 *  ├──────────────────────────────────┼──────────────────────────────────┤
 *  │ Section 7(A)(2) in GSTR-1        │ INTRA state B2CS (CGST + SGST)   │
 *  │ Section 7(B)(2) in GSTR-1        │ INTER state B2CS (IGST)          │
 *  ├──────────────────────────────────┼──────────────────────────────────┤
 *  │ Section 5B in GSTR-1             │ B2CL large invoices (optional)   │
 *  │ Section 12 in GSTR-1             │ HSN summary (optional)           │
 *  │ Section 3 in GSTR-8              │ TCS data (optional)              │
 *  └──────────────────────────────────┴──────────────────────────────────┘
 *
 *  Also supports Shopsy (Flipkart's social commerce) — same format.
 * ══════════════════════════════════════════════════════════════════════
 */

return [

    // ── Detection ───────────────────────────────────────────────────────
    'detect_by_filename' => [
        'flipkart', 'fk_', 'fkm', 'flipkrt', 'flikart', 'flpkart',
        'shopsy',
    ],

    'detect_by_sheet' => [
        'section 7(a)(2)', 'section 7(b)(2)', 'section 5b',
        'section 12 in gstr-1', 'section 3 in gstr-8',
        'gstr-1', '7a2', '7b2',
    ],

    // ── Sheet Routing ────────────────────────────────────────────────────
    'sheets' => [
        // INTRA — CGST + SGST
        'b2cs_intra' => [
            'section 7(a)(2) in gstr-1',
            'section 7(a)(2)',
            '7(a)(2)',
            '7a(2)',
            '7a2',
            'intra state',
            'intra-state b2cs',
        ],

        // INTER — IGST
        'b2cs_inter' => [
            'section 7(b)(2) in gstr-1',
            'section 7(b)(2)',
            '7(b)(2)',
            '7b(2)',
            '7b2',
            'inter state',
            'inter-state b2cs',
        ],

        // Large invoices (B2CL)
        'b2cl' => [
            'section 5b in gstr-1',
            'section 5b',
            '5b',
            'b2cl',
            'large invoices',
        ],

        // HSN Summary
        'hsn' => [
            'section 12 in gstr-1',
            'section 12',
            'hsn summary',
            'hsn',
        ],

        // TCS (Tax Collected at Source)
        'tcs' => [
            'section 3 in gstr-8',
            'section 3',
            'gstr-8',
            'tcs',
        ],
    ],

    // ── Column Aliases ───────────────────────────────────────────────────
    'columns' => [

        // ── 7(A)(2) INTRA sheet columns ───────────────────────────────
        'gstin' => [
            'gstin',
            'gstin number',
            'seller gstin',
            'your gstin',
        ],

        'taxable_value' => [
            'aggregate taxable value rs.',
            'aggregate taxable value',
            'aggregate taxable value (rs.)',
            'taxable value rs.',
            'taxable value',
            'taxable amt',
            'net taxable value',
            'total taxable value',
        ],

        'cgst_rate' => [
            'cgst %',
            'cgst%',
            'cgst rate',
            'cgst rate (%)',
            'central tax rate',
            'cgst rate(%)',
        ],

        'cgst_amount' => [
            'cgst amount rs.',
            'cgst amount',
            'cgst amount (rs.)',
            'central tax amount rs.',
            'central tax amount',
            'cgst amt',
        ],

        'sgst_rate' => [
            'sgst/ut %',
            'sgst %',
            'sgst rate',
            'state tax rate',
            'sgst/ut rate',
            'sgst rate (%)',
        ],

        'sgst_amount' => [
            'sgst /ut amount rs.',
            'sgst amount rs.',
            'sgst amount',
            'sgst/ut amount rs.',
            'sgst/ut amount',
            'state tax amount rs.',
            'state tax amount',
            'sgst amt',
        ],

        // ── 7(B)(2) INTER sheet columns ────────────────────────────────
        'igst_rate' => [
            'igst %',
            'igst%',
            'igst rate',
            'igst rate (%)',
            'integrated tax rate',
            'igst rate(%)',
        ],

        'igst_amount' => [
            'igst amount rs.',
            'igst amount',
            'igst amount (rs.)',
            'integrated tax amount rs.',
            'integrated tax amount',
            'igst amt',
        ],

        'place_of_supply' => [
            'delivered state (pos)',
            'delivered state code',
            'place of supply',
            'pos',
            'state',
            'delivery state',
            'customer state',
            'buyer state',
            'ship to state',
        ],

        // ── 5B / B2CL columns ──────────────────────────────────────────
        'invoice_number' => [
            'invoice number',
            'invoice no',
            'invoice no.',
            'invoice_number',
            'inv no',
            'bill number',
        ],

        'invoice_date' => [
            'invoice date',
            'invoice dt',
            'date',
            'invoice_date',
            'bill date',
        ],

        'invoice_value' => [
            'invoice value rs.',
            'invoice value',
            'invoice total',
            'total invoice value',
            'bill value',
        ],

        'quantity' => [
            'total quantity in nos.',
            'total quantity',
            'quantity',
            'qty',
            'no. of items',
            'no of items',
        ],

        // ── HSN Sheet ──────────────────────────────────────────────────
        'hsn_code' => [
            'hsn number',
            'hsn/sac number',
            'hsn code',
            'hsn',
            'hsn/sac',
        ],

        'hsn_description' => [
            'description',
            'product description',
            'goods description',
        ],

        'uqc' => [
            'unit',
            'uqc',
            'unit of measurement',
        ],

        // ── TCS Sheet (GSTR-8) ────────────────────────────────────────
        'net_taxable_value' => [
            'net taxable value',
            'net value',
            'taxable value',
        ],

        'tcs_rate' => [
            'tcs %',
            'tcs rate',
            'rate of tcs',
            'tcs_rate',
        ],

        'tcs_igst' => [
            'tcs igst amount rs.',
            'tcs igst amount',
            'igst tcs amount',
        ],

        'tcs_cgst' => [
            'tcs cgst amount rs.',
            'tcs cgst amount',
            'cgst tcs amount',
        ],

        'tcs_sgst' => [
            'tcs sgst amount rs.',
            'tcs sgst amount',
            'sgst tcs amount',
        ],
    ],

    // ── Defaults ─────────────────────────────────────────────────────────
    'defaults' => [
        'quantity'    => 1,
        'cess_amount' => 0,
        'supply_type' => 'B2CS',
    ],

    // ── Validation ───────────────────────────────────────────────────────
    'required_columns' => [
        'taxable_value',
    ],
];
