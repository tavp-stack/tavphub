<?php

declare(strict_types=1);

namespace Tavp\Hub\Controllers;

use Tavp\Hub\HubController;
use Tavp\Hub\TrendMetric;
use Tavp\Hub\UI;
use Tavp\Core\Http\Response;

/**
 * Admin dashboard — stats overview + resource metric cards.
 */
class DashboardController extends HubController
{
    public function index(): string|Response
    {
        if ($r = $this->guard()) {
            return $r;
        }

        $stats = $this->collectStats();
        $statsHtml = $this->renderStats($stats);
        $metricHtml = $this->renderMetrics();

        return $this->view('hub::dashboard', [
            'stats' => $stats,
            'stats_html' => $statsHtml,
            'metric_html' => $metricHtml,
            'flash_html' => $this->flashHtml(),
            'recent_activity' => [],
        ]);
    }

    /**
     * Render metric cards (+ trend charts) from all registered resources.
     */
    private function renderMetrics(): string
    {
        $cards = '';
        $charts = '';

        foreach (\Tavp\Hub\ResourceRegistry::all() as $key => $resource) {
            foreach ($resource->metrics() as $metric) {
                if (!is_object($metric) || !method_exists($metric, 'calculate')) {
                    continue;
                }

                $computed = $metric->calculate($resource->model());
                $color = method_exists($metric, 'color') ? $metric->color : 'brand';
                $icon = $this->iconGlyph(method_exists($metric, 'icon') ? $metric->icon : 'chart');

                $sparkline = [];
                if ($metric instanceof TrendMetric) {
                    $sparkline = array_values($computed['series'] ?? []);
                }

                $cards .= UI::statCard(
                    $metric->label,
                    $computed['value'] ?? 0,
                    $computed['delta'] ?? '',
                    $computed['deltaColor'] ?? 'gray',
                    $icon,
                    $color,
                    $sparkline
                );

                if ($metric instanceof TrendMetric) {
                    $series = $computed['series'] ?? [];
                    $chartHtml = UI::chart($metric->label, $series, 'line', 220);
                    $charts .= UI::card($metric->label . ' — trend', $chartHtml);
                }
            }
        }

        $html = '';
        if ($cards !== '') {
            $html .= '<div class="grid grid-cols-1 gap-6 mb-8 sm:grid-cols-2 lg:grid-cols-4">' . $cards . '</div>';
        }
        if ($charts !== '') {
            $html .= '<div class="grid grid-cols-1 gap-6 mb-8 lg:grid-cols-2">' . $charts . '</div>';
        }

        return $html;
    }

    /**
     * Map a metric icon name to a glyph (emoji) for the StatCard icon tile.
     */
    private function iconGlyph(string $name): string
    {
        return match ($name) {
            'users', 'user' => '👥',
            'orders' => '🧾',
            'products', 'product' => '📦',
            'revenue', 'money' => '💰',
            'sales' => '📈',
            'views' => '👁️',
            'comments' => '💬',
            'heart', 'likes' => '❤️',
            'star' => '⭐',
            'bell' => '🔔',
            'settings' => '⚙️',
            'download' => '⬇️',
            'upload' => '⬆️',
            'cart' => '🛒',
            'globe' => '🌐',
            'shield' => '🛡️',
            'bolt' => '⚡',
            'fire' => '🔥',
            default => '📊',
        };
    }

    /**
     * Render the resource count cards (StatCard + trend color/icon).
     */
    private function renderStats(array $stats): string
    {
        if ($stats === []) {
            return '';
        }

        $palette = ['brand', 'blue', 'green', 'purple', 'yellow', 'pink', 'indigo'];
        $icons = ['users' => '👥', 'product' => '📦', 'order' => '🧾', 'role' => '🛡️'];

        $html = '';
        $i = 0;
        foreach ($stats as $key => $stat) {
            $label = strtolower((string) $key);
            $icon = '📊';
            foreach ($icons as $needle => $glyph) {
                if (str_contains($label, $needle)) {
                    $icon = $glyph;
                    break;
                }
            }
            $color = $palette[$i % count($palette)];
            $html .= UI::statCard($stat['label'], $stat['count'], '', 'gray', $icon, $color, []);
            $i++;
        }

        return '<div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-3">' . $html . '</div>';
    }

    private function collectStats(): array
    {
        $stats = [];
        $resources = $this->getResources();

        foreach ($resources as $key => $config) {
            $modelClass = $config['model'] ?? null;

            if ($modelClass && class_exists($modelClass)) {
                try {
                    $count = $modelClass::count();
                } catch (\Throwable) {
                    $count = 0;
                }

                $stats[$key] = [
                    'label' => $config['label'] ?? ucfirst($key),
                    'count' => $count,
                ];
            }
        }

        return $stats;
    }
}
