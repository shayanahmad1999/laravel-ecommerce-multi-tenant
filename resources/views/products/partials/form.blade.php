<form method="POST" action="{{ $mode === 'edit' ? route('products.update', $product) : route('products.store') }}" enctype="multipart/form-data">
    @csrf
    @if($mode === 'edit')
        @method('PUT')
    @endif

    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Name *</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $product->name ?? '') }}" required>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">SKU</label>
                <input type="text" name="sku" class="form-control" value="{{ old('sku', $product->sku ?? '') }}">
            </div>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="3">{{ old('description', $product->description ?? '') }}</textarea>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Category *</label>
                <select name="category_id" class="form-select" required>
                    <option value="">Select Category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id', $product->category_id ?? '') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Images</label>
                <input type="file" name="images[]" class="form-control" multiple>
                @if(!empty($product?->images))
                    <div class="mt-2">
                        @foreach($product->images as $image)
                            <img src="/storage/{{ $image }}" class="img-thumbnail me-2 mb-2" style="max-width: 100px;">
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="mb-3">
                <label class="form-label">Price *</label>
                <input type="number" step="0.01" name="price" class="form-control" value="{{ old('price', $product->price ?? '') }}" required>
            </div>
        </div>
        <div class="col-md-4">
            <div class="mb-3">
                <label class="form-label">Cost Price</label>
                <input type="number" step="0.01" name="cost_price" class="form-control" value="{{ old('cost_price', $product->cost_price ?? '') }}">
            </div>
        </div>
        <div class="col-md-4">
            <div class="mb-3">
                <label class="form-label">Stock Quantity *</label>
                <input type="number" name="stock_quantity" class="form-control" value="{{ old('stock_quantity', $product->stock_quantity ?? 0) }}" required>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Minimum Stock Level *</label>
                <input type="number" name="min_stock_level" class="form-control" value="{{ old('min_stock_level', $product->min_stock_level ?? 0) }}" required>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3 form-check form-switch">
                <input type="checkbox" name="is_active" id="is_active_form" class="form-check-input" {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active_form">Active</label>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="allow_installments_form" name="allow_installments" {{ old('allow_installments', $product->allow_installments ?? false) ? 'checked' : '' }}>
                <label class="form-check-label" for="allow_installments_form">Allow Installment Payments</label>
            </div>
        </div>
        <div class="card-body" id="installmentSettingsForm" style="display: {{ old('allow_installments', $product->allow_installments ?? false) ? 'block' : 'none' }};">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Maximum Installments</label>
                        <input type="number" name="max_installments" class="form-control" value="{{ old('max_installments', $product->max_installments ?? 12) }}" min="2" max="60">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Interest Rate (%)</label>
                        <input type="number" step="0.01" name="installment_interest_rate" class="form-control" value="{{ old('installment_interest_rate', $product->installment_interest_rate ?? 0) }}" min="0" max="100">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <button type="submit" class="btn btn-primary">{{ $mode === 'edit' ? 'Update' : 'Create' }} Product</button>
    </div>
</form>

@push('scripts')
<script>
document.getElementById('allow_installments_form').addEventListener('change', function() {
    document.getElementById('installmentSettingsForm').style.display = this.checked ? 'block' : 'none';
});
</script>
@endpush


