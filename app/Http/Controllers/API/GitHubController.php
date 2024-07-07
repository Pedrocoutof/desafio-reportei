<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClearRepositoriesCacheRequest;
use App\Http\Requests\GenerateChartDataRequest;
use App\Http\Requests\GetRepositoriesRequest;
use App\Http\Resources\CommitsToChartResourceCollection;
use App\Models\CommitView;
use App\Models\User;
use App\Repositories\RepositoryRepository;
use App\Services\GitHubService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class GitHubController extends Controller
{
    /**
     * Busca os repositórios do usuário diretamente da API do GitHub e os armazena em cache por 10 minutos.
     *
     * Esta função recebe um `GetRepositoriesRequest` que contém o campo:
     * - `user`: o nome do usuário do GitHub.
     *
     * @param GetRepositoriesRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    function getRepositories(GetRepositoriesRequest $request): JsonResponse
    {
        $cacheKey = 'repositories_' . $request->user;
        $response = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($request) {
            return GitHubService::getAllRepositories($request->user);
        });

        return response()->json($response);
    }

    /**
     * Limpa o cache dos repositórios de um usuário.
     *
     * Esta função recebe um `ClearRepositoriesCacheRequest` que contém o campo:
     * - `user`: o nome do usuário do GitHub cujo cache de repositórios será limpo.
     *
     * @param ClearRepositoriesCacheRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    function clearRepositoriesCache(ClearRepositoriesCacheRequest $request): JsonResponse
    {
        $cacheKey = 'repositories_' . $request->user;

        if (Cache::has($cacheKey)) {
            Cache::forget($cacheKey);
        }

        return response()->json(["message" => "Cache excluído com sucesso."]);
    }

    /**
     * Atualiza os commits do repositório, se necessário, e retorna um objeto formatado para o front-end.
     *
     * Esta função recebe um `GenerateChartDataRequest` que contém os campos:
     * - `user`: o nome do usuário do GitHub.
     * - `repository`: o nome do repositório do GitHub.
     * - `since`: a data inicial para os commits (padrão: 90 dias atrás).
     * - `until`: a data final para os commits (padrão: data atual).
     *
     * @param GenerateChartDataRequest $request
     * @return CommitsToChartResourceCollection
     * @throws \Exception
     */
    function generateChart(GenerateChartDataRequest $request): CommitsToChartResourceCollection
    {
        $user = User::whereNickname($request->user)->first();

        $repository = RepositoryRepository::getRepository($user->id, $request->repository)
                      ?? RepositoryRepository::create($request->repository, $user->id);

        RepositoryRepository::syncCommits($repository);
        $commits = CommitView::where('repository_id', $repository->id)
            ->whereBetween('created_at_date', [$request->since, $request->until])
            ->get();

        return CommitsToChartResourceCollection::make($commits)->since($request->since)->until($request->until);
    }
}
