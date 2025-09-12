<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - Modern Online Shopping</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #64748b;
            --accent-color: #f59e0b;
            --dark-color: #1f2937;
            --light-color: #f8fafc;
        }

        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
        }

        .hero-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, #1d4ed8 100%);
            color: white;
            padding: 120px 0;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="2" fill="rgba(255,255,255,0.1)"/><circle cx="80" cy="80" r="2" fill="rgba(255,255,255,0.1)"/><circle cx="60" cy="30" r="1" fill="rgba(255,255,255,0.1)"/></svg>');
            opacity: 0.1;
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .feature-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            background: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            margin: 0 auto 20px;
        }

        .product-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }

        .product-image {
            height: 200px;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #cbd5e1;
            font-size: 48px;
        }

        .product-price {
            font-size: 18px;
            font-weight: 600;
            color: var(--primary-color);
        }

        .btn-primary-custom {
            background: var(--primary-color);
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary-custom:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
        }

        .navbar-custom {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
        }

        .search-section {
            background: var(--light-color);
            padding: 60px 0;
        }

        .footer-section {
            background: var(--dark-color);
            color: white;
            padding: 60px 0 30px;
        }

        .social-icon {
            width: 40px;
            height: 40px;
            background: var(--secondary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin: 0 10px;
            transition: all 0.3s ease;
        }

        .social-icon:hover {
            background: var(--primary-color);
            transform: translateY(-3px);
        }

        .category-filter {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }

        .category-btn {
            background: transparent;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            padding: 8px 20px;
            border-radius: 25px;
            margin: 5px;
            transition: all 0.3s ease;
        }

        .category-btn:hover,
        .category-btn.active {
            background: var(--primary-color);
            color: white;
        }

        @media (max-width: 768px) {
            .hero-section {
                padding: 80px 0;
            }

            .feature-card {
                margin-bottom: 30px;
            }
        }

        /* Modal Scroll Enhancements for Welcome Page */
        .modal-dialog-scrollable .modal-body {
            overflow-y: auto;
            max-height: calc(100vh - 200px);
        }

        .modal-dialog-scrollable .modal-content {
            max-height: 90vh;
        }

        /* Custom Scrollbar for Welcome Page Modals */
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
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-custom fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="#">
                <i class="fas fa-shopping-bag me-2"></i>
                {{ config('app.name', 'E-Commerce Store') }}
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#home">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#products">Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
                    </li>
                </ul>

                <div class="d-flex">
                    @guest
                        <a href="{{ route('login') }}" class="btn btn-outline-primary me-2">Login</a>
                        <a href="{{ route('register') }}" class="btn btn-primary-custom">Sign Up</a>
                    @else
                        <a href="{{ route('dashboard') }}" class="btn btn-primary-custom">Dashboard</a>
                    @endguest
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section" id="home">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="hero-content">
                        <h1 class="display-4 fw-bold mb-4">
                            Discover Amazing Products at <span class="text-warning">Great Prices</span>
                        </h1>
                        <p class="lead mb-4">
                            Shop from thousands of products across multiple categories.
                            Fast delivery, secure payments, and exceptional customer service.
                        </p>
                        <div class="d-flex gap-3">
                            <a href="#products" class="btn btn-light btn-lg px-4">
                                <i class="fas fa-shopping-bag me-2"></i>Shop Now
                            </a>
                            <a href="#about" class="btn btn-outline-light btn-lg px-4">
                                Learn More
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="text-center">
                        <i class="fas fa-shopping-cart display-1 text-white opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="py-5" id="about">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h2 class="display-5 fw-bold mb-4">About Our Company</h2>
                    <p class="lead mb-4">
                        We're a modern e-commerce platform dedicated to providing exceptional
                        shopping experiences. With years of experience in retail and technology,
                        we've built a platform that combines cutting-edge technology with
                        customer-centric design.
                    </p>
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-users text-primary me-3 fa-2x"></i>
                                <div>
                                    <h5 class="mb-1">10,000+ Customers</h5>
                                    <small class="text-muted">Happy shoppers worldwide</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-box text-success me-3 fa-2x"></i>
                                <div>
                                    <h5 class="mb-1">50,000+ Products</h5>
                                    <small class="text-muted">Across all categories</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-truck text-info me-3 fa-2x"></i>
                                <div>
                                    <h5 class="mb-1">Fast Delivery</h5>
                                    <small class="text-muted">Same day delivery available</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-shield-alt text-warning me-3 fa-2x"></i>
                                <div>
                                    <h5 class="mb-1">Secure Payments</h5>
                                    <small class="text-muted">100% secure transactions</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="text-center">
                        <i class="fas fa-store display-1 text-primary opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5 bg-light" id="features">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Why Choose Us?</h2>
                <p class="lead text-muted">Experience shopping like never before with our advanced features</p>
            </div>

            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div class="feature-card text-center h-100">
                        <div class="feature-icon">
                            <i class="fas fa-shipping-fast"></i>
                        </div>
                        <h5 class="card-title">Free Shipping</h5>
                        <p class="card-text">Free delivery on orders over $50. Fast and reliable shipping worldwide.
                        </p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="feature-card text-center h-100">
                        <div class="feature-icon">
                            <i class="fas fa-lock"></i>
                        </div>
                        <h5 class="card-title">Secure Payments</h5>
                        <p class="card-text">Multiple payment options with bank-level security and encryption.</p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="feature-card text-center h-100">
                        <div class="feature-icon">
                            <i class="fas fa-undo"></i>
                        </div>
                        <h5 class="card-title">Easy Returns</h5>
                        <p class="card-text">30-day return policy with hassle-free returns and exchanges.</p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="feature-card text-center h-100">
                        <div class="feature-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h5 class="card-title">24/7 Support</h5>
                        <p class="card-text">Round-the-clock customer support to help you with any queries.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Products Section -->
    <section class="py-5" id="products">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Featured Products</h2>
                <p class="lead text-muted">Discover our most popular and trending products</p>
            </div>

            <!-- Search and Filter -->
            <div class="category-filter">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="text" class="form-control form-control-lg" id="productSearch"
                                placeholder="Search products...">
                            <button class="btn btn-primary-custom" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex flex-wrap justify-content-end" id="categoryFilters">
                            <button class="btn category-btn active" data-category="all">All</button>
                            <!-- Categories will be loaded dynamically -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="row g-4" id="productsGrid">
                <!-- Products will be loaded here -->
            </div>

            <div class="text-center mt-5">
                <button class="btn btn-primary-custom btn-lg" id="loadMoreBtn">
                    <i class="fas fa-plus me-2"></i>Load More Products
                </button>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="py-5 bg-light" id="contact">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Get In Touch</h2>
                <p class="lead text-muted">Have questions? We're here to help!</p>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card shadow">
                        <div class="card-body p-5">
                            <form>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <input type="text" class="form-control form-control-lg"
                                            placeholder="Your Name">
                                    </div>
                                    <div class="col-md-6">
                                        <input type="email" class="form-control form-control-lg"
                                            placeholder="Your Email">
                                    </div>
                                    <div class="col-12">
                                        <input type="text" class="form-control form-control-lg"
                                            placeholder="Subject">
                                    </div>
                                    <div class="col-12">
                                        <textarea class="form-control form-control-lg" rows="5" placeholder="Your Message"></textarea>
                                    </div>
                                    <div class="col-12 text-center">
                                        <button type="submit" class="btn btn-primary-custom btn-lg px-5">
                                            <i class="fas fa-paper-plane me-2"></i>Send Message
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer-section">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <h5 class="text-white mb-3">
                        <i class="fas fa-shopping-bag me-2"></i>
                        {{ config('app.name', 'E-Commerce Store') }}
                    </h5>
                    <p class="text-light mb-3">
                        Your trusted online shopping destination. We offer quality products,
                        fast delivery, and exceptional customer service.
                    </p>
                    <div class="d-flex">
                        <a href="#" class="social-icon">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="social-icon">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="social-icon">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="social-icon">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-2 col-md-4">
                    <h6 class="text-white mb-3">Quick Links</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#home" class="text-light text-decoration-none">Home</a></li>
                        <li class="mb-2"><a href="#about" class="text-light text-decoration-none">About</a></li>
                        <li class="mb-2"><a href="#products" class="text-light text-decoration-none">Products</a>
                        </li>
                        <li class="mb-2"><a href="#contact" class="text-light text-decoration-none">Contact</a>
                        </li>
                    </ul>
                </div>

                <div class="col-lg-2 col-md-4">
                    <h6 class="text-white mb-3">Categories</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#" class="text-light text-decoration-none">Electronics</a>
                        </li>
                        <li class="mb-2"><a href="#" class="text-light text-decoration-none">Clothing</a>
                        </li>
                        <li class="mb-2"><a href="#" class="text-light text-decoration-none">Home &
                                Garden</a></li>
                        <li class="mb-2"><a href="#" class="text-light text-decoration-none">Sports</a></li>
                    </ul>
                </div>

                <div class="col-lg-4 col-md-4">
                    <h6 class="text-white mb-3">Contact Info</h6>
                    <div class="text-light">
                        <p class="mb-2">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            123 Commerce Street, Business City, BC 12345
                        </p>
                        <p class="mb-2">
                            <i class="fas fa-phone me-2"></i>
                            +1 (555) 123-4567
                        </p>
                        <p class="mb-2">
                            <i class="fas fa-envelope me-2"></i>
                            info@ecommerce-store.com
                        </p>
                        <p class="mb-0">
                            <i class="fas fa-clock me-2"></i>
                            Mon - Fri: 9AM - 6PM
                        </p>
                    </div>
                </div>
            </div>

            <hr class="my-4" style="border-color: rgba(255,255,255,0.1);">

            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="text-light mb-0">
                        &copy; {{ date('Y') }} {{ config('app.name', 'E-Commerce Store') }}.
                        All rights reserved.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="#" class="text-light text-decoration-none me-3">Privacy Policy</a>
                    <a href="#" class="text-light text-decoration-none me-3">Terms of Service</a>
                    <a href="#" class="text-light text-decoration-none">FAQ</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Product Detail Modal -->
    <div class="modal fade" id="productModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="productModalTitle">Product Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="productModalBody">
                    <!-- Product details will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary-custom" id="addToCartBtn">
                        <i class="fas fa-cart-plus me-2"></i>Add to Cart
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            // Load categories and products on page load
            loadCategories();
            loadProducts();

            // Smooth scrolling for navigation
            $('a[href^="#"]').on('click', function(event) {
                var target = $(this.getAttribute('href'));
                if (target.length) {
                    event.preventDefault();
                    $('html, body').stop().animate({
                        scrollTop: target.offset().top - 70
                    }, 1000);
                }
            });

            // Search functionality
            $('#productSearch').on('input', function() {
                const searchTerm = $(this).val().toLowerCase();
                filterProducts(searchTerm);
            });

            // Load more products
            $('#loadMoreBtn').on('click', function() {
                loadProducts(true);
            });
        });

        function loadCategories() {
            // Load categories from database
            $.get('/api/categories', function(data) {
                if (data.success) {
                    let html = '<button class="btn category-btn active" data-category="all">All</button>';
                    data.data.forEach(category => {
                        html +=
                            `<button class="btn category-btn" data-category="${category.id}">${category.name}</button>`;
                    });
                    $('#categoryFilters').html(html);

                    // Category filter click
                    $('.category-btn').on('click', function() {
                        $('.category-btn').removeClass('active');
                        $(this).addClass('active');
                        const categoryId = $(this).data('category');
                        filterProducts('', categoryId);
                    });
                }
            });
        }

        function loadProducts(append = false) {
            const url = append ? '/api/products?page=' + (currentPage + 1) : '/api/products';
            $.get(url, function(data) {
                if (data.success) {
                    if (!append) {
                        $('#productsGrid').empty();
                    }

                    data.data.forEach(product => {
                        const productHtml = `
                            <div class="col-lg-3 col-md-6 product-item" data-category="${product.category_id}"
                                 data-name="${product.name.toLowerCase()}" data-sku="${product.sku.toLowerCase()}">
                                <div class="product-card h-100">
                                    <div class="product-image">
                                        <i class="fas fa-box"></i>
                                    </div>
                                    <div class="card-body d-flex flex-column">
                                        <h6 class="card-title">${product.name}</h6>
                                        <p class="card-text text-muted small">${product.category?.name || 'Uncategorized'}</p>
                                        <div class="mt-auto">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="product-price">$${parseFloat(product.price).toFixed(2)}</span>
                                                <span class="badge bg-success">${product.stock_quantity} in stock</span>
                                            </div>
                                            <button class="btn btn-primary-custom w-100 mt-2 view-product-btn"
                                                    data-product-id="${product.id}">
                                                <i class="fas fa-eye me-1"></i>View Details
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        $('#productsGrid').append(productHtml);
                    });

                    // View product details
                    $('.view-product-btn').on('click', function() {
                        const productId = $(this).data('product-id');
                        viewProductDetails(productId);
                    });

                    if (append) {
                        currentPage++;
                    } else {
                        currentPage = 1;
                    }

                    // Hide load more if no more products
                    if (!data.next_page_url) {
                        $('#loadMoreBtn').hide();
                    } else {
                        $('#loadMoreBtn').show();
                    }
                }
            });
        }

        function filterProducts(searchTerm = '', categoryId = 'all') {
            $('.product-item').each(function() {
                const productName = $(this).data('name');
                const productSku = $(this).data('sku');
                const productCategory = $(this).data('category');

                const matchesSearch = !searchTerm ||
                    productName.includes(searchTerm) ||
                    productSku.includes(searchTerm);

                const matchesCategory = categoryId === 'all' ||
                    productCategory == categoryId;

                if (matchesSearch && matchesCategory) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }

        function viewProductDetails(productId) {
            $.get(`/api/products/${productId}`, function(data) {
                if (data.success) {
                    const product = data.data;
                    const modalHtml = `
                        <div class="row">
                            <div class="col-md-6">
                                <div class="product-image mb-3" style="height: 300px;">
                                    <i class="fas fa-box fa-5x"></i>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h4>${product.name}</h4>
                                <p class="text-muted">${product.category?.name || 'Uncategorized'}</p>
                                <h5 class="text-primary mb-3">$${parseFloat(product.price).toFixed(2)}</h5>

                                <div class="mb-3">
                                    <h6>Description:</h6>
                                    <p>${product.description || 'No description available.'}</p>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-sm-6">
                                        <small class="text-muted">SKU: ${product.sku}</small>
                                    </div>
                                    <div class="col-sm-6">
                                        <small class="text-muted">Stock: ${product.stock_quantity}</small>
                                    </div>
                                </div>

                                ${product.allow_installments ? `
                                        <div class="alert alert-info">
                                            <i class="fas fa-calendar-alt me-2"></i>
                                            Installments available (up to ${product.max_installments} months)
                                        </div>
                                    ` : ''}
                            </div>
                        </div>
                    `;

                    $('#productModalBody').html(modalHtml);
                    $('#productModal').modal('show');
                }
            });
        }

        let currentPage = 1;
    </script>
</body>

</html>
