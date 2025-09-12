<?php

declare(strict_types=1);

/**
 * Middleware Example
 *
 * This example demonstrates the middleware system of the Ariyx HTTP Client.
 *
 * @package Ariyx\HttpClient\Examples
 * @author  Armin Malekzadeh <arixologist@gmail.com>
 * @version 2.0.0
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Ariyx\HttpClient\HttpClient;
use Ariyx\HttpClient\Middleware\RetryMiddleware;
use Ariyx\HttpClient\Middleware\RateLimitMiddleware;
use Ariyx\HttpClient\Middleware\LoggingMiddleware;
use Ariyx\HttpClient\Middleware\CacheMiddleware;
use Ariyx\HttpClient\Cache\FileCache;
use Ariyx\HttpClient\Request;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

echo "=== Ariyx HTTP Client - Middleware Example ===\n\n";

// Create logger
$logger = new Logger('http-client');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::INFO));

// Create HTTP client with logger
$client = new HttpClient([], $logger);

echo "1. Adding Logging Middleware...\n";
$loggingMiddleware = new LoggingMiddleware(
    logger: $logger,
    requestLogLevel: 'info',
    responseLogLevel: 'info',
    logHeaders: true,
    logBody: false,
    maxBodyLength: 500
);
$client->addMiddleware($loggingMiddleware);

echo "2. Adding Retry Middleware...\n";
$retryMiddleware = new RetryMiddleware(
    maxRetries: 3,
    baseDelay: 1000,
    backoffMultiplier: 2.0,
    maxDelay: 5000,
    retryableStatusCodes: [500, 502, 503, 504]
);
$client->addMiddleware($retryMiddleware);

echo "3. Adding Rate Limiting Middleware...\n";
$rateLimitMiddleware = new RateLimitMiddleware(
    maxRequests: 5,  // 5 requests
    timeWindow: 10   // per 10 seconds
);
$client->addMiddleware($rateLimitMiddleware);

echo "4. Adding Caching Middleware...\n";
$cache = new FileCache(sys_get_temp_dir() . '/http-client-cache');
$cacheMiddleware = new CacheMiddleware(
    cache: $cache,
    defaultTtl: 30,  // 30 seconds cache
    cacheableMethods: ['GET', 'HEAD'],
    cacheableStatusCodes: [200, 203, 300, 301, 302, 304, 307, 308]
);
$client->addMiddleware($cacheMiddleware);

echo "\n5. Sending requests with middleware...\n";

// First request - should be cached
echo "First request (will be cached):\n";
try {
    $response = $client->get('https://httpbin.org/get');
    echo "Status: " . $response->getStatusCode() . "\n";
    echo "Duration: " . round($response->getDuration() * 1000, 2) . "ms\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Second request - should use cache
echo "\nSecond request (should use cache):\n";
try {
    $response = $client->get('https://httpbin.org/get');
    echo "Status: " . $response->getStatusCode() . "\n";
    echo "Duration: " . round($response->getDuration() * 1000, 2) . "ms\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Test rate limiting
echo "\n6. Testing rate limiting (sending multiple requests quickly):\n";
for ($i = 1; $i <= 7; $i++) {
    echo "Request $i: ";
    try {
        $startTime = microtime(true);
        $response = $client->get('https://httpbin.org/delay/1');
        $endTime = microtime(true);
        echo "Status: " . $response->getStatusCode() . ", Duration: " . round(($endTime - $startTime) * 1000, 2) . "ms\n";
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}

echo "\n7. Testing retry mechanism with failing endpoint:\n";
try {
    // This will fail and trigger retries
    $response = $client->get('https://httpbin.org/status/500');
    echo "Status: " . $response->getStatusCode() . "\n";
} catch (Exception $e) {
    echo "Error after retries: " . $e->getMessage() . "\n";
}

echo "\n8. Middleware information:\n";
$middleware = $client->getMiddleware();
echo "Number of middleware: " . count($middleware) . "\n";
foreach ($middleware as $mw) {
    echo "- " . $mw->getName() . "\n";
}

echo "\n9. Cache statistics:\n";
$stats = $cache->getStats();
echo "Total files: " . $stats['total_files'] . "\n";
echo "Valid files: " . $stats['valid_files'] . "\n";
echo "Expired files: " . $stats['expired_files'] . "\n";
echo "Total size: " . $stats['total_size_mb'] . " MB\n";

echo "\n10. Cleaning up cache...\n";
$cleaned = $cache->cleanExpired();
echo "Cleaned $cleaned expired cache entries\n";

echo "\n=== Middleware example completed ===\n";
