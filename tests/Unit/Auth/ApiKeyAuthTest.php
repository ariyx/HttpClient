<?php

declare(strict_types=1);

namespace Ariyx\HttpClient\Tests\Unit\Auth;

use Ariyx\HttpClient\Auth\ApiKeyAuth;
use Ariyx\HttpClient\Request;
use PHPUnit\Framework\TestCase;

/**
 * API Key Auth Test
 *
 * @package Ariyx\HttpClient\Tests\Unit\Auth
 * @author  Armin Malekzadeh <arixologist@gmail.com>
 * @version 2.0.0
 */
class ApiKeyAuthTest extends TestCase
{
    public function testCanCreateApiKeyAuthWithDefaultSettings(): void
    {
        $auth = new ApiKeyAuth('api-key-123');

        $this->assertEquals('api-key-123', $auth->getApiKey());
        $this->assertEquals('X-API-Key', $auth->getName());
        $this->assertEquals(ApiKeyAuth::LOCATION_HEADER, $auth->getLocation());
        $this->assertEquals('API Key', $auth->getType());
    }

    public function testCanCreateApiKeyAuthWithCustomSettings(): void
    {
        $auth = new ApiKeyAuth('api-key-123', 'Authorization', ApiKeyAuth::LOCATION_QUERY);

        $this->assertEquals('api-key-123', $auth->getApiKey());
        $this->assertEquals('Authorization', $auth->getName());
        $this->assertEquals(ApiKeyAuth::LOCATION_QUERY, $auth->getLocation());
    }

    public function testCanAuthenticateRequestWithHeader(): void
    {
        $auth = new ApiKeyAuth('api-key-123', 'X-API-Key', ApiKeyAuth::LOCATION_HEADER);
        $request = Request::get('https://example.com');

        $authenticatedRequest = $auth->authenticate($request);

        $this->assertSame($request, $authenticatedRequest);
        $this->assertEquals('api-key-123', $authenticatedRequest->getHeader('X-API-Key'));
    }

    public function testCanAuthenticateRequestWithQueryParam(): void
    {
        $auth = new ApiKeyAuth('api-key-123', 'api_key', ApiKeyAuth::LOCATION_QUERY);
        $request = Request::get('https://example.com');

        $authenticatedRequest = $auth->authenticate($request);

        $this->assertSame($request, $authenticatedRequest);
        $params = $authenticatedRequest->getQueryParams();
        $this->assertEquals('api-key-123', $params['api_key']);
    }

    public function testCanSetApiKey(): void
    {
        $auth = new ApiKeyAuth('old-key');
        $auth->setApiKey('new-key');

        $this->assertEquals('new-key', $auth->getApiKey());
    }

    public function testCanSetName(): void
    {
        $auth = new ApiKeyAuth('api-key-123');
        $auth->setName('Custom-Header');

        $this->assertEquals('Custom-Header', $auth->getName());
    }

    public function testCanSetLocation(): void
    {
        $auth = new ApiKeyAuth('api-key-123');
        $auth->setLocation(ApiKeyAuth::LOCATION_QUERY);

        $this->assertEquals(ApiKeyAuth::LOCATION_QUERY, $auth->getLocation());
    }

    public function testCanHandleEmptyApiKey(): void
    {
        $auth = new ApiKeyAuth('');
        $request = Request::get('https://example.com');

        $authenticatedRequest = $auth->authenticate($request);

        $this->assertEquals('', $authenticatedRequest->getHeader('X-API-Key'));
    }

    public function testCanHandleSpecialCharactersInApiKey(): void
    {
        $apiKey = 'sk-1234567890abcdef!@#$%^&*()';
        $auth = new ApiKeyAuth($apiKey);
        $request = Request::get('https://example.com');

        $authenticatedRequest = $auth->authenticate($request);

        $this->assertEquals($apiKey, $authenticatedRequest->getHeader('X-API-Key'));
    }
}
