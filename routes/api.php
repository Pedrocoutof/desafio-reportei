<?php

use App\Http\Controllers\API\GitHubController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/repositories/{user}', [GitHubController::class, 'getRepositories']);
Route::get('/chart/{user}/{repository}', [GitHubController::class, 'generateChart']);

Route::get('/repository', [GitHubController::class, 'getRepository']);
Route::get('/branches', [GitHubController::class, 'getAllBranches']);
Route::get('/commits', [GitHubController::class, 'getAllCommits']);
Route::get('/branch', [GitHubController::class, 'getBranch']);
