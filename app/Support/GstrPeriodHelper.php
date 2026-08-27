<?php

namespace App\Support;

class GstrPeriodHelper
{
    /**
     * MM/YYYY se GST-financial-year quarter nikalta hai.
     * FY India mein April se shuru hoti hai.
     */
    public static function quarterFromMonth(string $mm, string $yyyy): array
    {
        $month = (int) $mm;
        $year  = (int) $yyyy;

        [$q, $fyStart] = match (true) {
            in_array($month, [4, 5, 6])    => [1, $year],
            in_array($month, [7, 8, 9])    => [2, $year],
            in_array($month, [10, 11, 12]) => [3, $year],
            default                        => [4, $year - 1], // Jan/Feb/Mar
        };

        $fyLabel = $fyStart . '-' . substr((string) ($fyStart + 1), 2, 2);

        return [
            'quarter'       => $q,
            'fy_start'      => $fyStart,
            'fy_label'      => $fyLabel,
            'filing_period' => "Q{$q}/{$fyLabel}",
            'months'        => self::monthsInQuarter($q, $fyStart),
        ];
    }

    /** Quarter number + FY-start-year se teeno months (MM/YYYY) return karta hai */
    public static function monthsInQuarter(int $q, int $fyStart): array
    {
        return match ($q) {
            1 => ["04/{$fyStart}", "05/{$fyStart}", "06/{$fyStart}"],
            2 => ["07/{$fyStart}", "08/{$fyStart}", "09/{$fyStart}"],
            3 => ["10/{$fyStart}", "11/{$fyStart}", "12/{$fyStart}"],
            4 => ["01/" . ($fyStart + 1), "02/" . ($fyStart + 1), "03/" . ($fyStart + 1)],
            default => [],
        };
    }

    /** "Q1/2025-26" jaisi string ko parse karke quarter+fyStart return karta hai */
    public static function parseFilingPeriod(string $filingPeriod): ?array
    {
        if (!preg_match('/^Q([1-4])\/(\d{4})-(\d{2})$/', $filingPeriod, $m)) {
            return null;
        }
        $q       = (int) $m[1];
        $fyStart = (int) $m[2];
        return [
            'quarter'  => $q,
            'fy_start' => $fyStart,
            'months'   => self::monthsInQuarter($q, $fyStart),
        ];
    }

    /** Human-readable label, e.g. "Apr-Jun 2025" */
    public static function humanLabel(string $filingPeriod): string
    {
        $parsed = self::parseFilingPeriod($filingPeriod);
        if (!$parsed) return $filingPeriod;

        $monthNames = ['01'=>'Jan','02'=>'Feb','03'=>'Mar','04'=>'Apr','05'=>'May','06'=>'Jun',
                       '07'=>'Jul','08'=>'Aug','09'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Dec'];
        $first = explode('/', $parsed['months'][0]);
        $last  = explode('/', $parsed['months'][2]);
        return $monthNames[$first[0]] . '-' . $monthNames[$last[0]] . ' ' . $last[1];
    }
}
