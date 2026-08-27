<?php
// config/gstr/gstportal.php
/**
 * ══════════════════════════════════════════════════════════════════════
 *  GST PORTAL (Government) COLUMN MAPPING CONFIG
 * ══════════════════════════════════════════════════════════════════════
 *
 *  Supports both GST Portal formats:
 *
 *  FORMAT 1 — B2CS CSV (GST Portal import template)
 *  ┌────────────────────────────────────────────────────────────────┐
 *  │ Type, Place Of Supply, Rate,                                  │
 *  │ Applicable % of Tax Rate, Taxable Value, Cess Amount,         │
 *  │ E-Commerce GSTIN                                              │
 *  └────────────────────────────────────────────────────────────────┘
 *
 *  FORMAT 2 — Gov Excel (Official multi-sheet GSTR-1 Excel)
 *  ┌────────────────────────────────────────────────────────────────┐
 *  │ Sheet: 7. B2CS (Others)                                       │
 *  │ Type | Place of Supply | Rate | Taxable Value | IGST | CGST   │
 *  └────────────────────────────────────────────────────────────────┘
 * ══════════════════════════════════════════════════════════════════
 */
return [
    'detect_by_filename' => [
        'gstr1', 'gstr-1', 'gst_return', 'gst return',
        'b2cs', 'gov_excel', 'government', 'portal',
        'gstr_1_', 'offline_tool',
    ],

    'detect_by_sheet' => [
        '7. b2cs (others)',
        'b2cs (others)',
        'b2c(others)',
        '7b2cs',
        'b2cs',
        'b2c others',
    ],

    'detect_by_columns' => [
        'place of supply',
        'applicable % of tax rate',
        'e-commerce gstin',
    ],

    'sheets' => [
        'b2cs' => [
            '7. b2cs (others)',
            'b2cs (others)',
            'b2c(others)',
            'b2cs',
            'b2c others',
            '7b2cs',
            'b2c (others)',
        ],
        'b2cl' => [
            '6. b2cl (large invoices)',
            'b2cl',
            'b2c large',
            '6b2cl',
        ],
        'b2b' => [
            '4a,4b,6b,6c. b2b invoices',
            'b2b',
            'b2b invoices',
        ],
        'hsn' => [
            '12. hsn-wise summary of outward supplies',
            'hsn summary',
            'hsn',
        ],
        'skip' => [
            'read me', 'readme', 'instructions', 'guide',
            'help', 'notes', 'coversheet', 'summary',
        ],
    ],

    'columns' => [
        // B2CS CSV columns (exact GST portal format)
        'supply_type' => [
            'type',
            'supply type',
            'transaction type',
        ],
        'place_of_supply' => [
            'place of supply',
            'place_of_supply',
            'pos',
            'state',
        ],
        'gst_rate' => [
            'rate',
            'gst rate',
            'applicable % of tax rate',
            'rate(%)',
            'gst%',
            'tax rate',
        ],
        'applicable_pct' => [
            'applicable % of tax rate',
            'applicable %',
            'tax applicable %',
        ],
        'taxable_value' => [
            'taxable value',
            'taxable_value',
            'taxable amount',
            'aggregate taxable value',
        ],
        'cess_amount' => [
            'cess amount',
            'cess',
            'compensation cess',
        ],
        'ecommerce_gstin' => [
            'e-commerce gstin',
            'ecommerce gstin',
            'e commerce gstin',
            'eco gstin',
        ],

        // B2B sheet columns
        'buyer_gstin' => [
            'gstin/uin of recipient',
            'gstin',
            'buyer gstin',
            'recipient gstin',
        ],
        'invoice_number' => [
            'invoice number',
            'invoice no.',
            'invoice no',
            'invoice_number',
        ],
        'invoice_date' => [
            'invoice date',
            'date of invoice',
            'invoice dt',
        ],
        'invoice_value' => [
            'invoice value',
            'total invoice value',
            'bill amount',
        ],
        'igst_amount' => [
            'integrated tax amount',
            'igst amount',
            'igst',
        ],
        'cgst_amount' => [
            'central tax amount',
            'cgst amount',
            'cgst',
        ],
        'sgst_amount' => [
            'state/ut tax amount',
            'sgst amount',
            'sgst/utgst amount',
            'sgst',
        ],
    ],

    'defaults'         => ['supply_type' => 'OE', 'quantity' => 1, 'cess_amount' => 0],
    'required_columns' => ['taxable_value', 'place_of_supply'],
];
