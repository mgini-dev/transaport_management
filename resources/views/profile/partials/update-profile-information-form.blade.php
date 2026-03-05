<section class="space-y-6">
    @php $profilePhotoUrl = $user->employee_photo_url; @endphp
    <div class="flex items-center gap-3">
        @if($profilePhotoUrl)
            <img src="{{ $profilePhotoUrl }}"
                 alt="{{ $user->name }}"
                 class="h-10 w-10 rounded-xl object-cover ring-1 ring-slate-200">
        @else
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-[var(--nmis-primary)]/10 to-[var(--nmis-secondary)]/10">
                <svg class="h-5 w-5 text-[var(--nmis-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
            </div>
        @endif
        <div>
            <h2 class="text-xl font-semibold gradient-text">{{ __('Profile Information') }}</h2>
            <p class="text-sm text-slate-500">{{ __("Update your account's profile information and email address.") }}</p>
        </div>
    </div>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-6 bg-white rounded-xl border border-slate-200/60 p-6 shadow-sm">
        @csrf
        @method('patch')

        <div class="space-y-4">
            <!-- Name Field -->
            <div>
                <label for="name" class="block text-sm font-medium text-slate-700 mb-1">
                    {{ __('Name') }} <span class="text-rose-500">*</span>
                </label>
                <input id="name" 
                       name="name" 
                       type="text" 
                       class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all"
                       value="{{ old('name', $user->name) }}" 
                       required 
                       autofocus 
                       autocomplete="name"
                       placeholder="Enter your full name" />
                @error('name')
                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email Field -->
            <div>
                <label for="email" class="block text-sm font-medium text-slate-700 mb-1">
                    {{ __('Email') }} <span class="text-rose-500">*</span>
                </label>
                <input id="email" 
                       name="email" 
                       type="email" 
                       class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all"
                       value="{{ old('email', $user->email) }}" 
                       required 
                       autocomplete="username"
                       placeholder="your@email.com" />
                @error('email')
                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                @enderror

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <div class="mt-4 rounded-lg bg-amber-50 border border-amber-200 p-4">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm text-amber-800">
                                    {{ __('Your email address is unverified.') }}
                                </p>
                                <button form="send-verification" 
                                        class="mt-2 inline-flex items-center gap-1 text-xs font-semibold text-amber-700 hover:text-amber-900 transition-colors">
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                    {{ __('Click here to re-send the verification email.') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    @if (session('status') === 'verification-link-sent')
                        <div class="mt-4 rounded-lg bg-emerald-50 border border-emerald-200 p-4">
                            <div class="flex items-center gap-3">
                                <div class="rounded-full bg-emerald-100 p-1">
                                    <svg class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <p class="text-sm font-medium text-emerald-800">
                                    {{ __('A new verification link has been sent to your email address.') }}
                                </p>
                            </div>
                        </div>
                    @endif
                @endif
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-end gap-3 border-t border-slate-200/60 pt-4">
            <button type="submit" 
                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-[var(--nmis-primary)] to-[var(--nmis-secondary)] px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-[var(--nmis-primary)]/20 hover:shadow-xl hover:scale-105 transition-all duration-300">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                </svg>
                {{ __('Save Changes') }}
            </button>

            @if (session('status') === 'profile-updated')
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
                    {{ __('Saved!') }}
                </p>
            @endif
        </div>
    </form>
</section>
