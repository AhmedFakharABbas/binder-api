<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class  ProjectAnalysis extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'number',
        'customer_name',
        "project_id",
        // "product_id",
        "data_file_url"
    ];

    protected $table = 'project_analysis';

    public function reports()
    {
        return $this->hasMany(ReportData::class);
    }

    public function graphVersions()
    {
        return $this->hasMany(GraphVersion::class);
    }

    /**
     * Get the project analyses associated with the project.
     */
    public function projectAnalyses()
    {
        return $this->belongsToMany(ReportData::class, 'graph_version_filters', 'project_analysis_id', 'data_column_id')
            ->withPivot('id','graph_version_id', 'start_value', 'end_value', 'avg_value', 'color_colde', 'curve_type');
    }
//    one to many relationship  between project and product



}

 
