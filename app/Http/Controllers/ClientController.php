<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(): View
    {
        $clients = Client::query()->withCount(['orders', 'deliveryChallans'])->orderBy('name')->paginate(15);

        return view('clients.index', compact('clients'));
    }

    public function create(): View
    {
        return view('clients.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('clients', 'name')],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['required', 'string'],
        ]);

        Client::query()->create($data);

        return redirect()->route('clients.index')->with('status', 'Client saved.');
    }

    public function edit(Client $client): View
    {
        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('clients', 'name')->ignore($client->id)],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['required', 'string'],
        ]);

        $client->update($data);

        return redirect()->route('clients.index')->with('status', 'Client updated.');
    }

    public function destroy(Client $client): RedirectResponse
    {
        if ($client->orders()->exists() || $client->deliveryChallans()->exists()) {
            return redirect()->route('clients.index')
                ->withErrors(['client' => "Cannot delete {$client->name}: this client has orders or delivery challans."]);
        }

        $client->delete();

        return redirect()->route('clients.index')->with('status', 'Client removed.');
    }
}
