<?php

declare(strict_types=1);

namespace Tavp\Hub;

/**
 * Thin wrapper around tavpblocks UI components.
 *
 * Renders prebuilt components (StatCard, Pagination, Badge, Alert,
 * Button, ...) when tavpblocks is installed, and falls back to plain
 * Tailwind HTML otherwise so the panel works either way.
 */
class UI
{
    /**
     * Render a tavpblocks component by name. Returns '' if the
     * component library is not available.
     */
    public static function block(string $name, array $props = []): string
    {
        if (!class_exists(\Tavp\Blocks\BlockRegistry::class)) {
            return self::fallback($name, $props);
        }

        static $registry = null;
        $registry ??= new \Tavp\Blocks\BlockRegistry();

        $component = $registry->make($name, $props);

        return $component === null ? self::fallback($name, $props) : $component->render();
    }

    public static function statCard(string $label, $value, string $trend = '', string $trendColor = 'gray'): string
    {
        return self::block('StatCard', ['label' => $label, 'value' => $value, 'trend' => $trend, 'trendColor' => $trendColor]);
    }

    public static function pagination(int $current, int $total, string $baseUrl): string
    {
        return self::block('Pagination', ['currentPage' => $current, 'totalPages' => $total, 'baseUrl' => $baseUrl]);
    }

    public static function badge(string $text, string $color = 'gray'): string
    {
        if (class_exists(\Tavp\Blocks\BlockRegistry::class)) {
            return self::block('Badge', ['text' => $text, 'color' => $color]);
        }

        return '<span class="inline-flex items-center rounded-full bg-gray-700 px-2 py-0.5 text-xs text-gray-200">' . htmlspecialchars($text) . '</span>';
    }

    public static function alert(string $message, string $type = 'info'): string
    {
        if (class_exists(\Tavp\Blocks\BlockRegistry::class)) {
            return self::block('Alert', ['message' => $message, 'type' => $type]);
        }

        return '<div class="rounded-lg border border-gray-700 bg-gray-900 p-4 text-sm text-gray-200">' . htmlspecialchars($message) . '</div>';
    }

    private static function fallback(string $name, array $props): string
    {
        return '<!-- tavpblocks component "' . htmlspecialchars($name) . '" not available -->';
    }
}
