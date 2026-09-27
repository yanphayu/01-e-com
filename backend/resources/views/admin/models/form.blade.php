@extends('admin.layouts.app')

@section('title', isset($model) ? 'Edit model' : 'New model')

@section('crumb')
    <a href="{{ route('admin.models.index') }}" class="hover:text-neutral-900 hover:underline">Models</a>
    <span class="mx-2 text-neutral-300">/</span>{{ isset($model) ? 'Edit' : 'New' }}
@endsection

@section('content')
    <div class="max-w-2xl">
        <h2 class="admin-page-title">{{ isset($model) ? 'Edit model' : 'New model' }}</h2>

        <form method="POST"
              action="{{ isset($model) ? route('admin.models.update', $model) : route('admin.models.store') }}"
              class="admin-form">
            @csrf
            @if (isset($model))
                @method('PUT')
            @endif

            <div>
                <label for="brand_id" class="mb-1 block text-sm font-medium text-neutral-700">Brand</label>
                <select id="brand_id" name="brand_id" required
                        class="admin-control">
                    @foreach ($brands as $brand)
                        <option value="{{ $brand->id }}" @selected(($model->brand_id ?? '') == $brand->id)>{{ $brand->name }}</option>
                    @endforeach
                </select>
                @error('brand_id')
                    <p class="admin-field-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="name" class="mb-1 block text-sm font-medium text-neutral-700">Name</label>
                <input type="text" id="name" name="name" value="{{ old('name', $model->name ?? '') }}" required
                       class="admin-control">
                @error('name')
                    <p class="admin-field-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <span class="mb-2 block text-sm font-medium text-neutral-700">Linked attributes</span>
                <div class="grid grid-cols-2 gap-2">
                    @foreach ($attributes as $attribute)
                        <label class="flex items-center gap-2 rounded-lg border border-neutral-200 px-3 py-2 text-sm text-neutral-700">
                            <input type="checkbox" name="attribute_ids[]" value="{{ $attribute->id }}"
                                   @checked(in_array($attribute->id, old('attribute_ids', $model?->attributes?->pluck('id')->all() ?? []), true))
                                   class="admin-checkbox">
                            {{ $attribute->name }}
                        </label>
                    @endforeach
                </div>
                @error('attribute_ids')
                    <p class="admin-field-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-2">
                <button type="submit" class="admin-button admin-button-primary">
                    {{ isset($model) ? 'Save changes' : 'Create model' }}
                </button>
                <a href="{{ route('admin.models.index') }}" class="admin-button admin-button-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection