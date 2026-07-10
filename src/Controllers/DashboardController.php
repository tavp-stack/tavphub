<?php

declare(strict_types=1);

namespace Tavp\Hub\Controllers;

use Tavp\Hub\HubController;
use Tavp\Core\Http\Response;

/**
 * Admin dashboard — stats overview.
 */
class DashboardController extends HubController
{
    public function index(): string|Response
    {
        if ($r = $this->guard()) {
            return $r;
        }

        $stats = $this->collectStats();

        return $this->view('hub::dashboard', [
            'stats' => $stats,
            'recent_activity' => [],
        ]);
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
