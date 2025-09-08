@extends('layouts.app')

@section('title', 'General Ledger')
@section('page-title', 'General Ledger')

@section('page-actions')
    <div class="btn-group">
        <button type="button" class="btn btn-outline-primary" onclick="exportLedger()">
            <i class="fas fa-download me-2"></i>Export CSV
        </button>
        <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
            <i class="fas fa-print me-2"></i>Print
        </button>
        @can('manage accounting')
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#entryModal"
                onclick="openCreateEntry()">
                <i class="fas fa-plus me-2"></i>New Entry
            </button>
        @endcan
    </div>
@endsection

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Filters</h5>
        </div>
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label" for="dateFrom">From Date</label>
                    <input type="date" class="form-control" id="dateFrom" value="{{ $dateFrom ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="dateTo">To Date</label>
                    <input type="date" class="form-control" id="dateTo" value="{{ $dateTo ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="accountFilter">Account</label>
                    <select id="accountFilter" class="form-select">
                        <option value="">All Accounts</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="typeFilter">Type</label>
                    <select id="typeFilter" class="form-select">
                        <option value="">All</option>
                        <option value="debit">Debit</option>
                        <option value="credit">Credit</option>
                    </select>
                </div>
                <div class="col-md-1 d-grid">
                    <button class="btn btn-primary" onclick="reloadLedger()"><i class="fas fa-sync-alt"></i></button>
                </div>
            </div>
            <div class="row g-2 mt-3">
                <div class="col-md-6">
                    <div class="btn-group">
                        <button class="btn btn-outline-secondary btn-sm" onclick="setQuickPeriod('thisMonth')">This
                            Month</button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="setQuickPeriod('lastMonth')">Last
                            Month</button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="setQuickPeriod('thisYear')">This
                            Year</button>
                    </div>
                </div>
                <div class="col-md-6 text-md-end">
                    <small class="text-muted">Period: <span id="periodText">—</span></small>
                </div>
            </div>
        </div>
    </div>

    <!-- Ledger & Trend -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Ledger Entries</h6>
                    <input type="text" class="form-control form-control-sm w-auto" id="searchInput"
                        placeholder="Search description/ref…">
                </div>
                <div class="card-body">
                    <!-- Loading Spinner -->
                    <div id="ledgerLoading" class="text-center py-5" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading ledger entries...</p>
                    </div>

                    <div id="ledgerContent" style="display: none;">
                        <div class="table-responsive">
                            <table id="ledgerTable" class="table table-striped table-hover w-100">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Description</th>
                                        <th>Reference</th>
                                        <th class="text-end">Debit</th>
                                        <th class="text-end">Credit</th>
                                        <th class="text-end">Balance</th>
                                        <th>Customer</th>
                                    </tr>
                                </thead>
                                <tbody id="ledgerBody"></tbody>
                                <tfoot>
                                    <tr class="table-light">
                                        <th colspan="3" class="text-end">Totals:</th>
                                        <th class="text-end" id="totalDebit">$0.00</th>
                                        <th class="text-end" id="totalCredit">$0.00</th>
                                        <th class="text-end" id="endingBalance">$0.00</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- No Data Message -->
                    <div id="noDataMessage" class="text-center py-5" style="display: none;">
                        <i class="fas fa-book fa-3x text-muted mb-3"></i>
                        <h4>No ledger entries found</h4>
                        <p class="text-muted">Try adjusting your date range or filters.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">Balance Trend</h6>
                </div>
                <div class="card-body" style="height:280px;">
                    <canvas id="balanceTrendChart"></canvas>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Key Figures</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between"><span>Opening Balance</span><strong
                            id="openingBalance">$0.00</strong></div>
                    <div class="d-flex justify-content-between text-success mt-1"><span>Total Debits</span><strong
                            id="sidebarTotalDebits">$0.00</strong></div>
                    <div class="d-flex justify-content-between text-danger mt-1"><span>Total Credits</span><strong
                            id="sidebarTotalCredits">$0.00</strong></div>
                    <hr>
                    <div class="d-flex justify-content-between"><span>Closing Balance</span><strong
                            id="closingBalance">$0.00</strong></div>
                </div>
            </div>
        </div>
    </div>

    <!-- New/Edit Entry Modal -->
    <div class="modal fade" id="entryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="entryForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="entryModalTitle">New Ledger Entry</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="entryId" name="id">
                        <div class="mb-3">
                            <label class="form-label">Date *</label>
                            <input type="date" class="form-control" id="entryDate" name="date" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Account *</label>
                            <select class="form-select" id="entryAccountId" name="account_id" required>
                                <option value="">Select Account</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <input type="text" class="form-control" id="entryDescription" name="description"
                                placeholder="e.g., Invoice #1234">
                        </div>
                        <div class="row g-3">
                            <div class="col">
                                <label class="form-label">Debit</label>
                                <input type="number" step="0.01" min="0" class="form-control"
                                    id="entryDebit" name="debit">
                            </div>
                            <div class="col">
                                <label class="form-label">Credit</label>
                                <input type="number" step="0.01" min="0" class="form-control"
                                    id="entryCredit" name="credit">
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="form-label">Reference</label>
                            <input type="text" class="form-control" id="entryReference" name="reference"
                                placeholder="Optional reference #">
                        </div>
                        <small class="text-muted d-block mt-2">Enter either Debit or Credit (not both).</small>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="entrySubmitBtn">
                            <span class="btn-text">Save Entry</span>
                            <span class="spinner-border spinner-border-sm ms-2 d-none" id="entryLoading"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

{{-- summary: @json(route('ledger.summary')), // GET opening/closing & totals & trend
export: @json(route('ledger.export')), // CSV export
store: @json(route('ledger.store')), // POST create entry --}}

@push('scripts')
    <script>
        // ---------- Routes & helpers ----------
        window.LEDGER = {
            routes: {
                index: @json(route('reports.ledger')), // GET entries JSON (supports filters)
                export: @json(route('reports.export')), // Export functionality
            }
        };

        let ledgerData = [];

        // Load ledger data on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadLedgerData();
        });

        function loadLedgerData() {
            const dateFrom = document.getElementById('dateFrom').value || '{{ $dateFrom }}';
            const dateTo = document.getElementById('dateTo').value || '{{ $dateTo }}';

            // Show loading
            document.getElementById('ledgerLoading').style.display = 'block';
            document.getElementById('ledgerContent').style.display = 'none';
            document.getElementById('noDataMessage').style.display = 'none';

            fetch(`${window.LEDGER.routes.index}?date_from=${dateFrom}&date_to=${dateTo}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayLedgerData(data.data);
                    } else {
                        showAlert('error', data.message || 'Failed to load ledger data');
                        document.getElementById('noDataMessage').style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('error', 'Failed to load ledger data');
                    document.getElementById('noDataMessage').style.display = 'block';
                })
                .finally(() => {
                    document.getElementById('ledgerLoading').style.display = 'none';
                });
        }

        function displayLedgerData(data) {
            const tbody = document.getElementById('ledgerBody');
            tbody.innerHTML = '';

            if (!data.transactions || data.transactions.length === 0) {
                document.getElementById('noDataMessage').style.display = 'block';
                return;
            }

            // Update totals
            document.getElementById('totalDebit').textContent = '$' + data.summary.total_debits.toFixed(2);
            document.getElementById('totalCredit').textContent = '$' + data.summary.total_credits.toFixed(2);
            document.getElementById('endingBalance').textContent = '$' + data.summary.closing_balance.toFixed(2);

            // Display transactions
            data.transactions.forEach(transaction => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${transaction.date}</td>
                    <td>${transaction.description}</td>
                    <td>${transaction.reference || '—'}</td>
                    <td class="text-end">${transaction.debit > 0 ? '$' + transaction.debit.toFixed(2) : '—'}</td>
                    <td class="text-end">${transaction.credit > 0 ? '$' + transaction.credit.toFixed(2) : '—'}</td>
                    <td class="text-end">$${transaction.balance.toFixed(2)}</td>
                    <td>${transaction.customer || '—'}</td>
                `;
                tbody.appendChild(row);
            });

            document.getElementById('ledgerContent').style.display = 'block';
        }

        function exportLedger() {
            const dateFrom = document.getElementById('dateFrom').value || '{{ $dateFrom }}';
            const dateTo = document.getElementById('dateTo').value || '{{ $dateTo }}';

            window.open(`${window.LEDGER.routes.export}?type=ledger&date_from=${dateFrom}&date_to=${dateTo}`, '_blank');
        }

        function showAlert(type, message) {
            // Simple alert implementation
            alert(message);
        }

        const fmtCurrency = (n) => '$' + (Number(n || 0)).toFixed(2);
        const fmtDate = (d) => {
            const dt = new Date(d);
            return isNaN(dt) ? '—' : dt.toLocaleDateString();
        };
        const debounce = (fn, ms = 350) => {
            let t;
            return (...a) => {
                clearTimeout(t);
                t = setTimeout(() => fn(...a), ms);
            };
        };
    </script>

    <script>
        let ledgerTable;
        let balanceTrendChart;

        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            initLedgerTable();
            initTrendChart();
            loadAccounts();
            loadSummary(); // opening/closing, totals, trend
            updatePeriodText();

            $('#searchInput').on('keyup', debounce(() => ledgerTable.ajax.reload(), 300));
            $('#dateFrom, #dateTo, #accountFilter, #typeFilter').on('change', () => {
                ledgerTable.ajax.reload();
                loadSummary();
                updatePeriodText();
            });

            // Entry form submit
            $('#entryForm').on('submit', function(e) {
                e.preventDefault();
                toggleEntryLoading(true);

                const id = $('#entryId').val();
                const isUpdate = !!id;
                const url = isUpdate ? `${window.LEDGER.routes.updateBase}/${id}` : window.LEDGER.routes
                    .store;
                const method = isUpdate ? 'PUT' : 'POST';
                const payload = $(this).serialize();

                $.ajax({
                        url,
                        method,
                        data: payload
                    })
                    .done(res => {
                        toggleEntryLoading(false);
                        if (res?.success) {
                            $('#entryModal').modal('hide');
                            showAlert('success', res.message || 'Entry saved');
                            ledgerTable.ajax.reload(null, false);
                            loadSummary();
                            this.reset();
                        } else {
                            showAlert('danger', res?.message || 'Failed to save entry');
                        }
                    })
                    .fail(xhr => {
                        toggleEntryLoading(false);
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON?.errors || {};
                            const msgs = Object.values(errors).flat().join('<br>');
                            showAlert('danger', msgs || 'Validation error');
                        } else {
                            showAlert('danger', 'Failed to save entry');
                        }
                    });
            });
        });

        // ---------- DataTable ----------
        function initLedgerTable() {
            ledgerTable = $('#ledgerTable').DataTable({
                processing: true,
                serverSide: false,
                responsive: true,
                order: [
                    [0, 'asc'],
                    [7, 'asc']
                ], // date asc, then id asc (if server returns hidden id column)
                ajax: {
                    url: window.LEDGER.routes.index,
                    data: function(d) {
                        d.search = $('#searchInput').val();
                        d.date_from = $('#dateFrom').val();
                        d.date_to = $('#dateTo').val();
                        d.account_id = $('#accountFilter').val();
                        d.type = $('#typeFilter').val();
                    },
                    dataSrc: function(json) {
                        return (json?.data?.data) || json?.data || [];
                    },
                    error: function() {
                        $('#ledgerTable tbody').html(
                            '<tr><td colspan="8" class="text-center text-muted">Failed to load entries.</td></tr>'
                        );
                    }
                },
                columns: [{
                        data: 'date',
                        render: d => fmtDate(d)
                    },
                    {
                        data: 'account',
                        render: a => a?.name || a || '—'
                    },
                    {
                        data: 'description',
                        defaultContent: ''
                    },
                    {
                        data: 'debit',
                        className: 'text-end',
                        render: v => v ? `<span class="text-success">${fmtCurrency(v)}</span>` : ''
                    },
                    {
                        data: 'credit',
                        className: 'text-end',
                        render: v => v ? `<span class="text-danger">${fmtCurrency(v)}</span>` : ''
                    },
                    {
                        data: 'running_balance',
                        className: 'text-end',
                        render: (v, _t, row) => {
                            // If backend gives running_balance use it; else compute per page
                            if (v !== undefined && v !== null) return fmtCurrency(v);
                            return '<span class="text-muted">—</span>';
                        }
                    },
                    {
                        data: 'reference',
                        defaultContent: ''
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: (_d, _t, row) => {
                            let html =
                                `
            <a class="btn btn-sm btn-info me-1" href="${window.LEDGER.routes.showBase}/${row.id}" title="View"><i class="fas fa-eye"></i></a>`;
                            @can('manage accounting')
                                html +=
                                    `
            <button class="btn btn-sm btn-warning me-1" onclick="openEditEntry(${row.id})" title="Edit"><i class="fas fa-edit"></i></button>
            <button class="btn btn-sm btn-danger" onclick="deleteEntry(${row.id})" title="Delete"><i class="fas fa-trash"></i></button>`;
                            @endcan
                            return html;
                        }
                    }
                ],
                pageLength: 25,
                drawCallback: function(settings) {
                    // Update footer totals for current filter set (use summary endpoint instead for accuracy)
                    computePageTotals();
                }
            });
        }

        function computePageTotals() {
            let debit = 0,
                credit = 0,
                endBal = 0;
            const data = ledgerTable.rows({
                page: 'current'
            }).data().toArray();
            data.forEach(r => {
                debit += Number(r.debit || 0);
                credit += Number(r.credit || 0);
                endBal = r.running_balance !== undefined ? Number(r.running_balance) : endBal;
            });
            $('#totalDebit').text(fmtCurrency(debit));
            $('#totalCredit').text(fmtCurrency(credit));
            if (endBal) $('#endingBalance').text(fmtCurrency(endBal));
        }

        // ---------- Summary + Trend ----------
        function initTrendChart() {
            const ctx = document.getElementById('balanceTrendChart').getContext('2d');
            balanceTrendChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Balance',
                        data: [],
                        tension: 0.3,
                        fill: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: false
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }

        function loadSummary() {
            $.get(window.LEDGER.routes.summary, {
                date_from: $('#dateFrom').val(),
                date_to: $('#dateTo').val(),
                account_id: $('#accountFilter').val()
            }).done(res => {
                if (!res?.success) return;
                const s = res.data || {};
                $('#openingBalance').text(fmtCurrency(s.opening_balance));
                $('#sidebarTotalDebits').text(fmtCurrency(s.total_debits));
                $('#sidebarTotalCredits').text(fmtCurrency(s.total_credits));
                $('#closingBalance').text(fmtCurrency(s.closing_balance));
                $('#endingBalance').text(fmtCurrency(s.closing_balance));

                // Trend
                const points = s.trend || []; // [{date: 'YYYY-MM-DD', balance: number}, ...]
                balanceTrendChart.data.labels = points.map(p => p.date);
                balanceTrendChart.data.datasets[0].data = points.map(p => Number(p.balance || 0));
                balanceTrendChart.update();
            });
        }

        // ---------- Accounts ----------
        function loadAccounts() {
            $.get(window.LEDGER.routes.accountsSelect, function(res) {
                if (!res?.success) return;
                const list = res.data || [];
                const selFilter = $('#accountFilter');
                const selForm = $('#entryAccountId');
                selFilter.empty().append('<option value="">All Accounts</option>');
                selForm.empty().append('<option value="">Select Account</option>');
                list.forEach(a => {
                    selFilter.append(`<option value="${a.id}">${a.name}</option>`);
                    selForm.append(`<option value="${a.id}">${a.name}</option>`);
                });
            });
        }

        // ---------- Period helpers ----------
        function setQuickPeriod(kind) {
            const today = new Date();
            let from, to;
            switch (kind) {
                case 'thisMonth':
                    from = new Date(today.getFullYear(), today.getMonth(), 1);
                    to = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                    break;
                case 'lastMonth':
                    from = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                    to = new Date(today.getFullYear(), today.getMonth(), 0);
                    break;
                case 'thisYear':
                    from = new Date(today.getFullYear(), 0, 1);
                    to = new Date(today.getFullYear(), 11, 31);
                    break;
                default:
                    from = today;
                    to = today;
            }
            $('#dateFrom').val(from.toISOString().split('T')[0]);
            $('#dateTo').val(to.toISOString().split('T')[0]);
            reloadLedger();
            loadSummary();
            updatePeriodText();
        }

        function updatePeriodText() {
            const f = $('#dateFrom').val() || '—';
            const t = $('#dateTo').val() || '—';
            $('#periodText').text(`${f} to ${t}`);
        }

        function reloadLedger() {
            ledgerTable.ajax.reload();
        }

        // ---------- Entry modal ----------
        function openCreateEntry() {
            $('#entryForm')[0].reset();
            $('#entryId').val('');
            $('#entryModalTitle').text('New Ledger Entry');
            $('#entrySubmitBtn .btn-text').text('Save Entry');
            $('#entryModal').modal('show');
        }

        function openEditEntry(id) {
            $.get(`${window.LEDGER.routes.showBase}/${id}`, function(res) {
                if (!res?.success) return showAlert('danger', res?.message || 'Failed to fetch entry');
                const e = res.data;
                $('#entryId').val(e.id);
                $('#entryDate').val((e.date || '').split('T')[0] || e.date);
                $('#entryAccountId').val(e.account_id);
                $('#entryDescription').val(e.description || '');
                $('#entryDebit').val(e.debit || '');
                $('#entryCredit').val(e.credit || '');
                $('#entryReference').val(e.reference || '');
                $('#entryModalTitle').text('Edit Ledger Entry');
                $('#entrySubmitBtn .btn-text').text('Update Entry');
                $('#entryModal').modal('show');
            });
        }

        function deleteEntry(id) {
            if (!confirm('Delete this entry?')) return;
            $.ajax({
                url: `${window.LEDGER.routes.destroyBase}/${id}`,
                method: 'POST',
                data: {
                    _method: 'DELETE'
                }
            }).done(res => {
                if (res?.success) {
                    showAlert('success', res.message || 'Entry deleted');
                    ledgerTable.ajax.reload(null, false);
                    loadSummary();
                } else {
                    showAlert('danger', res?.message || 'Failed to delete entry');
                }
            }).fail(() => showAlert('danger', 'Failed to delete entry'));
        }

        function toggleEntryLoading(on) {
            $('#entrySubmitBtn').prop('disabled', on);
            $('#entryLoading').toggleClass('d-none', !on);
        }

        // ---------- Export ----------
        function exportLedger() {
            const params = new URLSearchParams({
                date_from: $('#dateFrom').val() || '',
                date_to: $('#dateTo').val() || '',
                account_id: $('#accountFilter').val() || '',
                type: $('#typeFilter').val() || ''
            }).toString();
            window.open(`${window.LEDGER.routes.export}?${params}`, '_blank');
        }

        // ---------- Alerts ----------
        function showAlert(type, message) {
            const id = 'alert-' + Math.random().toString(36).slice(2);
            const html = `
    <div id="${id}" class="alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3"
         role="alert" style="z-index:1080;min-width:260px;">
      ${message}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>`;
            $('body').append(html);
            setTimeout(() => $('#' + id).alert('close'), 4000);
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
