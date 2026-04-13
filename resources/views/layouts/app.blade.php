<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased" x-data="{ sidebarOpen: false }">
    <div class="min-h-screen bg-gray-50 flex">
        <!-- Sidebar Navigation -->
        @include('layouts.navigation')

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Top Navigation / Header -->
            <header class="bg-white border-b border-gray-200">
                <div class="px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center justify-between h-16 gap-4">
                        <div class="flex items-center gap-3 min-w-0">
                            <!-- Mobile/Sidebar toggle -->
                            <button @click="sidebarOpen = true"
                                class="text-gray-500 hover:text-gray-600 focus:outline-none lg:hidden">
                                <span class="sr-only">Open sidebar</span>
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </button>

                            <!-- Page header / title -->
                            @isset($header)
                                <div
                                    class="hidden sm:flex items-center text-base sm:text-lg font-semibold text-gray-800 truncate">
                                    {{ $header }}
                                </div>
                            @endisset
                        </div>

                        <!-- Right side items (User Dropdown) -->
                        <div class="flex items-center gap-3 sm:gap-4">
                            @isset($header)
                                <div class="sm:hidden text-sm font-semibold text-gray-800 truncate max-w-[140px]">
                                    {{ $header }}
                                </div>
                            @endisset

                            <x-dropdown align="right" width="48">
                                <x-slot name="trigger">
                                    <button
                                        class="flex items-center gap-2 text-sm font-medium text-gray-600 hover:text-gray-800 focus:outline-none">
                                        <span
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-indigo-50 text-indigo-700 font-bold uppercase">
                                            {{ Str::of(Auth::user()->name)->substr(0, 1) }}
                                        </span>
                                        <span
                                            class="hidden md:block truncate max-w-[140px]">{{ Auth::user()->name }}</span>
                                        <svg class="h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </x-slot>

                                <x-slot name="content">
                                    <x-dropdown-link :href="route('profile.edit')">
                                        {{ __('Profile') }}
                                    </x-dropdown-link>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <x-dropdown-link :href="route('logout')"
                                            onclick="event.preventDefault(); this.closest('form').submit();">
                                            {{ __('Log Out') }}
                                        </x-dropdown-link>
                                    </form>
                                </x-slot>
                            </x-dropdown>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 relative z-0 overflow-y-auto focus:outline-none">
                <div class="py-6">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
                        {{ $slot }}
                    </div>
                </div>
            </main>
        </div>
    </div>

    @stack('scripts')
</body>

</html>
