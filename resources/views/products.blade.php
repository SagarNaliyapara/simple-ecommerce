@auth
    <x-app-layout>
        <x-slot name="header">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ __('Products') }}
            </h2>
        </x-slot>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <livewire:products-list />
            </div>
        </div>
    </x-app-layout>
@else
    <!DOCTYPE html>
    <html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <meta name="csrf-token" content="{{ csrf_token() }}">

            <title>{{ config('app.name', 'Laravel') }} - Products</title>

            <!-- Fonts -->
            <link rel="preconnect" href="https://fonts.bunny.net">
            <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

            <!-- Scripts -->
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        </head>
        <body class="font-sans antialiased">
            <div class="min-h-screen bg-gray-100">
                <!-- Guest Navigation -->
                <nav class="bg-white border-b border-gray-100">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="flex justify-between h-16">
                            <div class="flex items-center">
                                <!-- Logo -->
                                <div class="shrink-0 flex items-center">
                                    <a href="/" wire:navigate>
                                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                                    </a>
                                </div>
                                
                                <!-- Products Link -->
                                <div class="hidden sm:flex sm:items-center sm:ms-10">
                                    <a href="{{ route('products') }}" class="text-sm font-semibold text-gray-800">
                                        Products
                                    </a>
                                </div>
                            </div>

                            <!-- Login/Register Links -->
                            <div class="hidden sm:flex sm:items-center sm:gap-4">
                                <a href="{{ route('login') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900" wire:navigate>
                                    Log in
                                </a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="rounded-md px-3 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 transition" wire:navigate>
                                        Register
                                    </a>
                                @endif
                            </div>
                            
                            <!-- Mobile menu button -->
                            <div class="flex items-center sm:hidden">
                                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 transition" x-data="{ open: false }">
                                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </nav>

                <!-- Page Content -->
                <div class="py-12">
                    <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                        <div class="mb-6">
                            <h2 class="text-2xl font-bold text-gray-900">Browse Our Products</h2>
                            <p class="mt-1 text-sm text-gray-600">Sign in to add items to your cart</p>
                        </div>
                        <livewire:products-list />
                    </div>
                </div>
            </div>
        </body>
    </html>
@endauth
