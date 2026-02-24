<x-guest-layout>
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="mb-4 text-center">
            <x-application-logo class="mx-auto h-14 w-auto" />
            <h1 class="mt-3 text-xl font-bold text-[var(--nmis-primary)]">NMIS</h1>
            <p class="text-sm text-slate-500">Transport & Trip Management System</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf
                <div>
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input id="email" class="mt-1 block w-full rounded-md border-slate-300" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="password" :value="__('Password')" />
                    <x-text-input id="password" class="mt-1 block w-full rounded-md border-slate-300"
                                  type="password"
                                  name="password"
                                  required autocomplete="current-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div class="flex items-center justify-between">
                    <label for="remember_me" class="inline-flex items-center">
                        <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-[var(--nmis-primary)] shadow-sm focus:ring-[var(--nmis-primary)]" name="remember">
                        <span class="ms-2 text-sm text-slate-600">{{ __('Remember me') }}</span>
                    </label>
                    @if (Route::has('password.request'))
                        <a class="text-sm font-medium text-[var(--nmis-primary)] hover:text-[var(--nmis-secondary)]" href="{{ route('password.request') }}">
                            {{ __('Forgot password?') }}
                        </a>
                    @endif
                </div>

                <button class="w-full rounded-md bg-[var(--nmis-primary)] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[var(--nmis-secondary)]">
                    {{ __('Log in') }}
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>
