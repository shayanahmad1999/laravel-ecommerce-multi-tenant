@extends('layouts.auth')

@section('content')
    <div class="auth-form">
        <div class="auth-header">
            <h2>Welcome Back</h2>
            <p>Sign in to your account</p>
        </div>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-4">
                <label for="email" class="form-label">Email Address</label>
                <input id="email" type="email" class="form-control form-control-lg @error('email') is-invalid @enderror"
                    name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <input id="password" type="password"
                    class="form-control form-control-lg @error('password') is-invalid @enderror" name="password" required
                    autocomplete="current-password">
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4 d-flex justify-content-between align-items-center">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember"
                        {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label" for="remember">
                        Remember Me
                    </label>
                </div>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-decoration-none">
                        Forgot Password?
                    </a>
                @endif
            </div>

            <button type="submit" class="btn btn-auth btn-lg">
                <i class="fas fa-sign-in-alt me-2"></i>Sign In
            </button>
        </form>

        <div class="auth-links mt-4">
            <span>Don't have an account? </span>
            <a href="{{ route('register') }}">Sign Up</a>
        </div>
    </div>
@endsection
