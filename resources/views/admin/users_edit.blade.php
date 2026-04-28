@extends('admin.dashboard')

@section('admin-content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center text-indigo-600 hover:text-indigo-900 mb-2">
            <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Users
        </a>
        <h2 class="text-2xl font-bold text-gray-900">Edit User</h2>
        <p class="text-gray-500 mt-1">Update user information and role assignments.</p>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('admin.users.update', $user) }}">
            @csrf
            @method('PUT')
            
            <div class="space-y-6">
                <!-- User Avatar -->
                <div class="flex items-center gap-4 pb-6 border-b border-gray-200">
                    <x-avatar :name="$user->name" :email="$user->email" size="lg"/>
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">{{ $user->name }}</h3>
                        <p class="text-sm text-gray-500">{{ $user->email }}</p>
                    </div>
                </div>

                <!-- Name Field -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                        Full Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="name"
                           name="name" 
                           value="{{ old('name', $user->name) }}" 
                           placeholder="John Doe"
                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-500 @enderror"
                           required>
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email Field -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                        Email Address <span class="text-red-500">*</span>
                    </label>
                    <input type="email" 
                           id="email"
                           name="email" 
                           value="{{ old('email', $user->email) }}" 
                           placeholder="john@example.com"
                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('email') border-red-500 @enderror"
                           required>
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Role Selection -->
                <div>
                    <label for="role_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Role <span class="text-red-500">*</span>
                    </label>
                    <select id="role_id"
                            name="role_id"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('role_id') border-red-500 @enderror"
                            required>
                        <option value="">Select a role</option>
                        @foreach($roles ?? [] as $role)
                            <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('role_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status Toggle -->
                <div class="flex items-center justify-between">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Account Status</label>
                        <p class="text-sm text-gray-500">User can log in when active</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active ?? true) ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        <span class="ml-3 text-sm font-medium text-gray-700">Active</span>
                    </label>
                </div>

                <!-- Password Field (Optional) -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                        New Password 
                        <span class="text-xs text-gray-500">(leave blank to keep current)</span>
                    </label>
                    <div class="relative">
                        <input type="password" 
                               id="password"
                               name="password" 
                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 pr-10 @error('password') border-red-500 @enderror">
                        <button type="button" 
                                onclick="togglePassword('password')" 
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Minimum 8 characters, include uppercase, lowercase, and number</p>
                </div>

                <!-- Password Confirmation -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                        Confirm New Password
                    </label>
                    <div class="relative">
                        <input type="password" 
                               id="password_confirmation"
                               name="password_confirmation" 
                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 pr-10">
                        <button type="button" 
                                onclick="togglePassword('password_confirmation')" 
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                    <p id="password-match-error" class="mt-1 text-sm text-red-600 hidden">Passwords do not match</p>
                </div>

                <!-- Last Login Info -->
                @if($user->last_login_at)
                <div class="bg-gray-50 rounded-md p-4">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-sm text-gray-600">Last login: <strong>{{ $user->last_login_at->diffForHumans() }}</strong> ({{ $user->last_login_at->format('M d, Y g:i A') }})</span>
                    </div>
                </div>
                @endif
            </div>

            <!-- Form Actions -->
            <div class="mt-8 flex justify-end gap-3 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.users.index') }}" 
                   class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md transition shadow-sm">
                    Update User
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = input.parentElement.querySelector('svg');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.597a3 3 0 002.122-.879m.907-3.579a3 3 0 00-4.242 0m-2.122 5.658a3 3 0 004.242 0m2.122-5.658a9.97 9.97 0 011.563 3.029c.391.965.666 1.995.802 3.029"/>';
    } else {
        input.type = 'password';
        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>';
    }
}

// Password match validation (only if password field has value)
document.getElementById('password_confirmation').addEventListener('blur', function() {
    const password = document.getElementById('password').value;
    const confirmPassword = this.value;
    const errorElement = document.getElementById('password-match-error');
    
    if (password && confirmPassword && password !== confirmPassword) {
        errorElement.classList.remove('hidden');
    } else {
        errorElement.classList.add('hidden');
    }
});
</script>
@endpush
@endsection