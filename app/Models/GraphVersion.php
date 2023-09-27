<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class  GraphVersion extends Model
{
    use HasFactory;
    protected $fillable = [
        'project_analysis_id',
        'graph_name',
        'graph_picture_url',
        'is_default',
        'encrypted_name',
        'start_time',
        'finish_time'
    ];

    protected $table = 'graph_versions';


    /**
     * Get the graph_version_filters for the graph Version.
     */
    public function filters()
    {
        return $this->hasMany(GraphVersionFilter::class);
    }

    public function users(){
        return $this->belongsToMany('App\Models\User', 'user_graph_versions',
            'graph_version_id','user_id')->withPivot('project_id','project_analysis_id');

    }

}
