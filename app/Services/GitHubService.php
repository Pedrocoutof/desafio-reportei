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

    public static function init(): void
    {
        self::$baseUrl = 'https://api.github.com';
        self::$client = new Client();
        self::$token = env('GITHUB_TOKEN');
    }

    private static function request($method, $url, $options = [])
    {
        $options['headers']['Authorization'] = "Bearer " . self::$token;
        return self::$client->request($method, $url, $options);
    }

    public static function getAllRepositories(string $user)
    {
        self::init();
        $url = self::$baseUrl . "/users/{$user}/repos";
        $response = self::request('GET', $url, [
            'query' => [
                'per_page' => 30,
            ]
        ]);
        return json_decode($response->getBody()->getContents());
    }

    public static function getRepository(string $user, string $repository)
    {
        self::init();
        $url = self::$baseUrl . "/repos/{$user}/{$repository}";
        $response = self::request('GET', $url);
        return json_decode($response->getBody()->getContents());
    }

    public static function getAllBranches(string $user, string $repository)
    {
        self::init();
        $url = self::$baseUrl . "/repos/{$user}/{$repository}/branches";
        $response = self::request('GET', $url);
        return json_decode($response->getBody()->getContents());
    }

    public static function getBranch($user, $repository, $branch)
    {
        self::init();
        $url = self::$baseUrl . "/repos/{$user}/{$repository}/branches/{$branch}";
        $response = self::request('GET', $url);
        return json_decode($response->getBody()->getContents());
    }

    public static function getAllCommits($user, $repo, $since, $perPage = 30)
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

    private static function convertToUTC($date, $timezone = 'America/Sao_Paulo')
    {
        $datetime = new DateTime($date, new DateTimeZone($timezone));
        $datetime->setTimezone(new DateTimeZone('UTC'));
        return $datetime->format('Y-m-d\TH:i:s\Z');
    }
}
