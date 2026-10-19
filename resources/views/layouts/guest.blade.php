<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>BSH ASSET - Login</title>

        <!-- Fonts: Lora for Titles, Inter for Body -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

        <!-- Scripts -->
        <script src="https://cdn.tailwindcss.com"></script>
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

        <style>
            body { font-family: 'Inter', sans-serif; background-color: #f4f1ea; }
            .font-serif { font-family: 'Lora', serif; }
        </style>
    </head>
    <body class="text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
            <div class="text-center mb-8">
                <a href="/" class="flex flex-col items-center gap-2">
                    <div class="w-16 h-16 bg-[#5c6b5b] rounded-2xl flex items-center justify-center text-white text-3xl shadow-lg">
                        🏰
                    </div>
                    <h1 class="font-serif text-3xl font-bold text-[#4a554a] tracking-tight mt-2">BSH FIX</h1>
                    <p class="text-xs text-[#a47b53] uppercase tracking-[0.3em] font-bold">Asset Management System</p>
                </a>
            </div>

            <div class="w-full sm:max-w-md px-8 py-10 bg-white shadow-2xl overflow-hidden sm:rounded-[2rem] border border-[#e5e0d8]">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
