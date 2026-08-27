<?php

namespace App\Services;

use Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Illuminate\Support\Str;

class IntelligentFileParser
{
    private array $stateMap = [
        "01" => "Jammu & Kashmir", "02" => "Himachal Pradesh", "03" => "Punjab",
        "04" => "Chandigarh", "05" => "Uttarakhand", "06" => "Haryana", "07" => "Delhi",
        "08" => "Rajasthan", "09" => "Uttar Pradesh", "10" => "Bihar", "11" => "Sikkim",
        "12" => "Arunachal Pradesh", "13" => "Nagaland", "14" => "Manipur",
        "15" => "Mizoram", "16" => "Tripura", "17" => "Meghalaya", "18" => "Assam",
        "19" => "West Bengal", "20" => "Jharkhand", "21" => "Odisha", "22" => "Chhattisgarh",
        "23" => "Madhya Pradesh", "24" => "Gujarat", "26" => "Dadra & Nagar Haveli",
        "27" => "Maharashtra", "29" => "Karnataka", "30" => "Goa", "31" => "Lakshadweep",
        "32" => "Kerala", "33" => "Tamil Nadu", "34" => "Puducherry",
        "35" => "Andaman & Nicobar", "36" => "Telangana", "37" => "Andhra Pradesh", "38" => "Ladakh"
    ];

    private array $platformPatterns = [
        'amazon' => ['seller order id', 'sku', 'fnsku', 'asin', 'product name'],
        'flipkart' => ['order number', 'shipment', 'order item id', 'hsn'],
        'meesho' => ['order id', 'supplier', 'order date', 'quantity'],
        'ajio' => ['order id', 'article number', 'delivery address'],
        'myntra' => ['order id', 'style code', 'size'],
        'snapdeal' => ['order id', 'sku', 'seller'],
        'nykaa' => ['order id', 'product', 'category'],
        'jiomart' => ['order id', 'item', 'quantity'],
        'glowroad' => ['order id', 'product', 'seller'],
        'shopsy' => ['order id', 'catalog id', 'sku'],
    ];

    /**
     * Parse file and return standardized transaction data
     */
    public function parseFile($filePath, $platform = null)
    {
        $file = new \SplFileInfo($filePath);
        $ext = strtolower($file->getExtension());

        // Auto-detect platform if not provided
        if (!$platform) {
            $platform = $this->detectPlatform($filePath);
        }

        if ($ext === 'csv') {
            return $this->parseCSV($filePath, $platform);
        } elseif (in_array($ext, ['xlsx', 'xls'])) {
            return $this->parseExcel($filePath, $platform);
        } else {
            throw new Exception("Unsupported file format: $ext");
        }
    }

    /**
     * Detect platform from filename or content
     */
    public function detectPlatform($filePath)
    {
        $filename = strtolower(basename($filePath));

        foreach ($this->platformPatterns as $platform => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($filename, $keyword) !== false || strpos($filename, $platform) !== false) {
                    return $platform;
                }
            }
        }

        // If filename detection fails, check header row
        return $this->detectFromHeaders($filePath);
    }

    /**
     * Parse Excel file intelligently
     */
    private function parseExcel($filePath, $platform)
    {
        try {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            if (empty($rows)) {
                throw new Exception("Excel file is empty");
            }

            // Find header row
            $headerRow = $this->findHeaderRow($rows);
            if ($headerRow === -1) {
                throw new Exception("Could not detect header row");
            }

            $headers = $this->normalizeHeaders($rows[$headerRow]);
            $columnMap = $this->mapColumns($headers, $platform);

            $transactions = [];
            for ($i = $headerRow + 1; $i < count($rows); $i++) {
                $row = $rows[$i];

                // Skip empty rows
                if ($this->isEmptyRow($row)) {
                    continue;
                }

                try {
                    $transaction = $this->parseRow($row, $columnMap, $platform);
                    if ($transaction) {
                        $transaction['raw_data'] = json_encode($row);
                        $transaction['confidence_score'] = $this->calculateConfidence($transaction, $row);
                        $transactions[] = $transaction;
                    }
                } catch (Exception $e) {
                    \Log::warning("Error parsing row {$i}: " . $e->getMessage());
                    continue;
                }
            }

            return $transactions;

        } catch (Exception $e) {
            throw new Exception("Error parsing Excel: " . $e->getMessage());
        }
    }

    /**
     * Parse CSV file intelligently
     */
    private function parseCSV($filePath, $platform)
    {
        $rows = [];
        if (($handle = fopen($filePath, 'r')) !== false) {
            while (($row = fgetcsv($handle)) !== false) {
                $rows[] = $row;
            }
            fclose($handle);
        }

        if (empty($rows)) {
            throw new Exception("CSV file is empty");
        }

        $headerRow = $this->findHeaderRow($rows);
        if ($headerRow === -1) {
            throw new Exception("Could not detect header row");
        }

        $headers = $this->normalizeHeaders($rows[$headerRow]);
        $columnMap = $this->mapColumns($headers, $platform);

        $transactions = [];
        for ($i = $headerRow + 1; $i < count($rows); $i++) {
            $row = $rows[$i];

            if ($this->isEmptyRow($row)) {
                continue;
            }

            try {
                $transaction = $this->parseRow($row, $columnMap, $platform);
                if ($transaction) {
                    $transaction['raw_data'] = json_encode($row);
                    $transaction['confidence_score'] = $this->calculateConfidence($transaction, $row);
                    $transactions[] = $transaction;
                }
            } catch (Exception $e) {
                \Log::warning("Error parsing row {$i}: " . $e->getMessage());
                continue;
            }
        }

        return $transactions;
    }

    /**
     * Find header row by detecting common keywords
     */
    private function findHeaderRow($rows)
    {
        $headerKeywords = ['order', 'date', 'amount', 'quantity', 'hsn', 'gst', 'tax', 'invoice', 'state'];

        for ($i = 0; $i < min(10, count($rows)); $i++) {
            $row = $rows[$i];
            $rowText = implode(' ', $row);
            $rowTextLower = strtolower($rowText);

            $matchCount = 0;
            foreach ($headerKeywords as $keyword) {
                if (strpos($rowTextLower, $keyword) !== false) {
                    $matchCount++;
                }
            }

            // If row contains 3+ header keywords, it's likely the header
            if ($matchCount >= 3) {
                return $i;
            }
        }

        return 0; // Default to first row
    }

    /**
     * Normalize header names for matching
     */
    private function normalizeHeaders($headers)
    {
        return array_map(function ($h) {
            return strtolower(trim($h));
        }, $headers);
    }

    /**
     * Map raw columns to standard transaction fields using fuzzy matching
     */
    private function mapColumns($headers, $platform)
    {
        $fieldPatterns = [
            'invoice_number' => ['order', 'invoice', 'bill', 'transaction', 'order id', 'order_id'],
            'invoice_date' => ['date', 'order date', 'bill date', 'transaction date'],
            'quantity' => ['qty', 'quantity', 'units', 'nos'],
            'taxable_value' => ['amount', 'value', 'price', 'taxable', 'gross', 'subtotal'],
            'gst_rate' => ['gst%', 'rate %', 'rate', 'gst rate', '%'],
            'cgst_amount' => ['cgst', 'central gst'],
            'sgst_amount' => ['sgst', 'state gst'],
            'igst_amount' => ['igst', 'integrated gst'],
            'invoice_value' => ['total', 'final amount', 'invoice value', 'net'],
            'place_of_supply' => ['state', 'destination', 'delivery state', 'pos', 'place of supply'],
            'hsn_code' => ['hsn', 'hsn code'],
            'product_description' => ['product', 'item', 'description'],
            'buyer_gstin' => ['buyer gstin', 'customer gstin'],
            'buyer_name' => ['buyer', 'customer', 'sold to'],
            'cess_amount' => ['cess'],
            'tcs_amount' => ['tcs'],
        ];

        $columnMap = [];

        foreach ($fieldPatterns as $field => $patterns) {
            foreach ($headers as $colIndex => $header) {
                foreach ($patterns as $pattern) {
                    if ($this->fuzzyMatch($header, $pattern)) {
                        $columnMap[$field] = $colIndex;
                        break 2;
                    }
                }
            }
        }

        return $columnMap;
    }

    /**
     * Fuzzy string matching
     */
    private function fuzzyMatch($str1, $str2)
    {
        $str1 = strtolower(trim($str1));
        $str2 = strtolower(trim($str2));

        // Exact match
        if ($str1 === $str2) {
            return true;
        }

        // Contains match
        if (strpos($str1, $str2) !== false || strpos($str2, $str1) !== false) {
            return true;
        }

        // Levenshtein distance for typos (>80% match)
        $distance = levenshtein($str1, $str2);
        $maxLen = max(strlen($str1), strlen($str2));
        $similarity = 1 - ($distance / $maxLen);

        return $similarity >= 0.8;
    }

    /**
     * Parse individual row to transaction
     */
    private function parseRow($row, $columnMap, $platform)
    {
        $transaction = [
            'platform' => $platform,
            'supply_type' => 'B2C',
            'nature_of_supply' => 'Sale',
        ];

        // Extract values using column map
        foreach ($columnMap as $field => $colIndex) {
            if (isset($row[$colIndex])) {
                $value = trim($row[$colIndex]);

                switch ($field) {
                    case 'invoice_date':
                        $transaction[$field] = $this->parseDate($value);
                        break;

                    case 'quantity':
                    case 'taxable_value':
                    case 'gst_rate':
                    case 'cgst_amount':
                    case 'sgst_amount':
                    case 'igst_amount':
                    case 'cess_amount':
                    case 'tcs_amount':
                    case 'invoice_value':
                        $transaction[$field] = $this->parseNumber($value);
                        break;

                    case 'place_of_supply':
                        $transaction[$field] = $this->parseState($value);
                        break;

                    default:
                        $transaction[$field] = $value;
                }
            }
        }

        // Calculate missing values
        if (empty($transaction['taxable_value']) && isset($transaction['invoice_value']) && isset($transaction['gst_rate'])) {
            $rate = $transaction['gst_rate'] / 100;
            $transaction['taxable_value'] = $transaction['invoice_value'] / (1 + $rate);
        }

        // Calculate tax amounts if missing
        if ($transaction['taxable_value'] > 0 && $transaction['gst_rate'] > 0) {
            $gstAmount = $transaction['taxable_value'] * ($transaction['gst_rate'] / 100);

            if (!isset($transaction['cgst_amount']) || $transaction['cgst_amount'] == 0) {
                if (!isset($transaction['igst_amount']) || $transaction['igst_amount'] == 0) {
                    $transaction['cgst_amount'] = $gstAmount / 2;
                    $transaction['sgst_amount'] = $gstAmount / 2;
                } else {
                    $transaction['cgst_amount'] = 0;
                    $transaction['sgst_amount'] = 0;
                }
            }

            if (!isset($transaction['invoice_value']) || $transaction['invoice_value'] == 0) {
                $transaction['invoice_value'] = $transaction['taxable_value'] + $gstAmount;
            }
        }

        // Validate required fields
        if (empty($transaction['invoice_number'])) {
            $transaction['invoice_number'] = uniqid($platform . '_');
        }

        if (empty($transaction['place_of_supply'])) {
            throw new Exception("Place of supply not found");
        }

        if (empty($transaction['taxable_value'])) {
            throw new Exception("Taxable value not found");
        }

        $transaction['total_tax'] = ($transaction['cgst_amount'] ?? 0) +
                                   ($transaction['sgst_amount'] ?? 0) +
                                   ($transaction['igst_amount'] ?? 0) +
                                   ($transaction['cess_amount'] ?? 0);

        return $transaction;
    }

    /**
     * Parse date from various formats
     */
    private function parseDate($dateString)
    {
        if (empty($dateString)) {
            return null;
        }

        $formats = [
            'd/m/Y', 'd-m-Y', 'Y/m/d', 'Y-m-d', 'd.m.Y', 'm/d/Y',
            'd/m/y', 'd-m-y', 'Y/m/d H:i', 'Y-m-d H:i'
        ];

        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $dateString);
            if ($date !== false) {
                return $date->format('Y-m-d');
            }
        }

        // Try strtotime as fallback
        try {
            return date('Y-m-d', strtotime($dateString));
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Parse number, handling currency symbols and commas
     */
    private function parseNumber($value)
    {
        if (empty($value) || $value === '-' || $value === 'N/A') {
            return 0;
        }

        // Remove currency symbols and spaces
        $value = preg_replace('/[^0-9.-]/', '', $value);
        $value = str_replace(',', '', $value);

        return (float) $value;
    }

    /**
     * Parse state code or name to standard format
     */
    private function parseState($value)
    {
        if (empty($value)) {
            return null;
        }

        $value = trim($value);

        // Try to find by state code
        if (isset($this->stateMap[$value])) {
            return $value;
        }

        // Try to find by state name
        foreach ($this->stateMap as $code => $name) {
            if (strtolower($name) === strtolower($value) ||
                strpos(strtolower($value), strtolower($name)) !== false) {
                return $code;
            }
        }

        // Extract first 2 digits if it's part of GSTIN
        if (strlen($value) >= 2 && is_numeric(substr($value, 0, 2))) {
            $code = substr($value, 0, 2);
            if (isset($this->stateMap[$code])) {
                return $code;
            }
        }

        return null;
    }

    /**
     * Check if row is empty
     */
    private function isEmptyRow($row)
    {
        foreach ($row as $cell) {
            if (!empty(trim($cell))) {
                return false;
            }
        }
        return true;
    }

    /**
     * Detect platform from headers
     */
    private function detectFromHeaders($filePath)
    {
        try {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $headerRow = $worksheet->toArray()[0] ?? [];
            $headerText = implode(' ', $headerRow);
            $headerLower = strtolower($headerText);

            foreach ($this->platformPatterns as $platform => $keywords) {
                $matches = 0;
                foreach ($keywords as $keyword) {
                    if (strpos($headerLower, $keyword) !== false) {
                        $matches++;
                    }
                }
                if ($matches >= 2) {
                    return $platform;
                }
            }
        } catch (Exception $e) {
            //
        }

        return 'unknown';
    }

    /**
     * Calculate parsing confidence score (0-1)
     */
    private function calculateConfidence($transaction, $rawRow)
    {
        $score = 0.5; // Base score

        // Check for complete required fields
        if (!empty($transaction['invoice_number'])) $score += 0.1;
        if (!empty($transaction['invoice_date'])) $score += 0.1;
        if (!empty($transaction['place_of_supply'])) $score += 0.1;
        if ($transaction['taxable_value'] > 0) $score += 0.1;
        if ($transaction['gst_rate'] > 0) $score += 0.05;
        if ($transaction['total_tax'] > 0) $score += 0.05;

        // Bonus for matching calculated totals
        if (abs($transaction['total_tax'] - (($transaction['taxable_value'] * $transaction['gst_rate']) / 100)) < 1) {
            $score += 0.1;
        }

        return min($score, 1.0);
    }
}
