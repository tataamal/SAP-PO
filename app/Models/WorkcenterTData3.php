<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkcenterTData3 extends Model
{
    protected $table = 'workcenter_t_data3';

    protected $fillable = [
        'MANDT', 'KDAUF', 'KDPOS', 'MATFG', 'MAKFG', 'EDATU',
    ];

    protected $casts = [
        'EDATU' => 'date',
    ];
}
