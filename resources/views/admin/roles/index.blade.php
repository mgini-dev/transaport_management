<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-3xl font-bold gradient-text">Role & Permission Management</h2>
                <p class="mt-2 text-sm text-slate-500">Dynamic security panel for roles, permissions, and direct user grants</p>
            </div>
        </div>
    </x-slot>

    <div class="space-y-8">
        <!-- Success Message -->
        @if(session('success'))
            <div class="animate-slide-down rounded-xl border border-emerald-200 bg-emerald-50/90 backdrop-blur-sm px-5 py-4">
                <div class="flex items-center gap-3">
                    <div class="rounded-full bg-emerald-100 p-1">
                        <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-emerald-800">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        <!-- Stats Cards -->
        <div class="grid gap-4 sm:grid-cols-4">
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-slate-500">Total Roles</p>
                    <span class="rounded-lg bg-[var(--nmis-primary)]/10 p-2 text-[var(--nmis-primary)]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-bold text-slate-900">{{ $roles->count() }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-slate-500">Total Permissions</p>
                    <span class="rounded-lg bg-[var(--nmis-secondary)]/10 p-2 text-[var(--nmis-secondary)]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-bold text-[var(--nmis-secondary)]">{{ $permissions->count() }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-slate-500">Users with Overrides</p>
                    <span class="rounded-lg bg-[var(--nmis-accent)]/10 p-2 text-[var(--nmis-accent)]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-bold text-[var(--nmis-accent)]">{{ $users->filter(fn($user) => $user->permissions->count() > 0)->count() }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-slate-500">Permission Groups</p>
                    <span class="rounded-lg bg-[var(--nmis-primary)]/10 p-2 text-[var(--nmis-primary)]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-bold text-slate-900">{{ count($permissionsByGroup) }}</p>
            </div>
        </div>

        <!-- Create Forms Grid -->
        <div class="grid gap-6 lg:grid-cols-2">
            <!-- Create Permission -->
            <div class="rounded-xl border border-slate-200/60 bg-white shadow-sm overflow-hidden">
                <div class="bg-gradient-to-r from-slate-50 to-white px-6 py-4 border-b border-slate-200/60">
                    <h3 class="text-lg font-semibold text-slate-900 flex items-center gap-2">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[var(--nmis-primary)]/10 text-[var(--nmis-primary)]">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </span>
                        Create New Permission
                    </h3>
                </div>
                <form method="POST" action="{{ route('admin.permissions.store') }}" class="p-6">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                Permission Name <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" 
                                   name="name" 
                                   required 
                                   class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all"
                                   placeholder="e.g., orders.export, users.create">
                            <p class="mt-1 text-xs text-slate-500">Use dot notation: module.action (e.g., orders.export)</p>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" 
                                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-[var(--nmis-primary)] to-[var(--nmis-secondary)] px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-[var(--nmis-primary)]/20 hover:shadow-xl hover:scale-105 transition-all">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Add Permission
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Create Role -->
            <div class="rounded-xl border border-slate-200/60 bg-white shadow-sm overflow-hidden">
                <div class="bg-gradient-to-r from-slate-50 to-white px-6 py-4 border-b border-slate-200/60">
                    <h3 class="text-lg font-semibold text-slate-900 flex items-center gap-2">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[var(--nmis-secondary)]/10 text-[var(--nmis-secondary)]">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </span>
                        Create New Role
                    </h3>
                </div>
                <form method="POST" action="{{ route('admin.roles.store') }}" class="p-6">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                Role Name <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" 
                                   name="name" 
                                   required 
                                   class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all"
                                   placeholder="e.g., Manager, Supervisor, Accountant">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Assign Permissions
                            </label>
                            <div class="grid max-h-64 gap-2 overflow-y-auto rounded-xl border border-slate-200 bg-slate-50 p-3 sm:grid-cols-2">
                                @foreach ($permissionsByGroup as $group => $groupPermissions)
                                    <div class="space-y-1">
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ $group }}</p>
                                        @foreach ($groupPermissions as $permission)
                                            <label class="flex items-center gap-2 text-xs hover:bg-white/50 p-1 rounded transition-colors">
                                                <input type="checkbox" 
                                                       name="permissions[]" 
                                                       value="{{ $permission->name }}" 
                                                       class="rounded border-slate-300 text-[var(--nmis-primary)] focus:ring-[var(--nmis-primary)]/20">
                                                <span class="text-slate-700">{{ $permission->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" 
                                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-[var(--nmis-primary)] to-[var(--nmis-secondary)] px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-[var(--nmis-primary)]/20 hover:shadow-xl hover:scale-105 transition-all">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Create Role
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Role Permission Assignment -->
        <section class="rounded-xl border border-slate-200/60 bg-white shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-slate-50 to-white px-6 py-4 border-b border-slate-200/60">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-900 flex items-center gap-2">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[var(--nmis-primary)]/10 text-[var(--nmis-primary)]">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        </span>
                        Role Permission Assignment
                    </h3>
                    <span class="text-xs text-slate-500">{{ $roles->count() }} roles</span>
                </div>
            </div>
            
            <div class="p-6">
                <div class="space-y-4">
                    @foreach ($roles as $role)
                        <div x-data="{ open: false }" class="rounded-xl border border-slate-200/60 bg-white hover:shadow-md transition-all">
                            <div class="flex items-center justify-between px-4 py-3 cursor-pointer" @click="open = !open">
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-lg bg-gradient-to-br from-[var(--nmis-primary)]/10 to-[var(--nmis-secondary)]/10 flex items-center justify-center">
                                        <span class="text-sm font-semibold text-[var(--nmis-primary)]">
                                            {{ strtoupper(substr($role->name, 0, 2)) }}
                                        </span>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-semibold text-slate-900">{{ $role->name }}</h4>
                                        <p class="text-xs text-slate-500">{{ $role->permissions->count() }} permissions assigned</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex items-center rounded-full bg-[var(--nmis-primary)]/10 px-2 py-0.5 text-xs font-medium text-[var(--nmis-primary)]">
                                        {{ $role->users_count ?? 0 }} users
                                    </span>
                                    <svg class="h-5 w-5 text-slate-400 transition-transform duration-200" 
                                         :class="{ 'rotate-180': open }" 
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                            </div>
                            
                            <div x-show="open" 
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0"
                                 x-transition:enter-end="opacity-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100"
                                 x-transition:leave-end="opacity-0"
                                 class="border-t border-slate-200/60 p-4">
                                
                                <form method="POST" action="{{ route('admin.roles.update', $role) }}">
                                    @csrf
                                    @method('PUT')
                                    
                                    <div class="grid gap-4 max-h-96 overflow-y-auto rounded-xl border border-slate-200 bg-slate-50 p-4 sm:grid-cols-3">
                                        @foreach ($permissionsByGroup as $group => $groupPermissions)
                                            <div class="rounded-lg border border-slate-200 bg-white p-3">
                                                <p class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-500">{{ $group }}</p>
                                                <div class="space-y-1.5">
                                                    @foreach ($groupPermissions as $permission)
                                                        <label class="flex items-center gap-2 text-xs hover:bg-slate-50 p-1 rounded transition-colors">
                                                            <input type="checkbox" 
                                                                   name="permissions[]" 
                                                                   value="{{ $permission->name }}" 
                                                                   class="rounded border-slate-300 text-[var(--nmis-primary)] focus:ring-[var(--nmis-primary)]/20"
                                                                   @checked($role->permissions->contains('name', $permission->name))>
                                                            <span class="text-slate-700">{{ $permission->name }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    
                                    <div class="mt-4 flex justify-end">
                                        <button type="submit" 
                                                class="inline-flex items-center gap-2 rounded-lg bg-[var(--nmis-primary)] px-4 py-2 text-xs font-semibold text-white hover:bg-[var(--nmis-secondary)] transition-all">
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                                            </svg>
                                            Save Role Permissions
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- Direct User Permission Overrides -->
        <section class="rounded-xl border border-slate-200/60 bg-white shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-slate-50 to-white px-6 py-4 border-b border-slate-200/60">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-900 flex items-center gap-2">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[var(--nmis-secondary)]/10 text-[var(--nmis-secondary)]">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </span>
                        Direct User Permission Overrides
                    </h3>
                    <span class="text-xs text-slate-500">{{ $users->count() }} users</span>
                </div>
                <p class="mt-1 text-xs text-slate-500">Grant or restrict specific permissions for individual users</p>
            </div>
            
            <div class="p-6">
                <div class="space-y-4">
                    @foreach ($users as $user)
                        <div x-data="{ open: false }" class="rounded-xl border border-slate-200/60 bg-white hover:shadow-md transition-all">
                            <div class="flex items-center justify-between px-4 py-3 cursor-pointer" @click="open = !open">
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-full bg-gradient-to-br from-[var(--nmis-primary)]/10 to-[var(--nmis-secondary)]/10 flex items-center justify-center">
                                        <span class="text-xs font-semibold text-[var(--nmis-primary)]">
                                            {{ strtoupper(substr($user->name, 0, 2)) }}
                                        </span>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-semibold text-slate-900">{{ $user->name }}</h4>
                                        <div class="flex items-center gap-2 text-xs">
                                            <span class="text-slate-500">{{ $user->email }}</span>
                                            <span class="text-slate-300">•</span>
                                            <span class="text-slate-500">{{ $user->roles->pluck('name')->implode(', ') ?: 'No role' }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex items-center rounded-full bg-[var(--nmis-accent)]/10 px-2 py-0.5 text-xs font-medium text-[var(--nmis-accent)]">
                                        {{ $user->permissions->count() }} overrides
                                    </span>
                                    <svg class="h-5 w-5 text-slate-400 transition-transform duration-200" 
                                         :class="{ 'rotate-180': open }" 
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                            </div>
                            
                            <div x-show="open" 
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0"
                                 x-transition:enter-end="opacity-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100"
                                 x-transition:leave-end="opacity-0"
                                 class="border-t border-slate-200/60 p-4">
                                
                                <form method="POST" action="{{ route('admin.users.permissions.update', $user) }}">
                                    @csrf
                                    @method('PUT')
                                    
                                    <div class="grid gap-4 max-h-96 overflow-y-auto rounded-xl border border-slate-200 bg-slate-50 p-4 sm:grid-cols-3">
                                        @foreach ($permissionsByGroup as $group => $groupPermissions)
                                            <div class="rounded-lg border border-slate-200 bg-white p-3">
                                                <p class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-500">{{ $group }}</p>
                                                <div class="space-y-1.5">
                                                    @foreach ($groupPermissions as $permission)
                                                        <label class="flex items-center gap-2 text-xs hover:bg-slate-50 p-1 rounded transition-colors">
                                                            <input type="checkbox" 
                                                                   name="permissions[]" 
                                                                   value="{{ $permission->name }}" 
                                                                   class="rounded border-slate-300 text-[var(--nmis-primary)] focus:ring-[var(--nmis-primary)]/20"
                                                                   @checked($user->permissions->contains('name', $permission->name))>
                                                            <span class="text-slate-700">{{ $permission->name }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    
                                    <div class="mt-4 flex justify-end gap-2">
                                        <button type="submit" 
                                                class="inline-flex items-center gap-2 rounded-lg bg-[var(--nmis-secondary)] px-4 py-2 text-xs font-semibold text-white hover:bg-[var(--nmis-primary)] transition-all">
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                                            </svg>
                                            Save User Permissions
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    </div>

    <style>
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-slide-down {
            animation: slideDown 0.3s ease-out;
        }

        .gradient-text {
            background: linear-gradient(135deg, var(--nmis-primary), var(--nmis-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        /* Custom scrollbar */
        .overflow-y-auto::-webkit-scrollbar {
            width: 4px;
        }
        
        .overflow-y-auto::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        
        .overflow-y-auto::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        
        .overflow-y-auto::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</x-app-layout>