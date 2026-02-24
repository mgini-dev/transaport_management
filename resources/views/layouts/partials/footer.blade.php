<footer class="mt-8 border-t border-slate-200/60 bg-white/50 backdrop-blur-sm py-6">
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/nexus-logo.png') }}" alt="NexusFlow" class="h-6 w-auto opacity-50">
                <p class="text-xs text-slate-400">
                    © {{ now()->format('Y') }} NexusFlow. All rights reserved.
                </p>
            </div>
            <div class="flex items-center gap-6">
                <p class="text-xs text-slate-400">NexusFlow Management System</p>
                <span class="h-4 w-px bg-slate-200"></span>
                <p class="text-xs text-slate-400">v1.0.0</p>
            </div>
        </div>
    </div>
</footer>