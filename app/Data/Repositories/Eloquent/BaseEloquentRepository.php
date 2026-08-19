<?php

namespace App\Data\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

abstract class BaseEloquentRepository
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function query(): Builder
    {
        return $this->model->newQuery();
    }

    public function update(int $id, array $data): bool
    {
        $record = $this->model->find($id);

        if (! $record) {
            return false;
        }

        return $record->update($data);
    }

    public function delete(int $id): bool
    {
        $record = $this->model->find($id);

        if (! $record) {
            return false;
        }

        return $record->delete();
    }
}
