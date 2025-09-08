@extends('layouts.app')

@section('title', 'Edit Category')
@section('page-title', 'Edit Category')

@section('content')
<div class="card">
    <div class="card-body">
        <a href="{{ route('categories.index') }}" class="btn btn-secondary mb-3">Back</a>
        @include('categories.partials.form', ['category' => $category, 'parentCategories' => $parentCategories, 'mode' => 'edit'])
    </div>
</div>
@endsection


