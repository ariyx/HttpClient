<?php

declare(strict_types=1);

namespace Ariyx\HttpClient\Contracts;

use Ariyx\HttpClient\Request;

/**
 * Authentication Interface
 *
 * Defines the contract for authentication implementations.
 *
 * @package Ariyx\HttpClient\Contracts
 * @author  Armin Malekzadeh <arixologist@gmail.com>
 * @version 2.0.0
 */
interface AuthenticationInterface
{
    /**
     * Authenticate the request
     *
     * @param  Request $request The HTTP request to authenticate
     * @return Request The authenticated request
     */
    public function authenticate(Request $request): Request;

    /**
     * Get the authentication type
     *
     * @return string The authentication type
     */
    public function getType(): string;
}
