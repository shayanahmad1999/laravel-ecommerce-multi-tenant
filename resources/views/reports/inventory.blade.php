@extends('layouts.app')

@section('title', 'Inventory Report')
@section('page-title', 'Inventory Report')

@section('page-actions')
<div class="btn-group">
    <a href="{{ route('reports.export-pdf', ['type' => 'inventory']) }}" class="btn btn-success">
        <i class="fas fa-download me-1"></i>Download PDF
    </a>
    <a href="{{ route('reports.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i>Back to Reports
    </a>
</div>
@endsection

@section('content')
<!-- Inventory Summary Cards -->
<div class="row mb-4" id="summaryCards">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6>Total Products</h6>
                        <h4 id="totalProducts">0</h4>
                    </div>
                    <i class="fas fa-boxes fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6>Total Value</h6>
                        <h4 id="totalValue">$0.00</h4>
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
                        <h6>Low Stock Items</h6>
                        <h4 id="lowStock">0</h4>
                    </div>
                    <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6>Out of Stock</h6>
                        <h4 id="outOfStock">0</h4>
                    </div>
                    <i class="fas fa-times-circle fa-2x opacity-75"></i>
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
            <div class="col-md-4">
                <label for="category_id" class="form-label">Category</label>
                <select class="form-select" id="category_id" name="category_id">
                    <option value="">All Categories</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="stock_status" class="form-label">Stock Status</label>
                <select class="form-select" id="stock_status" name="stock_status">
                    <option value="">All Status</option>
                    <option value="good">In Stock</option>
                    <option value="low">Low Stock</option>
                    <option value="out">Out of Stock</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">&nbsp;</label>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i>Filter
                    </button>
                    <a href="{{ route('reports.inventory') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Inventory Data Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Inventory Details</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="inventoryTable" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>SKU</th>
                        <th>Category</th>
                        <th>Stock Qty</th>
                        <th>Min Level</th>
                        <th>Cost Price</th>
                        <th>Selling Price</th>
                        <th>Inventory Value</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="inventoryTableBody">
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
    loadInventoryData();

    $('#filterForm').submit(function(e) {
        e.preventDefault();
        loadInventoryData();
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

function loadInventoryData() {
    const categoryId = $('#category_id').val();
    const stockStatus = $('#stock_status').val();

    $.get('{{ route("reports.inventory") }}', {
        category_id: categoryId,
        stock_status: stockStatus
    }, function(response) {
        if (response.success) {
            updateSummaryCards(response.data.summary);
            updateInventoryTable(response.data.products);
        }
    });
}

function updateSummaryCards(summary) {
    $('#totalProducts').text(summary.total_products.toLocaleString());
    $('#totalValue').text('$' + summary.total_value.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    $('#lowStock').text(summary.low_stock.toLocaleString());
    $('#outOfStock').text(summary.out_of_stock.toLocaleString());
}

function updateInventoryTable(products) {
    let html = '';

    if (products.length === 0) {
        html = '<tr><td colspan="9" class="text-center text-muted">No inventory data found.</td></tr>';
    } else {
        products.forEach(function(product) {
            const inventoryValue = product.stock_quantity * product.cost_price;
            let statusBadge = '';

            if (product.stock_quantity === 0) {
                statusBadge = '<span class="badge bg-danger">Out of Stock</span>';
            } else if (product.stock_quantity <= product.min_stock_level) {
                statusBadge = '<span class="badge bg-warning">Low Stock</span>';
            } else {
                statusBadge = '<span class="badge bg-success">In Stock</span>';
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
                    <td>$${inventoryValue.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    <td>${statusBadge}</td>
                </tr>
            `;
        });
    }

    $('#inventoryTableBody').html(html);
}
</script>
@endpush