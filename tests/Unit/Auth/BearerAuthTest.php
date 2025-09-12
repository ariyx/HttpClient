<?php

declare(strict_types=1);

namespace Ariyx\HttpClient\Tests\Unit\Auth;

use Ariyx\HttpClient\Auth\BearerAuth;
use Ariyx\HttpClient\Request;
use PHPUnit\Framework\TestCase;

/**
 * Bearer Auth Test
 *
 * @package Ariyx\HttpClient\Tests\Unit\Auth
 * @author  Armin Malekzadeh <arixologist@gmail.com>
 * @version 2.0.0
 */
class BearerAuthTest extends TestCase
{
    public function testCanCreateBearerAuth(): void
    {
        $auth = new BearerAuth('token123');

        $this->assertEquals('token123', $auth->getToken());
        $this->assertEquals('Bearer', $auth->getType());
    }

    public function testCanAuthenticateRequest(): void
    {
        $auth = new BearerAuth('token123');
        $request = Request::get('https://example.com');

        $authenticatedRequest = $auth->authenticate($request);

        $this->assertSame($request, $authenticatedRequest);
        $this->assertEquals('Bearer token123', $authenticatedRequest->getHeader('Authorization'));
    }

    public function testCanSetToken(): void
    {
        $auth = new BearerAuth('token123');
        $auth->setToken('newtoken456');

        $this->assertEquals('newtoken456', $auth->getToken());
    }

    public function testCanHandleEmptyToken(): void
    {
        $auth = new BearerAuth('');
        $request = Request::get('https://example.com');

        $authenticatedRequest = $auth->authenticate($request);

        $this->assertEquals('Bearer ', $authenticatedRequest->getHeader('Authorization'));
    }

    public function testCanHandleSpecialCharactersInToken(): void
    {
        $token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.'
            . 'SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c';
        $auth = new BearerAuth($token);
        $request = Request::get('https://example.com');

        $authenticatedRequest = $auth->authenticate($request);

        $this->assertEquals('Bearer ' . $token, $authenticatedRequest->getHeader('Authorization'));
    }
}
