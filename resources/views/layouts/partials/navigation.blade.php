<header class="nmis-topbar">
    <div class="flex items-center gap-3">
        <button type="button" class="rounded-lg bg-slate-100 p-2 text-slate-700 hover:bg-slate-200 lg:hidden" @click="mobileSidebar = true">
            ☰
        </button>
        <button type="button" class="hidden rounded-lg bg-slate-100 p-2 text-slate-700 hover:bg-slate-200 lg:block" @click="sidebarCollapsed = !sidebarCollapsed">
            ⇆
        </button>
        <div>
        <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Transport Control</p>
        <p class="text-lg font-semibold text-slate-900">Good day, {{ auth()->user()->name }}</p>
        </div>
    </div>
    <div class="flex items-center gap-4">
        <div class="hidden md:block">
            <input type="text" placeholder="Search module..." class="rounded-lg border-slate-300 bg-slate-100 text-sm focus:border-[var(--nmis-primary)] focus:ring-[var(--nmis-primary)]" />
        </div>
        <a href="{{ route('notifications.center') }}" class="relative rounded-lg bg-slate-100 p-2 text-lg text-slate-700 hover:bg-slate-200" title="Notifications">
            🔔
            <span class="absolute -right-2 -top-2 rounded-full bg-[var(--nmis-accent)] px-1.5 py-0.5 text-[10px] font-semibold text-slate-900">{{ auth()->user()->unreadNotifications()->count() }}</span>
        </a>
        <div class="relative">
            <button type="button" class="rounded-lg bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-800 hover:bg-slate-200" @click="profileOpen = !profileOpen" @click.away="profileOpen = false">
                {{ auth()->user()->name }}
            </button>
            <div x-show="profileOpen" x-transition class="absolute right-0 z-50 mt-2 w-80 rounded-xl border border-slate-200 bg-white p-4 shadow-2xl">
                <div class="mb-3 rounded-lg bg-slate-50 p-3">
                    <p class="text-sm font-semibold text-slate-900">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-slate-600">{{ auth()->user()->email }}</p>
                    <p class="mt-1 text-xs text-slate-500">Roles: {{ auth()->user()->roles->pluck('name')->implode(', ') ?: 'N/A' }}</p>
                </div>
                <div class="space-y-2">
                    <a href="{{ route('profile.edit') }}" class="block rounded-lg bg-slate-100 px-3 py-2 text-sm text-slate-700 hover:bg-slate-200">Open Profile</a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full rounded-lg bg-[var(--nmis-primary)] px-3 py-2 text-sm font-semibold text-white hover:bg-[var(--nmis-secondary)]">Sign out</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
