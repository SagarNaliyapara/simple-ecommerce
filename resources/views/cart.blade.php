<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ __('Shopping Cart') }}
            </h2>
            <a href="{{ route('products') }}" class="text-sm text-blue-600 hover:text-blue-800">
                ← Continue Shopping
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            <livewire:cart />
        </div>
    </div>
</x-app-layout>
