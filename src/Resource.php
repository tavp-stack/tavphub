<?php

declare(strict_types=1);

namespace Tavp\Hub;

/**
 * Base class for TAVPhub admin resources.
 *
 * A resource describes a model's columns, form schema and sidebar label
 * so the panel can auto-generate index/create/edit/delete CRUD views
 * (HUB-003/004/005/006).
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
     * Whether this resource appears in the sidebar.
     */
    public function inSidebar(): bool
    {
        return true;
    }

    /**
     * Build the full definition consumed by the panel renderer.
     */
    public function definition(): array
    {
        return [
            'label' => $this->label(),
            'model' => $this->model(),
            'columns' => $this->columns(),
            'fields' => $this->fields(),
            'in_sidebar' => $this->inSidebar(),
        ];
    }
}
