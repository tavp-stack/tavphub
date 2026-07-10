<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ page_title | default('Dashboard') }} — {{ __brand }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-950 text-gray-100">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-gray-900 border-r border-gray-800 flex flex-col">
            <div class="p-6 border-b border-gray-800">
                <a href="{{ config('hub.admin_prefix', '/admin') }}" class="text-lg font-bold text-white">{{ __brand }}</a>
            </div>
            <nav class="flex-1 p-4 space-y-1">
                <a href="{{ config('hub.admin_prefix', '/admin') }}"
                   class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 hover:bg-gray-800 hover:text-white">
                    Dashboard
                </a>
                {% for item in __sidebar %}
                    <a href="{{ item['url'] }}"
                       class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 hover:bg-gray-800 hover:text-white">
                        {{ item['label'] }}
                    </a>
                {% endfor %}
            </nav>
            <div class="p-4 border-t border-gray-800">
                <form method="post" action="{{ config('hub.admin_prefix', '/admin') }}/logout">
                    <button type="submit" class="w-full text-left text-sm text-gray-500 hover:text-red-400">Sign out</button>
                </form>
            </div>
        </aside>

        <!-- Main content -->
        <main class="flex-1 p-8">
            {% block content %}{% endblock %}
        </main>
    </div>
</body>
</html>
