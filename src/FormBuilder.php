<?php

declare(strict_types=1);

namespace Tavp\Hub;

/**
 * Form builder — generate admin forms from config.
 */
class FormBuilder
{
    /**
     * Build a form from configuration.
     */
    public static function make(array $config): string
    {
        $html = '<form method="' . ($config['method'] ?? 'POST') . '" action="' . ($config['action'] ?? '#') . '" class="space-y-6">';

        foreach ($config['fields'] ?? [] as $field) {
            $html .= self::renderField($field);
        }

        $html .= '<div class="flex gap-2">';
        $html .= '<button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Save</button>';
        $html .= '<a href="' . ($config['cancel_url'] ?? '#') . '" class="text-gray-600 hover:underline px-4 py-2">Cancel</a>';
        $html .= '</div>';
        $html .= '</form>';

        return $html;
    }

    /**
     * Render a single form field.
     */
    public static function renderField(array $field): string
    {
        $type = $field['type'] ?? 'text';
        $name = $field['name'] ?? '';
        $label = $field['label'] ?? ucfirst($name);
        $required = ($field['required'] ?? false) ? ' required' : '';
        $value = $field['value'] ?? '';

        $html = '<div class="mb-4">';
        $html .= '<label class="block text-sm font-medium text-gray-700 mb-1">' . htmlspecialchars($label);
        if ($required) {
            $html .= ' <span class="text-red-500">*</span>';
        }
        $html .= '</label>';

        switch ($type) {
            case 'textarea':
                $html .= '<textarea name="' . htmlspecialchars($name) . '" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"' . $required . '>' . htmlspecialchars($value) . '</textarea>';
                break;

            case 'select':
                $html .= '<select name="' . htmlspecialchars($name) . '" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"' . $required . '>';
                foreach ($field['options'] ?? [] as $option) {
                    $optValue = is_array($option) ? $option['value'] : $option;
                    $optLabel = is_array($option) ? $option['label'] : $option;
                    $selected = $optValue == $value ? ' selected' : '';
                    $html .= '<option value="' . htmlspecialchars((string)$optValue) . '"' . $selected . '>' . htmlspecialchars($optLabel) . '</option>';
                }
                $html .= '</select>';
                break;

            case 'checkbox':
                $checked = $value ? ' checked' : '';
                $html .= '<input type="checkbox" name="' . htmlspecialchars($name) . '" value="1" class="rounded border-gray-300 text-blue-600"' . $checked . $required . '>';
                break;

            case 'file':
                $html .= '<input type="file" name="' . htmlspecialchars($name) . '" class="mt-1 block w-full"' . $required . '>';
                break;

            default:
                $html .= '<input type="' . htmlspecialchars($type) . '" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars($value) . '" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"' . $required . '>';
        }

        if (!empty($field['help'])) {
            $html .= '<p class="mt-1 text-sm text-gray-500">' . htmlspecialchars($field['help']) . '</p>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Generate a form from model fields.
     */
    public static function fromModel(string $modelClass, array $exclude = []): string
    {
        $config = [
            'fields' => [],
        ];

        // Introspect model to generate fields
        // For now, return empty form
        return self::make($config);
    }
}
