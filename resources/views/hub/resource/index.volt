{% extends 'hub/layouts/admin.volt' %}

{% block content %}
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">{{ resource['label'] | default('Records') }}</h1>
    <a href="{{ config('hub.admin_prefix', '/admin') }}/resource/{{ resource_key }}/create"
       class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
        + New {{ resource['singular'] | default('Record') }}
    </a>
</div>

<div class="rounded-lg bg-gray-900 border border-gray-800 overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-800">
            <tr>
                {% for col in columns %}
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">{{ col['label'] | default(col['field'] | default('')) }}</th>
                {% endfor %}
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-800">
            {% for record in records %}
                <tr class="hover:bg-gray-800/50">
                    {% for col in columns %}
                        <td class="px-6 py-4 text-sm text-gray-300">{{ record[col['field'] | default('')] | default('') }}</td>
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
                    <td colspan="{{ columns | length + 1 }}" class="px-6 py-12 text-center text-gray-500">
                        No records found.
                    </td>
                </tr>
            {% endif %}
        </tbody>
    </table>
</div>
{% endblock %}
