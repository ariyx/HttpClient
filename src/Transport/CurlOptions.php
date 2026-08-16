<?php

declare(strict_types=1);

namespace Ariyx\HttpClient\Transport;

final readonly class CurlOptions
{
    public function __construct(
        public int $connectTimeoutMs = 10000,
        public int $timeoutMs = 30000,
        public bool $verifyPeer = true,
        public bool $verifyHost = true,
        public bool $followRedirects = false,
        public int $maxRedirects = 5,
        public string $userAgent = 'ariyx/http-client',
    ) {
        if ($connectTimeoutMs < 0 || $timeoutMs < 0) {
            throw new \InvalidArgumentException('Timeout values cannot be negative.');
        }
        if ($maxRedirects < 0) {
            throw new \InvalidArgumentException('Maximum redirects cannot be negative.');
        }
        if ($userAgent === '') {
            throw new \InvalidArgumentException('User agent cannot be empty.');
        }
    }
}
