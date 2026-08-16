<?php

declare(strict_types=1);

namespace Ariyx\HttpClient\Tests\Unit\Middleware;

use Ariyx\HttpClient\Contracts\RequestHandlerInterface;
use Ariyx\HttpClient\Exception\NetworkException;
use Ariyx\HttpClient\Exception\RequestException;
use Ariyx\HttpClient\Middleware\RetryMiddleware;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use Psr\Http\Client\NetworkExceptionInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class ForeignNetworkException extends \RuntimeException implements NetworkExceptionInterface
{
    public function __construct(private readonly RequestInterface $request)
    {
        parent::__construct('foreign network failure');
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}

final class RetryMiddlewareTest extends TestCase
{
    #[Test]
    public function it_retries_transient_responses(): void
    {
        $attempts = 0;
        $handler = $this->handler(static function () use (&$attempts): ResponseInterface {
            ++$attempts;
            return new Response($attempts === 1 ? 503 : 200);
        });
        $response = (new RetryMiddleware(baseDelayMs: 0))->process(new Request('GET', 'https://example.test'), $handler);
        self::assertSame(200, $response->getStatusCode());
        self::assertSame(2, $attempts);
    }

    #[Test]
    public function it_retries_network_failures_and_rethrows_the_last_one(): void
    {
        $request = new Request('GET', 'https://example.test');
        $attempts = 0;
        $handler = $this->handler(static function () use (&$attempts, $request): ResponseInterface {
            ++$attempts;
            throw new NetworkException('offline', $request);
        });
        $this->expectException(NetworkException::class);
        try {
            (new RetryMiddleware(baseDelayMs: 0))->process($request, $handler);
        } finally {
            self::assertSame(3, $attempts);
        }
    }

    #[Test]
    public function it_retries_foreign_psr_network_exceptions(): void
    {
        $request = new Request('GET', 'https://example.test');
        $attempts = 0;
        $handler = $this->handler(static function () use (&$attempts, $request): ResponseInterface {
            ++$attempts;
            if ($attempts === 1) {
                throw new ForeignNetworkException($request);
            }
            return new Response(200);
        });
        $response = (new RetryMiddleware(baseDelayMs: 0))->process($request, $handler);
        self::assertSame(200, $response->getStatusCode());
        self::assertSame(2, $attempts);
    }

    #[Test]
    public function it_does_not_retry_post_by_default(): void
    {
        $attempts = 0;
        $handler = $this->handler(static function () use (&$attempts): ResponseInterface {
            ++$attempts;
            return new Response(503);
        });
        (new RetryMiddleware(baseDelayMs: 0))->process(new Request('POST', 'https://example.test'), $handler);
        self::assertSame(1, $attempts);
    }

    #[Test]
    public function it_returns_the_last_transient_response(): void
    {
        $attempts = 0;
        $handler = $this->handler(static function () use (&$attempts): ResponseInterface {
            ++$attempts;
            return new Response(503);
        });
        $response = (new RetryMiddleware(baseDelayMs: 0, maxAttempts: 2))->process(new Request('GET', 'https://example.test'), $handler);
        self::assertSame(503, $response->getStatusCode());
        self::assertSame(2, $attempts);
    }

    #[Test]
    public function it_does_not_retry_request_exceptions(): void
    {
        $request = new Request('GET', 'https://example.test');
        $attempts = 0;
        $handler = $this->handler(static function () use (&$attempts, $request): ResponseInterface {
            ++$attempts;
            throw new RequestException('invalid', $request);
        });
        $this->expectException(RequestException::class);
        try {
            (new RetryMiddleware(baseDelayMs: 0))->process($request, $handler);
        } finally {
            self::assertSame(1, $attempts);
        }
    }

    /** @param \Closure(RequestInterface): ResponseInterface $callback */
    private function handler(\Closure $callback): RequestHandlerInterface
    {
        return new class ($callback) implements RequestHandlerInterface {
            /** @param \Closure(RequestInterface): ResponseInterface $callback */
            public function __construct(private \Closure $callback) {}
            public function handle(RequestInterface $request): ResponseInterface
            {
                return ($this->callback)($request);
            }
        };
    }
}
