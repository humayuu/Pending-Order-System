@extends('layouts.app')

@section('title', 'New delivery challan')

@section('content')
<x-page-header title="New delivery challan" subtitle="Choose the client, then add lines. Only pending quantity can be dispatched." />

@if ($unassignedCount > 0)
    <div class="alert alert-info">{{ $unassignedCount }} order(s) have no client and are hidden here. <a href="{{ route('orders.index', ['client_id' => 'unassigned']) }}">Assign a client</a> to dispatch them.</div>
@endif

@if ($clients->isEmpty())
    <div class="alert alert-warning">Add at least one <a href="{{ route('clients.create') }}">client</a> before creating a challan.</div>
@endif

<form method="post" action="{{ route('challans.store') }}" id="challanForm">
    @csrf
    <div class="card shadow-sm form-card mb-4">
        <div class="card-header">Challan details</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-lg-6">
                    <label class="form-label" for="client_id">Client <span class="text-danger">*</span></label>
                    <select name="client_id" id="client_id" class="form-select" required>
                        <option value="">Select client…</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}" @selected(old('client_id') == $client->id)>{{ $client->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label" for="issued_on">Issue date <span class="text-danger">*</span></label>
                    <input type="date" name="issued_on" id="issued_on" class="form-control" required
                           value="{{ old('issued_on', now()->toDateString()) }}">
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label" for="vehicle_no">Vehicle no. <span class="text-body-secondary fw-normal">(optional)</span></label>
                    <input type="text" name="vehicle_no" id="vehicle_no" class="form-control" value="{{ old('vehicle_no') }}">
                </div>
                <div class="col-12">
                    <label class="form-label" for="remarks">Remarks <span class="text-body-secondary fw-normal">(optional)</span></label>
                    <textarea name="remarks" id="remarks" class="form-control" rows="2" placeholder="Anything to note on this challan">{{ old('remarks') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm form-card">
        <div class="card-header">
            <span>Dispatch lines</span>
            <button type="button" class="btn btn-sm btn-outline-primary" id="addChallanLine" aria-label="Add another dispatch line"><i class="fa-solid fa-plus me-1"></i>Add line</button>
        </div>
        <div class="card-body">
            <div id="challanLines"></div>
        </div>
        <div class="card-footer d-flex flex-column flex-sm-row align-items-sm-center gap-2">
            <button type="submit" class="btn btn-primary">Create challan</button>
            <a href="{{ route('challans.index') }}" class="btn btn-outline-secondary">Cancel</a>
            <div class="ms-sm-auto text-body-secondary small">
                <span id="lineCount">0</span> line(s) &middot; total qty <strong id="totalQty" class="text-body">0</strong>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
(function () {
    const available = @json($availableLines);
    const container = document.getElementById('challanLines');
    const addBtn = document.getElementById('addChallanLine');

    const clientSelect = document.getElementById('client_id');

    function currentLines() {
        const cid = clientSelect.value;
        return cid ? available.filter(function (row) { return String(row.client_id) === cid; }) : [];
    }

    function optionsHtml() {
        let html = '<option value="">Select PO line…</option>';
        currentLines().forEach(function (row) {
            let label = 'PO ' + row.po_number + ' — ' + row.item_name;
            if (row.line_notes) {
                label += ' — ' + row.line_notes;
            }
            label += ' (pending ' + row.pending + ', order #' + row.order_id + ')';
            html += '<option value="' + row.id + '" data-pending="' + row.pending + '">' + label.replace(/</g, '&lt;') + '</option>';
        });
        return html;
    }

    function rowTemplate(index) {
        return `
        <div class="border rounded-3 bg-body-tertiary p-3 mb-3 challan-line-row" data-index="${index}">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="fw-semibold small text-body-secondary line-title">Line ${index + 1}</span>
                <button type="button" class="btn btn-sm btn-outline-danger remove-challan-line" title="Remove" aria-label="Remove this dispatch line"><i class="fa-solid fa-trash-can"></i></button>
            </div>
            <div class="row g-2">
                <div class="col-12 col-lg-8">
                    <label class="form-label">PO / item line</label>
                    <select name="lines[${index}][order_item_id]" class="form-select line-select" required>
                        ${optionsHtml()}
                    </select>
                </div>
                <div class="col-12 col-lg-4">
                    <label class="form-label">Quantity</label>
                    <div class="input-group">
                        <input type="number" name="lines[${index}][quantity]" class="form-control qty-input" min="1" value="1" required>
                        <span class="input-group-text pending-hint">of &mdash;</span>
                    </div>
                </div>
            </div>
        </div>`;
    }

    let nextIndex = 0;

    function reindexChallan() {
        const rows = container.querySelectorAll('.challan-line-row');
        rows.forEach((row, i) => {
            row.querySelectorAll('select, input').forEach((el) => {
                const name = el.getAttribute('name');
                if (name) {
                    el.setAttribute('name', name.replace(/lines\[\d+]/, 'lines[' + i + ']'));
                }
            });
        });
        nextIndex = rows.length;
        refreshSummary();
    }

    function refreshSummary() {
        const rows = container.querySelectorAll('.challan-line-row');
        let total = 0;
        rows.forEach(function (row, i) {
            row.querySelector('.line-title').textContent = 'Line ' + (i + 1);
            const sel = row.querySelector('.line-select');
            const q = parseInt(row.querySelector('.qty-input').value, 10);
            if (sel.value && !Number.isNaN(q)) {
                total += q;
            }
        });
        document.getElementById('lineCount').textContent = rows.length;
        document.getElementById('totalQty').textContent = total;
        syncChosen();
    }

    // Stop the same PO line being picked twice (challan lines are unique per order item).
    function syncChosen() {
        const rows = Array.from(container.querySelectorAll('.line-select'));
        const chosen = rows.map(function (el) { return el.value; }).filter(Boolean);
        rows.forEach(function (el) {
            const ts = el.tomselect;
            if (!ts) { return; }
            Object.keys(ts.options).forEach(function (val) {
                if (!val) { return; }
                const taken = chosen.includes(val) && el.value !== val;
                if (!!ts.options[val].disabled !== taken) {
                    ts.updateOption(val, Object.assign({}, ts.options[val], { disabled: taken }));
                }
            });
        });
    }

    function wireRow(row) {
        const sel = row.querySelector('.line-select');
        const qty = row.querySelector('.qty-input');
        const hint = row.querySelector('.pending-hint');

        function pendingForSelection() {
            const opt = sel.options[sel.selectedIndex];
            return opt ? parseInt(opt.getAttribute('data-pending') || '0', 10) : 0;
        }

        function clampQty() {
            let v = parseInt(String(qty.value).trim(), 10);
            if (Number.isNaN(v)) {
                return;
            }
            const pending = pendingForSelection();
            if (pending > 0 && v > pending) {
                qty.value = String(pending);
            }
            if (v < 1) {
                qty.value = '1';
            }
        }

        function updateHint() {
            const pending = pendingForSelection();
            hint.innerHTML = pending ? ('of ' + pending + ' pending') : 'of &mdash;';
            if (pending > 0) {
                qty.setAttribute('max', String(pending));
            } else {
                qty.removeAttribute('max');
            }
            clampQty();
            refreshSummary();
        }

        new TomSelect(sel, {
            maxItems: 1,
            placeholder: 'Search PO or item…',
            allowEmptyOption: true,
        });

        sel.addEventListener('change', updateHint);
        qty.addEventListener('input', function () { clampQty(); refreshSummary(); });
        qty.addEventListener('blur', function () { clampQty(); refreshSummary(); });
        updateHint();
    }

    function addRow() {
        container.insertAdjacentHTML('beforeend', rowTemplate(nextIndex));
        nextIndex++;
        const row = container.lastElementChild;
        wireRow(row);
        row.querySelector('.remove-challan-line').onclick = function () {
            destroySelects(row);
            row.remove();
            reindexChallan();
        };
    }

    addBtn.addEventListener('click', addRow);

    const challanForm = document.getElementById('challanForm');
    if (challanForm) {
        challanForm.addEventListener('submit', function (e) {
            const rows = container.querySelectorAll('.challan-line-row');
            const seen = new Set();
            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const sel = row.querySelector('.line-select');
                const qty = row.querySelector('.qty-input');
                if (!sel || !qty) {
                    continue;
                }
                const opt = sel.options[sel.selectedIndex];
                const pending = opt ? parseInt(opt.getAttribute('data-pending') || '0', 10) : 0;
                const q = parseInt(String(qty.value).trim(), 10);
                if (!sel.value) {
                    e.preventDefault();
                    if (typeof window.showAppToast === 'function') {
                        window.showAppToast('Select a PO line on every row.', 'danger', 'Cannot save challan');
                    }
                    return;
                }
                if (seen.has(sel.value)) {
                    e.preventDefault();
                    if (typeof window.showAppToast === 'function') {
                        window.showAppToast('The same PO line is selected more than once.', 'danger', 'Cannot save challan');
                    }
                    return;
                }
                seen.add(sel.value);
                if (!pending) {
                    e.preventDefault();
                    if (typeof window.showAppToast === 'function') {
                        window.showAppToast('The selected line has no pending quantity. Refresh the page.', 'danger', 'Cannot save challan');
                    }
                    return;
                }
                if (Number.isNaN(q) || q < 1 || q > pending) {
                    e.preventDefault();
                    if (typeof window.showAppToast === 'function') {
                        window.showAppToast('Quantity must be between 1 and pending (' + pending + ') for each line.', 'danger', 'Cannot save challan');
                    }
                    return;
                }
            }
        });
    }

    function destroySelects(scope) {
        scope.querySelectorAll('.line-select').forEach(function (el) {
            if (el.tomselect) {
                el.tomselect.destroy();
            }
        });
    }

    function resetRows() {
        destroySelects(container);
        container.innerHTML = '';
        nextIndex = 0;
        refreshSummary();
        const lines = currentLines();
        if (!clientSelect.value) {
            container.innerHTML = '<div class="border rounded-3 text-center text-body-secondary p-4" style="border-style:dashed !important">Select a client to see their pending PO lines.</div>';
            addBtn.disabled = true;
        } else if (!lines.length) {
            container.innerHTML = '<div class="alert alert-warning mb-0">This client has no pending PO lines.</div>';
            addBtn.disabled = true;
        } else {
            addBtn.disabled = false;
            addRow();
        }
    }

    clientSelect.addEventListener('change', resetRows);
    resetRows();
})();
</script>
@endpush
