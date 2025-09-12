<?php

declare(strict_types=1);

namespace Ariyx\HttpClient\Auth;

use Ariyx\HttpClient\Contracts\AuthenticationInterface;
use Ariyx\HttpClient\Request;

/**
 * Basic Authentication
 *
 * Implements HTTP Basic Authentication.
 *
 * @package Ariyx\HttpClient\Auth
 * @author  Armin Malekzadeh <arixologist@gmail.com>
 * @version 2.0.0
 */
class BasicAuth implements AuthenticationInterface
{
    private string $username;
    private string $password;

    public function __construct(string $username, string $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Authenticate the request
     */
    public function authenticate(Request $request): Request
    {
        $credentials = base64_encode($this->username . ':' . $this->password);
        $request->addHeader('Authorization', 'Basic ' . $credentials);

        return $request;
    }

    /**
     * Get the authentication type
     */
    public function getType(): string
    {
        return 'Basic';
    }

    /**
     * Get the username
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * Get the password
     */
    public function getPassword(): string
    {
        return $this->password;
    }
}
