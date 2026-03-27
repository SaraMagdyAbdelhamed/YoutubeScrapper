<?php

namespace App\Repositories\Interfaces;

use Illuminate\Support\Collection;

interface CategoryRepositoryInterface extends BaseRepositoryInterface
{
    public function getWithCoursesCount(): Collection;
}
