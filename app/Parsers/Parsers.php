<?php
namespace App\Parsers;

/**
 * ══════════════════════════════════════════════════════════════════════
 *  MYNTRA PARSER
 * ══════════════════════════════════════════════════════════════════════
 *  CSV format. Two files (Sales + Returns).
 *  item_status: C = sale, IC/RTO = return
 *  Buyer state: delivery_state (sales) | customer_state (returns)
 * ══════════════════════════════════════════════════════════════════════
 */
class MyntraParser extends BaseParser
{
    protected string $platform = 'myntra';

    public function __construct(string $sellerStateCode = '', string $clientGstin = '')
    {
        parent::__construct($sellerStateCode, $clientGstin);
    }

    public function parse(array $sheets, string $sellerStateCode, string $period): array
    {
        $this->sellerStateCode = $sellerStateCode;
        $this->period          = $period;
        $this->warnings        = [];
        $transactions          = [];

        $rows = array_values($sheets)[0] ?? [];
        if (count($rows) < 2) {
            $this->warn("CSV file is empty or has no data rows");
            return ['transactions' => [], 'warnings' => $this->warnings];
        }

        $headers = array_map(fn($h) => strtolower(trim((string)$h)), $rows[0]);
        $cm      = $this->buildColMap($rows[0]); // use original for display

        // Build lowercase col map manually for CSV
        $colIdx = [];
        foreach ($headers as $i => $h) { $colIdx[$h] = $i; }

        // Status values from config
        $saleStatuses   = array_map('strtoupper', $this->cfg['item_status']['sale']   ?? ['C']);
        $returnStatuses = array_map('strtoupper', $this->cfg['item_status']['return'] ?? ['IC', 'RTO']);

        $statusColAliases = array_map('strtolower', $this->cfg['columns']['item_status'] ?? ['item_status', 'status']);
        $statusCol = -1;
        foreach ($statusColAliases as $alias) {
            if (isset($colIdx[$alias])) { $statusCol = $colIdx[$alias]; break; }
        }

        $txvalColAliases = array_map('strtolower', $this->cfg['columns']['taxable_value'] ?? ['base_amount']);
        $txvalCol = -1;
        foreach ($txvalColAliases as $alias) {
            if (isset($colIdx[$alias])) { $txvalCol = $colIdx[$alias]; break; }
        }

        if ($txvalCol < 0) {
            $this->warn("'base_amount' / taxable value column not found. Headers: " . implode(', ', array_keys($colIdx)));
            return ['transactions' => [], 'warnings' => $this->warnings];
        }

        // Delivery state aliases
        $deliveryAliases = array_map('strtolower', $this->cfg['columns']['delivery_state'] ?? ['delivery_state']);
        $customerAliases = array_map('strtolower', $this->cfg['columns']['customer_state'] ?? ['customer_state']);
        $rateAliases     = array_map('strtolower', $this->cfg['columns']['gst_rate']       ?? ['tax_rate']);
        $igstAliases     = array_map('strtolower', $this->cfg['columns']['igst_rate']      ?? ['igst_rate']);
        $qtyAliases      = array_map('strtolower', $this->cfg['columns']['quantity']       ?? ['qty']);
        $orderAliases    = array_map('strtolower', $this->cfg['columns']['order_id']       ?? ['new_parent_order_id']);
        $dateAliases     = array_map('strtolower', $this->cfg['columns']['order_date']     ?? ['order_created_date']);

        $findCol = fn($aliases) => collect($aliases)->first(fn($a) => isset($colIdx[$a]) ? $colIdx[$a] : false) ?? -1;

        $deliveryCol  = array_reduce($deliveryAliases, fn($c, $a) => $c >= 0 ? $c : ($colIdx[$a] ?? -1), -1);
        $customerCol  = array_reduce($customerAliases, fn($c, $a) => $c >= 0 ? $c : ($colIdx[$a] ?? -1), -1);
        $rateCol      = array_reduce($rateAliases,     fn($c, $a) => $c >= 0 ? $c : ($colIdx[$a] ?? -1), -1);
        $igstRateCol  = array_reduce($igstAliases,     fn($c, $a) => $c >= 0 ? $c : ($colIdx[$a] ?? -1), -1);
        $qtyCol       = array_reduce($qtyAliases,      fn($c, $a) => $c >= 0 ? $c : ($colIdx[$a] ?? -1), -1);
        $orderCol     = array_reduce($orderAliases,    fn($c, $a) => $c >= 0 ? $c : ($colIdx[$a] ?? -1), -1);
        $dateCol      = array_reduce($dateAliases,     fn($c, $a) => $c >= 0 ? $c : ($colIdx[$a] ?? -1), -1);

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if ($this->rowIsEmpty($row, 4)) continue;

            $statusRaw = strtoupper(trim((string)($row[$statusCol] ?? '')));
            $isSale    = in_array($statusRaw, $saleStatuses);
            $isReturn  = in_array($statusRaw, $returnStatuses);
            if (!$isSale && !$isReturn) continue;

            $txval = $this->num($row[$txvalCol] ?? null);
            if ($txval <= 0) continue;

            // Buyer state: sales → delivery_state, returns → customer_state
            $stateRaw = $isSale
                ? ($row[$deliveryCol] ?? $row[$customerCol] ?? '')
                : ($row[$customerCol] ?? $row[$deliveryCol] ?? '');
            $pos = $this->resolveStateCode($stateRaw);
            if ($pos === '') { $this->warn("Row {$i}: Cannot resolve buyer state '{$stateRaw}'"); continue; }

            // GST rate: prefer igst_rate if > 0, else tax_rate
            $rate = $this->num($row[$igstRateCol] ?? null);
            if ($rate <= 0) $rate = $this->num($row[$rateCol] ?? null);
            if ($rate <= 0) continue;

            if ($isReturn) $txval = -abs($txval);

            $t = $this->makeTransaction(
                pos:         $pos,
                rate:        $rate,
                taxableValue:$txval,
                invoiceNo:   $orderCol >= 0 ? (string)($row[$orderCol] ?? '') : null,
                invoiceDate: $dateCol  >= 0 ? $this->date($row[$dateCol]  ?? null) : null,
                qty:         max(1, (int)$this->num($row[$qtyCol] ?? 1)),
            );

            if (!empty($t)) $transactions[] = $t;
        }

        return ['transactions' => array_values(array_filter($transactions)), 'warnings' => $this->warnings];
    }
}
