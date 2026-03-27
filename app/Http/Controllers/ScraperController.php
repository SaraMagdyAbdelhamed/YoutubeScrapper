<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\ScrapeCategoryJob;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Repositories\Interfaces\PlaylistRepositoryInterface;

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
        
        // Count total unique playlists for 'All' pill
        $totalPlaylists = $this->playlistRepository->count();

        return view('home', compact('playlists', 'categories', 'categoryId', 'totalPlaylists'));
    }

    public function scrape(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'categories' => 'required|string',
        ]);

        $input = $request->categories;
        $categoryNames = array_map('trim', explode("\n", $input));
        $categoryNames = array_filter($categoryNames);

        foreach ($categoryNames as $name) {
            ScrapeCategoryJob::dispatch($name);
        }

        return redirect()->back()->with('success', 'Fetching started! Please wait for the background jobs to finish.');
    }
}
