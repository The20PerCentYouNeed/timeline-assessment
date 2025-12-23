<?php

use App\Http\Controllers\API\StepController;
use App\Http\Controllers\API\StepStatusController;
use App\Http\Controllers\API\TimelineController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/timelines/{timeline}', [TimelineController::class, 'show']);

    Route::post('/timelines', [TimelineController::class, 'store']);

    Route::post('/timelines/{timeline}/steps', [StepController::class, 'store']);

    Route::post('/steps/{step}/statuses', [StepStatusController::class, 'store']);
});
