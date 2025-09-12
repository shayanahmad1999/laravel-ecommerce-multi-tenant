@extends('layouts.app')

@section('title', 'Payment Methods Report')
@section('page-title', 'Payment Methods Report')

@section('page-actions')
<div class="btn-group">
    <a href="{{ route('reports.export-pdf', ['type' => 'payment-methods', 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}"
       class="btn btn-success">
        <i class="fas fa-download me-1"></i>Download PDF
    </a>
    <a href="{{ route('reports.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i>Back to Reports
    </a>
</div>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Payment Methods Filters</h5>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label for="date_from" class="form-label">From Date</label>
                        <input type="date" class="form-control" id="date_from" name="date_from"
                               value="{{ $dateFrom }}">
                    </div>
                    <div class="col-md-4">
                        <label for="date_to" class="form-label">To Date</label>
                        <input type="date" class="form-control" id="date_to" name="date_to"
                               value="{{ $dateTo }}">
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i>Filter
                            </button>
                            <a href="{{ route('reports.payment-methods') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Payment Methods Summary Cards -->
<div class="row mb-4" id="summaryCards">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6>Total Methods</h6>
                        <h4 id="totalMethods">0</h4>
                    </div>
                    <i class="fas fa-credit-card fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6>Total Transactions</h6>
                        <h4 id="totalTransactions">0</h4>
                    </div>
                    <i class="fas fa-exchange-alt fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6>Total Amount</h6>
                        <h4 id="totalAmount">$0.00</h4>
                    </div>
                    <i class="fas fa-dollar-sign fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6>Most Used</h6>
                        <h6 id="mostUsed">-</h6>
                    </div>
                    <i class="fas fa-trophy fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Methods Data Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Payment Methods Details</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="paymentTable" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Payment Method</th>
                        <th>Transaction Count</th>
                        <th>Total Amount</th>
                        <th>Average Amount</th>
                        <th>Order Count</th>
                        <th>Percentage</th>
                    </tr>
                </thead>
                <tbody id="paymentTableBody">
                    <!-- Data will be loaded via AJAX -->
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    loadPaymentData();

    $('#date_from, #date_to').change(function() {
        loadPaymentData();
    });
});

function loadPaymentData() {
    const dateFrom = $('#date_from').val();
    const dateTo = $('#date_to').val();

    $.get('{{ route("reports.payment-methods") }}', {
        date_from: dateFrom,
        date_to: dateTo
    }, function(response) {
        if (response.success) {
            updateSummaryCards(response.data.summary);
            updatePaymentTable(response.data.payment_methods);
        }
    });
}

function updateSummaryCards(summary) {
    $('#totalMethods').text(summary.total_methods.toLocaleString());
    $('#totalTransactions').text(summary.total_transactions.toLocaleString());
    $('#totalAmount').text('$' + summary.total_amount.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    $('#mostUsed').text(summary.most_used ? summary.most_used.payment_method.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) : '-');
}

function updatePaymentTable(methods) {
    let html = '';

    if (methods.length === 0) {
        html = '<tr><td colspan="6" class="text-center text-muted">No payment method data found for the selected period.</td></tr>';
    } else {
        const totalAmount = methods.reduce((sum, method) => sum + parseFloat(method.total_amount), 0);

        methods.forEach(function(method) {
            const percentage = totalAmount > 0 ? ((method.total_amount / totalAmount) * 100).toFixed(1) : 0;
            const methodName = method.payment_method.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());

            html += `
                <tr>
                    <td>${methodName}</td>
                    <td>${method.transaction_count}</td>
                    <td>$${parseFloat(method.total_amount).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    <td>$${parseFloat(method.average_amount).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    <td>${method.order_count}</td>
                    <td>${percentage}%</td>
                </tr>
            `;
        });
    }

    $('#paymentTableBody').html(html);
}
</script>
@endpush