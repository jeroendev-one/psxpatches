<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\WebController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Visitor routes
Route::get('/', [WebController::class, 'getIndex'])->name('home');
Route::get('/details/{title_id}', [WebController::class, 'getDetails'])->name('details');

Route::get('/search', [WebController::class, 'liveSearch']);


//Route::get('/{any?}', [WebController::class, 'catchAll'])->where('any', '.*')->name('catchall');