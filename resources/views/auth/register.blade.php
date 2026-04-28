<x-guest-layout>
    <form method="POST" action="{{ route('register') }}" novalidate>
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" 
                aria-describedby="name-error"
                placeholder="Enter your full name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" id="name-error" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <div class="relative">
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" 
                    aria-describedby="email-error email-valid"
                    placeholder="Enter your email address" />
                <span class="absolute right-3 top-9 text-green-600 hidden" id="email-valid-icon" aria-hidden="true">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                </span>
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" id="email-error" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <div class="relative">
                <x-text-input id="password" class="block mt-1 w-full pr-10"
                                type="password"
                                name="password"
                                required autocomplete="new-password"
                                aria-describedby="password-error password-strength password-help"
                                placeholder="Create a strong password" />
                <button type="button" 
                        class="absolute right-3 top-9 text-gray-500 hover:text-gray-700 focus:outline-none focus:text-indigo-600"
                        onclick="togglePasswordVisibility('password', this)"
                        aria-label="Toggle password visibility"
                        tabindex="-1">
                    <svg class="w-5 h-5 eye-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <svg class="w-5 h-5 eye-off-icon hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                    </svg>
                </button>
            </div>

            <x-input-error :messages="$errors->get('password')" class="mt-2" id="password-error" />
            
            <!-- Password Strength Indicator -->
            <div id="password-strength" class="mt-2" aria-live="polite">
                <div class="flex gap-1 mb-1">
                    <div class="h-1 flex-1 bg-gray-200 rounded" id="strength-bar-1"></div>
                    <div class="h-1 flex-1 bg-gray-200 rounded" id="strength-bar-2"></div>
                    <div class="h-1 flex-1 bg-gray-200 rounded" id="strength-bar-3"></div>
                    <div class="h-1 flex-1 bg-gray-200 rounded" id="strength-bar-4"></div>
                </div>
                <p class="text-xs text-gray-500" id="strength-text">Password strength: Not entered</p>
            </div>
            
            <p id="password-help" class="text-xs text-gray-500 mt-1">Minimum 8 characters. Use uppercase, lowercase, numbers, and symbols for stronger password.</p>
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <div class="relative">
                <x-text-input id="password_confirmation" class="block mt-1 w-full pr-10"
                                type="password"
                                name="password_confirmation" 
                                required autocomplete="new-password"
                                aria-describedby="password-confirm-error password-confirm-valid"
                                placeholder="Confirm your password" />
                <span class="absolute right-3 top-9 text-green-600 hidden" id="password-match-icon" aria-hidden="true">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                </span>
            </div>

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" id="password-confirm-error" />
            <p id="password-confirm-valid" class="text-xs text-green-600 mt-1 hidden" role="status">Passwords match!</p>
        </div>

        <div class="flex items-center justify-end mt-6">
            <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>

    <script>
        function togglePasswordVisibility(inputId, button) {
            const input = document.getElementById(inputId);
            const eyeIcon = button.querySelector('.eye-icon');
            const eyeOffIcon = button.querySelector('.eye-off-icon');
            
            if (input.type === 'password') {
                input.type = 'text';
                eyeIcon.classList.add('hidden');
                eyeOffIcon.classList.remove('hidden');
            } else {
                input.type = 'password';
                eyeIcon.classList.remove('hidden');
                eyeOffIcon.classList.add('hidden');
            }
        }

        function calculatePasswordStrength(password) {
            let score = 0;
            if (password.length >= 8) score++;
            if (password.length >= 12) score++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) score++;
            if (/\d/.test(password)) score++;
            if (/[^a-zA-Z0-9]/.test(password)) score++;
            return Math.min(score, 4);
        }

        function updateStrengthIndicator(strength) {
            const colors = ['bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-green-500'];
            const texts = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
            
            for (let i = 1; i <= 4; i++) {
                const bar = document.getElementById(`strength-bar-${i}`);
                if (i <= strength) {
                    bar.className = `h-1 flex-1 rounded ${colors[Math.min(strength - 1, 3)]}`;
                } else {
                    bar.className = 'h-1 flex-1 bg-gray-200 rounded';
                }
            }
            
            const textEl = document.getElementById('strength-text');
            textEl.textContent = `Password strength: ${strength > 0 ? texts[strength] : 'Not entered'}`;
            textEl.className = `text-xs ${strength <= 2 ? 'text-red-600' : strength === 3 ? 'text-yellow-600' : 'text-green-600'}`;
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Email validation
            const emailInput = document.getElementById('email');
            const emailValidIcon = document.getElementById('email-valid-icon');
            
            if (emailInput) {
                emailInput.addEventListener('blur', function() {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (emailRegex.test(this.value)) {
                        emailValidIcon.classList.remove('hidden');
                    } else {
                        emailValidIcon.classList.add('hidden');
                    }
                });
                
                emailInput.addEventListener('input', function() {
                    emailValidIcon.classList.add('hidden');
                });
            }

            // Password strength
            const passwordInput = document.getElementById('password');
            if (passwordInput) {
                passwordInput.addEventListener('input', function() {
                    const strength = calculatePasswordStrength(this.value);
                    updateStrengthIndicator(strength);
                    validatePasswordMatch();
                });
            }

            // Password match validation
            const confirmInput = document.getElementById('password_confirmation');
            const matchIcon = document.getElementById('password-match-icon');
            const matchText = document.getElementById('password-confirm-valid');
            
            function validatePasswordMatch() {
                if (confirmInput && passwordInput) {
                    if (confirmInput.value && passwordInput.value === confirmInput.value) {
                        matchIcon.classList.remove('hidden');
                        matchText.classList.remove('hidden');
                    } else {
                        matchIcon.classList.add('hidden');
                        matchText.classList.add('hidden');
                    }
                }
            }

            if (confirmInput) {
                confirmInput.addEventListener('input', validatePasswordMatch);
                confirmInput.addEventListener('blur', validatePasswordMatch);
            }
        });
    </script>
</x-guest-layout>
