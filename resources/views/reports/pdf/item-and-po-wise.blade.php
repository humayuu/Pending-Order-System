@extends('reports.pdf.layout')

@section('pdf_body')
    <table>
        <thead>
        <tr>
            <th>PO number</th>
            <th>Item</th>
            <th class="num">Ordered</th>
            <th class="num">Delivered</th>
            <th class="num">Pending</th>
        </tr>
        </thead>
        <tbody>
        @forelse ($rows as $row)
            <tr>
                <td>{{ $row->po_number }}</td>
                <td>{{ $row->item_name }}</td>
                <td class="num">{{ $row->total_ordered }}</td>
                <td class="num">{{ $row->total_delivered }}</td>
                <td class="num">{{ $row->pending }}</td>
            </tr>
        @empty
            <tr><td colspan="5" class="muted">No data.</td></tr>
        @endforelse
        </tbody>
    </table>
@endsection
