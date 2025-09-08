<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Laravel E-commerce Multi-Tenant')</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <!-- Custom CSS -->
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
        }

        .sidebar .nav-link {
            color: #adb5bd;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: #fff;
            background-color: #495057;
        }

        .main-content {
            padding: 20px;
        }

        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }

        .btn-action {
            margin-right: 5px;
        }

        .loading {
            display: none;
        }

        .table-responsive {
            border-radius: 0.375rem;
        }
    </style>
    @stack('styles')
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-dark sidebar">
                <div class="position-sticky pt-3">
                    <div class="text-center text-white mb-4">
                        <h5>{{ config('app.name') }}</h5>
                        @php($currentTenant = app()->bound('currentTenant') ? app('currentTenant') : null)
                        @if ($currentTenant)
                            <small class="text-muted">{{ $currentTenant->name }}</small>
                        @endif
                    </div>

                    <ul class="nav nav-pills flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                                href="{{ route('dashboard') }}">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                Dashboard
                            </a>
                        </li>

                        @can('manage categories')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}"
                                    href="{{ route('categories.index') }}">
                                    <i class="fas fa-tags me-2"></i>
                                    Categories
                                </a>
                            </li>
                        @endcan

                        @can('manage products')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}"
                                    href="{{ route('products.index') }}">
                                    <i class="fas fa-box me-2"></i>
                                    Products
                                </a>
                            </li>
                        @endcan

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('orders.*') ? 'active' : '' }}"
                                href="{{ route('orders.index') }}">
                                <i class="fas fa-shopping-cart me-2"></i>
                                Orders
                            </a>
                        </li>

                        @can('manage payments')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('payments.*') ? 'active' : '' }}"
                                    href="{{ route('payments.index') }}">
                                    <i class="fas fa-credit-card me-2"></i>
                                    Payments
                                </a>
                            </li>
                        @endcan

                        @can('view reports')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="#"
                                    data-bs-toggle="collapse" data-bs-target="#reportsSubmenu">
                                    <i class="fas fa-chart-bar me-2"></i>
                                    Reports
                                </a>
                                <div class="collapse {{ request()->routeIs('reports.*') ? 'show' : '' }}"
                                    id="reportsSubmenu">
                                    <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
                                        @if (Route::has('reports.ledger'))
                                            <li><a href="{{ route('reports.ledger') }}"
                                                    class="nav-link text-white rounded ps-4">Ledger</a></li>
                                        @endif
                                        @if (Route::has('reports.balance-sheet'))
                                            <li><a href="{{ route('reports.balance-sheet') }}"
                                                    class="nav-link text-white rounded ps-4">Balance Sheet</a></li>
                                        @endif
                                        @if (Route::has('reports.profit-loss'))
                                            <li><a href="{{ route('reports.profit-loss') }}"
                                                    class="nav-link text-white rounded ps-4">Profit & Loss</a></li>
                                        @endif
                                        @if (Route::has('reports.sales-analytics'))
                                            <li><a href="{{ route('reports.sales-analytics') }}"
                                                    class="nav-link text-white rounded ps-4">Sales Analytics</a></li>
                                        @endif
                                        @if (Route::has('reports.inventory'))
                                            <li><a href="{{ route('reports.inventory') }}"
                                                    class="nav-link text-white rounded ps-4">Inventory</a></li>
                                        @endif
                                        @if (Route::has('reports.customers'))
                                            <li><a href="{{ route('reports.customers') }}"
                                                    class="nav-link text-white rounded ps-4">Customer Analytics</a></li>
                                        @endif
                                        @if (Route::has('reports.export'))
                                            <li><a href="{{ route('reports.export') }}"
                                                    class="nav-link text-white rounded ps-4">Export</a></li>
                                        @endif
                                    </ul>
                                </div>
                            </li>
                        @endcan

                        @if (Auth::check() && Auth::user()->isAdmin())
                            <li class="nav-item mt-3">
                                <span class="nav-link text-uppercase text-white-50 small">Admin</span>
                            </li>
                            @if (Route::has('admin.tenants.index'))
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('admin.tenants.*') ? 'active' : '' }}"
                                        href="{{ route('admin.tenants.index') }}">
                                        <i class="fas fa-building me-2"></i>
                                        Tenants
                                    </a>
                                </li>
                            @endif
                            @if (Route::has('admin.users.index'))
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
                                        href="{{ route('admin.users.index') }}">
                                        <i class="fas fa-users me-2"></i>
                                        Users
                                    </a>
                                </li>
                            @endif
                            @if (Route::has('admin.roles.index'))
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}"
                                        href="{{ route('admin.roles.index') }}">
                                        <i class="fas fa-user-shield me-2"></i>
                                        Roles
                                    </a>
                                </li>
                            @endif
                            @if (Route::has('admin.permissions.index'))
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}"
                                        href="{{ route('admin.permissions.index') }}">
                                        <i class="fas fa-key me-2"></i>
                                        Permissions
                                    </a>
                                </li>
                            @endif
                        @endif

                        <li class="nav-item mt-3">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="nav-link btn btn-link text-start w-100" type="submit">
                                    <i class="fas fa-sign-out-alt me-2"></i>
                                    Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <!-- Header -->
                <div
                    class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">@yield('page-title', 'Dashboard')</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        @yield('page-actions')
                    </div>
                </div>

                <!-- Flash messages -->
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Page content -->
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Loading overlay -->
    <div id="loadingOverlay"
        class="loading position-fixed top-0 start-0 w-100 h-100 justify-content-center align-items-center"
        style="display: none; background-color: rgba(0,0,0,0.5); z-index: 9999;">
        <div class="spinner-border text-light" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Global AJAX setup -->
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Global loading functions
        function showLoading() {
            $('#loadingOverlay').addClass('d-flex').show();
        }

        function hideLoading() {
            $('#loadingOverlay').removeClass('d-flex').hide();
        }

        // Global AJAX error handler
        $(document).ajaxError(function(event, xhr, settings, error) {
            hideLoading();
            if (xhr.status === 422) {
                // Validation errors
                const errors = xhr.responseJSON.errors;
                let errorMessage = '';
                for (const field in errors) {
                    errorMessage += errors[field].join('<br>') + '<br>';
                }
                showAlert('danger', errorMessage);
            } else {
                showAlert('danger', 'An error occurred: ' + (xhr.responseJSON?.message || error));
            }
        });

        // Ensure overlay is hidden on load and after any ajax completes
        $(function() {
            hideLoading();
            $(document).ajaxStop(function() {
                hideLoading();
            });
        });

        // Show alert function
        function showAlert(type, message) {
            const alert = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            $('.main-content').prepend(alert);
        }

        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            $('.alert').alert('close');
        }, 5000);
    </script>

    @stack('scripts')
</body>

</html>
