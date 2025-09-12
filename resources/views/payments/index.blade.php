@extends('layouts.app')

@section('title', 'Payments')
@section('page-title', 'Payments')

@section('page-actions')
    <div class="btn-group">
        <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-sync-alt me-2"></i>Refresh
        </a>
        <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
            <i class="fas fa-print me-2"></i>Print
        </button>
    </div>
@endsection

@section('content')
    {{-- Filters --}}
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0">Filters</h5>
        </div>
        <div class="card-body">
            <form id="filtersForm" method="GET" action="{{ route('payments.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label" for="search">Search</label>
                        <input type="text" name="search" id="search" value="{{ request('search') }}"
                            class="form-control" placeholder="Payment #, Transaction ID, Order #">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label" for="status">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">All</option>
                            @php
                                $statuses = [
                                    'pending',
                                    'processing',
                                    'completed',
                                    'failed',
                                    'refunded',
                                    'cancelled',
                                    'overdue',
                                ];
                            @endphp
                            @foreach ($statuses as $st)
                                <option value="{{ $st }}" @selected(request('status') === $st)>{{ ucfirst($st) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label" for="payment_type">Payment Type</label>
                        <select name="payment_type" id="payment_type" class="form-select">
                            <option value="">All</option>
                            {{-- Controller uses 'full' and 'installment' --}}
                            <option value="full" @selected(request('payment_type') === 'full')>Full (Instant)</option>
                            <option value="installment" @selected(request('payment_type') === 'installment')>Installment</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label" for="payment_method">Payment Method</label>
                        <select name="payment_method" id="payment_method" class="form-select">
                            <option value="">All</option>
                            @php
                                $methods = ['credit_card', 'debit_card', 'bank_transfer', 'digital_wallet', 'cash'];
                            @endphp
                            @foreach ($methods as $m)
                                <option value="{{ $m }}" @selected(request('payment_method') === $m)>
                                    {{ Str::title(str_replace('_', ' ', $m)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-2"></i>Apply
                    </button>
                    <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- List --}}
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Payments List</h5>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table id="paymentsTable" class="table table-striped table-hover align-middle w-100">
                    <thead>
                        <tr>
                            <th>Payment #</th>
                            <th>Transaction ID</th>
                            <th>Order</th>
                            @if (Auth::user()->isAdmin())
                                <th>Customer</th>
                            @endif
                            <th>Type</th>
                            <th>Method</th>
                            <th class="text-end">Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th style="width: 110px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Payments will be loaded via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // ---------- Routes & helpers ----------
        window.PAYMENTS = {
            routes: {
                index: @json(route('payments.index')),
            }
        };

        const fmtCurrency = (n) => {
            const num = Number(n || 0);
            return num < 0
                ? `<span class="text-danger">($${Math.abs(num).toFixed(2)})</span>`
                : '$' + num.toFixed(2);
        };

        const fmtDate = (d) => {
            const dt = new Date(d);
            return isNaN(dt) ? '—' : dt.toLocaleDateString();
        };

        $(document).ready(function() {
            // CSRF header for all AJAX
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Initialize DataTable
            const table = $('#paymentsTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: window.PAYMENTS.routes.index,
                    type: 'GET',
                    data: function(d) {
                        // Add custom search parameters
                        d.search = $('#search').val();
                        d.status = $('#status').val();
                        d.payment_type = $('#payment_type').val();
                        d.payment_method = $('#payment_method').val();
                        d.date_from = $('#dateFrom').val();
                        d.date_to = $('#dateTo').val();
                    },
                    dataSrc: function(json) {
                        // DataTables expects the data array directly
                        return json.data || [];
                    },
                    error: function(xhr, error, thrown) {
                        console.error('DataTables error:', error, thrown);
                        $('#paymentsTable tbody').html(
                            '<tr><td colspan="{{ Auth::user()->isAdmin() ? 10 : 9 }}" class="text-center text-danger">Failed to load payments.</td></tr>'
                        );
                    }
                },
                columns: [
                    {
                        data: 'payment_number',
                        render: function(data) {
                            return `<strong>${data || '—'}</strong>`;
                        }
                    },
                    {
                        data: 'transaction_id',
                        render: function(data) {
                            return `<span class="text-muted">${data || '—'}</span>`;
                        }
                    },
                    {
                        data: 'order',
                        render: function(data) {
                            if (data && data.order_number) {
                                return `<a href="/orders/${data.id}" class="text-decoration-none">${data.order_number}</a>`;
                            }
                            return '<span class="text-muted">—</span>';
                        }
                    },
                    @if (Auth::user()->isAdmin())
                    {
                        data: 'user',
                        render: function(data) {
                            if (data) {
                                return `<div>${data.name || '—'}</div><small class="text-muted">${data.email || ''}</small>`;
                            }
                            return '<span class="text-muted">—</span>';
                        }
                    },
                    @endif
                    {
                        data: 'payment_type',
                        render: function(data) {
                            if (data === 'installment') {
                                return '<span class="badge bg-info">Installment</span>';
                            } else if (data === 'refund') {
                                return '<span class="badge bg-secondary">Refund</span>';
                            } else {
                                return '<span class="badge bg-success">Full</span>';
                            }
                        }
                    },
                    {
                        data: 'payment_method',
                        render: function(data) {
                            return data ? data.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) : '—';
                        }
                    },
                    {
                        data: 'amount',
                        className: 'text-end',
                        render: function(data) {
                            return fmtCurrency(data);
                        }
                    },
                    {
                        data: 'status',
                        render: function(data) {
                            const statusMap = {
                                'pending': 'warning',
                                'processing': 'info',
                                'completed': 'success',
                                'failed': 'danger',
                                'refunded': 'secondary',
                                'cancelled': 'dark',
                                'overdue': 'danger'
                            };
                            const statusClass = statusMap[data] || 'secondary';
                            return `<span class="badge bg-${statusClass}">${data ? data.charAt(0).toUpperCase() + data.slice(1) : '—'}</span>`;
                        }
                    },
                    {
                        data: 'created_at',
                        render: function(data) {
                            return fmtDate(data);
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            let actions = `<a href="/payments/${row.id}" class="btn btn-sm btn-info me-1" title="Details"><i class="fas fa-eye"></i></a>`;
                            if (row.order) {
                                actions += `<a href="/orders/${row.order.id}" class="btn btn-sm btn-outline-primary" title="View Order"><i class="fas fa-receipt"></i></a>`;
                            }
                            return actions;
                        }
                    }
                ],
                pageLength: 15,
                order: [[8, 'desc']], // Order by date descending
                language: {
                    emptyTable: "No payments found"
                }
            });

            // Submit filters on Enter in search field
            $('#search').on('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    table.ajax.reload();
                }
            });

            // Auto-submit on dropdown change
            $('#status, #payment_type, #payment_method').on('change', function() {
                table.ajax.reload();
            });

            // Date range filters
            $('#dateFrom, #dateTo').on('change', function() {
                table.ajax.reload();
            });

            // Refresh button
            $('.btn-outline-secondary').first().on('click', function(e) {
                e.preventDefault();
                table.ajax.reload();
            });
        });
    </script>
@endpush

@push('styles')
    <style>
        @media print {

            .sidebar,
            .page-actions,
            .card-header,
            .btn,
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
        }
    </style>
@endpush
