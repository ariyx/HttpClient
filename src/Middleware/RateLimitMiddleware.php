<?php

declare(strict_types=1);

namespace Ariyx\HttpClient\Middleware;

use Ariyx\HttpClient\Contracts\MiddlewareInterface;
use Ariyx\HttpClient\Request;
use Ariyx\HttpClient\Response;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Rate Limit Middleware
 *
 * Middleware that implements rate limiting for requests.
 *
 * @package Ariyx\HttpClient\Middleware
 * @author  Armin Malekzadeh <arixologist@gmail.com>
 * @version 2.0.0
 */
class RateLimitMiddleware implements MiddlewareInterface
{
    private int $maxRequests;
    private int $timeWindow;
    private array $requestTimes = [];
    private LoggerInterface $logger;

    public function __construct(
        int $maxRequests = 100,
        int $timeWindow = 60,
        ?LoggerInterface $logger = null
    ) {
        $this->maxRequests = $maxRequests;
        $this->timeWindow = $timeWindow;
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * Process the request and response
     */
    public function process(Request $request, callable $next): Response
    {
        $this->cleanOldRequests();

        if ($this->isRateLimited()) {
            $waitTime = $this->calculateWaitTime();

            $this->logger->warning('Rate limit exceeded, waiting', [
                'wait_time' => $waitTime,
                'max_requests' => $this->maxRequests,
                'time_window' => $this->timeWindow,
            ]);

            usleep($waitTime * 1000); // Convert to microseconds
        }

        $this->recordRequest();

        return $next($request);
    }

    /**
     * Get the middleware name
     */
    public function getName(): string
    {
        return 'rate_limit';
    }

    /**
     * Check if the request is rate limited
     */
    private function isRateLimited(): bool
    {
        return count($this->requestTimes) >= $this->maxRequests;
    }

    /**
     * Calculate the wait time until the next request can be made
     */
    private function calculateWaitTime(): int
    {
        if (empty($this->requestTimes)) {
            return 0;
        }

        $oldestRequest = min($this->requestTimes);
        $waitTime = ($oldestRequest + $this->timeWindow) - time();

        return max(0, $waitTime * 1000); // Convert to milliseconds
    }

    /**
     * Record a request timestamp
     */
    private function recordRequest(): void
    {
        $this->requestTimes[] = time();
    }

    /**
     * Clean old request timestamps
     */
    private function cleanOldRequests(): void
    {
        $cutoff = time() - $this->timeWindow;
        $this->requestTimes = array_filter($this->requestTimes, function ($timestamp) use ($cutoff) {
            return $timestamp > $cutoff;
        });
    }

    /**
     * Get the maximum number of requests
     */
    public function getMaxRequests(): int
    {
        return $this->maxRequests;
    }

    /**
     * Set the maximum number of requests
     */
    public function setMaxRequests(int $maxRequests): self
    {
        $this->maxRequests = $maxRequests;
        return $this;
    }

    /**
     * Get the time window in seconds
     */
    public function getTimeWindow(): int
    {
        return $this->timeWindow;
    }

    /**
     * Set the time window in seconds
     */
    public function setTimeWindow(int $timeWindow): self
    {
        $this->timeWindow = $timeWindow;
        return $this;
    }

    /**
     * Get the current request count
     */
    public function getCurrentRequestCount(): int
    {
        $this->cleanOldRequests();
        return count($this->requestTimes);
    }

    /**
     * Reset the rate limiter
     */
    public function reset(): void
    {
        $this->requestTimes = [];
    }
}
