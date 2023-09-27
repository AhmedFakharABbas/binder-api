<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class  DataColumnsMapping extends Model
{
     protected $fillable = [
        'data_column_id',
        "value",
        "user_id"
       
    ];
    use HasFactory;
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
