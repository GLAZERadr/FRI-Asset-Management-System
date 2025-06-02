@extends('layouts.auth')

@section('content')
<div class="bg-white p-6 md:p-8 rounded-lg shadow-md mx-4 md:mx-0">
    <h2 class="text-xl md:text-2xl font-semibold text-center mb-6">Login</h2>

    @if ($errors->any())
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
            <ul class="list-disc list-inside text-sm md:text-base">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-4">
            <label for="username" class="block text-gray-700 mb-2 text-sm md:text-base">Username</label>
            <input type="text" 
                   id="username" 
                   name="username" 
                   value="{{ old('username') }}" 
                   class="w-full px-4 py-3 md:py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-green-600 text-base md:text-sm" 
                   required 
                   autofocus>
        </div>

        <div class="mb-4">
            <label for="password" class="block text-gray-700 mb-2 text-sm md:text-base">Password</label>
            <div class="relative">
                <input type="password" 
                       id="password" 
                       name="password" 
                       class="w-full px-4 py-3 md:py-2 pr-12 border rounded-md focus:outline-none focus:ring-2 focus:ring-green-600 text-base md:text-sm" 
                       required>
                <button type="button" 
                        onclick="togglePassword()" 
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600">
                    <svg id="eye-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    <svg id="eye-slash-icon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
                    </svg>
                </button>
            </div>
        </div>

        <div class="flex items-center mb-4">
            <input type="checkbox" 
                   id="remember" 
                   name="remember" 
                   class="mr-2 w-4 h-4 text-green-600 bg-gray-100 border-gray-300 rounded focus:ring-green-500">
            <label for="remember" class="text-gray-600 text-sm md:text-base select-none cursor-pointer">Remember me</label>
        </div>

        <button type="submit" 
                class="w-full py-3 md:py-2 px-4 bg-green-600 text-white rounded-md hover:bg-green-700 transition duration-200 text-base md:text-sm font-medium">
            Log in
        </button>
    </form>

    <div class="mt-4 text-center">
        <p class="text-gray-600 text-sm md:text-base">
            Don't have an account? 
            <a href="{{ route('register') }}" class="text-green-600 hover:underline">Register</a>
        </p>
    </div>
</div>

<style>
    /* Mobile-specific improvements */
    @media (max-width: 640px) {
        /* Prevent zoom on input focus for iOS */
        input[type="text"],
        input[type="password"] {
            font-size: 16px !important;
        }
        
        /* Better spacing on mobile */
        .bg-white {
            margin: 1rem;
        }
    }

    /* Ensure good touch targets on mobile */
    @media (hover: none) and (pointer: coarse) {
        input, button, a, label {
            min-height: 44px;
            display: flex;
            align-items: center;
        }
        
        /* Special handling for checkbox */
        input[type="checkbox"] {
            min-height: auto;
            width: 18px;
            height: 18px;
        }
    }

    /* Smooth transitions */
    input:focus {
        transition: all 0.2s ease-in-out;
    }

    button {
        transition: all 0.2s ease-in-out;
    }

    button:active {
        transform: scale(0.98);
    }
</style>

<script>
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eye-icon');
        const eyeSlashIcon = document.getElementById('eye-slash-icon');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.classList.add('hidden');
            eyeSlashIcon.classList.remove('hidden');
        } else {
            passwordInput.type = 'password';
            eyeIcon.classList.remove('hidden');
            eyeSlashIcon.classList.add('hidden');
        }
    }

    // Mobile optimizations
    document.addEventListener('DOMContentLoaded', function() {
        // Only auto-focus on desktop to prevent mobile keyboard popup
        if (window.innerWidth > 768) {
            document.getElementById('username').focus();
        }

        // Handle form submission
        document.querySelector('form').addEventListener('submit', function(e) {
            const button = this.querySelector('button[type="submit"]');
            button.innerHTML = `
                <span class="flex items-center justify-center">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Logging in...
                </span>
            `;
            button.disabled = true;
        });

        // Prevent double-tap zoom on buttons for iOS
        if (/iPad|iPhone|iPod/.test(navigator.userAgent)) {
            const buttons = document.querySelectorAll('button');
            buttons.forEach(button => {
                button.addEventListener('touchstart', function() {});
            });
        }

        // Form validation feedback
        const inputs = document.querySelectorAll('input[required]');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                if (this.value.trim() === '') {
                    this.classList.add('border-red-300');
                    this.classList.remove('border-gray-300');
                } else {
                    this.classList.remove('border-red-300');
                    this.classList.add('border-gray-300');
                }
            });

            input.addEventListener('input', function() {
                if (this.classList.contains('border-red-300') && this.value.trim() !== '') {
                    this.classList.remove('border-red-300');
                    this.classList.add('border-gray-300');
                }
            });
        });
    });
</script>
@endsection