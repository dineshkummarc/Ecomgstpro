<?php

namespace App\Support;

/**
 * ══════════════════════════════════════════════════════════════════════
 *  GstinFileScanner — UNIVERSAL, PLATFORM-AGNOSTIC GSTIN PRESENCE CHECK
 * ══════════════════════════════════════════════════════════════════════
 *
 *  PROBLEM THIS SOLVES:
 *  Right now, upload-time validation (GstrUploadController::validateGstin /
 *  validateGstinCsv) samples a SINGLE GSTIN out of the uploaded file
 *  (usually from the first data row, or the first row containing a
 *  15-char-looking value) and compares it to the selected client's
 *  GSTIN. If they don't match, the whole upload is REJECTED with the
 *  "GSTIN Mismatch" popup — even if the file is a legitimate multi-GSTIN
 *  export (common for sellers registered in multiple states — Amazon,
 *  Flipkart, Meesho etc. can all bundle several state-GSTINs of the same
 *  PAN into one consolidated report) and the selected client's GSTIN
 *  DOES appear somewhere in that file, just not in the row that got
 *  sampled.
 *
 *  FIX:
 *  Don't sample one GSTIN and compare. Instead, scan every cell of
 *  every row in the file for the EXACT selected-client GSTIN string.
 *  - If found anywhere → the file is relevant to this client. Allow the
 *    upload to proceed to the platform parser, which is responsible for
 *    filtering OUT rows belonging to any other GSTIN (see AmazonParser
 *    v8's per-row 'seller_gstin' filter for the reference pattern —
 *    the same per-row filter should exist in every platform parser).
 *  - If NOT found anywhere → genuinely the wrong file. Show the
 *    mismatch popup, exactly as today.
 *
 *  WHY THIS WORKS FOR **EVERY** PLATFORM WITHOUT KNOWING COLUMN NAMES:
 *  A GSTIN is a fixed, unambiguous 15-character token
 *  (2 digits + 10-char PAN + 1 entity code + 'Z' + 1 checksum char).
 *  Whether Amazon calls its column "Seller Gstin", Flipkart calls it
 *  something else, Meesho/Myntra/JioMart/Snapdeal/Ajio all have their
 *  own header names — this scanner doesn't care. It just looks for the
 *  literal GSTIN VALUE anywhere in the parsed grid (or raw file text as
 *  a fallback for formats not yet grid-parsed). That means ONE scanner
 *  covers all seven platforms without a single platform-specific alias
 *  list to maintain here.
 *
 *  USAGE (wire this into GstrUploadController, replacing the old
 *  single-sample validateGstin()/validateGstinCsv() check):
 *
 *      use App\Support\GstinFileScanner;
 *
 *      $sheets = $this->readCsvOrExcel($filePath); // however you already read it
 *      if (!GstinFileScanner::fileContainsGstin($sheets, $client->gstin)) {
 *          return response()->json([
 *              'gstin_mismatch' => true,
 *              'found_gstin'    => GstinFileScanner::firstGstinFound($sheets),
 *              'expected_gstin' => $client->gstin,
 *          ], 422);
 *      }
 *      // else: proceed to the normal platform parser as today.
 *
 *  This file has ZERO dependency on any specific parser or platform —
 *  it can be called before ANY of them (Amazon, Flipkart, Meesho,
 *  Myntra, JioMart, Snapdeal, Ajio) with the exact same two lines.
 * ══════════════════════════════════════════════════════════════════════
 */
class GstinFileScanner
{
    // Standard GSTIN shape: 2-digit state code, 10-char PAN, 1 entity
    // code, literal 'Z', 1 checksum char. Case-insensitive on input,
    // always compared in uppercase.
    private const GSTIN_REGEX = '/^\d{2}[A-Z]{5}\d{4}[A-Z]{1}[A-Z0-9]{1}Z[A-Z0-9]{1}$/';

    /**
     * Does this GSTIN appear ANYWHERE in the file, in any cell, on any
     * sheet/row? This is the ONLY question upload-time validation should
     * ask — not "does row 1's GSTIN match".
     *
     * @param array<string, array<int, array<int, mixed>>> $sheets  sheetName => rows => cells
     *                                                               (exactly what readCsv()/readExcel()
     *                                                               already returns in this codebase)
     * @param string $clientGstin  the currently-selected client's GSTIN
     */
    public static function fileContainsGstin(array $sheets, string $clientGstin): bool
    {
        $target = strtoupper(trim($clientGstin));
        if ($target === '') return false;

        foreach ($sheets as $rows) {
            foreach ($rows as $row) {
                foreach ($row as $cell) {
                    if (self::cellMatches($cell, $target)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Convenience for the error-message path: what GSTIN(s) WERE found
     * in the file, so the mismatch popup can show something useful
     * (mirrors the "GSTIN found in the file" box in the current modal).
     * Returns up to $limit distinct GSTIN-shaped values found, in the
     * order first encountered.
     *
     * @return string[]
     */
    public static function allGstinsFound(array $sheets, int $limit = 10): array
    {
        $found = [];

        foreach ($sheets as $rows) {
            foreach ($rows as $row) {
                foreach ($row as $cell) {
                    $val = strtoupper(trim((string)$cell));
                    if ($val !== '' && preg_match(self::GSTIN_REGEX, $val) && !in_array($val, $found, true)) {
                        $found[] = $val;
                        if (count($found) >= $limit) {
                            return $found;
                        }
                    }
                }
            }
        }

        return $found;
    }

    /**
     * Back-compat helper for the existing "GSTIN found in the file" box
     * in the mismatch modal — just the first one, same as today's
     * single-sample behaviour, but now only used for DISPLAY, never for
     * the pass/fail decision.
     */
    public static function firstGstinFound(array $sheets): ?string
    {
        $all = self::allGstinsFound($sheets, 1);
        return $all[0] ?? null;
    }

    /**
     * True if this file has MORE THAN ONE distinct GSTIN in it at all —
     * useful for a friendlier message ("this file covers multiple GST
     * registrations; only rows for {gstin} were imported") instead of a
     * flat mismatch/match binary.
     */
    public static function hasMultipleGstins(array $sheets): bool
    {
        return count(self::allGstinsFound($sheets, 2)) > 1;
    }

    // ────────────────────────────────────────────────────────────────
    //  Internal: does a single cell equal the target GSTIN exactly?
    //  (Exact match only — not substring — to avoid a state-code-only
    //  prefix or a truncated value producing a false positive.)
    // ────────────────────────────────────────────────────────────────
    private static function cellMatches($cell, string $target): bool
    {
        if ($cell === null) return false;
        $val = strtoupper(trim((string)$cell));
        return $val === $target;
    }
}
