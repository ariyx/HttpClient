<?php

declare(strict_types=1);

namespace Ariyx\HttpClient\Middleware;

use Ariyx\HttpClient\Contracts\MiddlewareInterface;
use Ariyx\HttpClient\Request;
use Ariyx\HttpClient\Response;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Logging Middleware
 *
 * Middleware that logs requests and responses.
 *
 * @package Ariyx\HttpClient\Middleware
 * @author  Armin Malekzadeh <arixologist@gmail.com>
 * @version 2.0.0
 */
class LoggingMiddleware implements MiddlewareInterface
{
    private LoggerInterface $logger;
    private string $requestLogLevel;
    private string $responseLogLevel;
    private bool $logHeaders;
    private bool $logBody;
    private int $maxBodyLength;

    public function __construct(
        LoggerInterface $logger,
        string $requestLogLevel = LogLevel::INFO,
        string $responseLogLevel = LogLevel::INFO,
        bool $logHeaders = true,
        bool $logBody = false,
        int $maxBodyLength = 1000
    ) {
        $this->logger = $logger;
        $this->requestLogLevel = $requestLogLevel;
        $this->responseLogLevel = $responseLogLevel;
        $this->logHeaders = $logHeaders;
        $this->logBody = $logBody;
        $this->maxBodyLength = $maxBodyLength;
    }

    /**
     * Process the request and response
     */
    public function process(Request $request, callable $next): Response
    {
        $startTime = microtime(true);

        // Log the request
        $this->logRequest($request);

        // Execute the request
        $response = $next($request);

        // Calculate duration
        $duration = microtime(true) - $startTime;
        $response->setDuration($duration);

        // Log the response
        $this->logResponse($response, $duration);

        return $response;
    }

    /**
     * Get the middleware name
     */
    public function getName(): string
    {
        return 'logging';
    }

    /**
     * Log the request
     */
    private function logRequest(Request $request): void
    {
        $context = [
            'method' => $request->getMethod(),
            'url' => $request->getUrl(),
            'timeout' => $request->getTimeout(),
        ];

        if ($this->logHeaders && !empty($request->getHeaders())) {
            $context['headers'] = $request->getHeaders();
        }

        if ($this->logBody && $request->getBody() !== null) {
            $body = $request->getBody();
            if (is_string($body)) {
                $context['body'] = $this->truncateBody($body);
            } else {
                $context['body'] = $body;
            }
        }

        if (!empty($request->getQueryParams())) {
            $context['query_params'] = $request->getQueryParams();
        }

        $this->logger->log($this->requestLogLevel, 'HTTP Request', $context);
    }

    /**
     * Log the response
     */
    private function logResponse(Response $response, float $duration): void
    {
        $context = [
            'status_code' => $response->getStatusCode(),
            'duration' => round($duration * 1000, 2) . 'ms',
            'content_length' => $response->getContentLength(),
            'content_type' => $response->getContentType(),
        ];

        if ($this->logHeaders && !empty($response->getHeaders())) {
            $context['headers'] = $response->getHeaders();
        }

        if ($this->logBody && !empty($response->getBody())) {
            $context['body'] = $this->truncateBody($response->getBody());
        }

        $logLevel = $response->isError() ? LogLevel::ERROR : $this->responseLogLevel;

        $this->logger->log($logLevel, 'HTTP Response', $context);
    }

    /**
     * Truncate body if it's too long
     */
    private function truncateBody(string $body): string
    {
        if (strlen($body) <= $this->maxBodyLength) {
            return $body;
        }

        return substr($body, 0, $this->maxBodyLength) . '... (truncated)';
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
     * Get the request log level
     */
    public function getRequestLogLevel(): string
    {
        return $this->requestLogLevel;
    }

    /**
     * Set the request log level
     */
    public function setRequestLogLevel(string $requestLogLevel): self
    {
        $this->requestLogLevel = $requestLogLevel;
        return $this;
    }

    /**
     * Get the response log level
     */
    public function getResponseLogLevel(): string
    {
        return $this->responseLogLevel;
    }

    /**
     * Set the response log level
     */
    public function setResponseLogLevel(string $responseLogLevel): self
    {
        $this->responseLogLevel = $responseLogLevel;
        return $this;
    }

    /**
     * Should log headers
     */
    public function shouldLogHeaders(): bool
    {
        return $this->logHeaders;
    }

    /**
     * Set whether to log headers
     */
    public function setLogHeaders(bool $logHeaders): self
    {
        $this->logHeaders = $logHeaders;
        return $this;
    }

    /**
     * Should log body
     */
    public function shouldLogBody(): bool
    {
        return $this->logBody;
    }

    /**
     * Set whether to log body
     */
    public function setLogBody(bool $logBody): self
    {
        $this->logBody = $logBody;
        return $this;
    }

    /**
     * Get the maximum body length
     */
    public function getMaxBodyLength(): int
    {
        return $this->maxBodyLength;
    }

    /**
     * Set the maximum body length
     */
    public function setMaxBodyLength(int $maxBodyLength): self
    {
        $this->maxBodyLength = $maxBodyLength;
        return $this;
    }
}
