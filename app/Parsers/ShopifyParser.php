<?php

namespace App\Parsers;

class ShopifyParser extends BaseParser
{
    protected string $platform = 'shopify';

    private const VALID_RATES = [0, 0.25, 3, 5, 12, 18, 28];

    public function canHandle(\PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet): bool
    {
        return false;
    }

    public function supportsCsv(): bool
    {
        return true;
    }

    public function canParseCsv(array $rows): bool
    {
        if (empty($rows[0])) return false;
        $headerStr = strtolower(implode(',', $rows[0]));
        return str_contains($headerStr, 'lineitem quantity') && str_contains($headerStr, 'financial status');
    }

    public function parse(array $sheets, string $sellerStateCode, string $period, string $fileRole = 'single'): array
    {
        $rows = $sheets['Sheet1'] ?? reset($sheets) ?? [];
        if (empty($rows)) {
            return ['success' => false, 'error' => 'Empty file'];
        }

        $headers = array_shift($rows);
        $colMap  = $this->buildColMap($headers);

        if (($colMap['order_name'] ?? -1) < 0 || ($colMap['total'] ?? -1) < 0) {
            return ['success' => false, 'error' => 'Invalid Shopify Orders Export CSV — required "Name" and/or "Total" column not found. Please upload a valid Shopify Orders Export CSV (Admin -> Orders -> Export).'];
        }

        // Group rows by order name, forward-fill order-level columns
        $orders = [];
        $lastOrderName = null;

        foreach ($rows as $row) {
            if ($this->rowIsEmpty($row)) continue;

            $orderName = trim((string)$this->val($row, $colMap, 'order_name'));
            if ($orderName !== '') {
                $lastOrderName = $orderName;
            }
            $key = $lastOrderName ?? ('UNKNOWN-' . count($orders));

            if (!isset($orders[$key])) {
                $orders[$key] = ['header_row' => null, 'lineitems' => []];
            }
            if ($orderName !== '') {
                $orders[$key]['header_row'] = $row;
            }
            $orders[$key]['lineitems'][] = $row;
        }

        $transactions = [];

        foreach ($orders as $orderName => $data) {
            $head = $data['header_row'];
            if (!$head) continue;

            $total    = $this->num($this->val($head, $colMap, 'total'));
            $subtotal = $this->num($this->val($head, $colMap, 'subtotal'));
            $taxes    = $this->num($this->val($head, $colMap, 'taxes'));

            $financialStatus = strtolower(trim((string)$this->val($head, $colMap, 'financial_status')));
            if (in_array($financialStatus, ['voided', 'refunded'], true)) {
                continue;
            }

            $taxableValue = $subtotal > 0 ? $subtotal : ($total - $taxes);
            if ($taxableValue == 0) continue;

            $province = trim((string)$this->val($head, $colMap, 'shipping_province'));
            if ($province === '') {
                $province = trim((string)$this->val($head, $colMap, 'billing_province'));
            }
            $pos = $this->resolveStateCode($province);
            if ($pos === '') continue;

            $cgst = $sgst = $igst = 0.0;
            $namedTaxFound = false;

            for ($i = 1; $i <= 3; $i++) {
                $name = strtoupper(trim((string)$this->val($head, $colMap, "tax{$i}_name")));
                $val  = $this->num($this->val($head, $colMap, "tax{$i}_value"));
                if ($val == 0) continue;

                if (str_contains($name, 'CGST')) { $cgst += $val; $namedTaxFound = true; }
                elseif (str_contains($name, 'SGST') || str_contains($name, 'UTGST')) { $sgst += $val; $namedTaxFound = true; }
                elseif (str_contains($name, 'IGST')) { $igst += $val; $namedTaxFound = true; }
            }

            $totalTax = $namedTaxFound ? ($cgst + $sgst + $igst) : $taxes;

            if (!$namedTaxFound) {
                $isIntra = ($sellerStateCode !== '' && $pos === $sellerStateCode);
                if ($isIntra) {
                    $cgst = round($totalTax / 2, 2);
                    $sgst = round($totalTax - $cgst, 2);
                    $igst = 0.0;
                } else {
                    $igst = $totalTax;
                    $cgst = 0.0;
                    $sgst = 0.0;
                }
            }

            $rate = $taxableValue > 0 ? round(($totalTax / $taxableValue) * 100, 2) : 0.0;
            $rate = $this->snapToValidRate($rate);

            $buyerGstin = $this->extractGstinFromNotes(
                (string)$this->val($head, $colMap, 'note_attributes') . ' ' .
                (string)$this->val($head, $colMap, 'notes')
            );

            $invoiceDate = $this->date($this->val($head, $colMap, 'created_at'));
            $qty = (int) array_sum(array_map(
                fn($li) => (int)$this->num($this->val($li, $colMap, 'lineitem_qty')),
                $data['lineitems']
            ));

            $tx = $this->makeTransaction(
                pos:          $pos,
                rate:         $rate,
                taxableValue: $taxableValue,
                igstAmt:      $igst,
                cgstAmt:      $cgst,
                sgstAmt:      $sgst,
                invoiceNo:    ltrim($orderName, '#'),
                invoiceDate:  $invoiceDate,
                qty:          $qty > 0 ? $qty : null,
                hsn:          null,
                buyerGstin:   $buyerGstin
            );

            if (!empty($tx)) $transactions[] = $tx;
        }

        if (empty($transactions)) {
            return ['success' => false, 'error' => 'No valid orders found in this Shopify export.', 'warnings' => $this->warnings];
        }

        return [
            'success'      => true,
            'transactions' => $transactions,
            'file_type'    => 'shopify_orders_export',
            'warnings'     => $this->warnings,
        ];
    }

    private function snapToValidRate(float $rate): float
    {
        $best = self::VALID_RATES[0];
        $bestDiff = PHP_FLOAT_MAX;
        foreach (self::VALID_RATES as $v) {
            $diff = abs($rate - $v);
            if ($diff < $bestDiff) { $bestDiff = $diff; $best = $v; }
        }
        return $best;
    }

    private function extractGstinFromNotes(string $text): ?string
    {
        if (preg_match('/\b([0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1})\b/i', strtoupper($text), $m)) {
            return $m[1];
        }
        return null;
    }
}
