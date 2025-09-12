<?php

declare(strict_types=1);

namespace Ariyx\HttpClient\Exceptions;

use Ariyx\HttpClient\Request;
use Ariyx\HttpClient\Response;

/**
 * Request Exception
 *
 * Exception thrown when a request fails.
 *
 * @package Ariyx\HttpClient\Exceptions
 * @author  Armin Malekzadeh <arixologist@gmail.com>
 * @version 2.0.0
 */
class RequestException extends HttpClientException
{
    protected ?Request $request = null;
    protected ?Response $response = null;

    public function __construct(
        string $message = '',
        ?Request $request = null,
        ?Response $response = null,
        ?Exception $previous = null
    ) {
        parent::__construct(
            $message,
            0,
            $previous,
            $request?->getUrl(),
            $response?->getStatusCode(),
            [
                'request' => $request?->toArray(),
                'response' => $response?->toArray(),
            ]
        );

        $this->request = $request;
        $this->response = $response;
    }

    /**
     * Get the request that caused the exception
     */
    public function getRequest(): ?Request
    {
        return $this->request;
    }

    /**
     * Get the response (if any)
     */
    public function getResponse(): ?Response
    {
        return $this->response;
    }

    /**
     * Create a request exception from a response
     */
    public static function fromResponse(Response $response, ?Request $request = null): self
    {
        $message = sprintf(
            'HTTP %d returned for "%s"',
            $response->getStatusCode(),
            $request?->getUrl() ?? 'unknown URL'
        );

        return new self($message, $request, $response);
    }

    /**
     * Create a request exception for a client error (4xx)
     */
    public static function clientError(Response $response, ?Request $request = null): self
    {
        $message = sprintf(
            'Client error: HTTP %d returned for "%s"',
            $response->getStatusCode(),
            $request?->getUrl() ?? 'unknown URL'
        );

        return new self($message, $request, $response);
    }

    /**
     * Create a request exception for a server error (5xx)
     */
    public static function serverError(Response $response, ?Request $request = null): self
    {
        $message = sprintf(
            'Server error: HTTP %d returned for "%s"',
            $response->getStatusCode(),
            $request?->getUrl() ?? 'unknown URL'
        );

        return new self($message, $request, $response);
    }
}
