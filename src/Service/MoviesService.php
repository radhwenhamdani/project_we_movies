<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\ArrayAdapter;
use Symfony\Contracts\HttpClient\ResponseInterface;

class MoviesService
{
    private HttpClientInterface $client;
    private string $apiKey;

    public function __construct(HttpClientInterface $client, string $apiKeyTheMovieDB)
    {
        $this->client = $client;
        $this->apiKey = $apiKeyTheMovieDB;
    }

    /**
     * @param int|null $id
     * @return array
     */
    public function getBestMovie(?int $id = null): array
    {
        $topMovie = null;

        if ($id === null) {
            $response = $this->client->request('GET', 'https://api.themoviedb.org/3/tv/top_rated', [
                'query' => ['api_key' => $this->apiKey, 'language' => 'fr'],
            ]);
            $topMovie = $response->toArray()['results'][0];
            $id = $topMovie['id'];
        }

        $detailMovie = $this->client->request('GET', 'https://api.themoviedb.org/3/movie/' . $id . '/videos', [
            'query' => ['api_key' => $this->apiKey],
        ])->toArray()['results'][0];

        return [
            'title' => $topMovie ? $topMovie['original_name'] : '',
            'description' => $topMovie ? $topMovie['overview'] : '',
            'image' => $topMovie ? $topMovie['backdrop_path'] : '',
            'poster' => $topMovie ? $topMovie['poster_path'] : '',
            'link' => $detailMovie['key'],
            'name' => $detailMovie['name'],
            'type' => $detailMovie['site']
        ];
    }

    /**
     * @param int $page
     * @param string $genres
     * @param string $query
     * @param int $maxPerPage
     * @return Pagerfanta
     */
    public function getMovies(int $page = 1, string $genres = '', string $query = '', int $maxPerPage = 5): Pagerfanta
    {
        if ($query !== '') {
            $response = $this->getMoviesBySearch($page, $query);
        } else {
            $response = $this->getMoviesByGenre($page, $genres);
        }

        $moviesData = $response->toArray();
        $adapter = new ArrayAdapter($moviesData['results']);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage($maxPerPage);
        $pagerfanta->setCurrentPage($page);

        return $pagerfanta;
    }

    /**
     * @param int $page
     * @param string $genres
     * @return ResponseInterface
     */
    private function getMoviesByGenre(int $page, string $genres): ResponseInterface
    {
        return $this->client->request('GET', 'https://api.themoviedb.org/3/discover/movie', [
            'query' => [
                'api_key' => $this->apiKey,
                'with_genres' => $genres,
                'language' => 'fr',
                'page' => $page
            ],
        ]);
    }

    /**
     * @param int $page
     * @param string $query
     * @return ResponseInterface
     */
    private function getMoviesBySearch(int $page, string $query): ResponseInterface
    {
        return $this->client->request('GET', 'https://api.themoviedb.org/3/search/movie', [
            'query' => [
                'api_key' => $this->apiKey,
                'query' => $query,
                'language' => 'fr',
                'page' => $page,
            ],
        ]);
    }

    /**
     * @param string $query
     * @return array
     */
    public function getMoviesAutocomplete(string $query): array
    {
        return $this->client->request('GET', 'https://api.themoviedb.org/3/search/movie', [
            'query' => [
                'api_key' => $this->apiKey,
                'language' => 'fr',
                'query' => $query
            ],
        ])->toArray();
    }
}
