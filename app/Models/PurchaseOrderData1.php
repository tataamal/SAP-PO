<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderData1 extends Model
{
    use HasFactory;
    
    protected $table = 'purchase_orders_data1';
    protected $primaryKey = ['EBELN', 'LOC']; // Composite key
    public $incrementing = false;
    
    protected $fillable = [
        'BADAT', 'BEDAT', 'EBELN', 'FRGCO', 'KRYW', 'NAME1', 
        'STATS', 'TOTPR', 'WAERK', 'WEEK', 'BSART', 'LOC'
    ];
    
    protected $dates = ['BADAT', 'BEDAT'];
    
    // Relationship dengan items
// app/Models/PurchaseOrderData1.php
public function items()
{
    return $this->hasMany(\App\Models\PurchaseOrderData2::class, 'EBELN', 'EBELN');
}


}