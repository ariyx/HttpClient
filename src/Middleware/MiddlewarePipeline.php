<?php

declare(strict_types=1);

namespace Ariyx\HttpClient\Middleware;

use Ariyx\HttpClient\Contracts\MiddlewareInterface;
use Ariyx\HttpClient\Contracts\RequestHandlerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final readonly class MiddlewarePipeline implements RequestHandlerInterface
{
    /** @param list<MiddlewareInterface> $middleware */
    public function __construct(private RequestHandlerInterface $handler, private array $middleware = []) {}

    public function handle(RequestInterface $request): ResponseInterface
    {
        $handler = $this->handler;
        foreach (array_reverse($this->middleware) as $middleware) {
            $handler = new class ($middleware, $handler) implements RequestHandlerInterface {
                public function __construct(private MiddlewareInterface $middleware, private RequestHandlerInterface $next) {}

                public function handle(RequestInterface $request): ResponseInterface
                {
                    return $this->middleware->process($request, $this->next);
                }
            };
        }
        return $handler->handle($request);
    }
}
