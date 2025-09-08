@extends('layouts.app')

@section('title', 'Create Tenant')
@section('page-title', 'Create Tenant')

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.tenants.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Name *</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Domain *</label>
                    <input type="text" name="domain" class="form-control" required placeholder="example.com">
                </div>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" checked>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>
                <button class="btn btn-primary" type="submit">Create</button>
                <a class="btn btn-secondary" href="{{ route('admin.tenants.index') }}">Cancel</a>
            </form>
        </div>
    </div>
@endsection
