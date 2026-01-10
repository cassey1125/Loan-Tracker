<x-layouts.app :title="__('Borrower Profile')">
    <div class="p-6">
        <livewire:borrowers.borrower-show :borrower="$borrower" />
    </div>
</x-layouts.app>
