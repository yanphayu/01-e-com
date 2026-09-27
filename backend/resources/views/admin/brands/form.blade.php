@extends('admin.layouts.app')

@section('title', isset($brand) ? 'Edit brand' : 'New brand')

@section('crumb')
    <a href="{{ route('admin.brands.index') }}" class="hover:text-neutral-900 hover:underline">Brands</a>
    <span class="mx-2 text-neutral-300">/</span>{{ isset($brand) ? 'Edit' : 'New' }}
@endsection

@section('content')
    <div class="max-w-2xl">
        <h2 class="admin-page-title">{{ isset($brand) ? 'Edit brand' : 'New brand' }}</h2>

        <form method="POST"
              action="{{ isset($brand) ? route('admin.brands.update', $brand) : route('admin.brands.store') }}"
              class="admin-form">
            @csrf
            @if (isset($brand))
                @method('PUT')
            @endif

            <div>
                <label for="subcategory_id" class="mb-1 block text-sm font-medium text-neutral-700">Subcategory</label>
                <select id="subcategory_id" name="subcategory_id" required
                        class="admin-control">
                    @foreach ($subcategories as $subcategory)
                        <option value="{{ $subcategory->id }}" @selected(($brand->subcategory_id ?? '') == $subcategory->id)>{{ $subcategory->name }}</option>
                    @endforeach
                </select>
                @error('subcategory_id')
                    <p class="admin-field-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="name" class="mb-1 block text-sm font-medium text-neutral-700">Name</label>
                <input type="text" id="name" name="name" value="{{ old('name', $brand->name ?? '') }}" required
                       class="admin-control">
                @error('name')
                    <p class="admin-field-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-2">
                <button type="submit" class="admin-button admin-button-primary">
                    {{ isset($brand) ? 'Save changes' : 'Create brand' }}
                </button>
                <a href="{{ route('admin.brands.index') }}" class="admin-button admin-button-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection