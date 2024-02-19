<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JobController;
use App\Http\Controllers\UrlController;
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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/url', [UrlController::class, 'index'])->name('url.index');
Route::get('/url/create', [UrlController::class, 'create'])->name('url.create');
Route::post('/url', [UrlController::class, 'store'])->name('url.store');
Route::get('/url/{key}', [UrlController::class, 'show'])->name('url.show');
Route::get('/url/{key}/edit', [UrlController::class, 'edit'])->name('url.edit');
Route::put('/url/{key}', [UrlController::class, 'update'])->name('url.update');
Route::delete('/url/{key}', [UrlController::class, 'destroy'])->name('url.destroy');
Route::get('/url/scrap/{key}', [UrlController::class, 'scrap'])->name('url.scrap');