<form method="POST" action="{{ $mode === 'edit' ? route('categories.update', $category) : route('categories.store') }}" enctype="multipart/form-data">
    @csrf
    @if($mode === 'edit')
        @method('PUT')
    @endif

    <div class="mb-3">
        <label class="form-label">Name *</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $category->name ?? '') }}" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="3">{{ old('description', $category->description ?? '') }}</textarea>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Parent Category</label>
                <select name="parent_id" class="form-select">
                    <option value="">Select Parent Category</option>
                    @foreach($parentCategories as $parent)
                        <option value="{{ $parent->id }}" {{ old('parent_id', $category->parent_id ?? '') == $parent->id ? 'selected' : '' }}>{{ $parent->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Image</label>
                <input type="file" name="image" class="form-control">
                @if(!empty($category?->image))
                    <img src="/storage/{{ $category->image }}" class="img-thumbnail mt-2" style="max-width: 120px;">
                @endif
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Sort Order</label>
                <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $category->sort_order ?? 0) }}" min="0">
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3 form-check form-switch">
                <input type="checkbox" name="is_active" id="is_active_category_form" class="form-check-input" {{ old('is_active', $category->is_active ?? true) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active_category_form">Active</label>
            </div>
        </div>
    </div>

    <div>
        <button type="submit" class="btn btn-primary">{{ $mode === 'edit' ? 'Update' : 'Create' }} Category</button>
    </div>
</form>


