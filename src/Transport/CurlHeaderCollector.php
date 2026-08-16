<?php

declare(strict_types=1);

namespace Ariyx\HttpClient\Transport;

final class CurlHeaderCollector
{
    /** @var array<string, list<string>> */
    private array $headers = [];
    private int $statusCode = 0;
    private string $reasonPhrase = '';
    private ?string $error = null;

    public function collect(string $line): int
    {
        $length = strlen($line);
        $trimmed = rtrim($line, "\r\n");

        if ($trimmed === '') {
            return $length;
        }

        if (preg_match('#^HTTP/\d(?:\.\d)?\s+(\d{3})(?:\s+(.*))?$#', $trimmed, $matches) === 1) {
            $this->headers = [];
            $this->statusCode = (int) $matches[1];
            $this->reasonPhrase = $matches[2] ?? '';
            return $length;
        }

        $position = strpos($trimmed, ':');
        if ($position === false) {
            $this->error = 'Malformed HTTP response header.';
            return 0;
        }

        $name = substr($trimmed, 0, $position);
        $value = ltrim(substr($trimmed, $position + 1));
        if ($name === '' || preg_match('/^[!#$%&\'*+.^_`|~0-9A-Za-z-]+$/', $name) !== 1) {
            $this->error = 'Malformed HTTP response header name.';
            return 0;
        }
        $this->headers[$name] ??= [];
        $this->headers[$name][] = $value;

        return $length;
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }

    public function reasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

    /** @return array<string, list<string>> */
    public function headers(): array
    {
        return $this->headers;
    }

    public function error(): ?string
    {
        return $this->error;
    }
}
