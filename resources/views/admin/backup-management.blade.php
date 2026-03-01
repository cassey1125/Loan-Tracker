<x-layouts.app :title="__('Backup Management')">
    <div class="p-6 space-y-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h1 class="text-2xl font-semibold text-gray-900">Backup Management</h1>
            <p class="text-sm text-gray-600 mt-1">Create, verify, restore, and delete backup files.</p>
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

        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex flex-wrap gap-3">
                <form method="POST" action="{{ route('admin.backups.run') }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700">
                        Backup Now
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.backups.verify') }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded border border-gray-300 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        Verify Latest Backup
                    </button>
                </form>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Filename</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Size</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Modified</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($files as $file)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $file['name'] }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ number_format($file['size']) }} bytes</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $file['modified_at'] }}</td>
                                <td class="px-4 py-3 text-sm">
                                    @if(auth()->user()?->canRestoreOrDeleteBackups())
                                        <form method="POST" action="{{ route('admin.backups.restore') }}" class="inline-block backup-restore-form" data-file-name="{{ $file['name'] }}">
                                            @csrf
                                            <input type="hidden" name="file" value="{{ $file['name'] }}">
                                            <input type="hidden" name="confirm" value="">
                                            <button type="submit" class="px-3 py-1.5 rounded bg-amber-500 text-white text-xs font-semibold hover:bg-amber-600">
                                                Restore
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.backups.delete') }}" class="inline-block backup-delete-form ml-2" data-file-name="{{ $file['name'] }}">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="file" value="{{ $file['name'] }}">
                                            <button type="submit" class="px-3 py-1.5 rounded bg-red-600 text-white text-xs font-semibold hover:bg-red-700">
                                                Delete
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-gray-500">Owner only</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-4 text-center text-sm text-gray-500">No backup files found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.backup-delete-form').forEach((form) => {
            form.addEventListener('submit', (event) => {
                const fileName = form.dataset.fileName || 'this backup';
                if (!window.confirm(`Delete backup ${fileName}? This cannot be undone.`)) {
                    event.preventDefault();
                }
            });
        });

        document.querySelectorAll('.backup-restore-form').forEach((form) => {
            form.addEventListener('submit', (event) => {
                const fileName = form.dataset.fileName || 'this backup';
                const response = window.prompt(`Type RESTORE to confirm restoring ${fileName}:`, '');
                if (response !== 'RESTORE') {
                    event.preventDefault();
                    return;
                }

                const confirmInput = form.querySelector('input[name="confirm"]');
                if (confirmInput) {
                    confirmInput.value = response;
                }
            });
        });
    </script>
</x-layouts.app>
