<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


//kunyakon
use App\Http\Controllers\ScholarController;

Route::get('/scholar/search', [ScholarController::class, 'searchScholar']);
Route::get('/scholar/{id}', [ScholarController::class, 'getScholar']);

//kunyakon
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
