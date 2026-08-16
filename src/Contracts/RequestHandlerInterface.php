<?php

declare(strict_types=1);

namespace Ariyx\HttpClient\Contracts;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface RequestHandlerInterface
{
    public function handle(RequestInterface $request): ResponseInterface;
}
