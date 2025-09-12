<?php

declare(strict_types=1);

namespace Ariyx\HttpClient\Tests\Unit;

use Ariyx\HttpClient\Auth\BasicAuth;
use Ariyx\HttpClient\Request;
use PHPUnit\Framework\TestCase;

/**
 * Request Test
 *
 * @package Ariyx\HttpClient\Tests\Unit
 * @author  Armin Malekzadeh <arixologist@gmail.com>
 * @version 2.0.0
 */
class RequestTest extends TestCase
{
    public function testCanCreateGetRequest(): void
    {
        $request = Request::get('https://example.com');

        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('https://example.com', $request->getUrl());
    }

    public function testCanCreatePostRequest(): void
    {
        $request = Request::post('https://example.com');

        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('https://example.com', $request->getUrl());
    }

    public function testCanCreatePutRequest(): void
    {
        $request = Request::put('https://example.com');

        $this->assertEquals('PUT', $request->getMethod());
        $this->assertEquals('https://example.com', $request->getUrl());
    }

    public function testCanCreatePatchRequest(): void
    {
        $request = Request::patch('https://example.com');

        $this->assertEquals('PATCH', $request->getMethod());
        $this->assertEquals('https://example.com', $request->getUrl());
    }

    public function testCanCreateDeleteRequest(): void
    {
        $request = Request::delete('https://example.com');

        $this->assertEquals('DELETE', $request->getMethod());
        $this->assertEquals('https://example.com', $request->getUrl());
    }

    public function testCanCreateHeadRequest(): void
    {
        $request = Request::head('https://example.com');

        $this->assertEquals('HEAD', $request->getMethod());
        $this->assertEquals('https://example.com', $request->getUrl());
    }

    public function testCanCreateOptionsRequest(): void
    {
        $request = Request::options('https://example.com');

        $this->assertEquals('OPTIONS', $request->getMethod());
        $this->assertEquals('https://example.com', $request->getUrl());
    }

    public function testCanSetMethod(): void
    {
        $request = new Request();
        $request->setMethod('POST');

        $this->assertEquals('POST', $request->getMethod());
    }

    public function testCanSetUrl(): void
    {
        $request = new Request();
        $request->setUrl('https://example.com');

        $this->assertEquals('https://example.com', $request->getUrl());
    }

    public function testCanAddHeaders(): void
    {
        $request = new Request();
        $request->addHeader('Content-Type', 'application/json');
        $request->addHeader('Authorization', 'Bearer token');

        $headers = $request->getHeaders();
        $this->assertEquals('application/json', $headers['Content-Type']);
        $this->assertEquals('Bearer token', $headers['Authorization']);
    }

    public function testCanGetSpecificHeader(): void
    {
        $request = new Request();
        $request->addHeader('Content-Type', 'application/json');

        $this->assertEquals('application/json', $request->getHeader('Content-Type'));
        $this->assertNull($request->getHeader('Non-Existent'));
    }

    public function testCanRemoveHeader(): void
    {
        $request = new Request();
        $request->addHeader('Content-Type', 'application/json');
        $request->removeHeader('Content-Type');

        $this->assertNull($request->getHeader('Content-Type'));
    }

    public function testCanSetBody(): void
    {
        $request = new Request();
        $request->setBody('{"key": "value"}');

        $this->assertEquals('{"key": "value"}', $request->getBody());
    }

    public function testCanAddQueryParams(): void
    {
        $request = new Request();
        $request->addQueryParam('page', 1);
        $request->addQueryParam('limit', 10);

        $params = $request->getQueryParams();
        $this->assertEquals(1, $params['page']);
        $this->assertEquals(10, $params['limit']);
    }

    public function testCanBuildUrlWithQueryParams(): void
    {
        $request = new Request();
        $request->setUrl('https://example.com/api');
        $request->addQueryParam('page', 1);
        $request->addQueryParam('limit', 10);

        $url = $request->buildUrl();
        $this->assertStringContainsString('page=1', $url);
        $this->assertStringContainsString('limit=10', $url);
    }

    public function testCanSetTimeout(): void
    {
        $request = new Request();
        $request->setTimeout(60);

        $this->assertEquals(60, $request->getTimeout());
    }

    public function testCanSetFollowRedirects(): void
    {
        $request = new Request();
        $request->setFollowRedirects(false);

        $this->assertFalse($request->shouldFollowRedirects());
    }

    public function testCanSetMaxRedirects(): void
    {
        $request = new Request();
        $request->setMaxRedirects(5);

        $this->assertEquals(5, $request->getMaxRedirects());
    }

    public function testCanSetVerifySSL(): void
    {
        $request = new Request();
        $request->setVerifySSL(false);

        $this->assertFalse($request->shouldVerifySSL());
    }

    public function testCanSetAuthentication(): void
    {
        $request = new Request();
        $auth = new BasicAuth('user', 'pass');
        $request->setAuthentication($auth);

        $this->assertSame($auth, $request->getAuthentication());
    }

    public function testCanAddOptions(): void
    {
        $request = new Request();
        $request->addOption('custom_option', 'value');

        $options = $request->getOptions();
        $this->assertEquals('value', $options['custom_option']);
    }

    public function testCanConvertToArray(): void
    {
        $request = Request::post('https://example.com')
            ->addHeader('Content-Type', 'application/json')
            ->setBody('{"key": "value"}')
            ->setTimeout(30);

        $array = $request->toArray();

        $this->assertEquals('POST', $array['method']);
        $this->assertEquals('https://example.com', $array['url']);
        $this->assertEquals('application/json', $array['headers']['Content-Type']);
        $this->assertEquals('{"key": "value"}', $array['body']);
        $this->assertEquals(30, $array['timeout']);
    }
}
