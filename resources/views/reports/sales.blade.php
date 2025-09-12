@extends('layouts.app')

@section('title', 'Sales Report')
@section('page-title', 'Sales Report')

@section('page-actions')
<div class="btn-group">
    <a href="{{ route('reports.export-pdf', ['type' => 'sales', 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}"
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
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="mb-0">Sales Report Filters</h5>
                    </div>
                </div>
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
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i>Filter
                            </button>
                            <a href="{{ route('reports.sales') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Sales Summary Cards -->
<div class="row mb-4" id="summaryCards">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6>Total Orders</h6>
                        <h4 id="totalOrders">0</h4>
                    </div>
                    <i class="fas fa-shopping-cart fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6>Total Revenue</h6>
                        <h4 id="totalRevenue">$0.00</h4>
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
                        <h6>Items Sold</h6>
                        <h4 id="totalItems">0</h4>
                    </div>
                    <i class="fas fa-box fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6>Avg Order Value</h6>
                        <h4 id="avgOrderValue">$0.00</h4>
                    </div>
                    <i class="fas fa-calculator fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Sales Data Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Sales Details</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="salesTable" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Items</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="salesTableBody">
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
    loadSalesData();

    $('#date_from, #date_to').change(function() {
        loadSalesData();
    });
});

function loadSalesData() {
    const dateFrom = $('#date_from').val();
    const dateTo = $('#date_to').val();

    $.get('{{ route("reports.sales") }}', {
        date_from: dateFrom,
        date_to: dateTo
    }, function(response) {
        if (response.success) {
            updateSummaryCards(response.data.summary);
            updateSalesTable(response.data.orders);
        }
    });
}

function updateSummaryCards(summary) {
    $('#totalOrders').text(summary.total_orders.toLocaleString());
    $('#totalRevenue').text('$' + summary.total_revenue.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    $('#totalItems').text(summary.total_items.toLocaleString());
    $('#avgOrderValue').text('$' + summary.average_order_value.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
}

function updateSalesTable(orders) {
    let html = '';

    if (orders.length === 0) {
        html = '<tr><td colspan="6" class="text-center text-muted">No sales data found for the selected period.</td></tr>';
    } else {
        orders.forEach(function(order) {
            const statusClass = {
                'pending': 'warning',
                'processing': 'info',
                'shipped': 'primary',
                'delivered': 'success',
                'cancelled': 'danger'
            }[order.status] || 'secondary';

            const itemsCount = order.order_items ? order.order_items.reduce((sum, item) => sum + item.quantity, 0) : 0;

            html += `
                <tr>
                    <td>${order.order_number}</td>
                    <td>${order.user ? order.user.name : 'N/A'}</td>
                    <td>${new Date(order.created_at).toLocaleDateString()}</td>
                    <td>${itemsCount}</td>
                    <td>$${parseFloat(order.total_amount).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    <td><span class="badge bg-${statusClass}">${order.status.charAt(0).toUpperCase() + order.status.slice(1)}</span></td>
                </tr>
            `;
        });
    }

    $('#salesTableBody').html(html);
}
</script>
@endpush