<?php

declare(strict_types=1);

namespace Ariyx\HttpClient\Exceptions;

use Exception;

/**
 * Base HTTP Client Exception
 *
 * Base exception class for all HTTP client related exceptions.
 *
 * @package Ariyx\HttpClient\Exceptions
 * @author  Armin Malekzadeh <arixologist@gmail.com>
 * @version 2.0.0
 */
class HttpClientException extends Exception
{
    protected ?string $url = null;
    protected ?int $statusCode = null;
    protected array $context = [];

    public function __construct(
        string $message = '',
        int $code = 0,
        ?Exception $previous = null,
        ?string $url = null,
        ?int $statusCode = null,
        array $context = []
    ) {
        parent::__construct($message, $code, $previous);
        $this->url = $url;
        $this->statusCode = $statusCode;
        $this->context = $context;
    }

    /**
     * Get the URL that caused the exception
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * Get the HTTP status code
     */
    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    /**
     * Get the context data
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Set context data
     */
    public function setContext(array $context): self
    {
        $this->context = $context;
        return $this;
    }

    /**
     * Add context data
     */
    public function addContext(string $key, mixed $value): self
    {
        $this->context[$key] = $value;
        return $this;
    }
}
