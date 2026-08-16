<?php

declare(strict_types=1);

namespace Ariyx\HttpClient;

use Ariyx\HttpClient\Contracts\MiddlewareInterface;
use Ariyx\HttpClient\Contracts\TransportInterface;
use Ariyx\HttpClient\Middleware\MiddlewarePipeline;
use Ariyx\HttpClient\Transport\CurlOptions;
use Ariyx\HttpClient\Transport\CurlTransport;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final readonly class Client implements ClientInterface
{
    /** @param list<MiddlewareInterface> $middleware */
    public function __construct(private TransportInterface $transport, private array $middleware = []) {}

    /** @param list<MiddlewareInterface> $middleware */
    public static function create(?CurlOptions $options = null, array $middleware = []): self
    {
        $factory = new Psr17Factory();
        return new self(new CurlTransport($factory, $factory, $options ?? new CurlOptions()), $middleware);
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        return (new MiddlewarePipeline($this->transport, $this->middleware))->handle($request);
    }
}
