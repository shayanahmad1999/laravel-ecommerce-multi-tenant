@extends('layouts.app')

@section('title', 'Payment #'.$payment->payment_number)
@section('page-title', 'Payment #'.$payment->payment_number)

@section('content')
<div class="card">
    <div class="card-body">
        <p><strong>Order:</strong> <a href="{{ route('orders.show', $payment->order_id) }}">#{{ $payment->order->order_number }}</a></p>
        <p><strong>Amount:</strong> ${{ number_format($payment->amount, 2) }}</p>
        <p><strong>Status:</strong> {{ ucfirst($payment->status) }}</p>
        <p><strong>Method:</strong> {{ str_replace('_', ' ', ucfirst($payment->payment_method)) }}</p>
        <p><strong>Type:</strong> {{ ucfirst($payment->payment_type) }}</p>
        <p><strong>Processed At:</strong> {{ $payment->processed_at }}</p>
    </div>
</div>
@endsection


