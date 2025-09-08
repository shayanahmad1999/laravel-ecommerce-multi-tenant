@extends('layouts.app')

@section('title', 'Balance Sheet')
@section('page-title', 'Balance Sheet')

@section('page-actions')
    <div class="btn-group">
        <button type="button" class="btn btn-outline-primary" onclick="exportBalanceSheet()">
            <i class="fas fa-download me-2"></i>Export CSV
        </button>
        <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
            <i class="fas fa-print me-2"></i>Print
        </button>
        <button type="button" class="btn btn-primary" onclick="loadBalanceSheet()">
            <i class="fas fa-sync-alt me-2"></i>Refresh
        </button>
    </div>
@endsection

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Period Filter -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Report Date</h5>
        </div>
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label" for="asOfDate">As of</label>
                    <input type="date" class="form-control" id="asOfDate"
                        value="{{ $asOfDate ?? now()->toDateString() }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div>
                        <button class="btn btn-primary" type="button" onclick="loadBalanceSheet()">Generate</button>
                    </div>
                </div>
                <div class="col-md-6 text-md-end">
                    <label class="form-label d-block">Quick Periods</label>
                    <div class="btn-group">
                        <button class="btn btn-outline-secondary btn-sm" onclick="setQuick('today')">Today</button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="setQuick('monthEnd')">This Month
                            End</button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="setQuick('yearEnd')">This Year
                            End</button>
                    </div>
                    <div class="mt-2">
                        <small class="text-muted">As of: <span
                                id="periodText">{{ $asOfDate ?? now()->toDateString() }}</span></small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main layout -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Balance Sheet</h6>
                    <small class="text-muted">As of <span
                            id="asOfText">{{ $asOfDate ?? now()->toDateString() }}</span></small>
                </div>
                <div class="card-body" id="balanceSheetBody">
                    <div class="text-center py-4" id="bsLoader">
                        <div class="spinner-border" role="status"><span class="visually-hidden">Loading…</span></div>
                        <p class="mb-0 mt-2">Loading report…</p>
                    </div>

                    <!-- Rendered content gets injected here -->
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Composition -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">Composition</h6>
                </div>
                <div class="card-body" style="height: 280px;">
                    <canvas id="compositionChart"></canvas>
                </div>
            </div>

            <!-- Current A vs L -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">Current Assets vs Liabilities</h6>
                </div>
                <div class="card-body" style="height: 260px;">
                    <canvas id="currentALChart"></canvas>
                </div>
            </div>

            <!-- Key Ratios -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Key Ratios</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between"><span>Current Ratio</span><strong
                            id="currentRatio">0.00</strong></div>
                    <div class="d-flex justify-content-between mt-1"><span>Quick Ratio</span><strong
                            id="quickRatio">0.00</strong></div>
                    <div class="d-flex justify-content-between mt-1"><span>Debt-to-Equity</span><strong
                            id="debtToEquity">0.00</strong></div>
                    <hr>
                    <div class="d-flex justify-content-between"><span>Total Assets</span><strong
                            id="totalAssets">$0.00</strong></div>
                    <div class="d-flex justify-content-between text-danger mt-1"><span>Total Liabilities</span><strong
                            id="totalLiabilities">$0.00</strong></div>
                    <div class="d-flex justify-content-between text-info mt-1"><span>Total Equity</span><strong
                            id="totalEquity">$0.00</strong></div>
                    <div class="mt-2 small" id="balanceCheckMsg"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // ---------- Routes & helpers ----------
        window.BS = {
            routes: {
                show: @json(route('reports.balance-sheet')), // GET JSON {success, data:{...}}
                export: @json(route('reports.export')), // CSV: type=balance-sheet
            }
        };

        const fmtCurrency = (n) => '$' + (Number(n || 0)).toFixed(2);
        const safeNum = (v) => Number(v || 0);

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

        function toggleLoader(on) {
            const el = document.getElementById('bsLoader');
            if (!el) return;
            el.style.display = on ? '' : 'none';
        }
    </script>

    <script>
        let compositionChart, currentALChart;

        $(document).ready(function() {
            // CSRF for future POSTs if needed
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            initCharts();
            loadBalanceSheet();

            $('#asOfDate').on('change', function() {
                updateAsOfText();
            });
        });

        function setQuick(kind) {
            const today = new Date();
            let date = today;
            if (kind === 'monthEnd') date = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            if (kind === 'yearEnd') date = new Date(today.getFullYear(), 11, 31);
            $('#asOfDate').val(date.toISOString().split('T')[0]);
            updateAsOfText();
            loadBalanceSheet();
        }

        function updateAsOfText() {
            const d = $('#asOfDate').val();
            $('#asOfText').text(d || '—');
            $('#periodText').text(d || '—');
        }

        function loadBalanceSheet() {
            const asOf = $('#asOfDate').val();
            toggleLoader(true);

            $.get(window.BS.routes.show, {
                    as_of: asOf
                })
                .done((res) => {
                    toggleLoader(false);
                    if (!res?.success) {
                        showAlert('danger', res?.message || 'Failed to load balance sheet');
                        return;
                    }
                    const data = res.data || {};
                    renderBalanceSheet(data);
                    updateRatiosAndCards(data);
                    updateCharts(data);
                })
                .fail(() => {
                    toggleLoader(false);
                    showAlert('danger', 'Failed to load balance sheet');
                });
        }

        function renderSection(title, items = [], highlightTotalLabel = null) {
            let html = `
    <table class="table table-sm mb-4">
      <thead>
        <tr class="table-light"><th>${title.toUpperCase()}</th><th class="text-end">Amount</th></tr>
      </thead>
      <tbody>`;

            items.forEach(row => {
                if (!row) return;
                const label = (row.label || '').replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
                const val = fmtCurrency(row.amount);
                html += `<tr><td class="ps-3">${label}</td><td class="text-end">${val}</td></tr>`;
            });

            if (highlightTotalLabel) {
                const total = items.reduce((s, r) => s + safeNum(r?.amount), 0);
                html += `<tr class="table-info">
      <td class="ps-3"><strong>${highlightTotalLabel}</strong></td>
      <td class="text-end"><strong>${fmtCurrency(total)}</strong></td>
    </tr>`;
            }

            html += `</tbody></table>`;
            return html;
        }

        function renderBalanceSheet(data) {
            // Expected buckets (any missing ones are treated as 0)
            // data.assets.current[], data.assets.non_current[]
            // data.liabilities.current[], data.liabilities.long_term[]
            // data.equity[]  (or data.equity.owner_equity / retained_earnings etc.)
            const A = data.assets || {};
            const L = data.liabilities || {};
            const E = data.equity || {};

            const currA = Array.isArray(A.current) ? A.current : toArray(A.current);
            const nonCurrA = Array.isArray(A.non_current) ? A.non_current : toArray(A.non_current);

            const currL = Array.isArray(L.current) ? L.current : toArray(L.current);
            const longL = Array.isArray(L.long_term) ? L.long_term : toArray(L.long_term);

            const equityItems = Array.isArray(E) ? E : toArray(E);

            const currAHtml = renderSection('Current Assets', currA, 'Total Current Assets');
            const nonCurrAHtml = renderSection('Non-Current Assets', nonCurrA, 'Total Non-Current Assets');

            const currLHtml = renderSection('Current Liabilities', currL, 'Total Current Liabilities');
            const longLHtml = renderSection('Long-Term Liabilities', longL, 'Total Long-Term Liabilities');

            const equityHtml = renderSection('Equity', equityItems, 'Total Equity');

            const totalAssets = sumRows(currA) + sumRows(nonCurrA);
            const totalLiabilities = sumRows(currL) + sumRows(longL);
            const totalEquity = sumRows(equityItems);

            const checkOk = Math.abs(totalAssets - (totalLiabilities + totalEquity)) < 0.01;

            const totalsHtml = `
    <table class="table table-sm">
      <tbody>
        <tr class="table-success">
          <td><strong>Total Assets</strong></td>
          <td class="text-end"><strong>${fmtCurrency(totalAssets)}</strong></td>
        </tr>
        <tr class="table-warning">
          <td><strong>Total Liabilities</strong></td>
          <td class="text-end"><strong>${fmtCurrency(totalLiabilities)}</strong></td>
        </tr>
        <tr class="table-info">
          <td><strong>Total Equity</strong></td>
          <td class="text-end"><strong>${fmtCurrency(totalEquity)}</strong></td>
        </tr>
        <tr class="${checkOk ? 'table-light' : 'table-danger'}">
          <td><strong>Check</strong></td>
          <td class="text-end">
            <strong>${fmtCurrency(totalAssets)} = ${fmtCurrency(totalLiabilities + totalEquity)}</strong>
            ${checkOk ? '<span class="badge bg-success ms-2">Balanced</span>' : '<span class="badge bg-danger ms-2">Not Balanced</span>'}
          </td>
        </tr>
      </tbody>
    </table>`;

            const html = `
    <div class="row">
      <div class="col-md-6">${currAHtml}${nonCurrAHtml}</div>
      <div class="col-md-6">${currLHtml}${longLHtml}${equityHtml}</div>
    </div>
    ${totalsHtml}
  `;

            $('#balanceSheetBody').html(html);
        }

        function toArray(objOrArray) {
            if (!objOrArray) return [];
            if (Array.isArray(objOrArray)) return objOrArray;
            // Convert object {cash: 100, inventory: 200} to [{label:'cash',amount:100}, ...]
            return Object.entries(objOrArray).map(([k, v]) => ({
                label: k,
                amount: v
            }));
        }

        function sumRows(arr) {
            return (arr || []).reduce((s, r) => s + safeNum(r?.amount), 0);
        }

        function updateRatiosAndCards(data) {
            const A = data.assets || {};
            const L = data.liabilities || {};

            const currA = sumRows(Array.isArray(A.current) ? A.current : toArray(A.current));
            const inv = safeNum((A.current && A.current.find?.(r => /inventory/i.test(r.label || ''))?.amount) ?? A
                .inventory ?? 0);
            const cash = safeNum((A.current && A.current.find?.(r => /cash|bank/i.test(r.label || ''))?.amount) ?? 0);
            const receivables = safeNum((A.current && A.current.find?.(r => /receivable/i.test(r.label || ''))?.amount) ??
                0);

            const currL = sumRows(Array.isArray(L.current) ? L.current : toArray(L.current));
            const totalLiabilities = currL + sumRows(Array.isArray(L.long_term) ? L.long_term : toArray(L.long_term));

            const totalAssets = sumRows(Array.isArray(A.current) ? A.current : toArray(A.current)) +
                sumRows(Array.isArray(A.non_current) ? A.non_current : toArray(A.non_current));

            const totalEquity = sumRows(Array.isArray(data.equity) ? data.equity : toArray(data.equity));

            // Ratios
            const currentRatio = currL ? (currA / currL) : 0;
            const quickAssets = Math.max(currA - inv, 0); // simplistic quick: (CA - Inventory)
            const quickRatio = currL ? (quickAssets / currL) : 0;
            const debtToEquity = totalEquity ? (totalLiabilities / totalEquity) : 0;

            $('#currentRatio').text((currentRatio).toFixed(2));
            $('#quickRatio').text((quickRatio).toFixed(2));
            $('#debtToEquity').text((debtToEquity).toFixed(2));

            $('#totalAssets').text(fmtCurrency(totalAssets));
            $('#totalLiabilities').text(fmtCurrency(totalLiabilities));
            $('#totalEquity').text(fmtCurrency(totalEquity));

            const checkOk = Math.abs(totalAssets - (totalLiabilities + totalEquity)) < 0.01;
            $('#balanceCheckMsg').html(checkOk ?
                '<span class="badge bg-success">Balanced</span>' :
                '<span class="badge bg-danger">Not Balanced (check mappings)</span>');
        }

        function initCharts() {
            const compCtx = document.getElementById('compositionChart').getContext('2d');
            compositionChart = new Chart(compCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Assets', 'Liabilities', 'Equity'],
                    datasets: [{
                        data: [0, 0, 0]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            const alCtx = document.getElementById('currentALChart').getContext('2d');
            currentALChart = new Chart(alCtx, {
                type: 'bar',
                data: {
                    labels: ['Current Assets', 'Current Liabilities'],
                    datasets: [{
                        label: 'Amount ($)',
                        data: [0, 0]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
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

        function updateCharts(data) {
            const A = data.assets || {};
            const L = data.liabilities || {};
            const E = data.equity || {};

            const totalAssets =
                sumRows(Array.isArray(A.current) ? A.current : toArray(A.current)) +
                sumRows(Array.isArray(A.non_current) ? A.non_current : toArray(A.non_current));

            const totalLiabilities =
                sumRows(Array.isArray(L.current) ? L.current : toArray(L.current)) +
                sumRows(Array.isArray(L.long_term) ? L.long_term : toArray(L.long_term));

            const totalEquity = sumRows(Array.isArray(E) ? E : toArray(E));

            const currentAssets = sumRows(Array.isArray(A.current) ? A.current : toArray(A.current));
            const currentLiabilities = sumRows(Array.isArray(L.current) ? L.current : toArray(L.current));

            if (compositionChart) {
                compositionChart.data.datasets[0].data = [totalAssets, totalLiabilities, totalEquity];
                compositionChart.update();
            }
            if (currentALChart) {
                currentALChart.data.datasets[0].data = [currentAssets, currentLiabilities];
                currentALChart.update();
            }
        }

        function exportBalanceSheet() {
            const asOf = encodeURIComponent($('#asOfDate').val() || '');
            const url = `${window.BS.routes.export}?type=balance-sheet&as_of=${asOf}`;
            window.open(url, '_blank');
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
