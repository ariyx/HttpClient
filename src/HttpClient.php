<?php

namespace Ariyx;

use Ariyx\Logger;

/**
 * HTTP client class for sending HTTP/HTTPS requests.
 *
 * This class provides methods to perform synchronous and asynchronous HTTP/HTTPS requests using cURL.
 *
 * @category HTTP Client
 * @package Ariyx
 * @author Armin Malekzadeh <arixologist@gmail.com>
 * @version 1.1
 */
class HttpClient
{
    private readonly string $url;
    private array $headers;
    private array $options;
    private ?string $cookieFile;
    private readonly int $timeout;
    private Logger $logger;

    /**
     * Constructor to initialize HttpClient with required parameters.
     *
     * @param string $url Base URL for requests.
     * @param array $headers Array of HTTP headers.
     * @param array $options Array of cURL options.
     * @param string|null $cookieFile Path to cookie file.
     * @param int $timeout Timeout for requests.
     * @param Logger|null $logger Logger instance for logging.
     */
    public function __construct(
        string $url,
        array $headers = [],
        array $options = [],
        ?string $cookieFile = null,
        int $timeout = 60,
        Logger $logger = null
    ) {
        $this->url = $url;
        $this->headers = $headers;
        $this->options = $options + [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ];
        $this->cookieFile = $cookieFile;
        $this->timeout = $timeout;
        $this->logger = $logger ?? new Logger();
    }

    /**
     * Add a new HTTP header.
     *
     * @param string $header Header name.
     * @param string $value Header value.
     */
    public function addHeader(string $header, string $value): void
    {
        $this->headers[$header] = $value;
    }

    /**
     * Add a new cURL option.
     *
     * @param int $option cURL option.
     * @param mixed $value Option value.
     */
    public function addOption(int $option, mixed $value): void
    {
        $this->options[$option] = $value;
    }

    /**
     * Set a file path for cookie storage.
     *
     * @param string $cookieFile Path to cookie file.
     */
    public function setCookieFile(string $cookieFile): void
    {
        $this->cookieFile = $cookieFile;
    }

    /**
     * Send a GET request.
     *
     * @param array $params Query parameters.
     * @param callable|null $callback Optional callback for processing response.
     * @return string Response body.
     */
    public function get(array $params = [],  ? callable $callback = null) : string
    {
        $url = $this->buildUrlWithParams($params);
        $ch = curl_init($url);
        $this->setCommonOptions($ch);
        curl_setopt($ch, CURLOPT_HTTPGET, true);

        $this->log('Sending GET request to ' . $url, 'DEBUG');

        return $this->executeRequest($ch, $callback);
    }

    /**
     * Send a POST request.
     *
     * @param array $data POST data.
     * @param callable|null $callback Optional callback for processing response.
     * @return string Response body.
     */
    public function post(array $data,  ? callable $callback = null) : string
    {
        $ch = curl_init($this->url);
        $this->setCommonOptions($ch);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $this->log('Sending POST request to ' . $this->url, 'DEBUG');

        return $this->executeRequest($ch, $callback);
    }

    /**
     * Send a PUT request.
     *
     * @param array $data PUT data.
     * @param callable|null $callback Optional callback for processing response.
     * @return string Response body.
     */
    public function put(array $data,  ? callable $callback = null) : string
    {
        $ch = curl_init($this->url);
        $this->setCommonOptions($ch);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $this->log('Sending PUT request to ' . $this->url, 'DEBUG');

        return $this->executeRequest($ch, $callback);
    }

    /**
     * Send a DELETE request.
     *
     * @param callable|null $callback Optional callback for processing response.
     * @return string Response body.
     */
    public function delete( ? callable $callback = null) : string
    {
        $ch = curl_init($this->url);
        $this->setCommonOptions($ch);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");

        $this->log('Sending DELETE request to ' . $this->url, 'DEBUG');

        return $this->executeRequest($ch, $callback);
    }

    /**
     * Send a PATCH request.
     *
     * @param array $data PATCH data.
     * @param callable|null $callback Optional callback for processing response.
     * @return string Response body.
     */
    public function patch(array $data,  ? callable $callback = null) : string
    {
        $ch = curl_init($this->url);
        $this->setCommonOptions($ch);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $this->log('Sending PATCH request to ' . $this->url, 'DEBUG');

        return $this->executeRequest($ch, $callback);
    }

    /**
     * Send a HEAD request.
     *
     * @param callable|null $callback Optional callback for processing response.
     * @return string Response body.
     */
    public function head( ? callable $callback = null) : string
    {
        $ch = curl_init($this->url);
        $this->setCommonOptions($ch);
        curl_setopt($ch, CURLOPT_NOBODY, true);

        $this->log('Sending HEAD request to ' . $this->url, 'DEBUG');

        return $this->executeRequest($ch, $callback);
    }

    /**
     * Send an OPTIONS request.
     *
     * @param callable|null $callback Optional callback for processing response.
     * @return string Response body.
     */
    public function options( ? callable $callback = null) : string
    {
        $ch = curl_init($this->url);
        $this->setCommonOptions($ch);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "OPTIONS");

        $this->log('Sending OPTIONS request to ' . $this->url, 'DEBUG');

        return $this->executeRequest($ch, $callback);
    }

    /**
     * Send multiple asynchronous requests.
     *
     * This method allows sending multiple requests asynchronously and returns their responses.
     *
     * @param array $requests Array of requests, where each request is an associative array with 'url' and optional 'options'.
     * @return array Array of responses from each request.
     */public function asyncRequests(array $requests): array
{
    $multiHandle = curl_multi_init();
    $handles = [];
    $responses = [];
    $startTime = microtime(true);

    foreach ($requests as $i => $request) {
        $ch = curl_init($request['url']);
        $this->setCommonOptions($ch);

        if (isset($request['options'])) {
            curl_setopt_array($ch, $request['options']);
        }

        curl_multi_add_handle($multiHandle, $ch);
        $handles[$i] = $ch;
    }

    $running = null;
    do {
        curl_multi_exec($multiHandle, $running);
        if ($running > 0) {
            curl_multi_select($multiHandle);
        }
    } while ($running > 0);

    foreach ($handles as $i => $ch) {
        $response = curl_multi_getcontent($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($httpCode >= 400) {
            $this->log("Request $i failed with status code: $httpCode", 'ERROR');
        }

        if (curl_errno($ch)) {
            $error_message = curl_error($ch);
            $this->log("Request $i failed with curl error: " . $error_message, 'ERROR');
        }

        $responses[$i] = $response;
        curl_multi_remove_handle($multiHandle, $ch);
        curl_close($ch);
    }

    curl_multi_close($multiHandle);

    $duration = microtime(true) - $startTime;
    $this->log('Total duration for async requests: ' . $duration . ' seconds', 'DEBUG');

    return $responses;
}


    /**
     * Build a URL with query parameters.
     *
     * @param array $params Array of query parameters.
     * @return string Full URL with query parameters.
     */
    private function buildUrlWithParams(array $params): string
    {
        if (!empty($params)) {
            $queryString = http_build_query($params);
            return $this->url . '?' . $queryString;
        }

        return $this->url;
    }

    /**
     * Set common cURL options for the request.
     *
     * @param resource $ch cURL handle.
     */
    private function setCommonOptions($ch): void
    {
        curl_setopt_array($ch, $this->options);

        $headerArr = [];
        foreach ($this->headers as $header => $value) {
            $headerArr[] = $header . ': ' . $value;
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArr);

        if ($this->cookieFile !== null) {
            curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookieFile);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFile);
        }

        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
    }

    /**
     * Execute a cURL request and handle errors.
     *
     * @param resource $ch cURL handle.
     * @param callable|null $callback Optional callback for processing response.
     * @return string Response body.
     */
    private function executeRequest($ch,  ? callable $callback = null) : string
    {
        $response = curl_exec($ch);

        // Check for cURL errors
        if (curl_errno($ch)) {
            $error_message = curl_error($ch);
            $this->log("cURL error: " . $error_message, 'ERROR');
            $response = ''; // Ensure an empty string is returned on error
        }

        $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->log("Response HTTP status code: " . $httpStatusCode, 'DEBUG');

        if ($callback && is_callable($callback)) {
            return call_user_func($callback, $response);
        }

        return $response;
    }

    /**
     * Log messages with different severity levels.
     *
     * @param string $message Message to log.
     * @param string $level Log level (e.g., INFO, DEBUG, ERROR).
     */
    private function log(string $message, string $level = 'INFO'): void
    {
        $this->logger->log($message, $level);
    }
}