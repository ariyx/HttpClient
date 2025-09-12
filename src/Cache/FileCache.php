<?php

declare(strict_types=1);

namespace Ariyx\HttpClient\Cache;

/**
 * File Cache Implementation
 *
 * Simple file-based cache implementation.
 *
 * @package Ariyx\HttpClient\Cache
 * @author  Armin Malekzadeh <arixologist@gmail.com>
 * @version 2.0.0
 */
class FileCache implements CacheInterface
{
    private string $cacheDir;
    private int $defaultTtl;

    public function __construct(string $cacheDir, int $defaultTtl = 3600)
    {
        $this->cacheDir = rtrim($cacheDir, '/\\');
        $this->defaultTtl = $defaultTtl;

        if (!is_dir($this->cacheDir)) {
            if (!mkdir($this->cacheDir, 0755, true)) {
                throw new \RuntimeException("Failed to create cache directory: {$this->cacheDir}");
            }
        }
    }

    /**
     * Get a value from the cache
     */
    public function get(string $key): mixed
    {
        $filePath = $this->getFilePath($key);

        if (!file_exists($filePath)) {
            return null;
        }

        $data = $this->readCacheFile($filePath);

        if ($data === null) {
            return null;
        }

        // Check if expired
        if ($data['expires'] < time()) {
            $this->delete($key);
            return null;
        }

        return $data['value'];
    }

    /**
     * Set a value in the cache
     */
    public function set(string $key, mixed $value, int $ttl = 3600): bool
    {
        $filePath = $this->getFilePath($key);
        $data = [
            'value' => $value,
            'expires' => time() + $ttl,
            'created' => time(),
        ];

        return $this->writeCacheFile($filePath, $data);
    }

    /**
     * Delete a value from the cache
     */
    public function delete(string $key): bool
    {
        $filePath = $this->getFilePath($key);

        if (file_exists($filePath)) {
            return unlink($filePath);
        }

        return true;
    }

    /**
     * Check if a key exists in the cache
     */
    public function has(string $key): bool
    {
        $filePath = $this->getFilePath($key);

        if (!file_exists($filePath)) {
            return false;
        }

        $data = $this->readCacheFile($filePath);

        if ($data === null) {
            return false;
        }

        // Check if expired
        if ($data['expires'] < time()) {
            $this->delete($key);
            return false;
        }

        return true;
    }

    /**
     * Clear all cache entries
     */
    public function clear(): bool
    {
        $files = glob($this->cacheDir . '/*.cache');
        $success = true;

        foreach ($files as $file) {
            if (!unlink($file)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Get multiple values from the cache
     */
    public function getMultiple(array $keys): array
    {
        $result = [];

        foreach ($keys as $key) {
            $result[$key] = $this->get($key);
        }

        return $result;
    }

    /**
     * Set multiple values in the cache
     */
    public function setMultiple(array $values, int $ttl = 3600): bool
    {
        $success = true;

        foreach ($values as $key => $value) {
            if (!$this->set($key, $value, $ttl)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Delete multiple values from the cache
     */
    public function deleteMultiple(array $keys): bool
    {
        $success = true;

        foreach ($keys as $key) {
            if (!$this->delete($key)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Get the file path for a cache key
     */
    private function getFilePath(string $key): string
    {
        $hash = hash('sha256', $key);
        return $this->cacheDir . '/' . $hash . '.cache';
    }

    /**
     * Read cache file
     */
    private function readCacheFile(string $filePath): ?array
    {
        $content = file_get_contents($filePath);

        if ($content === false) {
            return null;
        }

        $data = unserialize($content);

        if ($data === false) {
            return null;
        }

        return $data;
    }

    /**
     * Write cache file
     */
    private function writeCacheFile(string $filePath, array $data): bool
    {
        $content = serialize($data);
        return file_put_contents($filePath, $content, LOCK_EX) !== false;
    }

    /**
     * Get cache directory
     */
    public function getCacheDir(): string
    {
        return $this->cacheDir;
    }

    /**
     * Get default TTL
     */
    public function getDefaultTtl(): int
    {
        return $this->defaultTtl;
    }

    /**
     * Set default TTL
     */
    public function setDefaultTtl(int $ttl): self
    {
        $this->defaultTtl = $ttl;
        return $this;
    }

    /**
     * Get cache statistics
     */
    public function getStats(): array
    {
        $files = glob($this->cacheDir . '/*.cache');
        $totalSize = 0;
        $expiredCount = 0;
        $validCount = 0;

        foreach ($files as $file) {
            $totalSize += filesize($file);
            $data = $this->readCacheFile($file);

            if ($data !== null) {
                if ($data['expires'] < time()) {
                    $expiredCount++;
                } else {
                    $validCount++;
                }
            }
        }

        return [
            'total_files' => count($files),
            'valid_files' => $validCount,
            'expired_files' => $expiredCount,
            'total_size' => $totalSize,
            'total_size_mb' => round($totalSize / 1024 / 1024, 2),
        ];
    }

    /**
     * Clean expired cache entries
     */
    public function cleanExpired(): int
    {
        $files = glob($this->cacheDir . '/*.cache');
        $cleaned = 0;

        foreach ($files as $file) {
            $data = $this->readCacheFile($file);

            if ($data !== null && $data['expires'] < time()) {
                if (unlink($file)) {
                    $cleaned++;
                }
            }
        }

        return $cleaned;
    }
}
