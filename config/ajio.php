<?php
// config/gstr/ajio.php
/**
 * ══════════════════════════════════════════════════════════════════════
 *  AJIO (Reliance Retail) COLUMN MAPPING CONFIG
 * ══════════════════════════════════════════════════════════════════════
 *  Excel format — single sheet or multiple sheets.
 *  Columns: Order ID, State, HSN, GST Rate, Taxable Amount, IGST, CGST, SGST
 * ══════════════════════════════════════════════════════════════════════
 */
return [
    'detect_by_filename' => ['ajio', 'reliance retail', 'ajio_gst'],
    'detect_by_sheet'    => ['ajio', 'reliance', 'b2c data', 'gst data'],
    'detect_by_columns'  => ['seller_sku', 'forward_shipment', 'ajio'],

    'sheets' => [
        'data' => [
            'data', 'b2c', 'gst', 'sheet1', 'ajio',
            'sale data', 'transaction data', 'main',
        ],
        'skip' => [
            'summary', 'lookup', 'readme', 'instructions', 'guide', 'read me',
        ],
    ],

    'columns' => [
        'taxable_value' => [
            'taxable amount',
            'taxable value',
            'taxable_value',
            'base amount',
            'order taxable value',
            'invoice taxable value',
            'net amount',
            'taxable amt',
        ],
        'gst_rate' => [
            'gst rate',
            'gst%',
            'tax rate',
            'rate',
            'gst_rate',
            'applicable gst rate',
        ],
        'place_of_supply' => [
            'state',
            'customer state',
            'delivery state',
            'ship to state',
            'buyer state',
            'place of supply',
            'pos',
            'order state',
        ],
        'igst_amount' => [
            'igst',
            'igst amount',
            'integrated tax amount',
        ],
        'cgst_amount' => [
            'cgst',
            'cgst amount',
            'central tax amount',
        ],
        'sgst_amount' => [
            'sgst',
            'sgst amount',
            'state tax amount',
        ],
        'hsn_code' => [
            'hsn',
            'hsn code',
            'hsn/sac',
            'sku hsn',
        ],
        'invoice_number' => [
            'order id',
            'order_id',
            'invoice number',
            'invoice no',
            'bill number',
        ],
        'invoice_date' => [
            'order date',
            'date',
            'invoice date',
            'sale date',
        ],
        'quantity' => [
            'quantity',
            'qty',
            'units',
            'no. of items',
        ],
    ],

    'defaults'        => ['quantity' => 1, 'cess_amount' => 0],
    'required_columns'=> ['taxable_value'],
];
