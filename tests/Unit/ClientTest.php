<?php

declare(strict_types=1);

namespace Ariyx\HttpClient\Tests\Unit;

use Ariyx\HttpClient\Client;
use Ariyx\HttpClient\Contracts\MiddlewareInterface;
use Ariyx\HttpClient\Contracts\RequestHandlerInterface;
use Ariyx\HttpClient\Tests\Support\FakeTransport;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class EventLog
{
    /** @var list<string> */
    public array $events = [];
}

final class ClientTest extends TestCase
{
    #[Test]
    public function it_delegates_and_returns_error_statuses_normally(): void
    {
        $transport = new FakeTransport(static fn(RequestInterface $request): ResponseInterface => new Response(404));
        $response = (new Client($transport))->sendRequest(new Request('GET', 'https://example.test'));
        self::assertSame(404, $response->getStatusCode());
        self::assertCount(1, $transport->requests);
    }

    #[Test]
    public function middleware_runs_in_declared_order(): void
    {
        $log = new EventLog();
        $makeMiddleware = static function (string $name) use ($log): MiddlewareInterface {
            return new class ($name, $log) implements MiddlewareInterface {
                public function __construct(private string $name, private EventLog $log) {}

                public function process(RequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
                {
                    $this->log->events[] = $this->name . '-before';
                    $response = $handler->handle($request);
                    $this->log->events[] = $this->name . '-after';
                    return $response;
                }
            };
        };
        $client = new Client(new FakeTransport(static fn(): ResponseInterface => new Response(500)), [$makeMiddleware('one'), $makeMiddleware('two')]);
        self::assertSame(500, $client->sendRequest(new Request('GET', 'https://example.test'))->getStatusCode());
        self::assertSame(['one-before', 'two-before', 'two-after', 'one-after'], $log->events);
    }
}
