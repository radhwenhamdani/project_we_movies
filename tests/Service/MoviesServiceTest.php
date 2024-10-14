<?php

namespace App\Tests\Service;

use App\Service\MoviesService;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class MoviesServiceTest extends KernelTestCase
{
    private $clientMock;
    private $moviesService;

    protected function setUp(): void
    {
        $this->clientMock = $this->createMock(HttpClientInterface::class);
        $this->moviesService = new MoviesService($this->clientMock, 'fake_api_key');
    }

    public function testGetBestMovie(): void
    {
        $topMovieResponseMock = $this->createMock(ResponseInterface::class);
        $topMovieResponseMock->method('toArray')->willReturn([
            'results' => [
                [
                    'id' => 123,
                    'original_name' => 'Top Movie',
                    'overview' => 'Best movie ever',
                    'backdrop_path' => '/top-movie-backdrop.jpg',
                    'poster_path' => '/top-movie-poster.jpg'
                ]
            ]
        ]);
        $movieDetailsResponseMock = $this->createMock(ResponseInterface::class);
        $movieDetailsResponseMock->method('toArray')->willReturn([
            'results' => [
                [
                    'key' => 'movie_trailer_key',
                    'name' => 'Trailer',
                    'site' => 'YouTube'
                ]
            ]
        ]);
        $this->clientMock->expects($this->exactly(2))
            ->method('request')
            ->willReturnOnConsecutiveCalls($topMovieResponseMock, $movieDetailsResponseMock);
        $result = $this->moviesService->getBestMovie();
        $this->assertIsArray($result);
        $this->assertEquals('Top Movie', $result['title']);
        $this->assertEquals('Best movie ever', $result['description']);
        $this->assertEquals('/top-movie-backdrop.jpg', $result['image']);
        $this->assertEquals('/top-movie-poster.jpg', $result['poster']);
        $this->assertEquals('movie_trailer_key', $result['link']);
        $this->assertEquals('Trailer', $result['name']);
        $this->assertEquals('YouTube', $result['type']);
    }

    public function testGetMoviesBySearch(): void
    {
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('toArray')->willReturn([
            'results' => [
                ['title' => 'Movie 1', 'id' => 1],
                ['title' => 'Movie 2', 'id' => 2],
            ]
        ]);

        $this->clientMock->method('request')->willReturn($responseMock);
        $pagerfanta = $this->moviesService->getMovies(1, '', 'action', 2);
        $this->assertInstanceOf(Pagerfanta::class, $pagerfanta);
        $this->assertCount(2, $pagerfanta->getCurrentPageResults());
        $movies = $pagerfanta->getCurrentPageResults();
        $this->assertEquals('Movie 1', $movies[0]['title']);
        $this->assertEquals('Movie 2', $movies[1]['title']);
    }

    public function testGetMoviesByGenre(): void
    {
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('toArray')->willReturn([
            'results' => [
                ['title' => 'Genre Movie 1', 'id' => 3],
                ['title' => 'Genre Movie 2', 'id' => 4],
            ]
        ]);
        $this->clientMock->method('request')->willReturn($responseMock);
        $pagerfanta = $this->moviesService->getMovies(1, 'comedy', '', 2);
        $this->assertInstanceOf(Pagerfanta::class, $pagerfanta);
        $this->assertCount(2, $pagerfanta->getCurrentPageResults());
        $movies = $pagerfanta->getCurrentPageResults();
        $this->assertEquals('Genre Movie 1', $movies[0]['title']);
        $this->assertEquals('Genre Movie 2', $movies[1]['title']);
    }

    public function testGetMoviesAutocomplete(): void
    {
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('toArray')->willReturn([
            'results' => [
                ['title' => 'Movie 1', 'id' => 1],
                ['title' => 'Movie 2', 'id' => 2],
            ]
        ]);
        $this->clientMock
            ->method('request')
            ->with(
                'GET',
                'https://api.themoviedb.org/3/search/movie',
                $this->callback(function($options) {
                    return isset($options['query']['api_key']) && $options['query']['query'] === 'test';
                })
            )
            ->willReturn($responseMock);
        $results = $this->moviesService->getMoviesAutocomplete('test');
        $this->assertCount(2, $results['results']);
        $this->assertEquals('Movie 1', $results['results'][0]['title']);
        $this->assertEquals(1, $results['results'][0]['id']);
        $this->assertEquals('Movie 2', $results['results'][1]['title']);
    }
}
