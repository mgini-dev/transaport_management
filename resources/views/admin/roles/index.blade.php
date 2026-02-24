<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Role & Permission Management</h2>
        <p class="mt-1 text-sm text-slate-500">Dynamic security panel for roles, permissions, and direct user grants.</p>
    </x-slot>

    <div class="space-y-6">
        <div class="grid gap-6 lg:grid-cols-2">
            <form method="POST" action="{{ route('admin.permissions.store') }}" class="rounded-lg border border-slate-200 bg-white p-4">
                @csrf
                <h3 class="mb-3 text-sm font-semibold text-slate-700">Create Permission</h3>
                <input name="name" class="w-full rounded-md border-slate-300" placeholder="example: orders.export" required />
                <button class="mt-3 rounded-md bg-[var(--nmis-primary)] px-4 py-2 text-sm font-semibold text-white">Add Permission</button>
            </form>

            <form method="POST" action="{{ route('admin.roles.store') }}" class="rounded-lg border border-slate-200 bg-white p-4">
                @csrf
                <h3 class="mb-3 text-sm font-semibold text-slate-700">Create Role</h3>
                <input name="name" class="w-full rounded-md border-slate-300" placeholder="Role name" required />
                <div class="mt-3 grid max-h-44 gap-2 overflow-y-auto rounded-md border border-slate-200 p-2 sm:grid-cols-2">
                    @foreach ($permissions as $permission)
                        <label class="inline-flex items-center gap-2 text-xs">
                            <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" class="rounded border-slate-300" />
                            <span>{{ $permission->name }}</span>
                        </label>
                    @endforeach
                </div>
                <button class="mt-3 rounded-md bg-[var(--nmis-secondary)] px-4 py-2 text-sm font-semibold text-white">Create Role</button>
            </form>
        </div>

        <section class="rounded-lg border border-slate-200 bg-white p-4">
            <h3 class="mb-3 text-sm font-semibold text-slate-700">Role Permission Assignment</h3>
            <div class="space-y-3">
                @foreach ($roles as $role)
                    <details class="rounded-md border border-slate-200 p-3">
                        <summary class="cursor-pointer text-sm font-semibold text-[var(--nmis-primary)]">{{ $role->name }}</summary>
                        <form method="POST" action="{{ route('admin.roles.update', $role) }}" class="mt-3">
                            @csrf
                            @method('PUT')
                            <div class="grid max-h-56 gap-2 overflow-y-auto rounded-md border border-slate-200 p-2 sm:grid-cols-3">
                                @foreach ($permissionsByGroup as $group => $groupPermissions)
                                    <div class="rounded-md border border-slate-100 p-2">
                                        <p class="mb-1 text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ $group }}</p>
                                        <div class="space-y-1">
                                            @foreach ($groupPermissions as $permission)
                                                <label class="inline-flex items-center gap-2 text-xs">
                                                    <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" class="rounded border-slate-300" @checked($role->permissions->contains('name', $permission->name)) />
                                                    <span>{{ $permission->name }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <button class="mt-3 rounded-md bg-[var(--nmis-primary)] px-3 py-2 text-xs font-semibold text-white">Save Role Permissions</button>
                        </form>
                    </details>
                @endforeach
            </div>
        </section>

        <section class="rounded-lg border border-slate-200 bg-white p-4">
            <h3 class="mb-3 text-sm font-semibold text-slate-700">Direct User Permission Overrides</h3>
            <div class="space-y-3">
                @foreach ($users as $user)
                    <details class="rounded-md border border-slate-200 p-3">
                        <summary class="cursor-pointer text-sm font-semibold text-[var(--nmis-secondary)]">
                            {{ $user->name }} ({{ $user->roles->pluck('name')->implode(', ') ?: 'No role' }})
                        </summary>
                        <form method="POST" action="{{ route('admin.users.permissions.update', $user) }}" class="mt-3">
                            @csrf
                            @method('PUT')
                            <div class="grid max-h-56 gap-2 overflow-y-auto rounded-md border border-slate-200 p-2 sm:grid-cols-3">
                                @foreach ($permissionsByGroup as $group => $groupPermissions)
                                    <div class="rounded-md border border-slate-100 p-2">
                                        <p class="mb-1 text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ $group }}</p>
                                        <div class="space-y-1">
                                            @foreach ($groupPermissions as $permission)
                                                <label class="inline-flex items-center gap-2 text-xs">
                                                    <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" class="rounded border-slate-300" @checked($user->permissions->contains('name', $permission->name)) />
                                                    <span>{{ $permission->name }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <button class="mt-3 rounded-md bg-[var(--nmis-secondary)] px-3 py-2 text-xs font-semibold text-white">Save User Permissions</button>
                        </form>
                    </details>
                @endforeach
            </div>
        </section>
    </div>
</x-app-layout>
