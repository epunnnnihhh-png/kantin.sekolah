<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title inertia>EcoBites Canteen</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=outfit:400,500,600,700,800&display=swap" rel="stylesheet" />
    
    <!-- Tailwind CSS (via CDN fallback for quick dev if npm fails) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Outfit', 'sans-serif'] },
                    colors: { primary: '#10b981', secondary: '#f59e0b' }
                }
            }
        }
    </script>
    <style type="text/tailwindcss">
        @layer utilities {
            .glass {
                @apply bg-white/70 backdrop-blur-lg border border-white/40 shadow-xl;
            }
        }
    </style>

    @viteReactRefresh
    @vite(['resources/js/app.jsx', "resources/js/Pages/{$page['component']}.jsx"])
    @inertiaHead
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-800">
    @inertia
</body>
</html>
