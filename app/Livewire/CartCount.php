<?php

namespace App\Livewire;

use App\Models\CartItem;
use Livewire\Attributes\On;
use Livewire\Component;

class CartCount extends Component
{
    public $count = 0;

    public function mount(): void
    {
        $this->updateCount();
    }

    #[On('cart-updated')]
    public function updateCount(): void
    {
        $this->count = CartItem::query()
            ->where('user_id', auth()->id())
            ->count();
    }

    public function render()
    {
        return view('livewire.cart-count');
    }
}
