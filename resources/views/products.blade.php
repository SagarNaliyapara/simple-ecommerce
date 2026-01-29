@auth
    <x-app-layout>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <livewire:products-list />
            </div>
        </div>
    </x-app-layout>
@else
    <x-guest-layout>
        <x-slot name="title">Products</x-slot>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <livewire:products-list />
            </div>
        </div>
    </x-guest-layout>
@endauth
