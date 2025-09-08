@extends('layouts.app')

@section('title', 'Pay Installment #'.$installment->installment_number)
@section('page-title', 'Pay Installment #'.$installment->installment_number)

@section('content')
<div class="card">
    <div class="card-body">
        <p><strong>Order:</strong> <a href="{{ route('orders.show', $installment->order_id) }}">#{{ $installment->order->order_number }}</a></p>
        <p><strong>Amount:</strong> ${{ number_format($installment->total_amount, 2) }}</p>
        <form method="POST" action="{{ route('payments.process-installment') }}">
            @csrf
            <input type="hidden" name="installment_id" value="{{ $installment->id }}">
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


