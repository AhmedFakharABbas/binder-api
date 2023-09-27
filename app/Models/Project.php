<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
     protected $fillable = [
        'name',
        'number',
        'customer_name',
        'country_id'
    ];
    use HasFactory;

    public function Analysis()
    {
        return $this->hasMany(ProjectAnalysis::class);
    }
    public function products () {
        return $this->belongsToMany('App\Models\Product', 'project_product',
            'project_id','product_id');
    }

//    public function products()
//    {
////        return $this->hasMany(Product::class);
//    }

    public function users(){
        return $this->belongsToMany('App\Models\User', 'user_projects',
            'project_id','user_id');

    }

    
}
