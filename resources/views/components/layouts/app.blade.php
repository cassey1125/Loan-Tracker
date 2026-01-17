<x-layouts.app.sidebar :title="$title ?? null">
    <flux:main class="w-full flex-1">
        {{ $slot }}
    </flux:main>
</x-layouts.app.sidebar>
