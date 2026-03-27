<?php

namespace App\Repositories;

use App\Models\Playlist;
use App\Repositories\Interfaces\PlaylistRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class PlaylistRepository extends BaseRepository implements PlaylistRepositoryInterface
{
    public function __construct(Playlist $model)
    {
        parent::__construct($model);
    }

    public function getFilteredWithPagination(?int $categoryId, int $perPage = 12): LengthAwarePaginator
    {
        $query = $this->model->newQuery()->with('courses.categories');
        
        if ($categoryId) {
            $query->whereHas('courses.categories', function($q) use ($categoryId) {
                $q->where('categories.id', $categoryId);
            });
        }
        
        return $query->paginate($perPage);
    }

    public function attachCourse(Playlist $playlist, int $courseId): void
    {
        $playlist->courses()->syncWithoutDetaching([$courseId]);
    }
}
