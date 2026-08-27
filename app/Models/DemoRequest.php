<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DemoRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'email', 'phone', 'company_name',
        'message', 'requested_date', 'requested_time',
        'confirmed_datetime', 'status', 'admin_note',
    ];

    protected $casts = [
        'requested_date'    => 'date',
        'confirmed_datetime' => 'datetime',
    ];
}
