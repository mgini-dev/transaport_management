<section class="space-y-6">
    <div class="flex items-center gap-3">
        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-[var(--nmis-primary)]/10 to-[var(--nmis-secondary)]/10">
            <svg class="h-5 w-5 text-[var(--nmis-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
            </svg>
        </div>
        <div>
            <h2 class="text-xl font-semibold gradient-text">{{ __('Update Password') }}</h2>
            <p class="text-sm text-slate-500">{{ __('Ensure your account is using a long, random password to stay secure.') }}</p>
        </div>
    </div>

    <form method="post" action="{{ route('password.update') }}" class="space-y-6 bg-white rounded-xl border border-slate-200/60 p-6 shadow-sm">
        @csrf
        @method('put')

        <div class="space-y-4">
            <!-- Current Password -->
            <div>
                <label for="update_password_current_password" class="block text-sm font-medium text-slate-700 mb-1">
                    {{ __('Current Password') }} <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <input id="update_password_current_password" 
                           name="current_password" 
                           type="password" 
                           class="w-full rounded-xl border-slate-300 bg-slate-50 pl-10 pr-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all"
                           autocomplete="current-password"
                           placeholder="Enter your current password" />
                </div>
                @error('current_password', 'updatePassword')
                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- New Password -->
            <div>
                <label for="update_password_password" class="block text-sm font-medium text-slate-700 mb-1">
                    {{ __('New Password') }} <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                    </div>
                    <input id="update_password_password" 
                           name="password" 
                           type="password" 
                           class="w-full rounded-xl border-slate-300 bg-slate-50 pl-10 pr-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all"
                           autocomplete="new-password"
                           placeholder="Enter new password" />
                </div>
                @error('password', 'updatePassword')
                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-slate-500">Password must be at least 8 characters long</p>
            </div>

            <!-- Confirm Password -->
            <div>
                <label for="update_password_password_confirmation" class="block text-sm font-medium text-slate-700 mb-1">
                    {{ __('Confirm Password') }} <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <input id="update_password_password_confirmation" 
                           name="password_confirmation" 
                           type="password" 
                           class="w-full rounded-xl border-slate-300 bg-slate-50 pl-10 pr-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all"
                           autocomplete="new-password"
                           placeholder="Confirm your new password" />
                </div>
                @error('password_confirmation', 'updatePassword')
                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Password Strength Indicator (Optional) -->
        <div x-data="{ password: '' }" class="space-y-2">
            <div class="h-1.5 w-full bg-slate-200 rounded-full overflow-hidden">
                <div class="h-full transition-all duration-300"
                     :class="{
                         'w-0 bg-slate-200': password.length === 0,
                         'w-1/4 bg-rose-500': password.length > 0 && password.length < 4,
                         'w-2/4 bg-amber-500': password.length >= 4 && password.length < 8,
                         'w-3/4 bg-[var(--nmis-secondary)]': password.length >= 8 && password.length < 12,
                         'w-full bg-[var(--nmis-accent)]': password.length >= 12
                     }">
                </div>
            </div>
            <p class="text-xs text-slate-500" x-show="password.length > 0" x-cloak>
                <span x-show="password.length < 4" class="text-rose-600">Weak password</span>
                <span x-show="password.length >= 4 && password.length < 8" class="text-amber-600">Fair password</span>
                <span x-show="password.length >= 8 && password.length < 12" class="text-[var(--nmis-secondary)]">Good password</span>
                <span x-show="password.length >= 12" class="text-[var(--nmis-accent)]">Strong password</span>
            </p>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-end gap-3 border-t border-slate-200/60 pt-4">
            <button type="submit" 
                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-[var(--nmis-primary)] to-[var(--nmis-secondary)] px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-[var(--nmis-primary)]/20 hover:shadow-xl hover:scale-105 transition-all duration-300">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                {{ __('Update Password') }}
            </button>

            @if (session('status') === 'password-updated')
                <p x-data="{ show: true }"
                   x-show="show"
                   x-transition:enter="transition ease-out duration-300"
                   x-transition:enter-start="opacity-0 translate-y-2"
                   x-transition:enter-end="opacity-100 translate-y-0"
                   x-transition:leave="transition ease-in duration-200"
                   x-transition:leave-start="opacity-100 translate-y-0"
                   x-transition:leave-end="opacity-0 translate-y-2"
                   x-init="setTimeout(() => show = false, 2000)"
                   class="text-sm text-emerald-600 flex items-center gap-1">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    {{ __('Password updated!') }}
                </p>
            @endif
        </div>
    </form>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</section>