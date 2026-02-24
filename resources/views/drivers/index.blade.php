<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-3xl font-bold gradient-text">Driver Management</h2>
                <p class="mt-2 text-sm text-slate-500">Register, update, and manage driver activation with fleet mapping</p>
            </div>
            
            @can('drivers.create')
                <button 
                    x-data="{}"
                    @click="$dispatch('open-driver-modal')"
                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-[var(--nmis-primary)] to-[var(--nmis-secondary)] px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-[var(--nmis-primary)]/20 hover:shadow-xl hover:scale-105 transition-all duration-300 group">
                    <svg class="h-5 w-5 group-hover:rotate-90 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Register New Driver
                </button>
            @endcan
        </div>
    </x-slot>

    <div class="space-y-6" 
         x-data="driverManager()" 
         @open-driver-modal.window="openCreateModal()">
        
        <!-- Create Driver Modal -->
        @can('drivers.create')
            <div x-show="showCreateModal" 
                 x-cloak
                 class="fixed inset-0 z-50 overflow-y-auto"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">
                
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" @click="showCreateModal = false"></div>

                <!-- Modal Panel -->
                <div class="flex min-h-full items-center justify-center p-4">
                    <div x-show="showCreateModal" 
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="relative w-full max-w-2xl rounded-2xl bg-white shadow-2xl">
                        
                        <!-- Modal Header -->
                        <div class="flex items-center justify-between border-b border-slate-200/60 px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-[var(--nmis-primary)]/10 to-[var(--nmis-secondary)]/10">
                                    <svg class="h-5 w-5 text-[var(--nmis-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-slate-900">Register New Driver</h3>
                                    <p class="text-xs text-slate-500">Add a new driver to your team</p>
                                </div>
                            </div>
                            <button @click="showCreateModal = false" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-500 transition-colors">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <!-- Modal Form -->
                        <form method="POST" action="{{ route('drivers.store') }}" class="p-6">
                            @csrf
                            <div class="grid gap-5 md:grid-cols-2">
                                <!-- Driver Name -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-slate-700 mb-1">
                                        Driver Name <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="text" 
                                           name="name" 
                                           required 
                                           class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all"
                                           placeholder="Enter driver's full name">
                                </div>

                                <!-- License Number -->
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">
                                        License Number <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="text" 
                                           name="license_number" 
                                           required 
                                           class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all"
                                           placeholder="e.g., DL-123456">
                                </div>

                                <!-- Mobile Number -->
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">
                                        Mobile Number <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="tel" 
                                           name="mobile_number" 
                                           required 
                                           class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all"
                                           placeholder="+254 XXX XXX XXX">
                                </div>

                                <!-- Fleet Assignment -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-slate-700 mb-1">
                                        Assign Fleet (Optional)
                                    </label>
                                    <select name="fleet_id" 
                                            class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all">
                                        <option value="">No fleet assigned</option>
                                        @foreach ($fleets as $fleet)
                                            <option value="{{ $fleet->encrypted_id }}">{{ $fleet->fleet_code }} - {{ $fleet->plate_number }} ({{ $fleet->capacity_tons }} tons)</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Status -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-slate-700 mb-1">
                                        Status <span class="text-rose-500">*</span>
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
                            </div>

                            <!-- Form Actions -->
                            <div class="mt-6 flex items-center justify-end gap-3 border-t border-slate-200/60 pt-4">
                                <button type="button" 
                                        @click="showCreateModal = false"
                                        class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-all">
                                    Cancel
                                </button>
                                <button type="submit" 
                                        class="rounded-xl bg-gradient-to-r from-[var(--nmis-primary)] to-[var(--nmis-secondary)] px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-[var(--nmis-primary)]/20 hover:shadow-xl hover:scale-105 transition-all">
                                    Register Driver
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endcan

        <!-- Edit Driver Modal -->
        <div x-show="showEditModal" 
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" @click="showEditModal = false"></div>

            <!-- Modal Panel -->
            <div class="flex min-h-full items-center justify-center p-4">
                <div x-show="showEditModal" 
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="relative w-full max-w-2xl rounded-2xl bg-white shadow-2xl">
                    
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between border-b border-slate-200/60 px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-[var(--nmis-primary)]/10 to-[var(--nmis-secondary)]/10">
                                <svg class="h-5 w-5 text-[var(--nmis-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900">Edit Driver</h3>
                                <p class="text-xs text-slate-500">Update driver information</p>
                            </div>
                        </div>
                        <button @click="showEditModal = false" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-500 transition-colors">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Modal Form -->
                    <form :action="`{{ url('/drivers') }}/${form.id}`" method="POST" class="p-6">
                        @csrf
                        @method('PUT')
                        <div class="grid gap-5 md:grid-cols-2">
                            <!-- Driver Name -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-slate-700 mb-1">
                                    Driver Name <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" 
                                       name="name" 
                                       x-model="form.name"
                                       required 
                                       class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all"
                                       placeholder="Enter driver's full name">
                            </div>

                            <!-- License Number -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">
                                    License Number <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" 
                                       name="license_number" 
                                       x-model="form.license_number"
                                       required 
                                       class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all"
                                       placeholder="e.g., DL-123456">
                            </div>

                            <!-- Mobile Number -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">
                                    Mobile Number <span class="text-rose-500">*</span>
                                </label>
                                <input type="tel" 
                                       name="mobile_number" 
                                       x-model="form.mobile_number"
                                       required 
                                       class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all"
                                       placeholder="+254 XXX XXX XXX">
                            </div>

                            <!-- Fleet Assignment -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-slate-700 mb-1">
                                    Assign Fleet (Optional)
                                </label>
                                <select name="fleet_id" 
                                        x-model="form.fleet_id"
                                        class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all">
                                    <option value="">No fleet assigned</option>
                                    @foreach ($fleets as $fleet)
                                        <option value="{{ $fleet->encrypted_id }}">{{ $fleet->fleet_code }} - {{ $fleet->plate_number }} ({{ $fleet->capacity_tons }} tons)</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Status -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-slate-700 mb-1">
                                    Status <span class="text-rose-500">*</span>
                                </label>
                                <div class="flex gap-4">
                                    <label class="flex items-center gap-2">
                                        <input type="radio" name="is_active" value="1" x-model="form.is_active" class="rounded-full border-slate-300 text-[var(--nmis-primary)] focus:ring-[var(--nmis-primary)]/20">
                                        <span class="text-sm text-slate-700">Active</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input type="radio" name="is_active" value="0" x-model="form.is_active" class="rounded-full border-slate-300 text-[var(--nmis-primary)] focus:ring-[var(--nmis-primary)]/20">
                                        <span class="text-sm text-slate-700">Inactive</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="mt-6 flex items-center justify-end gap-3 border-t border-slate-200/60 pt-4">
                            <button type="button" 
                                    @click="showEditModal = false"
                                    class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-all">
                                Cancel
                            </button>
                            <button type="submit" 
                                    class="rounded-xl bg-gradient-to-r from-[var(--nmis-primary)] to-[var(--nmis-secondary)] px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-[var(--nmis-primary)]/20 hover:shadow-xl hover:scale-105 transition-all">
                                Update Driver
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

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
                    <p class="text-sm font-semibold text-slate-500">Total Drivers</p>
                    <span class="rounded-lg bg-[var(--nmis-primary)]/10 p-2 text-[var(--nmis-primary)]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-bold text-slate-900">{{ $driverStats['total'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-slate-500">Active Drivers</p>
                    <span class="rounded-lg bg-[var(--nmis-accent)]/10 p-2 text-[var(--nmis-accent)]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-bold text-[var(--nmis-accent)]">{{ $driverStats['active'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-slate-500">Inactive Drivers</p>
                    <span class="rounded-lg bg-[var(--nmis-secondary)]/10 p-2 text-[var(--nmis-secondary)]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-bold text-[var(--nmis-secondary)]">{{ $driverStats['inactive'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-slate-500">With Fleet</p>
                    <span class="rounded-lg bg-[var(--nmis-primary)]/10 p-2 text-[var(--nmis-primary)]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A2 2 0 013 15.382V5.618a2 2 0 011.053-1.764L9 2m0 18l5.447-2.724A2 2 0 0016 15.382V5.618a2 2 0 00-1.053-1.764L9 2m0 18V2"></path>
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-bold text-slate-900">{{ $driverStats['with_fleet'] }}</p>
            </div>
        </div>

        <!-- Search and Filter Bar -->
        <form method="GET" class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="relative flex-1">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                    <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="Search by name, license, or mobile..." 
                       class="w-full rounded-xl border-slate-200 bg-white pl-11 pr-4 py-3 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all">
            </div>
            <div class="flex items-center gap-3">
                <select name="active" 
                        class="rounded-xl border-slate-200 bg-white px-4 py-3 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all min-w-[150px]">
                    <option value="">All Statuses</option>
                    <option value="1" {{ request('active') === '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ request('active') === '0' ? 'selected' : '' }}>Inactive</option>
                </select>
                <button type="submit" 
                        class="inline-flex items-center gap-2 rounded-xl bg-[var(--nmis-primary)] px-5 py-3 text-sm font-semibold text-white hover:bg-[var(--nmis-secondary)] transition-all shadow-lg shadow-[var(--nmis-primary)]/20">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    Apply Filters
                </button>
            </div>
        </form>

        <!-- Drivers Table -->
        <div class="overflow-hidden rounded-xl border border-slate-200/60 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-gradient-to-r from-slate-50 to-white">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Driver</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">License</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Mobile</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Fleet Assignment</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($drivers as $driver)
                            <tr class="hover:bg-slate-50/80 transition-colors duration-200 group">
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 flex-shrink-0 rounded-full bg-gradient-to-br from-[var(--nmis-primary)]/10 to-[var(--nmis-secondary)]/10 flex items-center justify-center">
                                            <span class="text-sm font-semibold text-[var(--nmis-primary)]">
                                                {{ strtoupper(substr($driver->name, 0, 2)) }}
                                            </span>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-slate-900">{{ $driver->name }}</div>
                                            <div class="text-xs text-slate-500">ID: #DRV-{{ str_pad($driver->id, 5, '0', STR_PAD_LEFT) }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="text-sm font-mono text-slate-900">{{ $driver->license_number }}</div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="flex items-center gap-2 text-sm text-slate-900">
                                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                        </svg>
                                        {{ $driver->mobile_number }}
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    @if($driver->fleet)
                                        <div class="flex items-center gap-2">
                                            <div class="h-8 w-8 rounded-lg bg-slate-100 flex items-center justify-center">
                                                <span class="text-xs font-semibold text-slate-600">{{ substr($driver->fleet->fleet_code, 0, 2) }}</span>
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-slate-900">{{ $driver->fleet->fleet_code }}</div>
                                                <div class="text-xs text-slate-500">{{ $driver->fleet->plate_number }}</div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-sm text-slate-400">No fleet assigned</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    @if($driver->is_active)
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
                                    <div class="flex items-center justify-end gap-2 opacity-60 group-hover:opacity-100 transition-opacity duration-200">
                                        @can('drivers.update')
                                            <button type="button"
                                                    class="rounded-lg bg-slate-100 p-2 text-slate-600 hover:bg-[var(--nmis-primary)] hover:text-white transition-all"
                                                    title="Edit Driver"
                                                    @click="openEditModal({
                                                        id: '{{ $driver->encrypted_id }}',
                                                        name: @js($driver->name),
                                                        license_number: @js($driver->license_number),
                                                        mobile_number: @js($driver->mobile_number),
                                                        is_active: '{{ $driver->is_active ? 1 : 0 }}',
                                                        fleet_id: '{{ $driver->fleet?->encrypted_id ?? '' }}'
                                                    })">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </button>
                                        @endcan
                                        @can('drivers.delete')
                                            <form method="POST" action="{{ route('drivers.destroy', $driver->encrypted_id) }}" 
                                                  class="inline"
                                                  onsubmit="return confirm('Are you sure you want to delete this driver?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="rounded-lg bg-slate-100 p-2 text-rose-600 hover:bg-rose-600 hover:text-white transition-all"
                                                        title="Delete Driver">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="rounded-full bg-slate-100 p-3 mb-4">
                                            <svg class="h-8 w-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                        </div>
                                        <h3 class="text-sm font-medium text-slate-900 mb-1">No drivers found</h3>
                                        <p class="text-sm text-slate-500 mb-4">Get started by registering your first driver</p>
                                        @can('drivers.create')
                                            <button 
                                                x-data="{}"
                                                @click="$dispatch('open-driver-modal')"
                                                class="inline-flex items-center gap-2 rounded-lg bg-[var(--nmis-primary)] px-4 py-2 text-sm font-medium text-white hover:bg-[var(--nmis-secondary)] transition-colors">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                </svg>
                                                Register Driver
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
            {{ $drivers->links() }}
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
    </style>

    @push('scripts')
    <script>
        function driverManager() {
            return {
                showCreateModal: false,
                showEditModal: false,
                form: { 
                    id: '', 
                    name: '', 
                    license_number: '', 
                    mobile_number: '', 
                    is_active: '1', 
                    fleet_id: '' 
                },
                
                openCreateModal() {
                    this.showCreateModal = true;
                },
                
                openEditModal(payload) {
                    this.form = { ...payload };
                    this.showEditModal = true;
                }
            }
        }

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
