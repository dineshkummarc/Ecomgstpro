1. Supported File Types (Amazon 3 tarah ki files deta hai)
File TypeDescriptionfile_type valueGSTR-1 ReadyEk hi Excel workbook, jisme sheets hoti hain: B2C Small, B2C Large, B2Bamazon_gstr1MTR B2C ReportAlag CSV — sirf B2C (retail customer) transactions, detailed per-rowamazon_mtr_b2cMTR B2B ReportAlag CSV — sirf B2B (business/GSTIN wale buyer) transactionsamazon_mtr_b2b
Rule: GSTR-1 Ready aur MTR (B2B+B2C) dono same data ko represent karte hain — isliye dono ek saath upload nahi karne dete (duplicate-format guard block kar deta hai). Lekin MTR B2B + MTR B2C dono saath upload ho sakte hain (yeh ek dusre ke duplicate nahi hain — alag-alag rows hoti hain).

2. GSTR-1 Ready File — Kaise Padha Jata Hai
Sheets detect karta hai: b2c small, b2c large, b2b (aur unke variations jaise b2cl, b2c(small) etc.)
Column mapping (COLS array se, fuzzy alias matching):
FieldKis column se aata hai (aliases)place_of_supply"Place Of Supply", "Ship To State", "Customer State"gst_rate"Rate", "Applicable % of Tax Rate"taxable_value"Taxable Value", "Taxable Amount"igst_amount"IGST Amount"cgst_amount"CGST Amount"sgst_amount"SGST Amount", "UTGST Amount" (UTGST bhi isi bucket me)invoice_number"Invoice Number", "Order Id"buyer_gstin"GSTIN/UIN of Recipient"
Column matching 3 passes me hota hai (buildAmazonColMap()):

Exact match — header hubahu alias se match kare
Contains match — header ke andar alias mile
Reverse contains — alias ke andar header mile (chhote headers jaise "rate", "state")

⚠️ Important guard: Amount fields (igst/cgst/sgst) kabhi bhi "rate/%" wale column se match nahi karte — looksLikeRateHeader() check karta hai. Isse "Rate ka column galti se Amount samajh liya" wala bug nahi hota.
Rate formula:
agar rate column mile aur value < 1 → rate × 100  (0.18 → 18%)
agar rate column na mile → (igst+cgst+sgst) / taxable_value × 100

3. MTR Detailed CSV — Kaise Padha Jata Hai
Yeh bilkul different schema hai — fingerprint detection se pehchana jata hai:
php$taxExclGross = find('Tax Exclusive Gross');
$totalTaxAmt  = find('Total Tax Amount');
if ($taxExclGross < 0 || $totalTaxAmt < 0) return null; // GSTR-1 format hai, MTR nahi
Yeh do columns sirf MTR file me hote hain, GSTR-1 me kabhi nahi — isi se decide hota hai kaunsa format hai.
Columns liye jaate hain:
FieldColumntaxable_valueTax Exclusive Gross (as-is, promo-netted already)total_taxTotal Tax Amount (as-is, promo-netted already)ratesCgst Rate, Sgst Rate, Utgst Rate, Igst Rateplace_of_supplyShip To Statebuyer_gstinCustomer Bill To Gstid / Ship To Gstidhsn_codeHsn/sac
⚠️ Sabse Important Formula (kyun raw tax columns use nahi karte)
rateRaw = cgstRate + sgstRate + utgstRate + igstRate
rate    = agar rateRaw < 1 → rateRaw × 100  (0.09 → 9%)
          warna round(rateRaw, 2)

totalTax = "Total Tax Amount" column se as-is (Igst Tax/Cgst Tax/Sgst Tax columns SE NAHI)
Split logic — totalTax ko rate ke hisaab se todte hain:
agar igstRate > 0        → igst = totalTax   (cgst=sgst=0)
agar utgstRate > 0        
   aur cgstRate==0, sgstRate==0 → sgst = totalTax  (UTGST, sgst bucket me daala)
agar cgstRate>0 ya sgstRate>0  → cgst = totalTax/2, sgst = totalTax - cgst
warna                     → sab 0 (nil-rated)
Kyun raw Igst Tax/Cgst Tax columns use nahi karte: Wo columns promo-discount netted nahi hote — unse taxable_value × rate ≠ igst+cgst+sgst ho jata (mismatch). "Total Tax Amount" already netted hai, isliye usi ko split karte hain.

4. POS (Place of Supply) Resolve Karne Ka Order (Fallback Chain)
Dono formats (GSTR-1 aur MTR) me same priority:
1. Place of Supply / Ship To State column se (agar mile)
2. Buyer GSTIN ke pehle 2 digits (state code) se
3. Agar B2C hai aur upar dono nahi mile → seller ka apna state
4. Kuch nahi mila → row skip, warning

5. Detail/Role Detection (kaunsa data B2B hai kaunsa B2C)

GSTR-1 file: sheet ka naam batata hai (B2B sheet = B2B, B2C sheet = B2C)
MTR CSV file: koi sheet naam nahi hota (Sheet1 hi hota hai) — isliye user ne upload ke time jo file_role (b2b/b2c) select kiya, wahi use hota hai:

php$isB2BFallback = ($fileRole === 'b2b');
B2B row me agar buyer GSTIN missing ho → row skip ho jaati hai (warning ke saath), kyunki B2B ke liye GSTIN mandatory hai.

6. Header Row Dhoondhna (findHeaderRowAmazon)
Keyword groups check karte hain (taxable value, place of supply, igst/cgst/sgst, invoice, gstin) — agar kisi row me 2+ groups match hon aur row me 4+ non-empty cells hon, wahi header row maan liya jata hai. Isse summary/subtotal rows (jaise "Total Taxable Value" jisme sirf 1-2 cells filled hote hain) galti se header nahi ban paate.

Quick Summary Table
CheezGSTR-1 ReadyMTR Detailed CSVDetectionSheet namesColumn fingerprint (Tax Exclusive Gross + Total Tax Amount)Taxable value source"Taxable Value" column"Tax Exclusive Gross" columnTax amounts sourceIGST/CGST/SGST columns directly"Total Tax Amount" split by rateB2B/B2C decide kaiseSheet name seUpload ke time file_role seRate formularate col × 100 (agar <1) ya tax/taxval×100sum of rate cols × 100 (agar <1)
