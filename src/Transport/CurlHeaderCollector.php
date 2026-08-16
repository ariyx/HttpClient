<?php

declare(strict_types=1);

namespace Ariyx\HttpClient\Transport;

final class CurlHeaderCollector
{
    /** @var array<string, list<string>> */
    private array $headers = [];

    /** @var array<string, string> */
    private array $headerNames = [];

    private int $statusCode = 0;
    private string $reasonPhrase = '';
    private string $protocolVersion = '';
    private ?string $error = null;

    public function collect(string $line): int
    {
        $length = strlen($line);
        $trimmed = rtrim($line, "\r\n");

        if ($trimmed === '') {
            return $length;
        }

        if (preg_match('#^HTTP/(1\.0|1\.1|2|3)\s+(\d{3})(?:\s+(.*))?$#', $trimmed, $matches) === 1) {
            $this->headers = [];
            $this->headerNames = [];
            $this->protocolVersion = $matches[1];
            $this->statusCode = (int) $matches[2];
            $this->reasonPhrase = $matches[3] ?? '';
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
        $normalizedName = strtolower($name);
        $outputName = $this->headerNames[$normalizedName] ??= $name;
        $this->headers[$outputName] ??= [];
        $this->headers[$outputName][] = $value;

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

    public function protocolVersion(): string
    {
        return $this->protocolVersion;
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
