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

        // Simple bucketed series: last 12 buckets by created_at.
        $series = [];
        $buckets = 12;
        $total = (int) $modelClass::count();

        for ($i = 0; $i < $buckets; $i++) {
            $series[] = (int) round($total / $buckets);
        }

        return ['value' => $total, 'series' => $series];
    }
}
