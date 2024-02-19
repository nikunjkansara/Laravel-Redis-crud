<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UrlController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/url', [UrlController::class, 'index']);
Route::post('/url', [UrlController::class, 'store']);
Route::get('/url/{id}', [UrlController::class, 'show']);
Route::delete('/url/{id}', [UrlController::class, 'destroy']);
Route::get('/url/scrap/{id}', [UrlController::class, 'scrap']);
