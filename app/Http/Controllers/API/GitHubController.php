<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Commit;
use App\Models\Repository;
use App\Models\User;
use App\Services\GitHubService;
use Carbon\Carbon;
use DateTime;
use DateTimeZone;
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
                    //$commitDate = Carbon::parse($commit['commit']['author']['date'])->toDateTimeString();
                    $commitDate = $this->convertToTimezone($commit['commit']['author']['date'])->toDateTimeString();

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
                //$commitDate = Carbon::parse($commit['commit']['author']['date'])->toDateTimeString();
                    $commitDate = $this->convertToTimezone($commit['commit']['author']['date'], 'America/Sao_Paulo', 'Y-m-d H:i:s');

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
        return response()->json([],200);
    }

    function generateChart(Request $request, $since = null, $until = null): \Illuminate\Http\JsonResponse{
        $this->getAllCommits($request);

        $user = User::where('nickname', '=', $request->user)->first();
        $repository = Repository::getCommitsGrouped($user->id, $request->get('repository'));

        // Geração das labels
        $now = Carbon::now('GMT-3');

        $since = $since ? Carbon::createFromFormat('Y-m-d', $since) : $now->copy()->subDays(90);
        $until = $until ? Carbon::createFromFormat('Y-m-d', $until) : $now->copy();

        $label = [];
        $currentDate = $since->copy();
        while ($currentDate <= $until) {
            $label[] = $currentDate->toDateString();
            $currentDate->addDay();
        }
        $data = $this->transformCommits($repository->commits, $since, $until);

        return response()->json($data);
    }

    private function convertToTimezone($date, $timezone = 'America/Sao_Paulo', $format='Y-m-d\TH:i:sP') {
        $datetime = new DateTime($date, new DateTimeZone('UTC'));
        $datetime->setTimezone(new DateTimeZone($timezone));
        return $datetime->format($format);
    }

    function transformCommits($commits, $since, $until) {
        $datasets = [];

        // Inicializar ['author_name']
        foreach ($commits as $commit) {
            $author = $commit->author_name;
            if (!isset($datasets[$author])) {
                $datasets[$author] = [];
            }
        }

        // Preenche commits por autor para cada dia no intervalo
        $currentDate = clone $since;
        while ($currentDate <= $until) {
            $dateStr = $currentDate->format('Y-m-d');
            foreach ($datasets as $author => &$data) {
                // Procurar commits para o autor e data atual
                $found = false;
                foreach ($commits as $commit) {
                    if ($commit->author_name === $author && $commit->created_at_date === $dateStr) {
                        $data[] = $commit->number_commits;
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $data[] = 0;
                }
            }
            $currentDate->addDay();
        }

        $chartDatasets = [];
        foreach ($datasets as $author => $data) {
            $chartDatasets[] = [
                'label' => $author,
                'data' => $data,
                'fill' => true,
                'backgroundColor' => 'rgba(59, 130, 246, 0.08)',
                'borderColor' => 'rgb(99, 102, 241)',
                'borderWidth' => 2,
                'tension' => 0.15,
                'pointRadius' => 0,
                'pointHoverRadius' => 3,
                'pointBackgroundColor' => 'rgb(99, 102, 241)',
            ];
        }

        return $chartDatasets;
    }
}
