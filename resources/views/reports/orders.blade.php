@extends('layouts.app')

@section('title', 'Orders Report')
@section('page-title', 'Orders Report')

@section('page-actions')
<div class="btn-group">
    <a href="{{ route('reports.export-pdf', ['type' => 'orders', 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}"
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
                        <h5 class="mb-0">Orders Report Filters</h5>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label for="date_from" class="form-label">From Date</label>
                        <input type="date" class="form-control" id="date_from" name="date_from"
                               value="{{ $dateFrom }}">
                    </div>
                    <div class="col-md-3">
                        <label for="date_to" class="form-label">To Date</label>
                        <input type="date" class="form-control" id="date_to" name="date_to"
                               value="{{ $dateTo }}">
                    </div>
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="shipped">Shipped</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="payment_type" class="form-label">Payment Type</label>
                        <select class="form-select" id="payment_type" name="payment_type">
                            <option value="">All Types</option>
                            <option value="instant">Instant</option>
                            <option value="installment">Installment</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i>Filter
                            </button>
                            <a href="{{ route('reports.orders') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Orders Summary Cards -->
<div class="row mb-4" id="summaryCards">
    <div class="col-md-2">
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
    <div class="col-md-2">
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
    <div class="col-md-2">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6>Pending</h6>
                        <h4 id="pendingOrders">0</h4>
                    </div>
                    <i class="fas fa-clock fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6>Processing</h6>
                        <h4 id="processingOrders">0</h4>
                    </div>
                    <i class="fas fa-cog fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6>Shipped</h6>
                        <h4 id="shippedOrders">0</h4>
                    </div>
                    <i class="fas fa-truck fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6>Delivered</h6>
                        <h4 id="deliveredOrders">0</h4>
                    </div>
                    <i class="fas fa-check fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Orders Data Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Order Details</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="ordersTable" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Items</th>
                        <th>Subtotal</th>
                        <th>Tax</th>
                        <th>Shipping</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="ordersTableBody">
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
    loadOrdersData();

    $('#date_from, #date_to, #status, #payment_type').change(function() {
        loadOrdersData();
    });
});

function loadOrdersData() {
    const dateFrom = $('#date_from').val();
    const dateTo = $('#date_to').val();
    const status = $('#status').val();
    const paymentType = $('#payment_type').val();

    $.get('{{ route("reports.orders") }}', {
        date_from: dateFrom,
        date_to: dateTo,
        status: status,
        payment_type: paymentType
    }, function(response) {
        if (response.success) {
            updateSummaryCards(response.data.summary);
            updateOrdersTable(response.data.orders);
        }
    });
}

function updateSummaryCards(summary) {
    $('#totalOrders').text(summary.total_orders.toLocaleString());
    $('#totalRevenue').text('$' + summary.total_revenue.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    $('#pendingOrders').text(summary.pending_orders.toLocaleString());
    $('#processingOrders').text(summary.processing_orders.toLocaleString());
    $('#shippedOrders').text(summary.shipped_orders.toLocaleString());
    $('#deliveredOrders').text(summary.delivered_orders.toLocaleString());
}

function updateOrdersTable(orders) {
    let html = '';

    if (orders.length === 0) {
        html = '<tr><td colspan="10" class="text-center text-muted">No orders found for the selected criteria.</td></tr>';
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
                    <td>$${parseFloat(order.subtotal).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    <td>$${parseFloat(order.tax_amount).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    <td>$${parseFloat(order.shipping_cost).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    <td><strong>$${parseFloat(order.total_amount).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong></td>
                    <td>${order.payment_type.charAt(0).toUpperCase() + order.payment_type.slice(1)}</td>
                    <td><span class="badge bg-${statusClass}">${order.status.charAt(0).toUpperCase() + order.status.slice(1)}</span></td>
                </tr>
            `;
        });
    }

    $('#ordersTableBody').html(html);
}
</script>
@endpush