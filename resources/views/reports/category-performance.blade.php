@extends('layouts.app')

@section('title', 'Category Performance Report')
@section('page-title', 'Category Performance Report')

@section('page-actions')
<div class="btn-group">
    <a href="{{ route('reports.export-pdf', ['type' => 'category-performance', 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}"
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
                <h5 class="mb-0">Category Performance Filters</h5>
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
                            <a href="{{ route('reports.category-performance') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Category Summary Cards -->
<div class="row mb-4" id="summaryCards">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6>Total Categories</h6>
                        <h4 id="totalCategories">0</h4>
                    </div>
                    <i class="fas fa-tags fa-2x opacity-75"></i>
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
                        <h6>Total Items Sold</h6>
                        <h4 id="totalSold">0</h4>
                    </div>
                    <i class="fas fa-box fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6>Best Category</h6>
                        <h6 id="bestCategory">-</h6>
                    </div>
                    <i class="fas fa-trophy fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Category Performance Data Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Category Performance Details</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="categoryTable" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Category Name</th>
                        <th>Items Sold</th>
                        <th>Total Revenue</th>
                        <th>Product Count</th>
                        <th>Order Count</th>
                        <th>Avg Order Value</th>
                    </tr>
                </thead>
                <tbody id="categoryTableBody">
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
    loadCategoryData();

    $('#date_from, #date_to').change(function() {
        loadCategoryData();
    });
});

function loadCategoryData() {
    const dateFrom = $('#date_from').val();
    const dateTo = $('#date_to').val();

    $.get('{{ route("reports.category-performance") }}', {
        date_from: dateFrom,
        date_to: dateTo
    }, function(response) {
        if (response.success) {
            updateSummaryCards(response.data.summary);
            updateCategoryTable(response.data.categories);
        }
    });
}

function updateSummaryCards(summary) {
    $('#totalCategories').text(summary.total_categories.toLocaleString());
    $('#totalRevenue').text('$' + summary.total_revenue.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    $('#totalSold').text(summary.total_sold.toLocaleString());
    $('#bestCategory').text(summary.best_category ? summary.best_category.name : '-');
}

function updateCategoryTable(categories) {
    let html = '';

    if (categories.length === 0) {
        html = '<tr><td colspan="6" class="text-center text-muted">No category performance data found for the selected period.</td></tr>';
    } else {
        categories.forEach(function(category) {
            const avgOrderValue = category.order_count > 0 ? category.total_revenue / category.order_count : 0;
            html += `
                <tr>
                    <td>${category.name}</td>
                    <td>${category.total_sold}</td>
                    <td>$${parseFloat(category.total_revenue).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    <td>${category.product_count}</td>
                    <td>${category.order_count}</td>
                    <td>$${avgOrderValue.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                </tr>
            `;
        });
    }

    $('#categoryTableBody').html(html);
}
</script>
@endpush