<?php

namespace App\Repositories\Interfaces;

interface CategoryRepositoryInterface extends BaseRepositoryInterface
{
    public function findBySlug(string $slug);
    public function getTree();
    public function getRootCategories();
    public function getChildren(string $parentId);
    public function getActiveCategories();
}
