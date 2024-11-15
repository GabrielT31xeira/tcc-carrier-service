<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::get('/travel/{user_id}', [\App\Http\Controllers\api\TravelController::class, 'getTravel'])->middleware('verify-token');
Route::get('/travel-all/{travel_id}', [\App\Http\Controllers\api\TravelController::class, 'getAllTravels'])->middleware('verify-token');
Route::post('/travel/{user_id}/store', [\App\Http\Controllers\api\TravelController::class, 'store'])->middleware('verify-token');
Route::delete('/travel/{travel_id}', [\App\Http\Controllers\api\TravelController::class, 'deleteTravel'])->middleware('verify-token');
