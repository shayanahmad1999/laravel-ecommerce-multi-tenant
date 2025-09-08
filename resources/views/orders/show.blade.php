@extends('layouts.app')

@section('title', 'Order #'.$order->order_number)
@section('page-title', 'Order #'.$order->order_number)

@section('content')
<div class="card">
    <div class="card-body">
        <h5 class="mb-3">Order Summary</h5>
        <p><strong>Status:</strong> {{ ucfirst($order->status) }}</p>
        <p><strong>Total:</strong> ${{ number_format($order->total_amount, 2) }}</p>

        <h6 class="mt-4">Items</h6>
        <ul class="list-group">
            @foreach($order->orderItems as $item)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        {{ $item->product_name }}
                        <small class="text-muted d-block">SKU: {{ $item->product_sku }}</small>
                    </div>
                    <div>
                        {{ $item->quantity }} x ${{ number_format($item->unit_price, 2) }} = ${{ number_format($item->total_price, 2) }}
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</div>
@endsection


