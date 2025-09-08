@extends('layouts.app')

@section('title', 'Create Product')
@section('page-title', 'Create Product')

@section('content')
<div class="card">
    <div class="card-body">
        <a href="{{ route('products.index') }}" class="btn btn-secondary mb-3">Back</a>
        @include('products.partials.form', ['product' => null, 'categories' => $categories, 'mode' => 'create'])
    </div>
</div>
@endsection


