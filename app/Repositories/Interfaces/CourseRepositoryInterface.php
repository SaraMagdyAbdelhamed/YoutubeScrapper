<?php

namespace App\Repositories\Interfaces;

use App\Models\Course;

interface CourseRepositoryInterface extends BaseRepositoryInterface
{
    public function attachCategory(Course $course, int $categoryId): void;
}
