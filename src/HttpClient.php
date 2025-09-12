<?php

declare(strict_types=1);

namespace Ariyx\HttpClient;

use Ariyx\HttpClient\Contracts\HttpClientInterface;
use Ariyx\HttpClient\Contracts\MiddlewareInterface;
use Ariyx\HttpClient\Exceptions\ConnectionException;
use Ariyx\HttpClient\Exceptions\RequestException;
use Ariyx\HttpClient\Exceptions\TimeoutException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Advanced HTTP Client
 *
 * A modern, feature-rich HTTP client with middleware support, authentication,
 * retry mechanisms, and comprehensive error handling.
 *
 * @package Ariyx\HttpClient
 * @author  Armin Malekzadeh <arixologist@gmail.com>
 * @version 2.0.0
 */
class HttpClient implements HttpClientInterface
{
    private array $defaultOptions = [];
    private array $middleware = [];
    private LoggerInterface $logger;

    public function __construct(
        array $defaultOptions = [],
        ?LoggerInterface $logger = null
    ) {
        $this->defaultOptions = array_merge([
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_USERAGENT => 'Ariyx HttpClient 2.0.0',
        ], $defaultOptions);

        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * Send a GET request
     */
    public function get(string $url, array $options = []): Response
    {
        return $this->send(Request::get($url, $options));
    }

    /**
     * Send a POST request
     */
    public function post(string $url, array $options = []): Response
    {
        return $this->send(Request::post($url, $options));
    }

    /**
     * Send a PUT request
     */
    public function put(string $url, array $options = []): Response
    {
        return $this->send(Request::put($url, $options));
    }

    /**
     * Send a PATCH request
     */
    public function patch(string $url, array $options = []): Response
    {
        return $this->send(Request::patch($url, $options));
    }

    /**
     * Send a DELETE request
     */
    public function delete(string $url, array $options = []): Response
    {
        return $this->send(Request::delete($url, $options));
    }

    /**
     * Send a HEAD request
     */
    public function head(string $url, array $options = []): Response
    {
        return $this->send(Request::head($url, $options));
    }

    /**
     * Send an OPTIONS request
     */
    public function options(string $url, array $options = []): Response
    {
        return $this->send(Request::options($url, $options));
    }

    /**
     * Send a custom request
     */
    public function send(Request $request): Response
    {
        // Apply authentication if set
        if ($request->getAuthentication()) {
            $request = $request->getAuthentication()->authenticate($request);
        }

        // Apply middleware
        $response = $this->executeWithMiddleware($request);

        // Check for HTTP errors
        if ($response->isError()) {
            throw RequestException::fromResponse($response, $request);
        }

        return $response;
    }

    /**
     * Send multiple requests asynchronously
     */
    public function sendAsync(array $requests): array
    {
        if (empty($requests)) {
            return [];
        }

        $multiHandle = curl_multi_init();
        $handles = [];
        $responses = [];
        $startTime = microtime(true);

        // Initialize all requests
        foreach ($requests as $index => $request) {
            if (!$request instanceof Request) {
                throw new \InvalidArgumentException('All requests must be Request instances');
            }

            // Apply authentication if set
            if ($request->getAuthentication()) {
                $request = $request->getAuthentication()->authenticate($request);
            }

            $ch = $this->createCurlHandle($request);
            curl_multi_add_handle($multiHandle, $ch);
            $handles[$index] = ['handle' => $ch, 'request' => $request];
        }

        // Execute all requests
        $running = null;
        do {
            curl_multi_exec($multiHandle, $running);
            if ($running > 0) {
                curl_multi_select($multiHandle);
            }
        } while ($running > 0);

        // Process responses
        foreach ($handles as $index => $handleData) {
            $ch = $handleData['handle'];
            $request = $handleData['request'];

            $response = $this->createResponseFromCurl($ch, $request);
            $response->setDuration(microtime(true) - $startTime);

            $responses[$index] = $response;

            curl_multi_remove_handle($multiHandle, $ch);
            curl_close($ch);
        }

        curl_multi_close($multiHandle);

        return $responses;
    }

    /**
     * Set default options for all requests
     */
    public function setDefaultOptions(array $options): self
    {
        $this->defaultOptions = array_merge($this->defaultOptions, $options);
        return $this;
    }

    /**
     * Add middleware to the client
     */
    public function addMiddleware(MiddlewareInterface $middleware): self
    {
        $this->middleware[] = $middleware;
        return $this;
    }

    /**
     * Remove middleware from the client
     */
    public function removeMiddleware(string $middlewareClass): self
    {
        $this->middleware = array_filter($this->middleware, function ($middleware) use ($middlewareClass) {
            return !($middleware instanceof $middlewareClass);
        });
        return $this;
    }

    /**
     * Execute request with middleware
     */
    private function executeWithMiddleware(Request $request): Response
    {
        if (empty($this->middleware)) {
            return $this->executeRequest($request);
        }

        $middleware = array_reverse($this->middleware);
        $next = function (Request $req) {
            return $this->executeRequest($req);
        };

        foreach ($middleware as $mw) {
            $next = function (Request $req) use ($mw, $next) {
                return $mw->process($req, $next);
            };
        }

        return $next($request);
    }

    /**
     * Execute a single request
     */
    private function executeRequest(Request $request): Response
    {
        $ch = $this->createCurlHandle($request);

        $startTime = microtime(true);
        $response = curl_exec($ch);
        $duration = microtime(true) - $startTime;

        if ($response === false) {
            $errorCode = curl_errno($ch);
            $errorMessage = curl_error($ch);
            curl_close($ch);

            if ($errorCode === CURLE_OPERATION_TIMEOUTED) {
                throw TimeoutException::create($request->getTimeout(), $request->getUrl());
            }

            throw ConnectionException::fromCurlError($errorCode, $errorMessage, $request->getUrl());
        }

        $httpResponse = $this->createResponseFromCurl($ch, $request);
        $httpResponse->setDuration($duration);

        curl_close($ch);

        return $httpResponse;
    }

    /**
     * Create a cURL handle for the request
     */
    private function createCurlHandle(Request $request): \CurlHandle
    {
        $url = $request->buildUrl();
        $ch = curl_init($url);

        // Set default options - only set known valid options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Ariyx HttpClient 2.0.0');

        // Set request-specific options
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $request->getMethod());
        curl_setopt($ch, CURLOPT_TIMEOUT, $request->getTimeout());
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $request->shouldFollowRedirects());
        curl_setopt($ch, CURLOPT_MAXREDIRS, $request->getMaxRedirects());
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $request->shouldVerifySSL());
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $request->shouldVerifySSL() ? 2 : 0);

        // Set headers
        if (!empty($request->getHeaders())) {
            $headers = [];
            foreach ($request->getHeaders() as $name => $value) {
                $headers[] = $name . ': ' . $value;
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        // Set body for methods that support it
        if (in_array($request->getMethod(), ['POST', 'PUT', 'PATCH'], true) && $request->getBody() !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request->getBody());
        }

        // Set custom options
        foreach ($request->getOptions() as $option => $value) {
            if (is_int($option)) {
                curl_setopt($ch, $option, $value);
            }
        }

        return $ch;
    }

    /**
     * Create a Response object from cURL handle
     */
    private function createResponseFromCurl(\CurlHandle $ch, Request $request): Response
    {
        $body = curl_multi_getcontent($ch) ?: curl_exec($ch);
        $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $info = curl_getinfo($ch);

        // Parse headers
        $headers = [];
        if (isset($info['request_header'])) {
            $headerLines = explode("\r\n", $info['request_header']);
            foreach ($headerLines as $line) {
                if (strpos($line, ':') !== false) {
                    [$name, $value] = explode(':', $line, 2);
                    $headers[trim($name)] = trim($value);
                }
            }
        }

        $response = new Response($statusCode, $headers, $body, $info);
        $response->setRequest($request);

        return $response;
    }

    /**
     * Get the logger
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * Set the logger
     */
    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * Get all middleware
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    /**
     * Get default options
     */
    public function getDefaultOptions(): array
    {
        return $this->defaultOptions;
    }
}
