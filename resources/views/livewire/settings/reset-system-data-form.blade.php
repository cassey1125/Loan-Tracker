<section class="mt-10 space-y-6">
    <div class="relative mb-5">
        <flux:heading>{{ __('Reset system data') }}</flux:heading>
        <flux:subheading>
            {{ __('Owner only. This clears all entered operational records and keeps user accounts. A backup from the last 24 hours is required.') }}
        </flux:subheading>
    </div>

    @if ($statusMessage)
        <flux:text class="font-medium !text-green-600 !dark:text-green-400">
            {{ $statusMessage }}
        </flux:text>
    @endif

    @if ($errorMessage)
        <flux:text class="font-medium !text-red-600 !dark:text-red-400">
            {{ $errorMessage }}
        </flux:text>
    @endif

    <flux:button variant="danger" wire:click="openResetModal">
        {{ __('Reset all data') }}
    </flux:button>

    <flux:modal name="confirm-system-reset" wire:model="showResetModal" focusable class="max-w-lg">
        <form method="POST" wire:submit="resetSystemData" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Reset all system data?') }}</flux:heading>

                <flux:subheading>
                    {{ __('This action is permanent and will delete all borrowers, loans, payments, funds, rentals, transactions, and related records.') }}
                    <br>
                    {{ __('Type RESET EVERYTHING and enter your password to continue.') }}
                </flux:subheading>
            </div>

            <flux:input
                wire:model="confirmation"
                :label="__('Confirmation phrase')"
                type="text"
                placeholder="RESET EVERYTHING"
            />
            @error('confirmation')
                <flux:text class="font-medium !text-red-600 !dark:text-red-400">
                    {{ $message }}
                </flux:text>
            @enderror

            <flux:input wire:model="password" :label="__('Password')" type="password" />
            @error('password')
                <flux:text class="font-medium !text-red-600 !dark:text-red-400">
                    {{ $message }}
                </flux:text>
            @enderror

            @error('backup')
                <flux:text class="font-medium !text-red-600 !dark:text-red-400">
                    {{ $message }}
                </flux:text>
            @enderror

            @error('general')
                <flux:text class="font-medium !text-red-600 !dark:text-red-400">
                    {{ $message }}
                </flux:text>
            @enderror

            @if (session('system-reset-status') === 'done')
                <flux:text class="font-medium !text-green-600 !dark:text-green-400">
                    {{ __('System data reset completed.') }}
                </flux:text>
            @endif

            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                <flux:modal.close>
                    <flux:button variant="filled">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>

                <flux:button variant="danger" type="submit" wire:loading.attr="disabled" wire:target="resetSystemData">
                    <span wire:loading.remove wire:target="resetSystemData">{{ __('Reset now') }}</span>
                    <span wire:loading wire:target="resetSystemData">{{ __('Resetting…') }}</span>
                </flux:button>
            </div>
        </form>
    </flux:modal>
</section>
