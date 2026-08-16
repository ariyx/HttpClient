<?php

declare(strict_types=1);

namespace Ariyx\HttpClient\Tests\Support;

use Ariyx\HttpClient\Contracts\TransportInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class FakeTransport implements TransportInterface
{
    /** @var list<RequestInterface> */
    public array $requests = [];

    /** @param \Closure(RequestInterface): ResponseInterface $handler */
    public function __construct(private \Closure $handler) {}

    public function handle(RequestInterface $request): ResponseInterface
    {
        $this->requests[] = $request;
        return ($this->handler)($request);
    }
}
