<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ brand }} — Verify Code</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'] },
                    colors: {
                        brand: {
                            50: '#eef2ff', 100: '#e0e7ff', 200: '#c7d2fe', 300: '#a5b4fc',
                            400: '#818cf8', 500: '#6366f1', 600: '#4f46e5', 700: '#4338ca',
                            800: '#3730a3', 900: '#312e81'
                        }
                    }
                }
            }
        };
    </script>
    <script>
        (function () {
            var t = localStorage.getItem('hub-theme');
            if (!t) {
                t = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }
            if (t === 'dark') {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="flex min-h-screen items-center justify-center bg-gray-100 px-4 dark:bg-gray-950">
    <div class="w-full max-w-sm">
        <div class="mb-7 text-center">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-600 text-2xl font-extrabold text-white">T</div>
            <h1 class="text-2xl font-bold">{{ brand }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Enter the code sent to {{ identifier }}</p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-8 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            {% if error %}
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">{{ error }}</div>
            {% endif %}

            <form method="post" action="{{ config('hub.admin_prefix', '/admin') }}/verify" class="space-y-4">
                <div>
                    <label for="code" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Verification code</label>
                    <input type="text" name="code" id="code" maxlength="6" pattern="[0-9]{6}" autocomplete="one-time-code"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-center text-2xl tracking-[0.5em] text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                        placeholder="000000" required>
                </div>
                <button type="submit" class="w-full rounded-lg bg-brand-600 px-4 py-3 text-sm font-semibold text-white hover:bg-brand-700">Verify</button>
            </form>
        </div>

        <p class="mt-6 text-center text-sm text-gray-500 dark:text-gray-400">
            <a href="{{ config('hub.admin_prefix', '/admin') }}/login" class="font-medium text-brand-600 hover:underline dark:text-brand-400">Use a different email</a>
        </p>
    </div>
</body>
</html>
