<header class="sticky top-0 z-20 bg-white/90 backdrop-blur-xl border-b border-slate-200/60 px-4 sm:px-6 lg:px-8 py-3">
    <div class="flex items-center justify-between">
        <!-- Left section -->
        <div class="flex items-center gap-4">
            <button type="button" 
                    class="relative group rounded-xl bg-gradient-to-br from-slate-100 to-slate-200 p-2.5 text-slate-700 hover:from-slate-200 hover:to-slate-300 transition-all duration-200 lg:hidden" 
                    @click="mobileSidebar = true">
                <span class="sr-only">Open sidebar</span>
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
            
            <button type="button" 
                    class="hidden lg:block relative group rounded-xl bg-gradient-to-br from-slate-100 to-slate-200 p-2.5 text-slate-700 hover:from-slate-200 hover:to-slate-300 transition-all duration-200" 
                    @click="sidebarCollapsed = !sidebarCollapsed">
                <span class="sr-only">Toggle sidebar</span>
                <svg class="h-5 w-5 transition-transform duration-300" :class="{ 'rotate-180': sidebarCollapsed }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                </svg>
            </button>
            
            <div class="hidden sm:block">
                <p class="text-xs uppercase tracking-wider text-slate-400 font-medium">Management System</p>
                <p class="text-lg font-semibold gradient-text">{{ auth()->user()->name }}</p>
            </div>
        </div>

        <!-- Right section -->
        <div class="flex items-center gap-3">
            <!-- Search (desktop) -->
            <div class="hidden md:block relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input type="text" 
                       placeholder="Search module..." 
                       class="w-64 rounded-xl border-0 bg-slate-100 pl-10 pr-4 py-2.5 text-sm placeholder:text-slate-400 focus:ring-2 focus:ring-[var(--nmis-primary)]/20 focus:bg-white transition-all duration-200">
            </div>

            <!-- Notifications -->
            <a href="{{ route('notifications.center') }}" 
               class="relative group rounded-xl bg-gradient-to-br from-slate-100 to-slate-200 p-2.5 text-slate-700 hover:from-slate-200 hover:to-slate-300 transition-all duration-200">
                <span class="sr-only">Notifications</span>
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
                @php $unreadCount = auth()->user()->unreadNotifications()->count(); @endphp
                @if($unreadCount > 0)
                    <span class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-[var(--nmis-accent)] text-[10px] font-bold text-white shadow-lg animate-pulse">
                        {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                    </span>
                @endif
            </a>

            <!-- Profile Dropdown -->
            <div class="relative" @click.away="profileOpen = false">
                <button type="button" 
                        class="flex items-center gap-2 rounded-xl bg-gradient-to-br from-slate-100 to-slate-200 px-3 py-2.5 text-sm font-semibold text-slate-800 hover:from-slate-200 hover:to-slate-300 transition-all duration-200 group"
                        @click="profileOpen = !profileOpen">
                    <span class="hidden sm:inline">{{ auth()->user()->name }}</span>
                    <div class="h-6 w-6 rounded-full bg-gradient-to-br from-[var(--nmis-primary)] to-[var(--nmis-secondary)] flex items-center justify-center">
                        <span class="text-xs font-bold text-white">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </span>
                    </div>
                    <svg class="h-4 w-4 text-slate-500 transition-transform duration-200" :class="{ 'rotate-180': profileOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                <!-- Dropdown menu -->
                <div x-show="profileOpen" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute right-0 z-50 mt-2 w-80">
                    <div class="rounded-2xl border border-slate-200/60 bg-white shadow-xl overflow-hidden">
                        <!-- User info -->
                        <div class="bg-gradient-to-br from-slate-50 to-white p-4 border-b border-slate-200/60">
                            <div class="flex items-center gap-3">
                                <div class="h-12 w-12 rounded-full bg-gradient-to-br from-[var(--nmis-primary)] to-[var(--nmis-secondary)] flex items-center justify-center shadow-lg">
                                    <span class="text-lg font-bold text-white">
                                        {{ substr(auth()->user()->name, 0, 2) }}
                                    </span>
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-900">{{ auth()->user()->name }}</p>
                                    <p class="text-xs text-slate-500">{{ auth()->user()->email }}</p>
                                </div>
                            </div>
                            <div class="mt-3 flex flex-wrap gap-1">
                                @foreach(auth()->user()->roles as $role)
                                    <span class="inline-flex items-center rounded-full bg-[var(--nmis-primary)]/10 px-2 py-0.5 text-xs font-medium text-[var(--nmis-primary)]">
                                        {{ $role->name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        <!-- Menu items -->
                        <div class="p-2">
                            <a href="{{ route('profile.edit') }}" 
                               class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm text-slate-700 hover:bg-slate-100 transition-colors duration-200 group">
                                <div class="rounded-lg bg-slate-100 p-2 group-hover:bg-white transition-colors duration-200">
                                    <svg class="h-4 w-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                                <span>Profile Settings</span>
                            </a>
                            
                            <form action="{{ route('logout') }}" method="POST" class="mt-1">
                                @csrf
                                <button type="submit" 
                                        class="w-full flex items-center gap-3 rounded-xl px-3 py-2 text-sm text-rose-700 hover:bg-rose-50 transition-colors duration-200 group">
                                    <div class="rounded-lg bg-rose-100 p-2 group-hover:bg-white transition-colors duration-200">
                                        <svg class="h-4 w-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                        </svg>
                                    </div>
                                    <span>Sign Out</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>