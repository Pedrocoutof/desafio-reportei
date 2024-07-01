<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class GitHubController extends Controller
{
    private string $urlbase = 'https://api.github.com/';
    private Client $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    function getRepositories(Request $request) {
        $client = new Client();

        if($request->user)
            $response = $client->get($this->urlbase . "users/" . $request->user . "/repos");
        else {
            $response = $client->get($this->urlbase . "users/Pedrocoutof/repos");
        }
        $repositories = json_decode($response->getBody()->getContents());

        return response()->json($repositories);
    }

}
