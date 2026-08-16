<?php

declare(strict_types=1);

namespace Ariyx\HttpClient\Tests\Unit\Transport;

use Ariyx\HttpClient\Transport\CurlHeaderCollector;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CurlHeaderCollectorTest extends TestCase
{
    #[Test]
    public function it_collects_duplicate_headers_from_the_final_block(): void
    {
        $collector = new CurlHeaderCollector();
        foreach (["HTTP/1.1 100 Continue\r\n", "\r\n", "HTTP/1.1 200 OK\r\n", "Set-Cookie: a=1\r\n", "Set-Cookie: b=2\r\n", "X-Test: yes\r\n", "\r\n"] as $line) {
            $collector->collect($line);
        }
        self::assertSame(200, $collector->statusCode());
        self::assertSame(['a=1', 'b=2'], $collector->headers()['Set-Cookie']);
        self::assertSame(['yes'], $collector->headers()['X-Test']);
    }

    #[Test]
    public function it_discards_redirect_headers_when_a_final_response_arrives(): void
    {
        $collector = new CurlHeaderCollector();
        foreach (["HTTP/1.1 302 Found\r\n", "Location: /next\r\n", "\r\n", "HTTP/1.1 200 OK\r\n", "X-Final: true\r\n"] as $line) {
            $collector->collect($line);
        }
        self::assertSame(200, $collector->statusCode());
        self::assertArrayNotHasKey('Location', $collector->headers());
        self::assertSame(['true'], $collector->headers()['X-Final']);
    }
}
