@extends('layouts.app')

@section('title', 'Reports')
@section('page-title', 'Reports')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Available Reports</h5>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <!-- Sales Report -->
                    <div class="col-md-6 col-lg-3">
                        <div class="card h-100 border-primary">
                            <div class="card-body text-center">
                                <i class="fas fa-chart-line fa-3x text-primary mb-3"></i>
                                <h5 class="card-title">Sales Report</h5>
                                <p class="card-text text-muted">View sales performance and revenue analytics</p>
                                <a href="{{ route('reports.sales') }}" class="btn btn-primary">
                                    <i class="fas fa-eye me-1"></i>View Report
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Inventory Report -->
                    <div class="col-md-6 col-lg-3">
                        <div class="card h-100 border-success">
                            <div class="card-body text-center">
                                <i class="fas fa-boxes fa-3x text-success mb-3"></i>
                                <h5 class="card-title">Inventory Report</h5>
                                <p class="card-text text-muted">Monitor stock levels and inventory value</p>
                                <a href="{{ route('reports.inventory') }}" class="btn btn-success">
                                    <i class="fas fa-eye me-1"></i>View Report
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Orders Report -->
                    <div class="col-md-6 col-lg-3">
                        <div class="card h-100 border-info">
                            <div class="card-body text-center">
                                <i class="fas fa-shopping-cart fa-3x text-info mb-3"></i>
                                <h5 class="card-title">Orders Report</h5>
                                <p class="card-text text-muted">Track order status and fulfillment</p>
                                <a href="{{ route('reports.orders') }}" class="btn btn-info">
                                    <i class="fas fa-eye me-1"></i>View Report
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Customers Report -->
                    <div class="col-md-6 col-lg-3">
                        <div class="card h-100 border-warning">
                            <div class="card-body text-center">
                                <i class="fas fa-users fa-3x text-warning mb-3"></i>
                                <h5 class="card-title">Customers Report</h5>
                                <p class="card-text text-muted">Analyze customer behavior and spending</p>
                                <a href="{{ route('reports.customers') }}" class="btn btn-warning">
                                    <i class="fas fa-eye me-1"></i>View Report
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Product Performance Report -->
                    <div class="col-md-6 col-lg-3">
                        <div class="card h-100 border-danger">
                            <div class="card-body text-center">
                                <i class="fas fa-trophy fa-3x text-danger mb-3"></i>
                                <h5 class="card-title">Product Performance</h5>
                                <p class="card-text text-muted">Best selling products and profit analysis</p>
                                <a href="{{ route('reports.product-performance') }}" class="btn btn-danger">
                                    <i class="fas fa-eye me-1"></i>View Report
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Category Performance Report -->
                    <div class="col-md-6 col-lg-3">
                        <div class="card h-100 border-secondary">
                            <div class="card-body text-center">
                                <i class="fas fa-tags fa-3x text-secondary mb-3"></i>
                                <h5 class="card-title">Category Performance</h5>
                                <p class="card-text text-muted">Category sales and performance analysis</p>
                                <a href="{{ route('reports.category-performance') }}" class="btn btn-secondary">
                                    <i class="fas fa-eye me-1"></i>View Report
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Methods Report -->
                    <div class="col-md-6 col-lg-3">
                        <div class="card h-100 border-dark">
                            <div class="card-body text-center">
                                <i class="fas fa-credit-card fa-3x text-dark mb-3"></i>
                                <h5 class="card-title">Payment Methods</h5>
                                <p class="card-text text-muted">Payment method usage and analysis</p>
                                <a href="{{ route('reports.payment-methods') }}" class="btn btn-dark">
                                    <i class="fas fa-eye me-1"></i>View Report
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Installments Report -->
                    <div class="col-md-6 col-lg-3">
                        <div class="card h-100 border-primary">
                            <div class="card-body text-center">
                                <i class="fas fa-calendar-alt fa-3x text-primary mb-3"></i>
                                <h5 class="card-title">Installments</h5>
                                <p class="card-text text-muted">Installment payment tracking</p>
                                <a href="{{ route('reports.installments') }}" class="btn btn-primary">
                                    <i class="fas fa-eye me-1"></i>View Report
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Low Stock Alert Report -->
                    <div class="col-md-6 col-lg-3">
                        <div class="card h-100 border-warning">
                            <div class="card-body text-center">
                                <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                                <h5 class="card-title">Low Stock Alert</h5>
                                <p class="card-text text-muted">Products needing restock</p>
                                <a href="{{ route('reports.low-stock') }}" class="btn btn-warning">
                                    <i class="fas fa-eye me-1"></i>View Report
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Revenue Trends Report -->
                    <div class="col-md-6 col-lg-3">
                        <div class="card h-100 border-success">
                            <div class="card-body text-center">
                                <i class="fas fa-chart-area fa-3x text-success mb-3"></i>
                                <h5 class="card-title">Revenue Trends</h5>
                                <p class="card-text text-muted">Daily/weekly/monthly revenue analysis</p>
                                <a href="{{ route('reports.revenue-trends') }}" class="btn btn-success">
                                    <i class="fas fa-eye me-1"></i>View Report
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection