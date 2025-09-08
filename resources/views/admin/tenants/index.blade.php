@extends('layouts.app')

@section('title', 'Tenants')
@section('page-title', 'Tenants Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Tenants</h4>
                    <a href="{{ route('admin.tenants.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Create Tenant
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Domain</th>
                                    <th>Database</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($tenants as $tenant)
                                    <tr>
                                        <td>{{ $tenant->name }}</td>
                                        <td>{{ $tenant->domain }}</td>
                                        <td>{{ $tenant->database ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $tenant->is_active ? 'success' : 'secondary' }}">
                                                {{ $tenant->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td>{{ $tenant->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-outline-primary"
                                                    onclick="toggleTenant({{ $tenant->id }})">
                                                    <i class="fas fa-toggle-{{ $tenant->is_active ? 'on' : 'off' }}"></i>
                                                    Toggle
                                                </button>
                                                <a href="{{ route('admin.tenants.edit', $tenant) }}"
                                                   class="btn btn-sm btn-warning">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <form action="{{ route('admin.tenants.destroy', $tenant) }}"
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('Are you sure you want to delete this tenant? This action cannot be undone.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No tenants found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $tenants->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        function toggleTenant(id) {
            showLoading();
            $.post('/admin/tenants/' + id + '/toggle', {
                _method: 'PUT'
            }, function(response) {
                hideLoading();
                if (response.success) {
                    location.reload();
                } else {
                    showAlert('danger', 'Failed to update tenant');
                }
            });
        }
    </script>
@endpush
