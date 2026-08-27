<?php
// config/gstr/snapdeal.php
/**
 * ══════════════════════════════════════════════════════════════════════
 *  SNAPDEAL COLUMN MAPPING CONFIG
 * ══════════════════════════════════════════════════════════════════════
 *  Excel/CSV — GST Report
 *  Columns: Order ID, State, GST Rate, Taxable Value, IGST, CGST, SGST
 * ══════════════════════════════════════════════════════════════════════
 */
return [
    'detect_by_filename' => ['snapdeal', 'sd_', 'snap deal'],
    'detect_by_sheet'    => ['snapdeal', 'gst report', 'b2c'],
    'detect_by_columns'  => ['snapdeal_order_id', 'sd_order'],

    'sheets' => [
        'data' => [
            'sheet1', 'data', 'gst report', 'b2c', 'snapdeal', 'sales',
        ],
        'skip' => ['summary', 'readme', 'instructions'],
    ],

    'columns' => [
        'taxable_value' => [
            'taxable value',
            'taxable_value',
            'taxable amount',
            'net taxable value',
            'base price',
            'selling price',
        ],
        'gst_rate' => [
            'gst rate',
            'rate',
            'tax rate',
            'gst%',
            'applicable rate',
        ],
        'place_of_supply' => [
            'state',
            'delivery state',
            'customer state',
            'ship to state',
            'place of supply',
            'buyer state',
            'pos',
        ],
        'igst_amount' => [
            'igst amount',
            'igst',
            'igst amt',
        ],
        'cgst_amount' => [
            'cgst amount',
            'cgst',
            'cgst amt',
        ],
        'sgst_amount' => [
            'sgst amount',
            'sgst',
            'sgst amt',
        ],
        'invoice_number' => [
            'order id',
            'order_id',
            'invoice number',
            'invoice no',
            'snapdeal order id',
        ],
        'invoice_date' => [
            'order date',
            'date',
            'invoice date',
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
    ],

    'defaults'         => ['quantity' => 1, 'cess_amount' => 0],
    'required_columns' => ['taxable_value'],
];
