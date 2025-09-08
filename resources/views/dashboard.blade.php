@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
    <!-- Statistics Cards -->
    <div class="row mb-4">
        @if (Auth::user()->isAdmin())
            <!-- Admin Dashboard -->
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6>Total Orders</h6>
                                <h4 id="totalOrders">0</h4>
                                <small id="ordersGrowth">+0% from last month</small>
                            </div>
                            <i class="fas fa-shopping-cart fa-3x opacity-50"></i>
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
                                <h4 id="totalRevenue">$0</h4>
                                <small id="revenueGrowth">+0% from last month</small>
                            </div>
                            <i class="fas fa-dollar-sign fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6>Products</h6>
                                <h4 id="totalProducts">0</h4>
                                <small id="lowStockAlert">0 low stock items</small>
                            </div>
                            <i class="fas fa-box fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6>Pending Payments</h6>
                                <h4 id="pendingPayments">$0</h4>
                                <small id="overdueInstallments">0 overdue</small>
                            </div>
                            <i class="fas fa-credit-card fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- Customer Dashboard -->
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6>My Orders</h6>
                                <h4 id="myOrders">0</h4>
                                <small id="recentOrders">0 this month</small>
                            </div>
                            <i class="fas fa-shopping-cart fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6>Total Spent</h6>
                                <h4 id="totalSpent">$0</h4>
                                <small id="monthlySpent">$0 this month</small>
                            </div>
                            <i class="fas fa-dollar-sign fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6>Pending Installments</h6>
                                <h4 id="myPendingInstallments">$0</h4>
                                <small id="nextDueDate">Next due: N/A</small>
                            </div>
                            <i class="fas fa-calendar-alt fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @if (Auth::user()->isAdmin())
        <!-- Admin Charts -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Sales Overview (Last 30 Days)</h6>
                    </div>
                    <div class="card-body" style="height: 260px;">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Payment Types Distribution</h6>
                    </div>
                    <div class="card-body" style="height: 260px;">
                        <canvas id="paymentTypesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Orders and Low Stock Items -->
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Recent Orders</h6>
                        <a href="{{ route('orders.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm" id="recentOrdersTable">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Customer</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody id="recentOrdersBody">
                                    <!-- Recent orders will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Low Stock Alert</h6>
                        <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-warning">Manage</a>
                    </div>
                    <div class="card-body">
                        <div id="lowStockItems">
                            <!-- Low stock items will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Customer Section -->
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">My Recent Orders</h6>
                        <a href="{{ route('orders.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm" id="myOrdersTable">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Items</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody id="myOrdersBody">
                                    <!-- My orders will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Upcoming Installments</h6>
                    </div>
                    <div class="card-body">
                        <div id="upcomingInstallments">
                            <!-- Upcoming installments will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Quick Actions -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @if (Auth::user()->isAdmin())
                            <div class="col-md-3">
                                <a href="{{ route('categories.index') }}" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-tags fa-2x d-block mb-2"></i>
                                    Manage Categories
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('products.index') }}" class="btn btn-outline-success w-100">
                                    <i class="fas fa-box fa-2x d-block mb-2"></i>
                                    Manage Products
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('reports.profit-loss') }}" class="btn btn-outline-info w-100">
                                    <i class="fas fa-chart-line fa-2x d-block mb-2"></i>
                                    View Reports
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('payments.index') }}" class="btn btn-outline-warning w-100">
                                    <i class="fas fa-credit-card fa-2x d-block mb-2"></i>
                                    Manage Payments
                                </a>
                            </div>
                        @else
                            <div class="col-md-4">
                                <a href="{{ route('products.index') }}" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-shopping-bag fa-2x d-block mb-2"></i>
                                    Browse Products
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="{{ route('orders.index') }}" class="btn btn-outline-success w-100">
                                    <i class="fas fa-list fa-2x d-block mb-2"></i>
                                    My Orders
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="{{ route('payments.pending-installments') }}"
                                    class="btn btn-outline-warning w-100">
                                    <i class="fas fa-calendar-check fa-2x d-block mb-2"></i>
                                    Pay Installments
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- Provide your own Chart.js include (via Vite/Mix or CDN) before this block --}}
    <script>
        window.DASHBOARD = {
            isAdmin: @json(Auth::user()->isAdmin()),
            routes: {
                adminStats: @json(route('dashboard.admin-stats')),
                customerStats: @json(route('dashboard.customer-stats')),
                recentOrders: @json(route('dashboard.recent-orders')),
                lowStock: @json(route('dashboard.low-stock')),
                salesAnalytics: @json(route('dashboard.sales-analytics')),
                pendingInstallments: @json(route('dashboard.pending-installments')),
                orderShow: @json(url('/orders')), // append /{id}
                payInstallmentBase: @json(url('/payments/installment')), // append /{id}
            }
        };
    </script>

    <script>
        let salesChart, paymentTypesChart;

        const fmtCurrency = (n) =>
            new Intl.NumberFormat(undefined, {
                style: 'currency',
                currency: 'USD',
                maximumFractionDigits: 2
            })
            .format(Number(n || 0));

        const fmtDate = (d) => {
            const dt = new Date(d);
            return isNaN(dt) ? '—' : dt.toLocaleDateString();
        };

        $(function() {
            loadDashboardData();
            if (window.DASHBOARD.isAdmin) initAdminCharts();
        });

        function loadDashboardData() {
            if (window.DASHBOARD.isAdmin) {
                $.get(window.DASHBOARD.routes.adminStats, (res) => {
                    if (res?.success) {
                        const s = res.data;
                        $('#totalOrders').text(s.total_orders ?? 0);
                        $('#totalRevenue').text(fmtCurrency(s.total_revenue));
                        $('#ordersGrowth').text(`${s.growth_orders ?? 0}% from last month`);
                        $('#revenueGrowth').text(`${s.growth_revenue ?? 0}% from last month`);
                    }
                });

                $.get(window.DASHBOARD.routes.lowStock, (res) => {
                    if (res?.success) {
                        const d = res.data;
                        $('#totalProducts').text(d?.summary?.total_products ?? 0);
                        $('#lowStockAlert').text(`${d?.summary?.low_stock_items ?? 0} low stock items`);
                        renderLowStockItems((d?.products || []).slice(0, 5));
                    }
                });

                $.get(window.DASHBOARD.routes.recentOrders, {
                    limit: 5
                }, (res) => {
                    if (res?.success) renderRecentOrders(res.data || []);
                });

            } else {
                $.get(window.DASHBOARD.routes.customerStats, (res) => {
                    if (res?.success) {
                        const s = res.data;
                        $('#myOrders').text(s.total_orders ?? 0);
                        $('#totalSpent').text(fmtCurrency(s.total_revenue));
                        // Optionally compute current-month spent server-side and place here:
                        // $('#monthlySpent').text(fmtCurrency(s.monthly_revenue));
                    }
                });

                $.get(window.DASHBOARD.routes.recentOrders, {
                    limit: 5
                }, (res) => {
                    if (res?.success) renderMyOrders(res.data || []);
                });

                $.get(window.DASHBOARD.routes.pendingInstallments, (res) => {
                    if (res?.success) {
                        const arr = res.data || [];
                        const totalPending = arr.reduce((sum, i) => sum + Number(i.total_amount || 0), 0);
                        $('#myPendingInstallments').text(fmtCurrency(totalPending));
                        if (arr.length) {
                            $('#nextDueDate').text('Next due: ' + fmtDate(arr[0].due_date));
                            renderUpcomingInstallments(arr.slice(0, 5));
                        }
                    }
                });
            }
        }

        /* ===== Admin renderers ===== */
        function renderRecentOrders(orders) {
            let html = '';
            if (!orders.length) {
                html = '<tr><td colspan="5" class="text-center text-muted">No recent orders</td></tr>';
            } else {
                html = orders.map(o => {
                    const statusClass = ({
                        pending: 'warning',
                        processing: 'info',
                        shipped: 'primary',
                        delivered: 'success',
                        cancelled: 'danger'
                    })[o.status] || 'secondary';
                    return `
        <tr>
          <td><a href="${window.DASHBOARD.routes.orderShow}/${o.id}">${o.order_number || o.id}</a></td>
          <td>${o.user?.name ?? 'Unknown'}</td>
          <td>${fmtCurrency(o.total_amount)}</td>
          <td><span class="badge bg-${statusClass}">${o.status ?? '—'}</span></td>
          <td>${fmtDate(o.created_at)}</td>
        </tr>`;
                }).join('');
            }
            $('#recentOrdersBody').html(html);
        }

        function renderLowStockItems(products) {
            let html = '';
            if (!products.length) {
                html = '<p class="text-muted">All products are well stocked!</p>';
            } else {
                html = products.map(p => `
      <div class="d-flex justify-content-between align-items-center mb-2">
        <div>
          <strong>${p.name ?? 'Unnamed'}</strong><br>
          <small class="text-muted">${p.sku ?? ''}</small>
        </div>
        <span class="badge bg-warning">${p.stock_quantity ?? 0} left</span>
      </div>`).join('');
            }
            $('#lowStockItems').html(html);
        }

        function initAdminCharts() {
            if (typeof Chart === 'undefined') return;

            const salesEl = document.getElementById('salesChart');
            const payEl = document.getElementById('paymentTypesChart');

            if (salesChart) {
                salesChart.destroy();
            }
            if (paymentTypesChart) {
                paymentTypesChart.destroy();
            }

            salesChart = new Chart(salesEl.getContext('2d'), {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Sales',
                        data: [],
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            paymentTypesChart = new Chart(payEl.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Instant', 'Installment'],
                    datasets: [{
                        data: [0, 0]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });

            $.get(window.DASHBOARD.routes.salesAnalytics, (res) => {
                if (!res?.success) return;
                const d = res.data || {};
                const periods = (d.sales_by_period || []).map(x => x.period);
                const totals = (d.sales_by_period || []).map(x => Number(x.total_sales || 0));

                salesChart.data.labels = periods;
                salesChart.data.datasets[0].data = totals;
                salesChart.update();

                const instant = (d.payment_type_stats || []).find(x => x.payment_type === 'instant')?.order_count ||
                    0;
                const installment = (d.payment_type_stats || []).find(x => x.payment_type === 'installment')
                    ?.order_count || 0;
                paymentTypesChart.data.datasets[0].data = [instant, installment];
                paymentTypesChart.update();
            });
        }

        /* ===== Customer renderers ===== */
        function renderMyOrders(orders) {
            let html = '';
            if (!orders.length) {
                html = '<tr><td colspan="5" class="text-center text-muted">No recent orders</td></tr>';
            } else {
                html = orders.map(o => {
                    const statusClass = ({
                        pending: 'warning',
                        processing: 'info',
                        shipped: 'primary',
                        delivered: 'success',
                        cancelled: 'danger'
                    })[o.status] || 'secondary';
                    const itemsCount = Array.isArray(o.order_items) ? o.order_items.length : (o.items_count ?? 0);
                    return `
        <tr>
          <td><a href="${window.DASHBOARD.routes.orderShow}/${o.id}">${o.order_number || o.id}</a></td>
          <td>${itemsCount} items</td>
          <td>${fmtCurrency(o.total_amount)}</td>
          <td><span class="badge bg-${statusClass}">${o.status ?? '—'}</span></td>
          <td>${fmtDate(o.created_at)}</td>
        </tr>`;
                }).join('');
            }
            $('#myOrdersBody').html(html);
        }

        function renderUpcomingInstallments(list) {
            let html = '';
            if (!list.length) {
                html = '<p class="text-muted">No upcoming installments</p>';
            } else {
                html = list.map(inst => {
                    const due = new Date(inst.due_date);
                    const isOverdue = !isNaN(due) && (due < new Date());
                    const btnClass = isOverdue ? 'btn-danger' : 'btn-warning';
                    return `
        <div class="d-flex justify-content-between align-items-center mb-2">
          <div>
            <strong>Installment #${inst.installment_number}</strong><br>
            <small class="text-muted">Order: ${inst.order?.order_number ?? '—'}</small><br>
            <small class="text-muted">Due: ${fmtDate(inst.due_date)}</small>
          </div>
          <div class="text-end">
            <div class="fw-bold">${fmtCurrency(inst.total_amount)}</div>
            <a class="btn btn-sm ${btnClass}" href="${window.DASHBOARD.routes.payInstallmentBase}/${inst.id}">
              ${isOverdue ? 'Pay Now (Overdue)' : 'Pay Now'}
            </a>
          </div>
        </div>`;
                }).join('');
            }
            $('#upcomingInstallments').html(html);
        }
    </script>
@endpush
