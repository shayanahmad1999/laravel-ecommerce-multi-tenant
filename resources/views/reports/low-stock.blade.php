@extends('layouts.app')

@section('title', 'Low Stock Alert Report')
@section('page-title', 'Low Stock Alert Report')

@section('page-actions')
<div class="btn-group">
    <a href="{{ route('reports.export-pdf', ['type' => 'low-stock']) }}" class="btn btn-success">
        <i class="fas fa-download me-1"></i>Download PDF
    </a>
    <a href="{{ route('reports.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i>Back to Reports
    </a>
</div>
@endsection

@section('content')
<!-- Low Stock Summary Cards -->
<div class="row mb-4" id="summaryCards">
    <div class="col-md-4">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6>Low Stock Items</h6>
                        <h4 id="lowStockCount">0</h4>
                    </div>
                    <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6>Out of Stock Items</h6>
                        <h4 id="outOfStockCount">0</h4>
                    </div>
                    <i class="fas fa-times-circle fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6>Value at Risk</h6>
                        <h4 id="valueAtRisk">$0.00</h4>
                    </div>
                    <i class="fas fa-dollar-sign fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Filters</h5>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3" id="filterForm">
            <div class="col-md-6">
                <label for="category_id" class="form-label">Category</label>
                <select class="form-select" id="category_id" name="category_id">
                    <option value="">All Categories</option>
                </select>
            </div>
            <div class="col-md-6">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i>Filter
                    </button>
                    <a href="{{ route('reports.low-stock') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Low Stock Data Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Low Stock Products</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="lowStockTable" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>SKU</th>
                        <th>Category</th>
                        <th>Current Stock</th>
                        <th>Min Stock Level</th>
                        <th>Cost Price</th>
                        <th>Selling Price</th>
                        <th>Stock Value</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="lowStockTableBody">
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
    loadLowStockData();

    $('#filterForm').submit(function(e) {
        e.preventDefault();
        loadLowStockData();
    });

    $('#category_id').change(function() {
        loadLowStockData();
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

function loadLowStockData() {
    const categoryId = $('#category_id').val();

    $.get('{{ route("reports.low-stock") }}', {
        category_id: categoryId
    }, function(response) {
        if (response.success) {
            updateSummaryCards(response.data.summary);
            updateLowStockTable(response.data.products);
        }
    });
}

function updateSummaryCards(summary) {
    $('#lowStockCount').text(summary.total_low_stock.toLocaleString());
    $('#outOfStockCount').text(summary.total_out_of_stock.toLocaleString());
    $('#valueAtRisk').text('$' + summary.total_value_at_risk.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
}

function updateLowStockTable(products) {
    let html = '';

    if (products.length === 0) {
        html = '<tr><td colspan="10" class="text-center text-muted">No low stock products found.</td></tr>';
    } else {
        products.forEach(function(product) {
            const stockValue = product.stock_quantity * product.cost_price;
            let statusBadge = '';
            let actionButton = '';

            if (product.stock_quantity === 0) {
                statusBadge = '<span class="badge bg-danger">Out of Stock</span>';
                actionButton = '<button class="btn btn-sm btn-danger" onclick="alert(\'Product is out of stock!\')">Restock</button>';
            } else {
                statusBadge = '<span class="badge bg-warning">Low Stock</span>';
                actionButton = '<button class="btn btn-sm btn-warning" onclick="alert(\'Product needs restocking!\')">Restock</button>';
            }

            html += `
                <tr>
                    <td>${product.name}</td>
                    <td>${product.sku || 'N/A'}</td>
                    <td>${product.category ? product.category.name : 'N/A'}</td>
                    <td>${product.stock_quantity}</td>
                    <td>${product.min_stock_level}</td>
                    <td>$${parseFloat(product.cost_price).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    <td>$${parseFloat(product.price).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    <td>$${stockValue.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    <td>${statusBadge}</td>
                    <td>${actionButton}</td>
                </tr>
            `;
        });
    }

    $('#lowStockTableBody').html(html);
}
</script>
@endpush