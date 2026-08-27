<?php

namespace App\Parsers;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * ══════════════════════════════════════════════════════════════════════
 *  BASE PARSER  (namespace: App\Parsers)
 * ══════════════════════════════════════════════════════════════════════
 *
 *  FIXES in this version:
 *  - makeTransaction $qty: float → ?int (null = column absent, no default)
 *  - makeTransaction: quantity field preserves null (no max(1,...) forcing)
 *  - Zero taxable_value rows are skipped; negative values ARE counted
 *  - Tax amounts: file se jo aaye wahi use, koi hardcoded default nahi
 *
 *  ← FIX: parseCsv() WAS NOT FORWARDING $fileRole
 *  parseCsv() used to call $this->parse($sheets, $this->sellerStateCode,
 *  $period) — dropping the $fileRole argument entirely. Any CSV-based
 *  parser (Myntra, JioMart, Snapdeal, etc.) that wanted to know which
 *  role a file was uploaded as (e.g. Myntra's Packed/RT/RTO multi-file
 *  upload) never actually received it. Now forwarded through.
 *
 *  ← NEW FIX: buyer_gstin VALIDATION
 *  Pehle makeTransaction() sirf yeh check karta tha ki buyer_gstin empty
 *  hai ya nahi ("NA", "-", garbage bhi non-empty hote hain, isliye pass ho
 *  jaate the). Iska result: DB me buyer_gstin = "NA" store ho jaata tha,
 *  aur GstrSummaryController::computeB2B() sab "NA" wale rows ko ek hi
 *  fake supplier ke andar group kar deta tha → JSON me "ctin": "NA" chala
 *  jaata tha, jo GST portal offline-tool validator ke liye invalid hai
 *  (ctin ek asli 15-char GSTIN hona chahiye) aur poori file reject ho
 *  jaati thi.
 *
 *  Ab isValidGstin() static helper add kiya hai jo GSTIN ko iske official
 *  regex format (2 digit state + 10 char PAN + entity code + Z + checksum)
 *  ke against verify karta hai. Controller is helper ko use karke decide
 *  karega ki row B2B me jayega ya B2CS me — is file me khud koi filtering
 *  nahi ki gayi hai, sirf validator provide kiya gaya hai taaki saare
 *  parsers aur controller isi ek jagah se validation lein.
 *
 *  ← NEW FIX: extractGstinFromCsvRows()
 *  extractGstinFromSpreadsheet() sirf PhpSpreadsheet objects (xlsx/xls)
 *  ke liye kaam karta hai — CSV files kabhi Spreadsheet object nahi
 *  banti (GstrUploadController::dispatchParser() ka CSV branch seedha
 *  parseCsv() call karke return kar deta tha, GSTIN validate() kabhi
 *  call hi nahi hota tha). Isi wajah se JioMart (jo hamesha .csv me
 *  aata hai) ka GSTIN check silently skip ho raha tha — koi bhi file
 *  kisi bhi client ke against upload ho jaati thi.
 *
 *  extractGstinFromCsvRows() ab raw CSV rows (array of arrays) leta hai
 *  aur:
 *    Pass 1 — header row me "Seller GSTIN" jaisa koi column dhoondhta
 *             hai aur us column ki value use karta hai (reliable —
 *             Buyer GSTIN column se confuse nahi hota)
 *    Pass 2 — agar aisa header na mile, to blind regex scan karta hai
 *             (extractGstinFromSpreadsheet() jaisa hi fallback)
 *
 *  ← NEW FIX: normalizeOrderKey()
 *  Sales/Return files aur Tax Invoice file ke order-number column ka
 *  header text alag-alag ho sakta hai ("Sub Order No", "sub_order_num",
 *  extra spaces, Excel scientific notation jaisa "2.17012E+12", case
 *  difference, etc.). Agar dono taraf raw string as-is compare ki jaaye
 *  to lookup silently miss ho jaata hai aur invoice_number/hsn_code kabhi
 *  merge nahi hote. normalizeOrderKey() ek single, consistent key banata
 *  hai jo dono taraf (lookup build karte waqt aur lookup match karte
 *  waqt) use hoti hai, taaki asli order id sahi se match ho.
 *
 *  STATE CODE RESOLUTION — kisi bhi format mein aaye, sahi 2-digit code milega:
 *
 *  Format examples handled:
 *  ┌──────────────────────────────┬────────┐
 *  │ Input (any case)             │ Output │
 *  ├──────────────────────────────┼────────┤
 *  │ "27"                         │ 27     │
 *  │ "27-Maharashtra"             │ 27     │
 *  │ "27 Maharashtra"             │ 27     │
 *  │ "Maharashtra"                │ 27     │
 *  │ "MAHARASHTRA"                │ 27     │
 *  │ "MH"                         │ 27     │
 *  │ "IN-MH"                      │ 27     │
 *  │ "maharsahtra" (typo)         │ 27     │
 *  │ "Tamil Nadu"                 │ 33     │
 *  │ "TN"                         │ 33     │
 *  │ "33-Tamil Nadu"              │ 33     │
 *  │ "Uttar Pradesh"              │ 09     │
 *  │ "UP"                         │ 09     │
 *  │ "09UP"                       │ 09     │
 *  │ "Delhi"                      │ 07     │
 *  │ "DL"                         │ 07     │
 *  │ "New Delhi"                  │ 07     │
 *  └──────────────────────────────┴────────┘
 * ══════════════════════════════════════════════════════════════════════
 */
abstract class BaseParser
{
    protected array  $cfg;
    protected string $platform;
    protected string $sellerStateCode = '';
    protected string $clientGstin     = '';
    protected string $period          = '';
    protected array  $warnings        = [];

    // ════════════════════════════════════════════════════════════════════
    //  COMPLETE STATE MAP
    // ════════════════════════════════════════════════════════════════════

    protected const STATE_MAP = [
        // Numeric codes
        '01' => '01', '02' => '02', '03' => '03', '04' => '04', '05' => '05',
        '06' => '06', '07' => '07', '08' => '08', '09' => '09', '10' => '10',
        '11' => '11', '12' => '12', '13' => '13', '14' => '14', '15' => '15',
        '16' => '16', '17' => '17', '18' => '18', '19' => '19', '20' => '20',
        '21' => '21', '22' => '22', '23' => '23', '24' => '24', '26' => '26',
        '27' => '27', '29' => '29', '30' => '30', '31' => '31', '32' => '32',
        '33' => '33', '34' => '34', '35' => '35', '36' => '36', '37' => '37',
        '38' => '38',

        // 01 Jammu & Kashmir
        'JAMMU AND KASHMIR'          => '01',
        'JAMMU & KASHMIR'            => '01',
        'J & K'                      => '01',
        'J&K'                        => '01',
        'JK'                         => '01',
        'J.K.'                       => '01',
        'JAMMU'                      => '01',
        'KASHMIR'                    => '01',

        // 02 Himachal Pradesh
        'HIMACHAL PRADESH'           => '02',
        'HIMACHAL'                   => '02',
        'HP'                         => '02',
        'H.P.'                       => '02',

        // 03 Punjab
        'PUNJAB'                     => '03',
        'PB'                         => '03',

        // 04 Chandigarh
        'CHANDIGARH'                 => '04',
        'CH'                         => '04',

        // 05 Uttarakhand
        'UTTARAKHAND'                => '05',
        'UTTARANCHAL'                => '05',
        'UTTRAKHAND'                 => '05',
        'UTTARKHAND'                 => '05',
        'UK'                         => '05',
        'UA'                         => '05',

        // 06 Haryana
        'HARYANA'                    => '06',
        'HR'                         => '06',

        // 07 Delhi
        'DELHI'                      => '07',
        'NEW DELHI'                  => '07',
        'NCT OF DELHI'               => '07',
        'NCT DELHI'                  => '07',
        'DL'                         => '07',
        'ND'                         => '07',

        // 08 Rajasthan
        'RAJASTHAN'                  => '08',
        'RJ'                         => '08',
        'RAJ'                        => '08',

        // 09 Uttar Pradesh
        'UTTAR PRADESH'              => '09',
        'U.P.'                       => '09',
        'UP'                         => '09',

        // 10 Bihar
        'BIHAR'                      => '10',
        'BR'                         => '10',
        'BH'                         => '10',

        // 11 Sikkim
        'SIKKIM'                     => '11',
        'SK'                         => '11',

        // 12 Arunachal Pradesh
        'ARUNACHAL PRADESH'          => '12',
        'ARUNACHAL'                  => '12',
        'AR'                         => '12',

        // 13 Nagaland
        'NAGALAND'                   => '13',
        'NL'                         => '13',

        // 14 Manipur
        'MANIPUR'                    => '14',
        'MN'                         => '14',

        // 15 Mizoram
        'MIZORAM'                    => '15',
        'MZ'                         => '15',

        // 16 Tripura
        'TRIPURA'                    => '16',
        'TR'                         => '16',

        // 17 Meghalaya
        'MEGHALAYA'                  => '17',
        'ML'                         => '17',

        // 18 Assam
        'ASSAM'                      => '18',
        'AS'                         => '18',

        // 19 West Bengal
        'WEST BENGAL'                => '19',
        'W.B.'                       => '19',
        'W.BENGAL'                   => '19',
        'WB'                         => '19',
        'WB.'                        => '19',

        // 20 Jharkhand
        'JHARKHAND'                  => '20',
        'JHARKAND'                   => '20',
        'JHARKHAND.'                 => '20',
        'JH'                         => '20',

        // 21 Odisha
        'ODISHA'                     => '21',
        'ORISSA'                     => '21',
        'ORRISA'                     => '21',
        'OD'                         => '21',
        'OR'                         => '21',

        // 22 Chhattisgarh
        'CHHATTISGARH'               => '22',
        'CHATTISGARH'                => '22',
        'CHHATISGARH'                => '22',
        'CG'                         => '22',

        // 23 Madhya Pradesh
        'MADHYA PRADESH'             => '23',
        'M.P.'                       => '23',
        'MP'                         => '23',

        // 24 Gujarat
        'GUJARAT'                    => '24',
        'GUJRAT'                     => '24',
        'GJ'                         => '24',

        // 26 Dadra & Nagar Haveli + Daman & Diu
        'DADRA AND NAGAR HAVELI AND DAMAN AND DIU' => '26',
        'DADRA AND NAGAR HAVELI'     => '26',
        'DADRA & NAGAR HAVELI'       => '26',
        'DAMAN AND DIU'              => '26',
        'DAMAN & DIU'                => '26',
        'DADRA'                      => '26',
        'DAMAN'                      => '26',
        'DIU'                        => '26',
        'DNHDD'                      => '26',
        'DD'                         => '26',
        'DN'                         => '26',

        // 27 Maharashtra
        'MAHARASHTRA'                => '27',
        'MAHARASTRA'                 => '27',
        'MAHARSAHTRA'                => '27',
        'MAHARASHTA'                 => '27',
        'MH'                         => '27',

        // 29 Karnataka
        'KARNATAKA'                  => '29',
        'KARNATKA'                   => '29',
        'KA'                         => '29',
        'KN'                         => '29',

        // 30 Goa
        'GOA'                        => '30',
        'GA'                         => '30',

        // 31 Lakshadweep
        'LAKSHADWEEP'                => '31',
        'LAKSHWADEEP'                => '31',
        'LD'                         => '31',

        // 32 Kerala
        'KERALA'                     => '32',
        'KL'                         => '32',

        // 33 Tamil Nadu
        'TAMIL NADU'                 => '33',
        'TAMILNADU'                  => '33',
        'TAMIL'                      => '33',
        'TN'                         => '33',

        // 34 Puducherry
        'PUDUCHERRY'                 => '34',
        'PONDICHERRY'                => '34',
        'PONDICHERY'                 => '34',
        'PONDY'                      => '34',
        'PY'                         => '34',

        // 35 Andaman & Nicobar
        'ANDAMAN AND NICOBAR ISLANDS'=> '35',
        'ANDAMAN AND NICOBAR'        => '35',
        'ANDAMAN & NICOBAR ISLANDS'  => '35',
        'ANDAMAN & NICOBAR'          => '35',
        'ANDAMAN'                    => '35',
        'ANDAMAN NICOBAR'            => '35',
        'AN'                         => '35',

        // 36 Telangana
        'TELANGANA'                  => '36',
        'TELENGANA'                  => '36',
        'TS'                         => '36',
        'TG'                         => '36',

        // 37 Andhra Pradesh
        'ANDHRA PRADESH'             => '37',
        'ANDHRA'                     => '37',
        'AP'                         => '37',

        // 38 Ladakh
        'LADAKH'                     => '38',
        'LA'                         => '38',
    ];

    protected const STATE_NAMES = [
        '01' => 'Jammu & Kashmir',
        '02' => 'Himachal Pradesh',
        '03' => 'Punjab',
        '04' => 'Chandigarh',
        '05' => 'Uttarakhand',
        '06' => 'Haryana',
        '07' => 'Delhi',
        '08' => 'Rajasthan',
        '09' => 'Uttar Pradesh',
        '10' => 'Bihar',
        '11' => 'Sikkim',
        '12' => 'Arunachal Pradesh',
        '13' => 'Nagaland',
        '14' => 'Manipur',
        '15' => 'Mizoram',
        '16' => 'Tripura',
        '17' => 'Meghalaya',
        '18' => 'Assam',
        '19' => 'West Bengal',
        '20' => 'Jharkhand',
        '21' => 'Odisha',
        '22' => 'Chhattisgarh',
        '23' => 'Madhya Pradesh',
        '24' => 'Gujarat',
        '26' => 'Dadra & Nagar Haveli and Daman & Diu',
        '27' => 'Maharashtra',
        '29' => 'Karnataka',
        '30' => 'Goa',
        '31' => 'Lakshadweep',
        '32' => 'Kerala',
        '33' => 'Tamil Nadu',
        '34' => 'Puducherry',
        '35' => 'Andaman & Nicobar Islands',
        '36' => 'Telangana',
        '37' => 'Andhra Pradesh',
        '38' => 'Ladakh',
    ];

    // ════════════════════════════════════════════════════════════════════
    //  CONSTRUCTOR
    // ════════════════════════════════════════════════════════════════════

    public function __construct(string $sellerStateCode = '', string $clientGstin = '')
    {
        $this->sellerStateCode = $sellerStateCode;
        $this->clientGstin     = $clientGstin;

        if (!empty($this->platform)) {
            $configPath = config_path("gstr/{$this->platform}.php");
            if (file_exists($configPath)) {
                $this->cfg = require $configPath;
            } else {
                $this->cfg = [];
            }
        } else {
            $this->cfg = [];
        }
    }

    // ════════════════════════════════════════════════════════════════════
    //  ABSTRACT METHODS
    // ════════════════════════════════════════════════════════════════════

    abstract public function parse(
        array  $sheets,
        string $sellerStateCode,
        string $period
    ): array;

    public function parseCsv(string $filePath, string $period, string $fileRole = 'single'): array
    {
        $sheets = $this->readCsv($filePath);
        // ← FIX: fileRole ab parse() tak forward hota hai (pehle drop ho raha tha)
        return $this->parse($sheets, $this->sellerStateCode, $period, $fileRole);
    }

    public function canHandle(Spreadsheet $spreadsheet): bool
    {
        return false;
    }

    // ════════════════════════════════════════════════════════════════════
    //  AUTO DETECT
    // ════════════════════════════════════════════════════════════════════

    public static function detectPlatformFromFilename(string $filename): string
    {
        $fn = strtolower($filename);
        return match(true) {
            str_contains($fn, 'amazon') || str_contains($fn, 'mtr')      => 'amazon',
            str_contains($fn, 'flipkart') || str_contains($fn, 'fk')     => 'flipkart',
            str_contains($fn, 'meesho')                                   => 'meesho',
            str_contains($fn, 'myntra')                                   => 'myntra',
            str_contains($fn, 'ajio')                                     => 'ajio',
            str_contains($fn, 'jiomart') || str_contains($fn, 'jio')     => 'jiomart',
            str_contains($fn, 'snapdeal')                                 => 'snapdeal',
            str_contains($fn, 'glowroad') || str_contains($fn, 'glow')   => 'glowroad',
            str_contains($fn, 'limeroad') || str_contains($fn, 'lime')   => 'limeroad',
            str_contains($fn, 'paytm')                                    => 'paytm',
            str_contains($fn, 'citymall') || str_contains($fn, 'city')   => 'citymall',
            default                                                        => 'auto',
        };
    }

    public static function detectParserFromContent(Spreadsheet $spreadsheet): ?string
    {
        $classes = [
            \App\Parsers\AmazonParser::class,
            \App\Parsers\FlipkartParser::class,
            \App\Parsers\MeeshoParser::class,
            \App\Parsers\MyntraParser::class,
            \App\Parsers\AjioParser::class,
            \App\Parsers\JioMartParser::class,
            \App\Parsers\SnapdealParser::class,
            \App\Parsers\GstPortalParser::class,
        ];

        foreach ($classes as $class) {
            if (!class_exists($class)) continue;
            try {
                $parser = new $class('', '');
                if ($parser->canHandle($spreadsheet)) return $class;
            } catch (\Throwable $e) {
                continue;
            }
        }

        return null;
    }

    public static function extractGstinFromSpreadsheet(Spreadsheet $spreadsheet, string $platform): ?string
    {
        try {
            $sheet = $spreadsheet->getActiveSheet();
            $rows  = $sheet->toArray(null, false,false, false);
            foreach (array_slice($rows, 0, 15) as $row) {
                foreach ($row as $cell) {
                    $v = strtoupper(trim((string)$cell));
                    if (preg_match('/\b([0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1})\b/', $v, $m)) {
                        return $m[1];
                    }
                }
            }
        } catch (\Throwable $e) {}
        return null;
    }

    // ════════════════════════════════════════════════════════════════════
    //  ← NEW: GSTIN EXTRACTOR FOR CSV ROWS
    //
    //  extractGstinFromSpreadsheet() only works on PhpSpreadsheet objects,
    //  which CSV files never become — GstrUploadController::dispatchParser()
    //  parses CSV directly and returns before any Spreadsheet is built.
    //  That meant the GSTIN mismatch check was silently never running for
    //  any CSV upload (JioMart, and any other CSV-based platform).
    //
    //  This method takes the raw CSV rows (array of arrays, as returned by
    //  BaseParser::readCsv()['Sheet1']) and:
    //
    //    Pass 1 — looks for a header column that clearly means "seller
    //             GSTIN" (e.g. "Seller GSTIN") and reads its value. This
    //             is the reliable path — it can't be confused with a
    //             per-row Buyer GSTIN column, which some platforms also
    //             have.
    //    Pass 2 — if no such header is found, falls back to a blind
    //             regex scan of the first 15 rows (same fallback logic
    //             as extractGstinFromSpreadsheet()).
    // ════════════════════════════════════════════════════════════════════

    public static function extractGstinFromCsvRows(array $rows, string $platform): ?string
    {
        if (empty($rows)) return null;

        // Pass 1: header column explicitly meaning "seller GSTIN"
        $headers = array_map(fn($h) => strtolower(trim((string)$h)), $rows[0] ?? []);
        $sellerAliases = [
            'seller gstin', 'seller_gstin', 'company gstin', 'gstin of seller',
            'gstin', 'seller gst', 'party gstin',
        ];

        foreach ($sellerAliases as $alias) {
            $idx = array_search($alias, $headers, true);
            if ($idx === false) continue;

            $limit = min(count($rows), 20);
            for ($i = 1; $i < $limit; $i++) {
                $val = strtoupper(trim((string)($rows[$i][$idx] ?? '')));
                if (self::isValidGstin($val)) return $val;
            }
        }

        // Pass 2: fallback — blind regex scan of first 15 rows
        foreach (array_slice($rows, 0, 15) as $row) {
            foreach ($row as $cell) {
                $v = strtoupper(trim((string)$cell));
                if (preg_match('/\b([0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1})\b/', $v, $m)) {
                    return $m[1];
                }
            }
        }

        return null;
    }

    // ════════════════════════════════════════════════════════════════════
    //  ← NEW: GSTIN VALIDATOR
    //
    //  Official GSTIN format: 15 characters
    //    [0-9]{2}   → 2-digit state code
    //    [A-Z]{5}   → PAN first 5 letters
    //    [0-9]{4}   → PAN 4 digits
    //    [A-Z]{1}   → PAN last letter
    //    [1-9A-Z]{1}→ entity/registration number
    //    Z          → fixed literal
    //    [0-9A-Z]{1}→ checksum character
    //
    //  Anything that does NOT match this — "NA", "-", "N/A", blank,
    //  truncated values, typos, etc. — is treated as INVALID, meaning the
    //  transaction should be routed to B2CS (unregistered/consumer sale),
    //  never into B2B with a fake ctin.
    //
    //  Used both here (so parsers can validate at source) and in
    //  GstrSummaryController (so B2B/B2CS split always agrees with the
    //  same rule) — single source of truth for what counts as a valid
    //  buyer GSTIN.
    // ════════════════════════════════════════════════════════════════════

    public static function isValidGstin(?string $gstin): bool
    {
        if ($gstin === null) return false;
        $g = strtoupper(trim($gstin));
        if ($g === '') return false;

        // Reject obvious non-GSTIN placeholder values before regex, for clarity
        if (in_array($g, ['NA', 'N/A', 'N.A.', '-', 'NIL', 'NONE', 'UNKNOWN'], true)) {
            return false;
        }

        return (bool) preg_match(
            '/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
            $g
        );
    }

    // ════════════════════════════════════════════════════════════════════
    //  ← NEW: ORDER NUMBER NORMALIZER
    //
    //  Sales/Return files aur Tax Invoice file ke order-number column ka
    //  header text alag ho sakta hai, aur cell values bhi Excel export ki
    //  wajah se thodi alag reh sakti hain — extra spaces, case difference,
    //  ya scientific notation (e.g. "2.17012E+12" bade numeric IDs ke
    //  liye jab Excel auto-format kar deta hai). Agar in dono files ke
    //  order_number ko as-is (raw) compare kiya jaaye, to lookup silently
    //  miss ho jaata hai aur invoice_number/hsn_code kabhi merge nahi hote
    //  — bina kisi error/warning ke.
    //
    //  Ye function ek single, consistent, comparable key banata hai jo:
    //    - MeeshoTaxInvoiceParser me lookup BUILD karte waqt, aur
    //    - MeeshoParserService::mergeTaxInvoiceData() me lookup ko MATCH
    //      karte waqt
    //  dono jagah use hoti hai — taaki same underlying order id hamesha
    //  same key produce kare, chahe do files me uska text-representation
    //  thoda alag kyun na ho.
    // ════════════════════════════════════════════════════════════════════

    public static function normalizeOrderKey(mixed $val): string
    {
        if ($val === null) return '';
        $s = trim((string)$val);
        if ($s === '') return '';

        // Excel scientific notation fix: "2.17012E+12" → plain integer string
        if (preg_match('/^\d+(\.\d+)?E\+\d+$/i', $s)) {
            $s = number_format((float)$s, 0, '', '');
        }

        // Sirf alphanumeric characters rakho, case-insensitive compare ke
        // liye upper karo — spaces, dashes, underscores sab ignore.
        $s = strtoupper((string)preg_replace('/[^A-Za-z0-9]/', '', $s));
        return $s;
    }

    // ════════════════════════════════════════════════════════════════════
    //  STATE CODE RESOLVER
    // ════════════════════════════════════════════════════════════════════

    public function resolveStateCode(mixed $raw): string
    {
        if ($raw === null || trim((string)$raw) === '') return '';

        $s = strtoupper(trim((string)$raw));

        // Step 1: Pure numeric 1-2 digit
        if (preg_match('/^(\d{1,2})$/', $s, $m)) {
            $code = str_pad($m[1], 2, '0', STR_PAD_LEFT);
            return isset(self::STATE_NAMES[$code]) ? $code : '';
        }

        // Step 2: Starts with numeric prefix "27-..." or "27 ..."
        if (preg_match('/^(\d{2})[\s\-\/]/', $s, $m)) {
            $code = $m[1];
            return isset(self::STATE_NAMES[$code]) ? $code : '';
        }

        // Step 3: Strip "IN-" or "IN_" prefix
        $stripped = preg_replace('/^IN[-_]?/', '', $s);

        if (preg_match('/^(\d{1,2})$/', $stripped, $m)) {
            $code = str_pad($m[1], 2, '0', STR_PAD_LEFT);
            return isset(self::STATE_NAMES[$code]) ? $code : '';
        }

        // Step 4: Direct map lookup
        if (isset(self::STATE_MAP[$s]))        return self::STATE_MAP[$s];
        if (isset(self::STATE_MAP[$stripped])) return self::STATE_MAP[$stripped];

        // Step 5: Remove dots and spaces
        $noDots = str_replace(['.', ' '], '', $s);
        if (isset(self::STATE_MAP[$noDots])) return self::STATE_MAP[$noDots];

        // Step 6: Contains match (longest wins)
        $bestContain    = '';
        $bestContainLen = 0;
        foreach (self::STATE_MAP as $name => $code) {
            if (strlen($name) < 3) continue;
            if (str_contains($s, $name) && strlen($name) > $bestContainLen) {
                $bestContain    = $code;
                $bestContainLen = strlen($name);
            }
        }
        if ($bestContain) return $bestContain;

        // Step 7: Input contained in known name
        foreach (self::STATE_MAP as $name => $code) {
            if (strlen($s) < 2 || strlen($name) < 2) continue;
            if (str_contains($name, $s) && strlen($s) >= 2) {
                return $code;
            }
        }

        // Step 8: Levenshtein fuzzy match
        if (strlen($s) >= 4) {
            $bestScore = PHP_INT_MAX;
            $bestCode  = '';
            foreach (self::STATE_MAP as $name => $code) {
                if (strlen($name) < 4) continue;
                $dist      = levenshtein($s, $name);
                $threshold = max(3, (int)(strlen($name) * 0.25));
                if ($dist < $bestScore && $dist <= $threshold) {
                    $bestScore = $dist;
                    $bestCode  = $code;
                }
            }
            if ($bestCode) return $bestCode;
        }

        return '';
    }

    public function fullPosFromCode(string $code): string
    {
        if ($code === '') return '';
        $name = self::STATE_NAMES[$code] ?? null;
        return $name ? "{$code}-" . strtoupper($name) : $code;
    }

    public static function normalizePos(string $pos): string
    {
        if (trim($pos) === '') return '';
        if (preg_match('/^\d{2}-/', $pos)) return strtoupper($pos);
        $tmpParser = new class extends BaseParser {
            protected string $platform = '';
            public function parse(array $s, string $sc, string $p): array { return []; }
        };
        $code = $tmpParser->resolveStateCode($pos);
        return $code ? $tmpParser->fullPosFromCode($code) : strtoupper($pos);
    }

    // ════════════════════════════════════════════════════════════════════
    //  sanitizeDate
    // ════════════════════════════════════════════════════════════════════

    public static function sanitizeDate(?string $date): ?string
    {
        if (!$date || trim($date) === '') return null;

        $date = trim($date);

        if (in_array(strtolower($date), ['null', 'n/a', 'na', '-', '0', ''])) return null;

        // Excel serial number
        if (is_numeric($date) && (float)$date > 1000) {
            try {
                $dt = ExcelDate::excelToDateTimeObject((float)$date);
                return $dt->format('Y-m-d');
            } catch (\Throwable $e) {}
        }

        $formats = [
            'Y-m-d',
            'd-m-Y',
            'd/m/Y',
            'm/d/Y',
            'd-M-Y',
            'd M Y',
            'Y/m/d',
            'd-m-y',
            'Y-m-d H:i:s',
            'd-m-Y H:i:s',
            'd/m/Y H:i:s',
            'm/d/Y H:i:s',
            'Y-m-d\TH:i:s',
            'd.m.Y',
            'd.m.y',
        ];

        foreach ($formats as $fmt) {
            $dt = \DateTime::createFromFormat($fmt, $date);
            if ($dt !== false) {
                $errors = \DateTime::getLastErrors();
                if (empty($errors['warning_count']) && empty($errors['error_count'])) {
                    return $dt->format('Y-m-d');
                }
            }
        }

        $ts = strtotime($date);
        return ($ts !== false && $ts > 0) ? date('Y-m-d', $ts) : null;
    }

    // ════════════════════════════════════════════════════════════════════
    //  FILE READERS
    // ════════════════════════════════════════════════════════════════════

    public function readExcel(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheets = [];

        foreach ($spreadsheet->getAllSheets() as $sheet) {
            $title = $sheet->getTitle();
            $rows  = $sheet->toArray(null, true, true, false);
            $rows  = array_values(array_filter($rows, fn($r) =>
                count(array_filter(array_map('strval', $r), fn($v) => trim($v) !== '')) > 0
            ));
            $sheets[$title] = $rows;
        }

        return $sheets;
    }

    public function readCsv(string $filePath): array
    {
        $content = file_get_contents($filePath);
        if ($content === false) throw new \RuntimeException("Cannot read CSV file: {$filePath}");

        // BOM strip
        $content = ltrim($content, "\xEF\xBB\xBF");

        $firstLine = strtok($content, "\n");
        $delimiter = $this->detectDelimiter($firstLine);

        $rows  = [];
        $lines = preg_split('/\r\n|\r|\n/', $content);
        foreach ($lines as $line) {
            if (trim($line) === '') continue;
            $rows[] = array_map('trim', str_getcsv($line, $delimiter));
        }

        return ['Sheet1' => $rows];
    }

    private function detectDelimiter(string $line): string
    {
        $counts = [
            ','  => substr_count($line, ','),
            "\t" => substr_count($line, "\t"),
            '|'  => substr_count($line, '|'),
            ';'  => substr_count($line, ';'),
        ];
        arsort($counts);
        return array_key_first($counts);
    }

    // ════════════════════════════════════════════════════════════════════
    //  COLUMN MAPPING
    // ════════════════════════════════════════════════════════════════════

    protected function findCol(array $headers, string $field): int
    {
        $aliases = $this->cfg['columns'][$field] ?? [];
        if (empty($aliases)) return -1;

        $normalized = array_map(fn($h) => strtolower(trim((string)$h)), $headers);

        // Pass 1: exact
        foreach ($aliases as $alias) {
            $idx = array_search(strtolower($alias), $normalized, true);
            if ($idx !== false) return (int)$idx;
        }

        // Pass 2: contains
        foreach ($aliases as $alias) {
            $al = strtolower($alias);
            foreach ($normalized as $i => $h) {
                if ($h === '') continue;
                if (str_contains($h, $al) || str_contains($al, $h)) return $i;
            }
        }

        // Pass 3: levenshtein
        $best = PHP_INT_MAX; $bestIdx = -1;
        foreach ($aliases as $alias) {
            $al = strtolower($alias);
            foreach ($normalized as $i => $h) {
                if (strlen($h) < 3) continue;
                $dist    = levenshtein($al, $h);
                $maxDist = max(3, (int)(strlen($al) * 0.30));
                if ($dist < $best && $dist <= $maxDist) { $best = $dist; $bestIdx = $i; }
            }
        }

        return $bestIdx;
    }

    protected function buildColMap(array $headers): array
    {
        $map = [];
        foreach (array_keys($this->cfg['columns'] ?? []) as $field) {
            $map[$field] = $this->findCol($headers, $field);
        }
        return $map;
    }

    protected function val(array $row, array $colMap, string $field): mixed
    {
        $idx = $colMap[$field] ?? -1;
        if ($idx < 0) return null;
        return $row[$idx] ?? null;
    }

    // ════════════════════════════════════════════════════════════════════
    //  HEADER ROW FINDER
    // ════════════════════════════════════════════════════════════════════

    protected function findHeaderRow(array $rows, array $keywords, int $minMatch = 1): int
    {
        foreach ($rows as $i => $row) {
            $rowStr  = strtolower(implode(' ', array_map('strval', $row)));
            $matches = 0;
            foreach ($keywords as $kw) {
                if (str_contains($rowStr, strtolower($kw))) $matches++;
            }
            if ($matches >= $minMatch) return $i;
        }
        return -1;
    }

    protected function findHeaderRowByConfig(array $rows): int
    {
        $required = $this->cfg['required_columns'] ?? [];
        if (empty($required)) return 0;

        $searchTerms = [];
        foreach ($required as $field) {
            foreach (($this->cfg['columns'][$field] ?? []) as $alias) {
                $searchTerms[] = strtolower($alias);
            }
        }

        foreach ($rows as $i => $row) {
            $rowStr = strtolower(implode(' ', array_map('strval', $row)));
            foreach ($searchTerms as $term) {
                if (str_contains($rowStr, $term)) return $i;
            }
        }

        foreach ($rows as $i => $row) {
            $nonEmpty = array_filter(array_map('strval', $row), fn($v) => trim($v) !== '');
            if (count($nonEmpty) >= 3) return $i;
        }

        return 0;
    }

    // ════════════════════════════════════════════════════════════════════
    //  SHEET ROUTER
    // ════════════════════════════════════════════════════════════════════

    protected function routeSheets(array $allSheets): array
    {
        $sheetConfig = $this->cfg['sheets'] ?? [];
        $skipList    = $sheetConfig['skip']  ?? [];
        $routed      = [];

        foreach ($allSheets as $sheetName => $rows) {
            $snLower = strtolower(trim($sheetName));

            $shouldSkip = false;
            foreach ($skipList as $skip) {
                if (str_contains($snLower, strtolower($skip))) { $shouldSkip = true; break; }
            }
            if ($shouldSkip) continue;

            $matched = false;
            foreach ($sheetConfig as $type => $aliases) {
                if ($type === 'skip') continue;
                foreach ((array)$aliases as $alias) {
                    if (str_contains($snLower, strtolower($alias))) {
                        $routed[$type] = $rows;
                        $matched       = true;
                        break 2;
                    }
                }
            }

            if (!$matched) $routed['unknown_' . $snLower] = $rows;
        }

        return $routed;
    }

    // ════════════════════════════════════════════════════════════════════
    //  NUMBER / DATE CLEANERS
    // ════════════════════════════════════════════════════════════════════

    public function num(mixed $val): float
    {
        if ($val === null || $val === '') return 0.0;
        if (is_float($val) || is_int($val)) return (float)$val;

        $s        = trim((string)$val);
        $negative = false;

        // Parentheses = negative e.g. (100.00)
        if (preg_match('/^\((.*)\)$/', $s, $m)) { $negative = true; $s = $m[1]; }

        $s = preg_replace('/[^\d.\-]/', '', str_replace([',', '₹', 'INR', 'Rs.', 'Rs', ' ', '%'], '', $s));

        $n = is_numeric($s) ? (float)$s : 0.0;
        return $negative ? -abs($n) : $n;
    }

    public function date(mixed $val): ?string
    {
        if ($val === null || $val === '') return null;
        $s = trim((string)$val);
        if ($s === '' || in_array(strtolower($s), ['null', 'n/a', 'na', '-'])) return null;

        // Excel serial
        if (is_numeric($s) && (float)$s > 1000) {
            try {
                $dt = ExcelDate::excelToDateTimeObject((float)$s);
                return $dt->format('Y-m-d');
            } catch (\Throwable $e) {}
        }

        $formats = [
            'd-m-Y', 'd/m/Y', 'm/d/Y', 'Y-m-d',
            'd-M-Y', 'd M Y', 'Y/m/d', 'd-m-y',
            'Y-m-d H:i:s', 'd-m-Y H:i:s', 'd/m/Y H:i:s',
            'd.m.Y', 'd.m.y',
        ];
        foreach ($formats as $fmt) {
            $dt = \DateTime::createFromFormat($fmt, $s);
            if ($dt !== false) return $dt->format('Y-m-d');
        }

        $ts = strtotime($s);
        return ($ts !== false && $ts > 0) ? date('Y-m-d', $ts) : null;
    }

    // ════════════════════════════════════════════════════════════════════
    //  TRANSACTION BUILDER
    //
    //  KEY RULES:
    //  1. $qty is ?int — null means column was not present in file (e.g. GSTR-1 B2C Small)
    //     It is NOT defaulted to 1. Whatever the file has, that is stored.
    //  2. taxable_value == 0  → skip (already enforced in child parsers, double-check here)
    //  3. Negative taxable_value (returns/credit notes) → counted as-is, not skipped
    //  4. Tax amounts: if provided from file → use them directly (no recalculation)
    //     If ALL three are null → calculate from rate (inter/intra logic)
    //  5. No hardcoded defaults for any amount field
    //  6. ← NEW: buyer_gstin is validated via BaseParser::isValidGstin()
    //     before being stored — "NA" / "-" / garbage values are stored as
    //     null instead, so downstream code correctly treats the row as
    //     B2C rather than accidentally creating a fake B2B supplier.
    // ════════════════════════════════════════════════════════════════════

    protected function makeTransaction(
        string  $pos,
        float   $rate,
        float   $taxableValue,
        ?float  $igstAmt     = null,
        ?float  $cgstAmt     = null,
        ?float  $sgstAmt     = null,
        ?string $invoiceNo   = null,
        ?string $invoiceDate = null,
        ?int    $qty         = null,   // ← ?int, null = column absent, never defaulted to 1
        ?string $hsn         = null,
        ?string $buyerGstin  = null
    ): array {
        // Safety: pos required, zero taxable skip
        if ($pos === '') return [];
        if ($taxableValue == 0) return [];

        // Determine inter/intra state
        $isInter = ($this->sellerStateCode !== '' && $pos !== $this->sellerStateCode);

        // Tax amount logic:
        // If file provided any of igst/cgst/sgst → use them as-is (file is source of truth)
        // If none provided → calculate from rate
        if ($igstAmt === null && $cgstAmt === null && $sgstAmt === null) {
            // Calculate only when rate > 0
            if ($rate > 0) {
                if ($isInter) {
                    $igst = round($taxableValue * $rate / 100, 2);
                    $cgst = 0.0;
                    $sgst = 0.0;
                } else {
                    $half = round($taxableValue * $rate / 200, 2);
                    $igst = 0.0;
                    $cgst = $half;
                    $sgst = $half;
                }
            } else {
                // Rate 0% (exempt / nil rated)
                $igst = 0.0;
                $cgst = 0.0;
                $sgst = 0.0;
            }
        } else {
            // Use file values directly; treat missing as 0
            $igst = $igstAmt ?? 0.0;
            $cgst = $cgstAmt ?? 0.0;
            $sgst = $sgstAmt ?? 0.0;
        }

        // ← NEW: validate buyer GSTIN before storing. "NA", "-", blank,
        // or any string that doesn't match the official GSTIN pattern is
        // stored as null (=> treated as B2C downstream), never as-is.
        $validBuyerGstin = ($buyerGstin && self::isValidGstin($buyerGstin))
            ? strtoupper(trim($buyerGstin))
            : null;

        return [
            'platform'        => $this->platform,
            'place_of_supply' => $this->fullPosFromCode($pos),
            'state_code'      => $pos,
            'taxable_value'   => round($taxableValue, 2),
            'gst_rate'        => round($rate, 2),
            'igst_amount'     => round($igst, 2),
            'cgst_amount'     => round($cgst, 2),
            'sgst_amount'     => round($sgst, 2),
            'cess_amount'     => 0,
            'invoice_number'  => $invoiceNo  ? trim($invoiceNo)  : null,
            'invoice_date'    => $invoiceDate ?? null,
            // quantity: exactly what file had — null if column absent, int if present
            'quantity'        => $qty,
            'hsn_code'        => $hsn ? trim((string)$hsn) : null,
            'buyer_gstin'     => $validBuyerGstin,
        ];
    }

    // ════════════════════════════════════════════════════════════════════
    //  HELPERS
    // ════════════════════════════════════════════════════════════════════

    protected function warn(string $msg): void
    {
        $this->warnings[] = "[{$this->platform}] {$msg}";
    }

    protected function rowIsEmpty(array $row, int $minNonEmpty = 2): bool
    {
        $count = count(array_filter(array_map('strval', $row), fn($v) => trim($v) !== ''));
        return $count < $minNonEmpty;
    }

    protected function rowToAssoc(array $headers, array $row): array
    {
        $data = [];
        foreach ($headers as $i => $h) {
            if (trim((string)$h) !== '') {
                $data[strtolower(trim((string)$h))] = $row[$i] ?? null;
            }
        }
        return $data;
    }

    protected function shouldSkipSheet(string $sheetName): bool
    {
        $skipList = $this->cfg['sheets']['skip'] ?? [];
        $sn = strtolower($sheetName);
        foreach ($skipList as $skip) {
            if (str_contains($sn, strtolower($skip))) return true;
        }
        return false;
    }
}
