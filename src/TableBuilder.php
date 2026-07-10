<?php

declare(strict_types=1);

namespace Tavp\Hub;

/**
 * Table builder — generate admin DataTables from config.
 */
class TableBuilder
{
    /**
     * Build a DataTable configuration.
     */
    public static function make(array $config): array
    {
        return [
            'columns' => $config['columns'] ?? [],
            'actions' => $config['actions'] ?? ['view', 'edit', 'delete'],
            'bulkActions' => $config['bulk_actions'] ?? ['delete'],
            'sortable' => $config['sortable'] ?? true,
            'searchable' => $config['searchable'] ?? true,
            'pagination' => $config['pagination'] ?? true,
            'perPage' => $config['per_page'] ?? 15,
        ];
    }

    /**
     * Render a DataTable from config.
     */
    public static function render(array $config, array $data): string
    {
        $table = self::make($config);

        $html = '<div class="bg-white shadow rounded-lg overflow-hidden">';
        $html .= '<div class="px-6 py-4 border-b border-gray-200">';
        $html .= '<input type="text" placeholder="Search..." class="w-full rounded-md border-gray-300 shadow-sm">';
        $html .= '</div>';
        $html .= '<table class="min-w-full divide-y divide-gray-200">';
        $html .= '<thead class="bg-gray-50"><tr>';

        foreach ($table['columns'] as $column) {
            $label = $column['label'] ?? $column['field'] ?? '';
            $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">' . htmlspecialchars($label) . '</th>';
        }

        if (!empty($table['actions'])) {
            $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>';
        }

        $html .= '</tr></thead><tbody class="bg-white divide-y divide-gray-200">';

        foreach ($data as $row) {
            $html .= '<tr>';

            foreach ($table['columns'] as $column) {
                $field = $column['field'] ?? '';
                $value = $row[$field] ?? '';
                $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' . htmlspecialchars((string)$value) . '</td>';
            }

            if (!empty($table['actions'])) {
                $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm">';
                foreach ($table['actions'] as $action) {
                    $html .= '<a href="#" class="text-blue-600 hover:underline mr-2">' . ucfirst($action) . '</a>';
                }
                $html .= '</td>';
            }

            $html .= '</tr>';
        }

        $html .= '</tbody></table>';
        $html .= '</div>';

        return $html;
    }
}
