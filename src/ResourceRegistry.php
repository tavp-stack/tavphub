<?php

declare(strict_types=1);

namespace Tavp\Hub;

/**
 * Central registry of TAVPhub resources.
 *
 * Resources can be registered explicitly (via `register()`), by class
 * name (`registerClass()`), or auto-discovered from a directory
 * (`discover()`). This powers resource auto-discovery so developers
 * no longer have to hand-wire everything into config.
 */
class ResourceRegistry
{
    /** @var array<string, Resource> */
    private static array $resources = [];

    public static function register(Resource $resource, ?string $key = null): void
    {
        $key = $key ?? $resource->uriKey();
        static::$resources[$key] = $resource;
    }

    public static function registerClass(string $class, ?string $key = null): void
    {
        if (!class_exists($class)) {
            return;
        }

        $resource = new $class();
        static::register($resource, $key);
    }

    /**
     * Scan a directory for Resource subclasses and register them.
     */
    public static function discover(string $directory, string $namespace): void
    {
        if (!is_dir($directory)) {
            return;
        }

        foreach (glob(rtrim($directory, '/') . '/*.php') ?: [] as $file) {
            $class = rtrim($namespace, '\\') . '\\' . basename($file, '.php');

            if (class_exists($class) && is_subclass_of($class, Resource::class)) {
                static::registerClass($class);
            }
        }
    }

    /**
     * Register resources from a config-style map (key => class string).
     *
     * @param array<string, string|Resource> $map
     */
    public static function registerMap(array $map): void
    {
        foreach ($map as $key => $entry) {
            if ($entry instanceof Resource) {
                static::register($entry, is_string($key) ? $key : null);
            } else {
                static::registerClass((string) $entry, is_string($key) ? $key : null);
            }
        }
    }

    /** @return array<string, Resource> */
    public static function all(): array
    {
        return static::$resources;
    }

    public static function get(string $key): ?Resource
    {
        return static::$resources[$key] ?? null;
    }

    public static function has(string $key): bool
    {
        return isset(static::$resources[$key]);
    }

    /** @return string[] */
    public static function keys(): array
    {
        return array_keys(static::$resources);
    }

    public static function clear(): void
    {
        static::$resources = [];
    }
}
