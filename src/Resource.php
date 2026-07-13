<?php

declare(strict_types=1);

namespace Tavp\Hub;

use ReflectionClass;

/**
 * Base class for TAVPhub admin resources (Nova-style Resource).
 *
 * A resource describes a model's columns, form schema, and — now —
 * filters, metrics, actions, lenses, relationships and a policy, so
 * the panel can auto-generate a full admin experience.
 */
abstract class Resource
{
    /**
     * The sidebar label for this resource.
     */
    abstract public function label(): string;

    /**
     * The model class this resource manages.
     */
    abstract public function model(): string;

    /**
     * Column definitions for the index table.
     * Each: ['key' => 'name', 'label' => 'Name', 'sortable' => true].
     *
     * @return array<int, array<string, mixed>>
     */
    abstract public function columns(): array;

    /**
     * Form field definitions for create/edit.
     * Each: ['name' => 'title', 'type' => 'text', 'label' => 'Title'].
     *
     * @return array<int, array<string, mixed>>
     */
    abstract public function fields(): array;

    /**
     * URL key used in routes (e.g. "users"). Derived from the class
     * name by default (UserResource => "user").
     */
    public function uriKey(): string
    {
        $short = (new ReflectionClass($this))->getShortName();
        $short = preg_replace('/Resource$/', '', $short) ?: $short;
        $snake = preg_replace('/(?<!^)[A-Z]/', '_$0', $short) ?: $short;

        return strtolower($snake);
    }

    public function icon(): string
    {
        return 'document';
    }

    public function singular(): string
    {
        $label = $this->label();

        return preg_match('/s$/i', $label) ? substr($label, 0, -1) : $label;
    }

    /**
     * Whether this resource appears in the sidebar.
     */
    public function inSidebar(): bool
    {
        return true;
    }

    /**
     * Columns searched by the global index search box.
     *
     * @return string[]
     */
    public function searchableColumns(): array
    {
        return [];
    }

    public function perPage(): int
    {
        return 15;
    }

    /**
     * Filters shown above the index table.
     *
     * @return Filter[]
     */
    public function filters(): array
    {
        return [];
    }

    /**
     * Dashboard metric cards for this resource.
     *
     * @return Metric[]
     */
    public function metrics(): array
    {
        return [];
    }

    /**
     * Bulk/row actions available on the index.
     *
     * @return Action[]
     */
    public function actions(): array
    {
        return [];
    }

    /**
     * Alternate pre-filtered views of this resource.
     *
     * @return Lens[]
     */
    public function lenses(): array
    {
        return [];
    }

    /**
     * Relationship fields (belongsTo / hasMany).
     *
     * @return Relation[]
     */
    public function relations(): array
    {
        return [];
    }

    /**
     * Policy class name for authorization, or null for no guard.
     */
    public function policy(): ?string
    {
        return null;
    }

    /**
     * Build the full definition consumed by the panel renderer + views.
     */
    public function definition(): array
    {
        return [
            'label' => $this->label(),
            'singular' => $this->singular(),
            'model' => $this->model(),
            'icon' => $this->icon(),
            'columns' => $this->columns(),
            'fields' => $this->fields(),
            'in_sidebar' => $this->inSidebar(),
            'searchable' => $this->searchableColumns(),
            'per_page' => $this->perPage(),
            'filters' => array_map(static fn ($f) => $f instanceof Filter ? $f->toArray() : $f, $this->filters()),
            'metrics' => array_map(static fn ($m) => $m instanceof Metric ? $m->toArray() : $m, $this->metrics()),
            'actions' => array_map(static fn ($a) => $a instanceof Action ? $a->toArray() : $a, $this->actions()),
            'lenses' => array_map(static fn ($l) => $l instanceof Lens ? $l->toArray() : $l, $this->lenses()),
            'relations' => array_map(static fn ($r) => $r instanceof Relation ? $r->toArray() : $r, $this->relations()),
            'policy' => $this->policy(),
        ];
    }

    /**
     * Build a Resource from a plain config array (backwards compatible
     * with config('hub.resources') style definitions).
     */
    public static function fromConfig(array $config, string $key): Resource
    {
        return new class($config, $key) extends Resource
        {
            private array $cfg;
            private string $k;

            public function __construct(array $config, string $key)
            {
                $this->cfg = $config;
                $this->k = $key;
            }

            public function label(): string
            {
                return $this->cfg['label'] ?? ucfirst($this->k) . 's';
            }

            public function model(): string
            {
                return $this->cfg['model'] ?? '';
            }

            public function columns(): array
            {
                return $this->cfg['columns'] ?? [];
            }

            public function fields(): array
            {
                return $this->cfg['fields'] ?? [];
            }

            public function uriKey(): string
            {
                return $this->k;
            }

            public function icon(): string
            {
                return $this->cfg['icon'] ?? 'document';
            }

            public function singular(): string
            {
                return $this->cfg['singular'] ?? parent::singular();
            }

            public function inSidebar(): bool
            {
                return $this->cfg['in_sidebar'] ?? true;
            }

            public function searchableColumns(): array
            {
                return $this->cfg['searchable'] ?? [];
            }

            public function perPage(): int
            {
                return $this->cfg['per_page'] ?? 15;
            }
        };
    }
}
