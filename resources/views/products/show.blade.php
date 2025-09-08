@extends('layouts.app')

@section('title', $product->name)
@section('page-title', $product->name)

@section('content')
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                @if($product->images && count($product->images))
                    <img src="/storage/{{ $product->images[0] }}" class="img-fluid rounded" alt="{{ $product->name }}">
                @else
                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 250px;">
                        <i class="fas fa-image text-muted fa-2x"></i>
                    </div>
                @endif
            </div>
            <div class="col-md-8">
                <h4>{{ $product->name }}</h4>
                <p class="text-muted">SKU: {{ $product->sku ?? 'N/A' }}</p>
                <p>{{ $product->description }}</p>
                <p><strong>Category:</strong> {{ $product->category?->name ?? 'N/A' }}</p>
                <p><strong>Price:</strong> ${{ number_format($product->price, 2) }}</p>
                <p><strong>Stock:</strong> {{ $product->stock_quantity }}</p>
            </div>
        </div>
    </div>
</div>
@endsection


