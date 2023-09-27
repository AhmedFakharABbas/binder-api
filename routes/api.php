<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/



Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/password/reset', 'App\Http\Controllers\Auth\ResetPasswordController@reset');
Route::group(['middleware' => ['auth:api']], function () {
//project
    Route::get('projects/all/{user_id}/{role_id}', 'App\Http\Controllers\ProjectController@getProjects');
    Route::get('projects/single/{id}/{user_id}', 'App\Http\Controllers\ProjectController@getSingleProject');
    Route::get('projects/analysis/meta/{id}/{user_id}', 'App\Http\Controllers\ProjectController@projectAnalysisMeta');
    Route::get('projects/analysis/update/meta/{id}/{analysis_id}/{user_id}', 'App\Http\Controllers\ProjectController@projectUpdateAnalysisMeta');
    Route::post('projects/create/{user_id}', 'App\Http\Controllers\ProjectController@createProject');
    Route::post('project/analysis/graph/save/{analysis_id}/{user_id}', 'App\Http\Controllers\ProjectController@saveGraphVersion');
    Route::post('project/analysis/graph/update/{analysis_id}/{user_id}/{id}', 'App\Http\Controllers\ProjectController@updateGraphVersion');
    Route::post('projects/update/{user_id}', 'App\Http\Controllers\ProjectController@updateProject');
    Route::get('project/analysis/{project_id}/{user_id}', 'App\Http\Controllers\ProjectController@getProjectAnalysis');
    Route::delete('project/delete/{id}', 'App\Http\Controllers\ProjectController@deleteProject');


    //products
    Route::get('product/{name}/{user_id}', 'App\Http\Controllers\ProductController@getProduct');
    Route::post('product/create/{user_id}', 'App\Http\Controllers\ProductController@createProduct');
    Route::post('product/update/{user_id}', 'App\Http\Controllers\ProductController@updateProduct');
    Route::delete('project/product/delete/{project_id}/{id}/{project_product_id}', 'App\Http\Controllers\ProductController@deleteProduct');

    Route::post('product/cabinet/create/{user_id}', 'App\Http\Controllers\ProductController@createCabinet');
    Route::post('product/cabinet/update/{user_id}', 'App\Http\Controllers\ProductController@updateCabinet');
    Route::post('product/loop/create/{user_id}', 'App\Http\Controllers\ProductController@createLoop');
    Route::post('product/loop/update/{user_id}', 'App\Http\Controllers\ProductController@updateLoop');
    Route::get('product/data/{project_id}/{product_id}', 'App\Http\Controllers\ProductController@getProductData');
//    Route::get('product/cabinet/{user_id}', 'App\Http\Controllers\ProductController@getProductCabinet');
//    Route::get('product/loop/{user_id}', 'App\Http\Controllers\ProductController@getProductLoop');
    Route::delete('product/cabinet/delete/{user_id}/{id}', 'App\Http\Controllers\ProductController@deleteCabinet');
    Route::delete('product/loop/delete/{user_id}/{id}', 'App\Http\Controllers\ProductController@deleteLoop');


    //analysis
    Route::post('project/analysis/create/{user_id}', 'App\Http\Controllers\ProjectController@createAnalysis');
    Route::post('project/analysis/update/{user_id}', 'App\Http\Controllers\ProjectController@updateAnalysis');
    Route::get('project/all/analysis/products/{user_id}/{project_id}/{role_id}', 'App\Http\Controllers\ProjectController@getProjectData');
    Route::get('project/analysis/{id}/{graph_id}/{user_id}', 'App\Http\Controllers\ProjectController@getAnalysis');
    Route::get('analysis/graphs/{user_id}/{analysis_id}/{role_id}', 'App\Http\Controllers\ProjectController@getAnalysisGraphs');
    Route::get('compare/meta/{user_id}/{role_id}', 'App\Http\Controllers\ProjectController@getCompareMeta');
    Route::delete('project/analysis/delete/{id}', 'App\Http\Controllers\ProjectController@deleteAnalysis');


    // data column
    Route::post('/project/dataColumn/creat/{user_id}', 'App\Http\Controllers\ProjectController@createdataColumn');
    //user
    Route::post('/register/{user_id}', 'App\Http\Controllers\Auth\RegisteredUserController@store');

    Route::get('users/all/{user_id}/{role_id}', 'App\Http\Controllers\ProjectController@getUsers');
    Route::get('user/projects/{user_id}', 'App\Http\Controllers\ProjectController@getUserProject');
    Route::get('user/graphversion/{user_id}', 'App\Http\Controllers\ProjectController@getUserGraphVersion');

    Route::delete('project/analysis/graph/delete/{id}', 'App\Http\Controllers\ProjectController@deleteGraphVersion');
    Route::post('user/asssociate/project/{user_id}/{as_u_id}', 'App\Http\Controllers\ProjectController@associateProject');
    Route::post('user/asssociate/versions/{user_id}/{as_u_id}', 'App\Http\Controllers\ProjectController@associateVersions');
    Route::delete('users/delete/{id}', 'App\Http\Controllers\ProjectController@deleteUser');

    //Curve Comparison
    Route::get('curve/meta/{user_id}/{role_id}', 'App\Http\Controllers\CurveController@getCurveMeta');
    Route::post('curve/comparison-data/{project_id}/{column_id}', 'App\Http\Controllers\CurveController@getCurveComparisonData');




});


