<?php

namespace App\Repositories\Interfaces;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface BaseRepositoryInterface
{
    public function all(array $columns = ['*']);
    public function find(string $id, array $columns = ['*']);
    public function findOrFail(string $id, array $columns = ['*']);
    public function create(array $data);
    public function update(string $id, array $data);
    public function delete(string $id);
    public function paginate(int $perPage = 15, array $columns = ['*']);
    public function search(array $filters, int $perPage = 15);
}
