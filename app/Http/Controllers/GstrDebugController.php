<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class GstrDebugController extends Controller
{
    /**
     * Debug endpoint to inspect Excel file structure
     * Usage: POST /gstr/debug-file with 'file' form parameter
     */
    public function inspectFile(Request $request)
    {
        try {
            $request->validate(['file' => 'required|file']);

            $file = $request->file('file');
            $ext = strtolower($file->getClientOriginalExtension());

            if (!in_array($ext, ['xlsx', 'xls'])) {
                return response()->json(['error' => 'Only Excel files supported'], 400);
            }

            // Save temp file
            $tempDir = storage_path('app/temp/gstr');
            if (!is_dir($tempDir)) mkdir($tempDir, 0755, true);

            $tempPath = $tempDir . '/' . uniqid('debug_', true) . '.' . $ext;
            $file->move($tempDir, basename($tempPath));

            // Load spreadsheet
            $spreadsheet = IOFactory::load($tempPath);

            // Inspect all sheets
            $sheetInfo = [];
            foreach ($spreadsheet->getAllSheets() as $sheet) {
                $sheetName = $sheet->getTitle();

                // Get all data
                $allRows = $sheet->toArray(null, false, false, false);

                // Count non-empty rows
                $nonEmptyRows = array_filter($allRows, function ($row) {
                    foreach ($row as $cell) {
                        if ($cell !== null && trim((string)$cell) !== '') {
                            return true;
                        }
                    }
                    return false;
                });

                $sheetInfo[] = [
                    'name'            => $sheetName,
                    'total_rows'      => count($allRows),
                    'non_empty_rows'  => count($nonEmptyRows),
                    'total_columns'   => count($allRows[0] ?? []),
                    'preview'         => array_slice($nonEmptyRows, 0, 5),
                    'has_header'      => !empty($nonEmptyRows),
                    'first_few_cells' => array_slice($allRows[0] ?? [], 0, 10),
                ];
            }

            @unlink($tempPath);

            return response()->json([
                'filename'    => $file->getClientOriginalName(),
                'file_size'   => $file->getSize(),
                'sheets_count' => count($sheetInfo),
                'sheets'      => $sheetInfo,
                'suggestion'  => $this->analyzeSheetsForAmazon($sheetInfo),
            ]);

        } catch (\Throwable $e) {
            Log::error('Debug inspect error', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Analyze sheets and suggest which ones are valid for Amazon parser
     */
    private function analyzeSheetsForAmazon(array $sheetInfo): array
    {
        $amazonPatterns = ['b2c small', 'b2c large', 'b2b', 'gstr', 'mtr'];
        $suggestions = [];

        foreach ($sheetInfo as $sheet) {
            $sheetNameLower = strtolower($sheet['name']);

            // Check if sheet name matches Amazon patterns
            $isAmazonSheet = false;
            $matchedPattern = null;

            foreach ($amazonPatterns as $pattern) {
                if (str_contains($sheetNameLower, $pattern)) {
                    $isAmazonSheet = true;
                    $matchedPattern = $pattern;
                    break;
                }
            }

            $status = 'unknown';
            $reason = '';

            if ($sheet['total_rows'] === 0) {
                $status = 'empty';
                $reason = 'Sheet has no data rows';
            } elseif ($sheet['non_empty_rows'] === 0) {
                $status = 'no_valid_data';
                $reason = 'All rows are empty or contain only whitespace';
            } elseif (!$isAmazonSheet) {
                $status = 'not_amazon_format';
                $reason = "Sheet name '{$sheet['name']}' doesn't match Amazon patterns (expected: b2c small, b2c large, b2b, gstr, mtr)";
            } else {
                $status = 'valid';
                $reason = "Matches Amazon pattern: '{$matchedPattern}'";
            }

            $suggestions[] = [
                'sheet_name'  => $sheet['name'],
                'status'      => $status,
                'reason'      => $reason,
                'row_count'   => $sheet['non_empty_rows'],
                'matched_pattern' => $matchedPattern,
            ];
        }

        return $suggestions;
    }
}
