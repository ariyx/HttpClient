<?php

declare(strict_types=1);

namespace Ariyx\HttpClient\Middleware;

use Ariyx\HttpClient\Contracts\MiddlewareInterface;
use Ariyx\HttpClient\Exceptions\RequestException;
use Ariyx\HttpClient\Request;
use Ariyx\HttpClient\Response;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Retry Middleware
 *
 * Middleware that retries failed requests with exponential backoff.
 *
 * @package Ariyx\HttpClient\Middleware
 * @author  Armin Malekzadeh <arixologist@gmail.com>
 * @version 2.0.0
 */
class RetryMiddleware implements MiddlewareInterface
{
    private int $maxRetries;
    private int $baseDelay;
    private float $backoffMultiplier;
    private int $maxDelay;
    private array $retryableStatusCodes;
    private LoggerInterface $logger;

    public function __construct(
        int $maxRetries = 3,
        int $baseDelay = 1000,
        float $backoffMultiplier = 2.0,
        int $maxDelay = 10000,
        array $retryableStatusCodes = [408, 429, 500, 502, 503, 504],
        ?LoggerInterface $logger = null
    ) {
        $this->maxRetries = $maxRetries;
        $this->baseDelay = $baseDelay;
        $this->backoffMultiplier = $backoffMultiplier;
        $this->maxDelay = $maxDelay;
        $this->retryableStatusCodes = $retryableStatusCodes;
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * Process the request and response
     */
    public function process(Request $request, callable $next): Response
    {
        $attempt = 0;
        $lastException = null;

        while ($attempt <= $this->maxRetries) {
            try {
                $response = $next($request);

                // If successful or not retryable, return the response
                if ($response->isSuccessful() || !$this->shouldRetry($response)) {
                    if ($attempt > 0) {
                        $this->logger->info('Request succeeded after retry', [
                            'url' => $request->getUrl(),
                            'attempt' => $attempt + 1,
                            'status_code' => $response->getStatusCode(),
                        ]);
                    }
                    return $response;
                }

                // If this is the last attempt, return the response
                if ($attempt === $this->maxRetries) {
                    $this->logger->warning('Request failed after all retries', [
                        'url' => $request->getUrl(),
                        'attempts' => $attempt + 1,
                        'status_code' => $response->getStatusCode(),
                    ]);
                    return $response;
                }
            } catch (RequestException $e) {
                $lastException = $e;

                // If this is the last attempt, throw the exception
                if ($attempt === $this->maxRetries) {
                    $this->logger->error('Request failed after all retries', [
                        'url' => $request->getUrl(),
                        'attempts' => $attempt + 1,
                        'error' => $e->getMessage(),
                    ]);
                    throw $e;
                }
            }

            $attempt++;
            $delay = $this->calculateDelay($attempt);

            $this->logger->info('Retrying request', [
                'url' => $request->getUrl(),
                'attempt' => $attempt + 1,
                'delay_ms' => $delay,
            ]);

            usleep($delay * 1000); // Convert to microseconds
        }

        // This should never be reached, but just in case
        if ($lastException) {
            throw $lastException;
        }

        throw new RequestException('Request failed after all retries');
    }

    /**
     * Get the middleware name
     */
    public function getName(): string
    {
        return 'retry';
    }

    /**
     * Check if the response should be retried
     */
    private function shouldRetry(Response $response): bool
    {
        return in_array($response->getStatusCode(), $this->retryableStatusCodes, true);
    }

    /**
     * Calculate the delay for the next retry
     */
    private function calculateDelay(int $attempt): int
    {
        $delay = (int) ($this->baseDelay * pow($this->backoffMultiplier, $attempt - 1));
        return min($delay, $this->maxDelay);
    }

    /**
     * Get the maximum number of retries
     */
    public function getMaxRetries(): int
    {
        return $this->maxRetries;
    }

    /**
     * Set the maximum number of retries
     */
    public function setMaxRetries(int $maxRetries): self
    {
        $this->maxRetries = $maxRetries;
        return $this;
    }

    /**
     * Get the base delay in milliseconds
     */
    public function getBaseDelay(): int
    {
        return $this->baseDelay;
    }

    /**
     * Set the base delay in milliseconds
     */
    public function setBaseDelay(int $baseDelay): self
    {
        $this->baseDelay = $baseDelay;
        return $this;
    }

    /**
     * Get the backoff multiplier
     */
    public function getBackoffMultiplier(): float
    {
        return $this->backoffMultiplier;
    }

    /**
     * Set the backoff multiplier
     */
    public function setBackoffMultiplier(float $backoffMultiplier): self
    {
        $this->backoffMultiplier = $backoffMultiplier;
        return $this;
    }

    /**
     * Get the maximum delay in milliseconds
     */
    public function getMaxDelay(): int
    {
        return $this->maxDelay;
    }

    /**
     * Set the maximum delay in milliseconds
     */
    public function setMaxDelay(int $maxDelay): self
    {
        $this->maxDelay = $maxDelay;
        return $this;
    }

    /**
     * Get the retryable status codes
     */
    public function getRetryableStatusCodes(): array
    {
        return $this->retryableStatusCodes;
    }

    /**
     * Set the retryable status codes
     */
    public function setRetryableStatusCodes(array $retryableStatusCodes): self
    {
        $this->retryableStatusCodes = $retryableStatusCodes;
        return $this;
    }
}
