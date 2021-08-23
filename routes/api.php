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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

$api = app('Dingo\Api\Routing\Router');
$api->version('v1', function ($api) {

    $api->group(['namespace' => 'App\Http\Controllers', 'middleware' => ['api']], function ($api) {
        $api->resource('products','ProductController');
        $api->post('products/{code}/stocks', 'ProductController@addStock');
        $api->post('products/bulk', 'ProductController@upsertBulk');
        $api->post('stocks/bulk', 'StockController@insertBulk');
    });

});
