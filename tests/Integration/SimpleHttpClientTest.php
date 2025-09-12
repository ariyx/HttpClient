<?php

declare(strict_types=1);

namespace Ariyx\HttpClient\Tests\Integration;

use Ariyx\HttpClient\HttpClient;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * Simple HTTP Client Integration Test
 *
 * @package Ariyx\HttpClient\Tests\Integration
 * @author  Armin Malekzadeh <arixologist@gmail.com>
 * @version 2.0.0
 */
class SimpleHttpClientTest extends TestCase
{
    private HttpClient $client;

    protected function setUp(): void
    {
        $this->client = new HttpClient([], new NullLogger());
    }

    public function testCanSendSimpleGetRequest(): void
    {
        $response = $this->client->get('https://jsonplaceholder.typicode.com/posts/1');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($response->isSuccessful());

        $json = $response->json();
        $this->assertIsArray($json);
        $this->assertArrayHasKey('id', $json);
    }

    public function testCanSendRequestWithHeaders(): void
    {
        $response = $this->client->get('https://jsonplaceholder.typicode.com/posts/1', [
            'headers' => [
                'User-Agent' => 'Test-Agent'
            ]
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($response->isSuccessful());
    }

    public function testCanSendPostRequest(): void
    {
        $data = ['title' => 'Test Post', 'body' => 'Test content', 'userId' => 1];
        $response = $this->client->post('https://jsonplaceholder.typicode.com/posts', [
            'body' => json_encode($data),
            'headers' => ['Content-Type' => 'application/json']
        ]);

        $this->assertEquals(201, $response->getStatusCode());

        $json = $response->json();
        $this->assertIsArray($json);
        $this->assertArrayHasKey('id', $json);
    }

    public function testCanSendAsyncRequests(): void
    {
        $requests = [
            \Ariyx\HttpClient\Request::get('https://jsonplaceholder.typicode.com/posts/1'),
            \Ariyx\HttpClient\Request::get('https://jsonplaceholder.typicode.com/posts/2'),
            \Ariyx\HttpClient\Request::get('https://jsonplaceholder.typicode.com/posts/3'),
        ];

        $responses = $this->client->sendAsync($requests);

        $this->assertCount(3, $responses);

        foreach ($responses as $response) {
            $this->assertEquals(200, $response->getStatusCode());
            $this->assertTrue($response->isSuccessful());
        }
    }
}
