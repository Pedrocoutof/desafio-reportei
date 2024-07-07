<?php

namespace App\Repositories;
use App\Models\Commit;
use App\Models\Repository;
use App\Services\GitHubService;
use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class RepositoryRepository
{
    /**
     * Função criada para abstrair create de um repositório.
     *
     * @param $name
     * @param $owner
     * @return Repository|null
     */
    static public function create($name, $owner): Repository|null
    {
        $createdRepository = new Repository;
        $createdRepository->name = $name;
        $createdRepository->owner = $owner;

        return $createdRepository->save() ? $createdRepository : null;
    }

    /**
     * Função utilizada para inserir commits de um determinado repositório.
     *
     * @param int $repositoryId
     * @param array $commits
     * @return void
     */
    static public function addCommits(int $repositoryId, array $commits): void
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

    /**
     *
     * Função utilizada para sincronizar os commits de um determinado repositório, conferindo a data de sua ultima sincronização.
     *
     * @param Repository $repository
     * @return JsonResponse
     * @throws \Exception
     */
    static public function syncCommits(Repository $repository): \Illuminate\Http\JsonResponse
    {
        $owner = $repository->owner()->first();
        $desynchronizedCommits = GitHubService::getAllCommits($owner->nickname, $repository->name, $repository->last_synced);
        RepositoryRepository::addCommits($repository->id, $desynchronizedCommits);
        return response()->json([], 200);
    }

    /**
     * Função utilizada para obter um repositório armazenado no banco
     *
     * @param $userId
     * @param $repository
     * @param $relationships
     * @return Builder|Model|null
     */
    static function getRepository($userId, $repository, $relationships = []): \Illuminate\Database\Eloquent\Builder|Model|null
    {
        $relationships[] = 'owner';
        $repository = Repository::with($relationships)
            ->where('owner', '=', $userId)
            ->where('name', '=', $repository)
            ->first();

        return $repository ?? null;
    }

    /**
     * @param $date
     * @return string
     * @throws \Exception
     */
    private static function convertToTimezone($date): string
    {
        $datetime = new DateTime($date, new DateTimeZone('UTC'));
        $datetime->setTimezone(new DateTimeZone('America/Sao_Paulo'));
        return $datetime->format('Y-m-d H:i:s');
    }
}
