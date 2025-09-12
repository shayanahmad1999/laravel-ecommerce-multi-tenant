@extends('layouts.app')

@section('title', 'Customers Report')
@section('page-title', 'Customers Report')

@section('page-actions')
<div class="btn-group">
    <a href="{{ route('reports.export-pdf', ['type' => 'customers', 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}"
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
                        <h5 class="mb-0">Customers Report Filters</h5>
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
                            <a href="{{ route('reports.customers') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Customers Summary Cards -->
<div class="row mb-4" id="summaryCards">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6>Total Customers</h6>
                        <h4 id="totalCustomers">0</h4>
                    </div>
                    <i class="fas fa-users fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6>Active Customers</h6>
                        <h4 id="activeCustomers">0</h4>
                    </div>
                    <i class="fas fa-user-check fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
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
                        <h6>Avg Order Value</h6>
                        <h4 id="avgOrderValue">$0.00</h4>
                    </div>
                    <i class="fas fa-calculator fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Customers Data Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Customer Details</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="customersTable" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Customer Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Total Orders</th>
                        <th>Total Spent</th>
                        <th>Last Order Date</th>
                        <th>Avg Order Value</th>
                    </tr>
                </thead>
                <tbody id="customersTableBody">
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
    loadCustomersData();

    $('#date_from, #date_to').change(function() {
        loadCustomersData();
    });
});

function loadCustomersData() {
    const dateFrom = $('#date_from').val();
    const dateTo = $('#date_to').val();

    $.get('{{ route("reports.customers") }}', {
        date_from: dateFrom,
        date_to: dateTo
    }, function(response) {
        if (response.success) {
            updateSummaryCards(response.data.summary);
            updateCustomersTable(response.data.customers);
        }
    });
}

function updateSummaryCards(summary) {
    $('#totalCustomers').text(summary.total_customers.toLocaleString());
    $('#activeCustomers').text(summary.active_customers.toLocaleString());
    $('#totalRevenue').text('$' + summary.total_revenue.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    $('#avgOrderValue').text('$' + summary.average_order_value.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
}

function updateCustomersTable(customers) {
    let html = '';

    if (customers.length === 0) {
        html = '<tr><td colspan="7" class="text-center text-muted">No customer data found for the selected period.</td></tr>';
    } else {
        customers.forEach(function(customer) {
            const avgOrderValue = customer.total_orders > 0 ? customer.total_spent / customer.total_orders : 0;
            const lastOrderDate = customer.last_order_date ?
                new Date(customer.last_order_date).toLocaleDateString() : 'N/A';

            html += `
                <tr>
                    <td>${customer.name}</td>
                    <td>${customer.email}</td>
                    <td>${customer.phone || 'N/A'}</td>
                    <td>${customer.total_orders}</td>
                    <td>$${parseFloat(customer.total_spent).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    <td>${lastOrderDate}</td>
                    <td>$${avgOrderValue.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                </tr>
            `;
        });
    }

    $('#customersTableBody').html(html);
}
</script>
@endpush