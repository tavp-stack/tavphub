<?php

declare(strict_types=1);

namespace Tavp\Hub;

/**
 * Base class for TAVPhub dashboard metrics (Nova-style "Metrics").
 *
 * A metric computes a value from a resource's model. `calculate()`
 * returns an array with `value`, optional `delta` and `deltaColor`.
 */
abstract class Metric
{
    public string $name;
    public string $label;
    public string $type = 'value';
    public string $icon = 'chart';

    public function __construct(string $name, ?string $label = null)
    {
        $this->name = $name;
        $this->label = $label ?? ucwords(str_replace(['_', '-'], ' ', $name));
    }

    public function icon(string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Compute the metric for the given model class.
     *
     * @return array{value:int|float|string, delta?:string, deltaColor?:string}
     */
    abstract public function calculate(string $modelClass): array;

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'label' => $this->label,
            'type' => $this->type,
            'icon' => $this->icon,
        ];
    }
}
