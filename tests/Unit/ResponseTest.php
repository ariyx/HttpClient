<?php

declare(strict_types=1);

namespace Ariyx\HttpClient\Tests\Unit;

use Ariyx\HttpClient\Request;
use Ariyx\HttpClient\Response;
use PHPUnit\Framework\TestCase;

/**
 * Response Test
 *
 * @package Ariyx\HttpClient\Tests\Unit
 * @author  Armin Malekzadeh <arixologist@gmail.com>
 * @version 2.0.0
 */
class ResponseTest extends TestCase
{
    public function testCanCreateResponse(): void
    {
        $response = new Response(200, ['Content-Type' => 'application/json'], '{"key": "value"}');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeader('Content-Type'));
        $this->assertEquals('{"key": "value"}', $response->getBody());
    }

    public function testCanGetSpecificHeader(): void
    {
        $response = new Response(200, ['Content-Type' => 'application/json']);

        $this->assertEquals('application/json', $response->getHeader('Content-Type'));
        $this->assertEquals('application/json', $response->getHeader('content-type')); // Case insensitive
        $this->assertNull($response->getHeader('Non-Existent'));
    }

    public function testCanCheckIfHeaderExists(): void
    {
        $response = new Response(200, ['Content-Type' => 'application/json']);

        $this->assertTrue($response->hasHeader('Content-Type'));
        $this->assertTrue($response->hasHeader('content-type')); // Case insensitive
        $this->assertFalse($response->hasHeader('Non-Existent'));
    }

    public function testCanParseJsonBody(): void
    {
        $response = new Response(200, [], '{"key": "value", "number": 123}');

        $json = $response->json();
        $this->assertIsArray($json);
        $this->assertEquals('value', $json['key']);
        $this->assertEquals(123, $json['number']);
    }

    public function testCanParseXmlBody(): void
    {
        $xml = '<?xml version="1.0"?><root><item>value</item></root>';
        $response = new Response(200, [], $xml);

        $parsed = $response->xml();
        $this->assertInstanceOf(\SimpleXMLElement::class, $parsed);
        $this->assertEquals('value', (string) $parsed->item);
    }

    public function testCanSetInfo(): void
    {
        $response = new Response();
        $info = ['total_time' => 1.5, 'size_download' => 1024];
        $response->setInfo($info);

        $this->assertEquals($info, $response->getInfo());
        $this->assertEquals(1.5, $response->getInfoValue('total_time'));
        $this->assertEquals(1024, $response->getInfoValue('size_download'));
    }

    public function testCanSetRequest(): void
    {
        $response = new Response();
        $request = Request::get('https://example.com');
        $response->setRequest($request);

        $this->assertSame($request, $response->getRequest());
    }

    public function testCanSetDuration(): void
    {
        $response = new Response();
        $response->setDuration(1.5);

        $this->assertEquals(1.5, $response->getDuration());
    }

    public function testCanCheckIfSuccessful(): void
    {
        $this->assertTrue((new Response(200))->isSuccessful());
        $this->assertTrue((new Response(201))->isSuccessful());
        $this->assertTrue((new Response(299))->isSuccessful());
        $this->assertFalse((new Response(300))->isSuccessful());
        $this->assertFalse((new Response(400))->isSuccessful());
        $this->assertFalse((new Response(500))->isSuccessful());
    }

    public function testCanCheckIfRedirect(): void
    {
        $this->assertFalse((new Response(200))->isRedirect());
        $this->assertTrue((new Response(301))->isRedirect());
        $this->assertTrue((new Response(302))->isRedirect());
        $this->assertTrue((new Response(399))->isRedirect());
        $this->assertFalse((new Response(400))->isRedirect());
    }

    public function testCanCheckIfClientError(): void
    {
        $this->assertFalse((new Response(200))->isClientError());
        $this->assertFalse((new Response(300))->isClientError());
        $this->assertTrue((new Response(400))->isClientError());
        $this->assertTrue((new Response(404))->isClientError());
        $this->assertTrue((new Response(499))->isClientError());
        $this->assertFalse((new Response(500))->isClientError());
    }

    public function testCanCheckIfServerError(): void
    {
        $this->assertFalse((new Response(200))->isServerError());
        $this->assertFalse((new Response(400))->isServerError());
        $this->assertTrue((new Response(500))->isServerError());
        $this->assertTrue((new Response(503))->isServerError());
        $this->assertTrue((new Response(599))->isServerError());
    }

    public function testCanCheckIfError(): void
    {
        $this->assertFalse((new Response(200))->isError());
        $this->assertFalse((new Response(300))->isError());
        $this->assertTrue((new Response(400))->isError());
        $this->assertTrue((new Response(500))->isError());
    }

    public function testCanGetContentType(): void
    {
        $response = new Response(200, ['Content-Type' => 'application/json']);

        $this->assertEquals('application/json', $response->getContentType());
    }

    public function testCanGetContentLength(): void
    {
        $response = new Response(200, ['Content-Length' => '1024']);

        $this->assertEquals(1024, $response->getContentLength());
    }

    public function testCanGetEffectiveUrl(): void
    {
        $response = new Response();
        $response->setInfo(['url' => 'https://example.com/final']);

        $this->assertEquals('https://example.com/final', $response->getEffectiveUrl());
    }

    public function testCanGetTotalTime(): void
    {
        $response = new Response();
        $response->setInfo(['total_time' => 1.5]);

        $this->assertEquals(1.5, $response->getTotalTime());
    }

    public function testCanGetConnectTime(): void
    {
        $response = new Response();
        $response->setInfo(['connect_time' => 0.5]);

        $this->assertEquals(0.5, $response->getConnectTime());
    }

    public function testCanGetSizeDownload(): void
    {
        $response = new Response();
        $response->setInfo(['size_download' => 1024]);

        $this->assertEquals(1024, $response->getSizeDownload());
    }

    public function testCanGetSpeedDownload(): void
    {
        $response = new Response();
        $response->setInfo(['speed_download' => 1024.5]);

        $this->assertEquals(1024.5, $response->getSpeedDownload());
    }

    public function testCanConvertToArray(): void
    {
        $response = new Response(200, ['Content-Type' => 'application/json'], '{"key": "value"}');
        $response->setDuration(1.5);
        $response->setInfo(['total_time' => 1.5]);

        $array = $response->toArray();

        $this->assertEquals(200, $array['status_code']);
        $this->assertEquals('application/json', $array['headers']['Content-Type']);
        $this->assertEquals('{"key": "value"}', $array['body']);
        $this->assertEquals(1.5, $array['duration']);
        $this->assertTrue($array['is_successful']);
        $this->assertFalse($array['is_error']);
        $this->assertEquals('application/json', $array['content_type']);
    }

    public function testCanConvertToJson(): void
    {
        $response = new Response(200, ['Content-Type' => 'application/json'], '{"key": "value"}');

        $json = $response->toJson();
        $decoded = json_decode($json, true);

        $this->assertIsArray($decoded);
        $this->assertEquals(200, $decoded['status_code']);
    }

    public function testCanConvertToString(): void
    {
        $response = new Response(200, [], 'Hello World');

        $this->assertEquals('Hello World', (string) $response);
    }
}
