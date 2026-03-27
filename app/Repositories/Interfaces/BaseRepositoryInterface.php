<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface BaseRepositoryInterface
{
    public function all(): Collection;
    public function count(): int;
    public function create(array $attributes): Model;
    public function firstOrCreate(array $attributes, array $values = []): Model;
    public function updateOrCreate(array $attributes, array $values = []): Model;
}
