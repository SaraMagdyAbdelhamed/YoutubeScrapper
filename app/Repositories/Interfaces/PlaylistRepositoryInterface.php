<?php

namespace App\Repositories\Interfaces;

use App\Models\Playlist;
use Illuminate\Pagination\LengthAwarePaginator;

interface PlaylistRepositoryInterface extends BaseRepositoryInterface
{
    public function getFilteredWithPagination(?int $categoryId, int $perPage = 12): LengthAwarePaginator;
    public function attachCourse(Playlist $playlist, int $courseId): void;
}
