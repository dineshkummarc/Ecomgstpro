<?php

// Shopify "Orders export" CSV column aliases.
// IMPORTANT: Shopify order-level columns (Total, Subtotal, Taxes,
// Shipping Province, Tax 1/2/3 Name+Value) are populated ONLY on the
// FIRST row of every order — subsequent line-item rows of the same
// order are blank in those columns. ShopifyParser forward-fills these
// per order (grouped by "Name") before computing anything.

return [
    'columns' => [
        'order_name'      => ['Name', 'Order', 'Order Number'],
        'created_at'      => ['Created at', 'Created At'],
        'subtotal'        => ['Subtotal'],
        'total'           => ['Total'],
        'taxes'           => ['Taxes'],
        'lineitem_qty'    => ['Lineitem quantity', 'Lineitem Quantity'],
        'lineitem_price'  => ['Lineitem price', 'Lineitem Price'],
        'shipping_province' => ['Shipping Province Name', 'Shipping Province'],
        'billing_province'  => ['Billing Province Name', 'Billing Province'],
        'tax1_name'       => ['Tax 1 Name'],
        'tax1_value'      => ['Tax 1 Value'],
        'tax2_name'       => ['Tax 2 Name'],
        'tax2_value'      => ['Tax 2 Value'],
        'tax3_name'       => ['Tax 3 Name'],
        'tax3_value'      => ['Tax 3 Value'],
        'financial_status'=> ['Financial Status'],
        'note_attributes' => ['Note Attributes'],
        'notes'           => ['Notes'],
    ],
    'required_columns' => ['order_name', 'total'],
    'sheets' => [
        'skip' => [],
    ],
];
