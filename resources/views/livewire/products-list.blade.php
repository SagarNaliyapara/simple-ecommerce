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

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @foreach ($products as $product)
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm transition-shadow hover:shadow-md">
                <div class="p-6">
                    <h3 class="mb-2 text-lg font-semibold text-gray-900">{{ $product->name }}</h3>

                    <div class="mb-4 space-y-2">
                        <p class="text-2xl font-bold text-gray-900">${{ number_format($product->price, 2) }}</p>

                        @if ($product->stockQuantity > 0)
                            <p class="text-sm text-gray-600">
                                Stock: {{ $product->stockQuantity }} available
                            </p>
                        @else
                            <p class="text-sm font-semibold text-red-600">Out of Stock</p>
                        @endif
                    </div>

                    <button
                        wire:click="addToCart({{ $product->id }})"
                        @disabled($product->stockQuantity < 1)
                        @class([
                            'w-full rounded-lg px-4 py-2 text-sm font-medium text-white transition-colors',
                            'bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2' => $product->stockQuantity > 0,
                            'cursor-not-allowed bg-gray-400' => $product->stockQuantity < 1,
                        ])
                        wire:loading.attr="disabled"
                        wire:target="addToCart({{ $product->id }})"
                    >
                        <span wire:loading.remove wire:target="addToCart({{ $product->id }})">
                            {{ $product->stockQuantity > 0 ? 'Add to Cart' : 'Unavailable' }}
                        </span>
                        <span wire:loading wire:target="addToCart({{ $product->id }})">
                            Adding...
                        </span>
                    </button>
                </div>
            </div>
        @endforeach
    </div>
</div>
