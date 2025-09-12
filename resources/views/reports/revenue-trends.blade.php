@extends('layouts.app')

@section('title', 'Revenue Trends Report')
@section('page-title', 'Revenue Trends Report')

@section('page-actions')
<div class="btn-group">
    <a href="{{ route('reports.export-pdf', ['type' => 'revenue-trends', 'date_from' => $dateFrom, 'date_to' => $dateTo, 'period' => $period]) }}"
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
                <h5 class="mb-0">Revenue Trends Filters</h5>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label for="date_from" class="form-label">From Date</label>
                        <input type="date" class="form-control" id="date_from" name="date_from"
                               value="{{ $dateFrom }}">
                    </div>
                    <div class="col-md-3">
                        <label for="date_to" class="form-label">To Date</label>
                        <input type="date" class="form-control" id="date_to" name="date_to"
                               value="{{ $dateTo }}">
                    </div>
                    <div class="col-md-3">
                        <label for="period" class="form-label">Period</label>
                        <select class="form-select" id="period" name="period">
                            <option value="daily" {{ $period == 'daily' ? 'selected' : '' }}>Daily</option>
                            <option value="weekly" {{ $period == 'weekly' ? 'selected' : '' }}>Weekly</option>
                            <option value="monthly" {{ $period == 'monthly' ? 'selected' : '' }}>Monthly</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i>Filter
                            </button>
                            <a href="{{ route('reports.revenue-trends') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Revenue Trends Summary Cards -->
<div class="row mb-4" id="summaryCards">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6>Total Periods</h6>
                        <h4 id="totalPeriods">0</h4>
                    </div>
                    <i class="fas fa-calendar fa-2x opacity-75"></i>
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
                        <h6>Total Orders</h6>
                        <h4 id="totalOrders">0</h4>
                    </div>
                    <i class="fas fa-shopping-cart fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6>Avg Revenue/Period</h6>
                        <h4 id="avgRevenue">$0.00</h4>
                    </div>
                    <i class="fas fa-chart-line fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Revenue Chart -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Revenue Trend Chart</h5>
    </div>
    <div class="card-body">
        <canvas id="revenueChart" width="400" height="200"></canvas>
    </div>
</div>

<!-- Revenue Trends Data Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Revenue Trends Details</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="trendsTable" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Period</th>
                        <th>Order Count</th>
                        <th>Revenue</th>
                        <th>Avg Order Value</th>
                        <th>Growth %</th>
                    </tr>
                </thead>
                <tbody id="trendsTableBody">
                    <!-- Data will be loaded via AJAX -->
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    loadTrendsData();

    $('#date_from, #date_to, #period').change(function() {
        loadTrendsData();
    });
});

function loadTrendsData() {
    const dateFrom = $('#date_from').val();
    const dateTo = $('#date_to').val();
    const period = $('#period').val();

    $.get('{{ route("reports.revenue-trends") }}', {
        date_from: dateFrom,
        date_to: dateTo,
        period: period
    }, function(response) {
        if (response.success) {
            updateSummaryCards(response.data.summary);
            updateTrendsTable(response.data.trends);
            updateChart(response.data.trends);
        }
    });
}

function updateSummaryCards(summary) {
    $('#totalPeriods').text(summary.total_periods.toLocaleString());
    $('#totalRevenue').text('$' + summary.total_revenue.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    $('#totalOrders').text(summary.total_orders.toLocaleString());
    $('#avgRevenue').text('$' + summary.average_revenue_per_period.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
}

function updateTrendsTable(trends) {
    let html = '';
    let previousRevenue = 0;

    if (trends.length === 0) {
        html = '<tr><td colspan="5" class="text-center text-muted">No revenue trend data found for the selected period.</td></tr>';
    } else {
        trends.forEach(function(trend, index) {
            const growth = index > 0 && previousRevenue > 0 ?
                ((trend.revenue - previousRevenue) / previousRevenue * 100).toFixed(1) : 0;
            previousRevenue = trend.revenue;

            const growthClass = growth >= 0 ? 'text-success' : 'text-danger';
            const growthIcon = growth >= 0 ? '↑' : '↓';

            html += `
                <tr>
                    <td>${trend.period}</td>
                    <td>${trend.order_count}</td>
                    <td>$${parseFloat(trend.revenue).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    <td>$${parseFloat(trend.avg_order_value).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    <td class="${growthClass}">${growthIcon} ${Math.abs(growth)}%</td>
                </tr>
            `;
        });
    }

    $('#trendsTableBody').html(html);
}

function updateChart(trends) {
    const ctx = document.getElementById('revenueChart').getContext('2d');

    // Destroy existing chart if it exists
    if (window.revenueChart) {
        window.revenueChart.destroy();
    }

    const labels = trends.map(trend => trend.period);
    const data = trends.map(trend => trend.revenue);

    window.revenueChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Revenue',
                data: data,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Revenue Trends'
                }
            },
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
</script>
@endpush