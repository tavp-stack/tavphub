<?php

declare(strict_types=1);

namespace Tavp\Hub;

use Tavp\Core\Database\QueryBuilder;

/**
 * Base class for TAVPhub lenses (Nova-style "Lenses").
 *
 * A lens is an alternate, pre-filtered view of a resource. Override
 * `query()` to constrain the index query, and optionally `columns()`
 * to show a different column set than the resource default.
 */
abstract class Lens
{
    public string $name;
    public string $label;
    public string $icon = 'eye';

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
     * Mutate the query to scope the lens.
     */
    abstract public function query(QueryBuilder $query): void;

    /**
     * Optional column override for this lens.
     *
     * @return array<int, array<string, mixed>>
     */
    public function columns(): array
    {
        return [];
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'label' => $this->label,
            'icon' => $this->icon,
        ];
    }
}
