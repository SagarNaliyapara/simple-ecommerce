<?php

namespace App\Livewire;

use App\Services\OrderService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Orders extends Component
{
    public function render(): View
    {
        $orders = app(OrderService::class)->getOrdersForUser(auth()->id());

        return view('livewire.orders', [
            'orders' => $orders,
        ]);
    }
}
