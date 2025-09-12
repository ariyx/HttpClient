<?php

declare(strict_types=1);

namespace Ariyx\HttpClient\Tests\Unit\Auth;

use Ariyx\HttpClient\Auth\BasicAuth;
use Ariyx\HttpClient\Request;
use PHPUnit\Framework\TestCase;

/**
 * Basic Auth Test
 *
 * @package Ariyx\HttpClient\Tests\Unit\Auth
 * @author  Armin Malekzadeh <arixologist@gmail.com>
 * @version 2.0.0
 */
class BasicAuthTest extends TestCase
{
    public function testCanCreateBasicAuth(): void
    {
        $auth = new BasicAuth('username', 'password');

        $this->assertEquals('username', $auth->getUsername());
        $this->assertEquals('password', $auth->getPassword());
        $this->assertEquals('Basic', $auth->getType());
    }

    public function testCanAuthenticateRequest(): void
    {
        $auth = new BasicAuth('username', 'password');
        $request = Request::get('https://example.com');

        $authenticatedRequest = $auth->authenticate($request);

        $this->assertSame($request, $authenticatedRequest);
        $this->assertEquals('Basic ' . base64_encode('username:password'), $authenticatedRequest->getHeader('Authorization'));
    }

    public function testCanHandleSpecialCharacters(): void
    {
        $auth = new BasicAuth('user:name', 'pass:word');
        $request = Request::get('https://example.com');

        $authenticatedRequest = $auth->authenticate($request);

        $expectedAuth = 'Basic ' . base64_encode('user:name:pass:word');
        $this->assertEquals($expectedAuth, $authenticatedRequest->getHeader('Authorization'));
    }

    public function testCanHandleEmptyCredentials(): void
    {
        $auth = new BasicAuth('', '');
        $request = Request::get('https://example.com');

        $authenticatedRequest = $auth->authenticate($request);

        $expectedAuth = 'Basic ' . base64_encode(':');
        $this->assertEquals($expectedAuth, $authenticatedRequest->getHeader('Authorization'));
    }
}
