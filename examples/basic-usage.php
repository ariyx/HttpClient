<?php

/**
 * Basic Usage Example
 *
 * This example demonstrates the basic usage of the Ariyx HTTP Client.
 *
 * @package Ariyx\HttpClient\Examples
 * @author  Armin Malekzadeh <arixologist@gmail.com>
 * @version 2.0.0
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Ariyx\HttpClient\HttpClient;
use Ariyx\HttpClient\Request;

echo "=== Ariyx HTTP Client - Basic Usage Example ===\n\n";

// Create a new HTTP client
$client = new HttpClient();

echo "1. Sending a GET request...\n";
try {
    $response = $client->get('https://httpbin.org/get');

    if ($response->isSuccessful()) {
        echo "✅ Request successful!\n";
        echo "Status Code: " . $response->getStatusCode() . "\n";
        echo "Content Type: " . $response->getContentType() . "\n";
        echo "Response Time: " . round($response->getDuration() * 1000, 2) . "ms\n";

        $data = $response->json();
        echo "Response URL: " . $data['url'] . "\n";
    } else {
        echo "❌ Request failed with status: " . $response->getStatusCode() . "\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n2. Sending a POST request with JSON data...\n";
try {
    $postData = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'age' => 30
    ];

    $response = $client->post('https://httpbin.org/post', [
        'body' => json_encode($postData),
        'headers' => ['Content-Type' => 'application/json']
    ]);

    if ($response->isSuccessful()) {
        echo "✅ POST request successful!\n";
        $data = $response->json();
        echo "Sent data: " . json_encode($data['json']) . "\n";
    } else {
        echo "❌ POST request failed with status: " . $response->getStatusCode() . "\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n3. Using Request objects...\n";
try {
    $request = Request::get('https://httpbin.org/user-agent')
        ->addHeader('User-Agent', 'Ariyx-HTTP-Client/2.0.0')
        ->addQueryParam('format', 'json');

    $response = $client->send($request);

    if ($response->isSuccessful()) {
        echo "✅ Request with custom headers successful!\n";
        $data = $response->json();
        echo "User Agent: " . $data['user-agent'] . "\n";
    } else {
        echo "❌ Request failed with status: " . $response->getStatusCode() . "\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n4. Sending multiple async requests...\n";
try {
    $requests = [
        Request::get('https://httpbin.org/delay/1'),
        Request::get('https://httpbin.org/delay/1'),
        Request::get('https://httpbin.org/delay/1'),
    ];

    $startTime = microtime(true);
    $responses = $client->sendAsync($requests);
    $endTime = microtime(true);

    echo "✅ Async requests completed!\n";
    echo "Total time: " . round(($endTime - $startTime) * 1000, 2) . "ms\n";
    echo "Number of responses: " . count($responses) . "\n";

    foreach ($responses as $index => $response) {
        echo "Response " . ($index + 1) . ": " . $response->getStatusCode() . "\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Example completed ===\n";