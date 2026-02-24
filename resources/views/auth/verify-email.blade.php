<x-guest-layout>
    <!-- Logo & Header Section -->
    <div class="bg-gradient-to-r from-[#1b3b86] to-[#2a9d8f] px-8 py-10 text-center">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-white/20 backdrop-blur-lg mb-4 shadow-lg animate-float">
            <span class="text-4xl font-bold text-white">N</span>
        </div>
        <h1 class="text-3xl font-bold text-white">Nexus NMIS</h1>
        <p class="text-white/90 mt-2 text-sm">Email Verification</p>
    </div>
    
    <!-- Content Section -->
    <div class="p-8">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-blue-100 mb-4">
                <svg class="h-10 w-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-800">Verify Your Email</h2>
            <p class="text-gray-600 mt-3 text-sm leading-relaxed">
                {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
            </p>
        </div>
        
        <!-- Success Message -->
        @if (session('status') == 'verification-link-sent')
            <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded-lg">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">
                            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif
        
        <!-- Tips Section -->
        <div class="bg-gray-50 rounded-lg p-5 mb-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                <svg class="h-4 w-4 mr-2 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                Quick Tips:
            </h3>
            <ul class="space-y-2 text-xs text-gray-600">
                <li class="flex items-start">
                    <span class="inline-flex items-center justify-center h-4 w-4 rounded-full bg-blue-100 text-blue-600 text-xs font-bold mr-2 mt-0.5">1</span>
                    <span>Check your spam/junk folder if you don't see the email in your inbox</span>
                </li>
                <li class="flex items-start">
                    <span class="inline-flex items-center justify-center h-4 w-4 rounded-full bg-blue-100 text-blue-600 text-xs font-bold mr-2 mt-0.5">2</span>
                    <span>Make sure to check the email address you registered with: <span class="font-medium text-gray-700">{{ auth()->user()->email ?? 'your email' }}</span></span>
                </li>
                <li class="flex items-start">
                    <span class="inline-flex items-center justify-center h-4 w-4 rounded-full bg-blue-100 text-blue-600 text-xs font-bold mr-2 mt-0.5">3</span>
                    <span>The verification link will expire after 24 hours for security reasons</span>
                </li>
            </ul>
        </div>
        
        <!-- Action Buttons -->
        <div class="flex flex-col space-y-3">
            <!-- Resend Verification Form -->
            <form method="POST" action="{{ route('verification.send') }}" class="w-full">
                @csrf
                <button type="submit" 
                    class="w-full bg-gradient-to-r from-[#1b3b86] to-[#2a9d8f] text-white font-semibold py-3 px-4 rounded-lg hover:shadow-lg hover:shadow-[#1b3b86]/20 transform hover:scale-[1.02] transition-all duration-200"
                >
                    <span class="flex items-center justify-center">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        {{ __('Resend Verification Email') }}
                    </span>
                </button>
            </form>
            
            <!-- Logout Form -->
            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <button type="submit" 
                    class="w-full flex items-center justify-center px-4 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition-all duration-200"
                >
                    <svg class="h-5 w-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    {{ __('Log Out') }}
                </button>
            </form>
        </div>
        
        <!-- Need Help? -->
        <div class="mt-8 pt-6 border-t border-gray-200">
            <p class="text-center text-xs text-gray-500">
                {{ __('Having trouble verifying your email?') }} 
                <a href="#" onclick="event.preventDefault(); document.getElementById('resend-verification').submit();" class="text-[#1b3b86] font-medium hover:text-[#2a9d8f] transition">
                    {{ __('Click here to resend') }}
                </a>
            </p>
        </div>
    </div>
    
    <!-- Hidden form for the help link -->
    <form id="resend-verification" method="POST" action="{{ route('verification.send') }}" class="hidden">
        @csrf
    </form>
</x-guest-layout>