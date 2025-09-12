<?php

declare(strict_types=1);

namespace Ariyx\HttpClient;

/**
 * HTTP Response Class
 *
 * Represents an HTTP response with all its components.
 *
 * @package Ariyx\HttpClient
 * @author  Armin Malekzadeh <arixologist@gmail.com>
 * @version 2.0.0
 */
class Response
{
    private int $statusCode;
    private array $headers = [];
    private string $body = '';
    private array $info = [];
    private ?Request $request = null;
    private float $duration = 0.0;

    public function __construct(
        int $statusCode = 200,
        array $headers = [],
        string $body = '',
        array $info = []
    ) {
        $this->statusCode = $statusCode;
        $this->headers = $headers;
        $this->body = $body;
        $this->info = $info;
    }

    /**
     * Get the HTTP status code
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Set the HTTP status code
     */
    public function setStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * Get all headers
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Set headers
     */
    public function setHeaders(array $headers): self
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * Get a specific header
     */
    public function getHeader(string $name): ?string
    {
        $name = strtolower($name);
        foreach ($this->headers as $header => $value) {
            if (strtolower($header) === $name) {
                return $value;
            }
        }
        return null;
    }

    /**
     * Check if a header exists
     */
    public function hasHeader(string $name): bool
    {
        return $this->getHeader($name) !== null;
    }

    /**
     * Get the response body
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * Set the response body
     */
    public function setBody(string $body): self
    {
        $this->body = $body;
        return $this;
    }

    /**
     * Get the response body as JSON
     */
    public function json(): ?array
    {
        $data = json_decode($this->body, true);
        return json_last_error() === JSON_ERROR_NONE ? $data : null;
    }

    /**
     * Get the response body as XML
     */
    public function xml(): ?\SimpleXMLElement
    {
        $previous = libxml_use_internal_errors(true);
        $xml = simplexml_load_string($this->body);
        libxml_use_internal_errors($previous);

        return $xml !== false ? $xml : null;
    }

    /**
     * Get cURL info
     */
    public function getInfo(): array
    {
        return $this->info;
    }

    /**
     * Set cURL info
     */
    public function setInfo(array $info): self
    {
        $this->info = $info;
        return $this;
    }

    /**
     * Get a specific info value
     */
    public function getInfoValue(string $key): mixed
    {
        return $this->info[$key] ?? null;
    }

    /**
     * Get the original request
     */
    public function getRequest(): ?Request
    {
        return $this->request;
    }

    /**
     * Set the original request
     */
    public function setRequest(Request $request): self
    {
        $this->request = $request;
        return $this;
    }

    /**
     * Get the request duration in seconds
     */
    public function getDuration(): float
    {
        return $this->duration;
    }

    /**
     * Set the request duration
     */
    public function setDuration(float $duration): self
    {
        $this->duration = $duration;
        return $this;
    }

    /**
     * Check if the response is successful (2xx status codes)
     */
    public function isSuccessful(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    /**
     * Check if the response is a redirect (3xx status codes)
     */
    public function isRedirect(): bool
    {
        return $this->statusCode >= 300 && $this->statusCode < 400;
    }

    /**
     * Check if the response is a client error (4xx status codes)
     */
    public function isClientError(): bool
    {
        return $this->statusCode >= 400 && $this->statusCode < 500;
    }

    /**
     * Check if the response is a server error (5xx status codes)
     */
    public function isServerError(): bool
    {
        return $this->statusCode >= 500 && $this->statusCode < 600;
    }

    /**
     * Check if the response is an error (4xx or 5xx status codes)
     */
    public function isError(): bool
    {
        return $this->isClientError() || $this->isServerError();
    }

    /**
     * Get the content type
     */
    public function getContentType(): ?string
    {
        return $this->getHeader('content-type');
    }

    /**
     * Get the content length
     */
    public function getContentLength(): ?int
    {
        $length = $this->getHeader('content-length');
        return $length ? (int) $length : null;
    }

    /**
     * Get the effective URL (after redirects)
     */
    public function getEffectiveUrl(): ?string
    {
        return $this->getInfoValue('url');
    }

    /**
     * Get the total time
     */
    public function getTotalTime(): ?float
    {
        return $this->getInfoValue('total_time');
    }

    /**
     * Get the connect time
     */
    public function getConnectTime(): ?float
    {
        return $this->getInfoValue('connect_time');
    }

    /**
     * Get the size of the downloaded data
     */
    public function getSizeDownload(): ?int
    {
        return $this->getInfoValue('size_download');
    }

    /**
     * Get the speed of the download
     */
    public function getSpeedDownload(): ?float
    {
        return $this->getInfoValue('speed_download');
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'status_code' => $this->statusCode,
            'headers' => $this->headers,
            'body' => $this->body,
            'info' => $this->info,
            'duration' => $this->duration,
            'is_successful' => $this->isSuccessful(),
            'is_error' => $this->isError(),
            'content_type' => $this->getContentType(),
            'content_length' => $this->getContentLength(),
            'effective_url' => $this->getEffectiveUrl(),
        ];
    }

    /**
     * Convert to JSON string
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT);
    }

    /**
     * String representation
     */
    public function __toString(): string
    {
        return $this->body;
    }
}
