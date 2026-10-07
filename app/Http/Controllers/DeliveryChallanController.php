<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDeliveryChallanRequest;
use App\Models\Client;
use App\Models\DeliveryChallan;
use App\Models\Order;
use App\Services\DeliveryChallanService;
use App\Support\StockFilters;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class DeliveryChallanController extends Controller
{
    public function __construct(protected DeliveryChallanService $challans) {}

    public function index(Request $request): View
    {
        $filters = StockFilters::fromRequest($request);

        $challans = DeliveryChallan::query()
            ->with('client')
            ->withSum('lines as total_qty', 'quantity')
            ->when($filters->clientId, fn ($q) => $q->where('client_id', $filters->clientId))
            ->when($filters->from, fn ($q) => $q->whereDate('issued_on', '>=', $filters->from->toDateString()))
            ->when($filters->to, fn ($q) => $q->whereDate('issued_on', '<=', $filters->to->toDateString()))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $clients = Client::query()->orderBy('name')->get(['id', 'name']);

        return view('challans.index', compact('challans', 'clients', 'filters'));
    }

    public function create(): View
    {
        $clients = Client::query()->orderBy('name')->get();

        $availableLines = $this->challans->availableLines();

        $unassignedCount = Order::query()->unassigned()->count();

        return view('challans.create', compact('clients', 'availableLines', 'unassignedCount'));
    }

    public function store(StoreDeliveryChallanRequest $request): RedirectResponse
    {
        try {
            $this->challans->create($request->validated());
        } catch (RuntimeException $e) {
            return back()->withInput()->withErrors(['lines' => $e->getMessage()]);
        }

        return redirect()->route('challans.index')->with('status', 'Delivery challan created.');
    }

    public function show(DeliveryChallan $challan): View
    {
        $challan->load(['client', 'lines.orderItem.order']);

        return view('challans.show', compact('challan'));
    }
}
