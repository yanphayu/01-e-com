<?php

namespace App\Http\Controllers;

use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductModel;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:1|max:100',
        ]);

        $search = $request->q;

        $products = Product::with(['images', 'subcategory.category'])
            ->where('is_active', true)
            ->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%"))
            ->limit(5)
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'type' => 'product',
                'name' => $p->name,
                'subtitle' => ($p->subcategory->category->name ?? '').' → '.($p->subcategory->name ?? ''),
                'price' => $p->price,
                'image' => $p->images->first()?->image,
            ]);

        $users = User::where('name', 'like', "%{$search}%")
            ->limit(3)
            ->get()
            ->map(fn ($u) => [
                'id' => $u->id,
                'type' => 'user',
                'name' => $u->name,
                'subtitle' => $u->email,
                'image' => $u->profile->avatar ?? null,
            ]);

        $categories = Category::where('name', 'like', "%{$search}%")
            ->limit(3)
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'type' => 'category',
                'name' => $c->name,
                'subtitle' => $c->subcategories->count().' subcategories',
            ]);

        $subcategories = Subcategory::with('category')
            ->where('name', 'like', "%{$search}%")
            ->limit(3)
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'type' => 'subcategory',
                'name' => $s->name,
                'subtitle' => $s->category->name ?? '',
                'category_id' => $s->category_id,
            ]);

        $brands = Brand::where('name', 'like', "%{$search}%")
            ->limit(3)
            ->get()
            ->map(fn ($b) => [
                'id' => $b->id,
                'type' => 'brand',
                'name' => $b->name,
                'subtitle' => $b->products()->count().' products',
            ]);

        $models = ProductModel::with('brand')
            ->where('name', 'like', "%{$search}%")
            ->limit(3)
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'type' => 'model',
                'name' => $m->name,
                'subtitle' => $m->brand->name ?? '',
                'brand_id' => $m->brand_id,
            ]);

        $attributes = Attribute::where('name', 'like', "%{$search}%")
            ->limit(3)
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'type' => 'attribute',
                'name' => $a->name,
                'subtitle' => $a->productAttributes()->count().' products',
            ]);

        return response()->json([
            'success' => true,
            'data' => [
                'products' => $products,
                'users' => $users,
                'categories' => $categories,
                'subcategories' => $subcategories,
                'brands' => $brands,
                'models' => $models,
                'attributes' => $attributes,
            ],
        ]);
    }
}
