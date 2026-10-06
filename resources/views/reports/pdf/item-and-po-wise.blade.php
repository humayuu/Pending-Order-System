@extends('reports.pdf.layout')

@section('pdf_body')
    @forelse ($blocks as $block)
        <div class="block">
            <h2>{{ $block->client_name }}</h2>
            <table>
                <thead>
                <tr>
                    <th>Product name</th>
                    @foreach ($block->pos as $po)
                        <th class="num">{{ $po }}</th>
                    @endforeach
                    <th class="num">Total</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($block->items as $item)
                    <tr>
                        <td>{{ $item->item_name }}</td>
                        @foreach ($block->pos as $po)
                            <td class="num">{{ $item->cells->has($po) ? number_format($item->cells[$po]) : '' }}</td>
                        @endforeach
                        <td class="num">{{ number_format($item->total) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @empty
        <p class="muted">No data.</p>
    @endforelse
@endsection
