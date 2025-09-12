<?php

declare(strict_types=1);

namespace Ariyx\HttpClient\Exceptions;

/**
 * Timeout Exception
 *
 * Exception thrown when a request times out.
 *
 * @package Ariyx\HttpClient\Exceptions
 * @author  Armin Malekzadeh <arixologist@gmail.com>
 * @version 2.0.0
 */
class TimeoutException extends HttpClientException
{
    protected int $timeout;

    public function __construct(
        string $message = '',
        int $timeout = 30,
        ?string $url = null,
        ?\Exception $previous = null
    ) {
        parent::__construct($message, 0, $previous, $url, null, [
            'timeout' => $timeout,
        ]);

        $this->timeout = $timeout;
    }

    /**
     * Get the timeout value that was exceeded
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * Create a timeout exception
     */
    public static function create(int $timeout, ?string $url = null): self
    {
        $message = sprintf('Request timed out after %d seconds', $timeout);
        return new self($message, $timeout, $url);
    }
}
