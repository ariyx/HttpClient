<?php

declare(strict_types=1);

namespace Ariyx\HttpClient\Contracts;

use Ariyx\HttpClient\Request;
use Ariyx\HttpClient\Response;

/**
 * HTTP Client Interface
 *
 * Defines the contract for HTTP client implementations.
 *
 * @package Ariyx\HttpClient\Contracts
 * @author  Armin Malekzadeh <arixologist@gmail.com>
 * @version 2.0.0
 */
interface HttpClientInterface
{
    /**
     * Send a GET request
     *
     * @param  string $url     The URL to send the request to
     * @param  array  $options Request options
     * @return Response The HTTP response
     */
    public function get(string $url, array $options = []): Response;

    /**
     * Send a POST request
     *
     * @param  string $url     The URL to send the request to
     * @param  array  $options Request options
     * @return Response The HTTP response
     */
    public function post(string $url, array $options = []): Response;

    /**
     * Send a PUT request
     *
     * @param  string $url     The URL to send the request to
     * @param  array  $options Request options
     * @return Response The HTTP response
     */
    public function put(string $url, array $options = []): Response;

    /**
     * Send a PATCH request
     *
     * @param  string $url     The URL to send the request to
     * @param  array  $options Request options
     * @return Response The HTTP response
     */
    public function patch(string $url, array $options = []): Response;

    /**
     * Send a DELETE request
     *
     * @param  string $url     The URL to send the request to
     * @param  array  $options Request options
     * @return Response The HTTP response
     */
    public function delete(string $url, array $options = []): Response;

    /**
     * Send a HEAD request
     *
     * @param  string $url     The URL to send the request to
     * @param  array  $options Request options
     * @return Response The HTTP response
     */
    public function head(string $url, array $options = []): Response;

    /**
     * Send an OPTIONS request
     *
     * @param  string $url     The URL to send the request to
     * @param  array  $options Request options
     * @return Response The HTTP response
     */
    public function options(string $url, array $options = []): Response;

    /**
     * Send a custom request
     *
     * @param  Request $request The request object
     * @return Response The HTTP response
     */
    public function send(Request $request): Response;

    /**
     * Send multiple requests asynchronously
     *
     * @param  array $requests Array of Request objects
     * @return array Array of Response objects
     */
    public function sendAsync(array $requests): array;

    /**
     * Set default options for all requests
     *
     * @param  array $options Default options
     * @return self
     */
    public function setDefaultOptions(array $options): self;

    /**
     * Add middleware to the client
     *
     * @param  MiddlewareInterface $middleware The middleware to add
     * @return self
     */
    public function addMiddleware(MiddlewareInterface $middleware): self;

    /**
     * Remove middleware from the client
     *
     * @param  string $middlewareClass The middleware class to remove
     * @return self
     */
    public function removeMiddleware(string $middlewareClass): self;
}
