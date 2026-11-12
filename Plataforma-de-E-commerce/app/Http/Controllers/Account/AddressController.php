<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddressRequest;
use App\Models\Address;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AddressController extends Controller
{
    public function index(Request $request): View
    {
        $addresses = $request->user()->addresses()->orderByDesc('is_default')->latest()->get();

        return view('account.addresses.index', ['addresses' => $addresses]);
    }

    public function create(): View
    {
        return view('account.addresses.form', ['address' => new Address]);
    }

    public function store(AddressRequest $request): RedirectResponse
    {
        $address = $request->user()->addresses()->create($request->validated());

        // A primeira morada passa a ser a principal.
        if ($address->is_default || $request->user()->addresses()->count() === 1) {
            $address->makeDefault();
        }

        return redirect()->route('account.addresses.index')->with('status', __('Morada adicionada.'));
    }

    public function edit(Address $address): View
    {
        Gate::authorize('update', $address);

        return view('account.addresses.form', ['address' => $address]);
    }

    public function update(AddressRequest $request, Address $address): RedirectResponse
    {
        Gate::authorize('update', $address);

        $address->update($request->validated());

        if ($address->is_default) {
            $address->makeDefault();
        }

        return redirect()->route('account.addresses.index')->with('status', __('Morada atualizada.'));
    }

    public function destroy(Request $request, Address $address): RedirectResponse
    {
        Gate::authorize('delete', $address);

        $address->delete();

        // Se apagou a principal, a mais recente que sobrar passa a ser a principal.
        if ($address->is_default) {
            $request->user()->addresses()->latest()->first()?->makeDefault();
        }

        return redirect()->route('account.addresses.index')->with('status', __('Morada eliminada.'));
    }
}
