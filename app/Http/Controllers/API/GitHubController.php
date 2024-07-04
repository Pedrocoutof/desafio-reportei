<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommitsToChartResource;
use App\Http\Resources\CommitsToChartResourceCollection;
use App\Models\CommitView;
use App\Models\Repository;
use App\Models\User;
use App\Repositories\RepositoryRepository;
use App\Services\GitHubService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class GitHubController extends Controller
{
    function getRepositories(Request $request): \Illuminate\Http\JsonResponse
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

    function generateChart(string $user, string $repository, string $since = null, string $until = null): CommitsToChartResourceCollection
    {
        $user = User::where('nickname', $user)->first();
        $repository = RepositoryRepository::getRepository($user->id, $repository);
        $repository = $repository ?? RepositoryRepository::create($repository->name, $user->id); // Caso não tenha encontrado um repositório, cria um

        RepositoryRepository::syncCommits($repository); // Sincroniza os commits
        $commits = CommitView::where('repository_id', $repository->id)->get();

        return CommitsToChartResourceCollection::make($commits)->since($since)->until($until);
    }
}
