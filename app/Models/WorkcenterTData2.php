<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkcenterTData2 extends Model
{
    protected $table = 'workcenter_t_data2';

    protected $fillable = [
        'MANDT', 'ARBPL', 'WERKS',
        'TOTALY', 'DAYX', 'DDAY', 'DDAY2',
        'SSSLD', 'SSSLD2','KTEXT',
    ];
}
