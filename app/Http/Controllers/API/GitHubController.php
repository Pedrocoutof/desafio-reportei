<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\GenerateChartDataRequest;
use App\Http\Requests\GetRepositoriesRequest;
use App\Http\Resources\CommitsToChartResource;
use App\Http\Resources\CommitsToChartResourceCollection;
use App\Models\CommitView;
use App\Models\User;
use App\Repositories\RepositoryRepository;
use App\Services\GitHubService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class GitHubController extends Controller
{
    function getRepositories(GetRepositoriesRequest $request): \Illuminate\Http\JsonResponse
    {
        $response = GitHubService::getAllRepositories($request->user);
        return response()->json($response);
    }

    function getRepository(Request $request): \Illuminate\Http\JsonResponse
    {
        $response = GitHubService::getRepository($request->user, $request->repository);
        return response()->json($response);
    }

    function getAllBranches(Request $request): \Illuminate\Http\JsonResponse
    {
        $response = GitHubService::getAllBranches($request->user, $request->repository);
        return response()->json($response);
    }

    function getBranch(Request $request): \Illuminate\Http\JsonResponse
    {
        $response = GitHubService::getBranch($request->user, $request->repository, $request->branch);
        return response()->json($response);
    }

    function generateChart(GenerateChartDataRequest $request)
    {
        $user = User::where('nickname', $request->user)->first();

        $repository = RepositoryRepository::getRepository($user->id, $request->repository)
                      ?? RepositoryRepository::create($request->repository, $user->id);

        RepositoryRepository::syncCommits($repository);
        $commits = CommitView::where('repository_id', $repository->id)->get();

        $since = Carbon::now()->subDays(90);
        $until = Carbon::now();

        return CommitsToChartResourceCollection::make($commits)->since($since)->until($until);
    }
}
