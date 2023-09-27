<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PpCabinet extends Model
{
    protected $fillable = [
        'name',
        'serial_number',
        'created_by',
        'updated_by',

    ];
    use HasFactory;
}
