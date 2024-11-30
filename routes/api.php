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

Route::middleware('verify-token')->group(function () {
    Route::get('travel/{travel_id}/compatible', [\App\Http\Controllers\api\IntercessionController::class, 'findRoutes']);
    // Travel routes
    Route::prefix('travel')->group(function () {
        Route::get('{user_id}', [\App\Http\Controllers\api\TravelController::class, 'getTravel']);
        Route::get('{user_id}/proposal', [\App\Http\Controllers\api\ProposalController::class, 'getUserProposals']);
        Route::get('all/{travel_id}', [\App\Http\Controllers\api\TravelController::class, 'getAllTravels']);
        Route::post('{user_id}/store', [\App\Http\Controllers\api\TravelController::class, 'store']);
        Route::delete('{travel_id}', [\App\Http\Controllers\api\TravelController::class, 'deleteTravel']);
    });

    // Proposal routes
    Route::post('client/{client_travel_id}/carrier/{travel_id}', [\App\Http\Controllers\api\ProposalController::class, 'proposal']);
    Route::get('carrier/{travel_id}/proposal', [\App\Http\Controllers\api\ProposalController::class, 'getCarrierProposal']);
    Route::get('proposal/{travel_id}/travel', [\App\Http\Controllers\api\ProposalController::class, 'getTravelProposal']);
    Route::get('client/{client_travel_id}/proposal', [\App\Http\Controllers\api\ProposalController::class, 'getClientProposal']);
    Route::patch('proposal/{proposal_id}/accept', [\App\Http\Controllers\api\ProposalController::class, 'acceptProposal']);
    Route::delete('proposal/{proposal_id}', [\App\Http\Controllers\api\ProposalController::class, 'deleteProposal']);
});
