# Meesho GST Parser – Complete Logic & Formula Documentation

## Objective
Implement the Meesho parser so that all GST reports (B2B, B2CS, HSN Summary, Document Issue, and GST JSON) are generated accurately without duplicate or merged data.

------------------------------------------------------------
1. Input Files
------------------------------------------------------------

The parser should process three files independently:

1. TCS Sales Report
2. TCS Sales Return Report
3. Tax Invoice Report

Rules:
- Do not merge data from different uploaded files.
- Every file should be parsed independently.
- Avoid duplicate calculations when multiple files are uploaded.

------------------------------------------------------------
2. Sales Calculation
------------------------------------------------------------

Source File:
TCS Sales Report

Columns:

Taxable Value
= total_taxable_sale_value

GST Amount
= tax_amount

Invoice Value
= total_invoice_value

Sales entries are always Positive (+).

------------------------------------------------------------
3. Return Calculation
------------------------------------------------------------

Source File:
TCS Sales Return Report

Columns:

Taxable Value
= total_taxable_sale_value

GST Amount
= tax_amount

Invoice Value
= total_invoice_value

Return entries are always Negative (-).

------------------------------------------------------------
4. Net GST Calculation
------------------------------------------------------------

Net Taxable Value
=
Sales Taxable Value
-
Return Taxable Value

Net GST
=
Sales GST
-
Return GST

Net Invoice Value
=
Sales Invoice Value
-
Return Invoice Value

------------------------------------------------------------
5. GST Split Logic
------------------------------------------------------------

Case 1:
Seller State == Place of Supply

CGST
=
GST Amount / 2

SGST
=
GST Amount / 2

IGST
=
0

------------------------------------------------------------

Case 2:
Seller State != Place of Supply

CGST
=
0

SGST
=
0

IGST
=
GST Amount

------------------------------------------------------------
6. GST Rate
------------------------------------------------------------

GST Rate should be read directly from the report.

Supported Rates

5%

12%

18%

28%

If tax_amount is already available in the report,
DO NOT recalculate GST.

Use the provided GST amount.

------------------------------------------------------------
7. HSN Logic
------------------------------------------------------------

HSN must always be fetched from

Tax Invoice Report

Never use HSN from

- TCS Sales Report
- TCS Sales Return Report

Matching Key

Invoice Number

------------------------------------------------------------
8. Invoice Number Logic
------------------------------------------------------------

Invoice Number must come only from

Tax Invoice Report

Flow

Tax Invoice
        ↓
Invoice Number
        ↓
Result
        ↓
Document Issue
        ↓
GST JSON

Never use Sub Order ID as Invoice Number.

------------------------------------------------------------
9. Document Issue Logic
------------------------------------------------------------

Generate Document Issue using Invoice Numbers from Tax Invoice Report.

Calculate

Minimum Invoice Number

Maximum Invoice Number

Cancelled Invoice Numbers

Missing Invoice Numbers

Formula

Minimum Invoice
=
MIN(Invoice Number)

Maximum Invoice
=
MAX(Invoice Number)

Cancelled
=
Invoice Status == Cancelled

Missing
=
Invoice Sequence Gap

------------------------------------------------------------
10. GST JSON Logic
------------------------------------------------------------

GST JSON should contain

Invoice Number

Invoice Date

HSN

GST Rate

Taxable Value

CGST

SGST

IGST

Invoice Value

The JSON must exactly match the generated GST report.

------------------------------------------------------------
11. Multiple File Upload Validation
------------------------------------------------------------

When three files are uploaded

TCS Sales

↓

TCS Return

↓

Tax Invoice

Rules

✔ Parse each file independently.

✔ Never combine Sales and Return totals automatically.

✔ Never duplicate uploaded records.

✔ Prevent merged calculations.

------------------------------------------------------------
12. Final Summary Formula
------------------------------------------------------------

Sales Taxable
=
SUM(total_taxable_sale_value)

Return Taxable
=
SUM(total_taxable_sale_value)

Net Taxable
=
Sales Taxable
-
Return Taxable

Sales GST
=
SUM(tax_amount)

Return GST
=
SUM(tax_amount)

Net GST
=
Sales GST
-
Return GST

Sales Invoice Value
=
SUM(total_invoice_value)

Return Invoice Value
=
SUM(total_invoice_value)

Net Invoice Value
=
Sales Invoice Value
-
Return Invoice Value

------------------------------------------------------------
13. Validation Checklist
------------------------------------------------------------

✔ Read TCS Sales Report correctly.

✔ Read TCS Sales Return Report correctly.

✔ Read Tax Invoice Report correctly.

✔ Do not merge values from multiple uploaded files.

✔ Calculate Sales and Returns separately.

✔ Calculate Net Values using Sales − Returns.

✔ Split GST correctly into CGST/SGST or IGST.

✔ Read HSN only from Tax Invoice.

✔ Read Invoice Number only from Tax Invoice.

✔ Never use Sub Order ID as Invoice Number.

✔ Generate correct Document Issue report.

✔ Generate correct HSN Summary.

✔ Generate correct GST JSON.

✔ Verify that Report Totals and JSON Totals are identical.

✔ Validate Taxable Value, GST Amount, Invoice Value, CGST, SGST, IGST before final report generation.

------------------------------------------------------------
14. Important Implementation Rules
------------------------------------------------------------

1. Never mix Sales and Return records.

2. Never calculate GST twice.

3. Never fetch HSN from Sales or Return reports.

4. Never fetch Invoice Number from Sub Order ID.

5. Always match Tax Invoice records using Invoice Number.

6. Ensure Document Issue, HSN Summary, GST Reports, and GST JSON all use the same Invoice Number.

7. Final GST calculations must always follow:

Net Value = Sales − Returns

without duplicate or merged records.





When i fix the hsn and invoice number through the of of taxinvoice detaile file then o got more error 

that i finaly solve that 

# Meesho — Invoice Number & HSN Merge Bug Fix

## Symptom

After splitting Meesho upload into 3 dedicated files —
**TCS Sales Sheet**, **TCS Sales Return Sheet**, and **Tax Invoice Details** —
the Document Issued Summary and transaction records showed **both
`invoice_number` and `hsn_code` as empty/null** for every row, even though:

- All 3 files were uploaded successfully
- The Tax Invoice file's lookup was built with `12033` entries
- `order_number` values were confirmed normalized identically on both sides
  (e.g. `295865269130130240_1` → `2958652691301302401`)

DB check confirmed **zero matches**:

```sql
SELECT COUNT(*) as total, COUNT(invoice_number) as with_invoice, COUNT(hsn_code) as with_hsn
FROM gstr_transactions WHERE batch_id = 'batch_1784270159115_twwleyv';
```
```
total: 15578   with_invoice: 0   with_hsn: 0
```

## Root Cause

A temporary diagnostic log (`MeeshoParserService::mergeTaxInvoiceData STATS`)
revealed the actual lookup table's keys were **not** the expected
order-number strings:

```json
"sample_misses": ["2958652691301302401", "2952281336480058241", ...],
"sample_lookup_keys": [0, 1, 2, 3, 4, 5, 6, 7]
```

The Tax Invoice lookup (`order_number => {invoice_number, hsn_code}`)
was stored in the session using:

```php
session([$sessionKey => array_merge(
    session($sessionKey, []),
    $result['lookup'] ?? []
)]);
```

**`array_merge()` re-indexes purely-numeric string keys.** Because every
`order_number` key looked like `"3035979087781697281"` (a numeric string,
even though PHP still treats it as a string key internally), `array_merge()`
silently discarded all of those keys and replaced them with sequential
integer keys `0, 1, 2, 3, ...`.

Result: the lookup table technically had `12033` entries and a non-empty
`count()`, but **every original `order_number` key was destroyed**, so
`mergeTaxInvoiceData()` could never find a match — `invoice_number` and
`hsn_code` stayed null on every single row.

This is a classic PHP gotcha: `array_merge()` preserves *string* keys only
if they are **not** entirely numeric; numeric-looking string keys are
treated like list indices and renumbered.

## Fix

### 1. `GstrUploadController.php` — session store (the actual bug)

Replaced `array_merge()` with the `+` (array union) operator, which
preserves **all** keys regardless of whether they look numeric:

```php
// Before (buggy — destroys numeric string keys)
session([$sessionKey => array_merge(
    session($sessionKey, []),
    $result['lookup'] ?? []
)]);

// After (fixed — preserves all order_number keys)
$existingLookup = session($sessionKey, []);
$newLookup      = $result['lookup'] ?? [];
session([$sessionKey => $newLookup + $existingLookup]);
```

### 2. `MeeshoParserService.php` — hardening + diagnostics

- `mergeTaxInvoiceData()` now re-normalizes `order_number` defensively
  before the lookup (protects against stray whitespace/encoding issues
  surviving a session round-trip).
- Added a last-resort fallback: if neither `'INVOICE'` nor `'CREDIT NOTE'`
  exists for an order but *some* type entry does, use that instead of
  dropping the match entirely.
- Added permanent stats logging (`total_meesho_rows`, `empty_order_num`,
  `not_in_lookup`, `entry_null`, `matched`, plus sample mismatched keys)
  so any future merge failures are immediately diagnosable from
  `storage/logs/laravel.log` instead of requiring guesswork.

### 3. `MeeshoTaxInvoiceParser.php` — logging

Empty normalized `order_number` rows are now logged with their raw value,
not just silently skipped, so a bad source column can be spotted quickly.

### 4. `MeeshoSalesParser.php` / `MeeshoReturnParser.php` — logging

Added a warning when `normalizeOrderKey()` produces an empty string from a
non-empty raw value, to catch any similar silent-data-loss pattern on the
Sales/Return side in future files.

## Verification

After the fix, re-uploading Sales → Return → Tax Invoice and checking
`storage/logs/laravel.log` for the `MeeshoParserService::mergeTaxInvoiceData
STATS` line should show:

- `sample_lookup_keys` as actual order-number strings
  (e.g. `"3035979087781697281"`), **not** `[0, 1, 2, 3, ...]`
- `matched` count close to the total Meesho row count (previously `0`)

And the DB check should now show non-zero counts:

```sql
SELECT COUNT(*) as total, COUNT(invoice_number) as with_invoice, COUNT(hsn_code) as with_hsn
FROM gstr_transactions WHERE batch_id = '<new_batch_id>';
```

## Key Takeaway

Never use `array_merge()` to combine associative arrays keyed by
numeric-looking strings (order IDs, invoice numbers, phone numbers, etc.).
Use the `+` union operator, or explicitly cast/prefix keys to force PHP to
treat them as non-numeric strings.
