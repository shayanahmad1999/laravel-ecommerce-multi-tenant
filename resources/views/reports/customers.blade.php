@extends('layouts.app')

@section('title', 'Customer Analytics')
@section('page-title', 'Customer Analytics')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Customer Analytics</h4>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-bs-toggle="collapse" data-bs-target="#filters">
                            <i class="fas fa-filter"></i> Filters
                        </button>
                        <button type="button" class="btn btn-tool" onclick="exportReport('customers')">
                            <i class="fas fa-download"></i> Export
                        </button>
                    </div>
                </div>

                <div class="collapse show" id="filters">
                    <div class="card-body">
                        <form id="customerForm" class="row g-3">
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
                                <button type="button" class="btn btn-primary w-100" onclick="loadCustomerAnalytics()">
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
                        <p class="mt-2">Generating customer analytics...</p>
                    </div>

                    <!-- Analytics Content -->
                    <div id="analyticsContent">
                        <div class="row">
                            <!-- Top Customers -->
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Top Customers by Revenue</h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="topCustomers" class="list-group">
                                            <div class="text-center py-3">
                                                <div class="spinner-border spinner-border-sm" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- New Customers Chart -->
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">New Customer Acquisition</h5>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="newCustomersChart" width="400" height="200"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <!-- Payment Type Preferences -->
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Payment Method Preferences</h5>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="paymentPreferencesChart" width="400" height="200"></canvas>
                                    </div>
                                </div>
                            </div>

                            <!-- Customer Statistics -->
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Customer Statistics</h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="customerStats">
                                            <div class="row text-center">
                                                <div class="col-6">
                                                    <h3 id="totalCustomers" class="text-primary">0</h3>
                                                    <p class="mb-0">Total Customers</p>
                                                </div>
                                                <div class="col-6">
                                                    <h3 id="newCustomers" class="text-success">0</h3>
                                                    <p class="mb-0">New This Period</p>
                                                </div>
                                            </div>
                                            <hr>
                                            <div class="row text-center">
                                                <div class="col-6">
                                                    <h3 id="avgOrderValue" class="text-info">$0</h3>
                                                    <p class="mb-0">Avg Order Value</p>
                                                </div>
                                                <div class="col-6">
                                                    <h3 id="totalRevenue" class="text-warning">$0</h3>
                                                    <p class="mb-0">Total Revenue</p>
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
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let newCustomersChart, paymentPreferencesChart;

document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    loadCustomerAnalytics();
});

function initializeCharts() {
    // New Customers Chart
    const newCustomersCtx = document.getElementById('newCustomersChart').getContext('2d');
    newCustomersChart = new Chart(newCustomersCtx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'New Customers',
                data: [],
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    // Payment Preferences Chart
    const paymentCtx = document.getElementById('paymentPreferencesChart').getContext('2d');
    paymentPreferencesChart = new Chart(paymentCtx, {
        type: 'pie',
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
}

function loadCustomerAnalytics() {
    const formData = new FormData(document.getElementById('customerForm'));
    const params = new URLSearchParams(formData);

    // Show loading
    document.getElementById('loadingSpinner').style.display = 'block';
    document.getElementById('analyticsContent').style.opacity = '0.5';

    fetch(`/reports/customers?${params}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateCustomerAnalytics(data.data);
            } else {
                showAlert('error', data.message || 'Failed to load customer analytics');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Failed to load customer analytics data');
        })
        .finally(() => {
            // Hide loading
            document.getElementById('loadingSpinner').style.display = 'none';
            document.getElementById('analyticsContent').style.opacity = '1';
        });
}

function updateCustomerAnalytics(data) {
    // Update Top Customers List
    const topCustomersHtml = data.top_customers.map(customer => `
        <div class="list-group-item d-flex justify-content-between align-items-center">
            <div>
                <strong>${customer.name}</strong><br>
                <small class="text-muted">${customer.email}</small>
            </div>
            <div class="text-end">
                <div class="fw-bold">$${customer.total_spent.toLocaleString()}</div>
                <small class="text-muted">${customer.total_orders} orders</small>
            </div>
        </div>
    `).join('');

    document.getElementById('topCustomers').innerHTML = topCustomersHtml || '<div class="text-center py-3 text-muted">No customer data available</div>';

    // Update New Customers Chart
    newCustomersChart.data.labels = data.new_customers.map(item => item.period);
    newCustomersChart.data.datasets[0].data = data.new_customers.map(item => item.new_customers);
    newCustomersChart.update();

    // Update Payment Preferences Chart
    paymentPreferencesChart.data.labels = data.payment_type_stats.map(item => item.payment_type);
    paymentPreferencesChart.data.datasets[0].data = data.payment_type_stats.map(item => item.total_amount);
    paymentPreferencesChart.update();

    // Update Statistics
    const totalCustomers = data.top_customers.length;
    const newCustomersCount = data.new_customers.reduce((sum, item) => sum + item.new_customers, 0);
    const totalRevenue = data.top_customers.reduce((sum, customer) => sum + customer.total_spent, 0);
    const avgOrderValue = totalCustomers > 0 ? totalRevenue / totalCustomers : 0;

    document.getElementById('totalCustomers').textContent = totalCustomers;
    document.getElementById('newCustomers').textContent = newCustomersCount;
    document.getElementById('avgOrderValue').textContent = '$' + avgOrderValue.toFixed(2);
    document.getElementById('totalRevenue').textContent = '$' + totalRevenue.toLocaleString();
}

function exportReport(type) {
    const formData = new FormData(document.getElementById('customerForm'));
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