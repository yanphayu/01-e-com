@extends('admin.layouts.app')

@section('title', isset($subcategory) ? 'Edit subcategory' : 'New subcategory')

@section('crumb')
    <a href="{{ route('admin.subcategories.index') }}" class="hover:text-neutral-900 hover:underline">Subcategories</a>
    <span class="mx-2 text-neutral-300">/</span>{{ isset($subcategory) ? 'Edit' : 'New' }}
@endsection

@section('content')
    <div class="max-w-2xl">
        <h2 class="admin-page-title">{{ isset($subcategory) ? 'Edit subcategory' : 'New subcategory' }}</h2>

        <form method="POST"
              action="{{ isset($subcategory) ? route('admin.subcategories.update', $subcategory) : route('admin.subcategories.store') }}"
              class="admin-form">
            @csrf
            @if (isset($subcategory))
                @method('PUT')
            @endif

            <div>
                <label for="category_id" class="mb-1 block text-sm font-medium text-neutral-700">Parent category</label>
                <select id="category_id" name="category_id" required
                        class="admin-control">
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(($subcategory->category_id ?? '') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('category_id')
                    <p class="admin-field-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="name" class="mb-1 block text-sm font-medium text-neutral-700">Name</label>
                <input type="text" id="name" name="name" value="{{ old('name', $subcategory->name ?? '') }}" required
                       class="admin-control">
                @error('name')
                    <p class="admin-field-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="mb-1 block text-sm font-medium text-neutral-700">Description</label>
                <textarea id="description" name="description" rows="3"
                          class="admin-control">{{ old('description', $subcategory->description ?? '') }}</textarea>
            </div>

            <div class="space-y-2">
                <label class="flex items-center gap-2 text-sm text-neutral-700">
                    <input type="checkbox" name="has_brand" value="1" @checked($subcategory->has_brand ?? false) class="admin-checkbox">
                    Products can have a brand
                </label>
                <label class="flex items-center gap-2 text-sm text-neutral-700">
                    <input type="checkbox" name="has_model" value="1" @checked($subcategory->has_model ?? false) class="admin-checkbox">
                    Products can have a model
                </label>
                <label class="flex items-center gap-2 text-sm text-neutral-700">
                    <input type="checkbox" name="is_active" value="1" @checked($subcategory->is_active ?? true) class="admin-checkbox">
                    Active on the storefront
                </label>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="admin-button admin-button-primary">
                    {{ isset($subcategory) ? 'Save changes' : 'Create subcategory' }}
                </button>
                <a href="{{ route('admin.subcategories.index') }}" class="admin-button admin-button-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection