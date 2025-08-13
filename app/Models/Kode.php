<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kode extends Model
{
    protected $table = 'kode';
    protected $primaryKey = 'id_kode';
    protected $fillable = ['user_sap_id','kode'];

    public function userSap() {
        return $this->belongsTo(UserSAP::class, 'user_sap_id');
    }
    public function mrps() {
        return $this->hasMany(Mrp::class, 'id_kode');
    }
}
