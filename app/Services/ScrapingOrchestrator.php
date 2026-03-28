<?php

namespace App\Services;

use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Repositories\Interfaces\CourseRepositoryInterface;
use App\Repositories\Interfaces\PlaylistRepositoryInterface;
use Illuminate\Support\Facades\Log;

class ScrapingOrchestrator
{
    public function __construct(
        protected AiCourseGeneratorService $aiService,
        protected YouTubeScraperService $youtubeService,
        protected CategoryRepositoryInterface $categoryRepository,
        protected CourseRepositoryInterface $courseRepository,
        protected PlaylistRepositoryInterface $playlistRepository
    ) {}

    public function processCategory(string $categoryName): void
    {
        Log::info("Processing category: {$categoryName}");

        $category = $this->categoryRepository->firstOrCreate(['name' => $categoryName]);

        $courseTitles = $this->aiService->generateCourses($categoryName);

        Log::info("Generated " . count($courseTitles) . " courses for category {$categoryName}");

        foreach ($courseTitles as $title) {
            $course = $this->courseRepository->firstOrCreate(['title' => $title]);
            $this->courseRepository->attachCategory($course, $category->id);

            // Case 1: Same course assigned to diff categories
            // If the course already existed, we don't need to re-scrape its playlists.
            if (! $course->wasRecentlyCreated) {
                Log::info("Course '{$title}' already exists. Assigned to category and skipping playlist scraping.");
                continue;
            }

            $playlists = $this->youtubeService->searchPlaylists($title);
            
            Log::info("Found " . count($playlists) . " playlists for course {$title}");

            foreach ($playlists as $playlistData) {
                $playlistInfo = [
                    'title' => $playlistData['title'],
                    'description' => $playlistData['description'],
                    'thumbnail' => $playlistData['thumbnail'],
                    'channel_name' => $playlistData['channel_name'],
                    'lessons_count' => $playlistData['lessons_count'] ?? 0,
                    'view_count' => $playlistData['view_count'] ?? 0,
                    'duration_seconds' => $playlistData['duration_seconds'] ?? 0,
                ];

                $playlist = $this->playlistRepository->updateOrCreate(
                    ['playlist_id' => $playlistData['playlist_id']],
                    $playlistInfo
                );

                // Case 2: Same playlist assigned to diff courses
                $this->playlistRepository->attachCourse($playlist, $course->id);
                
                if (! $playlist->wasRecentlyCreated) {
                    Log::info("Playlist '{$playlist->playlist_id}' already exists. Assigned to course '{$title}'.");
                }
            }
        }

        Log::info("Completed processing category: {$categoryName}");
    }
}
