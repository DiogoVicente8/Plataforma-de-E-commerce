<?php

namespace App\Providers;

use App\Models\Category;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Categorias do menu da loja: só as que têm produtos à venda.
        View::composer('layouts.shop', function ($view) {
            $view->with('menuCategories', Category::query()
                ->whereHas('products', fn ($query) => $query->where('is_active', true))
                ->orderBy('name')
                ->get(['id', 'name', 'slug']));
        });
    }
}