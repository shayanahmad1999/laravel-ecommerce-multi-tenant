@extends('layouts.app')

@section('title', 'Installments Report')
@section('page-title', 'Installments Report')

@section('page-actions')
<div class="btn-group">
    <a href="{{ route('reports.export-pdf', ['type' => 'installments']) }}" class="btn btn-success">
        <i class="fas fa-download me-1"></i>Download PDF
    </a>
    <a href="{{ route('reports.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i>Back to Reports
    </a>
</div>
@endsection

@section('content')
<!-- Installments Summary Cards -->
<div class="row mb-4" id="summaryCards">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6>Total Installments</h6>
                        <h4 id="totalInstallments">0</h4>
                    </div>
                    <i class="fas fa-calendar-alt fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6>Pending Amount</h6>
                        <h4 id="pendingAmount">$0.00</h4>
                    </div>
                    <i class="fas fa-clock fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6>Paid Amount</h6>
                        <h4 id="paidAmount">$0.00</h4>
                    </div>
                    <i class="fas fa-check fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6>Overdue Count</h6>
                        <h4 id="overdueCount">0</h4>
                    </div>
                    <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
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
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="paid">Paid</option>
                    <option value="overdue">Overdue</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="overdue_only" class="form-label">Overdue Only</label>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="overdue_only" name="overdue_only" value="1">
                    <label class="form-check-label" for="overdue_only">
                        Show only overdue installments
                    </label>
                </div>
            </div>
            <div class="col-md-4">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i>Filter
                    </button>
                    <a href="{{ route('reports.installments') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Installments Data Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Installment Details</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="installmentsTable" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Installment #</th>
                        <th>Amount</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Days Overdue</th>
                    </tr>
                </thead>
                <tbody id="installmentsTableBody">
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
    loadInstallmentsData();

    $('#filterForm').submit(function(e) {
        e.preventDefault();
        loadInstallmentsData();
    });

    $('#status, #overdue_only').change(function() {
        loadInstallmentsData();
    });
});

function loadInstallmentsData() {
    const status = $('#status').val();
    const overdueOnly = $('#overdue_only').is(':checked') ? 1 : 0;

    $.get('{{ route("reports.installments") }}', {
        status: status,
        overdue_only: overdueOnly
    }, function(response) {
        if (response.success) {
            updateSummaryCards(response.data.summary);
            updateInstallmentsTable(response.data.installments);
        }
    });
}

function updateSummaryCards(summary) {
    $('#totalInstallments').text(summary.total_installments.toLocaleString());
    $('#pendingAmount').text('$' + summary.pending_amount.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    $('#paidAmount').text('$' + summary.paid_amount.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    $('#overdueCount').text(summary.overdue_count.toLocaleString());
}

function updateInstallmentsTable(installments) {
    let html = '';

    if (installments.length === 0) {
        html = '<tr><td colspan="7" class="text-center text-muted">No installment data found.</td></tr>';
    } else {
        installments.forEach(function(installment) {
            const dueDate = new Date(installment.due_date);
            const today = new Date();
            const daysOverdue = installment.status === 'pending' && dueDate < today ?
                Math.floor((today - dueDate) / (1000 * 60 * 60 * 24)) : 0;

            const statusClass = {
                'pending': daysOverdue > 0 ? 'danger' : 'warning',
                'paid': 'success',
                'overdue': 'danger',
                'cancelled': 'secondary'
            }[installment.status] || 'secondary';

            const statusText = installment.status === 'pending' && daysOverdue > 0 ? 'Overdue' : installment.status.charAt(0).toUpperCase() + installment.status.slice(1);

            html += `
                <tr>
                    <td>${installment.order_number || 'N/A'}</td>
                    <td>${installment.customer_name || 'N/A'}</td>
                    <td>${installment.installment_number}</td>
                    <td>$${parseFloat(installment.total_amount).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    <td>${dueDate.toLocaleDateString()}</td>
                    <td><span class="badge bg-${statusClass}">${statusText}</span></td>
                    <td>${daysOverdue > 0 ? daysOverdue + ' days' : '-'}</td>
                </tr>
            `;
        });
    }

    $('#installmentsTableBody').html(html);
}
</script>
@endpush