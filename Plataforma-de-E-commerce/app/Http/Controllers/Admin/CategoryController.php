<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use App\Models\User;
use App\Notifications\NewCollection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()
            ->withCount('products')
            ->orderBy('name')
            ->paginate(10);

        return view('admin.categories.index', ['categories' => $categories]);
    }

    public function create(): View
    {
        return view('admin.categories.form', ['category' => new Category]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        Category::create($request->validated());

        return redirect()->route('admin.categories.index')->with('success', __('Categoria criada.'));
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.form', ['category' => $category]);
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());

        return redirect()->route('admin.categories.index')->with('success', __('Categoria atualizada.'));
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->exists()) {
            return redirect()->route('admin.categories.index')
                ->with('error', __('Não é possível eliminar uma categoria que tem produtos.'));
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', __('Categoria eliminada.'));
    }

    public function announce(Category $category): RedirectResponse
    {
        $announced = DB::transaction(function () use ($category): bool {
            $category = Category::whereKey($category->id)->lockForUpdate()->firstOrFail();

            if ($category->collection_announced_at || ! $category->products()->active()->exists()) {
                return false;
            }

            User::where('role', UserRole::Customer)->chunkById(100, function ($customers) use ($category) {
                Notification::send($customers, new NewCollection($category));
            });

            $category->collection_announced_at = now();
            $category->save();

            return true;
        });

        return redirect()->route('admin.categories.index')->with($announced ? 'success' : 'error',
            $announced ? 'Coleção anunciada nas notificações dos clientes e emails agendados para envio.' : 'Esta coleção já foi anunciada ou ainda não tem produtos ativos.');
    }
}
