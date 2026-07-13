{% extends 'hub/layouts/admin.volt' %}

{% block content %}
{% if flash_html is defined and flash_html != '' %}
    {% autoescape false %}{{ flash_html }}{% autoescape true %}
{% endif %}

<div class="mb-6">
    <nav class="text-xs text-gray-400">Dashboard <span class="px-1">/</span> <span class="text-gray-600 dark:text-gray-300">Overview</span></nav>
    <h1 class="mt-1 text-2xl font-bold">Dashboard</h1>
    <p class="text-sm text-gray-500 dark:text-gray-400">Welcome back. Here's what's happening with your app today.</p>
</div>

{% if metric_html is defined and metric_html != '' %}
    {% autoescape false %}{{ metric_html }}{% autoescape true %}
{% endif %}

{% if stats_html is defined and stats_html != '' %}
    {% autoescape false %}{{ stats_html }}{% autoescape true %}
{% endif %}

{% if stats is empty and (metric_html | default('')) == '' %}
    <div class="rounded-xl border border-gray-200 bg-white p-6 text-center text-gray-500 shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400">
        No resources configured. Register resources via <code>ResourceRegistry</code> or config('hub.resources').
    </div>
{% endif %}

{% endblock %}
