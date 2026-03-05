<aside class="fixed inset-y-0 left-0 z-40 w-64 bg-gradient-to-b from-[var(--nmis-primary)] to-[#0f1f3f] shadow-2xl transition-all duration-300 lg:translate-x-0"
       :class="{ 
           '-translate-x-full': !mobileSidebar && !sidebarCollapsed,
           'translate-x-0': mobileSidebar || sidebarCollapsed,
           'lg:w-20': sidebarCollapsed 
       }">
    
    <!-- Logo Section -->
    <div class="flex items-center gap-3 px-4 py-5 border-b border-white/10" 
         :class="{ 'justify-center': sidebarCollapsed }">
        <div class="flex h-14 w-14 items-center justify-center rounded-xl border border-white/30 bg-white/95 shadow-[0_8px_20px_rgba(15,23,42,0.22)] ring-1 ring-black/5">
            <img src="{{ asset('images/nexus-logo.png') }}" alt="NexusFlow" class="h-10 w-auto">
        </div>
        <div x-show="!sidebarCollapsed" class="overflow-hidden">
            <p class="text-xs uppercase tracking-wider text-slate-300/60">NexusFlow</p>
            <p class="text-sm font-bold text-white">NMIS Portal</p>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="p-3 h-[calc(100vh-5rem)] overflow-y-auto scrollbar-thin scrollbar-thumb-white/10 scrollbar-track-transparent">
        <ul class="space-y-1">
            @php
                $navItems = [
                    ['route' => 'dashboard', 'name' => 'home', 'label' => 'Dashboard', 'permission' => null],
                    ['route' => 'customers.*', 'name' => 'users', 'label' => 'Customers', 'permission' => 'customers.view'],
                    ['route' => 'trips.*', 'name' => 'trip', 'label' => 'Trips', 'permission' => 'trips.view'],
                    ['route' => 'orders.*', 'name' => 'box', 'label' => 'Orders', 'permission' => 'orders.view'],
                    ['route' => 'fleet.*', 'name' => 'truck', 'label' => 'Fleet', 'permission' => 'fleet.view'],
                    ['route' => 'drivers.*', 'name' => 'id', 'label' => 'Drivers', 'permission' => 'drivers.view'],
                    ['route' => 'fuel.*', 'name' => 'fuel', 'label' => 'Fuel Requisitions', 'permission' => 'fuel.view'],
                    ['route' => 'hr.employees.*', 'name' => 'users', 'label' => 'HR Employees', 'permission' => 'hr.employees.view|hr.employees.manage'],
                   // ['route' => 'notifications.*', 'name' => 'bell', 'label' => 'Notifications', 'permission' => null],
                    ['route' => 'admin.users.*', 'name' => 'shield', 'label' => 'Admin Users', 'permission' => 'admin.users.manage'],
                    ['route' => 'admin.roles.*', 'name' => 'lock', 'label' => 'Roles & Permissions', 'permission' => 'admin.roles.manage'],
                    ['route' => 'admin.logs.*', 'name' => 'scroll', 'label' => 'Audit Logs', 'permission' => 'admin.logs.view'],
                    ['route' => 'admin.reports.*', 'name' => 'chart', 'label' => 'Reports', 'permission' => 'admin.dashboard.view_all'],
                ];
            @endphp

            @foreach($navItems as $item)
                @php
                    $canSeeItem = true;
                    if (!empty($item['permission'])) {
                        $permissions = explode('|', (string) $item['permission']);
                        $canSeeItem = collect($permissions)->contains(fn ($permission) => Gate::allows(trim($permission)));
                    }
                @endphp
                @if($canSeeItem)
                    <li>
                        <a href="{{ route(str_replace('.*', '.index', $item['route'])) }}" 
                           class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-200 group relative
                                  {{ request()->routeIs($item['route']) 
                                     ? 'bg-white/15 text-white shadow-lg' 
                                     : 'text-slate-300/90 hover:bg-white/10 hover:text-white' }}"
                           :class="{ 'justify-center': sidebarCollapsed }">
                            
                            <!-- Icon -->
                            <span class="flex-shrink-0">
                                @include('layouts.partials.icon', ['name' => $item['name'], 'size' => 'h-5 w-5'])
                            </span>
                            
                            <!-- Label -->
                            <span x-show="!sidebarCollapsed" class="truncate">{{ $item['label'] }}</span>
                            
                            <!-- Active Indicator -->
                            @if(request()->routeIs($item['route']))
                                <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-white rounded-r-full"
                                      :class="{ 'hidden': sidebarCollapsed }"></span>
                            @endif
                            
                            <!-- Tooltip (collapsed mode) -->
                            <span x-show="sidebarCollapsed" 
                                  class="absolute left-full ml-2 px-2 py-1 bg-slate-900 text-white text-xs rounded-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 whitespace-nowrap z-50">
                                {{ $item['label'] }}
                            </span>
                        </a>
                    </li>
                @endif
            @endforeach
        </ul>
    </nav>
</aside>
