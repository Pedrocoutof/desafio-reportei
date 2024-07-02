<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Commit;
use App\Models\Repository;
use App\Models\User;
use App\Services\GitHubService;
use Carbon\Carbon;
use GuzzleHttp\Client;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        // Pega o usuário do banco
        $user = User::where('nickname', '=', $request->user)->first();

        $repository = Repository::getRepository($user->id, $request->repository);

        // Caso tenha um repositório no banco
        if($repository !== null) {
            // verificar ultima atualização
            $desynchronizedCommits = $this->githubService->getAllCommits($request->user, $request->repository, $repository->last_synced);

            DB::transaction(function () use ($request, $repository, $user, $desynchronizedCommits) {
                $bulkCommitData = array_map(function ($commit) use ($repository) {
                    $commitDate = Carbon::parse($commit['commit']['author']['date'])->toDateTimeString();
                    return [
                        'sha'=> $commit['sha'],
                        'repository_id'=> $repository->id,
                        'author_name'=> $commit['committer']['login'],
                        'created_at' => $commitDate,
                    ];
                }, $desynchronizedCommits);

                $repository->update(['last_synced' =>  Carbon::now('GMT-3')->toDateTimeString()]);

                // insere os commits
                Commit::insert($bulkCommitData);
            });

        }
        else {
            // Pega commits da github api
            $allCommits = $this->githubService->getAllCommits($request->user, $request->repository);

            DB::transaction(function () use ($request, $user, $allCommits) {
                // Cria um repositorio
                $createdRepository = new Repository;
                $createdRepository->name = $request->repository;
                $createdRepository->owner = $user->id;
                $createdRepository->last_synced = Carbon::now('GMT-3')->toDateTimeString();

                $createdRepository->save();

                $bulkCommitData = array_map(function ($commit) use ($createdRepository) {
                $commitDate = Carbon::parse($commit['commit']['author']['date'])->toDateTimeString();

                    return [
                        'sha'=> $commit['sha'],
                        'repository_id'=> $createdRepository->id,
                        'author_name'=> $commit['committer']['login'],
                        'created_at' => $commitDate,
                    ];

                }, $allCommits);

                // insere os commits
                Commit::insert($bulkCommitData);
            });

        }


        dd("Teste");


        return response()->json([
            "commits" => $response,
            "total_commits" => count($response)
        ]);
    }

}
