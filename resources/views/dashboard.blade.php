<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-primary-dark leading-tight">
            @php
                $hour = date('H');
                if ($hour < 12) {
                    $greeting = 'Good morning';
                } elseif ($hour < 18) {
                    $greeting = 'Good afternoon';
                } else {
                    $greeting = 'Good evening';
                }
            @endphp
            {{ $greeting }}, {{ Auth::user()->name }}!
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Quick Actions Panel -->
            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6">
                    <h3 class="text-white text-lg font-semibold mb-4">Quick Actions</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <a href="{{ route('members.index') ?? '#' }}" class="flex flex-col items-center justify-center p-4 bg-white/20 hover:bg-white/30 rounded-lg transition backdrop-blur-sm">
                            <svg class="w-8 h-8 text-white mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                            </svg>
                            <span class="text-white text-sm font-medium">Add Member</span>
                        </a>
                        <a href="{{ route('donations.create') ?? '#' }}" class="flex flex-col items-center justify-center p-4 bg-white/20 hover:bg-white/30 rounded-lg transition backdrop-blur-sm">
                            <svg class="w-8 h-8 text-white mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="text-white text-sm font-medium">Record Donation</span>
                        </a>
                        <a href="{{ route('events.create') ?? '#' }}" class="flex flex-col items-center justify-center p-4 bg-white/20 hover:bg-white/30 rounded-lg transition backdrop-blur-sm">
                            <svg class="w-8 h-8 text-white mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span class="text-white text-sm font-medium">Create Event</span>
                        </a>
                        <a href="{{ route('groups.index') ?? '#' }}" class="flex flex-col items-center justify-center p-4 bg-white/20 hover:bg-white/30 rounded-lg transition backdrop-blur-sm">
                            <svg class="w-8 h-8 text-white mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <span class="text-white text-sm font-medium">Manage Groups</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-neutral-dark">
                    <h2 class="text-2xl font-bold mb-6">Church Management Dashboard</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                        <!-- Members Card -->
                        <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-blue-500 hover:shadow-lg transition cursor-pointer" onclick="window.location='{{ route('members.index') ?? '#' }}'">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold">Members</h3>
                                    <p class="text-gray-600 text-sm">Manage church members</p>
                                    <p class="text-2xl font-bold text-blue-600 mt-2">--</p>
                                </div>
                                <div class="rounded-full bg-blue-100 p-4">
                                    <svg class="h-8 w-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="mt-4 flex items-center text-sm">
                                <span class="text-green-600 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"/></svg>
                                    View all
                                </span>
                            </div>
                        </div>

                        <!-- Groups Card -->
                        <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-green-500 hover:shadow-lg transition cursor-pointer" onclick="window.location='{{ route('groups.index') ?? '#' }}'">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold">Groups</h3>
                                    <p class="text-gray-600 text-sm">Manage ministry groups</p>
                                    <p class="text-2xl font-bold text-green-600 mt-2">--</p>
                                </div>
                                <div class="rounded-full bg-green-100 p-4">
                                    <svg class="h-8 w-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="mt-4 flex items-center text-sm">
                                <span class="text-green-600 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"/></svg>
                                    View all
                                </span>
                            </div>
                        </div>

                        <!-- Events Card -->
                        <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-purple-500 hover:shadow-lg transition cursor-pointer" onclick="window.location='{{ route('events.index') ?? '#' }}'">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold">Events</h3>
                                    <p class="text-gray-600 text-sm">Schedule and manage events</p>
                                    <p class="text-2xl font-bold text-purple-600 mt-2">--</p>
                                </div>
                                <div class="rounded-full bg-purple-100 p-4">
                                    <svg class="h-8 w-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="mt-4 flex items-center text-sm">
                                <span class="text-green-600 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"/></svg>
                                    View all
                                </span>
                            </div>
                        </div>

                        <!-- Finance Card -->
                        <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-yellow-500 hover:shadow-lg transition cursor-pointer" onclick="window.location='{{ route('donations.index') ?? '#' }}'">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold">Finance</h3>
                                    <p class="text-gray-600 text-sm">Manage donations and expenses</p>
                                    <p class="text-2xl font-bold text-yellow-600 mt-2">--</p>
                                </div>
                                <div class="rounded-full bg-yellow-100 p-4">
                                    <svg class="h-8 w-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="mt-4 flex items-center text-sm">
                                <span class="text-green-600 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"/></svg>
                                    View all
                                </span>
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