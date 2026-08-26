<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Interfaces\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class BaseRepository implements BaseRepositoryInterface
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function all(array $columns = ['*'])
    {
        return $this->model->all($columns);
    }

    public function find(string $id, array $columns = ['*'])
    {
        return $this->model->find($id, $columns);
    }

    public function findOrFail(string $id, array $columns = ['*'])
    {
        return $this->model->findOrFail($id, $columns);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(string $id, array $data)
    {
        $model = $this->findOrFail($id);
        $model->update($data);

        return $model->fresh();
    }

    public function delete(string $id)
    {
        $model = $this->findOrFail($id);

        return $model->delete();
    }

    public function paginate(int $perPage = 15, array $columns = ['*'])
    {
        return $this->model->paginate($perPage, $columns);
    }

    public function search(array $filters, int $perPage = 15)
    {
        $query = $this->model->newQuery();

        foreach ($filters as $field => $value) {
            if (is_null($value) || $value === '') {
                continue;
            }
            if (str_contains($field, '_like')) {
                $query->where(str_replace('_like', '', $field), 'LIKE', "%{$value}%");
            } else {
                $query->where($field, $value);
            }
        }

        return $query->paginate($perPage);
    }
}