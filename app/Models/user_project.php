<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProject extends Model
{
     protected $fillable = [
        'user_id',
        'project_id',
        
    ];
    use HasFactory;

 
     public function Users()
    {
        return $this->belongsToMany(Users::class);
    }
}
