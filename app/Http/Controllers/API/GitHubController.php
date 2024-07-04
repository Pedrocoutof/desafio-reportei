<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Commit;
use App\Models\Repository;
use App\Models\User;
use App\Repositories\RepositoryRepository;
use App\Services\GitHubService;
use Carbon\Carbon;
use DateTime;
use DateTimeZone;
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
        $response = $this->githubService->getAllRepositories($request->user);
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
        $user = User::where('nickname', '=', $request->user)->first();
        $repo = $this->githubService->getRepository($request->user, $request->repository);
        $repository = Repository::getRepository($user->id, $request->repository);

        if(!$repository){
            $repository = RepositoryRepository::create($repo->name, $user->id);
        }

        $desynchronizedCommits = $this->githubService->getAllCommits($request->user, $repo->name, $repository->last_synced);

        RepositoryRepository::addCommits($repository->id, $desynchronizedCommits);

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
