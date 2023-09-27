<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectProduct extends Model
{
    protected $fillable = [
        'project_id',
        'product_id',
        'serial_number',
        'software_version',

        
    ];

    use HasFactory;

    public function Products()
    {
        return $this->belongsToMany(Product::class);
    }
}
