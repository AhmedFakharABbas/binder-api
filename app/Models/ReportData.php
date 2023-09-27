<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class  ReportData extends Model
{
    use HasFactory;
    protected $fillable = [
       "project_analysis_id",
       "data_column_id",
       "project_id",
       "data_value",
        "date_time",
        "row_set"
    ];


    protected $table = 'report_data';

    public function analysis()
    {
        return $this->belongsTo(ProjectAnalysis::class);
    }


    /**
     * The distributionList that belong to the contact.
     */
    public function dataReports()
    {
        return $this->belongsToMany(ProjectAnalysis::class);
    }


}
