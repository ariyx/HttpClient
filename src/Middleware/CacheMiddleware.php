<?php

declare(strict_types=1);

namespace Ariyx\HttpClient\Middleware;

use Ariyx\HttpClient\Cache\CacheInterface;
use Ariyx\HttpClient\Contracts\MiddlewareInterface;
use Ariyx\HttpClient\Request;
use Ariyx\HttpClient\Response;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Cache Middleware
 *
 * Middleware that caches HTTP responses based on request characteristics.
 *
 * @package Ariyx\HttpClient\Middleware
 * @author  Armin Malekzadeh <arixologist@gmail.com>
 * @version 2.0.0
 */
class CacheMiddleware implements MiddlewareInterface
{
    private CacheInterface $cache;
    private int $defaultTtl;
    private array $cacheableMethods;
    private array $cacheableStatusCodes;
    private array $cacheHeaders;
    private LoggerInterface $logger;

    public function __construct(
        CacheInterface $cache,
        int $defaultTtl = 3600,
        array $cacheableMethods = ['GET', 'HEAD'],
        array $cacheableStatusCodes = [200, 203, 300, 301, 302, 304, 307, 308],
        array $cacheHeaders = ['Cache-Control', 'ETag', 'Last-Modified'],
        ?LoggerInterface $logger = null
    ) {
        $this->cache = $cache;
        $this->defaultTtl = $defaultTtl;
        $this->cacheableMethods = $cacheableMethods;
        $this->cacheableStatusCodes = $cacheableStatusCodes;
        $this->cacheHeaders = $cacheHeaders;
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * Process the request and response
     */
    public function process(Request $request, callable $next): Response
    {
        // Check if request is cacheable
        if (!$this->isCacheableRequest($request)) {
            return $next($request);
        }

        $cacheKey = $this->generateCacheKey($request);

        // Try to get from cache
        $cachedResponse = $this->getFromCache($cacheKey);
        if ($cachedResponse !== null) {
            $this->logger->info('Cache hit', [
                'url' => $request->getUrl(),
                'method' => $request->getMethod(),
                'cache_key' => $cacheKey,
            ]);

            return $cachedResponse;
        }

        // Execute request
        $response = $next($request);

        // Check if response is cacheable
        if ($this->isCacheableResponse($response)) {
            $ttl = $this->calculateTtl($response);
            $this->storeInCache($cacheKey, $response, $ttl);

            $this->logger->info('Response cached', [
                'url' => $request->getUrl(),
                'method' => $request->getMethod(),
                'cache_key' => $cacheKey,
                'ttl' => $ttl,
                'status_code' => $response->getStatusCode(),
            ]);
        }

        return $response;
    }

    /**
     * Get the middleware name
     */
    public function getName(): string
    {
        return 'cache';
    }

    /**
     * Check if the request is cacheable
     */
    private function isCacheableRequest(Request $request): bool
    {
        // Check method
        if (!in_array($request->getMethod(), $this->cacheableMethods, true)) {
            return false;
        }

        // Check for cache-busting headers
        $headers = $request->getHeaders();
        if (isset($headers['Cache-Control']) && str_contains($headers['Cache-Control'], 'no-cache')) {
            return false;
        }

        return true;
    }

    /**
     * Check if the response is cacheable
     */
    private function isCacheableResponse(Response $response): bool
    {
        // Check status code
        if (!in_array($response->getStatusCode(), $this->cacheableStatusCodes, true)) {
            return false;
        }

        // Check cache headers
        $cacheControl = $response->getHeader('Cache-Control');
        if ($cacheControl !== null) {
            if (
                str_contains($cacheControl, 'no-cache') ||
                str_contains($cacheControl, 'no-store') ||
                str_contains($cacheControl, 'private')
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Generate cache key for the request
     */
    private function generateCacheKey(Request $request): string
    {
        $keyData = [
            'method' => $request->getMethod(),
            'url' => $request->getUrl(),
            'headers' => $this->getRelevantHeaders($request->getHeaders()),
            'body' => $request->getBody(),
        ];

        return 'http_client_' . hash('sha256', serialize($keyData));
    }

    /**
     * Get relevant headers for cache key generation
     */
    private function getRelevantHeaders(array $headers): array
    {
        $relevant = [];

        foreach ($this->cacheHeaders as $header) {
            $headerLower = strtolower($header);
            foreach ($headers as $name => $value) {
                if (strtolower($name) === $headerLower) {
                    $relevant[$name] = $value;
                }
            }
        }

        return $relevant;
    }

    /**
     * Get response from cache
     */
    private function getFromCache(string $cacheKey): ?Response
    {
        $cached = $this->cache->get($cacheKey);

        if ($cached === null) {
            return null;
        }

        if (!is_array($cached) || !isset($cached['response'])) {
            return null;
        }

        // Recreate response object
        $data = $cached['response'];
        $response = new Response(
            $data['status_code'],
            $data['headers'],
            $data['body'],
            $data['info'] ?? []
        );

        return $response;
    }

    /**
     * Store response in cache
     */
    private function storeInCache(string $cacheKey, Response $response, int $ttl): void
    {
        $cacheData = [
            'response' => [
                'status_code' => $response->getStatusCode(),
                'headers' => $response->getHeaders(),
                'body' => $response->getBody(),
                'info' => $response->getInfo(),
            ],
            'cached_at' => time(),
        ];

        $this->cache->set($cacheKey, $cacheData, $ttl);
    }

    /**
     * Calculate TTL from response headers
     */
    private function calculateTtl(Response $response): int
    {
        // Check Cache-Control max-age
        $cacheControl = $response->getHeader('Cache-Control');
        if ($cacheControl !== null && preg_match('/max-age=(\d+)/', $cacheControl, $matches)) {
            return (int) $matches[1];
        }

        // Check Expires header
        $expires = $response->getHeader('Expires');
        if ($expires !== null) {
            $expiresTime = strtotime($expires);
            if ($expiresTime !== false) {
                $ttl = $expiresTime - time();
                return max(0, $ttl);
            }
        }

        return $this->defaultTtl;
    }

    /**
     * Get the cache instance
     */
    public function getCache(): CacheInterface
    {
        return $this->cache;
    }

    /**
     * Set the cache instance
     */
    public function setCache(CacheInterface $cache): self
    {
        $this->cache = $cache;
        return $this;
    }

    /**
     * Get default TTL
     */
    public function getDefaultTtl(): int
    {
        return $this->defaultTtl;
    }

    /**
     * Set default TTL
     */
    public function setDefaultTtl(int $ttl): self
    {
        $this->defaultTtl = $ttl;
        return $this;
    }

    /**
     * Get cacheable methods
     */
    public function getCacheableMethods(): array
    {
        return $this->cacheableMethods;
    }

    /**
     * Set cacheable methods
     */
    public function setCacheableMethods(array $methods): self
    {
        $this->cacheableMethods = $methods;
        return $this;
    }

    /**
     * Get cacheable status codes
     */
    public function getCacheableStatusCodes(): array
    {
        return $this->cacheableStatusCodes;
    }

    /**
     * Set cacheable status codes
     */
    public function setCacheableStatusCodes(array $statusCodes): self
    {
        $this->cacheableStatusCodes = $statusCodes;
        return $this;
    }

    /**
     * Get cache headers
     */
    public function getCacheHeaders(): array
    {
        return $this->cacheHeaders;
    }

    /**
     * Set cache headers
     */
    public function setCacheHeaders(array $headers): self
    {
        $this->cacheHeaders = $headers;
        return $this;
    }
}
