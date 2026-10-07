@props([
    'action',
    'clients',
    'filters',
    'orders' => null,
    'po' => false,
    'status' => false,
    'unassigned' => false,
])
<form method="get" action="{{ $action }}" class="card shadow-sm mb-3 no-print" aria-label="Filters">
    <div class="card-body py-3">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-sm-6 col-lg-3">
                <label class="form-label small fw-medium" for="f_client">Client</label>
                <select name="client_id" id="f_client" class="form-select form-select-sm">
                    <option value="">All clients</option>
                    @if ($unassigned)
                        <option value="unassigned" @selected($filters->unassigned)>Unassigned</option>
                    @endif
                    @foreach ($clients as $c)
                        <option value="{{ $c->id }}" @selected($filters->clientId === $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            @if ($orders !== null)
                <div class="col-6 col-lg-2">
                    <label class="form-label small fw-medium" for="f_order">Order</label>
                    <select name="order_id" id="f_order" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach ($orders as $oid)
                            <option value="{{ $oid }}" @selected($filters->orderId === (int) $oid)>#{{ $oid }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            @if ($po)
                <div class="col-6 col-lg-2">
                    <label class="form-label small fw-medium" for="f_po">PO number</label>
                    <input type="text" name="po_number" id="f_po" class="form-control form-control-sm" value="{{ $filters->poNumber }}">
                </div>
            @endif
            <div class="col-6 col-sm-3 col-lg-2">
                <label class="form-label small fw-medium" for="f_from">From</label>
                <input type="date" name="from" id="f_from" class="form-control form-control-sm" value="{{ $filters->from?->toDateString() }}">
            </div>
            <div class="col-6 col-sm-3 col-lg-2">
                <label class="form-label small fw-medium" for="f_to">To</label>
                <input type="date" name="to" id="f_to" class="form-control form-control-sm" value="{{ $filters->to?->toDateString() }}">
            </div>
            @if ($status)
                <div class="col-6 col-lg-2">
                    <label class="form-label small fw-medium" for="f_status">Status</label>
                    <select name="status" id="f_status" class="form-select form-select-sm">
                        <option value="pending" @selected($filters->status === 'pending')>Pending</option>
                        <option value="completed" @selected($filters->status === 'completed')>Completed</option>
                        <option value="all" @selected($filters->status === 'all')>All</option>
                    </select>
                </div>
            @endif
            <div class="col d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary"><i class="fa-solid fa-filter me-1"></i>Apply</button>
                <a href="{{ $action }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </div>
    </div>
</form>
