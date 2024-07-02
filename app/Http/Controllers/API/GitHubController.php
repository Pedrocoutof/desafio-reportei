<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\GitHubService;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class GitHubController extends Controller
{
    protected GitHubService $githubService;

    public function __construct()
    {
        $this->githubService = new GithubService();
    }

    function getRepositories(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user;

        if(!$user) {
            $user = "Pedrocoutof";
        }

        $response = $this->githubService->getAllRepositories($user);
        return response()->json($response);
    }
    function getRepository(Request $request): \Illuminate\Http\JsonResponse
    {
        $response = $this->githubService->getRepository($request->user, $request->repository);
        return response()->json($response);
    }
    function getAllBranches(Request $request): \Illuminate\Http\JsonResponse
    {
        $response = $this->githubService->getAllBranches($request->user, $request->repository);
        return response()->json($response);
    }
    function getBranch(Request $request): \Illuminate\Http\JsonResponse
    {
        $response = $this->githubService->getBranch($request->user, $request->repository, $request->branch);
        return response()->json($response);
    }
    function getAllCommits(Request $request): \Illuminate\Http\JsonResponse
    {
        $response = $this->githubService->getAllCommits($request->user, $request->repository);
        return response()->json([
            "commits" => $response,
            "total_commits" => count($response)
        ]);
    }

}
