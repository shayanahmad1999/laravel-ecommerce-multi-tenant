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
                <table class="table table-striped table-hover align-middle">
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
                        @forelse ($payments as $p)
                            @php
                                $statusMap = [
                                    'pending' => 'warning',
                                    'processing' => 'info',
                                    'completed' => 'success',
                                    'failed' => 'danger',
                                    'refunded' => 'secondary',
                                    'cancelled' => 'dark',
                                    'overdue' => 'danger',
                                ];
                                $statusClass = $statusMap[$p->status] ?? 'secondary';

                                $isNegative = $p->amount < 0;
                                $amountAbs = number_format(abs($p->amount), 2);
                                $amountHtml = $isNegative
                                    ? "<span class=\"text-danger\">(${$amountAbs})</span>"
                                    : '$' . number_format($p->amount, 2);
                            @endphp

                            <tr>
                                <td><strong>{{ $p->payment_number }}</strong></td>
                                <td><span class="text-muted">{{ $p->transaction_id ?? '—' }}</span></td>
                                <td>
                                    @if ($p->order)
                                        <a href="{{ url('/orders/' . $p->order->id) }}" class="text-decoration-none">
                                            {{ $p->order->order_number }}
                                        </a>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                @if (Auth::user()->isAdmin())
                                    <td>
                                        @if ($p->user)
                                            <div>{{ $p->user->name }}</div>
                                            <small class="text-muted">{{ $p->user->email }}</small>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                @endif
                                <td>
                                    @if ($p->payment_type === 'installment')
                                        <span class="badge bg-info">Installment</span>
                                    @elseif($p->payment_type === 'refund')
                                        <span class="badge bg-secondary">Refund</span>
                                    @else
                                        <span class="badge bg-success">Full</span>
                                    @endif
                                </td>
                                <td>{{ Str::title(str_replace('_', ' ', $p->payment_method)) }}</td>
                                <td class="text-end">{!! $amountHtml !!}</td>
                                <td><span class="badge bg-{{ $statusClass }}">{{ ucfirst($p->status) }}</span></td>
                                <td>{{ optional($p->created_at)->format('Y-m-d') }}</td>
                                <td>
                                    <div class="btn-group">
                                        {{-- If you have a payments.show route, switch to it; controller supports it --}}
                                        <a href="{{ url('/payments/' . $p->id) }}" class="btn btn-sm btn-info"
                                            title="Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if ($p->order)
                                            <a href="{{ url('/orders/' . $p->order->id) }}"
                                                class="btn btn-sm btn-outline-primary" title="View Order">
                                                <i class="fas fa-receipt"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ Auth::user()->isAdmin() ? 10 : 9 }}" class="text-center text-muted py-4">
                                    No payments found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($payments->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="small text-muted">
                        Showing {{ $payments->firstItem() }}–{{ $payments->lastItem() }} of {{ $payments->total() }}
                    </div>
                    <div>
                        {{ $payments->appends(request()->query())->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Submit filters on Enter in search field
        document.getElementById('search').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('filtersForm').submit();
            }
        });

        // Optional: auto-submit on dropdown change
        ['status', 'payment_type', 'payment_method'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('change', () => document.getElementById('filtersForm').submit());
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
