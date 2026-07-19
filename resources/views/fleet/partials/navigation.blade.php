<div class="mb-6 flex flex-wrap items-center gap-3 border-b border-slate-200 pb-1">
    <a href="{{ route('fleet.index') }}" 
       class="px-4 py-3 text-sm font-bold transition-all border-b-2 {{ request()->routeIs('fleet.index') ? 'border-[var(--nmis-primary)] text-[var(--nmis-primary)]' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
        Fleet Inventory
    </a>
    <a href="{{ route('fleet.maintenance') }}" 
       class="px-4 py-3 text-sm font-bold transition-all border-b-2 {{ request()->routeIs('fleet.maintenance') ? 'border-[var(--nmis-primary)] text-[var(--nmis-primary)]' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
        Maintenance Center
    </a>
    <a href="{{ route('fleet.assignments') }}" 
       class="px-4 py-3 text-sm font-bold transition-all border-b-2 {{ request()->routeIs('fleet.assignments') ? 'border-[var(--nmis-primary)] text-[var(--nmis-primary)]' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
        Assignment Logs
    </a>
    <a href="{{ route('fleet.alerts') }}" 
       class="px-4 py-3 text-sm font-bold transition-all border-b-2 {{ request()->routeIs('fleet.alerts') ? 'border-[var(--nmis-primary)] text-[var(--nmis-primary)]' : 'border-transparent text-slate-500 hover:text-slate-700' }} flex items-center gap-2">
        Service Alerts
        @if(isset($needsServiceCount) && $needsServiceCount > 0)
            <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-rose-500 text-[10px] text-white">
                {{ $needsServiceCount }}
            </span>
        @endif
    </a>
</div>
