<?php

namespace App\Controller;

use App\Service\GenresService;
use App\Service\MoviesService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MovieController extends AbstractController
{
    private GenresService $genresService;
    private MoviesService $moviesService;

    public function __construct(GenresService $genresService, MoviesService $moviesService)
    {
        $this->genresService = $genresService;
        $this->moviesService = $moviesService;
    }

    #[Route('/', name: 'home', methods: ["GET"])]
    public function index(Request $request): Response
    {
        $allGenres = $this->genresService->getListGenres();
        $page = $request->query->getInt('page', 1);
        $genres = $request->query->get('genres', '');
        $search = $request->query->get('search', '');
        $popularMovies = $this->moviesService->getMovies($page, $genres, $search);
        $bestMovie = $this->moviesService->getBestMovie();

        return $this->render('index.html.twig', [
            'genres' => $allGenres,
            'movies' => $popularMovies,
            'bestMovie' => $bestMovie
        ]);
    }

    #[Route('/movie/{id}', name: 'movie_details', methods: ["GET"], options: ['expose' => true])]
    public function movieDetails(int $id): Response
    {
        $response = $this->moviesService->getBestMovie($id);

        return new JsonResponse(
            [
                'detailMovie' => $response
            ]
        );
    }

    #[Route('/movie/{query}/autocomplete', name: 'movie_autocomplete', methods: ["GET"], options: ['expose' => true])]
    public function moviesAutocomplete(string $query): Response
    {
        $response = $this->moviesService->getMoviesAutocomplete($query);

        return new JsonResponse($response);
    }
}
