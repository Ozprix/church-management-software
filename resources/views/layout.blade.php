<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Church Management System') }}</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 font-sans antialiased">
    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <header class="bg-white shadow-md flex items-center justify-between px-4 py-3 md:py-0">
            <div class="flex items-center">
                <!-- Hamburger for mobile -->
                <button id="sidebarToggle" class="md:hidden mr-3 focus:outline-none">
                    <svg class="w-6 h-6 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <span class="font-bold text-xl text-blue-700">{{ config('app.name', 'Church Mgt') }}</span>
            </div>
            <!-- User Profile Dropdown -->
            <div class="relative">
                <button id="userMenuButton" class="flex items-center focus:outline-none">
                    <span class="mr-2 text-gray-700 font-medium">{{ Auth::user()->name ?? 'User' }}</span>
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name ?? 'User') }}&background=2563eb&color=fff" class="w-8 h-8 rounded-full" alt="User Avatar">
                </button>
                <div id="userDropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded shadow z-50">
                    <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Profile</a>
                    <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Settings</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100">Logout</button>
                    </form>
                </div>
            </div>
        </header>
        <div class="flex flex-1">
            <!-- Sidebar -->
            <aside id="sidebar" class="w-64 bg-white shadow-md fixed md:static inset-y-0 left-0 transform -translate-x-full md:translate-x-0 transition-transform duration-200 z-40 md:block">
                <div class="p-6 font-bold text-xl text-blue-700 border-b md:hidden">{{ config('app.name', 'Church Mgt') }}</div>
                <nav class="mt-6">
                    <a href="/dashboard" class="block py-2.5 px-4 rounded hover:bg-blue-50 text-gray-700 font-medium">Dashboard</a>
                    <a href="/members" class="block py-2.5 px-4 rounded hover:bg-blue-50 text-gray-700 font-medium">Members</a>
                    <a href="/donations" class="block py-2.5 px-4 rounded hover:bg-blue-50 text-gray-700 font-medium">Donations</a>
                    <a href="/events" class="block py-2.5 px-4 rounded hover:bg-blue-50 text-gray-700 font-medium">Events</a>
                    <a href="/reports" class="block py-2.5 px-4 rounded hover:bg-blue-50 text-gray-700 font-medium">Reports</a>
                </nav>
            </aside>
            <!-- Overlay for mobile -->
            <div id="sidebarOverlay" class="fixed inset-0 bg-black bg-opacity-30 z-30 hidden md:hidden"></div>
            <!-- Main Content -->
            <main class="flex-1 p-6 md:ml-0 ml-0 mt-16 md:mt-0">
                @yield('content')
            </main>
        </div>
        <script>
            // Sidebar toggle for mobile
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', () => {
                    sidebar.classList.toggle('-translate-x-full');
                    sidebarOverlay.classList.toggle('hidden');
                });
            }
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', () => {
                    sidebar.classList.add('-translate-x-full');
                    sidebarOverlay.classList.add('hidden');
                });
            }
            // User dropdown
            const userMenuButton = document.getElementById('userMenuButton');
            const userDropdown = document.getElementById('userDropdown');
            if (userMenuButton && userDropdown) {
                userMenuButton.addEventListener('click', () => {
                    userDropdown.classList.toggle('hidden');
                });
                document.addEventListener('click', (e) => {
                    if (!userMenuButton.contains(e.target) && !userDropdown.contains(e.target)) {
                        userDropdown.classList.add('hidden');
                    }
                });
            }
        </script>
    </div>
</body>

</html>