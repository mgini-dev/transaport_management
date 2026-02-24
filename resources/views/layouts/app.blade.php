<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    @include('layouts.partials.head')
    <body class="nmis-body" x-data="{ sidebarCollapsed: false, mobileSidebar: false, profileOpen: false }">
        <div class="nmis-app">
            <div x-show="mobileSidebar" class="fixed inset-0 z-30 bg-slate-900/50 lg:hidden" @click="mobileSidebar = false"></div>
            @include('layouts.partials.sidebar')
            <div class="nmis-main" :class="{ 'nmis-main-expanded': sidebarCollapsed }">
                @include('layouts.partials.navigation')
                <main class="nmis-content">
                    <div class="nmis-card">
                        @isset($header)
                            <header class="mb-6 border-b border-slate-200 pb-4">
                                {{ $header }}
                            </header>
                        @endisset

                        @if (session('status'))
                            <div class="mb-4 rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                                {{ session('status') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="mb-4 rounded-lg border border-rose-300 bg-rose-50 px-4 py-3 text-sm text-rose-900">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        {{ $slot }}
                    </div>
                </main>
                @include('layouts.partials.footer')
            </div>
        </div>
        @stack('scripts')
    </body>
</html>
