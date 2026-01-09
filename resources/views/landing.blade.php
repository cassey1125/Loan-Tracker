<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Loan Tracker System</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Instrument Sans', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            900: '#1e3a8a',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="antialiased bg-gray-50 text-gray-800 font-sans">

    <!-- Navigation -->
    <nav class="bg-white/80 backdrop-blur-md sticky top-0 z-50 border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center">
                    <span class="text-2xl font-bold text-brand-900 tracking-tight">LoanTracker</span>
                </div>
                <div class="hidden md:flex space-x-8 items-center">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="text-gray-600 font-medium transition">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="text-gray-600 font-medium transition">Log in</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="bg-brand-600 text-white px-5 py-2.5 rounded-full font-medium transition shadow-lg shadow-brand-500/30">Get Started</a>
                            @endif
                        @endauth
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="relative overflow-hidden pt-16 pb-20 lg:pt-24 lg:pb-28">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="text-center max-w-3xl mx-auto">
                <h1 class="text-4xl tracking-tight font-extrabold text-gray-900 sm:text-5xl md:text-6xl mb-6">
                    <span class="block">Manage Loans with</span>
                    <span class="block text-brand-600">Confidence & Clarity</span>
                </h1>
                <p class="mt-3 max-w-md mx-auto text-base text-gray-500 sm:text-lg md:mt-5 md:text-xl md:max-w-3xl">
                    The Web-Based Loan Tracker System manages borrower records, loans, payments, and financial summaries in a clean, automated, and user-friendly interface.
                </p>
                <div class="mt-10 max-w-sm mx-auto sm:max-w-none sm:flex sm:justify-center gap-4">
                    <a href="{{ route('register') }}" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-brand-600 md:py-4 md:text-lg md:px-10 shadow-xl shadow-brand-500/20 transition-all">
                        Start Tracking Now
                    </a>
                    <a href="#features" class="w-full flex items-center justify-center px-8 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white md:py-4 md:text-lg md:px-10 transition-all">
                        Learn More
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Feature Showcase (Images) -->
    <div class="bg-white py-16 sm:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-base font-semibold text-brand-600 tracking-wide uppercase">Interface</h2>
                <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                    Modern Dashboard & Tracking
                </p>
            </div>

            <div class="space-y-24">
                <!-- Feature 1 -->
                <div class="lg:grid lg:grid-cols-2 lg:gap-16 items-center">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">Comprehensive Dashboard</h3>
                        <p class="text-lg text-gray-500 mb-6">
                            Get a complete overview of your lending business at a glance. Visual indicators for due payments, active loans, and total collections.
                        </p>
                        <ul class="space-y-3 text-gray-500">
                            <li class="flex items-center">
                                <svg class="h-6 w-6 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Real-time calculations
                            </li>
                            <li class="flex items-center">
                                <svg class="h-6 w-6 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Financial summaries
                            </li>
                        </ul>
                    </div>
                    <div class="mt-10 lg:mt-0 relative group">
                        <div class="absolute -inset-2 bg-gradient-to-r from-brand-500 to-purple-600 rounded-lg blur opacity-20 transition duration-1000"></div>
                        <img class="relative rounded-lg shadow-2xl border border-gray-200 w-full" src="{{ asset('images/image1.png') }}" alt="Dashboard Preview">
                    </div>
                </div>

                <!-- Feature 2 -->
                <div class="lg:grid lg:grid-cols-2 lg:gap-16 items-center lg:flex-row-reverse">
                    <div class="lg:order-2">
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">Borrower Management</h3>
                        <p class="text-lg text-gray-500 mb-6">
                            Keep detailed records of all your borrowers. Access complete history, contact details, and loan status instantly.
                        </p>
                        <ul class="space-y-3 text-gray-500">
                            <li class="flex items-center">
                                <svg class="h-6 w-6 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Complete borrower history
                            </li>
                            <li class="flex items-center">
                                <svg class="h-6 w-6 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Easy record updates
                            </li>
                        </ul>
                    </div>
                    <div class="mt-10 lg:mt-0 lg:order-1 relative group">
                        <div class="absolute -inset-2 bg-gradient-to-r from-brand-500 to-teal-500 rounded-lg blur opacity-20 transition duration-1000"></div>
                        <img class="relative rounded-lg shadow-2xl border border-gray-200 w-full" src="{{ asset('images/image2.png') }}" alt="Borrower Management">
                    </div>
                </div>

                <!-- Feature 3 -->
                <div class="lg:grid lg:grid-cols-2 lg:gap-16 items-center">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">Automated Calculations</h3>
                        <p class="text-lg text-gray-500 mb-6">
                            No more manual math. The system automatically calculates interest, penalties, and remaining balances for you.
                        </p>
                        <ul class="space-y-3 text-gray-500">
                            <li class="flex items-center">
                                <svg class="h-6 w-6 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Accurate income tracking
                            </li>
                            <li class="flex items-center">
                                <svg class="h-6 w-6 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Visual indicators for due payments
                            </li>
                        </ul>
                    </div>
                    <div class="mt-10 lg:mt-0 relative group">
                        <img class="relative rounded-lg shadow-2xl border border-gray-200 w-full" src="{{ asset('images/image3.png') }}" alt="Payment Tracking">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Grid -->
    <div id="features" class="bg-gray-50 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white p-8 rounded-2xl shadow-sm transition">
                    <div class="w-12 h-12 bg-brand-100 rounded-xl flex items-center justify-center text-brand-600 mb-6">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Clean Interface</h3>
                    <p class="text-gray-500">User-friendly design that makes navigating through loans and records intuitive and fast.</p>
                </div>
                <div class="bg-white p-8 rounded-2xl shadow-sm transition">
                    <div class="w-12 h-12 bg-brand-100 rounded-xl flex items-center justify-center text-brand-600 mb-6">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Financial Summaries</h3>
                    <p class="text-gray-500">Instant access to your financial health with automated summaries and income tracking.</p>
                </div>
                <div class="bg-white p-8 rounded-2xl shadow-sm transition">
                    <div class="w-12 h-12 bg-brand-100 rounded-xl flex items-center justify-center text-brand-600 mb-6">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Real-time Tracking</h3>
                    <p class="text-gray-500">Never miss a payment with visual indicators and real-time status updates.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-100 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center">
            <div class="mb-4 md:mb-0">
                <span class="text-xl font-bold text-gray-900">LoanTracker</span>
                <p class="text-sm text-gray-500 mt-1">© {{ date('Y') }} All rights reserved.</p>
            </div>
            <div class="flex space-x-6">
                <a href="#" class="text-gray-400">Terms of Service</a>
                <a href="#" class="text-gray-400">Contact: 0722334455</a>
            </div>
        </div>
    </footer>

</body>
</html>
