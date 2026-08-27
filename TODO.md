# TODO - Fix B2CS upload/compute flow

## Step 1
Improve numeric parsing inside `GstrUploadController::uploadFile()`:
- Strip commas, currency symbols, percent signs
- Parse string numbers safely into floats
- Use parsed taxable/gst rate to avoid filtering out valid rows as 0

## Step 2
Improve upload JSON response fields so frontend knows what happened:
- return `parsed_rows`, `transactions_count`, `filtered_out_rows` (optional)

## Step 3
Make `computeB2CS()` resilient:
- compute summary when any `processed` transactions exist
- only return “No transactions found” when none exist

## Step 4
Manual test checklist
- Upload a file, confirm upload response shows non-zero transactions
- If parsed transactions_count > 0 but still 0 saved: check network response for processTransactions
- Compute B2CS redirects to `/gstr/summary`
- Summary page shows table + download buttons
- Try with another file where taxable column contains ₹ and commas


