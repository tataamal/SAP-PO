<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workcenters extends Model
{
    protected $table = 'workcenters';

    protected $fillable = [
        'code',
        'description',
        'categories',
        'plant',
    ];
}