<x-guest-layout>
    <!-- Logo & Header Section -->
    <div class="bg-gradient-to-r from-[#1b3b86] to-[#2a9d8f] px-8 py-10 text-center">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-white/20 backdrop-blur-lg mb-4 shadow-lg animate-float">
            <span class="text-4xl font-bold text-white">N</span>
        </div>
        <h1 class="text-3xl font-bold text-white">Nexus NMIS</h1>
        <p class="text-white/90 mt-2 text-sm">Password Recovery</p>
    </div>
    
    <!-- Form Section -->
    <div class="p-8">
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-gray-800">Forgot Password?</h2>
            <p class="text-gray-600 mt-2 text-sm leading-relaxed">
                Enter your registered email address. If valid, we will send a One-Time Password (OTP) for secure reset.
            </p>
        </div>
        
        <!-- Session Status -->
        @if (session('status'))
            <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded-lg text-sm">
                <div class="flex items-center">
                    <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    {{ session('status') }}
                </div>
            </div>
        @endif
        
        <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
            @csrf
            
            <!-- Email Address -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('Email Address') }}
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <input 
                        id="email" 
                        type="email" 
                        name="email" 
                        value="{{ old('email') }}" 
                        required 
                        autofocus
                        class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1b3b86] focus:border-transparent transition"
                        placeholder="Enter your email address"
                    />
                </div>
                @error('email')
                    <p class="mt-2 text-sm text-red-600 flex items-center">
                        <svg class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col space-y-3">
                <button type="submit" 
                    class="w-full bg-gradient-to-r from-[#1b3b86] to-[#2a9d8f] text-white font-semibold py-3 px-4 rounded-lg hover:shadow-lg hover:shadow-[#1b3b86]/20 transform hover:scale-[1.02] transition-all duration-200"
                >
                    <span class="flex items-center justify-center">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        Send OTP
                    </span>
                </button>
                
                <!-- Back to Login Link -->
                <a href="{{ route('login') }}" 
                    class="w-full flex items-center justify-center px-4 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition-all duration-200"
                >
                    <svg class="h-5 w-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    {{ __('Back to Login') }}
                </a>
            </div>
        </form>
        
        <!-- Help Text -->
        <div class="mt-8 pt-6 border-t border-gray-200">
            <p class="text-center text-xs text-gray-500">
                {{ __('Remember your password?') }} 
                <a href="{{ route('login') }}" class="text-[#1b3b86] font-medium hover:text-[#2a9d8f] transition">
                    {{ __('Sign in here') }}
                </a>
            </p>
        </div>
    </div>
</x-guest-layout>
