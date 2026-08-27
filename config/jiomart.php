<?php
// config/gstr/jiomart.php
/**
 * ══════════════════════════════════════════════════════════════════════
 *  JIOMART (Reliance Jio) COLUMN MAPPING CONFIG
 * ══════════════════════════════════════════════════════════════════════
 *  Excel/CSV — Invoice Report format
 *  Columns: Invoice Number, Invoice Date, Place of Supply, HSN,
 *           Taxable Value, GST Rate, IGST/CGST/SGST Amount
 * ══════════════════════════════════════════════════════════════════════
 */
return [
    'detect_by_filename' => ['jiomart', 'jio mart', 'jio_mart', 'reliance jio'],
    'detect_by_sheet'    => ['jiomart', 'jio mart', 'invoice report', 'invoices'],
    'detect_by_columns'  => ['jiomart', 'jio_order_id'],

    'sheets' => [
        'data' => [
            'sheet1', 'invoice report', 'invoices', 'data',
            'jiomart', 'gst report',
        ],
        'skip' => ['summary', 'readme', 'instructions', 'lookup'],
    ],

    'columns' => [
        'taxable_value' => [
            'taxable value',
            'taxable_value',
            'taxable amount',
            'base amount',
            'item taxable value',
            'net value',
        ],
        'gst_rate' => [
            'gst rate',
            'tax rate',
            'rate',
            'gst%',
        ],
        'place_of_supply' => [
            'place of supply',
            'delivery state',
            'customer state',
            'state',
            'ship to state',
            'pos',
        ],
        'igst_amount' => [
            'igst amount',
            'igst',
            'integrated tax',
        ],
        'cgst_amount' => [
            'cgst amount',
            'cgst',
            'central tax',
        ],
        'sgst_amount' => [
            'sgst amount',
            'sgst',
            'state tax',
        ],
        'invoice_number' => [
            'invoice number',
            'invoice no',
            'invoice_number',
            'jio invoice number',
            'order id',
        ],
        'invoice_date' => [
            'invoice date',
            'order date',
            'date',
        ],
        'hsn_code' => [
            'hsn',
            'hsn code',
            'hsn/sac',
        ],
        'quantity' => [
            'quantity',
            'qty',
            'units',
        ],
        'buyer_gstin' => [
            'buyer gstin',
            'customer gstin',
            'gstin',
        ],
    ],

    'defaults'         => ['quantity' => 1, 'cess_amount' => 0],
    'required_columns' => ['taxable_value'],
];
