<?php

declare(strict_types=1);

namespace Ariyx\HttpClient\Config;

/**
 * HTTP Client Configuration
 *
 * Manages configuration for the HTTP client including default options,
 * middleware settings, and other client-wide configurations.
 *
 * @package Ariyx\HttpClient\Config
 * @author  Armin Malekzadeh <arixologist@gmail.com>
 * @version 2.0.0
 */
class HttpClientConfig
{
    private array $defaultOptions = [];
    private array $middlewareConfig = [];
    private array $retryConfig = [];
    private array $rateLimitConfig = [];
    private array $loggingConfig = [];
    private array $cacheConfig = [];
    private string $userAgent = 'Ariyx HttpClient 2.0.0';
    private int $defaultTimeout = 30;
    private int $defaultConnectTimeout = 30;
    private bool $verifySSL = true;
    private bool $followRedirects = true;
    private int $maxRedirects = 10;

    public function __construct(array $config = [])
    {
        $this->loadFromArray($config);
    }

    /**
     * Load configuration from array
     */
    public function loadFromArray(array $config): self
    {
        if (isset($config['default_options'])) {
            $this->setDefaultOptions($config['default_options']);
        }

        if (isset($config['middleware'])) {
            $this->setMiddlewareConfig($config['middleware']);
        }

        if (isset($config['retry'])) {
            $this->setRetryConfig($config['retry']);
        }

        if (isset($config['rate_limit'])) {
            $this->setRateLimitConfig($config['rate_limit']);
        }

        if (isset($config['logging'])) {
            $this->setLoggingConfig($config['logging']);
        }

        if (isset($config['cache'])) {
            $this->setCacheConfig($config['cache']);
        }

        if (isset($config['user_agent'])) {
            $this->setUserAgent($config['user_agent']);
        }

        if (isset($config['timeout'])) {
            $this->setDefaultTimeout($config['timeout']);
        }

        if (isset($config['connect_timeout'])) {
            $this->setDefaultConnectTimeout($config['connect_timeout']);
        }

        if (isset($config['verify_ssl'])) {
            $this->setVerifySSL($config['verify_ssl']);
        }

        if (isset($config['follow_redirects'])) {
            $this->setFollowRedirects($config['follow_redirects']);
        }

        if (isset($config['max_redirects'])) {
            $this->setMaxRedirects($config['max_redirects']);
        }

        return $this;
    }

    /**
     * Get default cURL options
     */
    public function getDefaultOptions(): array
    {
        return array_merge([
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => $this->followRedirects,
            CURLOPT_MAXREDIRS => $this->maxRedirects,
            CURLOPT_CONNECTTIMEOUT => $this->defaultConnectTimeout,
            CURLOPT_TIMEOUT => $this->defaultTimeout,
            CURLOPT_SSL_VERIFYPEER => $this->verifySSL,
            CURLOPT_SSL_VERIFYHOST => $this->verifySSL ? 2 : 0,
            CURLOPT_USERAGENT => $this->userAgent,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        ], $this->defaultOptions);
    }

    /**
     * Set default options
     */
    public function setDefaultOptions(array $options): self
    {
        $this->defaultOptions = $options;
        return $this;
    }

    /**
     * Add a default option
     */
    public function addDefaultOption(int $option, mixed $value): self
    {
        $this->defaultOptions[$option] = $value;
        return $this;
    }

    /**
     * Get middleware configuration
     */
    public function getMiddlewareConfig(): array
    {
        return $this->middlewareConfig;
    }

    /**
     * Set middleware configuration
     */
    public function setMiddlewareConfig(array $config): self
    {
        $this->middlewareConfig = $config;
        return $this;
    }

    /**
     * Get retry configuration
     */
    public function getRetryConfig(): array
    {
        return array_merge([
            'max_retries' => 3,
            'base_delay' => 1000,
            'backoff_multiplier' => 2.0,
            'max_delay' => 10000,
            'retryable_status_codes' => [408, 429, 500, 502, 503, 504],
        ], $this->retryConfig);
    }

    /**
     * Set retry configuration
     */
    public function setRetryConfig(array $config): self
    {
        $this->retryConfig = $config;
        return $this;
    }

    /**
     * Get rate limit configuration
     */
    public function getRateLimitConfig(): array
    {
        return array_merge([
            'max_requests' => 100,
            'time_window' => 60,
        ], $this->rateLimitConfig);
    }

    /**
     * Set rate limit configuration
     */
    public function setRateLimitConfig(array $config): self
    {
        $this->rateLimitConfig = $config;
        return $this;
    }

    /**
     * Get logging configuration
     */
    public function getLoggingConfig(): array
    {
        return array_merge([
            'enabled' => true,
            'request_log_level' => 'info',
            'response_log_level' => 'info',
            'log_headers' => true,
            'log_body' => false,
            'max_body_length' => 1000,
        ], $this->loggingConfig);
    }

    /**
     * Set logging configuration
     */
    public function setLoggingConfig(array $config): self
    {
        $this->loggingConfig = $config;
        return $this;
    }

    /**
     * Get cache configuration
     */
    public function getCacheConfig(): array
    {
        return array_merge([
            'enabled' => false,
            'ttl' => 3600,
            'driver' => 'file',
            'path' => sys_get_temp_dir() . '/http-client-cache',
        ], $this->cacheConfig);
    }

    /**
     * Set cache configuration
     */
    public function setCacheConfig(array $config): self
    {
        $this->cacheConfig = $config;
        return $this;
    }

    /**
     * Get user agent
     */
    public function getUserAgent(): string
    {
        return $this->userAgent;
    }

    /**
     * Set user agent
     */
    public function setUserAgent(string $userAgent): self
    {
        $this->userAgent = $userAgent;
        return $this;
    }

    /**
     * Get default timeout
     */
    public function getDefaultTimeout(): int
    {
        return $this->defaultTimeout;
    }

    /**
     * Set default timeout
     */
    public function setDefaultTimeout(int $timeout): self
    {
        $this->defaultTimeout = $timeout;
        return $this;
    }

    /**
     * Get default connect timeout
     */
    public function getDefaultConnectTimeout(): int
    {
        return $this->defaultConnectTimeout;
    }

    /**
     * Set default connect timeout
     */
    public function setDefaultConnectTimeout(int $timeout): self
    {
        $this->defaultConnectTimeout = $timeout;
        return $this;
    }

    /**
     * Should verify SSL
     */
    public function shouldVerifySSL(): bool
    {
        return $this->verifySSL;
    }

    /**
     * Set verify SSL
     */
    public function setVerifySSL(bool $verify): self
    {
        $this->verifySSL = $verify;
        return $this;
    }

    /**
     * Should follow redirects
     */
    public function shouldFollowRedirects(): bool
    {
        return $this->followRedirects;
    }

    /**
     * Set follow redirects
     */
    public function setFollowRedirects(bool $follow): self
    {
        $this->followRedirects = $follow;
        return $this;
    }

    /**
     * Get max redirects
     */
    public function getMaxRedirects(): int
    {
        return $this->maxRedirects;
    }

    /**
     * Set max redirects
     */
    public function setMaxRedirects(int $maxRedirects): self
    {
        $this->maxRedirects = $maxRedirects;
        return $this;
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'default_options' => $this->defaultOptions,
            'middleware' => $this->middlewareConfig,
            'retry' => $this->retryConfig,
            'rate_limit' => $this->rateLimitConfig,
            'logging' => $this->loggingConfig,
            'cache' => $this->cacheConfig,
            'user_agent' => $this->userAgent,
            'timeout' => $this->defaultTimeout,
            'connect_timeout' => $this->defaultConnectTimeout,
            'verify_ssl' => $this->verifySSL,
            'follow_redirects' => $this->followRedirects,
            'max_redirects' => $this->maxRedirects,
        ];
    }

    /**
     * Create from file
     */
    public static function fromFile(string $filePath): self
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("Configuration file not found: {$filePath}");
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        switch ($extension) {
            case 'json':
                $config = json_decode(file_get_contents($filePath), true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \InvalidArgumentException('Invalid JSON configuration file');
                }
                break;

            case 'php':
                $config = require $filePath;
                if (!is_array($config)) {
                    throw new \InvalidArgumentException('PHP configuration file must return an array');
                }
                break;

            default:
                throw new \InvalidArgumentException("Unsupported configuration file format: {$extension}");
        }

        return new self($config);
    }

    /**
     * Save to file
     */
    public function saveToFile(string $filePath): self
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $config = $this->toArray();

        switch ($extension) {
            case 'json':
                $content = json_encode($config, JSON_PRETTY_PRINT);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \RuntimeException('Failed to encode configuration as JSON');
                }
                break;

            case 'php':
                $content = "<?php\n\nreturn " . var_export($config, true) . ";\n";
                break;

            default:
                throw new \InvalidArgumentException("Unsupported configuration file format: {$extension}");
        }

        if (file_put_contents($filePath, $content) === false) {
            throw new \RuntimeException("Failed to write configuration file: {$filePath}");
        }

        return $this;
    }
}
