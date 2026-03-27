<?php

namespace App\Repositories;

use App\Models\Course;
use App\Repositories\Interfaces\CourseRepositoryInterface;

class CourseRepository extends BaseRepository implements CourseRepositoryInterface
{
    public function __construct(Course $model)
    {
        parent::__construct($model);
    }

    public function attachCategory(Course $course, int $categoryId): void
    {
        $course->categories()->syncWithoutDetaching([$categoryId]);
    }
}
