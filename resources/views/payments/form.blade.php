@extends('layouts.app')

@section('title', 'Pay Order #'.$order->order_number)
@section('page-title', 'Pay Order #'.$order->order_number)

@section('content')
<div class="card">
    <div class="card-body">
        <p><strong>Total Amount:</strong> ${{ number_format($order->total_amount, 2) }}</p>
        <form method="POST" action="{{ route('payments.process-instant') }}">
            @csrf
            <input type="hidden" name="order_id" value="{{ $order->id }}">
            <div class="mb-3">
                <label class="form-label">Payment Method</label>
                <select name="payment_method" class="form-select" required>
                    <option value="credit_card">Credit Card</option>
                    <option value="debit_card">Debit Card</option>
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="digital_wallet">Digital Wallet</option>
                    <option value="cash">Cash</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Pay Now</button>
        </form>
    </div>
</div>
@endsection


