<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable,HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'company_name',
//        'company_address',
        'country_id',
        'city_id',
        'zip_code',
        'phone',
        'role_id',
        'created_by',
        'password',

        
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    public function dataColumnMappings()
    {
        return $this->hasMany(DataColumnsMapping::class);
    }
    public function permissions(){
         return $this->belongsToMany('App\Models\permission', 'user_permissions',
            'user_id','permission_id');
    
    }
    //  public function projects(){
    //      return $this->belongsToMany('App\Models\Project', 'user_permissions',
    //         'user_id','permission_id');
    
    // }
     public function projects(){
         return $this->belongsToMany('App\Models\Project', 'user_projects',
            'user_id','project_id');
    
    }
    public function versions(){
         return $this->belongsToMany('App\Models\GraphVersion', 'user_graph_versions',
            'user_id','graph_version_id')->withPivot('project_id','project_analysis_id');
    
    }
}
