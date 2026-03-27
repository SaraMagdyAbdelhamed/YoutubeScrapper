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

                $this->playlistRepository->attachCourse($playlist, $course->id);
            }
        }

        Log::info("Completed processing category: {$categoryName}");
    }
}
