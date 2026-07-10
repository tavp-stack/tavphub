{% extends 'hub/layouts/admin.volt' %}

{% block content %}
<h1 class="text-2xl font-bold mb-6">Dashboard</h1>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    {% for key, stat in stats %}
        <div class="rounded-lg bg-gray-900 border border-gray-800 p-6">
            <div class="text-sm text-gray-400 mb-1">{{ stat['label'] }}</div>
            <div class="text-3xl font-bold">{{ stat['count'] }}</div>
        </div>
    {% endfor %}

    {% if stats is empty %}
        <div class="col-span-3 rounded-lg bg-gray-900 border border-gray-800 p-6 text-center text-gray-500">
            No resources configured. Add resources to config('hub.resources').
        </div>
    {% endif %}
</div>

{% endblock %}
