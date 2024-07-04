<?php

namespace App\Repositories;
use App\Models\Commit;
use App\Models\Repository;
use App\Services\GitHubService;
use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RepositoryRepository
{
    static public function findByUserIdAndRepositoryName(int $userId, string $repositoryName)
    {
        return Repository::where('owner', $userId)->where('name', $repositoryName)->first();
    }

    static public function findUserRepositories(int $userId)
    {
        return Repository::where('owner', $userId)->get();
    }

    static public function create($name, $owner): Repository|null
    {
        $createdRepository = new Repository;
        $createdRepository->name = $name;
        $createdRepository->owner = $owner;

        return $createdRepository->save() ? $createdRepository : null;
    }

    static public function addCommits(int $repositoryId, array $commits)
    {
        $now = Carbon::now('GMT-3')->toDateTimeString();
        $repository = Repository::where('id', $repositoryId)->first();

        DB::transaction(function () use ($repository, $commits, $now) {
            $bulkCommitData = array_map(function ($commit) use ($repository) {
                $commitDate = self::convertToTimezone($commit['commit']['author']['date']);

                return [
                    'sha'=> $commit['sha'],
                    'repository_id'=> $repository->id,
                    'author_name'=> $commit['committer']['login'],
                    'created_at' => $commitDate,
                ];
            }, $commits);

            $repository->update(['last_synced' =>  $now]);
            Commit::insert($bulkCommitData);
        });

    }

    private static function convertToTimezone($date) {
        $datetime = new DateTime($date, new DateTimeZone('UTC'));
        $datetime->setTimezone(new DateTimeZone('America/Sao_Paulo'));
        return $datetime->format('Y-m-d H:i:s');
    }

    static public function syncCommits(Repository $repository): \Illuminate\Http\JsonResponse
    {
        $owner = $repository->owner()->first();
        $desynchronizedCommits = GitHubService::getAllCommits($owner->nickname, $repository->name, $repository->last_synced);
        RepositoryRepository::addCommits($repository->id, $desynchronizedCommits);
        return response()->json([], 200);
    }

    static function getRepository($userId, $repository, $relationships = []): \Illuminate\Database\Eloquent\Builder|Model|null
    {
        $relationships[] = 'owner';
        $repository = Repository::with($relationships)
            ->where('owner', '=', $userId)
            ->where('name', '=', $repository)
            ->first();

        return $repository ?? null;
    }
}
