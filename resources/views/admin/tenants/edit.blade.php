@extends('layouts.app')

@section('title', 'Edit Tenant')
@section('page-title', 'Edit Tenant')

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.tenants.update', $tenant) }}">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Name *</label>
                    <input type="text" name="name" class="form-control" value="{{ $tenant->name }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Domain *</label>
                    <input type="text" name="domain" class="form-control" value="{{ $tenant->domain }}" required>
                </div>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                        {{ $tenant->is_active ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>
                <button class="btn btn-primary" type="submit">Save</button>
                <a class="btn btn-secondary" href="{{ route('admin.tenants.index') }}">Cancel</a>
            </form>
            <form class="mt-3" method="POST" action="{{ route('admin.tenants.destroy', $tenant) }}"
                onsubmit="return confirm('Delete this tenant?')">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger" type="submit">Delete Tenant</button>
            </form>
        </div>
    </div>
@endsection
