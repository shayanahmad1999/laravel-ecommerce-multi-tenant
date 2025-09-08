@extends('layouts.app')

@section('title', 'Orders Management')
@section('page-title', 'Orders')

@section('page-actions')
    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#cartModal" onclick="loadCart()">
        <i class="fas fa-shopping-cart me-2"></i>
        Shopping Cart <span id="cartCount" class="badge bg-light text-dark">0</span>
    </button>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#productSelectionModal"
        onclick="loadProductsForOrder()">
        <i class="fas fa-plus me-2"></i>New Order
    </button>
@endsection

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Order Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6>Total Orders</h6>
                            <h4 id="totalOrders">0</h4>
                        </div>
                        <i class="fas fa-shopping-cart fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6>Pending</h6>
                            <h4 id="pendingOrders">0</h4>
                        </div>
                        <i class="fas fa-clock fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6>Processing</h6>
                            <h4 id="processingOrders">0</h4>
                        </div>
                        <i class="fas fa-cog fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6>Delivered</h6>
                            <h4 id="deliveredOrders">0</h4>
                        </div>
                        <i class="fas fa-check fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6>Cancelled</h6>
                            <h4 id="cancelledOrders">0</h4>
                        </div>
                        <i class="fas fa-times fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-dark text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6>Revenue</h6>
                            <h4 id="totalRevenue">$0</h4>
                        </div>
                        <i class="fas fa-dollar-sign fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Orders List -->
    <div class="card">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0">Orders List</h5>
                </div>
                <div class="col-auto">
                    <div class="row g-2">
                        <div class="col">
                            <input type="text" class="form-control" id="orderSearchInput" placeholder="Search orders...">
                        </div>
                        <div class="col">
                            <select class="form-select" id="orderStatusFilter">
                                <option value="">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="processing">Processing</option>
                                <option value="shipped">Shipped</option>
                                <option value="delivered">Delivered</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="col">
                            <select class="form-select" id="paymentTypeFilter">
                                <option value="">All Payment Types</option>
                                <option value="instant">Instant</option>
                                <option value="installment">Installment</option>
                            </select>
                        </div>
                        <div class="col">
                            <input type="date" class="form-control" id="dateFrom" placeholder="From Date">
                        </div>
                        <div class="col">
                            <input type="date" class="form-control" id="dateTo" placeholder="To Date">
                        </div>
                        <div class="col">
                            <button type="button" class="btn btn-outline-secondary" onclick="refreshOrdersTable()"
                                title="Refresh">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="ordersTable" class="table table-striped table-hover w-100">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            @if (Auth::user()->isAdmin())
                                <th>Customer</th>
                            @endif
                            <th>Items</th>
                            <th>Total</th>
                            <th>Payment Type</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th style="width: 160px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Orders will be loaded via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Shopping Cart Modal -->
    <div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cartModalLabel">Shopping Cart</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="cartItems"><!-- Cart items --></div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6 offset-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Cart Total:</strong></td>
                                    <td class="text-end"><strong id="cartTotal">$0.00</strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="clearCart()">Clear Cart</button>
                    <button type="button" class="btn btn-warning" data-bs-dismiss="modal">Continue Shopping</button>
                    <button type="button" class="btn btn-primary" onclick="proceedToCheckout()" id="checkoutBtn"
                        disabled>Proceed to Checkout</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Selection Modal -->
    <div class="modal fade" id="productSelectionModal" tabindex="-1" aria-labelledby="productSelectionModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="productSelectionModalLabel">Add Products to Cart</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="productSearch"
                                placeholder="Search products...">
                        </div>
                        <div class="col-md-6">
                            <select class="form-select" id="productCategoryFilter">
                                <option value="">All Categories</option>
                            </select>
                        </div>
                    </div>
                    <div id="productsList" class="row g-3"><!-- Products --></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Checkout Modal -->
    <div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="checkoutForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="checkoutModalLabel">Checkout</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Order Summary -->
                        <div class="card mb-3">
                            <div class="card-header"><strong>Order Summary</strong></div>
                            <div class="card-body" id="checkoutOrderSummary"><!-- summary --></div>
                        </div>

                        <!-- Payment Type -->
                        <div class="card mb-3">
                            <div class="card-header"><strong>Payment Options</strong></div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="payment_type"
                                                id="instantPayment" value="instant" checked>
                                            <label class="form-check-label" for="instantPayment">
                                                <strong>Pay Now (Instant)</strong><br>
                                                <small class="text-muted">Pay the full amount immediately</small>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="payment_type"
                                                id="installmentPayment" value="installment">
                                            <label class="form-check-label" for="installmentPayment">
                                                <strong>Pay in Installments</strong><br>
                                                <small class="text-muted">Split payment over multiple months</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div id="installmentOptions" style="display:none;" class="mt-3">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="installments" class="form-label">Number of Installments</label>
                                            <select class="form-select" id="installments" name="installments">
                                                <option value="3">3 months</option>
                                                <option value="6">6 months</option>
                                                <option value="12" selected>12 months</option>
                                                <option value="24">24 months</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Monthly Payment</label>
                                            <div class="form-control-plaintext" id="monthlyPayment">$0.00</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div class="card mb-3">
                            <div class="card-header"><strong>Payment Method</strong></div>
                            <div class="card-body">
                                <select class="form-select" name="payment_method" required>
                                    <option value="">Select Payment Method</option>
                                    <option value="credit_card">Credit Card</option>
                                    <option value="debit_card">Debit Card</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="digital_wallet">Digital Wallet</option>
                                    <option value="cash">Cash</option>
                                </select>
                            </div>
                        </div>

                        <!-- Addresses -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header"><strong>Shipping Address</strong></div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <input type="text" class="form-control" name="shipping_address[address]"
                                                placeholder="Street Address" required>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <input type="text" class="form-control" name="shipping_address[city]"
                                                    placeholder="City" required>
                                            </div>
                                            <div class="col-md-6">
                                                <input type="text" class="form-control" name="shipping_address[state]"
                                                    placeholder="State" required>
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <input type="text" class="form-control" name="shipping_address[zip_code]"
                                                placeholder="ZIP Code" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <strong>Billing Address</strong>
                                        <div class="form-check form-check-inline float-end">
                                            <input class="form-check-input" type="checkbox" id="sameAsShipping">
                                            <label class="form-check-label" for="sameAsShipping">Same as shipping</label>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <input type="text" class="form-control" name="billing_address[address]"
                                                placeholder="Street Address" required>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <input type="text" class="form-control" name="billing_address[city]"
                                                    placeholder="City" required>
                                            </div>
                                            <div class="col-md-6">
                                                <input type="text" class="form-control" name="billing_address[state]"
                                                    placeholder="State" required>
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <input type="text" class="form-control" name="billing_address[zip_code]"
                                                placeholder="ZIP Code" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="card mt-3">
                            <div class="card-header"><strong>Order Notes (Optional)</strong></div>
                            <div class="card-body">
                                <textarea class="form-control" name="notes" rows="3" placeholder="Any special instructions..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success" id="placeOrderBtn">
                            <span class="btn-text">Place Order</span>
                            <span class="spinner-border spinner-border-sm ms-2 d-none" id="placeOrderLoading"
                                role="status" aria-hidden="true"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // ---------- Routes & helpers ----------
        window.ORDERS = {
            routes: {
                // Data endpoints (should return JSON when requested via AJAX)
                index: @json(route('orders.index')),
                statistics: @json(route('orders.statistics')),
                cart: @json(route('orders.cart')),
                addToCart: @json(route('orders.add-to-cart')),
                updateCart: @json(route('orders.update-cart')),
                clearCart: @json(route('orders.clear-cart')),
                checkout: @json(route('orders.checkout')),
                productsIndex: @json(route('products.index')),
                categoriesSelect: @json(url('/categories/select')),
                // base URLs for resource pages / actions
                orderShowBase: @json(url('/orders')), // append /{id}
                orderStatusBase: @json(url('/orders')), // append /{id}/status
            }
        };

        const fmtCurrency = (n) => '$' + (Number(n || 0)).toFixed(2);
        const fmtDate = (d) => {
            const dt = new Date(d);
            return isNaN(dt) ? '—' : dt.toLocaleDateString();
        };
        const debounce = (fn, wait = 350) => {
            let t;
            return (...a) => {
                clearTimeout(t);
                t = setTimeout(() => fn(...a), wait);
            };
        };
    </script>

    <script>
        let ordersTable;
        let currentCartTotal = 0;

        $(document).ready(function() {
            // CSRF header for all AJAX
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Initialize DataTable
            ordersTable = $('#ordersTable').DataTable({
                processing: true,
                serverSide: false, // set true if your API supports it
                responsive: true,
                ajax: {
                    url: window.ORDERS.routes.index,
                    data: function(d) {
                        d.search = $('#orderSearchInput').val();
                        d.status = $('#orderStatusFilter').val();
                        d.payment_type = $('#paymentTypeFilter').val();
                        d.date_from = $('#dateFrom').val();
                        d.date_to = $('#dateTo').val();
                    },
                    dataSrc: function(json) {
                        // support {data:[...]} or {data:{data:[...]}}
                        return (json?.data?.data) || json?.data || [];
                    },
                    error: function() {
                        $('#ordersTable tbody').html(
                            '<tr><td colspan="8" class="text-center text-muted">Failed to load orders.</td></tr>'
                        );
                    }
                },
                columns: [{
                        data: 'order_number',
                        defaultContent: ''
                    },
                    @if (Auth::user()->isAdmin())
                        {
                            data: 'user',
                            render: function(data) {
                                return data ?
                                    `${data.name}<br><small class="text-muted">${data.email||''}</small>` :
                                    '<span class="text-muted">—</span>';
                            }
                        },
                    @endif {
                        data: 'order_items',
                        render: function(data) {
                            const len = Array.isArray(data) ? data.length : (Number(data) || 0);
                            return `${len} items`;
                        }
                    },
                    {
                        data: 'total_amount',
                        render: function(data) {
                            return fmtCurrency(data);
                        }
                    },
                    {
                        data: 'payment_type',
                        render: function(val) {
                            if (val === 'instant')
                                return '<span class="badge bg-success">Instant</span>';
                            if (val === 'installment')
                                return '<span class="badge bg-info">Installment</span>';
                            return '<span class="badge bg-secondary">—</span>';
                        }
                    },
                    {
                        data: 'status',
                        render: function(val) {
                            const map = {
                                pending: 'bg-warning',
                                processing: 'bg-info',
                                shipped: 'bg-primary',
                                delivered: 'bg-success',
                                cancelled: 'bg-danger'
                            };
                            const cls = map[val] || 'bg-secondary';
                            const label = val ? (val.charAt(0).toUpperCase() + val.slice(1)) : '—';
                            return `<span class="badge ${cls}">${label}</span>`;
                        }
                    },
                    {
                        data: 'created_at',
                        render: function(d) {
                            return fmtDate(d);
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(_, __, row) {
                            let actions = `
                        <button class="btn btn-sm btn-info me-1" onclick="viewOrder(${row.id})" title="View">
                            <i class="fas fa-eye"></i>
                        </button>`;
                            @if (Auth::user()->isAdmin())
                                if (['pending', 'processing'].includes(row.status)) {
                                    actions += `
                            <div class="btn-group">
                                <button class="btn btn-sm btn-warning dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    Status
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" onclick="updateOrderStatus(${row.id}, 'processing')">Processing</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="updateOrderStatus(${row.id}, 'shipped')">Shipped</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="updateOrderStatus(${row.id}, 'delivered')">Delivered</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="#" onclick="updateOrderStatus(${row.id}, 'cancelled')">Cancel</a></li>
                                </ul>
                            </div>`;
                                }
                            @endif
                            return actions;
                        }
                    }
                ],
                pageLength: 25,
                order: [
                    [0, 'desc']
                ],
                language: {
                    emptyTable: "No orders found"
                }
            });

            // Stats
            loadOrderStatistics();

            // Filters / search
            const reload = debounce(() => ordersTable.ajax.reload(), 300);
            $('#orderSearchInput').on('keyup', reload);
            $('#orderStatusFilter, #paymentTypeFilter, #dateFrom, #dateTo').on('change', reload);

            // Payment type toggles
            $('input[name="payment_type"]').on('change', function() {
                const isInst = $(this).val() === 'installment';
                $('#installmentOptions').toggle(isInst);
                if (isInst) calculateMonthlyPayment();
            });
            $('#installments').on('change', calculateMonthlyPayment);
            $('#sameAsShipping').on('change', function() {
                if (this.checked) copyShippingToBilling();
            });

            // Checkout submit
            $('#checkoutForm').on('submit', function(e) {
                e.preventDefault();
                togglePlaceOrderLoading(true);

                $.ajax({
                    url: window.ORDERS.routes.checkout,
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        togglePlaceOrderLoading(false);
                        if (res?.success) {
                            $('#checkoutModal').modal('hide');
                            $('#cartModal').modal('hide');
                            showAlert('success', res.message || 'Order placed successfully');
                            ordersTable.ajax.reload(null, false);
                            loadOrderStatistics();
                            updateCartCount();
                        } else {
                            showAlert('danger', res?.message || 'Failed to place order');
                        }
                    },
                    error: function() {
                        togglePlaceOrderLoading(false);
                        showAlert('danger', 'Failed to place order');
                    }
                });
            });

            // Initial cart count
            updateCartCount();

            // Load product categories for product picker
            loadProductCategories();
        });

        // ---------- Stats ----------
        function loadOrderStatistics() {
            $.get(window.ORDERS.routes.statistics, function(res) {
                if (res?.success) {
                    const s = res.data || {};
                    $('#totalOrders').text(s.total_orders ?? 0);
                    $('#pendingOrders').text(s.pending_orders ?? 0);
                    $('#processingOrders').text(s.processing_orders ?? 0);
                    $('#deliveredOrders').text(s.delivered_orders ?? 0);
                    $('#cancelledOrders').text(s.cancelled_orders ?? 0);
                    $('#totalRevenue').text(fmtCurrency(s.total_revenue));
                }
            });
        }

        // ---------- Cart ----------
        function loadCart() {
            $.get(window.ORDERS.routes.cart, function(res) {
                if (!res?.success) return;

                const cart = res.cart || {};
                let html = '';

                if (!Object.keys(cart).length) {
                    html = '<div class="text-center text-muted"><p>Your cart is empty</p></div>';
                    $('#checkoutBtn').prop('disabled', true);
                } else {
                    html = `
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead><tr><th>Product</th><th>Price</th><th>Quantity</th><th>Total</th><th>Actions</th></tr></thead>
                    <tbody>`;
                    for (const id in cart) {
                        const item = cart[id];
                        html += `
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            ${item.image ? `<img src="/storage/${item.image}" class="rounded me-2" style="width:40px;height:40px;object-fit:cover;">`
                                          : `<div class="rounded me-2 bg-light d-flex align-items-center justify-content-center" style="width:40px;height:40px;"><i class="fas fa-image text-muted"></i></div>`}
                            <strong>${item.name}</strong>
                        </div>
                    </td>
                    <td>${fmtCurrency(item.price)}</td>
                    <td>
                        <input type="number" class="form-control form-control-sm" value="${item.quantity}" min="0"
                               onchange="updateCartItemQuantity(${id}, this.value)" style="width:80px;">
                    </td>
                    <td>${fmtCurrency(item.price * item.quantity)}</td>
                    <td>
                        <button class="btn btn-sm btn-danger" onclick="updateCartItemQuantity(${id}, 0)" title="Remove">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>`;
                    }
                    html += '</tbody></table></div>';
                    $('#checkoutBtn').prop('disabled', false);
                }

                $('#cartItems').html(html);
                $('#cartTotal').text(fmtCurrency(res.cart_total));
                currentCartTotal = Number(res.cart_total || 0);
                calculateMonthlyPayment();
            });
        }

        function addToCart(productId, qty = 1) {
            $.post(window.ORDERS.routes.addToCart, {
                product_id: productId,
                quantity: qty
            }, function(res) {
                if (res?.success) {
                    showAlert('success', res.message || 'Added to cart');
                    updateCartCount();
                } else {
                    showAlert('danger', res?.message || 'Failed to add to cart');
                }
            });
        }

        function updateCartItemQuantity(productId, quantity) {
            $.post(window.ORDERS.routes.updateCart, {
                product_id: productId,
                quantity
            }, function(res) {
                if (res?.success) {
                    loadCart();
                    updateCartCount();
                }
            });
        }

        function clearCart() {
            if (!confirm('Are you sure you want to clear the cart?')) return;
            $.post(window.ORDERS.routes.clearCart, function(res) {
                if (res?.success) {
                    loadCart();
                    updateCartCount();
                    showAlert('info', res.message || 'Cart cleared');
                }
            });
        }

        function updateCartCount() {
            $.get(window.ORDERS.routes.cart, function(res) {
                if (res?.success) $('#cartCount').text(res.cart_count ?? 0);
            });
        }

        function proceedToCheckout() {
            // Load latest cart into summary then open modal
            $.get(window.ORDERS.routes.cart, function(res) {
                if (res?.success && Object.keys(res.cart || {}).length) {
                    let html = `
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead>
                    <tbody>`;
                    for (const id in res.cart) {
                        const item = res.cart[id];
                        html += `
                    <tr>
                        <td><strong>${item.name}</strong></td>
                        <td>${item.quantity}</td>
                        <td>${fmtCurrency(item.price)}</td>
                        <td>${fmtCurrency(item.price * item.quantity)}</td>
                    </tr>`;
                    }
                    html += `
                    </tbody>
                    <tfoot>
                        <tr><td colspan="3"><strong>Total:</strong></td><td><strong>${fmtCurrency(res.cart_total)}</strong></td></tr>
                    </tfoot>
                </table>
            </div>`;

                    $('#checkoutOrderSummary').html(html);
                    currentCartTotal = Number(res.cart_total || 0);

                    $('#cartModal').modal('hide');
                    $('#checkoutModal').modal('show');
                    // If installments picked previously, recompute
                    calculateMonthlyPayment();
                }
            });
        }

        // ---------- Product picker ----------
        function loadProductsForOrder() {
            // categories for filter drop-down (if not already)
            loadProductCategories();

            $.get(window.ORDERS.routes.productsIndex, function(res) {
                if (!res?.success) return;
                const products = res.data?.data || res.data || [];
                let html = '';

                products.forEach(p => {
                    const image = (p.images && p.images[0]) ? `/storage/${p.images[0]}` : null;
                    const stockBadge = Number(p.stock_quantity) > 0 ?
                        `<span class="badge bg-success">In Stock (${p.stock_quantity})</span>` :
                        `<span class="badge bg-danger">Out of Stock</span>`;

                    html += `
            <div class="col-md-4">
                <div class="card h-100">
                    ${image ? `<img src="${image}" class="card-img-top" style="height:150px;object-fit:cover;">`
                            : `<div class="bg-light d-flex align-items-center justify-content-center" style="height:150px;"><i class="fas fa-image fa-3x text-muted"></i></div>`}
                    <div class="card-body text-center">
                        <h6 class="card-title mb-1">${p.name}</h6>
                        <p class="card-text mb-2"><strong>${fmtCurrency(p.price)}</strong></p>
                        ${stockBadge}
                        <div class="mt-2">
                            ${Number(p.stock_quantity) > 0 ? `
                                    <div class="input-group input-group-sm mb-2">
                                        <input type="number" class="form-control" id="qty_${p.id}" value="1" min="1" max="${p.stock_quantity}">
                                        <button class="btn btn-success" type="button" onclick="addToCart(${p.id}, document.getElementById('qty_${p.id}').value)">
                                            <i class="fas fa-cart-plus"></i>
                                        </button>
                                    </div>` : `
                                    <button class="btn btn-sm btn-secondary" disabled>Out of Stock</button>`}
                        </div>
                    </div>
                </div>
            </div>`;
                });

                $('#productsList').html(html);
            });
        }

        function loadProductCategories() {
            $.get(window.ORDERS.routes.categoriesSelect, function(res) {
                if (!res?.success) return;
                const list = res.data || [];
                const sel = $('#productCategoryFilter');
                if (!sel.children().length || sel.children().length === 1) {
                    sel.empty().append('<option value="">All Categories</option>');
                    list.forEach(c => sel.append(`<option value="${c.id}">${c.name}</option>`));
                }
            });
        }

        // ---------- Checkout helpers ----------
        function calculateMonthlyPayment() {
            if ($('#installmentPayment').is(':checked') && currentCartTotal > 0) {
                const n = parseInt($('#installments').val() || '12', 10);
                $('#monthlyPayment').text(fmtCurrency(currentCartTotal / n));
            } else {
                $('#monthlyPayment').text('$0.00');
            }
        }

        function copyShippingToBilling() {
            $('input[name="billing_address[address]"]').val($('input[name="shipping_address[address]"]').val());
            $('input[name="billing_address[city]"]').val($('input[name="shipping_address[city]"]').val());
            $('input[name="billing_address[state]"]').val($('input[name="shipping_address[state]"]').val());
            $('input[name="billing_address[zip_code]"]').val($('input[name="shipping_address[zip_code]"]').val());
        }

        function togglePlaceOrderLoading(on) {
            $('#placeOrderBtn').prop('disabled', on);
            $('#placeOrderLoading').toggleClass('d-none', !on);
        }

        // ---------- Navigation / actions ----------
        function viewOrder(orderId) {
            window.location.href = `${window.ORDERS.routes.orderShowBase}/${orderId}`;
        }

        function updateOrderStatus(orderId, status) {
            if (!confirm(`Change order status to ${status}?`)) return;
            $.ajax({
                url: `${window.ORDERS.routes.orderStatusBase}/${orderId}/status`,
                method: 'PUT',
                data: {
                    status
                },
                success: function(res) {
                    if (res?.success) {
                        showAlert('success', res.message || 'Status updated');
                        ordersTable.ajax.reload(null, false);
                        loadOrderStatistics();
                    } else {
                        showAlert('danger', res?.message || 'Failed to update status');
                    }
                },
                error: function() {
                    showAlert('danger', 'Failed to update status');
                }
            });
        }

        function refreshOrdersTable() {
            ordersTable.ajax.reload();
            loadOrderStatistics();
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
