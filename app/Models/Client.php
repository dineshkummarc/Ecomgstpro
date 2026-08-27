<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'gstin',
        'state_code',
        'state_name',
        'filing_frequency', // ← FIX: missing tha, isliye quarterly/monthly select save hi nahi ho raha tha
        'phone',            // ← form mein field hai, isliye fillable mein bhi hona chahiye
        'email',            // ← form mein field hai, isliye fillable mein bhi hona chahiye
    ];

    public function getInitialsAttribute()
    {
        $words = explode(' ', $this->name);
        return strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));
    }

    public function batches()
    {
        return $this->hasMany(\App\Models\GstrBatch::class, 'client_id');
    }
}
