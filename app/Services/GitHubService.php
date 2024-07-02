<?php

namespace App\Services;

use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;

class GitHubService
{
    private $client;
    private $baseUrl;
    private $token;

    public function __construct()
    {
        $this->baseUrl = 'https://api.github.com';
        $this->client = new Client();
        $this->token = env('GITHUB_TOKEN');
    }

    private function request($method, $url, $options = [])
    {
        $options['headers']['Authorization'] = "token {$this->token}";
        return $this->client->request($method, $url, $options);
    }

    function getAllRepositories(string $user) {
        $url = "{$this->baseUrl}/users/{$user}/repos";
        $response = $this->request('GET', $url, [
            'query' => [
                'per_page' => 30,
            ]
        ]);
        return json_decode($response->getBody()->getContents());
    }

    function getRepository(string $user, string $repository)
    {
        $url = "{$this->baseUrl}/repos/{$user}/{$repository}";
        $response = $this->request('GET', $url);
        return json_decode($response->getBody()->getContents());
    }

    function getAllBranches(string $user, string $repository) {
        $url = "{$this->baseUrl}/repos/{$user}/{$repository}/branches";
        $response = $this->request('GET', $url);
        return json_decode($response->getBody()->getContents());
    }

    function getBranch($user, $repository, $branch)
    {
        $url = "{$this->baseUrl}/repos/{$user}/{$repository}/branches/{$branch}";
        $response = $this->request('GET', $url);
        return json_decode($response->getBody()->getContents());
    }

    function getAllCommits($user, $repo, $since = "1970-01-01", $perPage = 30, )
    {
        $url = "{$this->baseUrl}/repos/{$user}/{$repo}/commits";
        $commits = [];
        $page = 1;


        do {
            $response = $this->request('GET', $url, [
                "query" => [
                    "per_page" => $perPage,
                    "page" => $page,
                    "since" => $this->convertToUTC($since)
                ]
            ]);

            $newCommits = json_decode($response->getBody()->getContents(), true);
            $commits = array_merge($commits, $newCommits);
            $page++;
        } while (count($newCommits) === $perPage);

        return $commits;
    }

    private function convertToUTC($date, $timezone = 'America/Sao_Paulo') {
        $datetime = new DateTime($date, new DateTimeZone($timezone));
        $datetime->setTimezone(new DateTimeZone('UTC'));
        return $datetime->format('Y-m-d\TH:i:s\Z');
    }
}
