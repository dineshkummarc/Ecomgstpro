<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * GSTR-1 E-Commerce Smart Parser — Laravel Backend
 *
 * Supports: Amazon, Flipkart, Meesho, TCS, Ajio, Myntra,
 *           Snapdeal, Nykaa, JioMart, Glowroad, Shopsy
 *
 * Uses ML-style fuzzy column matching (Levenshtein distance)
 * for zero-failure parsing even on slightly different report formats.
 */
class GstrParserController extends Controller
{
    // ─── STATE MAP ────────────────────────────────────────────────────
    private const STATE_MAP = [
        'ANDHRA PRADESH' => '37', 'ARUNACHAL PRADESH' => '12',
        'ASSAM' => '18', 'BIHAR' => '10', 'CHANDIGARH' => '04',
        'CHHATTISGARH' => '22', 'DELHI' => '07', 'GOA' => '30',
        'GUJARAT' => '24', 'HARYANA' => '06', 'HIMACHAL PRADESH' => '02',
        'JAMMU & KASHMIR' => '01', 'JAMMU AND KASHMIR' => '01',
        'JHARKHAND' => '20', 'KARNATAKA' => '29', 'KERALA' => '32',
        'MADHYA PRADESH' => '23', 'MAHARASHTRA' => '27', 'MANIPUR' => '14',
        'MEGHALAYA' => '17', 'MIZORAM' => '15', 'NAGALAND' => '13',
        'ODISHA' => '21', 'PUDUCHERRY' => '28', 'PONDICHERRY' => '28',
        'PUNJAB' => '03', 'RAJASTHAN' => '08', 'SIKKIM' => '11',
        'TAMIL NADU' => '33', 'TELANGANA' => '36', 'TRIPURA' => '16',
        'UTTAR PRADESH' => '09', 'UTTARAKHAND' => '05',
        'WEST BENGAL' => '19', 'DADRA AND NAGAR HAVELI' => '26',
        'DADRA & NAGAR HAVELI' => '26', 'LAKSHADWEEP' => '31',
        'ANDAMAN AND NICOBAR' => '35', 'ANDAMAN & NICOBAR' => '35',
        // Aliases / typos
        'J & K' => '01', 'J&K' => '01', 'JHARKAND' => '20',
        'ORRISA' => '21', 'UTTARANCHAL' => '05', 'ORISSA' => '21',
        'TAMILNADU' => '33', 'PONDICHERY' => '28', 'UTTRAKHAND' => '05',
        'AP' => '37', 'UP' => '09', 'HP' => '02', 'MP' => '23',
        'WB' => '19', 'TN' => '33', 'KA' => '29', 'KL' => '32',
        'MH' => '27', 'GJ' => '24', 'RJ' => '08', 'HR' => '06',
        'DL' => '07', 'PB' => '03', 'BR' => '10', 'OR' => '21',
        'CG' => '22', 'JH' => '20', 'UK' => '05', 'HP' => '02',
    ];

    // ─── PLATFORM SIGNATURE PATTERNS ─────────────────────────────────
    private const PLATFORM_SIGNATURES = [
        'amazon'   => ['amazon', 'amzn', 'marketplace facilitator'],
        'flipkart' => ['flipkart', 'fk_', 'fkm'],
        'meesho'   => ['meesho'],
        'ajio'     => ['ajio', 'reliance retail'],
        'myntra'   => ['myntra', 'myntra fashion'],
        'snapdeal' => ['snapdeal', 'sd_'],
        'nykaa'    => ['nykaa', 'fsg'],
        'jiomart'  => ['jiomart', 'jio mart', 'reliance jio'],
        'glowroad' => ['glowroad', 'glow road'],
        'shopsy'   => ['shopsy'],
        'tcs_return' => ['return', 'refund', 'cancel', 'reversal', 'credit note'],
        'tcs_sales'  => ['tcs', 'collected at source'],
        'b2cs_csv'   => ['b2cs'],
    ];

    // ─── FUZZY COLUMN ALIASES (ML-style matching) ─────────────────────
    // Each canonical key → possible header variations
    private const COL_ALIASES = [
        'taxable_value' => [
            'taxable value', 'taxable amount', 'taxable sale value',
            'total taxable value', 'total_taxable_sale_value',
            'net taxable value', 'taxablevalue', 'taxable_value',
            'taxable amt', 'base amount', 'invoice value',
            'selling price', 'net amount', 'order taxable value',
            'product taxable value', 'taxable_sale_value',
            'aggregate taxable value', 'aggregate value',
            'item taxable value', 'net_taxable_value',
        ],
        'gst_rate' => [
            'rate', 'gst rate', 'gst%', 'tax rate', 'igst rate',
            'tax %', 'gst_rate', 'applicable gst rate', 'rate(%)',
            'cgst rate', 'sgst rate', 'rate %', 'gst_percent',
            'tax_rate', 'tax rate(%)', 'applicable rate',
        ],
        'igst_rate' => [
            'igst rate', 'igst%', 'igst rate (%)', 'igst_rate',
            'integrated tax rate', 'igst rate(%)',
        ],
        'cgst_rate' => [
            'cgst rate', 'cgst%', 'cgst rate (%)', 'cgst_rate',
            'central tax rate', 'cgst rate(%)',
        ],
        'place_of_supply' => [
            'place of supply', 'pos', 'place_of_supply',
            'place of delivery', 'delivery state', 'customer state',
            'ship to state', 'shipping state', 'buyer state',
            'destination state', 'delivered state', 'state',
            'end_customer_state_new', 'end customer state',
            'customer delivery state', 'customer billing state',
            'billing state', 'recipient state', 'state name',
            'delivery_state', 'ship_state', 'state_code',
            'delivered state code', 'ship to state code',
            'customer_state', 'buyer_state', 'order state',
        ],
        'invoice_type' => [
            'invoice type', 'supply type', 'transaction type',
            'type', 'order type', 'supply_type',
        ],
    ];

    /**
     * Main API endpoint: POST /api/gstr/parse
     * Accepts: file, platform (optional), home_state, filing_month, filing_year
     */
    public function parse(Request $request)
    {
        $request->validate([
            'file'         => 'required|file|mimes:xlsx,xls,csv|max:20480',
            'home_state'   => 'required|string|size:2',
            'filing_month' => 'required|string|size:2',
            'filing_year'  => 'required|string|size:4',
            'platform'     => 'nullable|string',
        ]);

        $file      = $request->file('file');
        $homeState = $request->input('home_state');
        $platform  = $request->input('platform') ?? 'auto';

        try {
            // Auto-detect platform from filename + content if not supplied
            if ($platform === 'auto') {
                $platform = $this->detectPlatform($file->getClientOriginalName(), $file->getRealPath());
            }

            $result = $this->parseFile($file->getRealPath(), $file->getClientOriginalName(), $platform, $homeState);

            return response()->json([
                'success'   => true,
                'platform'  => $platform,
                'rows'      => $result['rows'],
                'b2cs'      => $result['b2cs'],
                'row_count' => count($result['rows']),
                'warnings'  => $result['warnings'],
                'debug'     => $result['debug'],
            ]);

        } catch (\Throwable $e) {
            Log::error('GSTR Parser error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
                'platform' => $platform,
            ], 422);
        }
    }

    /**
     * Batch parse: multiple files at once
     * POST /api/gstr/parse-batch
     */
    public function parseBatch(Request $request)
    {
        $request->validate([
            'files'        => 'required|array|min:1',
            'files.*'      => 'required|file|mimes:xlsx,xls,csv|max:20480',
            'home_state'   => 'required|string|size:2',
            'filing_month' => 'required|string|size:2',
            'filing_year'  => 'required|string|size:4',
            'platforms'    => 'nullable|array',
        ]);

        $homeState  = $request->input('home_state');
        $platforms  = $request->input('platforms', []);
        $allB2CS    = [];
        $results    = [];
        $warnings   = [];

        foreach ($request->file('files') as $idx => $file) {
            $platform = $platforms[$idx] ?? 'auto';
            if ($platform === 'auto') {
                $platform = $this->detectPlatform($file->getClientOriginalName(), $file->getRealPath());
            }

            try {
                $result = $this->parseFile($file->getRealPath(), $file->getClientOriginalName(), $platform, $homeState);

                // Merge b2cs rows
                foreach ($result['b2cs'] as $key => $row) {
                    if (!isset($allB2CS[$key])) {
                        $allB2CS[$key] = $row;
                    } else {
                        $allB2CS[$key]['txval'] = round($allB2CS[$key]['txval'] + $row['txval'], 2);
                        $allB2CS[$key]['iamt']  = round($allB2CS[$key]['iamt']  + $row['iamt'],  2);
                        $allB2CS[$key]['camt']  = round($allB2CS[$key]['camt']  + $row['camt'],  2);
                        $allB2CS[$key]['samt']  = round($allB2CS[$key]['samt']  + $row['samt'],  2);
                    }
                }

                $results[] = [
                    'file'      => $file->getClientOriginalName(),
                    'platform'  => $platform,
                    'row_count' => count($result['rows']),
                    'b2cs'      => $result['b2cs'],
                    'warnings'  => $result['warnings'],
                ];
                $warnings = array_merge($warnings, $result['warnings']);

            } catch (\Throwable $e) {
                $results[] = [
                    'file'     => $file->getClientOriginalName(),
                    'platform' => $platform,
                    'error'    => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'success'    => true,
            'results'    => $results,
            'combined_b2cs' => array_values($allB2CS),
            'warnings'   => array_unique($warnings),
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════
    // CORE: PLATFORM DETECTION
    // ═══════════════════════════════════════════════════════════════════

    private function detectPlatform(string $filename, string $filepath): string
    {
        $name = strtolower($filename);

        // 1. Filename-based detection (fastest)
        foreach (self::PLATFORM_SIGNATURES as $platform => $patterns) {
            foreach ($patterns as $pattern) {
                if (str_contains($name, $pattern)) {
                    return $platform;
                }
            }
        }

        // 2. Content-based detection (read first sheet headers)
        try {
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            if ($ext === 'csv') {
                $content = strtolower(file_get_contents($filepath, false, null, 0, 2000));
            } else {
                $spreadsheet = IOFactory::load($filepath);
                $sheet = $spreadsheet->getActiveSheet();
                $content = strtolower($sheet->getTitle() . ' ' . implode(' ', array_slice(
                    array_map(fn($row) => implode(' ', array_column($row, 'value')),
                    $sheet->toArray(null, true, true, false)),
                    0, 5
                )));
            }

            foreach (self::PLATFORM_SIGNATURES as $platform => $patterns) {
                foreach ($patterns as $pattern) {
                    if (str_contains($content, $pattern)) {
                        return $platform;
                    }
                }
            }
        } catch (\Throwable $e) {
            // Silently fall through
        }

        return 'unknown';
    }

    // ═══════════════════════════════════════════════════════════════════
    // CORE: FILE PARSER DISPATCHER
    // ═══════════════════════════════════════════════════════════════════

    private function parseFile(string $path, string $filename, string $platform, string $homeState): array
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        // Read file into rows array
        if ($ext === 'csv') {
            $allSheets = ['Sheet1' => $this->readCSV($path)];
        } else {
            $allSheets = $this->readXLSX($path);
        }

        // Route to platform-specific parser
        return match($platform) {
            'amazon'     => $this->parseAmazon($allSheets, $homeState),
            'flipkart'   => $this->parseFlipkart($allSheets, $homeState),
            'meesho'     => $this->parseMeesho($allSheets, $homeState),
            'ajio'       => $this->parseAjio($allSheets, $homeState),
            'myntra'     => $this->parseMyntra($allSheets, $homeState),
            'snapdeal'   => $this->parseSnapdeal($allSheets, $homeState),
            'nykaa'      => $this->parseNykaa($allSheets, $homeState),
            'jiomart'    => $this->parseJiomart($allSheets, $homeState),
            'glowroad'   => $this->parseGlowroad($allSheets, $homeState),
            'shopsy'     => $this->parseShopsy($allSheets, $homeState),
            'tcs_sales'  => $this->parseTCS($allSheets, $homeState, false),
            'tcs_return' => $this->parseTCS($allSheets, $homeState, true),
            'b2cs_csv'   => $this->parseB2CSCSV($allSheets, $homeState),
            default      => $this->parseGeneric($allSheets, $homeState),
        };
    }

    // ═══════════════════════════════════════════════════════════════════
    // FILE READERS
    // ═══════════════════════════════════════════════════════════════════

    private function readXLSX(string $path): array
    {
        $spreadsheet = IOFactory::load($path);
        $sheets = [];
        foreach ($spreadsheet->getAllSheets() as $sheet) {
            $sheets[$sheet->getTitle()] = $sheet->toArray(null, true, true, false);
        }
        return $sheets;
    }

    private function readCSV(string $path): array
    {
        $rows = [];
        if (($handle = fopen($path, 'r')) !== false) {
            while (($line = fgetcsv($handle, 4096)) !== false) {
                $rows[] = $line;
            }
            fclose($handle);
        }
        return $rows;
    }

    // ═══════════════════════════════════════════════════════════════════
    // ML-STYLE FUZZY COLUMN MATCHER
    // Core of the smart parsing — matches any header variation
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Find the best column index for a canonical field using:
     * 1. Exact match
     * 2. Alias match
     * 3. Levenshtein fuzzy match (max distance = 4)
     * 4. Substring contains match
     */
    private function findColumn(array $headers, string $field): int
    {
        $aliases  = self::COL_ALIASES[$field] ?? [];
        $lowered  = array_map(fn($h) => strtolower(trim((string)$h)), $headers);

        // Pass 1: Exact alias match
        foreach ($aliases as $alias) {
            $idx = array_search($alias, $lowered);
            if ($idx !== false) return (int)$idx;
        }

        // Pass 2: Contains match
        foreach ($aliases as $alias) {
            foreach ($lowered as $i => $h) {
                if (str_contains($h, $alias) || str_contains($alias, $h)) {
                    return $i;
                }
            }
        }

        // Pass 3: Levenshtein fuzzy match
        $bestScore = PHP_INT_MAX;
        $bestIdx   = -1;
        foreach ($aliases as $alias) {
            foreach ($lowered as $i => $h) {
                if (empty($h)) continue;
                $dist = levenshtein($alias, $h);
                // Allow proportional distance: max 4 for strings > 8 chars
                $maxDist = max(3, (int)(strlen($alias) * 0.35));
                if ($dist < $bestScore && $dist <= $maxDist) {
                    $bestScore = $dist;
                    $bestIdx   = $i;
                }
            }
        }

        return $bestIdx;
    }

    /**
     * Find the header row index in a sheet
     * (skips blank/summary rows at top — common in marketplace reports)
     */
    private function findHeaderRow(array $rows, array $requiredKeywords): int
    {
        foreach ($rows as $i => $row) {
            $rowStr = strtolower(implode(' ', array_map('strval', $row)));
            $matches = 0;
            foreach ($requiredKeywords as $kw) {
                if (str_contains($rowStr, $kw)) $matches++;
            }
            if ($matches >= max(1, count($requiredKeywords) - 1)) {
                return $i;
            }
        }
        return -1;
    }

    /**
     * Clean and parse numeric value from any cell
     */
    private function numVal(mixed $val): float
    {
        if ($val === null || $val === '') return 0.0;
        return (float) preg_replace('/[^\d.\-]/', '', str_replace(',', '', (string)$val));
    }

    /**
     * Normalize state name/code to 2-digit GST state code
     */
    private function normState(mixed $raw): ?string
    {
        if (!$raw) return null;
        $s = strtoupper(trim((string)$raw));

        // Remove IN- prefix (Flipkart uses IN-KA etc)
        $s = preg_replace('/^IN-?/', '', $s);

        // Direct map
        if (isset(self::STATE_MAP[$s])) return self::STATE_MAP[$s];

        // Numeric code already
        if (preg_match('/^(\d{2})/', $s, $m)) return $m[1];

        // Fuzzy: find closest state name
        $bestScore = PHP_INT_MAX;
        $bestCode  = null;
        foreach (self::STATE_MAP as $name => $code) {
            $dist = levenshtein($s, $name);
            $threshold = max(4, (int)(strlen($name) * 0.25));
            if ($dist < $bestScore && $dist <= $threshold) {
                $bestScore = $dist;
                $bestCode  = $code;
            }
        }
        return $bestCode;
    }

    private function extractPosCode(mixed $raw): ?string
    {
        if (!$raw) return null;
        $s = trim((string)$raw);
        // "29-Karnataka" style
        if (preg_match('/^(\d{2})/', $s, $m)) return $m[1];
        return $this->normState($s);
    }

    // ═══════════════════════════════════════════════════════════════════
    // B2CS ACCUMULATOR
    // ═══════════════════════════════════════════════════════════════════

    private function accumulate(array &$b2cs, string $pos, float $rt, float $txval, string $homeState): void
    {
        if (!$pos || $txval == 0) return;
        $key      = "{$pos}|{$rt}";
        $isIntra  = ($pos === $homeState);
        $splyType = $isIntra ? 'INTRA' : 'INTER';

        if (!isset($b2cs[$key])) {
            $b2cs[$key] = ['pos' => $pos, 'rt' => $rt, 'sply_ty' => $splyType, 'txval' => 0, 'iamt' => 0, 'camt' => 0, 'samt' => 0];
        }
        $b2cs[$key]['txval'] = round($b2cs[$key]['txval'] + $txval, 2);
        if ($isIntra) {
            $t = round($txval * $rt / 200, 2);
            $b2cs[$key]['camt'] = round($b2cs[$key]['camt'] + $t, 2);
            $b2cs[$key]['samt'] = round($b2cs[$key]['samt'] + $t, 2);
        } else {
            $i = round($txval * $rt / 100, 2);
            $b2cs[$key]['iamt'] = round($b2cs[$key]['iamt'] + $i, 2);
        }
    }

    private function buildResult(array $rows, array $b2cs, array $warnings, array $debug = []): array
    {
        return ['rows' => $rows, 'b2cs' => $b2cs, 'warnings' => $warnings, 'debug' => $debug];
    }

    // ═══════════════════════════════════════════════════════════════════
    // AMAZON PARSER
    // ═══════════════════════════════════════════════════════════════════

    private function parseAmazon(array $sheets, string $homeState): array
    {
        $b2cs = []; $rows = []; $warnings = []; $debug = [];

        foreach ($sheets as $sheetName => $sheetRows) {
            $sn = strtolower($sheetName);
            if (!str_contains($sn, 'b2c') && !str_contains($sn, 'small')) continue;

            $hIdx = $this->findHeaderRow($sheetRows, ['place of supply', 'rate', 'taxable']);
            if ($hIdx < 0) { $warnings[] = "Amazon: Header not found in sheet '$sheetName'"; continue; }

            $headers = array_map('strval', $sheetRows[$hIdx]);
            $posIdx  = $this->findColumn($headers, 'place_of_supply');
            $rtIdx   = $this->findColumn($headers, 'gst_rate');
            $txIdx   = $this->findColumn($headers, 'taxable_value');
            $debug[] = "Amazon '$sheetName': pos=$posIdx rt=$rtIdx tx=$txIdx";

            if ($posIdx < 0 || $txIdx < 0) { $warnings[] = "Amazon: Required columns missing in '$sheetName'"; continue; }

            for ($i = $hIdx + 1; $i < count($sheetRows); $i++) {
                $row  = $sheetRows[$i];
                $txval = $this->numVal($row[$txIdx] ?? '');
                if ($txval == 0) continue;
                $pos  = $this->extractPosCode($row[$posIdx] ?? '');
                $rt   = $rtIdx >= 0 ? $this->numVal($row[$rtIdx] ?? '') : 0;
                if (!$pos) { $warnings[] = "Amazon row $i: could not resolve state '{$row[$posIdx]}'"; continue; }
                $this->accumulate($b2cs, $pos, $rt, $txval, $homeState);
                $rows[] = ['pos' => $pos, 'rt' => $rt, 'txval' => $txval, 'platform' => 'amazon'];
            }
        }

        return $this->buildResult($rows, $b2cs, $warnings, $debug);
    }

    // ═══════════════════════════════════════════════════════════════════
    // FLIPKART PARSER
    // ═══════════════════════════════════════════════════════════════════

    private function parseFlipkart(array $sheets, string $homeState): array
    {
        $b2cs = []; $rows = []; $warnings = [];

        foreach ($sheets as $sheetName => $sheetRows) {
            $sn = strtolower($sheetName);

            // INTRA: Section 7A2 (CGST/SGST)
            if (str_contains($sn, '7') && str_contains($sn, 'a')) {
                $hIdx = $this->findHeaderRow($sheetRows, ['taxable', 'cgst']);
                if ($hIdx < 0) continue;
                $headers = array_map('strval', $sheetRows[$hIdx]);
                $txIdx   = $this->findColumn($headers, 'taxable_value');
                $cgstIdx = $this->findColumn($headers, 'cgst_rate');

                for ($i = $hIdx + 1; $i < count($sheetRows); $i++) {
                    $row   = $sheetRows[$i];
                    $txval = $this->numVal($row[$txIdx] ?? '');
                    if ($txval == 0) continue;
                    $cgstRate = $cgstIdx >= 0 ? $this->numVal($row[$cgstIdx] ?? '') : 0;
                    $this->accumulate($b2cs, $homeState, $cgstRate * 2, $txval, $homeState);
                    $rows[] = ['pos' => $homeState, 'rt' => $cgstRate * 2, 'txval' => $txval, 'platform' => 'flipkart'];
                }
            }

            // INTER: Section 7B2 (IGST)
            if (str_contains($sn, '7') && str_contains($sn, 'b')) {
                $hIdx = $this->findHeaderRow($sheetRows, ['taxable', 'igst', 'state']);
                if ($hIdx < 0) continue;
                $headers  = array_map('strval', $sheetRows[$hIdx]);
                $txIdx    = $this->findColumn($headers, 'taxable_value');
                $igstIdx  = $this->findColumn($headers, 'igst_rate');
                $stateIdx = $this->findColumn($headers, 'place_of_supply');

                for ($i = $hIdx + 1; $i < count($sheetRows); $i++) {
                    $row   = $sheetRows[$i];
                    $txval = $this->numVal($row[$txIdx] ?? '');
                    if ($txval == 0) continue;
                    $pos  = $stateIdx >= 0 ? $this->normState($row[$stateIdx] ?? '') : null;
                    $rt   = $igstIdx >= 0  ? $this->numVal($row[$igstIdx] ?? '') : 0;
                    if (!$pos) continue;
                    $this->accumulate($b2cs, $pos, $rt, $txval, $homeState);
                    $rows[] = ['pos' => $pos, 'rt' => $rt, 'txval' => $txval, 'platform' => 'flipkart'];
                }
            }
        }

        return $this->buildResult($rows, $b2cs, $warnings);
    }

    // ═══════════════════════════════════════════════════════════════════
    // MEESHO PARSER
    // ═══════════════════════════════════════════════════════════════════

    private function parseMeesho(array $sheets, string $homeState): array
    {
        $b2cs = []; $rows = []; $warnings = [];

        // Find best sheet
        $targetSheet = null;
        foreach ($sheets as $name => $sheetRows) {
            if (str_contains(strtolower($name), 'sales')) { $targetSheet = $sheetRows; break; }
        }
        $targetSheet = $targetSheet ?? array_values($sheets)[0] ?? [];

        $hIdx = $this->findHeaderRow($targetSheet, ['taxable value', 'igst rate']);
        if ($hIdx < 0) {
            $hIdx = $this->findHeaderRow($targetSheet, ['taxable', 'state', 'rate']);
        }
        if ($hIdx < 0) return $this->buildResult($rows, $b2cs, ['Meesho: Header row not found']);

        $headers  = array_map('strval', $targetSheet[$hIdx]);
        $txIdx    = $this->findColumn($headers, 'taxable_value');
        $igstIdx  = $this->findColumn($headers, 'igst_rate');
        $cgstIdx  = $this->findColumn($headers, 'cgst_rate');
        $stateIdx = $this->findColumn($headers, 'place_of_supply');

        for ($i = $hIdx + 1; $i < count($targetSheet); $i++) {
            $row   = $targetSheet[$i];
            $txval = $this->numVal($row[$txIdx] ?? '');
            if ($txval == 0) continue;
            $pos   = $stateIdx >= 0 ? $this->normState($row[$stateIdx] ?? '') : null;
            if (!$pos) continue;

            $igstRate = $igstIdx >= 0 ? $this->numVal($row[$igstIdx] ?? '') : 0;
            $cgstRate = $cgstIdx >= 0 ? $this->numVal($row[$cgstIdx] ?? '') : 0;
            $rt = $igstRate > 0 ? $igstRate : ($cgstRate * 2);

            $this->accumulate($b2cs, $pos, $rt, $txval, $homeState);
            $rows[] = ['pos' => $pos, 'rt' => $rt, 'txval' => $txval, 'platform' => 'meesho'];
        }

        return $this->buildResult($rows, $b2cs, $warnings);
    }

    // ═══════════════════════════════════════════════════════════════════
    // AJIO PARSER
    // Ajio (Reliance) GST report format
    // Columns: Order ID, State, HSN, GST Rate, Taxable Amount, IGST, CGST, SGST
    // ═══════════════════════════════════════════════════════════════════

    private function parseAjio(array $sheets, string $homeState): array
    {
        $b2cs = []; $rows = []; $warnings = [];

        foreach ($sheets as $sheetName => $sheetRows) {
            $sn = strtolower($sheetName);
            // Skip summary/lookup sheets
            if (str_contains($sn, 'summary') || str_contains($sn, 'lookup')) continue;

            $hIdx = $this->findHeaderRow($sheetRows, ['taxable', 'state', 'gst']);
            if ($hIdx < 0) continue;

            $headers  = array_map('strval', $sheetRows[$hIdx]);
            $txIdx    = $this->findColumn($headers, 'taxable_value');
            $rtIdx    = $this->findColumn($headers, 'gst_rate');
            $stateIdx = $this->findColumn($headers, 'place_of_supply');

            if ($txIdx < 0 || $stateIdx < 0) { $warnings[] = "Ajio: Required columns missing in '$sheetName'"; continue; }

            for ($i = $hIdx + 1; $i < count($sheetRows); $i++) {
                $row   = $sheetRows[$i];
                $txval = $this->numVal($row[$txIdx] ?? '');
                if ($txval == 0) continue;
                $pos = $this->normState($row[$stateIdx] ?? '');
                if (!$pos) continue;
                $rt = $rtIdx >= 0 ? $this->numVal($row[$rtIdx] ?? '') : 0;
                $this->accumulate($b2cs, $pos, $rt, $txval, $homeState);
                $rows[] = ['pos' => $pos, 'rt' => $rt, 'txval' => $txval, 'platform' => 'ajio'];
            }
        }

        return $this->buildResult($rows, $b2cs, $warnings);
    }

    // ═══════════════════════════════════════════════════════════════════
    // MYNTRA PARSER
    // Myntra (Flipkart group) — similar to Flipkart format
    // Columns: Order ID, Ship To State, GST Rate, Taxable Value
    // ═══════════════════════════════════════════════════════════════════

    private function parseMyntra(array $sheets, string $homeState): array
    {
        $b2cs = []; $rows = []; $warnings = [];

        foreach ($sheets as $sheetName => $sheetRows) {
            $hIdx = $this->findHeaderRow($sheetRows, ['taxable', 'state']);
            if ($hIdx < 0) continue;

            $headers  = array_map('strval', $sheetRows[$hIdx]);
            $txIdx    = $this->findColumn($headers, 'taxable_value');
            $rtIdx    = $this->findColumn($headers, 'gst_rate');
            $stateIdx = $this->findColumn($headers, 'place_of_supply');

            if ($txIdx < 0 || $stateIdx < 0) { $warnings[] = "Myntra: Required columns missing"; continue; }

            for ($i = $hIdx + 1; $i < count($sheetRows); $i++) {
                $row   = $sheetRows[$i];
                $txval = $this->numVal($row[$txIdx] ?? '');
                if ($txval == 0) continue;
                $pos = $this->normState($row[$stateIdx] ?? '');
                if (!$pos) continue;
                $rt = $rtIdx >= 0 ? $this->numVal($row[$rtIdx] ?? '') : 0;
                $this->accumulate($b2cs, $pos, $rt, $txval, $homeState);
                $rows[] = ['pos' => $pos, 'rt' => $rt, 'txval' => $txval, 'platform' => 'myntra'];
            }
        }

        return $this->buildResult($rows, $b2cs, $warnings);
    }

    // ═══════════════════════════════════════════════════════════════════
    // SNAPDEAL PARSER
    // ═══════════════════════════════════════════════════════════════════

    private function parseSnapdeal(array $sheets, string $homeState): array
    {
        $b2cs = []; $rows = []; $warnings = [];

        foreach ($sheets as $sheetName => $sheetRows) {
            $hIdx = $this->findHeaderRow($sheetRows, ['taxable', 'state', 'rate']);
            if ($hIdx < 0) continue;

            $headers  = array_map('strval', $sheetRows[$hIdx]);
            $txIdx    = $this->findColumn($headers, 'taxable_value');
            $rtIdx    = $this->findColumn($headers, 'gst_rate');
            $stateIdx = $this->findColumn($headers, 'place_of_supply');

            if ($txIdx < 0) { $warnings[] = "Snapdeal: Taxable value column missing"; continue; }

            for ($i = $hIdx + 1; $i < count($sheetRows); $i++) {
                $row   = $sheetRows[$i];
                $txval = $this->numVal($row[$txIdx] ?? '');
                if ($txval == 0) continue;
                $pos = $stateIdx >= 0 ? $this->normState($row[$stateIdx] ?? '') : $homeState;
                if (!$pos) $pos = $homeState;
                $rt = $rtIdx >= 0 ? $this->numVal($row[$rtIdx] ?? '') : 0;
                $this->accumulate($b2cs, $pos, $rt, $txval, $homeState);
                $rows[] = ['pos' => $pos, 'rt' => $rt, 'txval' => $txval, 'platform' => 'snapdeal'];
            }
        }

        return $this->buildResult($rows, $b2cs, $warnings);
    }

    // ═══════════════════════════════════════════════════════════════════
    // NYKAA PARSER
    // ═══════════════════════════════════════════════════════════════════

    private function parseNykaa(array $sheets, string $homeState): array
    {
        // Nykaa uses similar column structure to Meesho/Myntra
        return $this->parseGenericPlatform($sheets, $homeState, 'nykaa');
    }

    // ═══════════════════════════════════════════════════════════════════
    // JIOMART PARSER
    // JioMart uses Reliance's internal format — similar to Ajio
    // ═══════════════════════════════════════════════════════════════════

    private function parseJiomart(array $sheets, string $homeState): array
    {
        return $this->parseGenericPlatform($sheets, $homeState, 'jiomart');
    }

    // ═══════════════════════════════════════════════════════════════════
    // GLOWROAD PARSER
    // ═══════════════════════════════════════════════════════════════════

    private function parseGlowroad(array $sheets, string $homeState): array
    {
        return $this->parseGenericPlatform($sheets, $homeState, 'glowroad');
    }

    // ═══════════════════════════════════════════════════════════════════
    // SHOPSY PARSER (Flipkart's Shopsy)
    // ═══════════════════════════════════════════════════════════════════

    private function parseShopsy(array $sheets, string $homeState): array
    {
        // Shopsy mirrors Flipkart format
        return $this->parseFlipkart($sheets, $homeState);
    }

    // ═══════════════════════════════════════════════════════════════════
    // GENERIC PLATFORM PARSER
    // Used for new/unknown platforms — pure fuzzy matching
    // ═══════════════════════════════════════════════════════════════════

    private function parseGenericPlatform(array $sheets, string $homeState, string $platform): array
    {
        $b2cs = []; $rows = []; $warnings = [];

        foreach ($sheets as $sheetName => $sheetRows) {
            $hIdx = $this->findHeaderRow($sheetRows, ['taxable', 'state']);
            if ($hIdx < 0) {
                $hIdx = $this->findHeaderRow($sheetRows, ['taxable', 'rate']);
            }
            if ($hIdx < 0) continue;

            $headers  = array_map('strval', $sheetRows[$hIdx]);
            $txIdx    = $this->findColumn($headers, 'taxable_value');
            $rtIdx    = $this->findColumn($headers, 'gst_rate');
            $igstIdx  = $this->findColumn($headers, 'igst_rate');
            $cgstIdx  = $this->findColumn($headers, 'cgst_rate');
            $stateIdx = $this->findColumn($headers, 'place_of_supply');

            if ($txIdx < 0) { $warnings[] = "$platform: Taxable value column not found in '$sheetName'"; continue; }

            $found = 0;
            for ($i = $hIdx + 1; $i < count($sheetRows); $i++) {
                $row   = $sheetRows[$i];
                $txval = $this->numVal($row[$txIdx] ?? '');
                if ($txval == 0) continue;

                $pos = null;
                if ($stateIdx >= 0 && !empty($row[$stateIdx])) {
                    $pos = $this->normState($row[$stateIdx]);
                }
                if (!$pos) $pos = $homeState; // fallback to home state (intra)

                // Determine rate
                $rt = 0;
                if ($rtIdx >= 0) $rt = $this->numVal($row[$rtIdx] ?? '');
                if ($rt == 0 && $igstIdx >= 0) $rt = $this->numVal($row[$igstIdx] ?? '');
                if ($rt == 0 && $cgstIdx >= 0) $rt = $this->numVal($row[$cgstIdx] ?? '') * 2;

                $this->accumulate($b2cs, $pos, $rt, $txval, $homeState);
                $rows[] = ['pos' => $pos, 'rt' => $rt, 'txval' => $txval, 'platform' => $platform];
                $found++;
            }

            if ($found === 0) $warnings[] = "$platform: No valid rows found in '$sheetName'";
        }

        return $this->buildResult($rows, $b2cs, $warnings);
    }

    private function parseGeneric(array $sheets, string $homeState): array
    {
        return $this->parseGenericPlatform($sheets, $homeState, 'unknown');
    }

    // ═══════════════════════════════════════════════════════════════════
    // TCS PARSER (Sales + Returns)
    // ═══════════════════════════════════════════════════════════════════

    private function parseTCS(array $sheets, string $homeState, bool $isReturn): array
    {
        $b2cs = []; $rows = []; $warnings = [];
        $label = $isReturn ? 'TCS Return' : 'TCS Sales';
        $sheetRows = array_values($sheets)[0] ?? [];

        $hIdx = $this->findHeaderRow($sheetRows, ['taxable', 'state']);
        if ($hIdx < 0) return $this->buildResult($rows, $b2cs, ["$label: Header row not found"]);

        $headers  = array_map('strval', $sheetRows[$hIdx]);
        $txIdx    = $this->findColumn($headers, 'taxable_value');
        $stateIdx = $this->findColumn($headers, 'place_of_supply');
        $rtIdx    = $this->findColumn($headers, 'gst_rate');

        if ($txIdx < 0 || $stateIdx < 0) return $this->buildResult($rows, $b2cs, ["$label: Required columns missing"]);

        for ($i = $hIdx + 1; $i < count($sheetRows); $i++) {
            $row   = $sheetRows[$i];
            $txval = $this->numVal($row[$txIdx] ?? '');
            if ($txval == 0) continue;
            $pos = $this->normState($row[$stateIdx] ?? '');
            if (!$pos) continue;
            $rt = $rtIdx >= 0 ? $this->numVal($row[$rtIdx] ?? '') : 0;
            $finalVal = $isReturn ? -abs($txval) : abs($txval);
            $this->accumulate($b2cs, $pos, $rt, $finalVal, $homeState);
            $rows[] = ['pos' => $pos, 'rt' => $rt, 'txval' => $finalVal, 'platform' => $isReturn ? 'tcs_return' : 'tcs_sales'];
        }

        return $this->buildResult($rows, $b2cs, $warnings);
    }

    // ═══════════════════════════════════════════════════════════════════
    // B2CS CSV PARSER
    // ═══════════════════════════════════════════════════════════════════

    private function parseB2CSCSV(array $sheets, string $homeState): array
    {
        $b2cs = []; $rows = []; $warnings = [];
        $sheetRows = array_values($sheets)[0] ?? [];
        if (count($sheetRows) < 2) return $this->buildResult($rows, $b2cs, ['B2CS CSV: Empty file']);

        $headers  = array_map('strval', $sheetRows[0]);
        $posIdx   = $this->findColumn($headers, 'place_of_supply');
        $rtIdx    = $this->findColumn($headers, 'gst_rate');
        $txIdx    = $this->findColumn($headers, 'taxable_value');

        if ($posIdx < 0 || $txIdx < 0) return $this->buildResult($rows, $b2cs, ['B2CS CSV: Invalid format']);

        for ($i = 1; $i < count($sheetRows); $i++) {
            $row   = $sheetRows[$i];
            $txval = $this->numVal($row[$txIdx] ?? '');
            if ($txval == 0) continue;
            $pos = $this->extractPosCode($row[$posIdx] ?? '');
            $rt  = $rtIdx >= 0 ? $this->numVal($row[$rtIdx] ?? '') : 0;
            if (!$pos) continue;
            $this->accumulate($b2cs, $pos, $rt, $txval, $homeState);
            $rows[] = ['pos' => $pos, 'rt' => $rt, 'txval' => $txval, 'platform' => 'b2cs_csv'];
        }

        return $this->buildResult($rows, $b2cs, $warnings);
    }
}
