@extends('layout')

@section('content')
<div class="max-w-2xl mx-auto bg-white rounded shadow p-8 mt-10">
    <h1 class="text-3xl font-bold text-blue-700 mb-4">Admin Dashboard</h1>
    <p class="mb-6 text-gray-600">Welcome, Super User! You have full access to manage the system.</p>
    <ul class="list-disc pl-6 text-gray-700">
        <li>Manage users and roles</li>
        <li>View system analytics</li>
        <li>Access all church management features</li>
    </ul>
</div>
@if(View::hasSection('admin-content'))
<div class="mt-8">
    @yield('admin-content')
</div>
@endif
@endsection