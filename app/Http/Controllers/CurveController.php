<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use PDO;

class CurveController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getCurveMeta($user_id, $role_id)
    {
        $pdo = DB::connection()->getpdo();
        if ($role_id ==1||$role_id ==2) {
            $stmt = $pdo->prepare('CALL get_curve_meta()');
            $stmt->execute(array());
        }
        if($role_id==3){
            $stmt = $pdo->prepare('CALL get_employee_curve_meta(?)');
            $stmt->execute(array($user_id));
        }
        if($role_id==4){
            $stmt = $pdo->prepare('CALL get_customer_curve_meta(?)');
            $stmt->execute(array($user_id));
        }



        $projects = $stmt->fetchAll(PDO::FETCH_CLASS, 'stdClass');
        $stmt->nextRowset();

        $products = $stmt->fetchAll(PDO::FETCH_CLASS, 'stdClass');
        $stmt->nextRowset();

        $analysis = $stmt->fetchAll(PDO::FETCH_CLASS, 'stdClass');
        $stmt->nextRowset();

        $versions = $stmt->fetchAll(PDO::FETCH_CLASS, 'stdClass');
        $stmt->nextRowset();

        $data_columns = $stmt->fetchAll(PDO::FETCH_CLASS, 'stdClass');
        $stmt->nextRowset();

        return response()-> json([
        'projects' => $projects, 'products' => $products ,'analysis' =>$analysis , 'versions'=>$versions
        ,'data_columns' => $data_columns],201);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Request $request
     * @param $project_id
     * @param $column_id
     * @return void
     */
    public function getCurveComparisonData(Request $request, $project_id, $column_id)
    {

        $analyses = $request-> input('analyses');
        $versions = $request-> input('versions');

        if (sizeof($analyses) > 0) {
            $analyses = implode(',', $analyses);
        } else {
            $analyses = '';
        }
        if (sizeof($versions) > 0) {
            $versions = implode(',', $versions);
        } else {
            $versions = '';
        }

        $pdo = DB::connection()->getpdo();

        $stmt = $pdo->prepare('CALL get_curve_comparison_data(
        :project_id,:column_id,:analyses,:versions
        )');
        $stmt->execute(array(
            ':project_id' => $project_id,
            ':column_id' => $column_id,
            ':analyses' => $analyses,
            ':versions' => $versions
    ));

        $temp_main_array = $stmt->fetchAll(PDO::FETCH_CLASS, 'stdClass');
        $stmt->nextRowset();

        $temp_column_array = $stmt->fetchAll(PDO::FETCH_CLASS, 'stdClass');
        $stmt->nextRowset();


        $data_array = array();
        foreach ($temp_main_array as $key => $ma) {

            if($key == 0) {$color = '#918484';}
            else if($key == 1) {$color = '#36a2eb';}
            else if($key == 2) {$color = '#ffce56';}
            else if($key == 3) {$color = '#97bbcd';}
            else if($key == 4) {$color = '#4bc0c0';}
            else if($key == 5) {$color = '#e7e9ed';}
            else  {$color = '#dcdcdc';}

                    $main = new \stdClass();
                    $main->analysis_id = $ma->project_analysis_id;
                    $main->column_header = ($key + 1).'_'.$ma->column_header;
                    $main->column_id = $ma->column_id;
                    $main->position = 'left';
                    $main->color_code = $color;
                    $main->column_min = $ma->min;
                    $main->column_max = $ma->max;
                    $main->avg_value = $ma->avg_value;
                    $main->is_do_curve_visible = true;
                    $main->curve_type = 1;
                    $main->is_default = $ma->is_default;
                    $main->curve_color_code = '#2c2c5c';


                    $filter_array = array_filter($temp_column_array, function ($key) use ($ma) {
                        return $key->project_analysis_id == $ma->project_analysis_id;
                    });
//
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

                }

                return response()->json(['dataArray' => $data_array], 200);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
