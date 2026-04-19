@extends('layouts.app')

@section('title', 'New order')

@section('content')
<h1 class="h3 mb-2">New order</h1>
<p class="text-muted mb-4 small lh-base">Each line: <strong>PO number</strong>, <strong>item name</strong>, <strong>quantity</strong>, optional <strong>notes</strong>, then optional <strong>PO PDF</strong> last.</p>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="post" action="{{ route('orders.store') }}" enctype="multipart/form-data" id="orderForm">
            @csrf

            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-stretch align-items-sm-center gap-2 mb-2">
                <span class="fw-semibold">Lines</span>
                <button type="button" class="btn btn-sm btn-outline-primary align-self-stretch align-self-sm-auto" id="addLine" aria-label="Add another order line">Add line</button>
            </div>

            <div id="linesContainer"></div>

            <div class="d-flex flex-column flex-sm-row gap-2 mt-3">
                <button type="submit" class="btn btn-primary">Save order</button>
                <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const container = document.getElementById('linesContainer');
    const addBtn = document.getElementById('addLine');

    function lineTemplate(index) {
        return `
        <div class="border rounded p-3 mb-3 line-row" data-index="${index}">
            <div class="row g-2">
                <div class="col-12 col-md-6 col-xl-4">
                    <label class="form-label">PO number</label>
                    <input type="text" name="lines[${index}][po_number]" class="form-control" required>
                </div>
                <div class="col-12 col-md-6 col-xl-4">
                    <label class="form-label">Item name</label>
                    <input type="text" name="lines[${index}][item_name]" class="form-control" required>
                </div>
                <div class="col-12 col-md-6 col-xl-2">
                    <label class="form-label">Quantity</label>
                    <input type="number" name="lines[${index}][quantity]" class="form-control" min="1" value="1" required>
                </div>
            </div>
            <div class="row g-2 mt-1">
                <div class="col-12">
                    <label class="form-label">Notes <span class="text-muted fw-normal">(optional)</span></label>
                    <textarea name="lines[${index}][notes]" class="form-control" rows="2" placeholder="Line notes"></textarea>
                </div>
            </div>
            <div class="row g-2 mt-1 align-items-end">
                <div class="col-12 col-lg-10">
                    <label class="form-label">PO PDF <span class="text-muted fw-normal">(optional)</span></label>
                    <input type="file" name="lines[${index}][po_pdf]" class="form-control form-control-sm" accept="application/pdf">
                </div>
                <div class="col-12 col-lg-2 d-grid d-lg-flex justify-content-lg-end">
                    <button type="button" class="btn btn-outline-danger btn-sm remove-line" title="Remove line" aria-label="Remove this order line">Remove</button>
                </div>
            </div>
        </div>`;
    }

    let nextIndex = 0;

    function reindex() {
        const rows = container.querySelectorAll('.line-row');
        rows.forEach((row, i) => {
            row.dataset.index = String(i);
            row.querySelectorAll('input, textarea').forEach((el) => {
                const name = el.getAttribute('name');
                if (!name) return;
                el.setAttribute('name', name.replace(/lines\[\d+]/, 'lines[' + i + ']'));
            });
        });
        nextIndex = rows.length;
    }

    function addLine() {
        container.insertAdjacentHTML('beforeend', lineTemplate(nextIndex));
        nextIndex++;
        attachRemoveHandlers();
    }

    function attachRemoveHandlers() {
        container.querySelectorAll('.remove-line').forEach((btn) => {
            btn.onclick = () => {
                btn.closest('.line-row').remove();
                reindex();
            };
        });
    }

    addBtn.addEventListener('click', addLine);
    addLine();
})();
</script>
@endpush
