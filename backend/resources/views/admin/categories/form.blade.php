@extends('admin.layouts.app')

@section('title', isset($category) ? 'Edit category' : 'New category')

@section('crumb')
    <a href="{{ route('admin.categories.index') }}" class="hover:text-neutral-900 hover:underline">Categories</a>
    <span class="mx-2 text-neutral-300">/</span>{{ isset($category) ? 'Edit' : 'New' }}
@endsection

@section('content')
    <div class="max-w-2xl">
        <h2 class="admin-page-title">{{ isset($category) ? 'Edit category' : 'New category' }}</h2>

        <form method="POST"
              action="{{ isset($category) ? route('admin.categories.update', $category) : route('admin.categories.store') }}"
              class="admin-form">
            @csrf
            @if (isset($category))
                @method('PUT')
            @endif

            <div>
                <label for="name" class="mb-1 block text-sm font-medium text-neutral-700">Name</label>
                <input type="text" id="name" name="name" value="{{ old('name', $category->name ?? '') }}" required
                       class="admin-control">
                @error('name')
                    <p class="admin-field-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="mb-1 block text-sm font-medium text-neutral-700">Description</label>
                <textarea id="description" name="description" rows="3"
                          class="admin-control">{{ old('description', $category->description ?? '') }}</textarea>
            </div>

            <label class="flex items-center gap-2 text-sm text-neutral-700">
                <input type="checkbox" name="is_active" value="1" @checked($category->is_active ?? true) class="admin-checkbox">
                Active on the storefront
            </label>

            <div class="flex gap-2">
                <button type="submit" class="admin-button admin-button-primary">
                    {{ isset($category) ? 'Save changes' : 'Create category' }}
                </button>
                <a href="{{ route('admin.categories.index') }}" class="admin-button admin-button-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection