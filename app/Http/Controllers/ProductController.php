<?php

namespace App\Http\Controllers;

use App\Models\Cabinet;
use App\Models\Loop;
use App\Models\PPCabinet;
use App\Models\PPLoop;
use App\Models\Product;
use App\Models\ProjectProduct;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use DB;
use Illuminate\Support\Facades\Validator;
use PDO;

class ProductController extends Controller
{

    /**
     * @param Request $request
     * @param $user_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function createProduct(Request $request, $user_id)
    {

        $validation = Validator::make($request -> all(), [
            'name' => 'required',
            'serial_number' => 'required|unique:project_products',
            'software_version' => 'required',
        ]);
        if($validation -> fails()){
            $error = $validation-> errors()-> first();
            return response()-> json(['error' => $error], 403);
        }

        if($request-> input('is_new') == true){
            $product = new Product();
            $product->name = $request->input('name');
            $product->created_by = $user_id;
            $product->is_deleted = 0;
            $product->save();
        }else{
            $product = Product::findOrFail($request->input('temp_id'));
        }


        $project_product = new ProjectProduct();
        $project_product->project_id = $request->input('project_id');
        $project_product->serial_number = $request->input('serial_number');
        $project_product->software_version = $request->input('software_version');
        $product->projects()->save($project_product);

        return response()->json(['success' => 'Created Successfully',
            'id' => $product->id , 'project_product_id' => $project_product->id], 201);


    }

    public function updateProduct(Request $request, $user_id)
    {

        $project_product_id = $request->input('project_product_id');

        $validation = Validator::make($request -> all(), [
            'serial_number' => 'required',
            'software_version' => 'required',
        ]);

        $validation->sometimes('serial_number', Rule::unique('project_products')->ignore($project_product_id), function ($input) {
            return $input->serial_number != null;
        });

        if($validation -> fails()){
            $error = $validation-> errors()-> first();
            return response()-> json(['error' => $error], 403);
        }

        $project_product = ProjectProduct::findOrFail($project_product_id);
        $project_product->serial_number = $request->input('serial_number');
        $project_product->software_version = $request->input('software_version');
        $project_product-> update();

        return response()->json(['success' => 'Updated Successfully'], 201);


//        $product = Product::find($request->input('id'));
//
//        $product->serial_number = $request->input('serial_number');
//        $product->name = $request->input('name');
//        $product->software_version = $request->input('software_version');
//        $product->updated_by = $user_id;
//
//        $product->save();

    }

    public function getProduct($name, $user_id)
    {
        $pdo = DB::connection()->getpdo();
        $stmt = $pdo->prepare('CALL get_product(?)');
        $stmt->execute(array($name));

        $product = $stmt->fetchAll(PDO::FETCH_CLASS, 'stdClass');
        $stmt->nextRowset();

        return response()->json(['product' => $product,], 200);

    }
    public  function  deleteProduct($project_id,$id, $project_product_id){

        $project_product= ProjectProduct::findOrFail($project_product_id);
        if($project_product != null){
            $project_product-> delete();
        }

        return response()->json(['success' => 'Product deleted successfully'], 200);

//
//        $product = Product::find($id);
//        $product->projects()->where('project_id', '=', $project_id)->detach();

//        $analysis->reports()->delete();
//
//        $graph_versions = $analysis->graphVersions()->get();
//
//        if(sizeof($graph_versions) > 0)
//        {
//            foreach ($graph_versions as $gv)
//            {
//                $gv->filters()->delete();
//
//                $gv->users()->detach();
//            }
//        }
//
//        $analysis->graphVersions()->delete();
//        $analysis->delete();

    }

    /**
     * @param Request $request
     * @param $user_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function createCabinet(Request $request,$user_id) {

        $validator = Validator::make($request->all(), [
            'project_id' => 'required',
            'product_id' => 'required',
            'name' => 'required',

            'serial_number' => 'required|unique:pp_cabinets',

        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return response()->json(['error' => $errors], 403);
        }
        $project_product_id = DB::table('project_products')->where('project_id',$request-> input('project_id'))
            ->where('product_id',$request-> input('product_id'))->first();

        if($project_product_id -> id != null){

            $cabinet = new PpCabinet();
            $cabinet -> name = $request-> input('name');
            $cabinet -> serial_number = $request-> input('serial_number');
            $cabinet -> created_by = $user_id;
            $cabinet -> pp_id = $project_product_id -> id;
            $cabinet -> save();

            return response()->json(['success' => 'Cabinet created successfully',
                'cabinet' => $cabinet],201);


        }else {
            return response()->json(['error' => 'Error has occurred'],403);
        }
    }

    /**
     * @param Request $request
     * @param $user_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCabinet(Request $request,$user_id) {


        $validator = Validator::make($request->all(), [
            'project_id' => 'required',
            'product_id' => 'required',
            'name' => 'required',
            'serial_number' => 'required',
        ]);

        $validator->sometimes('serial_number', Rule::unique('pp_cabinets')->ignore($request-> input('id')),
            function ($input) {return $input->serial_number != null; });

        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return response()->json(['error' => $errors], 403);
        }

        $project_product = DB::table('project_products')->where('project_id',$request-> input('project_id'))
            ->where('product_id',$request-> input('product_id'))->first();

        if($project_product -> id != null){

            $cabinet = PpCabinet::findOrFail($request-> input('id'));
            $cabinet -> name = $request-> input('name');
            $cabinet -> serial_number = $request-> input('serial_number');
            $cabinet -> updated_by = $user_id;
            $cabinet -> update();

            return response()->json(['success' => 'Cabinet updated successfully'],201);

        }else {
            return response()->json(['error' => 'Error has occurred'],403);
        }
    }

    /**
     * @param Request $request
     * @param $user_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function createLoop(Request $request, $user_id) {
        $validator = Validator::make($request->all(), [
            'project_id' => 'required',
            'product_id' => 'required',
            'name' => 'required',
            'cabinet_id' => 'required',
            'serial_number' => 'required|unique:pp_loops',

        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return response()->json(['error' => $errors], 403);
        }
        $project_product_id = DB::table('project_products')->where('project_id',$request-> input('project_id'))
            ->where('product_id',$request-> input('product_id'))->first();

        if($project_product_id -> id != null){
            $loop = new PpLoop();
            $loop -> name = $request-> input('name');
            $loop -> serial_number = $request-> input('serial_number');
            $loop -> cabinet_id = $request-> input('cabinet_id');
            $loop -> created_by = $user_id;
            $loop -> pp_id = $project_product_id -> id;
            $loop -> save();

            return response()->json(['success' => 'Loop created successfully',
                'loop' => $loop],201);
        }else {
            return response()->json(['error' => 'Error has occurred'],403);
        }
    }

    /**
     * @param Request $request
     * @param $user_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateLoop(Request $request, $user_id) {

        $validator = Validator::make($request->all(), [
            'project_id' => 'required',
            'product_id' => 'required',
            'name' => 'required',
            'serial_number' => 'required',
        ]);

        $validator->sometimes('serial_number', Rule::unique('pp_loops')->ignore($request-> input('id')),
            function ($input) {return $input->serial_number != null; });

        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return response()->json(['error' => $errors], 403);
        }


        $project_product = DB::table('project_products')->where('project_id',$request-> input('project_id'))
            ->where('product_id',$request-> input('product_id'))->first();

        if($project_product -> id != null){
            $loop = PpLoop::findOrFail($request-> input('id'));
            $loop -> name = $request-> input('name');
            $loop -> serial_number = $request-> input('serial_number');
            $loop -> cabinet_id = $request-> input('cabinet_id');
            $loop -> updated_by = $user_id;
            $loop -> update();

            return response()->json(['success' => 'Loop updated successfully'],201);
        }else {
            return response()->json(['error' => 'Error has occurred'],403);
        }

    }

    public function deleteCabinet($user_id ,$id)
    {
        $cabinet = PpCabinet::findOrFail($id);
        if ($cabinet != null) {

            DB::table('pp_loops')->where('cabinet_id',$id)->delete();
            $cabinet->delete();
        }
        return response()->json(['success' => 'Cabinet deleted successfully'], 200);
    }

    public function deleteLoop($user_id,$id)
    {
        $loop = PPLoop::findOrFail($id);
        if ($loop != null) {
            $loop->delete();
        }
        return response()->json(['success' => 'Loop deleted successfully'], 200);
    }


    public function getProductData($project_id, $product_id) {

        $project_product = DB::table('project_products')->where('project_id',$project_id)
            ->where('product_id',$product_id)->first();
//        dd($project_product_id);

        if($project_product != null){
        if($project_product -> id != null ) {

            $pdo = DB::connection()->getpdo();
            $stmt = $pdo->prepare('CALL get_product_data(?)');
            $stmt->execute(array($project_product -> id));

            $cabinets = $stmt->fetchAll(PDO::FETCH_CLASS, 'stdClass');
            $stmt->nextRowset();

            $loops = $stmt->fetchAll(PDO::FETCH_CLASS, 'stdClass');
            $stmt->nextRowset();

            return response()->json(['cabinets' => $cabinets,'loops' => $loops], 200);
        }else {
            return response()->json(['error' => 'Error has occurred'],403);
        }
        }

    }



}

