<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Church Management System') }}</title>
    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">
    <div class="flex flex-col items-center justify-center min-h-screen bg-gray-100">
        <div class="bg-white p-8 rounded shadow text-center">
            <h1 class="text-3xl font-bold mb-4 text-blue-700">Welcome to Church Management System</h1>
            <p class="mb-6 text-gray-600">A modern platform to manage your church activities efficiently.</p>
            <a href="/dashboard" class="inline-block px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">Go to Dashboard</a>
        </div>
    </div>
</body>

</html>