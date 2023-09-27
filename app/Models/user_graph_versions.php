<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProject extends Model
{
     protected $fillable = [
        'user_id',
        'graph_version_id',
        'project_id',
        'project_analysis_id'
        
    ];
    use HasFactory;

 
     public function Users()
    {
        return $this->belongsToMany(Users::class);
    }
}