<?php

declare(strict_types=1);

namespace Ariyx\HttpClient\Tests\Unit\Transport;

use Ariyx\HttpClient\Transport\CurlOptions;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CurlOptionsTest extends TestCase
{
    #[Test]
    public function it_has_secure_defaults(): void
    {
        $options = new CurlOptions();
        self::assertSame(10000, $options->connectTimeoutMs);
        self::assertSame(30000, $options->timeoutMs);
        self::assertTrue($options->verifyPeer);
        self::assertTrue($options->verifyHost);
        self::assertFalse($options->followRedirects);
    }

    #[Test]
    public function it_rejects_negative_values(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new CurlOptions(timeoutMs: -1);
    }
}
