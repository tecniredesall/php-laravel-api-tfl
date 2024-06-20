<?php

use Illuminate\Http\Request;
use Illuminate\Http\Response;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|*/

//Route::get('/dashboard/reports', 'API\WEB\Reports@index');

Route::namespace('API\WEB')->prefix('webhook')->group(function () {
    Route::resource('dropbox','DropboxSync');
});


Route::fallback(function () {
    return response()->json([
        "status" => false,
        "code" => 404,
        "reason" => "not found",
        "message" => "Not Found",
        "data" => []
    ], 404);
});

?>