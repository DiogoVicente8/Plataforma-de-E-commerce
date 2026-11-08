<?php

use App\Http\Controllers\Account\OrderController as AccountOrderController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ProductImageController as AdminProductImageController;
use App\Http\Controllers\Admin\ProductVariantController as AdminProductVariantController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShopController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/loja', [ShopController::class, 'index'])->name('shop.index');
Route::get('/loja/{category:slug}', [ShopController::class, 'category'])->name('shop.category');
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::view('/', 'admin.dashboard')->name('dashboard');
        Route::resource('orders', AdminOrderController::class)->only(['index', 'show', 'update', 'destroy']);
        Route::patch('orders/{order}/refund', [AdminOrderController::class, 'refund'])->name('orders.refund');
        Route::resource('categories', AdminCategoryController::class)->except(['show']);
        Route::resource('products', AdminProductController::class)->except(['show']);
        Route::resource('products.variants', AdminProductVariantController::class)->only(['store', 'update', 'destroy'])->shallow();
        Route::resource('products.images', AdminProductImageController::class)->only(['store', 'destroy'])->shallow();
    });

Route::middleware(['auth', 'verified'])->prefix('conta')->name('account.')->group(function () {
    Route::get('/encomendas', [AccountOrderController::class, 'index'])->name('orders.index');
    Route::get('/encomendas/{order}', [AccountOrderController::class, 'show'])->name('orders.show');
});
require __DIR__.'/auth.php';
