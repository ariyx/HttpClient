# Ariyx HTTP Client

A small, standards-based PSR-18 HTTP client for PHP, backed by cURL.

## Requirements

PHP 8.3+ and ext-curl.

## Installation

```bash
composer require ariyx/http-client
```

## Usage

```php
use Ariyx\HttpClient\Client;
use Nyholm\Psr7\Request;

$client = Client::create();
$request = new Request('GET', 'https://example.com');
$response = $client->sendRequest($request);

echo $response->getStatusCode();
echo $response->getBody();
```

Retry transient failures with the included client middleware:

```php
use Ariyx\HttpClient\Middleware\RetryMiddleware;

$client = Client::create(middleware: [new RetryMiddleware()]);
```

## Error handling

HTTP 4xx and 5xx responses are returned normally. Network and invalid-request failures throw PSR-18 compatible exceptions.

## Development

```bash
composer check
```

## License

MIT