<?php

namespace App\Http\Controllers;

use App\Models\GraphVersion;
use App\Models\GraphVersionFilter;
use App\Models\Project;
use App\Models\ProjectAnalysis;
use App\Models\ReportData;
use App\Models\DataColumn;
use App\Models\DataColumnsMapping;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use DB;
use Illuminate\Validation\Rule;
use PDO;

class ProjectController extends Controller
{
    public function getUsers($user_id, $role_id)
    {
        $pdo = DB::connection()->getpdo();

        $stmt = $pdo->prepare('CALL get_users(?)');
        $stmt->execute(array($role_id));

        $users = $stmt->fetchAll(PDO::FETCH_CLASS, 'stdClass');
        $stmt->nextRowset();

        $userRole = $stmt->fetchAll(PDO::FETCH_CLASS, 'stdClass');
        $stmt->nextRowset();

        $permissions = $stmt->fetchAll(PDO::FETCH_CLASS, 'stdClass');
        $stmt->nextRowset();

        $projects = $stmt->fetchAll(PDO::FETCH_CLASS, 'stdClass');
        $stmt->nextRowset();

        $analysis = $stmt->fetchAll(PDO::FETCH_CLASS, 'stdClass');
        $stmt->nextRowset();

        $graphversion = $stmt->fetchAll(PDO::FETCH_CLASS, 'stdClass');
        $stmt->nextRowset();

        $countries = $stmt->fetchAll(PDO::FETCH_CLASS, 'stdClass');
        $stmt->nextRowset();

        $cities = $stmt->fetchAll(PDO::FETCH_CLASS, 'stdClass');
        $stmt->nextRowset();

        return response()->json(['users' => $users, 'userRole' => $userRole, 'Permissions' => $permissions, 'Projects' => $projects , 'analysis' => $analysis , 'graphversion' => $graphversion,'countries'=>$countries,'cities'=>$cities], 200);
    }
   public function getUserProject($user_id){
       $pdo = DB::connection()->getpdo();

       $stmt = $pdo->prepare('CALL get_user_projects(?)');
       $stmt->execute(array($user_id));

            $projects = $stmt->fetchAll(PDO::FETCH_CLASS, 'stdClass');
           $stmt->nextRowset();



       return response()->json(['projects' => $projects, ], 200);
  }
    public function getUserGraphVersion($user_id){
        $pdo = DB::connection()->getpdo();

        $stmt = $pdo->prepare('CALL get_user_graphversion(?)');
        $stmt->execute(array($user_id));

        $projects = $stmt->fetchAll(PDO::FETCH_CLASS, 'stdClass');
        $stmt->nextRowset();



        return response()->json(['projects' => $projects, ], 200);
    }
    public  function  deleteUser($id)
    {
        $user = User::find($id);
        $user->dataColumnMappings()->delete();
        $user->permissions()->detach();
        $user->projects()->detach();
        $user->versions()->detach();
        $user->delete();


        return response()->json(['success' => 'User deleted successfully'], 200);
    }


    public function associateProject(Request $request, $user_id, $as_u_id)
    {
        $user = User::findOrFail($as_u_id);


        $dataProjects = [];

        $data = $request->input('projects');
        if (sizeof($data) > 0) {
            foreach ($data as $d) {
                $project = new \stdClass();
                $project->project_id = $d['project_id'];
                $dataProjects [] = (array)$project;
            }
            $user->projects()->detach();
            $user->projects()->attach($dataProjects);
        }
        return response()->json(['success' => 'Created Successfully', 'id' => $user->id], 201);
    }
    
    public function associateVersions(Request $request, $user_id,$as_u_id)
    {
        $user = User::findOrFail($as_u_id);
        
        
     $dataVersions = [];

        $data = $request-> input('versions');
        if(sizeof($data) > 0) {
                    foreach ($data as $d) {
                        $version =new \stdClass();
                         $version -> graph_version_id = $d['graph_version_id'];
                          $version -> project_id = $d['project_id'];
                           $version -> project_analysis_id = $d['project_analysis_id'];
                          $dataVersions [] = (array) $version;
                    }
                    $user->versions()->detach();
                    $user->versions()->attach( $dataVersions); 
        }
        return response()->json(['success' => 'Created Successfully', 'id' => $user->id], 201);
    }

    public function createProject(Request $request, $user_id)
    {
        $project = Project::where('name', '=', $request->input('name'))->first();
        if ($project == null) {
            $project = new Project();
            $project->number = $request->input('number');
            $project->name = $request->input('name');
            $project->customer_name = $request->input('customer_name');
            $project->country_id = $request->input('country_id');
            $project->created_by = $user_id;
            $project->is_deleted = 0;

            $project->save();

        } else {
            return response()->json(['error' => 'Project  already exist',], 403);
        }


        return response()->json(['success' => 'Created Successfully', 'id' => $project->id], 201);
    }

    public function createdataColumn(Request $request, $user_id)
    {
        $dataColumn = new DataColumn();
        $dataColumn->en_column = $request->input('en_column');
        $dataColumn->du_column = $request->input('du_column');
        $dataColumn->unit_name = $request->input('unit_name');
        $dataColumn->colour_code = $request->input('colour_code');

        $dataColumn->created_by = $user_id;
        $dataColumn->save();

        return response()->json(['success' => 'Created Successfully', 'id' => $dataColumn->id], 201);
    }

    public function updateProject(Request $request, $user_id)
    {
        $project = Project::find($request->input('id'));


        $project->name = $request->input('name');
        $project->customer_name = $request->input('customer_name');
        $project->number = $request->input('number');
        $project->country_id = $request->input('country_id');
        $project->updated_by = $user_id;

        $project->save();

        return response()->json(['success' => 'Updated Successfully'], 201);

    }
    public function  deleteProject($id)
{
    $project = Project::find($id);

//    $flight->delete();
//    $version = GraphVersion::findOrFail($id);
//    if ($project != null) {

        $projectAnalysis = $project->Analysis()->get();

        if(sizeof($projectAnalysis)>0)
        {
            foreach ($projectAnalysis as  $pa)
            {
                 $pa->reports()->delete();

                 $graph_versions = $pa->graphVersions()->get();

                 if(sizeof($graph_versions) > 0)
                 {
                     foreach ($graph_versions as $gv)
                     {
                         $gv->filters()->delete();

                         $gv->users()->detach();
                     }
                 }

                $pa->graphVersions()->delete();

            }
        }

//        $projectProducts = $project->products()->get();
//        if(sizeof($projectProducts)>0)
//        {
//            foreach ($projectProducts as  $pp)
//            {
//
//                $pp->projects->detach();
//
//            }
//        }

        $project->Analysis()->delete();


//        $project->products()->delete();

        $project->users()->detach();
        $project->products()->detach();
        $project->delete();

//    }
    return response()->json(['success' => 'Project deleted successfully'], 200);
}
    public function  deleteAnalysis($id)
    {
        $analysis = ProjectAnalysis::find($id);
        $analysis->reports()->delete();

        $graph_versions = $analysis->graphVersions()->get();

        if(sizeof($graph_versions) > 0)
        {
            foreach ($graph_versions as $gv)
            {
                $gv->filters()->delete();

                $gv->users()->detach();
            }
        }

        $analysis->graphVersions()->delete();
        $analysis->delete();


        return response()->json(['success' => 'Analysis deleted successfully'], 200);
    }



    public function getProjects($user_id, $role_id)
    {
        $pdo = DB::connection()->getpdo();
        if ($role_id ==1||$role_id ==2) {
            $stmt = $pdo->prepare('CALL get_projects');
            $stmt->execute();
        }
        if($role_id==3){
        $stmt = $pdo->prepare('CALL get_staff_projects(?)');
        $stmt->execute(array($user_id));
        }
        if($role_id==4){
            $stmt = $pdo->prepare('CALL get_customer_projects(?)');
            $stmt->execute(array($user_id));
        }


        $projects = $stmt->fetchAll(PDO::FETCH_CLASS, 'stdClass');
        $stmt->nextRowset();

        $countries = $stmt->fetchAll(PDO::FETCH_CLASS, 'stdClass');
        $stmt->nextRowset();

        return response()->json(['projects' => $projects, 'countries' => $countries], 200);
    }

    public function getSingleProject($id, $user_id)
    {
        $pdo = DB::connection()->getpdo();

        $stmt = $pdo->prepare('CALL get_project(?)');
        $stmt->execute(array($id));

        $project = $stmt->fetchAll(PDO::FETCH_CLASS, 'stdClass')[0];
        $stmt->nextRowset();


        return response()->json(['project' => $project,], 200);
    }

    public function projectAnalysisMeta($id, $user_id)
    {
        $pdo = DB::connection()->getpdo();

        $stmt = $pdo->prepare('CALL project_analysis_meta(?)');
        $stmt->execute(array($id));

        $project = $stmt->fetchAll(PDO::FETCH_CLASS, 'stdClass')[0];
        $stmt->nextRowset();

        $colour = $stmt->fetchAll(PDO::FETCH_CLASS, 'stdClass')[0];
        $stmt->nextRowset();

        $dataColumns = $stmt->fetchAll(PDO::FETCH_CLASS, 'stdClass');
        $stmt->nextRowset();

        $products = $stmt->fetchAll(PDO::FETCH_CLASS, 'stdClass');
        $stmt->nextRowset();

        $dataCoumnsMapping = $stmt->fetchAll(PDO::FETCH_CLASS, 'stdClass');
        $stmt->nextRowset();

        return response()->json(['project' => $project,'color_Code'=>$colour, 'dataColumns' => $dataColumns, 'products' => $products,
            'dataCoumnsMapping' => $dataCoumnsMapping], 200);
    }
    public function projectUpdateAnalysisMeta($id, $analysis_id, $user_id)
    {
        $pdo = DB::connection()->getpdo();

        $stmt = $pdo->prepare('CALL project_analysis_update_meta(?,?)');
        $stmt->execute(array($id,$analysis_id));

        $project = $stmt->fetchAll(PDO::FETCH_CLASS, 'stdClass')[0];
        $stmt->nextRowset();

        $analysis = $stmt->fetchAll(PDO::FETCH_CLASS, 'stdClass')[0];
        $stmt->nextRowset();

        $dataColumns = $stmt->fetchAll(PDO::FETCH_CLASS, 'stdClass');
        $stmt->nextRowset();

        $products = $stmt->fetchAll(PDO::FETCH_CLASS, 'stdClass');
        $stmt->nextRowset();

        $dataCoumnsMapping = $stmt->fetchAll(PDO::FETCH_CLASS, 'stdClass');
        $stmt->nextRowset();

        $previousDataColums = $stmt->fetchAll(PDO::FETCH_CLASS, 'stdClass');
        $stmt->nextRowset();

        $previousMaxRowSet = $stmt->fetchAll(PDO::FETCH_CLASS, 'stdClass');
        $stmt->nextRowset();


        return response()->json(['project' => $project,'Analysis'=>$analysis, 'dataColumns' => $dataColumns, 'products' => $products,
            'dataCoumnsMapping' => $dataCoumnsMapping,'previousDataColums'=>$previousDataColums,'previousMaxRowSet'=>$previousMaxRowSet], 200);
    }
    public function getProjectAnalysis($project_id, $user_id )
    {
        $pdo = DB::connection()->getpdo();

        $stmt = $pdo->prepare('CALL get_project_analysis(?,?)');
        $stmt->execute(array($project_id,$user_id));

        

        $projectAnalyses = $stmt->fetchAll(PDO::FETCH_CLASS, 'stdClass');
        $stmt->nextRowset();

        


        return response()->json(['projectAnalyses' => $projectAnalyses], 200);
    }
    public function createAnalysis(Request $request, $user_id)
    {
// $ProjectAnalysis = ProjectAnalysis::where('name', '=', $request->input('name'))->first();
//         if ($project == null) {}

        $analysis = new ProjectAnalysis();
        $graph_version = new GraphVersion();
        $user = new User();
        $user->id = $user_id;

        $projectAnalyses = $request->input('projectAnalysis');

        if ($projectAnalyses != null) {
            if (sizeof($projectAnalyses) > 0) {

                foreach ($projectAnalyses as $key => $value) {
                    if ($key == 'name') {
                        $analysis->name = $value;
                    } else if ($key == 'project_id') {
                        $analysis->project_id = $value;
                    } else if ($key == 'product_id') {
                        $analysis->product_id = $value;
                    }
                    else if ($key == 'data_file_url') {
                        $analysis->data_file_url = $value;
                    } else if ($key == 'start_time') {
                        $graph_version->start_time = $value;
                    } else if ($key == 'finish_time') {
                        $graph_version->finish_time = $value;
                    }
                    $analysis->created_by = $user_id;
                    $analysis->is_deleted = 0;
                }

                $analysis->save();

                $graph_version->graph_name = 'Default';
                $graph_version->is_default = 1;
                $graph_version->project_analysis_id = $analysis->id;
                $graph_version->created_by = $user_id;

                $graph_version->save();


            }
        }


        //selected data colums
        $reports = $request->input('reportData');
        $selectedDatacolums = $request->input('selectedDatacolums');

        if ($selectedDatacolums != null) {

            $dataColumns = array();
            if (sizeof($selectedDatacolums) > 0) {

                foreach ($selectedDatacolums as $ct) {
                    $values=array();

                    if (sizeof($reports) > 0) {
                        if(($ct !=1 && $ct !=11) && $ct !=12) {

                            foreach ($reports as $rt) {

                                if ($rt['data_column_id'] == $ct) {
                                    array_push($values, $rt['data_value']);
                                }
  






                            }

                        }
                    }

                    $max_value=null;
                    $min_value=null;
                    $avg_value=null;

                    if(sizeof($values) > 0){

                        $max_value = max($values);

                        $max_value=round($max_value, 2);
                        $min_value = min($values);
                        $arraysum= array_sum($values);
                        $avg_value=$arraysum/sizeof($values);
                  }
                    $graph_version_filter = new \stdClass();
                    $graph_version_filter->data_column_id = $ct;
                    $graph_version_filter->start_value =$min_value;
                    $graph_version_filter->end_value = $max_value;
                    $graph_version_filter->avg_value =$avg_value;
                    $graph_version_filter->graph_version_id = $graph_version->id;

//                    $data_column_id = $ct;
                    $dataColumns [] = (array)$graph_version_filter;

                }
            }
            $analysis->projectAnalyses()->sync($dataColumns);
        }




        if (sizeof($reports) > 0) {
            foreach ($reports as $rt) {

                $report = new ReportData();

                if (isset($rt['project_id'])) {
                    $report->project_id = $rt['project_id'];
                }
                if (isset($rt['data_column_id'])) {
                    $report->data_column_id = $rt['data_column_id'];
                }
                if (isset($rt['data_value'])) {
                    $report->data_value = $rt['data_value'];
                }
                if (isset($rt['date_time'])) {
                    $report->date_time = $rt['date_time'];
                }
                if (isset($rt['row_set'])) {
                    $report->row_set = $rt['row_set'];
                }

                $analysis->reports()->save($report);

            }
        }
        //data columns mapping


        $dataColumnsMappings = $request->input('dataColumnsMapping');


        if (sizeof($dataColumnsMappings) > 0) {
            foreach ($dataColumnsMappings as $rt) {

                $DataColumnsMapping = new DataColumnsMapping();

                if (isset($rt['data_column_id'])) {
                    $DataColumnsMapping->data_column_id = $rt['data_column_id'];
                }
                if (isset($rt['value'])) {
                    $DataColumnsMapping->value = $rt['value'];
                }
                //   if (isset($rt['user_id'])) {
                //     $DataColumnsMapping->user_id = $rt['user_id'];
                // }
                // $DataColumnsMapping->user_id = $user_id;

                //   dd($DataColumnsMapping->user_id);
                //,
                // ['value','=', $DataColumnsMapping->value]

                $record = null;
                if (isset($rt['value'])) {
                    $record = DB::table('data_columns_mappings')->where('value', $rt['value'])->exists();
                }


                if ($record == true) {
                    DB::table('data_columns_mappings')->where('value', $rt['value'])->delete();
                    $user->dataColumnMappings()->save($DataColumnsMapping);
                } else if ($record == false) {
                    $user->dataColumnMappings()->save($DataColumnsMapping);
                }


            }
        }
        return response()->json(['success' => 'Created Successfully', 'id' => $analysis->id], 201);
    }
    public function updateAnalysis(Request $request, $user_id)
    {
        $analysisId=null;
        $isMerged=null;

        $user = new User();
        $user->id = $user_id;

        $projectAnalyses = $request->input('projectAnalysis');
        $analysisId = $projectAnalyses['id'];
        $isMerged = $projectAnalyses['is_merged'];

        $analysis = ProjectAnalysis::find($analysisId);

        if ($projectAnalyses != null) {
            if (sizeof($projectAnalyses) > 0) {

                if($isMerged==false){

                    DB::table('graph_version_filters')->where('project_analysis_id', $analysisId)->delete();
                    DB::table('graph_versions')->where('project_analysis_id', $analysisId)->delete();
                    DB::table('user_graph_versions')->where('project_analysis_id', $analysisId)->delete();

                    $graph_version = new GraphVersion();
                    $graph_version->graph_name = 'Default';
                    $graph_version->is_default = 1;
                    $graph_version->project_analysis_id = $analysis->id;
                    $graph_version->updated_by = $user_id;
                    $graph_version->save();
                    //$GraphVersionFilter->version()->save($graph_version);

                    //selected data colums
                    $selectedDatacolums = $request->input('selectedDatacolums');
                    $reports = $request->input('reportData');

                    if ($selectedDatacolums != null) {
                        $dataColumns = array();
                        if (sizeof($selectedDatacolums) > 0) {
                            foreach ($selectedDatacolums as $ct) {
                                $values=array();
                                if (sizeof($reports) > 0) {
                                    if(($ct != 1 && $ct != 11) && $ct != 12) {

                                        foreach ($reports as $rt) {
                                            if ($rt['data_column_id'] == $ct) {
                                                array_push($values, $rt['data_value']);
                                            }
                                        }
                                    }
                                }

                                $max_value=null;
                                $min_value=null;
                                $avg_value=null;

                                if(sizeof($values) > 0){

                                    $max_value = max($values);
                                    $max_value=round($max_value, 2);
                                    $min_value = min($values);
                                    $array_sum= array_sum($values);
                                    $avg_value=$array_sum/sizeof($values);
                                }

                                $graph_version_filter = new \stdClass();
                                $graph_version_filter->data_column_id = $ct;
                                $graph_version_filter->graph_version_id = $graph_version->id;
                                $graph_version_filter->start_value =$min_value;
                                $graph_version_filter->end_value = $max_value;
                                $graph_version_filter->avg_value =$avg_value;

                                $dataColumns [] = (array)$graph_version_filter;
                            }
                        }
                        $analysis->projectAnalyses()->sync($dataColumns);
                    }

                    //report data
                    $reports = $request->input('reportData');


                    if (sizeof($reports) > 0) {
                        DB::table('report_data')->where('project_analysis_id', $analysisId)->delete();
                        foreach ($reports as $rt) {


                            $report = new ReportData();

                            if (isset($rt['project_id'])) {
                                $report->project_id = $rt['project_id'];
                            }
                            if (isset($rt['data_column_id'])) {
                                $report->data_column_id = $rt['data_column_id'];
                            }
                            if (isset($rt['data_value'])) {
                                $report->data_value = $rt['data_value'];
                            }
                            if (isset($rt['date_time'])) {
                                $report->date_time = $rt['date_time'];
                            }
                            if (isset($rt['row_set'])) {
                                $report->row_set = $rt['row_set'];
                            }

                            $analysis->reports()->save($report);

                        }
                    }


                    //data columns mapping

                    $dataColumnsMappings = $request->input('dataColumnsMapping');


                    if (sizeof($dataColumnsMappings) > 0) {
                        foreach ($dataColumnsMappings as $rt) {

                            $DataColumnsMapping = new DataColumnsMapping();

                            if (isset($rt['data_column_id'])) {
                                $DataColumnsMapping->data_column_id = $rt['data_column_id'];
                            }
                            if (isset($rt['value'])) {
                                $DataColumnsMapping->value = $rt['value'];
                            }


                            $record = null;
                            if (isset($rt['value'])) {
                                $record = DB::table('data_columns_mappings')->where('value', $rt['value'])->exists();
                            }


                            if ($record == true) {
                                DB::table('data_columns_mappings')->where('value', $rt['value'])->delete();
                                $user->dataColumnMappings()->save($DataColumnsMapping);
                            } else if ($record == false) {
                                $user->dataColumnMappings()->save($DataColumnsMapping);
                            }


                        }
                    }

                }
                else{

                    $dbReportData = DB::table('report_data')->where('project_analysis_id',$analysisId )->get();
                    $reports = $request->input('reportData');
                    $isExist= false;


                    foreach($dbReportData as $data){
                        $filter_array = array_filter($reports, function ($key) use ($data) {

                            return $key['data_column_id'] == $data->data_column_id && $key['data_value'] == $data->data_value && $key['date_time'] == $data->date_time;
                        });
                        if(sizeof($filter_array) > 0 ){
                            return response()->json(['error' => 'The record already exists. Upload another file'], 403);
                        }
                    }

                    foreach ($reports as $rt) {

                        $report = new ReportData();

                        if (isset($rt['project_id'])) {
                            $report->project_id = $rt['project_id'];
                        }
                        if (isset($rt['data_column_id'])) {
                            $report->data_column_id = $rt['data_column_id'];
                        }
                        if (isset($rt['data_value'])) {
                            $report->data_value = $rt['data_value'];
                        }
                        if (isset($rt['date_time'])) {
                            $report->date_time = $rt['date_time'];
                        }
                        if (isset($rt['row_set'])) {
                            $report->row_set = $rt['row_set'];
                        }

                        $analysis->reports()->save($report);

    }

    $graphVersion = GraphVersion::where('project_analysis_id',$analysisId) -> where('is_default', 1) -> first();
                    $selectedDataColumns = $request->input('selectedDatacolums');

                        if (sizeof($selectedDataColumns) > 0) {
                            foreach ($selectedDataColumns as $ct) {

                                $values = array();
                                if (sizeof($reports) > 0) {
                                    if (($ct != 1 && $ct != 11) && $ct != 12) {

                                        foreach ($reports as $rt) {
                                            if ($rt['data_column_id'] == $ct) {
                                                array_push($values, $rt['data_value']);
                                            }
                                        }
                                    }
                                }
                                $max_value=null;
                                $min_value=null;
                                $avg_value=null;

                                if(sizeof($values) > 0){
                                    $max_value = max($values);
                                    $max_value = round($max_value, 2);
                                    $min_value = min($values);
                                    $array_sum = array_sum($values);
                                    $avg_value = $array_sum/sizeof($values);
                                }


                                if (($ct != 1 && $ct != 11) && $ct != 12) {

                                    $graphFilterVersion = GraphVersionFilter::where('project_analysis_id', $analysisId)
                                        ->where('data_column_id', $ct)-> where('graph_version_id', $graphVersion ->id)->first();

                                    if($graphFilterVersion != null){

                                        if($min_value > $graphFilterVersion -> start_value ) {
                                            $graphFilterVersion -> start_value = $min_value;
                                        }
                                        if($max_value > $graphFilterVersion-> end_value ) {
                                            $graphFilterVersion -> end_value = $max_value;
                                        }

                                        $graphFilterVersion -> avg_value = ( $graphFilterVersion -> avg_value + $avg_value)/2 ;

                                        $graphFilterVersion -> update();
                                    }
                                }

                            }
                        }


                }

                $analysis -> name =  $projectAnalyses['name'];
                $analysis -> product_id =  $projectAnalyses['product_id'];
                $analysis -> data_file_url = $projectAnalyses['data_file_url'];
                $analysis ->updated_by=$user_id;

                $analysis->save();
            }

        }

        return response()->json(['success' => 'Updated Record Successfully', ], 201);



//        return response()->json(['success' => 'updated Record Successfully', 'id' => $analysis->id], 201);

    }

    public function saveGraphVersion(Request $request, $analysis_id, $user_id)
    {
        $graph_version = new GraphVersion();
        $data = $request->input('data');
        $rules = [];

//dd($data);
        if ($data != null) {
            if (sizeof($data) > 0) {
                foreach ($data as $key => $value) {
                    if ($key == 'graph_name') {
//                        $rules[$key] = 'unique:graph_versions';

                       $validate = DB::table('graph_versions')-> where('graph_name', str_replace(' ', '_', $value))->where('project_analysis_id', $analysis_id)-> first();
                       if($validate != null){
                           return response() -> json(['error' => 'Graph name Already Exists'],403);
                        }

                        $graph_version->graph_name = str_replace(' ', '_', $value);

//                        $graph_version->graph_name = $value;

                    } else if ($key == 'graph_picture_url') {
                        if ($value != null) {
                            if ($value != 'data:' && $value != null) {
                                preg_match("/data:image\/(.*?);/", $value, $image_extension);
                                @list($type, $file_data) = explode(';', $value);
                                @list(, $file_data) = explode(',', $file_data);
                                $imageName = 'file_' . time() . '_' . rand(pow(10, 3 - 1), pow(10, 3) - 1) . '.' . $image_extension[1];
                                Storage::disk('local')->put('public/uploads/' . $imageName, base64_decode($file_data));
                                $graph_version->encrypted_name = $imageName;
                                $graph_version->graph_picture_url = storage_path() . 'public/uploads/' . $imageName;
                                $graph_version->project_analysis_id = $analysis_id;
                                $graph_version->created_by = $user_id;
                                $graph_version->is_default = 0;
                            } else {
                                return response()->json(['error' => 'Invalid file url '], 403);
                            }
                        }
                    } else if ($key == 'start_time') {
                        $graph_version->start_time = $value;
                    } else if ($key == 'finish_time') {
                        $graph_version->finish_time = $value;
                    }
                }

                $validator = Validator::make($data, $rules);

                if ($validator->fails()) {
                    return response()->json(['error' => 'graph name already exists'], 403);
                }

                $graph_version->save();

                $dataArray = $request->input('dataArray');

//                DB::table('graph_version_filters')->where('project_analysis_id', $analysis_id)->where('graph_version_id', $graph_version->id)->delete();

                if (sizeof($dataArray) > 0) {
                    foreach ($dataArray as $da) {

                        $version_filter = new GraphVersionFilter();
                        $version_filter->project_analysis_id = $analysis_id;
                        $version_filter->graph_version_id = $graph_version->id;


                        if (isset($da['id'])) {
                            $version_filter->data_column_id = $da['id'];
                        }
                        if (isset($da['display'])) {
                            $version_filter->is_display = $da['display'];
                        }

                        $ticks = $da['ticks'];
                        if (sizeof($ticks) > 0) {
//                            dd($ticks);
                            foreach ($ticks as $key => $value) {
                                if ($da['display'] == true) {
                                    if ($key == 'min') {
                                        $version_filter->start_value = $value;
                                    }
                                    if ($key == 'max') {
                                        $version_filter->end_value = $value;
                                    }
                                    if ($key == 'avg') {
                                        $version_filter->avg_value = $value;
                                    }
                                    if ($key == 'fontColor') {
                                        $version_filter->color_code = $value;
                                    }
                                }
                            }
                        }
                        $graph_version->filters()->save($version_filter);
                    }
                }
            }
        }
        return response()->json(['success' => 'graph version saved successfully' , $graph_version], 200);
    }

    public function updateGraphVersion(Request $request, $analysis_id, $user_id, $id)
    {
        $graph_version = GraphVersion::findOrFail($id);
        $data = $request->input('data');
        $rules = [];

        if ($data != null) {
            if (sizeof($data) > 0) {
                foreach ($data as $key => $value) {
                    if ($key == 'graph_name') {
                        $rules[$key] = ['required', Rule::unique('graph_versions')->ignore($graph_version->id)];
                        $graph_version->graph_name = $value;
                    } else if ($key == 'graph_picture_url') {
                        if ($value != null) {
                            if ($value != 'data:' && $value != null) {
                                preg_match("/data:image\/(.*?);/", $value, $image_extension);
                                @list($type, $file_data) = explode(';', $value);
                                @list(, $file_data) = explode(',', $file_data);
                                $imageName = 'file_' . time() . '_' . rand(pow(10, 3 - 1), pow(10, 3) - 1) . '.' . $image_extension[1];
                                Storage::disk('local')->put('public/uploads/' . $imageName, base64_decode($file_data));
                                $graph_version->encrypted_name = $imageName;
                                $graph_version->graph_picture_url = storage_path() . 'public/uploads/' . $imageName;
                                $graph_version->project_analysis_id = $analysis_id;
                                $graph_version->created_by = $user_id;
                                $graph_version->is_default = 0;
                            } else {
                                return response()->json(['error' => 'Invalid file url '], 403);
                            }
                        }
                    } else if ($key == 'start_time') {
                        $graph_version->start_time = $value;
                    } else if ($key == 'finish_time') {
                        $graph_version->finish_time = $value;
                    }
                }

                $validator = Validator::make($data, $rules);

                if ($validator->fails()) {
                    return response()->json(['error' => 'graph name already exists'], 403);
                }

                $graph_version->save();

                $dataArray = $request->input('dataArray');


                DB::table('graph_version_filters')->where('project_analysis_id', $analysis_id)->where('graph_version_id', $graph_version->id)->delete();

                if (sizeof($dataArray) > 0) {
                    foreach ($dataArray as $da) {

                        $version_filter = new GraphVersionFilter();
                        $version_filter->project_analysis_id = $analysis_id;
                        $version_filter->graph_version_id = $graph_version->id;

                        if (isset($da['id'])) {
                            $version_filter->data_column_id = $da['id'];
                        }
                        if (isset($da['display'])) {
                            $version_filter->is_display = $da['display'];
                        }

                        $ticks = $da['ticks'];
                        if (sizeof($ticks) > 0) {
//                            dd($ticks);
                            foreach ($ticks as $key => $value) {
//                                dd($ticks);
                                if ($da['display'] == true) {
                                    if ($key == 'min') {
                                        $version_filter->start_value = $value;
                                    }
                                    if ($key == 'max') {
                                        $version_filter->end_value = $value;
                                    }
                                    if ($key == 'fontColor') {
                                        $version_filter->color_code = $value;
                                    }
                                }
                            }
                        }
                        $graph_version->filters()->save($version_filter);
                    }


                }
            }
        }

        return response()->json(['success' => 'graph version updated successfully',$graph_version], 200);
    }

    public function deleteGraphVersion($id)
    {
        $version = GraphVersion::findOrFail($id);
        if ($version != null) {
            DB::table('graph_version_filters')->where('graph_version_id', $id)->delete();
            $version->delete();
        }
        return response()->json(['success' => 'graph version deleted successfully'], 200);
    }

    public function getProjectData($user_id, $project_id,$role_id)
    {
        $pdo = DB::connection()->getpdo();

        if($role_id==4){
            $stmt = $pdo->prepare('CALL   get_customer_project_data(?,?)');
            $stmt->execute(array($user_id, $project_id));
        }
        else{
        $stmt = $pdo->prepare('CALL  get_project_data(?,?)');
        $stmt->execute(array($user_id, $project_id));
    }



        $project = $stmt->fetchAll(PDO::FETCH_CLASS, 'stdClass');
        $stmt->nextRowset();

        $analyses = $stmt->fetchAll(PDO::FETCH_CLASS, 'stdClass');
        $stmt->nextRowset();

        $products = $stmt->fetchAll(PDO::FETCH_CLASS, 'stdClass');
        $stmt->nextRowset();

        $products_meta = $stmt->fetchAll(PDO::FETCH_CLASS, 'stdClass');
        $stmt->nextRowset();

        return response()->json(['project' => $project, 'Analyses' => $analyses,
            'products' => $products, 'products_meta' => $products_meta], 200);
    }

    public function getCompareMeta($user_id, $role_id)
    {
        $pdo = DB::connection()->getpdo();

        if ($role_id ==1||$role_id ==2) {
            $stmt = $pdo->prepare('CALL get_compare_meta()');
            $stmt->execute(array());
        }
        if($role_id==3){
            $stmt = $pdo->prepare('CALL get_employee_compare_meta(?)');
            $stmt->execute(array($user_id));
        }
        if($role_id==4){
            $stmt = $pdo->prepare('CALL get_customer_compare_meta(?)');
            $stmt->execute(array($user_id));
        }


        $projects = $stmt->fetchAll(PDO::FETCH_CLASS, 'stdClass');
        $stmt->nextRowset();

        $analyses = $stmt->fetchAll(PDO::FETCH_CLASS, 'stdClass');
        $stmt->nextRowset();

        $versions = $stmt->fetchAll(PDO::FETCH_CLASS, 'stdClass');
        $stmt->nextRowset();

        return response()->json(['projects' => $projects, 'analyses' => $analyses,
            'versions' => $versions], 200);
    }

    public function getAnalysisGraphs($user_id, $analysis_id,$role_id)
    {
        $pdo = DB::connection()->getpdo();
        if($role_id!=4){
            $stmt = $pdo->prepare('CALL get_all_graphs(?,?)');
            $stmt->execute(array($user_id, $analysis_id));
        }
        else{
            $stmt = $pdo->prepare('CALL get_customer_graphs(?,?)');
            $stmt->execute(array($user_id, $analysis_id));

        }



        $graphs = $stmt->fetchAll(PDO::FETCH_CLASS, 'stdClass');
        $stmt->nextRowset();

        return response()->json(['graphs' => $graphs], 200);
    }


    public function getAnalysis($id, $graph_id, $user_id)
    {
        $position='left';


        $default_version = DB::table('graph_versions')->select('id', 'project_analysis_id', 'graph_name')->where('project_analysis_id', $id)->where('is_default', 1)->where('is_deleted', 0)->first();

        $pdo = DB::connection()->getpdo();

        $stmt = $pdo->prepare('CALL get_analysis(?,?)');
        $stmt->execute(array($id, $graph_id));


        $temp_analysis = $stmt->fetchAll(PDO::FETCH_CLASS, 'stdClass');
        $analysis = null;
        if (sizeof($temp_analysis) > 0) {
            $analysis = $temp_analysis[0];
        } else {
            $analysis = $stmt->fetchAll(PDO::FETCH_CLASS, 'stdClass');
        }

        $stmt->nextRowset();


        $temp_main_array = $stmt->fetchAll(PDO::FETCH_CLASS, 'stdClass');
        $stmt->nextRowset();

        $temp_graph = $stmt->fetchAll(PDO::FETCH_CLASS, 'stdClass')[0];
        $stmt->nextRowset();

        $object_types = $stmt->fetchAll(PDO::FETCH_CLASS, 'stdClass');
        $stmt->nextRowset();

        $temp_column_array = $stmt->fetchAll(PDO::FETCH_CLASS, 'stdClass');
        $stmt->nextRowset();



        $data_array = array();
        $data_points = array();

        foreach ($temp_main_array as $ma) {

            $main = new \stdClass();
            $main->column_header = $ma->column_header;
            $main->column_id = $ma->column_id;
            $main->position = $position;
            $main->color_code = $ma->color_code;
            $main->is_change = 0;
            $main->unit= $ma->unit_name;

            $position=$position ==='left'? 'right':'left';

//          $main->initial_value = $ma->initial_value;
//          $main->final_value = $ma->final_value; avg_value

            $main->column_min = $ma->min;
            $main->column_max = $ma->max;
            $main->avg_value = $ma->avg_value;
            $main->is_do_curve_visible = true;
            $main->curve_type = 1;
            $main->curve_color_code = '#2c2c5c';


            $filter_array = array_filter($temp_column_array, function ($key) use ($ma) {
                return $key->data_column_id == $ma->column_id;
            });

            $main->column_array = array();

            if (sizeof($filter_array) > 0) {
                foreach ($filter_array as $fa) {

                    $coordinate = new \stdClass();
                    $coordinate->x = $fa->xvalue;
                    $coordinate->y = (float)$fa->yvalue;
                    $main->column_array [] = $coordinate;
                }
            }
            $data_array [] = (array)$main;


            $main->object_type_array = array();

//            if (sizeof($filter_array) > 0) {
//                foreach ($filter_array as $fa) {
//                    $object_type = new \stdClass();
//                    $object_type->object_value =  $fa->object_value;
//                    $object_type->object_name =  $fa->object_name;
//                    $main->object_type_array [] = $object_type;
//                }
//            }
//            $object_type_array [] = (array)$main;


            $points = array();

            if (sizeof($filter_array) > 0) {
                foreach ($filter_array as $far) {
                    $point = new \stdClass();
                    $point->y = $far->data_value;
                    $points [] = $point;
                }
            }
            $data_points [] = (array)$points;
        }

//        $time_array = array();
//
//        if (sizeof($temp_time_array) > 0) {
//            foreach ($temp_time_array as $tta) {
//
//                $xaxis = new \stdClass();
//                $xaxis->x = $tta->data_value;
//
//                $time_array [] = $xaxis;
//
//            }
//        }


        return response()->json([
            'analysis' => $analysis,
            'graphVersion' => $temp_graph,
            'filterValues' => $temp_main_array,
            'dataArray' => $data_array,
            'object_types' => $object_types,
            'defaultVersion' => $default_version
        ], 200);

    }


}
