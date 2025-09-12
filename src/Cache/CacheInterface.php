<?php

declare(strict_types=1);

namespace Ariyx\HttpClient\Cache;

/**
 * Cache Interface
 *
 * Defines the contract for cache implementations.
 *
 * @package Ariyx\HttpClient\Cache
 * @author  Armin Malekzadeh <arixologist@gmail.com>
 * @version 2.0.0
 */
interface CacheInterface
{
    /**
     * Get a value from the cache
     *
     * @param  string $key The cache key
     * @return mixed|null The cached value or null if not found
     */
    public function get(string $key): mixed;

    /**
     * Set a value in the cache
     *
     * @param  string  $key   The cache key
     * @param  mixed   $value The value to cache
     * @param  integer $ttl   Time to live in seconds
     * @return boolean True on success, false on failure
     */
    public function set(string $key, mixed $value, int $ttl = 3600): bool;

    /**
     * Delete a value from the cache
     *
     * @param  string $key The cache key
     * @return boolean True on success, false on failure
     */
    public function delete(string $key): bool;

    /**
     * Check if a key exists in the cache
     *
     * @param  string $key The cache key
     * @return boolean True if the key exists, false otherwise
     */
    public function has(string $key): bool;

    /**
     * Clear all cache entries
     *
     * @return boolean True on success, false on failure
     */
    public function clear(): bool;

    /**
     * Get multiple values from the cache
     *
     * @param  array $keys Array of cache keys
     * @return array Associative array of key => value pairs
     */
    public function getMultiple(array $keys): array;

    /**
     * Set multiple values in the cache
     *
     * @param  array   $values Associative array of key => value pairs
     * @param  integer $ttl    Time to live in seconds
     * @return boolean True on success, false on failure
     */
    public function setMultiple(array $values, int $ttl = 3600): bool;

    /**
     * Delete multiple values from the cache
     *
     * @param  array $keys Array of cache keys
     * @return boolean True on success, false on failure
     */
    public function deleteMultiple(array $keys): bool;
}
