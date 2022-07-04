<?php

use App\Http\Controllers\Api\HomeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return response()->json([
        'Programmer' => 'Hello :)'
    ]);
});
Route::any('sitemap.xml', [HomeController::class, 'sitemap'])->name('sitemap');

