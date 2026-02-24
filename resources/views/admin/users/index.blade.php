<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-3xl font-bold gradient-text">User Management</h2>
                <p class="mt-2 text-sm text-slate-500">Create users, assign roles, and manage active/inactive status</p>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.users.export.csv') }}" 
                   class="inline-flex items-center gap-2 rounded-xl bg-white border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 hover:text-[var(--nmis-primary)] transition-all shadow-sm">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Export CSV
                </a>
                
                @can('users.create')
                    <button 
                        x-data="{}"
                        @click="$dispatch('open-user-modal')"
                        class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-[var(--nmis-primary)] to-[var(--nmis-secondary)] px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-[var(--nmis-primary)]/20 hover:shadow-xl hover:scale-105 transition-all duration-300 group">
                        <svg class="h-5 w-5 group-hover:rotate-90 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Create New User
                    </button>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="space-y-8" 
         x-data="{ openUserModal: false }" 
         @open-user-modal.window="openUserModal = true">
        
        <!-- Create User Modal -->
        @can('users.create')
            <div x-show="openUserModal" 
                 x-cloak
                 class="fixed inset-0 z-50 overflow-y-auto"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">
                
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" @click="openUserModal = false"></div>

                <!-- Modal Panel -->
                <div class="flex min-h-full items-center justify-center p-4">
                    <div x-show="openUserModal" 
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="relative w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-2xl bg-white shadow-2xl">
                        
                        <!-- Modal Header -->
                        <div class="sticky top-0 z-10 flex items-center justify-between border-b border-slate-200/60 bg-white px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-[var(--nmis-primary)]/10 to-[var(--nmis-secondary)]/10">
                                    <svg class="h-5 w-5 text-[var(--nmis-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-slate-900">Create New User</h3>
                                    <p class="text-xs text-slate-500">Add a new user to the system</p>
                                </div>
                            </div>
                            <button @click="openUserModal = false" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-500 transition-colors">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <!-- Modal Form -->
                        <form method="POST" action="{{ route('admin.users.store') }}" class="p-6">
                            @csrf
                            <div class="grid gap-5 md:grid-cols-2">
                                <!-- Full Name -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-slate-700 mb-1">
                                        Full Name <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="text" 
                                           name="name" 
                                           required 
                                           class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all"
                                           placeholder="John Doe">
                                </div>

                                <!-- Email -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-slate-700 mb-1">
                                        Email Address <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="email" 
                                           name="email" 
                                           required 
                                           class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all"
                                           placeholder="john@example.com">
                                </div>

                                <!-- Password -->
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">
                                        Password <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="password" 
                                           name="password" 
                                           required 
                                           class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all"
                                           placeholder="••••••••">
                                </div>

                                <!-- Confirm Password -->
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">
                                        Confirm Password <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="password" 
                                           name="password_confirmation" 
                                           required 
                                           class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all"
                                           placeholder="••••••••">
                                </div>

                                <!-- Status -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-slate-700 mb-1">
                                        Account Status
                                    </label>
                                    <div class="flex gap-4">
                                        <label class="flex items-center gap-2">
                                            <input type="radio" name="is_active" value="1" class="rounded-full border-slate-300 text-[var(--nmis-primary)] focus:ring-[var(--nmis-primary)]/20" checked>
                                            <span class="text-sm text-slate-700">Active</span>
                                        </label>
                                        <label class="flex items-center gap-2">
                                            <input type="radio" name="is_active" value="0" class="rounded-full border-slate-300 text-[var(--nmis-primary)] focus:ring-[var(--nmis-primary)]/20">
                                            <span class="text-sm text-slate-700">Inactive</span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Role Selection -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-slate-700 mb-2">
                                        Assign Roles
                                    </label>
                                    <div class="grid gap-3 sm:grid-cols-2 md:grid-cols-3">
                                        @foreach ($roles as $role)
                                            <label class="flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 hover:bg-white hover:border-[var(--nmis-primary)]/30 transition-all cursor-pointer">
                                                <input type="checkbox" 
                                                       name="roles[]" 
                                                       value="{{ $role->name }}" 
                                                       class="rounded border-slate-300 text-[var(--nmis-primary)] focus:ring-[var(--nmis-primary)]/20">
                                                <span class="text-sm text-slate-700">{{ $role->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    <p class="mt-2 text-xs text-slate-500">Select one or more roles for the user</p>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="mt-6 flex items-center justify-end gap-3 border-t border-slate-200/60 pt-4">
                                <button type="button" 
                                        @click="openUserModal = false"
                                        class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-all">
                                    Cancel
                                </button>
                                <button type="submit" 
                                        class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-[var(--nmis-primary)] to-[var(--nmis-secondary)] px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-[var(--nmis-primary)]/20 hover:shadow-xl hover:scale-105 transition-all">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Create User
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endcan

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
                    <p class="text-sm font-semibold text-slate-500">Total Users</p>
                    <span class="rounded-lg bg-[var(--nmis-primary)]/10 p-2 text-[var(--nmis-primary)]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-bold text-slate-900">{{ $users->total() }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-slate-500">Active Users</p>
                    <span class="rounded-lg bg-[var(--nmis-accent)]/10 p-2 text-[var(--nmis-accent)]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-bold text-[var(--nmis-accent)]">{{ $users->where('is_active', true)->count() }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-slate-500">Inactive Users</p>
                    <span class="rounded-lg bg-rose-100 p-2 text-rose-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-bold text-rose-700">{{ $users->where('is_active', false)->count() }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-slate-500">Total Roles</p>
                    <span class="rounded-lg bg-[var(--nmis-secondary)]/10 p-2 text-[var(--nmis-secondary)]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-bold text-[var(--nmis-secondary)]">{{ $roles->count() }}</p>
            </div>
        </div>

        <!-- Users Table -->
        <div class="overflow-hidden rounded-xl border border-slate-200/60 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-gradient-to-r from-slate-50 to-white">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">User</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Email</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Roles</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($users as $user)
                            <tr x-data="{ openEdit: false }" class="hover:bg-slate-50/80 transition-colors duration-200 group">
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 flex-shrink-0 rounded-full bg-gradient-to-br from-[var(--nmis-primary)]/10 to-[var(--nmis-secondary)]/10 flex items-center justify-center">
                                            <span class="text-sm font-semibold text-[var(--nmis-primary)]">
                                                {{ strtoupper(substr($user->name, 0, 2)) }}
                                            </span>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-slate-900">{{ $user->name }}</div>
                                            <div class="text-xs text-slate-500">ID: #USR-{{ str_pad($user->id, 5, '0', STR_PAD_LEFT) }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="flex items-center gap-2 text-sm text-slate-900">
                                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                        {{ $user->email }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-1">
                                        @forelse ($user->roles as $role)
                                            <span class="inline-flex items-center rounded-full bg-[var(--nmis-primary)]/10 px-2 py-0.5 text-xs font-medium text-[var(--nmis-primary)]">
                                                {{ $role->name }}
                                            </span>
                                        @empty
                                            <span class="text-sm text-slate-400">No roles</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    @if($user->is_active)
                                        <span class="inline-flex items-center rounded-full bg-[var(--nmis-accent)]/10 px-2.5 py-1 text-xs font-medium text-[var(--nmis-accent)]">
                                            <span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-[var(--nmis-accent)]"></span>
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-rose-100 px-2.5 py-1 text-xs font-medium text-rose-700">
                                            <span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-rose-700"></span>
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    <button type="button" 
                                            @click="openEdit = !openEdit" 
                                            class="inline-flex items-center gap-1 rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-[var(--nmis-primary)] hover:text-white transition-all">
                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                        Edit
                                    </button>
                                </td>

                                <!-- Edit Row (hidden by default) -->
                                <tr x-show="openEdit" 
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0"
                                    x-transition:enter-end="opacity-100"
                                    x-transition:leave="transition ease-in duration-150"
                                    x-transition:leave-start="opacity-100"
                                    x-transition:leave-end="opacity-0"
                                    class="bg-slate-50/50">
                                    <td colspan="5" class="px-6 py-4">
                                        <div class="rounded-xl border border-slate-200 bg-white p-5">
                                            <div class="flex items-center justify-between mb-4">
                                                <h4 class="text-sm font-semibold text-slate-900 flex items-center gap-2">
                                                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-[var(--nmis-secondary)]/10">
                                                        <svg class="h-3 w-3 text-[var(--nmis-secondary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                        </svg>
                                                    </span>
                                                    Edit User: {{ $user->name }}
                                                </h4>
                                                <button @click="openEdit = false" class="text-slate-400 hover:text-slate-600">
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                </button>
                                            </div>

                                            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="grid gap-4 md:grid-cols-2">
                                                @csrf
                                                @method('PUT')
                                                
                                                <!-- Name -->
                                                <div>
                                                    <label class="block text-xs font-medium text-slate-700 mb-1">Full Name</label>
                                                    <input type="text" 
                                                           name="name" 
                                                           value="{{ $user->name }}" 
                                                           required 
                                                           class="w-full rounded-lg border-slate-300 bg-slate-50 px-3 py-2 text-sm focus:border-[var(--nmis-primary)] focus:ring-1 focus:ring-[var(--nmis-primary)]/20 transition-all">
                                                </div>

                                                <!-- Email -->
                                                <div>
                                                    <label class="block text-xs font-medium text-slate-700 mb-1">Email Address</label>
                                                    <input type="email" 
                                                           name="email" 
                                                           value="{{ $user->email }}" 
                                                           required 
                                                           class="w-full rounded-lg border-slate-300 bg-slate-50 px-3 py-2 text-sm focus:border-[var(--nmis-primary)] focus:ring-1 focus:ring-[var(--nmis-primary)]/20 transition-all">
                                                </div>

                                                <!-- Status -->
                                                <div>
                                                    <label class="block text-xs font-medium text-slate-700 mb-1">Account Status</label>
                                                    <select name="is_active" 
                                                            class="w-full rounded-lg border-slate-300 bg-slate-50 px-3 py-2 text-sm focus:border-[var(--nmis-primary)] focus:ring-1 focus:ring-[var(--nmis-primary)]/20 transition-all">
                                                        <option value="1" @selected($user->is_active)>Active</option>
                                                        <option value="0" @selected(!$user->is_active)>Inactive</option>
                                                    </select>
                                                </div>

                                                <!-- Password (optional) -->
                                                <div>
                                                    <label class="block text-xs font-medium text-slate-700 mb-1">
                                                        New Password <span class="text-slate-400">(leave blank to keep current)</span>
                                                    </label>
                                                    <input type="password" 
                                                           name="password" 
                                                           class="w-full rounded-lg border-slate-300 bg-slate-50 px-3 py-2 text-sm focus:border-[var(--nmis-primary)] focus:ring-1 focus:ring-[var(--nmis-primary)]/20 transition-all"
                                                           placeholder="••••••••">
                                                </div>

                                                <!-- Roles -->
                                                <div class="md:col-span-2">
                                                    <label class="block text-xs font-medium text-slate-700 mb-2">Assign Roles</label>
                                                    <div class="grid gap-2 sm:grid-cols-3 md:grid-cols-4">
                                                        @foreach ($roles as $role)
                                                            <label class="flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 hover:bg-white transition-all cursor-pointer">
                                                                <input type="checkbox" 
                                                                       name="roles[]" 
                                                                       value="{{ $role->name }}" 
                                                                       class="rounded border-slate-300 text-[var(--nmis-primary)] focus:ring-[var(--nmis-primary)]/20"
                                                                       @checked($user->roles->contains('name', $role->name))>
                                                                <span class="text-xs text-slate-700">{{ $role->name }}</span>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                </div>

                                                <!-- Form Actions -->
                                                <div class="md:col-span-2 flex items-center justify-end gap-2 mt-2">
                                                    <button type="button" 
                                                            @click="openEdit = false"
                                                            class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-all">
                                                        Cancel
                                                    </button>
                                                    <button type="submit" 
                                                            class="inline-flex items-center gap-1 rounded-lg bg-gradient-to-r from-[var(--nmis-primary)] to-[var(--nmis-secondary)] px-4 py-2 text-xs font-semibold text-white shadow-lg shadow-[var(--nmis-primary)]/20 hover:shadow-xl transition-all">
                                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                                                        </svg>
                                                        Save Changes
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="rounded-full bg-slate-100 p-3 mb-4">
                                            <svg class="h-8 w-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                            </svg>
                                        </div>
                                        <h3 class="text-sm font-medium text-slate-900 mb-1">No users found</h3>
                                        <p class="text-sm text-slate-500 mb-4">Get started by creating your first user</p>
                                        @can('users.create')
                                            <button 
                                                x-data="{}"
                                                @click="$dispatch('open-user-modal')"
                                                class="inline-flex items-center gap-2 rounded-lg bg-[var(--nmis-primary)] px-4 py-2 text-sm font-medium text-white hover:bg-[var(--nmis-secondary)] transition-colors">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                </svg>
                                                Create New User
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $users->withQueryString()->links() }}
        </div>
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

        [x-cloak] {
            display: none !important;
        }

        .gradient-text {
            background: linear-gradient(135deg, var(--nmis-primary), var(--nmis-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        /* Custom scrollbar for modal */
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

    @push('scripts')
    <script>
        // Auto-hide success message after 5 seconds
        const successMessage = document.querySelector('.animate-slide-down');
        if (successMessage) {
            setTimeout(() => {
                successMessage.style.opacity = '0';
                setTimeout(() => {
                    successMessage.remove();
                }, 300);
            }, 5000);
        }
    </script>
    @endpush
</x-app-layout>