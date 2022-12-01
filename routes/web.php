<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|------------------------right-move--------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//Route::get('/', function () {
//    return view('welcome');
//});

Route::get('/',[\App\Http\Controllers\SymfonyScrapeController::class,'symfonyScrape']);
Route::post('right-move',[\App\Http\Controllers\SymfonyScrapeController::class,'rightMoveSiteData']);
Route::get('delete-rec',[\App\Http\Controllers\SymfonyScrapeController::class,'destroy']);

Route::post('import', [\App\Http\Controllers\SymfonyScrapeController::class, 'import']);
