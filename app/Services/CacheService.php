<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CacheService
{
    /**
     * Get data from cache or execute the callback and cache the result
     *
     * @param string $key Cache key
     * @param int $minutes Minutes to cache the data
     * @param callable $callback Function to execute if cache misses
     * @return mixed
     */
    public static function remember(string $key, int $minutes, callable $callback)
    {
        return Cache::remember($key, $minutes * 60, $callback);
    }

    /**
     * Get data from cache forever or execute the callback and cache the result
     *
     * @param string $key Cache key
     * @param callable $callback Function to execute if cache misses
     * @return mixed
     */
    public static function rememberForever(string $key, callable $callback)
    {
        return Cache::rememberForever($key, $callback);
    }

    /**
     * Forget a cached item
     *
     * @param string $key Cache key
     * @return bool
     */
    public static function forget(string $key)
    {
        return Cache::forget($key);
    }

    /**
     * Flush the entire cache
     *
     * @return bool
     */
    public static function flush()
    {
        return Cache::flush();
    }

    /**
     * Generate a cache key for a model with specific parameters
     *
     * @param string $model Model name
     * @param string $method Method name
     * @param array $params Parameters
     * @return string
     */
    public static function generateKey(string $model, string $method, array $params = [])
    {
        $paramString = empty($params) ? '' : '_' . md5(serialize($params));
        return strtolower("{$model}_{$method}{$paramString}");
    }

    /**
     * Forget cache keys by pattern
     *
     * @param string $pattern Pattern to match
     * @return void
     */
    public static function forgetByPattern(string $pattern)
    {
        $cacheDriver = config('cache.default');
        
        if ($cacheDriver === 'redis') {
            $redis = app('redis');
            $keys = $redis->keys(config('cache.prefix') . ':' . $pattern . '*');
            
            foreach ($keys as $key) {
                $redis->del($key);
            }
        }
    }
}
