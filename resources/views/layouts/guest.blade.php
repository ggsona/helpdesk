<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script>
            const savedTheme = localStorage.getItem("theme") || "light";
            document.documentElement.setAttribute("data-bs-theme", savedTheme);
        </script>
        <style>
            :root {
                --guest-bg: #f3f4f6;
                --guest-card-bg: #ffffff;
                --guest-text: #111827;
                --guest-border: #e5e7eb;
            }
            [data-bs-theme="dark"] {
                --guest-bg: #0b0c0d;
                --guest-card-bg: #17191b;
                --guest-text: #e5e7eb;
                --guest-border: #2d2f31;
            }
            body {
                background-color: var(--guest-bg) !important;
                color: var(--guest-text) !important;
                transition: background-color 0.3s ease, color 0.3s ease;
            }
            .bg-gray-100 {
                background-color: var(--guest-bg) !important;
            }
            .bg-white {
                background-color: var(--guest-card-bg) !important;
                border: 1px solid var(--guest-border);
            }
            .text-gray-900 {
                color: var(--guest-text) !important;
            }
            [data-bs-theme="dark"] input,
            [data-bs-theme="dark"] select,
            [data-bs-theme="dark"] textarea {
                background-color: #1f2327 !important;
                color: var(--guest-text) !important;
                border-color: #374151 !important;
            }
            [data-bs-theme="dark"] label,
            [data-bs-theme="dark"] p,
            [data-bs-theme="dark"] span {
                color: #d1d5db !important;
            }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
            <div>
                <a href="/">
                    <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
