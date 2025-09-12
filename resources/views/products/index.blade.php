@extends('layouts.app')

@section('title', 'Products Management')
@section('page-title', 'Products')

@section('page-actions')
    @can('manage products')
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#productModal"
            onclick="openCreateModal()">
            <i class="fas fa-plus me-2"></i>Add Product
        </button>
    @endcan
@endsection

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="card">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0">Products List</h5>
                </div>
                <div class="col-auto">
                    <div class="row g-2">
                        <div class="col">
                            <input type="text" class="form-control" id="searchInput" placeholder="Search products...">
                        </div>
                        <div class="col">
                            <select class="form-select" id="categoryFilter">
                                <option value="">All Categories</option>
                            </select>
                        </div>
                        <div class="col">
                            <select class="form-select" id="statusFilter">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="low_stock">Low Stock</option>
                            </select>
                        </div>
                        <div class="col">
                            <button type="button" class="btn btn-outline-secondary" onclick="refreshTable()"
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
                <table id="productsTable" class="table table-striped table-hover w-100">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Product</th>
                            <th>SKU</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th style="width: 140px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="productsTableBody">
                        <!-- Products will be loaded via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Product Modal -->
    <div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <form id="productForm" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="productModalLabel">Add Product</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                            id="productModalCloseBtn"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="productId" name="id">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Product Name *</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="sku" class="form-label">SKU</label>
                                    <input type="text" class="form-control" id="sku" name="sku">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="category_id" class="form-label">Category *</label>
                                    <select class="form-select" id="category_id" name="category_id" required>
                                        <option value="">Select Category</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="images" class="form-label">Product Images</label>
                                    <input type="file" class="form-control" id="images" name="images[]"
                                        accept="image/*" multiple>
                                    <div id="currentImages" class="mt-2"></div>
                                    <div id="newImagesPreview" class="mt-2 d-flex flex-wrap gap-2"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="price" class="form-label">Price *</label>
                                    <input type="number" step="0.01" class="form-control" id="price"
                                        name="price" required min="0">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="cost_price" class="form-label">Cost Price</label>
                                    <input type="number" step="0.01" class="form-control" id="cost_price"
                                        name="cost_price" min="0">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="stock_quantity" class="form-label">Stock Quantity *</label>
                                    <input type="number" class="form-control" id="stock_quantity" name="stock_quantity"
                                        required min="0">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="min_stock_level" class="form-label">Minimum Stock Level *</label>
                                    <input type="number" class="form-control" id="min_stock_level"
                                        name="min_stock_level" required min="0">
                                </div>
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                            checked>
                                        <label class="form-check-label" for="is_active">Active</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Installment Settings -->
                        <div class="card mt-3">
                            <div class="card-header">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="allow_installments"
                                        name="allow_installments">
                                    <label class="form-check-label" for="allow_installments">Allow Installment
                                        Payments</label>
                                </div>
                            </div>
                            <div class="card-body" id="installmentSettings" style="display:none;">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="max_installments" class="form-label">Maximum Installments</label>
                                            <input type="number" class="form-control" id="max_installments"
                                                name="max_installments" min="2" max="60" value="12">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="installment_interest_rate" class="form-label">Interest Rate
                                                (%)</label>
                                            <input type="number" step="0.01" class="form-control"
                                                id="installment_interest_rate" name="installment_interest_rate"
                                                min="0" max="100" value="0">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /Installment Settings -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                            id="productModalCancelBtn">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <span class="btn-text">Save Product</span>
                            <span class="spinner-border spinner-border-sm ms-2 d-none" role="status" aria-hidden="true"
                                id="submitLoading"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Stock Update Modal -->
    <div class="modal fade" id="stockModal" tabindex="-1" aria-labelledby="stockModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <form id="stockForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="stockModalLabel">Update Stock</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                            id="stockModalCloseBtn"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="stockProductId">
                        <div class="mb-3">
                            <label for="new_stock_quantity" class="form-label">New Stock Quantity</label>
                            <input type="number" class="form-control" id="new_stock_quantity" name="stock_quantity"
                                required min="0">
                        </div>
                        <div id="currentStockInfo" class="text-muted small"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                            id="stockModalCancelBtn">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="btn-text">Update Stock</span>
                            <span class="spinner-border spinner-border-sm ms-2 d-none" role="status" aria-hidden="true"
                                id="stockSubmitLoading"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // --------- Config & helpers ---------
        window.PRODUCTS = {
            routes: {
                index: @json(route('products.index')), // should return JSON when requested via AJAX
                store: @json(route('products.store')),
                showBase: @json(url('/products')), // append /{id}
                updateBase: @json(url('/products')), // append /{id}
                destroyBase: @json(url('/products')), // append /{id}
                stockUpdateBase: @json(url('/products')), // append /{id}/stock
                categoriesSelect: @json(url('/categories/select')), // replace with route() if you have it
            }
        };

        const fmtCurrency = (n) => {
            const num = Number(n || 0);
            return '$' + num.toFixed(2);
        };

        const debounce = (fn, ms = 400) => {
            let t;
            return (...args) => {
                clearTimeout(t);
                t = setTimeout(() => fn.apply(this, args), ms);
            };
        };
    </script>

    <script>
        $(document).ready(function() {
            // CSRF for all AJAX
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // --------- DataTable ---------
            const table = $('#productsTable').DataTable({
                processing: true,
                serverSide: true, // enable server-side processing
                responsive: true,
                ajax: {
                    url: window.PRODUCTS.routes.index,
                    type: 'GET',
                    data: function(d) {
                        // Add custom search parameters
                        d.search = $('#searchInput').val();
                        d.category_id = $('#categoryFilter').val();
                        d.status = $('#statusFilter').val();
                    },
                    dataSrc: function(json) {
                        // DataTables expects the data array directly
                        return json.data || [];
                    },
                    error: function(xhr, error, thrown) {
                        console.error('DataTables error:', error, thrown);
                        $('#productsTableBody').html(
                            '<tr><td colspan="8" class="text-center text-danger">Failed to load products.</td></tr>'
                        );
                    }
                },
                columns: [{
                        data: 'id'
                    },
                    {
                        data: 'name',
                        render: function(data, type, row) {
                            const img = (row.images && row.images[0]) ?
                                `/storage/${row.images[0]}` : null;
                            const safeDesc = row.description ? String(row.description).substring(0,
                                60) : '';
                            const thumb = img ?
                                `<img src="${img}" class="rounded me-2" style="width:50px;height:50px;object-fit:cover;">` :
                                `<div class="rounded me-2 bg-light d-flex align-items-center justify-content-center" style="width:50px;height:50px;"><i class="fas fa-image text-muted"></i></div>`;
                            return `
                        <div class="d-flex align-items-center">
                            ${thumb}
                            <div>
                                <strong>${data || 'Unnamed'}</strong>
                                ${safeDesc ? `<br><small class="text-muted">${safeDesc}${row.description.length > 60 ? '…' : ''}</small>` : ''}
                            </div>
                        </div>`;
                        }
                    },
                    {
                        data: 'sku',
                        defaultContent: ''
                    },
                    {
                        data: 'category',
                        render: function(data) {
                            return data && data.name ? data.name :
                                '<span class="text-muted">No Category</span>';
                        }
                    },
                    {
                        data: 'price',
                        render: function(data) {
                            return fmtCurrency(data);
                        }
                    },
                    {
                        data: 'stock_quantity',
                        render: function(data, type, row) {
                            const qty = Number(data || 0);
                            const minLvl = Number(row.min_stock_level || 0);
                            const badgeClass = qty <= 0 ? 'bg-danger' : (qty <= minLvl ?
                                'bg-warning' : 'bg-success');
                            return `<span class="badge ${badgeClass}" onclick="updateStock(${row.id}, ${qty})" style="cursor:pointer;" title="Click to update stock">${qty}</span>`;
                        }
                    },
                    {
                        data: 'is_active',
                        render: function(data) {
                            return data ?
                                '<span class="badge bg-success">Active</span>' :
                                '<span class="badge bg-danger">Inactive</span>';
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(_, __, row) {
                            let actions = `
                        <button class="btn btn-sm btn-info btn-action me-1" onclick="viewProduct(${row.id})" title="View">
                            <i class="fas fa-eye"></i>
                        </button>`;
                            @can('manage products')
                                actions += `
                        <button class="btn btn-sm btn-warning btn-action me-1" onclick="editProduct(${row.id})" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger btn-action" onclick="deleteProduct(${row.id})" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>`;
                            @endcan
                            return actions;
                        }
                    }
                ],
                pageLength: 25,
                order: [
                    [0, 'desc']
                ],
                language: {
                    emptyTable: "No products found"
                }
            });

            // --------- Filters / Search ---------
            $('#searchInput').on('keyup', debounce(function() {
                table.ajax.reload();
            }, 300));

            $('#categoryFilter, #statusFilter').on('change', function() {
                table.ajax.reload();
            });

            // --------- Installments toggle ---------
            $('#allow_installments').on('change', function() {
                const on = $(this).is(':checked');
                $('#installmentSettings').toggle(on);
                $('#max_installments, #installment_interest_rate').prop('required', on);
            });

            // --------- Form submit (Create/Update) ---------
            $('#productForm').on('submit', function(e) {
                e.preventDefault();
                toggleSubmitLoading(true);

                const formData = new FormData(this);
                const productId = $('#productId').val();
                const isUpdate = !!productId;

                const url = isUpdate ?
                    `${window.PRODUCTS.routes.updateBase}/${productId}` :
                    window.PRODUCTS.routes.store;

                // Laravel method spoof for PUT
                if (isUpdate) formData.append('_method', 'PUT');

                $.ajax({
                    url,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        toggleSubmitLoading(false);
                        if (res?.success) {
                            $('#productModal').modal('hide');
                            showAlert('success', res.message || 'Saved successfully');
                            table.ajax.reload(null, false);
                            resetProductForm();
                        } else {
                            showAlert('danger', res?.message || 'Something went wrong');
                        }
                    },
                    error: function(xhr) {
                        toggleSubmitLoading(false);
                        if (xhr.status === 422) {
                            displayValidationErrors(xhr.responseJSON.errors);
                        } else {
                            showAlert('danger', 'Failed to save product');
                        }
                    }
                });
            });

            // --------- Stock submit ---------
            $('#stockForm').on('submit', function(e) {
                e.preventDefault();
                toggleStockSubmitLoading(true);

                const productId = $('#stockProductId').val();
                const stockQuantity = $('#new_stock_quantity').val();

                $.ajax({
                    url: `${window.PRODUCTS.routes.stockUpdateBase}/${productId}/stock`,
                    method: 'PUT',
                    data: {
                        stock_quantity: stockQuantity
                    },
                    success: function(res) {
                        toggleStockSubmitLoading(false);
                        if (res?.success) {
                            $('#stockModal').modal('hide');
                            showAlert('success', res.message || 'Stock updated');
                            table.ajax.reload(null, false);
                        } else {
                            showAlert('danger', res?.message || 'Failed to update stock');
                        }
                    },
                    error: function() {
                        toggleStockSubmitLoading(false);
                        showAlert('danger', 'Failed to update stock');
                    }
                });
            });

            // --------- Images live preview (new uploads) ---------
            $('#images').on('change', function() {
                const container = $('#newImagesPreview').empty();
                const files = this.files || [];
                Array.from(files).forEach(file => {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        container.append(
                            `<img src="${e.target.result}" class="img-thumbnail" style="width:100px;height:100px;object-fit:cover;">`
                        );
                    };
                    reader.readAsDataURL(file);
                });
            });

            // Load categories (filter + form)
            loadCategories();
        });

        // --------- UI helpers ---------
        function refreshTable() {
            $('#productsTable').DataTable().ajax.reload();
        }

        function toggleSubmitLoading(on) {
            $('#submitBtn').prop('disabled', on);
            $('#submitLoading').toggleClass('d-none', !on);
        }

        function toggleStockSubmitLoading(on) {
            $('#stockForm button[type=submit]').prop('disabled', on);
            $('#stockSubmitLoading').toggleClass('d-none', !on);
        }

        function showAlert(type, message) {
            // Use Bootstrap alert in a toast zone, or swap for your preferred notifier
            const id = 'alert-' + Math.random().toString(36).slice(2);
            const html = `
      <div id="${id}" class="alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3" role="alert" style="z-index:1080; min-width: 260px;">
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>`;
            $('body').append(html);
            setTimeout(() => {
                $('#' + id).alert('close');
            }, 4000);
        }

        function displayValidationErrors(errors) {
            $('.invalid-feedback').remove();
            $('.is-invalid').removeClass('is-invalid');

            for (const field in errors) {
                const input = $(`#${field}`);
                input.addClass('is-invalid');
                const msgs = Array.isArray(errors[field]) ? errors[field].join('<br>') : errors[field];
                input.after(`<div class="invalid-feedback">${msgs}</div>`);
            }
        }

        // --------- Modal actions ---------
        function openCreateModal() {
            resetProductForm();
            $('#productModalLabel').text('Add Product');
            $('#submitBtn .btn-text').text('Save Product');
            $('#productModal').modal('show');
        }

        function resetProductForm() {
            $('#productForm')[0].reset();
            $('#productId').val('');
            $('#currentImages').empty();
            $('#newImagesPreview').empty();
            $('#installmentSettings').hide();
            $('#allow_installments').prop('checked', false);
            $('.invalid-feedback').remove();
            $('.is-invalid').removeClass('is-invalid');
        }

        // --------- CRUD actions ---------
        function editProduct(id) {
            $.get(`${window.PRODUCTS.routes.showBase}/${id}`, function(res) {
                if (res?.success) {
                    const p = res.data;
                    $('#productId').val(p.id);
                    $('#name').val(p.name);
                    $('#description').val(p.description);
                    $('#sku').val(p.sku);
                    $('#price').val(p.price);
                    $('#cost_price').val(p.cost_price);
                    $('#stock_quantity').val(p.stock_quantity);
                    $('#min_stock_level').val(p.min_stock_level);
                    $('#category_id').val(p.category_id);
                    $('#is_active').prop('checked', !!p.is_active);
                    $('#allow_installments').prop('checked', !!p.allow_installments);
                    $('#max_installments').val(p.max_installments || 12);
                    $('#installment_interest_rate').val(p.installment_interest_rate || 0);

                    // toggle installment pane
                    $('#installmentSettings').toggle(!!p.allow_installments);

                    // current images
                    const cur = $('#currentImages').empty();
                    if (Array.isArray(p.images) && p.images.length) {
                        cur.append('<div class="mt-2"><strong>Current Images:</strong><br></div>');
                        p.images.forEach(img => {
                            cur.append(
                                `<img src="/storage/${img}" class="img-thumbnail me-2 mb-2" style="width:100px;height:100px;object-fit:cover;">`
                            );
                        });
                    }

                    $('#productModalLabel').text('Edit Product');
                    $('#submitBtn .btn-text').text('Update Product');
                    $('#productModal').modal('show');
                } else {
                    showAlert('danger', res?.message || 'Failed to fetch product');
                }
            }).fail(() => showAlert('danger', 'Failed to fetch product'));
        }

        function viewProduct(id) {
            // If your /products/{id} returns an HTML page, just navigate.
            window.location.href = `${window.PRODUCTS.routes.showBase}/${id}`;
        }

        function deleteProduct(id) {
            if (!confirm('Are you sure you want to delete this product?')) return;

            $.ajax({
                url: `${window.PRODUCTS.routes.destroyBase}/${id}`,
                method: 'POST',
                data: {
                    _method: 'DELETE'
                },
                success: function(res) {
                    if (res?.success) {
                        showAlert('success', res.message || 'Product deleted');
                        $('#productsTable').DataTable().ajax.reload(null, false);
                    } else {
                        showAlert('danger', res?.message || 'Failed to delete product');
                    }
                },
                error: function() {
                    showAlert('danger', 'Failed to delete product');
                }
            });
        }

        function updateStock(productId, currentStock) {
            $('#stockProductId').val(productId);
            $('#new_stock_quantity').val(currentStock);
            $('#currentStockInfo').text(`Current stock: ${currentStock}`);
            $('#stockModal').modal('show');
        }

        // --------- Data bootstrapping ---------
        function loadCategories() {
            $.get(window.PRODUCTS.routes.categoriesSelect, function(res) {
                if (res?.success) {
                    const list = res.data || [];
                    // filter select
                    const filterSelect = $('#categoryFilter');
                    filterSelect.empty().append('<option value="">All Categories</option>');

                    // form select
                    const formSelect = $('#category_id');
                    formSelect.empty().append('<option value="">Select Category</option>');

                    list.forEach(cat => {
                        filterSelect.append(`<option value="${cat.id}">${cat.name}</option>`);
                        formSelect.append(`<option value="${cat.id}">${cat.name}</option>`);
                    });
                }
            });
        }
    </script>
@endpush
