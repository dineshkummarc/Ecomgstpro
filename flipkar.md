# Flipkart Parser — Notes (kis section se kya aata hai + tax formulas)

Yeh file batati hai ki Flipkart ki do report types (Sales Report/MTR aur GST Report/GSTR-1 Ready) se
data kaise nikala jaata hai — kaunsi sheet, kaunsa column, aur taxable value / CGST / SGST / IGST
kis formula se calculate hote hain.

---

## 1. Do Report Types

Flipkart 2 alag files deta hai, dono mein same invoices hote hain, bas format alag:

| Report | File pehchaan (sheet names) | Kaunsa data invoice-level hai |
|---|---|---|
| **Sales Report / MTR** | Sheet name mein `"Sales Report"` hota hai | Har row = 1 order item, invoice number hamesha available |
| **GST Report / GSTR-1 Ready** | `7(A)(2)`, `7(B)(2)`, `5B`, `Section 12`, `Section 13` jaisi sheets | Sirf `5B` (B2CL) invoice-level hai, baaki B2CS-style aggregated hain |

`detectFormatFromSheets()` function check karta hai — agar koi sheet name mein `"sales report"`
mile toh MTR maana jaata hai, warna GSTR-1 Ready.

---

## 2. GST Report (GSTR-1 Ready) ke andar kaunsi sheet se kya milta hai

### 2.1 Section `7(A)(2)` → INTRA-state transactions (`parse7A2()`)

- Yeh table **already aggregated** hai (per row = ek state+rate ka total), invoice-level nahi.
- POS (Place of Supply) = **seller ka apna state** (kyunki INTRA matlab same state).
- **Taxable Value** column direct sheet se liya jaata hai.
- **Rate formula:**
  ```
  rate = cgst_rate × 2        (agar CGST rate column mila)
  ```
- **CGST / SGST Amount:**
  - Agar sheet mein CGST/SGST amount column diya hai → wahi use hota hai.
  - Agar amount nahi mila lekin rate mil gaya:
    ```
    half  = taxable_value × rate / 200      (rate/200 kyunki CGST+SGST milke poora rate hai)
    cgst  = half
    sgst  = half
    ```
  - Agar rate bhi 0 hai lekin CGST amount mil gaya, toh reverse-calculate:
    ```
    rate = (cgst_amount / taxable_value) × 200
    ```
- **IGST = 0** (kyunki intra-state mein IGST lagta hi nahi)

### 2.2 Section `7(B)(2)` → INTER-state transactions (`parse7B2()`)

- Yeh bhi aggregated table hai (per state+rate), invoice-level nahi.
- POS = row ke andar diye gaye **delivery/buyer state** se nikala jaata hai.
- **IGST Amount:** sheet ke column se direct.
- **IGST Rate formula (agar rate column nahi mila):**
  ```
  igst_rate = (igst_amount / taxable_value) × 100
  ```
- **IGST Amount formula (agar amount nahi mila lekin rate mil gaya):**
  ```
  igst_amount = taxable_value × igst_rate / 100
  ```
- **CGST = SGST = 0** (kyunki inter-state mein sirf IGST lagta hai)

### 2.3 Section `5B` (B2CL — large inter-state invoices) → (`parse5B()`)

- Yeh **invoice-level** hai — ismein `invoice_number` aur `invoice_date` milte hain.
- Sirf woh rows liye jaate hain jinka `taxable_value > 0`.
- POS = row ke `place_of_supply` / `delivery_state` column se.
- IGST rate/amount ka formula same hai jaisa 7(B)(2) mein hai:
  ```
  igst_rate   = (igst_amount / taxable_value) × 100      (agar rate missing)
  igst_amount = taxable_value × igst_rate / 100          (agar amount missing)
  ```
- Yehi sheet **Document Issued Summary** ke liye asli invoice numbers deti hai (generic/fallback
  logic mein) — 7(A)(2) aur 7(B)(2) invoice number **kabhi nahi dete** (woh aggregated hain).

---

## 3. Section 12 in GSTR-1 → **HSN Summary** (`parseNativeHsnSection()`)

Yeh sheet Flipkart ne **khud already-calculated** di hoti hai (ek row = ek HSN code). Isliye humne
koi naya calculation nahi kiya — **jo Flipkart ki file mein diya hai wahi as-is** utha liya:

| Cell Column (Flipkart ka original header) | Kis field mein jaata hai |
|---|---|
| HSN Number | `hsn_sc` |
| Total Quantity in Nos. | `qty` |
| Total Value Rs. | `val` |
| Total Taxable Value Rs. | `txval` |
| IGST Amount Rs. | `iamt` |
| CGST Amount Rs. | `camt` |
| SGST Amount Rs. | `samt` |
| Cess Rs. | `csamt` |

**Koi formula nahi lagaya gaya** — sirf column match karke value copy ki gayi hai. `rate` field
yahan `0` fixed rakha gaya hai kyunki is sheet mein per-HSN rate nahi diya hota.

**Important:** Is sheet ke headers Excel mein **2 lines mein wrap** hote hain (cell ke andar ek
line-break/newline character hota hai — jaise "Total Taxable\nValue Rs."). Isliye matching se pehle
header text ko **normalize** kiya jaata hai (newline → space, extra spaces collapse) taaki column
sahi se pehchana ja sake.

---

## 4. Section 13 in GSTR-1 → **Document Issued Summary** (`parseNativeDocIssueSection()`)

Yeh sheet bhi Flipkart ki khud ki **already-calculated** summary hai (ek row = ek invoice series
ka range). Yahan bhi koi calculation nahi — sirf as-is copy:

| Cell Column (Flipkart ka original header) | Kis field mein jaata hai |
|---|---|
| Invoice Series From | `from` |
| Invoice Series To | `to` |
| Total Number of Invoices | `totnum` |
| Cancelled if any | `cancel` |
| Net invoices Issued | `net_issue` |

Yahan bhi headers wrap hote hain ("Invoice Series\nFrom" / "Invoice Series\nTo"), isliye same
normalization logic use hoti hai, aur ek extra safety-check hai: agar From aur To dono ek hi column
resolve ho jaayein (matching galti se), toh us case ko **invalid** maan ke skip kar diya jaata hai —
taaki galat data kabhi UI tak na pahunche.

**Jab yeh native data available hoti hai**, toh purana generic calculation (raw invoice numbers ke
min→max range nikaal ke gaps ko "cancelled" maan lena) **use hi nahi hota** — sirf Flipkart ka apna
diya hua number final maana jaata hai.

---

## 5. MTR / Sales Report format → (`buildMtrTransaction()`)

Yeh line-item level data hai, har row ek order/invoice.

- **Taxable Value:** sheet ke column se direct.
- **Rate formula:**
  ```
  rate = igst_rate                          (agar IGST rate mila, inter-state case)
       ya
  rate = cgst_rate × 2                      (agar CGST rate mila, intra-state case)
  ```
- **Agar rate 0 hai lekin amount mil gaya, reverse-calculate:**
  ```
  rate = (igst_amount / taxable_value) × 100      (agar IGST amount tha)
  rate = (cgst_amount / taxable_value) × 200      (agar CGST amount tha)
  ```
- Return/refund rows mein **taxable_value negative** already hoti hai Flipkart ki file mein khud
  se — isliye code dobara sign flip nahi karta.
- Invoice number, invoice date, HSN, buyer GSTIN — sab seedha column se liye jaate hain (formula
  nahi lagta, sirf mapping hai).

---

## 6. Quick Summary Table — Kahan se kya aata hai

| Data | Kis sheet se | Calculation ya as-is? |
|---|---|---|
| B2CS Intra transactions | `7(A)(2)` | Calculated (rate×txval formula) |
| B2CS Inter transactions | `7(B)(2)` | Calculated (rate×txval formula) |
| B2CL (large invoices) | `5B` | Calculated + invoice-level |
| HSN Summary | `Section 12 in GSTR-1` | **As-is copy**, Flipkart ka khud ka number |
| Document Issued Summary | `Section 13 in GSTR-1` | **As-is copy**, Flipkart ka khud ka number |
| MTR/Sales Report transactions | `Sales Report` sheet | Calculated, per line-item |

---

## 7. Ek cheez yaad rakhna

Section 12 aur Section 13 sheets sirf **GSTR-1 Ready** file mein hoti hain — **Sales Report/MTR**
file mein yeh kabhi nahi hoti. Isliye jab MTR file upload hoti hai, `hsn_summary` aur `doc_issue`
dono **khaali `[]`** return hote hain (koi native data nahi milta us format mein).
