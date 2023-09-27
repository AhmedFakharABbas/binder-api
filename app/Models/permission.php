<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class permission extends Model
{
    protected $fillable = [
        'en_name',
        'du_name',
    ];
    use HasFactory;


}
