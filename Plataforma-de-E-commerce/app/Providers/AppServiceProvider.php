<?php

namespace App\Providers;

use App\Models\Category;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(60)->by($request->user()?->id ?: $request->ip()));
        Password::defaults(fn () => app()->isProduction()
            ? Password::min(12)->letters()->mixedCase()->numbers()->uncompromised()
            : Password::min(8));

        // Categorias do menu da loja: só as que têm produtos à venda.
        View::composer('layouts.shop', function ($view) {
            $view->with('menuCategories', Category::query()
                ->whereHas('products', fn ($query) => $query->where('is_active', true))
                ->orderBy('name')
                ->get(['id', 'name', 'slug']));
        });
    }
}
