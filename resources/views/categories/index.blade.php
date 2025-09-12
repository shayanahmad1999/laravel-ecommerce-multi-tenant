@extends('layouts.app')

@section('title', 'Categories Management')
@section('page-title', 'Categories')

@section('page-actions')
    @can('manage categories')
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal" onclick="openCreateModal()">
        <i class="fas fa-plus me-2"></i>Add Category
    </button>
    @endcan
@endsection

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col">
                <h5 class="mb-0">Categories List</h5>
            </div>
            <div class="col-auto">
                <div class="row g-2">
                    <div class="col">
                        <input type="text" class="form-control" id="searchInput" placeholder="Search categories...">
                    </div>
                    <div class="col">
                        <select class="form-select" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col">
                        <button type="button" class="btn btn-outline-secondary" onclick="refreshTable()">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="categoriesTable" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Parent Category</th>
                        <th>Products Count</th>
                        <th>Status</th>
                        <th>Sort Order</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="categoriesTableBody">
                    <!-- Categories will be loaded via AJAX -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <form id="categoryForm" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="categoryModalLabel">Add Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="categoryId" name="id">
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Name *</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="parent_id" class="form-label">Parent Category</label>
                        <select class="form-select" id="parent_id" name="parent_id">
                            <option value="">Select Parent Category</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="image" class="form-label">Image</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        <div id="currentImage"></div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="sort_order" class="form-label">Sort Order</label>
                                <input type="number" class="form-control" id="sort_order" name="sort_order" value="0" min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                                    <label class="form-check-label" for="is_active">Active</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // CSRF header for all AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Initialize DataTable
    const table = $('#categoriesTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: '{{ route("categories.index") }}',
            type: 'GET',
            data: function(d) {
                // Add custom search parameters
                d.search = $('#searchInput').val();
                d.status = $('#statusFilter').val();
            },
            dataSrc: function(json) {
                // DataTables expects the data array directly
                return json.data || [];
            },
            error: function(xhr, error, thrown) {
                console.error('DataTables error:', error, thrown);
                $('#categoriesTableBody').html('<tr><td colspan="7" class="text-center text-danger">Error loading data</td></tr>');
            }
        },
        columns: [
            { data: 'id' },
            { 
                data: 'name',
                render: function(data, type, row) {
                    return `<div class="d-flex align-items-center">
                        ${row.image ? `<img src="/storage/${row.image}" class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">` : ''}
                        <div>
                            <strong>${data}</strong>
                            ${row.description ? `<br><small class="text-muted">${row.description}</small>` : ''}
                        </div>
                    </div>`;
                }
            },
            { 
                data: 'parent',
                render: function(data, type, row) {
                    return data ? data.name : '<span class="text-muted">Root Category</span>';
                }
            },
            { 
                data: 'products_count',
                defaultContent: '0'
            },
            { 
                data: 'is_active',
                render: function(data, type, row) {
                    return data 
                        ? '<span class="badge bg-success">Active</span>' 
                        : '<span class="badge bg-danger">Inactive</span>';
                }
            },
            { data: 'sort_order' },
            { 
                data: null,
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    let actions = `
                        <button class="btn btn-sm btn-info btn-action" onclick="viewCategory(${row.id})" title="View">
                            <i class="fas fa-eye"></i>
                        </button>`;
                    
                    @can('manage categories')
                    actions += `
                        <button class="btn btn-sm btn-warning btn-action" onclick="editCategory(${row.id})" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger btn-action" onclick="deleteCategory(${row.id})" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>`;
                    @endcan
                    
                    return actions;
                }
            }
        ],
        pageLength: 25,
        order: [[0, 'desc']],
        language: {
            emptyTable: "No categories found"
        }
    });

    // Search functionality
    $('#searchInput').on('keyup', function() {
        table.ajax.reload();
    });

    $('#statusFilter').on('change', function() {
        table.ajax.reload();
    });

    // Form submission
    $('#categoryForm').on('submit', function(e) {
        e.preventDefault();
        showLoading();

        const formData = new FormData(this);
        const categoryId = $('#categoryId').val();
        const url = categoryId ? `/categories/${categoryId}` : '{{ route("categories.store") }}';
        const method = categoryId ? 'PUT' : 'POST';
        
        if (method === 'PUT') {
            formData.append('_method', 'PUT');
        }

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                hideLoading();
                if (response.success) {
                    $('#categoryModal').modal('hide');
                    showAlert('success', response.message);
                    table.ajax.reload();
                    resetForm();
                } else {
                    showAlert('danger', response.message);
                }
            },
            error: function(xhr) {
                hideLoading();
                if (xhr.status === 422) {
                    displayValidationErrors(xhr.responseJSON.errors);
                }
            }
        });
    });

    // Load parent categories for select dropdown
    loadParentCategories();
});

function refreshTable() {
    $('#categoriesTable').DataTable().ajax.reload();
}

function showLoading() {
    $('#submitBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Loading...');
}

function hideLoading() {
    $('#submitBtn').prop('disabled', false).html('Save Category');
}

function showAlert(type, message) {
    const id = 'alert-' + Math.random().toString(36).slice(2);
    const html = `<div id="${id}" class="alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3" role="alert" style="z-index:1080; min-width: 260px;">
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>`;
    $('body').append(html);
    setTimeout(() => $('#' + id).alert('close'), 4000);
}

function openCreateModal() {
    resetForm();
    $('#categoryModalLabel').text('Add Category');
    $('#submitBtn').text('Save Category');
    $('#categoryModal').modal('show');
}

function editCategory(id) {
    showLoading();
    $.get(`/categories/${id}`, function(response) {
        hideLoading();
        if (response.success) {
            const category = response.data;
            $('#categoryId').val(category.id);
            $('#name').val(category.name);
            $('#description').val(category.description);
            $('#parent_id').val(category.parent_id);
            $('#sort_order').val(category.sort_order);
            $('#is_active').prop('checked', category.is_active);
            
            if (category.image) {
                $('#currentImage').html(`<img src="/storage/${category.image}" class="img-thumbnail mt-2" style="max-width: 200px;">`);
            }
            
            $('#categoryModalLabel').text('Edit Category');
            $('#submitBtn').text('Update Category');
            $('#categoryModal').modal('show');
        }
    });
}

function viewCategory(id) {
    showLoading();
    $.get(`/categories/${id}`, function(response) {
        hideLoading();
        if (response.success) {
            // Implement view modal or redirect to show page
            window.location.href = `/categories/${id}`;
        }
    });
}

function deleteCategory(id) {
    if (confirm('Are you sure you want to delete this category?')) {
        showLoading();
        $.ajax({
            url: `/categories/${id}`,
            method: 'DELETE',
            success: function(response) {
                hideLoading();
                if (response.success) {
                    showAlert('success', response.message);
                    $('#categoriesTable').DataTable().ajax.reload();
                } else {
                    showAlert('danger', response.message);
                }
            }
        });
    }
}

function resetForm() {
    $('#categoryForm')[0].reset();
    $('#categoryId').val('');
    $('#currentImage').empty();
    $('.invalid-feedback').remove();
    $('.is-invalid').removeClass('is-invalid');
}

function loadParentCategories() {
    $.get('/categories/select', function(response) {
        if (response.success) {
            const select = $('#parent_id');
            select.empty().append('<option value="">Select Parent Category</option>');
            response.data.forEach(function(category) {
                if (!category.parent_id) { // Only root categories
                    select.append(`<option value="${category.id}">${category.name}</option>`);
                }
            });
        }
    });
}

function displayValidationErrors(errors) {
    $('.invalid-feedback').remove();
    $('.is-invalid').removeClass('is-invalid');
    
    for (const field in errors) {
        const input = $(`#${field}`);
        input.addClass('is-invalid');
        input.after(`<div class="invalid-feedback">${errors[field].join('<br>')}</div>`);
    }
}
</script>
@endpush
