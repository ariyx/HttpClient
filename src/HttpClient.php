<?php

namespace Ariyx;

use Ariyx\Logger;

/**
 * HTTP client class for sending HTTP/HTTPS requests.
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
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ];
        $this->cookieFile = $cookieFile;
        $this->timeout = $timeout;
        $this->logger = $logger ?? new Logger();
    }

    public function addHeader(string $header, string $value): void
    {
        $this->headers[$header] = $value;
    }

    public function addOption(int $option, mixed $value): void
    {
        $this->options[$option] = $value;
    }

    public function setCookieFile(string $cookieFile): void
    {
        $this->cookieFile = $cookieFile;
    }

    public function get(array $params = [], ?callable $callback = null): string
    {
        $url = $this->buildUrlWithParams($params);
        $ch = curl_init($url);
        $this->setCommonOptions($ch);
        curl_setopt($ch, CURLOPT_HTTPGET, true);

        $this->log('Sending GET request to ' . $url, 'DEBUG');

        return $this->executeRequest($ch, $callback);
    }

    public function post(array $data, ?callable $callback = null): string
    {
        $ch = curl_init($this->url);
        $this->setCommonOptions($ch);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $this->log('Sending POST request to ' . $this->url, 'DEBUG');

        return $this->executeRequest($ch, $callback);
    }

    public function put(array $data, ?callable $callback = null): string
    {
        $ch = curl_init($this->url);
        $this->setCommonOptions($ch);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $this->log('Sending PUT request to ' . $this->url, 'DEBUG');

        return $this->executeRequest($ch, $callback);
    }

    public function delete(?callable $callback = null): string
    {
        $ch = curl_init($this->url);
        $this->setCommonOptions($ch);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");

        $this->log('Sending DELETE request to ' . $this->url, 'DEBUG');

        return $this->executeRequest($ch, $callback);
    }

    public function patch(array $data, ?callable $callback = null): string
    {
        $ch = curl_init($this->url);
        $this->setCommonOptions($ch);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $this->log('Sending PATCH request to ' . $this->url, 'DEBUG');

        return $this->executeRequest($ch, $callback);
    }

    public function head(?callable $callback = null): string
    {
        $ch = curl_init($this->url);
        $this->setCommonOptions($ch);
        curl_setopt($ch, CURLOPT_NOBODY, true);

        $this->log('Sending HEAD request to ' . $this->url, 'DEBUG');

        return $this->executeRequest($ch, $callback);
    }

    public function options(?callable $callback = null): string
    {
        $ch = curl_init($this->url);
        $this->setCommonOptions($ch);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "OPTIONS");

        $this->log('Sending OPTIONS request to ' . $this->url, 'DEBUG');

        return $this->executeRequest($ch, $callback);
    }

    private function buildUrlWithParams(array $params): string
    {
        if (!empty($params)) {
            $queryString = http_build_query($params);
            return $this->url . '?' . $queryString;
        }

        return $this->url;
    }

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

    private function executeRequest($ch, ?callable $callback = null): string
    {
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $error_message = curl_error($ch);
            $this->log("Curl error: " . $error_message, 'ERROR');
        }

        $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->log("Response HTTP status code: " . $httpStatusCode, 'DEBUG');

        if ($callback && is_callable($callback)) {
            return call_user_func($callback, $response);
        }

        return $response;
    }

    private function log(string $message, string $level = 'INFO'): void
    {
        $this->logger->log($message, $level);
    }
}
