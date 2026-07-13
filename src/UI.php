<?php

declare(strict_types=1);

namespace Tavp\Hub;

/**
 * Thin wrapper around tavpblocks UI components.
 *
 * Renders prebuilt components (StatCard, Pagination, Badge, Alert,
 * Button, Dropdown, SearchBar, Chart) when tavpblocks is installed,
 * and falls back to plain Tailwind HTML otherwise so the panel works
 * either way. All methods return HTML strings meant to be printed
 * inside a Volt `{% autoescape false %}` block.
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
            return self::block('Badge', ['label' => $text, 'color' => $color]);
        }

        $classes = match ($color) {
            'green' => 'bg-green-100 text-green-800',
            'red' => 'bg-red-100 text-red-800',
            'yellow' => 'bg-yellow-100 text-yellow-800',
            'blue' => 'bg-blue-100 text-blue-800',
            default => 'bg-gray-100 text-gray-800',
        };

        return '<span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ' . $classes . '">' . htmlspecialchars($text) . '</span>';
    }

    public static function alert(string $message, string $type = 'info'): string
    {
        if (class_exists(\Tavp\Blocks\BlockRegistry::class)) {
            return self::block('Alert', ['message' => $message, 'type' => $type]);
        }

        $classes = match ($type) {
            'success' => 'bg-green-50 border-green-200 text-green-800',
            'error' => 'bg-red-50 border-red-200 text-red-800',
            'warning' => 'bg-yellow-50 border-yellow-200 text-yellow-800',
            default => 'bg-blue-50 border-blue-200 text-blue-800',
        };

        return '<div class="rounded-lg border p-4 ' . $classes . '">' . htmlspecialchars($message) . '</div>';
    }

    public static function button(string $label, string $variant = 'primary', string $href = ''): string
    {
        if (class_exists(\Tavp\Blocks\BlockRegistry::class)) {
            return self::block('Button', ['label' => $label, 'variant' => $variant, 'href' => $href]);
        }

        $classes = match ($variant) {
            'primary' => 'bg-blue-600 text-white hover:bg-blue-700',
            'danger' => 'bg-red-600 text-white hover:bg-red-700',
            'ghost' => 'bg-transparent text-gray-600 hover:bg-gray-100',
            default => 'bg-gray-200 text-gray-800 hover:bg-gray-300',
        };

        $tag = $href !== '' ? 'a' : 'button';
        $hrefAttr = $href !== '' ? ' href="' . htmlspecialchars($href) . '"' : '';

        return '<' . $tag . $hrefAttr . ' class="rounded-lg ' . $classes . ' px-4 py-2 text-sm font-medium">' . htmlspecialchars($label) . '</' . $tag . '>';
    }

    public static function searchBar(string $name, string $value = '', string $placeholder = 'Search...'): string
    {
        if (class_exists(\Tavp\Blocks\BlockRegistry::class)) {
            return self::block('SearchBar', ['name' => $name, 'value' => $value, 'placeholder' => $placeholder]);
        }

        return '<div class="relative">'
            . '<input type="text" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars($value) . '" placeholder="' . htmlspecialchars($placeholder) . '" class="w-full rounded-lg border border-gray-700 bg-gray-800 px-4 py-2 text-sm text-white">'
            . '</div>';
    }

    /**
     * Build a dropdown menu. Items: [['label' => ..., 'url' => ..., 'icon' => ...]].
     */
    public static function dropdown(string $trigger, array $items = []): string
    {
        if (class_exists(\Tavp\Blocks\Components\Dropdown::class)) {
            $dd = new \Tavp\Blocks\Components\Dropdown($trigger);
            foreach ($items as $item) {
                $dd->addItem($item['label'] ?? '', $item['url'] ?? '', $item['icon'] ?? '');
            }

            return $dd->render();
        }

        $html = '<select class="rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-white">';
        foreach ($items as $item) {
            $html .= '<option value="' . htmlspecialchars($item['url'] ?? '') . '">' . htmlspecialchars($item['label'] ?? '') . '</option>';
        }
        $html .= '</select>';

        return $html;
    }

    /**
     * Render a small Chart.js sparkline (trend metric).
     *
     * @param array<int|string, float> $series
     */
    public static function chart(string $title, array $series, string $type = 'line', int $height = 80): string
    {
        if (!class_exists(\Tavp\Blocks\Components\Chart::class)) {
            return '';
        }

        $chart = new \Tavp\Blocks\Components\Chart($type, $title, $height);
        foreach ($series as $label => $value) {
            $chart->addPoint((string) $label, (float) $value);
        }

        return $chart->render();
    }

    private static function fallback(string $name, array $props): string
    {
        return '<!-- tavpblocks component "' . htmlspecialchars($name) . '" not available -->';
    }
}
