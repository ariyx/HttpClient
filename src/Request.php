<?php

declare(strict_types=1);

namespace Ariyx\HttpClient;

use Ariyx\HttpClient\Contracts\AuthenticationInterface;

/**
 * HTTP Request Class
 *
 * Represents an HTTP request with all its components.
 *
 * @package Ariyx\HttpClient
 * @author  Armin Malekzadeh <arixologist@gmail.com>
 * @version 2.0.0
 */
class Request
{
    private string $method;
    private string $url;
    private array $headers = [];
    private mixed $body = null;
    private array $queryParams = [];
    private array $options = [];
    private ?AuthenticationInterface $authentication = null;
    private int $timeout = 30;
    private bool $followRedirects = true;
    private int $maxRedirects = 10;
    private bool $verifySSL = false;

    public function __construct(
        string $method = 'GET',
        string $url = '',
        array $options = []
    ) {
        $this->method = strtoupper($method);
        $this->url = $url;
        $this->options = $options;
    }

    /**
     * Create a new GET request
     */
    public static function get(string $url, array $options = []): self
    {
        return new self('GET', $url, $options);
    }

    /**
     * Create a new POST request
     */
    public static function post(string $url, array $options = []): self
    {
        return new self('POST', $url, $options);
    }

    /**
     * Create a new PUT request
     */
    public static function put(string $url, array $options = []): self
    {
        return new self('PUT', $url, $options);
    }

    /**
     * Create a new PATCH request
     */
    public static function patch(string $url, array $options = []): self
    {
        return new self('PATCH', $url, $options);
    }

    /**
     * Create a new DELETE request
     */
    public static function delete(string $url, array $options = []): self
    {
        return new self('DELETE', $url, $options);
    }

    /**
     * Create a new HEAD request
     */
    public static function head(string $url, array $options = []): self
    {
        return new self('HEAD', $url, $options);
    }

    /**
     * Create a new OPTIONS request
     */
    public static function options(string $url, array $options = []): self
    {
        return new self('OPTIONS', $url, $options);
    }

    /**
     * Get the HTTP method
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Set the HTTP method
     */
    public function setMethod(string $method): self
    {
        $this->method = strtoupper($method);
        return $this;
    }

    /**
     * Get the URL
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Set the URL
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;
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
     * Add a header
     */
    public function addHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Get a specific header
     */
    public function getHeader(string $name): ?string
    {
        return $this->headers[$name] ?? null;
    }

    /**
     * Remove a header
     */
    public function removeHeader(string $name): self
    {
        unset($this->headers[$name]);
        return $this;
    }

    /**
     * Get the request body
     */
    public function getBody(): mixed
    {
        return $this->body;
    }

    /**
     * Set the request body
     */
    public function setBody(mixed $body): self
    {
        $this->body = $body;
        return $this;
    }

    /**
     * Get query parameters
     */
    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    /**
     * Set query parameters
     */
    public function setQueryParams(array $params): self
    {
        $this->queryParams = $params;
        return $this;
    }

    /**
     * Add a query parameter
     */
    public function addQueryParam(string $name, mixed $value): self
    {
        $this->queryParams[$name] = $value;
        return $this;
    }

    /**
     * Get all options
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Set options
     */
    public function setOptions(array $options): self
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Add an option
     */
    public function addOption(string $name, mixed $value): self
    {
        $this->options[$name] = $value;
        return $this;
    }

    /**
     * Get authentication
     */
    public function getAuthentication(): ?AuthenticationInterface
    {
        return $this->authentication;
    }

    /**
     * Set authentication
     */
    public function setAuthentication(AuthenticationInterface $authentication): self
    {
        $this->authentication = $authentication;
        return $this;
    }

    /**
     * Get timeout
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * Set timeout
     */
    public function setTimeout(int $timeout): self
    {
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * Should follow redirects
     */
    public function shouldFollowRedirects(): bool
    {
        return $this->followRedirects;
    }

    /**
     * Set follow redirects
     */
    public function setFollowRedirects(bool $follow): self
    {
        $this->followRedirects = $follow;
        return $this;
    }

    /**
     * Get max redirects
     */
    public function getMaxRedirects(): int
    {
        return $this->maxRedirects;
    }

    /**
     * Set max redirects
     */
    public function setMaxRedirects(int $maxRedirects): self
    {
        $this->maxRedirects = $maxRedirects;
        return $this;
    }

    /**
     * Should verify SSL
     */
    public function shouldVerifySSL(): bool
    {
        return $this->verifySSL;
    }

    /**
     * Set verify SSL
     */
    public function setVerifySSL(bool $verify): self
    {
        $this->verifySSL = $verify;
        return $this;
    }

    /**
     * Build the full URL with query parameters
     */
    public function buildUrl(): string
    {
        if (empty($this->queryParams)) {
            return $this->url;
        }

        $separator = strpos($this->url, '?') !== false ? '&' : '?';
        return $this->url . $separator . http_build_query($this->queryParams);
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'method' => $this->method,
            'url' => $this->url,
            'headers' => $this->headers,
            'body' => $this->body,
            'query_params' => $this->queryParams,
            'options' => $this->options,
            'timeout' => $this->timeout,
            'follow_redirects' => $this->followRedirects,
            'max_redirects' => $this->maxRedirects,
            'verify_ssl' => $this->verifySSL,
        ];
    }
}
