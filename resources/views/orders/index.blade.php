@extends('layouts.app')

@section('title', 'Orders Management')
@section('page-title', 'Orders')

@section('page-actions')
    <div class="d-flex gap-2 align-items-center">
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#cartModal" onclick="loadCart()">
            <i class="fas fa-shopping-cart me-2"></i>
            Shopping Cart <span id="cartCount" class="badge bg-light text-dark">0</span>
        </button>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#productSelectionModal"
            onclick="loadProductsForOrder()" id="newOrderBtn">
            <i class="fas fa-plus me-2"></i>New Order
        </button>
        @if(Auth::user()->isAdmin())
        <div class="dropdown">
            <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown"
                    title="Quick Add Product (Ctrl+Q)">
                <i class="fas fa-plus-circle me-2"></i>Quick Add Product
                <small class="text-muted ms-1">(Ctrl+Q)</small>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" id="quickAddDropdown" style="min-width: 350px;">
                <li><h6 class="dropdown-header">Quick Add to Cart</h6></li>
                <li>
                    <div class="px-3 py-2">
                        <div class="input-group input-group-sm mb-2">
                            <input type="text" class="form-control" id="quickSearch" placeholder="Search products..."
                                   title="Type to search products (min 2 characters)">
                            <button class="btn btn-outline-secondary" type="button" onclick="searchQuickProducts()">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        <div id="quickProductsList" style="max-height: 250px; overflow-y: auto;">
                            <div class="text-center text-muted py-3">
                                <small>Start typing to search products...</small>
                            </div>
                        </div>
                        <div class="border-top pt-2 mt-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="fas fa-cart-plus me-1"></i>
                                    <span id="quickCartCount">0</span> items in cart
                                </small>
                                <button class="btn btn-sm btn-outline-primary" onclick="loadCart(); $('.dropdown-toggle').dropdown('hide');" title="View Cart">
                                    <i class="fas fa-shopping-cart"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
        @endif
    </div>
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
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="cartModalLabel">
                        <i class="fas fa-shopping-cart me-2"></i>Shopping Cart
                        <span class="badge bg-primary ms-2" id="cartItemCount">0</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Empty Cart State -->
                    <div id="emptyCart" class="text-center py-5 d-none">
                        <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                        <h5>Your cart is empty</h5>
                        <p class="text-muted">Add some products to get started!</p>
                        <button type="button" class="btn btn-primary" onclick="openProductSelection()">
                            <i class="fas fa-plus me-1"></i>Browse Products
                        </button>
                    </div>

                    <!-- Cart Items -->
                    <div id="cartItemsContainer" class="d-none">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th class="text-center">Price</th>
                                        <th class="text-center" style="width: 120px;">Quantity</th>
                                        <th class="text-center">Total</th>
                                        <th class="text-center" style="width: 80px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="cartItems">
                                    <!-- Cart items will be loaded here -->
                                </tbody>
                            </table>
                        </div>

                        <!-- Cart Summary -->
                        <div class="card mt-3">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h6>Order Summary</h6>
                                        <div class="d-flex justify-content-between">
                                            <span>Subtotal:</span>
                                            <span id="cartSubtotal">$0.00</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span>Tax (10%):</span>
                                            <span id="cartTax">$0.00</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span>Shipping:</span>
                                            <span id="cartShipping">$0.00</span>
                                        </div>
                                        <hr>
                                        <div class="d-flex justify-content-between fw-bold">
                                            <span>Total:</span>
                                            <span id="cartTotal">$0.00</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-center">
                                        <div class="mb-2">
                                            <i class="fas fa-truck fa-2x text-success"></i>
                                        </div>
                                        <small class="text-muted">Free shipping on orders over $100</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <div class="d-flex justify-content-between w-100">
                        <div>
                            <button type="button" class="btn btn-outline-danger me-2" onclick="clearCart()">
                                <i class="fas fa-trash me-1"></i>Clear Cart
                            </button>
                            <button type="button" class="btn btn-outline-primary" onclick="openProductSelection()">
                                <i class="fas fa-plus me-1"></i>Add More
                            </button>
                        </div>
                        <div>
                            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i>Close
                            </button>
                            <button type="button" class="btn btn-success" onclick="proceedToCheckout()" id="checkoutBtn" disabled>
                                <i class="fas fa-credit-card me-1"></i>Proceed to Checkout
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Selection Modal -->
    <div class="modal fade" id="productSelectionModal" tabindex="-1" aria-labelledby="productSelectionModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="productSelectionModalLabel">
                        <i class="fas fa-shopping-bag me-2"></i>Add Products to Cart
                        <span id="apiStatus" class="badge bg-secondary ms-2">
                            <i class="fas fa-circle-notch fa-spin me-1"></i>Loading...
                        </span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Customer Selection -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="fas fa-user me-2"></i>Select Customer
                                    </h6>
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                                <input type="text" class="form-control" id="customerSearch"
                                                       placeholder="Search customers by name or email...">
                                                <button class="btn btn-outline-secondary" type="button" onclick="searchCustomers()">
                                                    <i class="fas fa-search"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <button type="button" class="btn btn-outline-success" onclick="showNewCustomerForm()">
                                                <i class="fas fa-plus me-1"></i>New Customer
                                            </button>
                                        </div>
                                    </div>
                                    <div id="customerResults" class="mt-3" style="max-height: 200px; overflow-y: auto;">
                                        <div class="text-muted small">Start typing to search customers...</div>
                                    </div>
                                    <div id="selectedCustomer" class="mt-2 d-none">
                                        <div class="alert alert-success py-2">
                                            <strong>Selected Customer:</strong>
                                            <span id="selectedCustomerName"></span>
                                            <button type="button" class="btn btn-sm btn-outline-danger ms-2" onclick="clearSelectedCustomer()">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Search and Filter Controls -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" id="productSearch"
                                    placeholder="Search products by name or SKU...">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <select class="form-select" id="productCategoryFilter">
                                <option value="">All Categories</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-outline-primary w-100" onclick="refreshProducts()">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Bulk Actions -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <button type="button" class="btn btn-sm btn-outline-success me-2" onclick="addSelectedToCart()">
                                        <i class="fas fa-cart-plus me-1"></i>Add Selected to Cart
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearSelections()">
                                        <i class="fas fa-times me-1"></i>Clear Selections
                                    </button>
                                </div>
                                <div class="text-muted small">
                                    <span id="selectedCount">0</span> products selected
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Products Grid -->
                    <div id="productsList" class="row g-3">
                        <!-- Products will be loaded here -->
                    </div>

                    <!-- Loading State -->
                    <div id="productsLoading" class="text-center py-5 d-none">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading products...</span>
                        </div>
                        <p class="mt-2">Loading products...</p>
                    </div>

                    <!-- Empty State -->
                    <div id="productsEmpty" class="text-center py-5 d-none">
                        <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                        <h5>No products found</h5>
                        <p class="text-muted">Try adjusting your search or filter criteria.</p>
                        <button type="button" class="btn btn-outline-primary mt-3" onclick="loadProductsForOrder()">
                            <i class="fas fa-sync-alt me-1"></i>Refresh Products
                        </button>
                    </div>

                    <!-- Error State -->
                    <div id="productsError" class="text-center py-5 d-none">
                        <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                        <h5>Failed to load products</h5>
                        <p class="text-muted">There was an error loading the products. Please try again.</p>
                        <button type="button" class="btn btn-outline-danger mt-3" onclick="loadProductsForOrder()">
                            <i class="fas fa-redo me-1"></i>Try Again
                        </button>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <div class="d-flex justify-content-between w-100">
                        <div class="text-muted small">
                            <i class="fas fa-shopping-cart me-1"></i>
                            Cart: <span id="modalCartCount">0</span> items
                        </div>
                        <div>
                            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i>Close
                            </button>
                            <button type="button" class="btn btn-primary" onclick="viewCartFromModal()">
                                <i class="fas fa-shopping-cart me-1"></i>View Cart
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Checkout Modal -->
    <div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <form id="checkoutForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="checkoutModalLabel">Checkout</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Customer Information -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong>Customer Information</strong>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="changeCustomer()">
                                        <i class="fas fa-edit me-1"></i>Change Customer
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="checkoutCustomerInfo">
                                    <div class="text-center text-muted">
                                        <i class="fas fa-user fa-2x mb-2"></i>
                                        <p>No customer selected</p>
                                        <button type="button" class="btn btn-primary btn-sm" onclick="changeCustomer()">
                                            Select Customer
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

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

@push('styles')
<!-- Animate.css for animations -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

<style>
    .product-card {
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .product-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .product-card.border-primary {
        box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.25);
    }

    .product-checkbox:focus {
        box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.25);
    }

    .card-img-top {
        border-bottom: 1px solid #dee2e6;
    }

    .product-qty {
        text-align: center;
    }

    .bg-success.bg-opacity-10 {
        background-color: rgba(25, 135, 84, 0.1) !important;
    }

    .modal-xl .modal-dialog {
        max-width: 95vw;
    }

    @media (max-width: 768px) {
        .modal-xl .modal-dialog {
            max-width: 100vw;
            margin: 0;
        }

        .product-card .card-body {
            padding: 1rem 0.5rem;
        }

        .product-card .card-title {
            font-size: 0.9rem;
        }
    }

    /* Cart modal enhancements */
    .cart-item-image {
        border-radius: 8px;
        border: 1px solid #e9ecef;
    }

    .cart-quantity-controls {
        max-width: 120px;
    }

    .cart-summary-card {
        border: 2px solid #e9ecef;
        border-radius: 10px;
    }

    /* Loading states */
    .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
    }

    /* Selection states */
    .product-selected {
        background-color: rgba(13, 110, 253, 0.1);
        border-color: #0d6efd;
    }

    /* Animation for cart updates */
    @keyframes cartUpdate {
        0% { background-color: transparent; }
        50% { background-color: rgba(25, 135, 84, 0.2); }
        100% { background-color: transparent; }
    }

    .cart-updated {
        animation: cartUpdate 1s ease-in-out;
    }
</style>
@endpush

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
                // Customer routes
                customersSearch: @json(url('/admin/users/search')),
                customersStore: @json(route('admin.users.store')),
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
        let selectedCustomerId = null; // Global variable for customer selection
        let selectedProducts = new Set(); // Global variable for product selection
        let customerSearchTimeout; // Global variable for customer search timeout
        let quickSearchTimeout; // Global variable for quick search timeout

        $(document).ready(function() {
            // CSRF header for all AJAX
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Initialize global variables
            selectedCustomerId = null;
            selectedProducts = new Set();
            customerSearchTimeout = null;
            quickSearchTimeout = null;

            // Initialize DataTable
            ordersTable = $('#ordersTable').DataTable({
                processing: true,
                serverSide: true, // enable server-side processing
                responsive: true,
                ajax: {
                    url: window.ORDERS.routes.index,
                    type: 'GET',
                    data: function(d) {
                        // Add custom search parameters
                        d.search = $('#orderSearchInput').val();
                        d.status = $('#orderStatusFilter').val();
                        d.payment_type = $('#paymentTypeFilter').val();
                        d.date_from = $('#dateFrom').val();
                        d.date_to = $('#dateTo').val();
                    },
                    dataSrc: function(json) {
                        // DataTables expects the data array directly
                        return json.data || [];
                    },
                    error: function(xhr, error, thrown) {
                        console.error('DataTables error:', error, thrown);
                        $('#ordersTable tbody').html(
                            '<tr><td colspan="8" class="text-center text-danger">Failed to load orders.</td></tr>'
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
                        data: 'order_items_count',
                        render: function(data) {
                            return `${Number(data || 0)} items`;
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

            // Checkout modal events
            $('#checkoutModal').on('shown.bs.modal', function() {
                updateCheckoutCustomerInfo();
            });

            // Checkout submit
            $('#checkoutForm').on('submit', function(e) {
                e.preventDefault();

                // Validate customer selection
                if (!selectedCustomerId) {
                    showAlert('warning', 'Please select a customer before placing the order. Click "Change Customer" to select one.');
                    $('#checkoutCustomerInfo').addClass('border border-warning');
                    setTimeout(() => {
                        $('#checkoutCustomerInfo').removeClass('border border-warning');
                    }, 3000);
                    return;
                }

                togglePlaceOrderLoading(true);

                const formData = $(this).serializeArray();
                // Add selected customer ID to form data
                formData.push({ name: 'customer_id', value: selectedCustomerId });

                $.ajax({
                    url: window.ORDERS.routes.checkout,
                    method: 'POST',
                    data: formData,
                    success: function(res) {
                        togglePlaceOrderLoading(false);
                        if (res?.success) {
                            $('#checkoutModal').modal('hide');
                            $('#cartModal').modal('hide');
                            $('#productSelectionModal').modal('hide');
                            showAlert('success', res.message || 'Order placed successfully');
                            ordersTable.ajax.reload(null, false);
                            loadOrderStatistics();
                            updateCartCount();
                            // Clear selected customer after successful order
                            clearSelectedCustomer();
                        } else {
                            showAlert('danger', res?.message || 'Failed to place order');
                        }
                    },
                    error: function(xhr) {
                        togglePlaceOrderLoading(false);
                        const errors = xhr.responseJSON?.errors;
                        if (errors) {
                            let errorMsg = 'Validation errors:\n';
                            Object.values(errors).forEach(err => {
                                errorMsg += '• ' + err.join(', ') + '\n';
                            });
                            showAlert('danger', errorMsg);
                        } else {
                            showAlert('danger', 'Failed to place order');
                        }
                    }
                });
            });

            // Initial cart count
            updateCartCount();

            // Load product categories for product picker
            loadProductCategories();

            // Customer search events
            $('#customerSearch').on('input', debounce(function() {
                searchCustomers();
            }, 300));

            // Product selection modal events
            $('#productSearch').on('input', debounce(function() {
                refreshProducts();
            }, 300));

            $('#productCategoryFilter').on('change', function() {
                refreshProducts();
            });

            // Modal show event
            $('#productSelectionModal').on('shown.bs.modal', function() {
                console.log('Product selection modal opened');
                loadProductsForOrder();
                updateModalCartCount();
            });

            // Debug: Check if New Order button is working
            $('#newOrderBtn').on('click', function() {
                console.log('New Order button clicked');
            });

            // Debug: Check if modal trigger is working
            $('#productSelectionModal').on('show.bs.modal', function() {
                console.log('Product selection modal showing');
            });

            // Quick add product search
            $('#quickSearch').on('input', function() {
                searchQuickProducts();
            });

            // Clear quick search when dropdown is hidden
            $('.dropdown').on('hidden.bs.dropdown', function() {
                $('#quickSearch').val('');
                $('#quickProductsList').html('<div class="text-center text-muted py-3"><small>Type to search products...</small></div>');
            });

            // Keyboard shortcut for quick add (Ctrl+Q)
            $(document).on('keydown', function(e) {
                if (e.ctrlKey && e.key === 'q') {
                    e.preventDefault();
                    $('.dropdown-toggle').dropdown('toggle');
                    $('#quickSearch').focus();
                }
            });
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
                const cartCount = Object.keys(cart).length;

                $('#cartItemCount').text(cartCount);

                if (cartCount === 0) {
                    $('#emptyCart').removeClass('d-none');
                    $('#cartItemsContainer').addClass('d-none');
                    $('#checkoutBtn').prop('disabled', true);
                    return;
                }

                $('#emptyCart').addClass('d-none');
                $('#cartItemsContainer').removeClass('d-none');
                $('#checkoutBtn').prop('disabled', false);

                let html = '';
                let subtotal = 0;

                for (const id in cart) {
                    const item = cart[id];
                    const itemTotal = item.price * item.quantity;
                    subtotal += itemTotal;

                    html += `
            <tr data-product-id="${id}">
                <td>
                    <div class="d-flex align-items-center">
                        ${item.image ? `<img src="/storage/${item.image}" class="rounded me-3" style="width:50px;height:50px;object-fit:cover;" alt="${item.name}">`
                                      : `<div class="rounded me-3 bg-light d-flex align-items-center justify-content-center" style="width:50px;height:50px;"><i class="fas fa-image text-muted"></i></div>`}
                        <div>
                            <div class="fw-bold">${item.name}</div>
                            <small class="text-muted">${item.sku || ''}</small>
                        </div>
                    </div>
                </td>
                <td class="text-center">${fmtCurrency(item.price)}</td>
                <td class="text-center">
                    <div class="input-group input-group-sm" style="width: 100px; margin: 0 auto;">
                        <button class="btn btn-outline-secondary btn-sm" type="button" onclick="changeCartQuantity(${id}, ${item.quantity - 1})">
                            <i class="fas fa-minus"></i>
                        </button>
                        <input type="number" class="form-control text-center" value="${item.quantity}" min="0"
                               onchange="updateCartItemQuantity(${id}, this.value)" style="max-width: 50px;">
                        <button class="btn btn-outline-secondary btn-sm" type="button" onclick="changeCartQuantity(${id}, ${item.quantity + 1})">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </td>
                <td class="text-center fw-bold">${fmtCurrency(itemTotal)}</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-outline-danger" onclick="updateCartItemQuantity(${id}, 0)" title="Remove from cart">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>`;
                }

                $('#cartItems').html(html);
                $('#cartSubtotal').text(fmtCurrency(subtotal));
                $('#cartTax').text(fmtCurrency(subtotal * 0.1));
                $('#cartShipping').text(subtotal > 100 ? '$0.00' : '$10.00');
                $('#cartTotal').text(fmtCurrency(res.cart_total));
                currentCartTotal = Number(res.cart_total || 0);
                calculateMonthlyPayment();
            });
        }

        function changeCartQuantity(productId, newQuantity) {
            if (newQuantity < 0) return;
            updateCartItemQuantity(productId, newQuantity);
        }

        function openProductSelection() {
            $('#cartModal').modal('hide');
            setTimeout(() => {
                $('#productSelectionModal').modal('show');
                loadProductsForOrder();
            }, 300);
        }


        function showSampleProducts() {
            console.log('Showing sample products for testing...');

            const sampleProducts = [
                {
                    id: 1,
                    name: 'Sample iPhone 15 Pro',
                    sku: 'SAMPLE001',
                    price: 999.99,
                    stock_quantity: 10,
                    images: [],
                    category: { name: 'Smartphones' }
                },
                {
                    id: 2,
                    name: 'Sample MacBook Pro',
                    sku: 'SAMPLE002',
                    price: 2499.99,
                    stock_quantity: 5,
                    images: [],
                    category: { name: 'Laptops' }
                },
                {
                    id: 3,
                    name: 'Sample Denim Jeans',
                    sku: 'SAMPLE003',
                    price: 79.99,
                    stock_quantity: 25,
                    images: [],
                    category: { name: 'Clothing' }
                }
            ];

            let html = '';
            sampleProducts.forEach(p => {
                const isSelected = selectedProducts.has(p.id);
                const stockQty = Number(p.stock_quantity);
                const isInStock = stockQty > 0;

                const stockBadge = isInStock ?
                    `<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>In Stock (${stockQty})</span>` :
                    `<span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i>Out of Stock</span>`;

                html += `
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 product-card ${isSelected ? 'border-primary' : ''}" data-product-id="${p.id}">
                    <div class="card-header p-2">
                        <div class="form-check">
                            <input class="form-check-input product-checkbox" type="checkbox"
                                   id="check_${p.id}" ${isSelected ? 'checked' : ''} ${!isInStock ? 'disabled' : ''}>
                            <label class="form-check-label small fw-bold" for="check_${p.id}">
                                Select for bulk add
                            </label>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <h6 class="card-title mb-1 text-truncate" title="${p.name}">${p.name}</h6>
                        <p class="card-text small text-muted mb-2">${p.sku || 'No SKU'}</p>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold text-primary">${fmtCurrency(p.price)}</span>
                            ${stockBadge}
                        </div>
                        ${isInStock ? `
                        <div class="d-flex gap-2 align-items-center">
                            <div class="input-group input-group-sm flex-grow-1">
                                <span class="input-group-text">Qty</span>
                                <input type="number" class="form-control product-qty" id="qty_${p.id}"
                                       value="1" min="1" max="${stockQty}" data-product-id="${p.id}">
                            </div>
                            <button class="btn btn-success btn-sm" onclick="addSampleToCart(${p.id}, '${p.name}', ${p.price})"
                                    title="Add to cart">
                                <i class="fas fa-cart-plus"></i>
                            </button>
                        </div>` : `
                        <button class="btn btn-sm btn-secondary w-100" disabled>
                            <i class="fas fa-ban me-1"></i>Out of Stock
                        </button>`}
                    </div>
                </div>
            </div>`;
            });

            $('#productsList').html(html);
            $('#productsLoading').addClass('d-none');
            $('#productsEmpty').addClass('d-none');
            $('#productsError').addClass('d-none');

            showAlert('info', 'Showing sample products for testing. Your API may need fixing.');

            // Re-bind events for sample products
            $('.product-checkbox').on('change', function() {
                const productId = $(this).closest('.product-card').data('product-id');
                if (this.checked) {
                    selectedProducts.add(productId);
                } else {
                    selectedProducts.delete(productId);
                }
                updateSelectedCount();
                updateCardSelectionUI(productId, this.checked);
            });
        }

        function addSampleToCart(productId, name, price) {
            const qtyInput = $(`#qty_${productId}`);
            const quantity = parseInt(qtyInput.val()) || 1;

            showAlert('success', `Sample: Added ${quantity}x ${name} to cart (price: ${fmtCurrency(price * quantity)})`);
            updateCartCount();
            updateModalCartCount();
        }

        // Make test function globally available
        window.testProductsAPI = testProductsAPI;

        function addToCart(productId, qty = 1) {
            // Show loading state
            const addButton = $(`.product-card[data-product-id="${productId}"] .btn-success`);
            const originalHtml = addButton.html();
            addButton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

            $.post(window.ORDERS.routes.addToCart, {
                product_id: productId,
                quantity: qty
            }, function(res) {
                addButton.prop('disabled', false).html(originalHtml);

                if (res?.success) {
                    showToast('success', res.message || 'Added to cart');
                    updateCartCount();
                    updateModalCartCount();

                    // Visual feedback for the product card
                    const card = $(`.product-card[data-product-id="${productId}"]`);
                    if (card.length) {
                        card.addClass('cart-updated');
                        setTimeout(() => {
                            card.removeClass('cart-updated');
                        }, 1000);
                    }

                    // Update cart badge animation
                    const cartBadge = $('#cartCount');
                    cartBadge.addClass('animate__animated animate__bounce');
                    setTimeout(() => {
                        cartBadge.removeClass('animate__animated animate__bounce');
                    }, 1000);
                } else {
                    showToast('danger', res?.message || 'Failed to add to cart');
                }
            }).fail(function() {
                addButton.prop('disabled', false).html(originalHtml);
                showToast('danger', 'Failed to add to cart');
            });
        }

        function showToast(type, message) {
            const toastId = 'toast-' + Date.now();
            const toastHtml = `
        <div id="${toastId}" class="toast align-items-center text-white bg-${type} border-0 position-fixed"
             style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>`;

            $('body').append(toastHtml);
            const toast = new bootstrap.Toast(document.getElementById(toastId));
            toast.show();

            // Remove toast from DOM after it's hidden
            $(`#${toastId}`).on('hidden.bs.toast', function() {
                $(this).remove();
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
                if (res?.success) {
                    const count = res.cart_count ?? 0;
                    $('#cartCount').text(count);
                    $('#quickCartCount').text(count);
                }
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

                    // Update customer info in checkout modal
                    updateCheckoutCustomerInfo();

                    $('#cartModal').modal('hide');
                    $('#checkoutModal').modal('show');
                    // If installments picked previously, recompute
                    calculateMonthlyPayment();
                }
            });
        }

        function updateCheckoutCustomerInfo() {
            const customerInfoDiv = $('#checkoutCustomerInfo');

            if (!selectedCustomerId) {
                customerInfoDiv.html(`
                    <div class="text-center text-muted">
                        <i class="fas fa-user fa-2x mb-2"></i>
                        <p>No customer selected</p>
                        <button type="button" class="btn btn-primary btn-sm" onclick="changeCustomer()">
                            Select Customer
                        </button>
                    </div>
                `);
                return;
            }

            // Find customer details from the selected customer
            const selectedCustomerName = $('#selectedCustomerName').text();
            const customerParts = selectedCustomerName.split(' (');
            const customerName = customerParts[0];
            const customerEmail = customerParts[1] ? customerParts[1].replace(')', '') : '';

            customerInfoDiv.html(`
                <div class="row align-items-center">
                    <div class="col-auto">
                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="fas fa-user"></i>
                        </div>
                    </div>
                    <div class="col">
                        <h6 class="mb-1 fw-bold">${customerName}</h6>
                        <p class="text-muted small mb-0"><i class="fas fa-envelope me-1"></i>${customerEmail}</p>
                        <small class="text-success"><i class="fas fa-check-circle me-1"></i>Customer selected</small>
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="changeCustomer()" title="Change Customer">
                            <i class="fas fa-edit me-1"></i>Change
                        </button>
                    </div>
                </div>
            `);
        }

        function changeCustomer() {
            // Close checkout modal and open product selection modal
            $('#checkoutModal').modal('hide');
            setTimeout(() => {
                $('#productSelectionModal').modal('show');
                // Focus on customer search
                $('#customerSearch').focus();
            }, 300);
        }

        // ---------- Quick Add Product ----------

        function searchQuickProducts() {
            const query = $('#quickSearch').val().trim();
            if (!query) {
                $('#quickProductsList').html('<div class="text-center text-muted py-3"><small>Type to search products...</small></div>');
                return;
            }

            // Show loading state
            $('#quickProductsList').html('<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');

            clearTimeout(quickSearchTimeout);
            quickSearchTimeout = setTimeout(() => {
                $.get(window.ORDERS.routes.productsIndex, {
                    search: query,
                    per_page: 10
                }, function(res) {
                    if (!res?.success) return;

                    const products = res.data?.data || res.data || [];
                    let html = '';

                    if (products.length === 0) {
                        html = '<div class="text-center text-muted py-3"><small>No products found</small></div>';
                    } else {
                        products.forEach(p => {
                            const image = (p.images && p.images[0]) ? `/storage/${p.images[0]}` : null;
                            const stockBadge = Number(p.stock_quantity) > 0 ?
                                `<span class="badge bg-success">${p.stock_quantity} in stock</span>` :
                                `<span class="badge bg-danger">Out of stock</span>`;

                            html += `
                        <div class="d-flex align-items-center justify-content-between p-2 border-bottom">
                            <div class="d-flex align-items-center flex-grow-1">
                                ${image ? `<img src="${image}" class="rounded me-2" style="width:30px;height:30px;object-fit:cover;">` : ''}
                                <div class="flex-grow-1">
                                    <div class="fw-bold small">${p.name}</div>
                                    <div class="text-primary small">${fmtCurrency(p.price)}</div>
                                    ${stockBadge}
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <input type="number" class="form-control form-control-sm" id="quickQty_${p.id}"
                                       value="1" min="1" max="${p.stock_quantity}" style="width:60px;"
                                       ${Number(p.stock_quantity) <= 0 ? 'disabled' : ''}>
                                <button class="btn btn-success btn-sm" onclick="quickAddToCart(${p.id})"
                                        ${Number(p.stock_quantity) <= 0 ? 'disabled' : ''}>
                                    <i class="fas fa-cart-plus"></i>
                                </button>
                            </div>
                        </div>`;
                        });
                    }

                    $('#quickProductsList').html(html);
                });
            }, 300);
        }

        function quickAddToCart(productId) {
            const quantity = $(`#quickQty_${productId}`).val() || 1;

            $.post(window.ORDERS.routes.addToCart, {
                product_id: productId,
                quantity: quantity
            }, function(res) {
                if (res?.success) {
                    showAlert('success', res.message || 'Added to cart');
                    updateCartCount();
                    // Close dropdown after successful add
                    $('.dropdown-toggle').dropdown('hide');
                    $('#quickSearch').val('');
                    $('#quickProductsList').html('<div class="text-center text-muted py-3"><small>Type to search products...</small></div>');
                } else {
                    showAlert('danger', res?.message || 'Failed to add to cart');
                }
            });
        }

        // ---------- Customer Selection ----------

        function searchCustomers() {
            const query = $('#customerSearch').val().trim();
            if (!query) {
                $('#customerResults').html('<div class="text-muted small">Start typing to search customers...</div>');
                return;
            }

            // Show loading state
            $('#customerResults').html('<div class="text-center py-2"><div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');

            clearTimeout(customerSearchTimeout);
            customerSearchTimeout = setTimeout(() => {
                $.get(window.ORDERS.routes.customersSearch, {
                    search: query,
                    role: 'customer'
                }, function(res) {
                    if (!res?.success) {
                        $('#customerResults').html('<div class="text-danger small">Failed to search customers</div>');
                        return;
                    }

                    const customers = res.data || [];
                    let html = '';

                    if (customers.length === 0) {
                        html = '<div class="text-muted small">No customers found. <button type="button" class="btn btn-link btn-sm p-0" onclick="showNewCustomerForm()">Create new customer</button></div>';
                    } else {
                        customers.forEach(customer => {
                            const isSelected = selectedCustomerId === customer.id;
                            html += `
                        <div class="d-flex align-items-center justify-content-between p-2 border-bottom customer-result ${isSelected ? 'bg-light' : ''}" data-customer-id="${customer.id}">
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle me-2">
                                    <i class="fas fa-user text-muted"></i>
                                </div>
                                <div>
                                    <div class="fw-bold small">${customer.name}</div>
                                    <div class="text-muted small">${customer.email}</div>
                                    ${customer.phone ? `<div class="text-muted small">${customer.phone}</div>` : ''}
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm ${isSelected ? 'btn-success' : 'btn-outline-primary'}"
                                    onclick="selectCustomer(${customer.id}, '${customer.name}', '${customer.email}')">
                                ${isSelected ? 'Selected' : 'Select'}
                            </button>
                        </div>`;
                        });
                    }

                    $('#customerResults').html(html);
                }).fail(function() {
                    $('#customerResults').html('<div class="text-danger small">Failed to search customers</div>');
                });
            }, 300);
        }

        function selectCustomer(id, name, email) {
            selectedCustomerId = id;
            $('#selectedCustomerName').text(`${name} (${email})`);
            $('#selectedCustomer').removeClass('d-none');

            // Update UI
            $('.customer-result').removeClass('bg-light');
            $(`.customer-result[data-customer-id="${id}"]`).addClass('bg-light');
            $(`.customer-result[data-customer-id="${id}"] .btn`).removeClass('btn-outline-primary').addClass('btn-success').text('Selected');

            // Update checkout customer info if checkout modal is open
            if ($('#checkoutModal').hasClass('show')) {
                updateCheckoutCustomerInfo();
            }

            showAlert('success', `Selected customer: ${name}`);
        }

        function clearSelectedCustomer() {
            selectedCustomerId = null;
            $('#selectedCustomer').addClass('d-none');
            $('.customer-result').removeClass('bg-light');
            $('.customer-result .btn').removeClass('btn-success').addClass('btn-outline-primary').text('Select');

            // Update checkout customer info if checkout modal is open
            if ($('#checkoutModal').hasClass('show')) {
                updateCheckoutCustomerInfo();
            }

            showAlert('info', 'Customer selection cleared');
        }

        function showNewCustomerForm() {
            const customerName = $('#customerSearch').val().trim();
            const modalHtml = `
            <div class="modal fade" id="newCustomerModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Create New Customer</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form id="newCustomerForm">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Name *</label>
                                    <input type="text" class="form-control" name="name" value="${customerName}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Email *</label>
                                    <input type="email" class="form-control" name="email" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Phone</label>
                                    <input type="text" class="form-control" name="phone">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Address</label>
                                    <textarea class="form-control" name="address" rows="3"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-success">
                                    <span class="btn-text">Create Customer</span>
                                    <span class="spinner-border spinner-border-sm ms-2 d-none" id="createCustomerLoading" role="status"></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>`;

            $('body').append(modalHtml);
            $('#newCustomerModal').modal('show');

            // Handle form submission
            $('#newCustomerForm').on('submit', function(e) {
                e.preventDefault();
                createNewCustomer();
            });

            // Remove modal from DOM when hidden
            $('#newCustomerModal').on('hidden.bs.modal', function() {
                $(this).remove();
            });
        }

        function createNewCustomer() {
            const formData = new FormData(document.getElementById('newCustomerForm'));
            const data = Object.fromEntries(formData.entries());

            $('#createCustomerLoading').removeClass('d-none');
            $('button[type="submit"]').prop('disabled', true);

            $.post(window.ORDERS.routes.customersStore, data, function(res) {
                $('#createCustomerLoading').addClass('d-none');
                $('button[type="submit"]').prop('disabled', false);

                if (res?.success) {
                    const customer = res.data;
                    selectCustomer(customer.id, customer.name, customer.email);
                    $('#newCustomerModal').modal('hide');
                    $('#customerSearch').val('');
                    $('#customerResults').html('<div class="text-muted small">Start typing to search customers...</div>');
                    showAlert('success', `Customer "${customer.name}" created successfully`);
                } else {
                    showAlert('danger', res?.message || 'Failed to create customer');
                }
            }).fail(function(xhr) {
                $('#createCustomerLoading').addClass('d-none');
                $('button[type="submit"]').prop('disabled', false);

                const errors = xhr.responseJSON?.errors;
                if (errors) {
                    let errorMsg = 'Validation errors:\n';
                    Object.values(errors).forEach(err => {
                        errorMsg += '• ' + err.join(', ') + '\n';
                    });
                    showAlert('danger', errorMsg);
                } else {
                    showAlert('danger', 'Failed to create customer');
                }
            });
        }

        // ---------- Enhanced Product Picker ----------

        function loadProductsForOrder(search = '', category = '') {
            $('#productsLoading').removeClass('d-none');
            $('#productsEmpty').addClass('d-none');
            $('#apiStatus').html('<i class="fas fa-circle-notch fa-spin me-1"></i>Loading...').removeClass('bg-success bg-danger').addClass('bg-secondary');

            const params = {};
            if (search) params.search = search;
            if (category) params.category_id = category;

            console.log('Loading products with params:', params);
            console.log('Products route:', window.ORDERS.routes.productsIndex);

            $.get(window.ORDERS.routes.productsIndex, params, function(res) {
                console.log('Products API response:', res);
                $('#productsLoading').addClass('d-none');

                if (!res?.success) {
                    console.error('API returned error:', res);
                    $('#apiStatus').html('<i class="fas fa-exclamation-triangle me-1"></i>API Error').removeClass('bg-success bg-secondary').addClass('bg-danger');
                    $('#productsEmpty').removeClass('d-none');
                    return;
                }

                const products = res.data?.data || res.data || [];
                console.log('Products found:', products.length, products);

                if (products.length === 0) {
                    $('#apiStatus').html('<i class="fas fa-info-circle me-1"></i>No Products').removeClass('bg-success bg-danger').addClass('bg-warning');
                    $('#productsEmpty').removeClass('d-none');
                    $('#productsList').html('');
                    return;
                }

                $('#apiStatus').html('<i class="fas fa-check-circle me-1"></i>Loaded').removeClass('bg-secondary bg-danger bg-warning').addClass('bg-success');

                let html = '';
                products.forEach(p => {
                    const image = (p.images && p.images[0]) ? `/storage/${p.images[0]}` : null;
                    const isSelected = selectedProducts.has(p.id);
                    const stockQty = Number(p.stock_quantity);
                    const isInStock = stockQty > 0;

                    const stockBadge = isInStock ?
                        `<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>In Stock (${stockQty})</span>` :
                        `<span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i>Out of Stock</span>`;

                    html += `
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 product-card ${isSelected ? 'border-primary' : ''}" data-product-id="${p.id}">
                    <div class="card-header p-2">
                        <div class="form-check">
                            <input class="form-check-input product-checkbox" type="checkbox"
                                   id="check_${p.id}" ${isSelected ? 'checked' : ''} ${!isInStock ? 'disabled' : ''}>
                            <label class="form-check-label small fw-bold" for="check_${p.id}">
                                Select for bulk add
                            </label>
                        </div>
                    </div>
                    ${image ? `<img src="${image}" class="card-img-top" style="height:120px;object-fit:cover;" alt="${p.name}">`
                            : `<div class="bg-light d-flex align-items-center justify-content-center" style="height:120px;"><i class="fas fa-image fa-2x text-muted"></i></div>`}
                    <div class="card-body p-3">
                        <h6 class="card-title mb-1 text-truncate" title="${p.name}">${p.name}</h6>
                        <p class="card-text small text-muted mb-2">${p.sku || 'No SKU'}</p>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold text-primary">${fmtCurrency(p.price)}</span>
                            ${stockBadge}
                        </div>
                        ${isInStock ? `
                        <div class="d-flex gap-2 align-items-center">
                            <div class="input-group input-group-sm flex-grow-1">
                                <span class="input-group-text">Qty</span>
                                <input type="number" class="form-control product-qty" id="qty_${p.id}"
                                       value="1" min="1" max="${stockQty}" data-product-id="${p.id}">
                            </div>
                            <button class="btn btn-success btn-sm" onclick="addSingleToCart(${p.id})"
                                    title="Add to cart">
                                <i class="fas fa-cart-plus"></i>
                            </button>
                        </div>` : `
                        <button class="btn btn-sm btn-secondary w-100" disabled>
                            <i class="fas fa-ban me-1"></i>Out of Stock
                        </button>`}
                    </div>
                </div>
            </div>`;
                });

                $('#productsList').html(html);
                $('#productsEmpty').addClass('d-none');
                $('#productsError').addClass('d-none');

                // Bind checkbox events
                $('.product-checkbox').on('change', function() {
                    const productId = $(this).closest('.product-card').data('product-id');
                    if (this.checked) {
                        selectedProducts.add(productId);
                    } else {
                        selectedProducts.delete(productId);
                    }
                    updateSelectedCount();
                    updateCardSelectionUI(productId, this.checked);
                });

                // Bind quantity change events
                $('.product-qty').on('change', function() {
                    const productId = $(this).data('product-id');
                    const qty = parseInt($(this).val()) || 1;
                    const max = parseInt($(this).attr('max')) || 1;
                    if (qty > max) {
                        $(this).val(max);
                        showAlert('warning', `Maximum quantity for this product is ${max}`);
                    }
                });
            }).fail(function(xhr, status, error) {
                console.error('Failed to load products:', status, error, xhr.responseText);
                $('#productsLoading').addClass('d-none');
                $('#productsEmpty').addClass('d-none');
                $('#productsError').removeClass('d-none');
                $('#apiStatus').html('<i class="fas fa-times-circle me-1"></i>Failed').removeClass('bg-success bg-secondary bg-warning').addClass('bg-danger');
                showAlert('danger', 'Failed to load products. Check console for details.');

                // Fallback: Show sample products for testing
                if (confirm('API failed. Show sample products for testing?')) {
                    showSampleProducts();
                }
            });
        }

        function updateSelectedCount() {
            $('#selectedCount').text(selectedProducts.size);
        }

        function updateCardSelectionUI(productId, isSelected) {
            const card = $(`.product-card[data-product-id="${productId}"]`);
            if (isSelected) {
                card.addClass('border-primary').addClass('shadow-sm');
            } else {
                card.removeClass('border-primary').removeClass('shadow-sm');
            }
        }

        function addSingleToCart(productId) {
            const qtyInput = $(`#qty_${productId}`);
            const quantity = parseInt(qtyInput.val()) || 1;

            addToCart(productId, quantity);
        }

        function addSelectedToCart() {
            if (selectedProducts.size === 0) {
                showAlert('warning', 'Please select products to add to cart');
                return;
            }

            let successCount = 0;
            let totalCount = selectedProducts.size;

            selectedProducts.forEach(productId => {
                const qtyInput = $(`#qty_${productId}`);
                const quantity = parseInt(qtyInput.val()) || 1;

                $.post(window.ORDERS.routes.addToCart, {
                    product_id: productId,
                    quantity: quantity
                }, function(res) {
                    successCount++;
                    if (successCount === totalCount) {
                        showAlert('success', `Added ${successCount} products to cart`);
                        selectedProducts.clear();
                        updateSelectedCount();
                        $('.product-checkbox').prop('checked', false);
                        $('.product-card').removeClass('border-primary shadow-sm');
                        updateCartCount();
                        updateModalCartCount();
                    }
                });
            });
        }

        function clearSelections() {
            selectedProducts.clear();
            $('.product-checkbox').prop('checked', false);
            $('.product-card').removeClass('border-primary shadow-sm');
            updateSelectedCount();
        }

        function refreshProducts() {
            const search = $('#productSearch').val();
            const category = $('#productCategoryFilter').val();
            loadProductsForOrder(search, category);
        }

        function viewCartFromModal() {
            $('#productSelectionModal').modal('hide');
            setTimeout(() => {
                $('#cartModal').modal('show');
                loadCart();
            }, 300);
        }

        function updateModalCartCount() {
            $.get(window.ORDERS.routes.cart, function(res) {
                if (res?.success) {
                    $('#modalCartCount').text(res.cart_count ?? 0);
                }
            });
        }

        function loadProductCategories() {
            console.log('Loading categories from:', window.ORDERS.routes.categoriesSelect);
            $.get(window.ORDERS.routes.categoriesSelect, function(res) {
                console.log('Categories API response:', res);
                if (!res?.success) {
                    console.error('Categories API failed:', res);
                    return;
                }
                const list = res.data || [];
                console.log('Categories loaded:', list.length, list);
                const sel = $('#productCategoryFilter');
                if (!sel.children().length || sel.children().length === 1) {
                    sel.empty().append('<option value="">All Categories</option>');
                    list.forEach(c => sel.append(`<option value="${c.id}">${c.name}</option>`));
                }
            }).fail(function(xhr, status, error) {
                console.error('Categories API request failed:', status, error, xhr.responseText);
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
