@extends('admin.layouts.app')

@section('title', isset($attribute) ? 'Edit attribute' : 'New attribute')

@section('crumb')
    <a href="{{ route('admin.attributes.index') }}" class="hover:text-neutral-900 hover:underline">Attributes</a>
    <span class="mx-2 text-neutral-300">/</span>{{ isset($attribute) ? 'Edit' : 'New' }}
@endsection

@section('content')
    <div class="max-w-2xl">
        <h2 class="admin-page-title">{{ isset($attribute) ? 'Edit attribute' : 'New attribute' }}</h2>

        <form method="POST"
              action="{{ isset($attribute) ? route('admin.attributes.update', $attribute) : route('admin.attributes.store') }}"
              class="admin-form">
            @csrf
            @if (isset($attribute))
                @method('PUT')
            @endif

            <div>
                <label for="name" class="mb-1 block text-sm font-medium text-neutral-700">Name</label>
                <input type="text" id="name" name="name" value="{{ old('name', $attribute->name ?? '') }}" required
                       placeholder="e.g. RAM, Storage, Color"
                       class="admin-control">
                @error('name')
                    <p class="admin-field-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-2">
                <button type="submit" class="admin-button admin-button-primary">
                    {{ isset($attribute) ? 'Save changes' : 'Create attribute' }}
                </button>
                <a href="{{ route('admin.attributes.index') }}" class="admin-button admin-button-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection