<?php

/**
 * ══════════════════════════════════════════════════════════════════════
 *  MYNTRA COLUMN MAPPING CONFIG
 * ══════════════════════════════════════════════════════════════════════
 *
 *  Myntra (Flipkart Group) sends CSV files — TCS format.
 *
 *  FILE 1 — Sales CSV
 *  ┌────────────────────────────────────────────────────────────────┐
 *  │ seller_gstin, delivery_state, seller_state_code,              │
 *  │ base_amount, tax_rate, igst_rate, cgst_rate, sgst_rate,       │
 *  │ item_status (= 'C' for completed sale),                       │
 *  │ tcs_amount, new_parent_order_id, order_created_date           │
 *  └────────────────────────────────────────────────────────────────┘
 *
 *  FILE 2 — Returns CSV
 *  ┌────────────────────────────────────────────────────────────────┐
 *  │ seller_gstin, customer_state, warehouse_state,                │
 *  │ base_amount, tax_rate,                                        │
 *  │ item_status (= 'IC' or 'RTO' for returns),                   │
 *  └────────────────────────────────────────────────────────────────┘
 *
 *  ITEM STATUS MAPPING:
 *  C   = Completed (positive sale)
 *  IC  = Item Cancelled (negative / return)
 *  RTO = Return to Origin (negative / return)
 * ══════════════════════════════════════════════════════════════════
 */

return [

    // ── Detection ───────────────────────────────────────────────────────
    'detect_by_filename' => [
        'myntra', 'myntra fashion',
    ],

    'detect_by_columns' => [
        'seller_gstin',
        'base_amount',
        'item_status',
    ],

    // ── Item Status Mapping ──────────────────────────────────────────────
    'item_status' => [
        'sale'   => ['C', 'COMPLETED', 'DELIVERED', 'COMPLETE'],
        'return' => ['IC', 'RTO', 'RETURN', 'CANCELLED', 'CANCEL', 'RTN', 'ITEM CANCELLED'],
    ],

    // ── Column Aliases ───────────────────────────────────────────────────
    'columns' => [

        'seller_gstin' => [
            'seller_gstin',
            'seller gstin',
            'gstin',
            'your gstin',
            'supplier gstin',
        ],

        // For SALES: buyer state = delivery_state
        'delivery_state' => [
            'delivery_state',
            'delivery state',
            'ship to state',
            'delivered state',
            'customer delivery state',
        ],

        // For RETURNS: buyer state = customer_state
        'customer_state' => [
            'customer_state',
            'customer state',
            'buyer state',
            'return state',
            'customer billing state',
        ],

        // Seller home state
        'seller_state_code' => [
            'seller_state_code',
            'seller state code',
            'seller state',
            'warehouse_state',
            'warehouse state',
            'dispatch state',
            'origin state',
        ],

        'taxable_value' => [
            'base_amount',
            'base amount',
            'taxable value',
            'taxable amount',
            'net amount',
            'item value',
            'selling price',
            'net taxable value',
        ],

        'gst_rate' => [
            'tax_rate',
            'tax rate',
            'gst rate',
            'gst%',
            'rate',
            'applicable tax rate',
        ],

        'igst_rate' => [
            'igst_rate',
            'igst rate',
            'igst%',
        ],

        'cgst_rate' => [
            'cgst_rate',
            'cgst rate',
            'cgst%',
        ],

        'sgst_rate' => [
            'sgst_rate',
            'sgst rate',
            'sgst%',
        ],

        'item_status' => [
            'item_status',
            'item status',
            'order status',
            'fulfillment status',
            'status',
        ],

        'order_id' => [
            'new_parent_order_id',
            'new parent order id',
            'parent order id',
            'order_id',
            'order id',
            'new_parent_id',
        ],

        'order_date' => [
            'order_created_date',
            'order created date',
            'order date',
            'date',
            'created at',
            'purchase date',
        ],

        'quantity' => [
            'qty',
            'quantity',
            'Qty',
            'item count',
            'units',
            'no. of items',
        ],

        'tcs_amount' => [
            'tcs_amount',
            'tcs amount',
            'tax collected at source',
            'tcs',
        ],

        'shipment_value' => [
            'shipment_value',
            'shipment value',
            'final_amount',
            'final amount',
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
        'item_status',
    ],

    'strong_detection_columns' => [
        'seller_gstin',
        'base_amount',
        'item_status',
    ],
];
