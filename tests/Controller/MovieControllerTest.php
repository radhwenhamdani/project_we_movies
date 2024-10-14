<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MovieControllerTest extends WebTestCase
{
    public function testIndexPage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');
        $this->assertResponseIsSuccessful();
    }

    public function testMovieDetails(): void
    {
        $client = static::createClient();
        $client->request('GET', '/movie/123');
        $this->assertResponseIsSuccessful();
        $response = $client->getResponse();
        $this->assertJson($response->getContent());
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('detailMovie', $data);
        $this->assertArrayHasKey('title', $data['detailMovie']);
        $this->assertArrayHasKey('link', $data['detailMovie']);
    }

    public function testMovieAutocomplete(): void
    {
        $client = static::createClient();
        $client->request('GET', '/movie/avatar/autocomplete');
        $this->assertResponseIsSuccessful();
        $response = $client->getResponse();
        $this->assertJson($response->getContent());
        $data = json_decode($response->getContent(), true);
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
    }
}
