<?php

declare(strict_types=1);

namespace Tavp\Hub;

/**
 * A value metric: count / sum / average of a model, with an optional
 * previous-period delta (for the "+12%" trend Nova shows).
 */
class ValueMetric extends Metric
{
    public string $aggregate = 'count';
    public ?string $column = null;
    public ?string $suffix = null;

    /** @var ?callable(string):float */
    public $previous = null;

    public function aggregate(string $aggregate, ?string $column = null): static
    {
        $this->aggregate = $aggregate;
        $this->column = $column;

        return $this;
    }

    public function suffix(string $suffix): static
    {
        $this->suffix = $suffix;

        return $this;
    }

    /**
     * Provide a callable that returns the previous-period value,
     * used to compute the delta percentage.
     *
     * @param callable(string):float $callable
     */
    public function compareTo(callable $callable): static
    {
        $this->previous = $callable;

        return $this;
    }

    public function calculate(string $modelClass): array
    {
        if (!class_exists($modelClass)) {
            return ['value' => 0, 'delta' => '', 'deltaColor' => 'gray'];
        }

        $value = match ($this->aggregate) {
            'sum' => (float) $modelClass::sum(['column' => $this->column]),
            'avg' => (float) $modelClass::average(['column' => $this->column]),
            'count' => (int) $modelClass::count(),
            default => (int) $modelClass::count(),
        };

        if ($this->suffix !== null) {
            $value = $value . $this->suffix;
        }

        $delta = '';
        $deltaColor = 'gray';

        if ($this->previous !== null) {
            $prev = (float) call_user_func($this->previous, $modelClass);
            if ($prev > 0) {
                $pct = (int) round((($value - $prev) / $prev) * 100);
                $delta = ($pct >= 0 ? '+' : '') . $pct . '%';
                $deltaColor = $pct >= 0 ? 'green' : 'red';
            }
        }

        return ['value' => $value, 'delta' => $delta, 'deltaColor' => $deltaColor];
    }
}
