<?php

declare(strict_types=1);

namespace Ariyx\HttpClient\Tests\Integration;

use Ariyx\HttpClient\Transport\CurlOptions;
use Ariyx\HttpClient\Transport\CurlTransport;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Request;
use PHPUnit\Framework\Attributes\AfterClass;
use PHPUnit\Framework\Attributes\BeforeClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CurlTransportTest extends TestCase
{
    private static mixed $server = null;
    private static string $baseUri;

    #[BeforeClass]
    public static function startServer(): void
    {
        $socket = stream_socket_server('tcp://127.0.0.1:0', $errorCode, $errorMessage);
        if ($socket === false) {
            self::fail($errorMessage ?? 'Unable to allocate a local port.');
        }
        $address = stream_socket_get_name($socket, false);
        fclose($socket);
        if ($address === false) {
            self::fail('Unable to determine the local server address.');
        }
        $separator = strrpos($address, ':');
        if ($separator === false) {
            self::fail('Local server address has no port.');
        }
        $port = (int) substr($address, $separator + 1);
        $router = __DIR__ . '/fixtures/router.php';
        self::$server = proc_open([PHP_BINARY, '-S', '127.0.0.1:' . $port, $router], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        if (!is_resource(self::$server)) {
            self::fail('Unable to start the local test server.');
        }
        self::$baseUri = 'http://127.0.0.1:' . $port;
        for ($i = 0; $i < 50; ++$i) {
            if (@file_get_contents(self::$baseUri . '/ok') !== false) {
                return;
            }
            usleep(10000);
        }
        self::fail('Local test server did not become ready.');
    }

    #[AfterClass]
    public static function stopServer(): void
    {
        if (is_resource(self::$server)) {
            proc_terminate(self::$server);
            proc_close(self::$server);
        }
    }

    #[Test]
    public function it_sends_bodies_headers_and_receives_response_headers(): void
    {
        $factory = new Psr17Factory();
        $transport = new CurlTransport($factory, $factory);
        $request = (new Request('POST', self::$baseUri . '/ok', ['X-Request' => ['value']]))->withBody($factory->createStream('payload'));
        $response = $transport->handle($request);
        self::assertSame(200, $response->getStatusCode());
        self::assertSame('1.1', $response->getProtocolVersion());
        self::assertSame('POST:payload', (string) $response->getBody());
        self::assertSame('value', $response->getHeaderLine('X-Request-Echo'));
        self::assertSame(['first=1', 'second=2'], $response->getHeader('Set-Cookie'));
    }

    #[Test]
    public function it_returns_a_404_and_does_not_follow_redirects_by_default(): void
    {
        $factory = new Psr17Factory();
        $transport = new CurlTransport($factory, $factory, new CurlOptions(followRedirects: false));
        self::assertSame(404, $transport->handle(new Request('GET', self::$baseUri . '/missing'))->getStatusCode());
        self::assertSame(302, $transport->handle(new Request('GET', self::$baseUri . '/redirect'))->getStatusCode());
    }
}
