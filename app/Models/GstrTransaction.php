<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GstrTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'gstr_transactions';

    protected $fillable = [
        'batch_id',
        'client_id',
        'gstin',
        'platform',
        'period',
        'invoice_number',
        'invoice_date',
        'place_of_supply',
        'supply_type',
        'nature_of_supply',
        'hsn_code',
        'sac_code',
        'product_description',
        'quantity',
        'uqc',
        'taxable_value',
        'gst_rate',
        'cgst_amount',
        'sgst_amount',
        'igst_amount',
        'cess_amount',
        'tcs_amount',
        'invoice_value',
        'total_tax',
        'buyer_gstin',
        'buyer_name',
        'seller_state',
        'status',
        'raw_data',
        'parsing_notes',
        'confidence_score',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'taxable_value' => 'decimal:2',
        'gst_rate' => 'decimal:2',
        'cgst_amount' => 'decimal:2',
        'sgst_amount' => 'decimal:2',
        'igst_amount' => 'decimal:2',
        'invoice_value' => 'decimal:2',
        'total_tax' => 'decimal:2',
        'confidence_score' => 'float',
    ];

    // Scopes
    public function scopeByBatch($query, $batchId)
    {
        return $query->where('batch_id', $batchId);
    }

    public function scopeByClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeByPeriod($query, $period)
    {
        return $query->where('period', $period);
    }

    public function scopeByPlatform($query, $platform)
    {
        return $query->where('platform', $platform);
    }

    public function scopeProcessed($query)
    {
        return $query->where('status', 'processed');
    }

    // Relationships
    public function getB2CSData()
    {
        return [
            'pos' => $this->place_of_supply,
            'sply_ty' => $this->supply_type,
            'rt' => $this->gst_rate,
            'txval' => $this->taxable_value,
            'iamt' => $this->igst_amount > 0 ? $this->igst_amount : null,
            'camt' => $this->cgst_amount > 0 ? $this->cgst_amount : null,
            'samt' => $this->sgst_amount > 0 ? $this->sgst_amount : null,
            'csamt' => $this->cess_amount > 0 ? $this->cess_amount : null,
        ];
    }

    // Compute totals for summary
    public static function getSummaryByBatch($batchId, $gstin, $period)
    {
        $transactions = static::byBatch($batchId)
            ->processed()
            ->get()
            ->groupBy(function ($item) {
                return $item->place_of_supply . '_' . $item->gst_rate . '_' . $item->supply_type;
            });

        $summary = [];
        foreach ($transactions as $group) {
            $first = $group->first();
            $summary[] = [
                'pos' => $first->place_of_supply,
                'rate' => $first->gst_rate,
                'splyType' => $first->supply_type,
                'taxable' => $group->sum('taxable_value'),
                'cgst' => $group->sum('cgst_amount'),
                'sgst' => $group->sum('sgst_amount'),
                'igst' => $group->sum('igst_amount'),
                'cess' => $group->sum('cess_amount'),
            ];
        }

        usort($summary, function ($a, $b) {
            return $a['pos'] <=> $b['pos'];
        });

        return $summary;
    }
}
