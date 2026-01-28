<?php

namespace App\Services;

use App\DTOs\CartItemDTO;
use App\Models\CartItem;
use Illuminate\Support\Collection;

class CartService
{
    /** @return Collection<int, CartItemDTO> */
    public function getItems(int $userId): Collection
    {
        return CartItem::query()
            ->with('product')
            ->where('user_id', $userId)
            ->get()
            ->map(fn ($item) => new CartItemDTO(
                id: $item->id,
                quantity: $item->quantity,
                productName: $item->product->name,
                productPrice: (float) $item->product->price,
                productStockQuantity: $item->product->stock_quantity,
            ));
    }

    public function getItemsWithLock(int $userId): Collection
    {
        return CartItem::query()
            ->where('user_id', $userId)
            ->with('product:id,name,price,stock_quantity')
            ->lockForUpdate()
            ->get();
    }

    public function countItems(int $userId): int
    {
        return CartItem::query()
            ->where('user_id', $userId)
            ->count();
    }

    public function findItemOrFail(int $cartItemId, int $userId): CartItem
    {
        return CartItem::query()
            ->where('id', $cartItemId)
            ->where('user_id', $userId)
            ->firstOrFail();
    }

    public function findItemByProduct(int $userId, int $productId): ?CartItem
    {
        return CartItem::query()
            ->where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();
    }

    public function incrementQuantity(CartItem $cartItem): void
    {
        $cartItem->increment('quantity');
    }

    public function decrementQuantity(CartItem $cartItem): void
    {
        $cartItem->decrement('quantity');
    }

    public function deleteItem(CartItem $cartItem): void
    {
        $cartItem->delete();
    }

    public function removeItem(int $cartItemId, int $userId): void
    {
        CartItem::query()
            ->where('id', $cartItemId)
            ->where('user_id', $userId)
            ->delete();
    }

    public function clearCart(int $userId): void
    {
        CartItem::query()->where('user_id', $userId)->delete();
    }

    public function createItem(int $userId, int $productId, int $quantity = 1): CartItem
    {
        return CartItem::query()->create([
            'user_id' => $userId,
            'product_id' => $productId,
            'quantity' => $quantity,
        ]);
    }
}
