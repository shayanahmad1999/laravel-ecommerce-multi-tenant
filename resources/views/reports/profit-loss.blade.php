@extends('layouts.app')

@section('title', 'Profit & Loss Statement')
@section('page-title', 'Profit & Loss Statement')

@section('page-actions')
    <div class="btn-group">
        <button type="button" class="btn btn-outline-primary" onclick="exportReport('profit-loss')">
            <i class="fas fa-download me-2"></i>Export CSV
        </button>
        <button type="button" class="btn btn-outline-secondary" onclick="printReport()">
            <i class="fas fa-print me-2"></i>Print
        </button>
        <button type="button" class="btn btn-primary" onclick="loadProfitLossData()">
            <i class="fas fa-sync-alt me-2"></i>Refresh
        </button>
    </div>
@endsection

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Date Range Filter -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Report Period</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="dateFrom" class="form-label">From Date</label>
                    <input type="date" class="form-control" id="dateFrom" value="{{ $dateFrom }}">
                </div>
                <div class="col-md-3">
                    <label for="dateTo" class="form-label">To Date</label>
                    <input type="date" class="form-control" id="dateTo" value="{{ $dateTo }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div>
                        <button type="button" class="btn btn-primary" onclick="loadProfitLossData()">Generate
                            Report</button>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Quick Periods</label>
                    <div class="btn-group w-100">
                        <button type="button" class="btn btn-outline-secondary btn-sm"
                            onclick="setQuickPeriod('thisMonth')">This Month</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm"
                            onclick="setQuickPeriod('lastMonth')">Last Month</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm"
                            onclick="setQuickPeriod('thisYear')">This Year</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Profit & Loss Statement -->
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Profit & Loss Statement</h5>
                    <small class="text-muted">Period: <span id="reportPeriod">{{ $dateFrom }} to
                            {{ $dateTo }}</span></small>
                </div>
                <div class="card-body" id="profitLossReport">
                    <div class="text-center py-4" id="reportLoader">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mb-0 mt-2">Loading report...</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <!-- Profit Margins Chart -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">Profit Margins</h6>
                </div>
                <div class="card-body" style="height: 320px;">
                    <canvas id="profitMarginsChart"></canvas>
                </div>
            </div>

            <!-- Key Metrics -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Key Metrics</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h5 class="text-success" id="grossMargin">0%</h5>
                                <small class="text-muted">Gross Margin</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h5 class="text-info" id="netMargin">0%</h5>
                            <small class="text-muted">Net Margin</small>
                        </div>
                    </div>
                    <hr>
                    <div class="mb-2">
                        <small class="text-muted">Net Profit</small>
                        <div class="h5" id="netProfitAmount">$0.00</div>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Total Revenue</small>
                        <div class="h6" id="totalRevenueAmount">$0.00</div>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Total Expenses</small>
                        <div class="h6 text-danger" id="totalExpensesAmount">$0.00</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Breakdown Chart -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">Revenue vs Expenses Breakdown</h5>
        </div>
        <div class="card-body" style="height: 280px;">
            <canvas id="revenueExpensesChart"></canvas>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // ---------- Routes & helpers ----------
        window.REPORTS = {
            routes: {
                profitLoss: @json(route('reports.profit-loss')), // must return JSON on AJAX
                export: @json(route('reports.export')), // CSV export route
            }
        };

        const fmtCurrency = (n) =>
            '$' + (Number(n || 0)).toFixed(2);

        const fmtPercent = (n) =>
            (Number(n || 0)).toFixed(1) + '%';

        function showAlert(type, message) {
            const id = 'alert-' + Math.random().toString(36).slice(2);
            const html = `
    <div id="${id}" class="alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3"
         role="alert" style="z-index:1080;min-width:260px;">
      ${message}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>`;
            document.body.insertAdjacentHTML('beforeend', html);
            setTimeout(() => {
                const el = document.getElementById(id);
                el && bootstrap.Alert.getOrCreateInstance(el).close();
            }, 4000);
        }

        function showLoader(on) {
            const loader = document.getElementById('reportLoader');
            if (!loader) return;
            loader.style.display = on ? '' : 'none';
        }
    </script>

    <script>
        let profitMarginsChart;
        let revenueExpensesChart;

        $(document).ready(function() {
            initializeProfitMarginsChart();
            initializeRevenueExpensesChart();
            loadProfitLossData();
        });

        function loadProfitLossData() {
            const dateFrom = $('#dateFrom').val();
            const dateTo = $('#dateTo').val();

            showLoader(true);

            $.get(window.REPORTS.routes.profitLoss, {
                    date_from: dateFrom,
                    date_to: dateTo
                })
                .done((res) => {
                    showLoader(false);
                    if (!res?.success) {
                        showAlert('danger', res?.message || 'Failed to load report data');
                        return;
                    }
                    const data = res.data || {};
                    renderProfitLossReport(data);
                    updateCharts(data);
                    $('#reportPeriod').text(`${dateFrom} to ${dateTo}`);
                })
                .fail(() => {
                    showLoader(false);
                    showAlert('danger', 'Failed to load report data');
                });
        }

        function renderProfitLossReport(data) {
            const revenue = data.revenue || {};
            const expenses = data.expenses || {};
            const profitMargins = data.profit_margins || {};

            const salesRevenue = Number(revenue.sales_revenue || 0);
            const refunds = Number(revenue.refunds || 0);
            const netRevenue = Number(revenue.net_revenue ?? (salesRevenue - refunds));
            const cogs = Number(data.cogs || 0);
            const grossProfit = Number(data.gross_profit ?? (netRevenue - cogs));
            const totalExpenses = Number(expenses.total_expenses || 0);
            const netProfit = Number(data.net_profit ?? (grossProfit - totalExpenses));

            let reportHtml = `
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <tbody>
                    <tr class="table-light">
                        <td><strong>REVENUE</strong></td>
                        <td class="text-end"></td>
                    </tr>
                    <tr>
                        <td class="ps-3">Sales Revenue</td>
                        <td class="text-end">${fmtCurrency(salesRevenue)}</td>
                    </tr>
                    <tr>
                        <td class="ps-3 text-danger">Less: Refunds</td>
                        <td class="text-end text-danger">(${fmtCurrency(refunds)})</td>
                    </tr>
                    <tr class="table-info">
                        <td class="ps-3"><strong>Net Revenue</strong></td>
                        <td class="text-end"><strong>${fmtCurrency(netRevenue)}</strong></td>
                    </tr>
                    <tr><td colspan="2">&nbsp;</td></tr>

                    <tr class="table-light">
                        <td><strong>COST OF GOODS SOLD</strong></td>
                        <td class="text-end text-danger">(${fmtCurrency(cogs)})</td>
                    </tr>
                    <tr><td colspan="2">&nbsp;</td></tr>

                    <tr class="table-success">
                        <td><strong>GROSS PROFIT</strong></td>
                        <td class="text-end"><strong>${fmtCurrency(grossProfit)}</strong></td>
                    </tr>
                    <tr><td colspan="2">&nbsp;</td></tr>

                    <tr class="table-light">
                        <td><strong>OPERATING EXPENSES</strong></td>
                        <td class="text-end"></td>
                    </tr>`;

            Object.entries(expenses).forEach(([key, value]) => {
                if (key === 'total_expenses') return;
                const label = key.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
                reportHtml += `<tr>
            <td class="ps-3">${label}</td>
            <td class="text-end text-danger">(${fmtCurrency(value)})</td>
        </tr>`;
            });

            reportHtml += `
                    <tr class="table-warning">
                        <td class="ps-3"><strong>Total Operating Expenses</strong></td>
                        <td class="text-end text-danger"><strong>(${fmtCurrency(totalExpenses)})</strong></td>
                    </tr>
                    <tr><td colspan="2">&nbsp;</td></tr>

                    <tr class="${netProfit >= 0 ? 'table-success' : 'table-danger'}">
                        <td><strong>NET ${netProfit >= 0 ? 'PROFIT' : 'LOSS'}</strong></td>
                        <td class="text-end"><strong>${fmtCurrency(netProfit)}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>`;

            $('#profitLossReport').html(reportHtml);

            // Key metrics
            $('#grossMargin').text(fmtPercent(profitMargins.gross_margin));
            $('#netMargin').text(fmtPercent(profitMargins.net_margin));
            $('#netProfitAmount').text(fmtCurrency(netProfit));
            $('#totalRevenueAmount').text(fmtCurrency(netRevenue));
            $('#totalExpensesAmount').text(fmtCurrency(totalExpenses));
        }

        function updateCharts(data) {
            const pm = data.profit_margins || {
                gross_margin: 0,
                net_margin: 0
            };
            const rev = Number((data.revenue && data.revenue.net_revenue) || 0);
            const exp = Number((data.expenses && data.expenses.total_expenses) || 0);
            const net = Number(data.net_profit || (rev - exp));

            // Profit margins doughnut
            if (profitMarginsChart) {
                profitMarginsChart.data.labels = ['Gross Margin', 'Net Margin'];
                profitMarginsChart.data.datasets[0].data = [pm.gross_margin || 0, pm.net_margin || 0];
                profitMarginsChart.update();
            }

            // Revenue vs Expenses bar
            if (revenueExpensesChart) {
                revenueExpensesChart.data.labels = ['Revenue', 'Expenses', 'Net Profit'];
                revenueExpensesChart.data.datasets[0].data = [rev, exp, net];
                revenueExpensesChart.update();
            }
        }

        function initializeProfitMarginsChart() {
            const ctx = document.getElementById('profitMarginsChart').getContext('2d');
            if (profitMarginsChart) profitMarginsChart.destroy();
            profitMarginsChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Gross Margin', 'Net Margin'],
                    datasets: [{
                        data: [0, 0],
                        backgroundColor: ['#198754', '#0d6efd'],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => `${ctx.label}: ${Number(ctx.parsed).toFixed(1)}%`
                            }
                        }
                    }
                }
            });
        }

        function initializeRevenueExpensesChart() {
            const ctx = document.getElementById('revenueExpensesChart').getContext('2d');
            if (revenueExpensesChart) revenueExpensesChart.destroy();
            revenueExpensesChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Revenue', 'Expenses', 'Net Profit'],
                    datasets: [{
                        label: 'Amount ($)',
                        data: [0, 0, 0],
                        backgroundColor: ['#198754', '#dc3545', '#0d6efd'],
                        borderColor: ['#198754', '#dc3545', '#0d6efd'],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => `${ctx.label}: ${fmtCurrency(ctx.parsed.y)}`
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: (v) => '$' + Number(v).toFixed(0)
                            }
                        }
                    }
                }
            });
        }

        function setQuickPeriod(period) {
            const today = new Date();
            let fromDate, toDate;

            switch (period) {
                case 'thisMonth':
                    fromDate = new Date(today.getFullYear(), today.getMonth(), 1);
                    toDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                    break;
                case 'lastMonth':
                    fromDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                    toDate = new Date(today.getFullYear(), today.getMonth(), 0);
                    break;
                case 'thisYear':
                    fromDate = new Date(today.getFullYear(), 0, 1);
                    toDate = new Date(today.getFullYear(), 11, 31);
                    break;
                default:
                    fromDate = today;
                    toDate = today;
            }

            $('#dateFrom').val(fromDate.toISOString().split('T')[0]);
            $('#dateTo').val(toDate.toISOString().split('T')[0]);
            loadProfitLossData();
        }

        function exportReport(type) {
            const dateFrom = encodeURIComponent($('#dateFrom').val());
            const dateTo = encodeURIComponent($('#dateTo').val());
            const url = `${window.REPORTS.routes.export}?type=${type}&date_from=${dateFrom}&date_to=${dateTo}`;
            window.open(url, '_blank');
        }

        function printReport() {
            window.print();
        }
    </script>
@endpush

@push('styles')
    <style>
        @media print {

            .sidebar,
            .btn,
            .card-header,
            .page-actions,
            nav,
            footer {
                display: none !important;
            }

            .main-content {
                margin: 0 !important;
                padding: 0 !important;
            }

            .card {
                border: none !important;
                box-shadow: none !important;
            }

            .card-body {
                padding: 0 !important;
            }
        }
    </style>
@endpush
