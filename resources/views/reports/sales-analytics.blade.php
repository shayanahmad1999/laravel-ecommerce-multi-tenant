@extends('layouts.app')

@section('title', 'Sales Analytics')
@section('page-title', 'Sales Analytics')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Sales Analytics</h4>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-bs-toggle="collapse" data-bs-target="#filters">
                            <i class="fas fa-filter"></i> Filters
                        </button>
                        <button type="button" class="btn btn-tool" onclick="exportReport('sales-analytics')">
                            <i class="fas fa-download"></i> Export
                        </button>
                    </div>
                </div>

                <div class="collapse show" id="filters">
                    <div class="card-body">
                        <form id="analyticsForm" class="row g-3">
                            <div class="col-md-3">
                                <label for="period" class="form-label">Period</label>
                                <select class="form-select" id="period" name="period">
                                    <option value="day">Daily</option>
                                    <option value="week">Weekly</option>
                                    <option value="month" selected>Monthly</option>
                                    <option value="year">Yearly</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="date_from" class="form-label">From Date</label>
                                <input type="date" class="form-control" id="date_from" name="date_from"
                                       value="{{ date('Y-m-01') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="date_to" class="form-label">To Date</label>
                                <input type="date" class="form-control" id="date_to" name="date_to"
                                       value="{{ date('Y-m-t') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <button type="button" class="btn btn-primary w-100" onclick="loadAnalytics()">
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
                        <p class="mt-2">Generating analytics report...</p>
                    </div>

                    <!-- Charts Container -->
                    <div id="analyticsContent">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Sales by Period</h5>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="salesChart" width="400" height="200"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Payment Methods</h5>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="paymentChart" width="400" height="200"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Top Products</h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="topProducts" class="list-group">
                                            <div class="text-center py-3">
                                                <div class="spinner-border spinner-border-sm" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Sales by Category</h5>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="categoryChart" width="400" height="200"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let salesChart, paymentChart, categoryChart;

document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    loadAnalytics();
});

function initializeCharts() {
    // Sales Chart
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    salesChart = new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Sales Amount',
                data: [],
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Payment Methods Chart
    const paymentCtx = document.getElementById('paymentChart').getContext('2d');
    paymentChart = new Chart(paymentCtx, {
        type: 'doughnut',
        data: {
            labels: [],
            datasets: [{
                data: [],
                backgroundColor: [
                    'rgb(255, 99, 132)',
                    'rgb(54, 162, 235)',
                    'rgb(255, 205, 86)',
                    'rgb(75, 192, 192)',
                    'rgb(153, 102, 255)'
                ]
            }]
        },
        options: {
            responsive: true
        }
    });

    // Category Chart
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    categoryChart = new Chart(categoryCtx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: 'Sales by Category',
                data: [],
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgb(54, 162, 235)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
}

function loadAnalytics() {
    const formData = new FormData(document.getElementById('analyticsForm'));
    const params = new URLSearchParams(formData);

    // Show loading
    document.getElementById('loadingSpinner').style.display = 'block';
    document.getElementById('analyticsContent').style.opacity = '0.5';

    fetch(`/reports/sales-analytics?${params}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateCharts(data.data);
            } else {
                showAlert('error', data.message || 'Failed to load analytics');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Failed to load analytics data');
        })
        .finally(() => {
            // Hide loading
            document.getElementById('loadingSpinner').style.display = 'none';
            document.getElementById('analyticsContent').style.opacity = '1';
        });
}

function updateCharts(data) {
    // Update Sales Chart
    salesChart.data.labels = data.sales_by_period.map(item => item.period);
    salesChart.data.datasets[0].data = data.sales_by_period.map(item => item.total_sales);
    salesChart.update();

    // Update Payment Methods Chart
    paymentChart.data.labels = data.payment_methods.map(item => item.payment_method);
    paymentChart.data.datasets[0].data = data.payment_methods.map(item => item.total_amount);
    paymentChart.update();

    // Update Category Chart
    categoryChart.data.labels = data.sales_by_category.map(item => item.name);
    categoryChart.data.datasets[0].data = data.sales_by_category.map(item => item.total_revenue);
    categoryChart.update();

    // Update Top Products List
    const topProductsHtml = data.top_products.map(product => `
        <div class="list-group-item d-flex justify-content-between align-items-center">
            ${product.name}
            <span class="badge bg-primary rounded-pill">$${product.total_revenue.toLocaleString()}</span>
        </div>
    `).join('');

    document.getElementById('topProducts').innerHTML = topProductsHtml || '<div class="text-center py-3 text-muted">No data available</div>';
}

function exportReport(type) {
    const formData = new FormData(document.getElementById('analyticsForm'));
    const params = new URLSearchParams(formData);
    params.set('type', type);

    window.open(`/reports/export?${params}`, '_blank');
}

function showAlert(type, message) {
    // Simple alert implementation
    alert(message);
}
</script>
@endpush