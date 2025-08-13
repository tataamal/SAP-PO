<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionMapping extends Model
{
    protected $table = 'production_mapping'; // pastikan sesuai
    protected $fillable = ['code', 'categories', 'bagian', 'plant'];

    public function data2()
    {
        return $this->hasOne(ProductionTData2::class, 'code', 'code');
    }
}
