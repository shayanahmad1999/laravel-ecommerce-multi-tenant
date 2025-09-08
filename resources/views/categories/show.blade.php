@extends('layouts.app')

@section('title', $category->name)
@section('page-title', $category->name)

@section('content')
<div class="card">
    <div class="card-body">
        <h4>{{ $category->name }}</h4>
        <p>{{ $category->description }}</p>
        @if($category->image)
            <img src="/storage/{{ $category->image }}" class="img-thumbnail mb-3" style="max-width: 200px;">
        @endif
        <p><strong>Status:</strong> {{ $category->is_active ? 'Active' : 'Inactive' }}</p>
        <p><strong>Parent:</strong> {{ $category->parent?->name ?? 'Root' }}</p>
    </div>
</div>
@endsection


