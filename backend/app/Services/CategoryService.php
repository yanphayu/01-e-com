<?php

namespace App\Services;

use App\Models\Category;
use App\Repositories\Interfaces\CategoryRepositoryInterface;

class CategoryService
{
    public function __construct(
        protected CategoryRepositoryInterface $categoryRepository,
    ) {}

    public function getAll()
    {
        return $this->categoryRepository->all();
    }

    public function getTree()
    {
        return $this->categoryRepository->getTree();
    }

    public function getById(string $id): Category
    {
        return $this->categoryRepository->findOrFail($id);
    }

    public function create(array $data): Category
    {
        return $this->categoryRepository->create($data);
    }

    public function update(string $id, array $data): Category
    {
        $this->categoryRepository->update($id, $data);

        return $this->categoryRepository->findOrFail($id);
    }

    public function delete(string $id): void
    {
        $this->categoryRepository->delete($id);
    }

    public function getBySlug(string $slug): Category
    {
        $category = $this->categoryRepository->findBySlug($slug);

        if (!$category) {
            throw new \Exception('Category not found.');
        }

        return $category;
    }
}
