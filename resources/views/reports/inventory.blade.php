@extends('layouts.app')

@section('title', 'Inventory Report')
@section('page-title', 'Inventory Report')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Inventory Report</h4>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-bs-toggle="collapse" data-bs-target="#filters">
                            <i class="fas fa-filter"></i> Filters
                        </button>
                        <button type="button" class="btn btn-tool" onclick="exportReport('inventory')">
                            <i class="fas fa-download"></i> Export
                        </button>
                    </div>
                </div>

                <div class="collapse show" id="filters">
                    <div class="card-body">
                        <form id="inventoryForm" class="row g-3">
                            <div class="col-md-3">
                                <label for="category_id" class="form-label">Category</label>
                                <select class="form-select" id="category_id" name="category_id">
                                    <option value="">All Categories</option>
                                    <!-- Categories will be loaded dynamically -->
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="stock_status" class="form-label">Stock Status</label>
                                <select class="form-select" id="stock_status" name="stock_status">
                                    <option value="">All Status</option>
                                    <option value="good">Good Stock</option>
                                    <option value="low">Low Stock</option>
                                    <option value="out">Out of Stock</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <button type="button" class="btn btn-primary w-100" onclick="loadInventory()">
                                    <i class="fas fa-search"></i> Generate Report
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Loading Spinner -->
                    <div id="loadingSpinner" class="text-center py-5" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Generating inventory report...</p>
                    </div>

                    <!-- Summary Cards -->
                    <div id="summaryCards" class="row mb-4" style="display: none;">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Products</h5>
                                    <h3 id="totalProducts">0</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Low Stock Items</h5>
                                    <h3 id="lowStockItems">0</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Out of Stock</h5>
                                    <h3 id="outOfStockItems">0</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Value</h5>
                                    <h3 id="totalValue">$0</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Inventory Table -->
                    <div id="inventoryTable" class="table-responsive" style="display: none;">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Product Name</th>
                                    <th>SKU</th>
                                    <th>Category</th>
                                    <th>Stock Quantity</th>
                                    <th>Min Stock Level</th>
                                    <th>Stock Status</th>
                                    <th>Cost Price</th>
                                    <th>Selling Price</th>
                                    <th>Inventory Value</th>
                                </tr>
                            </thead>
                            <tbody id="inventoryBody">
                                <!-- Data will be loaded here -->
                            </tbody>
                        </table>
                    </div>

                    <!-- No Data Message -->
                    <div id="noDataMessage" class="text-center py-5" style="display: none;">
                        <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                        <h4>No inventory data found</h4>
                        <p class="text-muted">Try adjusting your filters or add some products to your inventory.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    loadInventory();
    loadCategories();
});

function loadInventory() {
    const formData = new FormData(document.getElementById('inventoryForm'));
    const params = new URLSearchParams(formData);

    // Show loading
    document.getElementById('loadingSpinner').style.display = 'block';
    document.getElementById('summaryCards').style.display = 'none';
    document.getElementById('inventoryTable').style.display = 'none';
    document.getElementById('noDataMessage').style.display = 'none';

    fetch(`/reports/inventory?${params}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateInventory(data.data);
            } else {
                showAlert('error', data.message || 'Failed to load inventory report');
                document.getElementById('noDataMessage').style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Failed to load inventory data');
            document.getElementById('noDataMessage').style.display = 'block';
        })
        .finally(() => {
            document.getElementById('loadingSpinner').style.display = 'none';
        });
}

function updateInventory(data) {
    // Update summary cards
    document.getElementById('totalProducts').textContent = data.summary.total_products;
    document.getElementById('lowStockItems').textContent = data.summary.low_stock_items;
    document.getElementById('outOfStockItems').textContent = data.summary.out_of_stock_items;
    document.getElementById('totalValue').textContent = '$' + data.summary.total_inventory_value.toLocaleString();

    // Update table
    const tbody = document.getElementById('inventoryBody');
    tbody.innerHTML = '';

    if (data.products.length === 0) {
        document.getElementById('noDataMessage').style.display = 'block';
        return;
    }

    data.products.forEach(product => {
        const row = document.createElement('tr');

        // Determine stock status class
        let statusClass = 'bg-success';
        let statusText = 'Good';

        if (product.stock_status === 'low') {
            statusClass = 'bg-warning';
            statusText = 'Low Stock';
        } else if (product.stock_status === 'out') {
            statusClass = 'bg-danger';
            statusText = 'Out of Stock';
        }

        row.innerHTML = `
            <td>${product.name}</td>
            <td>${product.sku || 'N/A'}</td>
            <td>${product.category ? product.category.name : 'N/A'}</td>
            <td>${product.stock_quantity}</td>
            <td>${product.min_stock_level}</td>
            <td><span class="badge ${statusClass}">${statusText}</span></td>
            <td>$${product.cost_price}</td>
            <td>$${product.price}</td>
            <td>$${product.inventory_value.toLocaleString()}</td>
        `;

        tbody.appendChild(row);
    });

    // Show content
    document.getElementById('summaryCards').style.display = 'block';
    document.getElementById('inventoryTable').style.display = 'block';
}

function loadCategories() {
    // This would typically fetch categories from an API endpoint
    // For now, we'll leave it as a placeholder
    console.log('Loading categories...');
}

function exportReport(type) {
    const formData = new FormData(document.getElementById('inventoryForm'));
    const params = new URLSearchParams(formData);
    params.set('type', type);

    window.open(`/reports/export?${params}`, '_blank');
}

function showAlert(type, message) {
    // Simple alert implementation - you can enhance this with a proper notification system
    alert(message);
}
</script>
@endpush