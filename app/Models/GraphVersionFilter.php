<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class  GraphVersionFilter extends Model
{
    use HasFactory;
    protected $fillable = [
        'graph_version_id',
        "start_value",
        "end_value",
        'avg_value',
        "color_code",
        "curve_type",
        "is_display"
    ];

    protected $table = 'graph_version_filters';
    public $timestamps = false;


    /**
     * Get the version that owns the filters.
     */
    public function version()
    {
        return $this->belongsTo(GraphVersion::class);
    }

}
