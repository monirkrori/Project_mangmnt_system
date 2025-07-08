<?php

use Illuminate\Support\Facades\Cache;

/**
 * Remember cache using seconds or Carbon instance.
 */
function cache_remember(string $key, int|\DateTimeInterface $duration, Closure $callback)
{
    return Cache::remember($key, $duration, $callback);
}

/**
 * Forget one or multiple cache keys.
 */
function cache_forget(array|string $keys): void
{
    foreach ((array) $keys as $key) {
        Cache::forget($key);
    }
}
