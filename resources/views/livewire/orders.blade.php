<div>
    @if (session()->has('success'))
        <div class="mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-800" role="alert">
            {{ session('success') }}
        </div>
    @endif

    @if ($orders->isEmpty())
        <div class="rounded-lg border border-gray-200 bg-white p-8 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900">No orders yet</h3>
            <p class="mt-2 text-sm text-gray-500">You haven't placed any orders yet.</p>
            <a href="{{ route('products') }}" class="mt-6 inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                Browse Products
            </a>
        </div>
    @else
        <div class="space-y-6">
            @foreach ($orders as $order)
                <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-200 bg-gray-50 p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Order #{{ $order->id }}</h3>
                                <p class="mt-1 text-sm text-gray-600">{{ $order->created_at->format('M d, Y H:i') }}</p>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold 
                                    @if($order->status === 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($order->status === 'completed') bg-green-100 text-green-800
                                    @else bg-red-100 text-red-800
                                    @endif">
                                    {{ ucfirst($order->status) }}
                                </span>
                                <p class="mt-2 text-lg font-bold text-gray-900">${{ number_format($order->total_amount, 2) }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-4">
                        <h4 class="mb-3 text-sm font-semibold text-gray-700">Order Items:</h4>
                        <div class="space-y-3">
                            @foreach ($order->orderItems as $item)
                                <div class="flex items-center justify-between rounded-lg bg-gray-50 p-3">
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-900">{{ $item->product->name }}</p>
                                        <p class="text-sm text-gray-600">Quantity: {{ $item->quantity }} × ${{ number_format($item->price, 2) }}</p>
                                    </div>
                                    <p class="font-semibold text-gray-900">${{ number_format($item->quantity * $item->price, 2) }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
