<?php

declare(strict_types=1);

namespace Ariyx\HttpClient\Auth;

use Ariyx\HttpClient\Contracts\AuthenticationInterface;
use Ariyx\HttpClient\Request;

/**
 * API Key Authentication
 *
 * Implements API Key Authentication with configurable header name and location.
 *
 * @package Ariyx\HttpClient\Auth
 * @author  Armin Malekzadeh <arixologist@gmail.com>
 * @version 2.0.0
 */
class ApiKeyAuth implements AuthenticationInterface
{
    public const LOCATION_HEADER = 'header';
    public const LOCATION_QUERY = 'query';

    private string $apiKey;
    private string $name;
    private string $location;

    public function __construct(
        string $apiKey,
        string $name = 'X-API-Key',
        string $location = self::LOCATION_HEADER
    ) {
        $this->apiKey = $apiKey;
        $this->name = $name;
        $this->location = $location;
    }

    /**
     * Authenticate the request
     */
    public function authenticate(Request $request): Request
    {
        if ($this->location === self::LOCATION_HEADER) {
            $request->addHeader($this->name, $this->apiKey);
        } elseif ($this->location === self::LOCATION_QUERY) {
            $request->addQueryParam($this->name, $this->apiKey);
        }

        return $request;
    }

    /**
     * Get the authentication type
     */
    public function getType(): string
    {
        return 'API Key';
    }

    /**
     * Get the API key
     */
    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * Set the API key
     */
    public function setApiKey(string $apiKey): self
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    /**
     * Get the parameter name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set the parameter name
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get the location
     */
    public function getLocation(): string
    {
        return $this->location;
    }

    /**
     * Set the location
     */
    public function setLocation(string $location): self
    {
        $this->location = $location;
        return $this;
    }
}
