@extends('layouts.auth')

@section('content')
<div class="auth-form">
    <div class="auth-header">
        <h2>Create Account</h2>
        <p>Join us today</p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="mb-4">
            <label for="name" class="form-label">Name</label>
            <input id="name" type="text" class="form-control form-control-lg @error('name') is-invalid @enderror"
                   name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <label for="email" class="form-label">Email Address</label>
            <input id="email" type="email" class="form-control form-control-lg @error('email') is-invalid @enderror"
                   name="email" value="{{ old('email') }}" required autocomplete="email">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <label for="password" class="form-label">Password</label>
            <input id="password" type="password" class="form-control form-control-lg @error('password') is-invalid @enderror"
                   name="password" required autocomplete="new-password">
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <label for="password-confirm" class="form-label">Confirm Password</label>
            <input id="password-confirm" type="password" class="form-control form-control-lg @error('password_confirmation') is-invalid @enderror"
                   name="password_confirmation" required autocomplete="new-password">
            @error('password_confirmation')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-auth btn-lg">
            <i class="fas fa-user-plus me-2"></i>Create Account
        </button>
    </form>

    <div class="auth-links mt-4">
        <span>Already have an account? </span>
        <a href="{{ route('login') }}">Sign In</a>
    </div>
</div>
@endsection
