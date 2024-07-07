<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClearRepositoriesCacheRequest;
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
use Illuminate\Support\Facades\Cache;

class GitHubController extends Controller
{
    function getRepositories(GetRepositoriesRequest $request): \Illuminate\Http\JsonResponse
    {
        $cacheKey = 'repositories_' . $request->user;
        $response = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($request) {
            return GitHubService::getAllRepositories($request->user);
        });

        return response()->json($response);
    }
    function clearRepositoriesCache(ClearRepositoriesCacheRequest $request)
    {
        $cacheKey = 'repositories_' . $request->user;

        if (Cache::has($cacheKey)) {
            Cache::forget($cacheKey);
        }

        return response()->json(["message" => "Cache excluído com sucesso."]);
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
        $commits = CommitView::where('repository_id', $repository->id)
            ->whereDateBetween('created_at_date', $request->since, $request->until)
            ->get();

        return CommitsToChartResourceCollection::make($commits)->since($request->since)->until($request->until);
    }
}
