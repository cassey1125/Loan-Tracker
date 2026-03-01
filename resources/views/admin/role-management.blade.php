<x-layouts.app :title="__('Role Management')">
    <div class="p-6 space-y-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h1 class="text-2xl font-semibold text-gray-900">Role Management</h1>
            <p class="text-sm text-gray-600 mt-1">Manage access levels for owner, admin, and staff users.</p>
        </div>

        @if (session('message'))
            <div class="bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded">
                {{ session('message') }}
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Current Role</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Change Role</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach($users as $managedUser)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $managedUser->name }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $managedUser->email }}</td>
                                <td class="px-4 py-3 text-sm font-semibold text-indigo-700">{{ strtoupper($managedUser->role->value) }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <form method="POST" action="{{ route('admin.roles.update', $managedUser) }}" class="flex items-center gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <select name="role" class="rounded border-gray-300 text-sm">
                                            @foreach($roles as $role)
                                                <option value="{{ $role->value }}" @selected($managedUser->role === $role)>
                                                    {{ ucfirst($role->value) }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="px-3 py-1.5 rounded bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700">
                                            Save
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
