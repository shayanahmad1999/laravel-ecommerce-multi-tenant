@extends('layouts.app')

@section('title', 'Edit Product')
@section('page-title', 'Edit Product')

@section('content')
<div class="card">
    <div class="card-body">
        <a href="{{ route('products.index') }}" class="btn btn-secondary mb-3">Back</a>
        @include('products.partials.form', ['product' => $product, 'categories' => $categories, 'mode' => 'edit'])
    </div>
</div>
@endsection


