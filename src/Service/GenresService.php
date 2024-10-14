<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GenresService
{
    private HttpClientInterface $client;
    private string $apiKey;

    public function __construct(HttpClientInterface $client, string $apiKeyTheMovieDB)
    {
        $this->client = $client;
        $this->apiKey = $apiKeyTheMovieDB;
    }

    /**
     * @return array
     */
    public function getListGenres(): array
    {
        $response = $this->client->request('GET', 'https://api.themoviedb.org/3/genre/movie/list', [
            'query' => ['api_key' => $this->apiKey, 'language' => 'fr', 'page' => 1],
        ]);

        return $response->toArray()['genres'];
    }
}
