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
        $metricHtml = $this->renderMetrics();

        return $this->view('hub::dashboard', [
            'stats' => $stats,
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
                $cards .= UI::statCard(
                    $metric->label,
                    $computed['value'] ?? 0,
                    $computed['delta'] ?? '',
                    $computed['deltaColor'] ?? 'gray'
                );

                if ($metric instanceof TrendMetric) {
                    $series = $computed['series'] ?? [];
                    $charts .= UI::chart($metric->label, $series, 'line', 90);
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
