<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-primary-dark leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-neutral-dark">
                    <h2 class="text-2xl font-bold mb-6">Church Management Dashboard</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                        <!-- Members Card -->
                        <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-blue-500 hover:shadow-lg transition">
                            <div class="flex items-center">
                                <div class="rounded-full bg-blue-100 p-3 mr-4">
                                    <svg class="h-8 w-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold">Members</h3>
                                    <p class="text-gray-600">Manage church members</p>
                                </div>
                            </div>
                        </div>

                        <!-- Groups Card -->
                        <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-green-500 hover:shadow-lg transition">
                            <div class="flex items-center">
                                <div class="rounded-full bg-green-100 p-3 mr-4">
                                    <svg class="h-8 w-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold">Groups</h3>
                                    <p class="text-gray-600">Manage ministry groups</p>
                                </div>
                            </div>
                        </div>

                        <!-- Events Card -->
                        <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-purple-500 hover:shadow-lg transition">
                            <div class="flex items-center">
                                <div class="rounded-full bg-purple-100 p-3 mr-4">
                                    <svg class="h-8 w-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold">Events</h3>
                                    <p class="text-gray-600">Schedule and manage events</p>
                                </div>
                            </div>
                        </div>

                        <!-- Finance Card -->
                        <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-yellow-500 hover:shadow-lg transition">
                            <div class="flex items-center">
                                <div class="rounded-full bg-yellow-100 p-3 mr-4">
                                    <svg class="h-8 w-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold">Finance</h3>
                                    <p class="text-gray-600">Manage donations and expenses</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Group Management Section -->
                    <div class="bg-white p-6 rounded-lg shadow-md mb-8">
                        <h3 class="text-xl font-bold mb-4">Group Management Features</h3>
                        <p class="mb-4">Access our new group management features to enhance ministry coordination and effectiveness:</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <a href="{{ route('group.analytics') }}" class="block p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                                <h4 class="text-lg font-semibold text-blue-700 mb-2">Group Analytics Dashboard</h4>
                                <p class="text-gray-600">View attendance trends, member engagement metrics, and demographic breakdowns.</p>
                            </a>
                            
                            <a href="{{ route('group.communication') }}" class="block p-4 bg-green-50 rounded-lg hover:bg-green-100 transition">
                                <h4 class="text-lg font-semibold text-green-700 mb-2">Group Communication Tools</h4>
                                <p class="text-gray-600">Send messages, announcements, prayer requests, and share documents with group members.</p>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>