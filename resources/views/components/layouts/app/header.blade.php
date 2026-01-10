<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:header container class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden mr-2" icon="bars-2" inset="left" />

            <x-app-logo href="{{ route('dashboard') }}" wire:navigate />

            <flux:navbar class="-mb-px max-lg:hidden">
                <flux:navbar.item :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                    <span class="material-icons mr-2">dashboard</span>
                    {{ __('Dashboard') }}
                </flux:navbar.item>
                <flux:navbar.item :href="route('borrowers.index')" :current="request()->routeIs('borrowers.*')" wire:navigate>
                    <span class="material-icons mr-2">group</span>
                    {{ __('Borrower List') }}
                </flux:navbar.item>
                <flux:navbar.item :href="route('loans')" :current="request()->routeIs('loans')" wire:navigate>
                    <span class="material-icons mr-2">account_balance</span>
                    {{ __('Loan Management') }}
                </flux:navbar.item>
                <flux:navbar.item :href="route('payments')" :current="request()->routeIs('payments')" wire:navigate>
                    <span class="material-icons mr-2">payment</span>
                    {{ __('Payment Management') }}
                </flux:navbar.item>
                <flux:navbar.item :href="route('paid_loans')" :current="request()->routeIs('paid_loans')" wire:navigate>
                    <span class="material-icons mr-2">history</span>
                    {{ __('Paid / Completed Loans History') }}
                </flux:navbar.item>
                <flux:navbar.item :href="route('investor-profit')" :current="request()->routeIs('investor-profit')" wire:navigate>
                    <span class="material-icons mr-2">trending_up</span>
                    {{ __('Investor Profit') }}
                </flux:navbar.item>
                <flux:navbar.item :href="route('income_expense')" :current="request()->routeIs('income_expense')" wire:navigate>
                    <span class="material-icons mr-2">bar_chart</span>
                    {{ __('Income & Expense Tracking') }}
                </flux:navbar.item>
                <flux:navbar.item :href="route('reports')" :current="request()->routeIs('reports')" wire:navigate>
                    <span class="material-icons mr-2">assessment</span>
                    {{ __('Reports & Search Filters') }}
                </flux:navbar.item>
            </flux:navbar>

            <flux:spacer />

            <flux:navbar class="me-1.5 space-x-0.5 rtl:space-x-reverse py-0!">
                <flux:tooltip :content="__('Search')" position="bottom">
                    <flux:navbar.item class="!h-10 [&>div>svg]:size-5" icon="magnifying-glass" href="#" :label="__('Search')" />
                </flux:tooltip>
                <flux:tooltip :content="__('Repository')" position="bottom">
                    <flux:navbar.item
                        class="h-10 max-lg:hidden [&>div>svg]:size-5"
                        icon="folder-git-2"
                        href="https://github.com/laravel/livewire-starter-kit"
                        target="_blank"
                        :label="__('Repository')"
                    />
                </flux:tooltip>
                <flux:tooltip :content="__('Documentation')" position="bottom">
                    <flux:navbar.item
                        class="h-10 max-lg:hidden [&>div>svg]:size-5"
                        icon="book-open-text"
                        href="https://laravel.com/docs/starter-kits#livewire"
                        target="_blank"
                        label="Documentation"
                    />
                </flux:tooltip>
            </flux:navbar>

            <x-desktop-user-menu />
        </flux:header>

        <!-- Mobile Menu -->
        <flux:sidebar collapsible="mobile" sticky class="lg:hidden border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Platform')">
                    <flux:sidebar.item :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        <span class="material-icons mr-2">dashboard</span>
                        {{ __('Dashboard')  }}
                    </flux:sidebar.item>
                    <flux:sidebar.item :href="route('borrowers')" :current="request()->routeIs('borrowers')" wire:navigate>
                        <span class="material-icons mr-2">group</span>
                        {{ __('Borrower List') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item :href="route('loans')" :current="request()->routeIs('loans')" wire:navigate>
                        <span class="material-icons mr-2">account_balance</span>
                        {{ __('Loan Management') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item :href="route('payments')" :current="request()->routeIs('payments')" wire:navigate>
                        <span class="material-icons mr-2">payment</span>
                        {{ __('Payment Management') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item :href="route('paid_loans')" :current="request()->routeIs('paid_loans')" wire:navigate>
                        <span class="material-icons mr-2">history</span>
                        {{ __('Paid / Completed Loans History') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item :href="route('investor-profit')" :current="request()->routeIs('investor-profit')" wire:navigate>
                        <span class="material-icons mr-2">trending_up</span>
                        {{ __('Investor Profit') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item :href="route('income_expense')" :current="request()->routeIs('income_expense')" wire:navigate>
                        <span class="material-icons mr-2">bar_chart</span>
                        {{ __('Income & Expense Tracking') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item :href="route('reports')" :current="request()->routeIs('reports')" wire:navigate>
                        <span class="material-icons mr-2">assessment</span>
                        {{ __('Reports & Search Filters') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:spacer />


        </flux:sidebar>

        {{ $slot }}

        @fluxScripts
    </body>
</html>
