<?php

namespace App\Services;

use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;

class GitHubService
{
    private static $client;
    private static $baseUrl;
    private static $token;

    /**
     * @return void
     */
    public static function init(): void
    {
        self::$baseUrl = 'https://api.github.com';
        self::$client = new Client();
        self::$token = env('GITHUB_TOKEN');
    }

    /**
     * @param $method
     * @param $url
     * @param array $options
     * @return mixed
     */
    private static function request($method, $url, array $options = []): mixed
    {
        $options['headers']['Authorization'] = "Bearer " . self::$token;
        return self::$client->request($method, $url, $options);
    }

    /**
     * @param string $user
     * @param int $perPage
     * @param int $page
     * @return array
     */
    public static function getAllRepositories(string $user, int $perPage = 30, int $page = 1): array
    {
        self::init();
        $url = self::$baseUrl . "/users/{$user}/repos";
        $repositories = [];

        do {
            $response = self::request('GET', $url, [
                "query" => [
                    "per_page" => $perPage,
                    "page" => $page,
                ]
            ]);
            $newRepositories = json_decode($response->getBody()->getContents(), true);
            $repositories = array_merge($repositories, $newRepositories);
            $page++;
        } while (count($newRepositories) === $perPage);

        return array_map(function ($repo) {
                return [
                    'name' => $repo['name'],
                    'id' => $repo['id'],
                    'owner' => $repo['owner']['login'],
                ];
        }, $repositories);
    }

    /**
     * @param string $user
     * @param string $repository
     * @return mixed
     */
    public static function getRepository(string $user, string $repository): mixed
    {
        self::init();
        $url = self::$baseUrl . "/repos/{$user}/{$repository}";
        $response = self::request('GET', $url);
        return json_decode($response->getBody()->getContents());
    }

    /**
     * @param $user
     * @param $repo
     * @param $since
     * @param int $perPage
     * @return array
     * @throws \Exception
     */
    public static function getAllCommits($user, $repo, $since, int $perPage = 30): array
    {
        self::init();
        $url = self::$baseUrl . "/repos/{$user}/{$repo}/commits";
        $commits = [];
        $page = 1;

        $since = $since ?? '1970-01-01';

        do {
            $response = self::request('GET', $url, [
                "query" => [
                    "per_page" => $perPage,
                    "page" => $page,
                    "since" => self::convertToUTC($since)
                ]
            ]);
            $newCommits = json_decode($response->getBody()->getContents(), true);
            $commits = array_merge($commits, $newCommits);
            $page++;
        } while (count($newCommits) === $perPage);
        return $commits;
    }

    /**
     * @param $date
     * @param string $timezone = 'America/Sao_Paulo'
     * @return string
     * @throws \Exception
     */
    private static function convertToUTC($date, string $timezone = 'America/Sao_Paulo'): string
    {
        $datetime = new DateTime($date, new DateTimeZone($timezone));
        $datetime->setTimezone(new DateTimeZone('UTC'));
        return $datetime->format('Y-m-d\TH:i:s\Z');
    }
}
