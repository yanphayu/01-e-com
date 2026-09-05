<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['subcategory.category', 'images', 'productAttributes.attribute'])
            ->where('is_active', true);

        if ($request->has('subcategory_id')) {
            $query->where('subcategory_id', $request->subcategory_id);
        }

        if ($request->has('category_id')) {
            $query->whereHas('subcategory', fn ($q) => $q->where('category_id', $request->category_id));
        }

        if ($request->has('province')) {
            $query->whereHas('detail', fn ($q) => $q->where('province', $request->province));
        }

        if ($request->has('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $products = $query->latest()->paginate(20);

        return response()->json([
            'success' => true,
            'message' => 'Products retrieved successfully',
            'data' => $products,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subcategory_id' => 'required|exists:subcategories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'details' => 'nullable|array',
            'details.brand' => 'nullable|string|max:255',
            'details.model' => 'nullable|string|max:255',
            'details.address' => 'nullable|string|max:255',
            'details.province' => 'nullable|string|max:255',
            'details.khan' => 'nullable|string|max:255',
            'details.sangkat' => 'nullable|string|max:255',
            'details.latitude' => 'nullable|numeric|between:-90,90',
            'details.longitude' => 'nullable|numeric|between:-180,180',
            'details.condition' => 'nullable|string|max:255',
            'phones' => 'nullable|array',
            'phones.*' => 'nullable|string|max:255',
            'attributes' => 'nullable|array',
            'attributes.*.attribute_id' => 'required_with:attributes|exists:attributes,id',
            'attributes.*.value' => 'required_with:attributes|string|max:255',
            'images' => 'nullable|array',
            'images.*' => 'image|max:5120',
        ]);

        $slug = Str::slug($validated['name']);
        $originalSlug = $slug;
        $counter = 1;

        while (Product::where('slug', $slug)->exists()) {
            $slug = $originalSlug.'-'.$counter;
            $counter++;
        }

        $product = $request->user()->products()->create([
            'subcategory_id' => $validated['subcategory_id'],
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
        ]);

        if (isset($validated['details'])) {
            $product->detail()->create($validated['details']);
        }

        if (isset($validated['attributes'])) {
            foreach ($validated['attributes'] as $index => $attr) {
                $product->productAttributes()->create([
                    'attribute_id' => $attr['attribute_id'],
                    'value' => $attr['value'],
                ]);
            }
        }

        if (isset($validated['phones'])) {
            foreach ($validated['phones'] as $index => $phone) {
                if ($phone) {
                    $product->phones()->create([
                        'phone' => $phone,
                        'is_primary' => $index === 0,
                    ]);
                }
            }
        }

        if ($request->hasFile('images')) {
            $images = $request->file('images');
            foreach ($images as $index => $image) {
                $product->images()->create([
                    'image' => $image->store('products/'.$product->id, 'public'),
                    'is_primary' => $index === 0,
                    'sort_order' => $index,
                ]);
            }
        }

        $product->load(['detail', 'images', 'productAttributes.attribute', 'subcategory.category', 'phones']);

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'data' => $product,
        ], 201);
    }

    public function show(Product $product): JsonResponse
    {
        $product->load(['user.profile', 'subcategory.category', 'detail', 'images', 'productAttributes.attribute', 'phones']);

        return response()->json([
            'success' => true,
            'message' => 'Product retrieved successfully',
            'data' => $product,
        ]);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        if ($request->user()->id !== $product->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'subcategory_id' => 'sometimes|exists:subcategories,id',
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'is_active' => 'boolean',
            'details' => 'nullable|array',
            'details.brand' => 'nullable|string|max:255',
            'details.model' => 'nullable|string|max:255',
            'details.address' => 'nullable|string|max:255',
            'details.province' => 'nullable|string|max:255',
            'details.khan' => 'nullable|string|max:255',
            'details.sangkat' => 'nullable|string|max:255',
            'details.latitude' => 'nullable|numeric|between:-90,90',
            'details.longitude' => 'nullable|numeric|between:-180,180',
            'details.condition' => 'nullable|string|max:255',
            'phones' => 'nullable|array',
            'phones.*' => 'nullable|string|max:255',
        ]);

        $productFields = collect($validated)->only([
            'subcategory_id', 'name', 'description', 'price', 'is_active',
        ])->toArray();

        if (isset($productFields['name']) && $productFields['name'] !== $product->name) {
            $slug = Str::slug($productFields['name']);
            $originalSlug = $slug;
            $counter = 1;

            while (Product::where('slug', $slug)->where('id', '!=', $product->id)->exists()) {
                $slug = $originalSlug.'-'.$counter;
                $counter++;
            }

            $productFields['slug'] = $slug;
        }

        $product->update($productFields);

        if (isset($validated['details'])) {
            $product->detail()->updateOrCreate([], $validated['details']);
        }

        if (isset($validated['phones'])) {
            $product->phones()->delete();
            foreach ($validated['phones'] as $index => $phone) {
                if ($phone) {
                    $product->phones()->create([
                        'phone' => $phone,
                        'is_primary' => $index === 0,
                    ]);
                }
            }
        }

        $product->load(['detail', 'images', 'productAttributes.attribute', 'subcategory.category', 'phones']);

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'data' => $product,
        ]);
    }

    public function destroy(Request $request, Product $product): JsonResponse
    {
        if ($request->user()->id !== $product->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully',
        ]);
    }
}
