# JioMart Parser — Developer Notes

`app/Parsers/JioMartParser.php`

Ye doc dusre developer ke liye hai jo iss parser ko touch karega —
kaunsa column kahan se aa raha hai, kaunsi business logic kaam kar rahi
hai, aur history mein kya bugs the / kyun fix kiye gaye.

---

## 1. Real File Format (JioMart "Invoice Report" export)

Actual JioMart CSV/Excel export mein ye 51 columns aate hain (index
1-based, jaisa file mein order hai):

| # | Column Header (as-is in file) |
|---|---|
| 1 | Seller GSTIN |
| 2 | Order ID |
| 3 | Order Item ID |
| 4 | Order Type |
| 5 | Order Date |
| 6 | Order Approval Date |
| 7 | Type |
| 8 | Shipment Number |
| 9 | Original Shipment Number |
| 10 | Fulfillment Type |
| 11 | Fulfiller Name |
| 12 | Product Title/Description |
| 13 | FSN / Product ID |
| 14 | SKU |
| 15 | **HSN Code** |
| 16 | Order Status |
| 17 | Event Type |
| 18 | Event Sub Type |
| 19 | **Item Quantity** |
| 20 | **Buyer Invoice ID** |
| 21 | **Original Invoice ID** |
| 22 | **Buyer Invoice Date** |
| 23 | Sale/Sale reversal TCS date |
| 24 | Buyer Invoice Amount |
| 25 | Order Shipped From (State) |
| 26 | Order Billed From (State) |
| 27 | Customer's Billing Pincode |
| 28 | Customer's Billing State |
| 29 | Customer's Delivery Pincode |
| 30 | **Customer's Delivery State** |
| 31 | Seller Coupon Code |
| 32 | Offer Price |
| 33 | Seller Coupon Amount |
| 34 | Final Invoice Amount (Offer Price minus Seller Coupon Amount) |
| 35 | Type of tax |
| 36 | **Taxable Value (Final Invoice Amount -Taxes)** |
| 37 | **IGST Rate** |
| 38 | **IGST Amount** |
| 39 | **CGST Rate** |
| 40 | **CGST Amount** |
| 41 | **SGST Rate (or UTGST as applicable)** |
| 42 | **SGST Amount (Or UTGST as applicable)** |
| 43-49 | TCS IGST/CGST/SGST Rate/Amount, Total TCS Deducted |
| 50-51 | TDS 194O Rate/Amount |

**Important — koi Buyer GSTIN column exist nahi karta.** JioMart ka
Invoice Report B2C-only hota hai; iske schema mein kabhi bhi buyer ka
GSTIN nahi aata. Sirf `Seller GSTIN` (col 1) hai, jo humara apna hi
GSTIN hai (client validation ke liye use hota hai, transaction-level
buyer GSTIN ke liye nahi).

---

## 2. Column-Map → Target Field (kaun sa column kis field mein jaata hai)

Parser flexible-alias-matching use karta hai (`COLS` constant +
`buildJioMartColMap()`), taaki har seller ka slightly-different export
bhi chal jaye. Neeche wahi mapping hai jo **is real file** ke against
resolve hoti hai:

| Target field (DB column) | Real file column jo match hoti hai | Kaise match hota hai |
|---|---|---|
| `taxable_value` | `Taxable Value (Final Invoice Amount -Taxes)` | exact alias match (Pass 1) |
| `gst_rate` | `IGST Rate` / `CGST Rate` (jo bhi pehle mile) | header-contains-alias |
| `place_of_supply` | `Customer's Delivery State` | exact alias, **priority di gayi hai over Billing State** — e-comm GST rule: POS = ship-to state, bill-to nahi |
| `igst_amount` | `IGST Amount` | header-contains-alias |
| `cgst_amount` | `CGST Amount` | header-contains-alias |
| `sgst_amount` | `SGST Amount (Or UTGST as applicable)` | exact alias match |
| `invoice_number` | `Buyer Invoice ID` (fallback: `Original Invoice ID` for returns) | exact alias, **highest priority** — see §3 for why |
| `invoice_date` | `Buyer Invoice Date` | exact alias match |
| `hsn_code` | `HSN Code` | exact alias match |
| `quantity` | `Item Quantity` | exact alias match |
| `buyer_gstin` | **kabhi kisi column se nahi** | hardcoded `null` — see §4 |

---

## 3. Invoice Number — kya problem thi, kya fix hua

### Problem (pehle)
`invoice_number` alias-list mein `order id` bhi tha, aur `Order ID`
column zyada aasani se match ho jaata tha (kyunki uska alias list ke
top pe tha ya generic tha). Result: Doc-Issued Summary tab mein
min/max/cancel/net-issue **Order ID** (jo per-item/per-shipment ID
hota hai, invoice number nahi) se calculate ho raha tha — jisse har
row apna alag "invoice series" ban rahi thi aur net-issue count galat
aa raha tha.

### Fix
- `order id` ko aliases se **hata diya gaya hai poori tarah**.
- Real invoice-number columns ab top priority par hain:
  `buyer invoice id` → `original invoice id` (returns ke liye) →
  generic fallbacks.

### Current state — TOGGLE OFF
Filhaal ek explicit flag lagaya gaya hai:

```php
private const ENABLE_INVOICE_NUMBER = false;
```

Jab tak ye `false` hai, `invoice_number` **hamesha `null`** save hoga
(chahe file mein `Buyer Invoice ID` column ho bhi), aur Doc-Issued
Summary tab is platform ke rows ko count hi nahi karega.

**Jab dobara invoice number chahiye ho**, sirf ye ek line badalni hai:

```php
private const ENABLE_INVOICE_NUMBER = true;
```

Column-detection/mapping logic (`COLS['invoice_number']`,
`findHeaderRowJioMart()`, `buildJioMartColMap()`) already sahi hai aur
is flag se independent hai — flip karte hi turant kaam karega, koi
aur jagah edit nahi karni.

---

## 4. Buyer GSTIN / B2B vs B2C — kya problem thi, kya fix hua

### Problem (pehle)
`buyer_gstin` ke aliases mein generic string `'gstin'` tha. Chunki
column-matching **substring-contains** logic bhi use karti hai (Pass
2/3), ye alias `Seller GSTIN` header ko bhi match kar leta tha (kyunki
`"seller gstin"` string ke andar `"gstin"` substring exist karta hai).
Isse **har row ko** koi-na-koi "buyer GSTIN" mil jaata tha (dar-asal
wo seller ka apna GSTIN hota), aur downstream (`GstrSummaryController::
isB2BTransaction()`) har transaction ko **B2B** samajh leta — jabki
JioMart poora ka poora B2C hai.

### Fix
`COLS['buyer_gstin']` ab **empty array** hai — koi alias hi nahi.
`buildJioMartColMap()` mein explicit check hai:

```php
if (empty($aliases)) continue;   // is field ko column-search se skip karo
```

Aur `parseSheet()` mein directly:

```php
$buyerGstin = null;   // hardcoded — kabhi column se read nahi hota
```

Result: JioMart ki har transaction ab hamesha B2C (B2CS tab) mein
jaati hai, kabhi B2B tab mein nahi — jo bilkul sahi hai kyunki JioMart
Invoice Report mein buyer GSTIN field exist hi nahi karta.

---

## 5. Place of Supply Resolution

- Primary source: **`Customer's Delivery State`** (col 30).
- Agar ye khaali/missing ho to seller ke apne state-code (`sellerStateCode`, jo client-select se aata hai) par fallback hota hai.
- Agar dono na milein, row skip ho jaati hai (warning ke saath).
- State-name → 2-digit code resolution `resolveStateCode()` (BaseParser) se hoti hai, aur per-unique-raw-value cache (`$posCache`) rakha gaya hai taaki repeated string lookups na ho.

**Note:** `Customer's Billing State` (col 28) ko jaan-boojh kar **use
nahi kiya** — GST rule ke hisaab se e-commerce sale ka Place of Supply
ship-to (delivery) state hota hai, billing state nahi.

---

## 6. Taxable Value & Tax Amounts

- `taxable_value == 0` → row **skip** (no transaction banti).
- `taxable_value < 0` → **counted as-is** (returns/credit-notes; sale
  aur return rows same POS+rate ke against sum hoke naturally net-out
  ho jaate hain downstream summary mein).
- `igst_amount` / `cgst_amount` / `sgst_amount`: agar column file mein
  maujood hai to uski value use hoti hai (`numOrNull()` — empty string
  ko `null` treat karta hai, `0` ko `0.0` treat karta hai — ye
  distinction zaroori hai taaki "column absent" aur "column mein 0 hai"
  mix na ho jayen).
- `gst_rate`: IGST Rate ya CGST Rate column se aata hai; agar value `<
  1` hai to `%` maan ke `×100` kiya jaata hai (e.g. `0.05` → `5.0`),
  warna as-is round kiya jaata hai.
- Koi hardcoded default amount kahin nahi hai — jo file mein nahi hai
  wo `null`/skip rehta hai, kabhi assume/guess nahi hota.

---

## 7. HSN Code

- `HSN Code` (col 15) column se directly liya jaata hai, exact-match
  priority ke saath (taaki koi partial/substring clash na ho).
- Agar khaali hai, `null` rehta hai (koi fallback nahi).

---

## 8. Quantity

- `Item Quantity` (col 19) column se `abs()` value li jaati hai
  (integer). Agar `0` ya missing, `null`.

---

## 9. Known limitation — Doc-Issued Summary is history-wide, not batch-wide

`GstrSummaryController::computeDocIssue()` (parser file nahi, ye
alag file hai) **is client-GSTIN ki poori DB history** se saare
`invoice_number` values khींचता hai — sirf current upload-batch se
nahi. Iska matlab:

- Agar koi purani JioMart batch **naye parser se pehle** upload hui
  thi (jab `invoice_number` galat/Order-ID se bhara ja raha tha), to
  wo purane rows database mein waisi hi padi rahengi jab tak unhe
  explicitly delete/replace na kiya jaye.
- Sirf naya file re-upload karne se purana saved data khud-ba-khud
  clean nahi hota — Doc-Issued tab tab tak purane numbers dikhata
  rahega jab tak purani rows ka `invoice_number` DB mein `NULL` na kar
  diya jaye ya wo rows delete na ho jayein.
- Ye is parser ka bug nahi hai — parser (naye upload ke liye) sahi
  kaam kar raha hai; ye sirf ek **data-hygiene** cheez hai jo
  purane/pre-fix uploads ke liye manually clear karni padegi.

---

## 10. Quick Reference — agar kal koi field add/change karna ho

| Karna hai | Kahan edit karo |
|---|---|
| Naya column-alias add karna (e.g. seller ka export thoda alag hai) | `COLS` constant mein us field ke array mein alias add karo |
| Invoice number wapas ON karna | `ENABLE_INVOICE_NUMBER = true` — bas itna hi |
| B2B support add karna (agar kabhi koi JioMart variant buyer GSTIN de) | `COLS['buyer_gstin']` mein aliases add karo, aur `parseSheet()` mein hardcoded `$buyerGstin = null;` ko wapas column-lookup logic se replace karo (jaisa doosre parsers mein hai) |
| Purana galat invoice-number data clean karna (DB) | Parser file nahi — DB query: `UPDATE gstr_transactions SET invoice_number = NULL WHERE platform LIKE 'jiomart%';` |
