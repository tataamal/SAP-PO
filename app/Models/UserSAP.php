<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSAP extends Model
{
    protected $table = 'user_sap';
    protected $fillable = ['id_sap','sap_username','sap_password'];
    protected $hidden   = ['sap_password'];

    public function kodes() {
        return $this->hasMany(Kode::class, 'user_sap_id');
    }
}
