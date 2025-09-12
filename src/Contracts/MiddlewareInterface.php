<?php

declare(strict_types=1);

namespace Ariyx\HttpClient\Contracts;

use Ariyx\HttpClient\Request;
use Ariyx\HttpClient\Response;

/**
 * Middleware Interface
 *
 * Defines the contract for HTTP middleware implementations.
 *
 * @package Ariyx\HttpClient\Contracts
 * @author  Armin Malekzadeh <arixologist@gmail.com>
 * @version 2.0.0
 */
interface MiddlewareInterface
{
    /**
     * Process the request and response
     *
     * @param  Request  $request The HTTP request
     * @param  callable $next    The next middleware in the chain
     * @return Response The HTTP response
     */
    public function process(Request $request, callable $next): Response;

    /**
     * Get the middleware name
     *
     * @return string The middleware name
     */
    public function getName(): string;
}
