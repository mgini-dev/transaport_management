<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-3xl font-bold gradient-text">Customers Management</h2>
                <p class="mt-2 text-sm text-slate-500">Manage and track all your customer information in one place</p>
            </div>
            
            @can('customers.create')
                <button 
                    x-data="{}"
                    @click="$dispatch('open-customer-modal')"
                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-[var(--nmis-primary)] to-[var(--nmis-secondary)] px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-[var(--nmis-primary)]/20 hover:shadow-xl hover:scale-105 transition-all duration-300 group">
                    <svg class="h-5 w-5 group-hover:rotate-90 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Register New Customer
                </button>
            @endcan
        </div>
    </x-slot>

    <div class="space-y-6" 
         x-data="{ 
            openCustomerModal: false, 
            openEditModal: false,
            openDeleteModal: false,
            editForm: { id: '', name: '', contact_person: '', phone: '', email: '', address: '' },
            deleteTarget: { id: '', name: '' }
         }" 
         @open-customer-modal.window="openCustomerModal = true">
        
        <!-- Customer Registration Modal -->
        @can('customers.create')
            <div x-show="openCustomerModal" 
                 x-cloak
                 class="fixed inset-0 z-50 overflow-y-auto"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">
                
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" @click="openCustomerModal = false"></div>

                <!-- Modal Panel -->
                <div class="flex min-h-full items-center justify-center p-4">
                    <div x-show="openCustomerModal" 
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
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-slate-900">Register New Customer</h3>
                                    <p class="text-xs text-slate-500">Fill in the customer details below</p>
                                </div>
                            </div>
                            <button @click="openCustomerModal = false" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-500 transition-colors">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <!-- Modal Form -->
                        <form method="POST" action="{{ route('customers.store') }}" class="p-6">
                            @csrf
                            <div class="grid gap-5 sm:grid-cols-2">
                                <!-- Customer Name -->
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-slate-700 mb-1">
                                        Customer Name <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="text" 
                                           name="name" 
                                           required 
                                           class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all"
                                           placeholder="Enter customer name">
                                </div>

                                <!-- Contact Person -->
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">
                                        Contact Person
                                    </label>
                                    <input type="text" 
                                           name="contact_person" 
                                           class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all"
                                           placeholder="Full name">
                                </div>

                                <!-- Phone -->
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">
                                        Phone Number
                                    </label>
                                    <input type="tel" 
                                           name="phone" 
                                           class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all"
                                           placeholder="+254 XXX XXX XXX">
                                </div>

                                <!-- Email -->
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-slate-700 mb-1">
                                        Email Address
                                    </label>
                                    <input type="email" 
                                           name="email" 
                                           class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all"
                                           placeholder="customer@example.com">
                                </div>

                                <!-- Address -->
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-slate-700 mb-1">
                                        Address <span class="text-rose-500">*</span>
                                    </label>
                                    <textarea name="address" 
                                              required 
                                              rows="3"
                                              class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all resize-none"
                                              placeholder="Street address, city, country"></textarea>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="mt-6 flex items-center justify-end gap-3 border-t border-slate-200/60 pt-4">
                                <button type="button" 
                                        @click="openCustomerModal = false"
                                        class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-all">
                                    Cancel
                                </button>
                                <button type="submit" 
                                        class="rounded-xl bg-gradient-to-r from-[var(--nmis-primary)] to-[var(--nmis-secondary)] px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-[var(--nmis-primary)]/20 hover:shadow-xl hover:scale-105 transition-all">
                                    Register Customer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endcan

        <!-- Edit Customer Modal -->
        @can('customers.edit')
            <div x-show="openEditModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto"
                 x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" @click="openEditModal = false"></div>
                <div class="flex min-h-full items-center justify-center p-4">
                    <div x-show="openEditModal"
                         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                         class="relative w-full max-w-2xl rounded-2xl bg-white shadow-2xl">
                        <div class="flex items-center justify-between border-b border-slate-200/60 px-6 py-4">
                            <h3 class="text-lg font-semibold text-slate-900">Edit Customer</h3>
                            <button @click="openEditModal = false" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-500">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                        <form :action="`{{ url('/customers') }}/${editForm.id}`" method="POST" class="p-6">
                            @csrf
                            @method('PUT')
                            <div class="grid gap-5 sm:grid-cols-2">
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Customer Name <span class="text-rose-500">*</span></label>
                                    <input type="text" name="name" required x-model="editForm.name" class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Contact Person</label>
                                    <input type="text" name="contact_person" x-model="editForm.contact_person" class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Phone Number</label>
                                    <input type="tel" name="phone" x-model="editForm.phone" class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Email Address</label>
                                    <input type="email" name="email" x-model="editForm.email" class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Address <span class="text-rose-500">*</span></label>
                                    <textarea name="address" rows="3" required x-model="editForm.address" class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 resize-none"></textarea>
                                </div>
                            </div>
                            <div class="mt-6 flex items-center justify-end gap-3 border-t border-slate-200/60 pt-4">
                                <button type="button" @click="openEditModal = false" class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
                                <button type="submit" class="rounded-xl bg-gradient-to-r from-[var(--nmis-primary)] to-[var(--nmis-secondary)] px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-[var(--nmis-primary)]/20">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endcan

        <!-- Delete Customer Confirmation Modal -->
        @can('customers.delete')
            <div x-show="openDeleteModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto"
                 x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" @click="openDeleteModal = false"></div>
                <div class="flex min-h-full items-center justify-center p-4">
                    <div x-show="openDeleteModal"
                         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                         class="relative w-full max-w-md rounded-2xl bg-white shadow-2xl">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-slate-900">Delete Customer</h3>
                            <p class="mt-2 text-sm text-slate-600">
                                Are you sure you want to delete <span class="font-semibold text-slate-900" x-text="deleteTarget.name"></span>?
                                This action cannot be undone.
                            </p>
                            <form :action="`{{ url('/customers') }}/${deleteTarget.id}`" method="POST" class="mt-6 flex justify-end gap-3">
                                @csrf
                                @method('DELETE')
                                <button type="button" @click="openDeleteModal = false" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
                                <button type="submit" class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700">Delete</button>
                            </form>
                        </div>
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
        <div class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-slate-500">Total Customers</p>
                    <span class="rounded-lg bg-[var(--nmis-primary)]/10 p-2 text-[var(--nmis-primary)]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-bold text-slate-900">{{ $customers->total() }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-slate-500">With Contacts</p>
                    <span class="rounded-lg bg-[var(--nmis-secondary)]/10 p-2 text-[var(--nmis-secondary)]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-bold text-slate-900">{{ $customers->where('contact_person', '!=', null)->count() }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-slate-500">With Email</p>
                    <span class="rounded-lg bg-[var(--nmis-accent)]/10 p-2 text-[var(--nmis-accent)]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-bold text-slate-900">{{ $customers->where('email', '!=', null)->count() }}</p>
            </div>
        </div>

        <!-- Search and Filter Bar -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="relative flex-1">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                    <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input type="text" 
                       placeholder="Search customers..." 
                       class="w-full rounded-xl border-slate-200 bg-white pl-11 pr-4 py-3 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all"
                       id="customerSearch">
            </div>
            <div class="flex items-center gap-3">
                <select class="rounded-xl border-slate-200 bg-white px-4 py-3 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all">
                    <option value="10">10 per page</option>
                    <option value="25">25 per page</option>
                    <option value="50">50 per page</option>
                    <option value="100">100 per page</option>
                </select>
                <button class="rounded-xl border border-slate-200 bg-white p-3 text-slate-600 hover:bg-slate-50 hover:text-[var(--nmis-primary)] transition-all">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Customers Table -->
        <div class="overflow-hidden rounded-xl border border-slate-200/60 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-gradient-to-r from-slate-50 to-white">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Customer</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Contact Person</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Phone</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Email</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Address</th>
                            @canany(['customers.edit', 'customers.delete'])
                                <th scope="col" class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Actions</th>
                            @endcanany
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($customers as $customer)
                            <tr class="hover:bg-slate-50/80 transition-colors duration-200 group">
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 flex-shrink-0 rounded-full bg-gradient-to-br from-[var(--nmis-primary)]/10 to-[var(--nmis-secondary)]/10 flex items-center justify-center">
                                            <span class="text-sm font-semibold text-[var(--nmis-primary)]">
                                                {{ strtoupper(substr($customer->name, 0, 2)) }}
                                            </span>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-slate-900">{{ $customer->name }}</div>
                                            <div class="text-xs text-slate-500">ID: #CUST-{{ str_pad($customer->id, 5, '0', STR_PAD_LEFT) }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="text-sm text-slate-900">{{ $customer->contact_person ?: '-' }}</div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    @if($customer->phone)
                                        <div class="flex items-center gap-2 text-sm text-slate-900">
                                            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                            </svg>
                                            {{ $customer->phone }}
                                        </div>
                                    @else
                                        <span class="text-sm text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    @if($customer->email)
                                        <div class="flex items-center gap-2 text-sm text-slate-900">
                                            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                            </svg>
                                            <a href="mailto:{{ $customer->email }}" class="hover:text-[var(--nmis-primary)] transition-colors">
                                                {{ $customer->email }}
                                            </a>
                                        </div>
                                    @else
                                        <span class="text-sm text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-start gap-2 text-sm text-slate-900 max-w-xs">
                                        <svg class="h-4 w-4 text-slate-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        <span class="line-clamp-2">{{ $customer->address }}</span>
                                    </div>
                                </td>
                                @canany(['customers.edit', 'customers.delete'])
                                    <td class="whitespace-nowrap px-6 py-4 text-right">
                                        <!-- Action buttons with improved visibility -->
                                        <div class="flex items-center justify-end gap-2">
                                            @can('customers.edit')
                                                <button type="button"
                                                        @click="editForm = {
                                                            id: '{{ $customer->encrypted_id }}',
                                                            name: @js($customer->name),
                                                            contact_person: @js($customer->contact_person ?? ''),
                                                            phone: @js($customer->phone ?? ''),
                                                            email: @js($customer->email ?? ''),
                                                            address: @js($customer->address)
                                                        }; openEditModal = true"
                                                        class="rounded-lg bg-slate-100 p-2 text-slate-600 hover:bg-[var(--nmis-primary)] hover:text-white transition-all opacity-60 hover:opacity-100" title="Edit">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                    </svg>
                                                </button>
                                            @endcan
                                            @can('customers.delete')
                                                <button type="button"
                                                        @click="deleteTarget = { id: '{{ $customer->encrypted_id }}', name: @js($customer->name) }; openDeleteModal = true"
                                                        class="rounded-lg bg-slate-100 p-2 text-rose-600 hover:bg-rose-600 hover:text-white transition-all opacity-60 hover:opacity-100" title="Delete">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            @endcan
                                        </div>
                                    </td>
                                @endcanany
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="rounded-full bg-slate-100 p-3 mb-4">
                                            <svg class="h-8 w-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                            </svg>
                                        </div>
                                        <h3 class="text-sm font-medium text-slate-900 mb-1">No customers found</h3>
                                        <p class="text-sm text-slate-500 mb-4">Get started by registering your first customer</p>
                                        @can('customers.create')
                                            <button 
                                                x-data="{}"
                                                @click="$dispatch('open-customer-modal')"
                                                class="inline-flex items-center gap-2 rounded-lg bg-[var(--nmis-primary)] px-4 py-2 text-sm font-medium text-white hover:bg-[var(--nmis-secondary)] transition-colors">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                </svg>
                                                Register Customer
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
            {{ $customers->links() }}
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

        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
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
        // Live search functionality
        document.getElementById('customerSearch')?.addEventListener('keyup', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                if (row.querySelector('td[colspan="6"]')) return; // Skip empty state row
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        // Handle per page change
        document.querySelector('select')?.addEventListener('change', function(e) {
            const perPage = e.target.value;
            const url = new URL(window.location.href);
            url.searchParams.set('per_page', perPage);
            window.location.href = url.toString();
        });

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
