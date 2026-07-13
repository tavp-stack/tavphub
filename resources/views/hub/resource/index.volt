{% extends 'hub/layouts/admin.volt' %}

{% block content %}
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">{{ resource['label'] | default('Records') }}</h1>
    <a href="{{ config('hub.admin_prefix', '/admin') }}/resource/{{ resource_key }}/create"
       class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
        + New {{ resource['singular'] | default('Record') }}
    </a>
</div>

{# Resource metric cards #}
{% if metrics is not empty %}
<div class="grid grid-cols-1 gap-4 mb-6 sm:grid-cols-2 lg:grid-cols-4">
    {% for m in metrics %}
        <div class="rounded-lg bg-gray-900 border border-gray-800 p-5">
            <div class="text-sm text-gray-400">{{ m['label'] }}</div>
            <div class="text-3xl font-bold mt-1">{{ m['value'] }}</div>
            {% if m['delta'] is defined and m['delta'] != '' %}
                {% set dc = m['delta_color'] == 'green' ? 'text-green-400' : (m['delta_color'] == 'red' ? 'text-red-400' : 'text-gray-500') %}
                <div class="text-sm mt-1 {{ dc }}">{{ m['delta'] }}</div>
            {% endif %}
        </div>
    {% endfor %}
</div>
{% endif %}

{# Lenses switcher #}
{% if lenses is not empty %}
<div class="flex flex-wrap gap-2 mb-4">
    <a href="{{ config('hub.admin_prefix', '/admin') }}/resource/{{ resource_key }}"
       class="rounded-lg px-3 py-1.5 text-sm border {{ active_lens is empty ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-700 text-gray-300 hover:bg-gray-800' }}">
        All
    </a>
    {% for lens in lenses %}
        <a href="{{ config('hub.admin_prefix', '/admin') }}/resource/{{ resource_key }}/lens/{{ lens['name'] }}"
           class="rounded-lg px-3 py-1.5 text-sm border {{ active_lens == lens['name'] ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-700 text-gray-300 hover:bg-gray-800' }}">
            {{ lens['label'] }}
        </a>
    {% endfor %}
</div>
{% endif %}

{# Filters + search #}
{% if filters is not empty or search is defined %}
<form method="get" action="{{ config('hub.admin_prefix', '/admin') }}/resource/{{ resource_key }}" class="rounded-lg bg-gray-900 border border-gray-800 p-4 mb-4 flex flex-wrap items-end gap-3">
    {% for f in filters %}
        <div>
            <label class="block text-xs text-gray-400 mb-1">{{ f['label'] }}</label>
            {% set cur = filter_values[f['name']] | default('') %}
            {% if f['type'] == 'select' %}
                <select name="{{ f['name'] }}" class="rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-white">
                    <option value="all">All</option>
                    {% for opt in f['options'] %}
                        {% set ov = opt['value'] | default(opt) %}
                        {% set ol = opt['label'] | default(opt) %}
                        <option value="{{ ov }}" {% if ov == cur %}selected{% endif %}>{{ ol }}</option>
                    {% endfor %}
                </select>
            {% elseif f['type'] == 'date' %}
                <input type="date" name="{{ f['name'] }}" value="{{ cur }}" class="rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-white">
            {% elseif f['type'] == 'boolean' %}
                <select name="{{ f['name'] }}" class="rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-white">
                    <option value="all">All</option>
                    <option value="1" {% if cur == '1' %}selected{% endif %}>Yes</option>
                    <option value="0" {% if cur == '0' %}selected{% endif %}>No</option>
                </select>
            {% else %}
                <input type="text" name="{{ f['name'] }}" value="{{ cur }}" placeholder="{{ f['label'] }}" class="rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-white">
            {% endif %}
        </div>
    {% endfor %}
    <div>
        <label class="block text-xs text-gray-400 mb-1">Search</label>
        <input type="text" name="search" value="{{ search | default('') }}" placeholder="Search..." class="rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-white">
    </div>
    <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Apply</button>
</form>
{% endif %}

{# Bulk action bar #}
{% if actions is not empty %}
<form id="bulk-form" method="post"
      action="{{ config('hub.admin_prefix', '/admin') }}/resource/{{ resource_key }}/action/__PLACEHOLDER__"
      class="flex items-center gap-3 mb-4">
    <select id="bulk-action" class="rounded-lg border border-gray-700 bg-gray-900 px-3 py-2 text-sm text-white">
        <option value="">Bulk action...</option>
        {% for a in actions %}
            <option value="{{ a['name'] }}">{{ a['label'] }}</option>
        {% endfor %}
    </select>
    <button type="submit" class="rounded-lg border border-gray-700 px-4 py-2 text-sm text-gray-300 hover:bg-gray-800">Run</button>
</form>
<script>
document.getElementById('bulk-form').addEventListener('submit', function (e) {
    var act = document.getElementById('bulk-action').value;
    if (!act) { e.preventDefault(); return; }
    this.action = this.action.replace('__PLACEHOLDER__', act);
});
</script>
{% endif %}

<div class="rounded-lg bg-gray-900 border border-gray-800 overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-800">
            <tr>
                {% if actions is not empty %}<th class="px-4 py-3"></th>{% endif %}
                {% for col in columns %}
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">{{ col['label'] | default(col['field'] | default('')) }}</th>
                {% endfor %}
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-800">
            {% for record in records %}
                <tr class="hover:bg-gray-800/50">
                    {% if actions is not empty %}
                        <td class="px-4 py-4">
                            <input type="checkbox" form="bulk-form" name="ids[]" value="{{ record['id'] | default('') }}">
                        </td>
                    {% endif %}
                    {% for col in columns %}
                        <td class="px-6 py-4 text-sm text-gray-300">{{ record[col['field'] | default(col['key'] | default(''))] | default('') }}</td>
                    {% endfor %}
                    <td class="px-6 py-4 text-sm">
                        <a href="{{ config('hub.admin_prefix', '/admin') }}/resource/{{ resource_key }}/{{ record['id'] | default('') }}/edit"
                           class="text-blue-400 hover:underline mr-3">Edit</a>
                        <form method="post" action="{{ config('hub.admin_prefix', '/admin') }}/resource/{{ resource_key }}/{{ record['id'] | default('') }}/delete" class="inline">
                            <button type="submit" class="text-red-400 hover:underline" onclick="return confirm('Delete this record?')">Delete</button>
                        </form>
                    </td>
                </tr>
            {% endfor %}

            {% if records is empty %}
                <tr>
                    <td colspan="{{ columns | length + 1 + (actions is not empty ? 1 : 0) }}" class="px-6 py-12 text-center text-gray-500">
                        No records found.
                    </td>
                </tr>
            {% endif %}
        </tbody>
    </table>
</div>

{# Pagination #}
{% if pagination is defined and pagination['last_page'] > 1 %}
<div class="flex items-center justify-between mt-4 text-sm text-gray-400">
    <div>Page {{ pagination['current_page'] }} of {{ pagination['last_page'] }} ({{ pagination['total'] }} records)</div>
    <div class="flex gap-1">
        {% if pagination['current_page'] > 1 %}
            <a href="{{ base_url }}{% if base_url contains '?' %}&{% else %}?{% endif %}page={{ pagination['current_page'] - 1 }}" class="px-3 py-1 border rounded hover:bg-gray-800">Prev</a>
        {% endif %}
        {% for p in range(1, pagination['last_page']) %}
            {% if p == pagination['current_page'] %}
                <span class="px-3 py-1 bg-blue-600 text-white rounded">{{ p }}</span>
            {% else %}
                <a href="{{ base_url }}{% if base_url contains '?' %}&{% else %}?{% endif %}page={{ p }}" class="px-3 py-1 border rounded hover:bg-gray-800">{{ p }}</a>
            {% endif %}
        {% endfor %}
        {% if pagination['current_page'] < pagination['last_page'] %}
            <a href="{{ base_url }}{% if base_url contains '?' %}&{% else %}?{% endif %}page={{ pagination['current_page'] + 1 }}" class="px-3 py-1 border rounded hover:bg-gray-800">Next</a>
        {% endif %}
    </div>
</div>
{% endif %}
{% endblock %}
