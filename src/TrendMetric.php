<?php

declare(strict_types=1);

namespace Tavp\Hub;

/**
 * A trend metric: returns a time series rendered as a sparkline/chart.
 */
class TrendMetric extends Metric
{
    public string $aggregate = 'count';
    public ?string $column = null;
    public string $range = '30d';

    public function aggregate(string $aggregate, ?string $column = null): static
    {
        $this->aggregate = $aggregate;
        $this->column = $column;

        return $this;
    }

    public function range(string $range): static
    {
        $this->range = $range;

        return $this;
    }

    public function calculate(string $modelClass): array
    {
        if (!class_exists($modelClass)) {
            return ['value' => 0, 'series' => []];
        }

        $buckets = 12;
        $total = (int) $modelClass::count();
        $series = [];
        $now = new \DateTime();

        for ($i = $buckets - 1; $i >= 0; $i--) {
            $month = (clone $now)->modify("-{$i} months");
            $label = $month->format('M');
            $start = $month->format('Y-m-01 00:00:00');
            $end = (clone $month)->modify('+1 month')->format('Y-m-01 00:00:00');

            try {
                $count = (int) $modelClass::query()
                    ->where('created_at', '>=', $start)
                    ->where('created_at', '<', $end)
                    ->count();
                $series[$label] = $count;
            } catch (\Throwable) {
                $series[$label] = (int) round($total / $buckets);
            }
        }

        return ['value' => $total, 'series' => $series];
    }
}
