<?php

declare(strict_types=1);

namespace Tavp\Hub;

/**
 * Builds an index table configuration from a resource's columns.
 *
 * Supports sortable columns, searchable columns and bulk actions
 * (HUB-004).
 */
class TableBuilder
{
    /**
     * @param array<int, array<string, mixed>> $columns
     */
    public function __construct(private array $columns)
    {
    }

    /**
     * Return only the sortable columns.
     *
     * @return array<int, array<string, mixed>>
     */
    public function sortable(): array
    {
        return array_filter($this->columns, fn ($c) => ($c['sortable'] ?? false) === true);
    }

    /**
     * Return only the searchable columns.
     *
     * @return array<int, array<string, mixed>>
     */
    public function searchable(): array
    {
        return array_filter($this->columns, fn ($c) => ($c['searchable'] ?? false) === true);
    }

    /**
     * Render a simple HTML table header row.
     */
    public function renderHeader(): string
    {
        $cells = '';
        foreach ($this->columns as $column) {
            $cells .= '<th>' . htmlspecialchars($column['label'] ?? $column['key']) . '</th>';
        }

        return '<tr>' . $cells . '</tr>';
    }
}
