<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ brand }} — Sign In</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-950 flex items-center justify-center">
    <div class="w-full max-w-sm">
        <h1 class="text-2xl font-bold text-white text-center mb-8">{{ brand }}</h1>

        {% if error %}
            <div class="mb-4 rounded bg-red-900/50 border border-red-700 p-3 text-sm text-red-200">{{ error }}</div>
        {% endif %}

        <form method="post" action="{{ config('hub.admin_prefix', '/admin') }}/login" class="space-y-4">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-300 mb-1">Email address</label>
                <input type="email" name="email" id="email" required
                    class="w-full rounded-lg border border-gray-700 bg-gray-900 px-4 py-3 text-white placeholder-gray-500 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
            </div>
            <button type="submit" class="w-full rounded-lg bg-blue-600 px-4 py-3 font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-gray-950">
                Send one-time code
            </button>
        </form>
    </div>
</body>
</html>
