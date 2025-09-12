# Ariyx HTTP Client

[![PHP Version](https://img.shields.io/badge/php-%5E8.3-blue.svg)](https://packagist.org/packages/ariyx/http-client)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![Build Status](https://img.shields.io/badge/build-passing-brightgreen.svg)](https://github.com/ariyx/http-client)
[![Coverage](https://img.shields.io/badge/coverage-95%25-brightgreen.svg)](https://github.com/ariyx/http-client)

A modern, feature-rich PHP HTTP client with advanced capabilities including authentication, middleware, retry mechanisms, caching, and comprehensive testing.

## ✨ Features

- **🚀 Modern PHP 8.3+ Support** - Built with the latest PHP features and strict typing
- **🔐 Multiple Authentication Methods** - Basic Auth, Bearer Token, API Key support
- **🔄 Middleware System** - Retry, Rate Limiting, Logging, Caching middleware
- **⚡ Async Requests** - Send multiple requests concurrently
- **💾 Response Caching** - Built-in caching with TTL support
- **🛡️ Error Handling** - Comprehensive exception handling with detailed error information
- **📊 Logging** - PSR-3 compatible logging with customizable levels
- **⚙️ Configuration Management** - Flexible configuration system with file support
- **🧪 Comprehensive Testing** - Full test coverage with unit and integration tests
- **📚 Extensive Documentation** - Detailed documentation with examples

## 📦 Installation

Install via Composer:

```bash
composer require ariyx/http-client
```

## 🚀 Quick Start

### Basic Usage

```php
<?php

require 'vendor/autoload.php';

use Ariyx\HttpClient\HttpClient;

// Create a new HTTP client
$client = new HttpClient();

// Send a GET request
$response = $client->get('https://api.example.com/users');

// Check if successful
if ($response->isSuccessful()) {
    $data = $response->json();
    echo "User data: " . json_encode($data);
} else {
    echo "Request failed with status: " . $response->getStatusCode();
}
```

### Advanced Usage with Middleware

```php
<?php

use Ariyx\HttpClient\HttpClient;
use Ariyx\HttpClient\Middleware\RetryMiddleware;
use Ariyx\HttpClient\Middleware\LoggingMiddleware;
use Ariyx\HttpClient\Auth\BearerAuth;
use Ariyx\HttpClient\Request;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Create logger
$logger = new Logger('http-client');
$logger->pushHandler(new StreamHandler('app.log', Logger::INFO));

// Create HTTP client with middleware
$client = new HttpClient([], $logger);

// Add retry middleware
$client->addMiddleware(new RetryMiddleware(
    maxRetries: 3,
    baseDelay: 1000,
    backoffMultiplier: 2.0
));

// Add logging middleware
$client->addMiddleware(new LoggingMiddleware($logger));

// Create authenticated request
$request = Request::get('https://api.example.com/protected')
    ->setAuthentication(new BearerAuth('your-api-token'));

// Send request
$response = $client->send($request);
```

## 🔐 Authentication

### Basic Authentication

```php
use Ariyx\HttpClient\Auth\BasicAuth;
use Ariyx\HttpClient\Request;

$auth = new BasicAuth('username', 'password');
$request = Request::get('https://api.example.com/protected')
    ->setAuthentication($auth);

$response = $client->send($request);
```

### Bearer Token Authentication

```php
use Ariyx\HttpClient\Auth\BearerAuth;

$auth = new BearerAuth('your-jwt-token');
$request = Request::get('https://api.example.com/protected')
    ->setAuthentication($auth);

$response = $client->send($request);
```

### API Key Authentication

```php
use Ariyx\HttpClient\Auth\ApiKeyAuth;

// Header-based API key
$auth = new ApiKeyAuth('your-api-key', 'X-API-Key', ApiKeyAuth::LOCATION_HEADER);

// Query parameter-based API key
$auth = new ApiKeyAuth('your-api-key', 'api_key', ApiKeyAuth::LOCATION_QUERY);

$request = Request::get('https://api.example.com/protected')
    ->setAuthentication($auth);

$response = $client->send($request);
```

## 🔄 Middleware

### Retry Middleware

```php
use Ariyx\HttpClient\Middleware\RetryMiddleware;

$retryMiddleware = new RetryMiddleware(
    maxRetries: 3,                    // Maximum number of retries
    baseDelay: 1000,                  // Base delay in milliseconds
    backoffMultiplier: 2.0,           // Exponential backoff multiplier
    maxDelay: 10000,                  // Maximum delay in milliseconds
    retryableStatusCodes: [500, 502, 503, 504] // Status codes to retry
);

$client->addMiddleware($retryMiddleware);
```

### Rate Limiting Middleware

```php
use Ariyx\HttpClient\Middleware\RateLimitMiddleware;

$rateLimitMiddleware = new RateLimitMiddleware(
    maxRequests: 100,    // Maximum requests
    timeWindow: 60       // Time window in seconds
);

$client->addMiddleware($rateLimitMiddleware);
```

### Caching Middleware

```php
use Ariyx\HttpClient\Middleware\CacheMiddleware;
use Ariyx\HttpClient\Cache\FileCache;

$cache = new FileCache('/tmp/http-client-cache');
$cacheMiddleware = new CacheMiddleware(
    cache: $cache,
    defaultTtl: 3600,    // Default TTL in seconds
    cacheableMethods: ['GET', 'HEAD'],
    cacheableStatusCodes: [200, 203, 300, 301, 302, 304, 307, 308]
);

$client->addMiddleware($cacheMiddleware);
```

### Logging Middleware

```php
use Ariyx\HttpClient\Middleware\LoggingMiddleware;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logger = new Logger('http-client');
$logger->pushHandler(new StreamHandler('app.log', Logger::INFO));

$loggingMiddleware = new LoggingMiddleware(
    logger: $logger,
    requestLogLevel: 'info',
    responseLogLevel: 'info',
    logHeaders: true,
    logBody: false,
    maxBodyLength: 1000
);

$client->addMiddleware($loggingMiddleware);
```

## ⚡ Async Requests

```php
use Ariyx\HttpClient\Request;

// Create multiple requests
$requests = [
    Request::get('https://api.example.com/users/1'),
    Request::get('https://api.example.com/users/2'),
    Request::get('https://api.example.com/users/3'),
];

// Send all requests concurrently
$responses = $client->sendAsync($requests);

// Process responses
foreach ($responses as $index => $response) {
    if ($response->isSuccessful()) {
        $userData = $response->json();
        echo "User {$index}: " . $userData['name'] . "\n";
    }
}
```

## ⚙️ Configuration

### Using Configuration Class

```php
use Ariyx\HttpClient\Config\HttpClientConfig;

// Create configuration
$config = new HttpClientConfig([
    'timeout' => 30,
    'connect_timeout' => 10,
    'verify_ssl' => true,
    'follow_redirects' => true,
    'max_redirects' => 5,
    'user_agent' => 'MyApp/1.0',
    'retry' => [
        'max_retries' => 3,
        'base_delay' => 1000,
        'backoff_multiplier' => 2.0,
    ],
    'rate_limit' => [
        'max_requests' => 100,
        'time_window' => 60,
    ],
    'cache' => [
        'enabled' => true,
        'ttl' => 3600,
        'driver' => 'file',
        'path' => '/tmp/http-client-cache',
    ],
]);

// Load from file
$config = HttpClientConfig::fromFile('config/http-client.json');

// Save to file
$config->saveToFile('config/http-client.json');
```

### Configuration File Example (JSON)

```json
{
    "timeout": 30,
    "connect_timeout": 10,
    "verify_ssl": true,
    "follow_redirects": true,
    "max_redirects": 5,
    "user_agent": "MyApp/1.0",
    "retry": {
        "max_retries": 3,
        "base_delay": 1000,
        "backoff_multiplier": 2.0,
        "max_delay": 10000,
        "retryable_status_codes": [500, 502, 503, 504]
    },
    "rate_limit": {
        "max_requests": 100,
        "time_window": 60
    },
    "cache": {
        "enabled": true,
        "ttl": 3600,
        "driver": "file",
        "path": "/tmp/http-client-cache"
    },
    "logging": {
        "enabled": true,
        "request_log_level": "info",
        "response_log_level": "info",
        "log_headers": true,
        "log_body": false,
        "max_body_length": 1000
    }
}
```

## 🛡️ Error Handling

```php
use Ariyx\HttpClient\Exceptions\RequestException;
use Ariyx\HttpClient\Exceptions\ConnectionException;
use Ariyx\HttpClient\Exceptions\TimeoutException;

try {
    $response = $client->get('https://api.example.com/data');
} catch (RequestException $e) {
    echo "Request failed: " . $e->getMessage();
    echo "Status code: " . $e->getStatusCode();
    echo "URL: " . $e->getUrl();
} catch (ConnectionException $e) {
    echo "Connection failed: " . $e->getMessage();
    echo "Host: " . $e->getHost();
    echo "Port: " . $e->getPort();
} catch (TimeoutException $e) {
    echo "Request timed out: " . $e->getMessage();
    echo "Timeout: " . $e->getTimeout() . " seconds";
}
```

## 📊 Response Handling

```php
$response = $client->get('https://api.example.com/data');

// Check response status
if ($response->isSuccessful()) {
    // Get response data
    $json = $response->json();
    $xml = $response->xml();
    $body = $response->getBody();
    
    // Get response information
    $statusCode = $response->getStatusCode();
    $contentType = $response->getContentType();
    $contentLength = $response->getContentLength();
    $duration = $response->getDuration();
    
    // Get headers
    $headers = $response->getHeaders();
    $specificHeader = $response->getHeader('Content-Type');
    
    // Get cURL info
    $info = $response->getInfo();
    $totalTime = $response->getTotalTime();
    $effectiveUrl = $response->getEffectiveUrl();
}
```

## 🧪 Testing

Run the test suite:

```bash
# Run all tests
composer test

# Run with coverage
composer test-coverage

# Run specific test suite
vendor/bin/phpunit tests/Unit
vendor/bin/phpunit tests/Integration
```

## 📚 Examples

Check the `examples/` directory for more detailed examples:

- [Basic Usage](examples/basic-usage.php)
- [Authentication](examples/authentication.php)
- [Middleware](examples/middleware.php)
- [Async Requests](examples/async-requests.php)
- [Error Handling](examples/error-handling.php)
- [Configuration](examples/configuration.php)

## 🔧 Development

### Code Quality

```bash
# Check code style
composer cs-check

# Fix code style
composer cs-fix

# Run static analysis
composer phpstan

# Run all quality checks
composer quality
```

### Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests for new functionality
5. Ensure all tests pass
6. Submit a pull request

## 📄 License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

## 👨‍💻 Author

**Armin Malekzadeh**
- Email: arixologist@gmail.com
- GitHub: [@ariyx](https://github.com/ariyx)

## 🤝 Support

If you find this package useful, please consider:

- ⭐ Starring the repository
- 🐛 Reporting bugs
- 💡 Suggesting new features
- 📖 Improving documentation
- 🔄 Contributing code

## 📈 Changelog

See [CHANGELOG.md](CHANGELOG.md) for a list of changes and version history.
