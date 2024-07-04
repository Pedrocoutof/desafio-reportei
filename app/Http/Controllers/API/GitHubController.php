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

    function syncCommits($user, $repository): \Illuminate\Http\JsonResponse
    {
        $user = User::where('nickname', '=', $user)->first();
        $repo = GitHubService::getRepository($user->nickname, $repository);
        $repository = Repository::getRepository($user->id, $repository);

        if (!$repository) {
            $repository = RepositoryRepository::create($repo->name, $user->id);
        }

        $desynchronizedCommits = GitHubService::getAllCommits($user->nickname, $repo->name, $repository->last_synced);

        RepositoryRepository::addCommits($repository->id, $desynchronizedCommits);

        return response()->json([], 200);
    }

    function generateChart(string $user, string $repository, string $since = null, string $until = null): \Illuminate\Http\JsonResponse
    {
        $this->syncCommits($user, $repository);
        
        $user = User::where('nickname', '=', $user)->first();
        $repository = Repository::getCommitsGrouped($user->id, $repository);

        $commits = CommitView::where('repository_id', $repository->id)->get();

        $return = CommitsToChartResourceCollection::make($commits)->since($since)->until($until);


        //$return = $this->transformCommits($repository->commits, $since, $until);
        return response()->json($return);

        $data = $this->transformCommits($repository->commits);

        return response()->json($data);
    }
    private function transformCommits($commits, $since, $until): array
    {
        $datasets = [];

        foreach ($commits as $commit) {
            $author = $commit->author_name;
            if (!isset($datasets[$author])) {
                $datasets[$author] = [];
            }
        }

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
