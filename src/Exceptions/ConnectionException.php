<?php

declare(strict_types=1);

namespace Ariyx\HttpClient\Exceptions;

/**
 * Connection Exception
 *
 * Exception thrown when a connection cannot be established.
 *
 * @package Ariyx\HttpClient\Exceptions
 * @author  Armin Malekzadeh <arixologist@gmail.com>
 * @version 2.0.0
 */
class ConnectionException extends HttpClientException
{
    protected ?string $host = null;
    protected ?int $port = null;

    public function __construct(
        string $message = '',
        ?string $host = null,
        ?int $port = null,
        ?\Exception $previous = null
    ) {
        parent::__construct($message, 0, $previous, null, null, [
            'host' => $host,
            'port' => $port,
        ]);

        $this->host = $host;
        $this->port = $port;
    }

    /**
     * Get the host that failed to connect
     */
    public function getHost(): ?string
    {
        return $this->host;
    }

    /**
     * Get the port that failed to connect
     */
    public function getPort(): ?int
    {
        return $this->port;
    }

    /**
     * Create a connection exception from cURL error
     */
    public static function fromCurlError(int $errorCode, string $errorMessage, ?string $url = null): self
    {
        $message = sprintf('Connection failed: %s (cURL error %d)', $errorMessage, $errorCode);

        $host = null;
        $port = null;

        if ($url) {
            $parsed = parse_url($url);
            $host = $parsed['host'] ?? null;
            $port = $parsed['port'] ?? null;
        }

        return new self($message, $host, $port);
    }
}
