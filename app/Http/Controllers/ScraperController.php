<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\ScrapeCategoryJob;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Repositories\Interfaces\PlaylistRepositoryInterface;

use App\Http\Requests\ScrapeCategoryRequest;

class ScraperController extends Controller
{
    public function __construct(
        protected CategoryRepositoryInterface $categoryRepository,
        protected PlaylistRepositoryInterface $playlistRepository
    ) {}

    public function index(\Illuminate\Http\Request $request)
    {
        $categories = $this->categoryRepository->getWithCoursesCount();
        $categoryId = $request->get('category_id');

        $playlists = $this->playlistRepository->getFilteredWithPagination($categoryId);
        $playlists->withQueryString();
        
        // Count total unique playlists for 'All' pill
        $totalPlaylists = $this->playlistRepository->count();

        return view('home', compact('playlists', 'categories', 'categoryId', 'totalPlaylists'));
    }

    public function scrape(ScrapeCategoryRequest $request)
    {

        $input = $request->categories;
        $categoryNames = array_map('trim', explode("\n", $input));
        $categoryNames = array_filter($categoryNames);

        foreach ($categoryNames as $name) {
            ScrapeCategoryJob::dispatch($name);
        }

        return redirect()->back()->with('success', 'تم بدء جلب البيانات! يرجى الانتظار حتى تنتهي المهام في الخلفية.');
    }
}
