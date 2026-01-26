<?php

namespace App\Livewire;

use App\Models\Order;
use Livewire\Component;

class Orders extends Component
{
    public function render()
    {
        $orders = Order::query()
            ->with(['orderItems.product'])
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('livewire.orders', [
            'orders' => $orders,
        ]);
    }
}
