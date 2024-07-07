<?php

use App\Http\Controllers\API\GitHubController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/repositories', [GitHubController::class, 'getRepositories']);
Route::post('/chart', [GitHubController::class, 'generateChart']);
Route::post('/clear-repositories-cache', [GitHubController::class, 'clearRepositoriesCache']);
