<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class  Product extends Model
{
 

     protected $fillable = [
        'name',
        "serial_number",
        "software_version"
    ];
    use HasFactory;
   
    //  public function Project()
    // {
    //     return $this->belongsToMany(Project::class);
    // }

     public function projects () {
//        return $this->belongsToMany('App\Models\Project', 'project_product',
//            'product_id','project_id');

         return $this->hasMany('App\Models\ProjectProduct');


    }
}