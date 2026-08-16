<?php

declare(strict_types=1);

namespace Ariyx\HttpClient\Exception;

use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Message\RequestInterface;

class RequestException extends ClientException implements RequestExceptionInterface
{
    public function __construct(string $message, private readonly RequestInterface $request, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}
