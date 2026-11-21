@extends('reports.pdf.layout')

@section('pdf_body')
    @foreach ($sections as $section)
        @if ($sections->count() > 1)
            <h2>PO: {{ $section->po_number }}</h2>
        @endif

        <div class="block">
            <table>
                <thead>
                <tr>
                    <th>Item</th>
                    <th class="num">Ordered</th>
                    <th class="num">Delivered</th>
                    <th class="num">Pending</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($section->items as $item)
                    @php($delivered = (int) ($item->delivered_sum ?? 0))
                    <tr>
                        <td>{{ $item->item_name }}</td>
                        <td class="num">{{ number_format($item->quantity) }}</td>
                        <td class="num">{{ number_format($delivered) }}</td>
                        <td class="num">{{ number_format(max(0, $item->quantity - $delivered)) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <h2>Delivery details</h2>
        @if ($section->deliveries->isEmpty())
            <p class="muted">No deliveries yet.</p>
        @else
            <table>
                <thead>
                <tr>
                    <th>Date</th>
                    <th>DC No.</th>
                    <th>Item</th>
                    <th class="num">Quantity</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($section->deliveries as $d)
                    <tr>
                        <td>{{ $d->issued_on->format('d-M-Y') }}</td>
                        <td>{{ $d->challan_number }}</td>
                        <td>{{ $d->item_name }}</td>
                        <td class="num">{{ number_format($d->quantity) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    @endforeach
@endsection
