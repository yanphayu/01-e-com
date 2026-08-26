<?php

namespace App\Repositories\Eloquent;

use App\Models\Category;
use App\Repositories\Interfaces\CategoryRepositoryInterface;

class CategoryRepository extends BaseRepository implements CategoryRepositoryInterface
{
    public function __construct(Category $model)
    {
        parent::__construct($model);
    }

    public function findBySlug(string $slug)
    {
        return $this->model->where('slug', $slug)->first();
    }

    public function getTree()
    {
        return $this->model
            ->with('children.children.children')
            ->whereNull('parent_id')
            ->get();
    }

    public function getRootCategories()
    {
        return $this->model
            ->whereNull('parent_id')
            ->withCount('products')
            ->get();
    }

    public function getChildren(string $parentId)
    {
        return $this->model
            ->where('parent_id', $parentId)
            ->withCount('products')
            ->get();
    }

    public function getActiveCategories()
    {
        return $this->model
            ->whereNull('parent_id')
            ->with(['children' => function ($query) {
                $query->withCount('products');
            }])
            ->withCount('products')
            ->get();
    }
}