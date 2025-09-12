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

        /* Modal Scroll Enhancements */
        .modal-dialog-scrollable .modal-body {
            overflow-y: auto;
            max-height: calc(100vh - 200px);
        }

        .modal-dialog-scrollable .modal-content {
            max-height: 90vh;
        }

        /* Custom Scrollbar for Modals */
        .modal-dialog-scrollable .modal-body::-webkit-scrollbar {
            width: 8px;
        }

        .modal-dialog-scrollable .modal-body::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .modal-dialog-scrollable .modal-body::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

        .modal-dialog-scrollable .modal-body::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        /* Firefox scrollbar styling */
        .modal-dialog-scrollable .modal-body {
            scrollbar-width: thin;
            scrollbar-color: #c1c1c1 #f1f1f1;
        }

        /* Ensure modal content doesn't overflow */
        .modal-body {
            word-wrap: break-word;
            overflow-wrap: break-word;
            padding-right: 15px; /* Space for scrollbar */
        }

        /* Large modal scroll improvements */
        .modal-xl .modal-dialog-scrollable .modal-body {
            max-height: calc(100vh - 150px);
        }

        .modal-lg .modal-dialog-scrollable .modal-body {
            max-height: calc(100vh - 180px);
        }

        /* Default modal scroll improvements */
        .modal-dialog-scrollable .modal-body {
            padding-right: 20px; /* Extra space for custom scrollbar */
        }

        /* Smooth scrolling for better UX */
        .modal-dialog-scrollable .modal-body {
            scroll-behavior: smooth;
        }

        /* Handle pre elements in modals */
        .modal-body pre {
            white-space: pre-wrap;
            word-break: break-all;
            overflow-wrap: break-word;
        }

        /* Handle long URLs and text */
        .modal-body .text-break {
            word-break: break-word !important;
        }

        /* Form elements in scrollable modals */
        .modal-dialog-scrollable .modal-body .form-control,
        .modal-dialog-scrollable .modal-body .form-select {
            margin-bottom: 1rem;
        }

        /* Ensure buttons don't get cut off */
        .modal-dialog-scrollable .modal-footer {
            flex-shrink: 0;
            border-top: 1px solid #dee2e6;
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
                                <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}"
                                    href="{{ route('reports.index') }}">
                                    <i class="fas fa-chart-bar me-2"></i>
                                    Reports
                                </a>
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

        // Enhanced Modal Scroll Functionality
        $(document).ready(function() {
            // Handle modal show events for better scroll experience
            $('.modal').on('shown.bs.modal', function() {
                var modal = $(this);
                var modalBody = modal.find('.modal-body');

                // Reset scroll position to top when modal opens
                if (modalBody.length) {
                    modalBody.scrollTop(0);
                }

                // Focus on first input if available
                var firstInput = modal.find('input[type="text"], input[type="email"], textarea, select').first();
                if (firstInput.length) {
                    setTimeout(function() {
                        firstInput.focus();
                    }, 100);
                }
            });

            // Handle modal scroll events for dynamic content loading
            $('.modal-dialog-scrollable .modal-body').on('scroll', function() {
                var modalBody = $(this);
                var scrollTop = modalBody.scrollTop();
                var scrollHeight = modalBody[0].scrollHeight;
                var height = modalBody.height();

                // Trigger custom event when near bottom (for infinite scroll if needed)
                if (scrollTop + height >= scrollHeight - 50) {
                    modalBody.trigger('modal-scroll-bottom');
                }
            });
        });

        // Utility functions for modal scrolling
        window.scrollModalToTop = function(modalId) {
            var modal = $('#' + modalId);
            var modalBody = modal.find('.modal-body');
            if (modalBody.length) {
                modalBody.animate({ scrollTop: 0 }, 300);
            }
        };

        window.scrollModalToBottom = function(modalId) {
            var modal = $('#' + modalId);
            var modalBody = modal.find('.modal-body');
            if (modalBody.length) {
                var scrollHeight = modalBody[0].scrollHeight;
                modalBody.animate({ scrollTop: scrollHeight }, 300);
            }
        };
    </script>

    @stack('scripts')
</body>

</html>
