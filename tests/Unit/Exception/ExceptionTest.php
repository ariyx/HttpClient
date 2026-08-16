<?php

declare(strict_types=1);

namespace Ariyx\HttpClient\Tests\Unit\Exception;

use Ariyx\HttpClient\Exception\ClientException;
use Ariyx\HttpClient\Exception\NetworkException;
use Ariyx\HttpClient\Exception\RequestException;
use Nyholm\Psr7\Request;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Client\RequestExceptionInterface;

final class ExceptionTest extends TestCase
{
    #[Test]
    public function it_implements_the_psr_exception_interfaces(): void
    {
        $request = new Request('GET', 'https://example.test');
        self::assertInstanceOf(ClientExceptionInterface::class, new ClientException('error'));
        self::assertInstanceOf(RequestExceptionInterface::class, new RequestException('error', $request));
        self::assertInstanceOf(NetworkExceptionInterface::class, new NetworkException('error', $request));
        self::assertSame($request, (new RequestException('error', $request))->getRequest());
    }
}
