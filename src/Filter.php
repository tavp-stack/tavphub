<?php

declare(strict_types=1);

namespace Tavp\Hub;

use Tavp\Core\Database\QueryBuilder;

/**
 * Base class for TAVPhub resource filters (Nova-style "Filters").
 *
 * A filter narrows the index query. Provide a name (query param), a
 * label, and an `apply()` that mutates the QueryBuilder. The default
 * apply does a simple `where(column, value)` so most filters need no
 * override — just configure options/type. Subclass for custom logic.
 */
class Filter
{
    public string $name;
    public string $label;
    public string $type = 'select';
    public array $options = [];
    public mixed $default = null;
    public ?string $column = null;

    public function __construct(string $name, ?string $label = null)
    {
        $this->name = $name;
        $this->label = $label ?? ucwords(str_replace(['_', '-'], ' ', $name));
        $this->column = $this->column ?? $name;
    }

    public function options(array $options): static
    {
        $this->options = $options;

        return $this;
    }

    public function type(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function column(string $column): static
    {
        $this->column = $column;

        return $this;
    }

    public function default(mixed $value): static
    {
        $this->default = $value;

        return $this;
    }

    /**
     * Apply the filter to the query. Override for custom logic
     * (date ranges, raw SQL, relationships, etc.).
     */
    public function apply(QueryBuilder $query, mixed $value): void
    {
        if ($value === null || $value === '' || $value === 'all') {
            return;
        }

        $query->where($this->column, $value);
    }

    /**
     * Serialize for the filter bar view.
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'label' => $this->label,
            'type' => $this->type,
            'options' => $this->options,
            'default' => $this->default,
        ];
    }
}
