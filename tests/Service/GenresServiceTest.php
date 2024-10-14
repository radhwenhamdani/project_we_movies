<?php

namespace App\Tests\Service;

use App\Service\GenresService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class GenresServiceTest extends KernelTestCase
{
    private $clientMock;
    private $genresService;

    protected function setUp(): void
    {
        $this->clientMock = $this->createMock(HttpClientInterface::class);
        $this->genresService = new GenresService($this->clientMock, 'fake_api_key');
    }

    public function testGetListGenres(): void
    {
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('toArray')
            ->willReturn([
                'genres' => [
                    ['id' => 28, 'name' => 'Action'],
                    ['id' => 35, 'name' => 'Comédie'],
                ]
            ]);
        $this->clientMock->method('request')
            ->willReturn($responseMock);
        $genres = $this->genresService->getListGenres();
        $this->assertIsArray($genres);
        $this->assertCount(2, $genres);
        $this->assertEquals('Action', $genres[0]['name']);
        $this->assertEquals('Comédie', $genres[1]['name']);
    }
}
