<?php

/**
 * ══════════════════════════════════════════════════════════════════════
 *  MEESHO COLUMN MAPPING CONFIG
 * ══════════════════════════════════════════════════════════════════════
 *
 *  Meesho provides 2-3 separate Excel files (TCS format):
 *
 *  FILE 1 — TCS Sales Report (REQUIRED)
 *  ┌────────────────────────────────────────────────────────────────┐
 *  │ identifier, sup_name, gstin, sub_order_num, order_date,       │
 *  │ hsn_code, quantity, gst_rate, total_taxable_sale_value,       │
 *  │ tax_amount, total_invoice_value, taxable_shipping,            │
 *  │ end_customer_state_new, financial_year, month_number          │
 *  └────────────────────────────────────────────────────────────────┘
 *
 *  FILE 2 — TCS Sales Return Report (REQUIRED for accuracy)
 *  ┌────────────────────────────────────────────────────────────────┐
 *  │ Same columns as Sales + cancel_return_date                    │
 *  │ Values must be treated as NEGATIVE taxable amounts            │
 *  └────────────────────────────────────────────────────────────────┘
 *
 *  FILE 3 — Tax Invoice (OPTIONAL, for document summary)
 *  ┌────────────────────────────────────────────────────────────────┐
 *  │ order_id, invoice_number, invoice_date, buyer_gstin, etc.     │
 *  └────────────────────────────────────────────────────────────────┘
 *
 *  ⚠️ FIX: 'order_number' alias list was DIFFERENT (shorter) here than
 *  in MeeshoTaxInvoiceParser::buildTaxInvoiceColMap(). Because Sales and
 *  Return parsers read their column-map aliases from THIS file, while
 *  the Tax Invoice parser had its own hardcoded (longer) alias list, the
 *  actual "Sub Order No" style column in the Sales/Return files was
 *  sometimes not detected at all — leaving order_number = null on every
 *  transaction. That silently broke the Tax Invoice merge (no match
 *  possible), even though the Tax Invoice file's own order_number column
 *  was being read fine. The alias list below is now the same superset
 *  used everywhere, so all three files key off the same set of possible
 *  header names.
 * ══════════════════════════════════════════════════════════════════
 */

return [

    // ── Detection ───────────────────────────────────────────────────────
    'detect_by_filename' => [
        'meesho', 'meso', 'meeshu',
        'tcs_sale', 'tcs_return', 'tcs_sales',
    ],

    // These column names uniquely identify a Meesho TCS file
    'detect_by_columns' => [
        'total_taxable_sale_value',
        'end_customer_state_new',
        'sub_order_num',
    ],

    // ── File Role Detection ──────────────────────────────────────────────
    // If this column exists → it's a RETURN file
    'return_indicator_column' => 'cancel_return_date',

    // ── Sheet Routing ────────────────────────────────────────────────────
    'sheets' => [
        'sales' => [
            'sales', 'tcs sales', 'tcs_sales',
            'sheet1', 'sheet 1', 'data',
        ],
        'return' => [
            'return', 'tcs return', 'tcs_return', 'returns',
            'cancel', 'refund',
        ],
        'invoice' => [
            'tax invoice', 'invoice', 'invoices', 'tax_invoice',
        ],
    ],

    // ── Column Aliases ───────────────────────────────────────────────────
    'columns' => [

        // ── REQUIRED columns ──────────────────────────────────────────
        'gstin' => [
            'gstin',
            'seller gstin',
            'supplier gstin',
            'your gstin',
        ],

        // ⚠️ FIX: expanded to match MeeshoTaxInvoiceParser's alias list
        // exactly, so Sales, Return, and Tax Invoice files all resolve
        // order_number from the same set of possible header names.
        'order_number' => [
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
            'order_id',
            'shipment id',
        ],

        'taxable_value' => [
            'total_taxable_sale_value',
            'total taxable sale value',
            'taxable sale value',
            'taxable value',
            'taxable_value',
            'net taxable value',
            'base value',
        ],

        'gst_rate' => [
            'gst_rate',
            'gst rate',
            'tax rate',
            'rate',
            'gst%',
            'applicable gst rate',
        ],

        'place_of_supply' => [
            'end_customer_state_new',
            'end customer state new',
            'end customer state',
            'customer state',
            'delivery state',
            'ship to state',
            'buyer state',
            'place of supply',
            'pos',
        ],

        // ── OPTIONAL but useful ────────────────────────────────────────
        'hsn_code' => [
            'hsn_code',
            'hsn code',
            'hsn',
            'hsn/sac',
            'product code',
        ],

        'quantity' => [
            'quantity',
            'qty',
            'units',
            'no. of items',
        ],

        'order_date' => [
            'order_date',
            'order date',
            'sale date',
            'date',
            'invoice date',
        ],

        'invoice_value' => [
            'total_invoice_value',
            'total invoice value',
            'invoice value',
            'invoice amount',
            'final amount',
        ],

        'tax_amount' => [
            'tax_amount',
            'tax amount',
            'total tax',
            'gst amount',
        ],

        'taxable_shipping' => [
            'taxable_shipping',
            'taxable shipping',
            'shipping taxable value',
            'shipping value',
        ],

        // Return specific
        'cancel_return_date' => [
            'cancel_return_date',
            'cancel return date',
            'return date',
            'cancellation date',
            'refund date',
        ],

        // Invoice file specific
        'buyer_gstin' => [
            'buyer_gstin',
            'buyer gstin',
            'customer gstin',
            'recipient gstin',
        ],

        'invoice_number' => [
            'invoice_number',
            'invoice number',
            'invoice no',
            'inv no',
            'document number',
        ],

        'financial_year' => [
            'financial_year',
            'financial year',
            'fy',
        ],

        'month_number' => [
            'month_number',
            'month number',
            'month',
        ],

        'supplier_name' => [
            'sup_name',
            'supplier name',
            'seller name',
        ],

        'identifier' => [
            'identifier',
            'id',
        ],
    ],

    // ── Defaults ─────────────────────────────────────────────────────────
    'defaults' => [
        'quantity'    => 1,
        'cess_amount' => 0,
    ],

    // ── Validation ───────────────────────────────────────────────────────
    'required_columns' => [
        'taxable_value',
        'place_of_supply',
    ],


    // three 3 phase controller= difective messo file detection
    // These 3 together = definitive Meesho file detection
    'strong_detection_columns' => [
        'total_taxable_sale_value',
        'end_customer_state_new',
        'sub_order_num',
    ],
];
