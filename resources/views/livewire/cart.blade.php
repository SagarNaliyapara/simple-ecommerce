<div>
    @if (session()->has('success'))
        <div class="mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-800" role="alert">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 rounded-lg bg-red-50 p-4 text-sm text-red-800" role="alert">
            {{ session('error') }}
        </div>
    @endif

    @if ($cartItems->isEmpty())
        <div class="rounded-lg border border-gray-200 bg-white p-8 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900">Your cart is empty</h3>
            <p class="mt-2 text-sm text-gray-500">Start adding some products to your cart.</p>
            <a
                wire:navigate
                href="{{ route('products') }}"
                class="mt-6 inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
            >
                Browse Products
            </a>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($cartItems as $item)
                <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                    <div class="p-6">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900">{{ $item->productName }}</h3>
                                <p class="mt-1 text-sm text-gray-600">Price: ${{ number_format($item->productPrice, 2) }}</p>
                                <p class="mt-1 text-sm text-gray-600">Subtotal: ${{ number_format($item->quantity * $item->productPrice, 2) }}</p>
                            </div>

                            <button
                                wire:click="removeItem({{ $item->id }})"
                                wire:confirm="Are you sure you want to remove this item?"
                                class="text-red-600 hover:text-red-800"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>

                        <div class="mt-4 flex items-center space-x-4">
                            <span class="text-sm font-medium text-gray-700">Quantity:</span>
                            <div class="flex items-center space-x-2">
                                <button
                                    wire:click="updateQuantity({{ $item->id }}, 'decrement')"
                                    class="rounded-lg border border-gray-300 px-3 py-1 text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    wire:loading.attr="disabled"
                                >
                                    -
                                </button>

                                <span class="w-12 text-center text-lg font-semibold">{{ $item->quantity }}</span>

                                <button
                                    wire:click="updateQuantity({{ $item->id }}, 'increment')"
                                    class="rounded-lg border border-gray-300 px-3 py-1 text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    wire:loading.attr="disabled"
                                    @if($item->quantity >= $item->productStockQuantity) disabled @endif
                                >
                                    +
                                </button>
                            </div>

                            @if ($item->quantity >= $item->productStockQuantity)
                                <span class="text-sm text-red-600">Max quantity reached</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="rounded-lg border border-gray-200 bg-gray-50 p-6">
                <div class="flex items-center justify-between">
                    <span class="text-xl font-semibold text-gray-900">Total:</span>
                    <span class="text-2xl font-bold text-gray-900">${{ number_format($total, 2) }}</span>
                </div>

                <div class="mt-6">
                    <button
                        wire:click="proceedToOrder"
                        wire:confirm="Are you sure you want to place this order?"
                        class="w-full rounded-lg bg-green-600 px-6 py-3 text-base font-semibold text-white shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                        wire:loading.attr="disabled"
                    >
                        <span wire:loading.remove>Proceed to Order</span>
                        <span wire:loading>Processing...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
