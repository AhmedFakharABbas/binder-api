<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataColumn extends Model
{
     protected $fillable = [
        'en_column',
        'du_column',
         'unit_name',
         'colour_code'
    ];
    use HasFactory;

    
}
