@extends('layouts.app')

@section('title', 'Create Category')
@section('page-title', 'Create Category')

@section('content')
<div class="card">
    <div class="card-body">
        <a href="{{ route('categories.index') }}" class="btn btn-secondary mb-3">Back</a>
        @include('categories.partials.form', ['category' => null, 'parentCategories' => $parentCategories, 'mode' => 'create'])
    </div>
</div>
@endsection


