<aside class="nmis-sidebar" :class="{ 'nmis-sidebar-collapsed': sidebarCollapsed, 'nmis-sidebar-mobile-open': mobileSidebar }">
    <div class="nmis-logo-wrap">
        <img src="{{ asset('images/nexus-logo.png') }}" alt="NexusFlow" class="h-10 w-auto">
        <div x-show="!sidebarCollapsed">
            <p class="text-xs uppercase tracking-[0.22em] text-slate-300">NexusFlow</p>
            <p class="text-sm font-semibold text-white">NMIS Console</p>
        </div>
    </div>

    <p class="nmis-menu-title" x-show="!sidebarCollapsed">Navigation</p>
    <nav>
        <ul class="space-y-1 text-sm">
            <li><a class="nmis-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><span class="nmis-link-icon">🏠</span><span x-show="!sidebarCollapsed">Dashboard</span></a></li>
            @can('customers.view')
                <li><a class="nmis-link {{ request()->routeIs('customers.*') ? 'active' : '' }}" href="{{ route('customers.index') }}"><span class="nmis-link-icon">👥</span><span x-show="!sidebarCollapsed">Customers</span></a></li>
            @endcan
            @can('trips.view')
                <li><a class="nmis-link {{ request()->routeIs('trips.*') ? 'active' : '' }}" href="{{ route('trips.index') }}"><span class="nmis-link-icon">🧭</span><span x-show="!sidebarCollapsed">Trips</span></a></li>
            @endcan
            @can('orders.view')
                <li><a class="nmis-link {{ request()->routeIs('orders.*') ? 'active' : '' }}" href="{{ route('orders.index') }}"><span class="nmis-link-icon">📦</span><span x-show="!sidebarCollapsed">Orders</span></a></li>
            @endcan
            @can('fleet.view')
                <li><a class="nmis-link {{ request()->routeIs('fleet.*') ? 'active' : '' }}" href="{{ route('fleet.index') }}"><span class="nmis-link-icon">🚚</span><span x-show="!sidebarCollapsed">Fleet</span></a></li>
            @endcan
            @can('drivers.view')
                <li><a class="nmis-link {{ request()->routeIs('drivers.*') ? 'active' : '' }}" href="{{ route('drivers.index') }}"><span class="nmis-link-icon">🪪</span><span x-show="!sidebarCollapsed">Drivers</span></a></li>
            @endcan
            @can('fuel.view')
                <li><a class="nmis-link {{ request()->routeIs('fuel.*') ? 'active' : '' }}" href="{{ route('fuel.index') }}"><span class="nmis-link-icon">⛽</span><span x-show="!sidebarCollapsed">Fuel Requisitions</span></a></li>
            @endcan
            <li><a class="nmis-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}" href="{{ route('notifications.center') }}"><span class="nmis-link-icon">🔔</span><span x-show="!sidebarCollapsed">Notifications</span></a></li>
            @can('admin.users.manage')
                <li><a class="nmis-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}"><span class="nmis-link-icon">🛡️</span><span x-show="!sidebarCollapsed">Admin Users</span></a></li>
            @endcan
            @can('admin.logs.view')
                <li><a class="nmis-link {{ request()->routeIs('admin.logs.*') ? 'active' : '' }}" href="{{ route('admin.logs.index') }}"><span class="nmis-link-icon">📜</span><span x-show="!sidebarCollapsed">Audit Logs</span></a></li>
            @endcan
        </ul>
    </nav>
</aside>
