<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800 lg:flex">
    <flux:sidebar sticky collapsible="mobile"
        class="app-sidebar border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.header>
            <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate class="app-sidebar-brand" />
            <flux:sidebar.collapse class="lg:hidden" />
        </flux:sidebar.header>

        <flux:sidebar.nav class="app-sidebar-nav">
            <flux:sidebar.group :heading="__('Platform')" class="app-sidebar-group grid">
                <flux:sidebar.item :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate class="app-sidebar-item">
                    <x-slot:icon>
                        <span class="material-icons app-sidebar-icon">dashboard</span>
                    </x-slot:icon>
                    {{ __('Dashboard') }}
                </flux:sidebar.item>
                <flux:sidebar.item :href="route('borrowers.index')" :current="request()->routeIs('borrowers.*')" wire:navigate class="app-sidebar-item">
                    <x-slot:icon>
                        <span class="material-icons app-sidebar-icon">group</span>
                    </x-slot:icon>
                    {{ __('Borrowers') }}
                </flux:sidebar.item>
                <flux:sidebar.item :href="route('loans')" :current="request()->routeIs('loans')" wire:navigate class="app-sidebar-item">
                    <x-slot:icon>
                        <span class="material-icons app-sidebar-icon">account_balance</span>
                    </x-slot:icon>
                    {{ __('Loans') }}
                </flux:sidebar.item>
                <flux:sidebar.item :href="route('payments')" :current="request()->routeIs('payments')" wire:navigate class="app-sidebar-item">
                    <x-slot:icon>
                        <span class="material-icons app-sidebar-icon">payment</span>
                    </x-slot:icon>
                    {{ __('Payments') }}
                </flux:sidebar.item>
                <flux:sidebar.item :href="route('funds')" :current="request()->routeIs('funds')" wire:navigate class="app-sidebar-item">
                    <x-slot:icon>
                        <span class="material-icons app-sidebar-icon">account_balance_wallet</span>
                    </x-slot:icon>
                    {{ __('Funds') }}
                </flux:sidebar.item>
                <flux:sidebar.item :href="route('paid_loans')" :current="request()->routeIs('paid_loans')"
                    wire:navigate class="app-sidebar-item">
                    <x-slot:icon>
                        <span class="material-icons app-sidebar-icon">history</span>
                    </x-slot:icon>
                    {{ __('Paid Loans') }}
                </flux:sidebar.item>
                <flux:sidebar.item :href="route('investor-profit')" :current="request()->routeIs('investor-profit')"
                    wire:navigate class="app-sidebar-item">
                    <x-slot:icon>
                        <span class="material-icons app-sidebar-icon">trending_up</span>
                    </x-slot:icon>
                    {{ __('Investor Returns') }}
                </flux:sidebar.item>
                <flux:sidebar.item :href="route('income_expense')" :current="request()->routeIs('income_expense')"
                    wire:navigate class="app-sidebar-item">
                    <x-slot:icon>
                        <span class="material-icons app-sidebar-icon">bar_chart</span>
                    </x-slot:icon>
                    {{ __('Income & Expenses') }}
                </flux:sidebar.item>
                <flux:sidebar.item :href="route('reports')" :current="request()->routeIs('reports')" wire:navigate class="app-sidebar-item">
                    <x-slot:icon>
                        <span class="material-icons app-sidebar-icon">assessment</span>
                    </x-slot:icon>
                    {{ __('Reports') }}
                </flux:sidebar.item>
                <flux:sidebar.item :href="route('motor_rentals')" :current="request()->routeIs('motor_rentals')"
                    wire:navigate class="app-sidebar-item">
                    <x-slot:icon>
                        <span class="material-icons app-sidebar-icon">two_wheeler</span>
                    </x-slot:icon>
                    {{ __('Motor Rentals') }}
                </flux:sidebar.item>
            </flux:sidebar.group>
        </flux:sidebar.nav>

        <flux:spacer />



        <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
    </flux:sidebar>

    <!-- Mobile User Menu -->
    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <flux:spacer />

        <flux:dropdown position="top" align="end">
            <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />

            <flux:menu>
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <flux:avatar :name="auth()->user()->name" :initials="auth()->user()->initials()" />

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                        {{ __('Settings') }}
                    </flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle"
                        class="w-full cursor-pointer" data-test="logout-button">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    {{ $slot }}

    @fluxScripts
</body>

</html>
