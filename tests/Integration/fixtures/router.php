<?php

declare(strict_types=1);

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url(is_string($requestUri) ? $requestUri : '/', PHP_URL_PATH);
if ($path === '/redirect') {
    header('Location: /ok', true, 302);
    exit;
}
if ($path === '/missing') {
    http_response_code(404);
    echo 'missing';
    exit;
}
$method = $_SERVER['REQUEST_METHOD'] ?? '';
header('X-Reply: local');
header('X-Request-Echo: ' . (is_string($_SERVER['HTTP_X_REQUEST'] ?? null) ? $_SERVER['HTTP_X_REQUEST'] : ''));
header('Set-Cookie: first=1', false);
header('Set-Cookie: second=2', false);
echo (is_string($method) ? $method : '') . ':' . file_get_contents('php://input');
