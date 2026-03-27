<?php

use App\Services\AiCourseGeneratorService;
use App\Services\YouTubeScraperService;

/**
 * @group integration
 */
it('actually integrates with Gemini AI to generate between 10 and 20 courses', function () {
    // We instantiate the real service wrapper
    $aiService = app(AiCourseGeneratorService::class);

    // Call the exact live Google Gemini API
    $courses = $aiService->generateCourses('Live Test Category');

    // Asserts that the third party integration is working and returning correct counts
    expect($courses)
        ->toBeArray()
        ->and(count($courses))->toBeGreaterThanOrEqual(10)
        ->and(count($courses))->toBeLessThanOrEqual(20);
});

/**
 * @group integration
 */
it('actually integrates with YouTube API to fetch exactly 2 playlists per course', function () {
    // We instantiate the real service wrapper
    $youtubeService = app(YouTubeScraperService::class);

    // Call the exact live YouTube API
    $playlists = $youtubeService->searchPlaylists('Laravel PHP Basics');

    // Asserts the live data boundary exactly
    expect($playlists)->toBeArray();
    expect(count($playlists))->toBe(2);

    // Asser the live structure data returned from YT
    expect($playlists[0])->toHaveKeys([
        'playlist_id',
        'title',
        'lessons_count',
        'view_count',
        'duration_seconds'
    ]);
});
