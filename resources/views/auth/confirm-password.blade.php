<x-guest-layout>
    <!-- Logo & Header Section -->
    <div class="bg-gradient-to-r from-[#1b3b86] to-[#2a9d8f] px-8 py-10 text-center">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-white/20 backdrop-blur-lg mb-4 shadow-lg animate-float">
            <span class="text-4xl font-bold text-white">N</span>
        </div>
        <h1 class="text-3xl font-bold text-white">Nexus NMIS</h1>
        <p class="text-white/90 mt-2 text-sm">Secure Area</p>
    </div>
    
    <!-- Form Section -->
    <div class="p-8">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-yellow-100 mb-4">
                <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-800">Confirm Password</h2>
            <p class="text-gray-600 mt-2 text-sm leading-relaxed">
                {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
            </p>
        </div>
        
        <form method="POST" action="{{ route('password.confirm') }}" class="space-y-6">
            @csrf
            
            <!-- Password -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('Password') }}
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <input 
                        id="password" 
                        type="password" 
                        name="password" 
                        required 
                        autocomplete="current-password"
                        class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1b3b86] focus:border-transparent transition"
                        placeholder="Enter your password to continue"
                    />
                </div>
                @error('password')
                    <p class="mt-2 text-sm text-red-600 flex items-center">
                        <svg class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Security Note -->
            <div class="bg-blue-50 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            {{ __('For your security, please re-enter your password to access sensitive areas.') }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col space-y-3">
                <button type="submit" 
                    class="w-full bg-gradient-to-r from-[#1b3b86] to-[#2a9d8f] text-white font-semibold py-3 px-4 rounded-lg hover:shadow-lg hover:shadow-[#1b3b86]/20 transform hover:scale-[1.02] transition-all duration-200"
                >
                    <span class="flex items-center justify-center">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        {{ __('Confirm Password') }}
                    </span>
                </button>
                
                <!-- Cancel/Back Link -->
                <a href="{{ route('dashboard') }}" 
                    class="w-full flex items-center justify-center px-4 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition-all duration-200"
                >
                    <svg class="h-5 w-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    {{ __('Cancel') }}
                </a>
            </div>
        </form>
        
        <!-- Help Text -->
        <div class="mt-8 pt-6 border-t border-gray-200">
            <p class="text-center text-xs text-gray-500">
                {{ __('Having trouble?') }} 
                <a href="{{ route('password.request') }}" class="text-[#1b3b86] font-medium hover:text-[#2a9d8f] transition">
                    {{ __('Reset your password') }}
                </a>
            </p>
        </div>
    </div>
</x-guest-layout>