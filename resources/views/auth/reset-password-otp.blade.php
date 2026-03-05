<x-guest-layout>
    <div class="bg-gradient-to-r from-[#1b3b86] to-[#2a9d8f] px-8 py-10 text-center">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-white/20 backdrop-blur-lg mb-4 shadow-lg animate-float">
            <span class="text-4xl font-bold text-white">N</span>
        </div>
        <h1 class="text-3xl font-bold text-white">Nexus NMIS</h1>
        <p class="text-white/90 mt-2 text-sm">Set New Password</p>
    </div>

    <div class="p-8">
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-gray-800">Create New Password</h2>
            <p class="text-gray-600 mt-2 text-sm leading-relaxed">
                OTP verified for <strong>{{ $email }}</strong>. Set your new password below.
            </p>
        </div>

        <form method="POST" action="{{ route('password.store') }}" class="space-y-6">
            @csrf

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                    New Password
                </label>
                <input id="password"
                       type="password"
                       name="password"
                       required
                       autocomplete="new-password"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1b3b86] focus:border-transparent transition"
                       placeholder="Enter new password">
                @error('password')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                    Confirm New Password
                </label>
                <input id="password_confirmation"
                       type="password"
                       name="password_confirmation"
                       required
                       autocomplete="new-password"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1b3b86] focus:border-transparent transition"
                       placeholder="Confirm new password">
                @error('password_confirmation')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit"
                    class="w-full bg-gradient-to-r from-[#1b3b86] to-[#2a9d8f] text-white font-semibold py-3 px-4 rounded-lg hover:shadow-lg hover:shadow-[#1b3b86]/20 transform hover:scale-[1.02] transition-all duration-200">
                Save New Password
            </button>
        </form>
    </div>
</x-guest-layout>

