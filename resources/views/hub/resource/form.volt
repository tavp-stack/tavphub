{% extends 'hub/layouts/admin.volt' %}

{% block content %}
<h1 class="text-2xl font-bold mb-6">{{ heading }}</h1>

{% set errors = __errors | default([]) %}
{% if errors is not empty %}
    <div class="mb-6 rounded-lg border border-red-700 bg-red-900/50 p-4">
        <p class="text-sm font-medium text-red-200 mb-2">Please fix the following errors:</p>
        <ul class="list-disc list-inside text-sm text-red-300 space-y-1">
            {% for field, errs in errors %}
                {% for err in errs %}
                    <li><strong>{{ field }}:</strong> {{ err }}</li>
                {% endfor %}
            {% endfor %}
        </ul>
    </div>
{% endif %}

<form method="post" action="{{ action }}" class="space-y-6 max-w-2xl">
    {% for field in resource['fields'] | default([]) %}
        {% set name = field['name'] | default(field['field'] | default('')) %}
        {% set label = field['label'] | default(name | capitalize) %}
        {% set type = field['type'] | default('text') %}
        {% set required = field['required'] | default(false) %}
        {% set value = record[name] | default(field['default'] | default('')) %}

        <div>
            <label for="{{ name }}" class="block text-sm font-medium text-gray-300 mb-1">
                {{ label }}{% if required %} <span class="text-red-400">*</span>{% endif %}
            </label>

            {% if type == 'textarea' or type == 'richtext' %}
                <textarea name="{{ name }}" id="{{ name }}" rows="6"
                    class="w-full rounded-lg border border-gray-700 bg-gray-900 px-4 py-3 text-white placeholder-gray-500 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    {% if required %}required{% endif %}>{{ value }}</textarea>

            {% elseif type == 'select' %}
                <select name="{{ name }}" id="{{ name }}"
                    class="w-full rounded-lg border border-gray-700 bg-gray-900 px-4 py-3 text-white focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    {% if required %}required{% endif %}>
                    {% for opt in field['options'] | default([]) %}
                        {% set optVal = opt['value'] | default(opt) %}
                        {% set optLabel = opt['label'] | default(opt) %}
                        <option value="{{ optVal }}" {% if optVal == value %}selected{% endif %}>{{ optLabel }}</option>
                    {% endfor %}
                </select>

            {% elseif type == 'belongsTo' %}
                {% set relOpts = relation_options[name] | default([]) %}
                <select name="{{ name }}" id="{{ name }}"
                    class="w-full rounded-lg border border-gray-700 bg-gray-900 px-4 py-3 text-white focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    {% if required %}required{% endif %}>
                    <option value="">— Select —</option>
                    {% for opt in relOpts %}
                        <option value="{{ opt['value'] }}" {% if (opt['value'] | default('')) == value %}selected{% endif %}>{{ opt['label'] }}</option>
                    {% endfor %}
                </select>

            {% elseif type == 'toggle' or type == 'checkbox' %}
                <input type="hidden" name="{{ name }}" value="0">
                <input type="checkbox" name="{{ name }}" id="{{ name }}" value="1"
                    class="rounded border-gray-700 bg-gray-900 text-blue-600 focus:ring-blue-500"
                    {% if value %}checked{% endif %}>

            {% elseif type == 'date' %}
                <input type="date" name="{{ name }}" id="{{ name }}" value="{{ value }}"
                    class="w-full rounded-lg border border-gray-700 bg-gray-900 px-4 py-3 text-white focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    {% if required %}required{% endif %}>

            {% elseif type == 'datetime' %}
                <input type="datetime-local" name="{{ name }}" id="{{ name }}" value="{{ value }}"
                    class="w-full rounded-lg border border-gray-700 bg-gray-900 px-4 py-3 text-white focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    {% if required %}required{% endif %}>

            {% elseif type == 'number' %}
                <input type="number" name="{{ name }}" id="{{ name }}" value="{{ value }}"
                    class="w-full rounded-lg border border-gray-700 bg-gray-900 px-4 py-3 text-white focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    {% if required %}required{% endif %}>

            {% elseif type == 'email' %}
                <input type="email" name="{{ name }}" id="{{ name }}" value="{{ value }}"
                    class="w-full rounded-lg border border-gray-700 bg-gray-900 px-4 py-3 text-white focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    {% if required %}required{% endif %}>

            {% else %}
                <input type="text" name="{{ name }}" id="{{ name }}" value="{{ value }}"
                    class="w-full rounded-lg border border-gray-700 bg-gray-900 px-4 py-3 text-white placeholder-gray-500 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    {% if required %}required{% endif %}>
            {% endif %}

            {% if field['help'] is defined and field['help'] %}
                <p class="mt-1 text-sm text-gray-500">{{ field['help'] }}</p>
            {% endif %}
        </div>
    {% endfor %}

    <div class="flex gap-3 pt-4">
        <button type="submit" class="rounded-lg bg-blue-600 px-6 py-3 font-medium text-white hover:bg-blue-700">Save</button>
        <a href="{{ config('hub.admin_prefix', '/admin') }}/resource/{{ resource_key }}" class="rounded-lg border border-gray-700 px-6 py-3 text-gray-300 hover:bg-gray-800">Cancel</a>
    </div>
</form>
{% endblock %}
