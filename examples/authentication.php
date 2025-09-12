<?php

/**
 * Authentication Example
 *
 * This example demonstrates different authentication methods with the Ariyx HTTP Client.
 *
 * @package Ariyx\HttpClient\Examples
 * @author  Armin Malekzadeh <arixologist@gmail.com>
 * @version 2.0.0
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Ariyx\HttpClient\HttpClient;
use Ariyx\HttpClient\Auth\BasicAuth;
use Ariyx\HttpClient\Auth\BearerAuth;
use Ariyx\HttpClient\Auth\ApiKeyAuth;
use Ariyx\HttpClient\Request;

echo "=== Ariyx HTTP Client - Authentication Example ===\n\n";

$client = new HttpClient();

echo "1. Basic Authentication...\n";
try {
    $auth = new BasicAuth('user', 'pass');
    $request = Request::get('https://httpbin.org/basic-auth/user/pass')
        ->setAuthentication($auth);

    $response = $client->send($request);

    if ($response->isSuccessful()) {
        echo "✅ Basic authentication successful!\n";
        $data = $response->json();
        echo "Authenticated: " . ($data['authenticated'] ? 'Yes' : 'No') . "\n";
        echo "User: " . $data['user'] . "\n";
    } else {
        echo "❌ Basic authentication failed with status: " . $response->getStatusCode() . "\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n2. Bearer Token Authentication...\n";
try {
    $auth = new BearerAuth('test-token-123');
    $request = Request::get('https://httpbin.org/bearer')
        ->setAuthentication($auth);

    $response = $client->send($request);

    if ($response->isSuccessful()) {
        echo "✅ Bearer authentication successful!\n";
        $data = $response->json();
        echo "Authenticated: " . ($data['authenticated'] ? 'Yes' : 'No') . "\n";
        echo "Token: " . $data['token'] . "\n";
    } else {
        echo "❌ Bearer authentication failed with status: " . $response->getStatusCode() . "\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n3. API Key Authentication (Header)...\n";
try {
    $auth = new ApiKeyAuth('my-api-key-123', 'X-API-Key', ApiKeyAuth::LOCATION_HEADER);
    $request = Request::get('https://httpbin.org/headers')
        ->setAuthentication($auth);

    $response = $client->send($request);

    if ($response->isSuccessful()) {
        echo "✅ API Key authentication (header) successful!\n";
        $data = $response->json();
        echo "API Key in headers: " . $data['headers']['X-Api-Key'] . "\n";
    } else {
        echo "❌ API Key authentication failed with status: " . $response->getStatusCode() . "\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n4. API Key Authentication (Query Parameter)...\n";
try {
    $auth = new ApiKeyAuth('my-api-key-456', 'api_key', ApiKeyAuth::LOCATION_QUERY);
    $request = Request::get('https://httpbin.org/get')
        ->setAuthentication($auth);

    $response = $client->send($request);

    if ($response->isSuccessful()) {
        echo "✅ API Key authentication (query) successful!\n";
        $data = $response->json();
        echo "API Key in query: " . $data['args']['api_key'] . "\n";
    } else {
        echo "❌ API Key authentication failed with status: " . $response->getStatusCode() . "\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n5. Custom Authentication with Multiple Headers...\n";
try {
    $request = Request::get('https://httpbin.org/headers')
        ->addHeader('X-Custom-Auth', 'custom-token')
        ->addHeader('X-Client-ID', 'my-client-123')
        ->addHeader('X-API-Version', 'v2');

    $response = $client->send($request);

    if ($response->isSuccessful()) {
        echo "✅ Custom authentication successful!\n";
        $data = $response->json();
        echo "Custom headers sent:\n";
        echo "  X-Custom-Auth: " . $data['headers']['X-Custom-Auth'] . "\n";
        echo "  X-Client-Id: " . $data['headers']['X-Client-Id'] . "\n";
        echo "  X-Api-Version: " . $data['headers']['X-Api-Version'] . "\n";
    } else {
        echo "❌ Custom authentication failed with status: " . $response->getStatusCode() . "\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Authentication example completed ===\n";
