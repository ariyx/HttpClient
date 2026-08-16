<?php

declare(strict_types=1);

namespace Ariyx\HttpClient\Middleware;

use Ariyx\HttpClient\Contracts\MiddlewareInterface;
use Ariyx\HttpClient\Contracts\RequestHandlerInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final readonly class RetryMiddleware implements MiddlewareInterface
{
    /** @param list<string> $retryableMethods */
    public function __construct(
        private int $maxAttempts = 3,
        private int $baseDelayMs = 100,
        private int $maxDelayMs = 1000,
        private array $retryableMethods = ['GET', 'HEAD', 'PUT', 'DELETE', 'OPTIONS'],
    ) {
        if ($maxAttempts < 1 || $baseDelayMs < 0 || $maxDelayMs < 0) {
            throw new \InvalidArgumentException('Retry options cannot be negative and attempts must be at least one.');
        }
    }

    public function process(RequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!in_array(strtoupper($request->getMethod()), $this->retryableMethods, true)) {
            return $handler->handle($request);
        }

        for ($attempt = 1; $attempt <= $this->maxAttempts; ++$attempt) {
            try {
                $response = $handler->handle($request);
            } catch (NetworkExceptionInterface $exception) {
                if ($attempt === $this->maxAttempts) {
                    throw $exception;
                }
                $this->wait($this->delayFor($attempt, null));
                continue;
            }
            if (!$this->isTransient($response) || $attempt === $this->maxAttempts) {
                return $response;
            }
            $this->wait($this->delayFor($attempt, $response));
        }

        throw new \LogicException('Retry loop ended unexpectedly.');
    }

    private function isTransient(ResponseInterface $response): bool
    {
        return in_array($response->getStatusCode(), [429, 500, 502, 503, 504], true);
    }

    private function delayFor(int $attempt, ?ResponseInterface $response): int
    {
        $delay = min($this->maxDelayMs, $this->baseDelayMs * (2 ** ($attempt - 1)));
        $retryAfter = $response?->getHeaderLine('Retry-After');
        if ($retryAfter !== null && ctype_digit($retryAfter)) {
            $delay = min($this->maxDelayMs, (int) $retryAfter * 1000);
        }
        return $delay;
    }

    private function wait(int $milliseconds): void
    {
        if ($milliseconds > 0) {
            usleep($milliseconds * 1000);
        }
    }
}
