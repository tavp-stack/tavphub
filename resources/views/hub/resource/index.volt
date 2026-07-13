{% extends 'hub/layouts/admin.volt' %}

{% block content %}
{% if flash_html is defined and flash_html != '' %}
    {% autoescape false %}{{ flash_html }}{% autoescape true %}
{% endif %}

<div class="mb-6 flex items-center justify-between">
    <div>
        <nav class="text-xs text-gray-400">{{ resource['label'] | default('Records') }} <span class="px-1">/</span> <span class="text-gray-600 dark:text-gray-300">List</span></nav>
        <h1 class="mt-1 text-2xl font-bold">{{ resource['label'] | default('Records') }}</h1>
    </div>
    {% autoescape false %}{{ new_html }}{% autoescape true %}
</div>

{# Resource metric cards (tavpblocks StatCard + trend charts) #}
{% if metrics_html is defined and metrics_html != '' %}
    {% autoescape false %}{{ metrics_html }}{% autoescape true %}
{% endif %}

{# Lenses switcher (tavpblocks Dropdown when available) #}
{% if lenses is not empty %}
    {% autoescape false %}{{ lens_html }}{% autoescape true %}
{% endif %}

{# Filters + search (tavpblocks SearchBar when available) #}
{% if filters is not empty or search is defined %}
<form method="get" action="{{ config('hub.admin_prefix', '/admin') }}/resource/{{ resource_key }}" class="mb-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
    <div class="flex flex-wrap items-end gap-3">
        {% for f in filters %}
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">{{ f['label'] }}</label>
                {% set cur = filter_values[f['name']] | default('') %}
                {% if f['type'] == 'select' %}
                    <select name="{{ f['name'] }}" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                        <option value="all">All</option>
                        {% for opt in f['options'] %}
                            {% set ov = opt['value'] | default(opt) %}
                            {% set ol = opt['label'] | default(opt) %}
                            <option value="{{ ov }}" {% if ov == cur %}selected{% endif %}>{{ ol }}</option>
                        {% endfor %}
                    </select>
                {% elseif f['type'] == 'date' %}
                    <input type="date" name="{{ f['name'] }}" value="{{ cur }}" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                {% elseif f['type'] == 'boolean' %}
                    <select name="{{ f['name'] }}" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                        <option value="all">All</option>
                        <option value="1" {% if cur == '1' %}selected{% endif %}>Yes</option>
                        <option value="0" {% if cur == '0' %}selected{% endif %}>No</option>
                    </select>
                {% else %}
                    <input type="text" name="{{ f['name'] }}" value="{{ cur }}" placeholder="{{ f['label'] }}" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                {% endif %}
            </div>
        {% endfor %}
        <div>
            <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Search</label>
            {% autoescape false %}{{ search_html }}{% autoescape true %}
        </div>
        <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white hover:bg-brand-700">Apply</button>
    </div>
</form>
{% endif %}

{# Bulk action bar #}
{% if actions is not empty %}
<form id="bulk-form" method="post"
      action="{{ config('hub.admin_prefix', '/admin') }}/resource/{{ resource_key }}/action/__PLACEHOLDER__"
      class="mb-4 flex items-center gap-3">
    <select id="bulk-action" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
        <option value="">Bulk action...</option>
        {% for a in actions %}
            <option value="{{ a['name'] }}">{{ a['label'] }}</option>
        {% endfor %}
    </select>
    <button type="submit" class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Run</button>
</form>
<script>
document.getElementById('bulk-form').addEventListener('submit', function (e) {
    var act = document.getElementById('bulk-action').value;
    if (!act) { e.preventDefault(); return; }
    this.action = this.action.replace('__PLACEHOLDER__', act);
});
</script>
{% endif %}

<div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
    <table class="w-full">
        <thead class="bg-gray-50 dark:bg-gray-800/50">
            <tr>
                {% if actions is not empty %}<th class="w-10 px-4 py-3"></th>{% endif %}
                {% for col in columns %}
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ col['label'] | default(col['field']) | default(col['key']) | default('') }}</th>
                {% endfor %}
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
            {% for record in records %}
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                    {% if actions is not empty %}
                        <td class="px-4 py-4">
                            <input type="checkbox" form="bulk-form" name="ids[]" value="{{ record['id'] | default('') }}">
                        </td>
                    {% endif %}
                    {% for col in columns %}
                        <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                            {% if col['key'] in badge_keys %}
                                {% autoescape false %}{{ record[col['key']] | default('') }}{% autoescape true %}
                            {% else %}
                                {{ record[col['field'] | default(col['key']) | default('')] | default('') }}
                            {% endif %}
                        </td>
                    {% endfor %}
                    <td class="px-6 py-4 text-sm">
                        <a href="{{ config('hub.admin_prefix', '/admin') }}/resource/{{ resource_key }}/{{ record['id'] | default('') }}/edit"
                           class="font-medium text-brand-600 hover:underline dark:text-brand-400 mr-3">Edit</a>
                        <form method="post" action="{{ config('hub.admin_prefix', '/admin') }}/resource/{{ resource_key }}/{{ record['id'] | default('') }}/delete" class="inline">
                            <button type="submit" class="font-medium text-red-500 hover:underline" onclick="return confirm('Delete this record?')">Delete</button>
                        </form>
                    </td>
                </tr>
            {% endfor %}

            {% if records is empty %}
                <tr>
                    <td colspan="{{ columns | length + 1 + (actions is not empty ? 1 : 0) }}" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                        No records found.
                    </td>
                </tr>
            {% endif %}
        </tbody>
    </table>
</div>

{# Pagination #}
{% if pagination is defined and pagination['last_page'] > 1 %}
<div class="mt-4 flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
    <div>Page {{ pagination['current_page'] }} of {{ pagination['last_page'] }} ({{ pagination['total'] }} records)</div>
    <div class="flex gap-1">
        {% if pagination['current_page'] > 1 %}
            <a href="{{ base_url }}{% if base_url contains '?' %}&{% else %}?{% endif %}page={{ pagination['current_page'] - 1 }}" class="rounded-lg border border-gray-300 px-3 py-1 hover:bg-gray-100 dark:border-gray-700 dark:hover:bg-gray-800">Prev</a>
        {% endif %}
        {% for p in range(1, pagination['last_page']) %}
            {% if p == pagination['current_page'] %}
                <span class="rounded-lg bg-brand-600 px-3 py-1 text-white">{{ p }}</span>
            {% else %}
                <a href="{{ base_url }}{% if base_url contains '?' %}&{% else %}?{% endif %}page={{ p }}" class="rounded-lg border border-gray-300 px-3 py-1 hover:bg-gray-100 dark:border-gray-700 dark:hover:bg-gray-800">{{ p }}</a>
            {% endif %}
        {% endfor %}
        {% if pagination['current_page'] < pagination['last_page'] %}
            <a href="{{ base_url }}{% if base_url contains '?' %}&{% else %}?{% endif %}page={{ pagination['current_page'] + 1 }}" class="rounded-lg border border-gray-300 px-3 py-1 hover:bg-gray-100 dark:border-gray-700 dark:hover:bg-gray-800">Next</a>
        {% endif %}
    </div>
</div>
{% endif %}
{% endblock %}
