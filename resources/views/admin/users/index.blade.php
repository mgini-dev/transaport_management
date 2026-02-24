<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">User Management</h2>
        <p class="mt-1 text-sm text-slate-500">Create users, assign roles, and manage active/inactive status.</p>
    </x-slot>

    <div class="space-y-6">
        <form method="POST" action="{{ route('admin.users.store') }}" class="grid gap-3 rounded-lg border border-slate-200 bg-white p-4 sm:grid-cols-2">
            @csrf
            <input name="name" class="rounded-md border-slate-300" placeholder="Full name" required />
            <input name="email" type="email" class="rounded-md border-slate-300" placeholder="Email" required />
            <input name="password" type="password" class="rounded-md border-slate-300" placeholder="Password" required />
            <input name="password_confirmation" type="password" class="rounded-md border-slate-300" placeholder="Confirm password" required />
            <label class="sm:col-span-2 text-sm font-medium text-slate-700">Roles</label>
            <div class="sm:col-span-2 grid gap-2 sm:grid-cols-3">
                @foreach ($roles as $role)
                    <label class="inline-flex items-center gap-2 rounded-md border border-slate-200 px-2 py-1 text-sm">
                        <input type="checkbox" name="roles[]" value="{{ $role->name }}" class="rounded border-slate-300 text-[var(--nmis-primary)]" />
                        <span>{{ $role->name }}</span>
                    </label>
                @endforeach
            </div>
            <button class="rounded-md bg-[var(--nmis-primary)] px-4 py-2 text-sm font-medium text-white sm:col-span-2">Create user</button>
        </form>

        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-3 py-2 text-left">Name</th>
                        <th class="px-3 py-2 text-left">Email</th>
                        <th class="px-3 py-2 text-left">Roles</th>
                        <th class="px-3 py-2 text-left">Status</th>
                        <th class="px-3 py-2 text-left">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($users as $user)
                        <tr x-data="{ openEdit: false }">
                            <td class="px-3 py-2">{{ $user->name }}</td>
                            <td class="px-3 py-2">{{ $user->email }}</td>
                            <td class="px-3 py-2">{{ $user->roles->pluck('name')->implode(', ') ?: '-' }}</td>
                            <td class="px-3 py-2">
                                <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $user->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-3 py-2">
                                <button type="button" class="rounded-md bg-slate-100 px-2 py-1 text-xs text-slate-700" @click="openEdit = !openEdit">Edit</button>
                            </td>
                            <td class="hidden">
                                <div x-show="openEdit"></div>
                            </td>
                        </tr>
                        <tr x-data="{ openEdit: false }" x-show="true" class="bg-slate-50/50">
                            <td colspan="5" class="px-3 py-3">
                                <details>
                                    <summary class="cursor-pointer text-xs font-semibold text-[var(--nmis-primary)]">Edit {{ $user->name }}</summary>
                                    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="mt-3 grid gap-2 sm:grid-cols-2">
                                        @csrf
                                        @method('PUT')
                                        <input name="name" class="rounded-md border-slate-300" value="{{ $user->name }}" required />
                                        <input name="email" type="email" class="rounded-md border-slate-300" value="{{ $user->email }}" required />
                                        <select name="is_active" class="rounded-md border-slate-300">
                                            <option value="1" @selected($user->is_active)>Active</option>
                                            <option value="0" @selected(! $user->is_active)>Inactive</option>
                                        </select>
                                        <div class="sm:col-span-2 grid gap-2 sm:grid-cols-3">
                                            @foreach ($roles as $role)
                                                <label class="inline-flex items-center gap-2 rounded-md border border-slate-200 px-2 py-1 text-xs">
                                                    <input type="checkbox" name="roles[]" value="{{ $role->name }}" class="rounded border-slate-300 text-[var(--nmis-primary)]" @checked($user->roles->contains('name', $role->name)) />
                                                    <span>{{ $role->name }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                        <button class="rounded-md bg-[var(--nmis-secondary)] px-3 py-2 text-xs font-semibold text-white sm:col-span-2">Save changes</button>
                                    </form>
                                </details>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $users->links() }}
    </div>
</x-app-layout>
