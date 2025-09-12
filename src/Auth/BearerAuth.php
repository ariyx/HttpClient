<?php

declare(strict_types=1);

namespace Ariyx\HttpClient\Auth;

use Ariyx\HttpClient\Contracts\AuthenticationInterface;
use Ariyx\HttpClient\Request;

/**
 * Bearer Token Authentication
 *
 * Implements HTTP Bearer Token Authentication.
 *
 * @package Ariyx\HttpClient\Auth
 * @author  Armin Malekzadeh <arixologist@gmail.com>
 * @version 2.0.0
 */
class BearerAuth implements AuthenticationInterface
{
    private string $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    /**
     * Authenticate the request
     */
    public function authenticate(Request $request): Request
    {
        $request->addHeader('Authorization', 'Bearer ' . $this->token);
        return $request;
    }

    /**
     * Get the authentication type
     */
    public function getType(): string
    {
        return 'Bearer';
    }

    /**
     * Get the token
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * Set the token
     */
    public function setToken(string $token): self
    {
        $this->token = $token;
        return $this;
    }
}
