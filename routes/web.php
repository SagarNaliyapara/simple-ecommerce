<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('products');
    }

    return view('welcome');
});

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::view('products', 'products')
    ->name('products');

Route::view('cart', 'cart')
    ->middleware(['auth'])
    ->name('cart');

Route::view('orders', 'orders')
    ->middleware(['auth'])
    ->name('orders');

require __DIR__.'/auth.php';
