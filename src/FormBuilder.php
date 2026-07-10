<?php

declare(strict_types=1);

namespace Tavp\Hub;

/**
 * Enhanced form builder with validation error display, old input support,
 * and CMS-aware field types (taxonomy, media, SEO, etc.).
 */
class FormBuilder
{
    /** @var array<string,string[]> */
    private static array $errors = [];

    /** @var array<string,mixed> */
    private static array $old = [];

    /**
     * Set validation errors for display.
     *
     * @param array<string,string[]> $errors
     */
    public static function setErrors(array $errors): void
    {
        self::$errors = $errors;
    }

    /**
     * Set old input values for repopulation.
     *
     * @param array<string,mixed> $old
     */
    public static function setOld(array $old): void
    {
        self::$old = $old;
    }

    /**
     * Clear old input and errors.
     */
    public static function flush(): void
    {
        self::$errors = [];
        self::$old = [];
    }

    /**
     * Get error for a specific field.
     *
     * @return string[]
     */
    public static function fieldErrors(string $name): array
    {
        return self::$errors[$name] ?? [];
    }

    /**
     * Get old value for a field (falls back to $default).
     */
    public static function old(string $name, mixed $default = null): mixed
    {
        return self::$old[$name] ?? $default;
    }

    /**
     * Check if a field has errors.
     */
    public static function hasErrors(string $name): bool
    {
        return !empty(self::$errors[$name]);
    }

    /**
     * Get all errors as a flat array.
     *
     * @return string[]
     */
    public static function allErrors(): array
    {
        return array_merge(...array_values(self::$errors));
    }

    /**
     * Build a form from configuration with validation support.
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
     * Render a single form field with validation error display.
     */
    public static function renderField(array $field): string
    {
        $type = $field['type'] ?? 'text';
        $name = $field['name'] ?? '';
        $label = $field['label'] ?? ucfirst($name);
        $required = ($field['required'] ?? false) ? ' required' : '';
        $value = self::old($name, $field['value'] ?? $field['default'] ?? '');
        $errors = self::fieldErrors($name);
        $hasError = !empty($errors);

        $errorClass = $hasError ? ' border-red-500' : '';
        $errorHtml = '';
        if ($hasError) {
            $errorHtml = '<p class="mt-1 text-sm text-red-500">' . htmlspecialchars(implode(' ', $errors)) . '</p>';
        }

        $html = '<div class="mb-4">';
        $html .= '<label class="block text-sm font-medium text-gray-700 mb-1">' . htmlspecialchars($label);
        if ($required) {
            $html .= ' <span class="text-red-500">*</span>';
        }
        $html .= '</label>';

        switch ($type) {
            case 'textarea':
            case 'richtext':
                $rows = $type === 'richtext' ? 10 : 4;
                $html .= '<textarea name="' . htmlspecialchars($name) . '" rows="' . $rows . '" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm' . $errorClass . '"' . $required . '>' . htmlspecialchars((string) $value) . '</textarea>';
                break;

            case 'select':
                $html .= '<select name="' . htmlspecialchars($name) . '" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm' . $errorClass . '"' . $required . '>';
                foreach ($field['options'] ?? [] as $option) {
                    $optValue = is_array($option) ? $option['value'] : $option;
                    $optLabel = is_array($option) ? $option['label'] : $option;
                    $selected = (string) $optValue === (string) $value ? ' selected' : '';
                    $html .= '<option value="' . htmlspecialchars((string) $optValue) . '"' . $selected . '>' . htmlspecialchars($optLabel) . '</option>';
                }
                $html .= '</select>';
                break;

            case 'toggle':
            case 'checkbox':
                $checked = $value ? ' checked' : '';
                $html .= '<input type="checkbox" name="' . htmlspecialchars($name) . '" value="1" class="rounded border-gray-300 text-blue-600"' . $checked . $required . '>';
                break;

            case 'file':
            case 'media':
                $html .= '<input type="file" name="' . htmlspecialchars($name) . '" class="mt-1 block w-full' . $errorClass . '"' . $required . '>';
                break;

            case 'date':
                $html .= '<input type="date" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars((string) $value) . '" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm' . $errorClass . '"' . $required . '>';
                break;

            case 'datetime':
                $html .= '<input type="datetime-local" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars((string) $value) . '" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm' . $errorClass . '"' . $required . '>';
                break;

            case 'number':
                $html .= '<input type="number" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars((string) $value) . '" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm' . $errorClass . '"' . $required . '>';
                break;

            case 'email':
                $html .= '<input type="email" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars((string) $value) . '" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm' . $errorClass . '"' . $required . '>';
                break;

            case 'url':
                $html .= '<input type="url" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars((string) $value) . '" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm' . $errorClass . '"' . $required . '>';
                break;

            case 'color':
                $html .= '<input type="color" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars((string) $value ?: '#000000') . '" class="mt-1 w-16 h-10 rounded' . $errorClass . '"' . $required . '>';
                break;

            case 'password':
                $html .= '<input type="password" name="' . htmlspecialchars($name) . '" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm' . $errorClass . '"' . $required . '>';
                break;

            case 'slug':
                $html .= '<input type="text" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars((string) $value) . '" placeholder="Auto-generated from title" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm' . $errorClass . '"' . $required . '>';
                break;

            default:
                $html .= '<input type="text" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars((string) $value) . '" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm' . $errorClass . '"' . $required . '>';
        }

        if (!empty($field['help'])) {
            $html .= '<p class="mt-1 text-sm text-gray-500">' . htmlspecialchars($field['help']) . '</p>';
        }

        $html .= $errorHtml;
        $html .= '</div>';

        return $html;
    }

    /**
     * Render a validation error summary block.
     */
    public static function renderErrors(): string
    {
        if (empty(self::$errors)) {
            return '';
        }

        $html = '<div class="mb-6 rounded border border-red-300 bg-red-50 p-4">';
        $html .= '<p class="text-sm font-medium text-red-800 mb-2">Please fix the following errors:</p>';
        $html .= '<ul class="list-disc list-inside text-sm text-red-700 space-y-1">';

        foreach (self::$errors as $field => $errs) {
            foreach ($errs as $err) {
                $html .= '<li><strong>' . htmlspecialchars(ucfirst($field)) . ':</strong> ' . htmlspecialchars($err) . '</li>';
            }
        }

        $html .= '</ul></div>';

        return $html;
    }

    /**
     * Generate a form from model fields.
     */
    public static function fromModel(string $modelClass, array $exclude = []): string
    {
        $config = ['fields' => []];
        return self::make($config);
    }
}
