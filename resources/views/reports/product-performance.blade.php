@extends('layouts.app')

@section('title', 'Product Performance Report')
@section('page-title', 'Product Performance Report')

@section('page-actions')
<div class="btn-group">
    <a href="{{ route('reports.export-pdf', ['type' => 'product-performance', 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}"
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
                        <h5 class="mb-0">Product Performance Filters</h5>
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
                        <label for="category_id" class="form-label">Category</label>
                        <select class="form-select" id="category_id" name="category_id">
                            <option value="">All Categories</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i>Filter
                            </button>
                            <a href="{{ route('reports.product-performance') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Performance Summary Cards -->
<div class="row mb-4" id="summaryCards">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6>Total Products</h6>
                        <h4 id="totalProducts">0</h4>
                    </div>
                    <i class="fas fa-box fa-2x opacity-75"></i>
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
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6>Total Profit</h6>
                        <h4 id="totalProfit">$0.00</h4>
                    </div>
                    <i class="fas fa-chart-line fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6>Avg Profit Margin</h6>
                        <h4 id="avgProfitMargin">0.00%</h4>
                    </div>
                    <i class="fas fa-percentage fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Product Performance Data Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Product Performance Details</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="performanceTable" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>SKU</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Cost</th>
                        <th>Units Sold</th>
                        <th>Total Revenue</th>
                        <th>Total Cost</th>
                        <th>Profit</th>
                        <th>Profit Margin</th>
                        <th>Orders</th>
                    </tr>
                </thead>
                <tbody id="performanceTableBody">
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
    loadCategories();
    loadPerformanceData();

    $('#date_from, #date_to, #category_id').change(function() {
        loadPerformanceData();
    });
});

function loadCategories() {
    $.get('{{ route("categories.select") }}', function(response) {
        if (response.success) {
            let options = '<option value="">All Categories</option>';
            response.data.forEach(function(category) {
                options += `<option value="${category.id}">${category.name}</option>`;
            });
            $('#category_id').html(options);
        }
    });
}

function loadPerformanceData() {
    const dateFrom = $('#date_from').val();
    const dateTo = $('#date_to').val();
    const categoryId = $('#category_id').val();

    $.get('{{ route("reports.product-performance") }}', {
        date_from: dateFrom,
        date_to: dateTo,
        category_id: categoryId
    }, function(response) {
        if (response.success) {
            updateSummaryCards(response.data.summary);
            updatePerformanceTable(response.data.products);
        }
    });
}

function updateSummaryCards(summary) {
    $('#totalProducts').text(summary.total_products.toLocaleString());
    $('#totalRevenue').text('$' + summary.total_revenue.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    $('#totalProfit').text('$' + summary.total_profit.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    $('#avgProfitMargin').text(summary.average_profit_margin.toFixed(2) + '%');
}

function updatePerformanceTable(products) {
    let html = '';

    if (products.length === 0) {
        html = '<tr><td colspan="11" class="text-center text-muted">No product performance data found for the selected criteria.</td></tr>';
    } else {
        products.forEach(function(product) {
            html += `
                <tr>
                    <td>${product.name}</td>
                    <td>${product.sku}</td>
                    <td>${product.category}</td>
                    <td>$${parseFloat(product.price).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    <td>$${parseFloat(product.cost_price).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    <td>${product.total_sold}</td>
                    <td>$${parseFloat(product.total_revenue).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    <td>$${parseFloat(product.total_cost).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    <td class="${product.profit >= 0 ? 'text-success' : 'text-danger'}">
                        $${parseFloat(product.profit).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                    </td>
                    <td class="${product.profit_margin >= 0 ? 'text-success' : 'text-danger'}">
                        ${product.profit_margin.toFixed(2)}%
                    </td>
                    <td>${product.order_count}</td>
                </tr>
            `;
        });
    }

    $('#performanceTableBody').html(html);
}
</script>
@endpush