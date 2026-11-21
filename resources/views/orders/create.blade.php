@extends('layouts.app')

@section('title', 'New order')

@section('content')
<x-page-header title="New order" subtitle="Enter the PO number, notes and PO file once, then add item lines with item name and quantity." />

<form method="post" action="{{ route('orders.store') }}" enctype="multipart/form-data" id="orderForm">
    @csrf
    @if ($clients->isEmpty())
        <div class="alert alert-warning">Add at least one <a href="{{ route('clients.create') }}">client</a> before creating an order.</div>
    @endif
    <div class="card shadow-sm form-card mb-4">
        <div class="card-header">Order details</div>
        <div class="card-body">
            <label class="form-label" for="client_id">Client this PO belongs to</label>
            <select name="client_id" id="client_id" class="form-select @error('client_id') is-invalid @enderror" required>
                <option value="">Select client…</option>
                @foreach ($clients as $client)
                    <option value="{{ $client->id }}" @selected(old('client_id', $selectedClient) == $client->id)>{{ $client->name }}</option>
                @endforeach
            </select>
            <div class="row g-3 mt-0">
                <div class="col-12 col-md-6">
                    <label class="form-label" for="po_number">PO number</label>
                    <input type="text" name="po_number" id="po_number" value="{{ old('po_number') }}" class="form-control @error('po_number') is-invalid @enderror" required>
                    @error('po_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label" for="notes">Notes <span class="text-body-secondary fw-normal">(optional, applies to all lines)</span></label>
                    <textarea name="notes" id="notes" rows="1" class="form-control @error('notes') is-invalid @enderror" placeholder="Order notes">{{ old('notes') }}</textarea>
                    @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label class="form-label" for="po_file">PO file <span class="text-body-secondary fw-normal">(optional: PDF or image, max 12 MB, applies to all lines)</span></label>
                    <input type="file" name="po_file" id="po_file" class="form-control @error('po_file') is-invalid @enderror" accept="application/pdf,image/jpeg,image/png,image/webp">
                    @error('po_file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <img id="po_file_preview" class="mt-2 rounded border d-none" alt="Selected image preview" style="max-height:96px">
                </div>
            </div>
        </div>
    </div>
    <div class="card shadow-sm form-card">
        <div class="card-header">
            <span>Lines</span>
            <button type="button" class="btn btn-sm btn-outline-primary" id="addLine" aria-label="Add another order line"><i class="fa-solid fa-plus me-1"></i>Add line</button>
        </div>
        <div class="card-body">
            <div id="linesContainer"></div>
        </div>
        <div class="card-footer d-flex flex-column flex-sm-row gap-2">
            <button type="submit" class="btn btn-primary">Save order</button>
            <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
(function () {
    const container = document.getElementById('linesContainer');
    const addBtn = document.getElementById('addLine');
    const itemNames = @json($itemNames);

    new TomSelect('#client_id', {
        maxItems: 1,
        create: false,
        selectOnTab: true,
        placeholder: 'Search client…',
        render: {
            no_results: () => '<div class="no-results">No client found. Add it from the Clients page first.</div>',
        },
    });

    function setupItemSelect(row) {
        const select = row.querySelector('.item-select');
        new TomSelect(select, {
            options: itemNames.map((name) => ({ value: name, text: name })),
            create: true,
            maxItems: 1,
            persist: false,
            createOnBlur: true,
            placeholder: 'Search or add item…',
            render: {
                option_create: (data, escape) => '<div class="create">Add <strong>' + escape(data.input) + '</strong>&hellip;</div>',
                no_results: () => '<div class="no-results">No items found. Keep typing to add a new one.</div>',
            },
        });
    }


    function lineTemplate(index) {
        return `
        <div class="border rounded-3 bg-body-tertiary p-3 mb-3 line-row" data-index="${index}">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-7 col-xl-6">
                    <label class="form-label">Item name</label>
                    <select name="lines[${index}][item_name]" class="item-select" required aria-label="Item name"></select>
                </div>
                <div class="col-8 col-md-3 col-xl-2">
                    <label class="form-label">Quantity</label>
                    <input type="number" name="lines[${index}][quantity]" class="form-control" min="1" value="1" required>
                </div>
                <div class="col-4 col-md-2 col-xl-4 d-grid d-md-flex justify-content-md-end">
                    <button type="button" class="btn btn-outline-danger remove-line" title="Remove line" aria-label="Remove this order line">Remove</button>
                </div>
            </div>
        </div>`;
    }

    let nextIndex = 0;

    function reindex() {
        const rows = container.querySelectorAll('.line-row');
        rows.forEach((row, i) => {
            row.dataset.index = String(i);
            row.querySelectorAll('input, textarea, select').forEach((el) => {
                const name = el.getAttribute('name');
                if (!name) return;
                el.setAttribute('name', name.replace(/lines\[\d+]/, 'lines[' + i + ']'));
            });
        });
        nextIndex = rows.length;
    }

    function addLine() {
        container.insertAdjacentHTML('beforeend', lineTemplate(nextIndex));
        setupItemSelect(container.lastElementChild);
        nextIndex++;
        attachRemoveHandlers();
    }

    function attachRemoveHandlers() {
        container.querySelectorAll('.remove-line').forEach((btn) => {
            btn.onclick = () => {
                const row = btn.closest('.line-row');
                const sel = row.querySelector('.item-select');
                if (sel && sel.tomselect) sel.tomselect.destroy();
                row.remove();
                reindex();
            };
        });
    }

    document.getElementById('po_file').addEventListener('change', (e) => {
        const preview = document.getElementById('po_file_preview');
        const file = e.target.files[0];
        const isImage = file && file.type.startsWith('image/');
        if (preview.src.startsWith('blob:')) URL.revokeObjectURL(preview.src);
        if (isImage) {
            preview.src = URL.createObjectURL(file);
            preview.classList.remove('d-none');
        } else {
            preview.removeAttribute('src');
            preview.classList.add('d-none');
        }
    });

    addBtn.addEventListener('click', addLine);
    addLine();
})();
</script>
@endpush
