<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkcenterTData1 extends Model
{
    protected $table = 'workcenter_t_data1';

    protected $fillable = [
        'MANDT', 'ARBPL', 'PWWRK', 'KTEXT', 'ARBID', 'VERID', 'KDAUF',
        'KDPOS', 'AUFNR', 'PLNUM', 'STATS', 'DISPO', 'MATNR', 'MTART',
        'MAKTX', 'VORNR', 'STEUS', 'AUART', 'MEINS', 'MATKL',
        'PSMNG', 'WEMNG', 'MGVGR2','SSSLD'
    ];
}
