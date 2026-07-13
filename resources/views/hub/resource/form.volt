{% extends 'hub/layouts/admin.volt' %}

{% block content %}
<div class="mb-6">
    <nav class="text-xs text-gray-400">{{ resource['label'] | default('Records') }} <span class="px-1">/</span> <span class="text-gray-600 dark:text-gray-300">{{ heading }}</span></nav>
    <h1 class="mt-1 text-2xl font-bold">{{ heading }}</h1>
</div>

{% set errors = __errors | default([]) %}
{% if errors is not empty %}
    <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-500/30 dark:bg-red-500/10">
        <p class="text-sm font-medium text-red-800 dark:text-red-200">Please fix the following errors:</p>
        <ul class="list-disc list-inside mt-2 space-y-1 text-sm text-red-700 dark:text-red-300">
            {% for field, errs in errors %}
                {% for err in errs %}
                    <li><strong>{{ field }}:</strong> {{ err }}</li>
                {% endfor %}
            {% endfor %}
        </ul>
    </div>
{% endif %}

<form method="post" action="{{ action }}" class="max-w-2xl space-y-6">
    {% for field in resource['fields'] | default([]) %}
        {% set name = field['name'] | default(field['field'] | default('')) %}
        {% set label = field['label'] | default(name | capitalize) %}
        {% set type = field['type'] | default('text') %}
        {% set required = field['required'] | default(false) %}
        {% set value = record[name] | default(field['default'] | default('')) %}

        <div>
            <label for="{{ name }}" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                {{ label }}{% if required %} <span class="text-red-500">*</span>{% endif %}
            </label>

            {% set inputCls = "w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100" %}

            {% if type == 'textarea' or type == 'richtext' %}
                <textarea name="{{ name }}" id="{{ name }}" rows="6" class="{{ inputCls }}" {% if required %}required{% endif %}>{{ value }}</textarea>

            {% elseif type == 'select' %}
                <select name="{{ name }}" id="{{ name }}" class="{{ inputCls }}" {% if required %}required{% endif %}>
                    {% for opt in field['options'] | default([]) %}
                        {% set optVal = opt['value'] | default(opt) %}
                        {% set optLabel = opt['label'] | default(opt) %}
                        <option value="{{ optVal }}" {% if optVal == value %}selected{% endif %}>{{ optLabel }}</option>
                    {% endfor %}
                </select>

            {% elseif type == 'belongsTo' %}
                {% set relOpts = relation_options[name] | default([]) %}
                <select name="{{ name }}" id="{{ name }}" class="{{ inputCls }}" {% if required %}required{% endif %}>
                    <option value="">— Select —</option>
                    {% for opt in relOpts %}
                        <option value="{{ opt['value'] }}" {% if (opt['value'] | default('')) == value %}selected{% endif %}>{{ opt['label'] }}</option>
                    {% endfor %}
                </select>

            {% elseif type == 'toggle' or type == 'checkbox' %}
                <input type="hidden" name="{{ name }}" value="0">
                <input type="checkbox" name="{{ name }}" id="{{ name }}" value="1"
                    class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-600 dark:bg-gray-800"
                    {% if value %}checked{% endif %}>

            {% elseif type == 'date' %}
                <input type="date" name="{{ name }}" id="{{ name }}" value="{{ value }}" class="{{ inputCls }}" {% if required %}required{% endif %}>

            {% elseif type == 'datetime' %}
                <input type="datetime-local" name="{{ name }}" id="{{ name }}" value="{{ value }}" class="{{ inputCls }}" {% if required %}required{% endif %}>

            {% elseif type == 'number' %}
                <input type="number" name="{{ name }}" id="{{ name }}" value="{{ value }}" class="{{ inputCls }}" {% if required %}required{% endif %}>

            {% elseif type == 'email' %}
                <input type="email" name="{{ name }}" id="{{ name }}" value="{{ value }}" class="{{ inputCls }}" {% if required %}required{% endif %}>

            {% else %}
                <input type="text" name="{{ name }}" id="{{ name }}" value="{{ value }}" placeholder="{{ field['placeholder'] | default('') }}" class="{{ inputCls }}" {% if required %}required{% endif %}>
            {% endif %}

            {% if field['help'] is defined and field['help'] %}
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ field['help'] }}</p>
            {% endif %}
        </div>
    {% endfor %}

    <div class="flex gap-3 pt-4">
        <button type="submit" class="rounded-lg bg-brand-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">Save</button>
        <a href="{{ config('hub.admin_prefix', '/admin') }}/resource/{{ resource_key }}" class="rounded-lg border border-gray-300 px-6 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Cancel</a>
    </div>
</form>
{% endblock %}
