<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderData2 extends Model
{
    use HasFactory;
    
    protected $table = 'purchase_orders_data2';
    protected $primaryKey = ['EBELN', 'EBELP']; // Hanya EBELN+EBELP sebagai primary key
    public $incrementing = false;
    
    protected $fillable = [
        'MANDT', 'STATS', 'WEEK', 'BADAT', 'BEDAT', 'EBELN', 'EBELP',
        'FRGCO', 'MATNR', 'MAKTX', 'MENGE', 'MEINS', 'NETTT', 'NETWR',
        'TAX', 'NAME1', 'TOTPR', 'WAERK', 'TEXT', 'KRYW'
    ];
    
    protected $dates = ['BADAT', 'BEDAT'];
    
    // Relationship dengan header
    public function header()
    {
        return $this->belongsTo(PurchaseOrderData1::class, 'EBELN', 'EBELN');
    }
}