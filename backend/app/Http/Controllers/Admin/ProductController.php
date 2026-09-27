<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Notifications\ProductStatusUpdated;
use App\Support\Notifier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function filteredQuery(Request $request): Builder
    {
        $query = Product::with(['user', 'subcategory.category', 'images']);

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->string('search')->trim()}%");
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('category_id')) {
            $query->whereHas('subcategory', function ($q) use ($request) {
                $q->where('category_id', $request->integer('category_id'));
            });
        }

        return $query->latest();
    }

    public function index(Request $request): View
    {
        $products = $this->filteredQuery($request)->paginate(15)->withQueryString();
        $categories = Category::orderBy('name')->get(['id', 'name']);

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function show(Product $product): View
    {
        $product->load([
            'user.profile',
            'subcategory.category',
            'detail.brand',
            'detail.model',
            'images',
            'phones',
            'productAttributes.attribute',
            'comments.user.profile',
        ]);

        return view('admin.products.show', compact('product'));
    }

    public function toggleActive(Product $product): RedirectResponse
    {
        $product->update(['is_active' => ! $product->is_active]);

        return back()->with('status', 'Product status updated.');
    }

    public function approve(Product $product): RedirectResponse
    {
        $product->update(['status' => 'approved', 'is_active' => true]);
        $this->notifyOwner($product);

        return redirect()->route('admin.products.show', $product)->with('status', 'Product approved and published.');
    }

    public function reject(Product $product): RedirectResponse
    {
        $product->update(['status' => 'rejected', 'is_active' => false]);
        $this->notifyOwner($product);

        return redirect()->route('admin.products.show', $product)->with('status', 'Product rejected.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('admin.products.index')->with('status', 'Product deleted.');
    }

    private function notifyOwner(Product $product): void
    {
        try {
            Notifier::send($product->user, new ProductStatusUpdated($product));
        } catch (\Exception $e) {
            // Broadcast may fail if Reverb is not running — notification is still saved to DB
        }
    }
}
