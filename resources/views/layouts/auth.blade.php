<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - Authentication</title>

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
            --success-color: #10b981;
            --danger-color: #ef4444;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, var(--primary-color) 0%, #1d4ed8 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }

        .auth-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 1200px;
            width: 100%;
            min-height: 500px;
        }

        .auth-left {
            background: linear-gradient(135deg, var(--primary-color) 0%, #1d4ed8 100%);
            color: white;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .auth-left h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .auth-left p {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 30px;
        }

        .auth-right {
            padding: 40px;
            background: white;
            flex: 1;
            max-width: 600px;
        }

        .auth-form .form-control {
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .auth-form .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .auth-form .form-label {
            font-weight: 500;
            color: var(--dark-color);
            margin-bottom: 8px;
        }

        .btn-auth {
            background: var(--primary-color);
            border: none;
            border-radius: 12px;
            padding: 12px 24px;
            font-weight: 600;
            font-size: 16px;
            width: 100%;
            transition: all 0.3s ease;
        }

        .btn-auth:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.3);
        }

        .auth-links {
            text-align: center;
            margin-top: 20px;
        }

        .auth-links a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .auth-links a:hover {
            color: #1d4ed8;
            text-decoration: underline;
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 20px 0;
            color: #6b7280;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e5e7eb;
        }

        .divider::before {
            margin-right: 16px;
        }

        .divider::after {
            margin-left: 16px;
        }

        .social-btn {
            width: 100%;
            border: 2px solid #e5e7eb;
            background: white;
            color: #6b7280;
            border-radius: 12px;
            padding: 12px 24px;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-bottom: 12px;
        }

        .social-btn:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
            transform: translateY(-1px);
        }

        .auth-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .auth-header h2 {
            color: var(--dark-color);
            font-weight: 700;
            margin-bottom: 8px;
        }

        .auth-header p {
            color: #6b7280;
            margin: 0;
        }

        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .invalid-feedback {
            display: block;
            color: var(--danger-color);
            font-size: 14px;
            margin-top: 4px;
        }

        .is-invalid {
            border-color: var(--danger-color) !important;
        }

        .is-invalid:focus {
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
        }

        @media (max-width: 768px) {
            .auth-container {
                flex-direction: column;
                max-width: 100%;
            }

            .auth-left {
                padding: 30px 20px;
            }

            .auth-left h1 {
                font-size: 2rem;
            }

            .auth-right {
                padding: 30px 20px;
            }
        }

        .back-to-home {
            position: absolute;
            top: 20px;
            left: 20px;
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .back-to-home:hover {
            color: #e5e7eb;
            text-decoration: underline;
        }

        .logo {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .logo i {
            color: var(--accent-color);
            margin-right: 8px;
        }
    </style>
</head>

<body>
    <a href="{{ route('home') }}" class="back-to-home">
        <i class="fas fa-arrow-left me-2"></i>Back to Home
    </a>

    <div class="auth-container d-flex">
        <div class="auth-left d-none d-md-flex">
            <div>
                <div class="logo">
                    <i class="fas fa-shopping-bag"></i>
                    {{ config('app.name', 'E-Commerce Store') }}
                </div>
                <h1>Welcome Back!</h1>
                <p>Discover amazing products and enjoy seamless shopping experience with our modern e-commerce platform.
                </p>
                <div class="mt-4">
                    <i class="fas fa-check-circle me-2"></i>Secure Payments<br>
                    <i class="fas fa-check-circle me-2"></i>Fast Delivery<br>
                    <i class="fas fa-check-circle me-2"></i>24/7 Support<br>
                    <i class="fas fa-check-circle me-2"></i>Quality Products
                </div>
            </div>
        </div>

        <div class="auth-right flex-fill">
            @yield('content')
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
