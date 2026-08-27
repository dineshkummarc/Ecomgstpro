<?php

namespace App\Services;

use App\Models\GstrTransaction;
use Illuminate\Support\Collection;

class B2CSComputationService
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

    /**
     * Compute B2CS summary from transactions
     */
    public function computeSummary($transactions)
    {
        if ($transactions instanceof Collection) {
            $transactions = $transactions->toArray();
        }

        $grouped = [];

        foreach ($transactions as $trans) {
            // Group by POS + Rate + Supply Type
            $key = $trans['place_of_supply'] . '_' . $trans['gst_rate'] . '_' . ($trans['supply_type'] ?? 'B2C');

            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'pos' => $trans['place_of_supply'],
                    'sply_ty' => $trans['supply_type'] ?? 'B2C',
                    'rt' => (float)$trans['gst_rate'],
                    'txval' => 0,
                    'camt' => 0,
                    'samt' => 0,
                    'iamt' => 0,
                    'csamt' => 0,
                ];
            }

            $grouped[$key]['txval'] += (float)($trans['taxable_value'] ?? 0);
            $grouped[$key]['camt'] += (float)($trans['cgst_amount'] ?? 0);
            $grouped[$key]['samt'] += (float)($trans['sgst_amount'] ?? 0);
            $grouped[$key]['iamt'] += (float)($trans['igst_amount'] ?? 0);
            $grouped[$key]['csamt'] += (float)($trans['cess_amount'] ?? 0);
        }

        // Sort by POS and rate
        usort($grouped, function ($a, $b) {
            if ($a['pos'] !== $b['pos']) {
                return strcmp($a['pos'], $b['pos']);
            }
            return $a['rt'] <=> $b['rt'];
        });

        return array_values($grouped);
    }

    /**
     * Get detailed transactions for download
     */
    public function getDetailedTransactions($batchId)
    {
        return GstrTransaction::byBatch($batchId)
            ->processed()
            ->get()
            ->map(function ($trans) {
                return [
                    'idx' => $trans->id,
                    'platform' => $trans->platform,
                    'period' => $trans->period,
                    'state' => $trans->place_of_supply,
                    'nature' => $trans->nature_of_supply,
                    'hsn' => $trans->hsn_code ?? '-',
                    'qty' => $trans->quantity ?? 0,
                    'taxable' => round($trans->taxable_value, 2),
                    'rate' => $trans->gst_rate,
                    'cgst' => round($trans->cgst_amount, 2),
                    'sgst' => round($trans->sgst_amount, 2),
                    'igst' => round($trans->igst_amount, 2),
                    'invoice' => round($trans->invoice_value, 2),
                    'tcs' => round($trans->tcs_amount ?? 0, 2),
                ];
            })
            ->toArray();
    }

    /**
     * Generate JSON for B2CS filing
     */
    public function generateJSON($gstin, $period, $summary)
    {
        // Format period: MM/YYYY -> MMYYYY
        [$month, $year] = explode('/', $period);
        $fp = str_pad($month, 2, '0', STR_PAD_LEFT) . substr($year, -2);

        $b2csData = [];

        foreach ($summary as $item) {
            $b2csEntry = [
                'sply_ty' => $item['sply_ty'] ?? 'OE',
                'pos' => $item['pos'],
                'rt' => (int)$item['rt'],
                'txval' => round($item['txval'], 2),
            ];

            // Add tax amounts based on supply type
            if ($item['iamt'] > 0) {
                $b2csEntry['iamt'] = round($item['iamt'], 2);
            } else {
                if ($item['camt'] > 0) {
                    $b2csEntry['camt'] = round($item['camt'], 2);
                }
                if ($item['samt'] > 0) {
                    $b2csEntry['samt'] = round($item['samt'], 2);
                }
            }

            if ($item['csamt'] > 0) {
                $b2csEntry['csamt'] = round($item['csamt'], 2);
            }

            // Add e-commerce GSTIN for B2C
            if ($item['sply_ty'] === 'B2C' || $item['sply_ty'] === 'OE') {
                $b2csEntry['ecomgstin'] = '';
            }

            $b2csData[] = $b2csEntry;
        }

        // Generate hash
        $hashData = json_encode($b2csData);
        $hash = hash('sha256', $hashData);

        $jsonPayload = [
            'gstin' => $gstin,
            'fp' => $fp,
            'version' => '1.1',
            'hash' => $hash,
            'b2cs' => $b2csData,
        ];

        return $jsonPayload;
    }

    /**
     * Generate CSV for B2CS filing
     */
    public function generateCSV($summary)
    {
        $csv = "Type,Place Of Supply,Applicable % of Tax Rate,Taxable Value,Cess Amount,E-Commerce GSTIN\n";

        foreach ($summary as $item) {
            $type = $item['sply_ty'] === 'B2C' || $item['sply_ty'] === 'OE' ? 'OE' : 'B2B';
            $state = $this->stateMap[$item['pos']] ?? $item['pos'];
            $rate = (int)$item['rt'];
            $taxable = round($item['txval'], 2);
            $cess = round($item['csamt'] ?? 0, 2);

            $csv .= "{$type},{$state},{$rate}%,{$taxable},{$cess},\n";
        }

        return $csv;
    }

    /**
     * Generate Excel data structure
     */
    public function generateExcelData($summary, $detailedTransactions)
    {
        return [
            'b2cs_summary' => $this->formatB2CSSummaryForExcel($summary),
            'detailed' => $this->formatDetailedForExcel($detailedTransactions),
        ];
    }

    private function formatB2CSSummaryForExcel($summary)
    {
        $data = [
            ['Type', 'Place of Supply', 'GST Rate %', 'Taxable Value (₹)', 'IGST (₹)', 'CGST (₹)', 'SGST (₹)', 'Total Tax (₹)']
        ];

        $totTaxable = $totIGST = $totCGST = $totSGST = 0;

        foreach ($summary as $item) {
            $type = $item['sply_ty'];
            $state = $this->stateMap[$item['pos']] ?? $item['pos'];
            $rate = $item['rt'];
            $taxable = round($item['txval'], 2);
            $igst = round($item['iamt'] ?? 0, 2);
            $cgst = round($item['camt'] ?? 0, 2);
            $sgst = round($item['samt'] ?? 0, 2);
            $totalTax = $igst + $cgst + $sgst;

            $data[] = [
                $type,
                $state,
                $rate . '%',
                $taxable,
                $igst,
                $cgst,
                $sgst,
                $totalTax,
            ];

            $totTaxable += $taxable;
            $totIGST += $igst;
            $totCGST += $cgst;
            $totSGST += $sgst;
        }

        // Grand total row
        $data[] = [
            'Grand Total',
            '',
            '',
            $totTaxable,
            $totIGST,
            $totCGST,
            $totSGST,
            $totIGST + $totCGST + $totSGST,
        ];

        return $data;
    }

    private function formatDetailedForExcel($transactions)
    {
        $data = [
            ['#', 'Platform', 'Period', 'State', 'Nature', 'HSN', 'Qty', 'Taxable (₹)', 'Rate %', 'CGST (₹)', 'SGST (₹)', 'IGST (₹)', 'Invoice (₹)', 'TCS (₹)']
        ];

        foreach ($transactions as $trans) {
            $data[] = [
                $trans['idx'],
                $trans['platform'],
                $trans['period'],
                $this->stateMap[$trans['state']] ?? $trans['state'],
                $trans['nature'],
                $trans['hsn'],
                $trans['qty'],
                $trans['taxable'],
                $trans['rate'],
                $trans['cgst'],
                $trans['sgst'],
                $trans['igst'],
                $trans['invoice'],
                $trans['tcs'],
            ];
        }

        return $data;
    }

    /**
     * Get statistics
     */
    public function getStatistics($summary, $transactions)
    {
        $stats = [
            'total_states' => count(array_unique(array_column($summary, 'pos'))),
            'total_transactions' => count($transactions),
            'total_taxable' => array_sum(array_column($summary, 'txval')),
            'total_tax' => array_sum(array_column($summary, 'iamt')) +
                          array_sum(array_column($summary, 'camt')) +
                          array_sum(array_column($summary, 'samt')),
            'tax_rates' => array_unique(array_column($summary, 'rt')),
            'platforms' => array_unique(array_column($transactions, 'platform')),
        ];

        return $stats;
    }
}
