<?php

use App\Http\Controllers\FixtureController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\SimulationController;
use App\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TeamController::class, 'index']);
Route::post('/teams', [TeamController::class, 'store']);
Route::put('/teams/{team}', [TeamController::class, 'update']);
Route::delete('/teams/{team}', [TeamController::class, 'destroy']);
Route::get('/fixtures', [FixtureController::class, 'index']);
Route::post('/fixtures/generate', [FixtureController::class, 'generate']);
Route::get('/simulation', [SimulationController::class, 'index']);
Route::post('/simulation/play-next', [SimulationController::class, 'playNext']);
Route::post('/simulation/play-all', [SimulationController::class, 'playAll']);
Route::post('/simulation/reset', [SimulationController::class, 'reset']);
Route::put('/matches/{matchResult}', [MatchController::class, 'update']);
